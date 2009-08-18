<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
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

// Site-wide account settings
$elements = array();
$elements['username'] = array(
    'type'    => 'text',
    'title'   => get_string('username'),
    'rules'   => array('required' => true),
);
$elements['firstname'] = array(
    'type'    => 'text',
    'title'   => get_string('firstname'),
    'rules'   => array('required' => true),
);
$elements['lastname'] = array(
    'type'    => 'text',
    'title'   => get_string('lastname'),
    'rules'   => array('required' => true),
);
$elements['email'] = array(
    'type'    => 'text',
    'title'   => get_string('email'),
    'rules'   => array('required' => true),
);
$elements['password'] = array(
    'type'    => 'text',
    'title'   => get_string('password'),
    'rules'   => array('required' => true),
);
if ($USER->get('admin')) {
    $elements['staff'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('sitestaff','admin'),
    );
    $elements['admin'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('siteadmin','admin'),
    );
}
$elements['quota'] = array(
    'type'         => 'bytes',
    'title'        => get_string('filequota','admin'),
    'rules'        => array('integer' => true),
    'defaultvalue' => get_config_plugin('artefact', 'file', 'defaultquota'),
);

if ($USER->get('admin')) {
    $authinstances = auth_get_auth_instances();
} else {
    $admininstitutions = $USER->get('admininstitutions');
    $authinstances = auth_get_auth_instances_for_institutions($admininstitutions);
    if (empty($authinstances)) {
        $SESSION->add_info_msg(get_string('configureauthplugin', 'admin'));
        redirect(get_config('wwwroot').'admin/users/institutions.php?i='.key($admininstitutions).'&amp;edit=1');
    }
}

if (count($authinstances) > 0) {
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

    $elements['authinstance'] = array(
        'type'         => 'select',
        'title'        => get_string('institution'),
        'options'      => $options,
        'defaultvalue' => 1,
        'rules'        => array('required' => true),
    );
    $elements['institutionadmin'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('institutionadministrator','admin'),
    );
    if ($external) {
        $elements['remoteusername'] = array(
            'type'         => 'text',
            'title'        => get_string('remoteusername', 'admin'),
            'description'  => get_string('remoteusernamedescription', 'admin', get_config('sitename')),
        );
    }
}

$elements['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('createuser','admin'),
);

$form = pieform(array(
    'name'       => 'adduser',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => $elements,
));


function adduser_validate(Pieform $form, $values) {
    global $USER;

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
        $SESSION->add_error_msg(get_string('institutionmaxusersexceeded', 'admin'));
        redirect('/admin/users/add.php');
    }

    $username  = $values['username'];
    $firstname = $values['firstname'];
    $lastname  = $values['lastname'];
    $email     = $values['email'];
    $password  = $values['password'];

    if (method_exists($authobj, 'is_username_valid') && !$authobj->is_username_valid($username)) {
        $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
        return;
    }
    if (!$form->get_error('username') && record_exists_select('usr', 'LOWER(username) = ?', strtolower($username))) {
        $form->set_error('username', get_string('usernamealreadytaken', 'auth.internal'));
        return;
    }

    if (!$form->get_error('firstname') && !preg_match('/\S/', $firstname)) {
        $form->set_error('firstname', $form->i18n('required'));
    }
    if (!$form->get_error('lastname') && !preg_match('/\S/', $lastname)) {
        $form->set_error('lastname', $form->i18n('required'));
    }

    if (record_exists('usr', 'email', $email)
        || record_exists('artefact_internal_profile_email', 'email', $email)) {
        $form->set_error('email', get_string('emailalreadytaken', 'auth.internal'));
    }

    if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
        $form->set_error('password', get_string('passwordinvalidform', 'auth.' . $authobj->type));
        return;
    }

}

function adduser_submit(Pieform $form, $values) {

    $user = new StdClass;
    $user->authinstance   = $values['authinstance'];
    $user->username       = $values['username'];
    $user->firstname      = $values['firstname'];
    $user->lastname       = $values['lastname'];
    $user->email          = $values['email'];
    $user->password       = $values['password'];
    $user->quota          = $values['quota'];
    $user->passwordchange = 1;

    global $USER, $SESSION;
    if ($USER->get('admin')) {  // Not editable by institutional admins
        $user->staff = (int) ($values['staff'] == 'on');
        $user->admin = (int) ($values['admin'] == 'on');
    }

    $authinstance = get_record('auth_instance', 'id', $values['authinstance']);

    if(!isset($values['remoteusername'])){
        $values['remoteusername'] = null;
    }

    db_begin();

    $user->id = create_user($user, array(), $authinstance->institution, $authinstance, $values['remoteusername']);

    if (isset($user->admin) && $user->admin) {
        require_once('activity.php');
        activity_add_admin_defaults(array($user->id));
    }

    if ($values['institutionadmin']) {
        set_field('usr_institution', 'admin', 1, 'usr', $user->id, 'institution', $authinstance->institution);
    }

    db_commit();

    try {
        email_user($user, $USER, get_string('accountcreated', 'mahara', get_config('sitename')),
            get_string('accountcreatedchangepasswordtext', 'mahara', $user->firstname, get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('sitename')),
            get_string('accountcreatedchangepasswordhtml', 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
        );
    }
    catch (EmailException $e) {
        $SESSION->add_error_msg(get_string('newuseremailnotsent', 'admin'));
    }

    redirect('/admin/users/edit.php?id='.$user->id);
}

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('admin/users/add.tpl');

?>
