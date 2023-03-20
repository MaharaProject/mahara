<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC_ACCESS', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'view');

require(dirname(dirname(__FILE__)) . '/init.php');

require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'objectionable.php');
require_once(get_config('libroot'). 'revokemyaccess.php');
require_once('institution.php');
require_once('group.php');
safe_require('artefact', 'comment');
safe_require('artefact', 'file');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'export/lib.php');

// Used by the Mahara assignment submission plugin for Moodle, to indicate that a user
// coming over from mnet should be able to view a certain page (i.e. a teacher viewing
// an assignment submission)
$mnetviewid = param_integer('mnetviewid', false);
$mnetcollid = param_integer('mnetcollid', false);

if (
        ($mnetviewid || $mnetcollid)
        && $SESSION->get('mnetuser')
        && safe_require_plugin('auth', 'xmlrpc')
) {
    auth_xmlrpc_mnet_view_access($mnetviewid, $mnetcollid);
}

// access key for roaming teachers
// TODO: The mt token is used by the old token-based Mahara assignment submission
// access system, which is now deprecated. Remove eventually.
$mnettoken = param_alphanum('mt', null);
$mnettokenaccess = $SESSION->get('mnetuser') ? $mnettoken : null;

// access key for logged out users
$usertoken = (is_null($mnettokenaccess) && get_config('allowpublicviews')) ? param_alphanum('t', null) : null;
$viewtoken = null;
if ($mnettoken) {
    $viewtoken = get_view_from_token($mnettoken, false);
    if (!$viewtoken->viewid) {
        throw new AccessDeniedException();
    }
    $viewid = $viewtoken->viewid;
}
else if ($usertoken) {
    $viewtoken = get_view_from_token($usertoken, true);
    if (!$viewtoken->viewid) {
        throw new AccessDeniedException();
    }
    $viewid = $viewtoken->viewid;
}
else if ($pageurl = param_alphanumext('page', null)) {
    if ($profile = param_alphanumext('profile', null)) {
        $view = new View(array('urlid' => $pageurl, 'ownerurlid' => $profile));
    }
    else if ($homepage = param_alphanumext('homepage', null)) {
        $view = new View(array('urlid' => $pageurl, 'groupurlid' => $homepage));
    }
    else {
        throw new ViewNotFoundException(get_string('viewnotfoundexceptiontitle', 'error'));
    }
    $viewid = $view->get('id');
}
else {
    $viewid = param_integer('id');
}

$showmore = param_boolean('showmore');
if (!$showmore) {
    $showmore = 0;
}

if (!isset($view)) {
    $view = new View($viewid);
}

$is_admin = $USER->get('admin') || $USER->is_institutional_admin();
$is_owner = $view->get('owner') == $USER->get('id');
$is_group_admin = false;

// check if this is a group page and the user is group admin
if ($groupid = $view->get('group')) {
    $is_group_admin = (group_user_access($groupid) == 'admin');
}

if (is_view_suspended($view) && !$is_admin && !$is_owner && !($groupid && $is_group_admin)) {
    $errorstr = get_string('accessdeniedsuspension', 'error');
    throw new AccessDeniedException($errorstr);
}

if (!can_view_view($view)) {
    $errorstr = (param_integer('objection', null)) ? get_string('accessdeniedobjection', 'error') : '';
    throw new AccessDeniedException($errorstr);
}

// If a block was configured & submitted, build the form now so it can
// be processed without having to render the other blocks.
if ($blockid = param_integer('blockconfig', 0)) {
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $bi = new BlockInstance($blockid);
    // Check if the block_instance belongs to this view
    if ($bi->get('view') != $view->get('id')) {
        throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
    }
    // check if the block type has quickedit enabled
    if (get_field('blocktype_installed', 'quickedit', 'name', $bi->get('blocktype')) > 0) {
        $bi->build_quickedit_form();
    }
}

// Prepare the signoff verify form in advance - used to be a block
$signoff_html = $view->has_signoff() ? $view->get_signoff_verify_form() : '';

$owner    = $view->get('owner');
$viewtype = $view->get('type');

if ($viewtype == 'profile' || $viewtype == 'dashboard' || $viewtype == 'grouphomepage') {
    redirect($view->get_url());
}

$institution = $view->get('institution');
View::set_nav($groupid, $institution, false, false, false);
// Comment list pagination requires limit/offset params
$limit       = param_integer('limit', 10);
$offset      = param_integer('offset', 0);
$showcomment_context = '';
$showcomment = param_integer('showcomment', null); // default

// For other showassessment, showfeedback, as to not hit the wrong db table (i.e. comment)
$show_comment_type = '';
$show_comment_types = ['comment', 'feedback', 'assessment'];
foreach ($show_comment_types as $type) {
    $show_context = 'show' . $type;
    if (param_integer($show_context, null) != null) {
        $$show_context = param_integer($show_context, null);
        $show_comment_type = $type;
    }
}

