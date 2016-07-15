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
$mnettoken = $SESSION->get('mnetuser') ? param_alphanum('mt', null) : null;

// access key for logged out users
$usertoken = (is_null($mnettoken) && get_config('allowpublicviews')) ? param_alphanum('t', null) : null;

if ($mnettoken) {
    if (!$viewid = get_view_from_token($mnettoken, false)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
}
else if ($usertoken) {
    if (!$viewid = get_view_from_token($usertoken, true)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
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

$new = param_boolean('new');
$showmore = param_boolean('showmore');
if (!$showmore) {
    $showmore = 0;
}

if (!isset($view)) {
    $view = new View($viewid);
}

if (!can_view_view($view)) {
    $errorstr = (param_integer('objection', null)) ? get_string('accessdeniedobjection', 'error') : get_string('accessdenied', 'error');
    throw new AccessDeniedException($errorstr);
}
else {
    // To save the atime in the db - make it a millisecond in the past
    // so it differs from the atime in the View constructor and so triggers
    // the saving of the atime change. Can't use $view->set('dirty', true)
    // as that will also get the view object to update the mtime which is not
    // what we want.
    $view->set('atime', (time()) - 1);
    $view->commit();
}

// Comment list pagination requires limit/offset params
$limit       = param_integer('limit', 10);
$offset      = param_integer('offset', 0);
$showcomment = param_integer('showcomment', null);

// Create the "make comment private form" now if it's been submitted
if (param_variable('make_public_submit', null)) {
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
$submittedgroup = (int)$view->get('submittedgroup');
if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view
    $submittedgroup = get_record('group', 'id', $submittedgroup);
    // If the view is part of a submitted collection, the whole
    // collection must be released at once.
    $releasecollection = !empty($collection) && $collection->get('submittedgroup') == $submittedgroup->id;
    if ($releasecollection) {
        if ($ctime = $collection->get('submittedtime')) {
            $text = get_string(
                'collectionsubmittedtogroupon', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name),
                format_date(strtotime($ctime))
            );
        }
        else {
            $text = get_string('collectionsubmittedtogroup', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name));
        }
    }
    else if ($view->get('submittedtime')) {
        $text = get_string('viewsubmittedtogroupon', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name), format_date(strtotime($view->get('submittedtime'))));
    }
    else {
        $text = get_string('viewsubmittedtogroup', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name));
    }
    if (($releasecollection && $collection->get('submittedstatus') == Collection::SUBMITTED) || $view->get('submittedstatus') == View::SUBMITTED) {
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
                    'class' => 'btn-default pull-right',
                    'value' => $releasecollection ? '<span class="icon icon-unlock left" role="presentation" aria-hidden="true"></span>' . get_string('releasecollection', 'group') : '<span class="icon icon-unlock left" role="presentation" aria-hidden="true"></span>' . get_string('releaseview', 'group'),
                ),
            ),
        ));
    }
    else {
        $releaseform = $text . ' ' . get_string('submittedpendingrelease', 'view');
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

$javascript = array('paginator', 'viewmenu', 'js/collection-navigation.js');
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
if (is_plugin_active('externalvideo', 'blocktype')) {
    $javascript = array_merge($javascript, array((is_https() ? 'https:' : 'http:') . '//cdn.embedly.com/widgets/platform.js'));
}
$inlinejs = "addLoadEvent( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";

// If the view has comments turned off, tutors can still leave
// comments if the view is submitted to their group.
if (!empty($releaseform) || ($commenttype = $view->user_comments_allowed($USER))) {
    $defaultprivate = !empty($releaseform);
    $moderate = isset($commenttype) && $commenttype === 'private';
    $addfeedbackform = pieform(ArtefactTypeComment::add_comment_form($defaultprivate, $moderate));
}
if ($USER->is_logged_in()) {
    $objectionform = pieform(objection_form());
    if ($notrudeform = notrude_form()) {
        $notrudeform = pieform($notrudeform);
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
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($view);
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

// include slimbox2 js and css files, if it is enabled...
if (get_config_plugin('blocktype', 'gallery', 'useslimbox2')) {
    $langdir = (get_string('thisdirection', 'langconfig') == 'rtl' ? '-rtl' : '');
    $headers = array_merge($headers, array(
        '<script type="application/javascript" src="' . append_version_number(get_config('wwwroot') . 'lib/slimbox2/js/slimbox2.js') . '"></script>',
        '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'lib/slimbox2/css/slimbox2' . $langdir . '.css') . '">'
    ));
}

$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted();
$can_copy = $view->is_copyable($view);

$viewgroupform = false;
if ($owner && $owner == $USER->get('id')) {
    if ($tutorgroupdata = group_get_user_course_groups()) {
        if (!$view->is_submitted()) {
            $viewgroupform = view_group_submission_form($view, $tutorgroupdata, 'view');
        }
    }
}

$viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.

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
addLoadEvent(function () {
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
                var closer = $('<a id="closer_' + blockid + '" href="#" class="close-link">Close</a>').click(function(e) {
                    $(this).parent().toggle(400, function() {
                        commentlink.focus();
                    });
                    e.preventDefault();
                });
                chtml.append(closer);
            }
            chtml.toggle(400, function() {
                if (chtml.is(':visible')) {
                    chtml.find('a').first().focus();
                }
                else {
                    commentlink.focus();
                }
            });
            e.preventDefault();
        });
    });
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

$smarty->assign('INLINEJAVASCRIPT', $javascript . $inlinejs);
$smarty->assign('new', $new);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtype', $viewtype);
$smarty->assign('feedback', $feedback);
$smarty->assign('owner', $owner);
$smarty->assign('tags', $view->get('tags'));
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
$smarty->assign('visitstring', $view->visit_message());
if ($can_edit) {
    $smarty->assign('editurl', get_config('wwwroot') . 'view/blocks.php?id=' . $viewid . ($new ? '&new=1' : ''));
}
if ($can_copy) {
    $smarty->assign('copyurl', get_config('wwwroot') . 'view/copy.php?id=' . $viewid . (!empty($collection) ? '&collection=' . $collection->get('id') : ''));
}

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
$smarty->assign('viewcontent', $viewcontent);
$smarty->assign('releaseform', $releaseform);
if (isset($addfeedbackform)) {
    $smarty->assign('enablecomments', 1);
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
    $smarty->assign('notrudeform', $notrudeform);
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

mahara_log('views', "$viewid"); // Log view visits
