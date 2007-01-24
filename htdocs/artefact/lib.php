<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once('artefact.php');

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
     * This function returns the name of the plugin.
     * @abstract
     * @return string
     */
    public static abstract function get_plugin_name();


    /**
     * Gets a list of top level artefact types, used in the view creation wizard
     */
    public static abstract function get_toplevel_artefact_types(); 

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
                $this->{$field} = $value;
            }
        }

        $this->atime = time();
        $this->artefacttype = $this->get_artefact_type();
    }

    public function get_views_instances() {
        // @todo
    }
    
    public function get_views_metadata() {
        // @todo
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
     * This function returns the instances 
     * of all children of this artefact
     * If you just want the basic info, 
     * use {@link get_children_metadata} instead.
     * 
     * @param int $userid user to check watchlist for
     * @return array of instances.
     */

    public function get_children_instances_watchlist($userid) {
        $instances = array();
        if ($children = $this->get_children_metadata_watchlist($userid)) {
            foreach ($children as $child) {
                $classname = generate_artefact_class_name($child->artefacttype);
                $instance = new $classname($child->id, $child);
                $instances[] = $instance;
            }
        }
        return $instances;
    }

    /**
     * This function returns the db rows 
     * from the artefact table that have this 
     * artefact as the parent.
     * If you want instances, use {@link get_children_instances}
     * but bear in mind this will have a performance impact.
     * 
     * @param int $userid user to check watchlist for
     * @return array
     */
    public function get_children_metadata_watchlist($userid) {
        $prefix = get_config('dbprefix');
        
        $sql = 'SELECT a.* FROM ' . $prefix . 'artefact a 
                JOIN ' . $prefix . 'usr_watchlist_artefact w ON w.artefact = a.id
                WHERE w.usr = ? AND a.parent = ?';
        
        return get_records_sql_array($sql, array($userid, $this->id));
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
        return get_record('artefact','id',$this->parent);
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
            $this->mtime = time();
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
      
        if (!empty($this->dirty)) {
            $this->commit();
        }
    }
    
    public function is_container() {
        return false;
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
        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime', 'atime')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }
        if (empty($this->id)) {
            $this->id = insert_record('artefact', $fordb, 'id', true);
            if (!empty($this->parent)) {
                $this->parentdirty = true;
            }
        }
        else {
            update_record('artefact', $fordb, 'id');
        }
        activity_occurred('watchlist', (object) array('artefact' => $this->id,
                                                      'subject' => get_string('artefactmodified')));
        if (!empty($this->parentdirty)) {
            if (!empty($this->parent) && !record_exists('artefact_parent_cache', 'artefact', $this->id)) {
                $apc = new StdClass;
                $apc->artefact = $this->id;
                $apc->parent = $this->parent;
                $apc->dirty  = 1; // set this so the cronjob will pick it up and go set all the other parents.
                insert_record('artefact_parent_cache', $apc);
            }
            set_field_select('artefact_parent_cache', 'dirty', 1,
                             'artefact = ? OR parent = ?', array($this->id, $this->id));
        }
        $this->dirty = false;
        $this->deleted = false;
        $this->parentdirty = false;
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
      
        // Call delete() on children (if there are any)
        if ($children = $this->get_children_instances()) {
            foreach ($children as $child) {
                $child->delete();
            }
        }

        // Delete any references to this artefact from non-artefact places.
        delete_records_select('artefact_parent_cache', 'artefact = ? OR parent = ?', array($this->id, $this->id));
        delete_records('view_artefact', 'artefact', $this->id);
        delete_records('artefact_feedback', 'artefact', $this->id);
        delete_records('usr_watchlist_artefact', 'artefact', $this->id);
      
        // Delete the record itself.
        delete_records('artefact', 'id', $this->id);
        
        // Set flags.
        $this->dirty = false;
        $this->parentdirty = true;
        $this->deleted = true;
    }

    /**
     * render instance to given format.  This function simply switches and
     * calls one of listself(), listchildren(), render_metadata() or
     * render_full().  If a format it doesn't know about is passed in, it
     * throws an exception.  You should only need to override this if you have
     * invented some kind of new format.
     *
     * @param int $format format type (constant)
     * @param array $options options for format
     */
    public function render($format, $options) {
        switch ($format) {
        case FORMAT_ARTEFACT_LISTSELF:
            return $this->listself($options);
            
        case FORMAT_ARTEFACT_RENDERMETADATA:
            return $this->render_metadata($options);

        case FORMAT_ARTEFACT_LISTCHILDREN:
            return $this->listchildren($options);

        case FORMAT_ARTEFACT_RENDERFULL:
            return $this->render_full($options);
            
        default:
            //@todo: This should be an invalid render format exception
            throw new Exception('invalid render format');
        }
    }

    
    protected function get_metadata() {
        $data = array('title'        => $this->get('title'),
                      'type'         => get_string($this->get('artefacttype')),
                      'owner'        => display_name(optional_userobj($this->get('owner'))),
                      'created'      => format_date($this->get('ctime')),
                      'lastmodified' => format_date($this->get('mtime')));
        foreach ($data as $key => $value) {
            $data[$key] = array('name' => get_string($key),
                                'value' => $value);
        }
        return $data;
    }


    /**
     * render instance to metadata format
     * @param $options 
     * @todo: get and display artefact size.
     */
    protected function render_metadata($options) {

        $smarty = smarty();

        $smarty->assign('title', $this->get('title'));
        $smarty->assign('type', get_string($this->get('artefacttype')));
        $smarty->assign('owner', display_name(optional_userobj($this->get('owner'))));
        $smarty->assign('nicectime', format_date($this->get('ctime')));
        $smarty->assign('nicemtime', format_date($this->get('mtime')));

        return $smarty->fetch('artefact/render_metadata.tpl');

    }

    /**
     * list artefact children.  There's a default for this, but we only use it
     * if the class thinks it can render FORMAT_ARTEFACT_LISTCHILDREN. 
     *
     * @param $options 
     * @todo: use a smarty template.
     */
    protected function listchildren($options) {
        if (in_array(FORMAT_ARTEFACT_LISTCHILDREN, $this->get_render_list())) {
      
            $html = '<ul>';
            foreach ($this->get_children_instances() as $child) {
                $html .= '<li>' . $child->render(FORMAT_ARTEFACT_LISTSELF, $options) . "</li>\n";
            }
            $html .= '</ul>';
            return $html;
        }

        throw new Exception('This artefact cannot render to this format.');
    }

    /** 
     * render self
     * @param array options
     */
    protected function listself($options) {
        if (isset($options['viewid'])) {
            require_once('artefact.php');
            if (artefact_in_view($id = $this->get('id'), $options['viewid'])) {
                $title = '<a href="' . get_config('wwwroot') . 'view/view.php?view=' . $options['viewid']
                    . '&artefact=' . $id . '">' . $this->title . '</a>';
            }
        }
        if (!isset($title)) {
            $title = $this->title;
        }
        if (!empty($options['size']) && method_exists($this, 'describe_size')) {
            $title .= ' (' . $this->describe_size() . ')';
        }
        if (!empty($options['link']) && method_exists($this, 'linkself')) {
            $title .= ' (' . $this->linkself() . ')';
        }
        return $title;
    }

    /**
     * render the artefact in full.  This isn't supported by default.  You need
     * to override this method if your artefact can do this.
     *
     * @param array
     */
    protected function render_full($options) {
        // @todo This should be a proper exception of some sort.
        throw new Exception('This artefact cannot render to this format.');
    }


    /**
     * By default public feedback can be placed on all artefacts.
     * Artefact types which don't want to allow public feedback should
     * redefine this function.
     */
    public function public_feedback_allowed() {
        return true;
    }


    /**
     * returns path to icon
     * can be called statically but not defined so
     * so that can be either from instance or static.
     * @abstract 
     * @return string path to icon (relative to docroot)
     */
    public abstract function get_icon();
    

    // ******************** STATIC FUNCTIONS ******************** //

    public static function get_instances_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    public static function get_metadata_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    /**
     * returns array of formats can render to (constants)
     */
    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERMETADATA
        );
    }

    /**
     * whether a user will have exactly 0 or 1 of this artefact type
     * @abstract
     */
    public static abstract function is_singular();

    /**
     * Whether the 'note' field is for the artefact's private use
     */
    public static function is_note_private() {
        return false;
    }

    // ******************** HELPER FUNCTIONS ******************** //

    protected function get_artefact_type() {
        $classname = get_class($this);
        
        $type = strtolower(substr($classname, strlen('ArtefactType')));

        if (!record_exists('artefact_installed_type', 'name', $type)) {
            throw new InvalidArgumentException("Classname $classname not a valid artefact type");
        }

        return $type;
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
}

?>
