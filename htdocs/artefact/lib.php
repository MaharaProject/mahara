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
define('ARTEFACT_FORMAT_LISTITEM', 1);


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
     * This function returns an array of crons it wants to have run
     * Each item should be a StdtClass object containing - 
     * - name (will have artefact.$pluginname added for uniqueness)
     * - script to hit relative to the documentroot
     * NOTE THAT each plugin that implements this should ship with a 
     * .htaccess that denies access to all cron scripts 
     */
    public static function get_cron_options() { 
        return array();
    }

    /** 
     * This function returns an array of events to subscribe to
     * by unique name. 
     * If an event the plugin is trying to subscribe to is unknown by the
     * core, an exception will be thrown.
     * @return array
     */
    public static function get_event_subscriptions() {
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
                    throw new ArtefactNotFoundException("Artefact with id $id not found");
                }
            }
            $this->id = $id;
        }
        else {
            $this->ctime = time();
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
        $this->artefacttype = $this->get_artefact_type();
    }

    public function get_views_instances() {
        // @todo
    }
    
    public function get_views_metadata() {
        // @todo
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
     */
    public function __destruct() {
        if (!empty($this->dirty)) {
            $this->commit();
        }
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
    }
    
    public function is_container() {
        return false;
    }

    /** 
     * As commit is abstract, subclasses
     * can use this as a helper to update
     * the contents of the artefact table
     */
    
    protected function commit_basic() {
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
        }
        else {
            update_record('artefact', $fordb, 'id');
        }
        $this->dirty = false;
    }


    /**
     * Saves any changes to the database
     * for basic commits, use {@link commit_basic}
     * @abstract
     */
    public abstract function commit();
    

    /** 
     * As delete is abstract, subclasses
     * can use this to clear out the artefact
     * table and set the parentdirty flag
     */

    protected function delete_basic() {
        delete_records('artefact', 'id', $this->id);
        $this->dirty = false;
        $this->parentdirty = true;
    }

    /**
     * Deletes current instance
     * you MUST set $this->parentdirty to true
     * when delete is called.
     * for basic delete, use {@link delete_basic}
     * @abstract
     */
    public abstract function delete();

    /**
     * render instance to given format
     * @param int $format format type (constant)
     * @param array $options options for format
     */
    public abstract function render($format, $options);

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
     * @abstract
     */
    public static abstract function get_render_list();

    /**
     * returns boolean for can render to given format
     * @abstract
     */
    public static abstract function can_render_to($format);


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

// helper functions for artefacts in general

function artefact_check_plugin_sanity($pluginname) {
    $classname = generate_class_name('artefact', $pluginname);
    safe_require('artefact', $pluginname);
    $types = call_static_method($classname, 'get_artefact_types');
    foreach ($types as $type) {
        $typeclassname = generate_artefact_class_name($type);
        if (get_config('installed')) {
            if ($taken = get_record_select('artefact_installed_type', 'name = ? AND plugin != ?', 
                                           array($type, $pluginname))) {
                throw new InstallationException("type $type is already taken by another plugin (" . $taken->plugin . ")");
            }
        }
        if (!class_exists($typeclassname)) {
            throw new InstallationException("class $typeclassname for type $type in plugin $pluginname was missing");
        }
    }
}

        
?>
