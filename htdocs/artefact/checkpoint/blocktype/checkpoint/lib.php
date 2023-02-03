<?php

/**
 * Checkpoint blocktype for activity pages
 *
 * @package    mahara
 * @subpackage blocktype-checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
defined('INTERNAL') || die();

class PluginBlocktypeCheckpoint extends MaharaCoreBlocktype {

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
        return get_string('title', 'blocktype.checkpoint/checkpoint');
    }

    /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        return 'Checkpoint';
    }

    public static function get_description() {
        return get_string('description', 'blocktype.checkpoint/checkpoint');
    }

    public static function get_categories() {
        return array("general" => 14400);
    }

    public static function get_viewtypes() {
        return array('activity');
    }

    public static function get_css_icon_type($blockname) {
        return 'icon-check';
    }

    public static function display_for_roles(BlockInstance $bi, $roles) {
        return true;
    }

    public static function render_instance(BlockInstance $instance, $editing = false, $versioning = false) {
        global $USER, $exporter;

        $html = '';
        $configdata = $instance->get('configdata');
        $instructions = false;
        safe_require('artefact', 'checkpoint');
        // Checkpoint comments list pagination requires limit/offset params
        $limit       = param_integer('limit', 10);
        $offset      = param_integer('offset', 0);
        $showfeedback = param_integer('showfeedback', null);
        if (param_exists('delete_checkpoint_feedback_submit')) {
            pieform(ArtefactTypeCheckpointfeedback::delete_checkpoint_feedback_form(
                param_integer('feedback'),
                param_integer('view'),
                param_integer('block'),
                $editing
            ));
        }
        $view = new View($instance->get('view'));
        safe_require('artefact', 'checkpoint');

        $options = ArtefactTypeCheckpointfeedback::get_checkpoint_feedback_options();
        $options->limit = $limit;
        $options->offset = $offset;
        $options->showfeedback = $showfeedback;
        $options->view = $instance->get_view();
        $options->block = $instance->get('id');
        $options->group = $view->get('group') ?? null;

        $smarty = smarty_core();
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('exporter', ($exporter ? true : false));
        if ($view->get('type') == 'activity' && $view->get('template') == View::SITE_TEMPLATE) {
            $smarty->assign('sitetemplate', true);
        }
        else {
            $feedback = ArtefactTypeCheckpointfeedback::get_checkpoint_feedback($options, $versioning, $exporter);
            $feedbackform = ArtefactTypeCheckpointfeedback::add_checkpoint_feedback_form($instance->get('id'), 0, $editing);
            $feedbackform = pieform($feedbackform);
            $smarty->assign('allowfeedback', $feedback->canedit && !$versioning);
            $smarty->assign('addcheckpointfeedbackform', $feedbackform);

            // Display achievement level if one exists, otherwise provide dropdown
            if (array_key_exists('level', $configdata)) {
                $smarty->assign('saved_achievement_level', $configdata['level']);
            }
            else {
                $achievementform = ArtefactTypeCheckpointfeedback::get_checkpoint_achievement_form($instance->get('id'));
                $achievementform = pieform($achievementform);
                $smarty->assign('select_achievement_form', $achievementform);
            }
            if ($feedback) {
                $smarty->assign('feedback', $feedback);
            }
            else {
                if ($feedback->count = 0) {
                    $smarty->assign('noassessment', get_string('nopeerassessment', 'blocktype.checkpoint/checkpoint'));
                }
            }

            $can_edit_activity = View::check_can_edit_activity_page_info($view->get('group'), true);
            $smarty->assign('can_edit_activity', $can_edit_activity);
        }
        $html = $smarty->fetch('blocktype:checkpoint:checkpoint.tpl');
        return $html;
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $elements = array();
        return $elements;
    }

    public static function override_instance_title(BlockInstance $instance) {
        // Get all the checkpoint blocks on the page...
        $block_title = get_string('titlelower', 'blocktype.checkpoint/checkpoint');
        $sql_get_blocks = "SELECT id, ctime FROM {block_instance} WHERE blocktype = ? AND view = ? ORDER BY ctime";
        $blocks = get_records_sql_array($sql_get_blocks, [$block_title, $instance->get('view')]);

        for ($i = 0; $i < count($blocks); $i++) {
            if ($blocks[$i]->id == $instance->get('id')) {
                return get_string('title', 'blocktype.checkpoint/checkpoint') . ' ' . ($i + 1);
            }
        }
    }

    public static function instance_config_save($values, $instance) {
        return $values;
    }

    public static function get_artefacts(BlockInstance $instance) {
        // TODO: Doris, relates to ArtefactTypeCheckpointFeedback...?
        return array();
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        return array(
            array(
                'file' => 'js/checkpoint.js',
                'initjs' => " checkpointBlockInit(); ",
            )
        );
    }

    public static function delete_instance(BlockInstance $instance) {
        $id = $instance->get('id');
        $artefacts = get_column('artefact_checkpoint_feedback', 'feedback', 'block', $id);
        if (!empty($artefacts)) {
            safe_require('artefact', 'checkpoint');
            foreach ($artefacts as $artefactid) {
                // Delete the assessment.
                $a = new ArtefactTypeCheckpointfeedback($artefactid);
                $a->delete();
            }
        }
    }

    public static function get_current_artefacts(BlockInstance $instance) {
        $artefacts = array();
        $values = array($instance->get('id'));
        $sql = "SELECT a.description, a.ctime, a.mtime, cf.assessment as id, cf.author
                    FROM {artefact} a
                    JOIN {artefact_checkpoint_feedback} cf
                    ON a.id = cf.feedback
                    WHERE block = ?";
        $artefacts = get_records_sql_array($sql, $values);
        return $artefacts;
    }
}