// Create the "make comment private form" now if it's been submitted
if (param_exists('make_public_submit')) {
    pieform(ArtefactTypeComment::make_public_form(param_integer('comment')));
}
else if (param_exists('delete_comment_submit')) {
    pieform(ArtefactTypeComment::delete_comment_form(param_integer('comment'), param_integer('blockid', null), param_integer('artefactid', null), param_integer('threaded', null)));
}

//pass down the artefact id of the artefact that was just commented on via the modal pieform
$commented_on_artefactid = param_integer('commented_on_artefactid', null);
if ($commented_on_artefactid) {
    $artefact = artefact_instance_from_id($commented_on_artefactid);
}
//pass down the blockid of the artefact that was just commented on via the modal pieform
$commented_on_blockid = param_integer('commented_on_blockid', null);
if ($commented_on_blockid) {
    $block = new BlockInstance($commented_on_blockid);
}

define('TITLE', $view->get('title'));

$collection = $view->get('collection');
// Do we need to redirect to the progress completion or matrix page on first visit via token access?
if ($viewtoken && $viewtoken->gotomatrix && $collection && $collection->has_progresscompletion()) {
    redirect($collection->get_progresscompletion_url($collection, true));
}
else if ($viewtoken && $viewtoken->gotomatrix && $collection && $collection->has_framework()) {
    redirect($collection->get_framework_url($collection, true));
}
$submittedgroup = (int)$view->get('submittedgroup');
$submittedhost = $view->get('submittedhost');

$user_logged_in = $USER->is_logged_in();
$access_via_group = ($user_logged_in &&
                     $submittedgroup &&
                     group_user_can_assess_submitted_views($submittedgroup, $USER->get('id')));
$access_via_submittedhost = ($submittedhost &&
                             host_user_can_assess_submitted_views($submittedhost));

if ($user_logged_in && $access_via_submittedhost) {
    // If the view is part of a submitted collection, the whole
    // collection must be released at once.
    $releasecollection = !empty($collection);
    $add_form = true;
    if ($releasecollection) {
        if ($ctime = $collection->get('submittedtime')) {
            $text = get_string('collectionsubmittedtohoston', 'view', format_date(strtotime($ctime)));
        }
        else {
            $text = get_string('collectionsubmittedtohost', 'view');
        }
        if (is_collection_in_export_queue($collection->get('id'))) {
            $add_form = false;
        }
    }
    else if ($view->get('submittedtime')) {
        $text = get_string('viewsubmittedtohoston', 'view', format_date(strtotime($view->get('submittedtime'))));
        if (is_view_in_export_queue($view->get('id'))) {
            $add_form = false;
        }
    }
    else {
        $text = get_string('viewsubmittedtohost', 'view');
        // $add_form = false;
    }
    if ($add_form) {
        $releaseform = release_form($text, $releasecollection);
    }
    else {
        $releaseform = $text . ' ' . get_string('submittedpendingrelease', 'view');
    }
}
else if ($user_logged_in && $access_via_group) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view
    $submittedgroup = get_group_by_id($submittedgroup, true);
    $ltigradeform = '';

    // Form for LTI grading
    if (is_plugin_active('lti', 'module')) {
        if ($collection) {
            $ltigradeform = PluginModuleLti::get_grade_dialogue($collection->get('id'), null);
        }
        else {
            $ltigradeform = PluginModuleLti::get_grade_dialogue(null, $view->get('id'));
        }
    }

    // If the view is part of a submitted collection, the whole
    // collection must be released at once.
    $releasecollection = !empty($collection) && $collection->get('submittedgroup') == $submittedgroup->id && empty($ltigradeform);

    if ($releasecollection) {
        if ($ltigradeform && $ctime = $collection->get('submittedtime')) {
            preg_match("/^.*?\"(.*?)\" - \"(.*?)\"/", $submittedgroup->name, $matches);
            $lticoursename = hsc($matches[1]);
            $ltiassignmentname = hsc($matches[2]);
            $text = get_string(
                'collectionsubmittedtogroupgrade',
                'view',
                group_homepage_url($submittedgroup),
                $ltiassignmentname,
                $lticoursename,
                format_date(strtotime($ctime))
            );
        }
        else if ($ctime = $collection->get('submittedtime')) {
            $text = get_string(
                'collectionsubmittedtogroupon', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name),
                format_date(strtotime($ctime))
            );
        }
        else {
            $text = get_string('collectionsubmittedtogroup', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name));
        }
    }
    else if ($ltigradeform && $view->get('submittedtime')) {
        preg_match("/^.*?\"(.*?)\" - \"(.*?)\"/", $submittedgroup->name, $matches);
        $lticoursename = hsc($matches[1]);
        $ltiassignmentname = hsc($matches[2]);
        $text = get_string('viewsubmittedtogroupgrade', 'view', group_homepage_url($submittedgroup), $ltiassignmentname, $lticoursename, format_date(strtotime($view->get('submittedtime'))));
    }
    else if ($view->get('submittedtime')) {
        $text = get_string('viewsubmittedtogroupon1', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name), format_date(strtotime($view->get('submittedtime'))));
    }
    else {
        $text = get_string('viewsubmittedtogroup1', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name));
    }
    if (($releasecollection && $collection->get('submittedstatus') == Collection::SUBMITTED) || $view->get('submittedstatus') == View::SUBMITTED && empty($ltigradeform)) {
        $releaseform = release_form($text, $releasecollection);
    }
    else if ($ltigradeform) {
        $releaseform = $text;
    }
    else {
        $exporttype = $collection ? 'collection' : 'view';
        $exporttypeid = $collection ? $collection->get('id') : $view->get('id');
        $exporttypeowner = $collection ? $collection->get('owner') : $view->get('owner');
        $failed = has_export_failed($exporttype, $exporttypeid, $exporttypeowner);
        if (($USER->get('admin') || $USER->is_institutional_admin()) && $failed) {
            $releaseform = $text . ' ' . get_string('submittedpendingreleasefailed', 'view', get_config('wwwroot') . 'admin/users/exportqueue.php');
        }
        else {
            $releaseform = $text . ' ' . get_string('submittedpendingrelease', 'view');
        }
    }

    if (!empty($ltigradeform)) {
        $releaseform .= $ltigradeform;
    }

}
else if ($is_owner) {
    $releasecollection = !empty($collection);
    $text = '';
    if ($releasecollection && $ctime = $collection->get('submittedtime')) {
        $text = get_string('collectionsubmittedtohoston', 'view', format_date(strtotime($ctime)));
    }
    else if ($ctime = $view->get('submittedtime')) {
        $text = get_string('viewsubmittedtohoston', 'view', format_date(strtotime($ctime)));
    }
    $releaseform = $text;
}
else {
    $releaseform = '';
}

