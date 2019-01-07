<?php
/**
 *
 * @package    mahara
 * @subpackage artefact
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('libroot') . 'group.php');

/**
 * Helper interface to hold the PluginArtefact's abstract static functions
 */
interface IPluginArtefact {
    /**
     * This function returns a list of classnames
     * of artefact types this plugin provides.
     * @abstract
     * @return array
     */
    public static function get_artefact_types();

    /**
    * This function returns a list of classnames
    * of block types this plugin provides
    * they must match directories inside artefact/$name/blocktype
    * @abstract
    * @return array
    */
    public static function get_block_types();

    /**
     * This function returns the name of the plugin.
     * @abstract
     * @return string
     */
    public static function get_plugin_name();
}

/**
 * Base artefact plugin class
 * @abstract
 */
abstract class PluginArtefact extends Plugin implements IPluginArtefact {

    public static function get_plugintype_name() {
        return 'artefact';
    }

    /**
     * This function returns an array of menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function menu_items() {
        return array();
    }


    /**
     * This function returns an array of menu items to be displayed
     * on a group page when viewed by group members.
     * Each item should be a stdClass() object containing -
     * - title language pack key
     * - url relative to wwwroot
     * @return array
     */
    public static function group_tabs($groupid, $role) {
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


    /**
     * Indicates whether this particular plugin has any progress bar options. By default, any new plugin
     * will have progress bar options, for creating at least one of the artefact.
     *
     * @return boolean
     */
    public static function has_progressbar_options() {
        return true;
    }

    /**
     * Returns the relative URL path to the place in mahara that relates
     * to the artefact.
     * E.g. For plan artefact the link will be 'artefact/plans/index.php'
     * @param int The name of the artefact type (in case different ones need different links)
     * @return string Url path to artefact.
     */
    public static function progressbar_link($artefacttype) {
        return '';
    }

    public static function progressbar_task_label($artefacttype, $target, $completed) {
        // By default we check to see if they provided a string called "progress_{$artefacttype}"
        // in the plugin lang file (which takes one param with the count remaining)
        $label = get_string('progress_' . $artefacttype, 'artefact.' . static::get_plugin_name(), ($target - $completed));

        // Kind of a hack: if get_string() gave us a result indicating the string could not be found,
        // try to construct one using the plugin and artefact name.
        if (substr($label, 0, 2) == '[[') {
            $artname = get_string($artefacttype, 'artefact.' . static::get_plugin_name());
            if (substr($artname, 0, 2) == '[[') {
                $artname = $artefacttype;
            }
            $label = get_string('progressbargenerictask', 'mahara', ($target - $completed), $artname);
        }
        return $label;
    }

    /**
     * Add any special progress items that may not exist as an artefact type.
     * @return array of objects each containing name, title, plugin, active, iscountable
     */
    public static function progressbar_additional_items() {
        return array();
    }

    /**
     * If this plugin provides some progress bar metaartefacts, then this method should
     * provide the logic to count them.
     * @param string $name The name of the meta-artefact to count
     * @return object A record containing the count data to be displayed in the sidebar.
     *                It should contain the fields "artefacttype" and "completion"
     */
    public static function progressbar_metaartefact_count($name) {
        return false;
    }

    /**
     * This function returns an array of menu items
     * to be displayed in the top right navigation menu
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function right_nav_menu_items() {
        return array();
    }

    /**
     * This function returns an array of admin menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function admin_menu_items() {
        return array();
    }

    /**
     * This function returns an array of institution menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function institution_menu_items() {
        return array();
    }

    /**
     * This function returns an array of institution staff menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function institution_staff_menu_items() {
        return array();
    }
}

/**
 * Helper interface to hold the Artefact class's abstract static functions
 */
interface IArtefactType {
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
    public static function get_icon($options=null);

    /**
     * whether a user will have exactly 0 or 1 of this artefact type
     * @abstract
     */
    public static function is_singular();

    /**
     * Returns a list of key => value pairs where the key is either '_default'
     * or a language string, and value is a URL linking to that behaviour for
     * this artefact type
     *
     * @param integer This is the ID of the artefact being linked to
     */
    public static function get_links($id);

    // @TODO maybe uncomment this later and implement it everywhere
    // when we know a bit more about what blocks we want.
    //public function render_self($options);
}

/**
 * Base artefact type class
 * @abstract
 */
abstract class ArtefactType implements IArtefactType {

    protected $dirty;
    protected $deleted = false;
    protected $id;
    protected $artefacttype;
    protected $owner;
    protected $container = 0;
    protected $parent;
    protected $oldparent;
    protected $ctime;
    protected $mtime;
    protected $atime;
    protected $locked = 0;
    protected $title;
    protected $description;
    protected $note;
    protected $tags = array();
    protected $institution;
    protected $group;
    protected $author;
    protected $authorname;
    protected $allowcomments = 0;
    protected $approvecomments = 0;
    protected $rolepermissions;
    protected $mtimemanuallyset;
    protected $license;
    protected $licensor;
    protected $licensorurl;
    protected $path;

    protected $viewsinstances;
    protected $viewsmetadata;
    protected $childreninstances;
    protected $childrenmetadata;
    protected $parentinstance;
    protected $parentmetadata;

    /**
     * Constructor.
     * If an id is supplied, will query the database
     * to build up the basic information about the object.
     * If an id is not supplied, we just create an empty
     * artefact, ready to be filled up.
     * If the $new parameter is true, we can skip the query
     * because we know the artefact is new.
     *
     * @param int   $id     artefact.id
     * @param mixed $data   optional data supplied for artefact
     * @param bool  $new
     */
    public function __construct($id=0, $data=null, $new = FALSE) {
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
                    if (!(!empty($value) && is_string($value) && $value = strtotime($value))) {
                        $value = time();
                    }
                }
                if ($field == 'tags' && !is_array($value)) {
                    $value = preg_split("/\s*,\s*/", trim($value));
                }
                $this->{$field} = $value;
            }
        }

