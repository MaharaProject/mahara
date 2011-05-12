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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/profile');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profile','artefact.internal'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'internal');

$fieldset = param_alpha('fs', 'aboutme');

$element_list = call_static_method('ArtefactTypeProfile', 'get_all_fields');
$element_data = ArtefactTypeProfile::get_field_element_data();
$element_required = call_static_method('ArtefactTypeProfile', 'get_mandatory_fields');

// load existing profile information
$profilefields = array();
$profile_data = get_records_select_array('artefact', "owner=? AND artefacttype IN (" . join(",",array_map(create_function('$a','return db_quote($a);'),array_keys($element_list))) . ")", array($USER->get('id')));

if ($profile_data) {
    foreach ($profile_data as $field) {
        $profilefields[$field->artefacttype] = $field->title;
    }
}

$lockedfields = locked_profile_fields();

$profilefields['email'] = array();
$profilefields['email']['all'] = get_records_array('artefact_internal_profile_email', 'owner', $USER->get('id'));
$profilefields['email']['validated'] = array();
$profilefields['email']['unvalidated'] = array();
$profilefields['email']['unsent'] = array();
if ($profilefields['email']['all']) {
    foreach ($profilefields['email']['all'] as $email) {
        if ($email->verified) {
            $profilefields['email']['validated'][] = $email->email;
        }
        else if (!empty($email->key)) {
            $profilefields['email']['unvalidated'][] = $email->email;
        }
        else {
            $profilefields['email']['unsent'][] = $email->email;
        }

        if ($email->principal) {
            $profilefields['email']['default'] = $email->email;
        }
    }
}

$items = array();
foreach ( $element_list as $element => $type ) {
    if ($type == 'wysiwyg' && isset($lockedfields[$element]) && !$USER->get('admin')) {
        // TinyMCE ignores the disabled attribute on the textarea, so just turn it
        // into an html element instead.
        $items[$element] = array(
            'type'  => 'html',
            'title' => get_string($element, 'artefact.internal'),
            'value' => '--',
        );
        if (isset($profilefields[$element])) {
            $items[$element]['value'] = clean_html($profilefields[$element]);
        }
        continue;
    }

    $items[$element] = array(
        'type'  => $type,
        'title' => get_string($element, 'artefact.internal'),
    );

    if (isset($element_data[$element]['rules'])) {
        $items[$element]['rules'] = $element_data[$element]['rules'];
    }

    if ($type == 'wysiwyg') {
        $items[$element]['rows'] = 10;
        $items[$element]['cols'] = 50;
        $items[$element]['rules'] = array('maxlength' => 65536);
    }
    if ($type == 'textarea') {
        $items[$element]['rows'] = 4;
        $items[$element]['cols'] = 50;
    }
    if ($type == 'text') {
        $items[$element]['size'] = 30;
    }
    if ($element == 'country') {
        $countries = getoptions_country();
        $items[$element]['options'] = array('' => get_string('nocountryselected')) + $countries;
        $items[$element]['defaultvalue'] = get_config('country');
    }

    if (get_helpfile_location('artefact', 'internal', 'profileform', $element)) {
        $items[$element]['help'] = true;
    }

    if (isset($profilefields[$element])) {
        $items[$element]['defaultvalue'] = $profilefields[$element];
    }

    if (isset($element_required[$element])) {
        $items[$element]['rules']['required'] = true;
    }

    if (isset($lockedfields[$element]) && !$USER->get('admin')) {
        $items[$element]['disabled'] = true;
    }

}
if ($items['firstname']) {
    $items['firstname']['autofocus'] = true;
}


$items['maildisabled']['ignore'] = !get_account_preference($USER->get('id'),'maildisabled');
$items['maildisabled']['value'] = get_string('maildisableddescription', 'account', get_config('wwwroot') . 'account/');

// build form elements
$elements = array(
    'topsubmit' => array(
        'type'  => 'submit',
        'value' => get_string('saveprofile','artefact.internal'),
    ),
    'profile' => array(
        'type' => 'fieldset',
        'legend' => get_string('aboutme', 'artefact.internal'),
        'class' => $fieldset != 'aboutme' ? 'collapsed' : '',
        'elements' => get_desired_fields($items, array('firstname', 'lastname', 'studentid', 'preferredname', 'introduction'), 'about'),
    ),
    'contact' => array(
        'type' => 'fieldset',
        'legend' => get_string('contact', 'artefact.internal'),
        'class' => $fieldset != 'contact' ? 'collapsed' : '',
        'elements' => get_desired_fields($items, array('email', 'maildisabled', 'officialwebsite', 'personalwebsite', 'blogaddress', 'address', 'town', 'city', 'country', 'homenumber', 'businessnumber', 'mobilenumber', 'faxnumber'), 'contact'),
    ),
    'messaging' => array(
        'type' => 'fieldset',
        'legend' => get_string('messaging', 'artefact.internal'),
        'class' => $fieldset != 'messaging' ? 'collapsed' : '',
        'elements' => get_desired_fields($items, array('icqnumber', 'msnnumber', 'aimscreenname', 'yahoochat', 'skypeusername', 'jabberusername'), 'messaging'),
    ),
    'general' => array(
        'type' => 'fieldset',
        'legend' => get_string('general', 'artefact.internal'),
        'class' => $fieldset != 'general' ? 'collapsed' : '',
        'elements' => $items
    ),
    'fs' => array(
        'type' => 'hidden',
        'value' => $fieldset,
    ),
    'submit' => array(
        'type'  => 'submit',
        'value' => get_string('saveprofile','artefact.internal'),
    )
);

