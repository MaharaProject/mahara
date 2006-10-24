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
 * @subpackage core
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
     * @return array
     */
    public static abstract function get_artefact_types();

    /**
     * This function returns the name of the plugin.
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

    /** 
     * Constructer. 
     * If an id is supplied, will query the database
     * to build up the basic information about the object.
     * If an id is not supplied, we just create an empty
     * artefact, ready to be filled up
     * @param int $id artefact.id
     */
    public function __construct($id=0) {
        if (!empty($id)) {
            if (!$record = get_record('artefact','id',$id)) {
                throw new ArtefactNotFoundException("Artefact with id $id not found");
            }
            foreach ((array)$record as $field => $value) {
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

    public function get_children_instances() {
        // @todo 
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
        return get_records('artefact','parentid',$this->id);
    }
            
    public function get_parent_instance() {
        // @todo
    }

    /** 
     * This function returns the db row 
     * (if there is one) of the parent
     * artefact for this instance.
     * If you want the instance, use 
     * {@link get_parent_instance} instead.
     * 
     * @return object (db row)
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

    public abstract function commit();
    
    public abstract function delete();

    public abstract function render($format, $options);

    public abstract function get_icon();
    

    // ******************** STATIC FUNCTIONS ******************** //

    public static function get_instances_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    public static function get_metadata_by_userid($userid, $order, $offset, $limit) {
        // @todo
    }

    public static abstract function get_render_list($format);

    public static abstract function can_render_to($format);
}



        
?>
