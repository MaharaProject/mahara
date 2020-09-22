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
require_once(get_config('docroot') . 'blocktype/lib.php');

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
    pieform(ArtefactTypeComment::delete_comment_form(param_integer('comment'), param_integer('blockid', null), param_integer('artefactid', null), param_integer('threaded', null)));
}

$owner    = $view->get('owner');
$viewtype = $view->get('type');

if ($viewtype == 'profile' || $viewtype == 'dashboard' || $viewtype == 'grouphomepage') {
    redirect($view->get_url());
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

$javascript = array('paginator', 'viewmenu', 'js/collection-navigation.js',
        'js/jquery/jquery-mobile/jquery.mobile.custom.min.js',
        'js/jquery/jquery-ui/js/jquery-ui.min.js',
        'js/lodash/lodash.js',
        'js/gridstack/gridstack.js',
        'js/gridstack/gridstack.jQueryUI.js',
        'js/gridlayout.js',
        'js/views.js',
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
    $peerhidden = false;
    if ($newlayout = $view->uses_new_layout()) {

        $blockresizeonload = "false";
        if ($view->uses_new_layout() && $view->needs_block_resize_on_load()) {
            // we're copying from an old layout view and need to resize blocks
            $blockresizeonload = "true";
        }

        $mincolumns = 'null';
        if ( $view->get('accessibleview')) {
            $mincolumns = '12';
        }

        $blocks = $view->get_blocks();
        $blocks = json_encode($blocks);
        $blocksjs =  <<<EOF
$(function () {
    var options = {
        verticalMargin: 5,
        cellHeight: 10,
        disableDrag : true,
        disableResize: true,
        minCellColumns: {$mincolumns},
    };
    var grid = $('.grid-stack');
    grid.gridstack(options);
    grid = $('.grid-stack').data('gridstack');

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

jQuery(window).on('blocksloaded', {}, function() {

    var deletebutton = $('#configureblock').find('.deletebutton');
    deletebutton.on('click', function(e) {
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
});

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
            $('#main-column-container').append('<a id="tmp_modal_link" class="modal_link" href="#" data-toggle="modal-docked" data-target="#configureblock" data-blockid="' + $block + '" data-artefactid="' + $artefact + '" ></a>');
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
            $('#main-column-container').append('<a id="tmp_modal_link" class="modal_link" href="#" data-toggle="modal-docked" data-target="#configureblock" data-artefactid="' + $artefact + '" ></a>');
            $('a#tmp_modal_link').off('click');
            $('a#tmp_modal_link').on('click', function(e) {
                open_modal(e);
                $('#configureblock').addClass('active').removeClass('closed');
            });
            $('a#tmp_modal_link').click();
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
            $smarty->assign('collection', $viewnav);
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

$smarty->assign('viewdescription', $view->get('description'), $view->get('id'));
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

$smarty->assign('viewid', $view->get('id'));
$smarty->display('view/view.tpl');

mahara_touch_record('view', $viewid); // Update record 'atime'
mahara_log('views', "$viewid"); // Log view visits
