<?php
/**
 * The main module file.
 *
 * @package    mahara
 * @subpackage module_beacon
 * @author     Fergus Whyte (fergusw@catalyst.net.nz)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();


/**
 * Module plugin class.
 */
class PluginModuleBeacon extends PluginModule {

    /**
     * API-Function: Is the plugin activated or not?
     *
     * @return boolean true, if the plugin is activated, otherwise false.
     */
    public static function is_active() {
        $active = false;
        if ($active = get_field('module_installed', 'active', 'name', 'beacon')) {
            $active = (bool)$active;
        }
        return $active;
    }

    /**
     * API-Function: get the Plugin ShortName
     *
     * @return string ShortName of the plugin
     */
    public static function get_plugin_name() {
        return 'beacon';
    }

    /**
     * API-Function: Prevent disabling the module.
     */
    public static function can_be_disabled() {
        return true;
    }

    /**
     * API-Function: Add a menu item for site admin users.
     */
    public static function admin_menu_items() {
        return array();
    }

    /**
     * APIhtdocs/auth/saml/lib.php-Function: does the module has config?
     *
     * @return bool true.
     */
    public static function has_config() {
        return true;
    }

    /**
     * API-Function: Return a list of config options.
     *
     * @return array A list of config options.
     */
    public static function get_config_options() {
        global $OVERRIDDEN;
        if( $secretkeydisabled = in_array('plugin_module_beacon_fleettrackersecretkey', $OVERRIDDEN) ) {
            $secretdefault = get_string('keysetbyserver', 'module.beacon');
        } else {
            $secretdefault = self::get_config_value('fleettrackersecretkey');
        }
        $elements = array(
            'fleettrackerurl' => array(
                'title' => get_string('fleettrackerurl', 'module.beacon'),
                'description' => get_string('fleettrackerurldescription', 'module.beacon'),
                'type' => 'text',
                'defaultvalue' => self::get_config_value('fleettrackerurl'),
                'rules' => array('required' => true),
                'disabled' => in_array('plugin_module_beacon_fleettrackerurl', $OVERRIDDEN)
            ),
            'fleettrackersecretkey' => array(
                'title' => get_string('fleettrackersecretkey', 'module.beacon'),
                'description' => get_string('fleettrackersecretkeydescription', 'module.beacon'),
                'type' => 'text',
                'defaultvalue' => $secretdefault,
                'rules' => array('required' => true ),
                'disabled' => $secretkeydisabled
            )
        );
        return array('elements' => $elements);
    }

    /**
     * Return configuration value.
     *
     * @param string $name A name of the config parameter.
     *
     * @return mixed|void A value for the parameter.
     */
    public static function get_config_value($name) {
        $value = get_config_plugin('module', 'beacon', $name);
        if (is_null($value)) {
            $value = self::get_default_config_value($name);
        }
        return $value;
    }

    /**
     * Return default configuration value.
     *
     * @param string $name A name of the config parameter.
     *
     * @return mixed|void A default value for the parameter or empty string.
     */
    protected static function get_default_config_value($name) {
        switch ($name) {
            default:
                $value = '';
                break;
        }
        return $value;
    }

    /**
     * API-Function: Save configuration parameters.
     *
     * @param \Pieform $form Submitted form object.
     * @param array $values A list of submitted values.
     */
    public static function save_config_options(Pieform $form, $values) {
        set_config_plugin('module', 'beacon', 'fleettrackerurl', $values['fleettrackerurl']);
        set_config_plugin('module', 'beacon', 'fleettrackersecretkey', $values['fleettrackersecretkey']);
    }

    /**
     * API-Function: post install actions.
     *
     * @param int $prevversion Prev version of the plugin.
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('module', 'beacon', 'fleettrackerurl', self::get_default_config_value('fleettrackerurl'));
            set_config_plugin('module', 'beacon', 'fleettrackersecretkey', self::get_default_config_value('fleettrackersecretkey'));
        }
    }

    /**
     * Executes "Beacon" process.
     * This requests questions from a "fleettracker" then answers those questions.
     * @return true|void
     * @throws SQLException
     */
    public static function beacon_cron() {
        require_once(get_config('docroot').'module/beacon/classes/processor.php');
        $beaconbaseurl = get_config_plugin('module','beacon', 'fleettrackerurl');
        $secretkey = get_config_plugin('module', 'beacon', 'fleettrackersecretkey');
        if (!empty($beaconbaseurl) && !empty($secretkey)) {
            $processor = new module_beacon\processor($beaconbaseurl , $secretkey);
            $success = $processor->execute();
            if ($success) {
                log_debug('module_beacon: question answers successfully beaconed');
            } else {
                log_debug('module_beacon: question processing failed.');
            }
        } else {
            log_debug("Skipping Beacon cron, config not set.");
            return true;
        }


    }
    /**
     * API-Function: Returns cron requirements.
     * @return array
     */
    public static function get_cron()
    {
        return array( (object)array(
            'callfunction' => 'beacon_cron',
            'hour' => '*',
            'minute' => '5'
        ));
    }

}
