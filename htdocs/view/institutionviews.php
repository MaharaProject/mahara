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

define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/institutionviews');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'institution.php');

$institution = param_alpha('institution', false);
$offset = param_integer('offset', 0);

if ($institution == 'mahara') {
    redirect('/admin/site/views.php');
}
$urlparams = array();

$s = institution_selector_for_page($institution,
                                   get_config('wwwroot') . 'view/institutionviews.php');

$institution = $s['institution'];

define('TITLE', get_string('institutionviewscollections', 'view'));

if ($institution === false) {
    $smarty = smarty();
    setpageicon($smarty, 'icon-university');
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

list($searchform, $data, $pagination) = View::views_by_owner(null, $institution);

$js = <<< EOF
jQuery(function () {
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
$js .= <<< EOF
});

{$s['institutionselectorjs']}
EOF;

$urlparamsstr = '';
if (!empty($institution)) {
    $urlparams['institution'] = $institution;
    $urlparamsstr = '&' . http_build_query($urlparams);
}

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-university');

$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('views', $data->data);
$smarty->assign('institution', $institution);
$smarty->assign('urlparamsstr', $urlparamsstr);
$smarty->assign('sitetemplate', View::SITE_TEMPLATE);
$smarty->assign('querystring', get_querystring());
$smarty->assign('pagination', $pagination['html']);
$html = $smarty->fetch('view/indexresults.tpl');
$smarty->assign('viewresults', $html);
$smarty->assign('query', param_variable('query', null));
$smarty->assign('searchform', $searchform);
$smarty->display('view/index.tpl');
