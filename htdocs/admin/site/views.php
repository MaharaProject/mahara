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
define('ADMIN', 1);
define('MENUITEM', 'configsite/siteviews');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'siteviews');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$title = get_string('siteviews', 'admin');
define('TITLE', $title);

$offset = param_integer('offset', 0);

$templateviews = View::get_site_template_views();
list($searchform, $data, $pagination) = View::views_by_owner(null, 'mahara');
if ($data->data) {
    $views = array_merge($templateviews, $data->data);
}
else {
    $views = $templateviews;
}

$js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
EOF;
if ($offset > 0) {
    $js .= <<< EOF
    if ($('myviews')) {
        getFirstElementByTagAndClassName('a', null, 'myviews'). focus();
    }
EOF;
}
else {
    $js .= <<< EOF
    if ($('searchresultsheading')) {
        addElementClass('searchresultsheading', 'hidefocus');
        setNodeAttribute('searchresultsheading', 'tabIndex', -1);
        $('searchresultsheading').focus();
    }
EOF;
}
$js .= '});';

$createviewform = pieform(create_view_form(null, 'mahara'));

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-file-text');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $views);
$smarty->assign('institution', 'mahara');
$smarty->assign('querystring', get_querystring());
$html = $smarty->fetch('view/indexresults.tpl');
$smarty->assign('viewresults', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('query', param_variable('query', null));

$smarty->assign('searchform', $searchform);
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');
