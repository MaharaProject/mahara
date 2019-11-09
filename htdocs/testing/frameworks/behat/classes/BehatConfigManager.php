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
 * Utils to set Behat config
 *
 */

defined('INTERNAL') || die();

require_once(dirname(__DIR__) . '/lib.php');
require_once(__DIR__ . '/util.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/TestsFinder.php');

/**
 * Behat configuration manager
 *
 * Creates/updates Behat config files getting tests
 * and steps from Mahara codebase
 *
 */
class BehatConfigManager {

    /**
     * Updates a config file
     *
     * Define test suites for mahara core and plugins
     *
     * Default test suite is
     *  - core_features
     *
     * Each test suite should have
     *  - paths of feature files
     *  - contexts
     *  - filters (for plugin test suite, use tags: pluginname)
     *
     * @return void
     */
    public static function update_config_file() {
        global $CFG;

        $configfilepath = BehatTestingUtil::get_behat_config_path();

        // Get core_features test suite
        $suites = array();

        $core_paths = array(BehatTestingUtil::get_mahararoot() . self::get_behat_tests_path(). DIRECTORY_SEPARATOR . 'features');

        // Optionally include features from additional directories.
        if (!empty($CFG->behat_additionalfeatures)) {
            $core_paths = array_merge($core_paths, array_map("realpath", $CFG->behat_additionalfeatures));
        }
        $core_contexts = array(
            'BehatMaharaCoreContext',
            'BehatHooks',
            'BehatGeneral',
            'BehatNavigation',
            'BehatView',
            'BehatDataGenerators',
            'BehatAccount',
            'BehatAdmin',
            'BehatForms',
        );
        $core_filters = array(
            'tags' => '@core'
        );
        $suites['core_features'] = array(
            'paths'    => $core_paths,
            'contexts' => $core_contexts,
            'filters'  => $core_filters,
        );
        $gherkin_filters = array(
            'tags' => '~@manual'
        );
        $gherkin = array(
            'filters' => $gherkin_filters,
        );
        // Get test suite config for each plugin
        // Gets all the plugins with features and/or contexts.
        $plugins = TestsFinder::get_plugins_with_tests('features');
        if ($plugins) {
            foreach ($plugins as $pluginname => $path) {
                $path = self::clean_path($path) ;
                if (file_exists($path . self::get_behat_tests_path())) {
                    $suites[$pluginname] = array(
                        'paths'    => array($path),
                        'contexts' => array_merge($core_contexts, self::get_plugin_contexts($path)),
                    );
                }

            }
            $features = array_merge($features, array_values($featurespaths));
        }

        // Behat config file specifing the main context class,
        // the required Behat extensions and Mahara test wwwroot.
        $contents = self::get_config_file_contents($suites, $gherkin);

        // Stores the file.
        check_dir_exists(dirname($configfilepath), true, true);
        if (!file_put_contents($configfilepath, $contents)) {
            behat_error(BEHAT_MAHARA_EXITCODE_BADPERMISSIONS, 'File ' . $configfilepath . ' can not be created');
        }

    }

    /**
     * Gets the list contexts for a plugin
     *
     * @param $pluginpath
     * @return array
     */
    public static function get_plugin_contexts($pluginpath) {

        $plugincontexts = array();

        // All Behat*.php inside self::get_behat_tests_path() are added contexts.
        $regite = new RegexIterator(new DirectoryIterator($pluginpath . self::get_behat_tests_path()), '|^Behat.*\.php$|');
        foreach ($regite as $file) {
            $key = $file->getBasename('.php');
            $plugincontexts[] = $key;
        }

        return $plugincontexts;
    }

    /**
     * Generate the Behat config file
     *
     * @param array $suites
     * @return string
     */
    protected static function get_config_file_contents($suites, $gherkin) {
        global $CFG;

        // We require here when we are sure behat dependencies are available.
        require_once(TestingUtil::get_mahararoot() . '/external/vendor/autoload.php');

        // It is possible that it has no value as we don't require a full behat setup to list the step definitions.
        if (empty($CFG->behat_wwwroot)) {
            $CFG->behat_wwwroot = 'http://example.com';
        }

        $basedir = $CFG->docroot . 'testing/frameworks/behat';
        $config = array(
            'default' => array(
                'autoload' => array($basedir . DIRECTORY_SEPARATOR . 'classes'),
                'formatters' => array(
                    'progress' => true,
                    'html' => array(
                      'output_path' => '%paths.base%/html_results/'
                    ),
                ),
                'extensions' => array(
                    'Behat\MinkExtension' => array(
                        'base_url' => $CFG->behat_wwwroot,
                        'files_path' => get_mahararoot_dir() . '/test/behat/upload_files',
                        'javascript_session' => 'selenium2',
                        'selenium2' => array(
                            'browser' => 'chrome',
                            'wd_host' => $CFG->behat_selenium2
                        ),
                        'goutte' => null,
                     ),
                     'emuse\BehatHTMLFormatter\BehatHTMLFormatterExtension' => array(
                       'name' => 'html',
                       'renderer' => 'Twig,Behat2',
                       'file_name' => 'index',
                       'print_args' => 'true',
                       'print_outp' => 'true',
                       'loop_break' => 'true'
                     )
                ),
                'gherkin' => $gherkin,
                'suites' => $suites
            )
        );

        // In case user defined overrides respect them over our default ones.
        if (!empty($CFG->behat_config)) {
            $config = self::merge_config($config, $CFG->behat_config);
        }

        return Symfony\Component\Yaml\Yaml::dump($config, 10, 2);
    }

    /**
     * Overrides default config with local config values
     *
     * array_merge does not merge completely the array's values
     *
     * @param mixed $config The node of the default config
     * @param mixed $localconfig The node of the local config
     * @return mixed The merge result
     */
    protected static function merge_config($config, $localconfig) {

        if (!is_array($config) && !is_array($localconfig)) {
            return $localconfig;
        }

        // Local overrides also deeper default values.
        if (is_array($config) && !is_array($localconfig)) {
            return $localconfig;
        }

        foreach ($localconfig as $key => $value) {

            // If defaults are not as deep as local values let locals override.
            if (!is_array($config)) {
                unset($config);
            }

            // Add the param if it doesn't exists or merge branches.
            if (empty($config[$key])) {
                $config[$key] = $value;
            }
            else {
                $config[$key] = self::merge_config($config[$key], $localconfig[$key]);
            }
        }

        return $config;
    }

    /**
     * Cleans the path returned by get_plugins_with_tests() to standarize it
     *
     * @see TestsFinder::get_all_directories_with_tests() it returns the path including /tests/
     * @param string $path
     * @return string The string without the last /tests part
     */
    protected final static function clean_path($path) {

        $path = rtrim($path, DIRECTORY_SEPARATOR);

        $parttoremove = DIRECTORY_SEPARATOR . 'tests';

        $substr = substr($path, strlen($path) - strlen($parttoremove));
        if ($substr == $parttoremove) {
            $path = substr($path, 0, strlen($path) - strlen($parttoremove));
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * The relative path where plugins stores their behat tests
     *
     * @return string
     */
    protected final static function get_behat_tests_path() {
        return DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'behat';
    }

}
