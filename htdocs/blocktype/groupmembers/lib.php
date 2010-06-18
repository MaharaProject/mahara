<?php
/**
* Mahara: Electronic portfolio, weblog, resume builder and social networking
* Copyright (C) 2006-2009 Liip AG, and others; see:
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
* @subpackage blocktype-groupmembers
* @author     Liip AG
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 2006-2009 Liip AG, http://liip.ch
*
*/

defined('INTERNAL') || die();

class PluginBlocktypeGroupMembers extends SystemBlocktype {

    public static function get_title () {
        return get_string('title', 'blocktype.groupmembers');
    }

    public static function get_description () {
        return get_string('description', 'blocktype.groupmembers');
    }

    public static function single_only () {
        return true;
    }

    public static function get_categories () {
        return array('general');
    }

    public static function get_viewtypes () {
        return array('grouphomepage');
    }

    public static function render_instance (BlockInstance $instance, $editing = false) {
        global $USER;
        $groupid = $instance->get_view()->get('group');
        require_once('searchlib.php');
        $groupmembers = get_group_user_search_results($groupid, '', 0, 16, '');

        if ($groupmembers['count']) {
            $friendarray = array_chunk($groupmembers['data'], 4); // get the friends into a 4x4 array
            $smarty = smarty_core();
            $smarty->assign_by_ref('friends', $friendarray);
            $groupmembers['tablerows'] = $smarty->fetch('blocktype:groupmembers:row.tpl');
        } else {
            $groupmembers = false;
        }

        $show_all = array(
            'url' => get_config('wwwroot') . 'group/members.php?id=' . $groupid,
            'message' => get_string('show_all', 'blocktype.groupmembers')
            );

        $smarty = smarty_core();
        $smarty->assign('friends', $groupmembers);
        $smarty->assign('show_all', $show_all);

        return $smarty->fetch('blocktype:groupmembers:groupmembers.tpl');

    }

}