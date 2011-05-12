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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/uploadcsv');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('uploadcsv', 'admin'));
require_once('pieforms/pieform.php');
require_once('institution.php');
safe_require('artefact', 'internal');
raise_memory_limit("512M");

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$FORMAT = array();
$ALLOWEDKEYS = array(
    'username',
    'remoteuser',
    'password',
    'email',
    'firstname',
    'lastname',
    'preferredname',
    'studentid',
    'introduction',
    'officialwebsite',
    'personalwebsite',
    'blogaddress',
    'address',
    'town',
    'city',
    'country',
    'homenumber',
    'businessnumber',
    'mobilenumber',
    'faxnumber',
    'icqnumber',
    'msnnumber',
    'aimscreenname',
    'yahoochat',
    'skypeusername',
    'jabberusername',
    'occupation',
    'industry',
    'authinstance'
);
$CSVERRORS = array();

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
        }
    }
    $default = key($options);

    $authinstanceelement = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'description' => get_string('uploadcsvinstitution', 'admin'),
        'options' => $options,
        'defaultvalue' => $default
    );
}

$form = array(
    'name' => 'uploadcsv',
    'elements' => array(
        'authinstance' => $authinstanceelement,
        'file' => array(
            'type' => 'file',
            'title' => get_string('csvfile', 'admin'),
            'description' => get_string('csvfiledescription', 'admin'),
            'rules' => array(
                'required' => true
            )
        ),
        'forcepasswordchange' => array(
            'type'         => 'checkbox',
            'title'        => get_string('forceuserstochangepassword', 'admin'),
            'description'  => get_string('forceuserstochangepassworddescription', 'admin'),
            'defaultvalue' => true,
        ),
        'emailusers' => array(
            'type' => 'checkbox',
            'title' => get_string('emailusersaboutnewaccount', 'admin'),
            'description' => get_string('emailusersaboutnewaccountdescription', 'admin'),
            'defaultvalue' => true,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('uploadcsv', 'admin')
        )
    )
);

/**
 * The CSV file is parsed here so validation errors can be returned to the
 * user. The data from a successful parsing is stored in the <var>$CVSDATA</var>
 * array so it can be accessed by the submit function
 *
 * @param Pieform  $form   The form to validate
 * @param array    $values The values submitted
 */
