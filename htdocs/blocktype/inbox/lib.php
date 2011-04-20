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
 * @subpackage blocktype-inbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeInbox extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.inbox');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.inbox');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        $configdata = $instance->get('configdata');

        $desiredtypes = array();
        foreach($configdata as $k => $v) {
            if (!empty($v) && $k != 'maxitems') {
                $type = preg_replace('/[^a-z]+/', '', $k);
                $desiredtypes[$type] = $type;
            }
        }

        if ($USER->get('admin') && !empty($desiredtypes['adminmessages'])) {
            unset($desiredtypes['adminmessages']);
            $desiredtypes += get_column('activity_type', 'name', 'admin', 1);
        }

        $maxitems = isset($configdata['maxitems']) ? $configdata['maxitems'] : 5;

        $records = array();
        if ($desiredtypes) {
            $sql = "
                SELECT n.id, n.subject, n.message, n.url, n.urltext, n.read, t.name AS type
                FROM {notification_internal_activity} n JOIN {activity_type} t ON n.type = t.id
                WHERE n.usr = ?
                AND t.name IN (" . join(',', array_map('db_quote', $desiredtypes)) . ")
                ORDER BY n.ctime DESC
                LIMIT ?;";

            $records = get_records_sql_array($sql, array(
                $USER->get('id'),
                $maxitems + 1 // Hack to decide whether to show the More... link
            ));
        }

        // Hack to decide whether to show the More... link
        if ($showmore = count($records) > $maxitems) {
            unset($records[$maxitems]);
        }

        if ($records) {
            require_once('activity.php');
            foreach ($records as &$r) {
                $r->message = format_notification_whitespace($r->message, $r->type);
            }
        }

        $smarty = smarty_core();
        if ($showmore) {
            $smarty->assign('desiredtypes', implode(',', $desiredtypes));
        }
        $smarty->assign('blockid', 'blockinstance_' . $instance->get('id'));
        $smarty->assign('items', $records);
        return $smarty->fetch('blocktype:inbox:inbox.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        $configdata = $instance->get('configdata');

        $types = get_records_array('activity_type', 'admin', 0, 'plugintype,pluginname,name', 'name,plugintype,pluginname');
        if ($USER->get('admin')) {
            $types[] = (object)array('name' => 'adminmessages');
        }

        $elements = array();
        $elements['types'] = array(
            'type' => 'fieldset',
            'legend' => get_string('messagetypes', 'blocktype.inbox'),
            'elements' => array(),
        );
        foreach($types as $type) {
            if (!empty($type->plugintype)) {
                $title = get_string('type' . $type->name, $type->plugintype . '.' . $type->pluginname);
            }
            else {
                $title = get_string('type' . $type->name, 'activity');
            }
            $elements['types']['elements'][$type->name] = array(
                'type' => 'checkbox',
                'title' => $title,
                'defaultvalue' => isset($configdata[$type->name]) ? $configdata[$type->name] : 0,
            );
        }
        $elements['maxitems'] = array(
            'type' => 'text',
            'title' => get_string('maxitems', 'blocktype.inbox'),
            'description' => get_string('maxitemsdescription', 'blocktype.inbox'),
            'defaultvalue' => isset($configdata['maxitems']) ? $configdata['maxitems'] : 5,
            'rules' => array(
                'minvalue' => 1,
                'maxvalue' => 100,
            ),
        );

        return $elements;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Inbox only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    /**
     * We need a default title for this block, so that the inbox blocks
     * on the dashboard are translatable.
     *
     * To maintain existing behaviour, use the 'recentactivity' string unless
     * the block has only got forum post notifications in it, in which case
     * use 'topicsimfollowing'
     */
    public static function get_instance_title(BlockInstance $instance) {
        if ($configdata = $instance->get('configdata')) {
            foreach ($configdata as $k => $v) {
                if ($v && $k != 'newpost' && $k != 'maxitems') {
                    return get_string('recentactivity');
                }
            }
            if ($configdata['newpost']) {
                return get_string('topicsimfollowing');
            }
        }
        return get_string('recentactivity');
    }

}
