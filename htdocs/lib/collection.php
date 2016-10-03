<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class Collection {

    private $id;
    private $name;
    private $description;
    private $owner;
    private $group;
    private $institution;
    private $mtime;
    private $ctime;
    private $navigation;
    private $submittedgroup;
    private $submittedhost;
    private $submittedtime;
    private $submittedstatus;
    private $views;
    private $tags;
    private $framework;

    const UNSUBMITTED = 0;
    const SUBMITTED = 1;
    const PENDING_RELEASE = 2;

    public function __construct($id=0, $data=null) {

        if (!empty($id)) {
            $tempdata = get_record('collection','id',$id);
            if (empty($tempdata)) {
                throw new CollectionNotFoundException("Collection with id $id not found");
            }
            if (!empty($data)) {
                $data = array_merge((array)$tempdata, $data);
            }
            else {
                $data = $tempdata; // use what the database has
            }
            $this->id = $id;
        }
        else {
            $this->ctime = time();
            $this->mtime = time();
        }

        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
        if (empty($this->group) && empty($this->institution) && empty($this->owner)) {
            global $USER;
            $this->owner = $USER->get('id');
        }
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'tags') {
            return $this->get_tags();
        }
        if ($field == 'views') {
            return $this->views();
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            $this->{$field} = $value;
            $this->mtime = time();
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * Helper function to create or update a Collection from the supplied data.
     *
     * @param array $data
     * @return collection           The newly created/updated collection
     */
    public static function save($data) {
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
        }
        else {
            $id = 0;
        }
        $collection = new Collection($id, $data);
        $collection->set('mtime', time());
        $collection->commit();

        return $collection; // return newly created Collections id
    }

    /**
     * Deletes a Collection
     *
     */
    public function delete() {
        $viewids = get_column('collection_view', 'view', 'collection', $this->id);
        db_begin();

        // Delete navigation blocks within the collection's views which point at this collection.
        if ($viewids) {
            $values = $viewids;
            $values[] = 'navigation';
            $navigationblocks = get_records_select_assoc(
                'block_instance', 'view IN (' . join(',', array_fill(0, count($viewids), '?')) . ') AND blocktype = ?',
                $values
            );
            if ($navigationblocks) {
                safe_require('blocktype', 'navigation');
                foreach ($navigationblocks as $b) {
                    $bi = new BlockInstance($b->id, $b);
                    $configdata = $bi->get('configdata');
                    if (isset($configdata['collection']) && $configdata['collection'] == $this->id) {
                        $bi->delete();
                    }
                }
            }
        }

        delete_records('collection_view','collection',$this->id);
        delete_records('collection_tag','collection',$this->id);
        delete_records('collection','id',$this->id);

        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        if ($viewids) {
            delete_records_select('view_access', 'view IN (' . join(',', $viewids) . ') AND token IS NOT NULL');
        }

        db_commit();
    }

    /**
     * This method updates the contents of the collection table only.
     */
    public function commit() {

        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime', 'submittedtime')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }

        db_begin();

        // if id is not empty we are editing an existing collection
        if (!empty($this->id)) {
            update_record('collection', $fordb, 'id');
        }
        else {
            $id = insert_record('collection', $fordb, 'id', true);
            if ($id) {
                $this->set('id', $id);
            }
        }

        if (isset($this->tags)) {
            delete_records('collection_tag', 'collection', $this->get('id'));
            $tags = check_case_sensitive($this->get_tags(), 'collection_tag');
            foreach ($tags as $tag) {
                //truncate the tag before insert it into the database
                $tag = substr($tag, 0, 128);
                insert_record('collection_tag', (object)array( 'collection' => $this->get('id'), 'tag' => $tag));
            }
        }

        db_commit();
    }

    /**
     * Generates a name for a newly created Collection
     */
    private static function new_name($name, $ownerdata) {
        $extText = get_string('version.', 'mahara');
        $tempname = preg_split('/ '. $extText . '[0-9]$/', $name);
        $name = $tempname[0];

        $taken = get_column_sql('
            SELECT name
            FROM {collection}
            WHERE ' . self::owner_sql($ownerdata) . "
                AND name LIKE ? || '%'", array($name));

        $ext = '';
        $i = 1;
        if ($taken) {
            while (in_array($name . $ext, $taken)) {
                $ext = ' ' . $extText . ++$i;
            }
        }
        return $name . $ext;
    }

    /**
     * Creates a Collection for the given user, based off a given template and other
     * Collection information supplied.
     *
     * Will set a default name of 'Copy of $collectiontitle' if name is not
     * specified in $collectiondata and $titlefromtemplate == false.
     *
     * @param array $collectiondata Contains information of the old collection, submitted in form
     * @param int $templateid The ID of the Collection to copy
     * @param int $userid     The user who has issued the command to create the
     *                        collection.
     * @param int $checkaccess Whether to check that the user can see the collection before copying it
     * @return array A list consisting of the new collection, the template collection and
     *               information about the copy - i.e. how many blocks and
     *               artefacts were copied
     * @throws SystemException under various circumstances, see the source for
     *                         more information
     */
    public static function create_from_template($collectiondata, $templateid, $userid=null, $checkaccess=true, $titlefromtemplate=false) {
        require_once(get_config('libroot') . 'view.php');
        global $SESSION;

        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        db_begin();

        $colltemplate = new Collection($templateid);

        $data = new StdClass;
        // Set a default name if one wasn't set in $collectiondata
        if ($titlefromtemplate) {
            $data->name = $colltemplate->get('name');
        }
        else if (!isset($collectiondata['name'])) {
            $desiredname = $colltemplate->get('name');
            if (get_config('renamecopies')) {
                $desiredname = get_string('Copyof', 'mahara', $desiredname);
            }
            $data->name = self::new_name($desiredname, (object)$collectiondata);
        }
        else {
            $data->name = $collectiondata['name'];
        }
        $data->description = $colltemplate->get('description');
        $data->tags = $colltemplate->get('tags');
        $data->navigation = $colltemplate->get('navigation');
        if (!empty($collectiondata['group'])) {
            $data->group = $collectiondata['group'];
        }
        else if (!empty($collectiondata['institution'])) {
            $data->institution = $collectiondata['institution'];
        }
        else if (!empty($collectiondata['owner'])) {
            $data->owner = $collectiondata['owner'];
        }
        else {
            $data->owner = $userid;
        }
        $data->framework = $colltemplate->get('framework');

        $collection = self::save($data);

        $numcopied = array('pages' => 0, 'blocks' => 0, 'artefacts' => 0);

        $views = $colltemplate->get('views');
        $copyviews = array();
        $evidenceviews = array();
        $artefactcopies = array();
        foreach ($views['views'] as $v) {
            $values = array(
                'new' => true,
                'owner' => isset($data->owner) ? $data->owner : null,
                'group' => isset($data->group) ? $data->group : null,
                'institution' => isset($data->institution) ? $data->institution : null,
                'usetemplate' => $v->view
            );
            list($view, $template, $copystatus) = View::create_from_template($values, $v->view, $userid, $checkaccess, $titlefromtemplate, $artefactcopies);
            // Check to see if we need to re-map any framework evidence
            if (!empty($data->framework) && $userid == $data->owner && count_records('framework_evidence', 'view', $v->view)) {
                $evidenceviews[$v->view] = $view->get('id');
            }
            if (isset($copystatus['quotaexceeded'])) {
                $SESSION->clear('messages');
                return array(null, $colltemplate, array('quotaexceeded' => true));
            }
            $copyviews['view_' . $view->get('id')] = true;
            $numcopied['blocks'] += $copystatus['blocks'];
            $numcopied['artefacts'] += $copystatus['artefacts'];
        }
        $numcopied['pages'] = count($views['views']);

        $collection->add_views($copyviews);

        // Update all the navigation blocks referring to this collection
        if ($viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'))) {
            $navblocks = get_records_select_array(
                'block_instance',
                'view IN (' . join(',', array_fill(0, count($viewids), '?')) . ") AND blocktype = 'navigation'",
                $viewids
            );

            if ($navblocks) {
                safe_require('blocktype', 'navigation');
                foreach ($navblocks as $b) {
                    $bi = new BlockInstance($b->id, $b);
                    $configdata = $bi->get('configdata');
                    if (isset($configdata['collection']) && $configdata['collection'] == $templateid) {
                        $bi->set('configdata', array('collection' => $collection->get('id')));
                        $bi->commit();
                    }
                }
            }
        }
        // If there are views with framework evidence to re-map
        if (!empty($evidenceviews)) {
            // We need to get how the old views/artefacts/blocks/evidence fit together
            $evidences = get_records_sql_array('
                SELECT va.view, va.artefact, va.block, fe.*
                FROM {view} v
                JOIN {view_artefact} va ON va.view = v.id
                JOIN {artefact} a ON a.id = va.artefact
                JOIN {framework_evidence} fe ON fe.view = v.id
                WHERE v.id IN (' . join(',', array_keys($evidenceviews)) . ')
                AND a.id IN (' . join(',', array_keys($artefactcopies)) . ')
                AND fe.annotation = va.block', array());
            $newartefactcopies = array();
            foreach ($artefactcopies as $ac) {
                $newartefactcopies[$ac->newid] = 1;
            }
            // And get how the new views/artefacts/blocks fit together
            $newblocks = get_records_sql_assoc('
                SELECT va.artefact, va.view, va.block
                FROM {view} v
                JOIN {view_artefact} va ON va.view = v.id
                JOIN {artefact} a ON a.id = va.artefact
                WHERE v.id IN (' . join(',', array_values($evidenceviews)) . ')
                AND a.id IN (' . join(',', array_keys($newartefactcopies)) . ')
                AND artefacttype = ?', array('annotation'));

            foreach ($evidences as $evidence) {
                if (key_exists($evidence->artefact, $artefactcopies) && key_exists($artefactcopies[$evidence->artefact]->newid, $newartefactcopies)) {
                    $newartefact = $artefactcopies[$evidence->artefact]->newid;
                    $newevidence = new stdClass();
                    $newevidence->view = $newblocks[$newartefact]->view;
                    $newevidence->artefact = $newartefact;
                    $newevidence->annotation = $newblocks[$newartefact]->block;
                    $newevidence->framework = $evidence->framework;
                    $newevidence->element = $evidence->element;
                    $newevidence->state = 0;
                    $newevidence->reviewer = null;
                    $newevidence->ctime = $evidence->ctime;
                    $newevidence->mtime = $evidence->mtime;
                    insert_record('framework_evidence', $newevidence);
                }
            }
        }

        db_commit();

        return array(
            $collection,
            $colltemplate,
            $numcopied,
        );
    }

    /**
     * Returns a list of the current user, group, or institution collections
     *
     * @param offset current page to display
     * @param limit how many collections to display per page
     * @param groupid current group ID
     * @param institutionname current institution name
     * @return array (count: integer, data: array, offset: integer, limit: integer)
     */
    public static function get_mycollections_data($offset=0, $limit=10, $owner=null, $groupid=null, $institutionname=null) {
        if (!empty($groupid)) {
            $wherestm = '"group" = ?';
            $values = array($groupid);
            $count  = count_records('collection', 'group', $groupid);
        }
        else if (!empty($institutionname)) {
            $wherestm = 'institution = ?';
            $values = array($institutionname);
            $count  = count_records('collection', 'institution', $institutionname);
        }
        else if (!empty($owner)) {
            $wherestm = 'owner = ?';
            $values = array($owner);
            $count  = count_records('collection', 'owner', $owner);
        }
        else {
            $count = 0;
        }
        $data = array();
        if ($count > 0) {
            $data = get_records_sql_assoc("
                SELECT
                    c.id,
                    c.description,
                    c.name,
                    c.submittedgroup,
                    c.submittedhost,
                    c.submittedtime,
                    c.framework,
                    (SELECT COUNT(*) FROM {collection_view} cv WHERE cv.collection = c.id) AS numviews
                FROM {collection} c
                WHERE " . $wherestm .
                " ORDER BY c.name, c.ctime, c.id ASC
                ", $values, $offset, $limit);
        }

        self::add_submission_info($data);
        self::add_framework_urls($data);

        $result = (object) array(
            'count'  => $count,
            'data'   => $data,
            'offset' => $offset,
            'limit'  => $limit,
        );
        return $result;
    }

    private static function add_submission_info(&$data) {
        global $CFG;
        require_once($CFG->docroot . 'lib/group.php');

        if (empty($data)) {
            return;
        }

        $records = get_records_sql_assoc('
            SELECT c.id, c.submittedgroup, c.submittedhost, ' . db_format_tsfield('submittedtime') . ',
                   sg.name AS groupname, sg.urlid, sh.name AS hostname
              FROM {collection} c
         LEFT JOIN {group} sg ON c.submittedgroup = sg.id
         LEFT JOIN {host} sh ON c.submittedhost = sh.wwwroot
             WHERE c.id IN (' . join(',', array_fill(0, count($data), '?')) . ')
               AND (c.submittedgroup IS NOT NULL OR c.submittedhost IS NOT NULL)',
            array_keys($data)
        );

        if (empty($records)) {
            return;
        }

        foreach ($records as $r) {
            if (!empty($r->submittedgroup)) {
                $groupdata = (object) array(
                    'id'    => $r->submittedgroup,
                    'name'  => $r->groupname,
                    'urlid' => $r->urlid,
                    'time'  => $r->submittedtime,
                );
                $groupdata->url = group_homepage_url($groupdata);
                $data[$r->id]->submitinfo = $groupdata;
            }
            else if (!empty($r->submittedhost)) {
                $data[$r->id]->submitinfo = (object) array(
                    'name' => $r->hostname,
                    'url'  => $r->submittedhost,
                    'time'  => $r->submittedtime,
                );
            }
        }
    }

    /**
    * Gets the fields for the new/edit collection form
    * - populates the fields with collection data if it is an edit
    *
    * @param array $collection
    * @return array $elements
    */
    public function get_collectionform_elements() {
        $elements = array(
            'name' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('name', 'collection'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'collection'),
            ),
            'tags'        => array(
                'type'         => 'tags',
                'title'        => get_string('tags'),
                'description'  => get_string('tagsdescprofile'),
                'defaultvalue' => null,
                'help'         => true,
            ),
            'navigation' => array(
                'type'  => 'switchbox',
                'title' => get_string('viewnavigation','collection'),
                'description' => get_string('viewnavigationdesc','collection'),
                'defaultvalue' => 1,
            ),
        );
        if ($frameworks = $this->get_available_frameworks()) {
            $options = array('' => get_string('noframeworkselected', 'module.framework'));
            foreach ($frameworks as $framework) {
                $options[$framework->id] = $framework->name;
            }
            $elements['framework'] = array(
                'type' => 'select',
                'title' => get_string('Framework', 'module.framework'),
                'options' => $options,
                'defaultvalue' => $this->framework,
                'width' => '280px',
                'description' => get_string('frameworkdesc', 'module.framework'),
            );
        }

        // populate the fields with the existing values if any
        if (!empty($this->id)) {
            foreach ($elements as $k => $element) {
                if ($k === 'tags') {
                    $elements[$k]['defaultvalue'] = $this->get_tags();
                }
                else {
                    $elements[$k]['defaultvalue'] = $this->$k;
                }
            }
            $elements['id'] = array(
                'type' => 'hidden',
                'value' => $this->id,
            );
        }
        if (!empty($this->group)) {
            $elements['group'] = array(
                'type' => 'hidden',
                'value' => $this->group,
            );
        }
        else if (!empty($this->institution)) {
            $elements['institution'] = array(
                'type' => 'hidden',
                'value' => $this->institution,
            );
        }
        else if (!empty($this->owner)) {
            $elements['owner'] = array(
                'type' => 'hidden',
                'value' => $this->owner,
            );
        }

        return $elements;
    }

    /**
     * Returns array of views in the current collection
     *
     * @return array views
     */
    public function views() {

        if (!isset($this->views)) {

            $sql = "SELECT v.id, cv.*, v.title, v.owner, v.group, v.institution, v.ownerformat, v.urlid
                FROM {collection_view} cv JOIN {view} v ON cv.view = v.id
                WHERE cv.collection = ?
                ORDER BY cv.displayorder, v.title, v.ctime ASC";

            $result = get_records_sql_assoc($sql, array($this->get('id')));

            if (!empty($result)) {
                require_once('view.php');
                View::get_extra_view_info($result, false, false);
                $result = array_values($result);
                $max = $min = $result[0]['displayorder'];
                foreach ($result as &$r) {
                    $max = max($max, $r['displayorder']);
                    $min = min($min, $r['displayorder']);
                    $r = (object) $r;
                }
                $this->views = array(
                    'views'     => array_values($result),
                    'count'     => count($result),
                    'max'       => $max,
                    'min'       => $min,
                );
            }
            else {
                $this->views = array();
            }

        }

        return $this->views;
    }

    /**
     * Check that a collection can have a framework
     * - The collection is not owned by a group
     * - The framework plugin is active
     * - The institution has 'SmartEvidence' turned on
     * - There frameworks available for the institution
     *
     * @return bool
     */
    public function can_have_framework() {
        if (!empty($this->group)) {
            return false;
        }

        if (!is_plugin_active('framework', 'module')) {
            return false;
        }

        if ($this->institution) {
            $institution = $this->institution;
        }
        else {
            $user = new User();
            $user->find_by_id($this->owner);
            $institutions = array_keys($user->get('institutions'));
            $institution = (!empty($institutions)) ? $institutions[0] : 'mahara';
        }
        $institution = new Institution($institution);
        // Check that smart evidence is enabled for the institution
        if (!$institution->allowinstitutionsmartevidence) {
            return false;
        }
        return true;
    }

    /**
     * Get available frameworks
     *
     * @return array Available frameworks
     */
    public function get_available_frameworks() {
        if (!$this->can_have_framework()) {
            return array();
        }

        if ($this->institution) {
            $institution = $this->institution;
        }
        else {
            $user = new User();
            $user->find_by_id($this->owner);
            $institutions = array_keys($user->get('institutions'));
            $institution = (!empty($institutions)) ? $institutions[0] : 'mahara';
        }
        $institution = new Institution($institution);
        // Check that smart evidence is enabled for the institution
        if (!$institution->allowinstitutionsmartevidence) {
            return false;
        }

        if ($frameworks = Framework::get_frameworks($institution->name, true)) {
            // Inactive frameworks are only allowed if they were added to
            // collection when they were active.
            foreach ($frameworks as $key => $framework) {
                if (empty($framework->active) && $framework->id != $this->framework) {
                    unset ($frameworks[$key]);
                }
            }
            return $frameworks;
        }
        return array();
    }

    /**
     * Check that a collection has a framework
     * - The collection can have a framework
     * - It has a framework id
     * - It has views in the collection
     *
     * @return boolean
     */
    public function has_framework() {
        if (!$this->can_have_framework()) {
            return false;
        }
        if (empty($this->framework)) {
            return false;
        }
        if (!$this->views()) {
            return false;
        }
        if (!is_plugin_active('framework', 'module')) {
            return false;
        }
        return true;
    }

    /**
     * Get collection framework option for collection navigation
     *
     * @return object $option;
     */
    public function collection_nav_framework_option() {
        $option = new StdClass;
        $option->framework = $this->framework;
        $option->id = $this->id;
        $option->title = get_field('framework', 'name', 'id', $this->framework);
        $option->framework = true;

        $option->fullurl = self::get_framework_url($option);

        return $option;
    }

    /**
     * Adding the framework frameworkurl / fullurl to collections
     *
     * @param array  $data    Array of objects
     *
     * @return $data
     */
    public static function add_framework_urls(&$data) {
        if (is_array($data)) {
            foreach ($data as $k => $r) {
                $r->frameworkurl = self::get_framework_url($r, false);
                $r->fullurl = self::get_framework_url($r, true);
            }
        }
    }

    /**
     * Making the framework url
     *
     * @param object $data    Either a collection or standard object
     * @param bool   $fullurl Return full url rather than relative one
     *
     * @return $url
     */
    public static function get_framework_url($data, $fullurl = true) {
        $url = 'module/framework/matrix.php?id=' . $data->id;
        if ($fullurl) {
            return get_config('wwwroot') . $url;
        }
        return $url;
    }

    /**
     * Get the available views the current user can choose to add to their collections.
     * Restrictions on this list include:
     * - currently dashboard, group and profile views are ignored to solve access issues
     * - default pages (with template == 2) are ignored
     * - each view can only belong to one collection
     * - locked/submitted views can't be added to collections
     *
     * @return array $views
     */
    public static function available_views($owner=null, $groupid=null, $institutionname=null) {
        if (!empty($groupid)) {
            $wherestm = '"group" = ?';
            $values = array($groupid);
        }
        else if (!empty($institutionname)) {
            $wherestm = 'institution = ?';
            $values = array($institutionname);
        }
        else if (!empty($owner)) {
            $wherestm = 'owner = ?';
            $values = array($owner);
        }
        else {
            return array();
        }
        ($views = get_records_sql_array("SELECT v.id, v.title
            FROM {view} v
            LEFT JOIN {collection_view} cv ON cv.view = v.id
            WHERE " . $wherestm .
            "   AND cv.view IS NULL
                AND v.type NOT IN ('dashboard','grouphomepage','profile')
                AND v.template != 2
                AND v.submittedgroup IS NULL
                AND v.submittedhost IS NULL
            GROUP BY v.id, v.title
            ORDER BY v.title ASC
            ", $values))
            || ($views = array());
        return $views;
    }

    /**
     * Submits the selected views to the collection
     *
     * @param array values selected views
     * @return integer count so we know what SESSION message to display
     */
    public function add_views($values) {
        require_once(get_config('libroot') . 'view.php');

        $count = 0; // how many views we are adding
        db_begin();

        // each view was marked with a key of view_<id> in order to identify the correct items
        // from the form values
        foreach ($values as $key => $value) {
            if (substr($key,0,5) === 'view_' AND $value == true) {
                $cv = array();
                $cv['view'] = substr($key,5);
                $cv['collection'] = $this->get('id');

                // set displayorder value
                $max = get_field('collection_view', 'MAX(displayorder)', 'collection', $this->get('id'));
                $cv['displayorder'] = is_numeric($max) ? $max + 1 : 0;

                insert_record('collection_view', (object)$cv);
                $count++;
            }
        }

        $viewids = get_column('collection_view', 'view', 'collection', $this->id);

        // Set the most permissive access records on all views
        View::combine_access($viewids, true);

        // Copy the whole view config from the first view to all the others
        if (count($viewids)) {
            $firstview = new View($viewids[0]);
            $viewconfig = array(
                'startdate'       => $firstview->get('startdate'),
                'stopdate'        => $firstview->get('stopdate'),
                'template'        => $firstview->get('template'),
                'retainview'      => $firstview->get('retainview'),
                'allowcomments'   => $firstview->get('allowcomments'),
                'approvecomments' => (int) ($firstview->get('allowcomments') && $firstview->get('approvecomments')),
                'accesslist'      => $firstview->get_access(),
            );
            View::update_view_access($viewconfig, $viewids);
        }

        db_commit();

        return $count;
    }

    /**
     * Removes the selected views from the collection
     *
     * @param integer $view the view to remove
     */
    public function remove_view($view) {
        db_begin();
        delete_records('collection_view','view',$view,'collection',$this->get('id'));

        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        delete_records_select('view_access', 'view = ? AND token IS NOT NULL', array($view));

        db_commit();
    }

    /**
     * Sets the displayorder for a view
     *
     * @param integer   $id   view id
     * @param mixed  direction - either string consisting 'up' or 'down' to
     *               indicate which way to move $id item, or an array containing
     *               the ids in order you want them saved
     */
    public function set_viewdisplayorder($id, $direction) {
        if (is_array($direction)) {
            // we already have new sort order
            $neworder = $direction;
        }
        else {
            $ids = get_column_sql('
                SELECT view FROM {collection_view}
                WHERE collection = ?
                ORDER BY displayorder', array($this->get('id')));

            foreach ($ids as $k => $v) {
                if ($v == $id) {
                    $oldorder = $k;
                    break;
                }
            }

            if ($direction == 'up' && $oldorder > 0) {
                $neworder = array_merge(array_slice($ids, 0, $oldorder - 1),
                                        array($id, $ids[$oldorder-1]),
                                        array_slice($ids, $oldorder+1));
            }
            else if ($direction == 'down' && ($oldorder + 1 < count($ids))) {
                $neworder = array_merge(array_slice($ids, 0, $oldorder),
                                        array($ids[$oldorder+1], $id),
                                        array_slice($ids, $oldorder+2));
            }
        }
        if (isset($neworder)) {
            foreach ($neworder as $k => $v) {
                set_field('collection_view', 'displayorder', $k, 'view', $v, 'collection',$this->get('id'));
            }
            $this->set('mtime', time());
            $this->commit();
        }
    }

    /**
     * after editing the collection, redirect back to the appropriate place
     */
    public function post_edit_redirect($new=false, $copy=false, $urlparams=null) {
        if ($new || $copy) {
            $urlparams['id'] = $this->get('id');
            $redirecturl = '/collection/views.php';
        }
        else {
            $redirecturl = '/collection/index.php';
        }
        if ($urlparams) {
            $redirecturl .= '?' . http_build_query($urlparams);
        }
        redirect($redirecturl);
    }

    public static function search_by_view_id($viewid) {
        $record = get_record_sql('
            SELECT c.*
            FROM {collection} c JOIN {collection_view} cv ON c.id = cv.collection
            WHERE cv.view = ?',
            array($viewid)
        );
        if ($record) {
            return new Collection(0, $record);
        }
        return false;
    }

    /**
     * Returns an SQL snippet that can be used in a where clause to get views
     * with the given owner.
     *
     * @param object $ownerobj An object that has view ownership information -
     *                         either the institution, group or owner fields set
     * @return string
     */
    private static function owner_sql($ownerobj) {
        if (isset($ownerobj->institution)) {
            return 'institution = ' . db_quote($ownerobj->institution);
        }
        if (isset($ownerobj->group) && is_numeric($ownerobj->group)) {
            return '"group" = ' . (int)$ownerobj->group;
        }
        if (isset($ownerobj->owner) && is_numeric($ownerobj->owner)) {
            return 'owner = ' . (int)$ownerobj->owner;
        }
        throw new SystemException("View::owner_sql: Passed object did not have an institution, group or owner field");
    }

    /**
     * Makes a URL for a collection
     *
     * @param bool $full return a full url
     * @param bool $useid ignore clean url settings and always return a url with an id in it
     *
     * @return string
     */
    public function get_url($full=true, $useid=false, &$firstview=null) {
        global $USER;
        $firstview = null;

        $views = $this->views();
        if (!empty($views)) {
            if ($this->framework) {
                if ($full) {
                    $this->fullurl = Collection::get_framework_url($this);
                    return $this->fullurl;
                }
                else {
                    $this->frameworkurl = Collection::get_framework_url($this, false);
                    return $this->frameworkurl;
                }
            }

            $v = new View(0, $views['views'][0]);
            $v->set('dirty', false);
            $firstview = $v;
            return $v->get_url($full, $useid);
        }

        log_warn("Attempting to get url for an empty collection");

        if ($this->owner === $USER->get('id')) {
            $url = 'collection/views.php?id=' . $this->id;
        }
        else {
            $url = '';
        }

        if ($full) {
            $url = get_config('wwwroot') . $url;
        }

        return $url;
    }

    /**
     * Sets released submissions to pending release status and adds
     * the submission item to the export queue ready for archiving.
     *
     * @param object $releaseuser The user releasing the collection
     */
    public function pendingrelease($releaseuser=null) {
        $submitinfo = $this->submitted_to();
        if (!$this->is_submitted()) {
            throw new ParameterException("Collection with id " . $this->id . " has not been submitted");
        }
        $viewids = $this->get_viewids();
        db_begin();
        execute_sql("UPDATE {collection}
                     SET submittedstatus = " . self::PENDING_RELEASE . "
                     WHERE id = ?",
                     array($this->id)
        );
        View::_db_pendingrelease($viewids);
        db_commit();

        require_once(get_config('docroot') . 'export/lib.php');
        add_submission_to_export_queue($this, $releaseuser);
    }

    /**
     * Release a submitted collection
     *
     * @param object $releaseuser The user releasing the collection
     */
    public function release($releaseuser=null) {

        if (!$this->is_submitted()) {
            throw new ParameterException("Collection with id " . $this->id . " has not been submitted");
        }

        // One day there might be group and institution collections, so be safe
        if (empty($this->owner)) {
            throw new ParameterException("Collection with id " . $this->id . " has no owner");
        }

        $viewids = $this->get_viewids();

        db_begin();
        execute_sql('
            UPDATE {collection}
            SET submittedgroup = NULL,
                submittedhost = NULL,
                submittedtime = NULL,
                submittedstatus = ' . self::UNSUBMITTED . '
            WHERE id = ?',
            array($this->id)
        );
        View::_db_release($viewids, $this->owner, $this->submittedgroup);
        db_commit();

        // We don't send out notifications about the release of remote-submitted Views & Collections
        // (though I'm not sure why)
        if ($this->submittedgroup) {
            $releaseuser = optional_userobj($releaseuser);
            $releaseuserdisplay = display_name($releaseuser, $this->owner);
            $submitinfo = $this->submitted_to();

            require_once('activity.php');
            activity_occurred(
                'maharamessage',
                array(
                    'users' => array($this->get('owner')),
                    'strings' => (object) array(
                        'subject' => (object) array(
                            'key'     => 'collectionreleasedsubject',
                            'section' => 'group',
                            'args'    => array($this->name, $submitinfo->name, $releaseuserdisplay),
                        ),
                        'message' => (object) array(
                            'key'     => 'collectionreleasedmessage',
                            'section' => 'group',
                            'args'    => array($this->name, $submitinfo->name, $releaseuserdisplay),
                        ),
                    ),
                    'url' => $this->get_url(false),
                    'urltext' => $this->name,
                )
            );
        }
    }

    public function get_viewids() {
        $ids = array();
        $viewdata = $this->views();

        if (!empty($viewdata['views'])) {
            foreach ($viewdata['views'] as $v) {
                $ids[] = $v->id;
            }
        }

        return $ids;
    }

    public function is_submitted() {
        return $this->submittedgroup || $this->submittedhost;
    }

    public function submitted_to() {
        if ($this->submittedgroup) {
            $record = get_record('group', 'id', $this->submittedgroup, null, null, null, null, 'id, name, urlid');
            $record->url = group_homepage_url($record);
        }
        else if ($this->submittedhost) {
            $record = get_record('host', 'wwwroot', $this->submittedhost, null, null, null, null, 'wwwroot, name');
            $record->url = $record->wwwroot;
        }
        else {
            throw new SystemException("Collection with id " . $this->id . " has not been submitted");
        }

        return $record;
    }

    /**
     * Submit this collection to a group or a remote host (but only one or the other!)
     * @param object $group
     * @param string $submittedhost
     * @param int $owner The owner of the collection (if not just $USER)
     * @throws SystemException
     */
    public function submit($group = null, $submittedhost = null, $owner = null) {
        global $USER;

        if ($this->is_submitted()) {
            throw new CollectionSubmissionException(get_string('collectionalreadysubmitted', 'view'));
        }
        // Gotta provide one or the other
        if (!$group && !$submittedhost) {
            return false;
        }

        $viewids = $this->get_viewids();
        if (!$viewids) {
            throw new CollectionSubmissionException(get_string('cantsubmitemptycollection', 'view'));
        }
        $idstr = join(',', array_map('intval', $viewids));
        $owner = ($owner == null) ? $USER->get('id') : $owner;

        // Check that none of the views is submitted to some other group.  This is bound to happen to someone,
        // because collection submission is being introduced at a time when it is still possible to submit
        // individual views in a collection.
        $sql = "SELECT title FROM {view} WHERE id IN ({$idstr}) AND (submittedhost IS NOT NULL OR (submittedgroup IS NOT NULL";
        $params = array();
        // To ease the transition, if you've submitted one page of the collection to this group already, you
        // can submit the rest as well
        if ($group) {
            $sql .= ' AND submittedgroup != ?';
            $params[] = $group->id;
        }
        $sql .= '))';
        $submittedtitles = get_column_sql($sql, $params );

        if (!empty($submittedtitles)) {
            throw new CollectionSubmissionException(get_string('collectionviewsalreadysubmitted', 'view', implode('", "', $submittedtitles)));
        }

        if ($group) {
            $group->roles = get_column('grouptype_roles', 'role', 'grouptype', $group->grouptype, 'see_submitted_views', 1);
        }

        db_begin();
        View::_db_submit($viewids, $group, $submittedhost, $owner);
        if ($group) {
            $this->set('submittedgroup', $group->id);
            $this->set('submittedhost', null);
        }
        else {
            $this->set('submittedgroup', null);
            $this->set('submittedhost', $submittedhost);
        }
        $this->set('submittedtime', time());
        $this->set('submittedstatus', self::SUBMITTED);
        $this->commit();
        db_commit();

        if ($group) {
            activity_occurred(
                'groupmessage',
                array(
                    'group'         => $group->id,
                    'roles'         => $group->roles,
                    'url'           => $this->get_url(false),
                    'strings'       => (object) array(
                        'urltext' => (object) array(
                            'key'     => 'Collection',
                            'section' => 'collection',
                        ),
                        'subject' => (object) array(
                            'key'     => 'viewsubmittedsubject1',
                            'section' => 'activity',
                            'args'    => array($group->name),
                        ),
                        'message' => (object) array(
                            'key'     => 'viewsubmittedmessage1',
                            'section' => 'activity',
                            'args'    => array(
                                display_name($USER, null, false, true),
                                $this->name,
                                $group->name,
                            ),
                        ),
                    ),
                )
            );
        }
    }

    /**
     * Returns the collection tags
     *
     * @return mixed
     */
    public function get_tags() {
        if (!isset($this->tags)) {
            $this->tags = get_column('collection_tag', 'tag', 'collection', $this->get('id'));
        }
        return $this->tags;
    }

    /**
     * Creates a new secret url for this collection
     * @param int $collectionid
     * @param false $visible
     * @return object The view_access record for the first view's secret URL
     */
    public function new_token($visible=1) {
        $viewids = $this->get_viewids();
        // It's not possible to add a secret key to a collection with no pages
        if (!$viewids) {
            return false;
        }

        reset($viewids);
        $access = View::new_token(current($viewids), $visible);
        while (next($viewids)) {
            $todb = new stdClass();
            $todb->view = current($viewids);
            $todb->visible = $access->visible;
            $todb->token = $access->token;
            $todb->ctime = $access->ctime;
            insert_record('view_access', $todb);
        }

        return $access;
    }

    /**
     * Retrieves the collection's invisible access token, if it has one. (Each
     * collection can only have one, because invisible access tokens are used
     * for submission access, and each collection can only be submitted to
     * one place at a time.)
     *
     * @return mixed boolean FALSE if there is no token, a data object if there is
     */
    public function get_invisible_token() {
        $viewids = $this->get_viewids();
        if (!$viewids) {
            return false;
        }
        reset($viewids);
        return View::get_invisible_token(current($viewids));
    }
}

class CollectionSubmissionException extends UserException {

    // For a CollectionSubmissionException, the error message is mandatory
    public function __construct($message) {
        parent::__construct($message);
    }

    public function strings() {
        return array_merge(
            parent::strings(),
            array(
                'title' => get_string('collectionsubmissionexceptiontitle', 'view'),
                'message' => get_string('collectionsubmissionexceptionmessage', 'view'),
            )
        );
    }
}
