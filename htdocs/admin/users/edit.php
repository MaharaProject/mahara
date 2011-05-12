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
define('MENUITEM', 'configusers/usersearch');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('accountsettings', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
require_once('pieforms/pieform.php');
require_once('activity.php');

$id = param_integer('id');
$user = new User;
$user->find_by_id($id);
$authobj = AuthFactory::create($user->authinstance);

if (!$USER->is_admin_for_user($user)) {
    $SESSION->add_error_msg(get_string('youcannotadministerthisuser', 'admin'));
    redirect('/user/view.php?id=' . $id);
}


// Site-wide account settings
$currentdate = getdate();
$elements = array();
$elements['id'] = array(
    'type'    => 'hidden',
    'rules'   => array('integer' => true),
    'value'   => $id,
);

if (method_exists($authobj, 'change_username')) {
    $elements['username'] = array(
        'type'         => 'text',
        'title'        => get_string('changeusername', 'admin'),
        'description'  => get_string('changeusernamedescription', 'admin'),
        'defaultvalue' => $user->username,
        'rules' => array(
            'maxlength' => 236,
         ),
    );
}

if (method_exists($authobj, 'change_password')) {
    // Only show the password options if the plugin allows for the functionality
    $elements['password'] = array(
        'type'         => 'text',
        'title'        => get_string('resetpassword','admin'),
        'description'  => get_string('resetpassworddescription','admin'),
    );

    $elements['passwordchange'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('forcepasswordchange','admin'),
        'description'  => get_string('forcepasswordchangedescription','admin'),
        'defaultvalue' => $user->passwordchange,
    );
}
if ($USER->get('admin')) {
    $elements['staff'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('sitestaff','admin'),
        'defaultvalue' => $user->staff,
        'help'         => true,
    );
    $elements['admin'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('siteadmin','admin'),
        'defaultvalue' => $user->admin,
        'help'         => true,
    );
}
$elements['maildisabled'] = array(
    'type' => 'checkbox',
    'defaultvalue' => get_account_preference($user->id, 'maildisabled'),
    'title' => get_string('email'),
    'help' => true,
);
$elements['expiry'] = array(
    'type'         => 'date',
    'title'        => get_string('accountexpiry', 'admin'),
    'description'  => get_string('accountexpirydescription', 'admin'),
    'minyear'      => $currentdate['year'] - 2,
    'maxyear'      => $currentdate['year'] + 20,
    'defaultvalue' => $user->expiry
);
$elements['quota'] = array(
    'type'         => 'bytes',
    'title'        => get_string('filequota','admin'),
    'description'  => get_string('filequotadescription','admin'),
    'rules'        => array('integer' => true),
    'defaultvalue' => $user->quota,
);

$authinstances = auth_get_auth_instances();
if (count($authinstances) > 1) {
    $options = array();

    // NOTE: This is a little broken at the moment. The "username in the remote 
    // system" setting is only actively used by the XMLRPC authentication 
    // plugin, and thus only makes sense when the user is authenticating in 
    // this manner.
    //
    // We hope to one day make it possible for users to get into accounts via 
    // multiple methods, at which time we can tie the username-in-remote-system 
    // setting to the XMLRPC plugin only, making the UI a bit more consistent
    $external = false;
    foreach ($authinstances as $authinstance) {
        if ($USER->can_edit_institution($authinstance->name)) {
            $options[$authinstance->id] = $authinstance->instancename . ' (' . $authinstance->displayname . ')';
            if ($authinstance->authname != 'internal') {
                $external = true;
            }
        }
    }

    if (isset($options[$user->authinstance])) {
        $elements['authinstance'] = array(
            'type'         => 'select',
            'title'        => get_string('authenticatedby', 'admin'),
            'description'  => get_string('authenticatedbydescription', 'admin'),
            'options'      => $options,
            'defaultvalue' => $user->authinstance,
            'help'         => true,
        );
        if ($external) {
            $un = get_field('auth_remote_user', 'remoteusername', 'authinstance', $user->authinstance, 'localusr', $user->id);
            $elements['remoteusername'] = array(
                'type'         => 'text',
                'title'        => get_string('remoteusername', 'admin'),
                'description'  => get_string('remoteusernamedescription', 'admin', hsc(get_config('sitename'))),
                'defaultvalue' => $un ? $un : $user->username,
            );
        }
    }

}

$elements['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('savechanges','admin'),
);

$siteform = pieform(array(
    'name'       => 'edituser_site',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => $elements,
));

function edituser_site_validate(Pieform $form, $values) {
    global $USER, $SESSION;
    if (!$user = get_record('usr', 'id', $values['id'])) {
        return false;
    }
    $maxquotaenabled = get_config_plugin('artefact', 'file', 'maxquotaenabled');
    $maxquota = get_config_plugin('artefact', 'file', 'maxquota');
    if ($maxquotaenabled && $values['quota'] > $maxquota) {
        $form->set_error('quota', get_string('maxquotaexceededform', 'artefact.file', display_size($maxquota)));
        $SESSION->add_error_msg(get_string('maxquotaexceeded', 'artefact.file', display_size($maxquota)));
    }

    $userobj = new User();
    $userobj = $userobj->find_by_id($user->id);

    if (isset($values['username']) && !empty($values['username']) && $values['username'] != $userobj->username) {

        if (!isset($values['authinstance'])) {
            $authobj = AuthFactory::create($userobj->authinstance);
        }
        else {
            $authobj = AuthFactory::create($values['authinstance']);
        }

        if (method_exists($authobj, 'change_username')) {

            if (method_exists($authobj, 'is_username_valid_admin')) {
                if (!$authobj->is_username_valid_admin($values['username'])) {
                    $form->set_error('username', get_string('usernameinvalidadminform', 'auth.internal'));
                }
            }
            else if (method_exists($authobj, 'is_username_valid')) {
                if (!$authobj->is_username_valid($values['username'])) {
                    $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
                }
            }

            if (!$form->get_error('username') && record_exists_select('usr', 'LOWER(username) = ?', strtolower($values['username']))) {
                $form->set_error('username', get_string('usernamealreadytaken', 'auth.internal'));
            }
        }
        else {
            $form->set_error('username', get_string('usernamechangenotallowed', 'admin'));
        }
    }

    // Check that the external username isn't already in use
    if (isset($values['remoteusername']) &&
        $usedby = get_record_select('auth_remote_user',
        'authinstance = ? AND remoteusername = ? AND localusr != ?',
        array($values['authinstance'], $values['remoteusername'], $values['id']))
    ) {
        $usedbyuser = get_field('usr', 'username', 'id', $usedby->localusr);
        $SESSION->add_error_msg(get_string('duplicateremoteusername', 'auth', $usedbyuser));
        $form->set_error('remoteusername', get_string('duplicateremoteusernameformerror', 'auth'));
    }
}

function edituser_site_submit(Pieform $form, $values) {
    global $USER, $authobj, $SESSION;

    if (!$user = get_record('usr', 'id', $values['id'])) {
        return false;
    }

    $user->quota = $values['quota'];
    $user->expiry = db_format_timestamp($values['expiry']);


    // Try to kick the user from any active login sessions, before saving data.
    require_once(get_config('docroot') . 'auth/session.php');
    remove_user_sessions($user->id);

    if ($USER->get('admin')) {  // Not editable by institutional admins
        $user->staff = (int) ($values['staff'] == 'on');
        $user->admin = (int) ($values['admin'] == 'on');
        if ($user->admin) {
            activity_add_admin_defaults(array($user->id));
        }
    }

    if ($values['maildisabled'] == 0 && get_account_preference($user->id, 'maildisabled') == 1) {
        // Reset the sent and bounce counts otherwise mail will be disabled
        // on the next send attempt
        $u = new StdClass;
        $u->email = $user->email;
        $u->id = $user->id;
        update_bounce_count($u,true);
        update_send_count($u,true);
    }
    set_account_preference($user->id, 'maildisabled', $values['maildisabled']);

    // Authinstance can be changed by institutional admins if both the
    // old and new authinstances belong to the admin's institutions
    $remotename = get_field('auth_remote_user', 'remoteusername', 'authinstance', $user->authinstance, 'localusr', $user->id);
    if (!$remotename) {
        $remotename = $user->username;
    }
    if (isset($values['authinstance'])
        && ($values['authinstance'] != $user->authinstance
            || (isset($values['remoteusername']) && $values['remoteusername'] != $remotename))) {
        $authinst = get_records_select_assoc('auth_instance', 'id = ? OR id = ?', 
                                             array($values['authinstance'], $user->authinstance));
        if ($USER->get('admin') || 
            ($USER->is_institutional_admin($authinst[$values['authinstance']]->institution) &&
             $USER->is_institutional_admin($authinst[$user->authinstance]->institution))) {
            delete_records('auth_remote_user', 'localusr', $user->id);
            if ($authinst[$values['authinstance']]->authname != 'internal') {
                if (isset($values['remoteusername']) && strlen($values['remoteusername']) > 0) {
                    $un = $values['remoteusername'];
                }
                else {
                    $un = $remotename;
                }
                insert_record('auth_remote_user', (object) array(
                    'authinstance'   => $values['authinstance'],
                    'remoteusername' => $un,
                    'localusr'       => $user->id,
                ));
            }
            $user->authinstance = $values['authinstance'];

            // update the global $authobj to match the new authinstance
            // this is used by the password/username change methods
            // if either/both has been requested at the same time
            $authobj = AuthFactory::create($user->authinstance);
        }
    }

    // Only change the pw if the new auth instance allows for it
    if (method_exists($authobj, 'change_password')) {
        $user->passwordchange = (int) ($values['passwordchange'] == 'on');

        if (isset($values['password']) && $values['password'] !== '') {
            $userobj = new User();
            $userobj = $userobj->find_by_id($user->id);

            $user->password = $authobj->change_password($userobj, $values['password']);
            $user->salt = $userobj->salt;

            unset($userobj);
        }
    } else {
        // inform the user that the chosen auth instance doesn't allow password changes
        // but only if they tried changing it
        if (isset($values['password']) && $values['password'] !== '') {
            $SESSION->add_error_msg(get_string('passwordchangenotallowed', 'admin'));

            // Set empty pw with salt
            $user->password = '';
            $user->salt = auth_get_random_salt();
        }
    }

    if (isset($values['username']) && $values['username'] !== '') {
        $userobj = new User();
        $userobj = $userobj->find_by_id($user->id);

        if ($userobj->username != $values['username']) {
            // Only change the username if the auth instance allows for it
            if (method_exists($authobj, 'change_username')) {
                // check the existence of the chosen username
                try {
                    if ($authobj->user_exists($values['username'])) {
                        // set an error message if it is already in use
                        $SESSION->add_error_msg(get_string('usernameexists', 'account'));
                    }
                } catch (AuthUnknownUserException $e) {
                    // update the username otherwise
                    $user->username = $authobj->change_username($userobj, $values['username']);
                }
            } else {
                // inform the user that the chosen auth instance doesn't allow username changes
                $SESSION->add_error_msg(get_string('usernamechangenotallowed', 'admin'));
            }
        }

        unset($userobj);
    }

    update_record('usr', $user);

    redirect('/admin/users/edit.php?id='.$user->id);
}


// Suspension/deletion controls
$suspended = $user->get('suspendedcusr');
if (empty($suspended)) {
    $suspendform = pieform(array(
        'name'       => 'edituser_suspend',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'elements'   => array(
            'id' => array(
                 'type'    => 'hidden',
                 'value'   => $id,
            ),
            'reason' => array(
                'type'        => 'textarea',
                'rows'        => 5,
                'cols'        => 28,
                'title'       => get_string('reason'),
                'description' => get_string('suspendedreasondescription', 'admin'),
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('suspenduser','admin'),
            ),
        )
    ));
}
else {
    $suspendformdef = array(
        'name'       => 'edituser_unsuspend',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'renderer'   => 'oneline',
        'elements'   => array(
            'id' => array(
                 'type'    => 'hidden',
                 'value'   => $id,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('unsuspenduser','admin'),
            ),
        )
    );

    // Create two forms for unsuspension - one in the suspend message and the 
    // other where the 'suspend' button normally goes. This keeps the HTML IDs 
    // unique
    $suspendform  = pieform($suspendformdef);
    $suspendformdef['name'] = 'edituser_suspend2';
    $suspendformdef['successcallback'] = 'edituser_unsuspend_submit';
    $suspendform2 = pieform($suspendformdef);

    $suspender = display_name(get_record('usr', 'id', $suspended));
}

function edituser_suspend_submit(Pieform $form, $values) {
    global $SESSION, $USER, $user;
    if (!$USER->get('admin') && ($user->get('admin') || $user->get('staff'))) {
        $SESSION->add_error_msg(get_string('errorwhilesuspending', 'admin'));
    }
    else {
        suspend_user($user->get('id'), $values['reason']);
        $SESSION->add_ok_msg(get_string('usersuspended', 'admin'));
    }
    redirect('/admin/users/edit.php?id=' . $user->get('id'));
}

function edituser_unsuspend_submit(Pieform $form, $values) {
    global $SESSION;
    unsuspend_user($values['id']);
    $SESSION->add_ok_msg(get_string('userunsuspended', 'admin'));
    redirect('/admin/users/edit.php?id=' . $values['id']);
}

$deleteform = pieform(array(
    'name' => 'edituser_delete',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'renderer' => 'oneline',
    'elements'   => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $id,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('deleteuser', 'admin'),
            'confirm' => get_string('confirmdeleteuser', 'admin'),
        ),
    ),
));

