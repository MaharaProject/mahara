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
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('institution.php');
require_once(get_config('docroot') . '/lib/htmloutput.php');
safe_require('artefact', 'internal');
safe_require('artefact', 'file');

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
        'file' => array(
            'type' => 'text',
            'title' => get_string('importfile', 'admin'),
            'size' => 40,
            'description' => get_string('bulkleap2aimportfiledescription', 'admin'),
            'rules' => array(
                'required' => true
            )
        ),
        'emailusers' => array(
            'type' => 'switchbox',
            'title' => get_string('emailusersaboutnewaccount', 'admin'),
            'description' => get_string('emailusersaboutnewaccountdescription', 'admin'),
            'defaultvalue' => true,
        ),
        'progress_meter_token' => array(
            'type' => 'hidden',
            'value' => 'bulkimport',
            'readonly' => TRUE,
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
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
    global $LEAP2AFILES, $USER;

    // Don't even start attempting to parse if there are previous errors
    if ($form->has_errors()) {
        return;
    }

    require_once('csvfile.php');
    $zipfile = $values['file'];
    if (!is_file($zipfile)) {
        $form->set_error('file', get_string('importfilenotafile', 'admin'));
        return;
    }
    if (!is_readable($zipfile)) {
        $form->set_error('file', get_string('importfilenotreadable', 'admin'));
        return;
    }

    // Create temporary directory
    $importdir = get_config('dataroot') . 'import/'
        . $USER->get('id')  . '/' . time() .  '/';
    if (!check_dir_exists($importdir)) {
        throw new SystemException("Couldn't create the temporary export directory $importdir");
    }

    $archive = new ZipArchive();
    if ($archive->open($zipfile) && $archive->extractTo($importdir)) {
        // successfully extracted
        $archive->close();
        log_debug("Unzipped $zipfile into $importdir");
    }
    else {
        throw new SystemException(get_string('unzipfailed', 'admin', hsc($zipfile)));
    }

    $csvfilename = $importdir . '/usernames.csv';
    if (!is_readable($csvfilename)) {
        $form->set_error('file', get_string('importfilemissinglisting', 'admin'));
        return;
    }

    $csvusers = new CsvFile($csvfilename);
    $csvusers->set('headerExists', false);
    $csvusers->set('format', array('username', 'filename'));
    $csvdata = $csvusers->get_data();

    if (!empty($csvdata->errors['file'])) {
        $form->set_error('file', get_string('invalidlistingfile', 'admin'));
        return;
    }

    foreach ($csvdata->data as $user) {
        $username = $user[0];
        $filename = $user[1];
        $LEAP2AFILES[$username] = "$importdir/users/$filename";
    }
}

/**
 * Add the users to the system.
 */
function bulkimport_submit(Pieform $form, $values) {
    global $SESSION, $LEAP2AFILES;

    require_once('file.php');
    require_once(get_config('docroot') . 'import/lib.php');
    safe_require('import', 'leap');

    $key = 0;
    $total = count($LEAP2AFILES);

    log_info('Attempting to import ' . $total . ' users from Leap2A files');

    foreach ($LEAP2AFILES as $username => $filename) {
        if (!($key % 10)) {
            set_progress_info('bulkimport', $key, $total, get_string('importing', 'admin'));
        }

        $key++;

        import_next_user($filename, $username, $values['authinstance']);
    }

    finish_import();
}

