<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/uploadcsv');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('uploadcsv', 'admin'));
require_once('institution.php');
safe_require('artefact', 'internal');

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$FORMAT = array();
$specialcases = array('username', 'password', 'remoteuser');
// Don't upload social profiles for now. A user can have multiple profiles. Not sure how to put that in a csv.
$notallowed = array('socialprofile');
$ALLOWEDKEYS = array_keys(ArtefactTypeProfile::get_all_fields());
$ALLOWEDKEYS = array_diff($ALLOWEDKEYS, $notallowed);
$maildisabled = array_search('maildisabled', $ALLOWEDKEYS);
unset($ALLOWEDKEYS[$maildisabled]);
$ALLOWEDKEYS = array_merge($ALLOWEDKEYS, $specialcases);

$UPDATES         = array(); // During validation, remember which users already exist
$INSTITUTIONNAME = array(); // Mapping from institution id to display name

if ($USER->get('admin')) {
    $authinstances = auth_get_auth_instances();
} else {
    $admininstitutions = $USER->get('admininstitutions');
    $authinstances = auth_get_auth_instances_for_institutions($admininstitutions);
    if (empty($authinstances)) {
        $SESSION->add_info_msg(get_string('configureauthplugin', 'admin'));
        redirect(get_config('wwwroot').'admin/users/institutions.php?i='.key($admininstitutions).'&amp;edit=1');
    }
}

if (count($authinstances) > 0) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        if ($USER->can_edit_institution($authinstance->name)) {
            $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
            $INSTITUTIONNAME[$authinstance->name] = $authinstance->displayname;
        }
    }
    if ($USER->get('admin')) {
        $definst = get_field('auth_instance', 'id', 'institution', 'mahara');
        $default = $definst ? $definst : key($options);
    }
    else {
        $default = key($options);
    }

    $authinstanceelement = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'description' => get_string('uploadcsvinstitution', 'admin'),
        'options' => $options,
        'defaultvalue' => $default
    );
}

$prefs = (object) expected_account_preferences();

$form = array(
    'name' => 'uploadcsv',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements' => array(
        'authinstance' => $authinstanceelement,
        'quota' => array(
            'type' => 'bytes',
            'title' => get_string('filequota1', 'admin'),
            'description' => get_string('filequotadescription', 'admin'),
            'rules' => array('integer' => true, 'minvalue' => 0),
            'defaultvalue' => get_config_plugin('artefact', 'file', 'defaultquota'),
        ),
        'file' => array(
            'type' => 'file',
            'title' => get_string('csvfile', 'admin'),
            'description' => get_string('csvfiledescription', 'admin'),
            'accept' => '.csv, text/csv, application/csv, text/comma-separated-values',
            'rules' => array(
                'required' => true
            )
        ),
        'forcepasswordchange' => array(
            'type'         => 'switchbox',
            'title'        => get_string('forceuserstochangepassword', 'admin'),
            'description'  => get_string('forceuserstochangepassworddescription', 'admin'),
            'defaultvalue' => true,
        ),
        'emailusers' => array(
            'type' => 'switchbox',
            'title' => get_string('emailusersaboutnewaccount', 'admin'),
            'description' => get_string('emailusersaboutnewaccountdescription', 'admin'),
            'defaultvalue' => true,
        ),
        'updateusers' => array(
            'type' => 'switchbox',
            'title' => get_string('updateusers', 'admin'),
            'description' => get_string('updateusersdescription', 'admin'),
            'defaultvalue' => false,
        ),
        'accountprefs' => array(
            'type' => 'fieldset',
            'legend' => get_string('accountoptionsdesc', 'account'),
            'collapsible' => true,
            'collapsed' => true,
            'class' => 'last with-formgroup',
            'elements' => general_account_prefs_form_elements($prefs),
        ),
        'progress_meter_token' => array(
            'type' => 'hidden',
            'value' => 'uploaduserscsv',
            'readonly' => TRUE,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('uploadcsv', 'admin'),
            'class' => 'btn-primary'
        )
    )
);

