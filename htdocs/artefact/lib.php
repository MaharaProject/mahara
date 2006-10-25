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
 * @author     Your Name <you@example.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/** 
 * Exception - artefact not found 
 */
class ArtefactNotFoundException extends Exception {}

/**
 * Exception - trying to get/set a field that doesn't exist
 */
class UndefinedArtefactFieldException extends Exception {}

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
     * This function handles an event that has been
     * generated, that the plugin has asked to be
     * subscribed to
     * @param object $event
     * @param array $options
     * @todo finish documenting the args when we have some egs.
     */
    public static function handle_event($event, $options) { }

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
    
    private $_dirty;
    protected $id;
    protected $type;
    protected $container;
    protected $parentid;
    protected $ctime;
    protected $mtime;
    protected $vtime;
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
            foreach ((array)$data as $field => $value) {
                if (property_exists($field)) {
                    $this->{$field} = $value;
                }
            }
        }
        else {
            $this->ctime = time();
        }
    }

    public function get_views_instances() {
        // @todo
    }
    
    public function get_views_metadata() {
        // @todo
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
                    $classname = $child->artefacttype;
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
            $this->childrenmetadata = get_records('artefact', 'parentid', $this->id);
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
                $classname = $parent->artefacttype;
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
        return get_record('artefact','id',$this->parentid);
    }

    public function get($field) {
        if (!property_exists($field)) {
            throw new UndefinedArtefactFieldException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($field)) {
            $this->{$field} = $value;
            $this->_dirty = true;
            return true;
        }
        throw new UndefinedArtefactFieldException("Field $field wasn't found in class " . get_class($this));
    }
    
    public function __destruct() {
        $this->commit();
    }
    
    public function is_container() {
        return false;
    }

    /**
     * Saves any changes to the database
     * @abstract
     */
    public abstract function commit();
    
    /**
     * Deletes current instance
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
    public static abstract function get_render_list($format);

    /**
     * returns boolean for can render to given format
     * @abstract
     */
    public static abstract function can_render_to($format);
}



        
?>
