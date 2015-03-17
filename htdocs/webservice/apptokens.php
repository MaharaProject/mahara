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

/*
 * This page is managing the two types of user tokens:
 *  * User Access Tokens
 *      the user can generate, and delete, as well as view
 *      access times
 *
 *  * OAuth access tokens
 *      the user can delete tokens, and view access times
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'configextensions/webservices/apps');
define('INADMINMENU', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(__FILE__) . '/lib.php');
define('TITLE', get_string('apptokens', 'auth.webservice'));
require_once('pieforms/pieform.php');

/*
 * get the list of services that are available for User Access Tokens usage
 * determine if there is a corresponding token for the service
 */
$dbservices = get_records_array('external_services', 'tokenusers', 1);
foreach ($dbservices as $dbservice) {
    $dbtoken = get_record('external_tokens', 'externalserviceid', $dbservice->id, 'userid', $USER->get('id'), 'tokentype', EXTERNAL_TOKEN_USER);
    if ($dbtoken) {
        $dbservice->token = $dbtoken->token;
        $dbservice->ctime = $dbtoken->ctime;
        $dbservice->mtime = $dbtoken->mtime;
        $dbservice->institution = $dbtoken->institution;
        $dbservice->validuntil = $dbtoken->validuntil;
    }
    else {
        $dbservice->validuntil = 0;
    }
}

/*
 * display the access tokens for services
 */
$userform = get_string('notokens', 'auth.webservice');
if (!empty($dbservices)) {
    $userform = array(
        'name'            => 'webservices_user_tokens',
        'elementclasses'  => false,
        'successcallback' => 'webservices_user_tokens_submit',
        'renderer'   => 'multicolumntable',
        'elements'   => array(
                        'service_name' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type'  => 'html',
                            'value' => get_string('serviceaccess', 'auth.webservice'),
                        ),
                        'enabled' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type'  => 'html',
                            'value' => get_string('enabled'),
                        ),
                        'token' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type'  => 'html',
                            'value' => get_string('token', 'auth.webservice'),
                        ),
                        'functions' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type'  => 'html',
                            'value' => get_string('functions', 'auth.webservice'),
                        ),
                        'last_access' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type'  => 'html',
                            'value' => get_string('last_access', 'auth.webservice'),
                        ),
                        'expires' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type'  => 'html',
                            'value' => get_string('expires', 'auth.webservice'),
                        ),
                        'actions' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type' => 'html',
                            'value' => '',
                        ),
                    ),
        );
        foreach ($dbservices as $service) {
            // name of the service group
            $userform['elements']['id' . $service->id . '_service_name'] = array(
                'value'        =>  $service->name,
                'type'         => 'html',
                'key'        => $service->id,
            );
            // is the service group enabled
            $userform['elements']['id' . $service->id . '_enabled'] = array(
                'value'        => (($service->enabled == 1) ?  display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $service->id,
            );
            // token for the service if it exists
            $userform['elements']['id' . $service->id . '_token'] = array(
                'value'        =>  (empty($service->token) ? get_string('no_token', 'auth.webservice') : $service->token),
                'type'         => 'html',
                'key'        => $service->id,
            );
            // list of functions that are available in the service group
            $functions = get_records_array('external_services_functions', 'externalserviceid', $service->id);
            $function_list = array();
            if ($functions) {
                foreach ($functions as $function) {
                    $dbfunction = get_record('external_functions', 'name', $function->functionname);
                    $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
                }
            }
            $userform['elements']['id' . $service->id . '_functions'] = array(
                'value'        =>  implode(', ', $function_list),
                'type'         => 'html',
                'key'        => $service->id,
            );
            // last time the token was accessed if there is a token
            $userform['elements']['id'. $service->id . '_last_access'] = array(
                'value'        =>  (empty($service->mtime) ? ' ' : format_date(strtotime($service->mtime))),
                'type'         => 'html',
                'key'        => $service->id,
            );
            // expiry date for the token if it exists
            $userform['elements']['id' . $service->id . '_expires'] = array(
                'value'        => (empty($service->validuntil) && empty($service->mtime) ? '' : format_date((empty($service->validuntil) ? strtotime($service->mtime) + EXTERNAL_TOKEN_USER_EXPIRES : $service->validuntil))),
                'type'         => 'html',
                'key'        => $service->id,
            );
            // generate button
            // delete button
            $userform['elements']['id' . $service->id . '_actions'] = array(
                'value'        => pieform(array(
                                    'name'            => 'webservices_user_token_generate_' . $service->id,
                                    'renderer'        => 'div',
                                    'elementclasses'  => false,
                                    'successcallback' => 'webservices_user_token_submit',
                                    'jsform'          => false,
                                    'elements' => array(
                                        'service'    => array('type' => 'hidden', 'value' => $service->id),
                                        'action'     => array('type' => 'hidden', 'value' => 'generate'),
                                        'submit'     => array(
                                                'type'  => 'image',
                                                'src'   => $THEME->get_image_url('btn_configure'),
                                                'alt'   => get_string('gen', 'auth.webservice'),
                                                'elementtitle' => get_string('gen', 'auth.webservice')
                                            ),
                                    ),
                                ))
                                .
                                (empty($service->token) ? ' ' :
                                pieform(array(
                                    'name'            => 'webservices_user_token_delete_' . $service->id,
                                    'renderer'        => 'div',
                                    'elementclasses'  => false,
                                    'successcallback' => 'webservices_user_token_submit',
                                    'jsform'          => false,
                                    'elements' => array(
                                        'service'    => array('type' => 'hidden', 'value' => $service->id),
                                        'action'     => array('type' => 'hidden', 'value' => 'delete'),
                                        'submit'     => array(
                                                'type'  => 'image',
                                                'src' => $THEME->get_image_url('btn_deleteremove'),
                                                'alt' => get_string('deletespecific', 'mahara', $service->id),
                                                'elementtitle' => get_string('delete'),
                                            ),
                                    ),
                                )))
                                ,
                'type'         => 'html',
                'key'        => $service->id,
                'class'        => 'webserviceconfigcontrols btns2 right',
            );
    }
    $pieform = new Pieform($userform);
    $userform = $pieform->build(false);
}

