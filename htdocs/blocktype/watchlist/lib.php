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

class PluginBlocktypeWatchlist extends SystemBlocktype {

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
        return array('general');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;

        $configdata = $instance->get('configdata');
        $limit = isset($configdata['count']) ? (int) $configdata['count'] : 10;
        $userid = $USER->get('id');

        $smarty = smarty_core();

        $sql = '
            SELECT v.id, v.title, v.owner, v.group, v.institution, v.ownerformat, v.urlid
            FROM {view} v
            JOIN {usr_watchlist_view} wv ON wv.view = v.id
            WHERE wv.usr = ?
            ORDER BY v.title
            LIMIT ?';

        $results = get_records_sql_assoc($sql, array($userid, $limit));

        // if there are no watched views, notify the user
        if (!$results) {
            $smarty->assign('watchlistempty', true);
            return $smarty->fetch('blocktype:watchlist:watchlist.tpl');
        }

        View::get_extra_view_info($results, false, false);
        foreach ($results as &$r) {
            $r = (object) $r;
        }

        $smarty->assign('blockid', 'blockinstance_' . $instance->get('id'));
        $smarty->assign('views', array_values($results));
        return $smarty->fetch('blocktype:watchlist:watchlist.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
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

}
