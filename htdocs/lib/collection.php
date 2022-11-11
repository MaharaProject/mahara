<?php
/**
 * The portfolio collection class
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Collection class for working with portfolio collection objects
 */
class Collection {

    /**
     * The unique ID of the collection
     * @var integer
     */
    private $id;

    /**
     * Name (title) of the collection
     * @var string
     */
    private $name;

    /**
     * Short description to mention what the collection is for
     * @var string
     */
    private $description;

    /**
     * The user id if the collection is owned by a person
     * @var integer|null
     */
    private $owner;

    /**
     * The group id if the collection is owned by a group
     * @var integer|null
     */
    private $group;

    /**
     * The institution name (short name) if the collection is owned by an institution
     * @var string|null
     */
    private $institution;

    /**
     * Unix timestamp of collection last modification
     * @var integer
     */
    private $mtime;

    /**
     * Unix timestamp of collection creation
     * @var integer
     */
    private $ctime;

    /**
     * Whether to show navigation bar or not
     * @var boolean
     */
    private $navigation;

    /**
     * The group ID if the collection is submitted to a group
     * @var integer|null
     */
    private $submittedgroup;

    /**
     * The host URL if the collection is submitted to an external host
     * @var string|null
     */
    private $submittedhost;

    /**
     * Unix timestamp of submission
     * @var string|null
     */
    private $submittedtime;

    /**
     * The current status of the collection in the submission process
     * Where 0 = not submitted, 1 = submitted, 2 = pending submission release
     * @var integer
     */
    private $submittedstatus;

    /**
     * An array of view objects that are associated with this collection
     * Initialise via $this->views();
     * @var array
     */
    private $views;

    /**
     * An array of tags that are associated with this collection
     * Initialise via $this->get_tags();
     * @var array
     */
    private $tags;

    /**
     * ID of the framework used with this collection
     * @var integer|null
     */
    private $framework;

    /**
     * The artefact ID of the image being used as the cover image
     * Initialise via $this->get_coverimage();
     * @var integer|null
     */
    private $coverimage;

    /**
     * Whether the collection needs to show a progress completion page
     * @var boolean
     */
    private $progresscompletion;

    /**
     * Whether the collection is locked for editing
     * @var boolean
     */
    private $lock;

    /**
     * Whether the collection can get automatically copied to people
     * @var boolean
     */
    private $autocopytemplate;

    /**
     * Whether the collection is a template
     * @var boolean
     */
    private $template;

    /**
     * @var string
     */
    private $fullurl;

    /**
     * @var string
     */
    private $progresscompletionurl;

    /**
     * @var string
     */
    private $frameworkurl;

    /**
     * The collection this collection is a copy of.
     * @var int
     */
    private $submissionoriginal = 0;

    /**
     * @var boolean
     */
    private $outcomeportfolio;

    /**
     * @var integer
     */
    private $outcomecategory;


    const UNSUBMITTED = 0;
    const SUBMITTED = 1;
    const PENDING_RELEASE = 2;

    /**
     * Collection constructor takes either an ID number or an array of data to initialise it with
     *
     * @param integer $id
     * @param array $data
     *
     */
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

