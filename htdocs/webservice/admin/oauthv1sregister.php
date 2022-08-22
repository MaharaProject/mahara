<?php
/**
 * External application using the oAuth version 1 protocol
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
$disabledopts = array();
$sopts = array();
foreach ($services as $service) {
    // Check langstring exists before adding to list options
    $displayname = get_string($service->shortname, preg_replace('/\//', '.', $service->component));
    if (has_oauth($service->id)) {
        $sopts[$service->id] = string_exists($displayname) ? $displayname : $service->name;
    }
    $disabledopts[$service->id] = array();
    if (substr_count($service->component, '/') > 0) {
        list($moduletype, $module) = explode("/", $service->component);
        safe_require_plugin($moduletype, $module);
        $classname = generate_class_name($moduletype, $module);
        if (is_callable(array($classname, 'disable_webservice_fields'))) {
            $disabledopts[$service->id] = call_static_method($classname, 'disable_webservice_fields');
        }
    }
}

// we have a service config form
if ($config_server_id) {
    $form = webservice_server_config_form($config_server_id);
}
// we have an edit form
else if (!empty($dbserver)) {
    $disabled = $hidden = $extra = $info = array();
    list($moduletype, $module) = get_module_from_serverid($server_id);
    safe_require_plugin($moduletype, $module);
    $classname = generate_class_name($moduletype, $module);
    if (is_callable(array($classname, 'disable_webservice_fields'))) {
        $disabled = call_static_method($classname, 'disable_webservice_fields');
    }
    if (is_callable(array($classname, 'hide_webservice_fields'))) {
        $hidden = call_static_method($classname, 'hide_webservice_fields');
    }
    if (is_callable(array($classname, 'extra_webservice_fields'))) {
        $extra = call_static_method($classname, 'extra_webservice_fields', $dbserver);
    }
    if (is_callable(array($classname, 'info_webservice_fields'))) {
        $info = call_static_method($classname, 'info_webservice_fields');
    }
    $form = webservice_server_edit_form($dbserver, $sopts, $iopts, $disabled, $hidden, $extra, $info);
}
// else we have just the standard list
else {
    $form = webservice_server_list_form($sopts, $iopts);
}

/**
 * Add webservice external application
 *
 * @param Pieform $form The pieform being validated
 * @param array $values data entered on pieform
 */
function webservices_add_application_submit(Pieform $form, $values) {
    global $SESSION, $USER, $services;
    $redirect = get_config('wwwroot').'webservice/admin/oauthv1sregister.php';

    $dbuser = get_record('usr', 'id', $USER->get('id'));
    if (empty($dbuser)) {
        $form->reply(PIEFORM_ERR, array(
            'message'  => get_string('erroruser', 'auth.webservice'),
            'goto'     => $redirect,
        ));
    }
    $store = OAuthStore::instance();

    list($moduletype, $module) = get_module_from_external_service($values['service']);
    safe_require_plugin($moduletype, $module);
    $classname = generate_class_name($moduletype, $module);

    if (is_callable(array($classname, 'create_new_app'))) {
        $new_app = call_static_method($classname, 'create_new_app', $values, $dbuser);
    }
    else {

        $new_app = array(
            'application_title' => $values['application'],
            'application_uri'   => 'http://example.com',
            'requester_name'    => $dbuser->firstname . ' ' . $dbuser->lastname,
            'requester_email'   => $dbuser->email,
            'callback_uri'      => 'http://example.com',
            'institution'       => $values['institution'],
            'externalserviceid' => $values['service'],
        );
        foreach ($services as $k => $service) {
            if ($service->id == $values['service'] && isset($service->component)) {
                list($moduletype, $module) = get_module_from_serverid($service->id);
                safe_require_plugin($moduletype, $module);
                $classname = generate_class_name($moduletype, $module);
                if (is_callable(array($classname, 'add_application'))) {
                    $new_app = call_static_method($classname, 'add_application', $new_app);
                }
            }
        }
    }
    $key = $store->updateConsumer($new_app, $dbuser->id, true);
    $c = (object) $store->getConsumer($key, $dbuser->id, true);

    if (empty($c)) {
        $form->reply(PIEFORM_ERR, array(
            'message'  => get_string('errorregister', 'auth.webservice'),
            'goto'     => $redirect,
        ));
    }
    else {
        // New application added - now check that institution has 'webservices' auth
        // Get auth priority we need for the ensure_record_exists function
        $priorities  = get_record_sql("SELECT MAX(priority) AS maxpriority, (
                                           SELECT priority FROM {auth_instance}
                                           WHERE institution = ? AND instancename = ?) AS webservicepriority
                                      FROM {auth_instance} WHERE institution = ?", array($values['institution'], $new_app['application_title'], $values['institution']));
        $priority = is_null($priorities->webservicepriority) ? $priorities->maxpriority + 1 : $priorities->webservicepriority;
        if (!ensure_record_exists('auth_instance', (object) array('institution' => $values['institution'], 'instancename' => $new_app['application_title']),
                                                   (object) array('institution' => $values['institution'], 'authname' => 'webservice', 'active' => 1, 'priority' => $priority, 'instancename' => $new_app['application_title']))) {
            $form->reply(PIEFORM_ERR, array(
                'message'  => get_string('setauthinstancefailed', 'auth.webservice', institution_display_name($values['institution'])),
                'goto'     => $redirect . '?edit=' . $c->id,
            ));
        }
        redirect($redirect . '?edit=' . $c->id);
    }
}

