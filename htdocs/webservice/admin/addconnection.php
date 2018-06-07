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
define('TITLE', get_string('webservices_title', 'auth.webservice'));
define('SUBSECTIONHEADING', get_string('addconnection', 'auth.webservice'));
require_once(get_config('docroot') . '/lib/htmloutput.php');

$institution = param_variable('i');
$connector   = param_variable('p', '');
$connectionid = param_variable('id', 0);
$add         = param_boolean('add', 0);
$edit        = param_boolean('edit', 0);
$delete      = param_boolean('delete', 0);


if (!$dbinstitution = get_record('institution', 'name', $institution)) {
    throw new MaharaException('addconnection - institution not found: ' . $institution);
}

$dbconnection = get_record('client_connections_institution', 'id', $connectionid);
if (empty($dbconnection)) {
    list($type, $plugin, $connector_instance) = explode(':', $connector);
    $classname = 'Plugin'. ucfirst(strtolower($type)) . ucfirst(strtolower($plugin));
    $dbconnection = (object) array('name' => '',
                                    'url' => '',
                                    'isfatal' => false,
                                    'json' => false,
                                    'enable' => false,
                                    'useheader' => false,
                                    'parameters' => '',
                                    'username' => '',
                                    'password' => '',
                                    'consumer' => '',
                                    'secret' => '',
                                    'token' => '',
                                    'header' => '',
                                    'certificate' => '',
                                    'plugintype' => $type,
                                    'pluginname' => $plugin,
                                    'connection' => $connector_instance,
                                    'class' => $classname);
}
else {
    $type = $dbconnection->plugintype;
    $plugin = $dbconnection->pluginname;
    $connector_instance = $dbconnection->connection;
    $classname = $dbconnection->class;
}
safe_require($type, strtolower($plugin));
$plugin_desc = get_string('name', strtolower($type).".".strtolower($plugin));

if ($delete) {
    try {
        form_validate(param_alphanum('sesskey', null));
    }
    catch (UserException $e) {
        json_reply(true, $e->getMessage());
    }

    if (!delete_records('client_connections_institution', 'id', $connectionid)) {
        $rc = 'failed';
    }
    else {
        $rc = 'succeeded';
    }
    echo json_encode(array('rc' => $rc));
    exit();
}

function allocate_client_connection_cancel_submit(Pieform $form) {
    $institution = $form->get_element('i');
    redirect(get_config('wwwroot') . 'webservice/admin/connections.php?i=' . $institution['value']);
}

