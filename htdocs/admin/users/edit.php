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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('accountsettings', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
require_once('pieforms/pieform.php');

$id = param_integer('id');
if (!$user = get_record('usr', 'id', $id)) {
    throw new UserNotFoundException("User not found");
}

// Deny access to institutional admins from different institutions to the displayed user
$userinstitution = get_record('usr_institution', 'usr', $id, null, null, null, null,
                              'usr,institution,studentid,staff,admin,'.db_format_tsfield('expiry'));
global $USER;
if (!$USER->get('admin')) {
    if (empty($userinstitution) || !$USER->is_institutional_admin($userinstitution->institution)) {
        redirect(get_config('wwwroot').'user/view.php?id='.$id);
    }
}

if (empty($user->suspendedcusr)) {
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
                'type'        => 'text',
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
    'type'         => 'text',
    'title'        => get_string('filequota','admin'),
    'defaultvalue' => $user->quota / 1048576,
    'rules'        => array('integer' => true),
);

$authinstances = auth_get_auth_instances();
if (count($authinstances) > 1) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        if ($USER->get('admin') || $USER->is_institutional_admin($authinstance->name)) {
            $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
        }
    }

    $elements['authinstance'] = array(
        'type' => 'select',
        'title' => get_string('authenticatedby', 'admin'),
        'options' => $options,
        'defaultvalue' => $user->authinstance
    );
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
    $user->quota = $values['quota'] * 1048576;

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


// Institution settings
$allinstitutions = get_records_array('institution');
$options = array();
foreach ($allinstitutions as $i) {
    if ($USER->get('admin') || $i->name == 'mahara' || $i->name == $userinstitution->institution) {
        $options[$i->name] = $i->displayname;
    }
}

$elements = array(
    'id' => array(
         'type'    => 'hidden',
         'value'   => $id,
     ),
    'institution' => array(
         'type'         => 'select',
         'title'        => get_string('institution'),
         'options'      => $options,
         'defaultvalue' => empty($userinstitution) ? 'mahara' : $userinstitution->institution
    ),
    'change' => array(
        'type'  => 'submit',
        'value' => get_string('changeinstitution','admin'),
    ),
);

if ($userinstitution) {
    $elements['subtitle'] = array(
        'type'         => 'html',
        'title'        => get_string('settingsfor', 'admin'),
        'value'        => $options[$userinstitution->institution],
    );
    $currentdate = getdate();
    $elements['expiry'] = array(
        'type'         => 'date',
        'title'        => get_string('membershipexpiry'),
        'minyear'      => $currentdate['year'],
        'maxyear'      => $currentdate['year'] + 20
    );
    if (!empty($userinstitution->expiry)) {
        $elements['expiry']['defaultvalue'] = $userinstitution->expiry;
    }
    $elements['studentid'] = array(
        'type'         => 'text',
        'title'        => get_string('studentid'),
        'defaultvalue' => $userinstitution->studentid,
    );
    $elements['staff'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('institutionstaff','admin'),
        'defaultvalue' => $userinstitution->staff,
    );
    $elements['admin'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('institutionadmin','admin'),
        'defaultvalue' => $userinstitution->admin,
    );
    $elements['submit'] = array(
        'type'  => 'submit',
        'value' => get_string('update'),
    );
}

$institutionform = pieform(array(
    'name'       => 'edituser_institution',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => $elements,
));

function edituser_institution_submit(Pieform $form, $values) {
    if (!$user = get_record('usr', 'id', $values['id'])) {
        return false;
    }
    $userinstitution = get_record('usr_institution', 'usr', $user->id);

    // Make sure institutional admins are from the same institution as
    // the user being edited
    global $USER;
    if (!$USER->get('admin')
        && (!$userinstitution || !$USER->is_institutional_admin($userinstitution->institution))) {
        redirect('/admin/users/edit.php?id='.$user->id);
    }

    if (isset($values['change'])) {
        // Do nothing if there's no change to the institution
        if (!$userinstitution && $values['institution'] == 'mahara'
            || $userinstitution && $values['institution'] == $userinstitution->institution) {
            redirect('/admin/users/edit.php?id='.$user->id);
        }
        // Don't let institutional admins change the institution, but let them unset it
        if (!$USER->get('admin') && $values['institution'] != 'mahara') {
            redirect('/admin/users/edit.php?id='.$user->id);
        }

        delete_records('usr_institution', 'usr', $user->id);
        if ($values['institution'] != 'mahara') {
            insert_record('usr_institution', (object) array(
                'usr' => $user->id,
                'institution' => $values['institution']  // ctime, expiry, etc
            ));
        }
    } else { // Changing settings for an existing institution
        $newuser = (object) array(
            'usr'         => $userinstitution->usr,
            'institution' => $userinstitution->institution,
            'ctime'       => $userinstitution->ctime,
            'studentid'   => $values['studentid'],
            'staff'       => (int) ($values['staff'] == 'on'),
            'admin'       => (int) ($values['admin'] == 'on'),
        );
        if ($values['expiry']) {
            $newuser->expiry = db_format_timestamp($values['expiry']);
        }
        delete_records('usr_institution', 'usr', $user->id);
        insert_record('usr_institution', $newuser);
    }

    redirect('/admin/users/edit.php?id='.$user->id);
}

$smarty = smarty();
$smarty->assign('user', $user);
$smarty->assign('suspendform', $suspendform);
$smarty->assign('siteform', $siteform);
$smarty->assign('institutionform', $institutionform);
$smarty->display('admin/users/edit.tpl');

?>