function uploadcsv_validate(Pieform $form, $values) {
    global $CSVDATA, $ALLOWEDKEYS, $FORMAT, $USER, $CSVERRORS;

    // Don't even start attempting to parse if there are previous errors
    if ($form->has_errors()) {
        return;
    }

    if ($values['file']['size'] == 0) {
        $form->set_error('file', $form->i18n('rule', 'required', 'required', array()));
        return;
    }

    require_once('csvfile.php');

    $authinstance = (int) $values['authinstance'];
    $institution = get_field('auth_instance', 'institution', 'id', $authinstance);
    if (!$USER->can_edit_institution($institution)) {
        $form->set_error('authinstance', get_string('notadminforinstitution', 'admin'));
        return;
    }

    $usernames = array();
    $emails = array();

    $csvusers = new CsvFile($values['file']['tmp_name']);
    $csvusers->set('allowedkeys', $ALLOWEDKEYS);

    // Now we know all of the field names are valid, we need to make
    // sure that the required fields are included
    $mandatoryfields = array(
        'username',
        'password'
    );
    $mandatoryfields = array_merge($mandatoryfields, array_keys(ArtefactTypeProfile::get_mandatory_fields()));
    if ($lockedprofilefields = get_column('institution_locked_profile_field', 'profilefield', 'name', $institution)) {
        $mandatoryfields = array_merge($mandatoryfields, $lockedprofilefields);
    }

    $csvusers->set('mandatoryfields', $mandatoryfields);
    $csvdata = $csvusers->get_data();

    if (!empty($csvdata->errors['file'])) {
        $form->set_error('file', $csvdata->errors['file']);
        return;
    }

    foreach ($csvdata->data as $key => $line) {
        // If headers exists, increment i = key + 2 for actual line number
        $i = ($csvusers->get('headerExists')) ? ($key + 2) : ($key + 1);

        // Trim non-breaking spaces -- they get left in place by File_CSV
        foreach ($line as &$field) {
            $field = preg_replace('/^(\s|\xc2\xa0)*(.*?)(\s|\xc2\xa0)*$/', '$2', $field);
        }

        // We have a line with the correct number of fields, but should validate these fields
        // Note: This validation should really be methods on each profile class, that way
        // it can be used in the profile screen as well.

        $formatkeylookup = array_flip($csvdata->format);
        $username = $line[$formatkeylookup['username']];
        $password = $line[$formatkeylookup['password']];
        $email    = $line[$formatkeylookup['email']];

        $authobj = AuthFactory::create($authinstance);

        if (method_exists($authobj, 'is_username_valid_admin')) {
            if (!$authobj->is_username_valid_admin($username)) {
                $CSVERRORS[] = get_string('uploadcsverrorinvalidusername', 'admin', $i);
            }
        }
        else if (method_exists($authobj, 'is_username_valid')) {
            if (!$authobj->is_username_valid($username)) {
                $CSVERRORS[] = get_string('uploadcsverrorinvalidusername', 'admin', $i);
            }
        }
        if (record_exists_select('usr', 'LOWER(username) = ?', strtolower($username)) || isset($usernames[strtolower($username)])) {
            $CSVERRORS[] = get_string('uploadcsverroruseralreadyexists', 'admin', $i, $username);
        }
        if (record_exists('usr', 'email', $email) || record_exists('artefact_internal_profile_email', 'email', $email) || isset($emails[$email])) {
            $CSVERRORS[] = get_string('uploadcsverroremailaddresstaken', 'admin', $i, $email);
        }

        // Note: only checks for valid form are done here, none of the checks
        // like whether the password is too easy. The user is going to have to
        // change their password on first login anyway.
        if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
            $CSVERRORS[] = get_string('uploadcsverrorinvalidpassword', 'admin', $i);
        }

        $usernames[strtolower($username)] = 1;
        $emails[$email] = 1;
    }

    if (!empty($CSVERRORS)) {
        $form->set_error('file', implode("<br />\n", $CSVERRORS));
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
    global $SESSION, $CSVDATA, $FORMAT;

    $formatkeylookup = array_flip($FORMAT);

    $authinstance = (int) $values['authinstance'];
    $authobj = get_record('auth_instance', 'id', $authinstance);

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

    log_info('Inserting users from the CSV file');
    db_begin();

    $addedusers = array();

    $cfgsendemail = get_config('sendemail');
    if (empty($values['emailusers'])) {
        // Temporarily disable email sent during user creation, e.g. institution membership
        $GLOBALS['CFG']->sendemail = false;
    }

    foreach ($CSVDATA as $record) {
        log_debug('adding user ' . $record[$formatkeylookup['username']]);
        $user = new StdClass;
        $user->authinstance = $authinstance;
        $user->username     = $record[$formatkeylookup['username']];
        $user->firstname    = $record[$formatkeylookup['firstname']];
        $user->lastname     = $record[$formatkeylookup['lastname']];
        $user->password     = $record[$formatkeylookup['password']];
        $user->email        = $record[$formatkeylookup['email']];

        if (isset($formatkeylookup['studentid'])) {
            $user->studentid = $record[$formatkeylookup['studentid']];
        }
        if (isset($formatkeylookup['preferredname'])) {
            $user->preferredname = $record[$formatkeylookup['preferredname']];
        }
        $user->passwordchange = (int)$values['forcepasswordchange'];

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

        $user->id = create_user($user, $profilefields, $institution, $authobj, $remoteuser);

        $addedusers[] = $user;
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

    foreach ($addedusers as $user) {
        // Add salt and encrypt the pw, if the auth instance allows for it
        $userobj = new User();
        $userobj = $userobj->find_by_id($user->id);
        $authobj_tmp = AuthFactory::create($user->authinstance);
        if (method_exists($authobj_tmp, 'change_password')) {
            $authobj_tmp->change_password($userobj, $user->password, false);
        } else {
            $userobj->password = '';
            $userobj->salt = auth_get_random_salt();
            $userobj->commit();
        }
    }
    unset($authobj_tmp, $userobj);

    log_info('Inserted ' . count($CSVDATA) . ' records');

    $SESSION->add_ok_msg(get_string('uploadcsvusersaddedsuccessfully', 'admin'));
    redirect('/admin/users/uploadcsv.php');
}

// Get a list of all profile fields, to inform the user on what fields they can
// put in their file.
$fields = "<ul class=fieldslist>\n";
$fieldlist = array_keys(ArtefactTypeProfile::get_all_fields());
$fieldlist[]= 'remoteuser'; // is a special case
foreach ($fieldlist as $type) {
    if ($type == 'firstname' || $type == 'lastname' || $type == 'email') {
        continue;
    }
    $fields .= '<li>' . hsc($type) . "</li>\n";
}
$fields .= "<div class=cl></div></ul>\n";

if ($USER->get('admin')) {
    $uploadcsvpagedescription = get_string('uploadcsvpagedescription2', 'admin',
        get_config('wwwroot') . 'admin/extensions/pluginconfig.php?plugintype=artefact&pluginname=internal&type=profile',
        get_config('wwwroot') . 'admin/users/institutions.php',
        $fields
    );
}
else {
    $uploadcsvpagedescription = get_string('uploadcsvpagedescription2institutionaladmin', 'admin',
        get_config('wwwroot') . 'admin/users/institutions.php',
        $fields
    );
}

$form = pieform($form);

$smarty = smarty();
$smarty->assign('uploadcsvpagedescription', $uploadcsvpagedescription);
$smarty->assign('uploadcsvform', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/uploadcsv.tpl');
