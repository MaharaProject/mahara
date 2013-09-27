<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeNavigation extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.navigation');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.navigation');
    }

    public static function get_categories() {
        return array('general');
    }

     /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['collection'])) {
            return $bi->get_data('collection', $configdata['collection'])->get('name');
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();

        if (!empty($configdata['collection'])) {
            $views = $instance->get_data('collection', $configdata['collection'])->views();
            if (!empty($views)) {
                $smarty->assign('views', $views['views']);
            }
        }
        $smarty->assign('currentview',$instance->get('view'));
        return $smarty->fetch('blocktype:navigation:navigation.tpl');
    }

    // Called by $instance->get_data('collection', ...).
    public static function get_instance_collection($id) {
        require_once('collection.php');
        return new Collection($id);
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $view = $instance->get_view();
        $groupid = $view->get('group');
        $institutionid = $view->get('institution');
        $userid = $view->get('owner');
        $urlparams['new'] = 1;
        if (!empty($groupid)) {
            $where = 'c.group = ?'; $values = array($groupid);
            $urlparams['group'] = $groupid;
        }
        else if (!empty($institutionid)) {
            $where = 'c.institution = ?'; $values = array($institutionid);
            $urlparams['institution'] = $institutionid;
        }
        else {
            $where = 'c.owner = ?'; $values = array($userid);
        }
        ($collections = get_records_sql_array("
            SELECT c.id, c.name
                FROM {collection} c
            WHERE " . $where . "
            ORDER BY c.name, c.ctime ASC", $values))
            || ($collections = array());

        $default = false;
        $options = array();
        if (!empty($collections)) {
            foreach ($collections as $collection) {
                if (!$default) { // need to have an initially selected item
                    $default = $collection->id;
                }
                $options[$collection->id] = $collection->name;
            }

            return array(
                'collection' => array(
                    'type' => 'select',
                    'title' => get_string('collection','blocktype.navigation'),
                    'rules' => array('required' => true),
                    'options' => $options,
                    'defaultvalue' => !empty($configdata['collection']) ? $configdata['collection'] : $default,
                ),
            );
        }
        else {
            $baseurl = get_config('wwwroot') . 'collection/edit.php';
            if ($urlparams) {
                $baseurl .= '?' . http_build_query($urlparams);
            }
            return array(
                'nocollections' => array(
                    'type'  => 'html',
                    'title' => get_string('collection', 'blocktype.navigation'),
                    'description' => get_string('nocollections1', 'blocktype.navigation', $baseurl),
                    'value' => '',
                ),
            );
        }

    }

    public static function default_copy_type() {
        return 'full';
    }

}
