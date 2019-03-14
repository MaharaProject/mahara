<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'profile');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profile','artefact.internal'));
safe_require('artefact', 'internal');

$fieldset = param_alpha('fs', 'aboutme');

$element_list = ArtefactTypeProfile::get_all_fields();
$element_data = ArtefactTypeProfile::get_field_element_data();
$element_required = ArtefactTypeProfile::get_mandatory_fields();

// load existing profile fields
$profilefields = array();
$profile_data = get_records_select_array('artefact', "owner=? AND artefacttype IN (" . join(",",array_map(function($a) { return db_quote($a); },array_keys($element_list))) . ")", array($USER->get('id')));

if ($profile_data) {
    foreach ($profile_data as $field) {
        if ($field->artefacttype == 'introduction') {
            $profilefields[$field->artefacttype] = $field->description;
        }
        else {
            $profilefields[$field->artefacttype] = $field->title;
        }
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
        $items[$element]['rules'] = array('maxlength' => 1000000);
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
    $classname = 'ArtefactType' . ucfirst($element);
    if (is_callable(array($classname, 'getoptions'))) {
        $options = call_static_method($classname, 'getoptions');
        $items[$element]['options'] = $options;
    }
    if (is_callable(array($classname, 'defaultoption'))) {
        $defaultoption = call_static_method($classname, 'defaultoption');
        $items[$element]['defaultvalue'] = $defaultoption;
    }
    if ($element == 'socialprofile') {
        $items[$element] = ArtefactTypeSocialprofile::render_profile_element();
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
        if ($element == 'email') {
            $items[$element]['help'] = false;
        }
    }

}
if ($items['firstname']) {
    $items['firstname']['autofocus'] = true;
}
if (isset($items['socialprofile']) && $items['socialprofile']) {
    $items['socialprofile']['title'] = null;
}


$items['maildisabled']['ignore'] = !get_account_preference($USER->get('id'),'maildisabled');
$items['maildisabled']['value'] = get_string('maildisableddescription', 'account', get_config('wwwroot') . 'account/index.php');

// build form elements
$elements = array(
    'profile' => array(
        'type' => 'fieldset',
        'legend' => get_string('aboutme', 'artefact.internal'),
        'class' => 'has-help' . $fieldset != 'aboutme' ? 'collapsed' : '',
        'elements' => get_desired_fields($items, 'about'),
    ),
    'contact' => array(
        'type' => 'fieldset',
        'legend' => get_string('contact', 'artefact.internal'),
        'class' => $fieldset != 'contact' ? '' : '',
        'elements' => get_desired_fields($items, 'contact'),
    ),
    'social' => array(
        'type' => 'fieldset',
        'legend' => get_string('social', 'artefact.internal'),
        'class' => $fieldset != 'social' ? 'collapsed' : '',
        'elements' => get_desired_fields($items, 'social'),
    ),
    'general' => array(
        'type' => 'fieldset',
        'legend' => get_string('general'),
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
        'class' => 'btn-primary'
    )
);
// Don't include fieldset if 'socialprofile' is not installed
if (!get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
    unset($elements['social']);
}

$profileform = pieform(array(
    'name'       => 'profileform',
    'class'      => 'jstabs form-group-nested',
    'plugintype' => 'artefact',
    'pluginname' => 'internal',
    // will be uncommented when js for tabbed interface is called again after form submit
    // 'jsform'     => true,
    'method'     => 'post',
    'renderer'   => 'div',
    'elements'   => $elements,
    'autofocus'  => false,
));

function get_desired_fields(&$allfields, $section) {
    global $USER;
    $desiredfields = array('about' => array('firstname', 'lastname', 'studentid', 'preferredname', 'introduction'),
                           'contact' => array('email', 'maildisabled', 'officialwebsite', 'personalwebsite', 'blogaddress', 'address', 'town', 'city', 'country', 'homenumber', 'businessnumber', 'mobilenumber', 'faxnumber'),
                           'social' => array('socialprofile'),
                           );

    if (is_callable(array('ArtefactTypeProfileLocal', 'get_desired_fields'))) {
        $localfields = call_static_method('ArtefactTypeProfileLocal', 'get_desired_fields');
        foreach ($localfields as $k => $v) {
            foreach ($v as $k2 => $v2) {
                array_splice($desiredfields[$k], $k2, 0, array($v2));
            }
        }
    }

    if ($section == 'about') {
        $r = get_record_select('view', 'type = ? AND owner = ?', array('profile', $USER->id), 'id');
        $label = '<div id="profileicon" class="profile-icon pseudolabel float-left"><a href="' . get_config('wwwroot') . 'artefact/file/profileicons.php" class="user-icon"><img src="'
            . profile_icon_url($USER, 100, 100) . '" alt="' . get_string("editprofileicon", "artefact.file") . '"></a></div>';
        $descr = '' . get_string('aboutprofilelinkdescription', 'artefact.internal', get_config('wwwroot') . 'view/blocks.php?id=' . $r->id);
        $descr .= '<p>' . get_string('aboutdescription', 'artefact.internal') . '</p>';
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
    foreach ($desiredfields[$section] as $field) {
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
        foreach ($values['email']['unsent'] as $email) {
            if (!sanitize_email($email)) {
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
    require_once('embeddedimage.php');

    db_begin();

    $now = db_format_timestamp(time());
    $email_errors = array();

    $lockedfields = locked_profile_fields();
    $alertuserofnewemail = array();

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
                    $alertuserofnewemail[] = $email;

                    $sitename = get_config('sitename');
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
                        get_string('emailvalidation_body1', 'artefact.internal', $USER->get('firstname'), $email, $sitename, $key_url, $sitename, $key_url_decline)
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
            // alert user that new email addresses have been added
            if (!empty($alertuserofnewemail)) {
                $emails = implode(', ', $alertuserofnewemail);
                try {
                    $sitename = get_config('sitename');
                    email_user(
                        (object)array(
                            'id'            => $USER->get('id'),
                            'username'      => $USER->get('username'),
                            'firstname'     => $USER->get('firstname'),
                            'lastname'      => $USER->get('lastname'),
                            'preferredname' => $USER->get('preferredname'),
                            'admin'         => $USER->get('admin'),
                            'staff'         => $USER->get('staff'),
                            'email'         => $profilefields['email']['default'],
                        ),
                        null,
                        get_string('newemailalert_subject', 'artefact.internal', $sitename),
                        get_string('newemailalert_body_text1', 'artefact.internal', $USER->get('firstname'), $sitename, $emails, $sitename, get_config('wwwroot')),
                        get_string('newemailalert_body_html1', 'artefact.internal', hsc($USER->get('firstname')), hsc($sitename), hsc($emails), hsc($sitename), get_config('wwwroot'))
                    );
                }
                catch (EmailException $e) {
                    $email_errors[] = $profilefields['email']['default'];
                }
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
                delete_records('usr_password_request', 'usr', $USER->get('id'));
                $USER->email = $values['email']['default'];
                $USER->commit();
            }
        }
        else if (in_array($element, array('maildisabled', 'socialprofile'))) {
            continue;
        }
        else {
            if (!isset($profilefields[$element]) || $values[$element] != $profilefields[$element]) {
                if ($element == 'introduction') {
                    $newintroduction = EmbeddedImage::prepare_embedded_images($values[$element], 'profileintrotext', $USER->get('id'));
                    $values[$element] = $newintroduction;
                }
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
        redirect('/artefact/internal/index.php');
    }
    // Should never be replying with an array for an OK message
}


$smarty = smarty(array(), array(), array(
    'mahara' => array(
        'cannotremovedefaultemail',
        'emailtoolong',
        'emailinvalid',
        'tabs',
        'tab',
        'selected',
    ),
    'artefact.internal' => array(
        'loseyourchanges',
    ),
));
setpageicon($smarty, 'icon-id-card-o');
$smarty->assign('profileform', $profileform);
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
