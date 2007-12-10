<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/preferences');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'preferences');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');

// load up user preferences
$prefs = (object)($USER->accountprefs);

$authobj = AuthFactory::create($USER->authinstance);

// @todo auth preference for a password change screen for all auth methods other than internal
if (method_exists($authobj, 'change_password')) {
    $elements = array(
        'changepassworddesc' => array(
            'value' => '<tr><td colspan="2"><p>' . get_string('changepassworddesc', 'account') . '</p></td></tr>'
        ),
        'oldpassword' => array( 'type' => 'password',
            'title' => get_string('oldpassword'),
            'help'  => true,
        ),
        'password1' => array(
            'type' => 'password',
            'title' => get_string('newpassword'),
        ),
        'password2' => array(
            'type' => 'password',
            'title' => get_string('confirmpassword')
        ),
    );
}
else if ($url = get_config_plugin_instance('auth', $USER->authinstance, 'changepasswordurl')) {
    // @todo contextual help
    $elements = array(
        'changepasswordotherinterface' => array(
            'value' => '<tr><td colspan="2"><p>' . get_string('changepasswordotherinterface', 'account', $url) . '</p></td></tr>'
        )
    );
}
else {
    $elements = array();
}

$elements['accountoptionsdesc'] = array(
    'value' => '<tr><td colspan="2"><p>' . get_string('accountoptionsdesc', 'account') . '</p></td></tr>'
);
$elements['friendscontrol'] = array(
    'type' => 'radio',
    'defaultvalue' => $prefs->friendscontrol, 
    'title'  => get_string('friendsdescr', 'account'),
    'separator' => HTML_BR,
    'options' => array(
        'nobody' => get_string('friendsnobody', 'account'),
        'auth'   => get_string('friendsauth', 'account'),
        'auto'   => get_string('friendsauto', 'account')
    ),
   'rules' => array(
        'required' => true
    ),
    'help' => true
);
$elements['wysiwyg'] = array(
    'type' => 'radio',
    'defaultvalue' => $prefs->wysiwyg,
    'title' => get_string('wysiwygdescr', 'account'),
    'options' => array(
        1 => get_string('on', 'account'),
        0 => get_string('off', 'account'),
    ),
   'rules' => array(
        'required' => true
    ),
    'help' => true,
);
$elements['messages'] = array(
    'type' => 'radio',
    'defaultvalue' => $prefs->messages,
    'title' => get_string('messagesdescr', 'account'),
    'separator' => HTML_BR,
    'options' => array(
        'nobody' => get_string('messagesnobody', 'account'),
        'friends' => get_string('messagesfriends', 'account'),
        'allow' => get_string('messagesallow', 'account'),
    ),
   'rules' => array(
       'required' => true
    ),
    'help' => true,
);
$elements['lang'] = array(
    'type' => 'select',
    'defaultvalue' => $prefs->lang,
    'title' => get_string('language', 'account'),
    'options' => array_merge(array('default' => get_string('sitedefault', 'admin')), get_languages()),
    'rules' => array(
        'required' => true
    ),
    'help' => true,
);                        
$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('save')
);

$prefsform = array(
    'name'        => 'accountprefs',
    'method'      => 'post',
    'jsform'      => true,
    'plugintype'  => 'core',
    'pluginname'  => 'account',
    'jssuccesscallback' => 'clearPasswords',
    'elements'    => $elements
);

function accountprefs_validate(Pieform $form, $values) {
    global $USER;

    $authobj = AuthFactory::create($USER->authinstance);

    if ($values['oldpassword'] !== '') {
        global $USER, $authtype, $authclass;
        if (!$authobj->authenticate_user_account($USER, $values['oldpassword'])) {
            $form->set_error('oldpassword', get_string('oldpasswordincorrect', 'account'));
            return;
        }
        password_validate($form, $values, $USER);
    }
    else if ($values['password1'] !== '' || $values['password2'] !== '') {
        $form->set_error('oldpassword', get_string('mustspecifyoldpassword'));
    }
}

function accountprefs_submit(Pieform $form, $values) {
    global $USER;

    $authobj = AuthFactory::create($USER->authinstance);

    db_begin();
    if ($values['password1'] !== '') {
        global $authclass;
        $password = $authobj->change_password($USER, $values['password1']);
        $USER->password = $password;
        $USER->passwordchange = 0;
        $USER->commit();
    }

    // use this as looping through values is not safe.
    $expectedprefs = expected_account_preferences(); 
    foreach (array_keys($expectedprefs) as $pref) {
        $USER->set_account_preference($pref, $values[$pref]);
    }

    db_commit();
    $form->json_reply(PIEFORM_OK, get_string('prefssaved', 'account'));
}


// Institution forms

$institutions = get_records_assoc('institution', '', '', '', 'name,displayname');