function allocate_client_connection_validate(Pieform $form, $values) {
    global $SESSION;

    $dbinstitution = get_record('institution', 'name', $values['i']);
    if (empty($dbinstitution) || empty($values['p'])) {
        $form->set_error(null, "An unknown error occurred while processing this form");
    }

    list($type, $plugin, $connector_instance) = explode(':', $values['p']);
    $classname = 'Plugin'. ucfirst(strtolower($type)) . ucfirst(strtolower($plugin));
    safe_require($type, strtolower($plugin));

    // check that the name is not already used
    if ($values['id'] > 0) {
        if ($results = get_records_sql_assoc(
            'SELECT cci.*
             FROM client_connections_institution AS cci
             WHERE cci.name = ? AND
               cci.id <> ? AND
               cci.institution = ? ', array($values['name'], $values['id'], $values['i']))) {
            $form->set_error(null, get_string('nameexists', 'auth.webservice'));
        }
    }
    else {
        $clientconnection = get_record('client_connections_institution', 'name', $values['name']);
        if ($clientconnection) {
            $form->set_error('name', get_string('nameexists', 'auth.webservice'));
        }
    }

    if (($values['type'] == 'rest'  && !in_array($values['authtype'], array('token', 'user', 'oauth1'))) || ($values['type'] == 'soap') && !in_array($values['authtype'], array('token', 'user', 'wsse'))) {
        $form->set_error('authtype', get_string('invalidauthtypecombination', 'auth.webservice', $values['type']));
    }

    if ($values['type'] == 'xmlrpc' && !in_array($values['authtype'], array('cert', 'token', 'user'))) {
        $form->set_error('authtype', get_string('invalidauthtypecombination', 'auth.webservice', $values['type']));
    }

    if ($values['authtype'] == 'token' && empty($values['token'])) {
        $form->set_error('token', get_string('emptytoken', 'auth.webservice'));
    }

    if ($values['authtype'] == 'oauth1') {
        if (empty($values['consumer'])) {
            $form->set_error('consumer', get_string('emptyoauthkey', 'auth.webservice'));
        }
        if (empty($values['secret'])) {
            $form->set_error('secret', get_string('emptyoauthsecret', 'auth.webservice'));
        }
    }

    if (($values['authtype'] == 'user' || $values['authtype'] == 'wsse')) {
        if (empty($values['username'])) {
            $form->set_error('username', get_string('emptyuser', 'auth.webservice'));
        }
        if (empty($values['password'])) {
            $form->set_error('password', get_string('emptyuserpass', 'auth.webservice'));
        }
    }

    if ($values['authtype'] == 'cert' && empty($values['certificate'])) {
        $form->set_error('certificate', get_string('emptycert', 'auth.webservice'));
    }

    if ($values['authtype'] == 'cert' && empty($values['username']) && empty($values['token'])) {
        $form->set_error('certificate', get_string('emptycertextended', 'auth.webservice'));
    }
}

function allocate_client_connection_submit(Pieform $form, $values) {
    global $SESSION;

    $clientconnection = new stdClass();

    if ($values['id'] > 0) {
        $values['create'] = false;
        $clientconnection = get_record('client_connections_institution', 'id', $values['id']);
        $clientconnection->id = $values['id'];
    }
    else {
        $values['create'] = true;
        $clientconnection->institution  = $values['i'];
        list($type, $plugin, $connector_instance) = explode(':', $values['p']);
        $classname = 'Plugin'. ucfirst(strtolower($type)) . ucfirst(strtolower($plugin));
        $max = get_field('client_connections_institution', 'MAX(priority)', 'institution', $clientconnection->institution);
        if (empty($max)) {
            $clientconnection->priority  = 1;
        }
        else {
            $clientconnection->priority  = $max + 1;
        }
        $clientconnection->plugintype  = $type;
        $clientconnection->pluginname  = $plugin;
        $clientconnection->class  = $classname;
        $clientconnection->connection  = $connector_instance;
    }

    $clientconnection->url = $values['url'];
    $clientconnection->name = $values['name'];
    $clientconnection->username = $values['username'];
    $clientconnection->password = $values['password'];
    $clientconnection->consumer = $values['consumer'];
    $clientconnection->secret = $values['secret'];
    $clientconnection->token = $values['token'];
    $clientconnection->certificate = $values['certificate'];
    $clientconnection->parameters = $values['parameters'];
    $clientconnection->enable = (int) $values['enable'];
    $clientconnection->isfatal = (int) $values['isfatal'];
    $clientconnection->useheader = (int) $values['useheader'];
    $clientconnection->header = $values['header'];
    $clientconnection->type = $values['type'];
    if ($clientconnection->type != 'rest') {
        $clientconnection->json = 0;
        $clientconnection->useheader = 0;
    }
    else {
        $clientconnection->json = (int) $values['json'];
    }
    $clientconnection->authtype = $values['authtype'];
    if ($values['authtype'] != 'token' && $values['authtype'] != 'cert') {
        $clientconnection->token =  '';
    }
    if ($values['authtype'] != 'user' && $values['authtype'] != 'wsse' && $values['authtype'] != 'cert') {
        $clientconnection->username = '';
        $clientconnection->password = '';
    }
    if ($values['authtype'] != 'cert') {
        $clientconnection->certificate =  '';
    }
    if (!$clientconnection->useheader) {
        $clientconnection->header = '';
    }
    if ($values['authtype'] != 'oauth1') {
        $clientconnection->consumer =  '';
        $clientconnection->secret =  '';
    }
    if ($values['create']) {
        $values['id'] = insert_record('client_connections_institution', $clientconnection, 'id', true);
    }
    else {
        update_record('client_connections_institution', $clientconnection, array('id' => $values['id']));
    }
    redirect(get_config('wwwroot') . 'webservice/admin/connections.php?i=' . $values['i']);
}

$js = <<<EOF

function allocate_client_connection() {
}

function update_auth_options() {
    var current = jQuery('#allocate_client_connection_authtype').val();

    if ('token' == current || 'cert' == current) {
        jQuery('#allocate_client_connection_token_container').removeClass('d-none');
        jQuery('#allocate_client_connection_useheader_container').removeClass('d-none');
        update_useheader_options();
    }
    else {
        jQuery('#allocate_client_connection_token_container').addClass('d-none');
        jQuery('#allocate_client_connection_useheader_container').addClass('d-none');
        jQuery('#allocate_client_connection_header_container').addClass('d-none');
    }

    if ('oauth1' == current) {
        jQuery('#allocate_client_connection_consumer_container').removeClass('d-none');
        jQuery('#allocate_client_connection_secret_container').removeClass('d-none');
    }
    else {
        jQuery('#allocate_client_connection_consumer_container').addClass('d-none');
        jQuery('#allocate_client_connection_secret_container').addClass('d-none');
    }

    if ('user' == current || 'cert' == current || 'wsse' == current) {
        jQuery('#allocate_client_connection_username_container').removeClass('d-none');
        jQuery('#allocate_client_connection_password_container').removeClass('d-none');
    }
    else {
        jQuery('#allocate_client_connection_username_container').addClass('d-none');
        jQuery('#allocate_client_connection_password_container').addClass('d-none');
    }

    if ('cert' == current) {
        jQuery('#allocate_client_connection_certificate_container').removeClass('d-none');
    }
    else {
        jQuery('#allocate_client_connection_certificate_container').addClass('d-none');
    }
}

function update_type_options() {
    var current = jQuery('#allocate_client_connection_type').val();
    if ('rest' == current || 'oauth1' == current) {
        jQuery('#allocate_client_connection_json_container').removeClass('d-none');
    }
    else {
        jQuery('#allocate_client_connection_json_container').addClass('d-none');
    }
}


function update_useheader_options() {
    var checked = jQuery('#allocate_client_connection_useheader:checked').length;
    if (checked) {
        jQuery('#allocate_client_connection_header_container').removeClass('d-none');
    }
    else {
        jQuery('#allocate_client_connection_header_container').addClass('d-none');
    }
}

jQuery(function() {

    jQuery('#allocate_client_connection_authtype').on('change', update_auth_options);
    update_auth_options();
    jQuery('#allocate_client_connection_type').on('change', update_type_options);
    update_type_options();
    jQuery('#allocate_client_connection_useheader').on('click', update_useheader_options);

});
EOF;

$connection_details =
    array(
        'id'               => 'maintable',
        'class'            => 'form-group-nested',
        'name'             => 'allocate_client_connection',
        'successcallback'  => 'allocate_client_connection_submit',
        'validatecallback' => 'allocate_client_connection_validate',
        'jsform'           => false,
        'renderer'         => 'div',
        'elements'   => array(
                        'id' => array(
                            'type'  => 'hidden',
                            'value' => $connectionid,
                        ),
                        'i' => array(
                            'type'  => 'hidden',
                            'value' => $institution,
                        ),
                        'p' => array(
                            'type'  => 'hidden',
                            'value' => implode(':', array($type, $plugin, $connector_instance)),
                        ),
                    ),
        );

$connection_details['elements']['plugin'] = array(
    'title'        => get_string('plugin', 'auth.webservice'),
    'value'        =>  ucfirst($type).'/'.$plugin_desc,
    'type'         => 'html',
);

$connection_details['elements']['institution'] = array(
    'type'         => 'html',
    'title'        => get_string('institution'),
    'value'        => $dbinstitution->displayname,
);

$connection_details['elements']['name'] = array(
    'defaultvalue' => $dbconnection->name,
    'type'         => 'text',
    'rules'        => array('required' => true, 'minlength' => 5, 'maxlength' => 255),
    'size'         => 50,
    'disabled'     => false,
    'title'        => get_string('name', 'auth.webservice'),
);

$connection_details['elements']['enable'] = array(
    'defaultvalue' => (($dbconnection->enable == 1) ? 'checked' : ''),
    'type'         => 'switchbox',
    'disabled'     => false,
    'title'        => get_string('enable', 'auth.webservice'),
);

// add protocol choice
$typeopts = array();
foreach (array('rest', 'soap', 'xmlrpc') as $proto) {
    $typeopts[$proto] = get_string($proto, 'auth.webservice');
}
$typeopts_keys = array_keys($typeopts);
$default_type = (empty($dbconnection->type) ?  array_shift($typeopts_keys) : $dbconnection->type);
$connection_details['elements']['type'] = array(
    'type'         => 'select',
    'title'        => get_string('type', 'auth.webservice'),
    'options'      => $typeopts,
    'defaultvalue' => trim($default_type),
    'disabled'     => false,
);

// add auth method
$aopts = array();
foreach (array('token', 'user', 'cert', 'wsse', 'oauth1') as $auth) {
    $aopts[$auth] = get_string($auth . 'auth', 'auth.webservice');
}
$default_authtype = (empty($dbconnection->authtype) ? 'token' : $dbconnection->authtype);
$connection_details['elements']['authtype'] = array(
    'type'         => 'select',
    'title'        => get_string('authtype', 'auth.webservice'),
    'options'      => $aopts,
    'defaultvalue' => trim($default_authtype),
    'disabled'     => false,
);

$connection_details['elements']['url'] = array(
    'defaultvalue' => $dbconnection->url,
    'type'         => 'text',
    'size'         => 50,
    'disabled'     => false,
    'title'        => get_string('clienturl', 'auth.webservice'),
);

$connection_details['elements']['username'] = array(
    'defaultvalue' => $dbconnection->username,
    'type'         => 'text',
    'size'         => 20,
    'disabled'     => false,
    'title'        => get_string('username', 'auth.webservice'),
);

$connection_details['elements']['password'] = array(
    'defaultvalue' => $dbconnection->password,
    'type'         => 'password',
    'size'         => 20,
    'disabled'     => false,
    'title'        => get_string('password', 'auth.webservice'),
);

$connection_details['elements']['consumer'] = array(
    'defaultvalue' => $dbconnection->consumer,
    'type'         => 'text',
    'size'         => 20,
    'disabled'     => false,
    'title'        => get_string('consumer', 'auth.webservice'),
);

$connection_details['elements']['secret'] = array(
    'defaultvalue' => $dbconnection->secret,
    'type'         => 'text',
    'size'         => 20,
    'disabled'     => false,
    'title'        => get_string('secret', 'auth.webservice'),
);

$connection_details['elements']['token'] = array(
    'defaultvalue' => $dbconnection->token,
    'type'         => 'text',
    'size'         => 50,
    'disabled'     => false,
    'title'        => get_string('token', 'auth.webservice'),
);

$connection_details['elements']['useheader'] = array(
    'title'        => get_string('useheader', 'auth.webservice'),
    'defaultvalue' => (($dbconnection->useheader == 1) ? 'checked' : ''),
    'type'         => 'switchbox',
    'disabled'     => false,
);

$connection_details['elements']['header'] = array(
    'defaultvalue' => $dbconnection->header,
    'type'         => 'text',
    'size'         => 50,
    'disabled'     => false,
    'title'        => get_string('header', 'auth.webservice'),
);


$connection_details['elements']['parameters'] = array(
    'type' => 'textarea',
    'title' => get_string('parameters', 'auth.webservice'),
    'defaultvalue' => $dbconnection->parameters,
    'rows' => 15,
    'cols' => 90,
);

// form-control textarea resizable
$connection_details['elements']['certificate'] = array(
    'type' => 'textarea',
    'title' => get_string('certificate', 'auth.webservice'),
    'defaultvalue' => $dbconnection->certificate,
    'style' => 'font-family: Monospace;',
    'rows' => 15,
    'cols' => 90,
);

$connection_details['elements']['json'] = array(
    'defaultvalue' => (($dbconnection->json == 1) ? 'checked' : ''),
    'type'         => 'switchbox',
    'disabled'     => false,
    'title'        => get_string('json', 'auth.webservice'),
);

$connection_details['elements']['isfatal'] = array(
    'defaultvalue' => (($dbconnection->isfatal == 1) ? 'checked' : ''),
    'type'         => 'switchbox',
    'disabled'     => false,
    'title'        => get_string('isfatal', 'auth.webservice'),
);

$connection_details['elements']['submit'] = array(
    'type'  => 'submitcancel',
    'class' => 'btn-primary',
    'value' => array(get_string('submit'), get_string('cancel')),
    'goto'  => 'addconnection.php?c=1',
);

$form = pieform($connection_details);
$smarty = smarty();
if ($add) {
    $smarty->assign('PAGETITLE', get_string('addconnection', 'auth.webservice'));
}
else {
    $smarty->assign('PAGETITLE', get_string('editconnection', 'auth.webservice'));
}
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('auth:webservice:addconnection.tpl');
