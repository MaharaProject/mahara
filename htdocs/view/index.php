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
define('MENUITEM', 'myportfolio/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
define('TITLE', get_string('Views', 'view'));

$offset = param_integer('offset', 0);

list($searchform, $data, $pagination) = View::views_by_owner();

$js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
EOF;
if ($offset > 0) {
    $js .= <<< EOF
    if ($('myviews')) {
        getFirstElementByTagAndClassName('a', null, 'myviews').focus();
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

$createviewform = pieform(create_view_form());

$smarty = smarty(array('paginator'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $data->data);
$smarty->assign('querystring', get_querystring());
$html = $smarty->fetch('view/indexresults.tpl');
$smarty->assign('viewresults', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('query', param_variable('query', null));
$smarty->assign('searchform', $searchform);
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');
