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

    private static $default_numtoshow = 12;

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

        $configdata = $instance->get('configdata');
        $rows = isset($configdata['rows']) ? $configdata['rows'] : 1;
        $columns = isset($configdata['columns']) ? $configdata['columns'] : 6;
        $order = isset($configdata['order']) ? $configdata['order'] : 'latest';
        $numtoshow = isset($configdata['numtoshow']) ? $configdata['numtoshow'] : $rows * $columns;

        $groupid = $instance->get_view()->get('group');
        require_once('searchlib.php');
        $groupmembers = get_group_user_search_results($groupid, '', 0, $numtoshow, '', $order);

        if ($groupmembers['count']) {
            $smarty = smarty_core();
            $smarty->assign_by_ref('groupmembers', $groupmembers['data']);
            $groupmembers['tablerows'] = $smarty->fetch('blocktype:groupmembers:row.tpl');
        } else {
            $groupmembers = false;
        }

        $show_all = array(
            'url' => get_config('wwwroot') . 'group/members.php?id=' . $groupid,
            'message' => get_string('show_all', 'blocktype.groupmembers')
            );

        $smarty = smarty_core();
        $smarty->assign('groupmembers', $groupmembers);
        $smarty->assign('show_all', $show_all);

        return $smarty->fetch('blocktype:groupmembers:groupmembers.tpl');

    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $options = range(0,20);
        unset($options[0]);

        return array(
            'numtoshow' => array(
                'type' => 'select',
                'title' => get_string('options_numtoshow_title', 'blocktype.groupmembers'),
                'description' => get_string('options_numtoshow_desc', 'blocktype.groupmembers'),
                'defaultvalue' => !empty($configdata['numtoshow']) ? $configdata['numtoshow'] : self::$default_numtoshow,
                'options' => $options,
            ),
            'order' => array(
                'type'  => 'select',
                'title' => get_string('options_order_title', 'blocktype.groupmembers'),
                'description' => get_string('options_order_desc', 'blocktype.groupmembers'),
                'defaultvalue' => !empty($configdata['order']) ? $configdata['order'] : 'latest',
                'options' => array(
                    'latest' => get_string('Latest','blocktype.groupmembers'),
                    'random' => get_string('Random','blocktype.groupmembers'),
                ),
            ),
        );
    }

    public static function get_instance_title () {
        return get_string('Members', 'group');
    }
}