function edituser_delete_validate(Pieform $form, $values) {
    global $USER, $SESSION;
    if (!$USER->get('admin')) {
        $form->set_error('submit', get_string('deletefailed', 'admin'));
        $SESSION->add_error_msg(get_string('deletefailed', 'admin'));
    }
}

function edituser_delete_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    if ($USER->get('admin')) {
        delete_user($values['id']);
        $SESSION->add_ok_msg(get_string('userdeletedsuccessfully', 'admin'));
    }
    redirect('/admin/users/search.php');
}


// Institution settings form
$elements = array(
    'id' => array(
         'type'    => 'hidden',
         'value'   => $id,
     ),
);

$allinstitutions = get_records_assoc('institution', '', '', 'displayname');
foreach ($user->get('institutions') as $i) {
    $elements[$i->institution.'_settings'] = array(
        'type' => 'fieldset',
        'legend' => $allinstitutions[$i->institution]->displayname,
        'elements' => array(
            $i->institution.'_expiry' => array(
                'type'         => 'date',
                'title'        => get_string('membershipexpiry', 'admin'),
                'description'  => get_string('membershipexpirydescription', 'admin'),
                'minyear'      => $currentdate['year'],
                'maxyear'      => $currentdate['year'] + 20,
                'defaultvalue' => $i->membership_expiry
            ),
            $i->institution.'_studentid' => array(
                'type'         => 'text',
                'title'        => get_string('studentid', 'admin'),
                'description'  => get_string('institutionstudentiddescription', 'admin'),
                'defaultvalue' => $i->studentid,
            ),
            $i->institution.'_staff' => array(
                'type'         => 'checkbox',
                'title'        => get_string('institutionstaff','admin'),
                'defaultvalue' => $i->staff,
            ),
            $i->institution.'_admin' => array(
                'type'         => 'checkbox',
                'title'        => get_string('institutionadmin','admin'),
                'description'  => get_string('institutionadmindescription','admin'),
                'defaultvalue' => $i->admin,
            ),
            $i->institution.'_submit' => array(
                'type'  => 'submit',
                'value' => get_string('update'),
            ),
            $i->institution.'_remove' => array(
                'type'  => 'submit',
                'value' => get_string('removeuserfrominstitution', 'admin'),
                'confirm' => get_string('confirmremoveuserfrominstitution', 'admin'),
            ),
        ),
    );
}

