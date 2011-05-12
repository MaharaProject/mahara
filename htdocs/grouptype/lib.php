<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage notification
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

abstract class PluginGrouptype extends Plugin {

    public static function installgrouptype($type) {
        $grouptype = new $type();
        $grouptype->install();
    }

}

/**
 * Where is the syntax error?
 */
abstract class GroupType {

    public function install() {

        $classname = get_class($this);
        $type = strtolower(substr($classname, strlen('GroupType')));

        // These tables may already be populated if the site is being
        // upgraded from before grouptypes became plugins, so check
        // before inserting these records.
        if (record_exists('grouptype', 'name', $type)) {
            return;
        }

        $assessingroles = $this->get_view_assessing_roles();
        insert_record('grouptype', (object) array(
            'name' => $type,
            'submittableto' => (int)!empty($assessingroles),
            'defaultrole' => $this->default_role(),
        ));
        $roles = $this->get_roles();
        if (!in_array('admin', $roles)) {
            $roles[] = 'admin';
        }
        $editingroles = $this->get_view_editing_roles();
        foreach ($roles as $r) {
            insert_record('grouptype_roles', (object) array(
                'grouptype' => $type,
                'role' => $r,
                'edit_views' => (int)in_array($r, $editingroles),
                'see_submitted_views' => (int)in_array($r, $assessingroles),
            ));
        }
    }

    public static abstract function allowed_join_types();

    public static abstract function user_allowed_join_types($user);
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

    /**
     * Returns the roles this group type implements
     */
    public static abstract function get_roles();

    public static abstract function get_view_editing_roles();

    public static abstract function get_view_assessing_roles();

    public static function get_group_artefact_plugins() {
        return array('file');
    }

    public static abstract function default_artefact_rolepermissions();
}
