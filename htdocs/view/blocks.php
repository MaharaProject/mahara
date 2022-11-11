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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'blocks');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'gridstacklayout.php');

$id = param_integer('id', 0); // if 0, we're editing our profile.
$profile = param_boolean('profile');
$dashboard = param_boolean('dashboard');
$progresspage = param_boolean('progress');

$view = null;
if (empty($id)) {
    if (!empty($profile)) {
        try {
            $view = $USER->get_profile_view();
            $id = $view->get('id');
        }
        catch (ViewNotFoundException $_e) {
            throw new ParameterException("Missing parameter id and couldn't find default user profile view");
        }
    }
    else {
        throw new ParameterException("Missing parameter id");
    }
}
if (!empty($id) && empty($view)) {
    $view = new View($id);
}
$viewid = $view->get('id'); // for tinymce editor
if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}
if ($view->is_submission()) {
    throw new AccessDeniedException(get_string('canteditsubmission', 'view'));
}
$collectionid = false;
if ($collection = $view->get('collection')) {
    $collectionid = $collection->get('id');
    // If the collection is locked or view copied from template, and the viewtype is 'progress' we disallow editing
    $pageistemplate = $view->get_original_template();
    if ($view->get('owner') && (($view->get('type') == 'progress' && $pageistemplate) || $collection->get('lock'))) {
        $errorstr = $view->get('type') == 'progress' ? 'canteditprogress' : 'canteditcollectionlocked';
        throw new AccessDeniedException(get_string($errorstr, 'view'));
    }
}

$group = $view->get('group');
$institution = $view->get('institution');

if ($group && !group_within_edit_window($group)) {
    throw new AccessDeniedException();
}

// If a block was configured & submitted, build the form now so it can
// be processed without having to render the other blocks.
if ($blockid = param_integer('blockconfig', 0)) {
    // However, if removing a newly placed block, let it fall through to process_changes
    if (!param_exists('cancel_action_configureblockinstance_id_' . $blockid)) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($blockid);
        // Check if the block_instance belongs to this view
        if ($bi->get('view') != $view->get('id')) {
            throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
        }
        $bi->build_configure_form();
    }
}

$view->set_edit_nav();
$state = get_string('editcontent1', 'view');

if ($view->get('type') == 'profile') {
    $profile = true;
    $title = get_string('usersprofile', 'mahara', display_name($view->get('owner'), null, true));
    define('TITLE', $title);
}
else if ($view->get('type') == 'dashboard') {
    $dashboard = true;
    $title = get_string('usersdashboard', 'mahara', display_name($view->get('owner'), null, true));
    define('TITLE', $title );
}
else if ($view->get('type') == 'progress') {
    $progresspage = true;
    $title = get_string('progresspage', 'collection');
    define('TITLE', $title);
}
else if ($view->get('type') == 'grouphomepage') {
    $title = get_string('Grouphomepage', 'view');
    if ($view->get('template') != View::SITE_TEMPLATE) {
        $groupurl = group_homepage_url(get_group_by_id($view->get('group'), true), false);
    }
    define('TITLE', $title);
}
else {
    define('TITLE', $view->get('title'));
    $editabletitle = true;
}
define('SUBSECTIONHEADING', TITLE);
// Make the default category the first tab if none is set
$category = '';
if (param_exists('c')) {
    $category = param_variable('c');
}
if (empty($category)) {
    $category = $view->get_default_category();
}

$view->process_changes($category, false);

$extraconfig = array(
    'sidebars'    => false,
);

// Set up theme
$viewtheme = $view->set_user_theme();

$allowedthemes = get_user_accessible_themes();

// Pull in cross-theme view stylesheet and file stylesheets
$stylesheets = array();
foreach (array_reverse($THEME->get_url('style/style.css', true, 'artefact/file')) as $sheet) {
    $stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number($sheet) . '">';
}

$issiteview = $view->get('institution') == 'mahara';
$issitetemplate = ($view->get('template') == View::SITE_TEMPLATE ? true : false);
$canedittitle = $view->can_edit_title();
$canuseskins = !$issitetemplate && can_use_skins(null, false, $issiteview);

