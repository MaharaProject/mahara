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
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('institution.php');
safe_require('artefact', 'internal');
safe_require('artefact', 'file');
raise_memory_limit("512M");

define('TITLE', get_string('bulkleap2aimport', 'admin'));

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$authinstances = auth_get_auth_instances();

if (count($authinstances) > 0) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
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
    'name' => 'bulkimport',
    'elements' => array(
        'authinstance' => $authinstanceelement,
        'directory' => array(
            'type' => 'text',
            'title' => get_string('Directory', 'admin'),
            'description' => get_string('bulkleap2aimportdirdescription', 'admin'),
            'rules' => array(
                'required' => true
            )
        ),
        'emailusers' => array(
            'type' => 'checkbox',
            'title' => get_string('emailusersaboutnewaccount', 'admin'),
            'description' => get_string('emailusersaboutnewaccountdescription', 'admin'),
            'defaultvalue' => true,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('Import', 'admin')
        )
    )
);

/**
 * The CSV file is parsed here so validation errors can be returned to the
 * user. The data from a successful parsing is stored in the <var>$LEAP2AFILES</var>
 * array so it can be accessed by the submit function
 *
 * @param Pieform  $form   The form to validate
 * @param array    $values The values submitted
 */
function bulkimport_validate(Pieform $form, $values) {
    global $LEAP2AFILES;

    // Don't even start attempting to parse if there are previous errors
    if ($form->has_errors()) {
        return;
    }

    require_once('csvfile.php');

    $strdirectory = hsc($values['directory']);

    if (!is_dir($values['directory'])) {
        $form->set_error('directory', get_string('bulkimportdirdoesntexist', 'admin', $strdirectory));
        return;
    }
    if (!$dh = opendir($values['directory'])) {
        $form->set_error('directory', get_string('unabletoreadbulkimportdir', 'admin', $strdirectory));
        return;
    }
    closedir($dh);

    $csvfilename = $values['directory'] . '/import.csv';

    if (!is_readable($csvfilename)) {
        $form->set_error('directory', get_string('unabletoreadcsvfile', 'admin', hsc($csvfilename)));
        return;
    }

    $csvusers = new CsvFile($csvfilename);
    $csvusers->set('headerExists', false);
    $csvusers->set('format', array('username', 'filename'));
    $csvdata = $csvusers->get_data();

    if (!empty($csvdata->errors['file'])) {
        $form->set_error('directory', $csvdata->errors['file']);
        return;
    }

    $ziptypes = PluginArtefactFile::get_mimetypes_from_description('zip');
    $csverrors = array();
    $LEAP2AFILES = array();

    $authobj = AuthFactory::create((int) $values['authinstance']);

    foreach ($csvdata->data as $key => $line) {
        $i = $key + 1;

        $username = $line[0];
        $filename = $values['directory'] . '/' . $line[1];

        if (method_exists($authobj, 'is_username_valid') && !$authobj->is_username_valid($username)) {
            $csverrors[] = get_string('uploadcsverrorinvalidusername', 'admin', $i);
        }
        if (record_exists_select('usr', 'LOWER(username) = ?', strtolower($username)) || isset($LEAP2AFILES[strtolower($username)])) {
            $csverrors[] = get_string('uploadcsverroruseralreadyexists', 'admin', $i, hsc($username));
        }

        if (!is_readable($filename)) {
            $csverrors[] = get_string('importfilenotreadable', 'admin', $i, hsc($filename));
        }
        if (!in_array(mime_content_type($filename), $ziptypes)) {
            $csverrors[] = get_string('importfileisnotazipfile', 'admin', hsc($filename));
        }

        $LEAP2AFILES[strtolower($username)] = $filename;
    }

    if (empty($csverrors) && !empty($LEAP2AFILES)) {
        // Try the unzip command just to check it's installed
        $testfile = current($LEAP2AFILES);
        $command = sprintf('%s %s %s',
            escapeshellcmd(get_config('pathtounzip')),
            get_config('unziplistarg'),
            escapeshellarg($testfile)
        );
        $output = array();
        exec($command, $output, $returnvar);
        if ($returnvar != 0) {
            log_debug("unzip command failed with return value $returnvar");
            // Let's make it obvious if the cause is obvious :)
            if ($returnvar == 127) {
                log_debug("This means that 'unzip' isn't installed, or the config var \$cfg->pathtounzip is not"
                    . " pointing at unzip (see Mahara's file lib/config-defaults.php)");
            }
            $csverrors[] = get_string('unzipfailed', 'admin', hsc($testfile));
        }
    }

    if (!empty($csverrors)) {
        $form->set_error('directory', implode("<br />\n", $csverrors));
        $LEAP2AFILES = array();
    }

}

/**
 * Add the users to the system.
 */
