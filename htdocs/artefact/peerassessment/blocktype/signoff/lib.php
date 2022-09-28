<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-peerassessment
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined ('INTERNAL') || die();

class PluginBlocktypeSignoff extends MaharaCoreBlocktype {
    public static function should_ajaxify() {
        // TinyMCE doesn't play well with loading by ajax
        return false;
    }

    public static function postinst($oldversion) {
        return set_config_plugin('blocktype', 'signoff', 'notretractable', true);
    }

    public static function single_only() {
        return true;
    }

    public static function single_artefact_per_block() {
        return false;
    }

    public static function get_title() {

        return get_string('title', 'blocktype.peerassessment/signoff');
    }

    public static function override_instance_title(BlockInstance $instance) {
        if (!$instance->get('inedit')) {
            return '';
        }
        return get_string('title', 'blocktype.peerassessment/signoff');
    }

    public static function hide_title_on_empty_content() {
        return true;
    }

    public static function get_description() {
        return get_string('description', 'blocktype.peerassessment/signoff');
    }

    public static function get_categories() {
        return array("general" => 14650);
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function display_for_roles(BlockInstance $bi, $roles) {
        return true;
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'fullinclself';
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER;

        $configdata = $instance->get('configdata');

        $smarty = smarty_core();
        if ($editing) {
            $smarty->assign('editing', $editing);
            $smarty->assign('placeholder', get_string('placeholder1', 'blocktype.peerassessment/signoff'));
            $html = $smarty->fetch('blocktype:signoff:signoff.tpl');
        }
        else {
            $view = $instance->get_view();
            safe_require('artefact', 'peerassessment');
            $owneraction = $view->get_progress_action('owner');
            $manageraction = $view->get_progress_action('manager');
            $signable = (bool)$owneraction->get_action();
            $verifiable = (bool)$manageraction->get_action();

            $smarty->assign('WWWROOT', get_config('wwwroot'));
            $smarty->assign('view', $view->get('id'));
            // Verify option
            $smarty->assign('showverify', !empty($configdata['verify']));
            $smarty->assign('verifiable', $verifiable);
            $smarty->assign('verified', ArtefactTypePeerassessment::is_verified($view));
            // Signoff option
            $smarty->assign('showsignoff', !empty($configdata['signoff']));
            $smarty->assign('signable', $signable);
            $smarty->assign('signoff', ArtefactTypePeerassessment::is_signed_off($view));

            // We make a couple of dummy forms so we get pieform 'switchbox' markup but we don't want
            // to submit via pieforms as the markup will be accessed via javascript
            $element['signoff'] = array(
                'type'         => 'switchbox',
                'title'        => '',
                'defaultvalue' => ArtefactTypePeerassessment::is_signed_off($view, false),
                'readonly'     => !$signable
            );

            $form = array('name' => 'dummyform', 'elements' => $element);
            $form = pieform_instance($form);
            $smarty->assign('signoffbutton', $form->build(false));

            $element2['verify'] = array(
                'type'         => 'switchbox',
                'title'        => '',
                'defaultvalue' => ArtefactTypePeerassessment::is_verified($view, false),
                'readonly'     => ArtefactTypePeerassessment::is_verified($view, false)
            );

            $form = array('name' => 'dummyform', 'elements' => $element2);
            $form = pieform_instance($form);
            $smarty->assign('verifybutton', $form->build(false));

            $html = $smarty->fetch('blocktype:signoff:verifyform.tpl');
        }
        return $html;
    }

    public static function has_instance_config(BlockInstance $instance) {
        if ($instance->get_view()->get_original_template()) {
            return false;
        }
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $elements = array (
            'signoff' => array (
                'type' => 'switchbox',
                'title' => get_string('signoff', 'blocktype.peerassessment/signoff'),
                'description' => get_string('signoffdesc', 'blocktype.peerassessment/signoff'),
                'value' => true,
                'disabled'     => true,
            ),
            'verify' => array (
                'type' => 'switchbox',
                'title' => get_string('verify', 'blocktype.peerassessment/signoff'),
                'description' => get_string('verifydesc', 'blocktype.peerassessment/signoff'),
                'defaultvalue' => !empty($configdata['verify']) ? 1 : 0,
            ),
        );
        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        $viewid = $instance->get_view()->get('id');
        ensure_record_exists('view_signoff_verify', (object) array('view' => $viewid), (object) array('view' => $viewid), 'id', true);
        $values['title'] = '';
        return $values;
    }

    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    public static function delete_instance(BlockInstance $instance) {
        $viewid = $instance->get_view()->get('id');
        execute_sql("DELETE FROM {view_signoff_verify} WHERE view = ?", array($viewid));
    }

    /**
     * We will use rewrite_blockinstance_extra_config to save the new view_signoff_verify row
     */
    public static function rewrite_blockinstance_extra_config(View $view, BlockInstance $block, $configdata, $artefactcopies) {
        $viewid = $view->get('id');
        ensure_record_exists('view_signoff_verify', (object) array('view' => $viewid), (object) array('view' => $viewid), 'id', true);
        return $configdata;
    }

    public static function set_block_height_on_load() {
        return true;
    }
}
