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
 * Return mahara root directory
 * @return string Full path
 */
function get_mahararoot_dir() {
    return dirname(dirname(__DIR__));
}

/**
 * Return composer root directory
 * @return string Full path
 */
function get_composerroot_dir() {
    return get_mahararoot_dir() . '/external';
}

/**
 * Return html report directory
 * @return string Full path
 */
function get_html_report_dir() {
    return get_composerroot_dir() . '/vendor/emuse/behat-html-formatter';
}

/**
 * Return mahara report styling directory
 * @return string Full path
 */
function get_html_styling_dir() {
    return get_mahararoot_dir() . '/htdocs/testing/frameworks/behat/html-formatter';
}

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
    }
    else {
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
    echo($text . "\n");
    exit($errorcode);
}

/**
 * Download or update composer.phar and
 * install dependencies for testing framework
 *
 * @return void exit() if something goes wrong
 */
function testing_install_dependencies() {

    // Directory to install PHP composer
    $composerroot = get_composerroot_dir();

    if (file_exists('/.dockerenv')) {
        echo "WARN: Need to update composer before running behat in docker via: make initcomposer\n";
    }
    else {
        echo "Installing composer and dependencies...\n";
        // Download composer.phar if we can.
        if (!file_exists($composerroot . '/composer.phar')) {
            passthru("curl -sS https://getcomposer.org/installer\
                    | php -- --install-dir=$composerroot", $errorcode);
        }
        else {
            // If it is already there update the installer.
            passthru("php {$composerroot}/composer.phar\
                --working-dir={$composerroot} self-update", $errorcode);
        }

        // Install dependencies.
        if (file_exists($composerroot . '/composer.json')
            && file_exists($composerroot . '/composer.phar')
            && !file_exists($composerroot . '/composer.lock')) {
            passthru("php {$composerroot}/composer.phar install\
                --working-dir={$composerroot}\
                --no-interaction --quiet --no-dev", $errorcode);
        }

        if (!empty($errorcode)) {
            echo "Can not install PHP composer and dependencies\n";
            exit($errorcode);
        }
    }
}

/**
 * Updates dependencies when the composer.json is updated.
 *
 * @return void exit() if something goes wrong
 */
function testing_update_dependencies() {

    $composerroot = get_composerroot_dir();
    // Update dependencies.
    if (file_exists($composerroot . '/composer.json')
        && file_exists($composerroot . '/composer.phar')
        && file_exists($composerroot . '/composer.lock')) {

        if (file_exists('/.dockerenv')) {
            echo "WARN: Need to update composer before running behat in docker via: make initcomposer\n";
        }
        else {
             echo "Verifying that composer dependencies are up to date...\n";
             passthru("php {$composerroot}/composer.phar update\
                --working-dir={$composerroot}\
                --no-interaction --quiet --no-dev", $errorcode);
            if ($errorcode !== 0) {
                echo "Can not update composer dependencies\n";
                exit($errorcode);
            }
        }
    }
}

/**
* Copies mahara styling to external html folder
*/
function set_report_styling() {
    $report_root = get_html_report_dir();
    $report_styling = get_html_styling_dir();
    $report_css = $report_root . "/assets/Twig/css";
    $report_js = $report_root . "/assets/Twig/js";
    $font_awesome_dir = get_mahararoot_dir() . "/htdocs/theme/raw/fonts/font-awesome";
    $font_files = array_values(array_diff(scandir($font_awesome_dir), array('.', '..')));
    $font_awesome_css = get_mahararoot_dir() . "/htdocs/theme/raw/style/lib/font-awesome/font-awesome.css";
    $jquery_js = get_mahararoot_dir() . "/htdocs/js/jquery/jquery.js";
    $bootstrap_js = get_mahararoot_dir() . "/htdocs/lib/bootstrap/assets/javascripts/bootstrap.min.js";

    if (file_exists($report_root . '/composer.json')) {
        echo "Adding styling for html report...\n";
        copy($report_styling . "/bootstrap.min.css" , $report_css . "/bootstrap.min.css");
        copy($report_styling . "/catalyst.css" , $report_css . "/catalyst.css");
        copy($report_styling . "/style.css" , $report_css . "/style.css");
        copy($report_styling . "/index.html.twig" , $report_root . "/templates/index.html.twig");
        copy($report_styling . "/Behat2Renderer.php" , $report_root . "/src/Renderer/Behat2Renderer.php");
        if (!file_exists($report_css . "/font-awesome")) {
          mkdir($report_css . "/font-awesome");
        }
        foreach ($font_files as $file) {
          copy($font_awesome_dir . "/$file" , $report_css . "/font-awesome/$file");
        }
        copy($font_awesome_css , $report_css . "/font-awesome/font-awesome.css");

        // JavaScript
        copy($jquery_js, $report_js . "/jquery.js");
        copy($bootstrap_js, $report_js . "/bootstrap.min.js");
    }
}
