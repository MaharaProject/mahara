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
require_once(__DIR__ . '/BehatCommand.php');
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
     * The tests runner and the steps definitions list uses different
     * config files to avoid problems with concurrent executions.
     *
     * The steps definitions list can be filtered by plugin so it's
     * behat.yml is different from the $CFG->docroot one.
     *
     * @param  string $plugin Restricts the obtained steps definitions to the specified plugin
     * @param  string $testsrunner If the config file will be used to run tests
     * @return void
     */
    public static function update_config_file($plugin = '', $testsrunner = true) {
        global $CFG;

        // Behat must have a separate behat.yml to have access to the whole set of features and steps definitions.
        if ($testsrunner === true) {
            $configfilepath = BehatCommand::get_behat_dir() . '/behat.yml';
        }
        else {
            // Alternative for steps definitions filtering, one for each user.
            $configfilepath = self::get_steps_list_config_filepath();
        }

        // Get core features
        $features = array(dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/test/behat/features');
        // Gets all the plugins with features.
        $plugins = TestsFinder::get_plugins_with_tests('features');
        if ($plugins) {
            foreach ($plugins as $pluginname => $path) {
                $path = self::clean_path($path) . self::get_behat_tests_path();
                if (empty($featurespaths[$path]) && file_exists($path)) {

                    // Standarizes separator (some dirs. comes with OS-dependant separator).
                    $uniquekey = str_replace('\\', '/', $path);
                    $featurespaths[$uniquekey] = $path;
                }
            }
            $features = array_merge($features, array_values($featurespaths));
        }

        // Optionally include features from additional directories.
        if (!empty($CFG->behat_additionalfeatures)) {
            $features = array_merge($features, array_map("realpath", $CFG->behat_additionalfeatures));
        }

        $stepsdefinitions = array();
        // Find step definitions from core. They must be in the folder $MAHARA_ROOT/test/behat/stepdefinitions
        // The file name must be /^Behat[A-z0-9_]+\.php$/
        $regite = new RegexIterator(new DirectoryIterator(get_mahararoot_dir() . '/test/behat/stepdefinitions'), '|^Behat[A-z0-9_\-]+\.php$|');
        foreach ($regite as $file) {
            $key = $file->getBasename('.php');
            $stepsdefinitions[$key] = $file->getPathname();
        }

        // Gets all the plugins with steps definitions.
        $steps = self::get_plugins_steps_definitions();
        if ($steps) {
            foreach ($steps as $key => $filepath) {
                if ($plugin === '' || $plugin === $key) {
                    $stepsdefinitions[$key] = $filepath;
                }
            }
        }

        // We don't want the deprecated steps definitions here.
        if (!$testsrunner) {
            unset($stepsdefinitions['behat_deprecated']);
        }

        // Behat config file specifing the main context class,
        // the required Behat extensions and Mahara test wwwroot.
        $contents = self::get_config_file_contents($features, $stepsdefinitions);

        // Stores the file.
        if (!file_put_contents($configfilepath, $contents)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, 'File ' . $configfilepath . ' can not be created');
        }

    }

    /**
     * Gets the list of Mahara steps definitions
     *
     * Class name as a key and the filepath as value
     *
     * Externalized from update_config_file() to use
     * it from the steps definitions web interface
     *
     * @return array
     */
    public static function get_plugins_steps_definitions() {

        $plugins = TestsFinder::get_plugins_with_tests('stepsdefinitions');
        if (!$plugins) {
            return false;
        }

        $stepsdefinitions = array();
        // Find step definitions from plugins
        foreach ($plugins as $pluginname => $pluginpath) {
            $pluginpath = self::clean_path($pluginpath);

            if (!file_exists($pluginpath . self::get_behat_tests_path())) {
                continue;
            }
            $diriterator = new DirectoryIterator($pluginpath . self::get_behat_tests_path());
            $regite = new RegexIterator($diriterator, '|^Behat.*\.php$|');

            // All Behat*.php inside BehatConfigManager::get_behat_tests_path() are added as steps definitions files.
            foreach ($regite as $file) {
                $key = $file->getBasename('.php');
                $stepsdefinitions[$key] = $file->getPathname();
            }
        }

        return $stepsdefinitions;
    }

    /**
     * Returns the behat config file path used by the steps definition list
     *
     * @return string
     */
    public static function get_steps_list_config_filepath() {
        global $USER;

        // We don't cygwin-it as it is called using exec() which uses cmd.exe.
        $userdir = behat_command::get_behat_dir() . '/users/' . $USER->id;
        make_writable_directory($userdir);

        return $userdir . '/behat.yml';
    }

    /**
     * Returns the behat config file path used by the behat cli command.
     *
     * @return string
     */
    public static function get_behat_cli_config_filepath() {
        global $CFG;

        $command = $CFG->behat_dataroot . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR . 'behat.yml';

        return $command;
    }

    /**
     * Behat config file specifing the main context class,
     * the required Behat extensions and Mahara test wwwroot.
     *
     * @param array $features The system feature files
     * @param array $stepsdefinitions The system steps definitions
     * @return string
     */
    protected static function get_config_file_contents($features, $stepsdefinitions) {
        global $CFG;

        // We require here when we are sure behat dependencies are available.
        require_once($CFG->docroot . '../external/vendor/autoload.php');

        // It is possible that it has no value as we don't require a full behat setup to list the step definitions.
        if (empty($CFG->behat_wwwroot)) {
            $CFG->behat_wwwroot = 'http://example.com';
        }

        $basedir = $CFG->docroot . 'testing' . DIRECTORY_SEPARATOR . 'frameworks' . DIRECTORY_SEPARATOR . 'behat';
        $config = array(
            'default' => array(
                'paths' => array(
                    'features' => $basedir . DIRECTORY_SEPARATOR . 'features',
                    'bootstrap' => $basedir . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . 'bootstrap',
                ),
                'context' => array(
                    'class' => 'BehatMaharaInitContext'
                ),
                'extensions' => array(
                    'Behat\MinkExtension\Extension' => array(
                        'base_url' => $CFG->behat_wwwroot,
                        'files_path' => get_mahararoot_dir() . '/test/behat/upload_files',
                        'goutte' => null,
                        'selenium2' => null
                     ),
                    $basedir . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'MaharaExtension.php' => array(
                        'formatters' => array(
                            'mahara_progress' => 'MaharaProgressFormatter'
                        ),
                        'features' => $features,
                        'steps_definitions' => $stepsdefinitions
                    )
                ),
                'formatter' => array(
                    'name' => 'mahara_progress'
                )
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
        return DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'behat';
    }

}
