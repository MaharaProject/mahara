<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('docroot') . 'blocktype/lib.php');
safe_require('artefact', 'comment');

$viewid = param_integer('viewid');
$blockid = param_variable('blockid', null);
$artefactid = param_integer('artefactid');

if (!can_view_view($viewid)) {
    json_reply('local', get_string('accessdenied', 'error'));
}

$html = '';
if ($blockid) {
    $block = new BlockInstance($blockid);
    $view = $block->get_view();
    $artefact = $block->get_artefact_instance($artefactid);
}
else {
    $artefact = artefact_instance_from_id($artefactid);
    $view = new View($viewid);
}

// Render the artefact
$options = array(
    'viewid' => $viewid,
    'details' => true,
    'metadata' => 1,
    'modal' => true,
);

if ($blockid) {
    $options['blockid'] = $blockid;
}

$rendered = $artefact->render_self($options);
if (!empty($rendered['javascript'])) {
    $html = '<script>' . $rendered['javascript'] . '</script>';
}
$html .= $rendered['html'];

// Get any existing comments for display
if ($artefact->get('allowcomments')) {
    $commentoptions = ArtefactTypeComment::get_comment_options();
    $commentoptions->view = $view;
    $commentoptions->artefact = $artefact;
    if ($blockid) {
        $commentoptions->blockid = $blockid;
    }

    $owner = $artefact->get('owner');
    if ($owner) {
        $threaded = get_user_institution_comment_threads($owner);
    }
    else {
        $threaded = false;
    }
    $commentoptions->threaded = $threaded;
    $feedback = ArtefactTypeComment::get_comments($commentoptions);
    $smarty = smarty_core();
    $smarty->assign('feedback', $feedback);

    if ($blockid) {
        $smarty->assign('blockid', $blockid);
    }
    if ($feedback->data) {
        $link = '<span><h2><span class="icon icon-comments" role="presentation" aria-hidden="true"></span>';
        $link .= ' ' . get_string('Comments', 'artefact.comment') . '</h2></span>';
        $html .= $link;
    }
    $html .= $smarty->fetch('blocktype:comment:comment.tpl');
}

if ($artefact->get('allowcomments') && ( $USER->is_logged_in() || (!$USER->is_logged_in() && get_config('anonymouscomments')))) {
    $tmpview = new View($viewid);
    $commenttype = $tmpview->user_comments_allowed($USER);
    $moderate = !$USER->is_logged_in() || (isset($commenttype) && $commenttype === 'private');
    // Add the comment form
    $link = '<span><h2><span class="icon icon-comments" role="presentation" aria-hidden="true"></span>';
    $link .=' ' . get_string('addcomment', 'artefact.comment') . '</h2></span>';
    $html .= $link;
    $html .= pieform(ArtefactTypeComment::add_comment_form(false, $moderate));
}

$title = $artefact->display_title();
json_reply(false, array(
    'message' => '',
    'title' => $title,
    'html' => $html
));
