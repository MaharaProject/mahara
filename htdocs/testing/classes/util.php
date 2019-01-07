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
 * Utils for preparing a mahara site for testing including:
 *
 * - dataroot
 * - database
 * - generated data
 *
 * All testing frameworks need to extend from this class
 * For example: Phpunit, Behat
 * class PhpunitTestingUtil extends TestingUtil {}
 * class BehatTestingUtil extends TestingUtil {}
 */

abstract class TestingUtil {

    /**
     * @var string dataroot (likely to be $CFG->dataroot).
     */
    private static $dataroot = null;

    /**
     * @var testing_data_generator
     */
    protected static $generator = null;

    /**
     * @var string current version hash from php files
     */
    protected static $versionhash = null;

    /**
     * @var array original content of all database tables
     */
    protected static $tabledata = null;

    /**
     * @var array original structure of all database tables
     */
    protected static $tablestructure = null;

    /**
     * @var array original structure of all database tables
     */
    protected static $sequencenames = null;

    /**
     * @var string name of the json file where we store the list of dataroot files to not reset during reset_dataroot.
     */
    private static $originaldatafilesjson = 'originaldatafiles.json';

    /**
     * @var boolean set to true once $originaldatafilesjson file is created.
     */
    private static $originaldatafilesjsonadded = false;

    /**
     * Return the name of the JSON file containing the init filenames.
     *
     * @static
     * @return string
     */
    public static function get_originaldatafilesjson() {
        return self::$originaldatafilesjson;
    }

    /**
     * Return the mahara root dir which should contains htdocs and test directories
     *
     * @static
     * @return string the mahara root dir
     */
    public static function get_mahararoot() {
        return dirname(dirname(dirname(__DIR__)));
    }

    /**
     * Return the dataroot. It's useful when mocking the dataroot when unit testing this class itself.
     *
     * @static
     * @return string the dataroot.
     */
    public static function get_dataroot() {
        global $CFG;

        //  By default it's the test framework dataroot.
        if (empty(self::$dataroot)) {
            self::$dataroot = $CFG->dataroot;
        }

        return self::$dataroot;
    }

    /**
     * Set the dataroot. It's useful when mocking the dataroot when unit testing this class itself.
     *
     * @param string $dataroot the dataroot of the test framework.
     * @static
     */
    public static function set_dataroot($dataroot) {
        self::$dataroot = $dataroot;
    }

    /**
     * Returns the testing framework name
     * @static
     * @return string
     */
    protected static final function get_framework() {
        $classname = get_called_class();
        return strtolower(substr($classname, 0, strpos($classname, 'TestingUtil')));
    }

    /**
     * Get data generator
     * @static
     * @return testing_data_generator
     */
    public static function get_data_generator() {
        if (is_null(self::$generator)) {
            require_once(__DIR__ . '/generator/lib.php');
            self::$generator = new TestingDataGenerator();
        }
        return self::$generator;
    }

    /**
     * Checks if the mahara db and dataroot are enabled to test
     *
     * @static
     * @return bool
     */
    public static function is_test_site_enabled() {
        return true;
    }

    /**
     * Returns whether test database and dataroot were created using the current version codebase
     *
     * @return bool
     */
    public static function is_test_site_updated() {
        $framework = self::get_framework();

        $datarootpath = self::get_dataroot() . DIRECTORY_SEPARATOR . $framework;
        if (!file_exists($datarootpath . DIRECTORY_SEPARATOR . 'tabledata.ser')
            || !file_exists($datarootpath . DIRECTORY_SEPARATOR . 'tablestructure.ser')) {
            return false;
        }

        if (!file_exists($datarootpath . '/versionshash.txt')) {
            return false;
        }

        $hash = get_all_versions_hash();
        $oldhash = file_get_contents($datarootpath . '/versionshash.txt');

        if ($hash !== $oldhash) {
            return false;
        }

        $dbhash = get_config($framework . 'test');
        if ($hash !== $dbhash) {
            return false;
        }

        return true;
    }

