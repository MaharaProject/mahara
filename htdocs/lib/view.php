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

class View {

    private $dirty;
    private $deleted;
    private $id;
    private $owner;
    private $ownerformat;
    private $group;
    private $institution;
    private $ctime;
    private $mtime;
    private $atime;
    private $startdate;
    private $stopdate;
    private $submittedgroup;
    private $submittedhost;
    private $submittedtime;
    private $submittedstatus;
    private $title;
    private $description;
    private $loggedin;
    private $friendsonly;
    private $artefact_instances;
    private $artefact_metadata;
    private $ownerobj;
    private $groupobj;
    private $institutionobj;
    private $numcolumns; // now redundant
    private $columnsperrow; // assoc array of rows set and get using view_rows_columns db table
    private $numrows;
    private $layout;
    private $theme;
    private $rows;
    private $columns;
    private $dirtyrows; // for when we change stuff
    private $dirtycolumns; // now includes reference to row [row][column]
    private $tags;
    private $categorydata;
    private $template;
    private $retainview;
    private $copynewuser = 0;
    private $copynewgroups;
    private $type;
    private $visits;
    private $allowcomments;
    private $approvecomments;
    private $collection;
    private $accessconf;
    private $locked;
    private $urlid;
    private $skin;
    private $anonymise = 0;

    const UNSUBMITTED = 0;
    const SUBMITTED = 1;
    const PENDING_RELEASE = 2;

    /**
     * Which view layout is considered the "default" for views with the given
     * number of columns. Must be present in $layouts of course.
     */
    public static $defaultcolumnlayouts = array(
            1 => '100',
            2 => '50,50',
            3 => '33,33,33',
            4 => '25,25,25,25',
            5 => '20,20,20,20,20',
    );

    /**
     * Valid view column layouts. These are read at install time and inserted into
     * view_layout_columns, but not updated afterwards, so if you're changing one
     * you'll need to do that manually.
     *
     * The key represents the number of columns, and the value is an array of all the
     * view_layout_columns records that have that number of columns
     */
    public static $basic_column_layouts = array(
        1 => array(
            '100',
        ),
        2 => array(
            '50,50',
            '67,33',
            '33,67',
        ),
        3 => array(
            '33,33,33',
            '25,50,25',
            '25,25,50',
            '50,25,25',
            '15,70,15',
        ),
        4 => array(
            '25,25,25,25',
            '20,30,30,20',
        ),
        5 => array(
            '20,20,20,20,20',
        ),
    );

    /**
     * The default layout options to be read at install time.
     * Each view_layout record is based on the array key and the count of its values.
     * Each view_layout_rows_columns record is based on the sub array.
     * For example:
     *  18 => array(
     *              1 => '100',
     *              2 => '50,50',
     *              3 => '100'
     *              'order' => 3
     *  ),
     * will insert a record in view_layout with id = 18 and rows = 3
     * and will insert 3 records in view_layout_rows_columns:
     *  - viewlayout = 18, rows = 1, columns = 1
     *  - viewlayout = 18, rows = 2, columns = 2
     *  - viewlayout = 18, rows = 3, columns = 1
     * And the "order" key indicates that this should be the 3rd option in the layout menu
     */
    public static $defaultlayoutoptions = array(
        1 => array(
                1 => '100',
                'order' => 1,
            ),
        2 => array(
                1 => '50,50',
                'order' => 2,
            ),
        3 => array(
                1 => '67,33',
                'order' => 3,
            ),
        4 => array(
                1 => '33,67',
                'order' => 4,
            ),
        5 => array(
                1 => '33,33,33',
                'order' => 5,
            ),
        6 => array(
                1 => '25,50,25',
                'order' => 6,
            ),
        7 => array(
                1 => '25,25,50'
            ),
        8 => array(
                1 => '50,25,25'
            ),
        9 => array(
                1 => '15,70,15'
            ),
        10 => array(
                1 => '25,25,25,25'
            ),
        11 => array(
                1 => '20,30,30,20'
            ),
        12 => array(
                1 => '20,20,20,20,20'
            ),
        13 => array(
                1 => '100',
                2 => '25,50,25'
            ),
        14 => array(
                1 => '100',
                2 => '33,67',
                'order' => 7
            ),
        15 => array(
                1 => '100',
                2 => '67,33'
            ),
        16 => array(
                1 => '100',
                2 => '50,50'
            ),
        17 => array(
                1 => '100',
                2 => '33,33,33',
                'order' => 8
            ),
        18 => array(
                1 => '100',
                2 => '50,50',
                3 => '100'
            ),
        19 => array(
                1 => '100',
                2 => '33,33,33',
                3 => '100',
                'order' => 9
            ),
        20 => array(
                1 => '100',
                2 => '25,50,25',
                3 => '100'
            ),
        21 => array(
                1 => '100',
                2 => '50,50',
                3 => '33,33,33',
                'order' => 10
            ),
    );

    public static $maxlayoutrows = 6; // see number of colours avail in layoutpreview.php

    /**
     * For retrieving and checking numbers of columnns in any given row
     * Initialised in constructor
     * An array of objects which represent each row in view_layout_columns
     */
    public static $layoutcolumns;

    public function __construct($id=0, $data=null) {
        global $USER;
        if (is_array($id) && isset($id['urlid']) && isset($id['ownerurlid'])) {
            $tempdata = get_record_sql('
                SELECT v.*
                FROM {view} v JOIN {usr} u ON v.owner = u.id
                WHERE v.urlid = ? AND u.urlid = ?',
                array($id['urlid'], $id['ownerurlid'])
            );
            if (empty($tempdata)) {
                throw new ViewNotFoundException(get_string('viewnotfoundbyname', 'error', $id['urlid'], $id['ownerurlid']));
            }
        }
        else if (is_array($id) && isset($id['urlid']) && isset($id['groupurlid'])) {
            $tempdata = get_record_sql('
                SELECT v.*
                FROM {view} v JOIN {group} g ON v.group = g.id
                WHERE v.urlid = ? AND g.urlid = ? AND g.deleted = 0',
                array($id['urlid'], $id['groupurlid'])
            );
            if (empty($tempdata)) {
                throw new ViewNotFoundException(get_string('viewnotfoundbyname', 'error', $id['urlid'], $id['groupurlid']));
            }
        }
        else if (!empty($id) && is_numeric($id)) {
            $tempdata = get_record_sql('
                SELECT v.*
                FROM {view} v LEFT JOIN {group} g ON v.group = g.id
                WHERE v.id = ? AND (v.group IS NULL OR g.deleted = 0)',
                array($id)
            );
            if (empty($tempdata)) {
                throw new ViewNotFoundException(get_string('viewnotfound', 'error', $id));
            }
        }
        if (isset($tempdata)) {
            if (!empty($data)) {
                $data = array_merge((array)$tempdata, $data);
            }
            else {
                $data = $tempdata; // use what the database has
            }
            $this->id = $tempdata->id;
        }
        else {
            $this->ctime = time();
            $this->mtime = time();
            $this->dirty = true;
        }

        $data = empty($data) ? array() : (array)$data;
        foreach ($data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }

        if (empty(self::$layoutcolumns)) {
            self::$layoutcolumns = get_records_assoc('view_layout_columns', '', '', 'columns,id');
        }

        // Add in owner and group objects if we already happen to have them from view_search(), etc.
        if (isset($data['user']) && isset($data['user']->id) && $data['user']->id == $this->owner) {
            $this->ownerobj = $data['user'];
        }
        else if (isset($data['groupdata']->id) && $data['groupdata']->id == $this->group) {
            $this->groupobj = $data['groupdata'];
        }
        else if (!isset($data['user']) && !empty($this->owner) && $this->owner == $USER->get('id')) {
            $this->ownerobj = $USER;
        }

        $this->atime = time();
        $this->rows = array();
        $this->columns = array();
        $this->dirtyrows = array();
        $this->dirtycolumns = array();

        // set only for existing views - _create provides default value
        if (empty($this->columnsperrow)) {
            $this->columnsperrow = get_records_assoc('view_rows_columns', 'view', $this->get('id'), 'row', 'row, columns');
        }
    }

    /**
     * Creates a new View for the given user/group/institution.
     *
     * You can specify who the view is being created _by_ with the second
     * parameter. This defaults to the current logged in user's ID.
     *
     * @param array $viewdata See View::_create
     * @return View           The newly created View
     */
    public static function create($viewdata, $userid=null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        $view = self::_create($viewdata, $userid);
        return $view;
    }

    /**
     * Creates a View for the given user, based off a given template and other
     * View information supplied.
     *
     * Will set a default title of 'Copy of $viewtitle' if title is not
     * specified in $viewdata and $titlefromtemplate == false.
     *
     * @param array $viewdata See View::_create
     * @param int $templateid The ID of the View to copy
     * @param int $userid     The user who has issued the command to create the
     *                        view. See View::_create
     * @param int $checkaccess Whether to check that the user can see the view before copying it
     * @return array A list consisting of the new view, the template view and
     *               information about the copy - i.e. how many blocks and
     *               artefacts were copied
     * @throws SystemException under various circumstances, see the source for
     *                         more information
     */
    public static function create_from_template($viewdata, $templateid, $userid=null, $checkaccess=true, $titlefromtemplate=false) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        $user = new User();
        $user->find_by_id($userid);

        db_begin();

        $template = new View($templateid);

        if ($template->get('deleted')) {
            throw new SystemException("View::create_from_template: This template has been deleted");
        }

        if ($checkaccess && !$template->get('template') && !$user->can_edit_view($template)) {
            throw new SystemException("View::create_from_template: Attempting to create a View from another View that is not marked as a template");
        }
        else if ($checkaccess && !can_view_view($templateid, $userid)) {
            throw new SystemException("View::create_from_template: User $userid is not permitted to copy View $templateid");
        }

        $view = self::_create($viewdata, $userid);

        // Set a default title if one wasn't set
        if ($titlefromtemplate) {
            $view->set('title', $template->get('title'));
        }
        else if (!isset($viewdata['title'])) {
            $desiredtitle = $template->get('title');
            if (get_config('renamecopies')) {
                $desiredtitle = get_string('Copyof', 'mahara', $desiredtitle);
            }
            $view->set('title', self::new_title($desiredtitle, (object)$viewdata));
            $view->set('dirty', true);
        }

        $view->urlid = generate_urlid($view->title, get_config('cleanurlviewdefault'), 3, 100);
        $viewdata['owner'] = $userid;
        $view->urlid = self::new_urlid($view->urlid, (object)$viewdata);

        try {
            $copystatus = $view->copy_contents($template);
        }
        catch (QuotaExceededException $e) {
            db_rollback();
            return array(null, $template, array('quotaexceeded' => true));
        }

        $view->commit();

        // if layout is set, and it's not a default layout
        // add an entry to usr_custom_layout if one does not already exist
        if ($template->get('layout') !== null) {
            $customlayout = get_record('view_layout', 'id', $template->get('layout'), 'iscustom', 1);
            if ($customlayout !== false) {
                // is the owner of the copy going to be a group or institution or not?
                $owner = $view->owner;
                $group = $view->group;
                $institution = $view->institution;
                $haslayout = false;

                if (!empty($group)) {
                    $owner = null;
                    $haslayout = get_record('usr_custom_layout', 'layout', $template->get('layout'), 'group', $group);
                }
                if (!empty($institution)) {
                    $owner = null;
                    $haslayout = get_record('usr_custom_layout', 'layout', $template->get('layout'), 'institution', $institution);
                }
                else if (isset($owner)) {
                    $haslayout = get_record('usr_custom_layout', 'layout', $template->get('layout'), 'usr', $owner);
                }

                if (!$haslayout) {
                    $newcustomlayout = insert_record('usr_custom_layout', (object) array('usr' => $owner, 'group' => $group, 'institution' => $institution, 'layout' => $template->get('layout')) );
                }
            }
        }

        $blocks = get_records_array('block_instance', 'view', $view->get('id'));
        if ($blocks) {
            foreach ($blocks as $b) {
                $configdata = unserialize($b->configdata);
                if (!isset($configdata['artefactid'])) {
                    continue;
                }
                if (!isset($configdata['copytype']) || $configdata['copytype'] !== 'reference') {
                    continue;
                }
                $va = new StdClass;
                $va->view = $b->view;
                $va->artefact = $configdata['artefactid'];
                $va->block = $b->id;
                insert_record('view_artefact', $va);
            }
        }

        if ($template->get('retainview') && !$template->get('institution')) {
            $obj = new StdClass;
            $obj->view  = $view->get('id');
            $obj->ctime = db_format_timestamp(time());
            $obj->usr   = $template->get('owner');
            $obj->group = $template->get('group');
            insert_record('view_access', $obj);
        }

        db_commit();

