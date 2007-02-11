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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'account');
define('SUBMENUITEM', 'accountprefs');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');

// load up user preferences
$prefs = (object)($USER->get('accountprefs'));

$authtype  = auth_get_authtype_for_institution($USER->get('institution'));
$authclass = 'Auth' . ucfirst($authtype);
safe_require('auth', $authtype);

// @todo auth preference for a password change screen for all auth methods other than internal
if (method_exists($authclass, 'change_password')) {
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
else if ($url = get_config_plugin('auth', $authtype, 'changepasswordurl')) {
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
    'value' => '<tr><td colspan="2"><p>You can set general account options here</p></td></tr>'
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
    'options' => get_languages(),
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


$smarty = smarty();
$smarty->assign('form', pieform($prefsform));
$smarty->assign('INLINEJAVASCRIPT', "
function clearPasswords(form, data) {
    formSuccess(form, data);
    $('accountprefs_oldpassword').value = '';
    $('accountprefs_password1').value = '';
    $('accountprefs_password2').value = '';
}");
$smarty->display('account/index.tpl');

function accountprefs_validate(Pieform $form, $values) {
    if ($values['oldpassword'] !== '') {
        global $USER, $authtype, $authclass;
        if (!call_static_method($authclass, 'authenticate_user_account', $USER->get('username'), $values['oldpassword'], $USER->get('institution'))) {
            $form->set_error('oldpassword', get_string('oldpasswordincorrect', 'account'));
            return;
        }
        password_validate($form, $values, $USER->get('username'), $USER->get('institution'));
    }
    else if ($values['password1'] !== '' || $values['password2'] !== '') {
        $form->set_error('oldpassword', get_string('mustspecifyoldpassword'));
    }
}

function accountprefs_submit(Pieform $form, $values) {
    global $USER;

    db_begin();
    if ($values['password1'] !== '') {
        global $authclass;
        $password = call_static_method($authclass, 'change_password', $USER->get('username'), $values['password1']);
        $user = new StdClass;
        $user->password = $password;
        $user->passwordchange = 0;
        $where = new StdClass;
        $where->username = $USER->get('username');
        update_record('usr', $user, $where);
        $USER->set('password', $password);
        $USER->set('passwordchange', 0);
    }

    // use this as looping through values is not safe.
    $expectedprefs = expected_account_preferences(); 
    foreach (array_keys($expectedprefs) as $pref) {
        $USER->set_account_preference($pref, $values[$pref]);
    }

    db_commit();
    $form->json_reply(PIEFORM_OK, get_string('prefssaved', 'account'));
}


?>
