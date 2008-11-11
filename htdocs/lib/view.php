<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
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
    private $submittedto;
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
    private $columns;
    private $dirtycolumns; // for when we change stuff
    private $tags;
    private $editingroles;
    private $template;
    private $copynewuser = 0;
    private $copynewgroups;
    private $copyconfig;
    private $type;

    public function __construct($id=0, $data=null) {
        if (!empty($id)) {
            $tempdata = get_record('view','id',$id);
            if (empty($tempdata)) {
                throw new ViewNotFoundException("View with id $id not found");
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
            safe_require('grouptype', $group->grouptype);
            $this->editingroles = call_static_method('GroupType' . ucfirst($group->grouptype), 'get_view_editing_roles');
        }
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'tags') { // special case
            return $this->get_tags();
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
            if (in_array($k, array('mtime', 'ctime', 'atime', 'startdate', 'stopdate')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }

        db_begin();

        if (empty($this->id)) {
            // users are only allowed one profile view
            if ($this->type == 'profile' && record_exists('view', 'owner', $this->owner, 'type', 'profile')) {
                throw new ViewLimitException(get_string('onlonlyyoneprofileviewallowed', 'error'));
            }
            $this->id = insert_record('view', $fordb, 'id', true);
        }
        else {
            update_record('view', $fordb, 'id');
        }

        delete_records('view_tag', 'view', $this->get('id'));
        foreach ($this->get_tags() as $tag) {
            insert_record('view_tag', (object)array( 'view' => $this->get('id'), 'tag' => $tag));
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
        db_begin();
        delete_records('artefact_feedback','view',$this->id);
        delete_records('view_feedback','view',$this->id);
        delete_records('view_access','view',$this->id);
        delete_records('view_access_group','view',$this->id);
        delete_records('view_access_usr','view',$this->id);
        delete_records('view_tag','view',$this->id);
        delete_records('usr_watchlist_view','view',$this->id);
        if ($blockinstanceids = get_column('block_instance', 'id', 'view', $this->id)) {
            foreach ($blockinstanceids as $id) {
                $bi = new BlockInstance($id);
                $bi->delete();
            }
        }
        delete_records('view','id',$this->id);
        $this->deleted = true;
        db_commit();
    }

    public function get_access($timeformat=null) {

        if (is_mysql()) {
            $uid = 'usr';
            $gid = '"group"';
        }
        else {
            $uid = 'CAST (usr AS TEXT)';
            $gid = 'CAST ("group" AS TEXT)';
        }

        $data = get_records_sql_array("
            SELECT accesstype AS type, NULL AS id, NULL AS role, NULL AS grouptype, startdate, stopdate
                FROM {view_access}
                WHERE view = ?
        UNION
            SELECT 'user' AS type, $uid AS id, NULL AS role, NULL AS grouptype, startdate, stopdate
                FROM {view_access_usr}
                WHERE view = ?
        UNION
            SELECT 'group', $gid, role, grouptype, startdate, stopdate FROM {view_access_group}
                INNER JOIN {group} g ON (\"group\" = g.id AND g.deleted = ?)
                WHERE view = ?
        UNION
            SELECT 'token', token, NULL AS role, NULL AS grouptype, startdate, stopdate
                FROM {view_access_token}
                WHERE view = ?
        ", array($this->id, $this->id, 0, $this->id, $this->id));
        if ($data) {
            foreach ($data as &$item) {
                $item = (array)$item;
                if ($item['role']) {
                    $item['roledisplay'] = get_string($item['role'], 'grouptype.'.$item['grouptype']);
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
        }
        else {
            $data = array();
        }
        return $data;
    }

    public function set_access($accessdata) {
        global $USER;
        require_once('activity.php');

        // For users who are being removed from having access to this view, they
        // need to have the view and any attached artefacts removed from their
        // watchlist.
        $oldusers = array();
        foreach ($this->get_access() as $item) {
            if ($item['type'] == 'user') {
                $oldusers[] = $item;
            }
        }

        $newusers = array();
        if ($accessdata) {
            foreach ($accessdata as $item) {
                if ($item['type'] == 'user') {
                    $newusers[] = $item;
                }
            }
        }

        $userstodelete = array();
        foreach ($oldusers as $olduser) {
            foreach ($newusers as $newuser) {
                if ($olduser['id'] == $newuser['id']) {
                    continue(2);
                }
            }
            $userstodelete[] = $olduser;
        }

        if ($userstodelete) {
            $userids = array();
            foreach ($userstodelete as $user) {
                $userids[] = intval($user['id']);
            }
            $userids = implode(',', $userids);

            execute_sql('DELETE FROM {usr_watchlist_view}
                WHERE view = ' . $this->get('id') . '
                AND usr IN (' . $userids . ')');
        }

        $beforeusers = activity_get_viewaccess_users($this->get('id'), $USER->get('id'), 'viewaccess');

        // Procedure:
        // get list of current friends - this is available in global $data
        // compare with list of new friends
        // work out which friends are being removed
        // foreach friend
        //     // remove record from usr_watchlist_view where usr = ? and view = ?
        //     // remove records from usr_watchlist_artefact where usr = ? and view = ?
        // endforeach
        //
        db_begin();
        delete_records('view_access', 'view', $this->get('id'));
        delete_records('view_access_usr', 'view', $this->get('id'));
        delete_records('view_access_group', 'view', $this->get('id'));
        delete_records('view_access_token', 'view', $this->get('id'));
        $time = db_format_timestamp(time());

        // View access
        if ($accessdata) {
            foreach ($accessdata as $item) {
                $accessrecord = new StdClass;
                $accessrecord->view = $this->get('id');
                $accessrecord->startdate = db_format_timestamp($item['startdate']);
                $accessrecord->stopdate  = db_format_timestamp($item['stopdate']);
                switch ($item['type']) {
                    case 'public':
                    case 'loggedin':
                    case 'friends':
                        $accessrecord->accesstype = $item['type'];
                        insert_record('view_access', $accessrecord);
                        break;
                    case 'user':
                        $accessrecord->usr = $item['id'];
                        insert_record('view_access_usr', $accessrecord);
                        break;
                    case 'group':
                        $accessrecord->group = $item['id'];
                        if ($item['role']) {
                            $accessrecord->role = $item['role'];
                        }
                        insert_record('view_access_group', $accessrecord);
                        break;
                    case 'token':
                        $accessrecord->token = $item['id'];
                        insert_record('view_access_token', $accessrecord);
                        break;
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
    }

    public function get_autocreate_grouptypes() {
        if (!isset($this->copynewgroups)) {
            $this->copynewgroups = get_column('view_autocreate_grouptype', 'grouptype', 'view', $this->id);
        }
        return $this->copynewgroups;
    }

    public function release($groupid, $releaseuser=null) {
        if ($this->get('submittedto') != $groupid) {
            throw new ParameterException("View with id " . $this->get('id') .
                                         " has not been submitted to group $groupid");
        }
        $releaseuser = optional_userobj($releaseuser);
        $this->set('submittedto', null);
        $this->commit();
        $ownerlang = get_user_language($this->get('owner'));
        require_once('activity.php');
        activity_occurred('maharamessage', 
                  array('users'   => array($this->get('owner')),
                  'subject' => get_string_from_language($ownerlang, 'viewreleasedsubject', 'group'),
                  'message' => get_string_from_language($ownerlang, 'viewreleasedmessage', 'group',
                       get_field('group', 'name', 'id', $groupid), 
                       display_name($releaseuser, $this->get_owner_object()))));
    }

    /**
     * Returns HTML for the category list
     *
     * @param string $defaultcategory The currently selected category
     * @param View   $view            The view we're currently using
    */
    public static function build_category_list($defaultcategory, View $view, $new=0) {
        require_once(get_config('docroot') . '/blocktype/lib.php');
        // Change to a left join to show tabs with no results
        $cats = get_records_sql_array('SELECT bc.name, COUNT(*) AS "count"
            FROM {blocktype_category} bc
            INNER JOIN {blocktype_installed_category} bic ON (bc.name = bic.category)
            WHERE EXISTS (
                SELECT 1 
                    FROM {blocktype_installed_viewtype} bivt
                    JOIN {blocktype_installed} bi ON bi.name = bivt.blocktype
                    WHERE bivt.viewtype = ?
                    AND bic.blocktype = bi.name
            )
            GROUP BY bc.name
            ORDER BY bc.name', array($view->get('type')));
        $categories = array_map(
            create_function(
                '$a', 
                'return array(
                    "name" => $a->name,
                    "title" => call_static_method("PluginBlocktype", "category_title_from_name", $a->name) . " (" . $a->count . ")",
                );'
            ),
            $cats
        );

        // The 'internal' plugin is known to the outside world as 'profile', so 
        // we need to sort on the actual name
        usort($categories, create_function('$a, $b', 'return strnatcasecmp($a[\'title\'], $b[\'title\']);'));

        $flag = false;
        foreach ($categories as &$cat) {
            $classes = '';
            if (!$flag) {
                $flag = true;
                $classes[] = 'first';
            }
            if ($defaultcategory == $cat['name']) {
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
        $smarty->assign('viewid', $view->get('id'));
        $smarty->assign('new', $new);
        return $smarty->fetch('view/blocktypecategorylist.tpl');
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
        $blocktypes = PluginBlockType::get_blocktypes_for_category_and_viewtype($category, $this->get('type'));

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

        if (empty($action)) {
            return;
        }
    
        $actionstring = $action;
        $action = substr($action, 0, strpos($action, '_'));
        $actionstring  = substr($actionstring, strlen($action) + 1);

        // Actions from <input type="image"> buttons send an _x and _y
        if (substr($actionstring, -2) == '_x' || substr($actionstring, -2) == '_y') {
            $actionstring = substr($actionstring, 0, -2);
        }
        
        $values = self::get_values_for_action($actionstring);

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
                        $yeslink = get_config('wwwroot') . '/view/blocks.php?id=' . $this->get('id') . '&c=file&new=' . $new . '&action_' . $action . '_' .  $actionstring . '=1&sure=true';
                        $baselink = '/view/blocks.php?id=' . $this->get('id') . '&c=' . $category . '&new=' . $new;
                        $SESSION->add_info_msg(get_string('confirmdeleteblockinstance', 'view') 
                            . ' <a href="' . $yeslink . '">' . get_string('yes') . '</a>'
                            . ' <a href="' . $baselink . '">' . get_string('no') . '</a>', false);
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
        if ($this->get('numcolumns') > 1) {
            $layout = $this->get('layout');
            if ($layout) {
                $i = 0;
                // The get_field also verifies the layout is correct for the
                // number of columns in the view
                foreach (explode(',', get_field('view_layout', 'widths', 'id', $layout, 'columns', $this->get('numcolumns'))) as $width) {
                    $this->columns[++$i]['width'] = $width;
                }
            }
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
     * @param int  $column     The column to build
     */
    public function build_column($column, $editing=false) {
        global $USER;
        $data = $this->get_column_datastructure($column);

        if ($editing) {
            $renderfunction = 'render_editing';
        }
        else {
            $renderfunction = 'render_viewing';
        }
        $blockcontent = '';
        foreach($data['blockinstances'] as $blockinstance) {
            $configure = ($blockinstance->get('id') == $this->blockinstance_currently_being_configured);
            $result = $blockinstance->$renderfunction($configure, false);
            if ($editing) {
                $blockcontent .= $result['html'];
                if ($configure) {
                    $blockcontent .= '<script type="text/javascript">' . $result['javascript'] . '</script>';
                }
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
        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $values['blocktype'],
                'title'      => call_static_method(generate_class_name('blocktype', $values['blocktype']), 'get_title'), 
                'view'       => $this->get('id'),
                'column'     => $values['column'],
                'order'      => $values['order'],
            )
        );
        $this->shuffle_column($values['column'], $values['order']);
        $bi->commit();
        $this->dirtycolumns[$values['column']] = 1;

        if ($values['returndata']) {
            // Make sure it's in configure mode if it has configuration
            return $bi->render_editing(call_static_method(generate_class_name('blocktype', $values['blocktype']), 'has_instance_config'), true);
        }
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
        return $bi->build_configure_form();
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

            }
            // shuffle everything down
            $this->shuffle_helper('order', 'down', '>', $remove, '"column" = ?', array($column));

            if (!empty($insert)) {
                // now move it back
                set_field('block_instance', 'order', $insert, 'view', $this->get('id'), 'column', $column, 'order', 0);
            }
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
        //log_debug($data);
        $artefacttypes = $data['artefacttypes'];
        $offset        = $data['offset'];
        $limit         = $data['limit'];
        $selectone     = $data['selectone'];
        $value         = $data['defaultvalue'];
        $elementname   = $data['name'];
        $template      = $data['template'];
        $extraselect   = (isset($data['extraselect']) ? ' AND ' . $data['extraselect'] : '');

        $offset -= $offset % $limit;

        safe_require('blocktype', $data['blocktype']);
        $blocktypeclass = generate_class_name('blocktype', $data['blocktype']);

        $sql = ' FROM {artefact} a ';
        if (isset($data['extrajoin'])) {
            $sql .= $data['extrajoin'];
        }
        if ($group) {
            // Get group-owned artefacts that the user has view
            // permission on, and site-owned artefacts
            $sql .= '
            LEFT OUTER JOIN (
                SELECT
                    r.artefact, r.can_view, m.group
                FROM
                    {artefact_access_role} r
                    INNER JOIN {group_member} m ON r.role = m.role
                WHERE
                    m."group" = ' . $group . '
                    AND m.member = ' . $USER->get('id') . '
                    AND r.can_view = 1
            ) ga ON (ga.group = a.group AND a.id = ga.artefact)';
            $select = "(a.institution = 'mahara' OR ga.can_view = 1)";
        }
        else if ($institution) {
            // Site artefacts & artefacts owned by this institution
            $select = "(a.institution = 'mahara' OR a.institution = '$institution')";
        }
        else { // The view is owned by a normal user
            // Get artefacts owned by the user, group-owned artefacts
            // the user has republish permission on, artefacts owned
            // by the user's institutions.
            $sql .= '
            LEFT OUTER JOIN {artefact_access_usr} aau ON (a.id = aau.artefact AND aau.usr = ' . $USER->get('id') . ')
            LEFT OUTER JOIN (
                SELECT
                    aar.artefact, aar.can_republish, m.group
                FROM
                    {artefact_access_role} aar
                    INNER JOIN {group_member} m ON aar.role = m.role
                WHERE
                    m.member = ' . $USER->get('id') . '
                    AND aar.can_republish = 1
            ) ra ON (a.id = ra.artefact AND a.group = ra.group)';
            $institutions = array_keys($USER->get('institutions'));
            $institutions[] = 'mahara';
            $select = '(
                owner = ' . $USER->get('id') . '
                OR ra.can_republish = 1
                OR aau.can_republish = 1
                OR a.institution IN (' . join(',', array_map('db_quote', $institutions)) . ')
            )';
        }
        if (!empty($artefacttypes)) {
            $select .= ' AND artefacttype IN(' . implode(',', array_map('db_quote', $artefacttypes)) . ')';
        }

        if ($search != '') {
            $search = db_quote('%' . str_replace('%', '%%', $search) . '%');
            $select .= 'AND (title ' . db_ilike() . '(' . $search . ') OR description ' . db_ilike() . '(' . $search . ') )';
        }

        $select .= $extraselect;

        $sortorder = 'title';
        if (method_exists($blocktypeclass, 'artefactchooser_get_sort_order')) {
            $sortorder = call_static_method($blocktypeclass, 'artefactchooser_get_sort_order');
        }
        $artefacts = get_records_sql_array('SELECT a.* ' . $sql . ' WHERE ' . $select . ($sortorder ? (' ORDER BY ' . $sortorder) : ''), null, $offset, $limit);
        $totalartefacts = count_records_sql('SELECT COUNT(*) ' . $sql . ' WHERE ' . $select);

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

        $smarty = smarty_core();
        $smarty->assign('artefacts', $result);
        $smarty->assign('datatable', $elementname . '_data');
        $smarty->assign('count', $totalartefacts);
        $baseurl = View::make_base_url();
        $pagination = build_pagination(array(
            'id' => $elementname . '_pagination',
            'class' => 'ac-pagination',
            'url' => View::make_base_url() . (param_boolean('s') ? '&s=1' : ''),
            'count' => $totalartefacts,
            'limit' => $limit,
            'offset' => $offset,
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

        return array($result, $pagination, $totalartefacts, $offset);
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

    /**
     * Return the profile view object for the given userid 
     *
     * @param int $userid the user id to fetch the profile view for 
     */
    public static function profile_view($userid) {
        $viewid = get_field('view', 'id', 'type', 'profile', 'owner', $userid);
        if (!$viewid) {
            $viewid = install_profile_view($userid);
        }
        return new View($viewid);
    }

    public static function get_myviews_data($limit=5, $offset=0, $groupid=null, $institution=null) {

        global $USER;
        $userid = $USER->get('id');

        if ($groupid) {
            $count = count_records('view', 'group', $groupid);
            $viewdata = get_records_sql_array('SELECT v.id,v.title,v.startdate,v.stopdate,v.description, v.template
                FROM {view} v
                WHERE v.group = ' . $groupid . '
                ORDER BY v.title, v.id', '', $offset, $limit);
        }
        else if ($institution) {
            $count = count_records('view', 'institution', $institution);
            $viewdata = get_records_sql_array('SELECT v.id,v.title,v.startdate,v.stopdate,v.description, v.template
                FROM {view} v
                WHERE v.institution = ?
                ORDER BY v.title, v.id', array($institution), $offset, $limit);
        }
        else {
            $count = count_records('view', 'owner', $userid, 'type', 'portfolio');
            $viewdata = get_records_sql_array('SELECT v.id,v.title,v.startdate,v.stopdate,v.description, v.template, g.id AS groupid, g.name
                FROM {view} v
                LEFT OUTER JOIN {group} g ON (v.submittedto = g.id AND g.deleted = 0)
                WHERE v.owner = ' . $userid . '
                AND v.type = \'portfolio\'
                ORDER BY v.title, v.id', '', $offset, $limit);
        }

        if ($viewdata) {
            $viewidlist = implode(', ', array_map(create_function('$a', 'return $a->id;'), $viewdata));
            $artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title, a.artefacttype, t.plugin
                FROM {view_artefact} va
                INNER JOIN {artefact} a ON va.artefact = a.id
                INNER JOIN {artefact_installed_type} t ON a.artefacttype = t.name
                WHERE va.view IN (' . $viewidlist . ')
                GROUP BY va.view, va.artefact, a.title, a.artefacttype, t.plugin
                ORDER BY a.title, va.artefact', '');
            $accessgroups = get_records_sql_array('SELECT view, accesstype, grouptype, role, id, name, startdate, stopdate
                FROM (
                    SELECT view, \'group\' AS accesstype, g.grouptype, vg.role, g.id, g.name, startdate, stopdate
                    FROM {view_access_group} vg
                    INNER JOIN {group} g ON g.id = vg.group AND g.deleted = 0
                    UNION SELECT view, \'user\' AS accesstype, NULL AS grouptype, NULL AS role, usr AS id, \'\' AS name, startdate, stopdate
                    FROM {view_access_usr} vu
                    UNION SELECT view, accesstype, NULL AS grouptype, NULL AS role, 0 AS id, \'\' AS name, startdate, stopdate
                    FROM {view_access} va
                ) AS a
                WHERE view in (' . $viewidlist . ')
                ORDER BY view, accesstype, grouptype, role, name, id
            ', array());
        }
    
        $data = array();
        if ($viewdata) {
            for ($i = 0; $i < count($viewdata); $i++) {
                $index[$viewdata[$i]->id] = $i;
                $data[$i]['id'] = $viewdata[$i]->id;
                $data[$i]['title'] = $viewdata[$i]->title;
                $data[$i]['description'] = $viewdata[$i]->description;
                if (!empty($viewdata[$i]->name)) {
                    $data[$i]['submittedto'] = array('name' => $viewdata[$i]->name, 'id' => $viewdata[$i]->groupid);
                }
                $data[$i]['artefacts'] = array();
                $data[$i]['accessgroups'] = array();
                if ($viewdata[$i]->startdate && $viewdata[$i]->stopdate) {
                    $data[$i]['access'] = get_string('accessbetweendates2', 'view', format_date(strtotime($viewdata[$i]->startdate), 'strftimedate'),
                        format_date(strtotime($viewdata[$i]->stopdate), 'strftimedate'));
                }
                else if ($viewdata[$i]->startdate) {
                    $data[$i]['access'] = get_string('accessfromdate2', 'view', format_date(strtotime($viewdata[$i]->startdate), 'strftimedate'));
                }
                else if ($viewdata[$i]->stopdate) {
                    $data[$i]['access'] = get_string('accessuntildate2', 'view', format_date(strtotime($viewdata[$i]->stopdate), 'strftimedate'));
                }
                $data[$i]['template'] = $viewdata[$i]->template;
            }

            // Go through all the artefact records and put them in with the
            // views they belong to.
            if ($artefacts) {
                foreach ($artefacts as $artefactrec) {
                    safe_require('artefact', $artefactrec->plugin);
                    // Perhaps I shouldn't have to construct the entire
                    // artefact object to render the name properly.
                    $classname = generate_artefact_class_name($artefactrec->artefacttype);
                    $artefactobj = new $classname(0, array('title' => $artefactrec->title));
                    $artefactobj->set('dirty', false);
                    if (!$artefactobj->in_view_list()) {
                      continue;
                    }
                    $artname = $artefactobj->display_title(30);
                    if (strlen($artname)) {
                      $data[$index[$artefactrec->view]]['artefacts'][] = array('id'    => $artefactrec->artefact,
                                                                               'title' => $artname);
                    }
                }
            }
            if ($accessgroups) {
                foreach ($accessgroups as $access) {
                  $data[$index[$access->view]]['accessgroups'][] = array(
                      'accesstype' => $access->accesstype, // friends, group, loggedin, public, tutorsgroup, user
                      'role' => $access->role,
                      'roledisplay' => $access->role ? get_string($access->role, 'grouptype.' . $access->grouptype) : null,
                      'id' => $access->id,
                      'name' => $access->name,
                      'startdate' => $access->startdate,
                      'stopdate' => $access->stopdate
                      );
                }
            }
        }

        return (object) array(
            'data'  => $data,
            'count' => $count,
        );
    }


    public static function owner_sql($ownerobj) {
        if (!empty($ownerobj->institution)) {
            return 'institution = ' . db_quote($ownerobj->institution);
        }
        if (!empty($ownerobj->group)) {
            return '"group" = ' . (int)$ownerobj->group;
        }
        if (!empty($ownerobj->user)) {
            return 'owner = ' . (int)$ownerobj->user;
        }
        return '';
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
     * @param StdClass $ownedby     Only return views owned by this owner (user, group, institution)
     * @param StdClass $copyableby  Only return views copyable by this owner (user, group, institution)
     * @param integer  $limit
     * @param integer  $offset
     * @param bool     $extra       Return full set of properties on each view including an artefact list
     *
     */
    public static function view_search($query=null, $ownerquery=null, $ownedby=null, $copyableby=null, $limit=null, $offset=0, $extra=true) {
        global $USER;
        $admin = $USER->get('admin');
        $loggedin = $USER->is_logged_in();
        $viewerid = $USER->get('id');

        $where = "
            WHERE v.type != 'profile'";

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
                AND (v.stopdate IS NULL OR v.stopdate < current_timestamp)
                AND va.accesstype = 'public'
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)";
        }
        else {
            $from = '
            FROM {view} v
            LEFT OUTER JOIN (
                SELECT
                    gtr.edit_views, gm.group AS groupid
                FROM {group} g
                INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ?)
                INNER JOIN {grouptype_roles} gtr ON (g.grouptype = gtr.grouptype AND gtr.role = gm.role)
            ) AS vg ON (vg.groupid = v.group)
            LEFT OUTER JOIN {view_access} va ON (
                va.view = v.id
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
            )
            LEFT OUTER JOIN {usr_friend} f ON (usr1 = v.owner AND usr2 = ?)
            LEFT OUTER JOIN {view_access_usr} vau ON (
                vau.view = v.id
                AND (vau.startdate IS NULL OR vau.startdate < current_timestamp)
                AND (vau.stopdate IS NULL OR vau.stopdate > current_timestamp)
                AND vau.usr = ?
            )
            LEFT OUTER JOIN (
                SELECT
                    vag.view, vagm.member
                FROM {view_access_group} vag
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
                        AND (v.stopdate IS NULL OR v.stopdate < current_timestamp)
                        AND (va.accesstype = 'public'
                            OR va.accesstype = 'loggedin'
                            OR (va.accesstype = 'friends' AND f.usr2 = ?)
                            OR (vau.usr = ?)
                            OR (ag.member = ?)
                        )
                    )
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
        $viewdata = get_records_sql_array('
            SELECT * FROM (
                SELECT
                    v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution, v.template
                ' . $from . $where . '
                GROUP BY v.id, v.title, v.description, v.owner, v.ownerformat, v.group, v.institution, v.template
            ) a
            ORDER BY a.title, a.id ',
            $ph, $offset, $limit
        );

        if ($viewdata) {
            if ($extra) {
                View::get_extra_view_info($viewdata);
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
            throw new AccessDeniedException();
        }
        $from = '
            FROM {view} v
            INNER JOIN {view_access_group} a ON (a.view = v.id)
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
            View::get_extra_view_info($viewdata);
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
    public static function get_submitted_views($groupid) {
        $viewdata = get_records_sql_assoc('
            SELECT id, title, description, owner, ownerformat
            FROM {view}
            WHERE submittedto = ?
            ORDER BY title, id',
            array($groupid)
        );

        if ($viewdata) {
            View::get_extra_view_info($viewdata);
            return array_values($viewdata);
        }
        return false;
    }


    public static function get_extra_view_info(&$viewdata) {
        if ($viewdata) {
            // Get view owner details for display
            $owners = array();
            $groups = array();
            $institutions = array();
            foreach ($viewdata as $v) {
                if ($v->owner && !isset($owners[$v->owner])) {
                    $owners[$v->owner] = $v->owner;
                } else if ($v->group && !isset($groups[$v->group])) {
                    $groups[$v->group] = $v->group;
                } else if ($v->institution && !isset($institutions[$v->institution])) {
                    $institutions[$v->institution] = $v->institution;
                }
            }
            $artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title, a.artefacttype, t.plugin
                FROM {view_artefact} va
                INNER JOIN {artefact} a ON va.artefact = a.id
                INNER JOIN {artefact_installed_type} t ON a.artefacttype = t.name
                WHERE va.view IN (' . join(',', array_keys($viewdata)) . ')
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
            if (!empty($owners)) {
                $owners = get_records_select_assoc('usr', 'id IN (' . join(',', $owners) . ')', null, '', 
                                                   'id,username,firstname,lastname,preferredname,admin,staff,studentid');
            }
            if (!empty($groups)) {
                $groups = get_records_select_assoc('group', 'id IN (' . join(',', $groups) . ')', null, '', 'id,name');
            }
            if (!empty($institutions)) {
                $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
                $institutions['mahara']->displayname = get_config('sitename');
            }
            foreach ($viewdata as &$v) {
                $v->shortdescription = clean_html(str_shorten(str_replace('<br />', ' ', $v->description), 100, true));
                if ($v->owner) {
                    $v->sharedby = View::owner_name($v->ownerformat, $owners[$v->owner]);
                } else if ($v->group) {
                    $v->sharedby = $groups[$v->group]->name;
                } else if ($v->institution) {
                    $v->sharedby = $institutions[$v->institution]->displayname;
                }
                $v = (array)$v;
            }
        }
    }

    public static function set_nav($group, $institution, $profile=false) {
        if ($group) {
            define('MENUITEM', 'groups/views');
            define('GROUP', $group);
        }
        else if ($institution) {
            define('INSTITUTIONALADMIN', 1);
            define('MENUITEM', $institution == 'mahara' ? 'configsite/siteviews' : 'manageinstitutions/institutionviews');
        }
        else if ($profile) {
            define('MENUITEM', 'profile/editprofilepage');
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
        $blocks = get_records_array('block_instance', 'view', $template->get('id'));
        $numcopied = array('blocks' => 0, 'artefacts' => 0);
        if ($blocks) {
            $newowner = $this->ownership();
            $oldowner = $template->ownership();
            $this->set('copyconfig', (object) array(
                'ownertype' => $newowner['type'],
                'ownerid'   => $newowner['id'],
                'sameowner' => $newowner['type'] == $oldowner['type'] && $newowner['id'] == $oldowner['id'],
            ));
            $artefactcopies = array(); // Correspondence between original artefact ids and id of the copy
            foreach ($blocks as $b) {
                safe_require('blocktype', $b->blocktype);
                $oldblock = new BlockInstance($b->id, $b);
                if ($oldblock->copy($this, $artefactcopies)) {
                    $numcopied['blocks']++;
                }
            }
            // Go back and fix up the parents of the new artefacts so
            // they also point to new artefacts.
            // Create parent folder for files that have no parent
            if ($artefactcopies) {
                safe_require('artefact', 'file');
                $viewfilesfolder = ArtefactTypeFolder::get_folder_id(get_string('viewfilesdirname', 'view'), get_string('viewfilesdirdesc', 'view'),
                                                                     null, true, $this->get('owner'), $this->get('group'), $this->get('institution'));
                $foldername = $template->get('id');
                $existing = get_column_sql("
                    SELECT title
                    FROM {artefact}
                    WHERE parent = ? AND title LIKE ? || '%'", array($viewfilesfolder, $foldername));
                $sep = '';
                $ext = '';
                if ($existing) {
                    while (in_array($foldername . $sep . $ext, $existing)) {
                        $sep = '-';
                        $ext++;
                    }
                }
                $data = (object) array(
                    'title'       => $foldername . $sep . $ext,
                    'description' => get_string('filescopiedfromviewtemplate', 'view', $template->get('title')),
                    'owner'       => $this->get('owner'),
                    'group'       => $this->get('group'),
                    'institution' => $this->get('institution'),
                    'parent'      => $viewfilesfolder,
                );
                $folder = new ArtefactTypeFolder(0, $data);
                $folder->commit();
                foreach ($artefactcopies as $c) {
                    $a = artefact_instance_from_id($c->newid);
                    if (isset($artefactcopies[$c->oldparent])) {
                        $a->set('parent', $artefactcopies[$c->oldparent]->newid);
                        $a->commit();
                    }
                    else if (in_array($a->get('artefacttype'), array_diff(PluginArtefactFile::get_artefact_types(), array('profileicon')))) {
                        $a->set('parent', $folder->get('id'));
                        $a->commit();
                    }
                }
            }
            $numcopied['artefacts'] = count($artefactcopies);
        }
        return $numcopied;
    }

    public static function new_title($user, $group, $institution) {
        $title = get_string('Untitled', 'view');
        $taken = get_column_sql('
            SELECT title
            FROM {view}
            WHERE ' . self::owner_sql((object) array('user' => $user, 'group' => $group, 'institution' => $institution)) . "
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

}


function create_view_form($group=null, $institution=null, $template=null) {
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
    global $USER, $SESSION;

    $group = isset($values['group']) ? (int) $values['group'] : null;
    $institution = isset($values['institution']) ? $values['institution'] : null;
    $templateid = isset($values['usetemplate']) ? (int) $values['usetemplate'] : null;

    $owner = ($group || $institution) ? null : $USER->get('id');

    if ($group && !group_user_can_edit_views($group)
        || $institution && !$USER->can_edit_institution($institution)) {
        throw new AccessDeniedException();
    }

    // Create a new view
    $data = (object) array(
        'numcolumns'  => 3,
        'template'    => 0,
        'type'        => 'portfolio',
        'group'       => $group,
        'institution' => $institution,
        'owner'       => $owner,
        'title'       => View::new_title($owner, $group, $institution),
    );
    $view = new View(0, $data);
    $view->commit();  // copy_contents call below needs a view id

    if ($templateid) {
        $template = new View($templateid);
        if (!$template->get('deleted') && ($template->get('template') && can_view_view($templateid)) || $USER->can_edit_view($template)) {
            $view->set('dirty', true);
            $copystatus = $view->copy_contents($template);
            $SESSION->add_ok_msg(get_string('copiedblocksandartefactsfromtemplate', 'view', $copystatus['blocks'], $copystatus['artefacts'], $template->get('title')));
        }
    }
    if ($group) {
        // By default, group views should be visible to the group
        $view->set_access(array(array(
            'type'      => 'group',
            'id'        => $group,
            'startdate' => null,
            'stopdate'  => null,
            'role'      => null
        )));
    }
    $view->commit();

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


function add_feedback_form($attachments=false) {
    global $USER;
    $form = array(
        'name'            => 'add_feedback_form',
        'method'          => 'post',
        'plugintype'      => 'core',
        'pluginname'      => 'view',
        'jsform'          => true,
        'elements'        => array(),
        'jssuccesscallback' => 'addFeedbackSuccess',
    );
    if (!$USER->is_logged_in()) {
        $form['elements']['authorname'] = array(
            'type'  => 'text',
            'title' => get_string('name'),
        );
    }
    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'title' => get_string('message'),
        'rows'  => 5,
        'cols'  => 80,
    );
    $form['elements']['ispublic'] = array(
        'type'  => 'checkbox',
        'title' => get_string('makepublic', 'view'),
    );
    if ($attachments) {
        $form['elements']['attachment'] = array(
            'type'  => 'file',
            'title' => get_string('attachfile', 'view'),
        );
    }
    $form['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'value' => array(get_string('placefeedback', 'view'), get_string('cancel')),
    );
    return $form;
}

function add_feedback_form_validate(Pieform $form, $values) {
    global $USER, $view;
    if (!$USER->get('id')) {
        if (empty($values['authorname'])) {
            $form->set_error(get_string('pleaseenteryourname', 'view'));
        }
        $token = get_cookie('viewaccess:'.$view->get('id'));
        if (!$token || get_view_from_token($token) != $view->get('id')) {
            $form->set_error(get_string('placefeedbacknotallowed', 'view'));
        }
    }
}

function add_feedback_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;
    $data = new StdClass;
    $data->view = $view->get('id');
    if ($artefact) {
        $data->artefact = $artefact->get('id');
        $table = 'artefact_feedback';
    }
    else {
        $table = 'view_feedback';
    }
    $data->message = $values['message'];
    $data->public = (int) $values['ispublic'];
    $data->author = $USER->get('id');
    if (!$data->author) {
        unset($data->author);
        $data->authorname = $values['authorname'];
    }
    $data->ctime = db_format_timestamp(time());
    insert_record($table, $data, 'id', true);
    require_once('activity.php');
    activity_occurred('feedback', $data);
    if ($artefact) {
        $goto = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefact->get('id') . '&view='.$view->get('id');
    }
    else {
        $goto = get_config('wwwroot') . 'view/view.php?id='.$view->get('id');
    }
    $form->reply(PIEFORM_OK, array(
        'message' => get_string('feedbacksubmitted', 'view'),
        'goto' => $goto,
    ));
}

function add_feedback_form_cancel_submit(Pieform $form) {
    $form->reply(PIEFORM_OK, array(
        'goto' => '',
    ));
}

function objection_form() {
    $form = array(
        'name'            => 'objection_form',
        'method'          => 'post',
        'plugintype'      => 'core',
        'pluginname'      => 'view',
        'jsform'          => true,
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
    );
    return $form;
}

function objection_form_submit(Pieform $form, $values) {
    global $USER, $view, $artefact;
    require_once('activity.php');

    $data = new StdClass;
    $data->view       = $view->get('id');
    $data->message    = $values['message'];
    $data->reporter   = $USER->get('id');
    if ($artefact) {
        $data->artefact = $artefact->get('id');
    }

    activity_occurred('objectionable', $data);
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

function objection_form_cancel_submit(Pieform $form) {
    $form->reply(PIEFORM_OK, array(
        'goto' => '',
    ));
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

?>
