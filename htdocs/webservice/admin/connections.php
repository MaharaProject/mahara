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
define('MENUITEM', 'webservices/connections');
define('INADMINMENU', 1);
define('ADMIN', 1);
define('SECTION_PLUGINTYPE', 'auth');
define('SECTION_PLUGINNAME', 'webservice');
define('SECTION_PAGE', 'connections');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/upgrade.php');
require_once($CFG->docroot . '/webservice/lib.php');
define('TITLE', get_string('webservices_title', 'auth.webservice'));
define('SUBSECTIONHEADING', get_string('connections', 'auth.webservice'));

$serviceenabled = get_string('webservicesenabled', 'auth.webservice');
$servicenotenabled = get_string('webservicesnotenabled', 'auth.webservice');
$institution = param_variable('i', '');

require_once('institution.php');
$institutionelement = get_institution_selector(true);
if (empty($institutionelement)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

if (empty($institution)) {
    $institution = 'mahara';
}
$institutionelement['defaultvalue'] = $institution;

$ids         = param_variable('ids', '');
$reorder     = param_boolean('reorder', 0);
$json        = param_boolean('j', 0);

if ($reorder && $json) {
    try {
        form_validate(param_alphanum('sesskey', null));
    }
    catch (UserException $e) {
        json_reply(true, $e->getMessage());
    }
    $len = count(explode(',', $ids));
    $ids = array_map("intval", explode(',', $ids));
    if (count(array_unique($ids)) != $len) {
        json_reply(true, 'Unique check failed for: ' . var_export($ids, true));
    }

    $cons = array();
    $idx = 1;
    foreach ($ids as $id) {
        $dbconnection = get_record('client_connections_institution', 'id', $id, 'institution', $institution);
        if (empty($dbconnection)) {
            json_reply(true, 'connection not found for: ' . $id);
        }
        else {
            $dbconnection->priority = $idx++;
            $cons[]= $dbconnection;
        }
    }
    if (count($cons) != $len) {
        json_reply(true, 'Not all connections found: ' . var_export($ids, true));
    }

    foreach ($cons as $c) {
        update_record('client_connections_institution', $c, array('id' => $c->id));
    }
    echo json_encode(array('rc' => 'succeeded'));
    exit();
}


/**
 *  Custom webservices config page
 *  - activate/deactivate webservices comletely
 *  - activate/deactivat protocols - SOAP/XML-RPC/REST
 *  - manage service clusters
 *  - manage users and access tokens
 *
 *  @return pieforms $element array
 */
function webservice_client_connections($institution) {
    $wcc = webservice_connection_classes($institution);
    $elements = array(
        'addconnection' => array(
            'type' => 'html',
            'value' => $wcc['addconnection'],
        ),
        // fieldset for managing service function groups
        'pluginconnections' => array(
            'type' => 'fieldset',
            'legend' => get_string('pluginconnections', 'auth.webservice'),
            'elements' => array(
                'webservicesservicecontainer' => array(
                    'type' => 'html',
                    'value' => $wcc['instances'],
                )
            ),
            'collapsible' => true,
            'collapsed' => false,
            'name' => 'plugin_connections',
        ),
    );

    $form = array(
        'name' => 'connectionform',
        'renderer' => 'div',
        'type' => 'div',
        'elements' => $elements,
    );

    return $form;
}


/**
 * Service Function Groups edit form
 *
 * @return html
 */
function webservice_connection_classes($institution) {
    global $USER;

    $plugin_connections = webservice_connection_definitions();
    $connections = array();
    foreach ($plugin_connections as $plugin => $objects) {
        foreach ($objects['connections'] as $object) {
            $id = $objects['type'].':'.$objects['key'].':'.$object['connection'];
            $connections[$id] = (object)array('id' => $id, 'name' => $object['name'], 'type' => $objects['type'], 'key' => $objects['key'], 'shortname' => $plugin.':'.$object['connection']);
        }
    }

    $instancearray = array();
    $data = get_records_assoc('client_connections_institution', 'institution', $institution, 'priority, name');

    $connection_instances = array();
    if (!empty($data)) {
        $c = count($data);
        $idx = 0;
        foreach($data as $val) {
            $instancearray[] = (int)$val->id;
            $val->index = $idx;
            $val->total = $c;
            $idx++;
            $connection_instances[]= $val;
        }
    }

    $instancestring = implode(',',$instancearray);

    $smarty = smarty_core();
    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';
    // $smarty->assign('authtypes', $authtypes);
    $smarty->assign('instancelist', $connection_instances);
    $smarty->assign('connections', $connections);
    $smarty->assign('institution', $institution);
    $addhtml = $smarty->fetch('auth:webservice:addconnectionform.tpl');
    $smarty->assign('instancestring', $instancestring);
    $smarty->assign('sesskey', $USER->get('sesskey'));
    $html = $smarty->fetch('auth:webservice:connections.tpl');
    return array('instances' => $html, 'addconnection' => $addhtml);
}


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
            $authinstance = get_record('auth_instance', 'id', $USER->get('authinstance'), 'active', 1);
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
$js = <<< EOF
jQuery(function($) {
    if ($('#institutionselect_institution').length) {
        $('#institutionselect_institution').on('change', function() {
            window.location.replace(config.wwwroot + 'webservice/admin/connections.php?i=' + $('#institutionselect_institution').val());
        });
    }
});
EOF;

$institutionselector = pieform(array(
    'name' => 'institutionselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));

// render the page
$form = webservice_client_connections($institution);

$smarty = smarty();
setpageicon($smarty, 'icon-plug');
safe_require('auth', 'webservice');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('institutionselector', $institutionselector);
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('pagedescription', get_string('webserviceconnectionsconfigdesc', 'auth.webservice'));
$smarty->assign('subsectionheading', get_field('institution', 'displayname', 'name', $institution));
$smarty->display('auth:webservice:configform.tpl');
