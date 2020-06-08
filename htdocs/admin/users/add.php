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
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/adduser');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('adduser', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
require_once('institution.php');

$TRANSPORTER = null;

if ($USER->get('admin')) {
    $authinstances = auth_get_auth_instances();
}
else {
    $admininstitutions = $USER->get('admininstitutions');
    $authinstances = auth_get_auth_instances_for_institutions($admininstitutions);
    if (empty($authinstances)) {
        $SESSION->add_info_msg(get_string('configureauthplugin', 'admin'));
        redirect(get_config('wwwroot').'admin/users/institutions.php?i='.key($admininstitutions).'&amp;edit=1');
    }
}

$authinstancecount = count($authinstances);

if ($authinstancecount) {
    $options = array();

    $external = false;
    foreach ($authinstances as $authinstance) {
        if ($USER->can_edit_institution($authinstance->name)) {
            $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
            if ($authinstance->authname != 'internal') {
                $external = true;
            }
        }
    }
}

$elements = array(
    'firstname' => array(
        'type'    => 'text',
        'title'   => get_string('firstname'),
        'rules'   => array('required' => true),
        'class' => 'form-control text',
    ),
    'lastname' => array(
        'type'    => 'text',
        'title'   => get_string('lastname'),
        'rules'   => array('required' => true),
        'class' => 'form-control text',
    ),
    'email' => array(
        'type'    => 'text',
        'title'   => get_string('email'),
        'rules'   => array('required' => true),
        'class' => 'form-control text',
    ),
    'leap2afile' => array(
        'type' => 'file',
        'class' => 'leap2aupload',
        'title' => '',
    ),
    'username' => array(
        'type' => 'text',
        'title' => get_string('username'),
        'rules' => array(
            'required' => true,
            'maxlength' => 236,
        ),
    ),
    'password' => array(
        'type' => 'password',
        'title' => get_string('password'),
        'rules' => array('required' => true),
        'description' => get_password_policy_description(),
        'showstrength' => true,
    ),
    'staff' => array(
        'type' => 'switchbox',
        'title' => get_string('sitestaff', 'admin'),
        'ignore' => !$USER->get('admin'),
    ),
    'admin' => array(
        'type' => 'switchbox',
        'title' => get_string('siteadmin', 'admin'),
        'ignore' => !$USER->get('admin'),
    ),
    'quota' => array(
        'type'         => 'bytes',
        'title'        => get_string('filequota1','admin'),
        'rules'        => array('integer' => true, 'minvalue' => 0),
        'defaultvalue' => get_config_plugin('artefact', 'file', 'defaultquota'),
    ),
    'authinstance' => array(
        'type'         => 'select',
        'title'        => get_string('institution'),
        'options'      => $options,
        'defaultvalue' => 1,
        'rules'        => array('required' => true),
        'ignore'       => !$authinstancecount,
    ),
    'institutionadmin' => array(
        'type'         => 'switchbox',
        'class'        => 'last',
        'title'        => get_string('institutionadministrator','admin'),
        'ignore'       => !$authinstancecount,
    ),
    'submit' => array(
        'type' => 'submit',
        'value' => get_string('createuser', 'admin'),
        'class' => 'btn-primary btn-lg btn-block',
    ),
);

if (!$USER->get('admin')) {
    unset ($elements['authinstance']['defaultvalue']);
}

if (!($USER->get('admin') || get_config_plugin('artefact', 'file', 'institutionaloverride'))) {
    $elements['quota'] = array(
        'type'         => 'text',
        'disabled'     => true,
        'title'        => get_string('filequota1', 'admin'),
        'description'  => get_string('filequotadescription', 'admin'),
        'value'        => display_size(get_config_plugin('artefact', 'file', 'defaultquota')),
    );
}

// Add general account options
$prefs = (object) expected_account_preferences();
$elements = array_merge($elements, general_account_prefs_form_elements($prefs));
unset($prefs);


$form = pieform(array(
    'name'       => 'adduser',
    'class'      => 'card card-body',
    'autofocus'  => false,
    'template'   => 'adduser.php',
    'templatedir' => pieform_template_dir('adduser.php'),
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'class'      => 'form-condensed',
    'elements'   => $elements,
));


