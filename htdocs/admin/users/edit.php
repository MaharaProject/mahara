<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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

$id = param_integer('id');
$user = new User;
$user->find_by_id($id);

global $USER;
if (!$USER->get('admin')) {
    // Institutional admins must share an institution with the displayed user
    $shared = false;
    foreach ($user->get('institutions') as $i) {
        $shared = $shared || $USER->is_institutional_admin($i->institution);
    }
    if (!$shared) {
        redirect(get_config('wwwroot').'user/view.php?id='.$id);
    }
}

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
                'cols'        => 60,
                'title'       => get_string('reason'),
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('suspenduser','admin'),
            ),
        )
    ));
} else {
    $suspendform = pieform(array(
        'name'       => 'edituser_unsuspend',
        'plugintype' => 'core',
        'pluginname' => 'admin',
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
    ));
    $suspender = display_name(get_record('usr', 'id', $suspended));
}

function edituser_suspend_submit(Pieform $form, $values) {
    global $SESSION;
    suspend_user($values['id'], $values['reason']);
    $SESSION->add_ok_msg(get_string('usersuspended', 'admin'));
    redirect('/admin/users/edit.php?id=' . $values['id']);
}

function edituser_unsuspend_submit(Pieform $form, $values) {
    global $SESSION;
    unsuspend_user($values['id']);
    $SESSION->add_ok_msg(get_string('userunsuspended', 'admin'));
    redirect('/admin/users/edit.php?id=' . $values['id']);
}


// Site-wide account settings
$elements = array();
$elements['id'] = array(
    'type'    => 'hidden',
    'rules'   => array('integer' => true),
    'value'   => $id,
);
$elements['password'] = array(
    'type'         => 'text',
    'title'        => get_string('resetpassword','admin'),
);
$elements['passwordchange'] = array(
    'type'         => 'checkbox',
    'title'        => get_string('forcepasswordchange','admin'),
    'defaultvalue' => $user->passwordchange,
);
if ($USER->get('admin')) {
    $elements['staff'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('sitestaff','admin'),
        'defaultvalue' => $user->staff,
    );
    $elements['admin'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('siteadmin','admin'),
        'defaultvalue' => $user->admin,
    );
}
$elements['quota'] = array(
    'type'         => 'bytes',
    'title'        => get_string('filequota','admin'),
    'defaultvalue' => $user->quota,
);

$authinstances = auth_get_auth_instances();
if (count($authinstances) > 1) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        if ($USER->can_edit_institution($authinstance->name)) {
            $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
        }
    }

    if (isset($options[$user->authinstance])) {
        $elements['authinstance'] = array(
            'type' => 'select',
            'title' => get_string('authenticatedby', 'admin'),
            'options' => $options,
            'defaultvalue' => $user->authinstance,
        );
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


function edituser_site_submit(Pieform $form, $values) {
    if (!$user = get_record('usr', 'id', $values['id'])) {
        return false;
    }

    if (isset($values['password']) && $values['password'] !== '') {
        $user->password = $values['password'];
    }
    $user->passwordchange = (int) ($values['passwordchange'] == 'on');
    $user->quota = $values['quota'];

    global $USER;
    if ($USER->get('admin')) {  // Not editable by institutional admins
        $user->staff = (int) ($values['staff'] == 'on');
        $user->admin = (int) ($values['admin'] == 'on');
    }

    // Authinstance can be changed by institutional admins if both the
    // old and new authinstances belong to the admin's institutions
    if (isset($values['authinstance']) &&
        ($USER->get('admin') || 
         ($USER->is_institutional_admin(get_field('auth_instance', 'institution', 'id', $values['authinstance'])) &&
          $USER->is_institutional_admin(get_field('auth_instance', 'institution', 'id', $user->authinstance))))) {
        $user->authinstance = $values['authinstance'];
    }

    update_record('usr', $user);

    redirect('/admin/users/edit.php?id='.$user->id);
}


// Institution settings form

$elements = array(
    'id' => array(
         'type'    => 'hidden',
         'value'   => $id,
     ),
);

$allinstitutions = get_records_assoc('institution');
$currentdate = getdate();
foreach ($user->get('institutions') as $i) {
    $elements[$i->institution.'_settings'] = array(
        'type' => 'fieldset',
        'legend' => $allinstitutions[$i->institution]->displayname,
        'elements' => array(
            $i->institution.'_expiry' => array(
                'type'         => 'date',
                'title'        => get_string('membershipexpiry'),
                'minyear'      => $currentdate['year'],
                'maxyear'      => $currentdate['year'] + 20,
                'defaultvalue' => $i->expiry
            ),
            $i->institution.'_studentid' => array(
                'type'         => 'text',
                'title'        => get_string('studentid'),
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
                'defaultvalue' => $i->admin,
            ),
            $i->institution.'_submit' => array(
                'type'  => 'submit',
                'value' => get_string('update'),
            ),
        ),
    );
    $elements[$i->institution.'_remove'] = array(
        'type'  => 'submit',
        'value' => get_string('remove'),
        'confirm' => get_string('confirmremoveuserfrominstitution', 'admin'),
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
        $elements['addinstitution'] = array(
            'type'         => 'select',
            'title'        => get_string('addinstitution', 'admin'),
            'options'      => $options,
        );
        $elements['add'] = array(
            'type'  => 'submit',
            'value' => get_string('addinstitution','admin'),
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
                    'ctime'       => $i->ctime,
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
                handle_event('updateuser', $user->id);
                db_commit();
                break;
            } else if (isset($values[$i->institution.'_remove'])) {
                db_begin();
                delete_records('usr_institution', 'usr', $user->id, 'institution', $i->institution);
                handle_event('updateuser', $user->id);
                db_commit();
                // Institutional admins can no longer access this page
                // if they remove the user from the institution, so
                // send them back to user search.
                if (!$USER->get('admin')) {
                    redirect('/admin/users/search.php');
                }
                break;
            }
        }
    }

    if (isset($values['add']) && $USER->get('admin')
        && (empty($userinstitutions) || get_config('usersallowedmultipleinstitutions'))) {
        // Do nothing if the user is already in the institution
        $addinstitution = get_record('institution', 'name', $values['addinstitution']);
        if (!$addinstitution || $addinstitution->name == 'mahara'
            || $user->in_institution($addinstitution->name)) {
            redirect('/admin/users/edit.php?id='.$user->id);
        }
        $now = time();
        if (!empty($addinstitution->defaultmembershipperiod)) {
            $expiry = db_format_timestamp($now + $addinstitution->defaultmembershipperiod);
        } else {
            $expiry = null;
        }
        db_begin();
        insert_record('usr_institution', (object) array(
            'usr' => $user->id,
            'institution' => $addinstitution->name,
            'ctime' => db_format_timestamp($now),
            'expiry' => $expiry,
        ));
        handle_event('updateuser', $user->id);
        db_commit();
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
$smarty->assign('siteform', $siteform);
$smarty->assign('institutionform', $institutionform);
$smarty->display('admin/users/edit.tpl');

?>
