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
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'view');

require(dirname(dirname(__FILE__)) . '/init.php');

require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'objectionable.php');
require_once('institution.php');
require_once('group.php');
safe_require('artefact', 'comment');
safe_require('artefact', 'file');

// Used by the Mahara assignment submission plugin for Moodle, to indicate that a user
// coming over from mnet should be able to view a certain page (i.e. a teacher viewing
// an assignmnet submission)
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
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    $viewid = $viewtoken->viewid;
}
else if ($usertoken) {
    $viewtoken = get_view_from_token($usertoken, true);
    if (!$viewtoken->viewid) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
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

// check if this is a group page and the user is group admin
if ($groupid = $view->get('group')) {
    $is_group_admin = (group_user_access($groupid) == 'admin');
}

if (is_view_suspended($view) && !$is_admin && !$is_owner && !($groupid && $is_group_admin)) {
    $errorstr = get_string('accessdeniedsuspension', 'error');
    throw new AccessDeniedException($errorstr);
}

if (!can_view_view($view)) {
    $errorstr = (param_integer('objection', null)) ? get_string('accessdeniedobjection', 'error') : get_string('accessdenied', 'error');
    throw new AccessDeniedException($errorstr);
}
$institution = $view->get('institution');
View::set_nav($groupid, $institution, false, false, false);
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

$owner    = $view->get('owner');
$viewtype = $view->get('type');

if ($viewtype == 'profile' || $viewtype == 'dashboard' || $viewtype == 'grouphomepage') {
    redirect($view->get_url());
}

define('TITLE', $view->get('title'));

$collection = $view->get('collection');
// Do we need to redirect to the matrix page on first visit via token access?
if ($viewtoken && $viewtoken->gotomatrix && $collection && $collection->has_framework()) {
    redirect($collection->get_framework_url($collection, true));
}
$submittedgroup = (int)$view->get('submittedgroup');
if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view
    $submittedgroup = get_group_by_id($submittedgroup, true);

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
        if (isset($ltigradeform) && $ltigradeform && $ctime = $collection->get('submittedtime')) {
            preg_match("/^.*?\"(.*?)\" - \"(.*?)\"/", $submittedgroup->name, $matches);
            $lticoursename = hsc($matches[1]);
            $ltiassignmentname = hsc($matches[2]);
            $text = get_string('collectionsubmittedtogroupgrade', 'view', group_homepage_url($submittedgroup), $ltiassignmentname, $lticoursename, format_date(strtotime($ctime)));
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
        $releaseform = pieform(array(
            'name'     => 'releaseview',
            'method'   => 'post',
            'class' => 'form-inline',
            'plugintype' => 'core',
            'pluginname' => 'view',
            'autofocus' => false,
            'elements' => array(
                'submittedview' => array(
                    'type'  => 'html',
                    'value' => $text,
                ),
                'submit' => array(
                    'type'  => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-secondary float-right',
                    'value' => $releasecollection ? '<span class="icon icon-unlock left" role="presentation" aria-hidden="true"></span>' . get_string('releasecollection', 'group') : '<span class="icon icon-unlock left" role="presentation" aria-hidden="true"></span>' . get_string('releaseview', 'group'),
                ),
            ),
        ));
    }
    else if ($ltigradeform) {
        $releaseform = $text;
    }
    else {
        $releaseform = $text . ' ' . get_string('submittedpendingrelease', 'view');
    }

    if (!empty($ltigradeform)) {
        $releaseform .= $ltigradeform;
    }

}
else {
    $releaseform = '';
}

function releaseview_submit() {
    global $USER, $SESSION, $view, $collection, $submittedgroup, $releasecollection;

    if ($releasecollection) {
        if (is_object($submittedgroup) && $submittedgroup->allowarchives) {
            $collection->pendingrelease($USER);
            $SESSION->add_ok_msg(get_string('collectionreleasedpending', 'group'));
        }
        else {
            $collection->release($USER);
            $SESSION->add_ok_msg(get_string('collectionreleasedsuccess', 'group'));
        }
    }
    else {
        if (is_object($submittedgroup) && $submittedgroup->allowarchives) {
            $view->pendingrelease($USER);
            $SESSION->add_ok_msg(get_string('viewreleasedpending', 'group'));
        }
        else {
            $view->release($USER);
            $SESSION->add_ok_msg(get_string('viewreleasedsuccess', 'group'));
        }
    }
    if ($submittedgroup) {
        // The tutor might not have access to the view any more; send
        // them back to the group page.
        redirect(group_homepage_url($submittedgroup));
    }
    redirect($view->get_url());
}

