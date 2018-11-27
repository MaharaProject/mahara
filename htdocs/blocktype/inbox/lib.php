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
        $data = get_config('wwwroot') . 'module/multirecipientnotification/inbox.php';
        return $data;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
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

        safe_require_plugin('module', 'multirecipientnotification');
        $activitylist = activityblocklistin(join(',', $desiredtypes), $maxitems);
        $records = $activitylist->records;
        $showmore = ($activitylist->count > $maxitems);

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

        return $smarty->fetch('blocktype:inbox:inboxmr.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        $configdata = $instance->get('configdata');

        $types = get_records_array('activity_type', 'admin', 0, 'plugintype,pluginname,name', 'name,plugintype,pluginname');

        $elements = array();
        $elements['types'] = array(
            'type' => 'fieldset',
            'legend' => get_string('messagetypes', 'blocktype.inbox'),
            'elements' => array(),
        );
        foreach($types as $type) {
            if (!empty($type->plugintype)) {
                $type->title = get_string('type' . $type->name, $type->plugintype . '.' . $type->pluginname);
            }
            else {
                $type->title = get_string('type' . $type->name, 'activity');
            }
            $type->class = '';
        }
        usort($types, function ($a, $b) { return strnatcasecmp($a->title, $b->title); });
        if ($USER->get('admin')) {
            $types[] = (object)array('name' => 'adminmessages',
                                     'title' => get_string('typeadminmessages', 'activity'),
                                     'class' => 'field-label-bold');
        }
        foreach($types as $type) {
            $elements['types']['elements'][$type->name] = array(
                'type' => 'switchbox',
                'title' => $type->title,
                'class' => $type->class,
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
        return in_array($view->get('type'), self::get_viewtypes());
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
