<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Hugh Davenport <hugh@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('MENUITEM', 'manageinstitutions/statistics');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require(get_config('libroot') . 'registration.php');
require_once('institution.php');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institution = param_alphanum('institution', null);
$notallowed = false;
if (!empty($institution)) {
    $staffstats = get_config('staffstats');
    if (!$USER->get('admin') && !$USER->is_institutional_admin($institution) && (!$USER->is_institutional_staff($institution) || ($USER->is_institutional_staff($institution) && empty($staffstats)))) {
        $notallowed = true;
    }
}

if (!$notallowed) {
    $institutionelement = get_institution_selector(true, false, true, get_config('staffstats'));
}

if (empty($institutionelement) || $notallowed) {
    $smarty = smarty();
    $smarty->assign('CANCREATEINST', $USER->get('admin'));
    $smarty->display('admin/users/noinstitutionsstats.tpl');
    exit;
}

if (!$institution || !$USER->can_edit_institution($institution, true)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}
$institutionselector = pieform(array(
    'name' => 'usertypeselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));

define('TITLE', get_string('institutionstatisticsfor', 'admin', get_field('institution', 'displayname', 'name', $institution)));

$type = param_alpha('type', 'users');
$subpages = array('users', 'views', 'content', 'historical');
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

if (!in_array($type, $subpages)) {
    $type = 'users';
}

if ($type == 'historical') {
    $field = param_alphanumext('field', 'count_members');
}

$institutiondata = institution_statistics($institution, true);

switch ($type) {
case 'historical':
    $data = institution_historical_statistics($limit, $offset, $field, $institutiondata);
    break;
case 'content':
    $data = institution_content_statistics($limit, $offset, $institutiondata);
    break;
case 'views':
    $data = institution_view_statistics($limit, $offset, $institutiondata);
    break;
case 'users':
default:
    $data = institution_user_statistics($limit, $offset, $institutiondata);
}

$wwwroot = get_config('wwwroot');
$js = <<< EOF
addLoadEvent(function () {
    {$data['table']['pagination_js']}
});
function reloadStats() {
    window.location.href = '{$wwwroot}admin/users/statistics.php?institution='+$('usertypeselect_institution').value+'&type={$type}';
}
addLoadEvent(function() {
    connect($('usertypeselect_institution'), 'onchange', reloadStats);
});
EOF;

$smarty = smarty(array('paginator','js/chartjs/Chart.min.js'));
setpageicon($smarty, 'icon-university');

$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign('institutiondata', $institutiondata);
$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);

$smarty->assign('subpagedata', $data);

$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/statistics.tpl');
