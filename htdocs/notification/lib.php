<?php
/**
 *
 * @package    mahara
 * @subpackage notification
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

abstract class PluginNotification extends Plugin {

    public static function get_plugintype_name() {
        return 'notification';
    }

    /**
     * Indicates if a plugin uses data from the activity table. If an activity type implements ActivityStreamable
     * then users can choose any notification methods that use the activity table (as well as the other methods).
     */
    public static function uses_activity_table() {
        return false;
    }

    public static function notify_user($user, $data) {
    }

    public static function can_be_disabled() {
        return false;
    }
}