/*
 * get the list of OAuth acccess tokens for this user
 */
$dbtokens = get_records_sql_assoc('
        SELECT  ost.id                  as id,
                ost.token               as token,
                ost.ctime               as ctime,
                osr.institution         as institution,
                osr.externalserviceid   as externalserviceid,
                es.name                 as service_name,
                osr.consumer_key        as consumer_key,
                osr.consumer_secret     as consumer_secret,
                osr.enabled             as enabled,
                osr.status              as status,
                osr.ctime               as issue_date,
                osr.application_uri     as application_uri,
                osr.application_title   as application_title,
                osr.application_descr   as application_descr,
                osr.requester_name      as requester_name,
                osr.requester_email     as requester_email,
                osr.callback_uri        as callback_uri
        FROM {oauth_server_token} ost
        JOIN {oauth_server_registry} osr
        ON ost.osr_id_ref = osr.id
        JOIN {external_services} es
        ON es.id = osr.externalserviceid
        WHERE ost.userid = ? AND
              ost.token_type = ?
        ORDER BY application_title, ctime desc
        ', array($USER->get('id'), 'access'));

$oauthform = get_string('notokens', 'auth.webservice');
if (!empty($dbtokens)) {
    $oauthform = array(
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
                        'service_name' => array(
                            'title' => ' ',
                            'type'  => 'html',
                            'value' => get_string('accessto', 'auth.webservice'),
                        ),
                        'token' => array(
                            'title' => ' ',
                            'type'  => 'html',
                            'value' => get_string('token', 'auth.webservice'),
                        ),
                        'functions' => array(
                            'title' => ' ',
                            'type'  => 'html',
                            'value' => get_string('functions', 'auth.webservice'),
                        ),
                        'last_access' => array(
                            'title' => ' ',
                            'type'  => 'html',
                            'value' => get_string('last_access', 'auth.webservice'),
                        ),
                    ),
        );
    foreach ($dbtokens as $token) {
        // application title associated with the access token
        $oauthform['elements']['id' . $token->id . '_application'] = array(
            'value'        =>  $token->application_title,
            'type'         => 'html',
            'key'        => $token->id,
        );
        // associated service group
        $oauthform['elements']['id' . $token->id . '_service_name'] = array(
            'value'        =>  $token->service_name,
            'type'         => 'html',
            'key'        => $token->id,
        );
        // OAuth access token
        $oauthform['elements']['id' . $token->id . '_token'] = array(
            'value'        =>  $token->token,
            'type'         => 'html',
            'key'        => $token->id,
        );
        // list of functions for this service group
        $functions = get_records_array('external_services_functions', 'externalserviceid', $token->externalserviceid);
        $function_list = array();
        if ($functions) {
            foreach ($functions as $function) {
                $dbfunction = get_record('external_functions', 'name', $function->functionname);
                $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
            }
        }
        $oauthform['elements']['id' . $token->id . '_functions'] = array(
            'value'        =>  implode(', ', $function_list),
            'type'         => 'html',
            'key'        => $token->id,
        );
        // token last access time
        $oauthform['elements']['id' . $token->id . '_last_access'] = array(
            'value'        =>  format_date(strtotime($token->ctime)),
            'type'         => 'html',
            'key'        => $token->id,
        );

        // edit and delete buttons
        $oauthform['elements']['id' . $token->id . '_actions'] = array(
            'value'        => '<span class="actions inline">'.
                            pieform(array(
                                'name'            => 'webservices_server_delete_'.$token->id,
                                'renderer'        => 'div',
                                'elementclasses'  => false,
                                'successcallback' => 'webservices_oauth_token_submit',
                                'class'           => 'oneline inline',
                                'jsform'          => false,
                                'elements' => array(
                                    'token'      => array('type' => 'hidden', 'value' => $token->id),
                                    'action'     => array('type' => 'hidden', 'value' => 'delete'),
                                    'submit'     => array(
                                            'type'  => 'submit',
                                            'class' => 'linkbtn inline',
                                            'value' => get_string('delete')
                                        ),
                                ),
                            )) . '</span>'
                            ,
            'type'         => 'html',
            'key'        => $token->id,
            'class'        => 'actions',
        );
    }
    $pieform = new Pieform($oauthform);
    $oauthform = $pieform->build(false);
}

