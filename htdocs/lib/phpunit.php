<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Penny Leach
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
 * @subpackage tests
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */
if (!defined('TESTSRUNNING')) {
    define('TESTSRUNNING', 1);
}

/**
 * Small class to handle all things necessary to bootstrap Mahara
 * to create an environment to run tests in.
 * Handles munging the database, config etc
 */
class UnitTestBootstrap {

    /**
     * original config loaded from the db
     * we need to hang on to this so we can unset it
     *
     * @todo investigate running Mahara with different types of config on
     */
    private $originaldbconfig = array();

    /**
     * constructor, make sure phpunit.xml settings are sane
     */
    public function __construct() {
        // small sanity check that the test db prefix is configured
        if (empty($GLOBALS['TESTDBPREFIX'])) {
            throw new UnitTestBootstrapException('No test prefix defined, refusing to run tests');
        }
    }

    /**
     * munge the Mahara config.
     *
     * @uses $CFG
     */
    public function jimmy_config() {
        global $CFG;
        $this->originaldbconfig = get_records_array('config');

        $CFG->dbprefix = $GLOBALS['TESTDBPREFIX'];
        $CFG->prefix   = $GLOBALS['TESTDBPREFIX'];
        $CFG->libdir = get_config('libroot');

        try {
            db_ignore_sql_exceptions(true);
            load_config();
            db_ignore_sql_exceptions(false);
        }
        catch (SQLException $e) {
            db_ignore_sql_exceptions(false);
        }


        // now reload the config since $CFG is dirty with the real config table
        foreach ($this->originaldbconfig as $c) {
            unset($CFG->{$c->field});
        }
    }

    /**
     * detect and clean up any old test tables lying around
     * as of phpunit 3.4, there's no corollary to bootstrap to clean up,
     * so this will actually be invoked every single time
     * which is quite annoying
     */
    public function clean_stale_tables() {
        if (table_exists(new XMLDBTable('config'))) {
            if (empty($GLOBALS['TESTDROPSTALEDB']) || $GLOBALS['TESTDROPSTALEDB'] !== true) {
                throw new UnitTestBootstrapException('Stale test tables found, and drop option not set.  Refusing to run tests');
            }
            log_info('Stale test tables found, and drop option is set.  Dropping them before running tests');
            $this->uninstall_mahara();
            log_info('Done');
        }
    }

    /**
     * completely uninstall mahara, drop all tables.
     * this just does what install does, but in reverse order
     * reversing the order of tables, and indexes
     * to respect referential integrity
     */
    public function uninstall_mahara() {
        // this can't be done in a transaction because sometimes
        // things exist in the database that aren't in the file or the other way around
        // in the case where there are stale tables and then the code is upgraded
        foreach (array_reverse(plugin_types_installed()) as $t) {
            if ($installed = plugins_installed($t, true)) {
                foreach ($installed  as $p) {
                    $location = get_config('docroot') . $t . '/' . $p->name. '/db/';
                    log_info('Uninstalling ' . $location);
                    if (is_readable($location . 'install.xml')) {
                        uninstall_from_xmldb_file($location . 'install.xml');
                    }
                }
            }
        }
        // now uninstall core
        log_info('Uninstalling core');

        // These constraints must be dropped manually as they cannot be
        // created with xmldb due to ordering issues
        if (is_postgres()) {
            execute_sql('ALTER TABLE {usr} DROP CONSTRAINT {usr_pro_fk}');
            execute_sql('ALTER TABLE {institution} DROP CONSTRAINT {inst_log_fk}');
        }
        else {
            execute_sql('ALTER TABLE {usr} DROP FOREIGN KEY {usr_pro_fk}');
            execute_sql('ALTER TABLE {institution} DROP FOREIGN KEY {inst_log_fk}');
        }

        uninstall_from_xmldb_file(get_config('docroot') . 'lib/db/install.xml');
    }

