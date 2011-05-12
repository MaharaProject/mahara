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
 * @subpackage artefact
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Base artefact plugin class
 * @abstract
 */
abstract class PluginArtefact extends Plugin {

    /** 
     * This function returns a list of classnames 
     * of artefact types this plugin provides.
     * @abstract
     * @return array
     */
    public static abstract function get_artefact_types();

    
    /**
    * This function returns a list of classnames
    * of block types this plugin provides
    * they must match directories inside artefact/$name/blocktype
    * @abstract
    * @return array
    */
    public static abstract function get_block_types();


    /**
     * This function returns the name of the plugin.
     * @abstract
     * @return string
     */
    public static abstract function get_plugin_name();


    /**
     * This function returns an array of menu items
     * to be displayed
     * Each item should be a StdClass object containing -
     * - name language pack key
     * - url relative to wwwroot
     * @return array
     */
    public static function menu_items() {
        return array();
    }


    /**
     * This function returns an array of menu items to be displayed
     * on a group page when viewed by group members.
     * Each item should be a StdClass object containing -
     * - title language pack key
     * - url relative to wwwroot
     * @return array
     */
    public static function group_tabs($groupid) {
        return array();
    }


    /**
     * Returns any artefacts that are not inside a view
     * but which need to be exported along with it.
     * @param array $viewids
     * @return array of artefact ids
     */
    public static function view_export_extra_artefacts($viewids) {
        return array();
    }


    /**
     * When filtering searches, some artefact types are classified the same way
     * even when they come from different artefact plugins.  This function allows
     * artefact plugins to declare which search filter content type each of their 
     * artefact types belong to.
     * @return array of artefacttype => array of filter content types
     */
    public static function get_artefact_type_content_types() {
        return array();
    }
}

/** 
 * Base artefact type class
 * @abstract
 */
abstract class ArtefactType {
    
    protected $dirty;
    protected $parentdirty;
    protected $deleted = false;
    protected $id;
    protected $artefacttype;
    protected $owner;
    protected $container;
    protected $parent;
    protected $ctime;
    protected $mtime;
    protected $atime;
    protected $locked;
    protected $title;
    protected $description;
    protected $note;
    protected $tags = array();
    protected $institution;
    protected $group;
    protected $author;
    protected $authorname;
    protected $allowcomments;
    protected $approvecomments;
    protected $rolepermissions;
    protected $mtimemanuallyset;

    protected $viewsinstances;
    protected $viewsmetadata;
    protected $childreninstances;
    protected $childrenmetadata;
    protected $parentinstance;
    protected $parentmetadata;

