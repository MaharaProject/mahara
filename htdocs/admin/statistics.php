<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('STAFF', 1);
define('MENUITEM', 'adminhome/statistics');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'registration.php');

define('TITLE', get_string('sitestatistics', 'admin'));

$type = param_alpha('type', 'users');
$subpages = array('users', 'groups', 'views', 'content', 'historical', 'institutions', 'logins');
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

if (!in_array($type, $subpages)) {
    $type = 'users';
}

if ($type == 'historical') {
    $field = param_alphanumext('field', 'count_usr');
}

if ($type == 'institutions' || $type == 'logins') {
    $sort = param_alphanumext('sort', 'displayname');
    $sortdesc = param_boolean('sortdesc');
    $start = param_alphanumext('start', null);
    $end = param_alphanumext('end', null);
}

$sitedata = site_statistics(true);

switch ($type) {
case 'logins':
    $data = institution_logins_statistics($limit, $offset, $sort, $sortdesc, $start, $end);
    break;
case 'institutions':
    $data = institution_comparison_statistics($limit, $offset, $sort, $sortdesc);
    break;
case 'historical':
    $data = historical_statistics($limit, $offset, $field);
    break;
case 'content':
    $data = content_statistics($limit, $offset);
    break;
case 'groups':
    $data = group_statistics($limit, $offset);
    break;
case 'views':
    $data = view_statistics($limit, $offset);
    break;
case 'users':
default:
    $data = user_statistics($limit, $offset, $sitedata);
}

$js = <<< EOF
addLoadEvent(function () {
    {$data['table']['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator','js/chartjs/Chart.min.js'));
setpageicon($smarty, 'icon-area-chart');

$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign('sitedata', $sitedata);

$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);

$smarty->assign('subpagedata', $data);

$smarty->display('admin/statistics.tpl');
