<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-mygroups
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeMyGroups extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.mygroups');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.mygroups');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal' => 32000);
    }

    public static function get_viewtypes() {
        return array('profile', 'dashboard');
    }

    /**
     * This function renders a list of items as html
     *
     * @param array items
     * @param string template
     * @param array options
     * @param array pagination
     */
    public function render_items(&$items, $template, $options, $pagination) {
        $smarty = smarty_core();
        $smarty->assign('options', $options);
        $smarty->assign('items', $items['data']);
        $items['tablerows'] = $smarty->fetch($template);
        if ($items['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $items['count'],
                'limit' => $items['limit'],
                'offset' => $items['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => $pagination['resultcounttextsingular'] ? $pagination['resultcounttextsingular'] : get_string('result'),
                'resultcounttextplural' => $pagination['resultcounttextplural'] ? $pagination['resultcounttextplural'] :get_string('results'),
            ));
            $items['pagination'] = $pagination['html'];
            $items['pagination_js'] = $pagination['javascript'];
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $sort = !empty($configdata['sort']) ? $configdata['sort'] : null;
        $limit = !empty($configdata['limitto']) ? $configdata['limitto'] : null;
        $view = $instance->get_view();
        $baseurl = ($view->get('type') == 'dashboard') ? $view->get_url() . '?id=' . $view->get('id') : $view->get_url();
        $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $instance->get('id');

        $userid = $view->get('owner');
        if (!$userid) {
            return '';
        }

        $smarty = smarty_core();
        require_once('group.php');
        // Group stuff
        if (!empty($limit)) {
            list($usergroups, $count) = group_get_user_groups($userid, null, $sort, $limit, 0);
        }
        else {
            $usergroups = group_get_user_groups($userid, null, $sort);
            $count = count($usergroups);
        }
        foreach ($usergroups as $group) {
            $group->roledisplay = get_string($group->role, 'grouptype.'.$group->grouptype);
        }
        $groups = array('data' => $usergroups,
                        'count' => $count,
                        'limit' => $limit,
                        'offset' => 0,
                        );
        $pagination = array(
            'baseurl' => $baseurl,
            'id' => 'mygroups_pagination',
            'datatable' => 'usergroupstable',
            'jsonscript' => 'blocktype/mygroups/mygroups.json.php',
            'resultcounttextsingular' => get_string('group', 'group'),
            'resultcounttextplural' => get_string('groups', 'group'),
        );
        self::render_items($groups, 'blocktype:mygroups:mygroupslist.tpl', $configdata, $pagination);

        $smarty->assign('USERGROUPS', $groups);
        $smarty->assign('userid', $userid);
        return $smarty->fetch('blocktype:mygroups:mygroups.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        return array(
            'sort' => array(
                'type'  => 'select',
                'title' => get_string('sortgroups', 'blocktype.mygroups'),
                'options' => array(
                    'latest' => get_string('latest', 'blocktype.mygroups'),
                    'earliest' => get_string('earliest', 'blocktype.mygroups'),
                    'alphabetical'  => get_string('alphabetical', 'blocktype.mygroups'),
                ),
                'defaultvalue' => isset($configdata['sort']) ? $configdata['sort'] : 'alphabetical',
            ),
            'limitto' => array(
                'type'  => 'text',
                'title' => get_string('limitto1', 'blocktype.mygroups'),
                'description' => get_string('limittodesc', 'blocktype.mygroups'),
                'size' => 3,
                'defaultvalue' => isset($configdata['limitto']) ? $configdata['limitto'] : 20,
                'rules' => array(
                    'maxlength' => 4,
                ),
            ),
        );
    }

    public static function instance_config_save($values) {
        $values['limitto'] = !empty($values['limitto']) ? (int)$values['limitto'] : '';
        return $values;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Mygroups only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function override_instance_title(BlockInstance $instance) {
        global $USER;
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid === null || $ownerid == $USER->get('id')) {
            return get_string('title', 'blocktype.mygroups');
        }
        return get_string('otherusertitle', 'blocktype.mygroups', display_name($ownerid, null, true));
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
