<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-inbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeInbox extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.inbox');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.inbox');
    }

    public static function get_categories() {
        return array('general' => 19000);
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function get_link(BlockInstance $instance) {
        safe_require('module', 'multirecipientnotification');
        if (PluginModuleMultirecipientnotification::is_active()) {
            $data = get_config('wwwroot') . 'module/multirecipientnotification/inbox.php';
        }
        else {
            $data = get_config('wwwroot') . 'account/activity/index.php';
        }
        return sanitize_url($data);
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER, $THEME;
        $configdata = $instance->get('configdata');
        require_once('activity.php');

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

        // check if multirecipientnotification plugin is active or if we proceed here
        if (record_exists('module_installed', 'name', 'multirecipientnotification', 'active', '1') && safe_require_plugin('module', 'multirecipientnotification')) {
            global $USER;
            $userid = $USER->get('id');
            $activitylist = activityblocklistin(join(',', $desiredtypes), $maxitems);
            $records = $activitylist->records;
            $showmore = ($activitylist->count > $maxitems);
            // use a different template
            $smartytemplate = 'blocktype:inbox:inboxmr.tpl';
        }
        else {
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
                foreach ($records as &$r) {
                    $r->message = format_notification_whitespace($r->message, $r->type);
                }
            }
            $smartytemplate = 'blocktype:inbox:inbox.tpl';
        }

        if ($records) {
            require_once('activity.php');
            foreach ($records as &$r) {
                $section = empty($r->plugintype) ? 'activity' : "{$r->plugintype}.{$r->pluginname}";
                $r->strtype = get_string('type' . $r->type, $section);
            }
        }

        $smarty = smarty_core();
        if ($showmore) {
            $smarty->assign('morelink', self::get_link($instance) . '?type=' . implode(',', $desiredtypes));
        }
        $smarty->assign('blockid', 'blockinstance_' . $instance->get('id'));
        $smarty->assign('items', $records);

        return $smarty->fetch($smartytemplate);
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
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
                'type' => 'switchbox',
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
     * To maintain existing behaviour, use the 'inboxblocktitle' string unless
     * the block has only got forum post notifications in it, in which case
     * use 'topicsimfollowing'
     */
    public static function get_instance_title(BlockInstance $instance) {
        if ($configdata = $instance->get('configdata')) {
            foreach ($configdata as $k => $v) {
                if ($v && $k != 'newpost' && $k != 'maxitems') {
                    return get_string('inboxblocktitle');
                }
            }
            if ($configdata['newpost']) {
                return get_string('topicsimfollowing');
            }
        }
        return get_string('inboxblocktitle');
    }

    public static function should_ajaxify() {
        return true;
    }

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return multitype:
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }
}