if ($maxcsvlines = get_config('maxusercsvlines')) {
    $form['elements']['file']['description'] .= ' ' . get_string('csvmaxusersdescription', 'admin', get_string('nusers', 'mahara', $maxcsvlines));
}

unset($prefs);

if (!($USER->get('admin') || get_config_plugin('artefact', 'file', 'institutionaloverride'))) {
    $form['elements']['quota'] = array(
        'type'         => 'text',
        'disabled'     => true,
        'title'        => get_string('filequota1', 'admin'),
        'description'  => get_string('filequotadescription', 'admin'),
        'value'        => display_size(get_config_plugin('artefact', 'file', 'defaultquota')),
    );
}

/**
 * The CSV file is parsed here so validation errors can be returned to the
 * user. The data from a successful parsing is stored in the <var>$CVSDATA</var>
 * array so it can be accessed by the submit function
 *
 * @param Pieform  $form   The form to validate
 * @param array    $values The values submitted
 */
function uploadcsv_validate(Pieform $form, $values) {
    global $CSVDATA, $ALLOWEDKEYS, $FORMAT, $USER, $INSTITUTIONNAME, $UPDATES;

    // Don't even start attempting to parse if there are previous errors
    if ($form->has_errors()) {
        return;
    }

    $steps_done = 0;
    $steps_total = $values['updateusers'] ? 5 : 4;

    if ($values['file']['size'] == 0) {
        $form->set_error('file', $form->i18n('rule', 'required', 'required', array()));
        return;
    }

    if ($USER->get('admin') || get_config_plugin('artefact', 'file', 'institutionaloverride')) {
        $maxquotaenabled = get_config_plugin('artefact', 'file', 'maxquotaenabled');
        $maxquota = get_config_plugin('artefact', 'file', 'maxquota');
        if ($maxquotaenabled && $values['quota'] > $maxquota) {
            $form->set_error('quota', get_string('maxquotaexceededform', 'artefact.file', display_size($maxquota)));
        }
    }

    require_once('csvfile.php');

    $authinstance = (int) $values['authinstance'];
    $institution = get_field('auth_instance', 'institution', 'id', $authinstance);
    if (!$USER->can_edit_institution($institution)) {
        $form->set_error('authinstance', get_string('notadminforinstitution', 'admin'));
        return;
    }

    $authobj = AuthFactory::create($authinstance);

    $csvusers = new CsvFile($values['file']['tmp_name']);
    $csvusers->set('allowedkeys', $ALLOWEDKEYS);

    // Now we know all of the field names are valid, we need to make
    // sure that the required fields are included
    $mandatoryfields = array(
        'username', 'email', 'firstname', 'lastname'
    );
    if (!$values['updateusers']) {
        $mandatoryfields[] = 'password';
    }

    $csvusers->set('mandatoryfields', $mandatoryfields);
    $csvdata = $csvusers->get_data();

    if (!empty($csvdata->errors['file'])) {
        $form->set_error('file', $csvdata->errors['file']);
        return;
    }

    $csverrors = new CSVErrors();

    $formatkeylookup = array_flip($csvdata->format);

    // First pass validates usernames & passwords in the file, and builds
    // up a list indexed by username.

    $emails = array();
    if (isset($formatkeylookup['remoteuser'])) {
        $remoteusers = array();
    }

    $num_lines = count($csvdata->data);

    $maxcsvlines = get_config('maxusercsvlines');
    if ($maxcsvlines && $maxcsvlines < $num_lines) {
        $form->set_error('file', get_string('uploadcsverrortoomanyusers', 'admin', get_string('nusers', 'mahara', $maxcsvlines)));
        return;
    }

    $existing_usernames = get_records_menu('usr', '', NULL, '', 'LOWER(username) AS username, 1 AS key2');
    $existing_usr_email_addresses = get_records_menu('usr', '', NULL, '', 'email, 1 AS key2');
    $existing_internal_email_addresses = get_records_menu('artefact_internal_profile_email', 'verified', 1, '', 'email, 1 AS key2');

    foreach ($csvdata->data as $key => $line) {
        // If headers exists, increment i = key + 2 for actual line number
        $i = ($csvusers->get('headerExists')) ? ($key + 2) : ($key + 1);

        if (!($key % 25)) {
            set_progress_info('uploaduserscsv', $key, $num_lines * $steps_total, get_string('validating', 'admin'));
        }

        // Trim non-breaking spaces -- they get left in place by File_CSV
        foreach ($line as &$field) {
            $field = preg_replace('/^(\s|\xc2\xa0)*(.*?)(\s|\xc2\xa0)*$/', '$2', $field);
        }

        if (count($line) != count($csvdata->format)) {
            $csverrors->add($i, get_string('uploadcsverrorwrongnumberoffields', 'admin', $i));
            continue;
        }

        // We have a line with the correct number of fields, but should validate these fields
        // Note: This validation should really be methods on each profile class, that way
        // it can be used in the profile screen as well.

        $username = $line[$formatkeylookup['username']];
        $password = isset($formatkeylookup['password']) ? $line[$formatkeylookup['password']] : null;
        $email    = $line[$formatkeylookup['email']];
        if (isset($remoteusers)) {
            $remoteuser = strlen($line[$formatkeylookup['remoteuser']]) ? $line[$formatkeylookup['remoteuser']] : null;
        }

        if (method_exists($authobj, 'is_username_valid_admin')) {
            if (!$authobj->is_username_valid_admin($username)) {
                $csverrors->add($i, get_string('uploadcsverrorinvalidusername', 'admin', $i));
            }
        }
        else if (method_exists($authobj, 'is_username_valid')) {
            if (!$authobj->is_username_valid($username)) {
                $csverrors->add($i, get_string('uploadcsverrorinvalidusername', 'admin', $i));
            }
        }

        if (!$values['updateusers']) {
            // Note: only checks for valid form are done here, none of the checks
            // like whether the password is too easy. The user is going to have to
            // change their password on first login anyway.
            if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
                $csverrors->add($i, get_string('uploadcsverrorinvalidpassword', 'admin', $i));
            }
        }

        if (isset($emails[$email])) {
            // Duplicate email within this file.
            $csverrors->add($i, get_string('uploadcsverroremailaddresstaken', 'admin', $i, $email));
        }
        else if (!sanitize_email($email)) {
            $csverrors->add($i, get_string('uploadcsverrorinvalidemail', 'admin', $i, $email));
        }
        else if (!$values['updateusers']) {
            // The email address must be new
            if (array_key_exists($email, $existing_usr_email_addresses) || array_key_exists($email, $existing_internal_email_addresses)) {
                $csverrors->add($i, get_string('uploadcsverroremailaddresstaken', 'admin', $i, $email));
            }
        }
        $emails[$email] = 1;

        if (isset($remoteusers) && $remoteuser) {
            if (isset($remoteusers[$remoteuser])) {
                $csverrors->add($i, get_string('uploadcsverrorduplicateremoteuser', 'admin', $i, $remoteuser));
            }
            else if (!$values['updateusers']) {
                if ($remoteuserowner = get_record_sql('
                    SELECT u.username
                    FROM {auth_remote_user} aru JOIN {usr} u ON aru.localusr = u.id
                    WHERE aru.remoteusername = ? AND aru.authinstance = ?',
                    array($remoteuser, $authinstance))) {
                    $csverrors->add($i, get_string('uploadcsverrorremoteusertaken', 'admin', $i, $remoteuser, $remoteuserowner->username));
                }
            }
            $remoteusers[$remoteuser] = true;
        }

        // If we didn't even get a username, we can't check for duplicates, so move on.
        if (strlen($username) < 1) {
            continue;
        }

        if (isset($usernames[strtolower($username)])) {
            // Duplicate username within this file.
            $csverrors->add($i, get_string('uploadcsverroruseralreadyexists', 'admin', $i, $username));
        }
        else {
            if (!$values['updateusers'] && array_key_exists(strtolower($username), $existing_usernames)) {
                $csverrors->add($i, get_string('uploadcsverroruseralreadyexists', 'admin', $i, $username));
            }
            $usernames[strtolower($username)] = array(
                'username' => $username,
                'password' => $password,
                'email'    => $email,
                'lineno'   => $i,
                'raw'      => $line,
            );
            if (!empty($remoteuser) && !empty($remoteusers[$remoteuser])) {
                $usernames[strtolower($username)]['remoteuser'] = $remoteuser;
            }
        }
    }

    // If the admin is trying to overwrite existing users, identified by username,
    // this second pass performs some additional checks

    if ($values['updateusers']) {

        $key = 0;

        foreach ($usernames as $lowerusername => $data) {

            if (!($key % 25)) {
                set_progress_info('uploaduserscsv', $num_lines + $key, $num_lines * $steps_total, get_string('checkingupdates', 'admin'));
            }
            $key++;

            $line      = $data['lineno'];
            $username  = $data['username'];
            $password  = $data['password'];
            $email     = $data['email'];

            // If the user already exists, they must already be in this institution.
            $userinstitutions = get_records_sql_assoc("
                SELECT COALESCE(ui.institution, 'mahara') AS institution, u.id
                FROM {usr} u LEFT JOIN {usr_institution} ui ON u.id = ui.usr
                WHERE LOWER(u.username) = ?",
                array($lowerusername)
            );
            if ($userinstitutions) {
                if (!isset($userinstitutions[$institution])) {
                    if ($institution == 'mahara') {
                        $institutiondisplay = array();
                        foreach ($userinstitutions as $i) {
                            $institutiondisplay[] = $INSTITUTIONNAME[$i->institution];
                        }
                        $institutiondisplay = join(', ', $institutiondisplay);
                        $message = get_string('uploadcsverroruserinaninstitution', 'admin', $line, $username, $institutiondisplay);
                    }
                    else {
                        $message = get_string('uploadcsverrorusernotininstitution', 'admin', $line, $username, $INSTITUTIONNAME[$institution]);
                    }
                    $csverrors->add($line, $message);
                }
                else {
                    // Remember that this user is being updated
                    $UPDATES[$username] = 1;
                }
            }
            else {
                // New user, check the password
                if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
                    $csverrors->add($line, get_string('uploadcsverrorinvalidpassword', 'admin', $line));
                }
            }

            // Check if the email already exists and if it's owned by this user.  This query can return more
            // than one row when there are duplicate emails already on the site.  If that happens, things are
            // already a bit out of hand, and we'll just allow an update if this user is one of the users who
            // owns the email.
            $emailowned = get_records_sql_assoc('
                SELECT LOWER(u.username) AS lowerusername, ae.principal FROM {usr} u
                LEFT JOIN {artefact_internal_profile_email} ae ON u.id = ae.owner AND ae.verified = 1 AND ae.email = ?
                WHERE ae.owner IS NOT NULL OR u.email = ?',
                array($email, $email)
            );

            // If the email is owned by someone else, it could still be okay provided
            // that other user's email is also being changed in this csv file.
            if ($emailowned && !isset($emailowned[$lowerusername])) {
                foreach ($emailowned as $e) {
                    // Only primary emails can be set in uploadcsv, so it's an error when someone else
                    // owns the email as a secondary.
                    if (!$e->principal) {
                        $csverrors->add($line, get_string('uploadcsverroremailaddresstaken', 'admin', $line, $email));
                        break;
                    }
                    // It's also an error if the email owner is not being updated in this file
                    if (!isset($usernames[$e->lowerusername])) {
                        $csverrors->add($line, get_string('uploadcsverroremailaddresstaken', 'admin', $line, $email));
                        break;
                    }
                    // If the other user is being updated in this file, but isn't changing their
                    // email address, it's ok, we've already notified duplicate emails within the file.
                }
            }

            if (isset($remoteusers) && !empty($data['remoteuser'])) {
                $remoteuser = $data['remoteuser'];
                $remoteuserowner = get_field_sql('
                    SELECT LOWER(u.username)
                    FROM {usr} u JOIN {auth_remote_user} aru ON u.id = aru.localusr
                    WHERE aru.remoteusername = ? AND aru.authinstance = ?',
                    array($remoteuser, $authinstance)
                );
                if ($remoteuserowner && $remoteuserowner != $lowerusername && !isset($usernames[$remoteuserowner])) {
                    // The remote username is owned by some other user who is not being updated in this file
                    $csverrors->add($line, get_string('uploadcsverrorremoteusertaken', 'admin', $line, $remoteuser, $remoteuserowner));
                }
            }
        }
    }

    if ($errors = $csverrors->process()) {
        $form->set_error('file', clean_html($errors), false);
        return;
    }

    $FORMAT = $csvdata->format;
    $CSVDATA = $csvdata->data;
}