function import_next_user($filename, $username, $authinstance) {
    global $ADDEDUSERS, $FAILEDUSERS;

    log_debug('adding user ' . $username . ' from ' . $filename);

    $authobj = get_record('auth_instance', 'id', $authinstance);
    $institution = new Institution($authobj->institution);

    $date = time();
    $nicedate = date('Y/m/d h:i:s', $date);
    $niceuser = preg_replace('/[^a-zA-Z0-9_-]/', '-', $username);

    $uploaddir = get_config('dataroot') . 'import/' . $niceuser . '-' . $date . '/';

    check_dir_exists($uploaddir);

    // Unzip the file
    $archive = new ZipArchive();
    if ($archive->open($filename) && $archive->extractTo($uploaddir)) {
        // successfully extracted
        $archive->close();
    }
    else {
        $FAILEDUSERS[$username] = get_string('unzipfailed', 'admin', hsc($filename));
        return;
    }

    $leap2afilename = $uploaddir . 'leap2a.xml';
    if (!is_file($leap2afilename)) {
        $FAILEDUSERS[$username] = get_string('noleap2axmlfiledetected', 'admin');
        log_debug($FAILEDUSERS[$username]);
        return;
    }

    // If the username is already taken, append something to the end
    while (get_record('usr', 'username', $username)) {
        $username .= "_";
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
        // address until we've imported them from the Leap2A file.
        log_debug("Failed sending email during user import");
    }

    $niceuser = preg_replace('/[^a-zA-Z0-9_-]/', '-', $user->username);
    $record = (object)array(
        'token'      => '',
        'usr'        => $user->id,
        'queue'      => (int)!(PluginImport::import_immediately_allowed()), // import allowed straight away? Then don't queue
        'ready'      => 0, // maybe 1?
        'expirytime' => db_format_timestamp(time()+(60*60*24)),
        'format'     => 'leap',
        'data'       => array('importfile' => $filename, 'importfilename' => $filename, 'importid' => $niceuser.time(), 'mimetype' => file_mime_type($filename)),
        'loglevel'   => PluginImportLeap::LOG_LEVEL_VERBOSE,
        'logtargets' => LOG_TARGET_FILE,
        'profile'    => true,
    );
    $tr = new LocalImporterTransport($record);
    $tr->extract_file();

    $importer = PluginImport::create_importer(null, $tr, $record);
    unset($record, $tr);
    try {
        $importer->process();
        log_info("Imported user account $user->id from Leap2A file, see" . $importer->get('logfile') . 'for a full log');
    }
    catch (ImportException $e) {
        log_info("Leap2A import failed: " . $e->getMessage());
        $FAILEDUSERS[$username] = get_string("leap2aimportfailed");
        db_rollback();
    }

    db_commit();

    if (empty($FAILEDUSERS[$username])) {
        // Reload the user details, as various fields are changed by the
        // importer when importing (e.g. firstname/lastname)
        $newuser = get_record('usr', 'id', $user->id);
        $newuser->clearpasswd = $user->password;
        $ADDEDUSERS[] = $newuser;
    }

    return;
}

function finish_import() {
    global $SESSION, $ADDEDUSERS, $FAILEDUSERS, $EMAILUSERS;

    $totalusers = count($ADDEDUSERS) + count($FAILEDUSERS);

    log_info('Imported ' . count($ADDEDUSERS) . '/' . $totalusers . ' users successfully');

    if (!empty($ADDEDUSERS)) {
        $SESSION->add_ok_msg(get_string('importednuserssuccessfully', 'admin', count($ADDEDUSERS), $totalusers));
    }

    // Only send e-mail to users after we're sure they have been inserted
    // successfully
    if ($EMAILUSERS && $ADDEDUSERS) {
        foreach ($ADDEDUSERS as $user) {
            $noemailusers = array();
            try {
                email_user($user, null, get_string('accountcreated', 'mahara', get_config('sitename')),
                    get_string('accountcreatedchangepasswordtext', 'mahara', $user->firstname, get_config('sitename'), $user->username, $user->clearpasswd, get_config('wwwroot'), get_config('sitename')),
                    get_string('accountcreatedchangepasswordhtml', 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $user->clearpasswd, get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
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

    if (!empty($FAILEDUSERS)) {
        $message = get_string('importfailedfornusers', 'admin', count($FAILEDUSERS), $totalusers) . "\n<ul>\n";
        foreach ($FAILEDUSERS as $username => $error) {
            $message .= '<li>' . hsc($username) . ': ' . hsc($error) . "</li>\n";
        }
        $message .= "</ul>\n";
        $SESSION->add_error_msg($message, false);
    }

    redirect(get_config('wwwroot') . '/admin/users/bulkimport.php');
}

$form = pieform($form);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->display('admin/users/bulkimport.tpl');