function adduser_validate(Pieform $form, $values) {
    global $USER, $TRANSPORTER;

    $authobj = AuthFactory::create($values['authinstance']);

    $institution = $authobj->institution;

    // Institutional admins can only set their own institutions' authinstances
    if (!$USER->get('admin') && !$USER->is_institutional_admin($authobj->institution)) {
        $form->set_error('authinstance', get_string('notadminforinstitution', 'admin'));
        return;
    }

    $institution = new Institution($authobj->institution);

    // Don't exceed max user accounts for the institution
    if ($institution->isFull()) {
        $institution->send_admin_institution_is_full_message();
        $form->set_error('authinstance', get_string('institutionmaxusersexceeded', 'admin'));
        return;
    }

    $username  = $values['username'];
    $firstname = sanitize_firstname($values['firstname']);
    $lastname  = sanitize_lastname($values['lastname']);
    $email     = sanitize_email($values['email']);
    $password  = $values['password'];

    if ($USER->get('admin') || get_config_plugin('artefact', 'file', 'institutionaloverride')) {
        $maxquotaenabled = get_config_plugin('artefact', 'file', 'maxquotaenabled');
        $maxquota = get_config_plugin('artefact', 'file', 'maxquota');
        if ($maxquotaenabled && $values['quota'] > $maxquota) {
            $form->set_error('quota', get_string('maxquotaexceededform', 'artefact.file', display_size($maxquota)));
        }
    }

    if (method_exists($authobj, 'is_username_valid_admin')) {
        if (!$authobj->is_username_valid_admin($username)) {
            $form->set_error('username', get_string('usernameinvalidadminform', 'auth.internal'));
        }
    }
    else if (method_exists($authobj, 'is_username_valid')) {
        if (!$authobj->is_username_valid($username)) {
            $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
        }
    }
    if (!$form->get_error('username') && record_exists_select('usr', 'LOWER(username) = ?', array(strtolower($username)))) {
        $form->set_error('username', get_string('usernamealreadytaken1', 'auth.internal'));
    }

    if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
        if ($authobj->type == 'internal') {
            $form->set_error('password', get_password_policy_description('error'));
        }
        else {
            // Allow auth type to return their own error message - Currently not used
            $form->set_error('password', get_string('passwordinvalidform' . $authobj->type, 'auth.' . $authobj->type));
        }
    }

    if (param_exists('createmethod') && param_variable('createmethod') == 'leap2a') {
        $form->set_error('firstname', null);
        $form->set_error('lastname', null);
        $form->set_error('email', null);
        if (!$values['leap2afile'] && ($_FILES['leap2afile']['error'] == UPLOAD_ERR_INI_SIZE || $_FILES['leap2afile']['error'] == UPLOAD_ERR_FORM_SIZE)) {
            $form->reply(PIEFORM_ERR, array(
                'message' => get_string('uploadedfiletoobig1', 'mahara', display_size(get_max_upload_size(false))),
                'goto'    => get_config('wwwroot') . 'admin/users/add.php'));
            $form->set_error('leap2afile', get_string('uploadedfiletoobig1', 'mahara', display_size(get_max_upload_size(false))));
            return;
        }
        else if (!$values['leap2afile']) {
            $form->set_error('leap2afile', $form->i18n('rule', 'required', 'required'));
            return;
        }

        if ($values['leap2afile']['type'] == 'application/octet-stream') {
            require_once('file.php');
            $mimetype = file_mime_type($values['leap2afile']['tmp_name']);
        }
        else {
            $mimetype = trim($values['leap2afile']['type'], '"');
        }
        $date = time();
        $niceuser = preg_replace('/[^a-zA-Z0-9_-]/', '-', $values['username']);
        safe_require('import', 'leap');
        $fakeimportrecord = (object)array(
            'data' => array(
                'importfile'     => $values['leap2afile']['tmp_name'],
                'importfilename' => $values['leap2afile']['name'],
                'importid'       => $niceuser . '-' . $date,
                'mimetype'       => $mimetype,
            )
        );

        $TRANSPORTER = new LocalImporterTransport($fakeimportrecord);
        try {
            $TRANSPORTER->extract_file();
            PluginImportLeap::validate_transported_data($TRANSPORTER);
        }
        catch (Exception $e) {
            $form->set_error('leap2afile', $e->getMessage());
        }
    }
    else {
        if (!$form->get_error('firstname') && empty($firstname)) {
            $form->set_error('firstname', $form->i18n('rule', 'required', 'required'));
        }
        if (!$form->get_error('lastname') && empty($lastname)) {
            $form->set_error('lastname', $form->i18n('rule', 'required', 'required'));
        }

        if (!$form->get_error('email')) {
            if (!$form->get_error('email') && empty($email)) {
                $form->set_error('email', get_string('invalidemailaddress', 'artefact.internal'));
            }

            if (record_exists('usr', 'email', $email)
                || record_exists('artefact_internal_profile_email', 'email', $email)) {
                $form->set_error('email', get_string('emailalreadytaken', 'auth.internal'));
            }
        }
    }
}