    /** 
     * Constructer. 
     * If an id is supplied, will query the database
     * to build up the basic information about the object.
     * If an id is not supplied, we just create an empty
     * artefact, ready to be filled up
     * @param int $id artefact.id
     */
    public function __construct($id=0, $data=null) {
        if (!empty($id)) {
            if (empty($data)) {
                if (!$data = get_record('artefact','id',$id)) {
                    throw new ArtefactNotFoundException(get_string('artefactnotfound', 'error', $id));
                }
            }
            $this->id = $id;
        }
        else {
            $this->ctime = $this->mtime = time();
            $this->dirty = true;
        }
        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                if (in_array($field, array('atime', 'ctime', 'mtime'))) {
                    $value = strtotime($value);
                } 
                if ($field == 'tags' && !is_array($value)) {
                    $value = preg_split("/\s*,\s*/", trim($value));
                }
                $this->{$field} = $value;
            }
        }

        $this->artefacttype = $this->get_artefact_type();
        if (!empty($data->artefacttype)) {
            if ($this->artefacttype != $data->artefacttype) {
                throw new SystemException(get_string('artefacttypemismatch', 'error', $data->artefacttype, $this->artefacttype));
            }
        }

        // load tags
        if ($this->id) {
            $tags = get_column('artefact_tag', 'tag', 'artefact', $this->id);
            if (is_array($tags)) {
                $this->tags = $tags;
            }
        }

        // load group permissions
        if ($this->group && !is_array($this->rolepermissions)) {
            $this->load_rolepermissions();
        }

        $this->atime = time();
    }

    public function get_views_instances() {
        // @todo test this
        if (!isset($this->viewsinstances)) {
            $this->viewsinstances = false;
            if ($views = $this->get_views_metadata()) {
                $this->viewsinstances = array();
                foreach ($views as $view) {
                    $this->viewsinstances[] = new View($view->id, $view);
                }
            }
        }
        return $this->viewsinstances;
    }

    public function get_views_metadata() {
        if (!isset($this->viewsmetadata)) {
            $this->viewsmetadata = get_records_array('view_artefact', 'artefact', $this->id);
        }
        return $this->viewsmetadata;
    }

    public function count_children() {
        return count_records('artefact', 'parent', $this->get('id'));
    }

    public function has_children() {
        if ($this->get_children_metadata()) {
            return true;
        }
        return false;
    }

    public function get_plugin_name() {
        return get_field('artefact_installed_type', 'plugin', 'name', $this->get('artefacttype'));
    }

    /** 
     * This function returns the instances 
     * of all children of this artefact
     * If you just want the basic info, 
     * use {@link get_children_metadata} instead.
     * 
     * @return array of instances.
     */

    public function get_children_instances() {
        if (!isset($this->childreninstances)) {
            $this->childreninstances = false;
            if ($children = $this->get_children_metadata()) {
                $this->childreninstances = array();
                foreach ($children as $child) {
                    $classname = generate_artefact_class_name($child->artefacttype);
                    $instance = new $classname($child->id, $child);
                    $this->childreninstances[] = $instance;
                }
            }
        }
        return $this->childreninstances;
    }

    /**
     * This function returns the db rows 
     * from the artefact table that have this 
     * artefact as the parent.
     * If you want instances, use {@link get_children_instances}
     * but bear in mind this will have a performance impact.
     * 
     * @return array
     */
    public function get_children_metadata() {
        if (!isset($this->childrenmetadata)) {
            $this->childrenmetadata = get_records_array('artefact', 'parent', $this->id);
        }
        return $this->childrenmetadata;
    }

    /**
     * This function returns the instance relating to the parent
     * of this object, or false if there isn't one.
     * If you just want basic information about it,
     * use {@link get_parent_metadata} instead.
     *
     * @return ArtefactType
     */
    public function get_parent_instance() {
        if (!isset($this->parentinstance)) {
            $this->parentinstance = false;
            if ($parent = $this->get_parent_metadata()) {
                $classname = generate_artefact_class_name($parent->artefacttype);
                $this->parentinstance = new $classname($parent->id, $parent);
            }
        }
        return $this->parentinstance;
    }

    /** 
     * This function returns the db row 
     * (if there is one) of the parent
     * artefact for this instance.
     * If you want the instance, use 
     * {@link get_parent_instance} instead.
     * 
     * @return object - db row
     */
    public function get_parent_metadata() {
        if (empty($this->parent)) {
            return false;
        }
        return get_record('artefact','id',$this->parent);
    }

    /**
     * Returns how much quota this artefact has used.
     *
     * It should try to not instantiate the artefact, because it is normally 
     * called as part of an expensive cron job
     *
     * @return int Size in bytes that the artefact is taking up in quota
     */
    public static function get_quota_usage($artefact) {
        return 0;
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
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
            if ($field == 'parent') {
                $this->parentdirty = true;
            }
            if ($field == 'mtime') {
                $this->mtimemanuallyset = true;
            }
            else if (!$this->mtimemanuallyset) {
                $this->mtime = time();
            }
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }
    
    /**
     * Artefact destructor. Calls commit and marks the
     * artefact cache as dirty if necessary.
     *
     * A special case is when the object has just been deleted.  In this case,
     * we do nothing.
     */
    public function __destruct() {
        if ($this->deleted) {
            return;
        }
      
        if (!empty($this->dirty) && !defined('MAHARA_CRASHING')) {
            $this->commit();
        }
    }
    
    /** 
     * This method updates the contents of the artefact table only.  If your
     * artefact has extra information in other tables, you need to override
     * this method, and call parent::commit() in your own function.
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        if (empty($this->author) && empty($this->authorname)) {
            $this->set_author();
        }

        db_begin();

        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime', 'atime')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }
        if (empty($this->id)) {
            $this->id = insert_record('artefact', $fordb, 'id', true);
            if ($this->can_be_logged()) {
                $this->log('created');
            }
            if (!empty($this->parent)) {
                $this->parentdirty = true;
            }
        }
        else {
            if ($this->can_be_logged()) {
                $this->log('edited');
            }
            update_record('artefact', $fordb, 'id');
        }

        if (!empty($this->group)) {
            $this->save_rolepermissions();
        }

        delete_records('artefact_tag', 'artefact', $this->id);
        if (is_array($this->tags)) {
            foreach (array_unique($this->tags) as $tag) {
                if (empty($tag)) {
                    continue;
                }
                insert_record(
                    'artefact_tag',
                    (object) array(
                        'artefact' => $this->id,
                        'tag'      => $tag,
                    )
                );
            }
        }

        artefact_watchlist_notification(array($this->id));

        handle_event('saveartefact', $this);

        if (!empty($this->parentdirty)) {
            if ($this->parent) {
                // Make sure we have a record for the new parent
                delete_records('artefact_parent_cache', 'artefact', $this->id, 'parent', $this->parent);
                insert_record('artefact_parent_cache', (object)array(
                    'artefact' => $this->id,
                    'parent'   => $this->parent,
                    'dirty'    => 0
                ));
                // Set anything relating to this artefact as dirty
                set_field_select('artefact_parent_cache', 'dirty', 1,
                                 'artefact = ? OR parent = ?', array($this->id, $this->id));
            }
            else {
                // No parent - no need for any records in the apc then
                delete_records('artefact_parent_cache', 'artefact', $this->id);
            }
        }
        $this->dirty = false;
        $this->deleted = false;
        $this->parentdirty = false;

        db_commit();
    }

    /** 
     * This function provides basic delete functionality.  It gets rid of the
     * artefact's row in the artefact table, and the tables that reference the
     * artefact table.  It also recursively deletes child artefacts.
     *
     * If your artefact has additional data in another table, you should
     * override this function, but you MUST call parent::delete() after you
     * have done your own thing.
     */
    public function delete() {
        if (empty($this->id)) {
            $this->dirty = false;
            return;
        }
      
        db_begin();

        // Call delete() on comments (if there are any)
        safe_require('artefact', 'comment');
        ArtefactTypeComment::delete_comments_onartefacts(array($this->id));

        // Call delete() on children (if there are any)
        if ($children = $this->get_children_instances()) {
            foreach ($children as $child) {
                $child->delete();
            }
        }

        artefact_watchlist_notification(array($this->id));

        self::_delete_dbrecords(array($this->id));

        if ($this->can_be_logged()) {
            $this->log('deleted');
        }
      
        handle_event('deleteartefact', $this);

        // Set flags.
        $this->dirty = false;
        $this->parentdirty = true;
        $this->deleted = true;

        db_commit();
    }

    /**
     * Does a bulk_delete on a list of artefacts, grouping artefacts of
     * the same type.
     *
     * Currently only tested for folders and their contents.
     */
    public static function delete_by_artefacttype($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        db_begin();

        artefact_watchlist_notification($artefactids);

        // Delete comments first
        safe_require('artefact', 'comment');
        ArtefactTypeComment::delete_comments_onartefacts($artefactids);

        $records = get_records_select_assoc(
            'artefact',
            'id IN (' . join(',', array_map('intval', $artefactids)) . ')',
            null, 'artefacttype', 'id,parent,artefacttype,container'
        );

        $containers = array();
        $leaves = array();
        foreach ($records as $r) {
            if ($r->container) {
                $containers[$r->artefacttype][] = (int)$r->id;
            }
            else {
                $leaves[$r->artefacttype][] = $r->id;
            }
        }

        // Delete non-containers grouped by artefacttype
        foreach ($leaves as $artefacttype => $ids) {
            $classname = generate_artefact_class_name($artefacttype);
            call_static_method($classname, 'bulk_delete', $ids);
        }

        // Delete containers grouped by artefacttype
        foreach ($containers as $artefacttype => $ids) {
            $classname = generate_artefact_class_name($artefacttype);
            if (is_mysql()) {
                set_field_select('artefact', 'parent', null, 'id IN (' . join(',', $ids) . ')', array());
            }
            call_static_method($classname, 'bulk_delete', $ids);
        }

        handle_event('deleteartefacts', $artefactids);

        db_commit();
    }

    /**
     * Faster delete for multiple artefacts.
     *
     * Should only be called on artefacts with no children, after
     * additional data in other tables has already been deleted.
     */
    public static function bulk_delete($artefactids, $log=false) {
        db_begin();

        self::_delete_dbrecords($artefactids);

        // Logging must be triggered by the caller because it's
        // slow to go through each artefact and ask it if it should
        // be logged.
        if ($log) {
            global $USER;
            $entry = (object) array(
                'usr'      => $USER->get('id'),
                'time'     => db_format_timestamp(time()),
                'deleted'  => 1,
            );
            foreach ($artefactids as $id) {
                $entry->artefact = $id;
                insert_record('artefact_log', $entry);
            }
        }

        db_commit();
    }


    private static function _delete_dbrecords($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = '(' . join(',', array_map('intval', $artefactids)) . ')';

        db_begin();

        // Detach any files from this artefact
        delete_records_select('artefact_attachment', "artefact IN $idstr");

        // Delete any references to these artefacts from non-artefact places.
        delete_records_select('artefact_parent_cache', "artefact IN $idstr");

        // The artefacts should have no 'real' children at this point, but they
        // could still be in the artefact_parent_cache as parents if they had
        // attachments, or if any of their children had attachments.
        delete_records_select('artefact_parent_cache', "parent IN $idstr");

        // Make sure that the artefacts are removed from any view blockinstances
        if ($records = get_records_sql_array("
            SELECT va.block, va.artefact, bi.configdata
            FROM {view_artefact} va JOIN {block_instance} bi ON va.block = bi.id
            WHERE va.artefact IN $idstr", array())) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            BlockInstance::bulk_delete_artefacts($records);
        }
        delete_records_select('view_artefact', "artefact IN $idstr");
        delete_records_select('artefact_tag', "artefact IN $idstr");
        delete_records_select('artefact_access_role', "artefact IN $idstr");
        delete_records_select('artefact_access_usr', "artefact IN $idstr");

        delete_records_select('artefact', "id IN $idstr");

        db_commit();
    }


    /**
     * Initialise artefact author to either the artefact owner, the
     * logged-in user, or the system user.
     */
    private function set_author() {
        global $USER;
        if (isset($this->owner)) {
            $this->author = $this->owner;
        }
        else {
            $this->author = $USER->get('id');
        }
    }

    /**
    * this function provides the way to link to viewing very deeply nested artefacts
    * within a view
    *
    * @todo not sure the comment here is appropriate
    */
    public function add_to_render_path(&$options) {
        if (empty($options['path'])) {
            $options['path'] = $this->get('id');
        }
        else {
            $options['path'] .= ',' . $this->get('id');
        }
    }


    /**
     * By default users are notified of all feedback on artefacts
     * which they own.  Artefact types which want to allow this
     * notification to be turned off should redefine this function.
     */
    public function feedback_notify_owner() {
        return true;
    }


    /**
     * A dummy method, giving graceful output, if this method is not implemented in the relevant child class
     */
    public function render_self($options) {
        $smarty = smarty();
        $smarty->assign('viewtitle', $this->get('title'));
        $smarty->assign('viewdescription', $this->get('description'));

        return array(
            'html' => $smarty->fetch('view/viewcontent.tpl'),
            'javascript'=>''
        );
    }


    /**
     * Returns a URL for an icon for the appropriate artefact
     *
     * @param array $options Options for the artefact. The array MUST have the 
     *                       'id' key, representing the ID of the artefact for 
     *                       which the icon is being generated. Other keys 
     *                       include 'size' for a [width]x[height] version of 
     *                       the icon, as opposed to the default 20x20, and 
     *                       'view' for the id of the view in which the icon is 
     *                       being displayed.
     * @abstract 
     * @return string URL for the icon
     */
    public static abstract function get_icon($options=null);
    

    // ******************** STATIC FUNCTIONS ******************** //

    public static function get_instances_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    public static function get_metadata_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    /**
     * whether a user will have exactly 0 or 1 of this artefact type
     * @abstract
     */
    public static abstract function is_singular();

    /**
     * Returns a list of key => value pairs where the key is either '_default'
     * or a langauge string, and value is a URL linking to that behaviour for
     * this artefact type
     * 
     * @param integer This is the ID of the artefact being linked to
     */
    public static abstract function get_links($id);

    // @TODO maybe uncomment this later and implement it everywhere
    // when we know a bit more about what blocks we want.
    //public abstract function render_self($options);


    /**
    * Returns the printable name of this artefact
    * (used in lists and such)
    */
    public function get_name() {
        return $this->get('title');
    }

    /**
    * Should the artefact be linked to from the listing on my views?
    */
    public function in_view_list() {
        return true;
    }

    /**
    * Returns a short name for the artefact to be used in a list of artefacts in a view 
    */
    public function display_title($maxlen=null) {
        if ($maxlen) {
            return str_shorten_text($this->get('title'), $maxlen, true);
        }
        return $this->get('title');
    }

    public function display_owner() {
        if ($owner = $this->get('owner')) {
            return display_name($owner);
        }
        if ($group = $this->get('group')) {
            return get_field('group', 'name', 'id', $group);
        }
        if ($institution = $this->get('institution')) {
            if ($institution == 'mahara') {
                return get_config('sitename');
            }
            return get_field('institution', 'displayname', 'name', $institution);
        }
        return null;
    }


    // ******************** HELPER FUNCTIONS ******************** //

    protected function get_artefact_type() {
        $classname = get_class($this);
        
        $type = strtolower(substr($classname, strlen('ArtefactType')));

        if (!artefact_type_installed($type)) {
            throw new InvalidArgumentException("Classname $classname not a valid artefact type");
        }

        return $type;
    }

    public function to_stdclass() {
       return (object)get_object_vars($this); 
    }

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }

    public static function collapse_config() {
        return false;
    }

    private function save_rolepermissions() {
        if (!$this->group) {
            return;
        }
        require_once(get_config('libroot') . 'group.php');
        if (!isset($this->rolepermissions)) {
            $this->rolepermissions = group_get_default_artefact_permissions($this->group);
        }
        $id = $this->get('id');
        db_begin();
        delete_records('artefact_access_role', 'artefact', $id);
        foreach ($this->rolepermissions as $role => $permissions) {
            insert_record('artefact_access_role', (object) array(
                'artefact'      => $id,
                'role'          => $role,
                'can_view'      => (int) !empty($permissions->view),
                'can_edit'      => (int) !empty($permissions->edit),
                'can_republish' => (int) !empty($permissions->republish),
            ));
        }
        db_commit();
    }

    private function load_rolepermissions() {
        if (!$this->group) {
            return;
        }
        $records = get_records_array('artefact_access_role', 'artefact', $this->get('id'));
        if ($records) {
            $this->rolepermissions = array();
            foreach ($records as $r) {
                $this->rolepermissions[$r->role] = (object) array(
                    'view' => (bool) $r->can_view,
                    'edit' => (bool) $r->can_edit,
                    'republish' => (bool) $r->can_republish,
                );
            }
        }
        else {
            $this->rolepermissions = group_get_default_artefact_permissions($this->group);
        }
    }

    public function copy_data() {
        $ignore = array(
            'dirty' => 1,
            'parentdirty' => 1,
            'deleted' => 1,
            'id' => 1,
            'locked' => 1,
            'rolepermissions' => 1,
            'viewsinstances' => 1,
            'viewsmetadata' => 1,
            'childreninstances' => 1,
            'childrenmetadata' => 1,
            'parentinstance' => 1,
            'parentmetadata' => 1
        );
        $data = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            if (in_array($k, array('atime', 'ctime', 'mtime'))) {
                $data->$k = db_format_timestamp($v);
            }
            else if (!isset($ignore[$k])) {
                $data->$k = $v;
            }
        }
        return $data;
    }

    public function copy_extra($new) {
    }

    public function copy_for_new_owner($user, $group, $institution) {
        $data = $this->copy_data();
        $data->owner = $user;
        $data->group = $group;
        $data->institution = $institution;
        $data->parent = null;
        $classname = generate_artefact_class_name($data->artefacttype);
        safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $data->artefacttype));
        $copy = new $classname(0, (object) $data);
        $this->copy_extra($copy);
        $copy->commit();
        return $copy->get('id');
    }

    /**
     * Called after a view has been copied to rewrite all artefact
     * references in the new artefact (which still point to the
     * original artefacts) so that they also point to new artefacts
     * that were copied during the view copy.
     *
     * @param View $view The newly copied view
     * @param View $template The old view
     * @param array $artefactcopies The mapping between old artefact ids and new ones (created in blockinstance copy)
     * @param integer $oldid id of the artefact this artefact was copied from
     */
    public function update_artefact_references(&$view, &$template, &$artefactcopies, $oldid) {
        $copyinfo = $artefactcopies[$oldid];
        if (isset($artefactcopies[$copyinfo->oldparent])) {
            $this->set('parent', $artefactcopies[$copyinfo->oldparent]->newid);
        }
        else {
            $this->set('parent', $this->default_parent_for_copy($view, $template, array_map(create_function('$a', 'return $a->newid;'), $artefactcopies)));
        }
    }

    /**
     * Returns the ID of the artefact that should be the parent for copied 
     * artefacts - e.g. the folder that files should be placed in.
     *
     * The $artefactstoignore is used to work around bug #3106
     *
     * @param View $view The new view being created by the copy
     * @param View $template The view being copied from
     * @param array $artefacttoignore A list of artefact IDs to ignore. In 
     *                                particular, it's a list of artefact IDs 
     *                                that have been created by a view being 
     *                                copied. This is so we don't accidentally 
     *                                try to use a new artefact as the parent 
     *                                for all of the artefacts, else we can get 
     *                                into a nasty infinite loop (e.g. when a 
     *                                folder called 'viewfiles' is being 
     *                                copied).
     */
    public function default_parent_for_copy(&$view, &$template, $artefactstoignore) {
        return null;
    }

    public function can_be_logged() {
        return false;
    }

    public function log($action) {
        global $USER;
        $entry = (object) array(
            'artefact' => $this->id,
            'usr'      => $USER->get('id'),
            'time'     => db_format_timestamp($this->mtime),
            $action    => 1,
        );
        if ($action == 'deleted') {
            insert_record('artefact_log', $entry);
            return;
        }
        $loggedfields = array('title', 'description', 'parent');
        if ($action == 'edited') {
            $old = get_record('artefact', 'id', $this->id);
            foreach ($loggedfields as $key) {
                if ($this->$key != $old->$key) {
                    $entry->$key = $this->$key;
                    $changed = true;
                }                
            }
            if (isset($changed)) {
                insert_record('artefact_log', $entry);
            }
        }
        if ($action == 'created') {
            foreach ($loggedfields as $key) {
                $entry->$key = $this->$key;
            }
            insert_record('artefact_log', $entry);
        }
    }

    public function can_have_attachments() {
        return false;
    }

    public function count_attachments() {
        return count_records('artefact_attachment', 'artefact', $this->get('id'));
    }

    public function attachment_id_list() {
        // During view copying, attachment_id_list can get called on artefacts of any type; don't call
        // get_column here unless it might actually return something.
        if ($this->can_have_attachments()) {
            if ($list = get_column('artefact_attachment', 'attachment', 'artefact', $this->get('id'))) {
                return $list;
            }
        }
        return array();
    }

    public function attachments_from_id_list($artefactids) {
        if (empty($artefactids)) {
            return array();
        }
        // @todo: Join on artefact_file_files shouldn't happen below.
        // We could either assume all attachments are files and then
        // move all these attachment functions to the artefact file
        // plugin, or we could allow artefact plugins to add stuff
        // to this query.
        $attachments = get_records_sql_array('
            SELECT
                aa.artefact, aa.attachment, a.artefacttype, a.title, a.description, f.size
            FROM {artefact_attachment} aa
                INNER JOIN {artefact} a ON aa.attachment = a.id
                LEFT JOIN {artefact_file_files} f ON a.id = f.artefact
            WHERE aa.artefact IN (' . join(', ', array_map('intval', $artefactids)) . ')', '');
        if (!$attachments) {
            return array();
        }
        return $attachments;
    }

    public function tags_from_id_list($artefactids) {
        if (empty($artefactids)) {
            return array();
        }
        $artefactids = join(',', array_map('intval', $artefactids));
        $tags = get_records_select_array('artefact_tag', 'artefact IN (' . $artefactids . ')');
        if (!$tags) {
            return array();
        }
        return $tags;
    }

    public function get_attachments($assoc=false) {
        $list = get_records_sql_assoc('SELECT a.id, a.artefacttype, a.title, a.description 
            FROM {artefact_attachment} aa
            INNER JOIN {artefact} a ON a.id = aa.attachment
            WHERE aa.artefact = ?
            ORDER BY a.title', array($this->id));

        // load tags
        if ($list) {
            $tags = get_records_select_array('artefact_tag', 'artefact IN (' . join(',', array_keys($list)) . ')');
            if ($tags) {
                foreach ($tags as $t) {
                    $list[$t->artefact]->tags[] = $t->tag;
                }
                foreach ($list as &$attachment) {
                    if (!empty($attachment->tags)) {
                        $attachment->tags = join(', ', $attachment->tags);
                    }
                }
            }
        }
        else {
            return array();
        }

        if ($assoc) {          // Remove once tablerenderers are gone.
            return $list;
        }
        return array_values($list);
    }

    public function attach($attachmentid) {
        if (record_exists('artefact_attachment', 'artefact', $this->get('id'), 'attachment', $attachmentid)) {
            return;
        }
        if (!record_exists('artefact', 'id', $attachmentid)) {
            throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $attachmentid));
        }
        $data = new StdClass;
        $data->artefact = $this->get('id');
        $data->attachment = $attachmentid;
        insert_record('artefact_attachment', $data);

        $data = new StdClass;
        $data->artefact = $attachmentid;
        $data->parent = $this->get('id');
        $data->dirty = true;
        insert_record('artefact_parent_cache', $data);

        // Ensure the attachment is recorded as being related to the parent as well
        if ($this->get('parent')) {
            $data = new StdClass;
            $data->artefact = $attachmentid;
            $data->parent = $this->get('parent');
            $data->dirty = 0;

            $where = $data;
            unset($where->dirty);
            ensure_record_exists('artefact_parent_cache', $where, $data);
        }
    }

    public function detach($attachmentid=null) {
        if (is_null($attachmentid)) {
            execute_sql("
                DELETE FROM {artefact_parent_cache}
                WHERE parent = ?
                AND artefact IN (
                    SELECT attachment
                    FROM {artefact_attachment}
                    WHERE artefact = ?
                )", array($this->id, $this->id));
            delete_records('artefact_attachment', 'artefact', $this->id);
            return;
        }
        if (!record_exists('artefact', 'id', $attachmentid)) {
            throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $attachmentid));
        }
        delete_records('artefact_attachment', 'artefact', $this->get('id'), 'attachment', $attachmentid);
        delete_records('artefact_parent_cache', 'parent', $this->get('id'), 'artefact', $attachmentid);
        if ($this->get('parent')) {
            // Remove the record relating the attachment with the parent
            delete_records('artefact_parent_cache', 'parent', $this->get('parent'), 'artefact', $attachmentid);
        }
    }

    // Interface:
    public static function attached_id_list($attachmentid) {
        return get_column('artefact_attachment', 'artefact', 'attachment', $attachmentid);
    }

    public function exportable() {
        return true;
    }

    // Update the locked field on a user's artefacts
    // Lock anything in a submitted view, and unlock anything that isn't
    public static function update_locked($userid) {
        if (empty($userid)) {
            return;
        }
        $submitted = get_column_sql('
            SELECT a.id
            FROM {artefact} a
                JOIN {view_artefact} va ON a.id = va.artefact
                JOIN {view} v ON va.view = v.id
            WHERE a.owner = ?
                AND v.owner = ?
                AND (v.submittedgroup IS NOT NULL OR v.submittedhost IS NOT NULL)',
            array($userid, $userid)
        );
        if ($submitted) {
            $submitted = artefact_get_descendants($submitted);
            if ($attachments = get_column_sql('
                SELECT attachment FROM {artefact_attachment}
                WHERE artefact IN (' . join(',', $submitted) . ')',
                array())) {
                $submitted = array_merge($submitted, $attachments);
            }
        }
        db_begin();
        if (!empty($submitted)) {
            $idstr = '(' . join(',', $submitted) . ')';
            set_field_select('artefact', 'locked', 1, "locked = 0 AND id IN $idstr", array());
        }
        // Unlock
        $select = 'locked = 1 AND "owner" = ?';
        if (isset($idstr)) {
            $select .= " AND NOT id IN $idstr";
        }
        set_field_select('artefact', 'locked', 0, $select, array($userid));
        db_commit();
    }
}