/**
 * Return the release form.
 *
 * @param string $text
 * @param bool $releasecollection
 *
 * @return string The HTML for the Pieform.
 */
function release_form($text, $releasecollection) {
    global $view, $collection;
    $form = array(
        'name'     => 'releaseview',
        'method'   => 'post',
        'class' => 'form-inline',
        'plugintype' => 'core',
        'pluginname' => 'view',
        'autofocus' => false,
        'elements' => [],
    );

    $form['elements']['submittedview'] = array(
        'type'  => 'html',
        'value' => $text,
    );

    if (is_plugin_active('submissions', 'module')) {
        list($submission, $evaluation) = \Submissions\Repository\SubmissionRepository::findCurrentSubmissionAndAssignedEvaluationByPortfolioElement(($releasecollection ? $collection : $view));

        if (!empty($submission)) {
            $form['elements']['selectsuccess'] = [
                'type' => 'select',
                'options' => [
                    null => get_string('chooseresult', 'module.submissions'),
                    1 => get_string('noresult', 'module.submissions'),
                    2 => get_string('fail', 'module.submissions'),
                    3 => get_string('success', 'module.submissions'),
                ],
                'defaultvalue' => $evaluation->get('success')
            ];
        }
    }

    $form['elements']['submit'] = array(
        'type'  => 'button',
        'usebuttontag' => true,
        'class' => 'btn-secondary float-end',
        'value' => $releasecollection ? '<span class="icon icon-unlock left" role="presentation" aria-hidden="true"></span>' . get_string('releasecollection', 'group') : '<span class="icon icon-unlock left" role="presentation" aria-hidden="true"></span>' . get_string('releaseview', 'group'),
    );
    return pieform($form);
}