/**
 * Add the users to the system. Make sure that they have to change their
 * password on next login also.
 */
function uploadcsv_submit(Pieform $form, $values) {
    global $USER, $SESSION, $CSVDATA, $FORMAT, $UPDATES;

    $formatkeylookup = array_flip($FORMAT);

    $authinstance = (int) $values['authinstance'];
    $authrecord   = get_record('auth_instance', 'id', $authinstance);
    $authobj      = AuthFactory::create($authinstance);

    $institution = new Institution($authobj->institution);

    $maxusers = $institution->maxuseraccounts;
    if (!empty($maxusers)) {
        $members = count_records_sql('
            SELECT COUNT(*) FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
            WHERE i.institution = ? AND u.deleted = 0', array($institution->name));
        if ($members + count($CSVDATA) > $maxusers) {
            $SESSION->add_error_msg(get_string('uploadcsvfailedusersexceedmaxallowed', 'admin'));
            redirect('/admin/users/uploadcsv.php');
        }
    }

    if ($values['updateusers']) {
        log_info('Updating users from the CSV file');
    }
    else {
        log_info('Inserting users from the CSV file');
    }
    db_begin();

    $addedusers = array();

    $cfgsendemail = get_config('sendemail');
    if (empty($values['emailusers'])) {
        // Temporarily disable email sent during user creation, e.g. institution membership
        $GLOBALS['CFG']->sendemail = false;
    }

    $key = 0;
    $steps_total = $values['updateusers'] ? 5 : 4;
    $steps_done = $steps_total - 3;
    $num_lines = sizeof($CSVDATA);

    foreach ($CSVDATA as $record) {

        if (!($key % 25)) {
            // This part has three times the weight of the other two steps.
            set_progress_info('uploaduserscsv', $num_lines * $steps_done + $key * 3, $num_lines * $steps_total, get_string('committingchanges', 'admin'));
        }
        $key++;

        $user = new StdClass;
        foreach ($FORMAT as $field) {
            if ($field == 'username'  ||
                $field == 'firstname' ||
                $field == 'lastname'  ||
                $field == 'password'  ||
                $field == 'email'     ||
                $field == 'studentid' ||
                $field == 'preferredname') {
                $user->{$field} = $record[$formatkeylookup[$field]];
            }
        }
        $user->authinstance = $authinstance;
        if ($USER->get('admin') || get_config_plugin('artefact', 'file', 'institutionaloverride')) {
            $user->quota        = $values['quota'];
        }

        $profilefields = new StdClass;
        $remoteuser = null;
        foreach ($FORMAT as $field) {
            if ($field == 'username' || $field == 'password') {
                continue;
            }
            if ($field == 'remoteuser') {
                if (!empty($record[$formatkeylookup[$field]])) {
                    $remoteuser = $record[$formatkeylookup[$field]];
                }
                continue;
            }
            $profilefields->{$field} = $record[$formatkeylookup[$field]];
        }

        if (!$values['updateusers'] || !isset($UPDATES[$user->username])) {
            $user->passwordchange = (int)$values['forcepasswordchange'];

            $user->id = create_user($user, $profilefields, $institution, $authrecord, $remoteuser, $values, true);

            $addedusers[] = $user;
            log_debug('added user ' . $user->username);
        }
        else if (isset($UPDATES[$user->username])) {
            $updated = update_user($user, $profilefields, $remoteuser, $values, true, true);

            if (empty($updated)) {
                // Nothing changed for this user
                unset($UPDATES[$user->username]);
            }
            else {
                $UPDATES[$user->username] = $updated;
                log_debug('updated user ' . $user->username . ' (' . implode(', ', array_keys($updated)) . ')');
            }
        }
        set_time_limit(10);
    }
    db_commit();

    // Reenable email
    set_config('sendemail', $cfgsendemail);

    // Only send e-mail to users after we're sure they have been inserted
    // successfully
    $straccountcreatedtext = ($values['forcepasswordchange']) ? 'accountcreatedchangepasswordtext' : 'accountcreatedtext';
    $straccountcreatedhtml = ($values['forcepasswordchange']) ? 'accountcreatedchangepasswordhtml' : 'accountcreatedhtml';
    if ($values['emailusers'] && $addedusers) {
        foreach ($addedusers as $user) {
            $failedusers = array();
            try {
                email_user($user, null, get_string('accountcreated', 'mahara', get_config('sitename')),
                    get_string($straccountcreatedtext, 'mahara', $user->firstname, get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('sitename')),
                    get_string($straccountcreatedhtml, 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
                );
            }
            catch (EmailException $e) {
                log_info($e->getMessage());
                $failedusers[] = $user;
            }
        }

        if ($failedusers) {
            $message = get_string('uploadcsvsomeuserscouldnotbeemailed', 'admin') . "\n<ul>\n";
            foreach ($failedusers as $user) {
                $message .= '<li>' . full_name($user) . ' &lt;' . hsc($user->email) . "&gt;</li>\n";
            }
            $message .= "</ul>\n";
            $SESSION->add_info_msg($message, false);
        }
    }

    log_info('Added ' . count($addedusers) . ' users, updated ' . count($UPDATES) . ' users.');

    $SESSION->add_ok_msg(get_string('csvfileprocessedsuccessfully', 'admin'));
    if ($UPDATES) {
        $updatemsg = smarty_core();
        $updatemsg->assign('added', count($addedusers));
        $updatemsg->assign('updates', $UPDATES);
        $SESSION->add_info_msg($updatemsg->fetch('admin/users/csvupdatemessage.tpl'), false);
    }
    else {
        $SESSION->add_ok_msg(get_string('numbernewusersadded', 'admin', count($addedusers)));
    }

    set_progress_done('uploaduserscsv');

    redirect('/admin/users/uploadcsv.php');
}

// Get a list of all profile fields, to inform the user on what fields they can
// put in their file.
natsort($ALLOWEDKEYS);
$fields = "<ul class='fieldslist column-list'>\n";
foreach ($ALLOWEDKEYS as $type) {
    if ($type == 'firstname' || $type == 'lastname' || $type == 'email' || $type == 'username' || $type == 'password') {
        continue;
    }
    $fields .= '<li>' . hsc($type) . "</li>\n";
}
$fields .= "<div class=cl></div></ul>\n";

$uploadcsvpagedescription = get_string('uploadcsvpagedescription6', 'admin', $fields);

$form = pieform($form);

set_progress_done('uploaduserscsv');

$smarty = smarty(array('adminuploadcsv'));
setpageicon($smarty, 'icon-user');
$smarty->assign('uploadcsvpagedescription', $uploadcsvpagedescription);
$smarty->assign('uploadcsvform', $form);
$smarty->display('admin/users/uploadcsv.tpl');