/**
 * Given an artefact plugin name, this function will test if 
 * it's installable or not.  If not, InstallationException will be thrown.
 */
function artefact_check_plugin_sanity($pluginname) {
    $classname = generate_class_name('artefact', $pluginname);
    safe_require('artefact', $pluginname);
    if (!is_callable(array($classname, 'get_artefact_types'))) {
        throw new InstallationException(get_string('artefactpluginmethodmissing', 'error', $classname, 'get_artefact_types'));
    }
    if (!is_callable(array($classname, 'get_block_types'))) {
        throw new InstallationException(get_string('artefactpluginmethodmissing', 'error', $classname, 'get_block_types'));
    }
    $types = call_static_method($classname, 'get_artefact_types');
    foreach ($types as $type) {
        $typeclassname = generate_artefact_class_name($type);
        if (get_config('installed')) {
            if ($taken = get_record_select('artefact_installed_type', 'name = ? AND plugin != ?', 
                                           array($type, $pluginname))) {
                // Check the other plugin's code in case the duplicate type is being removed from it at the same time
                $otherclass = generate_class_name('artefact', $taken->plugin);
                safe_require('artefact', $taken->plugin);
                if (in_array($type, call_static_method($otherclass, 'get_artefact_types'))) {
                    throw new InstallationException(get_string('artefacttypenametaken', 'error', $type, $taken->plugin));
                }
            }
        }
        if (!class_exists($typeclassname)) {
            throw new InstallationException(get_string('classmissing', 'error', $typeclassname, $type, $plugin));
        }
    }
    $types = call_static_method($classname, 'get_block_types');
    foreach ($types as $type) {
        $pluginclassname = generate_class_name('blocktype', $type);
        if (get_config('installed')) {
            if (table_exists(new XMLDBTable('blocktype_installed')) && $taken = get_record_select('blocktype_installed', 
                'name = ? AND artefactplugin != ? ',
                array($type, $pluginname))) {
                throw new InstallationException(get_string('blocktypenametaken', 'error', $type,
                    ((!empty($taken->artefactplugin)) ? $taken->artefactplugin : get_string('system'))));
            }
        }
        // go look for the lib file to include
        try {
            safe_require('blocktype', $pluginname . '/' . $type);
        }
        catch (Exception $_e) {
            throw new InstallationException(get_string('blocktypelibmissing', 'error', $type, $pluginname));
        }
        if (!class_exists($pluginclassname)) {
            throw new InstallationException(get_string('classmissing', 'error', $pluginclassname, $type, $pluginname));
        }
    }
}