function bulkimport_submit(Pieform $form, $values) {
    global $SESSION, $LEAP2AFILES;

    log_debug($values);

    require_once(get_config('docroot') . 'import/lib.php');
    safe_require('import', 'leap');

    $authinstance = (int) $values['authinstance'];
    $authobj = get_record('auth_instance', 'id', $authinstance);
    $institution = new Institution($authobj->institution);

    log_info('Attempting to import ' . count($LEAP2AFILES) . ' users from LEAP2A files');

    $addedusers = array();
    $failedusers = array();
    foreach ($LEAP2AFILES as $username => $filename) {

        log_debug('adding user ' . $username . ' from ' . $filename);
        set_time_limit(10);

        $date = time();
        $nicedate = date('Y/m/d h:i:s', $date);
        $niceuser = preg_replace('/[^a-zA-Z0-9_-]/', '-', $username);

        $uploaddir = get_config('dataroot') . 'import/' . $niceuser . '-' . $date . '/';

        check_dir_exists($uploaddir);

        // Unzip the file
        $command = sprintf('%s %s %s %s',
            escapeshellcmd(get_config('pathtounzip')),
            escapeshellarg($filename),
            get_config('unzipdirarg'),
            escapeshellarg($uploaddir)
        );
        $output = array();
        exec($command, $output, $returnvar);
        if ($returnvar != 0) {
            $failedusers[$username] = get_string('unzipfailed', 'admin', hsc($filename));
            log_debug("unzip command failed with return value $returnvar");
            continue;
        }

        $leap2afilename = $uploaddir . 'leap2a.xml';
        if (!is_file($leap2afilename)) {
            $failedusers[$username] = get_string('noleap2axmlfiledetected', 'admin');
            log_debug($failedusers[$username]);
            continue;
        }

        $user = (object)array(
            'authinstance'   => $authinstance,
            'username'       => $username,
            'firstname'      => 'Imported',
            'lastname'       => 'User',
            'password'       => get_random_key(6),
            'passwordchange' => 1,
        );

        db_begin();

        try {
            $user->id = create_user($user, array(), $institution, $authobj);
        }
        catch (EmailException $e) {
            // Suppress any emails (e.g. new institution membership) sent out
            // during user creation, becuase the user doesn't have an email
            // address until we've imported them from the LEAP2A file.
            log_debug("Failed sending email during user import");
        }

        $importerfilename = substr($leap2afilename, strlen(get_config('dataroot')));
        $logfile          = dirname($leap2afilename) . '/import.log';

        $importer = PluginImport::create_importer(null, (object)array(
            'token'      => '',
            'usr'        => $user->id,
            'queue'      => (int)!(PluginImport::import_immediately_allowed()), // import allowed straight away? Then don't queue
            'ready'      => 0, // maybe 1?
            'expirytime' => db_format_timestamp(time()+(60*60*24)),
            'format'     => 'leap',
            'data'       => array('filename' => $importerfilename),
            'loglevel'   => PluginImportLeap::LOG_LEVEL_VERBOSE,
            'logtargets' => LOG_TARGET_FILE,
            'logfile'    => $logfile,
            'profile'    => true,
        ));

        try {
            $importer->process();
            log_info("Imported user account $user->id from leap2a file, see $logfile for a full log");
        }
        catch (ImportException $e) {
            log_info("LEAP2A import failed: " . $e->getMessage());
            $failedusers[$username] = get_string("leap2aimportfailed");
            db_rollback();
            continue;
        }

        db_commit();

        // Reload the user details, as various fields are changed by the
        // importer when importing (e.g. firstname/lastname)
        $addedusers[] = get_record('usr', 'id', $user->id);
    }

    log_info('Imported ' . count($addedusers) . '/' . count($LEAP2AFILES) . ' users successfully');

    if (!empty($addedusers)) {
        $SESSION->add_ok_msg(get_string('importednuserssuccessfully', 'admin', count($addedusers), count($LEAP2AFILES)));
    }

    // Only send e-mail to users after we're sure they have been inserted
    // successfully
    if ($values['emailusers'] && $addedusers) {
        foreach ($addedusers as $user) {
            $noemailusers = array();
            try {
                email_user($user, null, get_string('accountcreated', 'mahara', get_config('sitename')),
                    get_string('accountcreatedchangepasswordtext', 'mahara', $user->firstname, get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('sitename')),
                    get_string('accountcreatedchangepasswordhtml', 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $user->password, get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
                );
            }
            catch (EmailException $e) {
                log_info($e->getMessage());
                $noemailusers[] = $user;
            }
        }

        if ($noemailusers) {
            $message = get_string('uploadcsvsomeuserscouldnotbeemailed', 'admin') . "\n<ul>\n";
            foreach ($noemailusers as $user) {
                $message .= '<li>' . full_name($user) . ' &lt;' . hsc($user->email) . "&gt;</li>\n";
            }
            $message .= "</ul>\n";
            $SESSION->add_info_msg($message, false);
        }
    }

    if (!empty($failedusers)) {
        $message = get_string('importfailedfornusers', 'admin', count($failedusers), count($LEAP2AFILES)) . "\n<ul>\n";
        foreach ($failedusers as $username => $error) {
            $message .= '<li>' . hsc($username) . ': ' . hsc($error) . "</li>\n";
        }
        $message .= "</ul>\n";
        $SESSION->add_err_msg($message, false);
    }

    redirect('/admin/users/bulkimport.php');
}

$form = pieform($form);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('admin/users/bulkimport.tpl');

?>