// Only site admins can add institutions; institutional admins must invite
if ($USER->get('admin') 
    && (get_config('usersallowedmultipleinstitutions') || count($user->institutions) == 0)) {
    $options = array();
    foreach ($allinstitutions as $i) {
        if (!$user->in_institution($i->name) && $i->name != 'mahara') {
            $options[$i->name] = $i->displayname;
        }
    }
    if (!empty($options)) {
        $elements['addinstitutionheader'] = array(
            'type'  => 'markup',
            'value' => '<tr><td colspan="2"><h4>' . get_string('addusertoinstitution', 'admin') . '</h4></td></tr>',
        );
        $elements['addinstitution'] = array(
            'type'         => 'select',
            'title'        => get_string('institution'),
            'options'      => $options,
        );
        $elements['add'] = array(
            'type'  => 'submit',
            'value' => get_string('addusertoinstitution', 'admin'),
        );
    }
}

$institutionform = pieform(array(
    'name'       => 'edituser_institution',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => $elements,
));

function edituser_institution_submit(Pieform $form, $values) {
    $user = new User;
    if (!$user->find_by_id($values['id'])) {
        return false;
    }
    $userinstitutions = $user->get('institutions');

    global $USER;
    foreach ($userinstitutions as $i) {
        if ($USER->can_edit_institution($i->institution)) {
            if (isset($values[$i->institution.'_submit'])) {
                $newuser = (object) array(
                    'usr'         => $user->id,
                    'institution' => $i->institution,
                    'ctime'       => db_format_timestamp($i->ctime),
                    'studentid'   => $values[$i->institution . '_studentid'],
                    'staff'       => (int) ($values[$i->institution . '_staff'] == 'on'),
                    'admin'       => (int) ($values[$i->institution . '_admin'] == 'on'),
                );
                if ($values[$i->institution . '_expiry']) {
                    $newuser->expiry = db_format_timestamp($values[$i->institution . '_expiry']);
                }
                db_begin();
                delete_records('usr_institution', 'usr', $user->id, 'institution', $i->institution);
                insert_record('usr_institution', $newuser);
                if ($newuser->admin) {
                    activity_add_admin_defaults(array($user->id));
                }
                handle_event('updateuser', $user->id);
                db_commit();
                break;
            } else if (isset($values[$i->institution.'_remove'])) {
                if ($user->id == $USER->id) {
                    $USER->leave_institution($i->institution);
                } else {
                    $user->leave_institution($i->institution);
                }
                // Institutional admins can no longer access this page
                // if they remove the user from the institution, so
                // send them back to user search.
                if (!$USER->get('admin')) {
                    if (!$USER->is_institutional_admin()) {
                        redirect(get_config('wwwroot'));
                    }
                    redirect('/admin/users/search.php');
                }
                break;
            }
        }
    }

    if (isset($values['add']) && $USER->get('admin')
        && (empty($userinstitutions) || get_config('usersallowedmultipleinstitutions'))) {
        if ($user->id == $USER->id) {
            $USER->join_institution($values['addinstitution']);
            $USER->commit();
        }
        else {
            $user->join_institution($values['addinstitution']);
        }
    }

    redirect('/admin/users/edit.php?id='.$user->id);
}

$smarty = smarty();
$smarty->assign('user', $user);
$smarty->assign('suspended', $suspended);
if ($suspended) {
    $smarty->assign('suspendedby', get_string('suspendedby', 'admin', $suspender));
}
$smarty->assign('suspendform', $suspendform);
if (isset($suspendform2)) {
    $smarty->assign('suspendform2', $suspendform2);
}
$smarty->assign('deleteform', $deleteform);
$smarty->assign('siteform', $siteform);
$smarty->assign('institutions', count($allinstitutions) > 1);
$smarty->assign('institutionform', $institutionform);

$smarty->assign('loginas', $id != $USER->get('id') && is_null($USER->get('parentuser')));
$smarty->assign('PAGEHEADING', TITLE . ': ' . display_name($user));

# Only allow deletion and suspension of a user if the viewed user is not
# the current user; or if they are the current user, they're not the only
# admin
if ($id != $USER->get('id') || count_records('usr', 'admin', 1, 'deleted', 0) > 1) {
    $smarty->assign('suspendable', ($USER->get('admin') || !$user->get('admin') && !$user->get('staff')));
    $smarty->assign('deletable', $USER->get('admin'));
}

$smarty->display('admin/users/edit.tpl');
