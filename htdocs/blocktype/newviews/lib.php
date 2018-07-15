<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-newviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeNewViews extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title1', 'blocktype.newviews');
    }

    public static function get_description() {
        return get_string('description2', 'blocktype.newviews');
    }

    public static function get_categories() {
        return array('general' => 21000);
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER;
        require_once('view.php');
        $configdata = $instance->get('configdata');
        $nviews = isset($configdata['limit']) ? intval($configdata['limit']) : 5;
        $view = $instance->get_view();
        $sort = array(array('column' => 'mtime', 'desc' => true));
        $views = View::view_search(
                null, // $query
                null, // $ownerquery
                null, // $ownedby
                null, // $copyableby
                $nviews, // $limit
                0, // $offset
                true, // $extra
                $sort, // $sort
                array('portfolio'), // $types
                null, // $collection
                null, // $accesstypes
                null, // $tag
                null, // $viewid
                $view->get('owner'), // $excludeowner
                true // $groupbycollection
        );
        $smarty = smarty_core();
        $smarty->assign('loggedin', $USER->is_logged_in());
        $smarty->assign('views', $views->data);
        return $smarty->fetch('blocktype:newviews:newviews.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        return array('limit' => array(
            'type' => 'text',
            'title' => get_string('viewstoshow1', 'blocktype.newviews'),
            'description' => get_string('viewstoshowdescription', 'blocktype.newviews'),
            'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
            'size' => 3,
            'minvalue' => 1,
            'maxvalue' => 100,
        ));
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title1', 'blocktype.newviews');
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