$javascript = array('paginator', 'viewmenu', 'js/collection-navigation.js', 'js/jquery/jquery-mobile/jquery.mobile.custom.min.js');
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
if (is_plugin_active('externalvideo', 'blocktype')) {
    $javascript = array_merge($javascript, array((is_https() ? 'https:' : 'http:') . '//cdn.embedly.com/widgets/platform.js'));
}
$inlinejs = "jQuery( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";

// If the view has comments turned off, tutors can still leave
// comments if the view is submitted to their group.
if (!empty($releaseform) || ($commenttype = $view->user_comments_allowed($USER))) {
    $defaultprivate = !empty($releaseform);
    $moderate = !$USER->is_logged_in() || (isset($commenttype) && $commenttype === 'private');
    $addfeedbackform = pieform(ArtefactTypeComment::add_comment_form($defaultprivate, $moderate));
}
$objectionform = false;
if ($USER->is_logged_in()) {
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
// if the view theme is set in view table
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
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

$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted();
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


// Don't show page content to a user with peer role
// if the view doesn't have a peer assessment block
if (!$USER->has_peer_role_only($view) || $view->has_peer_assessement_block()
    || ($USER->is_admin_for_user($view->get('owner')) && $view->is_objectionable())) {
    $viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.
}

$smarty = smarty(
    $javascript,
    $headers,
    array('confirmcopytitle' => 'view',
          'confirmcopydesc' => 'view',
          'View' => 'view',
          'Collection' => 'collection'),
    array(
        'sidebars' => false,
        'skin' => $skin
    )
);

$javascript = <<<EOF
var viewid = {$viewid};
var showmore = {$showmore};
jQuery(function () {
    paginator = {$feedback->pagination_js}
});

jQuery(function($) {
    $('#column-container .blockinstance-content .commentlink').each(function() {
        var blockid = $(this).attr('id').match(/\d+/);
        // only use comments expander if there are comments on the artefact
        $(this).on('click', function(e) {
            var commentlink = $(this);
            var chtml = commentlink.parent().parent().find('#feedbacktable_' + blockid).parent();
            // add a 'close' link at the bottom of the list for convenience
            if ($('#closer_' + blockid).length == 0) {
                var closer = $('<a id="closer_' + blockid + '" href="#" class="close-link">Close</a>').on("click", function(e) {
                    $(this).parent().toggle(400, function() {
                        commentlink.trigger("focus");
                    });
                    e.preventDefault();
                });
                chtml.append(closer);
            }
            chtml.toggle(400, function() {
                if (chtml.is(':visible')) {
                    chtml.find('a').first().trigger("focus");
                }
                else {
                    commentlink.trigger("focus");
                }
            });
            e.preventDefault();
        });
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
});

jQuery(window).on('pageupdated', {}, function() {
    dock.init(jQuery(document));
});
EOF;

// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        if ($views = $collection->get('views')) {
            $viewnav = $views['views'];
            if ($collection->has_framework()) {
                array_unshift($viewnav, $collection->collection_nav_framework_option());
            }
            $smarty->assign('collection', $viewnav);
        }
    }
}

$blocktype_toolbar = $view->get_all_blocktype_toolbar();
if (!empty($blocktype_toolbar['toolbarhtml'])) {
    $smarty->assign('toolbarhtml', join("\n", $blocktype_toolbar['toolbarhtml']));
}
$smarty->assign('canremove', $can_edit);
$smarty->assign('INLINEJAVASCRIPT', $javascript . $inlinejs);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtype', $viewtype);
$smarty->assign('feedback', $feedback);
$smarty->assign('owner', $owner);
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


$titletext = ($collection && $shownav) ? hsc($collection->get('name')) : $view->display_title(true, false, false);
$smarty->assign('lastupdatedstr', $view->lastchanged_message());
$smarty->assign('visitstring', $view->visit_message());
if ($can_edit) {
    $smarty->assign('editurl', get_config('wwwroot') . 'view/blocks.php?id=' . $viewid);
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

$title = hsc(TITLE);

$smarty->assign('maintitle', $titletext);

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

$smarty->assign('viewdescription', ArtefactTypeFolder::append_view_url($view->get('description'), $view->get('id')));
$smarty->assign('viewinstructions', ArtefactTypeFolder::append_view_url($view->get('instructions'), $view->get('id')));
$smarty->assign('viewcontent', (isset($viewcontent) ? $viewcontent : null));
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
if (isset($reviewform)) {
    $smarty->assign('reviewform', $reviewform);
}
$smarty->assign('viewbeingwatched', $viewbeingwatched);

if ($viewgroupform) {
    $smarty->assign('view_group_submission_form', $viewgroupform);
}

if ($titletext !== $title) {
    $smarty->assign('title', $title);
}

$smarty->assign('userisowner', ($owner && $owner == $USER->get('id')));

$smarty->display('view/view.tpl');

mahara_touch_record('view', $viewid); // Update record 'atime'
mahara_log('views', "$viewid"); // Log view visits
