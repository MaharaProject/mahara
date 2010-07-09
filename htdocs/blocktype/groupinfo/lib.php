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
 * @subpackage blocktype-groupinfo
 * @author     Liip AG
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010 Liip AG, http://www.liip.ch
 *
 */

defined('INTERNAL') || die();

require_once('group.php');
safe_require('artefact', 'file');
class PluginBlocktypeGroupInfo extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.groupinfo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.groupinfo');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('grouphomepage');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $groupid = $instance->get_view()->get('group');
        if (!$groupid) {
            return '';
        }

        $data = self::get_data($groupid);

        $dwoo = smarty_core();
        $dwoo->assign('group', $data['group']);
        $dwoo->assign('groupid', $data['group']->id);
        $dwoo->assign('membercount', count_records('group_member', 'group', $data['group']->id));
        $dwoo->assign('viewcount', count_records('view', 'group', $data['group']->id));
        $dwoo->assign('filecount', $data['filecounts']->files);
        $dwoo->assign('foldercount', $data['filecounts']->folders);

        return $dwoo->fetch('blocktype:groupinfo:groupinfo.tpl');
    }

    public static function has_instance_config() {
        return false;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    protected static function get_data($groupid) {
        global $USER;

        if(!defined('GROUP')) {
            define('GROUP', $groupid);
        }
        // get the currently requested group
        $group = group_current_group();

        $group->ctime = strftime(get_string('strftimedate'), $group->ctime);
        // if the user isn't logged in an the group isn't public don't show anything
        if (!is_logged_in() && !$group->public) {
            throw new AccessDeniedException();
        }

        // find the group administrators
        $group->admins = get_column_sql("SELECT \"member\"
            FROM {group_member}
            WHERE \"group\" = ?
            AND \"role\" = 'admin'", array($group->id));

        $role = group_user_access($group->id);
        $group->role = $role;

        // logged in user can do stuff
        if (is_logged_in()) {
            $afterjoin = param_variable('next', 'view');
            if ($role) {
                if ($role == 'admin') {
                    $group->membershiptype = 'admin';
                    $group->requests = count_records('group_member_request', 'group', $group->id);
                }
                else {
                    $group->membershiptype = 'member';
                }
                $group->canleave = group_user_can_leave($group->id);
            }
            else if ($group->jointype == 'invite'
                and $invite = get_record('group_member_invite', 'group', $group->id, 'member', $USER->get('id'))) {
                    $group->membershiptype = 'invite';
                    $group->invite = group_get_accept_form('invite', $group->id, $afterjoin);
                }
            else if ($group->jointype == 'request'
                and $request = get_record('group_member_request', 'group', $group->id, 'member', $USER->get('id'))) {
                    $group->membershiptype = 'request';
                }
            else if ($group->jointype == 'open') {
                $group->groupjoin = group_get_join_form('joingroup', $group->id, $afterjoin);
            }
        }
        $group->settingsdescription = group_display_settings($group);

        $filecounts = ArtefactTypeFileBase::count_user_files(null, $group->id, null);

        return array('group'=>$group, 'filecounts'=>$filecounts);
    }
}

?>