$stylesheets = array_merge($stylesheets, $view->get_all_blocktype_css());
// Tell the user to change the view theme if the current one is no
// longer available to them.
if ($viewtheme && !isset($allowedthemes[$viewtheme])) {
    $smarty = smarty(array(), $stylesheets, false, $extraconfig);
    $smarty->assign('PAGEHEADING', get_string('choosetheme'));
    $smarty->assign('formurl', get_config('wwwroot') . 'view/blocks.php');
    $smarty->assign('view', $view->get('id'));
    $smarty->assign('viewtitle', $view->get('title'));
    $smarty->assign('viewtheme', $viewtheme);
    $smarty->assign('viewthemes', $allowedthemes);
    $smarty->display('view/changetheme.tpl');
    exit;
}

// We use gridstack-jq.js here instead of gridstack-h5.js to allow block moving on mobile
// but gridstack-h5.js elsewhere as it allows for better scrolling on page in mobile
$javascript = array('views', 'tinymce', 'paginator', 'js/jquery/jquery-ui/js/jquery-ui.min.js',
                    'js/jquery/jquery-ui/js/jquery-ui.touch-punch.min.js', 'tablerenderer',
                    'artefact/file/js/filebrowser.js',
                    'lib/pieforms/static/core/pieforms.js', 'js/switchbox.js',
                    'js/gridstack/gridstack_modules/gridstack-jq.js',
                    'js/gridlayout.js',
                    );
if ($view->get('accessibleview')) {
    $javascript[] = 'js/dragondrop/dragon-drop.js';
    $javascript[] = 'js/accessibilityreorder.js';
}
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
$inlinejs = "jQuery( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";
require_once('pieforms/pieform/elements/select.php');
$inlinejs .= pieform_element_select_get_inlinejs();
$inlinejs .= "jQuery(window).on('pageupdated', {}, function() { dock.init(jQuery(document)); });";

$blockresizeonload = "false";
if ($view->uses_new_layout() && $view->needs_block_resize_on_load()) {
    // we're copying from an old layout view and need to resize blocks
    $blockresizeonload = "true";
}
if (!$view->uses_new_layout()) {
    // flag to set the showlayouttranslatewarning=true in account preferences
    $alwaystranslate = param_boolean('alwaystranslate', false);
    // flag to translate this page
    $translate = param_boolean('translate', false);
    // if  showlayouttranslatewarning is not set in account preferences, then we need to show the warning
    $showlayouttranslatewarning = is_null($USER->get_account_preference('showlayouttranslatewarning')) ? 1 : $USER->get_account_preference('showlayouttranslatewarning');

    if ($showlayouttranslatewarning && $alwaystranslate) {
      $USER->set_account_preference('showlayouttranslatewarning', 0);
      $showlayouttranslatewarning = false;
    }

    if ($showlayouttranslatewarning && !$translate) {
      // user needs to confirm that wants to translate old layout page to grid layout
      // before we continue
      $smarty = smarty(array(), $stylesheets, false, $extraconfig);
      $smarty->assign('PAGEHEADING', get_string('pleaseconfirmtranslate', 'view'));
      $smarty->assign('formurl', get_config('wwwroot') . 'view/blocks.php');
      $smarty->assign('viewid', $view->get('id'));
      $smarty->assign('viewtitle', $view->get('title'));
      $smarty->assign('accountprefsurl', get_config('wwwroot') . 'account');
      $smarty->display('view/translatewarning.tpl');
      exit;
    }
    else {
        // if it's old row layout, we need to translate to grid layout
        save_blocks_in_new_layout($view->get('id'));
        $blockresizeonload = "true";
    }
}
$blocks = $view->get_blocks(true);
$blocksencode = json_encode($blocks);

if ( $view->get('accessibleview')) {
    $mincolumns = BlockInstance::GRIDSTACK_CONSTANTS['desktopWidth'];
    $reorder = '  accessibilityReorder();';
}
else {
    $mincolumns = 'null';
    $reorder = '  ';
}

