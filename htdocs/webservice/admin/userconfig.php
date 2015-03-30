<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/webservices');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
require_once('pieforms/pieform.php');

$suid  = param_variable('suid', '');
// lookup user cancelled
if ($suid == 'add') {
    redirect('/webservice/admin/index.php?open=webservices_user');
}

$dbserviceuser = get_record('external_services_users', 'id', $suid);
if (empty($dbserviceuser)) {
    $SESSION->add_error_msg(get_string('invalidserviceuser', 'auth.webservice'));
    redirect('/webservice/admin/index.php?open=webservices_user');
}

$services = get_records_array('external_services', 'restrictedusers', 1);
$sopts = array();
foreach ($services as $service) {
    $sopts[$service->id] = $service->name;
}

$dbuser = get_record('usr', 'id', $dbserviceuser->userid);
$function_list = array();
if (isset($dbserviceuser->externalserviceid)) {
    $dbservice = get_record('external_services', 'id', $dbserviceuser->externalserviceid);
    $defaultserviceid = $dbserviceuser->externalserviceid;
    $serviceenabled = $dbservice->enabled;
    $restrictedusers = $dbservice->restrictedusers;
    $functions = get_records_array('external_services_functions', 'externalserviceid', $dbserviceuser->externalserviceid);
    if ($functions) {
        foreach ($functions as $function) {
            $dbfunction = get_record('external_functions', 'name', $function->functionname);
            $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
        }
    }
}
else {
    $serviceenabled = 0;
    $defaultserviceid = array_pop(array_keys($sopts));
    $restrictedusers = 0;
}

$serviceuser_details =
    array(
        'name'             => 'allocate_webservice_users',
        'successcallback'  => 'allocate_webservice_users_submit',
        'validatecallback' => 'allocate_webservice_users_validate',
        'jsform'           => true,
        'renderer'         => 'multicolumntable',
        'elements'   => array(
                        'suid' => array(
                            'type'  => 'hidden',
                            'value' => $dbserviceuser->id,
                        ),
                ),
        );

$dbinstitution = get_record('institution', 'name', $dbserviceuser->institution);
$serviceuser_details['elements']['institution'] = array(
    'type'         => 'html',
    'title'        => get_string('institution'),
    'value'        => $dbinstitution->displayname,
);

$searchicon = $THEME->get_image_url('btn-search.gif', 'auth/webservice');

if ($USER->is_admin_for_user($dbuser->id)) {
    $user_url = get_config('wwwroot') . 'admin/users/edit.php?id=' . $dbuser->id;
}
else {
    $user_url = get_config('wwwroot') . 'user/view.php?id=' . $dbuser->id;
}
$serviceuser_details['elements']['username'] = array(
    'type'        => 'html',
    'title'       => get_string('username'),
    'value'       =>  '<a href="' . $user_url . '">' . $dbuser->username . '</a>',
);

$serviceuser_details['elements']['user'] = array(
    'type'        => 'hidden',
    'value'       => $dbuser->id,
);

$services = get_records_array('external_services');
$sopts = array();
foreach ($services as $service) {
    $sopts[$service->id] = $service->name;
}

$serviceuser_details['elements']['service'] = array(
    'type'         => 'select',
    'title'        => get_string('servicename', 'auth.webservice'),
    'options'      => $sopts,
    'defaultvalue' => $defaultserviceid,
);

$serviceuser_details['elements']['enabled'] = array(
    'title'        => get_string('enabled'),
    'defaultvalue' => (($serviceenabled == 1) ? 'checked' : ''),
    'type'         => 'checkbox',
    'disabled'     => true,
);

$serviceuser_details['elements']['restricted'] = array(
    'title'        => get_string('restrictedusers', 'auth.webservice'),
    'defaultvalue' => (($restrictedusers == 1) ? 'checked' : ''),
    'type'         => 'checkbox',
    'disabled'     => true,
);

$serviceuser_details['elements']['functions'] = array(
    'title'        => get_string('functions', 'auth.webservice'),
    'value'        =>  implode(', ', $function_list),
    'type'         => 'html',
);

