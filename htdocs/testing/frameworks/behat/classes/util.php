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
 * Utils for Behat testing
 * Classes:
 *     BehatTestingUtil
 *     MaharaBehatTestException
 *     MaharaBehatTestBootstrapException
 */

defined('INTERNAL') || die();

require_once(dirname(__DIR__) . '/lib.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/util.php');
require_once(__DIR__ . '/BehatCommand.php');
require_once(__DIR__ . '/BehatConfigManager.php');

/**
 * Init/reset the mahara database and dataroot for Behat testing
 */
class BehatTestingUtil extends TestingUtil {

    /**
     * The behat test site fullname and shortname.
     */
    const BEHATSITENAME = "Mahara - Acceptance test site";

    /**
     * @var array Default info of the new testing mahara site
     */
    protected static $sitedefaultinfo = array(
        'admin' => array(
            'password' => 'Kupuhipa1',
            'email'    => 'admin@test.mahara.org',
        ),
        'sitename' => self::BEHATSITENAME,
    );

    /**
     * @var array Files to skip when resetting dataroot folder
     */
    protected static $datarootskiponreset = array('.', '..', 'behat', 'behattestdir.txt');

    /**
     * @var array Files to skip when dropping dataroot folder
     */
    protected static $datarootskipondrop = array('.', '..', 'lock');

/**
 * Checks that the behat config vars are properly set.
 *
 * @return errorCode. (see testing/frameworks/behat/lib.php)
 */
function check_test_site_config() {
    global $CFG;

    // Verify prefix value.
    if (empty($CFG->behat_wwwroot)
        || empty($CFG->behat_dataroot)
        || empty($CFG->behat_dbprefix)
    ) {
        return BEHAT_MAHARA_EXITCODE_BADCONFIG_MISSING;
    }
    if ($CFG->behat_wwwroot == $CFG->wwwroot_orig
        || (isset($CFG->phpunit_wwwroot) && $CFG->behat_wwwroot == $CFG->phpunit_wwwroot)
        ) {
        return BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEWWWROOT;
    }
    if ($CFG->behat_dataroot == $CFG->dataroot_orig
        || (isset($CFG->phpunit_dataroot) && $CFG->behat_dataroot == $CFG->phpunit_dataroot)
        ) {
        return BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDATAROOT;
    }
    if ($CFG->behat_dbprefix == $CFG->dbprefix_orig
        || (isset($CFG->phpunit_dbprefix) && $CFG->behat_dbprefix == $CFG->phpunit_dbprefix)
        ) {
        return BEHAT_MAHARA_EXITCODE_BADCONFIG_DUPLICATEDBPREFIX;
    }
    $CFG->behat_dataroot = realpath($CFG->behat_dataroot);
    if (!file_exists($CFG->behat_dataroot)) {
        $permissions = isset($CFG->directorypermissions) ? $CFG->directorypermissions : 02777;
        umask(0);
        if (!mkdir($CFG->behat_dataroot, $permissions, true)) {
            return BEHAT_MAHARA_EXITCODE_BADPERMISSIONS;
        }
    }
    if (!is_dir($CFG->behat_dataroot) or !is_writable($CFG->behat_dataroot)) {
        return BEHAT_MAHARA_EXITCODE_NOTWRITABLEDATAROOT;
    }
}

/**
     * Installs a site using $CFG->dataroot and $CFG->dbprefix
     * As we are setting up the behat test environment, these settings
     * are replaced by $CFG->behat_dataroot and $CFG->behat_dbprefix
     *
     * @throws MaharaBehatTestException
     * @return void
     */
    public static function install_site() {
        if (!defined('BEHAT_UTIL')) {
            throw new MaharaBehatTestException('This method can be only used by Behat CLI tool');
        }

        if (table_exists(new XMLDBTable('config'))) {
            return;
        }

        // New dataroot.
        self::reset_dataroot();

        // Determine what we will install
        $upgrades = check_upgrades();
        $upgrades['firstcoredata'] = true;
        $upgrades['localpreinst'] = true;
        $upgrades['lastcoredata'] = true;
        $upgrades['localpostinst'] = true;
        upgrade_mahara($upgrades);

        $userobj = new User();
        $userobj = $userobj->find_by_username('admin');
        $userobj->email = self::$sitedefaultinfo['admin']['email'];
        $userobj->commit();

        // Password changes should be performed by the authfactory
        $authobj = AuthFactory::create($userobj->authinstance);
        $authobj->change_password($userobj, self::$sitedefaultinfo['admin']['password'], true);

        // Set site name
        set_config('sitename', self::$sitedefaultinfo['sitename']);

        // We need to keep the installed dataroot artefact files.
        // So each time we reset the dataroot before running a test, the default files are still installed.
        self::save_original_data_files();

        // Disable some settings that are not wanted on test sites.
        set_config('sendemail', false);

        // Keeps the current version of database and dataroot.
        self::store_versions_hash();

        // Stores the database contents for fast reset.
        self::store_database_state();

        // Updates behat config file
        BehatConfigManager::update_config_file();
    }

