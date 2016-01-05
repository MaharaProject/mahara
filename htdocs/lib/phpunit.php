<?php
/**
 *
 * @package    mahara
 * @subpackage tests
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Penny Leach
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
        foreach (get_installed_plugins_paths() as $pluginpath) {
            $location = $pluginpath . '/db/';
            log_info('Uninstalling ' . $location);
            $xmldbfile = $location . 'install.xml';
            if (is_readable($xmldbfile)) {
                uninstall_from_xmldb_file($xmldbfile);
            }
        }
        // now uninstall core
        log_info('Uninstalling core');

        // These constraints must be dropped manually as they cannot be
        // created with xmldb due to ordering issues
        if (is_postgres()) {
            try {
                execute_sql('ALTER TABLE {usr} DROP CONSTRAINT {usr_pro_fk}');
            }
            catch (Exception $e) {
            }
            try {
                execute_sql('ALTER TABLE {institution} DROP CONSTRAINT {inst_log_fk}');
            }
            catch (Exception $e) {
            }
        }
        else {
            try {
                execute_sql('ALTER TABLE {usr} DROP FOREIGN KEY {usr_pro_fk}');
            }
            catch (Exception $e) {
            }
            try {
                execute_sql('ALTER TABLE {institution} DROP FOREIGN KEY {inst_log_fk}');
            }
            catch (Exception $e) {
            }
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
            if ($name == 'settings') {
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
 * @todo create_test_* methods:
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

    /** @var array list of common last names */
    public $lastnames = array(
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'García', 'Rodríguez', 'Wilson',
        'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Meyer', 'Weber', 'Schulz', 'Wagner', 'Becker', 'Hoffmann',
        'Novák', 'Svoboda', 'Novotný', 'Dvořák', 'Černý', 'Procházková', 'Kučerová', 'Veselá', 'Horáková', 'Němcová',
        'Смирнов', 'Иванов', 'Кузнецов', 'Соколов', 'Попов', 'Лебедева', 'Козлова', 'Новикова', 'Морозова', 'Петрова',
        '王', '李', '张', '刘', '陈', '楊', '黃', '趙', '吳', '周',
        '佐藤', '鈴木', '高橋', '田中', '渡辺', '伊藤', '山本', '中村', '小林', '斎藤',
    );

    /** @var array list of common first names */
    public $firstnames = array(
        'Jacob', 'Ethan', 'Michael', 'Jayden', 'William', 'Isabella', 'Sophia', 'Emma', 'Olivia', 'Ava',
        'Lukas', 'Leon', 'Luca', 'Timm', 'Paul', 'Leonie', 'Leah', 'Lena', 'Hanna', 'Laura',
        'Jakub', 'Jan', 'Tomáš', 'Lukáš', 'Matěj', 'Tereza', 'Eliška', 'Anna', 'Adéla', 'Karolína',
        'Даниил', 'Максим', 'Артем', 'Иван', 'Александр', 'София', 'Анастасия', 'Дарья', 'Мария', 'Полина',
        '伟', '伟', '芳', '伟', '秀英', '秀英', '娜', '秀英', '伟', '敏',
        '翔', '大翔', '拓海', '翔太', '颯太', '陽菜', 'さくら', '美咲', '葵', '美羽',
    );

    public $loremipsum = <<<EOD
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nulla non arcu lacinia neque faucibus fringilla. Vivamus porttitor turpis ac leo. Integer in sapien. Nullam eget nisl. Aliquam erat volutpat. Cras elementum. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Integer malesuada. Nullam lectus justo, vulputate eget mollis sed, tempor sed magna. Mauris elementum mauris vitae tortor. Aliquam erat volutpat.
Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Pellentesque ipsum. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Aliquam ante. Proin in tellus sit amet nibh dignissim sagittis. Vivamus porttitor turpis ac leo. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. In sem justo, commodo ut, suscipit at, pharetra vitae, orci. Aliquam erat volutpat. Nulla est.
Vivamus luctus egestas leo. Aenean fermentum risus id tortor. Mauris dictum facilisis augue. Aliquam erat volutpat. Aliquam ornare wisi eu metus. Aliquam id dolor. Duis condimentum augue id magna semper rutrum. Donec iaculis gravida nulla. Pellentesque ipsum. Etiam dictum tincidunt diam. Quisque tincidunt scelerisque libero. Etiam egestas wisi a erat.
Integer lacinia. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Mauris tincidunt sem sed arcu. Nullam feugiat, turpis at pulvinar vulputate, erat libero tristique tellus, nec bibendum odio risus sit amet ante. Aliquam id dolor. Maecenas sollicitudin. Et harum quidem rerum facilis est et expedita distinctio. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Nullam dapibus fermentum ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Pellentesque sapien. Duis risus. Mauris elementum mauris vitae tortor. Suspendisse nisl. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.
In laoreet, magna id viverra tincidunt, sem odio bibendum justo, vel imperdiet sapien wisi sed libero. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Maecenas lorem. Etiam posuere lacus quis dolor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Curabitur ligula sapien, pulvinar a vestibulum quis, facilisis vel sapien. Nam sed tellus id magna elementum tincidunt. Suspendisse nisl. Vivamus luctus egestas leo. Nulla non arcu lacinia neque faucibus fringilla. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Etiam dictum tincidunt diam. Etiam commodo dui eget wisi. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Duis ante orci, molestie vitae vehicula venenatis, tincidunt ac pede. Pellentesque sapien.
EOD;

    // Arrays of objects we have created - used to automatically tidy up later.
    protected $testusers = array();
    protected $testgroups = array();
    protected $testinstitutions = array();

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
     * Create a user that can be used in a test.
     *
     * @param stdclass $userdata data about the user to create - this can take anything that {@link create_user} can take.
     *                 If null then a user called 'testX' will be created, where X is the number of users created so far.
     *                 These will be automatically cleaned up in tearDown, so make sure you call parent::tearDown().
     * @return int new user id.
     */
    protected function create_test_user($userdata = null, $institution = null) {
        $authinstance = get_record('auth_instance', 'institution', 'mahara');
        $testdata = array(
            'username'     => 'test' . count($this->testusers),
            'email'        => 'test' . count($this->testusers) . '@localhost',
            'firstname'    => $this->firstnames[array_rand($this->firstnames)],
            'lastname'     => $this->lastnames[array_rand($this->lastnames)],
            'password'     => 'test',
            'authinstance' => $authinstance->id,
        );

        $combineddata = (object)array_merge($testdata, (array)$userdata);

        if (array_key_exists($combineddata->username, $this->testusers)) {
            throw new MaharaUnitTextException("MaharaUnitTest::create_test_user called with duplicate username {$combineddata->username}");
        }
        try {
            $newuser = create_user($combineddata, array(), $institution);
            $this->testusers[$combineddata->username] = $newuser;
            return $newuser;
        }
        catch (Exception $e) {
            throw new MaharaUnitTestException("MaharaUnitTest::create_test_user call caught an exception creating a user: " . $e->getMessage());
        }
    }

    /**
     * Create a group that can be used in a test.
     *
     * @param array $groupdata data about the group to create - this can take anything that {@link group_create} can take.
     *              If null then a group called 'groupX' will be created, where X is the number of groups created so far.
     *              These will be automatically cleaned up in tearDown, so make sure you call parent::tearDown().
     * @return int new group id.
     */
    protected function create_test_group($groupdata = null) {
        $testdata = array(
            'name'      => 'group' . count($this->testgroups),
            'grouptype' => 'test' . count($this->testusers) . '@localhost',
        );

        $combineddata = array_merge($testdata, (array)$groupdata);

        if (array_key_exists($combineddata['name'], $this->testgroups)) {
            throw new MaharaUnitTextException("MaharaUnitTest::create_test_group called with duplicate name {$combineddata['name']}");
        }

        try {
            $newgroupid = group_create($combineddata);
            $this->testgroups[$combineddata['name']] = $newgroupid;
            return $newgroupid;
        }
        catch (Exception $e) {
            throw new MaharaUnitTestException("MaharaUnitTest::create_test_group call caught an exception creating a group: " . $e->getMessage());
        }
    }

    /**
     * Create an institution that can be used in a test.
     *
     * @param array $instdata data about the institution to create - this can take anything that can go into the institution table.
     *              If null then an institution called 'institutionX' will be created, where X is the number of institutions created so far.
     *              These will be automatically cleaned up in tearDown, so make sure you call parent::tearDown().
     * @return int new institution id.
     */
    protected function create_test_institution($instdata = null) {
        $testdata = array(
            'name' => 'institution' . count($this->testinstitutions),
            'displayname' => 'institution' . count($this->testinstitutions),
        );

        $combineddata = (object)array_merge($testdata, (array)$instdata);

        if (array_key_exists($combineddata->name, $this->testinstitutions)) {
            throw new MaharaUnitTextException("MaharaUnitTest::create_test_institution called with duplicate name {$combineddata->name}");
        }

        try {
            insert_record('institution', $combineddata);
            $this->testinstitutions[$combineddata->name] = $combineddata->name;
            return get_field('institution', 'id', 'name', $combineddata->name);
        }
        catch (Exception $e) {
            throw new MaharaUnitTestException("MaharaUnitTest::create_test_institution call caught an exception creating an institution: " . $e->getMessage());
        }
    }

    /**
     * Superclass tearDown method takes care to delete all data that has been created with any of the create_test_ methods.
     *
     * <b>always</b> call this, even if you override it.
     */
    protected function tearDown() {
        foreach ($this->testusers as $userid) {
            delete_user($userid);
        }
        foreach ($this->testgroups as $group) {
            group_delete($group);
        }
        foreach ($this->testinstitutions as $institution) {
            delete_records('institution', 'name', $institution);
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