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
define('MENUITEM', 'home');
define('HOME', 1);
require('init.php');

// Check for whether the user is logged in, before processing the page. After
// this, we can guarantee whether the user is logged in or not for this page.
if (!$USER->is_logged_in()) {
    define('TITLE', get_string('home'));
    $pagename = 'loggedouthome';
}
else {
    define('TITLE', get_string('dashboard', 'view'));
    $pagename = 'home';
}

if ($USER->is_logged_in()) {
    // get the user's dashboard view
    require_once(get_config('libroot') . 'view.php');
    $view = $USER->get_view_by_type('dashboard');

    $javascript = array('paginator');
    $blocktype_js = $view->get_all_blocktype_javascript();
    $javascript = array_merge($javascript, $blocktype_js['jsfiles']);
    $inlinejs = "jQuery( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";
    $stylesheets = array();
    $stylesheets = array_merge($stylesheets, $view->get_all_blocktype_css());

    // Set up skin, if the page has one
    $viewskin = $view->get('skin');
    if ($viewskin && get_config('skins') && can_use_skins($view->get('owner'), false, false) && (!isset($THEME->skins) || $THEME->skins !== false)) {
        $skin = array('skinid' => $viewskin, 'viewid' => $view->get('id'));
    }
    else {
        $skin = false;
    }

    $viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.
    $smarty = smarty(
        $javascript,
        $stylesheets,
        array(),
        array(
            'stylesheets' => array('style/views.css'),
            'skin' => $skin,
        )
    );

    $js = '';
    if (get_config('homepageinfo') && $USER->get_account_preference('showhomeinfo')) {
        // allow the user to choose never to see the info boxes again
        $strhowtodisable = json_encode(get_string('howtodisable', 'mahara', get_config('wwwroot') . 'account'));
        $js = <<<JAVASCRIPT
jQuery(function($) {
    function hideinfo() {
        var m = $('<span>');
        m.html({$strhowtodisable});
        $('#home-info-container').slideUp('fast', function() { displayMessage(m, 'ok'); });
    }

    function nevershow() {
        var data = {'showhomeinfo' : 0};
        sendjsonrequest('homeinfo.json.php', data, 'POST', hideinfo);
    }

    if ($('#hideinfo').length) {
        $('#hideinfo').on('click', nevershow);
    }
});
JAVASCRIPT;

    }
    $smarty->assign('INLINEJAVASCRIPT', $js . $inlinejs);

    $smarty->assign('dashboardview', true);
    $smarty->assign('viewcontent', $viewcontent);
    $smarty->assign('viewid', $view->get('id'));
}
else {
    $smarty = smarty();
    // Used to set a 'loggedout' class on body tag for styling purposes
    $smarty->assign('loggedout', true);
}

// Assign urls used in homeinfo.tpl
$wwwroot = get_config('wwwroot');
$urls = array(
    'profile' => $wwwroot . 'artefact/internal/index.php',
    'files'   => $wwwroot . 'artefact/file/index.php',
    'resume'  => $wwwroot . 'artefact/resume/index.php',
    'blog'    => $wwwroot . 'artefact/blog/index.php',
    'views'   => $wwwroot . 'view/index.php',
    'friends' => $wwwroot . 'user/index.php?filter=current',
    'groups'  => $wwwroot . 'group/index.php',
    'topics'  => $wwwroot . 'group/topics.php',
    'share'   => $wwwroot . 'view/share.php',
);
$smarty->assign('PAGEHEADING', null);
$smarty->assign('pagename', $pagename);
$smarty->assign('url', $urls);
$smarty->assign('page_content', get_site_page_content($pagename));
$smarty->display('index.tpl');
