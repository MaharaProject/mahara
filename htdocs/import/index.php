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
 * @subpackage import
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/import');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('import', 'import'));

$importplugins = plugins_installed('import');

if (!$importplugins) {
    die_info(get_string('noimportpluginsenabled', 'import'));
}

$form = pieform(array(
    'name' => 'import',
    'elements' => array(
        'file' => array(
            'type' => 'file',
            'title' => 'LEAP2A file',
            'description' => 'Either a .zip file or just the LEAP2A XML file',
            'rules' => array(
                'required' => true,
            ),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => 'Import',
        ),
    ),
));

function import_validate(Pieform $form, $values) {
    if ($values['file']['type'] != 'application/zip'
        && $values['file']['type'] != 'text/xml') {
        $form->set_error('file', 'The file must be a .zip or LEAP2A XML file');
    }
}

function import_submit(Pieform $form, $values) {
    global $SESSION;

    $date = time();
    $nicedate = date('Y/m/d h:i:s', $date);

    $uploaddir = get_config('dataroot') . 'import/test-' . $date . '/';
    $filename = $uploaddir . $values['file']['name'];
    check_dir_exists($uploaddir);
    move_uploaded_file($values['file']['tmp_name'], $filename);

    if ($values['file']['type'] == 'application/zip') {
        // Unzip here
        $command = sprintf('%s %s %s %s',
            escapeshellcmd(get_config('pathtounzip')),
            escapeshellarg($filename),
            get_config('unzipdirarg'),
            escapeshellarg($uploaddir)
        );
        $output = array();
        exec($command, $output, $returnvar);
        if ($returnvar != 0) {
            $SESSION->add_error_msg('Unable to unzip the file');
            redirect('/import/');
        }

        $filename = $uploaddir . 'leap2a.xml';
        if (!is_file($filename)) {
            $SESSION->add_error_msg('No leap2a.xml file detected - please check your export file again');
            redirect('/import/');
        }
    }

    // Create dummy user
    $user = (object)array(
        'username' => 'import_' . $date,
        'password' => 'import1',
        'firstname' => 'Imported',
        'lastname' => 'User (' . $nicedate .')',
    );
    $userid = create_user($user);

    // And we're good to go
    echo '<pre>';
    $filename = substr($filename, strlen(get_config('dataroot')));
    require_once(dirname(dirname(__FILE__)) . '/import/lib.php');
    safe_require('import', 'leap');
    db_begin();
    $importer = PluginImport::create_importer(null, (object)array(
        'token'      => '',
        //'host'       => '',
        'usr'        => $userid,
        'queue'      => (int)!(PluginImport::import_immediately_allowed()), // import allowed straight away? Then don't queue
        'ready'      => 0, // maybe 1?
        'expirytime' => db_format_timestamp(time()+(60*60*24)),
        'format'     => 'leap',
        'data'       => array('filename' => $filename),
        'loglevel'   => PluginImportLeap::LOG_LEVEL_VERBOSE,
        'logtargets' => LOG_TARGET_STDOUT,
        'profile'    => true,
    ));
    $importer->process();

    db_commit();

    echo "\n\n";
    echo 'Done. You can <a href="' . get_config('wwwroot') . '/admin/users/changeuser.php?id=' . $userid . '">change to this user</a> to inspect the result, ';
    echo 'or <a href="' . get_config('wwwroot') . 'import/">try importing again</a>';
    echo '</pre>';
    exit;
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(get_string('import', 'import')));
$smarty->assign('form', $form);
$smarty->assign('previouslyimporteduser', param_integer('user', null));
$smarty->display('import/index.tpl');

?>
