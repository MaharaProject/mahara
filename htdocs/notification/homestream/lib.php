<?php
/**
 *
 * @package    mahara
 * @subpackage notification-homestream
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginNotificationHomestream extends PluginNotification {

    static $userdata = array('activitystream');

    /**
     * Indicates if a plugin uses data from the activity table. If an activity type implements ActivityStreamable
     * then users can choose any notification methods that use the activity table (as well as the other methods).
     */
    public static function uses_activity_table() {
        return true;
    }

}
