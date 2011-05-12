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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutions', 'admin'));
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . '/lib/htmloutput.php');

// CHECK FOR CANCEL BEFORE THE 'REQUIRED' PARAMS:
$cancel = param_boolean('c');

if ($cancel) {
    execute_javascript_and_close();
}

// NOT CANCELLING? OK - OTHER PARAMS THEN:
$institution = param_variable('i');
$plugin      = param_variable('p');
$add         = param_boolean('add');
$edit        = param_boolean('edit');
$json        = param_boolean('j');
$instanceid  = param_variable('id', 0);

// IF WE'RE EDITING OR CREATING AN AUTHORITY:
if ($institution && $plugin) {
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
    safe_require('auth', strtolower($plugin));

    $has_instance_config = call_static_method($classname, 'has_instance_config');
    if (false == $has_instance_config && $add) {

        // We've been asked to add an instance of an auth plugin that has no
        // config options. We've been called by an AJAX request, so we just
        // add the instance and generate an acknowledgement.

        // The session key has not been checked yet, because this page doesn't
        // define JSON
        try {
            form_validate(param_alphanum('sesskey', null));
        }
        catch (UserException $e) {
            json_reply(true, $e->getMessage());
        }

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
        $authinstance->authname     = $plugin;
        $authinstance->id           = insert_record('auth_instance', $authinstance, 'id', true);
        json_reply(false, array('id' => $authinstance->id, 'name' => ucfirst($authinstance->authname), 'authname' => $authinstance->authname));
        exit;
    }

    $authclass = new $classname();
    $form = $authclass->get_instance_config_options($institution, $instanceid);
    $form['name'] = 'auth_config';
    $form['plugintype'] = 'auth';
    $form['pluginname'] = strtolower($plugin);

    $form['elements']['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('submit'), get_string('cancel')),
        'goto'  => 'addauthority.php?c=1'
    );

    $form = pieform($form);
    $smarty = smarty();
    if ($add) {
        $smarty->assign('PAGETITLE', get_string('addauthority', 'auth'));
    } else {
        $smarty->assign('PAGETITLE', get_string('editauthority', 'auth'));
    }
    $smarty->assign('auth_imap_form', $form);
}

function auth_config_validate(Pieform $form, $values) {
    $plugin = $values['authname'];
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));

    if (!method_exists($classname, 'validate_config_options')) {
        return;
    }
    safe_require('auth', strtolower($plugin));

    try {
        $values = call_static_method($classname, 'validate_config_options', $values, $form);
    } catch (Exception $e) {
        if (!$form->has_errors()) {
            $form->set_error('instancename', "An unknown error occurred while processing this form");
        }
    }
}

function auth_config_submit(Pieform $form, $values) {
    global $SESSION;
    $plugin = $values['authname'];
    $classname = 'PluginAuth' . ucfirst(strtolower($plugin));
    safe_require('auth', strtolower($plugin));
    try {
        $values = call_static_method($classname, 'save_config_options', $values, $form);
    } catch (Exception $e) {
        log_info($e->getMessage());
        log_info($e->getTrace());
        $SESSION->add_error_msg("An error occurred while processing this form: " . $e->getMessage());
        redirect('/admin/users/addauthority.php?'. $_SERVER['QUERY_STRING']);
    }

    if (array_key_exists('create', $values) && $values['create']) {
        execute_javascript_and_close('window.opener.addAuthority('.$values['instance'].', "'.addslashes($values['instancename']).'", "'.$values['authname'].'");');
    } else {
        execute_javascript_and_close();
    }
    exit;
}

$js = <<<EOF
function authloginmsgVisibility() {
    // If Parent authority is 'None'
    if ($('auth_config_parent').value != 0) {
      addElementClass('auth_config_authloginmsg_container', 'hidden');
      addElementClass(nextSiblingTR($('auth_config_authloginmsg_container')), 'hidden');
    }
    else {
      removeElementClass('auth_config_authloginmsg_container', 'hidden');
      removeElementClass(nextSiblingTR($('auth_config_authloginmsg_container')), 'hidden');
    }
}
function nextSiblingTR(node) {
    while (node.nextSibling.tagName != 'TR') {
        node = node.nextSibling;
    }
    return node.nextSibling;
}
var ssoAllOptions = {
    'updateuserinfoonlogin': 'theyssoin',
    'weautocreateusers': 'theyssoin',
    'theyautocreateusers': 'wessoout',
    'weimportcontent': 'theyssoin'
};
function updateSsoOptions() {
    var current = $('auth_config_ssodirection').value;
    for (var opt in ssoAllOptions) {
        if (ssoAllOptions[opt] == current) {
            removeElementClass('auth_config_' + opt + '_container', 'hidden');
        }
        else {
            addElementClass('auth_config_' + opt + '_container', 'hidden');
        }
    }
}
addLoadEvent(
    function() {
        if ($('auth_config_parent')) {
            connect('auth_config_parent', 'onchange', authloginmsgVisibility);
            authloginmsgVisibility();
        }
        if ($('auth_config_ssodirection')) {
            connect('auth_config_ssodirection', 'onchange', updateSsoOptions);
            updateSsoOptions();
        }
    }
);
EOF;

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/users/addauthority.tpl');
