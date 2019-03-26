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
define('MENUITEM', 'webservices/oauthconfig');
define('SECTION_PAGE', 'oauth');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('webservices_title', 'auth.webservice'));
define('SUBSECTIONHEADING', get_string('externalappsregister', 'auth.webservice'));

require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthServer.php');
require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthStore.php');
OAuthStore::instance('Mahara');

$server_id  = param_integer('edit', 0);
$config_server_id = param_integer('config', 0);
$dbserver = get_record('oauth_server_registry', 'id', $server_id);

$institutions = get_records_array('institution');
$iopts = array();
foreach ($institutions as $institution) {
    $iopts[trim($institution->name)] = $institution->displayname;
}

$services = get_records_array('external_services', 'restrictedusers', 0);
$sopts = array();
foreach ($services as $service) {
    $sopts[$service->id] = $service->name;
}

// we have a service config form
if ($config_server_id) {
    $form = webservice_server_config_form($config_server_id);
}
// we have an edit form
else if (!empty($dbserver)) {
    $disabled = array();
    list($moduletype, $module) = get_module_from_serverid($server_id);
    safe_require_plugin($moduletype, $module);
    $classname = generate_class_name($moduletype, $module);
    if (is_callable(array($classname, 'disable_webservice_fields'))) {
        $disabled = call_static_method($classname, 'disable_webservice_fields');
    }
    $form = webservice_server_edit_form($dbserver, $sopts, $iopts, $disabled);
}
// else we have just the standard list
else {
    $form = webservice_server_list_form($sopts, $iopts);
}

function webservices_add_application_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $dbuser = get_record('usr', 'id', $USER->get('id'));
    if (empty($dbuser)) {
        $SESSION->add_error_msg(get_string('erroruser', 'auth.webservice'));
        redirect('/webservice/admin/oauthv1sregister.php');
    }
    $store = OAuthStore::instance();

    $new_app = array(
                'application_title' => $values['application'],
                'application_uri'   => 'http://example.com',
                'requester_name'    => $dbuser->firstname . ' ' . $dbuser->lastname,
                'requester_email'   => $dbuser->email,
                'callback_uri'      => 'http://example.com',
                'institution'       => $values['institution'],
                'externalserviceid' => $values['service'],
      );
    $key = $store->updateConsumer($new_app, $dbuser->id, true);
    $c = (object) $store->getConsumer($key, $dbuser->id, true);
    if (empty($c)) {
        $SESSION->add_error_msg(get_string('errorregister', 'auth.webservice'));
        redirect('/webservice/admin/oauthv1sregister.php');
    }
    else {
        // New application added - now check that institution has 'webservices' auth
        // Get auth priority we need for the ensure_record_exists function
        $priorities  = get_record_sql("SELECT MAX(priority) AS maxpriority, (
                                           SELECT priority FROM {auth_instance}
                                           WHERE institution = ? AND authname = 'webservice') AS webservicepriority
                                      FROM {auth_instance} WHERE institution = ?", array($values['institution'], $values['institution']));
        $priority = is_null($priorities->webservicepriority) ? $priorities->maxpriority + 1 : $priorities->webservicepriority;
        if (!ensure_record_exists('auth_instance', (object) array('institution' => $values['institution'], 'authname' => 'webservice'),
                                                   (object) array('institution' => $values['institution'], 'authname' => 'webservice', 'active' => 1, 'priority' => $priority, 'instancename' => 'webservice'))) {
            $SESSION->add_error_msg(get_string('setauthinstancefailed', 'auth.webservice', institution_display_name($values['institution'])));
        }
        redirect('/webservice/admin/oauthv1sregister.php?edit=' . $c->id);
    }

}