function releaseview_submit(Pieform $form, $values) {
    global $USER, $SESSION, $view, $collection, $submittedgroup, $submittedhost, $releasecollection;
    $submission = array();

    if (is_plugin_active('submissions', 'module')) {
        /** @var \Submissions\Models\Submission $submission */
        /** @var \Submissions\Models\Evaluation $evaluation */
        list($submission, $evaluation) = \Submissions\Repository\SubmissionRepository::findCurrentSubmissionAndAssignedEvaluationByPortfolioElement(($releasecollection ? $collection : $view));
        if ($submission && $evaluation->get('success') != $values['selectsuccess']) {
            $evaluation->set('success', ($values['selectsuccess'] == null ? null : $values['selectsuccess']));
            $evaluation->commit();
        }
    }

    if ($releasecollection) {
        if (is_object($submittedgroup) && $submittedgroup->allowarchives) {
            $collection->pendingrelease($USER);
            $SESSION->add_ok_msg(get_string('portfolioreleasedpending', 'group'));
        }
        else if ($submittedhost) {
            $externalhost = new stdClass();
            $externalhost->id = $submittedhost;
            $externalhost->name = $submittedhost;
            $externalhost->url = $submittedhost;
            $collection->pendingrelease($USER, $externalhost, false);
            $SESSION->add_ok_msg(get_string('portfolioreleasedpending', 'group'));
        }
        else {
            $collection->release($USER);
            $SESSION->add_ok_msg(get_string('portfolioreleasedsuccess', 'group'));
        }
    }
    else {
        if (is_object($submittedgroup) && $submittedgroup->allowarchives) {
            $view->pendingrelease($USER);
            $SESSION->add_ok_msg(get_string('portfolioreleasedpending', 'group'));
        }
        else if ($submittedhost) {
            $externalhost = new stdClass();
            $externalhost->id = $submittedhost;
            $externalhost->name = $submittedhost;
            $externalhost->url = $submittedhost;
            $view->pendingrelease($USER, $externalhost);
            $SESSION->add_ok_msg(get_string('portfolioreleasedpending', 'group'));
        }
        else {
            $view->release($USER);
            $SESSION->add_ok_msg(get_string('portfolioreleasedsuccess', 'group'));
        }
    }
    if (is_plugin_active('submissions', 'module')) {
        $flashback = \Submissions\Tools\UrlFlashback::createInstanceFromEntry();
        if ($flashback->isValid() && $submission && $flashback->getData()->selectedSubmission->submissionId === $submission->get('id')) {
            redirect($flashback->flashbackUrl);
        }
    }
    if ($submittedgroup) {
        // The tutor might not have access to the view any more; send
        // them back to the group page.
        redirect(group_homepage_url($submittedgroup));
    }
    redirect($view->get_url());
}

$javascript = array('paginator', 'viewmenu', 'js/collection-navigation.js',
        'js/jquery/jquery-mobile/jquery.mobile.custom.min.js',
        'js/jquery/jquery-ui/js/jquery-ui.min.js',
        'js/gridstack/gridstack_modules/gridstack-h5.js',
        'js/gridlayout.js',
        'js/views.js',
        'tinymce',
    );
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
$inlinejs = "jQuery( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";

// If the view has comments turned off, tutors can still leave
// comments if the view is submitted to their group.
if (!empty($releaseform) || ($commenttype = $view->user_comments_allowed($USER))) {
    $defaultprivate = !empty($releaseform);
    $moderate = !$USER->is_logged_in() || (isset($commenttype) && $commenttype === 'private');
    $addfeedbackform = pieform(ArtefactTypeComment::add_comment_form($defaultprivate, $moderate));
}
$objectionform = false;
$revokeaccessform = false;
$notrudeform = array();
$stillrudeform = array();
if ($USER->is_logged_in()) {
    if (record_exists('view_access', 'view', $view->get('id'), 'usr', $USER->get('id'))) {
        $revokeaccessform = pieform(revokemyaccess_form($view->get('id')));
    }
    $objectionform = pieform(objection_form());
    $reviewform = pieform(review_form($view->get('id')));
    if ($notrudeform = notrude_form()) {
        $notrudeform = pieform($notrudeform);
    }
    // For for admin to review objection claim, add comment
    // about objectionable content and possibly remove access
    if ($stillrudeform = stillrude_form()) {
        $stillrudeform = pieform($stillrudeform);
    }
}

$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);
$commentoptions = ArtefactTypeComment::get_comment_options();
$commentoptions->limit = $limit;
$commentoptions->offset = $offset;
$commentoptions->showcomment = $showcomment;
$commentoptions->view = $view;
$feedback = ArtefactTypeComment::get_comments($commentoptions);

// Set up theme
// if the view theme is set in view table as is usable
if ($view->is_themeable() && $view->get('theme') && $THEME->basename != $view->get('theme')) {
    $THEME = new Theme($view);
}
// if it's another users view, it should be displayed with the other users institution theme
else if ($owner && $owner != $USER->get('id')) {
    $THEME = new Theme((int)$owner);
}

