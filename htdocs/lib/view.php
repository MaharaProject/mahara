<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    private $title;
    private $description;
    private $loggedin;
    private $friendsonly;
    private $artefact_instances;
    private $artefact_metadata;
    private $ownerobj;
    private $groupobj;
    private $numcolumns;
    private $layout;
    private $theme;
    private $columns;
    private $dirtycolumns; // for when we change stuff
    private $tags;
    private $categorydata;
    private $editingroles;
    private $template;
    private $copynewuser = 0;
    private $copynewgroups;
    private $type;
    private $visits;
    private $allowcomments;
    private $approvecomments;
    private $collection;
    private $accessconf;

    /**
     * Valid view layouts. These are read at install time and inserted into
     * view_layout, but not updated afterwards, so if you're changing one
     * you'll need to do that manually. Actually, you'd better talk to the
     * Mahara dev team about what else needs changing if you do touch this.
     *
     * A hash of columns => list of view widths
     */
    public static $layouts = array(
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
     * Which view layout is considered the "default" for views with the given
     * number of columns. Must be present in $layouts of course.
     */
    public static $defaultlayouts = array(
        1 => '100',
        2 => '50,50',
        3 => '33,33,33',
        4 => '25,25,25,25',
        5 => '20,20,20,20,20',
    );

    public function __construct($id=0, $data=null) {
        if (!empty($id)) {
            $tempdata = get_record('view','id',$id);
            if (empty($tempdata)) {
                throw new ViewNotFoundException(get_string('viewnotfound', 'error', $id));
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
            $this->dirty = true;
        }

        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
        $this->atime = time();
        $this->columns = array();
        $this->dirtycolumns = array();
        if ($this->group) {
            $group = get_record('group', 'id', $this->group);
            if ($group->deleted) {
                throw new ViewNotFoundException(get_string('viewnotfound', 'error', $id));
            }
            safe_require('grouptype', $group->grouptype);
            $this->editingroles = call_static_method('GroupType' . ucfirst($group->grouptype), 'get_view_editing_roles');
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
     * specified in $viewdata.
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
    public static function create_from_template($viewdata, $templateid, $userid=null, $checkaccess=true) {
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

        if (!$template->get('template') && !$user->can_edit_view($template)) {
            throw new SystemException("View::create_from_template: Attempting to create a View from another View that is not marked as a template");
        }
        else if ($checkaccess && !can_view_view($templateid, $userid)) {
            throw new SystemException("View::create_from_template: User $userid is not permitted to copy View $templateid");
        }

        $view = self::_create($viewdata, $userid);

        // Set a default title if one wasn't set
        if (!isset($viewdata['title'])) {
            $view->set('title', self::new_title(get_string('Copyof', 'mahara', $template->get('title')), (object)$viewdata));
            $view->set('dirty', true);
        }

        try {
            $copystatus = $view->copy_contents($template);
        }
        catch (QuotaExceededException $e) {
            db_rollback();
            return array(null, $template, array('quotaexceeded' => true));
        }

        $view->commit();

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
                $ownertheme = $owner->get('theme');
                if ($ownertheme && $ownertheme != get_config('theme')) {
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
            'numcolumns'  => 3,
            'template'    => 0,
            'type'        => 'portfolio',
            'title'       => self::new_title(get_string('Untitled', 'view'), (object)$viewdata),
        );

        $data = (object)array_merge($defaultdata, $viewdata);

        $view = new View(0, $data);
        $view->commit();

        if (isset($viewdata['group'])) {
            // By default, group views should be visible to the group
            insert_record('view_access', (object) array(
                'view'  => $view->get('id'),
                'group' => $viewdata['group'],
            ));
        }

        return new View($view->get('id')); // Reread to ensure defaults are set
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
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($this->{$field} != $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
            }
            $this->{$field} = $value;
            $this->mtime = time();
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
            delete_records('view_tag', 'view', $this->get('id'));
            foreach ($this->get_tags() as $tag) {
                insert_record('view_tag', (object)array( 'view' => $this->get('id'), 'tag' => $tag));
            }
        }

        if (isset($this->copynewgroups)) {
            delete_records('view_autocreate_grouptype', 'view', $this->get('id'));
            foreach ($this->copynewgroups as $grouptype) {
                insert_record('view_autocreate_grouptype', (object)array( 'view' => $this->get('id'), 'grouptype' => $grouptype));
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
        if (!isset($this->ownerobj)) {
            $this->ownerobj = get_record('usr', 'id', $this->get('owner'));
        }
        return $this->ownerobj;
    }

    public function get_group_object() {
        if (!isset($this->groupobj)) {
            $this->groupobj = get_record('group', 'id', $this->get('group'));
        }
        return $this->groupobj;
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
            return self::process_access_records($data, $timeformat);
        }
        return array();
    }

    public function get_access_records() {
        $data = get_records_sql_array("
            SELECT accesstype, va.group, role, usr, startdate, stopdate, allowcomments, approvecomments
            FROM {view_access} va
            WHERE view = ? AND visible = 1 AND token IS NULL
            ORDER BY
                accesstype IS NULL, accesstype DESC,
                va.group, role IS NOT NULL, role,
                usr,
                startdate IS NOT NULL, startdate, stopdate IS NOT NULL, stopdate,
                allowcomments, approvecomments",
            array($this->id)
        );
        return $data ? $data : array();
    }

    public static function process_access_records($data=array(), $timeformat=null) {
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
            if ($item['usr']) {
                $item['type'] = 'user';
                $item['id'] = $item['usr'];
            }
            else if ($item['group']) {
                $item['type'] = 'group';
                $item['id'] = $item['group'];
            }
            else {
                $item['type'] = $item['accesstype'];
                $item['id'] = null;
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

        $beforeusers = activity_get_viewaccess_users($this->get('id'), $USER->get('id'), 'viewaccess');

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
                    'accesstype' => null,
                    'group' => null,
                    'role' => null,
                    'usr' => null,
                    'token' => null,
                    'startdate' => null,
                    'stopdate' => null,
                    'allowcomments' => 0,
                    'approvecomments' => 1,
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
        $data->owner = $USER->get('id');
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
                    insert_record('view_access', $a);
                }
            }
        }
        db_commit();
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

    public function release($releaseuser=null) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $submitinfo = $this->submitted_to();
        if (is_null($submitinfo)) {
            throw new ParameterException("View with id " . $this->get('id') . " has not been submitted");
        }
        $releaseuser = optional_userobj($releaseuser);
        db_begin();
        if ($submitinfo['type'] == 'group') {
            $group = $this->get('submittedgroup');
            $this->set('submittedgroup', null);
            if ($group) {
                // Remove hidden tutor view access records
                delete_records('view_access', 'view', $this->id, 'group', $group, 'visible', 0);
            }
        }
        else if ($submitinfo['type'] == 'host') {
            $this->set('submittedhost', null);
        }
        $this->set('submittedtime', null);
        $this->commit();
        ArtefactType::update_locked($this->owner);
        db_commit();
        $ownerlang = get_user_language($this->get('owner'));
        $url = get_config('wwwroot') . 'view/view.php?id=' . $this->get('id');
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

        $smarty = smarty_core();
        $smarty->assign('categories', $categories);
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
        return $data[0]['name'];
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
        $sql = 'SELECT bic.* FROM {blocktype_installed_category} bic
            JOIN {blocktype_installed} bi ON (bic.blocktype = bi.name AND bi.active = 1)
            JOIN {blocktype_installed_viewtype} biv ON (bi.name = biv.blocktype AND biv.viewtype = ?)';
        if (function_exists('local_get_allowed_blocktype_categories')) {
            $localallowed = local_get_allowed_blocktype_categories($this);
        }
        foreach (get_records_sql_array($sql, array($this->get('type'))) as $blocktypecategory) {
            if (isset($localallowed) && is_array($localallowed) && !in_array($blocktypecategory->category, $localallowed)) {
                continue;
            }
            safe_require('blocktype', $blocktypecategory->blocktype);
            if (call_static_method(generate_class_name("blocktype", $blocktypecategory->blocktype), "allowed_in_view", $this)) {
                if (!isset($categories[$blocktypecategory->category])) {
                    $categories[$blocktypecategory->category] = array(
                        'name'  => $blocktypecategory->category,
                        'title' => call_static_method("PluginBlocktype", "category_title_from_name", $blocktypecategory->category),
                    );
                }
            }
        }

        // The 'internal' plugin is known to the outside world as 'profile', so 
        // we need to sort on the actual name
        usort($categories, create_function('$a, $b', 'return strnatcasecmp($a[\'title\'], $b[\'title\']);'));

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
            case 'moveblockinstance': // requires action_moveblockinstance_id_\d_column_\d_order_\d
            case 'addcolumn': // requires action_addcolumn_before_\d
            case 'removecolumn': // requires action_removecolumn_column_\d
            case 'changetheme':
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
            require_once('activity.php');
            activity_occurred('watchlist', $data);

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
    private function build_column_datastructure($force=false) {
        if (!empty($this->columns) && empty($force)) { // we've already built it up
            return;
        }

        $sql = 'SELECT bi.*
            FROM {block_instance} bi
            WHERE bi.view = ?
            ORDER BY bi.column, bi.order';
        if (!$data = get_records_sql_array($sql, array($this->get('id')))) {
            $data = array();
        }

        // fill up empty columns array keys
        for ($i = 1; $i <= $this->get('numcolumns'); $i++) {
            $this->columns[$i] = array('blockinstances' => array());
        }

        // Set column widths
        $layout = $this->get_layout();
        $i = 0;
        $is_ie6 = (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0'));
        foreach (explode(',', $layout->widths) as $width) {
            // IE6 has interesting padding issues that mean we have to tell
            // porkies so all the columns stay beside each other
            if ($is_ie6) {
                $width -= 2;
            }
            $this->columns[++$i]['width'] = $width;
        }

        foreach ($data as $block) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            $b = new BlockInstance($block->id, (array)$block);
            $this->columns[$block->column]['blockinstances'][] = $b;
        }

    }

    /*
    * returns the datastructure for the view's column(s)
    *
    * @param int $column optional, defaults to returning all columns
    * @return mixed array
    */
    public function get_column_datastructure($column=0) {
        // make sure we've already built up the structure
        $force = false;
        if (array_key_exists($column, $this->dirtycolumns)) {
            $force = true;
        }
        $this->build_column_datastructure($force);

        if (empty($column)) {
            return $this->columns;
        }

        if (!array_key_exists($column, $this->columns)) {
            throw new ParamOutOfRangeException(get_string('invalidcolumn', 'view', $column));
        }


        return $this->columns[$column];
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
     * Returns the HTML for the columns of this view
     */
    public function build_columns($editing=false) {
        $numcols = $this->get('numcolumns');

        $result = '';
        for ($i = 1; $i <= $numcols; $i++) {
            $result .= $this->build_column($i, $editing);
        }

        return $result;
    }

    /**
     * Returns the HTML for a particular column
     *
     * @param int $column   The column to build
     * @param int $editing  Whether the view is being built in edit mode
     */
    public function build_column($column, $editing=false) {
        global $USER;
        $data = $this->get_column_datastructure($column);
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

        // Widths don't appear to apply to divs unless they have at least
        // _some_ content - at least in gecko (make a view with a particular
        // layout like 25/50/25 and make the middle column empty and you'll see
        // what I mean)
        if ($blockcontent == '') {
            $blockcontent = '&nbsp;';
        }

        $smarty = smarty_core();
        $smarty->assign('javascript',  defined('JSON'));
        $smarty->assign('column',      $column);
        $smarty->assign('numcolumns',  $this->get('numcolumns'));
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
        $requires = array('blocktype', 'column', 'order');
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
                'column'     => $values['column'],
                'order'      => $values['order'],
            )
        );
        $this->shuffle_column($values['column'], $values['order']);
        $bi->commit();
        $this->dirtycolumns[$values['column']] = 1;

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
        if (!$bi->get('column')) {
            $bi->set('column', 1);
        }
        if (!$bi->get('order')) {
            $bi->set('order', 1);
        }
        if (!$bi->get('view')) {
            $bi->set('view', $this->get('id'));
        }
        $this->shuffle_column($bi->get('column'), $bi->get('order'));
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
        db_begin();
        $bi->delete();
        $this->shuffle_column($bi->get('column'), null, $bi->get('order'));
        db_commit();
        $this->dirtycolumns[$bi->get('column')] = 1;
    }

    /**
    * moves a block instance to a specified location
    *
    * @param array $values parameters for this function
    *                      id     => int of block instance to move
    *                      column => int column to move to
    *                      order  => position in new column to insert at
    */
    public function moveblockinstance($values) {
        $require = array('id', 'column', 'order');
        foreach ($require as $require) {
            if (!array_key_exists($require, $values) || empty($values[$require])) {
                throw new ParamOutOfRangeException(get_string('missingparam' . $require, 'error'));
            }
        }
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($values['id']);
        db_begin();
        // moving within the same column
        if ($bi->get('column') == $values['column']) {
            if ($values['order'] == $bi->get('order') + 1
                || $values['order'] == $bi->get('order') -1) {
                // we're switching two, it's a bit different
                // set the one we're moving to out of range (to 0)
                set_field('block_instance', 'order', 0,                 'view', $this->get('id'), 'column', $values['column'], 'order', $values['order']);
                // set the new order
                set_field('block_instance', 'order', $values['order'],  'view', $this->get('id'), 'column', $values['column'], 'order', $bi->get('order'));
                // move the old one back to where the moving one was.
                set_field('block_instance', 'order', $bi->get('order'), 'view', $this->get('id'), 'column', $values['column'], 'order', 0);
                // and set it in the object for good measure.
                $bi->set('order', $values['order']);
            }
            else if ($values['order'] == $this->get_current_max_order($values['column'])) {
                // moving to the very bottom
                set_field('block_instance', 'order', 0, 'view', $this->get('id'), 'column', $values['column'], 'order', $bi->get('order'));
                $this->shuffle_helper('order', 'down', '>=', $bi->get('order'), '"column" = ?', array($bi->get('column')));
                set_field('block_instance', 'order', $values['order'], 'view', $this->get('id'), 'column', $values['column'], 'order', 0);
                $bi->set('order', $values['order']);
            }
            else {
                $this->shuffle_column($bi->get('column'), $values['order'], $bi->get('order'));
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
            $newmax = $this->get_current_max_order($values['column']);
            if ($values['order'] > $newmax+1) {
                $values['order'] = $newmax+1;
            }
            // remove it from the old column
            $this->shuffle_column($bi->get('column'), null, $bi->get('order'));
            // and make a hole in the new column
            $this->shuffle_column($values['column'], $values['order']);
        }
        $bi->set('column', $values['column']);
        $bi->set('order', $values['order']);
        $bi->commit();
        $this->dirtycolumns[$bi->get('column')] = 1;
        $this->dirtycolumns[$values['column']] = 1;
        db_commit();
    }

    /**
     * Returns a list of required javascript files, based on
     * the blockinstances present in the view.
     */
    public function get_blocktype_javascript() {
        $view_data = $this->get_column_datastructure();
        $javascript = array();
        foreach($view_data as $column) {
            foreach($column['blockinstances'] as $blockinstance) {
                $pluginname = $blockinstance->get('blocktype');
                safe_require('blocktype', $pluginname);
                $instancejs = call_static_method(
                    generate_class_name('blocktype', $pluginname),
                    'get_instance_javascript',
                    $blockinstance
                );
                foreach($instancejs as &$jsfile) {
                    if(strpos($jsfile, 'http://') === false) {
                        if($artefactplugin = get_field('blocktype_installed', 'artefactplugin', 'name', $pluginname)) {
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
        }
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
        if (!array_key_exists('before', $values) || empty($values['before'])) {
            throw new ParamOutOfRangeException(get_string('missingparamcolumn', 'error'));
        }
        if (!array_key_exists($this->get('numcolumns') + 1, self::$layouts)) {
            throw new ParamOutOfRangeException(get_string('cantaddcolumn', 'view'));
        }
        db_begin();
        $this->set('numcolumns', $this->get('numcolumns') + 1);
        if ($values['before'] != ($this->get('numcolumns') + 1)) {
            $this->shuffle_helper('column', 'up', '>=', $values['before']);
        }
        $this->set('layout', null);
        $this->commit();
        // @TODO this could be optimised by actually moving the keys around,
        // but I don't think there's much point as the objects aren't persistent
        // unless we're in ajax land, in which case it would be an optimisation
        for ($i = $values['before']; $i <= $this->get('numcolumns'); $i++) {
            $this->dirtycolumns[$i] = 1;
        }
        $this->columns[$this->get('numcolumns')] = null; // set the key 
        db_commit();
        if ($values['returndata']) {
            return $this->build_column($values['before'], true);
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
        if (!array_key_exists('column', $values) || empty($values['column'])) {
            throw new ParamOutOfRangeException(get_string('missingparamcolumn', 'error'));
        }
        if (!array_key_exists($this->get('numcolumns') - 1, self::$layouts)) {
            throw new ParamOutOfRangeException(get_string('cantremovecolumn', 'view'));
        }
        db_begin();
        $numcolumns = $this->get('numcolumns') - 1;
        $columnmax = array(); // keep track of where we're at in each column
        $currentcol = 1;
        if ($blocks = $this->get_column_datastructure($values['column'])) {
            // we have to rearrange them first
            foreach ($blocks['blockinstances'] as $block) {
                if ($currentcol > $numcolumns) {
                    $currentcol = 1;
                }
                if ($currentcol == $values['column']) {
                    $currentcol++; // don't redistrubute blocks here!
                }
                if (!array_key_exists($currentcol, $columnmax)) {
                    $columnmax[$currentcol] = $this->get_current_max_order($currentcol);
                }
                $this->shuffle_column($currentcol, $columnmax[$currentcol]+1);
                $block->set('column', $currentcol);
                $block->set('order', $columnmax[$currentcol]+1);
                $block->commit();
                $columnmax[$currentcol]++;
                $currentcol++;
            }
        }

        $this->set('layout', null);
        $this->set('numcolumns', $this->get('numcolumns') - 1);
        // now shift all blocks one left and we're done
        $this->shuffle_helper('column', 'down', '>', $values['column']);

        $this->commit();
        db_commit();
        unset($this->columns); // everything has changed
    }

    /** 
     * helper function for re-ordering block instances within a column
     * @param int $column the column to re-order
     * @param int $insert the order we need to insert
     * @param int $remove the order we need to move out of the way
     */
    private function shuffle_column($column, $insert=0, $remove=0) {
        /*
        inserting something in the middle from somewhere else (insert and remove)
        we're either reshuffling after a delete, (no insert),
        inserting something in the middle out of nowhere (no remove)
        */
        // inserting and removing
        if (!empty($remove)) {
            // move it out of range (set to 0)
            set_field('block_instance', 'order', 0, 'order', $remove, 'column', $column, 'view', $this->get('id'));

            if (!empty($insert)) {
                // shuffle everything up
                $this->shuffle_helper('order', 'up', '>=', $insert, '"column" = ?', array($column)); 
                // now move it back
                set_field('block_instance', 'order', $insert, 'view', $this->get('id'), 'column', $column, 'order', 0);
            }

            // shuffle everything down
            $this->shuffle_helper('order', 'down', '>', $remove, '"column" = ?', array($column));
        }
        else if (!empty($insert)) {
            // shuffle everything up
            $this->shuffle_helper('order', 'up', '>=', $insert, '"column" = ?', array($column));
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
    private function get_current_max_order($column) {
        return get_field('block_instance', 'max("order")', 'column', $column, 'view', $this->get('id')); 
    }

    private function changetheme($values) {
        if ($theme = $values['theme']) {
            $themes = get_user_accessible_themes();
            if (isset($themes[$theme])) {
                if($theme == 'sitedefault') {
                    $theme = null;
                }
                $this->set('theme', $theme);
                $this->commit();
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
     * This function formats a user's name
     * according to their view preference
     *
     * @return string formatted name
     */
    public function formatted_owner() {

        if ($this->get('owner')) {
            $user = $this->get_owner_object();

            switch ($this->ownerformat) {
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
        } else if ($this->get('group')) {
            $group = $this->get_group_object();
            return $group->name;
        }
        return null;
    }

    /**
     * Returns a record from the view_layout table matching the layout for this
     * View.
     *
     * If the layout for the view is null, then this method returns the record
     * for the default layout for the number of columns the View has.
     *
     * Check the view_layout table for what fields you'll get back, but the
     * most interesting one is 'widths', which is a comma-separated list of %
     * widths for the columns in the View.
     *
     * @return array A record from the view_layout table.
     */
    public function get_layout() {
        static $viewlayouts = null;
        if ($viewlayouts === null) {
            $viewlayouts = get_records_assoc('view_layout');
        }

        $layout     = $this->get('layout');
        $numcolumns = $this->get('numcolumns');

        if (!$layout) {
            foreach ($viewlayouts as $layout) {
                if ($layout->widths == self::$defaultlayouts[$numcolumns]) {
                    return $layout;
                }
            }
        }

        if (isset($viewlayouts[$layout])) {
            return $viewlayouts[$layout];
        }

        throw new SystemException("Unknown view layout (id=$layout)");
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
        $data = $this->get_column_datastructure();
        $config = array(
            'title'       => $this->get('title'),
            'description' => $this->get('description'),
            'type'        => $this->get('type'),
            'layout'      => $this->get('layout'),
            'tags'        => $this->get('tags'),
            'numcolumns'  => $this->get('numcolumns'),
            'ownerformat' => $this->get('ownerformat'),
        );

        foreach ($data as $key => $column) {
            $config['columns'][$key] = array();
            foreach ($column['blockinstances'] as $bi) {
                safe_require('blocktype', $bi->get('blocktype'));
                $classname = generate_class_name('blocktype', $bi->get('blocktype'));
                $method = 'export_blockinstance_config';
                if (method_exists($classname, $method . "_$format")) {
                    $method .= "_$format";
                }
                $config['columns'][$key][] = array(
                    'blocktype' => $bi->get('blocktype'),
                    'title'     => $bi->get('title'),
                    'config'    => call_static_method($classname, $method, $bi),
                );
            }
        }

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
            'numcolumns'  => $config['numcolumns'],
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

        $col = 1;
        foreach ($config['columns'] as $column) {
            $row = 1;
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
                    $bi->set('column', $col);
                    $bi->set('order',  $row);
                    $view->addblockinstance($bi);

                    $row++;
                }
                else {
                    log_debug("Blocktype {$blockinstance['type']}'s import_create_blockinstance did not give us a blockinstance, so not importing this block");
                }
            }
            $col++;
        }

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
                $baseurl .= hsc($key) . '=' . hsc($value) . '&amp;';
            }
        }
        $baseurl = substr($baseurl, 0, -5);
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

        $result = '';
        if ($artefacts) {
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

        return array($result, $pagination, $totalartefacts, $data['offset']);
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
        else if (intval($owner) != 0) {
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
                    r.artefact, r.can_view, m.group
                FROM
                    {artefact_access_role} r
                    INNER JOIN {group_member} m ON r.role = m.role
                WHERE
                    m."group" = ' . (int)$group . '
                    AND m.member = ' . $user->get('id') . '
                    AND r.can_view = 1
            ) ga ON (ga.group = a.group AND a.id = ga.artefact)';
            $select = "(a.institution = 'mahara' OR ga.can_view = 1";
            if (!empty($data['userartefactsallowed'])) {
                $select .= ' OR "owner" = ' . $user->get('id');
            }
            $select .= ')';
        }
        else if ($institution) {
            // Site artefacts & artefacts owned by this institution
            $select = "(a.institution = 'mahara' OR a.institution = '$institution')";
        }
        else { // The view is owned by a normal user
            // Get artefacts owned by the user, group-owned artefacts
            // the user has republish permission on, artefacts owned
            // by the user's institutions.
            $from .= '
            LEFT OUTER JOIN {artefact_access_usr} aau ON (a.id = aau.artefact AND aau.usr = ' . $user->get('id') . ')
            LEFT OUTER JOIN {artefact_parent_cache} apc ON (a.id = apc.artefact)
            LEFT OUTER JOIN (
                SELECT
                    aar.artefact, aar.can_republish, m.group
                FROM
                    {artefact_access_role} aar
                    INNER JOIN {group_member} m ON aar.role = m.role
                WHERE
                    m.member = ' . $user->get('id') . '
                    AND aar.can_republish = 1
            ) ra ON (a.id = ra.artefact AND a.group = ra.group)';
            $institutions = array_keys($user->get('institutions'));
            $select = '(
                "owner" = ' . $user->get('id') . '
                OR ra.can_republish = 1
                OR aau.can_republish = 1';
            if ($user->get('admin')) {
                $institutions[] = 'mahara';
            }
            else {
                safe_require('artefact', 'file');
                $select .= "
                OR ( a.institution = 'mahara' AND apc.parent = " . (int)ArtefactTypeFolder::admin_public_folder_id() . ')';
            }
            if ($institutions) {
              $select .= '
                OR a.institution IN (' . join(',', array_map('db_quote', $institutions)) . ')';
            }
            $select .= "
            )";
        }
        if (!empty($data['artefacttypes']) && is_array($data['artefacttypes'])) {
            $select .= ' AND artefacttype IN(' . implode(',', array_map('db_quote', $data['artefacttypes'])) . ')';
        }

        if (!empty($data['search'])) {
            $search = db_quote('%' . str_replace('%', '%%', $data['search']) . '%');
            $select .= 'AND (title ' . db_ilike() . '(' . $search . ') OR description ' . db_ilike() . '(' . $search . ') )';
        }

        $select .= $extraselect;

        $cols = $short ? 'a.id, a.id AS b' : 'a.*'; // get_records_sql_assoc wants > 1 column

        $artefacts = get_records_sql_assoc(
            'SELECT ' . $cols . $from . ' WHERE ' . $select . $sortorder,
            null, $offset, $limit
        );
        $totalartefacts = count_records_sql('SELECT COUNT(*) ' . $from . ' WHERE ' . $select);

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

    public static function get_myviews_data($limit=5, $offset=0, $query=null, $tag=null, $groupid=null, $institution=null) {
        global $USER;
        $userid = (!$groupid && !$institution) ? $USER->get('id') : null;

        $select = '
            SELECT v.id,v.title,v.description,v.type,v.mtime';
        $from = '
            FROM {view} v';
        $where = '
            WHERE ' . self::owner_sql((object) array('owner' => $userid, 'group' => $groupid, 'institution' => $institution));
        $sort = '
            ORDER BY v.title, v.id';
        $values = array();

        if ($tag) { // Filter by the tag
            $from .= "
                INNER JOIN {view_tag} vt ON (vt.view = v.id AND vt.tag = ?)";
            $values[] = $tag;
        }
        elseif ($query) { // Include matches on the title, description or tag
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
        if ($userid) {
            $select .= ',v.submittedtime,
                g.id AS submitgroupid, g.name AS submitgroupname, h.wwwroot AS submithostwwwroot, h.name AS submithostname';
            $from .= '
                LEFT OUTER JOIN {group} g ON (v.submittedgroup = g.id AND g.deleted = 0)
                LEFT OUTER JOIN {host} h ON (v.submittedhost = h.wwwroot)';
            $sort = '
                ORDER BY v.type = \'portfolio\', v.type, v.title, v.id';
        }

        $count = count_records_sql('SELECT COUNT(v.id) ' . $from . $where, $values);
        $viewdata = get_records_sql_array($select . $from . $where . $sort, $values, $offset, $limit);

        $data = array();
        if ($viewdata) {
            for ($i = 0; $i < count($viewdata); $i++) {
                $index[$viewdata[$i]->id] = $i;
                $data[$i]['id'] = $viewdata[$i]->id;
                $data[$i]['type'] = $viewdata[$i]->type;
                $data[$i]['title'] = $viewdata[$i]->title;
                $data[$i]['mtime'] = $viewdata[$i]->mtime;
                $data[$i]['removable'] = self::can_remove_viewtype($viewdata[$i]->type);
                $data[$i]['description'] = $viewdata[$i]->description;
                if (!empty($viewdata[$i]->submitgroupid)) {
                    if (!empty($viewdata[$i]->submittedtime)) {
                        $data[$i]['submittedto'] = get_string(
                            'viewsubmittedtogroupon', 'view',
                            get_config('wwwroot') . 'group/view.php?id=' . $viewdata[$i]->submitgroupid,
                            hsc($viewdata[$i]->submitgroupname), format_date(strtotime($viewdata[$i]->submittedtime))
                        );
                    }
                    else {
                        $data[$i]['submittedto'] = get_string(
                            'viewsubmittedtogroup', 'view',
                            get_config('wwwroot') . 'group/view.php?id=' . $viewdata[$i]->submitgroupid,
                            hsc($viewdata[$i]->submitgroupname)
                        );
                    }
                }
                else if (!empty($viewdata[$i]->submithostwwwroot)) {
                    if (!empty($viewdata[$i]->submittedtime)) {
                        $data[$i]['submittedto'] = get_string(
                            'viewsubmittedtogroupon', 'view', $viewdata[$i]->submithostwwwroot,
                            hsc($viewdata[$i]->submithostname), format_date(strtotime($viewdata[$i]->submittedtime))
                        );
                    }
                    else {
                        $data[$i]['submittedto'] = get_string(
                            'viewsubmittedtogroup', 'view', $viewdata[$i]->submithostwwwroot,
                            hsc($viewdata[$i]->submithostname)
                        );
                    }
                }
            }
        }

        return (object) array(
            'data'  => $data,
            'count' => $count,
        );
    }

    public static function get_myviews_url($group=null, $institution=null, $query=null, $tag=null) {
        $queryparams = array();

        if (!empty($tag)) {
            $queryparams[] = 'tag=' . urlencode($tag);
        }
        else if (!empty($query)) {
            $queryparams[] =  'query=' . urlencode($query);
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
            $url = get_config('wwwroot') . 'view/';
        }

        if (!empty($queryparams)) {
            $url .= '?' . join('&', $queryparams);
        }

        return $url;
    }

    public static function views_by_owner($group=null, $institution=null) {

        $limit  = param_integer('limit', 25);
        $offset = param_integer('offset', 0);
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
            'renderer' => 'oneline',
            'elements' => array(
                'query' => array(
                    'type' => 'text',
                    'title' => get_string('search') . ': ',
                    'defaultvalue' => $searchdefault,
                ),
                'type' => array(
                    'type'         => 'select',
                    'options'      => $searchoptions,
                    'defaultvalue' => $searchtype,
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

        $data = self::get_myviews_data($limit, $offset, $query, $tag, $group, $institution);

        $url = self::get_myviews_url($group, $institution, $query, $tag);

        $pagination = build_pagination(array(
            'url'    => $url,
            'count'  => $data->count,
            'limit'  => $limit,
            'offset' => $offset,
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
     *
     */
    public static function view_search($query=null, $ownerquery=null, $ownedby=null, $copyableby=null, $limit=null, $offset=0, $extra=true, $sort=null, $types=null) {
        global $USER;
        $admin = $USER->get('admin');
        $loggedin = $USER->is_logged_in();
        $viewerid = $USER->get('id');

        $where = 'WHERE (v.owner IS NULL OR v.owner > 0)';

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
        if ($query) {
            $where .= "
                AND (v.title $like '%' || ? || '%' OR v.description $like '%' || ? || '%' )";
            $ph = array($query, $query);
        }
        else {
            $ph = array();
        }

        if (!$loggedin) {
            // Unreachable and not tested yet:
            $from = '
            FROM {view} v
                INNER JOIN {view_access} va ON (va.view = v.id)
            ';
            $where .= "
                AND (v.startdate IS NULL OR v.startdate < current_timestamp)
                AND (v.stopdate IS NULL OR v.stopdate > current_timestamp)
                AND va.accesstype = 'public'
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)";
        }
        else {
            $from = '
            FROM {view} v
            LEFT OUTER JOIN {group} gd ON v.group = gd.id
            LEFT OUTER JOIN (
                SELECT
                    gtr.edit_views, gm.group AS groupid
                FROM {group} g
                INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ?)
                INNER JOIN {grouptype_roles} gtr ON (g.grouptype = gtr.grouptype AND gtr.role = gm.role)
            ) AS vg ON (vg.groupid = v.group)
            LEFT OUTER JOIN {view_access} va ON (
                va.view = v.id
                AND va.accesstype IS NOT NULL
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
            )
            LEFT OUTER JOIN {usr_friend} f ON (usr1 = v.owner AND usr2 = ?)
            LEFT OUTER JOIN {view_access} vau ON (
                vau.view = v.id
                AND (vau.startdate IS NULL OR vau.startdate < current_timestamp)
                AND (vau.stopdate IS NULL OR vau.stopdate > current_timestamp)
                AND vau.usr = ?
            )
            LEFT OUTER JOIN (
                SELECT
                    vag.view, vagm.member
                FROM {view_access} vag
                INNER JOIN {group_member} vagm ON (vag.group = vagm.group AND (vag.role = vagm.role OR vag.role IS NULL))
                WHERE
                    (vag.startdate IS NULL OR vag.startdate < current_timestamp)
                    AND (vag.stopdate IS NULL OR vag.stopdate > current_timestamp)
                    AND vagm.member = ?
            ) AS ag ON (
                ag.view = v.id
            )';
            $where .= "
                AND (
                    v.owner = ?
                    OR vg.edit_views = 1
                    OR ((v.startdate IS NULL OR v.startdate < current_timestamp)
                        AND (v.stopdate IS NULL OR v.stopdate > current_timestamp)
                        AND (va.accesstype = 'public'
                            OR va.accesstype = 'loggedin'
                            OR (va.accesstype = 'friends' AND f.usr2 = ?)
                            OR (vau.usr = ?)
                            OR (ag.member = ?)
                        )
                    )
                )
                AND (
                    v.group IS NULL OR gd.deleted = 0
                )";
            $ph = array_merge(array($viewerid,$viewerid,$viewerid,$viewerid), $ph, array($viewerid,$viewerid,$viewerid,$viewerid));
        }

        if (!$ownedby && $ownerquery) {
            $from .= '
            LEFT OUTER JOIN {usr} qu ON (v.owner = qu.id)
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
            $ph = array_merge($ph, array($ownerquery,$ownerquery,$ownerquery,$ownerquery,$ownerquery));
        }

        $count = count_records_sql('SELECT COUNT (DISTINCT v.id) ' . $from . $where, $ph);
        $orderby = 'title ASC';
        if (!empty($sort)) {
            $orderby = '';
            foreach ($sort as $item) {
                if (!preg_match('/^[a-zA-Z_0-9"]+$/', $item['column'])) {
                    continue; // skip this item (it fails validation)
                }

                if (!empty($orderby)) {
                    $orderby .= ', ';
                }
                $orderby .= $item['column'];
                if ($item['desc']) {
                    $orderby .= ' DESC';
                }
                else {
                    $orderby .= ' ASC';
                }
            }
        }
        $viewdata = get_records_sql_assoc('
            SELECT * FROM (
                SELECT
                    v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution,
                    v.template, v.mtime, v.ctime
                ' . $from . $where . '
                GROUP BY
                    v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution,
                    v.template, v.mtime, v.ctime
            ) a
            ORDER BY a.' . $orderby . ', a.id ASC',
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

        return (object) array(
            'data'  => array_values($viewdata),
            'count' => $count,
        );

    }
    /**
     * Get views that have been actively shared with a user, either
     * shared with the user individually, shared as a friend of the
     * view owner, or shared with one of the user's groups.
     *
     * @param string   $query       Search string for title/description
     * @param string   $tag         Return only views with this tag
     * @param integer  $limit
     * @param integer  $offset
     *
     */
    public static function shared_to_user($query=null, $tag=null, $limit=null, $offset=0) {
        global $USER;
        $viewerid = $USER->get('id');

        $values = array();

        $select = "
            SELECT va.view
            FROM {view_access} va
                INNER JOIN {view} v ON va.view = v.id";

        if ($tag) { // Filter by the tag
            $select .= "
                INNER JOIN {view_tag} vt ON (vt.view = va.view AND vt.tag = ?)";
            $values[] = $tag;
        }
        elseif ($query) { // Include matches on the title, description or tag
            $select .= "
                LEFT JOIN {view_tag} vt ON (vt.view = va.view AND vt.tag = ?)";
            $values[] = $query;
        }

        $select .= "
                LEFT JOIN {usr_friend} f1 ON (v.owner = f1.usr1 AND f1.usr2 = ?)
                LEFT JOIN {usr_friend} f2 ON (v.owner = f2.usr2 AND f2.usr1 = ?)
                LEFT JOIN {group_member} gm ON (va.group = gm.group AND (va.role IS NULL OR va.role = gm.role) AND gm.member = ?)";

        array_push($values, $viewerid, $viewerid, $viewerid);

        $where = "
            WHERE
                (v.owner IS NULL OR (v.owner > 0 AND v.owner != ?))
                AND v.type NOT IN ('profile', 'dashboard', 'grouphomepage')
                AND (v.startdate IS NULL OR v.startdate < current_timestamp)
                AND (v.stopdate IS NULL OR v.stopdate > current_timestamp)
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                AND (va.usr = ?
                    OR (va.accesstype = 'friends' AND (f1.usr2 IS NOT NULL OR f2.usr1 IS NOT NULL))
                    OR gm.member IS NOT NULL
                )";

        array_push($values, $viewerid, $viewerid);

        if ($query) {
            $like = db_ilike();
            $where .= "
                AND (v.title $like '%' || ? || '%' OR v.description $like '%' || ? || '%' OR vt.tag = ?)";
            array_push($values, $query, $query, $query);
        }

        $innerselect = $select . $where;

        $result = (object) array(
            'data'  => array(),
            'count' => count_records_sql('SELECT COUNT(*) FROM {view} WHERE id IN (' . $innerselect . ')', $values),
        );

        if (!$result->count) {
            return $result;
        }

        // Order by last comment.
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

        $viewdata = get_records_sql_assoc('
            SELECT
                v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution, v.mtime, v.ctime,
                last.mtime AS lastcommenttime, last.author AS commentauthor, last.authorname AS commentauthorname,
                last.description AS commenttext, last.id AS commentid,
                COUNT(c.artefact) AS commentcount
            FROM {view} v
                LEFT JOIN {artefact_comment_comment} c ON (c.onview = v.id AND c.deletedby IS NULL AND c.private = 0)
                LEFT JOIN (' . $lastcomments . '
                ) last ON last.onview = v.id
            WHERE v.id IN (' . $innerselect . ')
            GROUP BY
                v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution, v.mtime, v.ctime,
                last.mtime, last.author, last.authorname, last.description, last.id
            ORDER BY GREATEST(last.mtime, v.mtime) DESC, v.title, v.id',
            $values, $offset, $limit
        );

        View::get_extra_view_info($viewdata, false);

        $result->data =& $viewdata;

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
     * not owned by the group
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
            WHERE a.group = ? AND m.member = ? AND (v.group IS NULL OR v.group != ?)';
        $ph = array($groupid, $userid, $groupid);

        $count = count_records_sql('SELECT COUNT(*) ' . $from, $ph);
        $viewdata = get_records_sql_assoc('
            SELECT v.id,v.title,v.startdate,v.stopdate,v.description,v.group,v.owner,v.ownerformat,v.institution ' . $from . '
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
            'data'  => array_values($viewdata),
            'count' => $count,
        );
    }


    /** 
     * Get views submitted to a group
     */
    public static function get_submitted_views($groupid, $userid=null) {
        $values = array($groupid);
        $where = 'submittedgroup = ?';

        if (!empty($userid)) { // Filter by view owner
            $values[] = (int) $userid;
            $where .= ' AND v.owner = ?';
        }

        $viewdata = get_records_sql_array('
            SELECT
                v.id as id, v.title, v.description, v.owner, v.ownerformat, u.firstname, u.lastname, u.preferredname,
                ' . db_format_tsfield('v.submittedtime','submittedtime') . '
            FROM {view} v
            INNER JOIN {usr} u ON u.id = v.owner
            WHERE ' . $where . '
            ORDER BY u.firstname ASC, u.lastname',
            $values
        );

        if ($viewdata) {
            foreach ($viewdata as &$v) {
                $v->sharedby = full_name($v);
                $v = (array)$v;
            }
        }

        return $viewdata;
    }


    public static function get_extra_view_info(&$viewdata, $getartefacts = true) {
        if ($viewdata) {
            // Get view owner details for display
            $owners = array();
            $groups = array();
            $institutions = array();
            foreach ($viewdata as $v) {
                if ($v->owner && !isset($owners[$v->owner])) {
                    $owners[$v->owner] = (int)$v->owner;
                } else if ($v->group && !isset($groups[$v->group])) {
                    $groups[$v->group] = (int)$v->group;
                } else if (strlen($v->institution) && !isset($institutions[$v->institution])) {
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
            $tags = get_records_select_array('view_tag', 'view IN (' . $viewidlist . ')');
            if ($tags) {
                foreach ($tags as &$tag) {
                    $viewdata[$tag->view]->tags[] = $tag->tag;
                }
            }
            if (!empty($owners)) {
                $owners = get_records_select_assoc('usr', 'id IN (' . join(',', $owners) . ')', null, '', 
                                                   'id,username,firstname,lastname,preferredname,admin,staff,studentid,email,profileicon');
            }
            if (!empty($groups)) {
                $groups = get_records_select_assoc('group', 'id IN (' . join(',', $groups) . ')', null, '', 'id,name');
            }
            if (!empty($institutions)) {
                $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
                $institutions['mahara']->displayname = get_config('sitename');
            }
            foreach ($viewdata as &$v) {
                if ($v->owner) {
                    $v->sharedby = View::owner_name($v->ownerformat, $owners[$v->owner]);
                    $v->user = $owners[$v->owner];
                } else if ($v->group) {
                    $v->sharedby = $groups[$v->group]->name;
                } else if ($v->institution) {
                    $v->sharedby = $institutions[$v->institution]->displayname;
                }
                $v = (array)$v;
            }
        }
    }

    public static function set_nav($group, $institution, $share=false) {
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
        else if ($this->institution == 'mahara') {
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
        $this->set('layout', $template->get('layout'));
        $this->set('description', $template->get('description'));
        $this->set('tags', $template->get('tags'));
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

    public static function get_templatesearch_data(&$search) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $results = self::view_search($search->query, $search->ownerquery, null, $search->copyableby, $search->limit, $search->offset, true);

        foreach ($results->data as &$r) {
            $r['form'] = pieform(create_view_form($search->copyableby->group, $search->copyableby->institution, $r['id']));
        }

        $params = array();
        if (!empty($search->query)) {
            $params[] = 'viewquery=' . $search->query;
        }
        if (!empty($search->ownerquery)) {
            $params[] = 'ownerquery=' . $search->query;
        }
        if (!empty($search->group)) {
            $params[] = 'group=' . $search->group;
        }
        if (!empty($search->institution)) {
            $params[] = 'institution=' . $search->institution;
        }
        $params[] = 'viewlimit=' . $search->limit;

        $smarty = smarty_core();
        $smarty->assign_by_ref('results', $results->data);
        $search->html = $smarty->fetch('view/templatesearchresults.tpl');
        $search->count = $results->count;

        $search->pagination = build_pagination(array(
            'id' => 'templatesearch_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'view/choosetemplate.php?' . join('&amp;', $params),
            'count' => $results->count,
            'limit' => $search->limit,
            'offset' => $search->offset,
            'offsetname' => 'viewoffset',
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
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
        while (record_exists('view_access', 'token', $data->token)) {
            $data->token = get_random_key(20);
        }
        if (insert_record('view_access', $data)) {
            return $data;
        }
        return false;
    }

    public function owner_link() {
        if ($this->owner) {
            return get_config('wwwroot') . 'user/view.php?id=' . $this->owner;
        }
        else if ($this->group) {
            return get_config('wwwroot') . 'group/view.php?id=' . $this->group;
        }
        return null;
    }

    public function display_title($long=true, $titlelink=true, $includeowner=true) {
        if ($this->type == 'profile') {
            $title = hsc(display_name($this->owner, null, true));
            if ($long) {
                return '<strong>' . get_string('usersprofile', 'mahara', $title) . '</strong>';
            }
            return $title;
        }
        if ($this->type == 'dashboard') {
            return '<strong>' . get_string('dashboardviewtitle', 'view') . '</strong>';
        }

        $ownername = hsc($this->formatted_owner());

        if ($this->type == 'grouphomepage') {
            return '<strong>' . get_string('aboutgroup', 'group', $ownername) . '</strong>';
        }

        $ownerlink = null;
        if ($includeowner) {
            $ownerlink = $this->owner_link();
        }

        if ($titlelink) {
            $title = '<a href="' . get_config('wwwroot') . 'view/view.php?id=' . $this->id . '">' . hsc($this->title) . '</a>';
        }
        else {
            $title = '<strong>' . hsc($this->title) . '</strong>';
        }

        if (isset($ownerlink)) {
            return get_string('viewtitleby', 'view', $title, $ownerlink, $ownername);
        }

        return $title;
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
                    $redirecturl = '/group/view.php?id='.$this->get('group');
                }
                $redirecturl = '/view/groupviews.php?group='.$this->get('group');
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
     */
    public function get_url() {
        if ($this->type == 'profile') {
            $url = 'user/view.php?id=' . (int) $this->owner;
        }
        else if ($this->type == 'dashboard') {
            $url = '';
        }
        else if ($this->type == 'grouphomepage') {
            $url = 'group/view.php?id=' . $this->group;
        }
        else {
            $url = 'view/view.php?id=' . (int) $this->id;
        }
        return get_config('wwwroot') . $url;
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
                    AND (va.accesstype IN ('public', 'loggedin', 'friends', 'objectionable')
                         OR va.usr = ? OR va.token IS NOT NULL OR gm.member IS NOT NULL)
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

            if ($a->allowcomments) {
                $allowcomments |= $a->allowcomments;
                $approvecomments &= $a->approvecomments;
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

    // Returns a form to mark a view as unobjectionable, if the user is allowed
    // to do that.
    function notrude_form() {
        global $USER;
        $owner = $this->get('owner');
        if (!(($owner && ($USER->get('admin') || $USER->is_admin_for_user($owner)))
              || ($this->get('group') && $USER->get('admin')))) {
            return;
        }

        $access = self::user_access_records($this->id, $USER->get('id'));

        if (empty($access)) {
            return;
        }

        $isrude = false;
        foreach ($access as $a) {
            // Nasty hack: If the objectionable access record has a stop date, it
            // means that one of the admins has already dealt with it, so we don't
            // mark the view as objectionable.
            if ($a->accesstype == 'objectionable' && empty($a->stopdate)) {
                $isrude = true;
                break;
            }
        }

        if (!$isrude) {
            return;
        }

        return array(
            'name'     => 'viewnotrude',
            'elements' => array(
                'text' => array(
                    'type' => 'html',
                    'value' => get_string('viewobjectionableunmark', 'view'),
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('notobjectionable', 'view'),
                ),
            ),
        );
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
     * @param integer $owner
     * @param integer $group
     * @param string  $institution
     * @param string  $matchconfig record all matches with given config hash (see set_access)
     *
     * @return array, array
     */
    function get_views_and_collections($owner=null, $group=null, $institution=null, $matchconfig=null) {
        $ownersql = self::owner_sql((object) array('owner' => $owner, 'group' => $group, 'institution' => $institution));
        $records = get_records_sql_array("
            SELECT v.id AS vid, v.title AS vname, v.accessconf,
                v.startdate, v.stopdate, v.template,
                c.id AS cid, c.name AS cname
            FROM {view} v
                LEFT JOIN {collection_view} cv ON v.id = cv.view
                LEFT JOIN {collection} c ON cv.collection = c.id
            WHERE v.$ownersql AND v.type = 'portfolio'
            ORDER BY c.name, v.title",
            array()
        );

        $collections = array();
        $views = array();

        if (!$records) {
            return array($collections, $views);
        }

        foreach ($records as &$r) {
            $v = array(
                'id'        => $r->vid,
                'name'      => $r->vname,
                'startdate' => $r->startdate,
                'stopdate'  => $r->stopdate,
                'template'  => $r->template,
            );
            if ($r->cid) {
                if (!isset($collections[$r->cid])) {
                    $collections[$r->cid] = array('id' => $r->cid, 'name' => $r->cname, 'views' => array());
                    if ($matchconfig && $matchconfig == $r->accessconf) {
                        $collections[$r->cid]['match'] = true;
                    }
                }
                $collections[$r->cid]['views'][$r->vid] = $v;
            }
            else {
                $views[$r->vid] = $v;
                if ($matchconfig && $matchconfig == $r->accessconf) {
                    $views[$r->vid]['match'] = true;
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
            SELECT va.*, g.grouptype, g.name
            FROM {view_access} va LEFT OUTER JOIN {group} g ON (g.id = va.group AND g.deleted = 0)
            WHERE va.view IN (' . join(',', array_keys($viewindex)) . ') AND va.visible = 1
            ORDER BY va.view, va.accesstype, g.grouptype, va.role, g.name, va.group, va.usr',
            array()
        );

        if (!$accessgroups) {
            return $data;
        }

        foreach ($accessgroups as $access) {
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

}


function create_view_form($group=null, $institution=null, $template=null) {
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

    if (isset($values['usetemplate'])) {
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

    redirect(get_config('wwwroot') . 'view/blocks.php?new=1&id=' . $view->get('id'));
}

function createview_cancel_submit(Pieform $form, $values) {
    if (isset($values['group'])) {
        redirect(get_config('wwwroot') . 'view/groupviews.php?group=' . $values['group']);
    }
    if (isset($values['institution'])) {
        redirect(get_config('wwwroot') . 'view/institutionviews.php?institution=' . $values['institution']);
    }
    redirect(get_config('wwwroot') . 'view/');
}

function searchviews_submit(Pieform $form, $values) {
    $tag = $query = null;
    if (!empty($values['query'])) {
        if ($values['type'] == 'tagsonly') {
            $tag = $values['query'];
        }
        else {
            $query = $values['query'];
        }
    }
    $group = isset($values['group']) ? $values['group'] : null;
    $institution = isset($values['institution']) ? $values['institution'] : null;
    redirect(View::get_myviews_url($group, $institution, $query, $tag));
}

function view_group_submission_form($viewid, $tutorgroupdata, $returnto=null) {
    $options = array();
    foreach ($tutorgroupdata as $group) {
        $options[$group->id] = $group->name;
    }
    // This form sucks from a language string point of view. It should
    // use pieforms' form template feature
    return pieform(array(
        'name' => 'view_group_submission_form_' . $viewid,
        'method' => 'post',
        'renderer' => 'oneline',
        'autofocus' => false,
        'successcallback' => 'view_group_submission_form_submit',
        'elements' => array(
            'text1' => array(
                'type' => 'html', 'value' => get_string('submitthisviewto', 'view') . ' ',
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
            'view' => array(
                'type' => 'hidden',
                'value' => $viewid
            ),
            'returnto' => array(
                'type' => 'hidden',
                'value' => $returnto,
            )
        ),
    ));
}

function view_group_submission_form_submit(Pieform $form, $values) {
    redirect('/view/submit.php?id=' . $values['view'] . '&group=' . $values['options'] . '&returnto=' . $values['returnto']);
}


function objection_form() {
    $form = array(
        'name'            => 'objection_form',
        'method'          => 'post',
        'class'           => 'js-safe-hidden',
        'plugintype'      => 'core',
        'pluginname'      => 'view',
        'jsform'          => true,
        'autofocus'       => false,
        'elements'        => array(),
        'jssuccesscallback' => 'objectionSuccess',
    );
    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'title' => get_string('complaint', 'view'),
        'rows'  => 5,
        'cols'  => 80,
    );
    $form['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'value' => array(get_string('notifysiteadministrator', 'view'), get_string('cancel')),
        'confirm' => array(get_string('notifysiteadministratorconfirm', 'view')),
    );
    return $form;
}

function objection_form_submit(Pieform $form, $values) {
    global $USER, $view, $artefact;

    if (!$USER->is_logged_in()) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }

    require_once('activity.php');

    db_begin();

    // The objectionable access record ensures the view is visible
    // to admins, and also marks the view as objectionable.

    $accessrecord = (object) array(
        'view'            => $view->get('id'),
        'accesstype'      => 'objectionable',
        'allowcomments'   => 1,
        'approvecomments' => 0,
        'visible'         => 0,
    );

    delete_records('view_access', 'view', $view->get('id'), 'accesstype', 'objectionable', 'visible', 0);
    insert_record('view_access', $accessrecord);

    $data = new StdClass;
    $data->view       = $view->get('id');
    $data->message    = $values['message'];
    $data->reporter   = $USER->get('id');
    $data->ctime      = time();
    if ($artefact) {
        $data->artefact = $artefact->get('id');
    }

    activity_occurred('objectionable', $data);

    db_commit();

    if ($artefact) {
        $goto = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefact->get('id') . '&view='.$view->get('id');
    }
    else {
        $goto = get_config('wwwroot') . 'view/view.php?id='.$view->get('id');
    }
    $form->reply(PIEFORM_OK, array(
        'message' => get_string('reportsent', 'view'),
        'goto' => $goto,
    ));
}

function viewnotrude_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;

    require_once('activity.php');

    db_begin();

    // Set exipiry date on view access record
    $accessrecord = (object) array(
        'view'            => $view->get('id'),
        'accesstype'      => 'objectionable',
        'allowcomments'   => 1,
        'approvecomments' => 0,
        'visible'         => 0,
        'stopdate'        => db_format_timestamp(time() + 60*60*24*7),
    );

    delete_records('view_access', 'view', $view->get('id'), 'accesstype', 'objectionable', 'visible', 0);
    insert_record('view_access', $accessrecord);

    // Send notification to other admins
    $reportername = display_default_name($USER);

    if ($artefact) {
        $goto = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefact->get('id') . '&view='.$view->get('id');
    }
    else {
        $goto = get_config('wwwroot') . 'view/view.php?id='.$view->get('id');
    }

    $data = (object) array(
        'view'      => $view->get('id'),
        'reporter'  => $USER->get('id'),
        'subject'   => false,
        'message'   => false,
        'strings'   => (object) array(
            'subject' => (object) array(
                'key'     => 'viewunobjectionablesubject',
                'section' => 'view',
                'args'    => array($view->get('title'), $reportername),
            ),
            'message' => (object) array(
                'key'     => 'viewunobjectionablebody',
                'section' => 'view',
                'args'    => array($reportername, $view->get('title'), $view->formatted_owner()),
            ),
        ),
    );

    activity_occurred('objectionable', $data);

    db_commit();

    $form->reply(PIEFORM_OK, array(
        'message' => get_string('messagesent'),
        'goto' => $goto,
    ));
}


function objection_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_OK, array(
        'goto' => '/view/view.php?id=' . $view->get('id'),
    ));
}

function togglepublic_form($viewid) {
    $view = new View($viewid);
    $public = $view->is_public();
    $togglepublic = pieform(array(
        'name'      => 'togglepublic',
        'autofocus' => false,
        'renderer'  => 'div',
        'elements'  => array(
            'changeto' => array(
                'type'  => 'hidden',
                'value' => ($public) ? 'loggedin' : 'public'
            ),
            'id' => array(
                'type' => 'hidden',
                'value' => $viewid
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => ($public) ? get_string('loggedinusersonly') : get_string('allowpublicaccess'),
            ),
        ),
    ));
    return $togglepublic;
}

function togglepublic_submit(Pieform $form, $values) {
    global $SESSION, $userid;
    $access = array(
        array(
            'type'      => 'loggedin',
            'startdate' => null,
            'stopdate'  => null,
        ),
    );

    if ($values['changeto'] == 'public') {
        $access[] = array(
            'type'      => 'public',
            'startdate' => null,
            'stopdate'  => null,
        );
    }
    $view = new View($values['id']);
    $view->set_access($access);
    $SESSION->add_ok_msg(get_string('viewaccesseditedsuccessfully', 'view'));

    redirect('/view/blocks.php?id=' . $view->get('id'));
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
