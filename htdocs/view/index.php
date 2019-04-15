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
define('MENUITEM', 'create/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
define('TITLE', get_string('Viewscollections', 'view'));

$offset = param_integer('offset', 0);

list($searchform, $data, $pagination) = View::views_by_owner();

$js = <<< EOF
jQuery(function ($) {
    {$pagination['javascript']}
    showmatchall();
EOF;
if ($offset > 0) {
    $js .= <<< EOF
    if ($('#myviews').length) {
        $('#myviews a').trigger("focus");
    }
EOF;
}
else {
    $js .= <<< EOF
    if ($('#searchresultsheading').length) {
        $('#searchresultsheading').addClass('hidefocus')
            .prop('tabIndex', -1)
            .trigger("focus");
    }
EOF;
}
$js .= '});';

$groupid = param_integer('group', 0);
$institutionname = param_alphanum('institution', false);

$smarty = smarty(array('paginator', 'js/jquery/jquery-ui/js/jquery-ui.min.js'));
setpageicon($smarty, 'icon-file-text-o');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $data->data);
$smarty->assign('sitetemplate', View::SITE_TEMPLATE);
$smarty->assign('querystring', get_querystring());
$smarty->assign('pagination', $pagination['html']);
$html = $smarty->fetch('view/indexresults.tpl');

$smarty->assign('viewresults', $html);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('query', param_variable('query', null));
$smarty->assign('searchform', $searchform);
$smarty->display('view/index.tpl');
