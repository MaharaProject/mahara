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
require(get_config('libroot') . 'statistics.php');
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
    $institutionelement = get_institution_selector(true, false, true, get_config('staffstats'), ($USER->get('admin') || $USER->get('staff')));
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

$showall = ($institution == 'all') ? true : false;
if ($showall) {
    define('TITLE', get_string('statisticsforallinstitutions', 'admin'));
    $icon = 'icon-area-chart';
}
else {
    define('TITLE', get_string('institutionstatisticsfor', 'admin', get_field('institution', 'displayname', 'name', $institution)));
    $icon = 'icon-university';
}

$type = param_alpha('type', 'users');
$extraparams = new stdClass();
$extraparams->type = $type;
$extraparams->offset = param_integer('offset', 0);
$extraparams->limit  = param_integer('limit', 10);
$extraparams->sort = param_alphanumext('sort', 'displayname');
$extraparams->sortdesc = param_boolean('sortdesc');
$extraparams->start = param_alphanumext('start', null);
$extraparams->end = param_alphanumext('end', null);

list($subpages, $institutiondata, $subpagedata) = display_statistics($institution, $type, $extraparams);

$wwwroot = get_config('wwwroot');
$js = <<< EOF
jQuery(function ($) {
    {$subpagedata['table']['pagination_js']}

    function reloadStats() {
        window.location.href = '{$wwwroot}admin/users/statistics.php?institution='+$('#usertypeselect_institution').val() +'&type={$type}';
    }

    $('#usertypeselect_institution').on('change', reloadStats);
});
EOF;

$smarty = smarty(array('paginator','js/chartjs/Chart.min.js'));
setpageicon($smarty, $icon);

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('institutiondata', $institutiondata);
$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);
$smarty->assign('showall', ($showall ? '_all' : ''));
$smarty->assign('subpagedata', $subpagedata);

$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/statistics.tpl');
