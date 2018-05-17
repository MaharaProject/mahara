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

abstract class PluginGrouptype extends Plugin {

    public static function get_plugintype_name() {
        return 'grouptype';
    }

    public static function installgrouptype($type) {
        $grouptype = new $type();
        $grouptype->install();
    }

}

/**
 * Helper interface to hold GroupType's abstract static methods
 */
interface IGroupType {
    public static function allowed_join_types();

    public static function user_allowed_join_types($user);

    /**
     * Returns the roles this group type implements
     */
    public static function get_roles();

    public static function get_view_moderating_roles();

    public static function get_view_assessing_roles();

    public static function default_artefact_rolepermissions();
}

abstract class GroupType implements IGroupType {

    public function install() {

        $classname = get_class($this);
        $type = strtolower(substr($classname, strlen('GroupType')));

        // These tables may already be populated if the site is being
        // upgraded from before grouptypes became plugins, so check
        // before inserting these records.
        if (record_exists('grouptype', 'name', $type)) {
            return;
        }

        insert_record('grouptype', (object) array(
            'name' => $type,
            'defaultrole' => $this->default_role(),
        ));
        $roles = $this->get_roles();
        if (!in_array('admin', $roles)) {
            $roles[] = 'admin';
        }
        $assessingroles = $this->get_view_assessing_roles();
        foreach ($roles as $r) {
            insert_record('grouptype_roles', (object) array(
                'grouptype' => $type,
                'role' => $r,
                'see_submitted_views' => (int)in_array($r, $assessingroles),
            ));
        }
    }

    /**
     * Returns whether the currently logged in user can create a group of this
     * grouptype
     */
    public static function can_be_created_by_user() {
        return true;
    }

    /**
     * Returns whether a user can be promoted to admin of a group of this
     * grouptype (by an existing group admin, on the 'change role' page)
     */
    public static function can_become_admin($userid) {
        return true;
    }
}
