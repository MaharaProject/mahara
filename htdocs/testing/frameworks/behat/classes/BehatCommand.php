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
    const DOCS_URL = 'https://wiki.mahara.org/wiki/Testing/Behat_Testing';

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

        $currentcwd = getcwd();
        // Change to composer installed directory
        chdir(get_mahararoot_dir());
        exec(get_composerroot_dir() . self::get_behat_command() . ' ' . $options, $output, $code);
        chdir($currentcwd);

        return array($output, $code);
    }

    /**
     * Checks if
     * - behat and its composer dependencies are installed
     * - behat and its composer dependencies are up to date with composer.json
     * - behat is working
     *
     * @return int Error code or 0 if all ok
     */
    public static function get_behat_setup_status() {

        if (!self::is_behat_installed()) {
            return BEHAT_EXITCODE_NOTINSTALLED;
        }

        if (!self::is_behat_updated()) {
            return BEHAT_EXITCODE_NOTUPDATED;
        }

        // Run behat command.
        list($output, $code) = self::run(' --help');
        if ($code != 0) {
            return BEHAT_EXITCODE_CANNOTRUN;
        }

        return 0;
    }

    /**
     * Returns TRUE if behat and its components are installed
     * @return bool
     */
    public static function is_behat_installed() {
        if (!is_dir(get_composerroot_dir().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'behat')) {
            return false;
        }
        return true;
    }

    /**
     * Returns TRUE if the composer lock file is up to date
     * @return bool
     */
    public static function is_behat_updated() {
        $composerroot = get_composerroot_dir();
        if (file_exists($composerroot.DIRECTORY_SEPARATOR.'composer.lock')
            && file_exists($composerroot.DIRECTORY_SEPARATOR.'composer.json')) {
            $lock = json_decode(file_get_contents($composerroot.DIRECTORY_SEPARATOR.'composer.lock'))->hash;
            $json = md5(file_get_contents($composerroot.DIRECTORY_SEPARATOR.'composer.json'));

            if ($lock === $json) {
                return true;
            }
        }
        return false;
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
