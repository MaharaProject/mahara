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
    $inlinejs = "addLoadEvent( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";
    $stylesheets = array();
    $stylesheets = array_merge($stylesheets, $view->get_all_blocktype_css());

    // include slimbox2 js and css files, if it is enabled...
    if (get_config_plugin('blocktype', 'gallery', 'useslimbox2')) {
        $langdir = (get_string('thisdirection', 'langconfig') == 'rtl' ? '-rtl' : '');
        $stylesheets = array_merge($stylesheets, array('<script type="application/javascript" src="' . append_version_number(get_config('wwwroot') . 'lib/slimbox2/js/slimbox2.js') . '"></script>',
           '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'lib/slimbox2/css/slimbox2' . $langdir . '.css') . '">'
           ));
    }

    $viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.
    $smarty = smarty(
        $javascript,
        $stylesheets,
        array(),
        array(
            'stylesheets' => array('style/views.css'),
        )
    );

    if (get_config('homepageinfo') && $USER->get_account_preference('showhomeinfo')) {
        // allow the user to choose never to see the info boxes again
        $strhowtodisable = json_encode(get_string('howtodisable', 'mahara', get_config('wwwroot') . 'account'));
        $js = <<<JAVASCRIPT
function hideinfo() {
    var m = SPAN();
    m.innerHTML = {$strhowtodisable};
    slideUp('home-info-container', {afterFinish: function() {displayMessage(m, 'ok');}});
}

function nevershow() {
    var data = {'showhomeinfo' : 0};
    sendjsonrequest('homeinfo.json.php', data, 'POST', hideinfo);
}
addLoadEvent(function () {
    if ($('hideinfo')) {
        $('hideinfo').onclick = nevershow;
    }
});
JAVASCRIPT;

        $smarty->assign('INLINEJAVASCRIPT', $js . $inlinejs);
    }

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
    'friends' => $wwwroot . 'user/find.php',
    'groups'  => $wwwroot . 'group/find.php',
    'topics'  => $wwwroot . 'group/topics.php',
    'share'   => $wwwroot . 'view/share.php',
);
$smarty->assign('PAGEHEADING', null);
$smarty->assign('pagename', $pagename);
$smarty->assign('url', $urls);
$smarty->assign('page_content', get_site_page_content($pagename));
$smarty->display('index.tpl');
