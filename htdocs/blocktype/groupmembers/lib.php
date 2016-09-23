<?php
/**
*
* @package    mahara
* @subpackage blocktype-groupmembers
* @author     Liip AG
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
* @copyright  For copyright information on Mahara, please see the README file distributed with this software.
* @copyright  (C) 2006-2009 Liip AG, http://liip.ch
*
*/

defined('INTERNAL') || die();

class PluginBlocktypeGroupMembers extends MaharaCoreBlocktype {

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
        return array('general' => 17000);
    }

    public static function get_viewtypes () {
        return array('grouphomepage');
    }

    public static function hide_title_on_empty_content() {
        return true;
    }

    public static function render_instance (BlockInstance $instance, $editing = false) {
        global $USER;

        // Not render if the block is in a template
        require_once(get_config('libroot') . 'view.php');
        if ($instance->get_view()->get('template') == View::SITE_TEMPLATE) {
            return '';
        }

        $configdata = $instance->get('configdata');
        $rows = isset($configdata['rows']) ? $configdata['rows'] : 1;
        $columns = isset($configdata['columns']) ? $configdata['columns'] : 6;
        $order = isset($configdata['order']) ? $configdata['order'] : 'latest';
        $numtoshow = isset($configdata['numtoshow']) ? $configdata['numtoshow'] : $rows * $columns;

        $groupid = $instance->get_view()->get('group');

        // If the group has hidden membership, display nothing
        $usergroups = $USER->get('grouproles');
        $group = defined('GROUP') && $groupid == GROUP ? group_current_group() : get_record('group', 'id', $groupid);
        if ($group->hidemembersfrommembers && (!isset($usergroups[$groupid]) || $usergroups[$groupid] != 'admin')) {
            return '';
        }
        if ($group->hidemembers && !isset($usergroups[$groupid])) {
            return '';
        }

        require_once('searchlib.php');
        $groupmembers = get_group_user_search_results($groupid, '', 0, $numtoshow, '', $order);

        if ($groupmembers['count']) {
            $smarty = smarty_core();
            $smarty->assign('groupmembers', $groupmembers['data']);
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

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        $options = range(0,100);
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