$profileform = pieform(array(
    'name'       => 'profileform',
    'plugintype' => 'artefact',
    'pluginname' => 'internal',
    // will be uncommented when js for tabbed interface is called again after form submit
    //'jsform'     => true,
    'method'     => 'post',
    'renderer'   => 'table',  // don't change unless you also modify profile.js to not require tables.
    'elements'   => $elements,
    'autofocus'  => false,
));

function get_desired_fields(&$allfields, $desiredfields, $section) {
    global $USER;
    if ($section == 'about') {
        $label = '<div id="profileicon"><a href="' . get_config('wwwroot') . 'artefact/file/profileicons.php"><img src="' . get_config('wwwroot') . 'thumb.php?type=profileicon&maxsize=100&id=' . intval($USER->get('id')) . '" alt=""></a></div>';
        $descr = get_string('aboutdescription', 'artefact.internal');
    }
    else {
        $label = '';
        $descr = get_string('infoisprivate', 'artefact.internal');
    }
    $return = array(
        "{$section}description" => array(
            'type'      => 'html',
            'labelhtml' => $label,
            'value'     => $descr,
        )
    );
    foreach ($desiredfields as $field) {
        if (isset($allfields[$field])) {
            $return[$field] = $allfields[$field];
            unset($allfields[$field]);
        }
    }
    return $return;
}

function profileform_validate(Pieform $form, $values) {
    global $profilefields;

    if (
        !isset($values['email']['default'])
        || !in_array($values['email']['default'], $profilefields['email']['validated'])
        || !in_array($values['email']['default'], $values['email']['validated'])
    ) {
        $form->set_error('email', get_string('primaryemailinvalid'));
    }

    if (isset($values['email']['unsent']) && is_array($values['email']['validated'])) {
        require_once('phpmailer/class.phpmailer.php');
        foreach ($values['email']['unsent'] as $email) {
            if (!PHPMailer::ValidateAddress($email)) {
                $form->set_error('email', get_string('invalidemailaddress', 'artefact.internal') . ': ' . hsc($email));
                break;
            }
            else if (record_exists('artefact_internal_profile_email', 'email', $email)) {
                $form->set_error('email', get_string('unvalidatedemailalreadytaken', 'artefact.internal'));
                break;
            }
        }
    }

}

