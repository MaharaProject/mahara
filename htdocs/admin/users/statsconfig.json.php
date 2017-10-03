<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'statistics.php');
require_once('institution.php');

$extradata = json_decode(param_variable('extradata'));
$institution = param_alphanum('institution', null);
if (empty($institution)) {
    if (isset($extradata->institution)) {
        $institution = $extradata->institution;
    }
    else if ($USER->get('admin') || $USER->get('staff')) {
        $institution = 'all';
    }
    else {
        $institution = 'mahara';
    }
}
$notallowed = false;

$allstaffstats = get_config('staffstats');
$userstaffstats = get_config('staffreports'); // The old 'Users/access list/masquerading' reports from users section

if (!$USER->get('admin') && !$USER->is_institutional_admin($institution) &&
    (!$USER->is_institutional_staff($institution) ||
    ($USER->is_institutional_staff($institution) && empty($allstaffstats) && empty($userstaffstats)))) {
   $notallowed = true;
}
if (!$notallowed) {
    // Get the institution selector to worl out what institutions they are allowed to see
    $institutionelement = get_institution_selector(true, false, true, ($allstaffstats || $userstaffstats), ($USER->get('admin') || $USER->get('staff')));
}
if (empty($institutionelement) || $notallowed) {
    json_reply(true, get_string('statistics', 'noaccessreport'));
    exit;
}

$type = param_alpha('type', 'users');
$subtype = param_alpha('subtype', $type);
$extraparams = new stdClass();
$extraparams->type = $type;
$extraparams->subtype = $subtype;
$extraparams->institution = $institution;
$extraparams->offset = param_integer('offset', 0);
$extraparams->limit  = param_integer('limit', 10);
$extraparams->sort = isset($extradata->sort) ? $extradata->sort : 'displayname';
$extraparams->sortdesc = isset($extradata->sortdesc) ? true : false;
$extraparams->start = param_alphanumext('start', null);
$extraparams->end = param_alphanumext('end', null);
$extraparams->field = isset($extradata->field) ? $extradata->field : (($institution == 'all') ? 'count_usr' : 'count_members');
$extraparams->extra = (array)$extradata;

$formarray = report_config_form($extraparams, $institutionelement);
$form = $formarray ? pieform($formarray) : '';

$reportinfo = get_string('reportdesc' . $subtype, 'statistics');
$reportdatestr = '<div class="alert alert-warning">';
if ($date = report_earliest_date($subtype, $institution)) {
    $reportdatestr .= get_string('earliestdate', 'statistics', $date);
}
else {
    $reportdatestr .= get_string('noearliestdate', 'statistics');
}
$reportdatestr .= '</div>';
$reportinfo .= $reportdatestr;

$tableheaders = '';
$function = $subtype . '_statistics_headers';
if (function_exists($function)) {
    $tableheaders = $function($extraparams->extra, null);
}

$smarty = smarty_core();
$smarty->assign('form', $form);
// $smarty->assign('tableheadings', $tableheaders);
$smarty->assign('reportinformation', $reportinfo);

$html = $smarty->fetch('admin/users/statsconfig.tpl');

$data['html'] = $html;
json_reply(false, (object) array('message' => false, 'data' => $data));
