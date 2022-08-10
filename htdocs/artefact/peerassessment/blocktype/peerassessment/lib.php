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

class PluginBlocktypePeerassessment extends MaharaCoreBlocktype {
    public static function should_ajaxify() {
        // TinyMCE doesn't play well with loading by ajax
        return false;
    }

    public static function single_only() {
        return false;
    }

    public static function single_artefact_per_block() {
        return false;
    }

    public static function get_title() {

        return get_string('title', 'blocktype.peerassessment/peerassessment');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.peerassessment/peerassessment');
    }

    public static function get_categories() {
        return array("general" => 14600);
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function display_for_roles(BlockInstance $bi, $roles) {
        return true;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER, $exporter;

        $configdata = $instance->get('configdata');
        $instructions = false;
        if (array_key_exists('instructions', $configdata)) {
            $instructions = $configdata['instructions'];
        }
        safe_require('artefact', 'peerassessment');
        // Peer assessment list pagination requires limit/offset params
        $limit       = param_integer('limit', 10);
        $offset      = param_integer('offset', 0);
        $showcomment = param_integer('showcomment', null);
        if (param_exists('delete_assessment_submit')) {
            pieform(ArtefactTypePeerassessment::delete_assessment_form(param_integer('assessment'), param_integer('view'), param_integer('block')));
        }
        $view = new View($instance->get('view'));

        safe_require('artefact', 'peerassessment');
        $options = ArtefactTypePeerassessment::get_assessment_options();
        $options->limit = $limit;
        $options->offset = $offset;
        $options->showcomment = $showcomment;
        $options->view = $instance->get_view();
        $options->block = $instance->get('id');
        $feedback = ArtefactTypePeerassessment::get_assessments($options, $versioning, $exporter);
        $feedbackform = ArtefactTypePeerassessment::add_assessment_form(true, $instance->get('id'), 0);
        $feedbackform = pieform($feedbackform);
        $smarty = smarty_core();
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('exporter', ($exporter ? true : false));
        $smarty->assign('instructions', $instructions);
        $smarty->assign('allowfeedback', $feedback->canedit && !$versioning);
        $smarty->assign('addassessmentfeedbackform', $feedbackform);
        if ($feedback && !$editing) {
            $smarty->assign('feedback', $feedback);
        }
        else {
            $smarty->assign('editing', $editing);
            if ($feedback->count = 0) {
                $smarty->assign('noassessment', get_string('nopeerassessment', 'blocktype.peerassessment/peerassessment'));
            }
        }
        $html = $smarty->fetch('blocktype:peerassessment:peerassessment.tpl');
        return $html;
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }

        safe_require('artefact', 'peerassessment');
        $view = $instance->get_view();

        $instructions = '';
        if (array_key_exists('instructions', $configdata)) {
            $instructions = $configdata['instructions'];
        }
        if (!$instance->get('view_obj')->is_instruction_locked()) {
            $elements = array (
                'instructions' => array (
                    'type' => 'wysiwyg',
                    'title' => get_string('blockcontent', 'blocktype.peerassessment/peerassessment'),
                    'width' => '100%',
                    'height' => $height . 'px',
                    'defaultvalue' => $instructions,
                    'rules' => array('maxlength' => 1000000),
                ),
            );
        }
        else {
            $elements = array (
                'instructionstitle' => array(
                    'type' => 'html',
                    'value' => '<a href="#instconf_instructions_container" aria-controls="instconf_instructions_container" class="" data-bs-toggle="collapse"
                     aria-expanded="' . (!empty($instructions) ? 'true' : 'false') . '">'
                        . get_string('instructions', 'view')
                        . '<span class="icon icon-chevron-down collapse-indicator right text-inline block-config-modal"></span>'
                        . '</a>',
                ),
                'instructions' => array (
                    'name' => 'instructions',
                    'type'  => 'html',
                    'value' => clean_html($instructions),
                    'class' => !empty($instructions) ? 'show' : '',
                ),
            );
        }
        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        require_once('embeddedimage.php');
        $newtext = EmbeddedImage::prepare_embedded_images($values['instructions'], 'peerinstruction', $instance->get('id'));
        $values['instructions'] = $newtext;
        return $values;
    }

    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        return array(
            array(
                'file' => 'js/peerassessment.js',
                'initjs' => " peerassessmentBlockInit(); ",
            )
        );
    }

    public static function delete_instance(BlockInstance $instance) {
        $id = $instance->get('id');
        require_once('embeddedimage.php');
        EmbeddedImage::delete_embedded_images('peerinstruction', $id);
        $artefacts = get_column('artefact_peer_assessment', 'assessment', 'block', $id);
        if (!empty($artefacts)) {
            safe_require('artefact', 'peerassessment');
            foreach ($artefacts as $artefactid) {
                // Delete the assessment.
                $a = new ArtefactTypePeerassessment($artefactid);
                $a->delete();
            }
        }
    }

    public static function get_current_artefacts(BlockInstance $instance) {
        $values = array($instance->get('id'));

        $sql = "SELECT a.description, a.ctime, a.mtime, apa.assessment as id, apa.usr as author, apa.private
                FROM {artefact} a
                JOIN {artefact_peer_assessment} apa
                ON a.id = apa.assessment
                WHERE block = ?";
        $artefacts = get_records_sql_array($sql, $values);
        return $artefacts;
    }
}
