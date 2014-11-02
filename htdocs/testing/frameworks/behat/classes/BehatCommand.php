<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

/**
 * Behat command utils
 */

defined('INTERNAL') || die();

require_once(dirname(__DIR__) . '/lib.php');

class BehatCommand {

    /**
     * Docs url
     */
    const DOCS_URL = 'http://wiki.mahara.org/Developer_area/Acceptance_testing';

    /**
     * Ensures the behat dir exists in maharadata
     * @return string Full path
     */
    public static function get_behat_dir() {
        global $CFG;

        $behatdir = $CFG->behat_dataroot . '/behat';

        if (!is_dir($behatdir)) {
            if (!mkdir($behatdir, $CFG->directorypermissions, true)) {
                behat_error(BEHAT_EXITCODE_PERMISSIONS, 'Directory ' . $behatdir . ' can not be created');
            }
        }

        if (!is_writable($behatdir)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, 'Directory ' . $behatdir . ' is not writable');
        }

        return $behatdir;
    }

    /**
     * Returns the executable path
     *
     * Note: Mahara does not support running behat on Windows
     * @param  bool $custombyterm  If the provided command should depend on the terminal where it runs
     * @return string
     */
    public final static function get_behat_command($custombyterm = false) {

        $separator = DIRECTORY_SEPARATOR;
        $exec = 'behat';

        return $separator . 'vendor' . $separator . 'bin' . $separator . $exec;
    }

    /**
     * Runs behat command with provided options
     *
     * Execution continues when the process finishes
     *
     * @param  string $options  Defaults to '' so tests would be executed
     * @return array            CLI command outputs [0] => string, [1] => integer
     */
    public final static function run($options = '') {
        global $CFG;

        $currentcwd = getcwd();
        // Change to composer installed directory
        chdir($CFG->docroot);
        exec(get_composerroot_dir() . self::get_behat_command() . ' ' . $options . ' 2>/dev/null', $output, $code);
        chdir($currentcwd);

        return array($output, $code);
    }

    /**
     * Checks if behat is set up and working
     *
     * Notifies failures both from CLI and web interface.
     *
     * It checks behat dependencies have been installed and runs
     * the behat help command to ensure it works as expected
     *
     * @return int Error code or 0 if all ok
     */
    public static function behat_setup_problem() {
        global $CFG;

        // Mahara setting.
        if (!self::are_behat_dependencies_installed()) {

            // Returning composer error code to avoid conflicts with behat and mahara error codes.
            self::output_msg(get_string('errorcomposer', 'behat'));
            return BEHAT_EXITCODE_COMPOSER;
        }

        // Behat test command.
        list($output, $code) = self::run(' --help');

        if ($code != 0) {

            // Returning composer error code to avoid conflicts with behat and mahara error codes.
            self::output_msg(get_string('errorbehatcommand', 'behat', self::get_behat_command()));
            return BEHAT_EXITCODE_COMPOSER;
        }

        // No empty values.
        if (empty($CFG->behat_dataroot) || empty($CFG->behat_dbprefix) || empty($CFG->behat_wwwroot)) {
            self::output_msg(get_string('errorsetconfig', 'behat'));
            return BEHAT_EXITCODE_CONFIG;

        }

        // Not repeated values.
        // We only need to check this when the behat site is not running as
        // at this point, when it is running, all $CFG->behat_* vars have
        // already been copied to $CFG->dataroot, $CFG->dbprefix and $CFG->wwwroot.
        if (!defined('BEHAT_SITE_RUNNING') &&
                ($CFG->behat_dbprefix == $CFG->dbprefix ||
                $CFG->behat_dataroot == $CFG->dataroot ||
                $CFG->behat_wwwroot == $CFG->wwwroot ||
                (!empty($CFG->phpunit_dbprefix) && $CFG->phpunit_dbprefix == $CFG->behat_dbprefix) ||
                (!empty($CFG->phpunit_dataroot) && $CFG->phpunit_dataroot == $CFG->behat_dataroot)
                )) {
            self::output_msg(get_string('erroruniqueconfig', 'behat'));
            return BEHAT_EXITCODE_CONFIG;
        }

        // Checking behat dataroot existence otherwise echo about admin/tool/behat/cli/init.php.
        if (!empty($CFG->behat_dataroot)) {
            $CFG->behat_dataroot = realpath($CFG->behat_dataroot);
        }
        if (empty($CFG->behat_dataroot) || !is_dir($CFG->behat_dataroot) || !is_writable($CFG->behat_dataroot)) {
            self::output_msg(get_string('errordataroot', 'behat'));
            return BEHAT_EXITCODE_CONFIG;
        }

        return 0;
    }

    /**
     * Has the site installed composer with --dev option
     * @return bool
     */
    public static function are_behat_dependencies_installed() {
        if (!is_dir(get_composerroot_dir() . '/vendor/behat')) {
            return false;
        }
        return true;
    }

    /**
     * Outputs a message.
     *
     * Used in CLI + web UI methods. Stops the
     * execution in web.
     *
     * @param string $msg
     * @return void
     */
    protected static function output_msg($msg) {
        global $CFG;

        // If we are using the web interface we want pretty messages.
        if (!CLI) {

            echo($msg);

            // Stopping execution.
            exit(1);

        }
        else {

            // We continue execution after this.
            $clibehaterrorstr = "Ensure you set \$CFG->behat_* vars in config.php " .
                "and you ran testing/frameworks/behat/cli/init.php.\n" .
                "More info in " . self::DOCS_URL . "#Installation\n\n";

            echo 'Error: ' . $msg . "\n\n" . $clibehaterrorstr;
        }
    }

}
