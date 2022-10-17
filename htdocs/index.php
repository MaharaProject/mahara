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
    $viewcontent = '';
    $layoutjs = array();
    if ($newlayout = $view->uses_new_layout()) {
        $layoutjs = array('js/gridstack/gridstack_modules/gridstack-h5.js', 'js/gridlayout.js');
    }

    $javascript = array('paginator');
    $javascript = array_merge($javascript, $layoutjs);
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
    if ($newlayout) {
        $blocks = $view->get_blocks();
        $blocks = json_encode($blocks);
        $blocksjs = <<<EOF
        $(function () {
            var options = {
                margin: 1,
                cellHeight: 10,
                disableDrag : true,
                disableResize: true
            };
            var blocks = {$blocks};
            var grid = GridStack.init(options);
            loadGrid(grid, blocks);
        });
EOF;
    }
    else {
        $viewcontent = $view->build_rows(); // Build content before initialising smarty in case pieform elements define headers.
        $blocksjs = "$(function () {jQuery(document).trigger('blocksloaded');});";
    }
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

    // Disable the modal_links for images etc... when page loads
    $('a[class*=modal_link], a[class*=inner-link]').addClass('no-modal');
    $('a[class*=modal_link], a[class*=inner-link]').css('cursor', 'default');
});
JAVASCRIPT;

    }
    $smarty->assign('INLINEJAVASCRIPT', $blocksjs . $js . $inlinejs);

    $smarty->assign('dashboardview', true);
    $smarty->assign('newlayout', $newlayout);
    if (!$newlayout) {
        $smarty->assign('viewcontent', $viewcontent);
    }
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
if ($SESSION->get('saml_logout')) {
    // Allow the template call the iframe breaker
    $SESSION->set('saml_logout', null);
    $smarty->assign('saml_logout', true);
}
$smarty->display('index.tpl');