function profileform_submit(Pieform $form, $values) {
    global $SESSION;
    global $USER;
    global $element_list;
    global $profilefields;

    db_begin();

    $now = db_format_timestamp(time());
    $email_errors = array();

    $lockedfields = locked_profile_fields();

    foreach ($element_list as $element => $type) {

        if (isset($lockedfields[$element]) && !$USER->get('admin')) {
            continue;
        }

        if ($element == 'email') {
            if (!isset($values['email']['unsent'])) {
                $values['email']['unsent'] = array();
            }
            // find new addresses
            foreach ($values['email']['unsent'] as $email) {
                if (
                    in_array($email, $profilefields['email']['validated'])
                    || in_array($email, $profilefields['email']['unvalidated'])
                ) {
                    continue;
                }

                $key = get_random_key();
                $key_url = get_config('wwwroot') . 'artefact/internal/validate.php?email=' . rawurlencode($email) . '&key=' . $key;
                $key_url_decline = $key_url . '&decline=1';

                try {
                    email_user(
                        (object)array(
                            'id'            => $USER->get('id'),
                            'username'      => $USER->get('username'),
                            'firstname'     => $USER->get('firstname'),
                            'lastname'      => $USER->get('lastname'),
                            'preferredname' => $USER->get('preferredname'),
                            'admin'         => $USER->get('admin'),
                            'staff'         => $USER->get('staff'),
                            'email'         => $email,
                        ),
                        null,
                        get_string('emailvalidation_subject', 'artefact.internal'),
                        get_string('emailvalidation_body', 'artefact.internal', $USER->get('firstname'), $email, $key_url, $key_url_decline)
                    );
                }
                catch (EmailException $e) {
                    $email_errors[] = $email;
                }

                insert_record(
                    'artefact_internal_profile_email',
                    (object) array(
                        'owner'    => $USER->get('id'),
                        'email'    => $email,
                        'verified' => 0,
                        'key'      => $key,
                        'expiry'   => db_format_timestamp(time() + 86400),
                    )
                );
            }

            // remove old addresses
            foreach ($profilefields['email']['validated'] as $email) {
                if (in_array($email, $values['email']['validated'])) {
                    continue;
                }
                if (!empty($values['email']['unvalidated']) && in_array($email, $values['email']['unvalidated'])) {
                    continue;
                }

                $artefact_id = get_field('artefact_internal_profile_email', 'artefact', 'email', $email, 'owner', $USER->get('id'));

                delete_records('artefact_internal_profile_email', 'email', $email, 'owner', $USER->get('id'));

                if ($artefact_id) {
                    $artefact = new ArtefactTypeEmail($artefact_id);
                    $artefact->delete();
                    // this is unset here to force the destructor to run now,
                    // rather than script exit time where it doesn't like
                    // throwing exceptions properly
                    unset($artefact);
                }

            }
            foreach ($profilefields['email']['unvalidated'] as $email) {
                if (
                    in_array($email, $values['email']['validated'])
                    || (isset($values['email']['unvalidated'])
                        && in_array($email, $values['email']['unvalidated']))
                ) {
                    continue;
                }

                delete_records('artefact_internal_profile_email', 'email', $email, 'owner', $USER->get('id'));
            }

            if ($profilefields['email']['default'] != $values['email']['default']) {
                update_record(
                    'artefact_internal_profile_email',
                    (object)array(
                        'principal' => 0,
                    ),
                    (object)array(
                        'owner' => $USER->get('id'),
                        'email' => $profilefields['email']['default'],
                    )
                );
                update_record(
                    'artefact_internal_profile_email',
                    (object) array(
                        'principal' => 1,
                    ),
                    (object) array(
                        'owner' => $USER->get('id'),
                        'email' => $values['email']['default'],
                    )
                );
                update_record(
                    'usr',
                    (object) array(
                        'email' => $values['email']['default'],
                    ),
                    (object) array(
                        'id' => $USER->get('id'),
                    )
                );
                $USER->email = $values['email']['default'];
                $USER->commit();
            }
        }
        else if ($element == 'maildisabled') {
            continue;
        }
        else {
            if (!isset($profilefields[$element]) || $values[$element] != $profilefields[$element]) {
                $classname = generate_artefact_class_name($element);
                $profile = new $classname(0, array('owner' => $USER->get('id')));
                $profile->set('title', $values[$element]);
                $profile->commit();
            }
        }
    }

    try {
        db_commit();
    }
    catch (Exception $e) {
        profileform_reply($form, PIEFORM_ERR, get_string('profilefailedsaved','artefact.internal'));
    }

    handle_event('updateuser', $USER->get('id'));

    if (count($email_errors)) {
        profileform_reply($form, PIEFORM_ERR, array('message' => get_string('emailingfailed', 'artefact.internal', join(', ', $email_errors))));
    }

    profileform_reply($form, PIEFORM_OK, get_string('profilesaved','artefact.internal'));
}

function profileform_reply($form, $code, $message) {
    global $SESSION;
    if ($form->submitted_by_js()) {
        $form->json_reply($code, $message);
    }
    else if (is_string($message)) {
        if ($code == PIEFORM_ERR) {
            $method = 'add_error_msg';
        }
        else {
            $method = 'add_ok_msg';
        }
        $SESSION->$method($message);
        redirect('/artefact/internal/');
    }
    // Should never be replying with an array for an OK message
}


$smarty = smarty(array('artefact/internal/js/profile.js'), array(), array(
    'mahara' => array(
        'cannotremovedefaultemail',
        'emailtoolong'
    ),
    'artefact.internal' => array(
        'loseyourchanges',
    ),
));


$smarty->assign('profileform', $profileform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('artefact:internal:index.tpl');


function locked_profile_fields() {
    global $USER, $SESSION;

    // Profile fields are locked for a user if they are locked by any
    // institution the user is a member of, but not an admin for.
    $lockinginstitutions = array_keys($USER->get('institutions'));
    $lockinginstitutions[] = 'mahara';
    $lockinginstitutions = array_diff($lockinginstitutions, $USER->get('admininstitutions'));

    $locked = get_records_select_assoc(
        'institution_locked_profile_field',
        'name IN (' . join(',', array_map('db_quote', $lockinginstitutions)) . ')',
        null, '', 'profilefield,name'
    );

    if ($remotelocked = $SESSION->get('lockedfields')) {
        foreach ($remotelocked as $f) {
            if (!isset($locked[$f])) {
                $locked[$f] = $f;
            }
        }
    }

    return $locked;
}
