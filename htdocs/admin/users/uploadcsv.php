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
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$FORMAT = array();
$ALLOWEDKEYS = array(
    'username',
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

if ($USER->get('admin')) {
    $authinstances = auth_get_auth_instances();
} else {
    $admininstitutions = $USER->get('admininstitutions');
    $authinstances = auth_get_auth_instances_for_institutions($admininstitutions);
    if (empty($authinstances)) {
        $SESSION->add_info_msg(get_string('uploadcsvconfigureauthplugin', 'admin'));
        redirect(get_config('wwwroot').'admin/users/institutions.php?i='.key($admininstitutions).'&amp;edit=1');
    }
}

if (count($authinstances) > 1) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        if ($USER->can_edit_institution($authinstance->name)) {
            $options[$authinstance->id .'_'. $authinstance->name] = $authinstance->displayname. ': '.$authinstance->instancename;
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
else {
    foreach ($authinstances as $authinstance) {
        $string = $authinstance->id .'_'. $authinstance->name;
    }

    $authinstanceelement = array(
        'type' => 'hidden',
        'value' => $string
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
    global $CSVDATA, $ALLOWEDKEYS, $FORMAT, $USER;

    // Don't even start attempting to parse if there are previous errors
    if ($form->has_errors()) {
        return;
    }

    if ($values['file']['size'] == 0) {
        $form->set_error('file', $form->i18n('rule', 'required', 'required', array()));
        return;
    }

    require_once('pear/File.php');
    require_once('pear/File/CSV.php');

    // Don't be tempted to use 'explode' here. There may be > 1 underscore.
    $break = strpos($values['authinstance'], '_');
    $authinstance = substr($values['authinstance'], 0, $break);
    $institution  = substr($values['authinstance'], $break+1);
    if (!$USER->can_edit_institution($institution)) {
        $form->set_error('authinstance', get_string('notadminforinstitution', 'admin'));
        return;
    }

    $conf = File_CSV::discoverFormat($values['file']['tmp_name']);
    $i = 0;
    while ($line = File_CSV::readQuoted($values['file']['tmp_name'], $conf)) {
        $i++;
        if (!is_array($line)) {
            // Note: the CSV parser returns true on some errors and false on
            // others! Yes that's retarded. No I didn't write it :(
            $form->set_error('file', get_string('uploadcsverrorincorrectnumberoffields', 'admin', $i));
            return;
        }

        // Get the format of the file
        if ($i == 1) {
            foreach ($line as &$potentialkey) {
                $potentialkey = trim($potentialkey);
                if (!in_array($potentialkey, $ALLOWEDKEYS)) {
                    $form->set_error('file', get_string('uploadcsverrorinvalidfieldname', 'admin', $potentialkey));
                    return;
                }
            }

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
            
            // Add in the locked profile fields for this institution
            foreach ($mandatoryfields as $field) {
                if (!in_array($field, $line)) {
                    $form->set_error('file', get_string('uploadcsverrorrequiredfieldnotspecified', 'admin', $field));
                    return;
                }
            }

            // The format line is valid
            $FORMAT = $line;
            log_info('FORMAT:');
            log_info($FORMAT);
        }
        else {
            // We have a line with the correct number of fields, but should validate these fields
            // Note: This validation should really be methods on each profile class, that way
            // it can be used in the profile screen as well.

            $formatkeylookup = array_flip($FORMAT);
            $username = $line[$formatkeylookup['username']];
            $password = $line[$formatkeylookup['password']];
            $email    = $line[$formatkeylookup['email']];

            $authobj = AuthFactory::create($authinstance);

            if (method_exists($authobj, 'is_username_valid') && !$authobj->is_username_valid($username)) {
                $form->set_error('file', get_string('uploadcsverrorinvalidusername', 'admin', $i));
                return;
            }
            if (record_exists('usr', 'username', $username)) {
                $form->set_error('file', get_string('uploadcsverroruseralreadyexists', 'admin', $i, $username));
                return;
            }
            if (record_exists('usr', 'email', $email)) {
                $form->set_error('file', get_string('uploadcsverroremailaddresstaken', 'admin', $i, $email));
            }

            // Note: only checks for valid form are done here, none of the checks
            // like whether the password is too easy. The user is going to have to
            // change their password on first login anyway.
            if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($password)) {
                $form->set_error('file', get_string('uploadcsverrorinvalidpassword', 'admin', $i));
                return;
            }

            // All OK!
            $CSVDATA[] = $line;
        }

    }

    if ($i == 1) {
        // There was only the title row :(
        $form->set_error('file', get_string('uploadcsverrornorecords', 'admin'));
        return;
    }

    if ($CSVDATA === null) {
        // Oops! Couldn't get CSV data for some reason
        $form->set_error('file', get_string('uploadcsverrorunspecifiedproblem', 'admin'));
    }
}

/**
 * Add the users to the system. Make sure that they have to change their
 * password on next login also.
 */
function uploadcsv_submit(Pieform $form, $values) {
    global $SESSION, $CSVDATA, $FORMAT;

    $formatkeylookup = array_flip($FORMAT);

    // Don't be tempted to use 'explode' here. There may be > 1 underscore.
    $break = strpos($values['authinstance'], '_');
    $authinstance = substr($values['authinstance'], 0, $break);
    $institution = substr($values['authinstance'], $break+1);
    $institution = new Institution($institution);

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

    foreach ($CSVDATA as $record) {
        log_debug('adding user ' . $record[$formatkeylookup['username']]);
        $user = new StdClass;
        $user->authinstance = $authinstance;
        $user->username     = $record[$formatkeylookup['username']];
        $user->password     = $record[$formatkeylookup['password']];
        $user->email        = $record[$formatkeylookup['email']];

        if (isset($formatkeylookup['studentid'])) {
            $user->studentid = $record[$formatkeylookup['studentid']];
        }
        if (isset($formatkeylookup['preferredname'])) {
            $user->preferredname = $record[$formatkeylookup['preferredname']];
        }
        $user->passwordchange = 1;
        $id = insert_record('usr', $user, 'id', true);
        $user->id = $id;
        if ($institution->name != 'mahara') {
            $institution->addUserAsMember($user);
        }
        if (get_field('auth_instance', 'authname', 'id', $authinstance) != 'internal') {
            // Assume the admin knows what they're doing when they choose the external auth instance.
            delete_records('auth_remote_user', 'authinstance', $authinstance, 'remoteusername', $user->username);
            insert_record('auth_remote_user', (object) array(
                'authinstance'   => $authinstance,
                'remoteusername' => $user->username,
                'localusr'       => $id,
            ));
        }

        foreach ($FORMAT as $field) {
            if ($field == 'username' || $field == 'password') {
                continue;
            }
            set_profile_field($id, $field, $record[$formatkeylookup[$field]]);
        }

        handle_event('createuser', $user);
    }
    db_commit();
    log_info('Inserted ' . count($CSVDATA) . ' records');

    $SESSION->add_ok_msg(get_string('uploadcsvusersaddedsuccessfully', 'admin'));
    redirect('/admin/users/uploadcsv.php');
}

// Get a list of all profile fields, to inform the user on what fields they can
// put in their file.
$fields = "<ul>\n";
foreach (array_keys(ArtefactTypeProfile::get_all_fields()) as $type) {
    if ($type == 'firstname' || $type == 'lastname' || $type == 'email') {
        continue;
    }
    $fields .= '<li>' . hsc($type) . "</li>\n";
}
$fields .= "</ul>\n";

$smarty = smarty();
$smarty->assign('uploadcsvpagedescription', get_string('uploadcsvpagedescription', 'admin',
    get_config('wwwroot') . 'admin/extensions/pluginconfig.php?plugintype=artefact&pluginname=internal&type=profile',
    get_config('wwwroot') . 'admin/users/institutions.php',
    $fields
));
$smarty->assign('uploadcsvform', pieform($form));
$smarty->display('admin/users/uploadcsv.tpl');

?>