    /**
     * Stores the status of the database
     *
     * Serializes the contents and the structure and
     * stores it in the test framework space in dataroot
     */
    protected static function store_database_state() {
        $framework = self::get_framework();

        // store data for all tables
        $data = array();
        $structure = array();
        $tables = get_tables_from_xmldb();
        foreach ($tables as $table) {
            $tablename = $table->getName();
            $columns = get_columns($tablename);
            $structure[$tablename] = $columns;
            if (isset($columns['ID']) && $columns['ID']->auto_increment) {
                $data[$tablename] = get_records_array($tablename, '', '', 'id ASC');
            }
            else {
                // there should not be many of these
                $data[$tablename] = get_records_array($tablename);
            }
        }
        $data = serialize($data);
        $datafile = self::get_dataroot() . '/' . $framework . '/tabledata.ser';
        file_put_contents($datafile, $data);
        testing_fix_file_permissions($datafile);

        $structure = serialize($structure);
        $structurefile = self::get_dataroot() . '/' . $framework . '/tablestructure.ser';
        file_put_contents($structurefile, $structure);
        testing_fix_file_permissions($structurefile);
    }

    /**
     * Stores the version hash in both database and dataroot
     */
    protected static function store_versions_hash() {
        global $CFG;

        $framework = self::get_framework();
        $hash = get_all_versions_hash();

        // add test db flag
        set_config($framework . 'test', $hash);

        // hash all plugin versions - helps with very fast detection of db structure changes
        $hashfile = self::get_dataroot() . '/' . $framework . '/versionshash.txt';
        file_put_contents($hashfile, $hash);
        testing_fix_file_permissions($hashfile);
    }

    /**
     * Returns contents of all tables right after installation.
     * @static
     * @return array  $table=>$records
     */
    protected static function get_tabledata() {
        $framework = self::get_framework();

        $datafile = self::get_dataroot() . '/' . $framework . '/tabledata.ser';
        if (!file_exists($datafile)) {
            // Not initialised yet.
            return array();
        }

        if (!isset(self::$tabledata)) {
            $data = file_get_contents($datafile);
            self::$tabledata = unserialize($data);
        }

        if (!is_array(self::$tabledata)) {
            testing_error(1, 'Can not read dataroot/' . $framework . '/tabledata.ser or invalid format, reinitialize test database.');
        }

        return self::$tabledata;
    }

    /**
     * Returns structure of all tables right after installation.
     * @static
     * @return array $table=>$records
     */
    public static function get_tablestructure() {
        $framework = self::get_framework();

        $structurefile = self::get_dataroot() . '/' . $framework . '/tablestructure.ser';
        if (!file_exists($structurefile)) {
            // Not initialised yet.
            return array();
        }

        if (!isset(self::$tablestructure)) {
            $data = file_get_contents($structurefile);
            self::$tablestructure = unserialize($data);
        }

        if (!is_array(self::$tablestructure)) {
            testing_error(1, 'Can not read dataroot/' . $framework . '/tablestructure.ser or invalid format, reinitialize test database.');
        }

        return self::$tablestructure;
    }

    /**
     * Returns the names of sequences for each autoincrementing id field in all standard tables.
     * @static
     * @return array $table=>$sequencename
     */
    public static function get_sequencenames() {
        if (isset(self::$sequencenames)) {
            return self::$sequencenames;
        }

        if (!$structure = self::get_tablestructure()) {
            return array();
        }

        self::$sequencenames = array();
        foreach ($structure as $table => $ignored) {
            $name = find_sequence_name(new XMLDBTable($table));
            if ($name !== false) {
                self::$sequencenames[$table] = $name;
            }
        }

        return self::$sequencenames;
    }