$headers = array();
$headers[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';
$headers = array_merge($headers, $view->get_all_blocktype_css());
// Set up skin, if the page has one
$viewskin = $view->get('skin');
$issiteview = $view->get('institution') == 'mahara';
if ($viewskin && get_config('skins') && can_use_skins($owner, false, $issiteview) && (!isset($THEME->skins) || $THEME->skins !== false)) {
    $skin = array('skinid' => $viewskin, 'viewid' => $view->get('id'));
}
else {
    $skin = false;
}

if (!$view->is_public()) {
    $headers[] = '<meta name="robots" content="noindex">';  // Tell search engines not to index non-public views
}

$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted() && !$view->is_submission();
if ($view->get_collection()) {
    $can_edit = $can_edit && $USER->can_edit_collection($view->get_collection());
}
$can_copy = $view->is_copyable();

$viewgroupform = false;
if ($owner && $owner == $USER->get('id')) {
    if ($tutorgroupdata = group_get_user_course_groups()) {
        if (!$view->is_submitted()) {
            $viewgroupform = view_group_submission_form($view, $tutorgroupdata, 'view');
        }
    }
    if (is_plugin_active('lti', 'module') && PluginModuleLti::can_submit_for_grading()) {
        $ltisubmissionform = PluginModuleLti::submit_from_view_or_collection_form($view);
    }
}

$ownerobj = null;
if ($owner) {
    $ownerobj = new User();
    $ownerobj = $ownerobj->find_by_id($owner);
}
// Don't show page content to a user with peer role
// if the view doesn't have a peer assessment block
if (!($USER->has_peer_role_only($view) && $owner && !$ownerobj->peers_allowed_content())
    || $view->has_peer_assessement_block()
    || ($USER->is_admin_for_user($view->get('owner')) && $view->is_objectionable())) {
    $peerhidden = false;
    if ($newlayout = $view->uses_new_layout()) {

        $blockresizeonload = "false";
        if ($view->uses_new_layout() && $view->needs_block_resize_on_load()) {
            // we're copying from an old layout view and need to resize blocks
            $blockresizeonload = "true";
        }

        $mincolumns = 'null';
        if ( $view->get('accessibleview')) {
            $mincolumns = BlockInstance::GRIDSTACK_CONSTANTS['desktopWidth'];
        }

        $blocks = $view->get_blocks();
        $blocks = json_encode($blocks);
        $blocksjs =  <<<EOF
$(function () {
    var options = {
        margin: 1,
        cellHeight: 10,
        disableDrag : true,
        disableResize: true,
        minCellColumns: {$mincolumns},
    };
    var grid = GridStack.init(options);
    if (grid) {
        var blocks = {$blocks};
        if ({$blockresizeonload}) {
            // the page was copied from an old layout page
            // and the blocks still need to be resized
            loadGridTranslate(grid, blocks);
        }
        else {
            loadGrid(grid, blocks);
        }
    }
});
EOF;
    }
    else {
        $viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.
        $blocksjs = "$(function () {jQuery(document).trigger('blocksloaded');});";
    }
}
else {
    $blocksjs = '';
    $newlayout = $view->uses_new_layout();
    $peerhidden = true;
}

$blocktype_toolbar = $view->get_all_blocktype_toolbar();
// To allow any blocktype that has config with calendar fields to work
// as get_instance_config_javascript() loads the .js files via ajax
// and so don't exist yet when the calendar wants to set the field
$javascript[] = get_config('wwwroot') . 'js/momentjs/moment-with-locales.min.js';
$javascript[] = get_config('wwwroot') . 'js/bootstrap-datetimepicker/tempusdominus-bootstrap-4.js';
$headers[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';
$smarty = smarty(
    $javascript,
    $headers,
    array('confirmcopytitle' => 'view',
          'confirmcopydesc' => 'view',
          'View' => 'view',
          'Collection' => 'collection',
          'Comments' => 'artefact.comment',
          'addcomment' => 'artefact.comment'),
    array(
        'sidebars' => false,
        'skin' => $skin
    )
);

$commentonartefact = param_integer('artefact', null);
$show_comment_id = 0;
// doublecheck it's a comment on  artefact in case is old email
if ($show_comment_type) {
    $show_comment_id = ${'show' . $show_comment_type};
    $artefacttype = get_field('artefact', 'artefacttype', 'id', $show_comment_id);
    if ($artefacttype) {
        $classname = generate_artefact_class_name($artefacttype);
        $tmpcomment = new $classname($show_comment_id);
        if (property_exists($tmpcomment, 'onartefact') && $tmpcomment->get('onartefact') && !$commentonartefact) {
            redirect(get_config('wwwroot') . 'view/view.php?id=' . $viewid . '&show' . $show_comment_type . '=' .
                $show_comment_id . '&modal=1&artefact=' . $tmpcomment->get('onartefact'));
        }
    }
    else {
        // Comment id is not valid - the comment may have already been deleted
        redirect(get_config('wwwroot') . 'view/view.php?id=' . $viewid);
    }
}

$javascript = <<<EOF
var viewid = {$viewid};
var showmore = {$showmore};
var commentonartefact = '{$commentonartefact}';
var showcommentid = '{$show_comment_id}';
let showCommentType = '{$show_comment_type}'

jQuery(function () {
    paginator = {$feedback->pagination_js}
});

jQuery(window).on('blocksloaded', {}, function() {

    var deletebutton = $('#configureblock').find('.deletebutton');
    deletebutton.on('click', function(e) {
        if ((formchangemanager.checkDirtyChanges() && confirm(get_string('confirmcloseblockinstance'))) || !formchangemanager.checkDirtyChanges()) {
            e.stopPropagation();
            e.preventDefault();
            var modal_textarea_id = null;
            $('#configureblock').find('textarea.wysiwyg').each(function() {
                modal_textarea_id = $(this).attr('id');
                if (isTinyMceUsed()) {
                    //Remove any existing tinymce
                    tinymce.EditorManager.execCommand('mceRemoveEditor', true, modal_textarea_id);
                }
            });
            clear();
        }
    });

    function clear() {
        var block = $('#configureblock');
        $('.blockinstance-content').html('');
        block.find('h4').html('');
        dock.hide();
    }

    activateModalLinks();

    $('#feedback-form .submitcancel[name="cancel_submit"]').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        tinymce.EditorManager.execCommand('mceRemoveEditor', true, $('#configureblock').find('textarea.wysiwyg').attr('id'));
        dock.hide();
    });

    $('.moretags').on('click', function(e) {
        e.preventDefault();
        var params = {
            'viewid': viewid
        }
        sendjsonrequest(config['wwwroot'] + 'view/viewtags.json.php',  params, 'POST', function(data) {
            if (data.count) {
                $('.tags').html(data.html);
            }
        });
    });
    // Wire up the annotation feedback forms
    $('.feedbacktable.modal-docked form').each(function() {
        initTinyMCE($(this).prop('id'));
    });

    // Focus on a page comment
    if (showcommentid && !commentonartefact) {
        focusOnShowComment();
    }
});

