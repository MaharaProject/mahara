<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-myviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeMyviews extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title1', 'blocktype.myviews');
    }

    public static function get_description() {
        return get_string('description1', 'blocktype.myviews');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal' => 33000);
    }

    public static function get_viewtypes() {
        return array('profile', 'dashboard');
    }

    /**
     * This function renders a list of items views as html
     *
     * @param array items
     * @param string template
     * @param array options
     * @param array pagination
     */
    public static function render_items(&$items, $template, $options, $pagination) {
        $smarty = smarty_core();
        $smarty->assign('options', $options);
        $smarty->assign('items', $items['data']);

        $items['tablerows'] = $smarty->fetch($template);

        if ($items['count'] && $items['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $items['count'],
                'limit' => $items['limit'],
                'setlimit' => true,
                'offset' => $items['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => $pagination['resultcounttextsingular'] ? $pagination['resultcounttextsingular'] : get_string('result'),
                'resultcounttextplural' => $pagination['resultcounttextplural'] ? $pagination['resultcounttextplural'] :get_string('results'),
            ));
            $items['pagination'] = $pagination['html'];
            $items['pagination_js'] = $pagination['javascript'];
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            return '';
        }

        $smarty = smarty_core();

        // Get viewable views
        $views = View::view_search(
                null, // $query
                null, // $ownerquery
                (object) array('owner' => $userid), // $ownedby
                null, // $copyableby
                10, // $limit
                0, // $offset
                true, // $extra
                null, // $sort
                array('portfolio'), // $types
                null, // $collection
                null, // $accesstypes
                null, // $tag
                null, // $viewid
                null, // $excludeowner
                true // $groupbycollection
        );
        $views = (array)$views;
        $viewid = $instance->get_view()->get('id');
        $baseurl = $instance->get_view()->get_url();
        $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'view=' . $viewid . '&editing=' . $editing;
        $pagination = array(
            'baseurl'    => $baseurl,
            'id'         => 'myviews_pagination',
            'datatable'  => 'myviewlist',
            'jsonscript' => 'blocktype/myviews/myviews.json.php',
            'resultcounttextsingular' => get_string('result'),
            'resultcounttextplural'   => get_string('results'),
        );
        self::render_items($views, 'blocktype:myviews:myviewspaginator.tpl', array(), $pagination);
        $smarty->assign('myviews', $views);
        return $smarty->fetch('blocktype:myviews:myviews.tpl');
    }

    public static function has_instance_config() {
        return false;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Myviews only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return in_array($view->get('type'), self::get_viewtypes());
    }

    public static function override_instance_title(BlockInstance $instance) {
        global $USER;
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid === null || $ownerid == $USER->get('id')) {
            return get_string('title1', 'blocktype.myviews');
        }
        return get_string('otherusertitle1', 'blocktype.myviews', display_name($ownerid, null, true));
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
