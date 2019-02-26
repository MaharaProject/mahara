<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . '/lib/htmloutput.php');
define('MENUITEM', 'manageinstitutions/institutions');

$institution = param_variable('i');
$plugin      = param_variable('p');
$add         = param_boolean('add');
$edit        = param_boolean('edit');
$json        = param_boolean('j');
$instanceid  = param_variable('id', 0);

define('TITLE', get_string($plugin . 'config', 'auth.' . $plugin));

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
        $authinstance->active       = 1;
        $authinstance->id           = insert_record('auth_instance', $authinstance, 'id', true);
        json_reply(false, array('id' => $authinstance->id, 'name' => ucfirst($authinstance->authname), 'authname' => $authinstance->authname));
        exit;
    }

    $form = call_static_method($classname, 'get_instance_config_options', $institution, $instanceid);
    $form['name'] = 'auth_config';
    $form['plugintype'] = 'auth';
    $form['pluginname'] = strtolower($plugin);

    $form['elements']['submit'] = array(
        'type' => 'submitcancel',
        'class' => 'btn-primary',
        'value' => array(get_string('submit'), get_string('cancel')),
        'goto'  => get_config('wwwroot') . 'admin/users/institutions.php?i=' . $institution
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

    try {
        $values = call_static_method($classname, 'validate_instance_config_options', $values, $form);
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
    $values = call_static_method($classname, 'save_instance_config_options', $values, $form);

    redirect(get_config('wwwroot') . 'admin/users/institutions.php?i=' . $values['institution']);
}

$js = <<<EOF
jQuery(function($) {
  function authloginmsgVisibility() {
      // If Parent authority is 'None'
      if ($('#auth_config_parent').val() != 0) {
        $('#auth_config_authloginmsg_container').addClass('d-none');
      }
      else {
        $('#auth_config_authloginmsg_container').removeClass('d-none');
      }
  }
  var ssoAllOptions = {
      'updateuserinfoonlogin': 'theyssoin',
      'weautocreateusers': 'theyssoin',
      'theyautocreateusers': 'wessoout',
      'weimportcontent': 'theyssoin'
  };

  function updateSsoOptions() {
      var current = $('#auth_config_ssodirection').val();
      for (var opt in ssoAllOptions) {
          if (ssoAllOptions[opt] == current) {
              $('#auth_config_' + opt + '_container').removeClass('d-none');
          }
          else {
            $('#auth_config_' + opt + '_container').addClass('d-none');
          }
      }
  }

  if ($('#auth_config_parent').length) {
    $('#auth_config_parent').on('change', authloginmsgVisibility);
    authloginmsgVisibility();
  }
  if ($('#auth_config_ssodirection').length) {
    $('#auth_config_ssodirection').on('change', updateSsoOptions);
    updateSsoOptions();
  }
});
EOF;

$institution = get_record('institution', 'name', $institution);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('SUBSECTIONHEADING', $institution->displayname);
$smarty->display('admin/users/addauthority.tpl');