// For all institutions the user is already a member of, create a
// button to leave the institution
$member = $USER->get('institutions');
if (!empty($member)) {
    $elements = array();
    foreach ($member as $i) {
        $elements[] = array(
            'type' => 'submit',
            'name' => '_leave_' . $i->institution,
            'confirm' => get_string('reallyleaveinstitution'),
            'title' => get_string('youareamemberof', 'mahara', 
                                  $institutions[$i->institution]->displayname),
            'value' => get_string('leaveinstitution')
        );
        unset($institutions[$i->institution]);
    }
    $memberform = pieform(array(
        'name'        => 'leaveinstitution',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => $elements
    ));
} else {
    $memberform = null;
}

function leaveinstitution_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^\_leave\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            break;
        }
    }
    if (!empty($institution)) {
        $USER->leave_institution($institution);
    }
    redirect(get_config('wwwroot') . 'account/index.php');
}



// List all institutions the user has requested membership, with a
// cancel request button
$requested = get_column('usr_institution_request', 'institution', 
                        'usr', $USER->id, 'confirmedusr', 1);
if (!empty($requested)) {
    $elements = array();
    foreach ($requested as $i) {
        $elements[] = array(
            'type' => 'submit',
            'name' => '_cancelrequest_' . $i,
            'title' => get_string('youhaverequestedmembershipof', 'mahara', 
                                  $institutions[$i]->displayname),
            'value' => get_string('cancelrequest')
        );
        unset($institutions[$i]);
    }
    $requestedform = pieform(array(
        'name'        => 'cancelrequest',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => $elements
    ));
} else {
    $requestedform = null;
}

function cancelrequest_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^\_cancelrequest\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            break;
        }
    }
    if (!empty($institution)) {
        delete_records('usr_institution_request', 'usr', $USER->id, 'institution', $institution);
        handle_event('updateuser', $USER->id);
    }
    redirect(get_config('wwwroot') . 'account/index.php');
}



// List all institutions the user has been invited to join, with a
// confirm membership button
$invited = get_column('usr_institution_request', 'institution', 'usr', $USER->id, 'confirmedinstitution', 1);
if (!empty($invited)) {
    $elements = array();
    foreach ($invited as $i) {
        $elements[] = array(
            'type' => 'submit',
            'name' => '_confirminvite_' . $i,
            'title' => get_string('youhavebeeninvitedtojoin', 'mahara', 
                                  $institutions[$i]->displayname),
            'value' => get_string('joininstitution')
        );
        unset($institutions[$i]);
    }
    $invitedform = pieform(array(
        'name'        => 'confirminvite',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => $elements
    ));
} else {
    $invitedform = null;
}

function confirminvite_submit(Pieform $form, $values) {
    global $USER;
    foreach ($values as $k => $v) {
        if (preg_match('/^\_confirminvite\_([a-z0-9]+)$/', $k, $m)) {
            $institution = $m[1];
            break;
        }
    }
    if (!empty($institution) && count_records('usr_institution_request', 'usr', $USER->id,
                                              'institution', $institution, 'confirmedinstitution', 1)) {
        $USER->join_institution($institution);
    }
    redirect(get_config('wwwroot') . 'account/index.php');
}




unset($institutions['mahara']);
// Request institution membership button for the remaining insitutions
if (!empty($institutions) &&
    (get_config('usersallowedmultipleinstitutions') || empty($member))) {
    $options = array();
    foreach ($institutions as $i) {
        $options[$i->name] = $i->displayname;
    }
    $joinform = pieform(array(
        'name'        => 'requestmembership',
        'method'      => 'post',
        'renderer'    => 'oneline',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => array(
            'institution' => array(
                'type' => 'select',
                'title' => get_string('requestmembershipofaninstitution'),
                'collapseifoneoption' => false,
                'options' => $options,
                'defaultvalue' => key($options),
             ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('sendrequest'),
             ),
        )
    ));
} else {
    $joinform = null;
}

function requestmembership_submit(Pieform $form, $values) {
    global $USER;
    if (!empty($values['institution'])) {
        $USER->add_institution_request($values['institution']);
    }
    redirect(get_config('wwwroot') . 'account/index.php');
}



$smarty = smarty();
$smarty->assign('form', pieform($prefsform));
$smarty->assign('memberform', $memberform);
$smarty->assign('requestedform', $requestedform);
$smarty->assign('invitedform', $invitedform);
$smarty->assign('joinform', $joinform);
$smarty->assign('INLINEJAVASCRIPT', "
function clearPasswords(form, data) {
    formSuccess(form, data);
    $('accountprefs_oldpassword').value = '';
    $('accountprefs_password1').value = '';
    $('accountprefs_password2').value = '';
}");
$smarty->display('account/index.tpl');


?>