    /**
     * Returns list of tables that are unmodified or empty.
     *
     * @static
     * @return array of table names, empty if unknown
     */
    protected static function guess_unmodified_empty_tables() {
        $data = self::get_tabledata();
        $structure = self::get_tablestructure();
        $prefix = get_config('dbprefix');
        $unmodifiedorempties = array();
        if (is_mysql()) {
            $records = get_records_sql_array("SHOW TABLE STATUS LIKE ?", array($prefix . '%'));
            foreach ($records as $info) {
                $tablename = strtolower($info->Name);
                if (strpos($tablename, $prefix) !== 0) {
                    // incorrect table match caused by _
                    continue;
                }
                if (!empty($info->auto_increment)) {
                    $tablename = substr($tablename, strlen($prefix));
                    if ($info->auto_increment === 1) {
                        $unmodifiedorempties[$tablename] = $tablename;
                    }
                }
            }
            unset($records);
        }
        else if (is_postgres()) {
            $tables = get_tables_from_xmldb();
            foreach ($tables as $table) {
                $tablename = $table->getName();
                $columns = get_columns($tablename);
                if (!record_exists($tablename) && empty($data[$tablename])) {
                    $unmodifiedorempties[$tablename] = $tablename;
                    continue;
                }
                if (isset($columns['ID']) && isset($columns['ID']->auto_increment)) {
                    if ($columns['ID']->auto_increment == 1) {
                        $unmodifiedorempties[$tablename] = $tablename;
                    }
                    else {
                        if (isset($structure[$tablename]['ID']->auto_increment) && $columns['ID']->auto_increment == $structure[$tablename]['ID']->auto_increment) {
                            $unmodifiedorempties[$tablename] = $tablename;
                        }
                    }
                }
            }
        }
        return $unmodifiedorempties;
    }