    /**
     * Install mahara from scratch.  Does both database tables and core data.
     * Exactly the same as the web-based installer
     * except for logging the current user in.
     */
    public function install_mahara() {
        log_info('Installing Mahara');

        db_ignore_sql_exceptions(true);
        $upgrades = check_upgrades();
        db_ignore_sql_exceptions(false);
        $upgrades['firstcoredata'] = true;
        $upgrades['lastcoredata'] = true;
        uksort($upgrades, 'sort_upgrades');
        foreach ($upgrades as $name => $data) {
            if ($name == 'disablelogin') {
                continue;
            }
            log_info('Installing ' . $name);
            if ($name == 'firstcoredata' || $name == 'lastcoredata') {
                $funname = 'core_install_' . $name . '_defaults';
                $funname();
                continue;
            }
            else {
                if ($name == 'core') {
                    $funname = 'upgrade_core';
                }
                else {
                    $funname = 'upgrade_plugin';
                }
                $data->name = $name;
                $funname($data);
            }
        }
    }
}

/**
 * Superclass for Mahara unit tests to provide helper methods to create data
 *
 * @todo require_* methods:
 * views
 * groups (takes plugins)
 * artefacts (takes plugins)
 * interactions (takes plugins)
 *
 * @todo think about:
 * mocking events (or just ignoring them)
 * mocking the file system
 */
class MaharaUnitTest extends PHPUnit_Framework_TestCase {

    /** array of users we have created */
    protected $users = array();

    /** required user data for creating users **/
    private static $userdata = array('username', 'email', 'firstname', 'lastname');

    /**
     * Superclass setUp method
     *
     * Takes care of setting up the database correctly as this doesn't
     * happen in unit test through init.php properly.
     *
     * parent::setUp() must always be called if it is overriden in
     * subclasses
     *
     * @return void
     */
    protected function setUp() {
        configure_dbconnection();
    }

    /**
     * require a user to be created in order for the tests to run
     *
     * @param stdclass $userdata data about the user to create
     *                 this can take anything that {@link create_user} can take
     *                 these will be automatically cleaned up in tearDown
     *                 so make sure you call parent::tearDown()
     * @return void
     */
    protected function require_user($userdata) {
        foreach (self::$userdata as $field) {
            if (!isset($userdata->{$field})) {
                throw new MaharaUnitTestException("MaharaUnitTest::require_user call missing required field $field");
            }
        }
        if (array_key_exists($userdata->username, $userdata)) {
            throw new MaharaUnitTextException("MaharaUnitTest::require_user called with duplicate username {$userdata->username}");
        }
        if (empty($userdata->password)) {
            $userdata->password = 'test';
        }
        try {
            $this->users[$userdata->username] = create_user($userdata);
        }
        catch (Exception $e) {
            throw new MaharaUnitTestException("MaharaUnitTest::require_user call caught an exception creating a user: " . $e->getMessage());
        }
    }

    /**
     * tiny wrapper around {@link require_user} to just create a test user
     * with a default username ('test')
     * this can only be called once
     *
     * @return void
     */
    protected function require_test_user() {
        $authinstance = get_record('auth_instance', 'institution', 'mahara');
        return $this->require_user((object)array(
            'username'      => 'test',
            'email'         => 'test@localhost',
            'firstname'     => 'Test',
            'lastname'      => 'User',
            'authinstance'  => $authinstance->id,
        ));
    }

    /**
     * superclass tearDown method
     * takes care to delete all data that has been created
     * with any of the require_ methods
     *
     * <b>always</b> call this, even if you override it.
     */
    protected function tearDown() {
        foreach ($this->users as $userid) {
            delete_user($userid);
        }
    }
}

/**
 * Test exceptions. Usually the fault of the test author
 * So they extend SystemException.
 */
class MaharaUnitTestException extends SystemException { }

/**
 * Bootstrap exceptions. Usually the fault of the phpunit.xml author
 * So they extend ConfigException.
 */
class UnitTestBootstrapException extends ConfigException { }