function rebuild_artefact_parent_cache_dirty() {
    // this will give us a list of artefacts, as the first returned column
    // is not unqiue, but that's ok, it's what we want.
    if (!$dirty = get_records_array('artefact_parent_cache', 'dirty', 1, '', 'DISTINCT(artefact)')) {
        return;
    }
    db_begin();
    delete_records('artefact_parent_cache', 'dirty', 1);
    foreach ($dirty as $d) {
        $parentids = array();
        $current = $d->artefact;
        delete_records('artefact_parent_cache', 'artefact', $current);
        $parentids = array_keys(artefact_get_parents_for_cache($current));
        foreach ($parentids as $p) {
            $apc = new StdClass;
            $apc->artefact = $d->artefact;
            $apc->parent   = $p;
            $apc->dirty    = 0;
            insert_record('artefact_parent_cache', $apc);
        }
    }
    db_commit();
}

function rebuild_artefact_parent_cache_complete() {
    db_begin();
    delete_records('artefact_parent_cache');

    $artefacts = get_records_sql_assoc('
        SELECT id, parent, COUNT(aa.artefact) AS attached
        FROM {artefact} a LEFT JOIN {artefact_attachment} aa ON a.id = aa.attachment
        GROUP BY id, parent
        HAVING parent IS NOT NULL OR COUNT(aa.artefact) > 0',
        array()
    );

    if ($artefacts) {

        foreach ($artefacts as &$artefact) {

            // Nothing that can be a parent can be an attachment, so it's good
            // enough to first get everything this artefact is attached to, and
            // then find all its ancestors and the ancestors of everything it's
            // attached to.

            $ancestors = array();
            if ($artefact->attached) {
                $ancestors = get_column('artefact_attachment', 'artefact', 'attachment', $artefact->id);
            }

            $tocheck = $ancestors;
            $tocheck[] = $artefact->id;

            foreach ($tocheck as $id) {
                $p = isset($artefacts[$id]) ? $artefacts[$id]->parent : null;
                while (!empty($p)) {
                    $ancestors[] = $p;
                    $p = isset($artefacts[$p]) ? $artefacts[$p]->parent : null;
                }
            }

            foreach (array_unique($ancestors) as $p) {
                insert_record('artefact_parent_cache', (object) array(
                    'artefact' => $artefact->id,
                    'parent'   => $p,
                    'dirty'    => 0,
                ));
            }
        }
    }
    db_commit();
}

