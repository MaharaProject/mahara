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
 * TestsFinder class
 *
 * Finds plugins with tests
 *
 */
class TestsFinder {

    /**
     * Returns all the plugins with tests of the specified type
     * @param string $testtype The kind of test we are looking for, e.g. phpunit|features|stepdefinitions
     * @return array
     */
    public static function get_plugins_with_tests($testtype) {

        // Get all the plugins
        $plugins = self::get_all_plugins_with_tests($testtype) + self::get_all_subsystems_with_tests($testtype);

        // Get all the directories having tests
        $directories = self::get_all_directories_with_tests($testtype);

        // Find any directory not covered by proper plugins
        $remaining = array_diff($directories, $plugins);

        // Add them to the list of plugins
        $plugins += $remaining;

        return $plugins;
    }

    /**
     * Returns all the plugins having tests
     * @param string $testtype The kind of test we are looking for, e.g. phpunit|features|stepdefinitions
     * @return array  all the plugins having tests
     */
    private static function get_all_plugins_with_tests($testtype) {
        $pluginswithtests = array();

        $pluginpaths = get_installed_plugins_paths();
        foreach ($pluginpaths as $pluginkey => $pluginpath) {
             // Look for tests recursively
            if (self::directory_has_tests($pluginpath, $testtype)) {
                $pluginswithtests[$pluginkey] = $pluginpath;
            }
        }
        return $pluginswithtests;
    }

    /**
     * Returns all the subsystems having tests
     *
     * Note we are hacking here the list of subsystems
     * to cover some well-known subsystems.
     *
     * @param string $testtype The kind of test we are looking for
     * @return array all the subsystems having tests
     */
    private static function get_all_subsystems_with_tests($testtype) {
        global $CFG;

        $subsystemspaths = array(
                'account'     => $CFG->docroot . 'account',
                'collection'  => $CFG->docroot . 'collection',
                'group'       => $CFG->docroot . 'group',
                'skin'        => $CFG->docroot . 'skin',
                'user'        => $CFG->docroot . 'user',
                'view'        => $CFG->docroot . 'view',
                'admin.users' => $CFG->docroot . 'admin/users',
                'admin.groups' => $CFG->docroot . 'admin/groups',
                'admin.site'  => $CFG->docroot . 'admin/site',
        );

        $subsystemswithtests = array();
        foreach ($subsystemspaths as $subsystem => $subsystempath) {
             // Look for tests recursively
            if (self::directory_has_tests($subsystempath, $testtype)) {
                $subsystemswithtests[$subsystem] = $subsystempath;
            }
        }
        return $subsystemswithtests;
    }

    /**
     * Returns all the directories having tests
     *
     * @param string $testtype The kind of test we are looking for
     * @return array all directories having tests
     */
    private static function get_all_directories_with_tests($testtype) {
        global $CFG;

        $dirs = array();
        $dirite = new RecursiveDirectoryIterator($CFG->docroot);
        $iteite = new RecursiveIteratorIterator($dirite);
        $regexp = self::get_regexp($testtype);
        $regite = new RegexIterator($iteite, $regexp);
        foreach ($regite as $path => $element) {
            $key = dirname(dirname(dirname($path)));
            $value = trim(str_replace('/', '.', str_replace($CFG->docroot, '', $key)), '.');
            $dirs[$key] = $value;
        }
        ksort($dirs);
        return array_flip($dirs);
    }

    /**
     * Returns if a given directory has tests (recursively)
     *
     * @param string $dir full path to the directory to look for phpunit tests
     * @param string $testtype phpunit|behat
     * @return bool if a given directory has tests (true) or no (false)
     */
    private static function directory_has_tests($dir, $testtype) {
        if (!is_dir($dir)) {
            return false;
        }

        $dirite = new RecursiveDirectoryIterator($dir);
        $iteite = new RecursiveIteratorIterator($dirite);
        $regexp = self::get_regexp($testtype);
        $regite = new RegexIterator($iteite, $regexp);
        $regite->rewind();
        if ($regite->valid()) {
            return true;
        }
        return false;
    }


    /**
     * Returns the regular expression to match by the test files
     * @param string $testtype
     * @return string
     */
    private static function get_regexp($testtype) {

        $sep = preg_quote(DIRECTORY_SEPARATOR, '|');

        switch ($testtype) {
            case 'phpunit':
                $regexp = '|' . $sep . 'tests' . $sep . 'phpunit' . $sep . '.*\.php$|';
                break;
            case 'features':
                $regexp = '|' . $sep . 'tests' . $sep . 'behat' . $sep . '.*\.feature$|';
                break;
            case 'contexts':
                $regexp = '|' . $sep . 'tests' . $sep . 'behat' . $sep . 'Behat.*\.php$|';
                break;
        }

        return $regexp;
    }
}