function webservices_server_validate(Pieform $form, $values) {
    $redirect = get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php';
    $dbserver = get_record('oauth_server_registry', 'id', $values['token']);
    if ($dbserver) {
        if ($values['action'] == 'delete') {
            if (external_app_used_by_auth($dbserver)) {
                $form->reply(PIEFORM_ERR, array(
                    'message'  => get_string('cannotdelete', 'auth.webservice', $dbserver->application_title),
                    'goto'     => $redirect,
                ));
            }
        }
    }
}

/**
 * Edit webservice external application configuration
 *
 * @param Pieform $form The pieform being validated
 * @param array $values data entered on pieform
 */
function webservices_server_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $store = OAuthStore::instance();
    $is_admin = ($USER->get('admin') ||defined('ADMIN') || defined('INSTITUTIONALADMIN') || $USER->is_institutional_admin() ? true : false);
    $dbserver = get_record('oauth_server_registry', 'id', $values['token']);

    if ($dbserver) {
        if ($values['action'] == 'delete') {
            list($moduletype, $module) = get_module_from_serverid($values['token']);
            safe_require_plugin($moduletype, $module);
            $classname = generate_class_name($moduletype, $module);

            if (is_callable(array($classname, 'webservices_server_submit'))) {
                call_static_method($classname, 'webservices_server_submit', $form, $values);
            }

            delete_records_sql('
                                DELETE FROM {oauth_server_config}
                                WHERE oauthserverregistryid = ?
                                ', array($dbserver->id));

            delete_records_sql('
                                DELETE FROM {oauth_server_token}
                                WHERE osr_id_ref = ?
                                ', array($dbserver->id));
            delete_records_sql('DELETE FROM {auth_instance} WHERE instancename = ?', array($dbserver->application_title));
            if (db_table_exists('lti_assessment')) {
                delete_records_sql('
                                   DELETE FROM {lti_assessment_submission} WHERE ltiassessment IN (
                                       SELECT id FROM {lti_assessment} WHERE oauthserver = ?
                                   )
                                   ', array($dbserver->id));
                delete_records_sql('
                                    DELETE FROM {lti_assessment}
                                    WHERE oauthserver = ?
                                    ', array($dbserver->id));
            }
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

/**
 * Validate the webservice external application
 *
 * @param Pieform $form The pieform being validated
 * @param array $values data entered on pieform
 */
function webservice_oauth_server_validate(Pieform $form, $values) {
    $owner = array_diff($values['user'], array(''));
    if (empty($owner)) {
        $form->set_error('user', get_string('needtosetowner', 'auth.webservice'));
    }
    list($moduletype, $module) = get_module_from_external_service($values['service']);
    safe_require_plugin($moduletype, $module);
    $classname = generate_class_name($moduletype, $module);

    if (is_callable(array($classname, 'webservice_oauth_server_validate'))) {
        return call_static_method($classname, 'webservice_oauth_server_validate', $form, $values);
    }
}

/**
 * Save the webservice external application
 *
 * @param Pieform $form The pieform being validated
 * @param array $values data entered on pieform
 */
function webservice_oauth_server_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $redirect = get_config('wwwroot').'webservice/admin/oauthv1sregister.php';

    $store = OAuthStore::instance();
    $dbserver = get_record('oauth_server_registry', 'id', $values['id']);
    if ($dbserver) {
        list($moduletype, $module) = get_module_from_external_service($values['service']);
        safe_require_plugin($moduletype, $module);
        $classname = generate_class_name($moduletype, $module);

        if (is_callable(array($classname, 'get_app_values'))) {
            $app = call_static_method($classname, 'get_app_values', $values, $dbserver);
        }
        else {
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
                        'enabled'           => $values['enabled']
            );
        }
        if ($USER->get('admin') && isset($values['user'])) {
            $useridchange = !empty($values['user'][0]) ? $values['user'][0] : false;
            if ($useridchange) {
                $app['userid'] = $useridchange;
            }
        }

        $key = $store->updateConsumer($app, $USER->get('id'), true, $dbserver->application_title);
        $c = (object) $store->getConsumer($key, $USER->get('id'), true);

        if (is_callable(array($classname, 'webservice_oauth_server_submit'))) {
            call_static_method($classname, 'webservice_oauth_server_submit', $form, $values);
        }

        if (empty($c)) {
            $form->reply(PIEFORM_ERR, array(
                'message'  => get_string('errorregister', 'auth.webservice'),
                'goto'     => $redirect,
            ));
        }
        else {
            $form->reply(PIEFORM_OK, array(
                'message'  => get_string('confirmupdate', 'auth.webservice', $app['application_title']),
                'goto'     => $redirect,
            ));
        }
    }
    $form->reply(PIEFORM_ERR, array(
        'message'  => get_string('errorupdate', 'auth.webservice'),
        'goto'     => $redirect,
    ));
}

$pieform = pieform_instance($form);
$form = $pieform->build(false);

$smarty = smarty();
setpageicon($smarty, 'icon-external-link-alt');
safe_require('auth', 'webservice');
PluginAuthWebservice::menu_items($smarty, 'webservice/oauthconfig');
$smarty->assign('form', $form);
$smarty->display('form.tpl');

/**
 * Dummy save function for external application list
 *
 * @param Pieform $form The pieform being validated
 * @param array $values data entered on pieform
 */
function webservice_main_submit(Pieform $form, $values) {
}

/**
 * The form array for making the edit external application form
 *
 * @param object $dbserver A db object
 * @param array $sopts     Service options
 * @param array $iopts     Institution options
 * @param array $disabled  Any webservice fields to be disabled
 * @param array $hidden    Any webservice fields to not hide from the form
 * @param array $extra     Any extra fields to display for this instance
 * @param array $info      Any extra fields to display for this webservice type
 * @return array A pieform compatible array to build a Pieform from
 */
function webservice_server_edit_form($dbserver, $sopts, $iopts, $disabled = array(), $hidden = array(), $extra = array(), $info = array()) {
    global $USER, $disabledopts;

    $server_details =
        array(
            'name'             => 'webservice_oauth_server',
            'plugintype'  => 'core',
            'pluginname'  => 'webservices',
            'validatecallback' => 'webservice_oauth_server_validate',
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
    if (!in_array('consumer_key_html', $hidden)) {
        $server_details['elements']['consumer_key_html'] = array(
            'title'        => get_string('consumer_key', 'auth.webservice'),
            'type'         => 'html',
            'value'        => $dbserver->consumer_key,
        );
    }
    if (!in_array('consumer_secret', $hidden)) {
        $server_details['elements']['consumer_secret'] = array(
            'title'        => get_string('consumer_secret', 'auth.webservice'),
            'type'         => 'html',
            'value'        => $dbserver->consumer_secret,
        );
    }

    $server_details['elements']['application_title'] = array(
        'title'        => get_string('application_title', 'auth.webservice'),
        'defaultvalue' =>  $dbserver->application_title,
        'type'         => 'text',
    );

    if ($USER->get('admin')) {
        // we can set another user as service owner
        $server_details['elements']['user'] = array(
            'title'        => get_string('serviceuser', 'auth.webservice'),
            'defaultvalue'        => array($dbserver->userid),
            'type' => 'autocomplete',
            'ajaxurl' => get_config('wwwroot') . 'webservice/admin/users.json.php',
            'initfunction' => 'translate_ids_to_names',
            'multiple' => true,
            'ajaxextraparams' => array(),
            'extraparams' => array(
                'maximumSelectionLength' => 1
            ),
            'width' => '280px',
            'rules' => array(
                'required' => true,
            ),
        );
    }
    else {
        $server_details['elements']['user'] = array(
            'title'        => get_string('serviceuser', 'auth.webservice'),
            'value'        => get_field('usr', 'username', 'id', $dbserver->userid),
            'type'         => 'html',
        );
    }
    if (!in_array('application_uri', $hidden)) {
        $server_details['elements']['application_uri'] = array(
            'title'        => get_string('application_uri', 'auth.webservice'),
            'defaultvalue' =>  $dbserver->application_uri,
            'type'         => 'text',
            'disabled'     => (isset($disabled['application_uri']) ? true : false),
            'help'         => true,
        );
    }
    if (!in_array('callback_uri', $hidden)) {
        $server_details['elements']['callback_uri'] = array(
            'title'        => get_string('callback', 'auth.webservice'),
            'defaultvalue' =>  $dbserver->callback_uri,
            'type'         => 'text',
            'disabled'     => (isset($disabled['callback_uri']) ? true : false),
        );
    }

    $server_details['elements']['institution'] = array(
        'type'         => 'select',
        'title'        => get_string('institution'),
        'options'      => $iopts,
        'defaultvalue' => trim($dbserver->institution),
        'disabled'     => (isset($disabled['institution']) ? true : false),
    );

    $server_details['elements']['service'] = array(
        'type'         => 'select',
        'title'        => get_string('servicename', 'auth.webservice'),
        'options'      => $sopts,
        'defaultvalue' => $dbserver->externalserviceid,
        'disabled'     => (isset($disabled['service']) ? true : false),
    );

    $server_details['elements']['enabled'] = array(
        'title'        => get_string('enabled'),
        'defaultvalue' => (($dbserver->enabled == 1) ? true : false),
        'type'         => 'switchbox',
    );

    $server_details['elements'] = array_merge($server_details['elements'], $extra);
    $server_details['elements'] = array_merge($server_details['elements'], $info);

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
        'value'        => '<div class="align-with-input-desc">' . implode(', ', $function_list) . '</div>',
        'type'         => 'html',
    );

    $disabledoptstr = json_encode($disabledopts);
    $server_details['elements']['disabledopts'] = array(
        'type' => 'html',
        'value' => '<script>var disopts = ' . $disabledoptstr . ';
        var selectedservice;
        jQuery(function($) {
            function update_service(service) {
               $("#webservice_oauth_server input").each(function() {
                   $(this).prop("disabled", false);
               });
               $.each(disopts[service], function (k, v) {
                   $("#webservice_oauth_server_" + k).prop("disabled", true);
               });
            }
            $("#webservice_oauth_server_service").change(function() {
                selectedservice = $(this).children("option:selected").val();
                update_service(selectedservice);
            });
            selectedservice = $("#webservice_oauth_server_service").children("option:selected").val();
            update_service(selectedservice);
        });
        </script>',
    );

    $server_details['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'value' => array(get_string('save'), get_string('back')),
        'goto'  => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
        'subclass' => array('btn-primary'),
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

/**
 * The form array for making the server list
 *
 * @param array $sopts     Service options
 * @param array $iopts     Institution options
 * @return array $form A pieform compatible array for building a Pieform
 */
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
                'key'          => $consumer->consumer_key,
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
                'key'          => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_consumer_key'] = array(
                'value'        =>  $consumer->consumer_key,
                'type'         => 'html',
                'key'          => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_consumer_secret'] = array(
                'value'        =>  $consumer->consumer_secret,
                'type'         => 'html',
                'key'          => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_enabled'] = array(
                'value' => (
                            ($consumer->enabled == 1) ?
                             display_icon('enabledspecific', false, get_string('enabledspecific', 'mahara', $consumer->application_title)) :
                             display_icon('disabledspecific', false, get_string('disabledspecific', 'mahara', $consumer->application_title))
                           ),
                'type'         => 'html',
                'class'        => 'text-center',
                'key'          => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_institution'] = array(
                'value'        => institution_display_name($consumer->institution),
                'type'         => 'html',
                'key'          => $consumer->consumer_key,
            );

            // edit and delete buttons
            $form['elements']['id' . $consumer->id . '_actions'] = array(
                'value' => pieform(array(
                        'name' => 'webservices_server_edit_' . $consumer->id,
                        'renderer' => 'div',
                        'class' => 'form-as-button float-start first',
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
                                'value' => '<span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>',
                                'elementtitle' => get_string('editspecific', 'mahara', $consumer->application_title),
                            ),
                        ),
                    ))
                    .
                    pieform(array(
                        'name' => 'webservices_server_delete_' . $consumer->id,
                        'renderer' => 'div',
                        'class' => 'form-as-button float-start',
                        'elementclasses' => false,
                        'validatecallback' => 'webservices_server_validate',
                        'successcallback' => 'webservices_server_submit',
                        'jsform' => false,
                        'elements' => array(
                            'token' => array('type' => 'hidden', 'value' => $consumer->id),
                            'action' => array('type' => 'hidden', 'value' => 'delete'),
                            'submit' => array(
                                'type' => 'button',
                                'usebuttontag' => true,
                                'class' => 'btn-secondary btn-sm',
                                'confirm' => get_string('confirmdeleteexternalapp', 'auth.webservice'),
                                'value' => '<span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>',
                                'elementtitle' => get_string('deletespecific', 'mahara', $consumer->application_title),
                            ),
                        ),
                    )),
                'type' => 'html',
                'key' => $consumer->consumer_key,
                'class' => 'webserviceconfigcontrols btn-group icon-cell',
            );
            // Check if service has extra settings
            if ($consumer_functions = get_records_sql_array("
                SELECT ef.component FROM {external_services_functions} esf
                JOIN {external_functions} ef ON ef.name = esf.functionname
                WHERE esf.externalserviceid = ?
                AND ef.hasconfig = ?", array($consumer->externalserviceid, 1))) {
                    $hasconfig = false;
                    foreach ($consumer_functions as $cf) {
                        list($moduletype, $module) = explode("/", $cf->component);

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
                                    'class' => 'form-as-button float-start last',
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
                                            'value' => '<span class="icon icon-cog" role="presentation" aria-hidden="true"></span><span class="visually-hidden">'.get_string('managespecific', 'mahara', $consumer->application_title).'</span>',
                                            'elementtitle' => get_string('managespecific', 'mahara', $consumer->application_title),
                                        ),
                                    ),
                                ));
                        }
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

/**
 * The form array for making the server config form
 *
 * @param integer $serverid   ID of service record
 *
 * @return array $form A pieform compatible array for building a Pieform
 */
function webservice_server_config_form($serverid) {
    global $USER, $THEME;

    list($moduletype, $module) = get_module_from_serverid($serverid);

    $form = array();
    if (safe_require_plugin($moduletype, $module)) {

        $elements = call_static_method(generate_class_name($moduletype, $module), 'get_oauth_service_config_options', $serverid);

        $elements['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('save'), get_string('back')),
            'goto'  => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
            'subclass' => array('btn-primary'),
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
                            'elements' => $elements
                        )),
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

    }
    return $form;
}