$blocksjs ="
$(function () {
    var options = {
        margin: 1,
        cellHeight: 10,
        resizable: false,
        acceptWidgets: true,
        draggable: {
            scroll: true,
        },
        animate: true,
        minCellColumns: {$mincolumns}
    },
    translate;
    var grid = GridStack.init(options);
    if (grid) {
        grid.resizable('.grid-stack-item', true);
        // should add the blocks one by one
        var blocks = {$blocksencode};
        if ({$blockresizeonload}) {
            // update block heights when they are loaded
            loadGridTranslate(grid, blocks);
        }
        else {
            loadGrid(grid, blocks);
        }
        {$reorder}
    }
    GridStack.setupDragIn('.btn-group-vertical .grid-stack-item', { revert: 'invalid', scroll: true, appendTo: 'body', helper: cloneGridstackNode });
    // This helper node is used so that the normal click event is stopped so we don't get drag/drop and add block popup
    function cloneGridstackNode(event) {
        let node = event.target.cloneNode(true);
        helper = $('<div></div>').append(node);
        helper.find('.icon').addClass('hidden');
        helper.find('.btn').removeClass('btn-primary');
        return helper;
    }
});


";

// The form for adding blocks via the keyboard
$addform = pieform(array(
    'name' => 'newblock',
    'method' => 'post',
    'jsform' => true,
    'renderer' => 'div',
    'autofocus' => false,
    'class' => 'cell-radios',
    'elements' => array(
        'position' => array(
            'type' => 'select',
            'title' => get_string('blockorder', 'view'),
            'defaultvalue' => 'bottom',
            'options' => array(
                'top' => get_string('top', 'view'),
                'bottom' => get_string('bottom', 'view')
            ),
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'subclass' => array('btn-secondary'),
            'value' => array(get_string('add'), get_string('cancel')),
        ),
    ),
));

// Get the placeholder block info
$placeholderblock = PluginBlocktype::get_blocktypes_for_category('shortcut', $view, 'placeholder');
$placeholderbutton = '';
if ($placeholderblock) {
    // it's active so make the button with different display title
    $placeholderblock[0]['title'] = $view->get('accessibleview') ? get_string('addnewblockaccessible', 'view') : get_string('addnewblockdrag', 'view');
    $placeholderblock[0]['cssicon'] = 'plus';
    $smarty = smarty_core();
    $smarty->assign('blocktypes', $placeholderblock);
    $smarty->assign('javascript', false);
    $smarty->assign('accessible', $view->get('accessibleview'));
    $smarty->assign('GS_DESKTOP_WIDTH', BlockInstance::GRIDSTACK_CONSTANTS['desktopWidth']);
    $smarty->assign('GS_DEFAULT_HEIGHT', BlockInstance::GRIDSTACK_CONSTANTS['defaultHeight']);
    $smarty->assign('GS_DRAG_HEIGHT', BlockInstance::GRIDSTACK_CONSTANTS['dragHeight']);
    $smarty->assign('GS_DRAG_WIDTH', BlockInstance::GRIDSTACK_CONSTANTS['dragWidth']);

    $placeholderbutton = $smarty->fetch('view/blocktypelist.tpl');
}
$strings = array(
    'view' => array(
        'addnewblock',
        'moveblock',
    ),
    'artefact.blog'=> array(
        'draft'
    ),
);

if ($view->get('accessibleview')) {
    $strings['view'][] = 'itemgrabbed';
    $strings['view'][] = 'itemdropped';
    $strings['view'][] = 'itemreorder';
}
// To allow any blocktype that has config with calendar fields to work
// as get_instance_config_javascript() loads the .js files via ajax
// and so don't exist yet when the calendar wants to set the field
$wwwroot = get_config('wwwroot');
if ($wwwroot == '/') {
    $wwwroot = '';
}
$javascript[] = $wwwroot . 'js/momentjs/moment-with-locales.min.js';
$javascript[] = $wwwroot . 'js/bootstrap-datetimepicker/tempusdominus-bootstrap-4.js';
$stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';

