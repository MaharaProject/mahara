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
 * @subpackage interaction
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


/**
 * Base interaction plugin class
 * @abstract
 */
abstract class PluginInteraction extends Plugin { }


/** 
 * Base class for interaction instances
 */
abstract class InteractionInstance {

    protected $id;
    protected $title;
    protected $description;
    protected $group;
    protected $plugin; // I wanted to make this private but then get_object_vars doesn't include it.
    protected $ctime;
    protected $dirty;

    public function __construct($id=0, $data=null) {
         if (!empty($id)) {
            if (empty($data)) {
                if (!$data = get_record('interaction_instance', 'id', $id)) {
                    throw new InteractionInstanceNotFoundException(get_string('interactioninstancenotfound', 'error', $id));
                }
            }
            $this->id = $id;
        }
        else {
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
        if (empty($this->id)) {
            $this->ctime = time();
        }
        $this->plugin = $this->get_plugin();
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($this->{$field} != $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
                $this->{$field} = $value;
            }
            return true;
        }
        throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            if ($k == 'ctime') {
                $v = db_format_timestamp($v);
            }
            $fordb->{$k} = $v;
        }
        if (empty($this->id)) {
            $this->id = insert_record('interaction_instance', $fordb, 'id', true);
        }
        else {
            update_record('interaction_instance', $fordb, 'id');
        }

        // @TODO maybe handle_event here.

        $this->dirty = false;
    }

    public function delete() {
        if (empty($this->id)) {
            $this->dirty = false;
            return;
        }
        
        delete_records('interaction_instance', 'id', $this->id);

        $this->dirty = false;
    }

    public static abstract function get_plugin();
}

function interaction_check_plugin_sanity($pluginname) {

    safe_require('interaction', $pluginname);
    $classname = generate_interaction_instance_class_name($pluginname);

    if (!class_exists($classname)) {
        throw new InstallationException(get_string('classmissing', 'error', $classname, 'interaction', $pluginname));
    }
}



?>