function webservices_server_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $store = OAuthStore::instance();
    $is_admin = ($USER->get('admin') ||defined('ADMIN') || defined('INSTITUTIONALADMIN') || $USER->is_institutional_admin() ? true : false);
    $dbserver = get_record('oauth_server_registry', 'id', $values['token']);
    if ($dbserver) {
        if ($values['action'] == 'delete') {

            delete_records_sql('
                                DELETE FROM {oauth_server_config}
                                WHERE oauthserverregistryid = ?
                                ', array($dbserver->id));

            delete_records_sql('
                                DELETE FROM {oauth_server_token}
                                WHERE osr_id_ref = ?
                                ', array($dbserver->id));
            delete_records_sql('
                                DELETE FROM {lti_assessment}
                                WHERE oauthserver = ?
                                ', array($dbserver->id));
            $store->deleteServer($dbserver->consumer_key, $dbserver->userid, $is_admin);
            $SESSION->add_ok_msg(get_string('oauthserverdeleted', 'auth.webservice'));
        }
        else if ($values['action'] == 'edit') {
            redirect('/webservice/admin/oauthv1sregister.php?edit=' . $values['token']);
        }
        else if ($values['action'] == 'config') {
            redirect('/webservice/admin/oauthv1sregister.php?config=' . $values['token']);
        }
    }
    redirect('/webservice/admin/oauthv1sregister.php');
}

function webservice_oauth_server_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $store = OAuthStore::instance();
    $dbserver = get_record('oauth_server_registry', 'id', $values['id']);
    if ($dbserver) {

       $app = array(
                    'application_title' => $values['application_title'],
                    'application_uri'   => $values['application_uri'],
                    'requester_name'    => $dbserver->requester_name,
                    'requester_email'   => $dbserver->requester_email,
                    'callback_uri'      => $values['callback_uri'],
                    'institution'       => $values['institution'],
                    'externalserviceid' => $values['service'],
                    'consumer_key'      => $dbserver->consumer_key,
                    'consumer_secret'   => $dbserver->consumer_secret,
                    'id'                => $values['id'],
       );
        $key = $store->updateConsumer($app, $USER->get('id'), true);
        $c = (object) $store->getConsumer($key, $USER->get('id'), true);
        if (empty($c)) {
            $SESSION->add_error_msg(get_string('errorregister', 'auth.webservice'));
            redirect('/webservice/admin/oauthv1sregister.php');
        }
        else {
            redirect('/webservice/admin/oauthv1sregister.php?edit=' . $c->id);
        }
    }

    $SESSION->add_error_msg(get_string('errorupdate', 'auth.webservice'));
    redirect('/webservice/admin/oauthv1sregister.php');
}

$pieform = pieform_instance($form);
$form = $pieform->build(false);

$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');
safe_require('auth', 'webservice');
PluginAuthWebservice::menu_items($smarty, 'webservice/oauthconfig');
$smarty->assign('form', $form);
$smarty->display('form.tpl');

function webservice_main_submit(Pieform $form, $values) {
}

function webservice_server_edit_form($dbserver, $sopts, $iopts, $disabled = array()) {

    $server_details =
        array(
            'name'             => 'webservice_oauth_server',
            'successcallback'  => 'webservice_oauth_server_submit',
            'jsform'           => false,
            'elements'   => array(
                    'id' => array(
                        'type'  => 'hidden',
                        'value' => $dbserver->id,
                    ),
                    'userid' => array(
                        'type'  => 'hidden',
                        'value' => $dbserver->userid,
                    ),
                    'consumer_key' => array(
                        'type'  => 'hidden',
                        'value' => $dbserver->consumer_key,
                    ),
                ),
            );
    $server_details['elements']['consumer_key_html'] = array(
        'title'        => get_string('consumer_key', 'auth.webservice'),
        'type'         => 'html',
        'value'        => $dbserver->consumer_key,
    );
    $server_details['elements']['consumer_secret'] = array(
        'title'        => get_string('consumer_secret', 'auth.webservice'),
        'type'         => 'html',
        'value'        => $dbserver->consumer_secret,
    );

    $server_details['elements']['application_title'] = array(
        'title'        => get_string('application_title', 'auth.webservice'),
        'defaultvalue' =>  $dbserver->application_title,
        'type'         => 'text',
    );

    $server_details['elements']['user'] = array(
        'title'        => get_string('serviceuser', 'auth.webservice'),
        'value'        =>  get_field('usr', 'username', 'id', $dbserver->userid),
        'type'         => 'html',
    );

    $server_details['elements']['application_uri'] = array(
        'title'        => get_string('application_uri', 'auth.webservice'),
        'defaultvalue' =>  $dbserver->application_uri,
        'type'         => 'text',
        'disabled'     => (isset($disabled['application_uri']) ? true : false),
    );

    $server_details['elements']['callback_uri'] = array(
        'title'        => get_string('callback', 'auth.webservice'),
        'defaultvalue' =>  $dbserver->callback_uri,
        'type'         => 'text',
        'disabled'     => (isset($disabled['callback_uri']) ? true : false),
    );

    $server_details['elements']['institution'] = array(
        'type'         => 'select',
        'title'        => get_string('institution'),
        'options'      => $iopts,
        'defaultvalue' => trim($dbserver->institution),
    );

    $server_details['elements']['service'] = array(
        'type'         => 'select',
        'title'        => get_string('servicename', 'auth.webservice'),
        'options'      => $sopts,
        'defaultvalue' => $dbserver->externalserviceid,
    );

    $server_details['elements']['enabled'] = array(
        'title'        => get_string('enabled'),
        'defaultvalue' => (($dbserver->enabled == 1) ? 'checked' : ''),
        'type'         => 'switchbox',
        'disabled'     => true,
    );

    $functions = get_records_array('external_services_functions', 'externalserviceid', $dbserver->externalserviceid);
    $function_list = array();
    if ($functions) {
        foreach ($functions as $function) {
            $dbfunction = get_record('external_functions', 'name', $function->functionname);
            $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
        }
    }
    $server_details['elements']['functions'] = array(
        'title'        => get_string('functions', 'auth.webservice'),
        'value'        => '<div class="align-with-input">' . implode(', ', $function_list) . '</div>',
        'type'         => 'html',
    );

    $server_details['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'value' => array(get_string('save'), get_string('back')),
        'goto'  => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
        'class'        => 'btn-primary',
    );

    $elements = array(
            // fieldset for managing service function list
            'token_details' => array(
                    'type' => 'fieldset',
                    'class' => 'with-padding',
                    'elements' => array(
                        'sflist' => array(
                            'value' =>     pieform($server_details),
                        )
                    ),
                    'collapsible' => false,
                ),
        );

    $form = array(
        'renderer' => 'div',
        'id' => 'maintable',
        'name' => 'tokenconfig',
        'jsform' => false,
        'successcallback' => 'webservice_server_edit_submit',
        'elements' => $elements,
    );

    return $form;
}

function webservice_server_list_form($sopts, $iopts) {
    global $USER, $THEME;

    $dbconsumers = get_records_sql_assoc('
            SELECT  osr.id              as id,
                    userid              as userid,
                    institution         as institution,
                    externalserviceid   as externalserviceid,
                    u.username          as username,
                    u.email             as email,
                    consumer_key        as consumer_key,
                    consumer_secret     as consumer_secret,
                    osr.enabled         as enabled,
                    status              as status,
                    osr.ctime           as issue_date,
                    application_uri     as application_uri,
                    application_title   as application_title,
                    application_descr   as application_descr,
                    requester_name      as requester_name,
                    requester_email     as requester_email,
                    callback_uri        as callback_uri,
                    es.component        as component
            FROM {oauth_server_registry} osr
            JOIN {usr} u
                ON osr.userid = u.id
            JOIN {external_services} es
                ON es.id = osr.externalserviceid
            ORDER BY application_title, username
            ', array());
    $form = '';
    if (!empty($dbconsumers)) {
        $form = array(
            'name'            => 'webservices_tokens',
            'elementclasses'  => false,
            'successcallback' => 'webservices_tokens_submit',
            'renderer'   => 'multicolumntable',
            'elements'   => array(
                            'application' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type'  => 'html',
                                'value' => get_string('application', 'auth.webservice'),
                            ),
                            'username' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type'  => 'html',
                                'value' => get_string('owner', 'auth.webservice'),
                            ),
                            'consumer_key' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type'  => 'html',
                                'value' => get_string('consumer_key', 'auth.webservice'),
                            ),
                            'consumer_secret' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type'  => 'html',
                                'value' => get_string('consumer_secret', 'auth.webservice'),
                            ),
                            'enabled' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type'  => 'html',
                                'value' => get_string('enabled'),
                            ),
                            'institution' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type'  => 'html',
                                'value' => get_string('institution'),
                            ),
                            'actions' => array(
                                'title' => ' ',
                                'datatable' => true,
                                'type' => 'html',
                                'value' => '',
                            ),
                        ),
            );
        foreach ($dbconsumers as $consumer) {
            $form['elements']['id' . $consumer->id . '_application'] = array(
                'value'        =>  $consumer->application_title,
                'type'         => 'html',
                'key'        => $consumer->consumer_key,
            );

            if ($USER->is_admin_for_user($consumer->userid)) {
                $user_url = get_config('wwwroot') . 'admin/users/edit.php?id=' . $consumer->userid;
            }
            else {
                $user_url = get_config('wwwroot') . 'user/view.php?id=' . $consumer->userid;
            }
            $form['elements']['id' . $consumer->id . '_username'] = array(
                'value'        =>  '<a href="' . $user_url . '">' . $consumer->username . '</a>',
                'type'         => 'html',
                'key'        => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_consumer_key'] = array(
                'value'        =>  $consumer->consumer_key,
                'type'         => 'html',
                'key'        => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_consumer_secret'] = array(
                'value'        =>  $consumer->consumer_secret,
                'type'         => 'html',
                'key'        => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_enabled'] = array(
                'value' => (
                            ($consumer->enabled == 1) ?
                             display_icon('enabledspecific', false, get_string('enabledspecific', 'mahara', $consumer->application_title)) :
                             display_icon('disabledspecific', false, get_string('disabledspecific', 'mahara', $consumer->application_title))
                           ),
                'type'         => 'html',
                'class'        => 'text-center',
                'key'        => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_institution'] = array(
                'value'        => institution_display_name($consumer->institution),
                'type'         => 'html',
                'key'        => $consumer->consumer_key,
            );

            // edit and delete buttons
            $form['elements']['id' . $consumer->id . '_actions'] = array(
                'value' => pieform(array(
                        'name' => 'webservices_server_edit_' . $consumer->id,
                        'renderer' => 'div',
                        'class' => 'form-as-button float-left',
                        'elementclasses' => false,
                        'successcallback' => 'webservices_server_submit',
                        'jsform' => false,
                        'elements' => array(
                            'token' => array('type' => 'hidden', 'value' => $consumer->id),
                            'action' => array('type' => 'hidden', 'value' => 'edit'),
                            'submit' => array(
                                'type' => 'button',
                                'usebuttontag' => true,
                                'class' => 'btn-secondary btn-sm',
                                'value' => '<span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">' . get_string('editspecific', 'mahara', $consumer->application_title) . '</span>',
                                'elementtitle' => get_string('editspecific', 'mahara', $consumer->application_title),
                            ),
                        ),
                    ))
                    .
                    pieform(array(
                        'name' => 'webservices_server_delete_' . $consumer->id,
                        'renderer' => 'div',
                        'class' => 'form-as-button float-left',
                        'elementclasses' => false,
                        'successcallback' => 'webservices_server_submit',
                        'jsform' => false,
                        'elements' => array(
                            'token' => array('type' => 'hidden', 'value' => $consumer->id),
                            'action' => array('type' => 'hidden', 'value' => 'delete'),
                            'submit' => array(
                                'type' => 'button',
                                'usebuttontag' => true,
                                'class' => 'btn-secondary btn-sm',
                                'value' => '<span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span><span class="sr-only">'.get_string('deletespecific', 'mahara', $consumer->application_title).'</span>',
                                'elementtitle' => get_string('deletespecific', 'mahara', $consumer->application_title),
                            ),
                        ),
                    )),
                'type' => 'html',
                'key' => $consumer->consumer_key,
                'class' => 'webserviceconfigcontrols btn-group icon-cell',
            );

            // Check if service has extra settings
            if ($consumer->component) {
                list($moduletype, $module) = explode("/", $consumer->component);

                $hasconfig = false;

                if (safe_require_plugin($moduletype, $module)) {
                    $classname = generate_class_name($moduletype, $module);
                    if (is_callable(array($classname, 'has_oauth_service_config'))) {
                        $hasconfig = call_static_method($classname, 'has_oauth_service_config');
                    }
                }

                if ($hasconfig) {
                    $form['elements']['id' . $consumer->id . '_actions']['value'] .=
                        pieform(array(
                            'name' => 'webservices_server_config_' . $consumer->id,
                            'renderer' => 'div',
                            'class' => 'form-as-button float-left',
                            'elementclasses' => false,
                            'successcallback' => 'webservices_server_submit',
                            'jsform' => false,
                            'elements' => array(
                                'token' => array('type' => 'hidden', 'value' => $consumer->id),
                                'action' => array('type' => 'hidden', 'value' => 'config'),
                                'submit' => array(
                                    'type' => 'button',
                                    'usebuttontag' => true,
                                    'class' => 'btn-secondary btn-sm',
                                    'value' => '<span class="icon icon-cog icon-lg " role="presentation" aria-hidden="true"></span><span class="sr-only">'.get_string('managespecific', 'mahara', $consumer->application_title).'</span>',
                                    'elementtitle' => get_string('managespecific', 'mahara', $consumer->application_title),
                                ),
                            ),
                        ));
                }
            }

        }

        $pieform = pieform_instance($form);
        $form = $pieform->build(false);
    }

    $form = '<div class="table-responsive">' . $form . '</div><div>' .
        pieform(array(
            'name' => 'webservices_token_generate',
            'renderer' => 'div',
            'validatecallback' => 'webservices_add_application_validate',
            'successcallback' => 'webservices_add_application_submit',
            'class' => 'form-inline form-inline-align-bottom',
            'jsform' => false,
            'action' => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
            'elements' => array(
                'application' => array(
                    'type' => 'text',
                    'title' => get_string('application', 'auth.webservice') . ': ',
                ),
                'institution' => array(
                    'type' => 'select',
                    'title' => get_string('institution'),
                    'class' => 'institution input-small hide-label',
                    'options' => $iopts,
                ),
                'service' => array(
                    'type' => 'select',
                    'title' => get_string('service', 'auth.webservice'),
                    'class' => 'hide-label',
                    'options' => $sopts,
                ),
                'action' => array('type' => 'hidden', 'value' => 'add'),
                'submit' => array(
                    'type' => 'submit',
                    'class' => 'btn-primary',
                    'value' => get_string('add', 'auth.webservice'),
                ),
            ),
        )) . '</div>';

    $elements = array(
        // fieldset for managing service function list
        'register_server' => array(
            'type' => 'container',
            'title' => get_string('userapplications1', 'auth.webservice'),
            'isformgroup' => false,
            'elements' => array(
                'sflist' => array(
                    'type' => 'html',
                    'value' => $form,
                )
            ),
        ),
    );

    $form = array(
        'renderer' => 'div',
        'type' => 'div',
        'id' => 'maintable',
        'name' => 'maincontainer',
        'dieaftersubmit' => false,
        'successcallback' => 'webservice_main_submit',
        'elements' => $elements,
    );

    return $form;
}

