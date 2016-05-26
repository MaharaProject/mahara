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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/upgrade.php');
define('TITLE', get_string('webservices_title', 'auth.webservice'));

$serviceenabled = get_string('webservicesenabled', 'auth.webservice');
$servicenotenabled = get_string('webservicesnotenabled', 'auth.webservice');
$institution = param_variable('i', '');

if (empty($institution)) {
    $SESSION->add_error_msg(get_string('chooseinstitution', 'auth.webservice'), false);
    redirect(get_config('wwwroot') .'admin/users/institutions.php');
}
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
        json_reply(true, 'Unique check failed for: '.var_export($ids, true));
    }

    $cons = array();
    $idx = 0;
    foreach ($ids as $id) {
        $dbconnection = get_record('client_connections_institution', 'id', $id, 'institution', $institution);
        if (empty($dbconnection)) {
            json_reply(true, 'connection not found for: '.$id);
        }
        else {
            $dbconnection->priority = $idx++;
            $cons[]= $dbconnection;
        }
    }
    if (count($cons) != $len) {
        json_reply(true, 'Not all connections found: '.var_export($ids, true));
    }

    foreach ($cons as $c) {
        update_record('client_connections_institution', $c, array('id' => $c->id));
    }
    echo json_encode(array('rc' => 'succeeded'));
    exit();
}


function webservice_connection_definitions() {

    $connections = array();

    $plugins = array();
    $plugins['blocktype'] = array();

    foreach (plugin_types()  as $plugin) {
        // this has to happen first because of broken artefact/blocktype ordering
        $plugins[$plugin] = array();
        $plugins[$plugin]['installed'] = array();
        $plugins[$plugin]['notinstalled'] = array();
    }
    foreach (array_keys($plugins) as $plugin) {
        if (table_exists(new XMLDBTable($plugin . '_installed'))) {
            if ($installed = plugins_installed($plugin, true)) {
                foreach ($installed as $i) {
                    $key = $i->name;
                    if ($plugin == 'blocktype') {
                        $key = blocktype_single_to_namespaced($i->name, $i->artefactplugin);
                    }
                    if (!safe_require_plugin($plugin, $key)) {
                        continue;
                    }
                    if ($i->active) {
                        $classname = generate_class_name($plugin, $key);
                        if (method_exists($classname, 'define_webservice_connections')) {
                            $conns = call_static_method($classname, 'define_webservice_connections');
                            if (!empty($conns)) {
                                $connections[$classname] = array('connections' => $conns, 'type' => $plugin, 'key' => $key);
                            }
                        }
                    }
                    if ($plugin == 'artefact') {
                        safe_require('artefact', $key);
                        if ($types = call_static_method(generate_class_name('artefact', $i->name), 'get_artefact_types')) {
                            foreach ($types as $t) {
                                $classname = generate_artefact_class_name($t);
                                if (method_exists($classname, 'define_webservice_connections')) {
                                    $conns = call_static_method($classname, 'define_webservice_connections');
                                    if (!empty($conns)) {
                                        $connections[$classname] = array('connections' => $conns, 'type' => $plugin, 'key' => $key);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $connections;
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

    $elements = array(
            // fieldset for managing service function groups
            'pluginconnections' => array(
                                'type' => 'fieldset',
                                'legend' => get_string('pluginconnections', 'auth.webservice'),
                                'elements' => array(
                                    'sfgdescription' => array(
                                        'value' => '<div><p>' . get_string('pcdescription', 'auth.webservice') . '</p></div>'
                                    ),
                                    'webservicesservicecontainer' => array(
                                        'type'         => 'html',
                                        'value' => webservice_connection_classes($institution),
                                    )
                                ),
                                'collapsible' => true,
                                'collapsed'   => false,
                                'name' => 'plugin_connections',
                            ),
    );

    $form = array(
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
    // $smarty->assign_by_ref('authtypes', $authtypes);
    $smarty->assign_by_ref('instancelist', $connection_instances);
    $smarty->assign_by_ref('connections', $connections);
    $smarty->assign('institution', $institution);
    $smarty->assign('instancestring', $instancestring);
    $smarty->assign('sesskey', $USER->get('sesskey'));
    return $smarty->fetch('connections.tpl');
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
$form = webservice_client_connections($institution);
$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');
safe_require('auth', 'webservice');


$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('pagedescription', get_string('webserviceconnectionsconfigdesc', 'auth.webservice'));
$smarty->assign('subsectionheading', get_field('institution', 'displayname', 'name', $institution));
$smarty->display('auth:webservice:configform.tpl');