$serviceuser_details['elements']['wssigenc'] = array(
    'defaultvalue' => (($dbserviceuser->wssigenc == 1) ? 'checked' : ''),
    'type'         => 'checkbox',
    'disabled'     => false,
    'title'        => get_string('wssigenc', 'auth.webservice'),
);

$serviceuser_details['elements']['publickey'] = array(
    'type' => 'textarea',
    'title' => get_string('publickey', 'admin'),
    'defaultvalue' => $dbserviceuser->publickey,
    'rows' => 15,
    'cols' => 90,
);

$serviceuser_details['elements']['publickeyexpires']= array(
    'type' => 'html',
    'title' => get_string('publickeyexpires', 'admin'),
    'value' => ($dbserviceuser->publickeyexpires ? format_date($dbserviceuser->publickeyexpires, 'strftimedatetime', 'formatdate', 'auth.webservice') : format_date(time(), 'strftimedatetime', 'formatdate', 'auth.webservice')),
);

$serviceuser_details['elements']['submit'] = array(
    'type'  => 'submitcancel',
    'value' => array(get_string('save'), get_string('back')),
    'goto'  => get_config('wwwroot') . 'webservice/admin/index.php?open=webservices_user',
);

$elements = array(
        // fieldset for managing service function list
        'serviceusers_details' => array(
                            'type' => 'fieldset',
                            'legend' => get_string('serviceusername', 'auth.webservice', $dbuser->username),
                            'elements' => array(
                                'sflist' => array(
                                    'type'         => 'html',
                                    'value' =>     pieform($serviceuser_details),
                                )
                            ),
                            'collapsible' => false,
                        ),
    );

$form = array(
    'renderer' => 'table',
    'type' => 'div',
    'id' => 'maintable',
    'name' => 'tokenconfig',
    'jsform' => true,
    'successcallback' => 'allocate_webservice_users_submit',
    'validatecallback' => 'allocate_webservice_users_validate',
    'elements' => $elements,
);


$form = pieform($form);

$smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
safe_require('auth', 'webservice');
$webservice_menu = PluginAuthWebservice::menu_items(MENUITEM);
$smarty->assign('TERTIARYMENU', $webservice_menu);
$smarty->assign('suid', $dbserviceuser->id);
$smarty->assign('form', $form);
$heading = get_string('users', 'auth.webservice');
$smarty->assign('PAGEHEADING', $heading);
$smarty->display('form.tpl');

function allocate_webservice_users_submit(Pieform $form, $values) {
    global $SESSION;
    $dbserviceuser = get_record('external_services_users', 'id', $values['suid']);
    if (empty($dbserviceuser)) {
        $SESSION->add_error_msg(get_string('invalidserviceuser', 'auth.webservice'));
        redirect('/webservice/admin/index.php?open=webservices_user');
        return;
    }

    if (!empty($values['wssigenc'])) {
        if (empty($values['publickey'])) {
            $SESSION->add_error_msg('Must supply a public key to enable WS-Security');
            redirect('/webservice/admin/userconfig.php?suid=' . $dbserviceuser->id);
        }
        $dbserviceuser->wssigenc = 1;
    }
    else {
        $dbserviceuser->wssigenc = 0;
    }

    if (!empty($values['publickey'])) {
        $publickey = openssl_x509_parse($values['publickey']);
        if (empty($publickey)) {
            $SESSION->add_error_msg('Invalid public key');
            redirect('/webservice/admin/userconfig.php?suid=' . $dbserviceuser->id);
        }
        $dbserviceuser->publickey = $values['publickey'];
        $dbserviceuser->publickeyexpires = $publickey['validTo_time_t'];
    }
    else {
        $dbserviceuser->publickey = '';
        $dbserviceuser->publickeyexpires = time();
    }

    if ($dbserviceuser->externalserviceid != $values['service']) {
        $dbservice = get_record('external_services', 'id', $values['service']);
        if ($dbservice) {
            $dbserviceuser->externalserviceid = $values['service'];
        }
    }
    $dbserviceuser->mtime = db_format_timestamp(time());
    update_record('external_services_users', $dbserviceuser);

    $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
    redirect('/webservice/admin/userconfig.php?suid=' . $dbserviceuser->id);
}

function allocate_webservice_users_validate(PieForm $form, $values) {
    global $SESSION;
    return true;
}