function webservice_server_config_form($serverid) {
    global $USER, $THEME;

    list($moduletype, $module) = get_module_from_serverid($serverid);

    if (safe_require_plugin($moduletype, $module)) {

        $elements = call_static_method(generate_class_name($moduletype, $module), 'get_oauth_service_config_options', $serverid);

        $elements['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('save'), get_string('back')),
            'goto'  => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
            'class' => 'btn-primary',
        );

        $elements['id'] = array(
            'type'  => 'hidden',
            'value' => $serverid,
        );

        $fieldset = array(
            // fieldset for managing service function list
            'token_details' => array(
                    'type' => 'fieldset',
                    'class' => 'with-padding',
                    'elements' => array(
                        'sflist' => array(
                            'value' =>  pieform(array(
                                'name' => 'oauthconfigoptions',
                                'plugintype' => $moduletype,
                                'pluginname' => $module,
                                'successcallback'  => 'webservice_server_config_submit',
                                'elements' => $elements)),
                        )
                    ),
                    'collapsible' => false,
                ),
        );

        $form = array(
            'name' => 'maincontainer',
            'jsform' => false,
            'elements' => $fieldset,
        );

        return $form;
    }
}

function webservice_server_config_submit(Pieform $form, $values) {

    $serverid = $values['id'];

    list($moduletype, $module) = get_module_from_serverid($serverid);

    if (safe_require_plugin($moduletype, $module)) {

        call_static_method(generate_class_name($moduletype, $module), 'save_oauth_service_config_options', $serverid, $values);
        redirect(get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php');
    }

    return false;
}


function update_oauth_server_config($serverid, $key, $value) {

    $dbvalue = get_field('oauth_server_config', 'value', 'oauthserverregistryid', $serverid, 'field', $key);
    if (false !== $dbvalue) {
        if (($dbvalue == $value)
            || set_field('oauth_server_config', 'value', $value, 'oauthserverregistryid', $serverid, 'field', $key)
        ) {
            return true;
        }
    }
    else {
        $config = new stdClass();
        $config->oauthserverregistryid = $serverid;
        $config->field = $key;
        $config->value = $value;
        $status = insert_record('oauth_server_config', $config);

        return true;
    }

    return false;
}

function get_module_from_serverid($serverid) {

    $consumer = get_record_sql('
            SELECT es.id, es.component
            FROM {oauth_server_registry} osr
            JOIN {external_services} es
                ON es.id = osr.externalserviceid
            WHERE osr.id = ? ', array($serverid));
    if (substr_count($consumer->component, '/') > 0) {
        return explode("/", $consumer->component);
    }
    return array('auth', 'webservice');
}
