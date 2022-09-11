<?php
/**
 *
 * @package    mahara
 * @subpackage grouptype-standard
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginGrouptypeStandard extends PluginGrouptype {

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return get_string('name', 'grouptype.standard');
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            parent::installgrouptype('GroupTypeStandard');
        }
        return true;
    }

    public static function can_be_disabled() {
        return false;
    }

}

class GroupTypeStandard extends GroupType {

    public static function allowed_join_types($all=false) {
        global $USER;
        return self::user_allowed_join_types($USER, $all);
    }

    public static function user_allowed_join_types($user, $all=false) {
        $jointypes = array('open', 'request', 'invite');
        if (defined('INSTALLER') || defined('CRON') || $all || $user->get('admin') || $user->get('staff') || $user->is_institutional_admin() || $user->is_institutional_staff()) {
           $jointypes[] = 'controlled';
        }
        return $jointypes;
    }

    public static function get_roles() {
        return array('member', 'admin');
    }

    public static function get_view_moderating_roles() {
        return array('admin');
    }

    public static function get_view_assessing_roles() {
        return array('admin');
    }

    public static function default_role() {
        return 'member';
    }

    public static function default_artefact_rolepermissions() {
        return array(
            'member' => (object) array('view' => true, 'edit' => true, 'republish' => true),
            'admin'  => (object) array('view' => true, 'edit' => true, 'republish' => true),
        );
    }

}
