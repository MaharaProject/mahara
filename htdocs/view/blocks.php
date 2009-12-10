<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'blocks');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

// Emulate IE7 compatibility mode for IE8 - views js doesn't work with ie8
header('X-UA-Compatible: IE=EmulateIE7');

$id = param_integer('id', 0); // if 0, we're editing our profile.
$new = param_boolean('new', false);
$profile = param_boolean('profile');

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

// check if cancel was selected
if ($new && isset($_POST['cancel'])) {
    $view->delete();
    if ($group) {
        redirect(get_config('wwwroot') . 'view/groupviews.php?group='.$group);
    }
    if ($institution) {
        redirect(get_config('wwwroot') . 'view/institutionviews.php?institution='.$institution);
    }
    redirect(get_config('wwwroot') . 'view/');
}

// If a block was configured & submitted, build the form now so it can
// be processed without having to render the other blocks.
if ($blockid = param_integer('blockconfig', 0)) {
    // However, if removing a newly placed block, let it fall through to process_changes
    if (!isset($_POST['cancel_action_configureblockinstance_id_' . $blockid]) || !param_integer('removeoncancel', 0) || param_integer('pieform_jssubmission', 0)) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($blockid);
        $bi->build_configure_form();
    }
}

View::set_nav($group, $institution, ($view->get('type') == 'profile'));

if ($view->get('type') == 'profile') {
    $profile = true;
    define('TITLE', get_string('editprofileview', 'view'));
}
else if ($new) {
    define('TITLE', get_string('createviewstepone', 'view'));
}
else {
    define('TITLE', get_string('editblocksforview', 'view', $view->get('title')));
}

$category = param_alpha('c', '');
// Make the default category the first tab if none is set
if ($category === '') {
    $category = $view->get_default_category();
}

$view->process_changes($category, $new);

$extraconfig = array(
    'stylesheets' => array('style/views.css'),
    'sidebars'    => false,
);

// Set up theme
$viewtheme = $view->get('theme');
$allowedthemes = get_themes(false);
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}

// Pull in cross-theme view stylesheet and file stylesheets
$stylesheets = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">');
foreach (array_reverse($THEME->get_url('style/style.css', true, 'artefact/file')) as $sheet) {
    $stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . $sheet . '">';
}

// Tell the user to change the view theme if the current one is no
// longer available to them.
if ($viewtheme && !isset($allowedthemes[$viewtheme])) {
    $smarty = smarty(array(), $stylesheets, false, $extraconfig);
    $smarty->assign('PAGEHEADING', hsc(TITLE));
    $smarty->assign('formurl', get_config('wwwroot') . 'view/blocks.php');
    $smarty->assign('view', $view->get('id'));
    $smarty->assign('viewtitle', $view->get('title'));
    $smarty->assign('viewtheme', $viewtheme);
    $smarty->assign('viewthemes', $allowedthemes);
    $smarty->display('view/changetheme.tpl');
    exit;
}

$smarty = smarty(array('views', 'tinymce', 'paginator', 'tablerenderer', 'artefact/file/js/filebrowser.js', 'lib/pieforms/static/core/pieforms.js', 'blocktype/creativecommons/js/creativecommons.js'), $stylesheets, false, $extraconfig);

// The list of categories for the tabbed interface
$smarty->assign('category_list', $view->build_category_list($category, $new));

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

$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('formurl', get_config('wwwroot') . 'view/blocks.php');
$smarty->assign('category', $category);
$smarty->assign('new', $new);
$smarty->assign('profile', $profile);
$viewid = $view->get('id');
$viewtype = $view->get('type');
$viewtitle = $view->get('title');
$owner = $view->get('owner');
if ($owner) {
    if ($viewtype == 'profile') {
        $microheaderlinks = array(
            array(
                'name' => get_string('viewmyprofilepage'),
                'url' => get_config('wwwroot') . 'user/view.php',
            ),
            array(
                'name' => get_string('editmyprofile', 'artefact.internal'),
                'url' => get_config('wwwroot') . 'artefact/internal/index.php',
                'edit' => 1,
            ),
        );
        $viewtitle = get_string('usersprofile', 'mahara', display_name($view->get('owner'), null, true));
    }
    else {
        $microheaderlinks = array(
            array(
                'name' => get_string('editdetails', 'view'),
                'url' => get_config('wwwroot') . 'view/edit.php?id=' . $viewid . '&amp;new=' . $new,
                'edit' => 1,
            ),
            array(
                'name' => get_string('editaccess', 'view'),
                'url' => get_config('wwwroot') . 'view/access.php?id=' . $viewid . '&amp;new=' . $new,
                'edit' => 1,
            ),
        );
    }
    $smarty->assign('microheaderlinks', $microheaderlinks);
}
$smarty->assign('userdisplayname', display_name($USER, null, true));
$smarty->assign('viewtype', $viewtype);
$smarty->assign('view', $view->get('id'));
$smarty->assign('groupid', $group);
$smarty->assign('institution', $institution);
$smarty->assign('can_change_layout', (!$USER->get_account_preference('addremovecolumns') || ($view->get('numcolumns') > 1 && $view->get('numcolumns') < 5)));

$smarty->assign('viewtheme', $viewtheme);
$smarty->assign('viewthemes', $allowedthemes);

$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtitle', $viewtitle);
if ($owner) {
    $smarty->assign('ownerlink', 'user/view.php?id=' . $owner);
}
else if ($group) {
    $smarty->assign('ownerlink', 'group/view.php?id=' . $group);
}

$blockid = $view->get_blockinstance_currently_being_configured();
if (!$blockid) {
    $blockid = param_integer('block', 0);
}
if ($blockid) {
    // Configuring a single block
    $bi = new BlockInstance($blockid);
    $smarty->assign('block', $bi->render_editing(true));
}
else {
    // The HTML for the columns in the view
    $columns = $view->build_columns(true);
    $smarty->assign('columns', $columns);
}

$smarty->display('view/blocks.tpl');

?>