/**
 * Focus on the comment matching the id provided by the showcomment param value
 *
 * Note: First wait for blocks to load
 */
function focusOnShowComment() {
    setTimeout(function() {
        const commentIdString = showCommentType + showcommentid;

        // Identify the comment by focusing on the author link
        // Open the page comments
        if (showCommentType == 'comment') {
            $(".comment-container button.collapsed").trigger('click');
        }
        let commentElements = $("#" + commentIdString + " a");
        if (commentElements) {
            let author_link = '';
            if (commentElements.length > 1) {
                author_link = commentElements[1];
            }
            else {
                author_link = commentElements[0];
            }
            $(author_link).focus();
            scrollToComment(commentIdString);
        }
    }, 500);
}

/**
 * Scroll down to the commentIdString
 *
 * Note: First wait for the dropdown to open
 */
function scrollToComment(commentIdString) {
    setTimeout(function() {
        const element = document.getElementById(commentIdString);
        if (!element) {
            return;
        }
        const headerOffset = $('header').height();
        const sitemessagesOffset = $('.site-messages').height();
        // Scroll down for page comments
        if (!commentonartefact) {
            const y = element.getBoundingClientRect().top + window.pageYOffset - headerOffset - sitemessagesOffset;
            window.scrollTo({top: y, behavior: 'smooth'});
        }
        else {
            const sectionOffset = $('#' + commentIdString).offset();
            $('#configureblock .modal-body').animate({ scrollTop: sectionOffset.top - 60 }, 'smooth');
        }
    }, 500);
}

function activateModalLinks() {
    $('.commentlink').off('click');
    $('.commentlink').on('click', function(e) {
        open_modal(e);
        $(this).closest('div[class*=block-header]').addClass('active-block');
    });

    $('.modal_link').off('click');
    $('.modal_link').on('click', function (e) {
        if ($(this).hasClass('no-modal')) {
            e.stopPropagation();
        }
        else {
            open_modal(e);
            $(this).closest('div[class*=block-header]').addClass('active-block');
        }
    });
}

jQuery(window).on('pageupdated', {}, function() {
    dock.init(jQuery(document));
    activateModalLinks();
});
EOF;

// Alert message for peers and peermanagers so that they know a page containing a peer assessment block has been signed off
if ($USER->can_peer_assess($view) && ArtefactTypePeerassessment::is_signed_off($view) && $view->has_peer_assessement_block()) {
    $signedoffbyurl = get_config('wwwroot') . 'user/view.php?id=' . $view->get('owner');
    $signedoffbylink = '<a href="'. $signedoffbyurl .'">' . display_name($view->get('owner')) . '</a>';
    $signedoffalertpeermsg = get_string('signedoffalertpeermsg', 'artefact.peerassessment', display_name($view->get('owner'), null, true), $signedoffbylink);
    $smarty->assign('signedoffalertpeermsg', $signedoffalertpeermsg);
}

