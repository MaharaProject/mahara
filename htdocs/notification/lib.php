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

    public abstract static function notify_user($user, $data);

    public static function can_be_disabled() {
        return false;
    }
}
