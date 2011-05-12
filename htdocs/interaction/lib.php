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
 * @subpackage interaction
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


/**
 * Base interaction plugin class
 * @abstract
 */
abstract class PluginInteraction extends Plugin { 

    /**
    * override this to add extra pieform elements to the edit instance form
    */
    public static abstract function instance_config_form($group, $instance=null);

    /**
    * override this to save any extra fields in the instance form.
    */
    public static abstract function instance_config_save($instance, $values);

    /*
    * override this to perform extra validation
    * public abstract function instance_config_validate(Pieform $form,  $values);
    */


    public static function instance_config_base_form($plugin, $group, $instance=null) {
        global $USER;
        return array(
            'id' => array(
                'type'  => 'hidden',
                'value' => (isset($instance) ? $instance->get('id') : 0),
            ),
            'plugin' => array(
                'type'  => 'hidden',
                'value' => $plugin,
            ),
            'group' => array(
                'type'  => 'hidden',
                'value' => $group->id
            ),
            'justcreated' => array(
                'type' => 'hidden',
                'value' => !isset($instance),
            ),
            'creator' => array(
                'type' => 'hidden',
                'value' => (isset($instance) ? $instance->get('creator') : $USER->get('id'))
            ),
            'title' => array(
                'type'         => 'text',
                'title'        => get_string('title', 'group'),
                'defaultvalue' => (isset($instance) ? $instance->get('title') : ''),
                'rules'        => array(
                    'required' => true,
                )
            ),
            'description' => array(
                'type'         => 'wysiwyg',
                'title'        => get_string('description'),
                'rows'         => 10,
                'cols'         => 60,
                'defaultvalue' => (isset($instance) ? $instance->get('description') : ''),
                'rules'        => array(
                    'required' => true,
                    'maxlength' => 65536,
                )
            ),
        );
    }

}


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
    protected $creator;
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
       
        set_field('interaction_instance', 'deleted', 1, 'id', $this->id);

        $this->dirty = false;
    }

    public static abstract function get_plugin();

    public abstract function interaction_remove_user($userid);

}

function interaction_check_plugin_sanity($pluginname) {

    safe_require('interaction', $pluginname);
    $classname = generate_interaction_instance_class_name($pluginname);

    if (!class_exists($classname)) {
        throw new InstallationException(get_string('classmissing', 'error', $classname, 'interaction', $pluginname));
    }
}

function interaction_instance_from_id($id) {
    if (!$interaction = get_record('interaction_instance', 'id', $id)) {
        throw new InteractionInstanceNotFoundException(get_string('interactioninstancenotfound', 'error', $id));
    }
    $classname = generate_interaction_instance_class_name($interaction->plugin);
    safe_require('interaction', $interaction->plugin);
    return new $classname($id, $interaction);
}

function edit_interaction_validation(Pieform $form, $values) {
    safe_require('interaction', $values['plugin']);
    if (is_callable(array(generate_class_name('interaction', $values['plugin'])), 'instance_config_validate')) {
        call_static_method(generate_class_name('interaction', $values['plugin']), 'instance_config_validate', $form, $values);
    }
}

function edit_interaction_submit(Pieform $form, $values) {
    safe_require('interaction', $values['plugin']);
    $classname = generate_interaction_instance_class_name($values['plugin']);
    $instance = new $classname($values['id']);
    $instance->set('creator', $values['creator']);
    $instance->set('title', $values['title']);
    $instance->set('description', $values['description']);
    if (empty($values['id'])) {
        $instance->set('group', $values['group']);
    }
    $instance->commit();
    call_static_method(generate_class_name('interaction', $values['plugin']), 'instance_config_save', $instance, $values);
    global $SESSION;
    $SESSION->add_ok_msg(get_string('interactionsaved', 'group', get_string('name', 'interaction.' . $values['plugin'])));
    $returnto = param_alpha('returnto', 'view');
    if ($returnto == 'index') {
        redirect('/interaction/' . $values['plugin'] . '/index.php?group=' . $instance->get('group'));
    }
    else {
        redirect('/interaction/' . $values['plugin'] . '/view.php?id=' . $instance->get('id'));
    }
}

function delete_interaction_submit(Pieform $form, $values) {
   
    require_once(get_config('docroot') . 'interaction/lib.php');
    $instance = interaction_instance_from_id($values['id']);

    $instance->delete();
    global $SESSION;
    $SESSION->add_ok_msg(get_string('interactiondeleted', 'group', get_string('name', 'interaction.' . $instance->get('plugin'))));
    redirect('/interaction/' . $instance->get('plugin') . '/index.php?group=' . $instance->get('group'));

}

/**
 * creates the information for the interaction_sideblock
 *
 * @param int $groupid the group the sideblock is for
 * @param boolean (optional) $membership whether the user is a member
 * @return array containing indices 'name', 'weight', 'data'
 */
function interaction_sideblock($groupid, $membership=true) {
    $interactiontypes = array_flip(
        array_map(
            create_function('$a', 'return $a->name;'),
            plugins_installed('interaction')
        )
    );

    if (!$interactions = get_records_select_array('interaction_instance',
        '"group" = ? AND deleted = ?', array($groupid, 0),
        'plugin, ctime', 'id, plugin, title')) {
        $interactions = array();
    }

    foreach ($interactions as $i) {
        if (!is_array($interactiontypes[$i->plugin])) {
            $interactiontypes[$i->plugin] = array();
        }
        $interactiontypes[$i->plugin][] = $i;
    }

    // Sort them according to how the plugin wants them sorted
    if ($interactiontypes) {
        foreach ($interactiontypes as $plugin => &$interactions) {
            safe_require('interaction', $plugin);
            $classname = generate_class_name('interaction', $plugin);
            if (method_exists($classname, 'sideblock_sort')) {
                $interactions = call_static_method($classname, 'sideblock_sort', $interactions);
            }
        }
    }

    $data = array(
        'group' => $groupid,
        'interactiontypes' => $interactiontypes,
        'membership' => $membership,
    );

    // Add a sideblock for group interactions
    return array(
        'name' => 'groupinteractions',
        'weight' => -5,
        'data' => $data
    );
}