function artefact_get_attachment_types() {
    static $artefacttypes = null;
    if (is_null($artefacttypes)) {
        $artefacttypes = array();
        foreach (require_artefact_plugins() as $plugin) {
            $classname = generate_class_name('artefact', $plugin->name);
            if (!is_callable($classname . '::get_attachment_types')) {
                continue;
            }
            $artefacttypes = array_merge($artefacttypes, call_static_method($classname, 'get_attachment_types'));
        }
    }
    return $artefacttypes;
}

function artefact_get_parents_for_cache($artefactids, &$parentids=false) {
    if (!is_array($artefactids)) {
        $artefactids = array($artefactids);
    }
    $current = array_map('intval', $artefactids);
    if (empty($parentids)) { // first call
        $parentids = array();
    }
    while (true) {
        if (!$parents = get_records_select_array('artefact', 'id IN (' . join(',',$current) . ')')) {
            break;
        }

        // get any blog posts these artefacts may be attached to
        $checkattachments = array();
        foreach ($parents as $p) {
            if (in_array($p->artefacttype, artefact_get_attachment_types())) {
                $checkattachments[] = (int)$p->id;
            }
        }
        if (!empty($checkattachments)) {
            if ($associated = get_records_select_assoc('artefact_attachment', 'attachment IN (' . join(',', $checkattachments) . ')')) {
                $associated = array_keys($associated);
                foreach ($associated as $a) {
                    $parentids[$a] = 1;
                }
                artefact_get_parents_for_cache($associated, $parentids);
            }
        }

        // check parents
        $current = array();
        foreach ($parents as $p) {
            if ($p->parent) {
                $parentids[$p->parent] = 1;
                $current[] = $p->parent;
            }
        }
        if (empty($current)) {
            break;
        }
    }
    return $parentids;
}

