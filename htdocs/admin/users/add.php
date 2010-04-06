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
        'rules' => array('required' => true),
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
    global $USER, $LEAP2A_FILE;

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

    if (method_exists($authobj, 'is_username_valid') && !$authobj->is_username_valid($username)) {
        $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
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

        $date = time();
        $nicedate = date('Y/m/d h:i:s', $date);
        $niceuser = preg_replace('/[^a-zA-Z0-9_-]/', '-', $values['username']);

        $uploaddir = get_config('dataroot') . 'import/' . $niceuser . '-' . $date . '/';
        $filename = $uploaddir . $values['leap2afile']['name'];
        check_dir_exists($uploaddir);
        if (!move_uploaded_file($values['leap2afile']['tmp_name'], $filename)) {
            $form->set_error('leap2afile', get_string('failedtoobtainuploadedleapfile', 'admin'));
        }

        if ($values['leap2afile']['type'] == 'application/octet-stream') {
            // the browser wasn't sure, so use mime_content_type to guess
            $mimetype = mime_content_type($filename);
        }
        else {
            $mimetype = $values['leap2afile']['type'];
        }

        safe_require('artefact', 'file');
        $ziptypes = PluginArtefactFile::get_mimetypes_from_description('zip');

        if (in_array($mimetype, $ziptypes)) {
            // Unzip the file
            $command = sprintf('%s %s %s %s',
                escapeshellcmd(get_config('pathtounzip')),
                escapeshellarg($filename),
                get_config('unzipdirarg'),
                escapeshellarg($uploaddir)
            );
            $output = array();
            exec($command, $output, $returnvar);
            if ($returnvar != 0) {
                log_debug("unzip command failed with return value $returnvar");
                // Let's make it obvious if the cause is obvious :)
                if ($returnvar == 127) {
                    log_debug("This means that 'unzip' isn't installed, or the config var \$cfg->pathtounzip is not"
                        . " pointing at unzip (see Mahara's file lib/config-defaults.php)");
                }
                $form->set_error('leap2afile', get_string('failedtounzipleap2afile', 'admin'));
                return;
            }

            $filename = $uploaddir . 'leap2a.xml';
            if (!is_file($filename)) {
                $form->set_error('leap2afile', get_string('noleap2axmlfiledetected', 'admin'));
                return;
            }

        }
        else if ($mimetype != 'text/xml') {
            $form->set_error('leap2afile', get_string('fileisnotaziporxmlfile', 'admin'));
        }
        $LEAP2A_FILE = $filename;
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
    global $USER, $SESSION, $LEAP2A_FILE;
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
        $filename = substr($LEAP2A_FILE, strlen(get_config('dataroot')));
        $logfile  = dirname($LEAP2A_FILE) . '/import.log';
        require_once(get_config('docroot') . 'import/lib.php');
        safe_require('import', 'leap');
        $importer = PluginImport::create_importer(null, (object)array(
            'token'      => '',
            //'host'       => '',
            'usr'        => $user->id,
            'queue'      => (int)!(PluginImport::import_immediately_allowed()), // import allowed straight away? Then don't queue
            'ready'      => 0, // maybe 1?
            'expirytime' => db_format_timestamp(time()+(60*60*24)),
            'format'     => 'leap',
            'data'       => array('filename' => $filename),
            'loglevel'   => PluginImportLeap::LOG_LEVEL_VERBOSE,
            'logtargets' => LOG_TARGET_FILE,
            'logfile'    => $logfile,
            'profile'    => true,
        ));

        try {
            $importer->process();
            log_info("Imported user account $user->id from leap2a file, see $logfile for a full log");
        }
        catch (ImportException $e) {
            log_info("LEAP2A import failed: " . $e->getMessage());
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

    $SESSION->add_ok_msg(get_string('newusercreated', 'admin'));
    redirect('/admin/users/edit.php?id=' . $user->id);
}

$smarty = smarty(array('adminadduser'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('admin/users/add.tpl');

?>
