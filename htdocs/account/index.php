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
define('MENUITEM', 'settings/account');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'preferences');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('account'));
require_once('pieforms/pieform.php');

// load up user preferences
$prefs = (object) load_account_preferences($USER->id);

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
    'type' => 'checkbox',
    'defaultvalue' => (get_config('wysiwyg')) ? get_config('wysiwyg') == 'enable' : $prefs->wysiwyg,
    'title' => get_string('wysiwygdescr', 'account'),
    'help' => true,
    'disabled' => get_config('wysiwyg'),
);
$elements['maildisabled'] = array(
    'type' => 'checkbox',
    'defaultvalue' => get_account_preference($USER->get('id'), 'maildisabled'),
    'title' => get_string('email'),
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
    'type' => 'checkbox',
    'defaultvalue' => $prefs->addremovecolumns,
    'title' => get_string('showviewcolumns', 'account'),
    'help' => 'true'
);
// TODO: add a way for plugins (like blog!) to have account preferences
$elements['multipleblogs'] = array(
    'type' => 'checkbox',
    'title'=> get_string('enablemultipleblogs' ,'account'),
    'description' => get_string('enablemultipleblogsdescription', 'account'),
    'defaultvalue' => $prefs->multipleblogs,
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
if (get_config('userscanhiderealnames')) {
    $elements['hiderealname'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('hiderealname', 'account'),
        'description'  => get_string('hiderealnamedescription', 'account'),
        'defaultvalue' => $prefs->hiderealname,
    );
}
if (get_config('homepageinfo')) {
    $elements['showhomeinfo'] = array(
        'type' => 'checkbox',
        'defaultvalue' => $prefs->showhomeinfo,
        'title' => get_string('showhomeinfo', 'account'),
        'help' => 'true'
    );
}
if (get_config('allowmobileuploads')) {
    $elements['mobileuploadtoken'] = array(
        'type'         => 'text',
        'title'        => get_string('mobileuploadtoken', 'account'),
        'description'  => get_string('mobileuploadtokendescription', 'account'),
        'defaultvalue' => isset($prefs->mobileuploadtoken) ? $prefs->mobileuploadtoken : get_config('mobileuploadtoken')
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
            try {
                if (!$authobj->authenticate_user_account($USER, $values['oldpassword'])) {
                    $form->set_error('oldpassword', get_string('oldpasswordincorrect', 'account'));
                    return;
                }
            }
            // propagate error correctly for User validation issues - this should
            // be catching AuthUnknownUserException and AuthInstanceException
             catch  (UserException $e) {
                 $form->set_error('oldpassword', $e->getMessage());
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

    // Don't let users turn multiple blogs off unless they only have 1 blog
    if ($USER->get_account_preference('multipleblogs')
        && empty($values['multipleblogs'])
        && count_records('artefact', 'artefacttype', 'blog', 'owner', $USER->get('id')) != 1) {
        $form->set_error('multipleblogs', get_string('disablemultipleblogserror', 'account'));
    }
}

function accountprefs_submit(Pieform $form, $values) {
    global $USER, $SESSION;

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

    // Remember the user's language pref, so we can reload the page if they change it
    $oldlang = $USER->get_account_preference('lang');

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

    if (isset($values['lang']) && $values['lang'] != $oldlang) {
        // Use PIEFORM_CANCEL here to force a page reload and show the new language.
        $returndata['location'] = get_config('wwwroot') . 'account/index.php';
        $SESSION->add_ok_msg(get_string_from_language($values['lang'], 'prefssaved', 'account'));
        $form->json_reply(PIEFORM_CANCEL, $returndata);
    }

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
        if (username) {
            replaceChildNodes(username, data.username);
        }
    }
}
");
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('account/index.tpl');
