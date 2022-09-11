<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
define('INTERNAL', 1);
define('JSON', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutions', 'admin'));

$institution = param_variable('i');
$plugin      = param_variable('p');
$add         = param_boolean('add');
$edit        = param_boolean('edit');
$instanceid  = param_variable('id', 0);

// IF WE'RE EDITING OR CREATING AN AUTHORITY:
$webservice = false;
if ($institution && $plugin) {
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
    try {
        safe_require('auth', strtolower($plugin));
        $has_instance_config = call_static_method($classname, 'has_instance_config');
    }
    catch (Exception $e) {
        // this is a custom webservice plugin without a defined class
        $has_instance_config = false; // config handled in webservices section
        $webservice = true;
    }

    if (false == $has_instance_config && $add) {

        // We've been asked to add an instance of an auth plugin that has no
        // config options. We've been called by an AJAX request, so we just
        // add the instance and generate an acknowledgement.
        $authinstance = new stdClass();

        // Get the auth instance with the highest priority number (which is
        // the instance with the lowest priority).
        // TODO: rethink 'priority' as a fieldname... it's backwards!!
        $lastinstance = get_records_array('auth_instance', 'institution', $institution, 'priority DESC', '*', '0', '1');

        if ($lastinstance == false) {
            $authinstance->priority = 0;
        } else {
            $authinstance->priority = $lastinstance[0]->priority + 1;
        }

        $authinstance->instancename = $plugin;
        $authinstance->institution  = $institution;
        $authinstance->authname     = $webservice ? 'webservice' : $plugin;
        $authinstance->active       = 1;
        $authinstance->id           = insert_record('auth_instance', $authinstance, 'id', true);
        json_reply(false, array('id' => $authinstance->id, 'name' => ucfirst($authinstance->instancename), 'authname' => $authinstance->authname));
        exit;
    }

    $form = call_static_method($classname, 'get_instance_config_options', $institution, $instanceid);
    if (isset($form['error'])) {
        json_reply(false, array('pluginname' => strtolower($plugin),
                                'html' => $form['error'])
        );
        exit;
    }
    $form['name'] = 'auth_config';
    $form['action'] = get_config('wwwroot') . 'admin/users/addauthority.php';
    $form['plugintype'] = 'auth';
    $form['pluginname'] = strtolower($plugin);
    $form['jsform'] = true;
    $form['jssuccesscallback'] = 'authlist_success';
    $form['jserrorcallback'] = 'authlist_error';
    $form['elements']['i'] = array('type' => 'hidden', 'value' => $institution);
    $form['elements']['p'] = array('type' => 'hidden', 'value' => $plugin);
    $form['elements']['add'] = array('type' => 'hidden', 'value' => $add);
    $form['elements']['edit'] = array('type' => 'hidden', 'value' => $edit);
    $form['elements']['id'] = array('type' => 'hidden', 'value' => $instanceid);

    $form['elements']['submit'] = array(
        'type' => 'submitcancel',
        'subclass' => array('btn-primary'),
        'value' => array(get_string('submit'), get_string('cancel')),
        'goto'  => get_config('wwwroot') . 'admin/users/institutions.php?i=' . $institution,
    );

    $pieform = pieform_instance($form);
    $html = $pieform->build();
    // TODO: The hacky code to extract the Javascript from the Pieforms has been
    // copy-pasted from BlockInstance->build_configure_form(). At some point we should
    // refactor it into shared code.
    //
    // We probably need a new version of $pieform->build() that separates out the js
    // Temporary evil hack:
    if (preg_match('/<script type="(text|application)\/javascript">(new Pieform\(.*\);)<\/script>/', $html, $matches)) {
        $js = "var pf_{$form['name']} = " . $matches[2] . "pf_{$form['name']}.init();";
    }
    else if (preg_match('/<script>(new Pieform\(.*\);)<\/script>/', $html, $matches)) {
        $js = "var pf_{$form['name']} = " . $matches[1] . "pf_{$form['name']}.init();";
    }
    else {
        $js = '';
    }

    json_reply(
        false,
        array(
            'html' => $html,
            'javascript' => $js,
            'pluginname' => strtolower($plugin),
        )
    );
}

function auth_config_validate(Pieform $form, $values) {
    $plugin = $values['authname'];
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));

    try {
        $values = call_static_method($classname, 'validate_instance_config_options', $values, $form);
    }
    catch (Exception $e) {
        if (!$form->has_errors()) {
            $form->json_reply(PIEFORM_ERR, "An unknown error occurred while processing this form");
        }
    }
}

function auth_config_submit(Pieform $form, $values) {
    global $SESSION;
    $plugin = $values['authname'];
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));

    safe_require('auth', strtolower($plugin));
    $values = call_static_method($classname, 'save_instance_config_options', $values, $form);

    $form->json_reply(
        PIEFORM_OK,
        array(
            'id' => $values['instance'],
            'name' => $values['instancename'],
            'authname' => $values['authname'],
            'new' => (int) array_key_exists('create', $values) && $values['create']
        )
    );
}
