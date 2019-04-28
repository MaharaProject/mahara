<?php
/**
 *
 * @package    mahara
 * @subpackage module-mobileapi
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * This page lets non-admin users manage which apps they've granted
 * webservice access tokens to.
 *
 * See /webservice/apptokens.php for the more complex admin version.
 */
define('INTERNAL', 1);
define('MENUITEM', 'settings/webservice');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'webservice');
define('APPS', 1);

require('./../../init.php');
require_once($CFG->docroot . 'webservice/lib.php');
safe_require('module', 'mobileapi');
define('TITLE', get_string('connectedapps'));
define('SUBSECTIONHEADING', get_string('mytokensmenutitle1', 'module.mobileapi'));

// Users shouldn't be able to access this page if webservices are not enabled.
if (!PluginModuleMobileapi::is_service_ready()) {
    throw new AccessDeniedException(get_string('featuredisabled', 'auth.webservice'));
}

// get the list of services that are available for User Access Tokens usage
// determine if there is a corresponding token for the service
$dbservices = get_records_sql_array(
    "SELECT
        es.id || '_' || et.id || '_' || es.id as dispid,
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
        es.tokenusers = 1
        AND (es.restrictedusers = 0 OR esu.id IS NOT NULL)
        AND (et.id IS NOT NULL OR esu.id IS NOT NULL)'
    ,array(
        $USER->get('id'),
        EXTERNAL_TOKEN_USER,
        $USER->get('id')
    )
);

/*
 * display the access tokens for services
 */
if (empty($dbservices)) {
    $userform = get_string('nopersonaltokens', 'module.mobileapi');
}
else {
    $userform = array(
        'name'            => 'webservices_user_tokens',
        'elementclasses'  => false,
        'successcallback' => 'webservices_user_tokens_submit',
        'renderer'   => 'multicolumntable',
    );
    $elements = array();
    $elements['client_info'] = array(
        'title' => ' ',
        'datatable' => true,
        'type' => 'html',
        'value' => get_string('clientinfo', 'module.mobileapi'),
    );

    if (get_config_plugin('module', 'mobileapi', 'manualtokens')) {
        $elements['token'] = array(
            'title' => ' ',
            'datatable' => true,
            'type'  => 'html',
            'value' => get_string('token', 'module.mobileapi'),
        );
    }

    $elements['created'] = array(
        'title' => ' ',
        'datatable' => true,
        'type'  => 'html',
        'value' => get_string('tokencreated', 'module.mobileapi'),
    );

    // Action buttons (no title)
    $elements['actions'] = array(
        'title' => ' ',
        'datatable' => true,
        'type' => 'html',
        'value' => '',
    );
    $userform['elements'] = $elements;

    foreach ($dbservices as $service) {

        $client = '<h3 class="title">';
        if ($service->clientname) {
            $client .= $service->clientname;
        }
        else {
            $client .= get_string('clientnotspecified', 'module.mobileapi');
        }
        $client .= '</h3>';

        if ($service->clientenv) {
            $client .= " ({$service->clientenv})";
        }

        // information about the client that generated it
        $userform['elements']['id' . $service->dispid . '_client_info'] = array(
            'value'        =>  $client,
            'type'         => 'html',
            'key'        => $service->dispid,
        );

        if (get_config_plugin('module', 'mobileapi', 'manualtokens')) {
            $userform['elements']['id' . $service->dispid . '_token'] = array(
                'value'        =>  $service->token,
                'type'         => 'html',
                'key'        => $service->dispid,
            );
        }

        $userform['elements']['id' . $service->dispid . '_ctime'] = array(
            'value' => format_date($service->token_ctime),
            'type' => 'html',
            'key' => $service->dispid,
        );

        // generate button
        // delete button
        $userform['elements']['id' . $service->dispid . '_actions'] = array(
            'value' => pieform(
                array(
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
                                'elementtitle' => get_string('deletespecific', 'mahara', $service->clientname),
                            ),
                    ),
                )
            ),
            'type'         => 'html',
            'key'        => $service->dispid,
            'class'        => 'webserviceconfigcontrols' . (empty($service->token) ? ' only-button only-button-top' : ''),
        );
    }
    $pieform = pieform_instance($userform);
    $userform = $pieform->build(false);
}

$page_elements = array(
    // fieldset for managing service function list
    'user_tokens' => array(
        'type' => 'fieldset',
        'legend' => get_string('mytokenspagedesc', 'module.mobileapi'),
        'elements' => array(
            'sflist' => array(
                'type'         => 'html',
                'value' =>     $userform,
            )
        ),
        'collapsible' => false,
    )
);

// TODO: Currently this is hardcoded to only allow self-generation of the
// maharamobile service.
$service = get_record('external_services', 'component', 'module/mobileapi', 'shortname', 'maharamobile');
if (get_config_plugin('module', 'mobileapi', 'manualtokens')) {
    $page_elements['generate_user_token'] = array(
        'type' => 'fieldset',
        'legend' => get_string('generateusertoken', 'module.mobileapi'),
        'elements' => array(
            'generate_user_token_html' => array(
                'type' => 'html',
                'value' => pieform(
                    array(
                        'name'            => 'webservices_user_token_generate_' . $service->id,
                        'renderer'        => 'div',
                        'elementclasses'  => false,
                        'successcallback' => 'webservices_user_token_submit',
                        'class'           => 'form-as-button float-left',
                        'jsform'          => false,
                        'elements' => array(
                            'action'     => array('type' => 'hidden', 'value' => 'generate'),
                            'submit'     => array(
                                    'type'  => 'button',
                                    'usebuttontag' => true,
                                    'class' => 'btn-secondary btn-sm',
                                    'value'   => '<span class="icon icon-refresh"></span> ' . get_string('gen', 'auth.webservice'),
                                    'elementtitle' => get_string('gen', 'auth.webservice')
                                ),
                        ),
                    )
                )
            )
        )
    );
}

$form = array(
    'renderer' => 'div',
    'type' => 'div',
    'id' => 'maintable',
    'name' => 'maincontainer',
    'dieaftersubmit' => false,
    'successcallback' => 'webservice_main_submit',
    'elements' => $page_elements,
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
        // TODO: Currently this is hard-coded to only the maharamobile service
        if (
            get_config_plugin('module', 'mobileapi', 'manualtokens')
            && ($service = get_record('external_services', 'component', 'module/mobileapi', 'shortname', 'maharamobile', 'tokenusers', 1))
        ) {
            $token = webservice_generate_token(
                EXTERNAL_TOKEN_USER,
                $service,
                $USER->get('id'),
                null,
                null,
                null,
                get_string('tokenmanuallycreated', 'auth.webservice')
            );
            $SESSION->add_ok_msg(get_string('token_generated', 'auth.webservice'));
        }
        else {
            $SESSION->add_error_msg(get_string('noservices', 'auth.webservice'));
        }
    }
    else if ($values['action'] == 'delete') {
        delete_records('external_tokens', 'userid', $USER->get('id'), 'token', $values['token']);
        $SESSION->add_ok_msg(get_string('appaccessrevoked', 'module.mobileapi'));
    }
    redirect('/module/mobileapi/apps.php');
}

// render the page
$pieform = pieform_instance($form);
$form = $pieform->build(false);

$smarty = smarty();
setpageicon($smarty, 'icon-globe');
safe_require('auth', 'webservice');

$smarty->assign('form', $form);

$smarty->display('form.tpl');