if ($modal = param_integer('modal', null)) {
    $artefact = param_integer('artefact', null);

    if ($block = param_integer('block', null)) {
        $javascript .= <<<EOF
        jQuery(window).on('blocksloaded', {}, function() {
            $('#main-column-container').append('<a id="tmp_modal_link" class="modal_link" href="#" data-bs-toggle="modal-docked" data-bs-target="#configureblock" data-blockid="' + $block + '" data-artefactid="' + $artefact + '" ></a>');
            $('a#tmp_modal_link').off('click');
            $('a#tmp_modal_link').on('click', function(e) {
                open_modal(e);
                $('#configureblock').addClass('active').removeClass('closed');
            });
            $('a#tmp_modal_link').click();
        });
EOF;
    }
    else {
        $javascript .= <<<EOF
        jQuery(window).on('blocksloaded', {}, function() {
            $('#main-column-container').append('<a id="tmp_modal_link" class="modal_link" href="#" data-bs-toggle="modal-docked" data-bs-target="#configureblock" data-artefactid="' + $artefact + '" ></a>');
            $('a#tmp_modal_link').off('click');
            $('a#tmp_modal_link').on('click', function(e) {
                open_modal(e);
                $('#configureblock').addClass('active').removeClass('closed');
            });
            $('a#tmp_modal_link').click();

            // Focus on an artefact comment
            const commentonartefact = {$artefact};
            if (showcommentid && commentonartefact) {
                focusOnShowComment();
            }
        });
EOF;
    }
}
// Load the page with details content (block headers) displaying according to user preferences.
if ($showdetails = get_account_preference($USER->get('id'), 'view_details_active')) {
    $javascript .= <<<EOF
    jQuery(window).on('blocksloaded', {}, function() {
        var headers = $('#main-column-container').find('.block-header');
        $('#details-btn').addClass('active');
        headers.removeClass('d-none');
    });
EOF;
}

// collection top navigation
$shownav = 0;
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        if ($views = $collection->get('views')) {
            $viewnav = $views['views'];
            if ($collection->has_framework()) {
                array_unshift($viewnav, $collection->collection_nav_framework_option());
            }
            if ($collection->has_progresscompletion()) {
                array_unshift($viewnav, $collection->collection_nav_progresscompletion_option());
            }
            if ($collection->has_outcomes()) {
                array_unshift($viewnav, $collection->collection_nav_outcomes_option());
            }
            $smarty->assign('collectionnav', $viewnav);
        }
    }
    $smarty->assign('collectiontitle', $collection->get('name'));
}

if (!empty($blocktype_toolbar['toolbarhtml'])) {
    $smarty->assign('toolbarhtml', join("\n", $blocktype_toolbar['toolbarhtml']));
}
$smarty->assign('canremove', $can_edit);
$smarty->assign('INLINEJAVASCRIPT', $blocksjs . $javascript . $inlinejs);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtype', $viewtype);
$smarty->assign('feedback', $feedback);
$smarty->assign('owner', $owner);
$smarty->assign('peerhidden', $peerhidden);
$smarty->assign('peerroleonly', $USER->has_peer_role_only($view));
list($tagcount, $alltags) = $view->get_all_tags_for_view(10);
$smarty->assign('alltags', $alltags);
$smarty->assign('moretags', ($tagcount > sizeof($alltags) ? true : false));
$smarty->assign('PAGEHEADING', null);
if ($view->is_anonymous()) {
  $smarty->assign('PAGEAUTHOR', get_string('anonymoususer'));
  $smarty->assign('author', get_string('anonymoususer'));
  if ($view->is_staff_or_admin_for_page()) {
    $smarty->assign('realauthor', $view->display_author());
  }
  $smarty->assign('anonymous', TRUE);
} else {
  $smarty->assign('PAGEAUTHOR', $view->formatted_owner());
  $smarty->assign('author', $view->display_author());
  $smarty->assign('anonymous', FALSE);
}

// Navigation title if the page is part of a collection.
$titletext = ($collection && $shownav) ? hsc($collection->get('name')) : $view->display_title(true, false, false);
$smarty->assign('maintitle', $titletext);

// Page title.
$title = hsc(TITLE);

if ($titletext !== $title) {
    $smarty->assign('title', $title);
}

