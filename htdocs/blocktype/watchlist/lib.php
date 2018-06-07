<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-watchlist
 * @author     Catalyst IT Ltd
 * @author     Gregor Anželj <gregor.anzelj@gmail.com>
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

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {

        $smarty = smarty_core();
        $views = self::fetch_items($instance, 0, $editing);
        // if there are no watched views, notify the user
        if (empty($views['data'])) {
            $smarty->assign('watchlistempty', true);
        }
        else {
            $smarty->assign('watchlistempty', false);
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
        $mode    = isset($configdata['mode']) ? $configdata['mode'] : 'watchlist';
        $period  = isset($configdata['period']) ? $configdata['period'] : 'login';
        $orderby = $mode == 'watchlist' ? '' : (isset($configdata['orderby']) ? $configdata['orderby'] : 'activity');
        $limit   = isset($configdata['count']) ? (int) $configdata['count'] : 10;
        $view = $instance->get_view();
        $userid = $view->get('owner');

        $results = array();
        $count = 0;
        switch ($mode) {
            case 'follower':
                // All viewable views (from friends), excluding user's own views
                require_once('searchlib.php');
                $friends = search_friend('current');
                $ownedby = null;
                if ($friends['count'] > 0) {
                    $ownedby = array();
                    foreach ($friends['data'] as $friend) {
                        $ownedby[] = (object) array('owner' => $friend['id']);
                    }
                }
                $sort[] = array('column' => 'mtime', 'desc' => true);
                $results = View::view_search(
                    null, // $query
                    null, // $ownerquery
                    $ownedby, // $ownedby
                    null, // $copyableby
                    $limit, // $limit
                    0, // $offset
                    true, // $extra
                    $sort, // $sort
                    array('portfolio'), // $types
                    null, // $collection
                    null, // $accesstypes
                    null, // $tag
                    null, // $viewid
                    $userid, // $excludeowner
                    false  // $groupbycollection
                );

                $options = new stdClass();
                $options->period = $period;
                $options->orderby = $orderby;
                if ($results->count > 0) {
                    // Pages shared to user filtered according to date/time period and filter
                    list($results, $count) = self::filter_views_shared_to_user($results->data, $options);
                }
                break;

            case 'watchlist':
            default:
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
                break;
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
        $smarty->assign('order', $orderby);

        $views['tablerows'] = $smarty->fetch('blocktype:watchlist:' . $mode . $orderby . 'paginator.tpl');

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

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array('js/configform.js');
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $classes = 'first last';
        if (!isset($configdata) || (isset($configdata['mode']) && $configdata['mode'] == 'watchlist')) {
            $classes .= ' d-none';
        }

        return array(
            'mode' => array(
                'type'          => 'radio',
                'title'         => get_string('typetoshow', 'blocktype.watchlist'),
                'description'   => get_string('typetoshowdesc', 'blocktype.watchlist'),
                'defaultvalue'  => isset($configdata['mode']) ? $configdata['mode'] : 'watchlist',
                'options'       => array(
                    'watchlist'      => get_string('list.watchlist', 'blocktype.watchlist'),
                    'follower'    => get_string('list.follower', 'blocktype.watchlist'),
                ),
            ),
            'settings' => array(
                'type' => 'fieldset',
                'class' => $classes,
                'iconclass' => 'filter',
                'collapsible' => true,
                'collapsed' => true,
                'legend' => get_string('additionalfilters', 'blocktype.watchlist'),
                'elements' => array(
                    'period' => array(
                        'type'        => 'select',
                        'title'       => get_string('filterby', 'blocktype.watchlist'),
                        'description' => get_string('filterbydesc', 'blocktype.watchlist'),
                        'options' => array(
                            'week' => get_string('filterby.week', 'blocktype.watchlist'),
                            'month' => get_string('filterby.month', 'blocktype.watchlist'),
                            '2months' => get_string('filterby.2months', 'blocktype.watchlist'),
                            'quarter' => get_string('filterby.quarter', 'blocktype.watchlist'),
                            'half' => get_string('filterby.half', 'blocktype.watchlist'),
                            'year' => get_string('filterby.year', 'blocktype.watchlist'),
                            'login' => get_string('filterby.login', 'blocktype.watchlist'),
                        ),
                        'defaultvalue' => isset($configdata['period']) ? $configdata['period'] : 'login',
                    ),
                    'orderby' => array(
                        'type'        => 'select',
                        'title'       => get_string('orderby', 'blocktype.watchlist'),
                        'description' => get_string('orderbydesc', 'blocktype.watchlist'),
                        'options' => array(
                            'activity' => get_string('orderby.activity', 'blocktype.watchlist'),
                            'owner' => get_string('orderby.owner', 'blocktype.watchlist'),
                        ),
                        'defaultvalue' => isset($configdata['orderby']) ? $configdata['orderby'] : 'activity',
                    ),
                ),
            ),
            'count' => array(
                'type'          => 'text',
                'title'         => get_string('itemstoshow', 'blocktype.watchlist'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue'  => isset($configdata['count']) ? $configdata['count'] : 10,
                'size'          => 3,
                'rules'         => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
        );
    }

    public static function instance_config_save($values, BlockInstance $instance) {
        $validwatchlistoptions = array('title', 'mode', 'count', 'retractable');

        if ($values['mode'] == 'watchlist') {
            foreach ($values as $key => $value) {
                if (!in_array($key, $validwatchlistoptions)) {
                    unset($values[$key]);
                }
            }
        }

        return $values;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Watchlist only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return in_array($view->get('type'), self::get_viewtypes());
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

    public static function filter_views_shared_to_user($viewdata, $options) {
        global $USER;

        $now = date("Y-m-d H:i:s");
        switch ($options->period) {
            case 'week':
                $then = date("Y-m-d H:i:s", strtotime("-7 days"));
                break;
            case 'month':
                $then = date("Y-m-d H:i:s", strtotime("-31 days"));
                break;
            case '2months':
                $then = date("Y-m-d H:i:s", strtotime("-62 days"));
                break;
            case 'quarter':
                $then = date("Y-m-d H:i:s", strtotime("-93 days"));
                break;
            case 'half':
                $then = date("Y-m-d H:i:s", strtotime("-183 days"));
                break;
            case 'year':
                $then = date("Y-m-d H:i:s", strtotime("-366 days"));
                break;
            case 'login':
            default:
                $then = db_format_timestamp($USER->get('lastlogin'));
        }
        if (empty($then)) {
            // we are masquerading as a user that has never logged in so set value to yesterday
            $then = date("Y-m-d H:i:s", strtotime("-1 days"));
        }

        $filterbydata = array();
        foreach ($viewdata as $v) {
            if ($v['mtime'] < $now && $v['mtime'] > $then) {
                $filterbydata[] = $v;
            }
        }

        $options->now = $now;
        $options->then = $then;

        if (isset($then) && !empty($then) && $options->orderby == 'owner') {
            list($viewdata, $count) = self::filter_views_by_owner($filterbydata, $options);
        }

        if (isset($then) && !empty($then) && $options->orderby == 'activity') {
            list($viewdata, $count) = self::filter_views_by_activity($filterbydata, $options);
        }

        return array($viewdata, $count);
    }

    /*
     *  Filter and group pages by owner, then sort owners alphabetically.
     */
    private static function filter_views_by_owner($viewdata, $options) {
        $views = array();

        // sort alphabetically by firstname and lastname
        function compare_fullname($a, $b) {
            if (!empty($a['owner']) && !empty($b['owner'])) {
                // sort by owner last name
                $retval = strnatcmp(no_accents($a['user']->lastname), no_accents($b['user']->lastname));
                // if last names are identical, sort by first name
                if (!$retval) {
                    $retval = strnatcmp(no_accents($a['user']->firstname), no_accents($b['user']->firstname));
                    return $retval;
                }
            }
        }

        if (count($viewdata) > 1) {
            usort($viewdata, __NAMESPACE__ . '\compare_fullname');
        }
        $count = 0;
        foreach ($viewdata as $v) {
            if (!empty($v['owner'])) {
                $key = $v['user']->username;
                if ($v['mtime'] < $options->then) {
                    $v['mtime'] = null;
                }
                $views[$key][] = $v;
                $count++;
            }
        }

        return array($views, $count);
    }

    /*
     *  Filter pages by reverse chronological order
     *  based on mtime (when page was last updated)
     */
    private static function filter_views_by_activity($viewdata, $options) {
        $views = array();
        foreach ($viewdata as $v) {
            if ($v['mtime'] < $options->then) {
                $v['mtime'] = null;
            }
            $views[] = $v;
        }
        return array($views, count($views));
    }

}