    /**
     * Drops dataroot and remove test database tables
     * @throws MaharaBehatTestException
     * @return void
     */
    public static function drop_site() {

        if (!defined('BEHAT_UTIL')) {
            throw new MaharaBehatTestException('This method can be only used by Behat CLI tool');
        }

        if (table_exists(new XMLDBTable('config'))) {
            self::drop_dataroot();
            self::drop_database(true);
        }
    }

    /**
     * Checks if $CFG->behat_wwwroot is available
     *
     * @return bool
     */
    public static function is_server_running() {
        global $CFG;

        $request = mahara_http_request(array(
            CURLOPT_URL => $CFG->behat_wwwroot,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => '',
        ), true);

        return !$request->error;
    }

    /**
     * Checks if the mahara database and dataroot for behat tests are ready
     * @return void
     */
    protected static function test_environment_problem() {

        if (!defined('BEHAT_UTIL')) {
            throw new MaharaBehatTestException('This method can be only used by Behat CLI tool');
        }

        if (!self::is_test_site_installed() || !self::is_test_site_enabled()) {
            behat_error(BEHAT_EXITCODE_NOTMAHARATESTSITE, 'This mahara site is not for behat testing!');
        }

        if (!self::is_test_site_updated()) {
            behat_error(BEHAT_EXITCODE_OUTOFDATEMAHARADB, 'The mahara database for testing is not updated');
        }
    }

    /**
     * Enables test mode
     *
     * It uses CFG->behat_dataroot
     *
     * Starts the test mode checking the composer installation and
     * the test environment and updating the available
     * features and steps definitions.
     *
     * Stores a file in dataroot/behat to allow Mahara to switch
     * to the test environment when using cli-server.
     * @throws MaharaBehatTestException
     * @return void
     */
    public static function start_test_mode() {
        global $CFG;

        if (!defined('BEHAT_UTIL')) {
            throw new MaharaBehatTestException('This method can be only used by Behat CLI tool');
        }

        $contents = '$CFG->behat_wwwroot, $CFG->behat_dbprefix and $CFG->behat_dataroot' .
                        ' are currently used as $CFG->wwwroot, $CFG->dbprefix and $CFG->dataroot';
        $filepath = self::get_test_file_path();
        if (!file_put_contents($filepath, $contents)) {
            behat_error(BEHAT_MAHARA_EXITCODE_NOTWRITABLEDATAROOT, 'File ' . $filepath . ' can not be created');
        }
    }

    /**
     * Returns the status of the behat test environment
     *
     * @return int
     *  = 0 if it is ready for Testing
     *  = Error code if not (see testing/frameworks/behat/lib.php)
     * @throws MaharaBehatTestException if not call by behat CLI util command
     */
    public static function get_test_env_status() {

        if (!defined('BEHAT_UTIL')) {
            throw new MaharaBehatTestException('This method can be only used by Behat CLI tool');
        }

        // Checks the behat set up and the PHP version, returning an error code if something went wrong.
        if ($errorcode = BehatCommand::get_behat_setup_status()) {
            return $errorcode;
        }

        if ($errorcode = self::check_test_site_config()) {
            return $errorcode;
        }

        if (!self::is_test_site_installed()) {
            return BEHAT_MAHARA_EXITCODE_NOTINSTALLED;
        }

        if (!self::is_test_site_enabled()) {
            return BEHAT_MAHARA_EXITCODE_NOTENABLED;
        }

        if (!self::is_test_site_updated()) {
            return BEHAT_MAHARA_EXITCODE_OUTOFDATEDB;
        }

        return 0;
    }

    /**
     * Disables test mode
     * @throws MaharaBehatTestException
     * @return void
     */
    public static function stop_test_mode() {

        if (!defined('BEHAT_UTIL')) {
            throw new MaharaBehatTestException('This method can be only used by Behat CLI tool');
        }

        if (!self::is_test_site_enabled()) {
            echo "Test environment was already disabled\n";
        }
        else {
            $testenvfile = self::get_test_file_path();
            if (!unlink($testenvfile)) {
                behat_error(BEHAT_MAHARA_EXITCODE_BADPERMISSIONS, 'Can not delete test environment file');
            }
        }
    }

    /**
     * Checks whether test site is installed or not
     *
     * @return bool
     */
    public static function is_test_site_installed() {

        if (table_exists(new XMLDBTable('config'))
            && get_config('behattest')) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether test environment is enabled or disabled
     *
     * @return bool
     */
    public static function is_test_site_enabled() {
        global $CFG;
        if (!isset($CFG->behat_dataroot)) {
            return false;
        }

        $testenvfile = self::get_test_file_path();
        if (file_exists($testenvfile)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the path to the file which specifies if test environment is enabled
     * @return string
     */
    protected final static function get_test_file_path() {
        global $CFG;

        return $CFG->behat_dataroot . '/behat/test_environment_enabled.txt';
    }

    /**
     * Returns the path to behat YML config file
     * @return string
     */
    public static function get_behat_config_path() {
        global $CFG;

        return $CFG->behat_dataroot . '/behat/behat.yml';
    }

}


/**
 * Test exceptions. Usually the fault of runing behat tests
 * So they extend SystemException.
 */
class MaharaBehatTestException extends SystemException { }

/**
 * Bootstrap exceptions. Usually the fault of the behat.yml
 * So they extend ConfigException.
 */
class MaharaBehatTestBootstrapException extends ConfigException { }