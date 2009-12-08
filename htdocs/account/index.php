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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/preferences');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'preferences');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('preferences'));
require_once('pieforms/pieform.php');

// load up user preferences
$prefs = (object)($USER->accountprefs);

$authobj = AuthFactory::create($USER->authinstance);

// @todo auth preference for a password change screen for all auth methods other than internal
if (method_exists($authobj, 'change_password')) {
    $elements = array(
        'changepassworddesc' => array(
            'value' => '<tr><td colspan="2"><h3>' . get_string('changepassworddesc', 'account') . '</h3></td></tr>'
        ),
        'oldpassword' => array( 'type' => 'password',
            'title' => get_string('oldpassword'),
            'help'  => true,
            'autocomplete' => 'off',
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
            'value' => '<tr><td colspan="2"><h3>' . get_string('changepasswordotherinterface', 'account', $url) . '</h3></td></tr>'
        )
    );
}
else {
    $elements = array();
}

if ($authobj->authname == 'internal') {
    $elements['changeusernameheading'] = array(
        'value' => '<tr><td colspan="2"><h3>' . get_string('changeusernameheading', 'account') . '</h3></td></tr>'
    );
    $elements['username'] = array(
        'type' => 'text',
        'defaultvalue' => $USER->get('username'),
        'title' => get_string('changeusername', 'account'),
        'description' => get_string('changeusernamedesc', 'account', hsc(get_config('sitename'))),
    );
}

$elements['accountoptionsdesc'] = array(
    'value' => '<tr><td colspan="2"><h3>' . get_string('accountoptionsdesc', 'account') . '</h3></td></tr>'
);
$elements['friendscontrol'] = array(
    'type' => 'radio',
    'defaultvalue' => $prefs->friendscontrol, 
    'title'  => get_string('friendsdescr', 'account'),
    'separator' => '<br>',
    'options' => array(
        'nobody' => get_string('friendsnobody', 'account'),
        'auth'   => get_string('friendsauth', 'account'),
        'auto'   => get_string('friendsauto', 'account')
    ),
    'help' => true
);
$elements['wysiwyg'] = array(
    'type' => 'radio',
    'defaultvalue' => $prefs->wysiwyg,
    'title' => get_string('wysiwygdescr', 'account'),
    'separator' => '<br>',
    'options' => array(
        1 => get_string('on', 'account'),
        0 => get_string('off', 'account'),
    ),
    'help' => true,
);
$elements['maildisabled'] = array(
    'type' => 'radio',
    'defaultvalue' => get_account_preference($USER->get('id'), 'maildisabled'),
    'title' => get_string('email'),
    'separator' => '<br>',
    'options' => array(
        0 => get_string('enabled', 'account'),
        1 => get_string('disabled', 'account'),
    ),
    'help' => true,
);
$elements['messages'] = array(
    'type' => 'radio',
    'defaultvalue' => $prefs->messages,
    'title' => get_string('messagesdescr', 'account'),
    'separator' => '<br>',
    'options' => array(
        'nobody' => get_string('messagesnobody', 'account'),
        'friends' => get_string('messagesfriends', 'account'),
        'allow' => get_string('messagesallow', 'account'),
    ),
    'help' => true,
);
$languages = get_languages();
$elements['lang'] = array(
    'type' => 'select',
    'defaultvalue' => $prefs->lang,
    'title' => get_string('language', 'account'),
    'options' => array_merge(array('default' => get_string('sitedefault', 'admin') . ' (' . $languages[get_config('lang')] . ')'), $languages),
    'help' => true,
    'ignore' => count($languages) < 2,
);
$elements['addremovecolumns'] = array(
    'type' => 'radio',
    'options' => array(
        1 => get_string('on', 'account'),
        0 => get_string('off', 'account'),
    ),
    'defaultvalue' => $prefs->addremovecolumns,
    'title' => get_string('showviewcolumns', 'account'),
    'separator' => '<br>',
    'help' => 'true'
);
if (get_config('showtagssideblock')) {
    $elements['tagssideblockmaxtags'] = array(
        'type'         => 'text',
        'size'         => 4,
        'title'        => get_string('tagssideblockmaxtags', 'account'),
        'description'  => get_string('tagssideblockmaxtagsdescription', 'account'),
        'defaultvalue' => isset($prefs->tagssideblockmaxtags) ? $prefs->tagssideblockmaxtags : get_config('tagssideblockmaxtags'),
        'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 1000),
    );
}
$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('save')
);

$prefsform = array(
    'name'        => 'accountprefs',
    'renderer'    => 'table',
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

    if (isset($values['oldpassword'])) {
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

    if ($authobj->authname == 'internal' && $values['username'] != $USER->get('username')) {
        if (!AuthInternal::is_username_valid($values['username'])) {
            $form->set_error('username', get_string('usernameinvalidform', 'auth.internal'));
        }
        if (!$form->get_error('username') && record_exists_select('usr', 'LOWER(username) = ?', strtolower($values['username']))) {
            $form->set_error('username', get_string('usernamealreadytaken', 'auth.internal'));
        }
    }
}

function accountprefs_submit(Pieform $form, $values) {
    global $USER;

    $authobj = AuthFactory::create($USER->authinstance);

    db_begin();
    if (isset($values['password1']) && $values['password1'] !== '') {
        global $authclass;
        $password = $authobj->change_password($USER, $values['password1']);
        $USER->password = $password;
        $USER->passwordchange = 0;
        $USER->commit();
    }

    // use this as looping through values is not safe.
    $expectedprefs = expected_account_preferences(); 
    if ($values['maildisabled'] == 0 && get_account_preference($USER->get('id'), 'maildisabled') == 1) {
        // Reset the sent and bounce counts otherwise mail will be disabled
        // on the next send attempt
        $u = new StdClass;
        $u->email = $USER->get('email');
        $u->id = $USER->get('id');
        update_bounce_count($u,true);
        update_send_count($u,true);
    }

    foreach (array_keys($expectedprefs) as $pref) {
        if (isset($values[$pref])) {
            $USER->set_account_preference($pref, $values[$pref]);
        }
    }

    $returndata = array();

    if (isset($values['username']) && $values['username'] != $USER->get('username')) {
        $USER->username = $values['username'];
        $USER->commit();
        $returndata['username'] = $values['username'];
    }

    db_commit();
    $returndata['message'] = get_string('prefssaved', 'account');
    $form->json_reply(PIEFORM_OK, $returndata);
}



$prefsform = pieform($prefsform);

$smarty = smarty();
$smarty->assign('form', $prefsform);
$smarty->assign('candeleteself', $USER->can_delete_self());
$smarty->assign('INLINEJAVASCRIPT', "
function clearPasswords(form, data) {
    formSuccess(form, data);
    if ($('accountprefs_oldpassword')) {
        $('accountprefs_oldpassword').value = '';
        $('accountprefs_password1').value = '';
        $('accountprefs_password2').value = '';
    }
    if (data.username) {
        var username = getFirstElementByTagAndClassName('a', null, 'profile-sideblock-username');
        replaceChildNodes(username, data.username);
    }
}
");
$smarty->assign('PAGEHEADING', hsc(get_string('preferences')));
$smarty->display('account/index.tpl');


?>