function adduser_submit(Pieform $form, $values) {
    global $USER, $SESSION, $TRANSPORTER;
    db_begin();

    raise_time_limit(180);

    // Create user
    $user = (object)array(
        'authinstance'   => $values['authinstance'],
        'username'       => $values['username'],
        'firstname'      => ($values['firstname']) ? $values['firstname'] : 'Imported',
        'lastname'       => ($values['lastname']) ? $values['lastname'] : 'User',
        'email'          => $values['email'],
        'password'       => $values['password'],
        'passwordchange' => 1,
    );
    if ($USER->get('admin')) {  // Not editable by institutional admins
        $user->staff = (int) ($values['staff'] == 'on');
        $user->admin = (int) ($values['admin'] == 'on');
    }
    if ($USER->get('admin') || get_config_plugin('artefact', 'file', 'institutionaloverride')) {
        $user->quota = $values['quota'];
    }

    $authinstance = get_record('auth_instance', 'id', $values['authinstance'], 'active', 1);
    if (!$authinstance) {
        throw new InvalidArgumentException("trying to add user to inactive auth instance " . $values['authinstance']);
    }
    $remoteauth = false;
    if ($authinstance->authname != 'internal') {
        $remoteauth = true;
    }
    if (!isset($values['remoteusername'])){
        $values['remoteusername'] = null;
    }

    $user->id = create_user($user, array(), $authinstance->institution, $remoteauth, $values['remoteusername'], $values);

    if (isset($user->admin) && $user->admin) {
        require_once('activity.php');
        activity_add_admin_defaults(array($user->id));
    }

    if ($values['institutionadmin']) {
        set_field('usr_institution', 'admin', 1, 'usr', $user->id, 'institution', $authinstance->institution);
    }

    if (isset($values['leap2afile'])) {
        // And we're good to go
        $importdata = (object)array(
            'token'      => '',
            'usr'        => $user->id,
            'queue'      => (int)!(PluginImport::import_immediately_allowed()), // import allowed straight away? Then don't queue
            'ready'      => 0, // maybe 1?
            'expirytime' => db_format_timestamp(time()+(60*60*24)),
            'format'     => 'leap',
            'loglevel'   => PluginImportLeap::LOG_LEVEL_VERBOSE,
            'logtargets' => LOG_TARGET_FILE,
            'profile'    => true,
        );
        $importer = PluginImport::create_importer(null, $TRANSPORTER, $importdata);

        try {
            $importer->process();
            log_info("Imported user account $user->id from Leap2A file, see " . $importer->get('logfile') . ' for a full log');
        }
        catch (ImportException $e) {
            log_info("Leap2A import failed: " . $e->getMessage());
            die_info(get_string('leap2aimportfailed', 'admin'));
        }

        // Reload the user details, as various fields are changed by the
        // importer when importing (e.g. firstname/lastname)
        $user = get_record('usr', 'id', $user->id);
    }

    db_commit();

    if (!empty($user->email)) {
        try {
            email_user($user, $USER, get_string('accountcreated', 'mahara', get_config('sitename')),
                get_string('accountcreatedchangepasswordtext', 'mahara', $user->firstname, get_config('sitename'), $user->username, $values['password'], get_config('wwwroot'), get_config('sitename')),
                get_string('accountcreatedchangepasswordhtml', 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $values['password'], get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
            );
        }
        catch (EmailException $e) {
            $SESSION->add_error_msg(get_string('newuseremailnotsent', 'admin'));
        }
    }

    $SESSION->add_ok_msg(get_string('newusercreated', 'admin'));
    redirect('/admin/users/edit.php?id=' . $user->id);
}

$smarty = smarty(array('adminadduser'));
setpageicon($smarty, 'icon-user-plus');
$smarty->assign('form', $form);
$smarty->assign('headingclass', 'page-header');
$smarty->display('admin/users/add.tpl');
