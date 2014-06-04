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

class PluginBlocktypeMyviews extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.myviews');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.myviews');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function get_viewtypes() {
        return array('profile', 'dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            return '';
        }

        $smarty = smarty_core();

        // Get viewable views
        $views = View::view_search(null, null, (object) array('owner' => $userid), null, null, 0, true, null, array('portfolio'));
        $views = $views->count ? $views->data : array();
        $smarty->assign('VIEWS',$views);
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
        return $view->get('owner') != null;
    }

    public static function override_instance_title(BlockInstance $instance) {
        global $USER;
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid === null || $ownerid == $USER->get('id')) {
            return get_string('title', 'blocktype.myviews');
        }
        return get_string('otherusertitle', 'blocktype.myviews', display_name($ownerid, null, true));
    }

}
