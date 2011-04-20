<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Nigel McNie (http://nigel.mcnie.name/)
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
 * @subpackage blocktype-recentforumposts
 * @author     Nigel McNie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Nigel McNie http://nigel.mcnie.name/
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeRecentForumPosts extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.recentforumposts');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.recentforumposts');
    }

    public static function get_categories() {
        return array('general');
    }

    private static function get_group(BlockInstance $instance) {
        static $groups = array();

        $block = $instance->get('id');

        if (!isset($groups[$block])) {

            // When this block is in a group view it should always display the
            // forum posts from that group

            $groupid = $instance->get_view()->get('group');
            $configdata = $instance->get('configdata');

            if (!$groupid && !empty($configdata['groupid'])) {
                $groupid = intval($configdata['groupid']);
            }

            if ($groupid) {
                $groups[$block] = get_record_select('group', 'id = ? AND deleted = 0', array($groupid), '*, ' . db_format_tsfield('ctime'));
            }
            else {
                $groups[$block] = false;
            }
        }

        return $groups[$block];
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        if ($group = self::get_group($instance)) {

            require_once('group.php');
            $role = group_user_access($group->id);

            if ($role || $group->public) {
                $limit = 5;
                $configdata = $instance->get('configdata');
                if (!empty($configdata['limit'])) {
                    $limit = intval($configdata['limit']);
                }

                $foruminfo = get_records_sql_array('
                    SELECT
                        p.id, p.subject, p.body, p.poster, p.topic, t.forum, pt.subject AS topicname
                    FROM
                        {interaction_forum_post} p
                        INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic)
                        INNER JOIN {interaction_instance} i ON (i.id = t.forum)
                        INNER JOIN {interaction_forum_post} pt ON (pt.topic = p.topic AND pt.parent IS NULL)
                    WHERE
                        i.group = ?
                        AND i.deleted = 0
                        AND t.deleted = 0
                        AND p.deleted = 0
                    ORDER BY
                        p.ctime DESC',
                    array($group->id), 0, $limit
                );

                $smarty = smarty_core();
                $smarty->assign('group', $group);
                $smarty->assign('foruminfo', $foruminfo);
                if ($instance->get_view()->get('type') == 'grouphomepage') {
                    return $smarty->fetch('blocktype:recentforumposts:latestforumposts.tpl');
                }
                return $smarty->fetch('blocktype:recentforumposts:recentforumposts.tpl');
            }
        }

        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;

        $elements   = array();
        $groupid    = $instance->get_view()->get('group');
        $configdata = $instance->get('configdata');

        if ($groupid || $instance->get_view()->get('institution')) {
            // This block will show recent forum posts from this group
            $elements['groupid'] = array(
                'type' => 'hidden',
                'value' => $groupid,
            );
        }
        else {
            // Allow the user to choose a group they're in to show posts for
            if (!empty($configdata['groupid'])) {
                $groupid = intval($configdata['groupid']);
                $group = get_record_select('group', 'id = ? AND deleted = 0', array($groupid), '*, ' . db_format_tsfield('ctime'));
            }

            $usergroups = get_records_sql_array(
                "SELECT g.id, g.name
                FROM {group} g
                JOIN {group_member} gm ON (gm.group = g.id)
                WHERE gm.member = ?
                AND g.deleted = 0
                ORDER BY g.name", array($USER->get('id')));

            if ($usergroups) {
                $choosablegroups = array();
                foreach ($usergroups as $group) {
                    $choosablegroups[$group->id] = $group->name;
                }
                $elements['groupid'] =  array(
                    'type'  => 'select',
                    'title' => get_string('group', 'blocktype.recentforumposts'),
                    'options' => $choosablegroups,
                    'collapseifoneoption' => false,
                    'defaultvalue' => $groupid,
                    'rules' => array(
                        'required' => true,
                    ),
                );
            }
        }

        if (isset($elements['groupid'])) {
            $elements['limit'] = array(
                'type' => 'text',
                'title' => get_string('poststoshow', 'blocktype.recentforumposts'),
                'description' => get_string('poststoshowdescription', 'blocktype.recentforumposts'),
                'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
                'size' => 3,
                'minvalue' => 1,
                'maxvalue' => 100,
            );
        }
        else {
            $elements = array(
                'whoops' => array(
                    'type' => 'html',
                    'value' => '<p class="noartefacts">' . get_string('nogroupstochoosefrom', 'blocktype.recentforumposts') . '</p>',
                ),
            );
        }

        return $elements;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function feed_url(BlockInstance $instance) {
        if ($group = self::get_group($instance)) {
            if ($group->public) {
                return get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
            }
        }
    }

    public static function get_instance_title(BlockInstance $instance) {
        if ($instance->get_view()->get('type') == 'grouphomepage') {
            return get_string('latestforumposts', 'interaction.forum');
        }
        return get_string('title', 'blocktype.recentforumposts');
    }
}