function artefact_can_render_to($type, $format) {
    return in_array($format, call_static_method(generate_artefact_class_name($type), 'get_render_list'));
}

function artefact_instance_from_id($id) {
    $sql = 'SELECT a.*, i.plugin 
            FROM {artefact} a 
            JOIN {artefact_installed_type} i ON a.artefacttype = i.name
            WHERE a.id = ?';
    if (!$data = get_record_sql($sql, array($id))) {
        throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $id));
    }
    $classname = generate_artefact_class_name($data->artefacttype);
    safe_require('artefact', $data->plugin);
    return new $classname($id, $data);
}

/**
 * This function will return an instance of any "0 or 1" artefact. That is any
 * artefact that each user will have at most one instance of (e.g. profile
 * fields).
 *
 * @param string Is the type of artefact to return
 * @param string The user_id who owns the fetched artefact. (defaults to the
 * current user)
 *
 * @returns ArtefactType Instance of the artefact.
 */
function artefact_instance_from_type($artefact_type, $user_id=null) {
    global $USER;

    if ($user_id === null) {
        $user_id = $USER->get('id');
    }

    safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $artefact_type));

    if (!call_static_method(generate_artefact_class_name($artefact_type), 'is_singular')) {
        throw new ArtefactNotFoundException("This artefact type is not a 'singular' artefact type");
    }

    // email is special (as in the user can have more than one of them, but
    // it's treated as a 0 or 1 artefact and the primary is returned
    if ($artefact_type == 'email') {
        $id = get_field('artefact_internal_profile_email', 'artefact', 'owner', $user_id, 'principal', 1);

        if (!$id) {
            throw new ArtefactNotFoundException("Artefact of type '${artefact_type}' doesn't exist");
        }

        $classname = generate_artefact_class_name($artefact_type);
        safe_require('artefact', 'internal');
        return new $classname($id);
    }
    else {
        $sql = 'SELECT a.*, i.plugin 
                FROM {artefact} a 
                JOIN {artefact_installed_type} i ON a.artefacttype = i.name
                WHERE a.artefacttype = ? AND a.owner = ?';
        if (!$data = get_record_sql($sql, array($artefact_type, $user_id))) {
            throw new ArtefactNotFoundException("Artefact of type '${artefact_type}' doesn't exist");
        }

        $classname = generate_artefact_class_name($artefact_type);
        safe_require('artefact', $data->plugin);
        return new $classname($data->id, $data);
    }

    throw new ArtefactNotFoundException("Artefact of type '${artefact_type}' doesn't exist");
}