/**
 * Submit the webservice server config form
 *
 * @param Pieform $form The pieform being validated
 * @param array $values data entered on pieform
 */
function webservice_server_config_submit(Pieform $form, $values) {

    $serverid = $values['id'];

    list($moduletype, $module) = get_module_from_serverid($serverid);

    if (safe_require_plugin($moduletype, $module)) {

        call_static_method(generate_class_name($moduletype, $module), 'save_oauth_service_config_options', $serverid, $values);
        redirect(get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php');
    }

    return false;
}

/**
 * Update values in the external application configuration
 *
 * @param integer $serverid External server ID
 * @param string $key   The configuration field
 * @param string $value The configuration value
 * @return boolean
 */
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

/**
 * Get the external application service plugintype and pluginname based on server id
 *
 * @param integer $serverid The id of the external service
 * @return array
 */
function get_module_from_serverid($serverid) {

    $consumer = get_record_sql('
            SELECT es.id, es.component
            FROM {oauth_server_registry} osr
            JOIN {external_services} es
                ON es.id = osr.externalserviceid
            WHERE osr.id = ? ', array($serverid));
    if ($consumer) {
        if (substr_count($consumer->component, '/') > 0) {
            return explode("/", $consumer->component);
        }
    }
    return array('auth', 'webservice');
}

function get_module_from_external_service($serviceid) {

    $consumer = get_record_sql('
            SELECT es.id, es.component
            FROM {external_services} es
            WHERE es.id = ? ', array($serviceid));
    if (substr_count($consumer->component, '/') > 0) {
        return explode("/", $consumer->component);
    }
    return array('auth', 'webservice');
}

/**
 * Translate the supplied user id to it's display name
 *
 * @param array $ids  User id number
 * @return object $results containing id and text values
 */
function translate_ids_to_names(array $ids) {
    return translate_user_ids_to_names($ids);
}

/**
 * Check service has OAuth configured. This includes checking custom apps that may be
 * using LTI functions.
 * @param object $serviceid ID of the web service group that the external app is being registered with
 * @return bool Whether or not the web service group has OAuth configured
 */
function has_oauth($serviceid) {
    // Get the classname for each of the functions added to the service
    $service_classnames = get_column_sql("
        SELECT DISTINCT classname FROM {external_functions} WHERE name IN (
            SELECT functionname FROM {external_services_functions} WHERE externalserviceid = ?
    )", array($serviceid));

    // Check if any of those classnames have OAuth configured
    $service_oauth = false;
    foreach ($service_classnames as $classname) {
        if ($classname == 'module_lti_launch' || $classname == 'module_lti_advantage_launch') {
            $service_oauth = true;
        }
    }
    return $service_oauth;
}

/**
 * Do not allow deletion if there are people still using this external app as their authentication method
 * @param object db server information about the external app being deleted
 * @return bool
 */
function external_app_used_by_auth($dbserver) {
    // We normally would check people by the institution but because admins can assign the auth method
    // via the admin user edit screen it means people outside the institution could have the auth method
    $matchingauth = get_field('auth_instance', 'id', 'institution', $dbserver->institution, 'instancename', $dbserver->application_title);
    $found = false;
    if ($matchingauth) {
        $found = (bool)count_records('usr', 'authinstance', $matchingauth);
    }
    return $found;
}
