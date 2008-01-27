<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class View {

    private $dirty;
    private $deleted;
    private $id;
    private $owner;
    private $ownerformat;
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
    private $numcolumns;
    private $layout;
    private $columns;
    private $dirtycolumns; // for when we change stuff
    private $tags;

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
            $this->id = insert_record('view', $fordb, 'id', true);
        }
        else {
            update_record('view', $fordb, 'id');
        }

        delete_records('view_tag', 'view', $this->get('id'));
        foreach ($this->get_tags() as $tag) {
            insert_record('view_tag', (object)array( 'view' => $this->get('id'), 'tag' => $tag));
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

    
    public function delete() {
        delete_records('artefact_feedback','view',$this->id);
        delete_records('view_feedback','view',$this->id);
        delete_records('view_artefact','view',$this->id);
        delete_records('view_access','view',$this->id);
        delete_records('view_access_group','view',$this->id);
        delete_records('view_access_usr','view',$this->id);
        delete_records('view_tag','view',$this->id);
        delete_records('usr_watchlist_view','view',$this->id);
        delete_records('block_instance', 'view', $this->id);
        delete_records('view','id',$this->id);
        $this->deleted = true;
    }

    public function get_access() {

        $data = get_records_sql_array('SELECT va.accesstype AS type, va.startdate, va.stopdate
            FROM {view_access} va
            LEFT JOIN {view} v ON (va.view = v.id)
            WHERE v.id = ?
            ORDER BY va.accesstype', array($this->id));
        if (!$data) {
            $data = array();
        }
        foreach ($data as &$item) {
            $item = (array)$item;
        }

        // Get access for users and groups
        $extradata = get_records_sql_array("
            SELECT 'user' AS type, usr AS id, 0 AS tutoronly, startdate, stopdate
                FROM {view_access_usr}
                WHERE view = ?
        UNION
            SELECT 'group', \"group\", tutoronly, startdate, stopdate FROM {view_access_group}
                INNER JOIN {group} g ON (\"group\" = g.id AND g.deleted = ?)
                WHERE view = ?", array($this->id, 0, $this->id));
        if ($extradata) {
            foreach ($extradata as &$extraitem) {
                $extraitem = (array)$extraitem;
                $extraitem['tutoronly'] = (int)$extraitem['tutoronly'];
            }
            $data = array_merge($data, $extradata);
        }
        return $data;
    }

    public function set_access($accessdata) {
        global $USER;

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
                        $accessrecord->tutoronly = $item['tutoronly'];
                        insert_record('view_access_group', $accessrecord);
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
                  'subject' => get_string_from_language($ownerlang, 'viewreleasedsubject'),
                  'message' => get_string_from_language($ownerlang, 'viewreleasedmessage', 'mahara', 
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
            GROUP BY bc.name
            ORDER BY bc.name', array());
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
    public static function build_blocktype_list($category, $javascript=false) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $blocktypes = PluginBlockType::get_blocktypes_for_category($category);

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
        if ($USER->get('id') != $this->get('owner')) {
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
                'subject' => get_string('newwatchlistmessagesubject', 'activity'),
                'message' => get_string('newwatchlistmessageview', 'activity', $this->get('title')),
            );
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
        $data = $this->get_column_datastructure($column);

        if ($editing) {
            $renderfunction = 'render_editing';
        }
        else {
            $renderfunction = 'render_viewing';
        }
        
        $blockcontent = '';
        foreach($data['blockinstances'] as $blockinstance) {
            $result = $blockinstance->$renderfunction($blockinstance->get('id') == $this->blockinstance_currently_being_configured);
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
            return $bi->render_editing(call_static_method(generate_class_name('blocktype', $values['blocktype']), 'has_instance_config'));
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
    public static function build_artefactchooser_data($data) {
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

        $select = 'owner = ' . $USER->get('id');
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
        $artefacts = get_records_select_array('artefact', $select, null, $sortorder, '*', $offset, $limit);
        $totalartefacts = count_records_select('artefact', $select);

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
                'value'     => $value,
                'blocktype' => $data['blocktype'],
            ),
        ));

        return array($result, $pagination, $totalartefacts, $offset);
    }
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
