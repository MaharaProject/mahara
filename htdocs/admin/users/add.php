<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/adduser');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('adduser', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
require_once('pieforms/pieform.php');
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
    ),
    'lastname' => array(
        'type'    => 'text',
        'title'   => get_string('lastname'),
        'rules'   => array('required' => true),
    ),
    'email' => array(
        'type'    => 'text',
        'title'   => get_string('email'),
        'rules'   => array('required' => true),
    ),
    'leap2afile' => array(
        'type' => 'file',
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
        'type' => 'text',
        'title' => get_string('password'),
        'rules' => array('required' => true),
    ),
    'staff' => array(
        'type' => 'checkbox',
        'title' => get_string('sitestaff', 'admin'),
        'ignore' => !$USER->get('admin'),
    ),
    'admin' => array(
        'type' => 'checkbox',
        'title' => get_string('siteadmin', 'admin'),
        'ignore' => !$USER->get('admin'),
    ),
    'quota' => array(
        'type'         => 'bytes',
        'title'        => get_string('filequota','admin'),
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
        'type'         => 'checkbox',
        'title'        => get_string('institutionadministrator','admin'),
        'ignore'       => !$authinstancecount,
    ),
    'submit' => array(
        'type' => 'submit',
        'value' => get_string('createuser', 'admin'),
    ),
);

if (!$USER->get('admin')) {
    unset ($elements['authinstance']['defaultvalue']);
}

$form = pieform(array(
    'name'       => 'adduser',
    'autofocus'  => false,
    'template'   => 'adduser.php',
    'templatedir' => pieform_template_dir('adduser.php'),
    'plugintype' => 'core',
    'pluginname' => 'admin',
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
        $form->set_error('authinstance', get_string('institutionmaxusersexceeded', 'admin'));
        return;
    }

    $username  = $values['username'];
    $firstname = $values['firstname'];
    $lastname  = $values['lastname'];
    $email     = $values['email'];
    $password  = $values['password'];

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
    if (!$form->get_error('username') && record_exists_select('usr', 'LOWER(username) = ?', strtolower($username))) {
        $form->set_error('username', get_string('usernamealreadytaken', 'auth.internal'));
    }

    if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
        $form->set_error('password', get_string('passwordinvalidform', 'auth.' . $authobj->type));
    }

    if (isset($_POST['createmethod']) && $_POST['createmethod'] == 'leap2a') {
        $form->set_error('firstname', null);
        $form->set_error('lastname', null);
        $form->set_error('email', null);

        if (!$values['leap2afile']) {
            $form->set_error('leap2afile', $form->i18n('rule', 'required', 'required'));
            return;
        }

        if ($values['leap2afile']['type'] == 'application/octet-stream') {
            require_once('file.php');
            $mimetype = file_mime_type($values['leap2afile']['tmp_name']);
        }
        else {
            $mimetype = $values['leap2afile']['type'];
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
        if (!$form->get_error('firstname') && !preg_match('/\S/', $firstname)) {
            $form->set_error('firstname', $form->i18n('rule', 'required', 'required'));
        }
        if (!$form->get_error('lastname') && !preg_match('/\S/', $lastname)) {
            $form->set_error('lastname', $form->i18n('rule', 'required', 'required'));
        }

        if (!$form->get_error('email')) {
            require_once('phpmailer/class.phpmailer.php');
            if (!$form->get_error('email') && !PHPMailer::ValidateAddress($email)) {
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

    ini_set('max_execution_time', 180);

    // Create user
    $user = (object)array(
        'authinstance'   => $values['authinstance'],
        'username'       => $values['username'],
        'firstname'      => ($values['firstname']) ? $values['firstname'] : 'Imported',
        'lastname'       => ($values['lastname']) ? $values['lastname'] : 'User',
        'email'          => $values['email'],
        'password'       => $values['password'],
        'quota'          => $values['quota'],
        'passwordchange' => 1,
    );
    if ($USER->get('admin')) {  // Not editable by institutional admins
        $user->staff = (int) ($values['staff'] == 'on');
        $user->admin = (int) ($values['admin'] == 'on');
    }

    $authinstance = get_record('auth_instance', 'id', $values['authinstance']);
    if (!isset($values['remoteusername'])){
        $values['remoteusername'] = null;
    }

    $user->id = create_user($user, array(), $authinstance->institution, $authinstance, $values['remoteusername']);

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
                get_string('accountcreatedchangepasswordtext', 'mahara', $user->firstname, get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('sitename')),
                get_string('accountcreatedchangepasswordhtml', 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
            );
        }
        catch (EmailException $e) {
            $SESSION->add_error_msg(get_string('newuseremailnotsent', 'admin'));
        }
    }

    // Add salt and encrypt the pw, if the auth instance allows for it
    $userobj = new User();
    $userobj = $userobj->find_by_id($user->id);
    $authobj = AuthFactory::create($user->authinstance);
    if (method_exists($authobj, 'change_password')) {
        $authobj->change_password($userobj, $user->password);
    } else {
        $userobj->password = '';
        $userobj->salt = auth_get_random_salt();
        $userobj->commit();
    }
    unset($userobj, $authobj);

    $SESSION->add_ok_msg(get_string('newusercreated', 'admin'));
    redirect('/admin/users/edit.php?id=' . $user->id);
}

$smarty = smarty(array('adminadduser'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/add.tpl');