        if (!empty($this->parent)) {
            $this->oldparent = $this->parent;
        }

        $this->artefacttype = $this->get_artefact_type();
        if (!empty($data->artefacttype)) {
            if ($this->artefacttype != $data->artefacttype) {
                throw new SystemException(get_string('artefacttypemismatch', 'error', $data->artefacttype, $this->artefacttype));
            }
        }

        // load tags
        if ($this->id) {
            $this->tags = ArtefactType::artefact_get_tags($this->id);
        }

        // load group permissions
        if ($this->group && !is_array($this->rolepermissions)) {
            $this->load_rolepermissions();
        }

        $this->atime = time();
    }

    /**
     * returns duplicated artefacts which have the same value of the following fields:
     *  - owner
     *  - type
     *  - content
     *      - title
     *      - description
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        if (!empty($values['content']['description'])) {
            return get_column_sql('
                SELECT id
                FROM {artefact}
                WHERE owner = ?
                    AND artefacttype = ?
                    AND title = ?
                    AND description = ?',
                array($values['owner'], $values['type'], $values['content']['title'], $values['content']['description'])
            );
        }
        else {
            return get_column('artefact', 'id',
                'owner', $values['owner'],
                'artefacttype', $values['type'],
                'title', $values['content']['title']);
        }
    }

    /**
     * returns existing artefacts which have the same artefacttype and owner
     *
     * @param array $values
     */
    public static function get_existing_artefacts(array $values) {
        return get_column('artefact', 'id',
                        'owner', $values['owner'],
                        'artefacttype', $values['type']);
    }

    /**
     * Returns the instances of all views where this artefact is used.
     *
     * @return array Array of view instances.
     */
    public function get_views_instances() {
        // @todo test this
        if (!isset($this->viewsinstances)) {
            $this->viewsinstances = false;
            if ($views = $this->get_views_metadata()) {
                $this->viewsinstances = array();
                if (!class_exists('View')) {
                    require_once(get_config('libroot') . 'view.php');
                }
                foreach ($views as $view) {
                    $this->viewsinstances[] = new View($view->view);
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
        static $cache = array();

        $type = $this->get('artefacttype');
        if (!isset($cache[$type])) {
            $cache[$type] = get_field('artefact_installed_type', 'plugin', 'name', $type);
        }

        return $cache[$type];
    }

    /**
     * This function returns the instances
     * of all children of this artefact
     * If you just want the basic info,
     * use {@link get_children_metadata} instead.
     *
     * @return array of instances or false if no children.
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
     * @return array of false if no children
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
                // Only set it to dirty if it's changed.
                $this->dirty = true;
                // Set oldparent only if it has changed.
                if ($field == 'parent') {
                    $this->oldparent = $this->parent;
                }
            }
            $this->{$field} = $value;
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
        global $USER;

        static $last_source, $last_output;

        $is_new = false;

        if (empty($this->dirty)) {
            return;
        }

        if (empty($this->author) && empty($this->authorname)) {
            $this->set_author();
        }

        db_begin();

        $fordb = new stdClass();
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime', 'atime')) && !empty($v)) {
                if ($v !== $last_source) {
                  $last_output = db_format_timestamp($v);
                  $last_source = $v;
                }
                $fordb->{$k} = $last_output;
            }
        }
        if (empty($this->id)) {
            $is_new = true;
            $this->id = insert_record('artefact', $fordb, 'id', true);
            if ($this->can_be_logged()) {
                $this->log('created');
            }
            $this->add_hierarchy_path($this->parent);
        }
        else {
            if ($this->can_be_logged()) {
                $this->log('edited');
            }
            update_record('artefact', $fordb, 'id');
            $this->update_hierarchy_path($this->parent);
        }

        if (!empty($this->group)) {
            $this->save_rolepermissions();
        }

        if (!$is_new) {
          $deleted = delete_records('tag', 'resourcetype', 'artefact', 'resourceid', $this->id);
        }

        if (is_array($this->tags)) {
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
            $this->tags = check_case_sensitive($this->tags, 'tag');

            foreach (array_unique($this->tags) as $tag) {
                if (empty($tag)) {
                    continue;
                }
                $tag = check_if_institution_tag($tag);
                insert_record('tag',
                    (object) array(
                        'resourcetype' => 'artefact',
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

        $this->postcommit_hook($is_new);

        handle_event('saveartefact', $this);

        $this->dirty = false;
        $this->deleted = false;

        db_commit();
    }

    /**
     * A hook method called immediately after the basic data is save in the commit() method,
     * but before the DB transaction is closed and before the saveartefact event is triggered.
     *
     * Child classes may use this to alter data or add data into additional tables so that
     * it's present when the saveartefact event is called.
     *
     * @param boolean $new True if the artefact has just been created
     */
    protected function postcommit_hook($new) {
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

        $ignorefields = array(
            'dirty', 'deleted', 'mtime', 'atime',
            'tags', 'allowcomments', 'approvecomments', 'path'
        );

        handle_event('deleteartefact', $this, $ignorefields);

        // Set flags.
        $this->dirty = false;
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

        $sql = 'SELECT a.id, a.parent, a.artefacttype, a.container, i.plugin
                FROM {artefact} a
                JOIN {artefact_installed_type} i ON a.artefacttype = i.name
                WHERE a.id IN (' . join(',', array_fill(0, count($artefactids), '?')) . ')'.
                ' ORDER BY artefacttype';
        $records = get_records_sql_assoc($sql, $artefactids);

        $containers = array();
        $leaves = array();
        foreach ($records as $r) {
            safe_require('artefact', $r->plugin);
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
        $logdata = array_merge($containers, $leaves);
        handle_event('deleteartefacts', $logdata);

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
        delete_records_select('interaction_forum_post_attachment', "attachment IN $idstr");

        // Make sure that the artefacts are removed from any view blockinstances
        require_once(get_config('docroot') . 'blocktype/lib.php');
        BlockInstance::bulk_remove_artefacts($artefactids);

        delete_records_select('view_artefact', "artefact IN $idstr");
        delete_records_select('tag', "resourcetype = 'artefact' AND resourceid IN ('" . join("','", array_map('intval', $artefactids)) . "')");
        delete_records_select('artefact_access_role', "artefact IN $idstr");
        delete_records_select('artefact_access_usr', "artefact IN $idstr");
        execute_sql("UPDATE {usr} SET profileicon = NULL WHERE profileicon IN $idstr");
        execute_sql("UPDATE {institution} SET logo = NULL WHERE logo IN $idstr");

        // Delete any references to files embedded in textboxes
        delete_records_select('artefact_file_embedded', "fileid IN $idstr");

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
     * By default users are notified of all comments on artefacts
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
        $smarty = smarty_core();
        $smarty->assign('title', $this->get('title'));
        $smarty->assign('owner', $this->get('owner'));
        $smarty->assign('tags', $this->get('tags'));
        $smarty->assign('description', $this->get('description'));
        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
        }
        $smarty->assign('view', (!empty($options['viewid']) ? $options['viewid'] : null));
        return array(
            'html' => $smarty->fetch('artefact.tpl'),
            'javascript'=>''
        );
    }


    // ******************** STATIC FUNCTIONS ******************** //

    public static function get_instances_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    public static function get_metadata_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

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

    public function role_has_permission($role, $permission) {
        return $this->rolepermissions[$role]->{$permission};
    }

    public function copy_data() {
        $ignore = array(
            'dirty' => 1,
            'deleted' => 1,
            'id' => 1,
            'locked' => 1,
            'rolepermissions' => 1,
            'viewsinstances' => 1,
            'viewsmetadata' => 1,
            'childreninstances' => 1,
            'childrenmetadata' => 1,
            'parentinstance' => 1,
            'parentmetadata' => 1,
            'path' => 1    // the path value will be updated later
        );
        $data = new stdClass();
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
            $this->set('parent', $this->default_parent_for_copy($view, $template, array_map(function($a) { return $a->newid; }, $artefactcopies)));
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

/**
 * Returns a list of embedded image artefact ids
 * This function is called when copying a view
 *
 * @return array
 */
    public function embed_id_list() {
        if ($this->can_have_attachments()) {
            if ($list = get_column('artefact_file_embedded', 'fileid', 'resourceid', $this->get('id'))) {
                return $list;
            }
        }
        return array();
    }

    public function attachment_id_list_with_item($itemid) {
        // If artefact attachment table has 'item' column utilised.
        if ($this->can_have_attachments()) {
            if ($list = get_column('artefact_attachment', 'attachment', 'artefact', $this->get('id'), 'item', $itemid)) {
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
            WHERE aa.artefact IN (' . join(', ', array_map('intval', $artefactids)) . ')', array());
        if (!$attachments) {
            return array();
        }
        return $attachments;
    }

    public function tags_from_id_list($artefactids) {
        if (empty($artefactids)) {
            return array();
        }
        $typecast = is_postgres() ? '::varchar' : '';
        $artefactids = join("','", array_map('intval', $artefactids));
        $tags = get_records_sql_array("
            SELECT
                (CASE
                    WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                    ELSE t.tag
                END) AS tag, t.resourceid
            FROM {tag} t
            LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
            LEFT JOIN {institution} i ON i.name = t2.ownerid
            WHERE t.resourcetype = 'artefact' AND t.resourceid IN ('" . $artefactids . "')");
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
        $typecast = is_postgres() ? '::varchar' : '';
        if ($list) {
            $tags = get_records_sql_array("
                SELECT
                    (CASE
                        WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                        ELSE t.tag
                    END) AS tag, t.resourceid
                FROM {tag} t
                LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
                LEFT JOIN {institution} i ON i.name = t2.ownerid
                WHERE t.resourcetype = 'artefact' AND t.resourceid IN ('" . join("','", array_keys($list)) . "')");
            if ($tags) {
                foreach ($tags as $t) {
                    $list[$t->resourceid]->tags[] = $t->tag;
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

    public function attach($attachmentid, $itemid=null) {
        if (record_exists('artefact_attachment', 'artefact', $this->get('id'), 'attachment', $attachmentid)) {
            return;
        }
        if (!record_exists('artefact', 'id', $attachmentid)) {
            throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $attachmentid));
        }
        $data = new stdClass();
        $data->artefact = $this->get('id');
        $data->attachment = $attachmentid;
        $data->item = $itemid;
        insert_record('artefact_attachment', $data);
    }

    public function detach($attachmentid=null) {
        if (is_null($attachmentid)) {
            delete_records('artefact_attachment', 'artefact', $this->id);
            return;
        }
        if (!record_exists('artefact', 'id', $attachmentid)) {
            throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $attachmentid));
        }
        delete_records('artefact_attachment', 'artefact', $this->get('id'), 'attachment', $attachmentid);
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

    /**
     * Return an array of tags associated to an artefact
     *
     * @param int  ID of the artefact
     *
     * @return array of strings
     */
    public static function artefact_get_tags($id) {
        if (empty($id)) {
            return array();
        }
        $typecast = is_postgres() ? '::varchar' : '';
        $tags = get_column_sql("
            SELECT
                (CASE
                    WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                    ELSE t.tag
                END) AS tag
            FROM {tag} t
            LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
            LEFT JOIN {institution} i ON i.name = t2.ownerid
            WHERE t.resourcetype = ? AND t.resourceid = ?
            ORDER BY tag", array('artefact', $id));
        if (!$tags) {
            return array();
        }
        return $tags;
    }

    /**
     * Checks to see if artefact type is allowed to be part of the progress bar.
     * By default all artefacts are included in progress bar. To remove an artefact
     * from being a progress bar option have your artefacttype return false for this.
     * @return boolean
     */
    public static function is_allowed_in_progressbar() {
        return true;
    }

    /**
     * Checks to see if artefact for the progress bar is countable.
     * By default all artefacts are counted as true/false (1 or 0). If you need to have
     * more then one instance counting towards progress, say image upload, you can specify
     * it to be countable. This will show a select box rather than a check box on the
     * progress admin screen.
     * @return boolean
     */
    public static function is_countable_progressbar() {
        return false;
    }

    /**
     * Check if artefacttype is meant to be handled as a meta artefact by progress bar
     * @return boolean
     */
    public static function is_metaartefact() {
        return false;
    }

    /**
     * The (optional) custom title of this artefact on the profile completion progress bar config page
     * @return mixed FALSE if it should just use the artefact type, a string otherwise
     */
    public static function get_title_progressbar() {
        return false;
    }

    /**
     * Move an artefact within a hierarchy of artefacts
     *
     * @param integer $newparentid ID of the item to attach it to, or null for top level
     */
    function update_hierarchy_path($newparentid = null) {
        // Don't do anything if parent is the same.
        if ($this->oldparent == $newparentid) {
            return false;
        }

        if ($this->oldparent == null) {
            // Create a 'fake' old parent item for items at the top level.
            $oldparent = new stdClass();
            $oldparent->id = 0;
            $oldparent->path = '';
        }
        else {
            $oldparent = get_record('artefact', 'id', $this->oldparent);
        }

        if ($newparentid == null) {
            // Create a 'fake' new parent item for attaching to the top level.
            $newparent = new stdClass();
            $newparent->id = 0;
            $newparent->path = '';
        }
        else {
            $newparent = get_record('artefact', 'id', $newparentid);

            if ($this->is_child_of($newparent, $this->id) || empty($newparent)) {
                // You can't move an item into its own child.
                throw new NotFoundException(get_string('cantmoveitem', 'mahara'));
            }
        }

        // Update the path of the item and its descendants:
        // - Remove the 'old parent' segment of the path from the beginning of the path;
        // - Add the 'new parent' segment of the path instead;
        // - Do this for all items that start with the item's path.
        // The WHERE clause must be like this to avoid /1% matching /10.
        $length = strlen($oldparent->path) + 1;
        if (!empty($this->institution)) {
            $ownertype = 'institution';
            $ownerid = $this->institution;
        }
        else if (!empty($this->group)) {
            $ownertype = '"group"';
            $ownerid = $this->group;
        }
        else {
            $ownertype = 'owner';
            $ownerid = $this->owner;
        }
        $params = array($newparent->path, $length, $ownerid, $this->path, db_like_escape("{$this->path}/") . '%');
        $sql = "UPDATE {artefact} SET path = ? || SUBSTR(path, ?) WHERE " . $ownertype . " = ? AND (path = ? OR path LIKE ? )";

        execute_sql($sql, $params);

        // Make sure that the value of a new path is set.
        $this->path = $newparent->path . substr($this->path, $length - 1);

        return true;
    }

    /**
     * Add a new artefact path
     *
     * @param integer $parentid The ID of the parent to attach to, or null for top level
     */
    function add_hierarchy_path($parentid = null) {
        // Calculate where the new item fits into the hierarchy.
        // Handle top level items differently.
        if ($parentid == null) {
            $parentpath = '';
        }
        else {
            // Parent item must exist.
            $parentpath = get_field('artefact', 'path', 'id', $parentid);
        }

        // Set the hierarchy path for the new item.
        set_field('artefact', 'path', $parentpath . '/' . $this->id, 'id', $this->id);

        // Make sure that the value of a new path is set.
        $this->path = $parentpath . '/' . $this->id;
    }

    /**
     * Get descendants of an artefact.
     * Result will include an item itself.
     *
     * @return array
     */
    function get_item_descendants() {
        $path = get_field('artefact', 'path', 'id', $this->id);
        if ($path) {
            // The WHERE clause must be like this to avoid /1% matching /10.
            $sql = "SELECT id, parent, path
                    FROM {artefact}
                    WHERE path = ? OR path LIKE ?
                    ORDER BY path";
            return get_records_sql_array($sql, array($path, db_like_escape("{$path}/") . '%'));
        }
        else {
            throw new NotFoundException(get_string('nopathfound', 'mahara'));
        }
    }

    /**
     * Get artefact's ancestors
     * Result will include an item itself.
     *
     * @return array
     */
    function get_item_ancestors() {
        return $ancestors = explode('/', substr($this->path, 1));
    }

    /**
     * Returns true if $item is a child of any of the item IDs given
     *
     * @param integer|array $ids ID or array of IDs to check against the item
     *
     * @return boolean True if $item is a child of any of $ids
     */
    public function is_child_of($ids) {
        if (!isset($this->path)) {
            return false;
        }

        $ids = (is_array($ids)) ? $ids : array($ids);

        $parents = explode('/', substr($this->path, 1));

        // Remove the item's ID.
        $itemid = array_pop($parents);

        foreach ($parents as $parent) {
            if (in_array($parent, $ids)) {
                return true;
            }
        }
        return false;
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

/**
 * Get artefact instances from ids
 * @param array of int $ids of the artefacts
 *
 * @result mixed    Either the artefact object or false if plugin has gone.
 */
function artefact_instances_from_ids($ids) {
    $result = array();

    if (empty($ids)) {
        return $result;
    }

    $sql = 'SELECT a.*, i.plugin
            FROM {artefact} a
            JOIN {artefact_installed_type} i ON a.artefacttype = i.name
            WHERE a.id IN (' . join(',', $ids) . ')';
    if (!$data = get_records_sql_array($sql, NULL)) {
        throw new ArtefactNotFoundException(get_string('artefactsnotfound', 'mahara', implode(', ', $ids)));
    }
    foreach ($data as $item) {
        $classname = generate_artefact_class_name($item->artefacttype);
        safe_require('artefact', $item->plugin);
        $result[$item->id] = new $classname($item->id, $item);
    }
    return $result;
}

/**
 * Get artefact instance from id
 * @param int $id of the artefact
 * @param int $deleting If we are wanting to delete the artefact we need
 *                      to check that the artefact plugin still exists on
 *                      the server.
 *
 * @result mixed    Either the artefact object or false if plugin has gone.
 */
function artefact_instance_from_id($id, $deleting = false) {
    $sql = 'SELECT a.*, i.plugin
            FROM {artefact} a
            JOIN {artefact_installed_type} i ON a.artefacttype = i.name
            WHERE a.id = ?';
    if (!$data = get_record_sql($sql, array($id))) {
        throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $id));
    }
    $classname = generate_artefact_class_name($data->artefacttype);
    if ($deleting) {
        safe_require('artefact', $data->plugin, 'lib.php', 'require_once', true);
        if (is_callable($classname . '::delete')) {
            return new $classname($id, $data);
        }
        return false;
    }
    else {
        safe_require('artefact', $data->plugin);
        return new $classname($id, $data);
    }
}
/**
 * This function returns the current title of an artefact's blockinstance
 * if $viewid and $blockid are provided.
 *
 * @param int $artefactid the id of the artefact
 * @param int $viewid     the id of the view the artefact associated with
 * @param int $blockid    the id of the block instance the artefact is connected to
 *
 * @return str            the block instance title
 */
function artefact_title_for_view_and_block($artefact, $viewid, $blockid) {
    $sql = "SELECT bi.title AS blocktitle,
            a.title AS artefacttitle
            FROM {artefact} a
            JOIN {view_artefact} va ON va.artefact = a.id
            JOIN {block_instance} bi ON bi.id = va.block
            WHERE va.artefact = ?
            AND va.view = ? AND va.block = ?";
    if (!$data = get_record_sql($sql, array($artefact->get('id'), $viewid, $blockid))) {
        // if we are traversing folders where the subfolders/files are not directly connected
        // to the blockinstance we just return their title
        return $artefact->display_title();
    }
    $currenttitle = (!empty($data->blocktitle)) ? $data->blocktitle : $data->artefacttitle;
    return $currenttitle;
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
        if (!call_static_method(generate_artefact_class_name($artefact_type), 'is_singular')) {
            throw new ArtefactNotFoundException("This artefact type is not a 'singular' artefact type");
        }
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
    // the notification will be handled by plugin watchlistnotification
    // responding to the event saveartefact
}

/**
 * Returns the id of descendant artefacts of the given artefacts
 * @param array $ids: IDs of ancestral artefacts
 * @return array
 */
function artefact_get_descendants(array $ids) {
    if (empty($ids)) {
        return array();
    }
    if (get_config('version') < 2014050901) {
        $seen = array();
        $new = $ids;
        if (!empty($new)) {
            $new = array_combine($new, $new);
        }
        while (!empty($new)) {
            $seen = $seen + $new;
            $children = get_column_sql('
                SELECT id
                FROM {artefact}
                WHERE parent IN (' . implode(',', array_map('intval', $new)) . ')
                    AND id NOT IN (' . implode(',', array_map('intval', $seen)) . ')'
                , array());
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
    else {
        // The column 'path' has been added since mahara 1.10
        if ($aids = get_column_sql('
                SELECT DISTINCT id
                FROM {artefact}
                WHERE ' . join(' OR ', array_map(
                    function($id) {
                        return 'path LIKE ' . db_quote('%/' . db_like_escape($id) . '/%');
                    }
                    , $ids)) . '
                ORDER BY id'
            )) {
            return array_merge($ids, array_values($aids));
        }
        else {
            return $ids;
        }
    }
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

function artefact_new_title($title, $artefacttype, $owner, $group, $institution) {
    $taken = get_column_sql('
        SELECT title
        FROM {artefact}
        WHERE ' . artefact_owner_sql($owner, $group, $institution) . "
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

/**
 * Given a list of artefact ids, return a name and url for the thing that
 * owns each artefact, suitable for display.
 *
 * @param array $ids list of artefact ids
 *
 * @return array list of stdClass() objects, each containing a name & url property
 */
function artefact_get_owner_info($ids) {
    $data = get_records_sql_assoc('
        SELECT
            a.id AS aid, a.owner, a.group, a.institution,
            u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.urlid,
            g.name AS groupname, g.urlid as groupurlid,
            i.displayname
        FROM
            {artefact} a
            LEFT JOIN {usr} u ON a.owner = u.id
            LEFT JOIN {group} g ON a.group = g.id
            LEFT JOIN {institution} i ON a.institution = i.name
        WHERE
            a.id IN (' . join(',', array_fill(0, count($ids), '?')) . ')',
        $ids
    );
    $wwwroot = get_config('wwwroot');
    foreach ($data as &$d) {
        if ($d->institution == 'mahara') {
            $name = get_config('sitename');
            $url  = $wwwroot;
        }
        else if ($d->institution) {
            $name = $d->displayname;;
            $url  = $wwwroot . 'institution/index.php?institution=' . $d->institution;
        }
        else if ($d->group) {
            $name = $d->groupname;;
            $url  = group_homepage_url((object) array('id' => $d->group, 'urlid' => $d->groupurlid));
        }
        else {
            $name = display_name($d);
            $url  = profile_url($d);
        }
        $d = (object) array('name' => $name, 'url' => $url);
    }
    return $data;
}


/**
 * Returns any artefact options allowed to be included in the progress_bar
 * @param array $onlythese (optional) An array of institution_config options indicating which items to include
 * @return array of objects each containing name, title, plugin, active, iscountable
 */
function artefact_get_progressbar_items($onlythese = false) {
    if ($onlythese === false) {
        $onlytheseplugins = array();
    }
    else {
        $onlytheseplugins = $onlythese;
    }
    $options = array();
    foreach(plugins_installed('artefact') as $plugin) {
        if ($onlythese !== false && empty($onlytheseplugins[$plugin->name])) {
            continue;
        }
        safe_require('artefact', $plugin->name);
        $pluginclassname = generate_class_name('artefact', $plugin->name);
        if (!call_static_method($pluginclassname, 'has_progressbar_options')) {
            continue;
        }

        $artefactoptions = array();
        $names = call_static_method($pluginclassname, 'get_artefact_types');
        foreach ($names as $name) {
            if ($onlythese !== false && empty($onlytheseplugins[$plugin->name][$name])) {
                continue;
            }
            // check if any of the artefact types want to opt out
            if (call_static_method('ArtefactType' . ucfirst($name), 'is_allowed_in_progressbar') == false) {
                continue;
            }
            $record = new stdClass();
            $record->name = $name;
            $record->title = call_static_method('ArtefactType' . ucfirst($name), 'get_title_progressbar');
            if (!$record->title) {
                $record->title = ucfirst(get_string($name, 'artefact.' . $plugin->name));
            }
            $record->plugin = call_static_method($pluginclassname, 'get_plugin_name');
            $record->active = (method_exists($pluginclassname, 'is_active')) ? call_static_method($pluginclassname, 'is_active') : true;
            $record->iscountable = call_static_method('ArtefactType' . ucfirst($name), 'is_countable_progressbar');
            $record->ismeta = call_static_method('ArtefactType' . ucfirst($name), 'is_metaartefact');
            $artefactoptions[$name] = $record;
        }
        // add any special cases
        if (is_array($specials = call_static_method($pluginclassname, 'progressbar_additional_items'))) {
            foreach ($specials as $special) {
                if ($onlythese !== false && empty($onlytheseplugins[$plugin->name][$special->name])) {
                    continue;
                }
                $special->ismeta = true;
                $artefactoptions[$special->name] = $special;
                if (!$special->active) {
                    unset($artefactoptions[$special->name]);
                }
            }
        }

        if ($artefactoptions) {
            $options[$plugin->name] = $artefactoptions;
        }
    }

    // Put the core artefact types into the order that makes the most sense.
    // 3rd party ones will be placed at the end of the list in alphabetical order
    uksort($options, function($item1, $item2) {
        static $expectedorder = array(
                'internal',
                'resume',
                'plans',
                'blog',
                'file',
                'social'
        );

        $val1 = array_search($item1, $expectedorder);
        $val2 = array_search($item2, $expectedorder);
        if ($val1 === false) {
            if ($val2 === false) {
                // Neither one is core, sort alphabetically
                return strcmp($item1, $item2);
            }
            else {
                return 1;
            }
        }
        else {
            if ($val2 === false) {
                return -1;
            }
            else {
                return ($val1 - $val2);
            }
        }
    });

    // An opportunity for users to override this sort order (and maybe accomodate 3rd party plugins)
    if (function_exists('local_progressbar_sortorder')) {
        $options = local_progressbar_sortorder($options);
    }

    return $options;
}

/**
 * Dealing with things to count in progressbar that are not true artefacts
 * and therefore are not countable by adding up how many instances exist in
 * the artefact table. Or if you want to count an artefact differently.
 * For example: Social -> Make a friend
 *
 * @param string $plugin name of artefact plugin
 * @param array $onlythese (optional) An array of items from artefact_get_progressbar_items, indicating which to include
 * @return array of objects each containing artefacttype, completed
 * (where completed represents the number completed)
 */
function artefact_get_progressbar_metaartefacts($plugin, $onlythese = false) {

    $results = array();
    $classname = generate_class_name('artefact', $plugin);

    // Check the artefacttypes to see if they have a special metaartefact count
    $names = call_static_method($classname, 'get_artefact_types');
    foreach ($names as $name) {
        if (!array_key_exists($name, $onlythese)) {
            continue;
        }
        $is_metaartefact = call_static_method('ArtefactType' . ucfirst($name), 'is_metaartefact');
        if ($is_metaartefact) {
            $meta = call_user_func($classname . '::progressbar_metaartefact_count', $name);
            if (is_object($meta)) {
                array_push($results, $meta);
            }
        }
    }

    // Also check the special artefacts
    if (is_array($specials = call_static_method($classname, 'progressbar_additional_items'))) {
        foreach ($specials as $special) {
            if (!array_key_exists($special->name, $onlythese)) {
                continue;
            }
            if (empty($special->is_metaartefact)) {
                // check to see if it can have mataartefact count
                $special->is_metaartefact = call_static_method('ArtefactType' . ucfirst($special->name), 'is_metaartefact');
            }
            if (!empty($special->is_metaartefact)) {
                // Now check if they have a special metaartefact count
                $meta = call_user_func($classname . '::progressbar_metaartefact_count', $special->name);
                if (is_object($meta)) {
                    array_push($results, $meta);
                }
            }
        }
    }
    return $results;
}
/**
 * Helper function to allow for attaching / detaching files via pieform artefactchooser
 *
 * @param object  $instance    The class object that contains the attach() and detach() functions
 * @param array   $values      The pieform submitted values array from filebrowser
 * @param string  $id          Optional: The id of the thing we want to attach/detach artefact to/from
 * @param boolean $mailsent    Optional: If we are not allowed to attach artefacts after mail is sent
 * @param boolean $publish     Optional: Check if current user is allowed to publish the artefacts
 */
function update_attachments($instance, $values, $id=null, $mailsent=false, $publish=false) {
    global $USER;

    $old = $instance->attachment_id_list($id);
    $new = is_array($values) ? $values : array();

    if ($publish) {
        // only allow the attaching of files that exist and are editable by user
        foreach ($new as $key => $fileid) {
            $file = artefact_instance_from_id($fileid);
            if (!($file instanceof ArtefactTypeFile) || !$USER->can_publish_artefact($file)) {
                unset($new[$key]);
            }
        }
    }
    if ($id) {
        // We are attaching the artefact to a non-artefact parent as defined by the $id
        if (!empty($new) || !empty($old)) {
            foreach ($old as $o) {
                if (!in_array($o, $new)) {
                    try {
                        $instance->detach($id, $o);
                    }
                    catch (ArtefactNotFoundException $e) {}
                }
            }
            foreach ($new as $n) {
                if (!in_array($n, $old)) {
                    try {
                        if (empty($mailsent)) {
                            $instance->attach($id, $n);
                        }
                    }
                    catch (ArtefactNotFoundException $e) {}
                }
            }
        }
    }
    else {
        // attaching artefact to parent artefact
        if (!empty($new) || !empty($old)) {
            foreach ($old as $o) {
                if (!in_array($o, $new)) {
                    try {
                        $instance->detach($o);
                    }
                    catch (ArtefactNotFoundException $e) {}
                }
            }
            foreach ($new as $n) {
                if (!in_array($n, $old)) {
                    try {
                        $instance->attach($n);
                    }
                    catch (ArtefactNotFoundException $e) {}
                }
            }
        }
    }
    return $new;
}
