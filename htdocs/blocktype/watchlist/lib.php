<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-watchlist
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeWatchlist extends MaharaCoreBlocktype {

    public static function single_only() {
        return true;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.watchlist');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.watchlist');
    }

    public static function get_categories() {
        return array('general' => 25000);
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {

        $smarty = smarty_core();
        $views = self::fetch_items($instance, 0, $editing);
        // if there are no watched views, notify the user
        if (empty($views['data'])) {
            $smarty->assign('watchlistempty', true);
        }
        else {
            $smarty->assign('watchlist', $views);
        }
        return $smarty->fetch('blocktype:watchlist:watchlist.tpl');
    }

    /**
     * This function fetches one pagination "page" of items to be displayed by the watchlist block.
     * (This is used both for the initial block display, and in the JSON pagination script.)
     *
     * @param BlockInstance $instance The watchlist to display
     * @param int $offset
     * @param boolean $editing Whether we're in editing more or not.
     */
    //public static function fetch_items($view, $userid, $offset = 0, $limit = 10, $editing = false) {
    public static function fetch_items(BlockInstance $instance, $offset = 0, $editing = false) {
        global $USER;

        $configdata = $instance->get('configdata');
        $limit = isset($configdata['count']) ? (int) $configdata['count'] : 10;
        $userid = $USER->get('id');

        $count = count_records_sql('SELECT COUNT(*) FROM {view} v JOIN {usr_watchlist_view} wv ON wv.view = v.id WHERE wv.usr = ?' , array($userid));
        $sql = '
            SELECT v.id, v.title, v.owner, v.group, v.institution, v.ownerformat, v.urlid, v.ctime, v.mtime
            FROM {view} v
            JOIN {usr_watchlist_view} wv ON wv.view = v.id
            WHERE wv.usr = ?
            ORDER BY v.title';
        $results = get_records_sql_assoc($sql, array($userid), $offset, $limit);

        if (!empty($results)) {
            View::get_extra_view_info($results, false, false);
            foreach ($results as &$r) {
                $r = (object) $r;
            }
        }
        $views = array('data' => $results,
                       'offset' => $offset,
                       'limit' => $limit,
                       'editing' => $editing,
                       'count' => $count);

        $view = $instance->get_view();
        $baseurl = $view->get_url();
        $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'id=' . $instance->get('id').  '&editing=' . $editing;
        $pagination = array(
            'baseurl'    => $baseurl,
            'id'         => 'watchlist_pagination',
            'datatable'  => 'watchlistblock',
            'jsonscript' => 'blocktype/watchlist/watchlist.json.php',
            'resultcounttextsingular' => get_string('result'),
            'resultcounttextplural'   => get_string('results'),
        );

        $smarty = smarty_core();
        $smarty->assign('options', array());
        $smarty->assign('views', $views['data']);
        $smarty->assign('loggedin', $USER->is_logged_in());
        $views['tablerows'] = $smarty->fetch('blocktype:watchlist:watchlistpaginator.tpl');

        if ($views['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $views['count'],
                'editing' => $views['editing'],
                'limit' => $views['limit'],
                'offset' => $views['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => $pagination['resultcounttextsingular'] ? $pagination['resultcounttextsingular'] : get_string('result'),
                'resultcounttextplural' => $pagination['resultcounttextplural'] ? $pagination['resultcounttextplural'] :get_string('results'),
            ));
            $views['pagination'] = $pagination['html'];
            $views['pagination_js'] = $pagination['javascript'];
        }
        return $views;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        return array(
            'count'     => array(
                'type'          => 'text',
                'title'         => get_string('itemstoshow', 'blocktype.watchlist'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue'  => isset($configdata['count']) ? $configdata['count'] : 10,
                'size'          => 3,
                'rules'         => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
        );
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * watchlist only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function should_ajaxify() {
        return false;
    }

    /**
     * We need a default title for this block, so that the watchlist block
     * on the dashboard is translatable.
     */
    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.watchlist');
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