$smarty->assign('lastupdatedstr', $view->lastchanged_message());
$smarty->assign('visitstring', $view->visit_message());
$smarty->assign('accessurl', get_config('wwwroot') . 'view/accessurl.php?return=view&id=' . $viewid . (!empty($collection) ? '&collection=' . $collection->get('id') : '' ));
if ($can_edit) {
    $smarty->assign('editurl', get_config('wwwroot') . 'view/blocks.php?id=' . $viewid);
    $smarty->assign('usercaneditview', TRUE);
}
if ($view->is_submission()) {
    $smarty->assign('issubmission', true);
}
if ($collection) {
    // $smarty->assign('iscollection', true);
    $smarty->assign('configureurl', get_config('wwwroot') . 'collection/edit.php?id=' . $collection->get('id'));
}
else {
    $smarty->assign('configureurl', get_config('wwwroot') . 'view/editlayout.php?id=' . $viewid);
}
if ($can_copy) {
    $smarty->assign('copyurl', get_config('wwwroot') . 'view/copy.php?id=' . $viewid . (!empty($collection) ? '&collection=' . $collection->get('id') : ''));
    if (!$USER->is_logged_in() && $view->get('owner')) {
        // if no user is loggedin and the personal profile is public, the Copy button should download the portfolio
        $smarty->assign('downloadurl', get_config('wwwroot') . 'view/download.php?id=' . $viewid . (!empty($collection) ? '&collection=' . $collection->get('id') : ''));
    }
}
$versions = View::get_versions($view->get('id'));
if ($versions->count > 0) {
    $smarty->assign('versionurl', get_config('wwwroot') . 'view/versioning.php?view=' . $viewid);
}
$smarty->assign('createversionurl', get_config('wwwroot') . 'view/createversion.php?view=' . $viewid);

// Provide a link for roaming teachers to return
$showmnetlink = false;
// Old token-based access list
if (
    $mnetviewlist = $SESSION->get('mnetviewaccess')
    && isset($mnetviewlist[$view->get('id')])
) {
    $showmnetlink = true;
}

// New mnet-based access list
if (
    $SESSION->get('mnetviews')
    && in_array($view->get('id'), $SESSION->get('mnetviews'))
) {
    $showmnetlink = true;
}


if ($showmnetlink) {
    $returnurl = $SESSION->get('mnetuserfrom');
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    if ($peer = get_peer_from_instanceid($SESSION->get('authinstance'))) {
        $smarty->assign('mnethost', array(
            'name'      => $peer->name,
            'url'       => $returnurl ? $returnurl : $peer->wwwroot,
        ));
    }
}

$smarty->assign('viewinstructions',  ArtefactTypeFolder::append_view_url($view->get('instructions'), $view->get('id')));
$smarty->assign('newlayout', $newlayout);
if ($newlayout) {
    $smarty->assign('blocks', (isset($blocks) ? $blocks : null));
}
else {
    $smarty->assign('viewcontent', (isset($viewcontent) ? $viewcontent : null));
}
$smarty->assign('releaseform', $releaseform);
if (isset($ltisubmissionform)) {
    $smarty->assign('ltisubmissionform', $ltisubmissionform);
}

if (isset($addfeedbackform)) {
    $smarty->assign('enablecomments', 1);
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
    if ($USER->is_logged_in()) {
        $smarty->assign('notrudeform', $notrudeform);
        $smarty->assign('stillrudeform', $stillrudeform);
    }
    $smarty->assign('objectedpage', $view->is_objectionable());
    $smarty->assign('objector', $view->is_objectionable($USER->get('id')));
    $smarty->assign('objectionreplied', $view->is_objectionable(null, true));
}
if (isset($revokeaccessform)) {
    $smarty->assign('revokeaccessform', $revokeaccessform);
}
if (isset($reviewform)) {
    $smarty->assign('reviewform', $reviewform);
}
$smarty->assign('viewbeingwatched', $viewbeingwatched);

if ($viewgroupform) {
    $smarty->assign('view_group_submission_form', $viewgroupform);
}

$smarty->assign('userisowner', ($owner && $owner == $USER->get('id')));

$returnto = $view->get_return_to_url_and_title();
$smarty->assign('url', $returnto['url']);
$smarty->assign('linktext', $returnto['title']);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('group', $view->get('group'));

// Activity data form
function activity_support_submit() {
    redirect('/view/view.php?id=' . param_integer('id'));
}

if ($view->get('group') && $view->get('type') == 'activity') {
    $activity_data = $view->get_view_activity_data();
    $group = $view->get('group');
    $smarty->assign('activity', $activity_data);
    $smarty->assign('is_activity_page', $view->get('type') == 'activity');
    $can_edit_layout = View::check_can_edit_activity_page_info($group, true);
    $smarty->assign(
        'activity_support',
        $view->get_activity_support_display_edit_form($can_edit_layout && !$activity_data->achieved)
    );
    $smarty->assign('can_edit_layout', $can_edit_layout);
    $smarty->assign('usercaneditview', $can_edit_layout);
    $smarty->assign('activity_signoff_html', $signoff_html);
}
else {
    $smarty->assign('signoff_html', $signoff_html);
}

$smarty->display('view/view.tpl');

mahara_touch_record('view', $viewid); // Update record 'atime'
mahara_log('views', "$viewid"); // Log view visits
