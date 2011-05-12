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
define('PUBLIC', 1);
define('MENUITEM', '');
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

    $javascript = array('paginator', 'jquery');
    $javascript = array_merge($javascript, $view->get_blocktype_javascript());
    $stylesheets = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">');
    $smarty = smarty(
        $javascript,
        $stylesheets,
        array(),
        array(
            'stylesheets' => array('style/views.css'),
        )
    );

    if ($USER->get_account_preference('showhomeinfo')) {
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
    $('hideinfo').onclick = nevershow;
});
JAVASCRIPT;

        $smarty->assign('INLINEJAVASCRIPT', $js);
    }

    $smarty->assign('dashboardview', true);
    $smarty->assign('viewcontent', $view->build_columns());
    $smarty->assign('viewid', $view->get('id'));
}
else {
    $smarty = smarty();
}

// Assign urls used in homeinfo.tpl
$wwwroot = get_config('wwwroot');
$urls = array(
    'profile' => $wwwroot . 'artefact/internal',
    'files'   => $wwwroot . 'artefact/file',
    'resume'  => $wwwroot . 'artefact/resume',
    'blog'    => $wwwroot . 'artefact/blog',
    'views'   => $wwwroot . 'view',
    'friends' => $wwwroot . 'user/find.php',
    'groups'  => $wwwroot . 'group/find.php',
);
$smarty->assign('url', $urls);

$smarty->assign('page_content', get_site_page_content($pagename));
$smarty->display('index.tpl');
