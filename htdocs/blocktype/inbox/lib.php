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

        $types = get_records_assoc('activity_type', 'admin', 0, '', 'id,name,plugintype,pluginname');

        $desiredtypes = array();
        foreach($types as $type) {
            if ($configdata[$type->name]) {
                $desiredtypes[] = $type->id;
            }
        }
        if ($USER->get('admin') && $configdata['adminmessages']) {
            $admintypes = get_records_assoc('activity_type', 'admin', 1, '', 'id,name,plugintype,pluginname');
            $types += $admintypes;
            foreach($admintypes as $type) {
                $desiredtypes[] = $type->id;
            }
        }

        $sql = "
            SELECT *
            FROM {notification_internal_activity} n
            WHERE n.usr = ?
            AND n.type IN (" . implode(',', $desiredtypes) . ")
            ORDER BY n.ctime DESC
            LIMIT ?;";

        $records = array();
        if ($desiredtypes) {
            $records = get_records_sql_array($sql, array(
                $USER->get('id'),
                $configdata['maxitems']
            ));
        }

        $items = array();
        if ($records) {
            foreach($records as $record) {
                $items[] = array(
                    'subject' => $record->subject,
                    'url' => $record->url,
                    'type' => $types[$record->type]->name,
                );
            }
        }

        $smarty = smarty_core();
        $smarty->assign('items', $items);
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
                'defaultvalue' => $configdata[$type->name] ? $configdata[$type->name] : 0,
            );
        }
        $elements['maxitems'] = array(
            'type' => 'text',
            'title' => get_string('maxitems', 'blocktype.inbox'),
            'description' => get_string('maxitemsdescription', 'blocktype.inbox'),
            'defaultvalue' => $configdata['maxitems'] ? $configdata['maxitems'] : 5,
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
}

?>