        return array(
            $view,
            $template,
            $copystatus,
        );
    }

    /**
     * Creates a new View for the given user, based on the given information
     * about the view.
     *
     * Validation of the view data is performed, then the View is created. If
     * the View is to be owned by a group, that group is given access to it.
     *
     * @param array $viewdata Data about the view. You can pass in most fields
     *                        that appear in the view table.
     *
     *                        Note that you set who owns the View by setting
     *                        either the owner, group or institution field as
     *                        approriate.
     *
     *                        Currently, you cannot pass in access data. Use
     *                        $view->set_access() after retrieving the $view
     *                        object.
     *
     * @param int $userid The user who has issued the command to create the
     *                    View (note: this is different from the "owner" of the
     *                    View - a group or institution could be the "owner",
     *                    but it's a _user_ who requests a View is created for it)
     * @return View The created View
     * @throws SystemException if the View data is invalid - mostly this is due
     *                         to owner information being specified incorrectly.
     */
    private static function _create(&$viewdata, $userid) {
        // If no owner information is provided, assume that the view is being
        // created by the user for themself
        if (!isset($viewdata['owner']) && !isset($viewdata['group']) && !isset($viewdata['institution'])) {
            $viewdata['owner'] = $userid;
        }

        if (isset($viewdata['owner'])) {
            if ($viewdata['owner'] != $userid) {
                $userobj = new User();
                $userobj->find_by_id($userid);
                if (!$userobj->is_admin_for_user($viewdata['owner'])) {
                    throw new SystemException("View::_create: User $userid is not allowed to create a view for owner {$viewdata['owner']}");
                }
            }

            // Users can only have one view of each non-portfolio type
            if (isset($viewdata['type']) && $viewdata['type'] != 'portfolio' && get_record('view', 'owner', $viewdata['owner'], 'type', $viewdata['type'])) {
                $viewdata['type'] = 'portfolio';
            }

            // Try to create the view with the owner's default theme if that theme is set by an
            // institution (i.e. if it's different from the site theme)
            //
            // This needs to be modified if users are ever allowed to change their own theme
            // preference.  Currently it's okay because users' themes are forced on them by
            // the site or institution default, but if some users are allowed to change their
            // own theme pref, we should create those users' views without a theme.
            if (!get_config('userscanchooseviewthemes') && !isset($viewdata['theme'])
                && (!isset($viewdata['type']) || $viewdata['type'] != 'dashboard')) {
                global $USER;
                if ($viewdata['owner'] == $USER->get('id')) {
                    $owner = $USER;
                }
                else {
                    $owner = new User();
                    $owner->find_by_id($viewdata['owner']);
                }
                $ownerthemedata = $owner->get('institutiontheme');
                $ownertheme = isset($ownerthemedata->basename) ? $ownerthemedata->basename : null;
                if ($ownertheme && $ownertheme != get_config('theme') && $ownertheme != 'custom') {
                    $viewdata['theme'] = $ownertheme;
                }
            }
        }

        if (isset($viewdata['group'])) {
            require_once('group.php');
            if (!group_user_can_edit_views($viewdata['group'], $userid)) {
                throw new SystemException("View::_create: User $userid is not permitted to create a view for group {$viewdata['group']}");
            }
        }

        if (isset($viewdata['institution'])) {
            $user = new User();
            $user->find_by_id($userid);
            if (!$user->can_edit_institution($viewdata['institution'])) {
                throw new SystemException("View::_create: User $userid is not permitted to create a view for institution {$viewdata['institution']}");
            }
        }

        // Create the view
        $defaultdata = array(
            'numcolumns'    => 2,
            'numrows'       => 1,
            'columnsperrow' => self::default_columnsperrow(),
            'template'      => 0,
            'type'          => 'portfolio',
            'title'         => (array_key_exists('title', $viewdata)) ? $viewdata['title'] : self::new_title(get_string('Untitled', 'view'), (object)$viewdata),
            'anonymise'     => 0,
        );

        $data = (object)array_merge($defaultdata, $viewdata);

        if ($data->type == 'portfolio' && (!isset($data->url) || is_null($data->url) || !strlen($data->url))) {
            $data->urlid = generate_urlid($data->title, get_config('cleanurlviewdefault'), 3, 100);
            $data->urlid = self::new_urlid($data->urlid, $data);
        }

        $view = new View(0, $data);
        $view->commit();
        if (isset($viewdata['group']) &&
            (empty($viewdata['type']) || (!empty($viewdata['type']) && $viewdata['type'] != 'grouphomepage'))
           ) {
            require_once('activity.php');

            // Although group views are owned by the group, the view creator is treated as owner here.
            // So we need to ignore them from the activity_occured email.
            $beforeusers[$userid] = get_record('usr', 'id', $userid);

            // By default, group views should be visible to the group
            insert_record('view_access', (object) array(
                'view'  => $view->get('id'),
                'group' => $viewdata['group'],
                'ctime' => db_format_timestamp(time()),
            ));

            // Notify group members
            $accessdata = new StdClass;
            $accessdata->view = $view->get('id');
            $accessdata->oldusers = $beforeusers;
            activity_occurred('viewaccess', $accessdata);
        }

        if (isset($viewdata['layout'])) {
            // e.g. importing via LEAP2A
            $layoutsrowscols = get_records_select_array('view_layout_rows_columns', 'viewlayout = ?', array($viewdata['layout']));
            if ($layoutsrowscols) {
                delete_records('view_rows_columns', 'view', $view->get('id'));
                foreach ($layoutsrowscols as $layoutrow) {
                    insert_record('view_rows_columns', (object)array( 'view' => $view->get('id'), 'row' => $layoutrow->row, 'columns' =>  self::$layoutcolumns[$layoutrow->columns]->columns));
                }
            }
        }

        return new View($view->get('id')); // Reread to ensure defaults are set
    }

    public function default_columnsperrow() {
        $default = array(1 => (object)array('row' => 1, 'columns' => 3, 'widths' => '33,33,33'));
        if (!$id = get_field('view_layout_columns', 'id', 'columns', $default[1]->columns, 'widths', $default[1]->widths)) {
            throw new SystemException("View::default_columnsperrow: Default columns = 3, widths = '33,33,33' not in view_layout_columns table");
        }
        return $default;
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'tags') { // special case
            return $this->get_tags();
        }
        if ($field == 'categorydata') {
            return $this->get_category_data();
        }
        if ($field == 'collection') {
            return $this->get_collection();
        }
        if ($field == 'columnsperrow') {
            return $this->get_columnsperrow();
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($this->{$field} != $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
            }
            $this->{$field} = $value;
            if ($field != 'atime') {
                // don't bother updating the modified time if we are
                // only wanting to update the accessed time
                $this->mtime = time();
            }
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    public function get_tags() {
        if (!isset($this->tags)) {
            $this->tags = get_column('view_tag', 'tag', 'view', $this->get('id'));
        }
        return $this->tags;
    }

    public function get_collection() {
        if (!isset($this->collection)) {
            require_once(get_config('libroot') . 'collection.php');
            $this->collection = Collection::search_by_view_id($this->id);
        }
        return $this->collection;
    }

    public function get_columnsperrow() {
        if (!isset($this->columnsperrow)) {
            $this->columnsperrow = get_records_assoc('view_rows_columns', 'view', $this->get('id'), 'row', 'row, columns');
        }
        return $this->columnsperrow;
    }

    public function collection_id() {
        if ($collection = $this->get_collection()) {
            return $collection->get('id');
        }
        return false;
    }

    /**
     * View destructor. Calls commit if necessary.
     *
     * A special case is when the object has just been deleted.  In this case,
     * we do nothing.
     */
    public function __destruct() {
        if ($this->deleted) {
            return;
        }

        if (!empty($this->dirty)) {
            return $this->commit();
        }
    }

    /**
     * This method updates the contents of the view table only.
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime', 'atime', 'startdate', 'stopdate', 'submittedtime')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }

        db_begin();

        if (empty($this->id)) {
            // users are only allowed one profile view
            if ($this->type == 'profile' && record_exists('view', 'owner', $this->owner, 'type', 'profile')) {
                throw new SystemException(get_string('onlonlyyoneprofileviewallowed', 'error'));
            }
            $this->id = insert_record('view', $fordb, 'id', true);
        }
        else {
            update_record('view', $fordb, 'id');
        }

        if (isset($this->tags)) {
            $this->tags = check_case_sensitive($this->tags, 'view_tag');
            delete_records('view_tag', 'view', $this->get('id'));
            foreach ($this->get_tags() as $tag) {
                //truncate the tag before insert it into the database
                $tag = substr($tag, 0, 128);
                insert_record('view_tag', (object)array( 'view' => $this->get('id'), 'tag' => $tag));
            }
        }

        if (isset($this->copynewgroups)) {
            delete_records('view_autocreate_grouptype', 'view', $this->get('id'));
            foreach ($this->copynewgroups as $grouptype) {
                insert_record('view_autocreate_grouptype', (object)array( 'view' => $this->get('id'), 'grouptype' => $grouptype));
            }
        }

        if (isset($this->columnsperrow)) {
            delete_records('view_rows_columns', 'view', $this->get('id'));
            foreach ($this->get_columnsperrow() as $viewrow) {
                insert_record('view_rows_columns', (object)array( 'view' => $this->get('id'), 'row' => $viewrow->row, 'columns' => $viewrow->columns));
            }
        }

        db_commit();

        $this->dirty = false;
        $this->deleted = false;
    }

    public function get_artefact_instances() {
        if (!isset($this->artefact_instances)) {
            $this->artefact_instances = false;
            if ($instances = $this->get_artefact_metadata()) {
                foreach ($instances as $instance) {
                    safe_require('artefact', $instance->plugin);
                    $classname = generate_artefact_class_name($instance->artefacttype);
                    $i = new $classname($instance->id, $instance);
                    $this->childreninstances[] = $i;
                }
            }
        }
        return $this->artefact_instances;
    }

    public function get_artefact_metadata() {
        if (!isset($this->artefact_metadata)) {
            $sql = 'SELECT a.*, i.name, va.block
                    FROM {view_artefact} va
                    JOIN {artefact} a ON va.artefact = a.id
                    JOIN {artefact_installed_type} i ON a.artefacttype = i.name
                    WHERE va.view = ?';
            $this->artefact_metadata = get_records_sql_array($sql, array($this->id));
        }
        return $this->artefact_metadata;
    }

    public function find_artefact_children($artefact, $allchildren, &$refs) {

        $children = array();
        if ($allchildren) {
            foreach ($allchildren as $child) {
                if ($child->parent != $artefact->id) {
                    continue;
                }
                $children[$child->id] = array();
                $children[$child->id]['artefact'] = $child;
                $refs[$child->id] = $child;
                $children[$child->id]['children'] = $this->find_artefact_children($child,
                                                            $allchildren, $refs);
            }
        }

        return $children;
    }


    public function has_artefacts() {
        if ($this->get_artefact_metadata()) {
            return true;
        }
        return false;
    }

    public function get_owner_object() {
        if (empty($this->owner)) {
            return false;
        }
        if (!isset($this->ownerobj)) {
            $this->ownerobj = get_user_for_display($this->get('owner'));
        }
        return $this->ownerobj;
    }

    public function get_group_object() {
        if (!isset($this->groupobj)) {
            $this->groupobj = get_record('group', 'id', $this->get('group'));
        }
        return $this->groupobj;
    }

    public function get_institution_object() {
        if (!isset($this->institutionobj)) {
            $this->institutionobj = get_record('institution', 'name', $this->get('institution'));
        }
        return $this->institutionobj;
    }

    public function delete() {
        safe_require('artefact', 'comment');
        db_begin();
        ArtefactTypeComment::delete_view_comments($this->id);
        delete_records('view_access','view',$this->id);
        delete_records('view_autocreate_grouptype', 'view', $this->id);
        delete_records('view_tag','view',$this->id);
        delete_records('view_visit','view',$this->id);
        delete_records('collection_view','view',$this->id);
        delete_records('usr_watchlist_view','view',$this->id);
        if ($blockinstanceids = get_column('block_instance', 'id', 'view', $this->id)) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            foreach ($blockinstanceids as $id) {
                $bi = new BlockInstance($id);
                $bi->delete();
            }
        }
        handle_event('deleteview', $this->id);
        delete_records('view_rows_columns', 'view', $this->id);
        delete_records('view','id',$this->id);
        if (!empty($this->owner) && $this->is_submitted()) {
            // There should be no way to delete a submitted view,
            // but unlock its artefacts just in case.
            ArtefactType::update_locked($this->owner);
        }
        $this->deleted = true;
        db_commit();
    }

    /* Only retrieve access records that the owner can edit on the
     * view access page.  Some records are not visible there, such as
     * tutor access records for submitted views and objectionable
     * content access records (visible = 0) and token/secret url
     * records which are managed per-view, on another page.
     */
    public function get_access($timeformat=null) {
        if ($data = $this->get_access_records()) {
            return $this->process_access_records($data, $timeformat);
        }
        return array();
    }

    public function get_access_records() {
        $data = get_records_sql_array("
            SELECT accesstype, va.group, institution, role, usr, startdate, stopdate, allowcomments, approvecomments
            FROM {view_access} va
            WHERE view = ? AND visible = 1 AND token IS NULL
            ORDER BY
                accesstype IS NULL, accesstype DESC,
                va.group, role IS NOT NULL, role,
                institution, usr,
                startdate IS NOT NULL, startdate, stopdate IS NOT NULL, stopdate,
                allowcomments, approvecomments",
            array($this->id)
        );
        return $data ? $data : array();
    }

    public function process_access_records($data=array(), $timeformat=null) {
        $rolegroups = array();
        foreach ($data as &$item) {
            if ($item->role && !isset($roledata[$item->group])) {
                $rolegroups[$item->group] = 1;
            }
        }
        if ($rolegroups) {
            $grouptypes = get_records_sql_assoc('
                SELECT id, grouptype
                FROM {group}
                WHERE id IN (' . join(',', array_map('intval', array_keys($rolegroups))) . ')
                AND deleted = 0',
                array()
            );
        }

        foreach ($data as &$item) {
            $item = (array)$item;
            $item['locked'] = false; // Indicate if item is editable
            if ($item['usr']) {
                $item['type'] = 'user';
                $item['id'] = $item['usr'];
            }
            else if ($item['group']) {
                $item['type'] = 'group';
                $item['id'] = $item['group'];
            }
            else if ($item['institution']) {
                $item['type'] = 'institution';
                $item['id'] = $item['institution'];

                if ($this->type == 'profile') {
                    $myinstitutions = array_keys(load_user_institutions($this->owner));
                    if (in_array($item['id'], $myinstitutions) && empty($item['startdate']) && empty($item['stopdate'])) {
                        $item['locked'] = true;
                    }
                }
            }
            else {
                $item['type'] = $item['accesstype'];
                $item['id'] = null;
            }

            if ($this->type == 'profile' && $item['type'] == 'loggedin' && get_config('loggedinprofileviewaccess')) {
                $item['locked'] = true;
            }

            if ($item['role']) {
                $item['roledisplay'] = get_string($item['role'], 'grouptype.'.$grouptypes[$item['group']]->grouptype);
            }
            if ($timeformat) {
                if ($item['startdate']) {
                    $item['startdate'] = strftime($timeformat, strtotime($item['startdate']));
                }
                if ($item['stopdate']) {
                    $item['stopdate'] = strftime($timeformat, strtotime($item['stopdate']));
                }
            }
        }
        return $data;
    }

    /* Attempt to sort two access records in the same order as the
       query in get_access_records */
    public static function cmp_accesslist($a, $b) {
        if (($c = empty($a->accesstype) - empty($b->accesstype))
            || ($c = strcmp($b->accesstype, $a->accesstype))
            || ($c = $a->group - $b->group)
            || ($c = !empty($a->role) - !empty($b->role))
            || ($c = strcmp($a->role, $b->role))
            || ($c = !empty($a->institution) - !empty($b->institution))
            || ($c = strcmp($a->institution, $b->institution))
            || ($c = $a->usr - $b->usr)
            || ($c = !empty($a->startdate) - !empty($b->startdate))
            || ($c = strcmp($a->startdate, $b->startdate))
            || ($c = !empty($a->stopdate) - !empty($b->stopdate))
            || ($c = strcmp($a->stopdate, $b->stopdate))
            || ($c = $a->allowcomments - $b->allowcomments)) {
            return $c;
        }
        return $a->approvecomments - $b->approvecomments;
    }

    public static function update_view_access($config, $viewids) {

        db_begin();

        // Use set_access() on the first view to get a hopefully consistent
        // and complete representation of the access list
        $firstview = new View($viewids[0]);
        $fullaccesslist = $firstview->set_access($config['accesslist']);

        // Copy the first view's access records to all the other views
        $firstview->copy_access($viewids);

        // Sort the full access list in the same order as the list
        // returned by get_access, so that views with the same set of
        // access records get grouped together
        usort($fullaccesslist, array('self', 'cmp_accesslist'));

        // Hash the config object so later on we can easily find
        // all the views with the same config/access rights
        $config['accesslist'] = $fullaccesslist;
        $accessconf = substr(md5(serialize($config)), 0, 10);

        foreach ($viewids as $viewid) {
            $v = new View((int) $viewid);
            $v->set('startdate', $config['startdate']);
            $v->set('stopdate', $config['stopdate']);
            $v->set('template', $config['template']);
            $v->set('retainview', $config['retainview']);
            $v->set('allowcomments', $config['allowcomments']);
            $v->set('approvecomments', $config['approvecomments']);
            if (isset($config['copynewuser'])) {
                $v->set('copynewuser', $config['copynewuser']);
            }
            if (isset($config['copynewgroups'])) {
                $v->set('copynewgroups', $config['copynewgroups']);
            }
            $v->set('accessconf', $accessconf);
            $v->commit();
        }

        db_commit();
    }

    /*Return preview image for creation of custom layout
     */
    public function updatecustomlayoutpreview($values) {
        global $THEME;
        require_once(get_config('libroot') . 'layoutpreviewimage.php');

        $require = array('numrows');
        foreach ($require as $require) {
            if (!array_key_exists($require, $values) || empty($values[$require])) {
                throw new ParamOutOfRangeException(get_string('missingparam' . $require, 'error'));
            }
        }

        $numrows = $values['numrows'];
        $collayouts = array();
        for ($i=0; $i<$numrows; $i++) {
            if (array_key_exists('row'. ($i+1), $values)) {
                $collayouts['row' . ($i+1)] = $values['row' . ($i+1)];
            }
        }

        $previewimage = 'vl-';
        $alttext = '';
        $customlayout = array();
        for ($i=0; $i<$numrows; $i++) {
            $id = $collayouts['row' . ($i+1)];
            $widths = get_field('view_layout_columns', 'widths', 'id', $id);
            $hyphenatedwidths = str_replace(',', '-', $widths);
            $customlayout[$i+1] = $hyphenatedwidths;
            $previewimage .= $hyphenatedwidths;
            $alttext .= $hyphenatedwidths;
            if ($i != $numrows -1) {
                $previewimage .= '_';
                $alttext .= ' / ';
            }
        }

        if (LayoutPreviewImage::preview_exists($previewimage)) {
            $img = get_config('wwwroot') . 'thumb.php?type=customviewlayout&cvl=' . $previewimage;
            $data = array('data' => $img, 'alttext' => $alttext, 'newimage' => 0);
            return $data;
        }
        else {
            // generate thumbnail images with GD
            $data= array();
            $data['layout'] = $customlayout;
            $data['description'] = 'test';
            $data['owner'] = 1;

            $previewlayoutimage = new LayoutPreviewImage($data);
            $newpreviewimage = $previewlayoutimage->create_preview();

            if ($newpreviewimage) {
                $img = get_config('wwwroot') . 'thumb.php?type=customviewlayout&cvl=' . $previewimage;
                $data = array('data' => $img, 'alttext' => $alttext, 'newimage' => 1);
                return $data;
            }
            else {
                $msg = '<p>' . get_string('previewimagegenerationfailed', 'error') . '</p>';
                $data = array('data' => $msg, 'alttext' => $alttext, 'newimage' => 0);
                return $data;
            }
        }
    }

    public function addcustomlayout($values) {
        global $THEME;
        $require = array('numrows');
        foreach ($require as $require) {
            if (!array_key_exists($require, $values) || empty($values[$require])) {
                throw new ParamOutOfRangeException(get_string('missingparam' . $require, 'error'));
            }
        }

        $numrows = $values['numrows'];
        $alttext = '';
        $rowscolssql = '';
        $rowscols = array();
        $resultids = array();
        $layoutid = 0;

        for ($i=0; $i<$numrows; $i++) {
            if (array_key_exists('row'. ($i+1), $values)) {
                $rowscolssql .= '(row = ' . ($i+1) . ' AND columns = ' . $values['row' . ($i+1)] . ')';
                if ($i != $numrows-1) {
                    $rowscolssql .= ' OR ';
                }
                $widths = get_field('view_layout_columns', 'widths', 'id', $values['row' . ($i+1)]);
                $hyphenatedwidths = str_replace(',', '-', $widths);
                $alttext .= $hyphenatedwidths;
                if ($i != $numrows -1) {
                    $alttext .= ' / ';
                }
                $rowscols[$i+1] = $values['row' . ($i+1)];
            }
        }

        $owner = $this->owner;
        $group = $this->group;
        $institution = $this->institution;
        if (!empty($group)) {
            $owner = null;
            $andclause = 'AND ucl.group = ?';
            $andclausevalue = $group;
        }
        else if (!empty($institution)) {
            $owner = null;
            $andclause = 'AND ucl.institution = ?';
            $andclausevalue = $institution;
        }
        else if (isset($owner)) {
            $andclause = 'AND ucl.usr = ?';
            $andclausevalue = $owner;
        }
        else {
            // no group or owner or institution set
            // site pages should have institution set
            throw new SystemException("View::addcustomlayout: No owner, group or institution set for view.");
        }

        // check for existing layout
        $sql = 'SELECT vlrc.viewlayout AS id
                FROM
                {view_layout} vl
                INNER JOIN {view_layout_rows_columns} vlrc
                ON vl.id = vlrc.viewlayout
                INNER JOIN (
                    SELECT
                    viewlayout, COUNT(*)
                    FROM {view_layout_rows_columns}
                    GROUP BY viewlayout
                    HAVING COUNT(*) = ?
                    ) vlrc2
                ON vlrc.viewlayout = vlrc2.viewlayout
                INNER JOIN {usr_custom_layout} ucl
                ON ucl.layout = vl.id
                WHERE (' . $rowscolssql . ')
                AND (
                   vl.iscustom = 0
                   OR (
                       vl.iscustom = 1 ' . $andclause . '
                      )
                )
                GROUP BY vlrc.viewlayout
                HAVING count(*) = ?
                LIMIT 1';
        $layoutids = get_records_sql_array($sql, array($numrows, $andclausevalue, $numrows));

        if ($layoutids) {
            $data = array('layoutid' => $layoutids[0]->id, 'newlayout' => 0);
            return $data;
        }
        else {

            db_begin();
            // no existing layout of this kind, create it
            $newlayoutid = insert_record('view_layout', (object) array('rows' => $numrows, 'iscustom' => 1), 'id', true);
            if (!$newlayoutid) {
                db_rollback();
                throw new SystemException("View::addcustomlayout: Couldn't create new layout record.");
            }

            if (isset($owner)) {
                $newcustomlayout = insert_record('usr_custom_layout', (object) array('usr' => $owner, 'group' => null, 'institution' => null, 'layout' => $newlayoutid));
            }
            else if (isset($group)) {
                $newcustomlayout = insert_record('usr_custom_layout', (object) array('usr' => null, 'group' => $group, 'institution' => null, 'layout' => $newlayoutid));
            }
            else if (isset($institution)) {
                $newcustomlayout = insert_record('usr_custom_layout', (object) array('usr' => null, 'group' => null, 'institution' => $institution, 'layout' => $newlayoutid));
            }
            if (!$newcustomlayout) {
                db_rollback();
                throw new SystemException("View::addcustomlayout: Couldn't create new usr custom layout record.");
            }

            for ($i=0; $i<$numrows; $i++) {
                if (array_key_exists(($i+1), $rowscols)) {
                    $numcols = get_field('view_layout_columns', 'columns', 'id', $rowscols[$i+1]);
                    $newrec = insert_record('view_layout_rows_columns', (object) array('viewlayout' => $newlayoutid, 'row' => ($i+1), 'columns' => $rowscols[$i+1]));
                    if (!$newrec) {
                        db_rollback();
                        throw new SystemException("View::addcustomlayout: Couldn't create new vlrc record.");
                    }
                }
            }

            db_commit();
            $data = array('layoutid' => $newlayoutid, 'newlayout' => 1, 'alttext' => $alttext);
            return $data;
        }
    }

    /**
     * Returns true if the view is currently marked as objectionable
     *
     * @return boolean True if view is objectionable
     */
    public function is_objectionable() {
        $params = array('view', $this->id);
        return record_exists_select('objectionable', 'objecttype = ? AND objectid = ? AND resolvedby IS NULL', $params);
    }

    public function is_public() {
        $accessrecords = self::user_access_records($this->id, 0);
        if (!$accessrecords) {
            return false;
        }

        foreach($accessrecords as &$a) {
            if ($a->accesstype == 'public') {
                return true;
            }
        }
        return false;
    }

    public function set_access($accessdata) {
        global $USER;
        require_once('activity.php');
        require_once('group.php');
        require_once('institution.php');

        $beforeusers = activity_get_viewaccess_users($this->get('id'));

        $select = 'view = ? AND visible = 1 AND token IS NULL';

        db_begin();
        delete_records_select('view_access', $select, array($this->id));

        // View access
        $accessdata_added = array();
        if ($accessdata) {
            /*
             * There should be a cleaner way to do this
             * $accessdata_added ensures that the same access is not granted twice because the profile page
             * gets very grumpy if there are duplicate access rules
             *
             * Additional rules:
             * - Don't insert records with stopdate in the past
             * - Remove startdates that are in the past
             * - If view allows comments, access record comment permissions, don't apply, so reset them.
             * @todo: merge overlapping date ranges.
             */
            $time = time();
            foreach ($accessdata as $item) {

                if (!empty($item['stopdate']) && $item['stopdate'] < $time) {
                    continue;
                }
                if (!empty($item['startdate']) && $item['startdate'] < $time) {
                    unset($item['startdate']);
                }
                if ($this->get('allowcomments')) {
                    unset($item['allowcomments']);
                    unset($item['approvecomments']);
                }

                $accessrecord = (object)array(
                    'accesstype'      => null,
                    'group'           => null,
                    'role'            => null,
                    'institution'     => null,
                    'usr'             => null,
                    'token'           => null,
                    'startdate'       => null,
                    'stopdate'        => null,
                    'allowcomments'   => 0,
                    'approvecomments' => 1,
                    'ctime'           => db_format_timestamp(time()),
                );

                switch ($item['type']) {
                case 'user':
                    $accessrecord->usr = $item['id'];
                    break;
                case 'group':
                    $accessrecord->group = $item['id'];
                    if (isset($item['role']) && strlen($item['role'])) {
                        // Don't insert a record for a role the group doesn't have
                        $roleinfo = group_get_role_info($item['id']);
                        if (!isset($roleinfo[$item['role']])) {
                            break;
                        }
                        $accessrecord->role = $item['role'];
                    }
                    break;
                case 'institution':
                    $accessrecord->institution = $item['id'];
                    break;
                case 'friends':
                    if (!$this->owner) {
                        continue; // Don't add friend access to group, institution or system views
                    }
                case 'public':
                case 'loggedin':
                    $accessrecord->accesstype = $item['type'];
                }

                if (isset($item['allowcomments'])) {
                    $accessrecord->allowcomments = (int) !empty($item['allowcomments']);
                    if ($accessrecord->allowcomments) {
                        $accessrecord->approvecomments = (int) !empty($item['approvecomments']);
                    }
                }
                if (isset($item['startdate'])) {
                    $accessrecord->startdate = db_format_timestamp($item['startdate']);
                }
                if (isset($item['stopdate'])) {
                    $accessrecord->stopdate  = db_format_timestamp($item['stopdate']);
                }

                if (array_search($accessrecord, $accessdata_added) === false) {
                    $accessrecord->view = $this->get('id');
                    insert_record('view_access', $accessrecord);
                    unset($accessrecord->view);
                    $accessdata_added[] = $accessrecord;
                }
            }
        }

        $data = new StdClass;
        $data->view = $this->get('id');
        $data->oldusers = $beforeusers;
        activity_occurred('viewaccess', $data);
        handle_event('saveview', $this->get('id'));

        db_commit();
        return $accessdata_added;
    }

    /**
     * Apply all the access rules among a set of views to every view in
     * the set.
     */
    public static function combine_access($viewids) {
        if (empty($viewids)) {
            return;
        }

        $select = 'view IN (' . join(',', array_map('intval', $viewids)) . ') AND visible = 1';

        if (!$access = get_records_select_array('view_access', $select)) {
            return;
        }

        $unique = array();
        foreach ($access as &$a) {
            unset($a->view);
            $k = serialize($a);
            if (!isset($unique[$k])) {
                $unique[$k] = $a;
            }
        }

        db_begin();

        delete_records_select('view_access', $select);

        foreach ($unique as &$a) {
            foreach ($viewids as $id) {
                $a->view = $id;
                $a->ctime = db_format_timestamp(time());
                insert_record('view_access', $a);
            }
        }

        db_commit();
    }

    /**
     * Copy access records from one view to a set of other views
     */
    public function copy_access($to) {
        if (empty($this->id)) {
            return;
        }

        $toupdate = array();
        foreach ($to as $viewid) {
            if ($this->id != $viewid) {
                $toupdate[] = (int) $viewid;
            }
        }

        if (empty($toupdate)) {
            return;
        }

        $firstviewaccess = get_records_select_array(
            'view_access',
            'view = ? AND visible = 1 AND token IS NULL',
            array($this->id)
        );

        db_begin();
        delete_records_select(
            'view_access',
            'view IN (' . join(',', $toupdate) . ') AND visible = 1 AND token IS NULL'
        );

        if ($firstviewaccess) {
            foreach ($toupdate as $id) {
                foreach ($firstviewaccess as &$a) {
                    $a->view = $id;
                    $a->ctime = db_format_timestamp(time());
                    insert_record('view_access', $a);
                }
            }
        }
        db_commit();
    }

    public function add_access($access) {
        if (!$this->id) {
            return false;
        }

        // Ensure view is correct
        $access->view = $this->id;
        $whereobject = clone $access;
        unset($whereobject->ctime);
        // Add ctime if needing to insert row
        if (!isset($access->ctime)) {
            $access->ctime = db_format_timestamp(time());
        }
        ensure_record_exists('view_access', $whereobject, $access);
    }

    public function add_owner_institution_access($instnames=array()) {
        if (!$this->id) {
            return false;
        }

        $institutions = empty($instnames) ? array_keys(load_user_institutions($this->owner)) : $instnames;
        if (!empty($institutions)) {
            db_begin();
            foreach ($institutions as $i) {
                $exists = record_exists_select(
                    'view_access',
                    'view = ? AND institution = ? AND startdate IS NULL AND stopdate IS NULL',
                    array($this->id, $i)
                );

                if (!$exists) {
                    $vaccess = new stdClass;
                    $vaccess->view = $this->id;
                    $vaccess->institution = $i;
                    $vaccess->startdate = null;
                    $vaccess->stopdate = null;
                    $vaccess->allowcomments = 0;
                    $vaccess->approvecomments = 1;
                    $vaccess->ctime = db_format_timestamp(time());

                    insert_record('view_access', $vaccess);
                }
            }
            db_commit();
        }

        return true;
    }

    public function get_autocreate_grouptypes() {
        if (!isset($this->copynewgroups)) {
            $this->copynewgroups = get_column('view_autocreate_grouptype', 'grouptype', 'view', $this->id);
        }
        return $this->copynewgroups;
    }

    public function is_submitted() {
        return $this->get('submittedgroup') || $this->get('submittedhost');
    }

    public function submitted_to() {
        if ($group = $this->get('submittedgroup')) {
            return array('type' => 'group', 'id' => $group, 'name' => get_field('group', 'name', 'id', $group));
        }
        if ($host = $this->get('submittedhost')) {
            return array('type' => 'host', 'wwwroot' => $host, 'name' => get_field('host', 'name', 'wwwroot', $host));
        }
        return null;
    }

    public function pendingrelease($releaseuser=null) {
        $submitinfo = $this->submitted_to();
        if (is_null($submitinfo)) {
            throw new ParameterException("View with id " . $this->get('id') . " has not been submitted");
        }
        db_begin();
        self::_db_pendingrelease(array($this->get('id')));
        require_once(get_config('docroot') . 'export/lib.php');
        add_submission_to_export_queue($this, $releaseuser);
        db_commit();
    }

    public function release($releaseuser=null) {
        $submitinfo = $this->submitted_to();
        if (is_null($submitinfo)) {
            throw new ParameterException("View with id " . $this->get('id') . " has not been submitted");
        }
        $releaseuser = optional_userobj($releaseuser);

        self::_db_release(array($this->id), $this->get('owner'), $this->get('submittedgroup'));

        $ownerlang = get_user_language($this->get('owner'));
        $url = $this->get_url(false);
        require_once('activity.php');
        activity_occurred('maharamessage',
            array(
                'users' => array($this->get('owner')),
                'subject' => get_string_from_language($ownerlang, 'viewreleasedsubject', 'group', $this->get('title'),
                    $submitinfo['name'], display_name($releaseuser, $this->get_owner_object())),
                'message' => get_string_from_language($ownerlang, 'viewreleasedmessage', 'group', $this->get('title'),
                    $submitinfo['name'], display_name($releaseuser, $this->get_owner_object())),
                'url' => $url,
                'urltext' => $this->get('title'),
            )
        );
    }

    public static function _db_pendingrelease(array $viewids) {
        $idstr = join(',', array_map('intval', $viewids));
        execute_sql("UPDATE {view}
                     SET submittedstatus = " . self::PENDING_RELEASE . "
                     WHERE id IN ($idstr)",
                     array()
        );
    }

    public static function _db_release(array $viewids, $owner, $group=null) {
        require_once(get_config('docroot') . 'artefact/lib.php');

        if (empty($viewids) || empty($owner)) {
            return;
        }
        $idstr = join(',', array_map('intval', $viewids));
        $owner = intval($owner);

        db_begin();
        execute_sql("
            UPDATE {view}
            SET submittedgroup = NULL,
                submittedhost = NULL,
                submittedtime = NULL,
                submittedstatus = " . self::UNSUBMITTED . "
            WHERE id IN ($idstr) AND owner = ?",
            array($owner)
        );
        if (!empty($group)) {
            // Remove hidden tutor view access records
            delete_records_select(
                'view_access',
                "view IN ($idstr) AND visible = 0 AND \"group\" = ?",
                array(intval($group))
            );
        }
        ArtefactType::update_locked($owner);
        db_commit();
    }

    /**
     * Returns HTML for the category list
     *
     * @param string $category The currently selected category
    */
    public function build_category_list($category, $new=0) {
        $categories = $this->get_category_data();
        $flag = false;
        foreach ($categories as &$cat) {
            $classes = '';
            if (!$flag) {
                $flag = true;
                $classes[] = 'first';
            }
            if ($category == $cat['name']) {
                $classes[] = 'current';
            }
            if ($classes) {
                $cat['class'] = hsc(implode(' ', $classes));
            }
        }

        // Because of the reference in the above loop, $cat refers to the last item
        $cat['class'] = (isset($cat['class'])) ? $cat['class'] . ' last' : 'last';

        $blocktypelist = $this->build_blocktype_list($category);
        $smarty = smarty_core();
        $smarty->assign('categories', $categories);
        $smarty->assign('selectedcategory', $category);
        $smarty->assign('blocktypelist', $blocktypelist);
        $smarty->assign('viewid', $this->get('id'));
        $smarty->assign('new', $new);
        return $smarty->fetch('view/blocktypecategorylist.tpl');
    }

    /**
     * Gets the name of the first blocktype category for this View.
     *
     * This can change based on what blocktypes allow themselves to be in what
     * types of View. For example, in a group View, blog blocktypes aren't
     * allowed (yet), so the first blocktype category shown won't be "blog"
     */
    public function get_default_category() {
        $data = $this->get_category_data();
        $first  = reset($data);
        return $first['name'];
    }

    /**
     * Gets information about blocktype categories for blocks that can be put
     * in this View
     *
     * For each category, returns its name, a localised title and the number of
     * blocktypes in the category that can be put in this View.
     *
     * If a category has no blocktypes that can be put in this View, it is not
     * returned
     */
    private function get_category_data() {
        if (isset($this->category_data)) {
            return $this->category_data;
        }

        require_once(get_config('docroot') . '/blocktype/lib.php');
        $categories = array();
        $sql = 'SELECT bic.*, bc.sort FROM {blocktype_installed_category} bic
            JOIN {blocktype_installed} bi ON (bic.blocktype = bi.name AND bi.active = 1)
            JOIN {blocktype_installed_viewtype} biv ON (bi.name = biv.blocktype AND biv.viewtype = ?)
            JOIN {blocktype_category} bc ON (bic.category = bc.name)
            ORDER BY bc.sort';
        if (function_exists('local_get_allowed_blocktype_categories')) {
            $localallowed = local_get_allowed_blocktype_categories($this);
        }
        $blockcategories = get_records_sql_array($sql, array($this->get('type')));
        foreach ($blockcategories as $blocktypecategory) {
            if (isset($localallowed) && is_array($localallowed) && !in_array($blocktypecategory->category, $localallowed)) {
                continue;
            }
            if (!safe_require_plugin('blocktype', $blocktypecategory->blocktype)) {
                continue;
            }
            if (call_static_method(generate_class_name("blocktype", $blocktypecategory->blocktype), "allowed_in_view", $this)) {
                if (!isset($categories[$blocktypecategory->sort])) {
                    $categories[$blocktypecategory->sort] = array(
                        'name'  => $blocktypecategory->category,
                        'title' => call_static_method("PluginBlocktype", "category_title_from_name", $blocktypecategory->category),
                        'description' => call_static_method("PluginBlocktype", "category_description_from_name", $blocktypecategory->category),
                    );
                }
            }
        }

        return $this->category_data = $categories;
    }

    /**
     * Returns HTML for the blocktype list for a particular category
     *
     * @param string $category   The category to build the blocktype list for
     * @param bool   $javascript Set to true if the caller is a json script,
     *                           meaning that nothing for the standard HTML version
     *                           alone should be output
     */
    public function build_blocktype_list($category, $javascript=false) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $blocktypes = PluginBlockType::get_blocktypes_for_category($category, $this);

        $smarty = smarty_core();
        $smarty->assign_by_ref('blocktypes', $blocktypes);
        $smarty->assign('javascript', $javascript);
        return $smarty->fetch('view/blocktypelist.tpl');
    }

    /**
     * Process view changes. This function is used both by the json stuff and
     * by normal posts
     */
    public function process_changes($category='', $new=0) {
        global $SESSION, $USER;

        // Security
        // TODO this might need to be moved below the requestdata check below, to prevent non owners of the view being
        // rejected
        if (!$USER->can_edit_view($this)) {
            throw new AccessDeniedException(get_string('canteditdontown', 'view'));
        }

        if (!count($_POST) && count($_GET) < 3) {
            return;
        }

        $action = '';
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 7) == 'action_') {
                $action = substr($key, 7);
                break;
            }
            else if (substr($key, 0, 37) == 'cancel_action_configureblockinstance_'
                     && param_integer('removeoncancel', 0)) {
                $action = 'removeblockinstance_' . substr($key, 37);
                break;
            }
        }
        // TODO Scan GET for an action. The only action that is GETted is
        // confirming deletion of a blockinstance. It _should_ be a POST, but
        // that can be fixed later.
        if (!$action) {
            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 7) == 'action_') {
                    $action = substr($key, 7);
                }
            }
        }

        $viewtheme = param_variable('viewtheme', '');
        if ($viewtheme && $viewtheme != $this->get('theme')) {
            $action = 'changetheme';
            $values = array('theme' => $viewtheme);
        }

        if (empty($action)) {
            return;
        }

        form_validate(param_alphanum('sesskey', null));

        if (!isset($values)) {
            $actionstring = $action;
            $action = substr($action, 0, strpos($action, '_'));
            $actionstring  = substr($actionstring, strlen($action) + 1);

            // Actions from <input type="image"> buttons send an _x and _y
            if (substr($actionstring, -2) == '_x' || substr($actionstring, -2) == '_y') {
                $actionstring = substr($actionstring, 0, -2);
            }

            $values = self::get_values_for_action($actionstring);
        }

        $result = null;
        switch ($action) {
            // the view class method is the same as the action,
            // but I've left these here in case any additional
            // parameter handling has to be done.
            case 'addblocktype': // requires action_addblocktype  (blocktype in separate parameter)
                $values['blocktype'] = param_alpha('blocktype', null);
            break;
            case 'removeblockinstance': // requires action_removeblockinstance_id_\d
                if (!defined('JSON')) {
                    if (!$sure = param_boolean('sure')) {
                        $yesform = '<form action="' . get_config('wwwroot') . '/view/blocks.php" class="inline">'
                            . '<input type="hidden" name="id" value="' . $this->get('id') . '">'
                            . '<input type="hidden" name="c" value="file">'
                            . '<input type="hidden" name="action_' . $action . '_' .  $actionstring . '" value="1">'
                            . '<input type="hidden" name="sure" value="1">'
                            . '<input type="hidden" name="sesskey" value="' . $USER->get('sesskey') . '">'
                            . '<input type="submit" class="submit" name="removeblock_submit" value="' . get_string('yes') . '">'
                            . '</form>';
                        $baselink = get_config('wwwroot') . 'view/blocks.php?id=' . $this->get('id') . '&c=' . $category . '&new=' . $new;
                        $SESSION->add_info_msg(get_string('confirmdeleteblockinstance', 'view')
                            . '&nbsp;' . $yesform . ' <a href="' . $baselink . '">' . get_string('no') . '</a>', false);
                        redirect($baselink);
                        exit;
                    }
                }
            break;
            case 'configureblockinstance': // requires action_configureblockinstance_id_\d_column_\d_order_\d
            case 'acsearch': // requires action_acsearch_id_\d
                if (!defined('JSON')) {
                    $this->blockinstance_currently_being_configured = $values['id'];
                    // And we're done here for now
                    return;
                }
            case 'moveblockinstance': // requires action_moveblockinstance_id_\d_row_\d_column_\d_order_\d
            case 'addcolumn': // requires action_addcolumn_\d_row_\d_before_\d
            case 'removecolumn': // requires action_removecolumn_\d_row_\d_column_\d
            case 'changetheme':
            case 'updatecustomlayoutpreview':
            case 'addcustomlayout':
            break;
            default:
                throw new InvalidArgumentException(get_string('noviewcontrolaction', 'error', $action));
        }

        $message = '';
        $success = false;
        try {
            $values['returndata'] = defined('JSON');
            $returndata = $this->$action($values);

            // Tell the watchlist that the view changed
            $data = (object)array(
                'view' => $this->get('id'),
            );

            if (!defined('JSON')) {
                $message = $this->get_viewcontrol_ok_string($action);
            }
            $success = true;
        }
        catch (Exception $e) {
            // if we're in ajax land, just throw it
            // the handler will deal with the message.
            if (defined('JSON')) {
                throw $e;
            }
            $message = $this->get_viewcontrol_err_string($action) . ': ' . $e->getMessage();
        }
        if (!defined('JSON')) {
            // set stuff in the session and redirect
            $fun = 'add_ok_msg';
            if (!$success) {
                $fun = 'add_error_msg';
            }
            $SESSION->{$fun}($message);
            redirect('/view/blocks.php?id=' . $this->get('id') . '&c=' . $category . '&new=' . $new);
        }
        return array('message' => $message, 'data' => $returndata);
    }

    /**
     * Parses the string and returns a hash of values
     *
     * @param string $action expects format name_value_name_value
     *                       where values are all numeric
     * @return array associative
    */
    private static function get_values_for_action($action) {
        $values = array();
        $bits = explode('_', $action);
        if ((count($bits) % 2) == 1) {
            throw new ParamOutOfRangeException(get_string('invalidviewaction', 'error', $action));
        }
        $lastkey = null;
        foreach ($bits as $index => $bit) {
            if ($index % 2 == 0) {
                $lastkey = $bit;
            }
            else {
                $values[$lastkey] = $bit;
            }
        }
        return $values;
    }

    /**
    * builds up the data structure for  this view
    * @param boolean $force force a re-read from the database
    *                       use this if a column is dirty
    * @private
    * @return void
    */
    private function build_column_datastructure($row, $force=false) {
        if (!empty($this->columns[$row]) && empty($force)) { // we've already built it up
            return;
        }

        $sql = 'SELECT bi.*
            FROM {block_instance} bi
            WHERE bi.view = ?
            AND bi.row = ?
            ORDER BY bi.column, bi.order';
        if (!$data = get_records_sql_array($sql, array($this->get('id'), $row))) {
            $data = array();
        }

        // fill up empty columns array keys
        $columnsperrow = $this->get('columnsperrow');
        $numcolumnsthisrow = $columnsperrow[$row]->columns;

        for ($i = 1; $i <= $numcolumnsthisrow; $i++) {
            $this->columns[$row][$i] = array('blockinstances' => array());
        }

        // Set column widths
        // This often returns the default layout for number of rows, as layout is often null at this point
        $layout = $this->get_layout();
        $i = 0;
        foreach (explode(',', $layout->rows[$row]['widths']) as $width) {
            $this->columns[$row][++$i]['width'] = $width;
        }

        foreach ($data as $block) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            $block->view_obj = $this;
            $b = new BlockInstance($block->id, (array)$block);
            $this->columns[$row][$block->column]['blockinstances'][] = $b;
        }

    }

    /*
    *
    * wrapper around get_column_datastructure
    * returns all rows
    * @return mixed array
    */
    public function get_row_datastructure() {
        $rowdata = array();
        // make sure we've already built up the structure
        for ($i = 1; $i <= $this->numrows; $i++) {
            $force = false;
            if (array_key_exists($i, $this->dirtycolumns) || array_key_exists($i, $this->dirtyrows)) {
                $force = true;
            }
            $this->build_column_datastructure($i, $force);
            $rowdata[$i] = $this->columns[$i];
        }
        return $rowdata;
    }

    /*
    *
    * @param int $column optional, defaults to returning all columns
    * @return mixed array
    */
    public function get_column_datastructure($row=1, $column=0) {
        // make sure we've already built up the structure
        $force = false;
        if (isset($this->dirtycolumns[$row]) && array_key_exists($column, $this->dirtycolumns[$row])) {
            $force = true;
        }
        $this->build_column_datastructure($row, $force);

        if (empty($column)) {
            return $this->columns[$row];
        }

        if (!array_key_exists($column, $this->columns[$row])) {
            throw new ParamOutOfRangeException(get_string('invalidcolumn', 'view', $column));
        }


        return $this->columns[$row][$column];
    }

    // ******** functions to do with the view creation ui ************** //

    /**
     * small wrapper around get_string to return a success string
     * for the given view control function
     * @param string $functionname the functionname that was called
     */
    public function get_viewcontrol_ok_string($functionname) {
        return get_string('success.' . $functionname, 'view');
    }

    /**
     * small wrapper around get_string to return an error string
     * for the given view control function
     * @param string $functionname the functionname that was called
     */
    public function get_viewcontrol_err_string($functionname) {
        return get_string('err.' . $functionname, 'view');
    }


    /**
     * Build_rows - for each row build_columms
     * Returns the HTML for the rows of this view
     */
    public function build_rows($editing=false) {
        $numrows = $this->get('numrows');
        $result = '';
        for ($i = 1; $i <= $numrows; $i++) {
            $result .= $this->build_columns($i, $editing);
        }
        return $result;
    }

    /**
     * Returns the HTML for the columns of this view
     */
    public function build_columns($row, $editing=false) {
        global $USER;
        $columnsperrow = $this->get('columnsperrow');
        $currentrownumcols = $columnsperrow[$row]->columns;

        $result = '';
        for ($i = 1; $i <= $currentrownumcols; $i++) {
            $result .= $this->build_column($row, $i, $editing);
        }

        $smarty = smarty_core();
        $smarty->assign('javascript',  defined('JSON'));
        $smarty->assign('row',         $row);
        $smarty->assign('numcolumns',  $currentrownumcols);
        $smarty->assign('rowcontent',  $result);
        $smarty->assign('addremovecolumns', $USER->get_account_preference('addremovecolumns'));

        if ($editing) {
            // TODO look into this - necessary?
            return $smarty->fetch('view/rowediting.tpl');
        }
        return $smarty->fetch('view/rowviewing.tpl');
    }

    /**
     * Returns the HTML for a particular column
     *
     * @param int $column   The column to build
     * @param int $editing  Whether the view is being built in edit mode
     */
    public function build_column($row, $column, $editing=false) {
        global $USER;
        $data = $this->get_column_datastructure($row, $column);
        static $installed = array();
        if (empty($installed)) {
            $installed = plugins_installed('blocktype');
            $installed = array_map(create_function('$a', 'return $a->name;'), $installed);
        }

        if ($editing) {
            $renderfunction = 'render_editing';
        }
        else {
            $renderfunction = 'render_viewing';
        }
        $blockcontent = '';
        foreach($data['blockinstances'] as $blockinstance) {
            if (!in_array($blockinstance->get('blocktype'), $installed)) {
                continue; // this plugin has been disabled
            }
            $result = $blockinstance->$renderfunction();
            if ($editing) {
                $blockcontent .= $result['html'];
                // NOTE: build_column is always called in the context of column
                // operations, so the javascript returned, which is currently
                // for configuring block instances only, is not necessary
            }
            else {
                $blockcontent .= $result;
            }
        }

        $columnsperrow = $this->get('columnsperrow');
        $thisrownumcolumns = $columnsperrow[$row]->columns;

        $smarty = smarty_core();
        $smarty->assign('javascript',  defined('JSON'));
        $smarty->assign('column',      $column);
        $smarty->assign('row',         $row);
        $smarty->assign('numcolumns',  $thisrownumcolumns);
        $smarty->assign('blockcontent', $blockcontent);

        if (isset($data['width'])) {
            $smarty->assign('width', intval($data['width']));
        }

        $smarty->assign('addremovecolumns', $USER->get_account_preference('addremovecolumns'));

        if ($editing) {
            return $smarty->fetch('view/columnediting.tpl');
        }
        return $smarty->fetch('view/columnviewing.tpl');
    }

    /**
     * adds a block with the given type to a view
     *
     * @param array $values parameters for this function
     *                      blocktype => string name of blocktype to add
     *                      column    => int column to add to
     *                      order     => position in column
     *
     */
    public function addblocktype($values) {
        $requires = array('blocktype', 'row', 'column', 'order');
        foreach ($requires as $require) {
            if (!array_key_exists($require, $values) || empty($values[$require])) {
                throw new ParamOutOfRangeException(get_string('missingparam'. $require, 'error'));
            }
        }

        safe_require('blocktype', $values['blocktype']);
        if (!call_static_method(generate_class_name('blocktype', $values['blocktype']), 'allowed_in_view', $this)) {
            throw new UserException('[translate] Cannot put ' . $values['blocktype'] . ' blocktypes into this view');
        }

        if (call_static_method(generate_class_name('blocktype', $values['blocktype']), 'single_only', $this)) {
            $count = count_records_select('block_instance', '"view" = ? AND blocktype = ?',
                                          array($this->id, $values['blocktype']));
            if ($count > 0) {
                throw new UserException(get_string('onlyoneblocktypeperview', 'error', $values['blocktype']));
            }
        }

        $blocktypeclass = generate_class_name('blocktype', $values['blocktype']);
        $newtitle = method_exists($blocktypeclass, 'get_instance_title') ? '' : call_static_method($blocktypeclass, 'get_title');

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $values['blocktype'],
                'title'      => $newtitle,
                'view'       => $this->get('id'),
                'view_obj'   => $this,
                'row'        => $values['row'],
                'column'     => $values['column'],
                'order'      => $values['order'],
            )
        );
        $this->shuffle_cell($values['row'], $values['column'], $values['order']);
        $bi->commit();
        $this->dirtycolumns[$values['row']][$values['column']] = 1;

        if ($values['returndata']) {
            // Return new block rendered in both configure mode and (editing) display mode
            $result = array(
                'display' => $bi->render_editing(false, true),
            );
            if (call_static_method(generate_class_name('blocktype', $values['blocktype']), 'has_instance_config')) {
                $result['configure'] = $bi->render_editing(true, true);
            }
            return $result;
        }
    }

    /**
     * adds a block instance to a view
     * @param array $values parameters for this function
     *                      block     => block to add
     */
    public function addblockinstance(BlockInstance $bi) {
        if (!$bi->get('row')) {
            $bi->set('row', 1);
        }
        if (!$bi->get('column')) {
            $bi->set('column', 1);
        }
        if (!$bi->get('order')) {
            $bi->set('order', 1);
        }
        if (!$bi->get('view')) {
            $bi->set('view', $this->get('id'));
        }
        $this->shuffle_cell($bi->get('row'), $bi->get('column'), $bi->get('order'));
        $bi->commit();
    }

    /**
     * deletes a block instance from the view
     *
     * @param array $values parameters for this function
     *                      id => int id of blockinstance to remove
     */
    public function removeblockinstance($values) {
        if (!array_key_exists('id', $values) || empty($values['id'])) {
            throw new ParamOutOfRangeException(get_string('missingparamid', 'error'));
        }
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($values['id']); // get it so we can reshuffle stuff
        // Check if the block_instance belongs to this view
        if ($bi->get('view') != $this->get('id')) {
            throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
        }
        db_begin();
        $bi->delete();
        $this->shuffle_cell($bi->get('row'), $bi->get('column'), null, $bi->get('order'));
        db_commit();
        $this->dirtycolumns[$bi->get('row')][$bi->get('column')] = 1;
    }

    /**
    * moves a block instance to a specified location
    *
    * @param array $values parameters for this function
    *                      id     => int of block instance to move
    *                      row      => int current row
    *                      column => int column to move to
    *                      order  => position in new column to insert at
    */
    public function moveblockinstance($values) {
        $requires = array('id', 'row', 'column', 'order');
        foreach ($requires as $require) {
            if (!array_key_exists($require, $values) || empty($values[$require])) {
                throw new ParamOutOfRangeException(get_string('missingparam' . $require, 'error'));
            }
        }
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($values['id']);
        // Check if the block_instance belongs to this view
        if ($bi->get('view') != $this->get('id')) {
            throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
        }
        db_begin();
        // moving within the same column and row
        if ($bi->get('row') == $values['row'] && $bi->get('column') == $values['column']) {
            if ($values['order'] == $bi->get('order') + 1 || $values['order'] == $bi->get('order') -1) {
                // we're switching two, it's a bit different
                // set the one we're moving to out of range (to 0)
                // double quotes required for field names to avoid exception
                set_field_select('block_instance', 'order', 0,                 '"view" = ? AND "row" = ? AND "column" = ? AND "order" = ?', array($this->get('id'), $values['row'], $values['column'], $values['order']) );
                // set the new order
                set_field_select('block_instance', 'order', $values['order'],  '"view" = ? AND "row" = ? AND "column" = ? AND "order" = ?', array($this->get('id'), $values['row'], $values['column'], $bi->get('order')) );
                // move the old one back to where the moving one was.
                set_field_select('block_instance', 'order', $bi->get('order'), '"view" = ? AND "row" = ? AND "column" = ? AND "order" = ?', array($this->get('id'), $values['row'], $values['column'], 0) );
                // and set it in the object for good measure.
                $bi->set('order', $values['order']);
            }
            else if ($values['order'] == $this->get_current_max_order($values['row'], $values['column'])) {
                // moving to the very bottom
                set_field_select('block_instance', 'order', 0, '"view" = ? AND "row" = ? AND "column" = ? AND "order" = ?', array($this->get('id'), $values['row'], $values['column'], $bi->get('order')) );
                $this->shuffle_helper('order', 'down', '>=', $bi->get('order'), '"column" = ? AND "row" = ?', array($bi->get('column'), $values['row']));
                set_field_select('block_instance', 'order', $values['order'], '"order" = ? AND "view" = ? AND "row" = ? AND "column" = ?', array(0, $this->get('id'), $values['row'], $values['column']));
                $bi->set('order', $values['order']);
            }
            else {
                $this->shuffle_cell($values['row'], $bi->get('column'), $values['order'], $bi->get('order'));
                if ($bi->get('order') < $values['order']) {
                    // When moving a block down within a column, the final order is one less
                    // than the 'desired' order because of the empty space created when the
                    // block gets taken out of its original spot.
                    $values['order'] -= 1;
                }
            }
        }
        // moving to another column
        else {
            // first figure out if we've asked to add it somewhere sensible
            // eg if we're moving a low down block into an empty column
            $newmax = $this->get_current_max_order($values['row'], $values['column']);
            if ($values['order'] > $newmax+1) {
                $values['order'] = $newmax+1;
            }
            // remove it from the old column
            $this->shuffle_cell($bi->get('row'), $bi->get('column'), null, $bi->get('order'));
            // and make a hole in the new column
            $this->shuffle_cell($values['row'], $values['column'], $values['order']);
        }
        $bi->set('column', $values['column']);
        $bi->set('row', $values['row']);
        $bi->set('order', $values['order']);
        $bi->commit();
        $this->dirtycolumns[$values['row']][$bi->get('column')] = 1;
        $this->dirtycolumns[$values['row']][$values['column']] = 1;
        db_commit();
    }

    /**
     * Returns a list of required javascript files + initialization codes, based on
     * the blockinstances present in the view.
     */
    public function get_all_blocktype_javascript() {
        $javascriptfiles = array();
        $initjavascripts = array();
        $view_data = $this->get_row_datastructure();
        foreach ($view_data as $row_data) {
            foreach($row_data as $column) {
                foreach($column['blockinstances'] as $blockinstance) {
                    $pluginname = $blockinstance->get('blocktype');
                    if (!safe_require_plugin('blocktype', $pluginname)) {
                        continue;
                    }
                    $instancejs = call_static_method(
                        generate_class_name('blocktype', $pluginname),
                        'get_instance_javascript',
                        $blockinstance
                    );
                    foreach($instancejs as $jsfile) {
                        if (is_array($jsfile) && isset($jsfile['file'])) {
                            $javascriptfiles[] = $this->add_blocktype_path($blockinstance, $jsfile['file']);;
                            if (isset($jsfile['initjs'])) {
                                $initjavascripts[] = $jsfile['initjs'];
                            }
                        }
                        else if (is_string($jsfile)) {
                            $javascriptfiles[] = $this->add_blocktype_path($blockinstance, $jsfile);;
                        }
                    }
                }
            }
        }
        return array(
            'jsfiles' => array_unique($javascriptfiles),
            'initjs'  => array_unique($initjavascripts)
        );
    }

    /**
     * Returns a list of required css files.
     */
    public function get_all_blocktype_css() {
        global $THEME;
        $cssfiles = array();
        $view_data = $this->get_row_datastructure();
        foreach ($view_data as $row_data) {
            foreach ($row_data as $column) {
                foreach ($column['blockinstances'] as $blockinstance) {
                    $pluginname = $blockinstance->get('blocktype');
                    if (!safe_require_plugin('blocktype', $pluginname)) {
                        continue;
                    }
                    $artefactdir = '';
                    if ($blockinstance->get('artefactplugin') != '') {
                        $artefactdir = 'artefact/' . $blockinstance->get('artefactplugin') . '/';
                    }
                    $hrefs = $THEME->get_url('style/style.css', true, $artefactdir . 'blocktype/' . $pluginname);
                    $hrefs = array_reverse($hrefs);

                    foreach ($hrefs as $href) {
                        $cssfiles[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number($href) . '">';
                    }
                }
            }
        }
        return array_unique($cssfiles);
    }

    /**
     * Returns the full path of a blocktype javascript file if it is internal
     */
    private function add_blocktype_path($blockinstance, $jsfilename) {
        $pluginname = $blockinstance->get('blocktype');
        if (stripos($jsfilename, 'http://') === false && stripos($jsfilename, 'https://') === false) {
            if ($blockinstance->get('artefactplugin')) {
                $jsfilename = 'artefact/' . $blockinstance->get('artefactplugin') . '/blocktype/' .
                    $pluginname . '/' . $jsfilename;
            }
            else {
                $jsfilename = 'blocktype/' . $blockinstance->get('blocktype') . '/' . $jsfilename;
            }
        }
        return $jsfilename;
    }

    /**
     * Returns a list of required javascript files, based on
     * the blockinstances present in the view.
     */
    public function get_blocktype_javascript() {
        $view_data = $this->get_row_datastructure();
        $javascript = array();
        foreach($view_data as $row) {
            foreach($row as $column) {
                foreach($column['blockinstances'] as $blockinstance) {
                    $pluginname = $blockinstance->get('blocktype');
                    safe_require('blocktype', $pluginname);
                    $instancejs = call_static_method(
                        generate_class_name('blocktype', $pluginname),
                        'get_instance_javascript',
                        $blockinstance
                    );
                    foreach($instancejs as &$jsfile) {
                        if (stripos($jsfile, 'http://') === false && stripos($jsfile, 'https://') === false) {
                            if ($artefactplugin = get_field('blocktype_installed', 'artefactplugin', 'name', $pluginname)) {
                                $jsfile = 'artefact/' . $artefactplugin . '/blocktype/' .
                                    $pluginname . '/' . $jsfile;
                            }
                            else {
                                $jsfile = 'blocktype/' . $blockinstance->get('blocktype') . '/' . $jsfile;
                            }
                        }
                    }
                    $javascript = array_merge($javascript, $instancejs);
                }
            } // cols
        } // rows
        return array_unique($javascript);
    }

    private $blockinstance_currently_being_configured = 0;

    /**
     * Sets what blockinstance is currently being edited
     * TODO: use get()
     */
    public function set_blockinstance_currently_being_configured($id) {
        $this->blockinstance_currently_being_configured = $id;
    }

    public function get_blockinstance_currently_being_configured() {
        return $this->blockinstance_currently_being_configured;
    }

    /**
     * Configures a blockinstance
     *
     * @param array $values parameters for this function
     */
    public function configureblockinstance($values) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($values['id']);
        // Check if the block_instance belongs to this view
        if ($bi->get('view') != $this->get('id')) {
            throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
        }
        return $bi->render_editing(true);
    }

    /**
     * adds a column to a view
     *
     * @param array $values parameters for this function
     *                      before => int column to insert the new column before
     *                      returndata => boolean whether to return the html
     *                                    for the new column or not (ajax requests need this)
     *
     */
    public function addcolumn($values) {

        if (!array_key_exists('before', $values) || empty($values['before']) || !array_key_exists('row', $values) || empty($values['row'])) {
            throw new ParamOutOfRangeException(get_string('missingparamcolumn', 'error'));
        }

        $columnsperrow = $this->get('columnsperrow');
        $columnsthisrow = clone $columnsperrow[$values['row']];
        $thisrownumcolumns = $columnsperrow[$values['row']]->columns;

        // simple check for valid number of columns
        $newlayouts = get_records_sql_array('SELECT vlc.id
            FROM {view_layout_columns} vlc
            WHERE vlc.columns = ?', array($thisrownumcolumns + 1) );

        if (!$newlayouts) {
            throw new ParamOutOfRangeException(get_string('cantaddcolumn', 'view'));
        }
        db_begin();

        $columnsthisrow->columns = $thisrownumcolumns + 1;
        $columnsperrow[$values['row']] = $columnsthisrow;
        $this->set('columnsperrow', $columnsperrow); //set makes dirty=1, which enables commit; numcolumnsperrrow used as check by layout submit function

        if ($values['before'] != ($thisrownumcolumns + 1)) {
            $this->shuffle_helper('column', 'up', '>=', $values['before'], 'row = ?', array($values['row']));
        }
        $this->set('layout', null);
        $this->commit();

        $currentrowcolumns = $columnsperrow[$values['row']]->columns;
        for ($i = $values['before']; $i <= $currentrowcolumns; $i++) {
            $this->dirtycolumns[$values['row']][$i] = 1;
        }

        $this->columns[$values['row']][$currentrowcolumns] = null; // set the key
        db_commit();
        if ($values['returndata']) {
            return $this->build_column($values['row'], $values['before'], true);
        }
    }


    /**
     * removes an entire column and redistributes its blocks
     *
     * @param array $values parameters for this function
     *                      column => int column to remove
     *
     */
    public function removecolumn($values) {
        if (!array_key_exists('column', $values) || empty($values['column']) || !array_key_exists('row', $values) || empty($values['row'])) {
            throw new ParamOutOfRangeException(get_string('missingparamcolumn', 'error'));
        }
        if (!array_key_exists('removerow', $values)) {
            $values['removerow'] = false;
        }
        $columnsperrow = $this->get('columnsperrow');
        $numcolumnsthisrow = clone $columnsperrow[$values['row']];
        $thisrownumcolumns = $columnsperrow[$values['row']]->columns;

        if (!$values['removerow']) {
            // simple check for valid number of columns
            $newlayouts = get_records_sql_array('SELECT vlc.id
                                                FROM {view_layout_columns} vlc
                                                WHERE vlc.columns = ?', array($thisrownumcolumns - 1) );
            if (!$newlayouts) {
                throw new ParamOutOfRangeException(get_string('cantremovecolumn', 'view'));
            }
        }
        else if ($thisrownumcolumns == 0) {
             throw new ParamOutOfRangeException(get_string('cantremovecolumn', 'view'));
        }

        db_begin();
        $numcolumns = $thisrownumcolumns - 1;

        // if removing row, move blocks to previous row
        if ($values['removerow']) {
            $prevrownumcolumns = $columnsperrow[$values['row']-1]->columns;
            $prevrowcolumnmax = array(); // keep track of where we're at in each column
            $currentcol = 1;
            if ($blocks = $this->get_column_datastructure($values['row'], $values['column'])) {
                // we have to rearrange them first
                foreach ($blocks['blockinstances'] as $block) {
                    if ($currentcol > $prevrownumcolumns) {
                        $currentcol = 1;
                    }
                    if ($currentcol == $values['column']) {
                        $currentcol++; // don't redistrubute blocks here!
                    }
                    if (!array_key_exists($currentcol, $prevrowcolumnmax)) {
                        $prevrowcolumnmax[$currentcol] = $this->get_current_max_order($values['row']-1, $currentcol);
                    }
                    $this->shuffle_cell($values['row']-1, $currentcol, $prevrowcolumnmax[$currentcol]+1);
                    $this->shuffle_cell($values['row'], $currentcol, null, $block->get('order'));
                    $block->set('row', $values['row']-1);
                    $block->set('column', $currentcol);
                    $block->set('order', $prevrowcolumnmax[$currentcol]+1);
                    $block->commit();
                    $prevrowcolumnmax[$currentcol]++;
                    $currentcol++;
                }
            }
        }
        else {
            $columnmax = array(); // keep track of where we're at in each column
            $currentcol = 1;
            if ($blocks = $this->get_column_datastructure($values['row'], $values['column'])) {
                // we have to rearrange them first
                foreach ($blocks['blockinstances'] as $block) {
                    if ($currentcol > $numcolumns) {
                        $currentcol = 1;
                    }
                    if ($currentcol == $values['column']) {
                        $currentcol++; // don't redistrubute blocks here!
                    }
                    if (!array_key_exists($currentcol, $columnmax)) {
                        $columnmax[$currentcol] = $this->get_current_max_order($values['row'], $currentcol);
                    }
                    $this->shuffle_cell($values['row'], $currentcol, $columnmax[$currentcol]+1);
                    $block->set('row', $values['row']);
                    $block->set('column', $currentcol);
                    $block->set('order', $columnmax[$currentcol]+1);
                    $block->commit();
                    $columnmax[$currentcol]++;
                    $currentcol++;
                }
            }
        }

        $this->set('layout', null);
        $numcolumnsthisrow->columns = $thisrownumcolumns - 1;
        $columnsperrow[$values['row']] = $numcolumnsthisrow;
        $this->set('columnsperrow', $columnsperrow); //set makes dirty=1, which enables commit; columnsperrrow used as check by layout submit function
        // now shift all blocks one left and we're done
        $this->shuffle_helper('column', 'down', '>', $values['column'], 'row = ?', array($values['row']));
        $this->commit();
        db_commit();
        unset($this->columns[$values['row']]); // everything has changed
    }
    /**
     * adds a row to a view
     *
     * @param array $values parameters for this function
     *                      before => int row to insert the new row before
     *                      returndata => boolean whether to return the html
     *                                    for the new row or not (ajax requests need this)
     *
     */
    public function addrow($values) {

        if (!array_key_exists('before', $values) || empty($values['before']) || !array_key_exists('newlayout', $values) || empty($values['newlayout'])) {
            throw new ParamOutOfRangeException(get_string('exceededmaxrows', 'error'));
        }

        if ($values['before'] > self::$maxlayoutrows) {
            throw new ParamOutOfRangeException(get_string('invalidnumrows', 'error'));
        }

        $columnsperrow = $this->get('columnsperrow');

        db_begin();
        $this->set('numrows', $this->get('numrows') + 1);
        if ($values['before'] != ($this->get('numrows'))) {
            $this->shuffle_helper('row', 'up', '>=', $values['before']);
        }
        $this->set('layout', null);

        $layoutrows = $this->get_layoutrows();
        $newrowcolumnsindex = $layoutrows[$values['newlayout']][$values['before']];
        $newrownumcolumns = self::$layoutcolumns[$newrowcolumnsindex]->columns;

        $columnsperrow[$values['before']] = (object)array('row' => $values['before'], 'columns' => $newrownumcolumns);
        $this->set('columnsperrow', $columnsperrow); //set makes dirty=1, which enables commit; columnsperrrow used as check by layout submit function

        $this->commit();
        // @TODO this could be optimised by actually moving the keys around,
        // but I don't think there's much point as the objects aren't persistent
        // unless we're in ajax land, in which case it would be an optimisation
        for ($i = $values['before']; $i <= $this->get('numrows'); $i++) {
            $this->dirtyrows[$i] = 1;
        }
        $this->rows[$this->get('numrows')] = null; // set the key
        db_commit();
        if ($values['returndata']) {
            return $this->build_row($values['before'], true); // MK follow up - AJAX
        }
    }

    /**
     * removes an entire row and redistributes its blocks
     *
     * @param array $values parameters for this function
     *                      row => int row to remove
     *
     */
    public function removerow($values) {
        // $layoutrows declared in layout.php
        global $SESSION;

        if (!array_key_exists('row', $values) || empty($values['row'])) {
            throw new ParamOutOfRangeException(get_string('missingparamrow', 'error'));
        }

        db_begin();
        // for each column, call removecolumn
        // first retrieve number of columns in row
        $layoutrows = $this->get_layoutrows();
        $layout = $values['layout'];
        $thisrownumcolumns = $layout->rows[$values['row']]['columns'];

        for ($i = $thisrownumcolumns; $i > 0 ; $i--) {
            $this->removecolumn(array('row' => $values['row'], 'column' => $i, 'removerow' => true));
        }

        // check for sucessful removal of columns
        $dbcolumns = get_field('view_rows_columns', 'columns', 'view', $this->get('id'), 'row', $values['row']);

        if ($dbcolumns != 0) {
            db_rollback();
            $SESSION->add_error_msg(get_string('changecolumnlayoutfailed', 'view'));
            redirect(get_config('wwwroot') . 'view/layout.php?id=' . $this->get('id') . ($new ? '&new=1' : ''));
        }

        $this->set('numrows', $this->get('numrows') - 1);
        $this->set('layout', null);

        $columnsperrow = $this->get('columnsperrow');
        unset($columnsperrow[$values['row']]);
        $this->set('columnsperrow', $columnsperrow); //set makes dirty=1, which enables commit; columnsperrrow used as check by layout submit function

        $this->commit();
        db_commit();
        unset($this->rows[$values['row']]);
    }
    /**
     * helper function for re-ordering block instances within a cell row x column
     * @param int $row     the row
     * @param int $column  the column of the cell to re-order
     * @param int $insert the order we need to insert
     * @param int $remove the order we need to move out of the way
     */
    private function shuffle_cell($row, $column, $insert=0, $remove=0) {
        /*
        inserting something in the middle from somewhere else (insert and remove)
        we're either reshuffling after a delete, (no insert),
        inserting something in the middle out of nowhere (no remove)
        */
        // inserting and removing
        if (!empty($remove)) {
            // move it out of range (set to 0)
            set_field_select('block_instance', 'order', 0, '"order" = ? AND "view" = ? AND "row" = ? AND "column" = ?', array($remove, $this->get('id'), $row, $column));
            if (!empty($insert)) {
                // shuffle everything up
                $this->shuffle_helper('order', 'up', '>=', $insert, '"row" = ? AND "column" = ?', array($row, $column));
                // now move it back
                set_field_select('block_instance', 'order', $insert, '"order" = ? AND "view" = ? AND "row" = ? AND "column" = ?', array(0, $this->get('id'), $row, $column));
            }

            // shuffle everything down
            $this->shuffle_helper('order', 'down', '>', $remove, '"row" = ? AND "column" = ?', array($row, $column));
        }
        else if (!empty($insert)) {
            // shuffle everything up
            $this->shuffle_helper('order', 'up', '>=', $insert, '"row" = ? AND "column" = ?', array($row, $column));
        }
    }

    private function shuffle_helper($field, $direction, $operator, $value, $extrawhere='', $extravalues='') {

        // doing this with execute_sql rather than set_field and friends because of
        // adodb retardedly trying to make "order"+1 and friends into a string

        // I couldn't find a way to shift a bunch of rows in step even with set constraints deferred.

        // the two options I found were to move them all out of range (eg start at max +1) and then back again
        // or move them into negative and back into positive (Grant's suggestion) which I like more.

        if (empty($extrawhere)) {
            $extrawhere = '';
        }
        else {
            $extrawhere = ' AND ' . $extrawhere;
        }
        if (empty($extravalues) || !is_array($extravalues) || count($extravalues) == 0) {
            $extravalues = array();
        }

        // first move them one but switch to negtaive
        $sql = 'UPDATE {block_instance}
                    SET "' . $field .'" = (-1 * ("' . $field . '") ' . (($direction == 'up') ? '-' : '+') . ' 1)
                    WHERE "view" = ? AND "' . $field . '"' . $operator . ' ? ' . $extrawhere;

        execute_sql($sql, array_merge(array($this->get('id'), $value), $extravalues));

        // and now flip to positive again
        $sql = 'UPDATE {block_instance}
                    SET "' . $field . '" = ("' . $field . '" * -1)
                WHERE "view" = ? AND "' . $field . '" < 0 ' . $extrawhere;

        execute_sql($sql, array_merge(array($this->get('id')), $extravalues));

    }

    /**
     * returns the current max block position within a column
     */
    private function get_current_max_order($row, $column) {
        return get_field('block_instance', 'max("order")', 'column', $column, 'view', $this->get('id'), 'row', $row);
    }

    private function changetheme($values) {
        if ($theme = $values['theme']) {
            $themes = get_user_accessible_themes();
            if (isset($themes[$theme])) {
                if ($theme == 'sitedefault') {
                    $theme = null;
                }
                $this->set('theme', $theme);
                $this->commit();
                handle_event('saveview', $this->get('id'));
            }
        }
    }

    public function set_user_theme() {
        global $THEME;
        if ($this->theme && $THEME->basename != $this->theme) {
            $THEME = new Theme($this->theme);
        }
        return $this->theme;
    }

    /**
     * This function formats the owner's name according to their view preference
     *
     * @param bool $includelink true if the result should be wrapped in an html anchor link
     * @return string formatted name
     */
    public function formatted_owner($includelink = false) {

        if ($this->get('owner')) {
            $user = $this->get_owner_object();

            switch ($this->ownerformat) {
            case FORMAT_NAME_FIRSTNAME:
                $name = $user->firstname;
                break;
            case FORMAT_NAME_LASTNAME:
                $name = $user->lastname;
                break;
            case FORMAT_NAME_FIRSTNAMELASTNAME:
                $name = $user->firstname . ' ' . $user->lastname;
                break;
            case FORMAT_NAME_PREFERREDNAME:
                $name = $user->preferredname;
                break;
            case FORMAT_NAME_STUDENTID:
                $name = $user->studentid;
                break;
            case FORMAT_NAME_DISPLAYNAME:
            default:
                $name = display_name($user);
                break;
            }
            if ($includelink) {
                return get_string('link', 'mahara', get_config('wwwroot') . 'user/view.php?id=' . $user->id, $name);
            }
            else {
                return $name;
            }
        }
        else if ($this->get('group')) {
            $group = $this->get_group_object();
            if ($includelink) {
                return get_string('link', 'mahara', get_config('wwwroot') . 'group/view.php?id=' . $group->id, $group->name);
            }
            else {
                return $group->name;
            }
        }
        else if ($i = $this->get('institution')) {
            if ($i == 'mahara') {
                return get_config('sitename');
            }
            $institution = $this->get_institution_object();
            if ($includelink) {
                return get_string('link', 'mahara', get_config('wwwroot') . 'institution/index.php?institution=' .
                        $institution->name, $institution->displayname);
            }
            else {
                return $institution->displayname;
            }
        }
        return null;
    }

    /**
     * This function returns a boolean indicating whether the current page should be anonymised.
     */
    public function is_anonymous()
    {
      return get_config('allowanonymouspages') && $this->anonymise;
    }

    /**
      * This function returns a boolean indicating whether author information should be made
      * available in an ajax link if the page is anonymised.
      */
    public function is_staff_or_admin_for_page()
    {
        global $USER;

        return (($USER->get('id') === $this->get('owner')) || $USER->is_staff_for_user($this->get_owner_object()));
    }

    /**
     * Returns a record from the view_layout table matching the layout for this View.
     *
     * If the layout for the view is null, and there is only one row,
     * then this method returns the record for the default layout for
     * the number of columns the View has.
     *
     * It's not meaningful to have a default for multi-row layouts as there are so many possible permutations. (19,530 with equal column spacing alone.)
     * Therefore we allow for an empty id value to be returned in the absence of a matching layout,
     * if there is more than one row, but provide the default widths for the columns in each row.
     * This is to cater for the dynamic adding and removing of columns via AJAX. In this case,
     * new layouts may be created which do not yet exist. Adding them to the database would confuse the
     * layout options page. In such cases the view layout is set to null in any case.
     *
     *
     * @return object containing an id from the view_layout table, and an array of rows with column numbers and column widths per row.
     */
    public function get_layout() {

        $layout = new StdClass;
        $layout->rows = array();
        $layoutid = $this->get('layout');
        $numrows = $this->get('numrows');
        $columnsperrow = $this->get('columnsperrow');
        $owner = $this->get('owner');
        $queryarray = array($owner, $numrows);
        $group = $this->get('group');
        $institution = $this->get('institution');

        if (isset($owner)) {
            $andclause = '(ucl.usr = 0 OR ucl.usr = ?)';
        }
        else if (!empty($group)) {
            $andclause = '(ucl.usr = 0 OR ucl.group = ?)';
            $queryarray = array($group, $numrows);
        }
        else if (!empty($institution)) {
            $andclause = '(ucl.usr = 0 OR ucl.institution = ?)';
            $queryarray = array($institution, $numrows);
        }
        else {
            throw new SystemException("View::get_layout: No owner, group or institution set for view.");
        }

        // get all valid possible layout records
        $validlayouts = get_records_sql_assoc('
                SELECT * FROM {view_layout} vl
                JOIN {usr_custom_layout} ucl
                ON ((vl.id = ucl.layout) AND ' . $andclause . ')
                WHERE rows = ?', $queryarray);

        if ($layoutid) {
            $layout->id = $layoutid;
            $layoutsrowscols = get_records_select_array('view_layout_rows_columns', 'viewlayout = ?', array($layoutid));
            foreach ($layoutsrowscols as $layoutrowcol) {
                $layout->rows[$layoutrowcol->row]['widths'] = self::$layoutcolumns[$layoutrowcol->columns]->widths;
                $layout->rows[$layoutrowcol->row]['columns'] = self::$layoutcolumns[$layoutrowcol->columns]->columns;
            }

        }
        else if (!$layoutid) {
            // view.layout is NULL, because the user hasn't chosen a stored layout or has been altering
            // the number of columns in each row using the "add/delete columns" buttons.

            // get widths for each row, based on equal spacing of columns
            $layoutid = 0;
            $layout->id = $layoutid;
            foreach ($columnsperrow as $row) {
                $numcolumns = $row->columns;
                $widths = self::$defaultcolumnlayouts[$numcolumns];
                $layout->id = get_field('view_layout_columns', 'id', 'columns', $numcolumns, 'widths', $widths);
                $layout->rows[$row->row]['widths'] = $widths;
                $layout->rows[$row->row]['columns'] = $numcolumns;
            }
        }

        return $layout;
    }

    /**
     * Exports the view configuration as a data structure. This does not
     * include access rules or ownership information - only the information
     * required to rebuild the view's layout, blocks and other such info.
     *
     * This structure can then be imported again, using {@link import_from_config()}
     *
     * @return array The configuration for this view, try calling this to see
     *               what fields are available.
     */
    public function export_config($format='') {
        $data = $this->get_row_datastructure();
        $config = array(
            'title'       => $this->get('title'),
            'description' => $this->get('description'),
            'type'        => $this->get('type'),
            'layout'      => $this->get('layout'),
            'tags'        => $this->get('tags'),
            'numrows'     => $this->get('numrows'),
            'ownerformat' => $this->get('ownerformat'),
        );

        foreach ($data as $rowkey => $row) {
            foreach ($row as $colkey => $column) {
                $config['rows'][$rowkey]['columns'][$colkey] = array();
                foreach ($column['blockinstances'] as $bi) {
                    safe_require('blocktype', $bi->get('blocktype'));
                    $classname = generate_class_name('blocktype', $bi->get('blocktype'));
                    $method = 'export_blockinstance_config';
                    if (method_exists($classname, $method . "_$format")) {
                        $method .= "_$format";
                    }
                    $config['rows'][$rowkey]['columns'][$colkey][] = array(
                        'blocktype' => $bi->get('blocktype'),
                        'title'     => $bi->get('title'),
                        'config'    => call_static_method($classname, $method, $bi),
                    );
                }
            } // cols
        } // rows

        return $config;
    }

    /**
     * Given a data structure like the one created by {@link export_config},
     * creates and returns a View object representing the config.
     *
     * @param array $config The config, as generated by export_config. Note
     *                      that if you miss fields, this method will throw
     *                      warnings.
     * @param int $userid   The user who issued the command to do the import
     *                      (defaults to the logged in user)
     * @return View The created view
     */
    public static function import_from_config(array $config, $userid=null, $format='') {
        $viewdata = array(
            'title'       => $config['title'],
            'description' => $config['description'],
            'type'        => $config['type'],
            'layout'      => $config['layout'],
            'tags'        => $config['tags'],
            'numrows'     => $config['numrows'],
            'ownerformat' => $config['ownerformat'],
        );
        if (isset($config['owner'])) {
            $viewdata['owner'] = $config['owner'];
        }
        if (isset($config['group'])) {
            $viewdata['group'] = $config['group'];
        }

        if (isset($config['institution'])) {
            $viewdata['institution'] = $config['institution'];
        }
        $view = View::create($viewdata, $userid);

        foreach ($config['rows'] as $rowkey => $row) {
            foreach ($row['columns'] as $colkey => $column) {
                $order = 1;
                foreach ($column as $blockinstance) {
                    safe_require('blocktype', $blockinstance['type']);
                    $classname = generate_class_name('blocktype', $blockinstance['type']);
                    $method = 'import_create_blockinstance';
                    if (method_exists($classname, $method . "_$format")) {
                        $method .= "_$format";
                    }
                    $bi = call_static_method($classname, $method, $blockinstance, $config);
                    if ($bi) {
                        $bi->set('title',  $blockinstance['title']);
                        $bi->set('row', $rowkey);
                        $bi->set('column', $colkey);
                        $bi->set('order',  $order);
                        $view->addblockinstance($bi);

                        $order++;
                    }
                    else {
                        log_debug("Blocktype {$blockinstance['type']}'s import_create_blockinstance did not give us a blockinstance, so not importing this block");
                    }
                }
            } // cols
        } // rows

        if ($viewdata['type'] == 'profile') {
            $view->set_access(array(
                array(
                    'type'      => 'loggedin',
                    'startdate' => null,
                    'stopdate'  => null,
                ),
            ));
        }

        return $view;
    }


    /**
     * Makes a URL for a view block editing page
     */
    public static function make_base_url() {
        static $allowed_keys = array('id', 'change', 'c', 'new', 'search');
        $baseurl = '?';
        foreach ($_POST + $_GET as $key => $value) {
            if (in_array($key, $allowed_keys) || preg_match('/^action_.*(_x)?$/', $key)) {
                $baseurl .= hsc($key) . '=' . hsc($value) . '&';
            }
        }
        $baseurl = substr($baseurl, 0, -1);
        return $baseurl;
    }

    /**
     * Builds data for the artefact chooser.
     *
     * This builds three pieces of information:
     *
     * - HTML containing table rows
     * - Pagination HTML and Javascript
     * - The total number of artefacts found
     * - Artefact fields to return
     */
    public static function build_artefactchooser_data($data, $group=null, $institution=null) {
        global $USER;
        $search = '';
        if (!empty($data['search']) && param_boolean('s')) {
            $search = param_variable('search', '');
            // Maybe later, depending on performance - don't search if there's
            // not enough characters. Prompts should be added to the UI too.
            //if (strlen($search) < 3) {
            //    $search = '';
            //}
        }

        $data['search'] = $search;
        $data['offset'] -= $data['offset'] % $data['limit'];

        safe_require('blocktype', $data['blocktype']);
        $blocktypeclass = generate_class_name('blocktype', $data['blocktype']);

        $data['sortorder'] = array(array('fieldname' => 'title', 'order' => 'ASC'));
        if (method_exists($blocktypeclass, 'artefactchooser_get_sort_order')) {
            $data['sortorder'] = call_static_method($blocktypeclass, 'artefactchooser_get_sort_order');
        }

        list($artefacts, $totalartefacts) = self::get_artefactchooser_artefacts($data, $USER, $group, $institution);

        $selectone     = $data['selectone'];
        $value         = $data['defaultvalue'];
        $elementname   = $data['name'];
        $template      = $data['template'];
        $returnfields  = isset($data['returnfields']) ? $data['returnfields'] : null;

        $returnartefacts = array();
        $result = '';
        if ($artefacts) {

            if (!empty($data['ownerinfo'])) {
                require_once(get_config('docroot') . 'artefact/lib.php');
                $userid = ($group || $institution) ? null : $USER->get('id');
                foreach (artefact_get_owner_info(array_keys($artefacts)) as $k => $v) {
                    if ($artefacts[$k]->owner !== $userid
                        || $artefacts[$k]->group !== $group
                        || $artefacts[$k]->institution !== $institution) {
                        $artefacts[$k]->ownername = $v->name;
                        $artefacts[$k]->ownerurl  = $v->url;
                    }
                }
            }

            foreach ($artefacts as &$artefact) {
                safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $artefact->artefacttype));

                if (method_exists($blocktypeclass, 'artefactchooser_get_element_data')) {
                    $artefact = call_static_method($blocktypeclass, 'artefactchooser_get_element_data', $artefact);
                }

                // Build the radio button or checkbox for the artefact
                $formcontrols = '';
                if ($selectone) {
                    $formcontrols .= '<input type="radio" class="radio" id="' . hsc($elementname . '_' . $artefact->id)
                        . '" name="' . hsc($elementname) . '" value="' . hsc($artefact->id) . '"';
                    if ($value == $artefact->id) {
                        $formcontrols .= ' checked="checked"';
                    }
                    $formcontrols .= '>';
                }
                else {
                    $formcontrols .= '<input type="checkbox" id="' . hsc($elementname . '_' . $artefact->id) . '" name="' . hsc($elementname) . '[' . hsc($artefact->id) . ']"';
                    if ($value && in_array($artefact->id, $value)) {
                        $formcontrols .= ' checked="checked"';
                    }
                    $formcontrols .= ' class="artefactid-checkbox checkbox">';
                    $formcontrols .= '<input type="hidden" name="' . hsc($elementname) . '_onpage[]" value="' . hsc($artefact->id) . '" class="artefactid-onpage">';
                }

                $smarty = smarty_core();
                $smarty->assign('artefact', $artefact);
                $smarty->assign('elementname', $elementname);
                $smarty->assign('formcontrols', $formcontrols);
                $result .= $smarty->fetch($template) . "\n";

                if ($returnfields) {
                    $returnartefacts[$artefact->id] = array();
                    foreach ($returnfields as $f) {
                        if ($f == 'safedescription') {
                            $returnartefacts[$artefact->id]['safedescription'] = clean_html($artefact->description);
                            continue;
                        }
                        if ($f == 'attachments') {
                            // Check if the artefact has attachments - we need to update the instance config form
                            // to have those attachments selected.
                            $attachment_ids = get_column('artefact_attachment','attachment', 'artefact', $artefact->id);
                            $returnartefacts[$artefact->id]['attachments'] = $attachment_ids;
                            continue;
                        }
                        $returnartefacts[$artefact->id][$f] = $artefact->$f;
                    }
                }
            }

            if ($returnfields && !empty($data['getblocks'])) {
                // Get ids of the blocks containing these artefacts
                $blocks = get_records_select_array(
                    'view_artefact',
                    'artefact IN (' . join(',', array_fill(0, count($artefacts), '?')) . ')',
                    array_keys($artefacts)
                );

                if (!empty($blocks)) {
                    // For each artefact, attach a list of block ids of all the blocks
                    // that contain it.
                    foreach ($blocks as $block) {
                        if (empty($returnartefacts[$block->artefact]['blocks'])) {
                            $returnartefacts[$block->artefact]['blocks'] = array();
                        }
                        $returnartefacts[$block->artefact]['blocks'][] = $block->block;
                    }
                }
            }
        }

        $pagination = build_pagination(array(
            'id' => $elementname . '_pagination',
            'class' => 'ac-pagination',
            'url' => View::make_base_url() . (param_boolean('s') ? '&s=1' : ''),
            'count' => $totalartefacts,
            'limit' => $data['limit'],
            'offset' => $data['offset'],
            'datatable' => $elementname . '_data',
            'jsonscript' => 'view/artefactchooser.json.php',
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'extradata' => array(
                'value'       => $value,
                'blocktype'   => $data['blocktype'],
                'group'       => $group,
                'institution' => $institution,
            ),
        ));

        return array($result, $pagination, $totalartefacts, $data['offset'], $returnartefacts);
    }

    /**
     * Return artefacts available for inclusion in a particular block
     *
     */
    public static function get_artefactchooser_artefacts($data, $owner=null, $group=null, $institution=null, $short=false) {
        if ($owner === null) {
            global $USER;
            $user = $USER;
        }
        else if ($owner instanceof User) {
            $user = $owner;
        }
        else if (intval($owner) != 0 || $owner == "0") {
            $user = new User();
            $user->find_by_id(intval($owner));
        }
        else {
            throw new SystemException("Invalid argument type " . gettype($owner) . " passed to View::get_artefactchooser_artefacts");
        }

        $offset        = !empty($data['offset']) ? $data['offset'] : null;
        $limit         = !empty($data['limit']) ? $data['limit'] : null;

        $sortorder = '';
        if (!empty($data['sortorder'])) {
            foreach ($data['sortorder'] as $field) {
                if (!preg_match('/^[a-zA-Z_0-9"]+$/', $field['fieldname'])) {
                    continue; // skip this item (it fails validation)
                }

                $order = 'ASC';
                if (!empty($field['order']) && ('DESC' == strtoupper($field['order']))) {
                    $order = 'DESC';
                }

                if (empty($sortorder)) {
                    $sortorder .= 'ORDER BY ';
                }
                else {
                    $sortorder .= ', ';
                }

                $sortorder .= $field['fieldname'] . ' ' . $order;
            }
        }

        $extraselect = '';
        if (isset($data['extraselect'])) {
            foreach ($data['extraselect'] as $field) {
                if (!preg_match('/^[a-zA-Z_0-9"]+$/', $field['fieldname'])) {
                    continue; // skip this item (it fails validation)
                }

                // Sanitise all values
                $values = $field['values'];
                foreach ($values as &$val) {
                    if ($field['type'] == 'int') {
                        $val = (int)$val;
                    }
                    elseif ($field['type'] == 'string') {
                        $val = db_quote($val);
                    }
                    else {
                        throw new SystemException("Unsupported field type '" . $field['type'] . "' passed to View::get_artefactchooser_artefacts");
                    }
                }

                $extraselect .= ' AND ';

                if (count($values) > 1) {
                    $extraselect .= $field['fieldname'] . ' IN (' . implode(', ', $values) . ')';
                }
                else {
                    $extraselect .= $field['fieldname'] . ' = ' . reset($values);
                }
            }
        }

        $from = ' FROM {artefact} a ';

        if ($group) {
            // Get group-owned artefacts that the user has view
            // permission on, and site-owned artefacts
            $from .= '
            LEFT OUTER JOIN (
                SELECT
                    r.artefact, r.can_view, r.can_edit, m.group
                FROM
                    {group_member} m
                    JOIN {artefact} aa ON aa.group = m.group
                    JOIN {artefact_access_role} r ON aa.id = r.artefact AND r.role = m.role
                WHERE
                    m.group = ?
                    AND m.member = ?
                    AND r.can_view = 1
            ) ga ON (ga.group = a.group AND a.id = ga.artefact)';

            $select = "(a.institution = 'mahara' OR ga.can_view = 1";

            $ph = array((int)$group, $user->get('id'));

            if (!empty($data['userartefactsallowed'])) {
                $select .= ' OR a.owner = ?';
                $ph[] = $user->get('id');
            }
            $select .= ')';
        }
        else if ($institution) {
            // Site artefacts & artefacts owned by this institution
            $select = "(a.institution = 'mahara' OR a.institution = ?)";
            $ph = array($institution);
        }
        else { // The view is owned by a normal user
            // Get artefacts owned by the user, group-owned artefacts
            // the user has republish permission on, artefacts owned
            // by the user's institutions.
            safe_require('artefact', 'file');

            $public = (int) ArtefactTypeFolder::admin_public_folder_id();
            $select = '(
                a.owner = ?
                OR a.id IN (
                    SELECT id
                    FROM {artefact}
                        WHERE (path = ? OR path LIKE ?) AND institution = \'mahara\'
                )
                OR a.id IN (
                    SELECT aar.artefact
                    FROM {group_member} m
                        JOIN {artefact} aa ON m.group = aa.group
                        JOIN {artefact_access_role} aar ON aar.role = m.role AND aar.artefact = aa.id
                    WHERE m.member = ? AND aar.can_republish = 1
                )
                OR a.id IN (SELECT artefact FROM {artefact_access_usr} WHERE usr = ? AND can_republish = 1)';

            $ph = array($user->get('id'), "/$public", db_like_escape("/$public/") . '%', $user->get('id'), $user->get('id'));

            $institutions = array_keys($user->get('institutions'));

            if ($user->get('admin')) {
                $institutions[] = 'mahara';
            }

            if ($institutions) {
                $select .= '
                OR a.institution IN (' . join(',', array_fill(0, count($institutions), '?')) . ')';
                $ph = array_merge($ph, $institutions);
            }

            $select .= "
            )";
        }

        if (!empty($data['artefacttypes']) && is_array($data['artefacttypes'])) {
            $select .= ' AND artefacttype IN(' . join(',', array_fill(0, count($data['artefacttypes']), '?')) . ')';
            $ph = array_merge($ph, $data['artefacttypes']);
        }

        if (!empty($data['search'])) {
            $search = db_quote('%' . str_replace('%', '%%', $data['search']) . '%');
            $select .= 'AND (title ' . db_ilike() . '(' . $search . ') OR description ' . db_ilike() . '(' . $search . ') )';
        }

        $select .= $extraselect;

        $selectph = $countph = $ph;

        if ($short) {
            // We just want to know which artefact ids are allowed for inclusion in a view,
            // but get_records_sql_assoc wants > 1 column
            $cols = 'a.id, a.id AS b';
        }
        else {
            $cols = 'a.*';

            // We also want to know which artefacts can be edited by the logged-in user within
            // the context of the view.  For an institution view, all artefacts from the same
            // institution are editable.  For an individual view, artefacts with the same 'owner'
            // are editable.  For group views, only those artefacts with the can_edit permission
            // out of artefact_access_role are editable.

            if ($group) {
                $expr = 'ga.can_edit IS NOT NULL AND ga.can_edit = 1';
            }
            else if ($institution) {
                $expr = 'a.institution = ?';
                array_unshift($selectph, $institution);
            }
            else {
                $expr = 'a.owner IS NOT NULL AND a.owner = ?';
                array_unshift($selectph, $user->get('id'));
            }
            if (is_mysql()) {
                $cols .= ", ($expr) AS editable";
            }
            else {
                $cols .= ", CAST($expr AS INTEGER) AS editable";
            }
        }

        $artefacts = get_records_sql_assoc(
            'SELECT ' . $cols . $from . ' WHERE ' . $select . $sortorder, $selectph, $offset, $limit
        );
        $totalartefacts = count_records_sql('SELECT COUNT(*) ' . $from . ' WHERE ' . $select, $countph);

        return array($artefacts, $totalartefacts);
    }

    public static function owner_name($ownerformat, $user) {

        switch ($ownerformat) {
            case FORMAT_NAME_FIRSTNAME:
                return $user->firstname;
            case FORMAT_NAME_LASTNAME:
                return $user->lastname;
            case FORMAT_NAME_FIRSTNAMELASTNAME:
                return $user->firstname . ' ' . $user->lastname;
            case FORMAT_NAME_PREFERREDNAME:
                return $user->preferredname;
            case FORMAT_NAME_STUDENTID:
                return $user->studentid;
            case FORMAT_NAME_DISPLAYNAME:
            default:
                return display_name($user);
        }
    }

    public static function can_remove_viewtype($viewtype) {
        $cannotremove = array('profile', 'dashboard', 'grouphomepage');
        if (in_array($viewtype, $cannotremove)) {
            return false;
        }
        // allow local custom code to make 'sticky' view types
        if (function_exists('local_can_remove_viewtype')) {
            return local_can_remove_viewtype($viewtype);
        }
        return true;
    }

    public function can_edit_title() {
        return self::can_remove_viewtype($this->type);
    }

    public static function get_myviews_data($limit=5, $offset=0, $query=null, $tag=null, $groupid=null, $institution=null, $orderby=null) {
        global $USER;
        $userid = (!$groupid && !$institution) ? $USER->get('id') : null;

        $select = '
            SELECT v.id, v.title, v.description, v.type, v.mtime, v.owner, v.group, v.institution, v.locked, v.ownerformat, v.urlid';
        $from = '
            FROM {view} v';
        $where = '
            WHERE ' . self::owner_sql((object) array('owner' => $userid, 'group' => $groupid, 'institution' => $institution));

        $order = '';
        $groupby = '';
        if (!empty($orderby)) {
            switch($orderby) {
                case 'latestcreated':
                    $order = 'v.ctime DESC,';
                    break;
                case 'latestmodified':
                    $order = 'v.mtime DESC,';
                    break;
                case 'latestviewed':
                    $order = 'v.atime DESC,';
                    break;
                case 'mostvisited':
                    $order = 'v.visits DESC,';
                    break;
                case 'mostcomments':
                    $select .= ', COUNT(DISTINCT acc.artefact) AS commentcount';
                    $from .= '
                        LEFT OUTER JOIN {artefact_comment_comment} acc ON (v.id = acc.onview)';
                    $groupby = ' GROUP BY v.id';
                    $order = 'COUNT(DISTINCT acc.artefact) DESC,';
                    break;
                default:
                    $order = '';
            }
        }

        $sort = '
            ORDER BY ' . $order . ' v.title, v.id';
        $values = array();

        if ($tag) { // Filter by the tag
            $from .= "
                INNER JOIN {view_tag} vt ON (vt.view = v.id AND vt.tag = ?)";
            $values[] = $tag;
        }
        else if ($query != '') { // Include matches on the title, description or tag
            $from .= "
                LEFT JOIN {view_tag} vt ON (vt.view = v.id AND vt.tag = ?)";
            $like = db_ilike();
            $where .= "
                AND (v.title $like '%' || ? || '%' OR v.description $like '%' || ? || '%' OR vt.tag = ?)";
            array_push($values, $query, $query, $query, $query);
        }

        if ($groupid && group_user_access($groupid) != 'admin') {
            $where .=  " AND v.type != 'grouphomepage'";
        }
        else if ($groupid && group_user_access($groupid) == 'admin') {
             $sort = ' ORDER BY ' . $order . ' v.type = \'grouphomepage\' desc, v.title, v.id';
        }
        if ($userid) {
            $select .= ',v.submittedtime, v.submittedstatus,
                g.id AS submitgroupid, g.name AS submitgroupname, g.urlid AS submitgroupurlid,
                h.wwwroot AS submithostwwwroot, h.name AS submithostname';
            $from .= '
                LEFT OUTER JOIN {group} g ON (v.submittedgroup = g.id AND g.deleted = 0)
                LEFT OUTER JOIN {host} h ON (v.submittedhost = h.wwwroot)';
            if (!empty($groupby)) {
                $groupby .= ', g.id, h.wwwroot';
            }
            $sort = '
                ORDER BY ' . $order . ' v.type = \'portfolio\', v.type, v.title, v.id';
        }

        // When using group by we need to get the count of how many rows are returned
        // and not the count value of the first row returned.
        if (!empty($groupby)) {
            $count = count_records_sql('SELECT COUNT(*) FROM (SELECT COUNT(v.id) ' . $from . $where . $groupby . ') AS count', $values);
        }
        else {
            $count = count_records_sql('SELECT COUNT(v.id) ' . $from . $where, $values);
        }
        $viewdata = get_records_sql_assoc($select . $from . $where . $groupby . $sort, $values, $offset, $limit);
        View::get_extra_view_info($viewdata, false);

        if ($viewdata) {
            foreach ($viewdata as $id => &$data) {
                $data['removable'] = self::can_remove_viewtype($data['type']);
                if (!empty($data['submittedstatus'])) {
                    $status = $data['submittedstatus'];
                    if (!empty($data['submitgroupid'])) {
                        $url = group_homepage_url((object) array('id' => $data['submitgroupid'], 'urlid' => $data['submitgroupurlid']));
                        $name = hsc($data['submitgroupname']);
                    }
                    else if (!empty($data['submithostwwwroot'])) {
                        $url = $data['submithostwwwroot'];
                        $name = hsc($data['submithostname']);
                    }
                    $time = (!empty($data['submittedtime'])) ? format_date(strtotime($data['submittedtime'])) : null;

                    if (!empty($status) && !empty($time)) {
                        $data['submittedto'] = get_string('viewsubmittedtogroupon', 'view', $url, $name, $time);
                    }
                    else if (!empty($status)) {
                        $data['submittedto'] = get_string('viewsubmittedtogroup', 'view', $url, $name);
                    }
                    if ($status == self::PENDING_RELEASE) {
                        $data['submittedto'] .= ' ' . get_string('submittedpendingrelease', 'view');
                    }
                }
            }
            $viewdata = array_values($viewdata);
        }

        return (object) array(
            'data'  => $viewdata,
            'count' => $count,
        );
    }

    public static function get_myviews_url($group=null, $institution=null, $query=null, $tag=null, $orderby=null) {
        $queryparams = array();

        if (!empty($tag)) {
            $queryparams[] = 'tag=' . urlencode($tag);
        }
        else if ($query != '') {
            $queryparams[] =  'query=' . urlencode($query);
        }
        if (!empty($orderby)) {
            $queryparams[] = 'orderby=' . urlencode($orderby);
        }

        if ($group) {
            $url = get_config('wwwroot') . 'view/groupviews.php';
            $queryparams[] = 'group=' . $group;
        }
        else if ($institution) {
            if ($institution == 'mahara') {
                $url = get_config('wwwroot') . 'admin/site/views.php';
            }
            else {
                $url = get_config('wwwroot') . 'view/institutionviews.php';
                $queryparams[] = 'institution=' . $institution;
            }
        }
        else {
            $url = get_config('wwwroot') . 'view/index.php';
        }

        if (!empty($queryparams)) {
            $url .= '?' . join('&', $queryparams);
        }

        return $url;
    }

    public static function views_by_owner($group=null, $institution=null) {
        global $USER;

        // Pagination configuration
        $setlimit = true;
        $limit = param_integer('limit', 0);
        $userlimit = get_account_preference($USER->get('id'), 'viewsperpage');
        if ($limit > 0 && $limit != $userlimit) {
            $USER->set_account_preference('viewsperpage', $limit);
        }
        else {
            $limit = $userlimit;
        }
        $offset = param_integer('offset', 0);
        $orderby = param_variable('orderby', null);

        $query  = param_variable('query', null);
        $tag    = param_variable('tag', null);

        $searchoptions = array(
            'titleanddescription' => get_string('titleanddescription', 'view'),
            'tagsonly' => get_string('tagsonly', 'view'),
        );

        if (!empty($tag)) {
            $searchtype = 'tagsonly';
            $searchdefault = $tag;
            $query = null;
        }
        else {
            $searchtype = 'titleanddescription';
            $searchdefault = $query;
        }

        $searchform = array(
            'name' => 'searchviews',
            'checkdirtychange' => false,
            'renderer' => 'oneline',
            'elements' => array(
                'query' => array(
                    'type' => 'text',
                    'title' => get_string('search') . ': ',
                    'defaultvalue' => $searchdefault,
                ),
                'type' => array(
                    'title'        => get_string('searchwithin'),
                    'hiddenlabel'  => true,
                    'type'         => 'select',
                    'options'      => $searchoptions,
                    'defaultvalue' => $searchtype,
                ),
                'orderby' => array(
                    'type' => 'select',
                    'title' => get_string('sortby'),
                    'options' => array('atoz' => get_string('defaultsort', 'view'),
                                       'latestcreated' => get_string('latestcreated', 'view'),
                                       'latestmodified' => get_string('latestmodified', 'view'),
                                       'latestviewed' => get_string('latestviewed', 'view'),
                                       'mostvisited' => get_string('mostvisited', 'view'),
                                       'mostcomments' => get_string('mostcomments', 'view'),
                                       ),
                    'defaultvalue' => $orderby,
                ),
                'setlimit' => array(
                    'type' => 'hidden',
                    'value' => $setlimit
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('search')
                )
            )
        );

        if ($group) {
            $searchform['elements']['group'] = array('type' => 'hidden', 'name' => 'group', 'value' => $group);
        }
        else if ($institution) {
            $searchform['elements']['institution'] = array('type' => 'hidden', 'name' => 'institution', 'value' => $institution);
        }

        $searchform = pieform($searchform);

        $data = self::get_myviews_data($limit, $offset, $query, $tag, $group, $institution, $orderby);

        $url = self::get_myviews_url($group, $institution, $query, $tag, $orderby);

        $pagination = build_pagination(array(
            'url'    => $url,
            'count'  => $data->count,
            'limit'  => $limit,
            'setlimit' => $setlimit,
            'offset' => $offset,
            'jumplinks' => 6,
            'numbersincludeprevnext' => 2,
        ));

        return array($searchform, $data, $pagination);
    }


    public function get_user_views($userid=null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }
        if ($views = get_records_sql_assoc(
            "SELECT v.*
            FROM {view} v
            WHERE v.owner = ?
            AND v.type NOT IN ('profile', 'dashboard')
            ORDER BY v.title, v.id
            ", array($userid))) {
            return $views;
        }
        return array();
    }

    public function get_template_views() {
        $views = get_records_sql_array(
            "SELECT v.*
               FROM {view} v
              WHERE v.owner = 0
              ORDER BY v.title, v.id", array());
        $results = array();
        if ($views) {
            foreach ($views as $view) {
                $view->displaytitle = get_string('template' . $view->type, 'view');
                $view->issitetemplate = true;
                $results[] = (array)$view;
            }
        }
        return $results;
    }

    public function get_layoutrows() {
        $layoutrows = array();
        $owner = $this->get('owner');
        $group = $this->get('group');
        $institution = $this->get('institution');
        $queryarray = array($owner);

        // get built-in layout options (owner=0) and any custom created layouts
        if (isset($owner)) {
            // view owned by individual
            $whereclause = '(ucl.usr = 0 OR ucl.usr = ?)';
        }
        else if (!empty($group)) {
            // view owned by group
            $whereclause = '(ucl.usr = 0 OR ucl.group = ?)';
            $queryarray = array($group);
        }
        else if (!empty($institution)) {
            // view owned by institution
            $whereclause = '(ucl.usr = 0 OR ucl.institution = ?)';
            $queryarray = array($institution);
        }
        else {
            throw new SystemException("View::get_layoutrows: No owner, group or institution set for view.");
        }

        $layoutsrowscols = get_records_sql_array('
                SELECT vlrc.viewlayout, vlrc.row, vlrc.columns
                FROM {view_layout_rows_columns} vlrc
                    JOIN {view_layout} vl ON vlrc.viewlayout = vl.id
                    JOIN {usr_custom_layout} ucl ON (vl.id = ucl.layout)
                WHERE ' . $whereclause, $queryarray);

        foreach ($layoutsrowscols as $layout) {
            $layoutrows[$layout->viewlayout][$layout->row] = $layout->columns;
        }
        return $layoutrows;
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
     * Returns an SQL snippet that can be used in a where clause to get views
     * with the given set of owners.
     *
     * Only one of the owner, group, institution fields is used.
     *
     * @param object $ownerobj An object that has view ownership information -
     *                         either the institution, group or owner fields set
     * @return array containing an sql string and an array of placeholder values
     */
    private static function multiple_owner_sql($ownerobj) {
        foreach (get_object_vars($ownerobj) as $column => $values) {
            if (is_array($values) && !empty($values)) {
                return array(
                    '"' . $column . '" IN (' . join(',', array_fill(0, count($values), '?')) . ')',
                    $values,
                );
            }
        }
        return array(self::owner_sql($ownerobj), array());
    }

    /**
     * Get all views visible to a user.  Complicated because a view v
     * is visible to a user u at time t if any of the following are
     * true:
     *
     * - u is a site admin
     * - v is owned by u
     * - v is owned by a group g, and u has a role (within g) with view editing permission
     * - v is publically visible at t (in view_access)
     * - v is visible to logged in users at t (in view_access)
     * - v is visible to friends at t, and u is a friend of the view owner (in view_access)
     * - v is visible to institution at t, and u is a member of the institution (in view_access)
     * - v is visible to u at t (in view_access_usr)
     * - v is visible to all roles of group g at t, and u is a member of g (view_access_group)
     * - v is visible to users with role r of group g at t, and u is a member of g with role r (view_access_group)
     *
     * @param string   $query       Search string
     * @param string   $ownerquery  Search string for owner
     * @param StdClass $ownedby     Only return views owned by this owner (owner, group, institution)
     * @param StdClass $copyableby  Only return views copyable by this owner (owner, group, institution)
     * @param integer  $limit
     * @param integer  $offset
     * @param bool     $extra       Return full set of properties on each view including an artefact list
     * @param array    $sort        Order by, each element of the array is an array containing "column" (string) and "desc" (boolean)
     * @param array    $types       List of view types to filter by
     * @param bool     $collection  Use query against collection names and descriptions
     * @param array    $accesstypes Only return views visible due to the given access types
     * @param array    $tag         Only return views with this tag
     *
     */
    public static function view_search($query=null, $ownerquery=null, $ownedby=null, $copyableby=null, $limit=null, $offset=0,
                                       $extra=true, $sort=null, $types=null, $collection=false, $accesstypes=null, $tag=null) {
        global $USER;
        $admin = $USER->get('admin');
        $loggedin = $USER->is_logged_in();
        $viewerid = $USER->get('id');

        // Query parameters
        $fromparams = array();
        $whereparams = array();

        $from = '
            FROM {view} v
            LEFT OUTER JOIN {collection_view} cv ON cv.view = v.id
            LEFT OUTER JOIN {collection} c ON cv.collection = c.id
            LEFT OUTER JOIN {usr} qu ON (v.owner = qu.id)
            ';

        $where = '
            WHERE (v.owner IS NULL OR v.owner > 0)
                AND (v.group IS NULL OR v.group NOT IN (SELECT id FROM {group} WHERE deleted = 1))
                AND (qu.suspendedctime is null OR v.owner = ?)';

        $whereparams[] = $viewerid;

        if (is_array($types) && !empty($types)) {
            $where .= ' AND v.type IN (';
        }
        else {
            $where .= ' AND v.type NOT IN (';
            if ($admin) {
                $types = array('profile', 'dashboard');
            }
            else {
                $types = array('profile', 'dashboard', 'grouphomepage');
            }
        }
        $where .= join(',', array_map('db_quote', $types)) . ')';

        if ($ownedby) {
            $where .= ' AND v.' . self::owner_sql($ownedby);
        }

        if ($copyableby) {
            $where .= '
                AND (v.template = 1 OR (v.' . self::owner_sql($copyableby) . '))';
        }

        $like = db_ilike();

        if ($query) { // Include matches on the title, description, tag or user

            // Include the group and institution tables to query on the owners's name.
            // Not same as $ownerquery as that 'AND's the owners.
            // Please note that these tables are joined again below
            // when $ownerquery is specified. Hence, the extra 'q' on the
            // table alias.
            $from .= "
                LEFT JOIN {view_tag} vt ON (vt.view = v.id AND vt.tag = ?)
                LEFT OUTER JOIN {group} qqg ON (v.group = qqg.id)
                LEFT OUTER JOIN {institution} qqi ON (v.institution = qqi.name)";
            if (strpos(strtolower(get_config('sitename')), strtolower($query)) !== false) {
                $sitequery = " OR qqi.name = 'mahara'";
            }
            else {
                $sitequery = '';
            }

            $where .= "
                AND (v.title $like '%' || ? || '%'
                    OR v.description $like '%' || ? || '%'
                    OR vt.tag = ?
                    OR qu.preferredname $like '%' || ? || '%'
                    OR qu.firstname $like '%' || ? || '%'
                    OR qu.lastname $like '%' || ? || '%'
                    OR qqg.name $like '%' || ? || '%'
                    OR qqi.displayname $like '%' || ? || '%'
                    $sitequery
                    ";
            array_push($fromparams, $query);
            array_push($whereparams, $query, $query, $query, $query, $query, $query, $query, $query);
            if ($collection) {
                $where .= "
                    OR c.name $like '%' || ? || '%' OR c.description $like '%' || ? || '%' ";
                array_push($whereparams, $query, $query);
            }
            if (get_config('searchusernames')) {
                // If the site setting 'Search usernames' is enabled, allow searching by username.
                $where .= "
                    OR qu.username $like '%' || ? || '%' ";
                array_push($whereparams, $query);
            }
            $where .= ")";
        }
        else if ($tag) { // Filter by the tag
            $from .= "
                INNER JOIN {view_tag} vt ON (vt.view = v.id AND vt.tag = ?)";
            $fromparams[] = $tag;
        }

        if (is_array($accesstypes)) {
            $editableviews = in_array('editable', $accesstypes);
        }
        else if ($loggedin) {
            $editableviews = true;
            $accesstypes = array('public', 'loggedin', 'friend', 'user', 'group', 'institution');
        }
        else {
            $editableviews = false;
            $accesstypes = array('public');
        }

        if ($editableviews) {
            $editablesql = "v.owner = ?      -- user owns the view
                    OR v.group IN (  -- group view, editable by the user
                        SELECT m.group
                        FROM {group_member} m JOIN {group} g ON m.member = ? AND m.group = g.id
                        WHERE m.role = 'admin' OR g.editroles = 'all' OR (g.editroles != 'admin' AND m.role != 'member')
                    )";
            $whereparams[] = $viewerid;
            $whereparams[] = $viewerid;
        }
        else {
            $editablesql = 'FALSE';
        }

        $accesssql = array();

        foreach ($accesstypes as $t) {
            if ($t == 'public') {
                $accesssql[] = "v.id IN ( -- public access
                                SELECT va.view
                                FROM {view_access} va
                                WHERE va.accesstype = 'public'
                                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                            )";
            }
            else if ($t == 'loggedin') {
                $accesssql[] = "v.id IN ( -- loggedin access
                                SELECT va.view
                                FROM {view_access} va
                                WHERE va.accesstype = 'loggedin'
                                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                            )";
            }
            else if ($t == 'friend') {
                $accesssql[] = "v.id IN ( -- friend access
                                SELECT va.view
                                FROM {view_access} va
                                    JOIN {view} vf ON va.view = vf.id AND vf.owner IS NOT NULL
                                    JOIN {usr_friend} f ON ((f.usr1 = ? AND f.usr2 = vf.owner) OR (f.usr1 = vf.owner AND f.usr2 = ?))
                                WHERE va.accesstype = 'friends'
                                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                            )";
                $whereparams[] = $viewerid;
                $whereparams[] = $viewerid;
            }
            else if ($t == 'user') {
                $accesssql[] = "v.id IN ( -- user access
                                SELECT va.view
                                FROM {view_access} va
                                WHERE va.usr = ?
                                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                            )";
                $whereparams[] = $viewerid;
            }
            else if ($t == 'group') {
                $accesssql[] = "v.id IN ( -- group access
                                SELECT va.view
                                FROM {view_access} va
                                    JOIN {group_member} m ON va.group = m.group AND (va.role = m.role OR va.role IS NULL)
                                WHERE
                                    m.member = ?
                                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                            )";
                $whereparams[] = $viewerid;
            }
            else if ($t == 'institution') {
                $accesssql[] = "v.id IN ( -- institution access
                                SELECT va.view
                                FROM {view_access} va
                                    JOIN {usr_institution} ui ON va.institution = ui.institution
                                WHERE
                                    ui.usr = ?
                                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                            )";
                $whereparams[] = $viewerid;
            }
        }

        if (!empty($accesssql)) {
            $accesssql = '( -- user has permission to see the view
                        (v.startdate IS NULL OR v.startdate < current_timestamp)
                        AND (v.stopdate IS NULL OR v.stopdate > current_timestamp)
                        AND (' . join(' OR ', $accesssql) . '))';
        }
        else {
            $accesssql = 'FALSE';
        }

        $where .= "
                AND ($editablesql
                    OR $accesssql)";

        if (!$ownedby && $ownerquery) {
            $from .= '
            LEFT OUTER JOIN {group} qg ON (v.group = qg.id)
            LEFT OUTER JOIN {institution} qi ON (v.institution = qi.name)';
            if (strpos(strtolower(get_config('sitename')), strtolower($ownerquery)) !== false) {
                $sitequery = " OR qi.name = 'mahara'";
            }
            else {
                $sitequery = '';
            }
            $where .= "
                AND (
                    qu.preferredname $like '%' || ? || '%'
                    OR qu.firstname $like '%' || ? || '%'
                    OR qu.lastname $like '%' || ? || '%'
                    OR qg.name $like '%' || ? || '%'
                    OR qi.displayname $like '%' || ? || '%'
                    $sitequery
                )";
            $whereparams = array_merge($whereparams, array($ownerquery,$ownerquery,$ownerquery,$ownerquery,$ownerquery));
        }

        $orderby = 'title ASC';
        if (!empty($sort)) {
            $orderby = '';
            foreach ($sort as $item) {
                if (!preg_match('/^[a-zA-Z_0-9\'="]+$/', $item['column'])) {
                    continue; // skip this item (it fails validation)
                }

                if (!empty($orderby)) {
                    $orderby .= ', ';
                }

                if ($item['column'] == 'lastchanged') {
                    // We need the date of the last comment on each view
                    $from .= 'LEFT OUTER JOIN (
                SELECT c.onview, MAX(a.mtime) AS lastcomment
                FROM {artefact_comment_comment} c JOIN {artefact} a ON c.artefact = a.id AND c.deletedby IS NULL AND c.private = 0
                GROUP BY c.onview
            ) l ON v.id = l.onview
            ';
                    $orderby .= 'GREATEST(lastcomment, v.mtime)';
                }
                else if ($item['column'] == 'ownername') {
                    // Join on usr, group, and institution and order by name
                    $from .= 'LEFT OUTER JOIN {usr} su ON su.id = v.owner
            LEFT OUTER JOIN {group} sg ON sg.id = v.group
            LEFT OUTER JOIN {institution} si ON si.name = v.institution
            ';
                    $orderby .= "COALESCE(sg.name, si.displayname, CASE WHEN su.preferredname IS NOT NULL AND su.preferredname != '' THEN su.preferredname ELSE su.firstname || ' ' || su.lastname END)";
                }
                else {
                    $orderby .= (!empty($item['tablealias']) ? $item['tablealias'] : 'v') . '.' . $item['column'];
                }

                if ($item['desc']) {
                    $orderby .= ' DESC';
                }
                else {
                    $orderby .= ' ASC';
                }
            }
        }

        $ph = array_merge($fromparams, $whereparams);
        $count = count_records_sql('SELECT COUNT(*) ' . $from . $where, $ph);

        $viewdata = get_records_sql_assoc('
            SELECT
                v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution,
                v.template, v.mtime, v.ctime,
                c.id AS collid, c.name, v.type, v.urlid, v.submittedtime, v.submittedgroup, v.submittedhost
            ' . $from . $where . '
            ORDER BY ' . $orderby . ', v.id ASC',
            $ph, $offset, $limit
        );

        if ($viewdata) {
            if ($extra) {
                View::get_extra_view_info($viewdata, false);
            }
        }
        else {
            $viewdata = array();
        }

        if (!empty($limit)) {
            return (object) array(
                'ids'   => array_keys($viewdata),
                'data'  => array_values($viewdata),
                'count' => $count,
                'limit' => $limit,
                'offset' => $offset,
            );
        }
        else {
            return (object) array(
                'ids'   => array_keys($viewdata),
                'data'  => array_values($viewdata),
                'count' => $count,
            );
        }

    }
    /**
     * Get views that have been shared with a user using the given
     * access types.
     *
     * @param string   $query       Search string for title/description
     * @param string   $tag         Return only views with this tag
     * @param integer  $limit
     * @param integer  $offset
     * @param string   $sort        Either 'lastchanged', 'ownername', or a column of the view table
     * @param string   $sortdir     Ascending/descending
     * @param array    $accesstypes Types of view access
     *
     */
    public static function shared_to_user($query=null, $tag=null, $limit=null, $offset=0, $sort='lastchanged', $sortdir='desc',
        $accesstypes=null) {

        $sort = array(
            array(
                'column' => $sort,
                'desc'   => $sortdir == 'desc',
            )
        );

        $result = self::view_search(
            $query, null, null, null, $limit, $offset, true, $sort, array('portfolio'), false, $accesstypes, $tag
        );

        if (!$result->count) {
            return $result;
        }

        if (is_postgres()) {
            $lastcomments = '
                    SELECT DISTINCT ON (l.onview) l.onview, a.mtime, a.author, a.authorname, a.id, a.description
                    FROM {artefact_comment_comment} l
                        JOIN {artefact} a ON (l.artefact = a.id AND l.deletedby IS NULL AND l.private = 0)
                    ORDER BY l.onview, a.mtime DESC';
        }
        else if (is_mysql()) {
            $lastcomments = '
                    SELECT onview, mtime, author, authorname, id, description
                    FROM (
                        SELECT l.onview, a.mtime, a.author, a.authorname, a.id, a.description
                        FROM {artefact_comment_comment} l
                            JOIN {artefact} a ON (l.artefact = a.id AND l.deletedby IS NULL AND l.private = 0)
                        ORDER BY a.mtime DESC
                    ) temp1
                    GROUP BY onview';
        }

        // Get additional data: number of comments, last commenter
        $commentdata = get_records_sql_assoc('
            SELECT
                v.id, last.mtime AS lastcommenttime, last.author AS commentauthor, last.authorname AS commentauthorname,
                last.description AS commenttext, last.id AS commentid,
                COUNT(c.artefact) AS commentcount
            FROM {view} v
                LEFT JOIN {artefact_comment_comment} c ON (c.onview = v.id AND c.deletedby IS NULL AND c.private = 0)
                LEFT JOIN (' . $lastcomments . '
                ) last ON last.onview = v.id
            WHERE v.id IN (' . join(',', array_fill(0, count($result->data), '?')) . ')
            GROUP BY
                v.id, last.mtime, last.author, last.authorname, last.description, last.id',
            $result->ids
        );

        $fields = array('lastcommenttime', 'commentauthor', 'commentauthorname', 'commenttext', 'commentid', 'commentcount');

        foreach ($result->data as &$v) {
            if (isset($commentdata[$v['id']])) {
                foreach ($fields as $f) {
                    $v[$f] = $commentdata[$v['id']]->$f;
                }
            }
        }

        return $result;
    }


    /**
     * Search view owners.
     */
    public static function search_view_owners($query=null, $template=null, $limit=null, $offset=0) {
        if ($template) {
            $tsql = ' AND v.template = 1';
        }
        else if ($template === false) {
            $tsql = ' AND v.template = 0';
        }
        else {
            $tsql = '';
        }

        if ($query) {
            $ph = array($query);
            $qsql = ' WHERE display ' . db_ilike() . " '%' || ? || '%' ";
        }
        else {
            $ph = array();
            $qsql = '';
        }

        if (is_mysql()) {
            $uid = 'u.id';
            $gid = 'g.id';
        }
        else {
            $uid = 'CAST (u.id AS TEXT)';
            $gid = 'CAST (g.id AS TEXT)';
        }

        $sql = "
                SELECT
                    'user' AS ownertype,
                    CASE WHEN u.preferredname IS NULL OR u.preferredname = '' THEN u.firstname || ' ' || u.lastname
                    ELSE u.preferredname END AS display,
                    $uid, COUNT(v.id)
                FROM {usr} u INNER JOIN {view} v ON (v.owner = u.id AND v.type = 'portfolio')
                WHERE u.deleted = 0 $tsql
                GROUP BY ownertype, display, u.id
            UNION
                SELECT 'group' AS ownertype, g.name AS display, $gid, COUNT(v.id)
                FROM {group} g INNER JOIN {view} v ON (g.id = v.group)
                WHERE g.deleted = 0 $tsql
                GROUP BY ownertype, display, g.id
            UNION
                SELECT 'institution' AS ownertype, i.displayname AS display, i.name AS id, COUNT(v.id)
                FROM {institution} i INNER JOIN {view} v ON (i.name = v.institution)
                WHERE TRUE $tsql
                GROUP BY ownertype, display, i.name ORDER BY display";

        $count = count_records_sql("SELECT COUNT(*) FROM ($sql) q $qsql", $ph);
        $data = get_records_sql_array("SELECT * FROM ($sql) q $qsql ORDER BY ownertype != 'institution', id != 'mahara', ownertype", $ph, $offset, $limit);

        foreach ($data as &$r) {
            if ($r->ownertype == 'institution' && $r->id == 'mahara') {
                $r->display = get_config('sitename');
            }
        }

        return array(
            'data'  => array_values($data),
            'count' => $count,
            'limit' => $limit,
            'offset' => $offset,
        );

    }


    /**
     * Get views which have been explicitly shared to a group and are
     * not owned by the group excluding the view in collections
     */
    public static function get_sharedviews_data($limit=10, $offset=0, $groupid) {
        global $USER;
        $userid = $USER->get('id');
        require_once(get_config('libroot') . 'group.php');
        if (!group_user_access($groupid)) {
            throw new AccessDeniedException(get_string('accessdenied', 'error'));
        }
        $from = '
            FROM {view} v
            INNER JOIN {view_access} a ON (a.view = v.id)
            INNER JOIN {group_member} m ON (a.group = m.group AND (a.role = m.role OR a.role IS NULL))
            WHERE a.group = ? AND m.member = ? AND (v.group IS NULL OR v.group != ?)
               AND NOT EXISTS (SELECT 1 FROM {collection_view} cv WHERE cv.view = v.id)';
        $ph = array($groupid, $userid, $groupid);

        $count = count_records_sql('SELECT COUNT(DISTINCT(v.id)) ' . $from, $ph);
        $viewdata = get_records_sql_assoc('
            SELECT DISTINCT v.id,v.title,v.startdate,v.stopdate,v.description,v.group,v.owner,v.ownerformat,v.institution,v.urlid ' . $from . '
            ORDER BY v.title, v.id',
            $ph, $offset, $limit
        );

        if ($viewdata) {
            View::get_extra_view_info($viewdata, false);
        }
        else {
            $viewdata = array();
        }

        return (object) array(
            'data'   => array_values($viewdata),
            'count'  => $count,
            'limit'  => $limit,
            'offset' => $offset,
        );
    }

    /**
     * Get collections which have been explicitly shared to a group and are
     * not owned by the group
     * @param $limit, $offset for pagination
     * @param $groupid
     * @return array of collections
     */
    public static function get_sharedcollections_data($limit=10, $offset=0, $groupid) {
        global $USER;

        $userid = $USER->get('id');
        require_once(get_config('libroot') . 'group.php');

        if (!group_user_access($groupid)) {
            throw new AccessDeniedException(get_string('accessdenied', 'error'));
        }

        $from = '
            FROM {collection} c
                INNER JOIN {collection_view} cv ON (cv.collection = c.id)
                INNER JOIN {view_access} a ON (a.view = cv.view)
                INNER JOIN {group_member} m ON (a.group = m.group AND (a.role = m.role OR a.role IS NULL))
            WHERE a.group = ? AND m.member = ? AND (c.group IS NULL OR c.group != ?)';
        $ph = array($groupid, $userid, $groupid);

        $count = count_records_sql('SELECT COUNT(DISTINCT c.id) ' . $from, $ph);
        $collectiondata = get_records_sql_assoc('
            SELECT DISTINCT c.id,c.name,c.description,c.owner,c.group,c.institution ' . $from . '
            ORDER BY c.name, c.id',
            $ph, $offset, $limit
        );

        if ($collectiondata) {
            View::get_extra_collection_info($collectiondata);
        }
        else {
            $collectiondata = array();
        }

        return (object) array(
            'data'   => array_values($collectiondata),
            'count'  => $count,
            'limit'  => $limit,
            'offset' => $offset,
        );
    }

    public static function get_extra_view_info(&$viewdata, $getartefacts=true, $gettags=true) {
        if ($viewdata) {
            // Get view owner details for display
            $owners = array();
            $groups = array();
            $institutions = array();
            foreach ($viewdata as $v) {
                if (!empty($v->owner) && !isset($owners[$v->owner])) {
                    $owners[$v->owner] = (int)$v->owner;
                }
                else if (!empty($v->group) && !isset($groups[$v->group])) {
                    $groups[$v->group] = (int)$v->group;
                }
                else if (!empty($v->institution) && !isset($institutions[$v->institution])) {
                    $institutions[$v->institution] = $v->institution;
                }
            }

            $viewidlist = join(',', array_map('intval', array_keys($viewdata)));
            if ($getartefacts) {
                $artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title, a.artefacttype, t.plugin
                    FROM {view_artefact} va
                    INNER JOIN {artefact} a ON va.artefact = a.id
                    INNER JOIN {artefact_installed_type} t ON a.artefacttype = t.name
                    WHERE va.view IN (' . $viewidlist . ')
                    GROUP BY va.view, va.artefact, a.title, a.artefacttype, t.plugin
                    ORDER BY a.title, va.artefact', '');
                if ($artefacts) {
                    foreach ($artefacts as $artefactrec) {
                        safe_require('artefact', $artefactrec->plugin);
                        $classname = generate_artefact_class_name($artefactrec->artefacttype);
                        $artefactobj = new $classname(0, array('title' => $artefactrec->title));
                        $artefactobj->set('dirty', false);
                        if (!$artefactobj->in_view_list()) {
                            continue;
                        }
                        $artname = $artefactobj->display_title(30);
                        if (strlen($artname)) {
                            $viewdata[$artefactrec->view]->artefacts[] = array('id'    => $artefactrec->artefact,
                                                                               'title' => $artname);
                        }
                    }
                }
            }
            if ($gettags) {
                $tags = get_records_select_array('view_tag', 'view IN (' . $viewidlist . ')');
                if ($tags) {
                    foreach ($tags as &$tag) {
                        $viewdata[$tag->view]->tags[] = $tag->tag;
                    }
                }
            }
            if (!empty($owners)) {
                global $USER;
                $userid = $USER->get('id');
                $fields = array(
                    'id', 'username', 'firstname', 'lastname', 'preferredname', 'admin', 'staff', 'studentid', 'email',
                    'profileicon', 'urlid', 'suspendedctime',
                );
                if (count($owners) == 1 && isset($owners[$userid])) {
                    $owners = array($userid => new StdClass);
                    foreach ($fields as $f) {
                        $owners[$userid]->$f = $USER->get($f);
                    }
                }
                else {
                    $owners = get_records_select_assoc(
                        'usr', 'id IN (' . join(',', array_fill(0, count($owners), '?')) . ')', $owners, '',
                        join(',', $fields)
                    );
                }
            }
            if (!empty($groups)) {
                require_once('group.php');
                $groups = get_records_select_assoc('group', 'id IN (' . join(',', $groups) . ')', null, '', 'id,name,urlid');
            }
            if (!empty($institutions)) {
                $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
                $institutions['mahara']->displayname = get_config('sitename');
            }

            $wwwroot = get_config('wwwroot');
            $needsubdomain = get_config('cleanurlusersubdomains');

            foreach ($viewdata as &$v) {
                $v->anonymous = FALSE;
                if (!empty($v->owner)) {
                    $v->sharedby = View::owner_name($v->ownerformat, $owners[$v->owner]);
                    $v->user = $owners[$v->owner];

                    // Get a real view object so we can do the checks.
                    $view_obj = new View($v->id);
                    $v->anonymous = $view_obj->is_anonymous();
                    $v->staff_or_admin = $view_obj->is_staff_or_admin_for_page();
                }
                else if (!empty($v->group)) {
                    $v->sharedby = $groups[$v->group]->name;
                    $v->groupdata = $groups[$v->group];
                }
                else if (!empty($v->institution)) {
                    $v->sharedby = $institutions[$v->institution]->displayname;
                }
                $v = (array)$v;

                // Now that we have the owner & group records, create a temporary View object
                // so that we can use display_title_editing and get_url methods.
                $view = new View(0, $v);
                $view->set('dirty', false);
                $v['displaytitle'] = $view->display_title_editing();
                $v['url'] = $view->get_url(false);
                $v['fullurl'] = $needsubdomain ? $view->get_url(true) : ($wwwroot . $v['url']);
            }
        }
    }

    /**
     * Get more info for the collections: owner, url, tags
     *
     * @param array a list of collections $collectiondata
     * @return array updated collection data
     */
    public static function get_extra_collection_info(&$collectiondata, $gettags=true) {
        if ($collectiondata) {
            // Get view owner details for display
            $owners = array();
            $groups = array();
            $institutions = array();
            foreach ($collectiondata as $c) {
                if (!empty($c->owner) && !isset($owners[$c->owner])) {
                    $owners[$c->owner] = (int)$c->owner;
                }
                else if (!empty($c->group) && !isset($groups[$c->group])) {
                    $groups[$c->group] = (int)$c->group;
                }
                else if (!empty($c->institution) && !isset($institutions[$c->institution])) {
                    $institutions[$c->institution] = $c->institution;
                }
            }
            if ($gettags) {
                $collectionidlist = join(',', array_map('intval', array_keys($collectiondata)));
                $tags = get_records_select_array('collection_tag', 'collection IN (' . $collectionidlist . ')');
                if ($tags) {
                    foreach ($tags as &$tag) {
                        $collectiondata[$tag->collection]->tags[] = $tag->tag;
                    }
                }
            }
            if (!empty($owners)) {
                global $USER;
                $userid = $USER->get('id');
                $fields = array(
                    'id', 'username', 'firstname', 'lastname', 'preferredname', 'admin', 'staff', 'studentid', 'email',
                    'profileicon', 'urlid', 'suspendedctime',
                );
                if (count($owners) == 1 && isset($owners[$userid])) {
                    $owners = array($userid => new StdClass);
                    foreach ($fields as $f) {
                        $owners[$userid]->$f = $USER->get($f);
                    }
                }
                else {
                    $owners = get_records_select_assoc(
                        'usr', 'id IN (' . join(',', array_fill(0, count($owners), '?')) . ')', $owners, '',
                        join(',', $fields)
                    );
                }
            }
            if (!empty($groups)) {
                $groups = get_records_select_assoc('group', 'id IN (' . join(',', $groups) . ')', null, '', 'id,name,urlid');
            }
            if (!empty($institutions)) {
                $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
                $institutions['mahara']->displayname = get_config('sitename');
            }

            $wwwroot = get_config('wwwroot');
            $needsubdomain = get_config('cleanurlusersubdomains');

            foreach ($collectiondata as &$c) {
                if (!empty($c->owner)) {
                    $c->sharedby = display_name($owners[$c->owner]);
                    $c->user = $owners[$c->owner];
                }
                else if (!empty($c->group)) {
                    $c->sharedby = $groups[$c->group]->name;
                    $c->groupdata = $groups[$c->group];
                }
                else if (!empty($c->institution)) {
                    $c->sharedby = $institutions[$c->institution]->displayname;
                }
                $c = (array)$c;

                // Now that we have the owner & group records, create a temporary Collection object
                // so that we can use get_url method.
                require_once(get_config('libroot') . 'collection.php');

                $collection = new Collection(0, $c);
                $c['url'] = $collection->get_url(false);
                $c['fullurl'] = $needsubdomain ? $collection->get_url(true) : ($wwwroot . $c['url']);
            }
        }
    }

    public static function set_nav($group, $institution, $share=false, $collection=false) {
        if ($group) {
            define('MENUITEM', $share ? 'groups/share' : 'groups/views');
            define('GROUP', $group);
        }
        else if ($institution == 'mahara') {
            define('ADMIN', 1);
            define('MENUITEM', $share ? 'configsite/share' : 'configsite/siteviews');
        }
        else if ($institution) {
            define('INSTITUTIONALADMIN', 1);
            define('MENUITEM', $share ? 'manageinstitutions/share' : 'manageinstitutions/institutionviews');
        }
        else if ($collection) {
            define('MENUITEM', 'myportfolio/collection');
        }
        else {
            define('MENUITEM', $share ? 'myportfolio/share' : 'myportfolio/views');
        }
    }

    public function set_edit_nav() {
        if ($this->group) {
            // Don't display the group nav; 5 levels of menu is too many
            define('MENUITEM', 'groups');
            define('GROUP', $this->group);
            define('NOGROUPMENU', 1);
        }
        else if ($this->institution == 'mahara' || $this->owner == "0") {
            define('ADMIN', 1);
            define('MENUITEM', 'configsite/siteviews');
        }
        else if ($this->institution) {
            define('INSTITUTIONALADMIN', 1);
            define('MENUITEM', 'manageinstitutions/institutionviews');
        }
        else {
            define('MENUITEM', 'myportfolio/views');
        }
    }

    public function ownership() {
        if ($this->group) {
            return array('type' => 'group', 'id' => $this->group);
        }
        if ($this->owner) {
            return array('type' => 'user', 'id' => $this->owner);
        }
        if ($this->institution) {
            return array('type' => 'institution', 'id' => $this->institution);
        }
        return null;
    }


    public function copy_contents($template) {
        $this->set('numcolumns', $template->get('numcolumns'));
        $this->set('numrows', $template->get('numrows'));
        $this->set('layout', $template->get('layout'));
        $this->set('description', $template->get('description'));
        $this->set('tags', $template->get('tags'));
        $this->set('columnsperrow', $template->get('columnsperrow'));
        $blocks = get_records_array('block_instance', 'view', $template->get('id'));
        $numcopied = array('blocks' => 0, 'artefacts' => 0);
        if ($blocks) {
            $artefactcopies = array(); // Correspondence between original artefact ids and id of the copy
            foreach ($blocks as $b) {
                safe_require('blocktype', $b->blocktype);
                $oldblock = new BlockInstance($b->id, $b);
                if ($oldblock->copy($this, $template, $artefactcopies)) {
                    $numcopied['blocks']++;
                }
            }
            // Go back and fix up artefact references in the new artefacts so
            // they also point to new artefacts.
            if ($artefactcopies) {
                foreach ($artefactcopies as $oldid => $copyinfo) {
                    $a = artefact_instance_from_id($copyinfo->newid);
                    $a->update_artefact_references($this, $template, $artefactcopies, $oldid);
                    $a->commit();
                }
            }
            $numcopied['artefacts'] = count($artefactcopies);
        }
        return $numcopied;
    }

    /**
     * Generates a title for a newly created View
     */
    private static function new_title($title, $ownerdata) {
        $taken = get_column_sql('
            SELECT title
            FROM {view}
            WHERE ' . self::owner_sql($ownerdata) . "
                AND title LIKE ? || '%'", array($title));
        $ext = ''; $i = 0;
        if ($taken) {
            while (in_array($title . $ext, $taken)) {
                $ext = ' (' . ++$i . ')';
            }
        }
        return $title . $ext;
    }

    /**
     * Get a simplified name for this view for use in a url, which must be unique for a
     * a given owner.
     */
    public static function new_urlid($desired, $ownerdata) {
        $maxlen = 100;
        $desired = strtolower(substr($desired, 0, $maxlen));
        $taken = get_column_sql('
            SELECT urlid
            FROM {view}
            WHERE urlid LIKE ?
                AND ' . self::owner_sql($ownerdata),
            array(substr($desired, 0, $maxlen - 6) . '%')
        );
        if (!$taken) {
            return $desired;
        }

        $i = 1;
        $newname = substr($desired, 0, $maxlen - 2) . '-1';
        while (in_array($newname, $taken)) {
            $i++;
            $newname = substr($desired, 0, $maxlen - strlen($i) - 1) . '-' . $i;
        }
        return $newname;
    }

    public static function get_templatesearch_data(&$search) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $search->sort = (isset($search->sort)) ? $search->sort : null; // for backwards compatibility
        $results = self::view_search($search->query, $search->ownerquery, null, $search->copyableby, $search->limit, $search->offset, true, $search->sort, null, true);
        $oldcollid = null;
        foreach ($results->data as &$r) {
            if (!empty($search->sort)) {
                $collid = ($r['collid'] == $oldcollid) ? null : $r['collid'];
            }
            else {
                $collid = $r['collid'];
            }
            $r['form'] = pieform(create_view_form($search->copyableby->group, $search->copyableby->institution, $r['id'], $collid));
            $oldcollid = $r['collid'];
        }

        $params = array();
        if (isset($search->query) && ($search->query != '')) {
            $params['viewquery'] = $search->query;
        }
        if (isset($search->ownerquery) && ($search->ownerquery != '')) {
            $params['ownerquery'] = $search->ownerquery;
        }
        if (!empty($search->group)) {
            $params['group'] = $search->group;
        }
        if (!empty($search->institution)) {
            $params['institution'] = $search->institution;
        }
        if (!empty($search->collection)) {
            $params['searchcollection'] = $search->collection;
        }
        $params['viewlimit'] = $search->limit;

        $smarty = smarty_core();
        $smarty->assign_by_ref('results', $results->data);
        $search->html = $smarty->fetch('view/templatesearchresults.tpl');
        $search->count = $results->count;

        $search->pagination = build_pagination(array(
            'id' => 'templatesearch_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'view/choosetemplate.php' . (!empty($params) ? ('?' . http_build_query($params)) : ''),
            'count' => $results->count,
            'limit' => $search->limit,
            'offset' => $search->offset,
            'jumplinks' => 6,
            'numbersincludeprevnext' => 2,
            'offsetname' => 'viewoffset',
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'resultcounttextsingular' => get_string('view', 'view'),
            'resultcounttextplural' => get_string('views', 'view'),
        ));
    }

    public static function new_token($viewid, $visible=1) {
        if (!$visible) {
            // Currently it only makes sense to have one invisible key per view.
            // They are only used during view submission, and a view can only be
            // submitted to one group or remote host at any one time.
            delete_records_select('view_access', 'view = ? AND token IS NOT NULL AND visible = 0', array($viewid));
        }

        $data = new StdClass;
        $data->view    = $viewid;
        $data->visible = (int) $visible;
        $data->token   = get_random_key(20);
        $data->ctime   = db_format_timestamp(time());

        while (record_exists('view_access', 'token', $data->token)) {
            $data->token = get_random_key(20);
        }
        insert_record('view_access', $data);
        return $data;
    }

    /**
     * Retrieve the invisible key for this view, if there is one. (A view can only have one
     * invisible key, because it can only be submitted to one place at a time.)
     * @param int $viewid
     * @return mixed Returns a boolean FALSE if there is no invisible token, a data object if there is one
     */
    public static function get_invisible_token($viewid) {
        return get_record_select('view_access', 'view = ? AND token IS NOT NULL AND visible = 0', array($viewid), 'view, visible, token, ctime');
    }

    public function owner_link() {
        if ($this->owner) {
            return profile_url($this->get_owner_object());
        }
        else if ($this->group) {
            return group_homepage_url($this->get_group_object());
        }
        else if ($this->institution) {
            return get_config('wwwroot') . 'institution/index.php?institution=' . $this->institution;
        }
        return null;
    }

    public function display_title($long=true, $titlelink=true, $includeowner=true) {
        if ($this->type == 'profile') {
            $title = hsc(display_name($this->owner, null, true));
            if ($long) {
                return get_string('usersprofile', 'mahara', $title);
            }
            return $title;
        }
        if ($this->type == 'dashboard') {
            return get_string('dashboardviewtitle', 'view');
        }

        $ownername = hsc($this->formatted_owner());

        if ($this->type == 'grouphomepage') {
            return get_string('aboutgroup', 'group', $ownername);
        }

        $ownerlink = null;
        if ($includeowner) {
            $ownerlink = $this->owner_link();
        }

        if ($titlelink) {
            $title = '<a href="' . $this->get_url() . '">' . hsc($this->title) . '</a>';
        }
        else {
            $title = hsc($this->title);
        }

        if (isset($ownerlink)) {
            return get_string('viewtitleby', 'view', $title, $ownerlink, $ownername);
        }

        return $title;
    }

    public function display_author() {
        $view = null;

        if (!empty($this->owner)) {
            $userobj = new User();
            $userobj->find_by_id($this->owner);
            $view = $userobj->get_profile_view();

            // Hide author if profile isn't visible to user
            if (!$view || !can_view_view($view)) {
                return null;
            }
        }
        else if (!empty($this->group)) {
            $view = group_get_homepage_view($this->group);

            // Hide author if profile isn't visible to user
            if (!$view || !can_view_view($view)) {
                return null;
            }
        }
        else if (!empty($this->institution)) {
            global $USER;
            if (!$USER->is_logged_in() || (
                    !get_field('institution', 'registerallowed', 'name', $this->institution) &&
                    !$USER->in_institution($this->institution) &&
                    !$USER->get('admin'))) {
                return null;
            }
        }

        $ownername = hsc($this->formatted_owner());
        $ownerlink = hsc($this->owner_link());
        return get_string('viewauthor', 'view', $ownerlink, $ownername);
    }

    public function display_title_editing() {
        if ($this->type == 'profile') {
            return get_string('profileviewtitle', 'view');
        }
        if ($this->type == 'dashboard') {
            return get_string('dashboardviewtitle', 'view');
        }
        if ($this->type == 'grouphomepage') {
            return get_string('grouphomepage', 'view');
        }
        return $this->title;
    }

    public function visit_message() {
        $visitcountstart = max(get_config('stats_installation_time'), $this->ctime);
        $visitcountend = get_config('viewloglatest');
        if ($visitcountstart && $visitcountend && $visitcountstart < $visitcountend) {
             return get_string(
                'viewvisitcount',
                'view',
                $this->visits,
                trim(format_date(strtotime($visitcountstart), 'strftimedate')),
                trim(format_date(strtotime($visitcountend), 'strftimedate'))
            );
        }
    }

    /**
     * after editing the view, redirect back to the appropriate place
     */
    public function post_edit_redirect($new=false) {
        if ($new) {
            $redirecturl = '/view/access.php?id=' . $this->get('id');
        }
        else {
            if ($this->get('group')) {
                if ($this->get('type') == 'grouphomepage') {
                    $redirecturl = group_homepage_url(get_record('group', 'id', $this->get('group')));
                }
                else {
                    $redirecturl = '/view/groupviews.php?group='.$this->get('group');
                }
            }
            else if ($this->get('institution')) {
                $redirecturl = '/view/institutionviews.php?institution=' . $this->get('institution');
            }
            else {
                $redirecturl = '/view/index.php';
            }
        }
        redirect($redirecturl);
    }


    /**
     * Makes a URL for a view page
     *
     * @param bool $full return a full url
     * @param bool $useid ignore clean url settings and always return a url with an id in it
     *
     * @return string
     */
    public function get_url($full=true, $useid=false) {
        if ($this->owner == "0") {
            return null;
        }
        else if ($this->type == 'profile') {
            if (!$useid) {
                return profile_url($this->get_owner_object(), $full);
            }
            $url = 'user/view.php?id=' . (int) $this->owner;
        }
        else if ($this->type == 'dashboard') {
            $url = '';
        }
        else if ($this->type == 'grouphomepage') {
            if (!$useid && $this->get('group')) {
                return group_homepage_url($this->get_group_object(), $full);
            }
            $url = 'group/view.php?id=' . $this->group;
        }
        else if (!$useid && !is_null($this->urlid) && get_config('cleanurls')) {
            if ($this->owner && !is_null($this->get_owner_object()->urlid)) {
                return profile_url($this->ownerobj, $full) . '/' . $this->urlid;
            }
            else if ($this->group && !is_null($this->get_group_object()->urlid)) {
                return group_homepage_url($this->groupobj, $full) . '/' . $this->urlid;
            }
        }

        if (!isset($url)) {
            $url = 'view/view.php?id=' . (int) $this->id;
        }

        return $full ? (get_config('wwwroot') . $url) : $url;
    }


    /**
     * Get all view access records relevant to a user
     */
    public static function user_access_records($viewid, $userid) {
        static $viewaccess = array();
        $userid = (int) $userid;

        if (!isset($viewaccess[$viewid][$userid])) {

            $viewaccess[$viewid][$userid] = get_records_sql_array("
                SELECT va.*
                FROM {view_access} va
                    LEFT OUTER JOIN {group_member} gm
                    ON (va.group = gm.group AND gm.member = ?
                        AND (va.role = gm.role OR va.role IS NULL))
                WHERE va.view = ?
                    AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                    AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                    AND (va.accesstype IN ('public', 'loggedin', 'friends')
                         OR va.usr = ? OR va.token IS NOT NULL OR gm.member IS NOT NULL OR va.institution IS NOT NULL)
                ORDER BY va.token IS NULL DESC, va.accesstype != 'friends' DESC",
                array($userid, $viewid, $userid)
            );
        }

        return $viewaccess[$viewid][$userid];
    }


    /**
     * Determine whether a user can write comments on this view
     *
     * If the view doesn't have the allowcomments property set,
     * then we must look at the view_access records to determine
     * whether the user can leave comments.
     *
     * In view_access, allowcomments indicates that the user can
     * comment, however if approvecomments is also set on a particular
     * access record, then all comments can only be private until the
     * view owner decides to make them public.
     *
     * Returns false, 'private', or true
     */
    public function user_comments_allowed(User $user) {
        global $SESSION;

        if (!$user->is_logged_in() && !get_config('anonymouscomments')) {
            return false;
        }

        if ($this->get('allowcomments')) {
            return $this->get('approvecomments') ? 'private' : true;
        }

        $userid = $user->get('id');
        $access = self::user_access_records($this->id, $userid);

        $publicviews = get_config('allowpublicviews');
        $publicprofiles = get_config('allowpublicprofiles');

        // a group view won't have an 'owner'
        if ($publicviews && $ownerobj = $this->get_owner_object()) {
            $owner = new User();
            $owner->find_by_id($ownerobj->id);

            $publicviews = $owner->institution_allows_public_views();
        }

        $allowcomments = false;
        $approvecomments = true;

        $mnettoken = get_cookie('mviewaccess:'.$this->id);
        $usertoken = get_cookie('viewaccess:'.$this->id);
        $cid = $this->collection_id();
        $ctoken = $cid ? get_cookie('caccess:'.$cid) : null;

        foreach ($access as $a) {
            if ($a->accesstype == 'public') {
                if (!$publicviews && (!$publicprofiles || $this->type != 'profile')) {
                    continue;
                }
            }
            else if ($a->token && $a->token != $mnettoken
                     && (!$publicviews || ($a->token != $usertoken && $a->token != $ctoken))) {
                continue;
            }
            else if (!$user->is_logged_in()) {
                continue;
            }
            else if ($a->accesstype == 'friends') {
                $owner = $this->get('owner');
                if (!get_field_sql('
                    SELECT COUNT(*) FROM {usr_friend} f WHERE (usr1=? AND usr2=?) OR (usr1=? AND usr2=?)',
                    array($owner, $userid, $userid, $owner)
                )) {
                    continue;
                }
            }

            $objectionable = $this->is_objectionable();
            if ($a->allowcomments && (($objectionable && ($user->get('admin')
                || $user->is_institutional_admin()) || !$objectionable))) {
                $allowcomments = $allowcomments || $a->allowcomments;
                $approvecomments = $approvecomments && $a->approvecomments;
            }
            if (!$approvecomments) {
                return true;
            }
        }

        if ($allowcomments) {
            return $approvecomments ? 'private' : true;
        }

        return false;
    }

    /**
     * Determine whether the current view is of a type which can be themed.
     * Certain view types do not respect themes when displayed.
     *
     * @return boolean whether the view type may be themed
     */
    function is_themeable() {
        $unthemable_types = array('grouphomepage', 'dashboard');
        return !in_array($this->type, $unthemable_types);
    }

    /**
     * Get all views for a (user,group,institution), grouping views
     * into their collections.  Empty collections not returned.
     *
     * @param mixed   $owner integer userid or array of userids
     * @param mixed   $group integer groupid or array of groupids
     * @param mixed   $institution string institution name or array of institution names
     * @param string  $matchconfig record all matches with given config hash (see set_access)
     * @param boolean $includeprofile include profile view
     * @param integer $submittedgroup return only views & collections submitted to this group
     *
     * @return array, array
     */
    function get_views_and_collections($owner=null, $group=null, $institution=null, $matchconfig=null, $includeprofile=true, $submittedgroup=null) {

        $excludelocked = $group && group_user_access($group) != 'admin';
        // Anonymous public viewing of a group with 'Allow submissions' checked needs to avoid including the dummy root profile page.
        if ($owner == '0') {
            $includeprofile = false;
        }
        $sql = "
            SELECT v.id, v.type, v.title, v.accessconf, v.ownerformat, v.startdate, v.stopdate, v.template,
                v.owner, v.group, v.institution, v.urlid, v.submittedgroup, v.submittedhost, " .
                db_format_tsfield('v.submittedtime', 'submittedtime') . ", v.submittedstatus,
                c.id AS cid, c.name AS cname,
                c.submittedgroup AS csubmitgroup, c.submittedhost AS csubmithost, " .
                db_format_tsfield('c.submittedtime', 'csubmittime') . ", c.submittedstatus AS csubmitstatus
            FROM {view} v
                LEFT JOIN {collection_view} cv ON v.id = cv.view
                LEFT JOIN {collection} c ON cv.collection = c.id
            WHERE  v.type IN ('portfolio'";
        $sql .= $includeprofile ? ", 'profile') " : ') ';
        $sql .= $excludelocked ? 'AND v.locked != 1 ' : '';

        if (is_null($owner) && is_null($group) && is_null($institution)) {
            $values = array();
        }
        else {
            list($ownersql, $values) = self::multiple_owner_sql(
                (object) array('owner' => $owner, 'group' => $group, 'institution' => $institution)
            );
            $sql .= "AND v.$ownersql ";
        }

        if ($submittedgroup) {
            $sql .= 'AND v.submittedgroup = ? ';
            $values[] = (int) $submittedgroup;
        }

        $sql .= 'ORDER BY c.name, v.title';
        $records = get_records_sql_assoc($sql, $values);

        $collections = array();
        $views = array();

        if (!$records) {
            return array($collections, $views);
        }

        self::get_extra_view_info($records, false, false);

        foreach ($records as &$r) {
            $vid = $r['id'];
            $cid = $r['cid'];
            $v = array(
                'id'             => $vid,
                'type'           => $r['type'],
                'name'           => $r['displaytitle'],
                'url'            => $r['fullurl'],
                'startdate'      => $r['startdate'],
                'stopdate'       => $r['stopdate'],
                'template'       => $r['template'],
                'owner'          => $r['owner'],
                'submittedgroup' => $r['submittedgroup'],
                'submittedhost'  => $r['submittedhost'],
                'submittedtime'  => $r['submittedtime'],
                'submittedstatus' => $r['submittedstatus'],
            );
            if (isset($r['user'])) {
                $v['ownername'] = display_name($r['user']);
                $v['ownerurl']  = profile_url($r['user']);
            }

            // If filtering by submitted views, and the view is submitted, but the collection isn't,
            // then ignore the collection and return the view by itself.
            if ($cid && (!$submittedgroup || ($r['csubmitgroup'] == $r['submittedgroup']))) {
                if (!isset($collections[$cid])) {
                    $collections[$cid] = array(
                        'id'             => $cid,
                        'name'           => $r['cname'],
                        'url'            => $r['fullurl'],
                        'owner'          => $r['owner'],
                        'group'          => $r['group'],
                        'institution'    => $r['institution'],
                        'submittedgroup' => $r['csubmitgroup'],
                        'submittedhost'  => $r['csubmithost'],
                        'submittedtime'  => $r['csubmittime'],
                        'submittedstatus' => $r['csubmitstatus'],
                        'template'       => $r['template'],
                        'views' => array(),
                    );
                    if (isset($r['user'])) {
                        $collections[$cid]['ownername'] = $v['ownername'];
                        $collections[$cid]['ownerurl'] = $v['ownerurl'];
                    }
                    if ($matchconfig && $matchconfig == $r['accessconf']) {
                        $collections[$cid]['match'] = true;
                    }
                }
                $collections[$cid]['views'][$vid] = $v;
            }
            else {
                $views[$vid] = $v;
                if ($matchconfig && $matchconfig == $r['accessconf']) {
                    $views[$vid]['match'] = true;
                }
            }
        }

        return array($collections, $views);
    }


    // Returns a string describing the override access for a view record
    public static function access_override_description($v) {
        if ($v['startdate'] && $v['stopdate']) {
            return get_string(
                'accessbetweendates2', 'view',
                format_date(strtotime($v['startdate']), 'strftimedate'),
                format_date(strtotime($v['stopdate']), 'strftimedate')
            );
        }
        if ($v['startdate']) {
            return get_string(
                'accessfromdate2', 'view',
                format_date(strtotime($v['startdate']), 'strftimedate')
            );
        }
        if ($v['stopdate']) {
            return get_string(
                'accessuntildate2', 'view',
                format_date(strtotime($v['stopdate']), 'strftimedate')
            );
        }
    }


    /**
     * Get all views & collections for a (user,group), grouped
     * by their accesslists as defined by the accessconf column
     *
     * @param integer $owner
     * @param integer $group
     *
     * @return array
     */
    public static function get_accesslists($owner=null, $group=null, $institution=null) {
        require_once('institution.php');

        if (!is_null($owner) && !is_array($owner) && $owner > 0) {
            $ownerobj = new User();
            $ownerobj->find_by_id($owner);
        }

        $data = array();
        list($data['collections'], $data['views']) = self::get_views_and_collections($owner, $group, $institution);

        // Remember one representative viewid in each collection
        $viewindex = array();

        // Add strings to describe startdate/stopdate access overrides
        foreach ($data['collections'] as &$c) {
            $view = current($c['views']);
            $viewindex[$view['id']] = array('type' => 'collections', 'id' => $c['id']);
            $c['access'] = self::access_override_description($view);
            $c['viewid'] = $view['id'];
        }
        foreach ($data['views'] as &$v) {
            $viewindex[$v['id']] = array('type' => 'views', 'id' => $v['id']);
            $v['access'] = self::access_override_description($v);
            $v['viewid'] = $v['id'];
        }

        if (empty($viewindex)) {
            return $data;
        }

        // Get view_access records, apart from those with visible = 0 (system access records)
        $accessgroups = get_records_sql_array('
            SELECT va.*, g.grouptype, g.name, g.urlid
            FROM {view_access} va LEFT OUTER JOIN {group} g ON (g.id = va.group AND g.deleted = 0)
            WHERE va.view IN (' . join(',', array_keys($viewindex)) . ') AND va.visible = 1
            ORDER BY va.view, va.accesstype, g.grouptype, va.role, g.name, va.group, va.usr',
            array()
        );

        if (!$accessgroups) {
            return $data;
        }

        if (!function_exists('is_probationary_user')) {
            require_once(get_config('libroot') . 'antispam.php');
        }
        foreach ($accessgroups as $access) {
            // remove 'Public' from the list if the owner isn't allowed to have them
            if ($access->accesstype == 'public'
                && (
                    get_config('allowpublicviews') != 1
                    || (isset($ownerobj) && !$ownerobj->institution_allows_public_views())
                    || (isset($ownerobj) && is_probationary_user($ownerobj->id))
                )
            ) {
                continue;
            }

            $vi = $viewindex[$access->view];

            // Just count secret urls.
            if ($access->token) {
                if (!isset($data[$vi['type']][$vi['id']]['secreturls'])) {
                    $data[$vi['type']][$vi['id']]['secreturls'] = 0;
                }
                $data[$vi['type']][$vi['id']]['secreturls']++;
                continue;
            }

            $key = null;
            if ($access->usr) {
                $access->accesstype = 'user';
                $access->id = $access->usr;
            }
            else if ($access->group) {
                $access->accesstype = 'group';
                $access->id = $access->group;
                if ($access->role) {
                    $access->roledisplay = get_string($access->role, 'grouptype.' . $access->grouptype);
                }
                $access->groupurl = group_homepage_url((object) array('id' => $access->group, 'urlid' => $access->urlid));
            }
            else if ($access->institution) {
                $access->accesstype = 'institution';
                $access->id = $access->institution;
                $access->name = institution_display_name($access->institution);
            }
            else {
                $key = $access->accesstype;
            }
            if ($key) {
                if (!isset($data[$vi['type']][$vi['id']]['accessgroups'][$key])) {
                    $data[$vi['type']][$vi['id']]['accessgroups'][$key] = (array) $access;
                }
            }
            else {
                $data[$vi['type']][$vi['id']]['accessgroups'][] = (array) $access;
            }
        }

        return $data;
    }

    public function submit($group) {
        global $USER;

        if ($this->is_submitted()) {
            throw new SystemException('Attempting to submit a submitted view');
        }

        $group->roles = get_column('grouptype_roles', 'role', 'grouptype', $group->grouptype, 'see_submitted_views', 1);

        self::_db_submit(array($this->id), $group);

        activity_occurred(
            'groupmessage',
            array(
                'group'         => $group->id,
                'roles'         => $group->roles,
                'url'           => $this->get_url(false),
                'strings'       => (object) array(
                    'urltext' => (object) array('key' => 'view'),
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
                            $this->title,
                            $group->name,
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Lower-level function to handle all the DB changes that should occur when you submit a view or views
     *
     * @param array $viewids The views to submit. (Normally one view by itself, or all the views in a Collection)
     * @param object $submittedgroupobj An object holding information about the group submitting to. Should contain id and roles array
     * @param string $submittedhost Alternately, the name of the remote host the group is being submitted to (for MNet submission)
     * @param int $owner The ID of the owner of the view. Used mostly for verification purposes.
     */
    public static function _db_submit($viewids, $submittedgroupobj = null, $submittedhost = null, $owner = null) {
        global $USER;
        require_once(get_config('docroot') . 'artefact/lib.php');

        $group = $submittedgroupobj;

        // Gotta provide some viewids and/or a remote username
        if (empty($viewids) || (empty($group->id) && empty($submittedhost))) {
            return;
        }

        $idstr = join(',', array_map('intval', $viewids));
        $userid = ($owner == null) ? $USER->get('id') : $owner;
        $sql = 'UPDATE {view} SET submittedtime = current_timestamp, submittedstatus = ' . self::SUBMITTED;
        $params = array();

        if ($group) {
            $groupid = (int) $group->id;
            $sql .= ', submittedgroup = ? ';
            $params[] = $groupid;
        }
        else {
            $sql .= ', submittedhost = ? ';
            $params[] = $submittedhost;
        }

        $sql .= " WHERE id IN ({$idstr}) AND owner = ?";
        $params[] = $userid;

        db_begin();
        execute_sql($sql, $params);

        if ($group) {
            foreach ($group->roles as $role) {
                foreach ($viewids as $viewid) {
                    $accessrecord = (object) array(
                        'view'            => $viewid,
                        'group'           => $groupid,
                        'role'            => $role,
                        'visible'         => 0,
                        'allowcomments'   => 1,
                        'approvecomments' => 0,
                        'ctime'           => db_format_timestamp(time()),
                    );
                    ensure_record_exists('view_access', $accessrecord, $accessrecord);
                }
            }
        }

        ArtefactType::update_locked($userid);
        db_commit();
    }
}

class ViewSubmissionException extends UserException {
    public function strings() {
        return array_merge(
            parent::strings(),
            array(
                'title' => get_string('viewsubmissionexceptiontitle', 'view'),
                'message' => get_string('viewsubmissionexceptionmessage', 'view'),
            )
        );
    }
}

function create_view_form($group=null, $institution=null, $template=null, $collection=null) {
    global $USER;
    $form = array(
        'name'            => 'createview',
        'method'          => 'post',
        'plugintype'      => 'core',
        'pluginname'      => 'view',
        'renderer'        => 'oneline',
        'successcallback' => 'createview_submit',
        'elements'   => array(
            'new' => array(
                'type' => 'hidden',
                'value' => true,
            ),
            'submitcollection' => array(
                'type'  => 'hidden',
                'value' => false,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('createview', 'view'),
            ),
        )
    );
    if ($group) {
        $form['elements']['group'] = array(
            'type'  => 'hidden',
            'value' => $group,
        );
    }
    else if ($institution) {
        $form['elements']['institution'] = array(
            'type'  => 'hidden',
            'value' => $institution,
        );
    }
    else {
        $form['elements']['owner'] = array(
            'type' => 'hidden',
            'value' => $USER->get('id'),
        );
    }
    if ($collection !== null) {
        $form['elements']['copycollection'] = array(
            'type'  => 'hidden',
            'value' => $collection,
        );
        $form['elements']['submitcollection'] = array(
            'type'  => 'submit',
            'value' => get_string('copycollection', 'collection'),
        );
    }
    if ($template !== null) {
        $form['elements']['usetemplate'] = array(
            'type'  => 'hidden',
            'value' => $template,
        );
        $form['elements']['submit']['value'] = get_string('copyview', 'view');
        $form['name'] .= $template;
    }
    return $form;
}

function createview_submit(Pieform $form, $values) {
    global $SESSION;

    $values['template'] = !empty($values['istemplate']) ? 1 : 0; // Named 'istemplate' in the form to prevent confusion with 'usetemplate'

    if (!empty($values['submitcollection'])) {
        require_once(get_config('libroot') . 'collection.php');
        $templateid = $values['copycollection'];
        unset($values['copycollection']);
        unset($values['usetemplate']);
        list($collection, $template, $copystatus) = Collection::create_from_template($values, $templateid);
        if (isset($copystatus['quotaexceeded'])) {
            $SESSION->add_error_msg(get_string('collectioncopywouldexceedquota', 'collection'));
            redirect(get_config('wwwroot') . 'view/choosetemplate.php');
        }
        $SESSION->add_ok_msg(get_string('copiedpagesblocksandartefactsfromtemplate', 'collection',
            $copystatus['pages'],
            $copystatus['blocks'],
            $copystatus['artefacts'],
            $template->get('name'))
        );

        redirect(get_config('wwwroot') . 'collection/edit.php?copy=1&id=' . $collection->get('id'));
    }
    else if (isset($values['usetemplate'])) {
        $templateid = $values['usetemplate'];
        unset($values['usetemplate']);
        list($view, $template, $copystatus) = View::create_from_template($values, $templateid);
        if (isset($copystatus['quotaexceeded'])) {
            $SESSION->add_error_msg(get_string('viewcopywouldexceedquota', 'view'));
            redirect(get_config('wwwroot') . 'view/choosetemplate.php');
        }
        $SESSION->add_ok_msg(get_string('copiedblocksandartefactsfromtemplate', 'view',
            $copystatus['blocks'],
            $copystatus['artefacts'],
            $template->get('title'))
        );
    }
    else {
        $view = View::create($values);
    }

    redirect(get_config('wwwroot') . 'view/edit.php?new=1&id=' . $view->get('id'));
}

function createview_cancel_submit(Pieform $form, $values) {
    if (isset($values['group'])) {
        redirect(get_config('wwwroot') . 'view/groupviews.php?group=' . $values['group']);
    }
    if (isset($values['institution'])) {
        redirect(get_config('wwwroot') . 'view/institutionviews.php?institution=' . $values['institution']);
    }
    redirect(get_config('wwwroot') . 'view/index.php');
}

function searchviews_submit(Pieform $form, $values) {
    $tag = $query = null;
    if ($values['query'] != '') {
        if ($values['type'] == 'tagsonly') {
            $tag = $values['query'];
        }
        else {
            $query = $values['query'];
        }
    }
    $orderby = isset($values['orderby']) ? $values['orderby'] : null;
    $group = isset($values['group']) ? $values['group'] : null;
    $institution = isset($values['institution']) ? $values['institution'] : null;
    redirect(View::get_myviews_url($group, $institution, $query, $tag, $orderby));
}

/**
 * Generates a form which will submit a view or collection to one of
 * the owner's groups.
 *
 * @param mixed $view The view to be submitted. Either a View object,
 *                    or (for compatibility with previous versions of
 *                    this function) an integer view id.
 * @param array $tutorgroupdata An array of StdClass objects with id
 *                    and name properties representing groups.
 * @param string $returnto A URL - where to go after leaving the
 *                    submit page.
 *
 * @return string
 */
function view_group_submission_form($view, $tutorgroupdata, $returnto=null) {
    if (is_numeric($view)) {
        $view = new View($view);
    }
    $viewid = $view->get('id');

    $options = array();
    foreach ($tutorgroupdata as $group) {
        $options[$group->id] = $group->name;
    }

    // This form sucks from a language string point of view. It should
    // use pieforms' form template feature
    $form = array(
        'name' => 'view_group_submission_form_' . $viewid,
        'method' => 'post',
        'renderer' => 'oneline',
        'autofocus' => false,
        'successcallback' => 'view_group_submission_form_submit',
        'elements' => array(
            'text1' => array(
                'type' => 'html',
                'value' => '',
            ),
            'options' => array(
                'type' => 'select',
                'collapseifoneoption' => false,
                'options' => $options,
            ),
            'text2' => array(
                'type' => 'html',
                'value' => get_string('forassessment', 'view'),
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('submit')
            ),
            'returnto' => array(
                'type' => 'hidden',
                'value' => $returnto,
            )
        ),
    );

    if ($view->get_collection()) {
        $form['elements']['collection'] = array(
            'type' => 'hidden',
            'value' => $view->get_collection()->get('id'),
        );
        $form['elements']['text1']['value'] = get_string('submitthiscollectionto', 'view') . ' ';
    }
    else {
        $form['elements']['view'] = array(
            'type' => 'hidden',
            'value' => $viewid
        );
        $form['elements']['text1']['value'] = get_string('submitthisviewto', 'view') . ' ';
    }

    return pieform($form);
}

function view_group_submission_form_submit(Pieform $form, $values) {
    $params = array(
        'group' => $values['options'],
        'returnto' => $values['returnto'],
    );
    if (isset($values['collection'])) {
        $params['collection'] = $values['collection'];
    }
    else {
        $params['id'] = $values['view'];
    }
    redirect('/view/submit.php?' . http_build_query($params));
}

/**
 * display format for author names in views - firstname
 */
define('FORMAT_NAME_FIRSTNAME', 1);

/**
 * display format for author names in views - lastname
 */
define('FORMAT_NAME_LASTNAME', 2);

/**
 * display format for author names in views - firstname lastname
 */
define('FORMAT_NAME_FIRSTNAMELASTNAME', 3);

/**
 * display format for author names in views - preferred name
 */
define('FORMAT_NAME_PREFERREDNAME', 4);

/**
 * display format for author names in views - student id
*/
define('FORMAT_NAME_STUDENTID', 5);

/**
 * display format for author names in views - obeys display_name
 */
define('FORMAT_NAME_DISPLAYNAME', 6);
