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

class PluginBlocktypeComment extends SystemBlocktype {
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
        return array("general");
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;

        if ($editing) {
            return get_string('ineditordescription1', 'blocktype.comment/comment');
        }
        // Feedback list pagination requires limit/offset params
        $limit       = param_integer('limit', 10);
        $offset      = param_integer('offset', 0);
        $showcomment = param_integer('showcomment', null);
        // Create the "make feedback private form" now if it's been submitted
        if (param_variable('make_public_submit', null)) {
            pieform(ArtefactTypeComment::make_public_form(param_integer('comment')));
        }
        else if (param_variable('delete_comment_submit_x', null)) {
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
        if (!empty($releaseform) || ($view->user_comments_allowed($USER))) {
            $addfeedbackpopup = true;
        }
        safe_require('artefact', 'comment');
        $feedback = ArtefactTypeComment::get_comments($limit, $offset, $showcomment, $instance->get_view());
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
}