    /**
     * Helper method to return value of private variables
     * @param string $field  Name of the property
     * @return mixed Value of the property
     */
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
        if ($field == 'coverimage') {
            return $this->get_coverimage();
        }
        return $this->{$field};
    }

    /**
     * Helper method to set value of private variables
     * @param string $field  Name of the property
     * @param mixed $value   New value of the property
     * @throws InvalidArgumentException  If the $field is not a property of the class
     * @return true
     */
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
     * @return Collection           The newly created/updated collection object
     */
    public static function save($data) {
        if (array_key_exists('id', $data)) {
            $id = $data['id'];
            $state = 'updatecollection';
        }
        else {
            $id = 0;
            $state = 'createcollection';
        }
        $collection = new Collection($id, $data);
        $collection->set('mtime', time());
        $collection->commit();
        $views = $collection->get('views');
        $viewids = array();
        if (!empty($views)) {
            foreach ($views['views'] as $view) {
                $viewids[] = $view->view;
            }
        }
        $eventdata = array('id' => $collection->get('id'),
                           'name' => $collection->get('name'),
                           'eventfor' => 'collection',
                           'viewids' => $viewids);
        handle_event($state, $eventdata);
        return $collection; // return newly created Collections id
    }

    /**
     * Deletes a Collection
     *
     */
    public function delete($deleteviews = false) {
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
        // Delete the progress page as it can't exist outside a collection
        // We can't use has_progresscompletion() check when the collection is empty of normal pages
        // So we just check directly in database
        if (!$deleteviews && $viewids && $pid = get_field_sql("SELECT cv.view FROM {collection_view} cv
                                                               JOIN {view} v ON v.id = cv.view
                                                               WHERE v.id IN (" . join(',', array_fill(0, count($viewids), '?')) . ")
                                                               AND v.type = 'progress'", $viewids)) {
            require_once(get_config('libroot') . 'view.php');
            $view = new View($pid);
            $view->delete();
        }
        delete_records('collection_view','collection', $this->id);
        delete_records('tag', 'resourcetype', 'collection', 'resourceid', $this->id);
        if (is_plugin_installed('lti', 'module')) {
            delete_records('lti_assessment_submission', 'collectionid', $this->id);
        }
        delete_records('existingcopy', 'collection', $this->id);
        delete_records('collection_template', 'collection', $this->id);
        delete_records('view_copy_queue', 'collection', $this->id);
        if (is_plugin_installed('assessmentreport', 'module')) {
            // Delete any submission history
            delete_records('module_assessmentreport_history', 'event', 'collection', 'itemid', $this->id);
        }
        if (is_plugin_installed('submissions', 'module')) {
            $submissionids = get_column('module_submissions', 'id', 'portfolioelementtype', 'collection', 'portfolioelementid', $this->id);
            if ($submissionids) {
                execute_sql("DELETE FROM {module_submissions_evaluation} WHERE submissionid IN (" . join(',', $submissionids) . ")");
                execute_sql("DELETE FROM {module_submissions} WHERE id IN (" . join(',', $submissionids) . ")");
            }
        }
        if (db_table_exists('outcome')) {
            if ($outcomes = get_column('outcome', 'id', 'collection', $this->id)) {
                foreach ($outcomes as $outcomeid) {
                    delete_records('outcome_view_activity', 'outcome', $outcomeid);
                }
                delete_records('outcome', 'collection', $this->id);
            }
        }
        delete_records('collection', 'id', $this->id);
        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        if ($viewids) {
            delete_records_select('view_access', 'view IN (' . join(',', $viewids) . ') AND token IS NOT NULL');
        }
        // Delete the views that were in the collection if required
        if ($deleteviews) {
            require_once('view.php');
            foreach ($viewids as $viewid) {
                $view = new View($viewid);
                $view->delete();
            }
        }
        $data = array('id' => $this->id,
                      'name' => $this->name,
                      'eventfor' => 'collection',
                      'viewids' => $viewids);
        handle_event('deletecollection', $data);
        db_commit();
    }

    /**
     * This method updates the contents of the collection table only.
     */
    public function commit() {
        global $USER;

        $fordb = new stdClass();
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
            if ($this->group) {
                $ownertype = 'group';
                $ownerid = $this->group;
            }
            else if ($this->institution) {
                $ownertype = 'institution';
                $ownerid = $this->institution;
            }
            else {
                $ownertype = 'user';
                $ownerid = $this->owner;
            }
            delete_records('tag', 'resourcetype', 'collection', 'resourceid', $this->get('id'));
            $tags = check_case_sensitive($this->get_tags(), 'tag');
            foreach (array_unique($tags) as $tag) {
                //truncate the tag before insert it into the database
                $tag = substr($tag, 0, 128);
                $tag = check_if_institution_tag($tag);
                insert_record('tag',
                    (object)array(
                        'resourcetype' => 'collection',
                        'resourceid' => $this->get('id'),
                        'ownertype' => $ownertype,
                        'ownerid' => $ownerid,
                        'tag' => $tag,
                        'ctime' => db_format_timestamp(time()),
                        'editedby' => $USER->get('id'),
                    )
                );
            }
        }

        db_commit();
    }

    /**
     * Generates a name for a newly created Collection
     *
     * Takes a supplied name and returns a unique one for this person/group/institution
     *
     * @param string $name  The supplied name
     * @param object $ownerdata Object containing information on ownership
     * @return string Unique name
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
     * Copy the cover image of the collection template
     *
     * @param Collection $template the collection template
     * @param array $collectiondata contains data about owner, group, institution of the copy
     * @return int new image artefact id
     */
    private static function copy_setting_coverimage(Collection $template, $collectiondata) {
        safe_require('artefact', 'file');
        $coverimageid = $template->get('coverimage');
        $owner = isset($collectiondata['owner']) ? $collectiondata['owner'] : null;
        $group = isset($collectiondata['group']) ? $collectiondata['group'] : null;
        $institution = isset($collectiondata['institution']) ? $collectiondata['institution'] : null;

        // if the owner of the template and the copy are the same, use the same file
        $sameowner = ($template->get('owner') && $template->get('owner') == $owner) ||
            ($template->get('group') && $template->get('group') == $group) ||
            ($template->get('institution') && $template->get('institution') == $institution);
        if ($sameowner) return $coverimageid;

        if ($coverimageid) {
            try {
                $a = artefact_instance_from_id($coverimageid);
                if ($a instanceof ArtefactTypeImage) {
                    $newid = $a->copy_for_new_owner(
                      $owner,
                      $group,
                      $institution
                    );

                    // move to cover image folder
                    $userobj = null;
                    if ($owner) {
                        $userobj = new User();
                        $userobj->find_by_id($owner);
                    }
                    $newa = artefact_instance_from_id($newid);
                    $folderid = ArtefactTypeImage::get_coverimage_folder($userobj, $group, $institution);
                    $newa->move($folderid);
                    return $newid;
                }
                return null;
            }
            catch (Exception $e) {
                return null;
            }
        }
        return null;
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
     * @param boolean $titlefromtemplate  Title of new collection or view will be exactly copied from the template
     * @param boolean $trackoriginal  Connect this copy to the original template it was copied from
     *
     * @return array A list consisting of the new collection, the template collection and
     *               information about the copy - i.e. how many blocks and
     *               artefacts were copied
     * @throws SystemException under various circumstances, see the source for
     *                         more information
     */
    public static function create_from_template($collectiondata, $templateid, $userid=null, $checkaccess=true, $titlefromtemplate=false, $trackoriginal=false) {
        require_once(get_config('libroot') . 'view.php');
        global $SESSION;

        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        db_begin();

        $colltemplate = new Collection($templateid);
        $issubmission = false;
        if (isset($collectiondata['submissionoriginalcollection'])) {
            $issubmission = ($collectiondata['submissionoriginalcollection'] > 0);
        }

        $data = new stdClass();
        // Set a default name if one wasn't set in $collectiondata
        if ($colltemplate->get('template')) {
            $user = new User();
            if (isset($collectiondata['owner'])) {
                $user->find_by_id($collectiondata['owner']);
            }
            else {
                $user->find_by_id($userid);
            }

            $username = display_name($user, null, true);
            $data->name = $username . ' ' . $colltemplate->get('name');

        }
        else if ($titlefromtemplate) {
            $data->name = $colltemplate->get('name');
        }
        else if (!isset($collectiondata['name'])) {
            $desiredname = $colltemplate->get('name');
            if (!$issubmission) {
                // This is not a submission, so we should tweak the name to
                // show that it's a copy.
                if (get_config('renamecopies')) {
                    $desiredname = get_string('Copyof', 'mahara', $desiredname);
                }
                $desiredname = self::new_name($desiredname, (object)$collectiondata);
            }
            $data->name = $desiredname;
        }
        else {
            $data->name = $collectiondata['name'];
        }
        $data->description = $colltemplate->get('description');
        $data->coverimage = self::copy_setting_coverimage($colltemplate, $collectiondata);
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

        // Copy the Smart Evidence Framework and Progress Completion settings.
        $data->framework = $colltemplate->get('framework');
        $data->progresscompletion = $colltemplate->get('progresscompletion');

        $data->submittedstatus = 0;
        $data->submissionoriginal = $issubmission ? $collectiondata['submissionoriginalcollection'] : 0;
        $data->outcomeportfolio = $colltemplate->get('outcomeportfolio');
        // If owner is copying a collection they own then the copy is made unlocked
        $data->lock = (isset($data->owner) && $data->owner == $colltemplate->owner) ? 0 : $colltemplate->get('lock');
        $data->autocopytemplate = 0;
        $data->template = 0;
        $collection = self::save((array)$data);
        if ($trackoriginal) {
            $collection->track_template($templateid);
        }

        $numcopied = array('pages' => 0, 'blocks' => 0, 'artefacts' => 0);

        $views = $colltemplate->get('views');
        if (empty($views)) {
            $views['views'] = array();
        }
        $copyviews = array();
        $evidenceviews = array();
        $artefactcopies = array();
        foreach ($views['views'] as $v) {
            $values = array(
                'new' => true,
                'owner' => isset($data->owner) ? $data->owner : null,
                'group' => isset($data->group) ? $data->group : null,
                'institution' => isset($data->institution) ? $data->institution : null,
                'usetemplate' => $v->view,
                'quiet_update' => 1,
            );
            if ($v->skin) {
                // Keep the skin on the copy if person is allowed to use that skin
                require_once('skin.php');
                $skin = new Skin($v->skin);
                if ($skin->can_use()) {
                    $values['skin'] = $v->skin;
                }
            }

            if ($issubmission) {
                $values['submissionoriginal'] = $v->id;
                $values['submissionoriginalcollection'] = $collectiondata['submissionoriginalcollection'];
            }

            list($view, $template, $copystatus) = View::create_from_template($values, $v->view, $userid, $checkaccess, $titlefromtemplate, $artefactcopies);
            if ($issubmission) {
                $view->copy_signoff_status($v->view);
            }
            // Check to see if we need to re-map any framework evidence
            // and if a personal collection will be copied as another personal collection (copying to groups/institutions/site porttfolios will have no owner field)
            if (!empty($data->owner)) {
                if (!empty($data->framework) && $userid == $data->owner && count_records('framework_evidence', 'view', $v->view)) {
                    $evidenceviews[$v->view] = $view->get('id');
                }
            }
            if (isset($copystatus['quotaexceeded'])) {
                $SESSION->clear('messages');
                return array(null, $colltemplate, array('quotaexceeded' => true));
            }
            $copyviews['view_' . $view->get('id')] = true;
            $numcopied['blocks'] += $copystatus['blocks'];
            $numcopied['artefacts'] += $copystatus['artefacts'];
            $collection_view_ids[] = $view->get('id');
        }
        $numcopied['pages'] = count($views['views']);

        $collection->add_views($copyviews);

        // Prep sending of notifications
        if (!empty($collection_view_ids)) {
            $accessdata = new stdClass();
            $accessdata->view = $collection_view_ids[0];
            // In the process of creating collections from templates, we notify users once
            // the views are created by setting a quiet_update flag on each view-creation
            $beforeusers[$userid] = get_record('usr', 'id', $userid);

            // Don't send an activity notification to the person sharing the view
            $accessdata->oldusers = $beforeusers;
            $firstview = new View($collection_view_ids[0]);
            $dataviews[] = array('id' => $firstview->get('id'),
                                'title' => $firstview->get('title'),
                                'collection_id' => $collection->get('id'),
                                'collection_name' => $collection->get('name'),
                                'collection_url' => $collection->get_url(),
                            );
            $accessdata->views = $dataviews;
            activity_occurred('viewaccess', $accessdata);
        }

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
        $evidences = array();
        if (!empty($evidenceviews) && !$issubmission) {
            // We need to get how the old views/artefacts/blocks/evidence fit together
            if (!empty($artefactcopies)) {
                $evidences = get_records_sql_array('
                    SELECT va.view, va.artefact, va.block, a.artefacttype, fe.*
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

                // Get how the new views/artefacts/blocks fit together
                $newblocks = get_records_sql_assoc('
                        SELECT va.artefact, va.view, va.block
                        FROM {view} v
                        JOIN {view_artefact} va ON va.view = v.id
                        JOIN {artefact} a ON a.id = va.artefact
                        WHERE v.id IN (' . join(',', array_values($evidenceviews)) . ')
                        AND a.id IN (' . join(',', array_keys($newartefactcopies)) . ')
                        AND artefacttype = ?', array('annotation'));

                // annotation artefacts and collective file artefacts too
                if (!empty($evidences)) {
                    foreach ($evidences as $evidence) {
                        if (
                            key_exists($evidence->artefact, $artefactcopies)
                            && key_exists($artefactcopies[$evidence->artefact]->newid, $newartefactcopies)
                        ) {
                            if ($evidence->artefacttype == 'annotation') {
                                $newartefact = $artefactcopies[$evidence->artefact]->newid;
                                // Not all the new artefacts are blocks.
                                if (!empty($newblocks[$newartefact])) {

                                    $newevidence = new stdClass();
                                    $newevidence->artefact = $newartefact;
                                    $newevidence->annotation = $newblocks[$newartefact]->block;
                                    $newevidence->framework = $evidence->framework;
                                    $newevidence->element = $evidence->element;
                                    $newevidence->view = $newblocks[$newartefact]->view;
                                    $newevidence->state = 0;
                                    $newevidence->reviewer = null;
                                    $newevidence->ctime = $evidence->ctime;
                                    $newevidence->mtime = $evidence->mtime;
                                    // Add the new annotation artefact/block - we don't need to copy files as
                                    // they'll still be owned by the same person
                                    insert_record('framework_evidence', $newevidence);
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($colltemplate->has_progresscompletion() && !$issubmission) {
            $values['type'] = 'progress';
            list($view, $template, $copystatus) = View::create_from_template($values, $colltemplate->has_progresscompletion(), $userid, false, false, $artefactcopies);
            $numcopied['blocks'] += $copystatus['blocks'];
            $numcopied['artefacts'] += $copystatus['artefacts'];

            // Update any existing pages sortorder
            execute_sql("UPDATE {collection_view} SET displayorder = displayorder + 1 WHERE collection = ?", array($collection->id));
            // Add progress page as first page of collection
            $id_of_oldfirstview = $collection->first_view()->get('id');
            $cv = array();
            $cv['view'] = $view->get('id');
            $cv['collection'] = $collection->id;
            $cv['displayorder'] = 0;
            $sql = "DELETE FROM {activity_queue} WHERE type = 4 AND data LIKE ? ";
            execute_sql($sql,  array('%"view";%"' . $id_of_oldfirstview . '"%'));
            insert_record('collection_view', (object)$cv);
            if (!empty($collection->views())) {
                $collection->add_views(array($view->get('id')));
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
     * @param integer $offset current page to display
     * @param integer $limit how many collections to display per page
     * @param integer $owner current person ID
     * @param integer $groupid current group ID
     * @param string  $institutionname current institution name
     * @return array (count: integer, data: array, offset: integer, limit: integer)
     */
    public static function get_mycollections_data($offset=0, $limit=10, $owner=null, $groupid=null, $institutionname=null) {
        $wherestm = '';
        $values = array();
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

    /**
     * Add any submission info to the array of collections
     * @param array &$data modified parameter array of collections
     */
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
        safe_require('artefact', 'file');
        global $USER;
        $folder = ArtefactTypeImage::get_coverimage_folder($USER, $this->group, $this->institution);

        $highlight = array(0);
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
                'institution'  =>  $this->institution,
            ),
            'navigation' => array(
                'type'  => 'switchbox',
                'title' => get_string('viewnavigation','collection'),
                'description' => get_string('viewnavigationdesc','collection'),
                'defaultvalue' => 1,
            ),
            'coverimage' => array(
                'type'         => 'filebrowser',
                'title'        => get_string('coverimage', 'view'),
                'description'  => get_string('coverimagedescription', 'view'),
                'folder'       => $folder,
                'highlight'    => $highlight,
                'accept'       => 'image/*',
                'institution'  => $this->institution,
                'group'        => $this->group,
                'page'         => '',
                'filters'      => array(
                     'artefacttype' => array('image'),
                ),
                'config'       => array(
                    'upload'          => true,
                    'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                    'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                    'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                    'createfolder'    => false,
                    'edit'            => false,
                    'select'          => true,
                    'selectone'       => true,
                ),
                'selectlistcallback' => 'artefact_get_records_by_id',
                'selectcallback'     => 'add_view_coverimage',
                'unselectcallback'   => 'delete_view_coverimage',
            ),
        );
        if ($this->group && is_outcomes_group($this->group)) {
            $institution = get_field('group', 'institution', 'id', $this->group);
            $categories = get_records_select_array('outcome_category',  "institution = ?", array($institution));
            $elements['outcomeportfolio'] = array(
                'type'  => 'switchbox',
                'title' => get_string('outcomeportfolio', 'collection'),
                'description' => get_string('outcomeportfoliodesc', 'collection'),
                'defaultvalue' => empty($categories) ? 0 : 1,
                'disabled' => empty($categories),
            );
            $options = [];
            if ($categories) {
                foreach($categories as $cat) {
                    $options[$cat->id] = $cat->title;
                }
                $elements['outcomecategory'] = array(
                    'type'  => 'select',
                    'title' => get_string('outcomecategory','collection'),
                    'description' => get_string('outcomecategorydesc','collection'),
                    'options' => $options,
                    'collapseifoneoption' => true,
                    'defaultvalue' => null,
                );
            }
            else {
                $elements['outcomecategory_html'] = array(
                    'type' => 'html',
                    'value' => get_string('outcomecategorymissing', 'collection', $institution),
                );
            }
        }
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

        if ($this->can_have_progresscompletion()) {
            $elements['progresscompletion'] = array(
                'type'  => 'switchbox',
                'title' => get_string('progresscompletion', 'admin'),
                'description' => get_string('progresscompletiondesc', 'collection'),
                'defaultvalue' => 0,
            );
        }
        if (isset($this->institution) && $this->institution) {
            $elements['template'] = array(
                'type'  => 'switchbox',
                'title' => get_string('template', 'collection'),
                'description' => get_string('templatedesc', 'collection'),
                'defaultvalue' => 0,
            );
            $elements['autocopytemplate'] = array(
                'type'  => 'switchbox',
                'title' => get_string('autocopytemplate', 'collection'),
                'description' => get_string('autocopytemplatedesc', 'collection'),
                'defaultvalue' => 0,
            );
        }
        $submissionorigin = $this->get_submission_origin();
        if ($submissionorigin !== false) {
            if ($submissionorigin != 0) {
                $original = new Collection($submissionorigin);
                $title = $original->get('name');
                $url = $original->get_url();
                $value = get_string('linktosubmissionoriginallink', 'collection', $url, $title);
                $description = get_string('linktosubmissionoriginaldescription', 'collection');
            }
            else {
                // The original has been deleted.
                $value = get_string('linktosubmissionoriginaldeleted', 'collection');
                $description = get_string('linktosubmissionoriginaldeleteddescription', 'collection');
            }
            $elements['linktosourceportfolio'] = array(
                'type'  => 'html',
                'title' => get_string('linktosubmissionoriginaltitle', 'collection'),
                'value' => $value,
                'description' => $description,
            );
            $elements['linkedtosourceportfolio'] = array(
                'type'         => 'switchbox',
                'title'        => get_string('linkedtosourceportfoliotitle','view'),
                'description'  => get_string('linkedtosourceportfoliodescription','view'),
                'defaultvalue' => 1,
            );
        }

        // Populate the fields with the existing values if any
        if (!empty($this->id)) {
            foreach ($elements as $k => $element) {
                if ($k === 'tags') {
                    $elements[$k]['defaultvalue'] = $this->get_tags();
                }
                else if ($k == 'coverimage') {
                    $elements[$k]['defaultvalue'] = ($this->get('coverimage') ? array($this->get('coverimage')) : null);
                }
                else {
                    if (isset($this->$k)) {
                        $elements[$k]['defaultvalue'] = $this->$k;
                    }
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
     * Returns the submission origin id of the collection.
     *
     * Return is mixed:
     *   - false if the collection is not a submission (no collection.submissionoriginal is set)
     *   - 0 collection.submissionoriginal is set but the related collection does not exist
     *   - integer collection id of the original collection to link to.
     *
     * @return integer|false
     */
    public function get_submission_origin() {
        $submissionoriginal = $this->get('submissionoriginal');
        if (empty($submissionoriginal)) {
            // Not currently a submission.
            return false;
        }

        if (get_record('collection', 'id', $submissionoriginal)) {
            // We have a record.
            return $submissionoriginal;
        }
        // If we get this far the submission original has been deleted.
        return 0;
    }

    /**
     * Returns true if the Collection is still a submission.
     *
     * This checks the submissionoriginal field that links this to the source
     * Collection.
     *
     * @return boolean
     */
    public function is_submission() {
        return (bool) $this->get_submission_origin();
    }

    /**
     * Returns array of views in the current collection
     *
     * @return array views
     */
    public function views() {

        if (!isset($this->views)) {

            $sql = "SELECT v.id, cv.*, v.title, v.owner, v.group, v.institution, v.ownerformat, v.urlid, v.skin
                FROM {collection_view} cv JOIN {view} v ON cv.view = v.id
                WHERE cv.collection = ?
                AND v.type != 'progress'
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
     * Returns first view in the current collection
     *
     * @return View the first view of the collection, null if the collection is empty
     */
    public function first_view() {

        $viewid = get_field('collection_view', 'view', 'collection', $this->get('id'), 'displayorder', '0');
        $viewid = get_field_sql("SELECT cv.view
            FROM {collection_view} cv
            WHERE cv.collection = ?
            AND cv.displayorder = (
                SELECT MIN(displayorder)
                FROM {collection_view} cv2
                WHERE cv2.collection = ?)",
            array($this->get('id'), $this->get('id')));
        if ($viewid) {
            require_once('view.php');
            $view = new View($viewid);
            return $view;
        }
        return null;
    }

    /**
     * Returns the first plain view in the collection.
     *
     * This excludes special pages like the progress and Smart Evidence pages.
     *
     * @return View The first view of the collection, null if the collection is empty
     */
    public function first_plain_view() {
        // A sanity check to ensure that the collection views are loaded.
        if (empty($this->views['views'])) {
            $this->views();
        }
        if (!empty($this->views['views'])) {
            $v = $this->views['views'][0];
            if (!empty($v->id)) {
                return new View($v->id);
            }
        }
        return null;
    }

    /**
     * Check that a collection can have a framework
     *
     * @return bool
     */
    public function can_have_framework() {
        return ($this->get_framework_institution()) ? true : false;
    }

    /**
     * Check that a collection can have a portfolio progress completion
     *
     * @return bool
     */
    public function can_have_progresscompletion() {
        $allowspc = false;
        require_once(get_config('docroot') . 'lib/institution.php');
        if ($this->institution) {
            $institution = $this->institution;
            $institution = new Institution($institution);
            $allowspc = ($institution->progresscompletion) ? $institution : false;
        }
        else if ($this->group) {
            $institution = get_field('group', 'institution', 'id', $this->group);
            $institution = new Institution($institution);
            $allowspc = ($institution->progresscompletion) ? $institution : false;
        }
        else {
            $user = new User();
            $user->find_by_id($this->owner);
            $institutionids = array_keys($user->get('institutions'));
            if (!empty($institutionids)) {
                foreach ($institutionids as $institution) {
                    $institution = new Institution($institution);
                    if ($institution->progresscompletion == true) {
                        $allowspc = $institution;
                        break;
                    }
                }
            }
            else {
                $institution = new Institution('mahara');
                $allowspc = ($institution->progresscompletion) ? $institution : false;
            }
        }
        return $allowspc;
    }

    /**
     * Check if any allowed institutions lets a collection have a framework
     * and return first valid one.
     *
     * Checks:
     * - The collection is not owned by a group
     * - The framework plugin is active
     * - The institution has 'SmartEvidence' turned on
     * - There are frameworks available for the institutions
     *
     * @return array of $institution objects or false
     */
     public function get_framework_institution() {
        require_once('institution.php');

        if (!is_plugin_active('framework', 'module')) {
            return false;
        }
        $allowsmartevidence = false;
        if ($this->institution) {
            $institution = $this->institution;
            $institution = new Institution($institution);
            $allowsmartevidence = ($institution->allowinstitutionsmartevidence) ? array($institution) : false;
        }
        else {
            $institutionids = array();
            if (!empty($this->group)) {
                $group =  get_group_by_id($this->group);
                $institutionids[] = $group->institution;
            }
            else {
                $user = new User();
                $user->find_by_id($this->owner);
                $institutionids = array_keys($user->get('institutions'));
            }
            if (!empty($institutionids)) {
                foreach ($institutionids as $institution) {
                    $institution = new Institution($institution);
                    if ($institution->allowinstitutionsmartevidence == true) {
                        $allowsmartevidence[] = $institution;
                    }
                }
            }
            else {
                $institution = new Institution('mahara');
                $allowsmartevidence = ($institution->allowinstitutionsmartevidence) ? array($institution) : false;
            }
        }
        return $allowsmartevidence;
    }

    /**
     * Get available frameworks
     *
     * @return array Available frameworks
     */
    public function get_available_frameworks() {
        $institutions = $this->get_framework_institution();
        if (!$institutions) {
            return array();
        }

        if ($frameworks = Framework::get_frameworks($institutions, true)) {
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
     * Check that a collection has oucomes
     *
     * @return id of page or false
     */
    public function has_outcomes() {
        return $this->outcomeportfolio && $this->outcomecategory;
    }

    /**
     * Check that a collection has progress completion enable
     * - The collection can have progress completion enabled
     * - It has progress completion enabled
     * - It has views in the collection
     * - It has the progress view type
     *
     * @return id of page or false
     */
    public function has_progresscompletion($checkpage=true) {
        if (!$this->can_have_progresscompletion()) {
            return false;
        }
        if (!$this->progresscompletion) {
            return false;
        }
        if ($checkpage) {
            if (!$this->views()) {
                return false;
            }

            $progressview = get_field_sql(
                "SELECT v.id
                 FROM {collection_view} cv
                 JOIN {view} v ON v.id = cv.view
                 WHERE cv.collection = ?
                 AND v.type = ?", array($this->id, 'progress'));
            return !empty($progressview) ? $progressview : false;
        }
        return true;
    }

    /**
     * Add a progress page to the progresscompletion collection
     *
     * If the collection already has a progress page then nothing happens
     *
     * @param boolean $return Return the view of the progress page just created
     *
     * @return View|void View of the progress page or void
     */
    public function add_progresscompletion_view($return = false) {
        if ($this->has_progresscompletion(false) && !$this->has_progresscompletion(true)) {
            // We can add a progress page as it is allowed a
            // progress page but doesn't already have one.
            // Add the progress page to the collection
            require_once(get_config('libroot') . 'view.php');
            $viewdata = array(
                'type'        => 'progress',
                'title'       => get_string('progresspage', 'collection'),
            );
            if (!empty($this->group)) {
                $viewdata['group'] = $this->group;
                $author = 0;
            }
            else if (!empty($this->institution)) {
                $viewdata['institution'] = $this->institution;
                require_once(get_config('libroot') . 'institution.php');
                $institution = new Institution($this->institution);
                $admins = $institution->institution_and_site_admins();
                $author = $admins[0];
            }
            else {
                $viewdata['owner'] = $this->owner;
                $author = $this->owner;
            }
            $systemprogressviewid = get_field('view', 'id', 'institution', 'mahara', 'template', View::SITE_TEMPLATE, 'type', 'progress');
            $artefactcopies = array();
            list($view) = View::create_from_template($viewdata, $systemprogressviewid, $author, false, false, $artefactcopies);
            // Update any existing pages sortorder
            execute_sql("UPDATE {collection_view} SET displayorder = displayorder + 1 WHERE collection = ?", array($this->id));
            // Add progress page as first page of collection
            $cv = array();
            $cv['view'] = $view->get('id');
            $cv['collection'] = $this->id;
            $cv['displayorder'] = 0;
            insert_record('collection_view', (object)$cv);
            if (!empty($this->views())) {
                $this->add_views(array($view->get('id')), 1);
            }
            if ($return) {
                return $view;
            }
        }
    }

    /**
     * Copy the progresscompletion view from the original collection to this one.
     *
     * @param Collection $original The original collection
     */
    public function copy_progresscompletion_view($original, $submissiondetail = []) {

        if ($this->get('progresscompletion')) {
            $pccopy = $this->add_progresscompletion_view(true);
            // Update the progresscompletion view with details from the original collection.
            $pcoriginal = new View($original->has_progresscompletion(true));

            // Copy some fields from the original progress view to the copy.
            $fields = ['ctime', 'mtime', 'atime'];
            for ($i = 0; $i < count($fields); $i++) {
                $pccopy->set($fields[$i], $pcoriginal->get($fields[$i]));
            }
            $artefactcopies = array();
            $pccopy->copy_contents($pcoriginal, $artefactcopies, true);
            foreach ($submissiondetail as $key => $value) {
                if ($value) {
                    $pccopy->set($key, $value);
                }
            }
            $pccopy->set('submissionoriginal', $pcoriginal->get('id'));

            if (!empty($submissiondetail['submittedgroup'])) {
                $viewaccess = [
                    'role'      => 'admin',
                    'view'        => $pccopy->get('id'),
                    'group'       => $submissiondetail['submittedgroup'],
                    'visible'     => 0,
                    'allowcomments' => 1,
                    'ctime' => db_format_timestamp(time()),
                ];
                ensure_record_exists('view_access', $viewaccess, $viewaccess, 'id');
            }
            $pccopy->commit();
        }
    }

    /**
     * Get collection framework option for collection navigation
     *
     * @return object $option;
     */
    public function collection_nav_framework_option() {
        $option = new stdClass();
        $option->framework = $this->framework;
        $option->id = $this->id;
        $option->title = get_field('framework', 'name', 'id', $this->framework);
        $option->framework = true;

        $option->fullurl = self::get_framework_url($option);

        return $option;
    }

    /**
     * Get collection outcomes option for collection navigation
     *
     * @return object $option;
     */
    public function collection_nav_outcomes_option() {
        $option = new stdClass();
        $option->id = $this->id;
        $option->title = get_string('Outcomes', 'group');
        $option->outcomes = true;

        $option->fullurl = self::get_outcomes_url($option);

        return $option;
    }

    /**
     * Get collection progress completion option for collection navigation
     *
     * @return object $option;
     */
    public function collection_nav_progresscompletion_option() {
        $option = new stdClass();
        $option->id = $this->id;
        $option->title = get_string('progresscompletion', 'admin');
        $option->progresscompletion = true;

        $option->fullurl = self::get_progresscompletion_url($option);

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
     * Making the outcomes url
     *
     * @param object $data    Either a collection or standard object
     * @param bool   $fullurl Return full url rather than relative one
     *
     * @return $url
     */
    public static function get_outcomes_url($data, $fullurl = true) {
        $url = 'collection/outcomesoverview.php?id=' . $data->id;
        if ($fullurl) {
            return get_config('wwwroot') . $url;
        }
        return $url;
    }

    /**
     * Making the progress completion url
     *
     * @param object $data    Either a collection or standard object
     * @param bool   $fullurl Return full url rather than relative one
     *
     * @return $url
     */
    public static function get_progresscompletion_url($data, $fullurl = true) {
        $url = 'collection/progresscompletion.php?id=' . $data->id;
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
     * @param integer|null $owner The ID of the person
     * @param integer|null $groupid The ID of the group
     * @param string|null $institutionname The name of the institution
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
     * @param integer index to base the first view off
     * @return integer count so we know what SESSION message to display
     */
    public function add_views($values, $index=0) {
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

        $viewids = get_column_sql("SELECT view FROM {collection_view} WHERE collection = ? ORDER BY displayorder", array($this->id));

        // Set the most permissive access records on all views
        View::combine_access($viewids);

        // Copy the whole view config from the first view to all the others
        if (count($viewids)) {
            $firstview = new View($viewids[$index]);
            $viewconfig = array(
                'startdate'       => $firstview->get('startdate'),
                'stopdate'        => $firstview->get('stopdate'),
                'template'        => $firstview->get('template'),
                'retainview'      => $firstview->get('retainview'),
                'allowcomments'   => $firstview->get('allowcomments'),
                'approvecomments' => (int) ($firstview->get('allowcomments') && $firstview->get('approvecomments')),
                'accesslist'      => $firstview->get_access(),
                'lockblocks'      => $firstview->get('lockblocks'),
            );
            View::update_view_access($viewconfig, $viewids);
        }

        // Now that we have added views to the collection we need to update the collection modified date
        $this->mtime = db_format_timestamp(time());
        $this->commit();
        db_commit();
        // Unset the current views and then reset it again
        $this->set('views', null);
        $this->views();

        return $count;
    }

    /**
     * Removes the selected views from the collection
     *
     * @param integer $view the view to remove
     */
    public function remove_view($view) {
        db_begin();

        $position = get_field_sql('
            SELECT displayorder FROM {collection_view}
                WHERE collection = ?
                AND view = ?',
                array($this->get('id'),$view)
        );

        delete_records('collection_view','view',$view,'collection',$this->get('id'));

        $this->update_display_order($position);
        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        delete_records_select('view_access', 'view = ? AND token IS NOT NULL', array($view));

        // Now that we have removed views from the collection we need to update the collection modified date
        $this->mtime = db_format_timestamp(time());
        $this->commit();

        db_commit();
    }

    /**
     * Updates the position number of the views in a collection
     *
     * @param integer $start position from where to start updating
     */
    public function update_display_order($start = 0) {
        $start = intval($start);
        $ids = get_column_sql('
                SELECT view FROM {collection_view}
                WHERE collection = ?
                ORDER BY displayorder', array($this->get('id')));
        foreach ($ids as $k => $v) {
            if ($start <= $k) {
                set_field('collection_view', 'displayorder', $k, 'view', $v, 'collection',$this->get('id'));
            }
        }
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
            // We already have new sort order
            $neworder = $direction;
            if ($pid = $this->has_progresscompletion()) {
                if (!in_array($pid, $neworder)) {
                    // We need to add the progress page to be displayorder = 0
                    array_unshift($neworder, $pid);
                }
            }
        }
        else {
            $oldorder = -1;
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
            if ($oldorder > -1) {
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
     * After editing the collection, redirect back to the appropriate place
     * @param boolean $new  Whether the collection has just been created
     * @param boolean $copy Whether the collection is a copy
     * @param array $urlparams An array of extra url params to set on the redirect
     */
    public function post_edit_redirect($new=false, $copy=false, $urlparams=null) {
        $redirecturl = $this->post_edit_redirect_url($new, $copy, $urlparams);
        redirect($redirecturl);
    }

    /**
     * Returns the url that we need to redirect to after editing a collection
     * @param boolean $new  Whether the collection has just been created
     * @param boolean $copy Whether the collection is a copy
     * @param array $urlparams An array of extra url params to set on the redirect
     * @return string  URL for redirection
     */
    public function post_edit_redirect_url($new=false, $copy=false, $urlparams=null) {
        $redirecturl = get_config('wwwroot');

        // Group owned collection with outcomes
        if ($this->get('group') && $this->get('outcomeportfolio')) {
          $urlparams['id'] = $this->get('id');
          $redirecturl .= 'collection/manageoutcomes.php';
        }
        else if ($new || $copy) {
            $urlparams['id'] = $this->get('id');
            $redirecturl .= 'collection/views.php';
        }
        else {
            if ($this->get('group')) {
                // Group owned collection
                $redirecturl .= 'view/groupviews.php';
            }
            else if ($this->get('institution')) {
                if ($this->get('institution') == 'mahara') {
                    // Site owned collection
                    $redirecturl .= 'admin/site/views.php';
                }
                else {
                    // Institution owned collection
                    $redirecturl .= 'view/institutionviews.php';
                }
            }
            else {
                // User owned collection
                $redirecturl .= 'view/index.php';
            }
        }
        if ($urlparams) {
            $redirecturl .= '?' . http_build_query($urlparams);
        }
        return $redirecturl;
    }

    /**
     * Fetch a collection based on the ID of a view it contains
     * @param integer $viewid ID of a view
     * @return Collection|false
     */
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
     * @param View|null &$firstview Finds the first view of the collection
     *
     * @return string
     */
    public function get_url($full=true, $useid=false, &$firstview=null) {
        global $USER;
        $firstview = null;

        $views = $this->views();
        if (!empty($views)) {
            if ($this->has_outcomes()) {
                if ($full) {
                    $this->fullurl = Collection::get_outcomes_url($this);
                    return $this->fullurl;
                }
                else {
                    $this->outcomesurl = Collection::get_outcomes_url($this, false);
                    return $this->outcomesurl;
                }
            }
            else if ($this->has_progresscompletion()) {
                if ($full) {
                    $this->fullurl = Collection::get_progresscompletion_url($this);
                    return $this->fullurl;
                }
                else {
                    $this->progresscompletionurl = Collection::get_progresscompletion_url($this, false);
                    return $this->progresscompletionurl;
                }
            }
            else if ($this->framework) {
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
     * @param string $externalid  An external ID that the archive relates to
     * @param bool $checkhost Verify a record is in the host table
     */
    public function pendingrelease($releaseuser=null, $externalid=null, $checkhost=true) {
        if ($checkhost) {
            // While not used, the absence of a record will throw an exception.
            $submitinfo = $this->submitted_to();
        }
        if (!$this->is_submitted()) {
            throw new ParameterException("Collection with id " . $this->id . " has not been submitted");
        }
        $viewids = $this->get_viewids(true);
        try {
            db_begin();
            execute_sql("UPDATE {collection}
                     SET submittedstatus = " . self::PENDING_RELEASE . "
                     WHERE id = ?",
                        array($this->id)
            );
            View::_db_pendingrelease($viewids);
            safe_require('module', 'submissions');
            if (PluginModuleSubmissions::is_active() && $this->submittedgroup && !group_external_group($this->submittedgroup)) {
                PluginModuleSubmissions::pending_release_submission($this, $releaseuser);
            }
            require_once(get_config('docroot') . 'export/lib.php');
            add_submission_to_export_queue($this, $releaseuser, $externalid);
            db_commit();
        }
        catch (Exception $e) {
            db_rollback();
            throw $e;
        }
    }

    /**
     * Release a submitted collection with a message to the user.
     *
     * The optional $releasemessageoverrides is of the form:
     * $releasemessageoverrides [
     *   'group' => [
     *     'subjectkey' => STRING,
     *     'messagekey' => STRING,
     *   ],
     *   'host' => [
     *     'subjectkey' => STRING,
     *     'messagekey' => STRING,
     *   ]
     * ]
     *
     * Only the ovrridden strings need to be supplied.  The strings need to be
     * present in htdocs/lang/en.utf8/group.php.
     *
     * The strings can take up to 3 replacement parameters. These are:
     * * Title - the title of what is being released.
     * * Released from - the group name or submittedhost.
     * * Released by - the display name of the user releasing the portfolio.
     *
     * @param LiveUser|null $releaseuser The Account releasing the Portfolio.
     * @param array $releasemessageoverrides Optional array of string keys to use rather than the defaults.
     * @param bool $returntouser If true, the submissionoriginal will also be cleared.
     *
     * @return void
     */
    public function release($releaseuser=null, $releasemessageoverrides = [], $returntouser = false) {

        if (!$this->is_submitted()) {
            throw new ParameterException("Collection with id " . $this->id . " has not been submitted");
        }

        // One day there might be group and institution collections, so be safe
        if (empty($this->owner)) {
            throw new ParameterException("Collection with id " . $this->id . " has no owner");
        }

        $viewids = $this->get_viewids(true);

        try {
            db_begin();
            $sql = '';
            $set = 'submittedgroup = NULL,
                    submittedhost = NULL,
                    submittedtime = NULL,
                    submittedstatus = ' . self::UNSUBMITTED;
            if ($returntouser) {
                $set .= ', submissionoriginal = ' . self::UNSUBMITTED;
            }
            execute_sql('
            UPDATE {collection}
            SET ' . $set . '
            WHERE id = ?',
                        array($this->id)
            );
            View::_db_release($viewids, $this->owner, $this->submittedgroup, $returntouser);
            safe_require('module', 'submissions');
            if (PluginModuleSubmissions::is_active() && $this->submittedgroup && !group_external_group($this->submittedgroup)) {
                PluginModuleSubmissions::release_submission($this, $releaseuser);
            }
            db_commit();
        }
        catch (Exception $e) {
            db_rollback();
            throw $e;
        }

        $releaseuser = optional_userobj($releaseuser);
        handle_event('releasesubmission', array('releaseuser' => $releaseuser,
                                                'id' => $this->get('id'),
                                                'hostname' => $this->submittedhost,
                                                'groupname' => ($this->submittedgroup ? get_field('group', 'name', 'id', $this->submittedgroup) : ''),
                                                'eventfor' => 'collection'));

        // We don't send out notifications about the release of remote-submitted Views & Collections
        // (though I'm not sure why)
        // if the method is called in an upgrade and we don't have a release user
        if (!defined('INSTALLER') && $this->submittedgroup) {
            $releaseuserdisplay = display_name($releaseuser, $this->owner);
            $releaseuserid = ($releaseuser instanceof User) ? $releaseuser->get('id') : $releaseuser->id;
            $submitinfo = $this->submitted_to();

            if ((int)$releaseuserid !== (int)$this->get('owner')) {
                require_once('activity.php');
                $subjectkey = 'portfolioreleasedsubject';
                $messagekey = 'portfolioreleasedmessage';
                if ($this->submittedgroup) {
                    $submitinfo = $this->submitted_to();
                    if (!empty($releasemessageoverrides['group']['subjectkey'])) {
                        $subjectkey = $releasemessageoverrides['group']['subjectkey'];
                    }
                    if (!empty($releasemessageoverrides['group']['messagekey'])) {
                        $messagekey = $releasemessageoverrides['group']['messagekey'];
                    }
                }
                else if ($this->submittedhost) {
                    if (!empty($releasemessageoverrides['host']['subjectkey'])) {
                        $subjectkey = $releasemessageoverrides['host']['subjectkey'];
                    }
                    if (!empty($releasemessageoverrides['host']['messagekey'])) {
                        $messagekey = $releasemessageoverrides['host']['messagekey'];
                    }
                }
                activity_occurred(
                    'maharamessage',
                    array(
                        'users' => array($this->get('owner')),
                        'strings' => (object) array(
                            'subject' => (object) array(
                                'key'     => $subjectkey,
                                'section' => 'group',
                                'args'    => array($this->name),
                            ),
                            'message' => (object) array(
                                'key'     => $messagekey,
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
    }

    /**
     * Quick way to find the view IDs of the collection
     *
     * @param boolean $includeprogress  Whether the progress page id is returned as well
     * @return array View IDs
     */
    public function get_viewids($includeprogress = false) {
        $ids = array();
        $viewdata = $this->views();

        if (!empty($viewdata['views'])) {
            foreach ($viewdata['views'] as $v) {
                $ids[] = $v->id;
            }
        }

        if ($includeprogress && $this->has_progresscompletion()) {
            $ids[] = $this->has_progresscompletion();
        }

        return $ids;
    }

    /**
     * Helper to check if collection is submitted
     * @return integer|string
     */
    public function is_submitted() {
        return $this->submittedgroup || $this->submittedhost;
    }

    /**
     * Helper to find where collection is submitted.
     *
     * @throws SystemException Collection is not submitted
     * @return object Info about what this was submitted to
     */
    public function submitted_to() {
        if ($this->submittedgroup) {
            $record = get_record('group', 'id', $this->submittedgroup, null, null, null, null, 'id, name, urlid');
            $record->url = group_homepage_url($record);
        }
        else if ($this->submittedhost) {
            if (!$hostconnection = get_field('host', 'name', 'wwwroot', $this->submittedhost)) {
                $hostconnection = $this->submittedhost;
            }
            $record = new stdClass;
            $record->url = $hostconnection;
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
     * @param integer $owner The owner of the collection (if not just $USER)
     * @param boolean $sendnotification
     * @throws CollectionSubmissionException
     * @return Collection The copy of the collection that was submitted
     */
    public function submit($group = null, $submittedhost = null, $owner = null, $sendnotification=true) {
        global $USER, $SESSION;
        require_once('group.php');

        // Gotta provide one or the other
        if (!$group && !$submittedhost) {
            throw new CollectionSubmissionException(get_string('cantsubmitneedgrouporsubmittedhost', 'view'));
        }
        $collectionid = $this->id;
        $firstview = $this->first_plain_view();

        $copy = copyview($firstview->get('id'), false, null, $collectionid, true);

        if ($copy === false) {
            $SESSION->add_error_msg(get_string('cantsubmitcopyfailed', 'view'));
            // Redirect back to the portfolio they came from. Any messages are in the $SESSION.
            redirect($this->get_url());
        }

        // Set the submissionoriginal field.
        $copy->set('submissionoriginal', $this->get('id'));

        $viewids = $copy->get_viewids(true);
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

        try {
            db_begin();
            $submissiondetail = [
                'submittedgroup' => false,
                'submittedhost' => false,
                'submittedtime' => false,
                'submittedstatus' => false,
            ];
            View::_db_submit($viewids, $group, $submittedhost, $owner);
            if ($group) {
                $copy->set('submittedgroup', $group->id);
                $submissiondetail['submittedgroup'] = $group->id;
                $copy->set('submittedhost', null);
            }
            else {
                $copy->set('submittedgroup', null);
                $copy->set('submittedhost', $submittedhost);
                $submissiondetail['submittedhost'] = $submittedhost;
            }
            $time = time();
            $date = format_date($time, 'strftimerecentyear');
            $copy->set('submittedtime', $time);
            $submissiondetail['submittedtime'] = $time;
            $copy->set('submittedstatus', self::SUBMITTED);
            $submissiondetail['submittedstatus'] = self::SUBMITTED;
            $copy->set('name', $copy->get('name') . get_string('submittedtimetitle', 'view', $date));
            // Copy progress completion view if it exists.
            $copy->copy_progresscompletion_view($this, $submissiondetail);
            $copy->commit();
            safe_require('module', 'submissions');
            if (PluginModuleSubmissions::is_active() && $group && !group_external_group($group)) {
                // This is a submission. Add that to the title.
                PluginModuleSubmissions::add_submission($copy, $group);
            }
            db_commit();
        }
        catch (Exception $e) {
            db_rollback();
            throw $e;
        }
        handle_event(
            'addsubmission',
            array(
                'id' => $copy->id,
                'eventfor' => 'collection',
                'name' => $copy->name,
                'group' => ($group) ? $group->id : null,
                'groupname' => ($group) ? $group->name : null,
                'externalhost' => ($submittedhost) ? $submittedhost : null,
            )
        );
        if ($group && $sendnotification) {
            activity_occurred(
                'groupmessage',
                array(
                    'group'         => $group->id,
                    'roles'         => $group->roles,
                    'url'           => $copy->get_url(false),
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
                                $copy->name,
                                $group->name,
                            ),
                        ),
                    ),
                )
            );
        }
        return $copy;
    }

    /**
     * Helper function to get ID of image artefact
     * @return integer|null ID of image artefact
     */
    public function get_coverimage() {
        if ($this->coverimage && get_field('artefact', 'id', 'id', $this->coverimage)) {
            return $this->coverimage;
        }
        return null;
    }

    /**
     * Returns the collection tags
     *
     * @return mixed
     */
    public function get_tags() {
        if (!isset($this->tags)) {
            $typecast = is_postgres() ? '::varchar' : '';
            $this->tags = get_column_sql("
            SELECT
                (CASE
                    WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                    ELSE t.tag
                END) AS tag
            FROM {tag} t
            LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
            LEFT JOIN {institution} i ON i.name = t2.ownerid
            WHERE t.resourcetype = ? AND t.resourceid = ?
            ORDER BY tag", array('collection', $this->get('id')));
        }
        return $this->tags;
    }

    /**
     * Creates a new secret url for this collection
     * @param boolean $visible
     * @return object|false The view_access record for the first view's secret URL
     */
    public function new_token($visible=true) {
        $viewids = get_column('collection_view', 'view', 'collection', $this->id);

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
            $vaid = insert_record('view_access', $todb, 'id', true);
            handle_event('updateviewaccess', array(
                'id' => $vaid,
                'eventfor' => 'token',
                'parentid' => current($viewids),
                'parenttype' => 'view',
                'rules' => $todb)
            );
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

    /**
     * Retrieves the groupid, if this collection is associated as outcome to a task
     * corresponding to a group selection plan, otherwise false
     *
     * @return bool
     * @throws SQLException
     */
    public function get_group_id_of_corresponding_group_task() {
        $sql = 'SELECT * FROM {artefact} AS a '.
            'INNER JOIN {artefact_plans_task} AS gt ON gt.artefact = a.id '.
            'INNER JOIN {artefact_plans_task} AS ut ON ut.rootgrouptask = gt.artefact '.
            'WHERE ut.outcometype = ? AND ut.outcome = ?';

        $result = get_record_sql($sql, ['collection', $this->get('id')]);

        if ($result && $result->group) {
            return $result->group;
        }
        return false;
    }

    /**
     * Gets the percentage of verified and signed off actions in the collection that have been completed
     *
     * @return integer percentage of completed actions
     * @return integer total of actions
     */
    public function get_signed_off_and_verified_percentage() {
        $views = $this->views();
        $numberofpages = !empty($views['count']) ? $views['count'] : 0;
        if ($numberofpages == 0) return false;
        safe_require('artefact', 'peerassessment');
        $numberofcompletedactions = 0;
        $numberofactions = 0;
        foreach ($this->views['views'] as $view) {
            $viewobj = new View($view->view);
            if ($viewobj->has_signoff()) {
                $numberofactions++;
                if (ArtefactTypePeerassessment::is_signed_off($viewobj)) {
                    $numberofcompletedactions++;
                }
                if (ArtefactTypePeerassessment::is_verify_enabled($viewobj)) {
                    $numberofactions++;
                    if (ArtefactTypePeerassessment::is_verified($viewobj)) {
                        $numberofcompletedactions++;
                    }
                }
            }
        }
        if ($numberofactions == 0) return false;
        return array(round(($numberofcompletedactions/$numberofactions)*100), $numberofactions);
    }

    public function get_outcomes_complete_percentage() {
      $complete = get_column('outcome', 'complete', 'collection', $this->get('id'));
      $outcomenumber = count($complete);
      $completednumber = count(array_filter($complete));
      return array(round(($completednumber/$outcomenumber)*100), $outcomenumber);
    }

    /**
     * Track parent and child template
     *
     * @param integer Collection ID of parent
     * @return integer Row ID
     */
    public function track_template($id) {
        if (!get_field('collection', 'id', 'id', $id)) {
            throw new CollectionNotFoundException("Collection with id $id not found");
        }
        if (!$trackingid = get_field('collection_template', 'id', 'originaltemplate', $id, 'collection', $this->id)) {
            $data = new stdClass();
            $data->collection = $this->id;
            $data->originaltemplate = $id;
            $trackingid = insert_record('collection_template', $data, 'id', true);
        }
        return $trackingid;
    }

    /**
     * Given an institution name we set this collection to be the auto copy template for the institution
     *
     * If setting to autocopy and there is another collection with the auto copy template set
     * to true, then we call unset_active_collection_template on that one
     * @param string $institution ID of institution
     */
    public function set_active_collection_template($institution) {
        $collectionid = $this->get('id');
        $oldtemplate = get_field('collection', 'id', 'autocopytemplate', 1, 'institution', $institution);
        // If the collection is already the auto copy, then do nothing
        if ($collectionid && $oldtemplate != $collectionid) {
            // Remove the auto copy setting from the old collection
            if ($oldtemplate) {
                $this->unset_active_collection_template($oldtemplate, $institution, true);
            }

            // Set new collection to be auto copy template
            set_field('collection', 'autocopytemplate', 1, 'id', $collectionid);
            // Share collection with the institution
            $viewids = get_column('collection_view', 'view', 'collection', $collectionid);
            if ($viewids) {
                $time = db_format_timestamp(time());
                foreach ($viewids as $vid) {
                    $newwhere = (object) array(
                        'view'       => $vid,
                        'institution' => $institution,
                    );
                    $newaccess = clone $newwhere;
                    $newaccess->ctime = $time;
                    ensure_record_exists('view_access', $newwhere, $newaccess, 'id');
                }
            }
        }
    }

    /**
     * Unset the auto copy for a collection
     *
     * Given an institution name and a collection id
     * We set that collection to be auto copy false
     * and remove the sharing permission to the institution specified
     * @param integer $id Collection ID
     * @param string $collection Collection ID
     * @param boolean $keeptemplate Whether to keep the template setting
     */
    public function unset_active_collection_template($id, $institution, $keeptemplate) {
        set_field('collection', 'autocopytemplate', 0, 'id', $id);
        // remove sharing permissions with institution for the collection
        $viewids = get_column('collection_view', 'view', 'collection', $id);
        if ($viewids) {
            foreach ($viewids as $vid) {
                delete_records('view_access', 'view', $vid, 'institution', $institution);
                set_field('view', 'copynewuser', 0, 'id', $vid);
                set_field('view', 'template', 0, 'id', $vid);
                if (!$keeptemplate) {
                    set_field('view', 'locktemplate', 0, 'id', $vid);
                }
            }
        }
    }

    /**
     * Set all the views in the collection to be templates
     *
     * The $autocopy argument only needs to be used when creating/updating collection config to
     * give the `autocopytemplate` field a value. When adding pages to a collection,
     * this value is already set, hence we look here instead of at the argument.
     *
     * @param  boolean|null $autocopyupdate The autocopy value when creating/updating the collection setting
     */
    public function set_views_as_template($autocopyupdate=null) {
        $autocopy = $autocopyupdate;
        if (is_null($autocopyupdate)) {
            if ($this->get('autocopytemplate')) {
                $autocopy = 1;
            }
        }

        $viewids = get_column('collection_view', 'view', 'collection', $this->get('id'));
        if ($viewids) {
            foreach($viewids as $vid) {
                set_field('view', 'locktemplate', 1, 'id', $vid);
                set_field('view', 'lockblocks', 1, 'id', $vid);
                if (!is_null($autocopy)) {
                    if ($autocopy) {
                        set_field('view', 'template', 1, 'id', $vid);
                        set_field('view', 'copynewuser', 1, 'id', $vid);
                    }
                    else {
                        set_field('view', 'template', 0, 'id', $vid);
                    }
                }
            }
        }
    }


    /**
     * Lock the collection and all of the views
     */
    public function lock_collection() {
        // Lock the collection
        $collectionid = $this->get('id');
        execute_sql("UPDATE {collection} SET " . db_quote_identifier('lock') . " = 1 WHERE id = ?", array($collectionid));
        // Lock all views in that collection
        $sql = "UPDATE {view} SET locked = 1
            WHERE id IN (
                SELECT view FROM {collection_view}
                WHERE collection = ?
            )";
        execute_sql($sql, array($collectionid));
        // Set the rollover / unlock date
        // TODO - get this to be an institution / block setting rather than hard code for 6 months
        execute_sql("UPDATE {collection_template} SET rolloverdate = ? WHERE collection = ?", array(db_format_timestamp(time()), $collectionid));
    }

    /**
     * Unlock the collection and all of the views
     */
    public function unlock_collection() {
        // Unlock the collection
        $collectionid = $this->get('id');
        execute_sql("UPDATE {collection} SET " . db_quote_identifier('lock') . " = 0 WHERE id = ?", array($collectionid));
        // Unlock all views in that collection
        $sql = "UPDATE {view} SET locked = 0
            WHERE id IN (
                SELECT view FROM {collection_view}
                WHERE collection = ?
            )";
        execute_sql($sql, array($collectionid));
    }

    /**
     * Get latest comment on a collection
     *
     * @param boolean $includedraft  Whether the latest comment can be a draft comment
     *
     * @return mixed|null comment object and view id
     */
    public function get_latest_comment($includedraft=false) {
        $viewids = $this->get_viewids();
        $sql = 'SELECT a.id, acc.onview FROM {artefact_comment_comment} acc
                JOIN {artefact} a ON a.id = acc.artefact
                WHERE acc.onview IN (' . join(',', array_fill(0, count($viewids), '?')) . ')';
        if (!$includedraft) {
            $sql .= ' AND acc.private != 1';
        }
        $sql .= ' ORDER BY a.mtime DESC
                  LIMIT 1';
        if ($viewids && $data = get_records_sql_array($sql, $viewids)) {
            safe_require('artefact', 'comment');
            $comment = new ArtefactTypeComment($data[0]->id);
            $viewid = $data[0]->onview;
            return array($comment, $viewid);
        }
        return null;
    }
}

/**
 * CollectionSubmissionException - something has gone wrong submitting a collection.
 * Generally these will be the fault with trying to submit an empty collection or one already submitted
 */
class CollectionSubmissionException extends UserException {

    /**
     * For a CollectionSubmissionException, the error message is mandatory
     *
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message);
    }

    /**
     * Return the error strings for the exception
     * @return array Title and message strings for exception
     */
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

/**
 * Find the collection that is the current active template for autocopy
 *
 * @param string $institution The internal name for the institution. Defaults to site.
 * @return Collection|null The Collection object for the active autocopy template or null
 */
function get_active_collection_template($institution='mahara') {
    if ($collectionid = get_field('collection', 'id', 'autocopytemplate', 1, 'institution', $institution)) {
        $collection = new Collection($collectionid);
        return $collection;
    }
    return false;
}

/**
 * Unlock the collections by the rollover date
 *
 * When unlocking via rollover we leave the views still locked
 * so a person can only delete their collection but not edit it
 *
 * @param $date string A valid date or string that can be used by strtotime() function
 *
 */
function unlock_collections_by_rollover($date = '-6 months') {
    // Unlock the collections
    $rollover = db_format_timestamp(strtotime($date));
    execute_sql("UPDATE {collection} SET " . db_quote_identifier('lock') . " = 0
                 WHERE id IN (
                    SELECT ct_collection  FROM (
                        SELECT ct.collection as ct_collection FROM {collection_template} ct
                        JOIN {collection} c ON c.id = ct.collection
                        WHERE ct.rolloverdate < ?
                        AND c." . db_quote_identifier('lock') . " = 1
                    ) as ct
                )", array($rollover));
}