$signoff_html = $view->has_signoff() ? $view->get_signoff_verify_form() : '';

$smarty = smarty($javascript, $stylesheets, $strings, $extraconfig);

$smarty->assign('addform', $addform);

// Tell smarty we're editing rather than just rendering
$smarty->assign('editing', true);

$smarty->assign('placeholder_button', $placeholderbutton);

// Work out what action is being performed. This is used to put a hidden submit
// button right at the very start of the form, so that hitting enter in any
// form fields will cause the correct action to be performed
foreach (array_keys($_POST + $_GET) as $key) {
    if (substr($key, 0, 7) == 'action_') {
        if (param_boolean('s')) {
            // When configuring a blockinstance and the search tab is open,
            // pressing enter should search
            $key = str_replace('configureblockinstance', 'acsearch', $key);
            if (substr($key, -2) == '_x') {
                $key = substr($key, 0, -2);
            }
        }
        $smarty->assign('action_name', $key);
        break;
    }
}

$viewid = $view->get('id');
$displaylink = $view->get_url();
$smarty->assign('edittitle', $canedittitle);
$smarty->assign('canuseskins', $canuseskins);
$smarty->assign('displaylink', $displaylink);
$smarty->assign('formurl', get_config('wwwroot') . 'view/blocks.php');
$smarty->assign('category', $category);
$smarty->assign('profile', $profile);
$smarty->assign('dashboard', $dashboard);
$smarty->assign('progresspage', $progresspage);
if (get_config('blockeditormaxwidth')) {
    $inlinejs .= 'config.blockeditormaxwidth = true;';
}
$smarty->assign('INLINEJAVASCRIPT', $blocksjs .  $inlinejs);
$viewtype = $view->get('type');
$viewtitle = $view->get('title');
$owner = $view->get('owner');
if ($owner &&  $viewtype == 'profile') {
    $viewtitle = get_string('usersprofile', 'mahara', display_name($view->get('owner'), null, true));
}
$smarty->assign('accesssuspended', View::access_override_pending(array('id' => $viewid)));
$smarty->assign('viewtype', $viewtype);
$smarty->assign('view', $view->get('id'));
$smarty->assign('groupid', $group);
$smarty->assign('is_activity_page', $view->get('type') == 'activity');

// Page settings include activity config
if ($view->get('type') == 'activity' && $view->get('group')) {
    $activity_data = $view->get_view_activity_data();
    $group = $view->get('group');
    $smarty->assign('activity', $activity_data);
    $can_edit_activity = View::check_can_edit_activity_page_info($group, true);
    $smarty->assign(
        'activity_support',
        $view->get_activity_support_display_edit_form($can_edit_activity && !$activity_data->achieved)
    );
    $smarty->assign('can_edit_page_settings', $can_edit_activity);
    $smarty->assign('activity_signoff_html', $signoff_html);
}
else {
    $smarty->assign('can_edit_page_settings', true);
}

if (isset($groupurl)) {
    $smarty->assign('groupurl', $groupurl);
}
$smarty->assign('institution', $institution);

$smarty->assign('viewid', $view->get('id'));
$smarty->assign('collectionid', $collectionid);

$smarty->assign('issiteview', isset($institution) && ($institution == 'mahara'));

$smarty->assign('issitetemplate', $view->is_site_template());
$smarty->assign('PAGEHEADING', $state);
$smarty->assign('instructions', $view->get('instructions'));
$smarty->assign('instructionscollapsed', $view->get('instructionscollapsed'));
$returnto = $view->get_return_to_url_and_title();
$smarty->assign('url', $returnto['url']);
if ($view->get('type') == 'progress') {
    $smarty->assign('collectionurl', 'collection/progresscompletion.php?id=' . $collectionid);
}
$smarty->assign('viewurl', $view->get_url());
$smarty->assign('title', $returnto['title']);
$smarty->assign('accessible', $view->get('accessibleview'));
$smarty->display('view/blocks.tpl');
