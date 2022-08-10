<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-newviews
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeNewViews extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title2', 'blocktype.newviews');
    }

    public static function get_description() {
        return get_string('description3', 'blocktype.newviews');
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
        $share = array();

        if (!empty($configdata['institution']) && $USER->get('institutions')) {
            $share['institution'] = 1;
        }
        if (!empty($configdata['public']) && get_config('allowpublicviews')) {
            $share['public'] = 1;
        }

        $accesstypes = array('public', 'loggedin', 'friend', 'user', 'group', 'institution');
        foreach ($configdata as $key => $value) {
            if (in_array($key, $accesstypes)) {
                $share[$key] = $value;
            }
        }

        $views = View::shared_to_user(
            null,
            null,
            $nviews,
            0,
            'mtime',
            'desc',
            array_keys($share, 1),
            $view->get('owner')
        );
        $smarty = smarty_core();
        $smarty->assign('loggedin', $USER->is_logged_in());
        $smarty->assign('views', $views->data);
        return $smarty->fetch('blocktype:newviews:newviews.tpl');
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        $configdata = $instance->get('configdata');
        $elements = array();
        $elements['limit'] = array(
            'type' => 'text',
            'title' => get_string('viewstoshow1', 'blocktype.newviews'),
            'description' => get_string('viewstoshowdescription', 'blocktype.newviews'),
            'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
            'size' => 3,
            'minvalue' => 1,
            'maxvalue' => 100,
        );
        $sharedefaults = array('user', 'group', 'friend');
        $shareoptions = array(
            'user'        => get_string('Me', 'view'),
            'friend'      => get_string('friends', 'view'),
            'group'       => get_string('mygroups'),
        );

        if ($USER->get('institutions')) {
            $shareoptions['institution'] = get_string('myinstitutions', 'group');
        }
        $shareoptions['loggedin'] = get_string('registeredusers', 'view');
        if (get_config('allowpublicviews')) {
            $shareoptions['public'] = get_string('public', 'view');
        }

        $elements['shareoptions'] = array(
            'type'        => 'fieldset',
            'legend'      => get_string('sharedwithellipsis', 'view'),
            'comment' => get_string('sharedwithdescription', 'view'),
            'elements'    => array(),
            'collapsible' => true,
            'collapsed'   => false,
            'class'       => 'dropdown-group js-dropdown-group',
        );

        foreach ($shareoptions as $key => $value) {
            $default = in_array($key, $sharedefaults) ? 1 : 0;
            $elements['shareoptions']['elements'][$key] = array(
                'type'         => 'switchbox',
                'title'        => $shareoptions[$key],
                'defaultvalue' => isset($configdata[$key]) ? $configdata[$key] : $default,
            );
        }
        return $elements;
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'shallow';
    }

    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title2', 'blocktype.newviews');
    }

    public static function should_ajaxify() {
        return true;
    }

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return array
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

}
