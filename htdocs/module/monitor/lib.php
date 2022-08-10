<?php
/**
 * The main module file.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @author     Ghada El-Zoghbi (ghada@catalyst-au.net)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Module plugin class.
 */
class PluginModuleMonitor extends PluginModule {
    const type_default            = 'processes';
    const type_processes          = 'processes';
    const type_ldaplookup         = 'ldaplookup';
    const type_ldapsuspendedusers = 'ldapsuspendedusers';
    const type_elasticsearch      = 'elasticsearch';
    const type_search             = 'search';

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return get_string('monitor', 'module.monitor');
    }

    /**
     * API-Function: Is the plugin activated or not?
     *
     * @return boolean true, if the plugin is activated, otherwise false.
     */
    public static function is_active() {
        $active = false;
        if ($active = get_field('module_installed', 'active', 'name', 'monitor')) {
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
        return 'monitor';
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
        if (!is_plugin_active('monitor', 'module')) {
            return array();
        }

        $items['adminhome/monitor'] = array(
            'path'   => 'adminhome/monitor',
            'url'    => 'module/monitor/monitor.php',
            'title'  => get_string('monitor', 'module.monitor'),
            'weight' => 40,
        );

        if (defined('MENUITEM') && isset($items[MENUITEM])) {
            $items[MENUITEM]['selected'] = true;
        }

        return $items;
    }

    /**
     * API-Function: does the module has config?
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
        $elements = array(
            'cronlockhours' => array(
                'title' => get_string('cronlockhours', 'module.monitor'),
                'description' => get_string('cronlockhoursdescription', 'module.monitor'),
                'type' => 'text',
                'defaultvalue' => self::get_config_value('cronlockhours'),
                'rules' => array('integer' => true, 'required' => true, 'maxlength' => 2, 'minvalue' => 1),
            ),
            'hourstoconsiderelasticsearchrecordold' => array(
                'title' => get_string('hourstoconsiderelasticsearchrecordold', 'module.monitor'),
                'description' => get_string('hourstoconsiderelasticsearchrecordolddescription', 'module.monitor'),
                'type' => 'text',
                'defaultvalue' => self::get_config_value('hourstoconsiderelasticsearchrecordold'),
                'rules' => array('integer' => true, 'required' => true, 'maxlength' => 2, 'minvalue' => 1),
            ),
            'ldapsuspendeduserspercentage' => array(
                'title' => get_string('ldapsuspendeduserspercentage', 'module.monitor'),
                'description' => get_string('ldapsuspendeduserspercentagedescription', 'module.monitor'),
                'type' => 'text',
                'defaultvalue' => self::get_config_value('ldapsuspendeduserspercentage'),
                'rules' => array('integer' => true, 'required' => true, 'maxlength' => 2, 'minvalue' => 1),
            ),
            'allowedips' => array(
                'title' => get_string('allowedips', 'module.monitor'),
                'description' => get_string('allowedipsdescription', 'module.monitor'),
                'type' => 'textarea',
                'defaultvalue' => self::get_config_value('allowedips'),
                'rows' => 5,
                'cols' => 76,
                'disabled' => in_array('plugin_module_monitor_allowedips', $OVERRIDDEN),
            ),
        );
        // Check Monitor Types for module type specific config.
        $monitor_types = self::get_list_of_types();
        foreach($monitor_types as $type) {
            $class_name = "MonitorType_{$type}";
            require_once(get_config('docroot') . "module/monitor/type/{$class_name}.php");
            if (method_exists($class_name, 'has_config') && $class_name::has_config()) {
                $elements[$class_name] = [
                    'type' => 'fieldset',
                    'legend' => get_string('config' . strtolower($class_name) . 'legend', 'module.monitor'),
                    'elements' => $class_name::config_elements(),
                    'collapsible' => true,
                    'collapsed' => false,
                ];
            }
        }

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
        $value = get_config_plugin('module', 'monitor', $name);
        if (is_null($value)) {
            $value = self::get_default_config_value($name);
        }
        if ($name == 'allowedips') {
            $value = implode("\n", explode(',', $value));
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
            case 'cronlockhours':
                $value = '2';
                break;

            case 'configmonitortype_searchhoursuntilold':
            case 'hourstoconsiderelasticsearchrecordold':
                $value = '1';
                break;

            case 'ldapsuspendeduserspercentage':
                $value = '10';
                break;

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
        set_config_plugin('module', 'monitor', 'cronlockhours', $values['cronlockhours']);
        set_config_plugin('module', 'monitor', 'hourstoconsiderelasticsearchrecordold', $values['hourstoconsiderelasticsearchrecordold']);
        set_config_plugin('module', 'monitor', 'ldapsuspendeduserspercentage', $values['ldapsuspendeduserspercentage']);
        set_config_plugin('module', 'monitor', 'allowedips', implode(',', explode("\n", $values['allowedips'])));
        // Check each Monitor Type for config saving.
        $monitor_types = self::get_list_of_types();
        foreach($monitor_types as $type) {
            $class_name = "MonitorType_{$type}";
            require_once(get_config('docroot') . "module/monitor/type/{$class_name}.php");
            if (method_exists($class_name, 'has_config') && $class_name::has_config()) {
                if (method_exists($class_name, 'save_config_options')) {
                    $class_name::save_config_options($values);
                }
            }
        }
    }

    /**
     * API-Function: post install actions.
     *
     * @param int $prevversion Prev version of the plugin.
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('module', 'monitor', 'cronlockhours', self::get_default_config_value('cronlockhours'));
            set_config_plugin('module', 'monitor', 'hourstoconsiderelasticsearchrecordold', self::get_default_config_value('hourstoconsiderelasticsearchrecordold'));
            set_config_plugin('module', 'monitor', 'ldapsuspendeduserspercentage', self::get_default_config_value('ldapsuspendeduserspercentage'));
        }
        return true;
    }

    /**
     * Return an array of all the types that have supporting plugins installed
     * and therefore can be monitored.
     */
    public static function get_list_of_types() {
        $types = array(self::type_processes);
        if (is_plugin_active('ldap', 'auth')) {
            $total = count_records_sql('SELECT COUNT(*) as Total
                    FROM {auth_instance} ai
                    INNER JOIN {institution} i on ai.institution = i.name
                    WHERE ai.authname = ?
                    AND i.suspended = ?
                    AND (i.expiry > ? OR i.expiry is null)',
                    array('ldap', 0, db_format_timestamp(time())));
            if ($total > 0) {
                $types[] = self::type_ldaplookup;
                $types[] = self::type_ldapsuspendedusers;
            }
        }
        if (is_plugin_active('elasticsearch', 'search')) {
            // The checks for elasticsearch work for version 15.10 and above.
            if (get_config('series') > 15.04) {
                $types[] = self::type_elasticsearch;
            }
        }
        if (does_search_plugin_have('monitor_support')) {
            // The currently selected search plugin has monitor support. Add it
            // to the list of types.
            $types[] = self::type_search;
        }
        return $types;
    }

    public static function check_monitor_access() {
        // Check that if we are hitting a monitor URL via browser then we either need
        // to have the urlsecret present or be on a whitelisted IP
        if (!is_cli() && get_config('urlsecret') !== null) {
            $allowedips = get_config_plugin('module', 'monitor', 'allowedips');
            if ($allowedips && trim($allowedips) != '') {
                require_once(get_config('docroot') . 'webservice/lib.php');
                if (!remoteip_in_list($allowedips)) {
                    $message = get_string('accessdeniednotvalidip', 'module.monitor', getremoteaddr(null));
                    return $message;
                }
            }
            else {
                $urlsecret = param_alphanumext('urlsecret', -1);
                if ($urlsecret !== get_config('urlsecret')) {
                    $message = get_string('accessdeniednourlsecret', 'error');
                    return $message;
                }
            }
        }
        return false;
    }
}

abstract class MonitorType {
    /**
     * Prepare the list of data to be displayed on the screen.
     *
     * @param array $data - result from general query.
     * @param int $limi - for pagination
     * @param int $offset - for pgination
     * @return array $data - results set.
     */
    abstract public static function format_for_display($data, $limit, $offset);

    abstract public static function format_for_display_table($data, $limit, $offset);
}
