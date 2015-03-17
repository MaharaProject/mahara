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
define('MENUITEM', 'configextensions/webservices/oauthconfig');
define('SECTION_PAGE', 'oauth');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('oauthv1sregister', 'auth.webservice'));
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthServer.php');
require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthStore.php');
OAuthStore::instance('Mahara');

$server_id  = param_variable('edit', 0);
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

// we have an edit form
if (!empty($dbserver)) {
    $form = webservice_server_edit_form($dbserver, $sopts, $iopts);
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
                                DELETE FROM {oauth_server_token}
                                WHERE osr_id_ref = ?
                                ', array($dbserver->id));
            $store->deleteServer($dbserver->consumer_key, $dbserver->userid, $is_admin);
            $SESSION->add_ok_msg(get_string('oauthserverdeleted', 'auth.webservice'));
        }
        else if ($values['action'] == 'edit') {
            redirect('/webservice/admin/oauthv1sregister.php?edit=' . $values['token']);
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

$pieform = new Pieform($form);
$form = $pieform->build(false);

$smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
safe_require('auth', 'webservice');
PluginAuthWebservice::menu_items($smarty, 'webservice/oauthconfig');
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('form.tpl');

function webservice_main_submit(Pieform $form, $values) {
}

function webservice_server_edit_form($dbserver, $sopts, $iopts) {

    $server_details =
        array(
            'name'             => 'webservice_oauth_server',
            'successcallback'  => 'webservice_oauth_server_submit',
            'jsform'           => false,
            'renderer'         => 'table',
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

    $server_details['elements']['consumer_secret'] = array(
        'title'        => get_string('consumer_secret', 'auth.webservice'),
        'value'        =>  $dbserver->consumer_secret,
        'type'         => 'html',
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
    );

    $server_details['elements']['callback_uri'] = array(
        'title'        => get_string('callback', 'auth.webservice'),
        'defaultvalue' =>  $dbserver->callback_uri,
        'type'         => 'text',
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
        'type'         => 'checkbox',
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
        'value'        =>  implode(', ', $function_list),
        'type'         => 'html',
    );

    $server_details['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'value' => array(get_string('save'), get_string('back')),
        'goto'  => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
    );

    $elements = array(
            // fieldset for managing service function list
            'token_details' => array(
                                'type' => 'fieldset',
                                'legend' => get_string('serverkey', 'auth.webservice', $dbserver->consumer_key),
                                'elements' => array(
                                    'sflist' => array(
                                        'type'         => 'html',
                                        'value' =>     pieform($server_details),
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
                    enabled             as enabled,
                    status              as status,
                    osr.ctime           as issue_date,
                    application_uri     as application_uri,
                    application_title   as application_title,
                    application_descr   as application_descr,
                    requester_name      as requester_name,
                    requester_email     as requester_email,
                    callback_uri        as callback_uri
            FROM {oauth_server_registry} osr
            JOIN {usr} u
            ON osr.userid = u.id
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
                                'class' => 'heading',
                                'type'  => 'html',
                                'value' => get_string('application', 'auth.webservice'),
                            ),
                            'username' => array(
                                'title' => ' ',
                                'type'  => 'html',
                                'value' => get_string('owner', 'auth.webservice'),
                            ),
                            'consumer_key' => array(
                                'title' => ' ',
                                'type'  => 'html',
                                'value' => get_string('consumer_key', 'auth.webservice'),
                            ),
                            'consumer_secret' => array(
                                'title' => ' ',
                                'type'  => 'html',
                                'value' => get_string('consumer_secret', 'auth.webservice'),
                            ),
                            'enabled' => array(
                                'title' => ' ',
                                'type'  => 'html',
                                'value' => get_string('enabled'),
                            ),
                            'calback_uri' => array(
                                'title' => ' ',
                                'type'  => 'html',
                                'value' => get_string('callback', 'auth.webservice'),
                            ),
                            'consumer_secret' => array(
                                'title' => ' ',
                                'type'  => 'html',
                                'value' => get_string('consumer_secret', 'auth.webservice'),
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
                'value' => (($consumer->enabled == 1) ? display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'        => $consumer->consumer_key,
            );
            $form['elements']['id' . $consumer->id . '_calback_uri'] = array(
                'value'        =>  $consumer->callback_uri,
                'type'         => 'html',
                'key'        => $consumer->consumer_key,
            );

            // edit and delete buttons
            $form['elements']['id' . $consumer->id . '_actions'] = array(
                'value' => '<span class="actions inline">' .
                    pieform(array(
                        'name' => 'webservices_server_edit_' . $consumer->id,
                        'renderer' => 'div',
                        'elementclasses' => false,
                        'successcallback' => 'webservices_server_submit',
                        'jsform' => false,
                        'elements' => array(
                            'token' => array('type' => 'hidden', 'value' => $consumer->id),
                            'action' => array('type' => 'hidden', 'value' => 'edit'),
                            'submit' => array(
                                'type' => 'image',
                                'src' => $THEME->get_image_url('btn_edit'),
                                'alt' => get_string('editspecific', 'mahara', $consumer->id),
                                'elementtitle' => get_string('edit'),
                            ),
                        ),
                    ))
                    .
                    pieform(array(
                        'name' => 'webservices_server_delete_' . $consumer->id,
                        'renderer' => 'div',
                        'elementclasses' => false,
                        'successcallback' => 'webservices_server_submit',
                        'jsform' => false,
                        'elements' => array(
                            'token' => array('type' => 'hidden', 'value' => $consumer->id),
                            'action' => array('type' => 'hidden', 'value' => 'delete'),
                            'submit' => array(
                                'type' => 'image',
                                'src' => $THEME->get_image_url('btn_deleteremove'),
                                'alt' => get_string('deletespecific', 'mahara', $consumer->id),
                                'elementtitle' => get_string('delete'),
                            ),
                        ),
                    )) . '</span>',
                'type' => 'html',
                'key' => $consumer->consumer_key,
                'class' => 'webserviceconfigcontrols',
            );
        }
        $pieform = new Pieform($form);
        $form = $pieform->build(false);
    }

    $form = '<tr><td colspan="2">' .
        $form . '</td></tr><tr><td colspan="2">' .
        pieform(array(
            'name' => 'webservices_token_generate',
            'renderer' => 'div',
            'validatecallback' => 'webservices_add_application_validate',
            'successcallback' => 'webservices_add_application_submit',
            'class' => 'oneline inline',
            'jsform' => false,
            'action' => get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php',
            'elements' => array(
                'application' => array(
                    'type' => 'text',
                    'title' => get_string('application', 'auth.webservice') . ': ',
                ),

                'institution' => array(
                    'type' => 'select',
                    'options' => $iopts,
                ),

                'service' => array(
                    'type' => 'select',
                    'options' => $sopts,
                ),
                'action' => array('type' => 'hidden', 'value' => 'add'),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('add', 'auth.webservice'),
                ),
            ),
        )) .
        '</td></tr>';

    $elements = array(
        // fieldset for managing service function list
        'register_server' => array(
            'type' => 'fieldset',
            'legend' => get_string('userapplications', 'auth.webservice'),
            'elements' => array(
                'sflist' => array(
                    'type' => 'html',
                    'value' => $form,
                )
            ),
            'collapsible' => false,
        ),
    );

    $form = array(
        'renderer' => 'table',
        'type' => 'div',
        'id' => 'maintable',
        'name' => 'maincontainer',
        'dieaftersubmit' => false,
        'successcallback' => 'webservice_main_submit',
        'elements' => $elements,
    );

    return $form;
}
