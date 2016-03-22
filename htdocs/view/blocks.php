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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'blocks');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$id = param_integer('id', 0); // if 0, we're editing our profile.
$new = param_boolean('new', false);
$profile = param_boolean('profile');
$dashboard = param_boolean('dashboard');

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

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
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
    if (!isset($_POST['cancel_action_configureblockinstance_id_' . $blockid]) || !param_integer('removeoncancel', 0) || param_integer('pieform_jssubmission', 0)) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($blockid);
        // Check if the block_instance belongs to this view
        if ($bi->get('view') != $view->get('id')) {
            throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
        }
        $bi->build_configure_form($new);
    }
}

$view->set_edit_nav();
$state = get_string('editcontent', 'view');

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
else if ($view->get('type') == 'grouphomepage') {
    $title = get_string('grouphomepage', 'view');
    if ($view->get('template') != View::SITE_TEMPLATE) {
        $groupurl = group_homepage_url(get_record('group', 'id', $view->get('group')), false);
    }
    define('TITLE', $title);
}
else if ($new) {
    define('TITLE', get_string('notitle', 'view'));
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

$view->process_changes($category, $new);

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
foreach (array_reverse($THEME->get_url('style/select2.css', true)) as $sheet) {
    $stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number($sheet) . '">';
}

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

$javascript = array('views', 'tinymce', 'paginator', 'js/jquery/jquery-ui/js/jquery-ui.min.js',
                    'js/jquery/jquery-ui/js/jquery-ui.touch-punch.min.js', 'tablerenderer', 'artefact/file/js/filebrowser.js',
                    'lib/pieforms/static/core/pieforms.js','js/jquery/modernizr.custom.js');
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
if (is_plugin_active('externalvideo', 'blocktype')) {
    $javascript = array_merge($javascript, array((is_https() ? 'https:' : 'http:') . '//cdn.embedly.com/widgets/platform.js'));
}
$inlinejs = "addLoadEvent( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";
require_once('pieforms/pieform/elements/select.php');
$inlinejs .= pieform_element_select_get_inlinejs();

// The form for adding blocks via the keyboard
$addform = pieform(array(
    'name' => 'addblock',
    'method' => 'post',
    'jsform' => true,
    'renderer' => 'div',
    'autofocus' => false,
    'class' => 'cell-radios',
    'elements' => array(
        'cellchooser' => array(
            'type' => 'radio',
            'class' => 'fullwidth',
            'title' => get_string('blockcell', 'view'),
            'rowsize' => 2,
            'options' => array('R1C1', 'R1C2', 'R2C1'),
        ),
        'position' => array(
            'type' => 'select',
            'title' => get_string('blockorder', 'view'),
            'options' => array('Top', 'After 1', 'After 2'),
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-default',
            'value' => array(get_string('add'), get_string('cancel')),
        ),
    ),
));

$blockid = $view->get_blockinstance_currently_being_configured();
if (!$blockid) {
    $blockid = param_integer('block', 0);
    if (!$blockid) {
        // Build content before initialising smarty in case pieform elements define headers.
        $viewcontent = $view->build_rows(true);
    }
}

$smarty = smarty($javascript, $stylesheets, array(
    'view' => array(
        'addblock',
        'cellposition',
        'blockordertopcell',
        'blockorderafter',
        'moveblock',
    ),
), $extraconfig);

$smarty->assign('addform', $addform);

// The list of categories for the tabbed interface
$smarty->assign('category_list', $view->build_category_list($category, $new));

// The list of shortcut blocks
$smarty->assign('shortcut_list', $view->build_blocktype_list('shortcut'));

// The list of blocktypes for the default category
$smarty->assign('blocktype_list', $view->build_blocktype_list($category));

// Tell smarty we're editing rather than just rendering
$smarty->assign('editing', true);

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
if ($new) {
    $displaylink .= (strpos($displaylink, '?') === false ? '?' : '&') . 'new=1';
}
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $displaylink);
$smarty->assign('formurl', get_config('wwwroot') . 'view/blocks.php');
$smarty->assign('category', $category);
$smarty->assign('new', $new);
$smarty->assign('profile', $profile);
$smarty->assign('dashboard', $dashboard);
if (get_config('blockeditormaxwidth')) {
    $inlinejs .= 'config.blockeditormaxwidth = true;';
}
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$viewtype = $view->get('type');
$viewtitle = $view->get('title');
$owner = $view->get('owner');
if ($owner &&  $viewtype == 'profile') {
    $viewtitle = get_string('usersprofile', 'mahara', display_name($view->get('owner'), null, true));
}

$smarty->assign('viewtype', $viewtype);
$smarty->assign('view', $view->get('id'));
$smarty->assign('groupid', $group);
if (isset($groupurl)) {
    $smarty->assign('groupurl', $groupurl);
}
$smarty->assign('institution', $institution);

if (get_config('userscanchooseviewthemes') && $view->is_themeable()) {
    $smarty->assign('viewtheme', $viewtheme);
    $smarty->assign('viewthemes', $allowedthemes);
}

$smarty->assign('viewid', $view->get('id'));
$collectionid = false;
if ($collection = $view->get('collection')) {
    $collectionid = $collection->get('id');
}
$smarty->assign('collectionid', $collectionid);

if ($blockid) {
    // Configuring a single block
    $bi = new BlockInstance($blockid);
    $smarty->assign('block', $bi->render_editing(true));
}
else {
    // The HTML for the columns in the view
    $columns = $viewcontent;
    $smarty->assign('columns', $columns);
}
$smarty->assign('issiteview', isset($institution) && ($institution == 'mahara'));

$smarty->assign('issitetemplate', $view->is_site_template());
$smarty->assign('PAGEHEADING', $state);
$smarty->display('view/blocks.tpl');
