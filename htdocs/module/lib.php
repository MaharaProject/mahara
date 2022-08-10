<?php
/**
 * The Plugin class for custom plugins
 *
 * @package    mahara
 * @subpackage module
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

defined('INTERNAL') || die();

/**
 * Provides the module plugin class for custom plugins
 *
 * The "Module" plugin type is a generic plugin type for plugins that don't fit into
 * any of the other plugin types. It allows for encapsulation, as well as access to the
 * standard plugin APIs.
 */
abstract class PluginModule extends Plugin {

    /**
     * {@inheritDoc}
     **/
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

    /**
     * This function returns an array of menu items to be displayed
     * on a group page when viewed by group members.
     * Each item should be a stdClass() object containing -
     * - title language pack key
     * - url relative to wwwroot
     * @param   int $groupid    The id of the group in the group table
     * @param   string  $role   The group membership role of the logged in user
     * @return array
     */
    public static function group_tabs($groupid, $role) {
        return array();
    }
}
