<?php
/**
 *
 * @package    mahara
 * @subpackage grouptype-standard
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginGrouptypeStandard extends PluginGrouptype {

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            parent::installgrouptype('GroupTypeStandard');
        }
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
