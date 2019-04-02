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

$title = get_string('siteviewscollections', 'admin');
define('TITLE', $title);

$offset = param_integer('offset', 0);
$urlparams = array();

$templateviews = View::get_site_template_views();
list($searchform, $data, $pagination) = View::views_by_owner(null, 'mahara');
if ($data->data) {
    $views = array_merge($templateviews, $data->data);
}
else {
    $views = $templateviews;
}

$js = <<< EOF
jQuery(function() {
    {$pagination['javascript']}
    showmatchall();
EOF;
if ($offset > 0) {
    $js .= <<< EOF
    if ($('#myviews')) {
        $('#myviews a:first').trigger("focus");
    }
EOF;
}
else {
    $js .= <<< EOF
    if ($('#searchresultsheading').length) {
      $('#searchresultsheading')
      .addClass('hidefocus')
      .prop('tabIndex', -1)
      .trigger("focus");
    }
EOF;
}
$js .= '});';

$urlparams['institution'] = 'mahara';
$urlparamsstr = '&' . http_build_query($urlparams);

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-file-text');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $views);
$smarty->assign('institution', 'mahara');
$smarty->assign('urlparamsstr', $urlparamsstr);
$smarty->assign('sitetemplate', View::SITE_TEMPLATE);
$smarty->assign('querystring', get_querystring());
$smarty->assign('pagination', $pagination['html']);
$html = $smarty->fetch('view/indexresults.tpl');
$smarty->assign('viewresults', $html);
$smarty->assign('query', param_variable('query', null));
$smarty->assign('searchform', $searchform);
$smarty->display('view/index.tpl');
