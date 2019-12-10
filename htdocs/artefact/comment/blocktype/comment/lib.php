<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined ('INTERNAL') || die();

class PluginBlocktypeComment extends MaharaCoreBlocktype {
    public static function should_ajaxify() {
        // TinyMCE doesn't play well with loading by ajax
        return false;
    }

    public static function single_only() {
        return true;
    }

    public static function get_title() {

        return get_string('title', 'blocktype.comment/comment');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.comment/comment');
    }

    public static function get_categories() {
        return array("general" => 14000);
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER;

        if ($editing) {
            $smarty = smarty_core();
            $smarty->assign('editing', get_string('ineditordescription1', 'blocktype.comment/comment'));
            $html = $smarty->fetch('blocktype:comment:comment.tpl');
            return $html;
        }

        // Comment list pagination requires limit/offset params
        $limit       = param_integer('limit', 10);
        $offset      = param_integer('offset', 0);
        $showcomment = param_integer('showcomment', null);
        // Create the "make comment private form" now if it's been submitted
        if (param_exists('make_public_submit')) {
            pieform(ArtefactTypeComment::make_public_form(param_integer('comment')));
        }
        else if (param_exists('delete_comment_submit')) {
            pieform(ArtefactTypeComment::delete_comment_form(param_integer('comment')));
        }
        $view = new View($instance->get('view'));
        $submittedgroup = (int)$view->get('submittedgroup');
        if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
            $releaseform = true;
        }
        else {
            $releaseform = false;
        }
        // If the view has comments turned off, tutors can still leave
        // comments if the view is submitted to their group.
        if ((!empty($releaseform) || ($view->user_comments_allowed($USER))) && !$versioning) {
            $addfeedbackpopup = true;
        }
        safe_require('artefact', 'comment');
        $commentoptions = ArtefactTypeComment::get_comment_options();
        $commentoptions->limit = $limit;
        $commentoptions->offset = $offset;
        $commentoptions->showcomment = $showcomment;
        $commentoptions->versioning = $versioning;
        $commentoptions->view = $instance->get_view();
        $feedback = ArtefactTypeComment::get_comments($commentoptions);
        $smarty = smarty_core();
        $smarty->assign('feedback', $feedback);
        if (isset($addfeedbackpopup)) {
            $smarty->assign('enablecomments', 1);
            $smarty->assign('addfeedbackpopup', $addfeedbackpopup);
        }

        $html = $smarty->fetch('blocktype:comment:comment.tpl');
        return $html;
    }

    public static function has_instance_config() {
        return false;
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