    /**
     * Reset all database sequences to initial values.
     *
     * @static
     * @param array $unmodifiedorempties tables that are known to be unmodified or empty
     * @return void
     */
    public static function reset_all_database_sequences(array $unmodifiedorempties = null) {
        if (!$data = self::get_tabledata()) {
            // Not initialised yet.
            return;
        }
        if (!$structure = self::get_tablestructure()) {
            // Not initialised yet.
            return;
        }

        db_begin();
        $prefix = get_config('dbprefix');
        if (is_postgres()) {
            foreach ($data as $table => $records) {
                if (isset($structure[$table]['ID'])
                    && !empty($structure[$table]['ID']->auto_increment)
                    ) {
                    if (empty($records)) {
                        $nextid = 1;
                    }
                    else {
                        $lastrecord = end($records);
                        $nextid = $lastrecord->id + 1;
                    }
                    execute_sql("ALTER SEQUENCE {$prefix}{$table}_id_seq RESTART WITH $nextid");
                }
            }

        }
        else if (is_mysql()) {
            $sequences = array();
            $records = get_records_sql_array("SHOW TABLE STATUS LIKE ?", array($prefix . '%'));
            foreach ($records as $info) {
                $table = strtolower($info->Name);
                if (strpos($table, $prefix) !== 0) {
                    // incorrect table match caused by _
                    continue;
                }
                if (!empty($info->auto_increment)) {
                    $table = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $table);
                    $sequences[$table] = $info->auto_increment;
                }
            }
            unset($records);
            foreach ($data as $table => $records) {
                if (isset($structure[$table]['ID']) && isset($structure[$table]['ID']->auto_increment)) {
                    if (isset($sequences[$table])) {
                        if (empty($records)) {
                            $nextid = 1;
                        }
                        else {
                            $lastrecord = end($records);
                            $nextid = $lastrecord->id + 1;
                        }
                        if ($sequences[$table] != $nextid) {
                            execute_sql("ALTER TABLE {$prefix}{$table} AUTO_INCREMENT = $nextid");
                            log_info('SQL command: ' . "ALTER TABLE {$prefix}{$table} AUTO_INCREMENT = $nextid");
                        }

                    }
                }
            }

        }
        db_commit();
    }

    /**
     * Reset all database tables to default values.
     * @static
     * @return bool true if reset done, false if skipped
     */
    public static function reset_database() {
        $tables = get_tables_from_xmldb();
        $prefix = get_config('dbprefix');

        if (!table_exists(new XMLDBTable('config'))) {
            // not installed yet
            return false;
        }

        if (!$data = self::get_tabledata()) {
            // not initialised yet
            return false;
        }
        if (!$structure = self::get_tablestructure()) {
            // not initialised yet
            return false;
        }

        $unmodifiedorempties = self::guess_unmodified_empty_tables();

        db_begin();
        // Temporary drop current foreign key contraints
        $foreignkeys = array();
        foreach ($tables as $table) {
            $tablename = $table->getName();
            $foreignkeys = array_merge($foreignkeys, get_foreign_keys($tablename));
        }
        // Drop foreign key contraints
        if (is_mysql()) {
            foreach ($foreignkeys as $key) {
                execute_sql('ALTER TABLE ' . db_quote_identifier($key['table']) . ' DROP FOREIGN KEY ' . db_quote_identifier($key['constraintname']));
            }
        }
        else {
            foreach ($foreignkeys as $key) {
                execute_sql('ALTER TABLE ' . db_quote_identifier($key['table']) . ' DROP CONSTRAINT IF EXISTS ' . db_quote_identifier($key['constraintname']));
            }
        }
        foreach ($tables as $table) {
            $tablename = $table->getName();
            if (isset($unmodifiedorempties[$tablename])) {
                continue;
            }
            if (!isset($data[$tablename])) {
                continue;
            }
            // Empty the table
            execute_sql('DELETE FROM {' . $tablename . '}');
            // Restore the table from the backup file
            if ($data[$tablename]) {
                foreach ($data[$tablename] as $record) {
                    insert_record($tablename, $record);
                    if ($tablename == 'usr' && $record->username == 'root' && is_mysql()) {
                        // gratuitous mysql workaround
                        set_field('usr', 'id', 0, 'username', 'root');
                        execute_sql('ALTER TABLE {usr} AUTO_INCREMENT=1');
                    }
                }
            }
        }
        // Re-add foreign key contraints
        foreach ($foreignkeys as $key) {
            execute_sql('ALTER TABLE ' . db_quote_identifier($key['table']) . ' ADD CONSTRAINT '
                            . db_quote_identifier($key['constraintname']) .' FOREIGN KEY '
                            . '(' . implode(',', array_map('db_quote_identifier', $key['fields'])) . ')'
                            . ' REFERENCES ' . db_quote_identifier($key['reftable']) . '(' . implode(',', array_map('db_quote_identifier', $key['reffields'])) . ')');
        }

        db_commit();

        // reset all next record ids - aka sequences
        self::reset_all_database_sequences($unmodifiedorempties);

        return true;
    }

    /**
     * Purge dataroot directory
     * @static
     * @return void
     */
    public static function reset_dataroot() {
        global $CFG;

        $childclassname = self::get_framework() . 'TestingUtil';

        // Do not delete automatically installed files.
        self::skip_original_data_files($childclassname);

        // Clean up the dataroot folder.
        $handle = opendir(self::get_dataroot());
        while (false !== ($item = readdir($handle))) {
            if (in_array($item, $childclassname::$datarootskiponreset)) {
                continue;
            }
            rmdirr(self::get_dataroot() . "/$item");
        }
        closedir($handle);

        // Clean up the dataroot/artefact folder.
        if (file_exists(self::get_dataroot() . '/artefact')) {
            $handle = opendir(self::get_dataroot() . '/artefact');
            while (false !== ($item = readdir($handle))) {
                if (in_array('artefact/' . $item, $childclassname::$datarootskiponreset)) {
                    continue;
                }
                rmdirr(self::get_dataroot()."/artefact/$item");
            }
            closedir($handle);
        }

        // Create the folder 'behat' in dataroot
        $dirpath = self::get_dataroot() . '/' . self::get_framework();
        check_dir_exists($dirpath, true);

    }

    /**
     * Gets a text-based site version description.
     *
     * @return string The site info
     */
    public static function get_site_info() {
        global $CFG;

        $output = '';

        $release = null;
        require("$CFG->docroot/lib/version.php");

        $output .= "Mahara $release, $CFG->dbtype";
        if ($hash = self::get_git_hash()) {
            $output .= ", $hash";
        }
        $output .= "\n";

        return $output;
    }

    /**
     * Try to get current git hash of the Mahara in $CFG->docroot.
     * @return string null if unknown, sha1 hash if known
     */
    public static function get_git_hash() {
        global $CFG;

        // This is a bit naive, but it should mostly work for all platforms.

        if (!file_exists("$CFG->docroot/.git/HEAD")) {
            return null;
        }

        $headcontent = file_get_contents("$CFG->docroot/.git/HEAD");
        if ($headcontent === false) {
            return null;
        }

        $headcontent = trim($headcontent);

        // If it is pointing to a hash we return it directly.
        if (strlen($headcontent) === 40) {
            return $headcontent;
        }

        if (strpos($headcontent, 'ref: ') !== 0) {
            return null;
        }

        $ref = substr($headcontent, 5);

        if (!file_exists("$CFG->docroot/.git/$ref")) {
            return null;
        }

        $hash = file_get_contents("$CFG->docroot/.git/$ref");

        if ($hash === false) {
            return null;
        }

        $hash = trim($hash);

        if (strlen($hash) != 40) {
            return null;
        }

        return $hash;
    }

    /**
     * Drop the whole test database
     * @static
     * @param bool $displayprogress
     */
    public static function drop_database($displayprogress = false) {
        global $CFG;
        // Drop triggers
        try {
            db_drop_trigger('update_unread_insert', 'notification_internal_activity');
            db_drop_trigger('update_unread_update', 'notification_internal_activity');
            db_drop_trigger('update_unread_delete', 'notification_internal_activity');
            db_drop_trigger('update_unread_insert2', 'module_multirecipient_userrelation');
            db_drop_trigger('update_unread_update2', 'module_multirecipient_userrelation');
            db_drop_trigger('update_unread_delete2', 'module_multirecipient_userrelation');
            db_drop_trigger('unmark_quota_exceed_upd_usr_set', 'usr');
        }
        catch (Exception $e) {
            exit(1);
        }

        // Drop plugins' tables
        // Find all plugins from the code base
        // and drop their tables from database if exists
        $plugins = array();
        $pluginstocheck = plugin_types();
        foreach ($pluginstocheck as $plugin) {
            $dirhandle = opendir(get_config('docroot') . $plugin);
            while (false !== ($dir = readdir($dirhandle))) {
                if (strpos($dir, '.') === 0) {
                    continue;
                }
                if (!is_dir(get_config('docroot') . $plugin . DIRECTORY_SEPARATOR . $dir)) {
                    continue;
                }

                $plugins[] = array($plugin, $dir);

                if ($plugin == 'artefact') { // go check it for blocks as well
                    $btlocation = get_config('docroot') . $plugin . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'blocktype';
                    if (!is_dir($btlocation)) {
                        continue;
                    }
                    $btdirhandle = opendir($btlocation);
                    while (false !== ($btdir = readdir($btdirhandle))) {
                        if (strpos($btdir, '.') === 0) {
                            continue;
                        }
                        if (!is_dir(get_config('docroot') . $plugin . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'blocktype' . DIRECTORY_SEPARATOR . $btdir)) {
                            continue;
                        }
                        $plugins[] = array('blocktype', $dir . DIRECTORY_SEPARATOR . $btdir);
                    }
                }
            }
        }

        foreach ($plugins as $plugin) {
            $plugintype = $plugin[0];
            $pluginname = $plugin[1];
            $pluginpath = $plugin[0] . DIRECTORY_SEPARATOR . $plugin[1];
            $pluginkey  = "$plugin[0].$plugin[1]";

            if ($plugintype == 'blocktype' && strpos($pluginname, DIRECTORY_SEPARATOR) !== false) {
                // sigh.. we're a bit special...
                $bits = explode(DIRECTORY_SEPARATOR, $pluginname);
                $pluginpath = 'artefact' . DIRECTORY_SEPARATOR . $bits[0] . DIRECTORY_SEPARATOR . 'blocktype' . DIRECTORY_SEPARATOR . $bits[1];
            }

            log_info("Uninstalling $plugintype.$pluginname");
            $location = get_config('docroot') . $pluginpath . DIRECTORY_SEPARATOR . 'db';
            if (is_readable($location . DIRECTORY_SEPARATOR . 'install.xml')) {
                if ($plugintype == 'auth' && $pluginname == 'webservice') {
                    // This module has LTI module lti_assessment.oauthserver as a foreign key
                    if (is_mysql()) {
                        execute_sql('ALTER TABLE {lti_assessment} DROP FOREIGN KEY {ltiasse_oau_fk}');
                    }
                    else {
                        execute_sql('ALTER TABLE {lti_assessment} DROP CONSTRAINT {ltiasse_oau_fk}');
                    }
                }
                if ($plugintype == 'module' && $pluginname == 'framework') {
                    // This module has a core collection.framework as a foreign key
                    execute_sql('UPDATE {collection} SET framework = null');
                    if (is_mysql()) {
                        execute_sql('ALTER TABLE {collection} DROP FOREIGN KEY {coll_fra_fk}');
                    }
                    else {
                        execute_sql('ALTER TABLE {collection} DROP CONSTRAINT {coll_fra_fk}');
                    }
                }
                uninstall_from_xmldb_file($location . DIRECTORY_SEPARATOR . 'install.xml');
            }
        }

        // These constraints must be dropped manually as they cannot be
        // created with xmldb due to ordering issues
        try {
            if (is_postgres()) {
                execute_sql('ALTER TABLE {usr} DROP CONSTRAINT {usr_pro_fk}');
                execute_sql('ALTER TABLE {institution} DROP CONSTRAINT {inst_log_fk}');
            }
        }
        catch (Exception $e) {
            exit(1);
        }

        // now uninstall core
        if (is_mysql()) {
            execute_sql('SET foreign_key_checks = 0');
        }
        log_info('Uninstalling core');
        uninstall_from_xmldb_file(get_config('docroot') . 'lib'.DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'install.xml');
        if (is_mysql()) {
            execute_sql('SET foreign_key_checks = 1');
        }
        // Reset info so we install rather than update if calling install_site straight after
        $CFG->installed = false;
        $CFG->version = 0;
    }

    /**
     * Drops the test framework dataroot
     * @static
     */
    protected static function drop_dataroot() {
        global $CFG;

        $framework = self::get_framework();
        $childclassname = $framework . 'TestingUtil';

        $filedir = self::get_dataroot() . DIRECTORY_SEPARATOR  . $framework;
        if (file_exists($filedir)) {
            $files = scandir($filedir);
            foreach ($files as $file) {
                if (in_array($file, $childclassname::$datarootskipondrop)) {
                    continue;
                }
                $path = $filedir . DIRECTORY_SEPARATOR . $file;
                rmdirr($path);
            }
        }

        $jsonfilepath = self::get_dataroot() . DIRECTORY_SEPARATOR . self::$originaldatafilesjson;
        if (file_exists($jsonfilepath)) {
            // Delete the json file.
            unlink($jsonfilepath);
            // Delete the dataroot artefact.
            rmdirr(self::get_dataroot() . DIRECTORY_SEPARATOR .'artefact');
        }
    }

    /**
     * Skip the original dataroot files to not been reset.
     *
     * @static
     * @param string $utilclassname the util class name..
     */
    protected static function skip_original_data_files($utilclassname) {
        $jsonfilepath = self::get_dataroot() . DIRECTORY_SEPARATOR . self::$originaldatafilesjson;
        if (file_exists($jsonfilepath)) {

            $listfiles = file_get_contents($jsonfilepath);

            // Mark each files as to not be reset.
            if (!empty($listfiles) && !self::$originaldatafilesjsonadded) {
                $originaldatarootfiles = json_decode($listfiles);
                // Keep the json file. Only drop_dataroot() should delete it.
                $originaldatarootfiles[] = self::$originaldatafilesjson;
                $utilclassname::$datarootskiponreset = array_merge($utilclassname::$datarootskiponreset,
                    $originaldatarootfiles);
                self::$originaldatafilesjsonadded = true;
            }
        }
    }

    /**
     * Save the list of the original dataroot files into a json file.
     */
    protected static function save_original_data_files() {
        global $CFG;

        $jsonfilepath = self::get_dataroot() . DIRECTORY_SEPARATOR . self::$originaldatafilesjson;
        $filedir = self::get_dataroot() . DIRECTORY_SEPARATOR . 'artefact';

        // Save the original dataroot files if not done (only executed the first time).
        if (file_exists($filedir)
            && !file_exists($jsonfilepath)) {

            $listfiles = array();
            $listfiles['artefact/.'] = 'artefact/.';
            $listfiles['artefact/..'] = 'artefact/..';

            $directory = new RecursiveDirectoryIterator($filedir);
            foreach (new RecursiveIteratorIterator($directory) as $file) {
                if ($file->isDir()) {
                    $key = substr($file->getPath(), strlen(self::get_dataroot() . DIRECTORY_SEPARATOR));
                }
                else {
                    $key = substr($file->getPathName(), strlen(self::get_dataroot() . DIRECTORY_SEPARATOR));
                }
                $listfiles[$key] = $key;
            }

            // Save the file list in a JSON file.
            $fp = fopen($jsonfilepath, 'w');
            fwrite($fp, json_encode(array_values($listfiles)));
            fclose($fp);
        }
    }
}