// put together the whole page
$elements = array(
        // fieldset for managing service function list
        'user_tokens' => array(
                            'type' => 'fieldset',
                            'legend' => get_string('usertokens', 'auth.webservice'),
                            'elements' => array(
                                'sflist' => array(
                                    'type'         => 'html',
                                    'value' =>     $userform,
                                )
                            ),
                            'collapsible' => false,
                        ),
        // fieldset for managing service function list
        'oauth_tokens' => array(
                            'type' => 'fieldset',
                            'legend' => get_string('accesstokens', 'auth.webservice'),
                            'elements' => array(
                                'sflist' => array(
                                    'type'         => 'html',
                                    'value' =>     $oauthform,
                                )
                            ),
                            'collapsible' => false,
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

/**
 * handle the callback for actions on the user token panel
 *  - generate noew token
 *  - delete token
 *
 * @param Pieform $form
 * @param array $values
 */
function webservices_user_token_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    if ($values['action'] == 'generate') {
        delete_records('external_tokens', 'userid', $USER->get('id'), 'externalserviceid', $values['service']);
        $services = get_records_select_array('external_services', 'id = ? AND tokenusers = ?', array($values['service'], 1));
        if (empty($services)) {
            $SESSION->add_error_msg(get_string('noservices', 'auth.webservice'));
        }
        else {
            // just pass the first one for the moment
            $service = array_shift($services);
            $authinstance = get_record('auth_instance', 'id', $USER->get('authinstance'));
            $token = webservice_generate_token(EXTERNAL_TOKEN_USER, $service, $USER->get('id'), $authinstance->institution, (time() + EXTERNAL_TOKEN_USER_EXPIRES));
            $SESSION->add_ok_msg(get_string('token_generated', 'auth.webservice'));
        }
    }
    else if ($values['action'] == 'delete') {
        delete_records('external_tokens', 'userid', $USER->get('id'), 'externalserviceid', $values['service']);
        $SESSION->add_ok_msg(get_string('oauthtokendeleted', 'auth.webservice'));
    }
    redirect('/webservice/apptokens.php');
}

/**
 * handle callback actions on the OAuth access tokens panel
 *  - delete token
 *
 * @param Pieform $form
 * @param array $values
 */
function webservices_oauth_token_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    if ($values['action'] == 'delete') {
        delete_records('oauth_server_token', 'id', $values['token'], 'userid', $USER->get('id'));
        $SESSION->add_ok_msg(get_string('oauthtokendeleted', 'auth.webservice'));
    }
    redirect('/webservice/apptokens.php');
}

// render the page
$pieform = new pieform($form);
$form = $pieform->build(false);

$smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
safe_require('auth', 'webservice');

$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('form.tpl');
