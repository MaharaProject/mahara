<?php
/**
 * @package    mahara
 * @subpackage module
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

defined('INTERNAL') || die();

/**
 * The "Module" plugin type is a generic plugin type for plugins that don't fit into
 * any of the other plugin types. It allows for encapsulation, as well as access to the
 * standard plugin APIs.
 */
abstract class PluginModule extends Plugin {
    public static function get_plugintype_name() {
        return 'module';
    }

    /**
     * Run initialisation code
     */
    public static function bootstrap() {
    }

    /**
     * This function returns an array of menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function menu_items() {
        return array();
    }

    /**
     * This function returns an array of menu items
     * to be displayed in the top right navigation menu
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function right_nav_menu_items() {
        return array();
    }

    /**
     * This function returns an array of admin menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function admin_menu_items() {
        return array();
    }

    /**
     * This function returns an array of institution menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function institution_menu_items() {
        return array();
    }

    /**
     * This function returns an array of institution staff menu items
     * to be displayed
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function institution_staff_menu_items() {
        return array();
    }
}
