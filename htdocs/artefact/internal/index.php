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
 * @subpackage artefact-internal
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myprofile');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profile','artefact.internal'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'internal');

$element_list = call_static_method('ArtefactTypeProfile', 'get_all_fields');
$element_required = call_static_method('ArtefactTypeProfile', 'get_mandatory_fields');

// load existing profile information
$profilefields = array();
$profile_data = get_records_select_array('artefact', "owner=? AND artefacttype IN (" . join(",",array_map(create_function('$a','return db_quote($a);'),array_keys($element_list))) . ")", array($USER->get('id')));

if ($profile_data) {
    foreach ($profile_data as $field) {
        $profilefields[$field->artefacttype] = $field->title;
    }
}

$profilefields['email'] = array();
$profilefields['email']['all'] = get_records_array('artefact_internal_profile_email', 'owner', $USER->get('id'));
$profilefields['email']['validated'] = array();
$profilefields['email']['unvalidated'] = array();
if ($profilefields['email']['all']) {
    foreach ($profilefields['email']['all'] as $email) {
        if ($email->verified) {
            $profilefields['email']['validated'][] = $email->email;
        }
        else {
            $profilefields['email']['unvalidated'][] = $email->email;
        }

        if ($email->principal) {
            $profilefields['email']['default'] = $email->email;
        }
    }
}

// build form elements
$elements = array();
foreach ( $element_list as $element => $type ) {
    $elements[$element] = array(
        'type'  => $type,
        'title' => get_string($element, 'artefact.internal'),
    );

    if ($type == 'wysiwyg') {
        $elements[$element]['rows'] = 10;
        $elements[$element]['cols'] = 60;
    }
    if ($type == 'textarea') {
        $elements[$element]['rows'] = 4;
        $elements[$element]['cols'] = 60;
    }
    if ($element == 'country') {
        $elements[$element]['options'] = getoptions_country();
        // @todo configure default country somehow...
        $elements[$element]['defaultvalue'] = 'nz';
    }

    if (get_helpfile_location('artefact', 'internal', 'profileform', $element)) {
        $elements[$element]['help'] = true;
    }

    if (isset($profilefields[$element])) {
        $elements[$element]['defaultvalue'] = $profilefields[$element];
    }

    if (isset($element_required[$element])) {
        $elements[$element]['rules']['required'] = true;
    }

}
$elements['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('saveprofile','artefact.internal'),
);

$profileform = pieform(array(
    'name'       => 'profileform',
    'plugintype' => 'artefact',
    'pluginname' => 'internal',
    'jsform'     => true,
    'method'     => 'post',
    'elements'   => $elements,
));

function profileform_validate(Pieform $form, $values) {
    global $profilefields;

    if (
        !isset($values['email']['default'])
        || !in_array($values['email']['default'], $profilefields['email']['validated'])
        || !in_array($values['email']['default'], $values['email']['validated'])
    ) {
        $form->set_error('email', get_string('primaryemailinvalid'));
    }

    if (isset($values['email']['unvalidated']) && is_array($values['email']['validated'])) {
        foreach ($values['email']['unvalidated'] as $email) {
            if (record_exists('artefact_internal_profile_email', 'email', $email)) {
                $form->set_error('email', get_string('unvalidatedemailalreadytaken', 'artefact.internal'));
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

    foreach ($element_list as $element => $type) {

        if ($element == 'email') {
            if (!isset($values['email']['unvalidated'])) {
                $values['email']['unvalidated'] = array();
            }
            // find new addresses
            foreach ($values['email']['unvalidated'] as $email) {
                if (
                    in_array($email, $profilefields['email']['validated'])
                    || in_array($email, $profilefields['email']['unvalidated'])
                ) {
                    continue;
                }

                $key = get_random_key();
                $key_url = get_config('wwwroot') . 'artefact/internal/validate.php?email=' . rawurlencode($email) . '&key=' . $key;

                try {
                    email_user(
                        (object)array(
                            'username'      => $USER->get('username'),
                            'firstname'     => $USER->get('firstname'),
                            'lastname'      => $USER->get('lastname'),
                            'preferredname' => $USER->get('preferredname'),
                            'admin'         => $USER->get('admin'),
                            'email'         => $email,
                        ),
                        null,
                        get_string('emailvalidation_subject', 'artefact.internal'),
                        get_string('emailvalidation_body', 'artefact.internal', $USER->get('firstname'), $email, $key_url)
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
                if (
                    in_array($email, $values['email']['validated'])
                    || in_array($email, $values['email']['unvalidated'])
                ) {
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
                    || in_array($email, $values['email']['unvalidated'])
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
            }
        }
        else {
            $classname = generate_artefact_class_name($element);
            $profile = new $classname(0, array('owner' => $USER->get('id')));
            $profile->set('title', $values[$element]);
            $profile->commit();
        }
    }

    try {
        db_commit();
    }
    catch (Exception $e) {
        $form->json_reply(PIEFORM_ERR, get_string('profilefailedsaved','artefact.internal'));
    }

    handle_event('updateuser', $USER->get('id'));

    if (count($email_errors)) {
        $form->json_reply(PIEFORM_ERR, array('message' => get_string('emailingfailed', 'artefact.internal', join(', ', $email_errors))));
    }

    $form->json_reply(PIEFORM_OK, get_string('profilesaved','artefact.internal'));

}


$smarty = smarty(array(), array(), array(
    'mahara' => array('cantremovedefaultemail'),
));

$smarty->assign('profileform', $profileform);

$smarty->assign('resumeinstalled', record_exists('artefact_installed', 'name', 'resume')); 
$smarty->display('artefact:internal:index.tpl');

?>
