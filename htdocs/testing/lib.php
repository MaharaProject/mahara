<?php
/**
 * @package    mahara
 * @subpackage test/core
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2012, Petr Skoda {@link http://skodak.org}
 *
 */
/**
 * Testing general functions
 *
 * Note: these functions must be self contained and must not rely on any library or include
 *
 */

/**
 * Returns relative path against current working directory,
 * to be used for shell execution hints.
 * @param string $maharapath starting with "/", ex: "/testing/frameworks/cli/init.php"
 * @return string path relative to current directory or absolute path
 */
function testing_cli_argument_path($maharapath) {
    global $CFG;

    if (isset($_SERVER['REMOTE_ADDR'])) {
        // Web access, this should not happen often.
        $cwd = dirname(dirname(__DIR__));
    } else {
        // This is the real CLI script, work with relative paths.
        $cwd = getcwd();
    }
    if (substr($cwd, -1) !== DIRECTORY_SEPARATOR) {
        $cwd .= DIRECTORY_SEPARATOR;
    }
    $path = realpath($CFG->docroot . $maharapath);

    if (strpos($path, $cwd) === 0) {
        $path = substr($path, strlen($cwd));
    }

    return $path;
}

/**
 * Try to change permissions to $CFG->docroot or $CFG->dataroot if possible
 * @param string $file
 * @return bool success
 */
function testing_fix_file_permissions($file) {
    global $CFG;

    $permissions = fileperms($file);
    if ($permissions & $CFG->filepermissions != $CFG->filepermissions) {
        $permissions = $permissions | $CFG->filepermissions;
        return chmod($file, $permissions);
    }

    return true;
}

/**
 * Mark empty dataroot to be used for testing.
 * @param string $dataroot  The dataroot directory
 * @param string $framework The test framework
 * @return void
 */
function testing_initdataroot($dataroot, $framework) {
    global $CFG;

    $filename = $dataroot . '/' . $framework . 'testdir.txt';

    umask(0);
    if (!file_exists($filename)) {
        file_put_contents($filename, 'Contents of this directory are used during tests only, do not delete this file!');
    }
    testing_fix_file_permissions($filename);

    $varname = $framework . '_dataroot';
    $datarootdir = $CFG->{$varname} . '/' . $framework;
    if (!file_exists($datarootdir)) {
        mkdir($datarootdir, $CFG->directorypermissions);
    }
}

/**
 * Prints an error and stops execution
 *
 * @param integer $errorcode
 * @param string $text
 * @return void exits
 */
function testing_error($errorcode, $text = '') {

    // do not write to error stream because we need the error message in PHP exec result from web ui
    echo($text."\n");
    exit($errorcode);
}

/**
 * Updates the composer installer and the dependencies.
 *
 * Includes --dev dependencies.
 *
 * @return void exit() if something goes wrong
 */
function testing_update_composer_dependencies() {

    // To restore the value after finishing.
    $cwd = getcwd();

    // Mahara Docroot.
    $maharadocroot = dirname(__DIR__);
    chdir($maharadocroot);

    // Download composer.phar if we can.
    if (!file_exists($maharadocroot . '/composer.phar')) {
        passthru("curl http://getcomposer.org/installer | php", $code);
        if ($code != 0) {
            exit($code);
        }
    }
    else {

        // If it is already there update the installer.
        passthru("php composer.phar self-update", $code);
        if ($code != 0) {
            exit($code);
        }
    }

    // Update composer dependencies.
    passthru("php composer.phar update --dev", $code);
    if ($code != 0) {
        exit($code);
    }

    chdir($cwd);
}