function artefact_watchlist_notification($artefactids) {
    // gets all the views containing this artefact or a parent of this artefact and creates a watchlist activity for each view
    if ($views = get_column_sql('SELECT DISTINCT "view" FROM {view_artefact} WHERE artefact IN (' . implode(',', array_merge(array_keys(artefact_get_parents_for_cache($artefactids)), array_map('intval', $artefactids))) . ')')) {
        require_once('activity.php');
        foreach ($views as $view) {
            activity_occurred('watchlist', (object)array('view' => $view));
        }
    }
}

function artefact_get_descendants($new) {
    $seen = array();
    if (!empty($new)) {
        $new = array_combine($new, $new);
    }
    while (!empty($new)) {
        $seen = $seen + $new;
        $children = get_column_sql('
            SELECT id
            FROM {artefact}
            WHERE parent IN (' . implode(',', array_map('intval', $new)) . ') AND id NOT IN (' . implode(',', array_map('intval', $seen)) . ')', array());
        if ($children) {
            $new = array_diff($children, $seen);
            $new = array_combine($new, $new);
        }
        else {
            $new = array();
        }
    }
    return array_values($seen);
}

function artefact_owner_sql($userid=null, $groupid=null, $institution=null) {
    if ($institution) {
        return 'institution = ' . db_quote($institution);
    }
    if ($groupid) {
        return '"group" = ' . (int)$groupid;
    }
    if ($userid) {
        return 'owner = ' . (int)$userid;
    }
    return null;
}

/**
 * Given a string of html, look for references to artefacts in it and return a 
 * list of artefact IDs found
 *
 * @return array List of artefact IDs found
 */
function artefact_get_references_in_html($html) {
    $matches = array();

    // Look for links to artefacts
    preg_match_all('#<a[^>]+href="[^>]+artefact/file/download\.php\?file=(\d+)#', $html, $matches);
    $artefacts = $matches[1];

    // Look for images sourcing artefacts
    preg_match_all('#<img[^>]+src="[^>]+artefact/file/download\.php\?file=(\d+)#', $html, $matches);
    $artefacts = array_unique(array_merge($artefacts, $matches[1]));

    // TODO: might have to look for object tags etc. later

    return $artefacts;
}

function artefact_get_records_by_id($ids) {
    if (!empty($ids)) {
        if ($records = get_records_select_assoc('artefact', 'id IN (' . join(',', array_map('intval', $ids)) . ')')) {
            return $records;
        }
    }
    return array();
}

function artefact_type_installed($type) {
    static $types = array();

    if (!$types) {
        $types = get_records_assoc('artefact_installed_type');
    }

    return isset($types[$type]);
}

function require_artefact_plugins() {
    static $plugins = null;
    if (is_null($plugins)) {
        $plugins = plugins_installed('artefact');
        foreach ($plugins as $plugin) {
            safe_require('artefact', $plugin->name);
        }
    }
    return $plugins;
}

function artefact_get_types_from_filter($filter) {
    static $contenttype_artefacttype = null;

    if (is_null($contenttype_artefacttype)) {
        $contenttype_artefacttype = array();
        foreach (require_artefact_plugins() as $plugin) {
            $classname = generate_class_name('artefact', $plugin->name);
            if (!is_callable($classname . '::get_artefact_type_content_types')) {
                continue;
            }
            $artefacttypetypes = call_static_method($classname, 'get_artefact_type_content_types');
            foreach ($artefacttypetypes as $artefacttype => $contenttypes) {
                if (!empty($contenttypes)) {
                    foreach ($contenttypes as $ct) {
                        $contenttype_artefacttype[$ct][] = $artefacttype;
                    }
                }
            }
        }
    }

    if (empty($contenttype_artefacttype[$filter])) {
        return null;
    }

    return $contenttype_artefacttype[$filter];
}
