<?php
/**
 *
 * @package    mahara
 * @subpackage interaction
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Helper interface to hold PluginInteraction's static abstract methods
 */
interface IPluginInteraction {
    /**
    * override this to add extra pieform elements to the edit instance form
    */
    public static function instance_config_form($group, $instance=null);

    /**
    * override this to save any extra fields in the instance form.
    */
    public static function instance_config_save($instance, $values);

    /*
    * override this to perform extra validation
    * public function instance_config_validate(Pieform $form,  $values);
    */
}

/**
 * Base interaction plugin class
 * @abstract
 */
abstract class PluginInteraction extends Plugin implements IPluginInteraction {

    public static function get_plugintype_name() {
        return 'interaction';
    }

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
                    'maxlength' => 1000000,
                )
            ),
        );
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
 * Helper interface to hold InteractionInstance's abstract static methods
 */
interface IInteractionInstance {
    public static function get_plugin();
}

/**
 * Base class for interaction instances
 */
abstract class InteractionInstance implements IInteractionInstance {

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
        $fordb = new stdClass();
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

    public abstract function interaction_remove_user($userid);

    public abstract function attach($id, $attachmentid);

    public abstract function detach($id, $attachmentid=null);

    public static function attached_id_list($attachmentid) {
        return array();
    }

    public static function attachment_id_list($post) {
        return array();
    }
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
