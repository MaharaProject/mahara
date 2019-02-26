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
define('MENUITEM', 'webservices/apps');
define('INADMINMENU', 1);
define('ADMIN', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(__FILE__) . '/lib.php');
define('TITLE', get_string('webservices_title', 'auth.webservice'));
define('SUBSECTIONHEADING', get_string('apptokens', 'auth.webservice'));

// get the list of services that are available for User Access Tokens usage
// determine if there is a corresponding token for the service
$dbservices = get_records_sql_array(
    "SELECT
        es.id || '_' || (CASE WHEN et.id IS NOT NULL THEN et.id ELSE 0 END) AS dispid,
        es.id,
        es.name,
        es.enabled,
        es.restrictedusers,
        et.token,
        " . db_format_tsfield('et.mtime', 'token_mtime') . ',
        ' . db_format_tsfield('et.ctime', 'token_ctime') . ',
        et.institution,
        et.validuntil as token_validuntil,
        et.clientname,
        et.clientenv,
        esu.validuntil as user_validuntil,
        esu.iprestriction
    FROM
        {external_services} es
        LEFT JOIN {external_tokens} et
            ON et.externalserviceid = es.id
            AND et.userid = ?
            AND et.tokentype = ?
        LEFT JOIN {external_services_users} esu
            ON esu.externalserviceid = es.id
            AND esu.userid = ?
    WHERE
        es.tokenusers = 1'
    ,array(
        $USER->get('id'),
        EXTERNAL_TOKEN_USER,
        $USER->get('id')
    )
);

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
                        'client_info' => array(
                            'title' => ' ',
                            'datatable' => true,
                            'type' => 'html',
                            'value' => get_string('tokenclient', 'auth.webservice'),
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
            $userform['elements']['id' . $service->dispid . '_service_name'] = array(
                'value'        =>  $service->name,
                'type'         => 'html',
                'key'        => $service->dispid,
            );
            // is the service group enabled
            $userform['elements']['id' . $service->dispid . '_enabled'] = array(
                'value'        => (($service->enabled == 1) ?  display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'text-center',
                'key'          => $service->dispid,
            );
            // Name of the client program that generated the token
            if ($service->clientname) {
                $client = "<b>{$service->clientname}</b>";
            }
            else {
                $client = get_string('tokenclientunknown', 'auth.webservice');
            }

            if ($service->clientenv) {
                $client .= " ({$service->clientenv})";
            }

            // information about the client that generated it
            $userform['elements']['id' . $service->dispid . '_client_info'] = array(
                'value'        =>  $client,
                'type'         => 'html',
                'key'        => $service->dispid,
            );
            // token for the service if it exists
            $userform['elements']['id' . $service->dispid . '_token'] = array(
                'value'        =>  (empty($service->token) ? get_string('no_token', 'auth.webservice') : $service->token),
                'type'         => 'html',
                'key'        => $service->dispid,
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
            $userform['elements']['id' . $service->dispid . '_functions'] = array(
                'value'        =>  implode(', ', $function_list),
                'type'         => 'html',
                'key'        => $service->dispid,
            );
            // last time the token was accessed if there is a token
            $userform['elements']['id'. $service->dispid . '_last_access'] = array(
                'value'        =>  (empty($service->mtime) ? ' ' : format_date(strtotime($service->mtime))),
                'type'         => 'html',
                'key'        => $service->dispid,
            );
            // expiry date for the token if it exists
            $userform['elements']['id' . $service->dispid . '_expires'] = array(
                'value'        => (empty($service->validuntil) && empty($service->mtime) ? '' : format_date((empty($service->validuntil) ? strtotime($service->mtime) + EXTERNAL_TOKEN_USER_EXPIRES : $service->validuntil))),
                'type'         => 'html',
                'key'        => $service->dispid,
            );
            // generate button
            // delete button
            $userform['elements']['id' . $service->dispid . '_actions'] = array(
                'value'        => pieform(array(
                                    'name'            => 'webservices_user_token_generate_' . $service->dispid,
                                    'renderer'        => 'div',
                                    'elementclasses'  => false,
                                    'successcallback' => 'webservices_user_token_submit',
                                    'class'           => 'form-as-button float-left',
                                    'jsform'          => false,
                                    'elements' => array(
                                        'service'    => array('type' => 'hidden', 'value' => $service->id),
                                        'action'     => array('type' => 'hidden', 'value' => 'generate'),
                                        'submit'     => array(
                                                'type'  => 'button',
                                                'usebuttontag' => true,
                                                'class' => 'btn-secondary btn-sm',
                                                'value'   => '<span class="icon icon-refresh"></span> ' . get_string('gen', 'auth.webservice'),
                                                'elementtitle' => get_string('gen', 'auth.webservice')
                                            ),
                                    ),
                                ))
                                .
                                (empty($service->token) ? ' ' :
                                pieform(array(
                                    'name'            => 'webservices_user_token_delete_' . $service->dispid,
                                    'renderer'        => 'div',
                                    'elementclasses'  => false,
                                    'successcallback' => 'webservices_user_token_submit',
                                    'class'           => 'form-as-button float-left',
                                    'jsform'          => false,
                                    'elements' => array(
                                        'token'    => array('type' => 'hidden', 'value' => $service->token),
                                        'action'     => array('type' => 'hidden', 'value' => 'delete'),
                                        'submit'     => array(
                                                'type'  => 'button',
                                                'usebuttontag' => true,
                                                'class' => 'btn-secondary btn-sm',
                                                'value' => '<span class="icon icon-trash icon-lg text-danger left" role="presentation" aria-hidden="true"></span>' . get_string('delete'),
                                                'elementtitle' => get_string('deletespecific', 'mahara', $service->id),
                                            ),
                                    ),
                                )))
                                ,
                'type'         => 'html',
                'key'        => $service->dispid,
                'class'        => 'webserviceconfigcontrols btn-group' . (empty($service->token) ? ' only-button only-button-top' : ''),
            );
    }
    $pieform = pieform_instance($userform);
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
            'value'        => '<span class="actions text-inline">'.
                            pieform(array(
                                'name'            => 'webservices_server_delete_'.$token->id,
                                'renderer'        => 'div',
                                'elementclasses'  => false,
                                'successcallback' => 'webservices_oauth_token_submit',
                                'class'           => 'div text-inline',
                                'jsform'          => false,
                                'elements' => array(
                                    'token'      => array('type' => 'hidden', 'value' => $token->id),
                                    'action'     => array('type' => 'hidden', 'value' => 'delete'),
                                    'submit'     => array(
                                            'type'  => 'submit',
                                            'class' => 'linkbtn text-inline',
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
    $pieform = pieform_instance($oauthform);
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
        $service = get_record('external_services', 'id', $values['service'], 'tokenusers', 1);
        if (!$service) {
            $SESSION->add_error_msg(get_string('noservices', 'auth.webservice'));
        }
        else {
            // just pass the first active one for the moment
            $authinstance = get_record('auth_instance', 'id', $USER->get('authinstance'), 'active', 1);
            $token = webservice_generate_token(
                EXTERNAL_TOKEN_USER,
                $service,
                $USER->get('id'),
                $authinstance->institution,
                (time() + EXTERNAL_TOKEN_USER_EXPIRES),
                null,
                get_string('tokenmanuallycreated', 'auth.webservice')
            );
            $SESSION->add_ok_msg(get_string('token_generated', 'auth.webservice'));
        }
    }
    else if ($values['action'] == 'delete') {
        delete_records('external_tokens', 'userid', $USER->get('id'), 'token', $values['token']);
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
$pieform = pieform_instance($form);
$form = $pieform->build(false);

$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');
safe_require('auth', 'webservice');

$smarty->assign('form', $form);

$smarty->display('form.tpl');
