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

$formarray = report_config_form($extraparams);
$form = $formarray ? pieform($formarray) : '';

$reportinfo = get_string('reportdesc' . $subtype, 'statistics');

$smarty = smarty_core();
$smarty->assign('form', $form);
$smarty->assign('reportinformation', $reportinfo);

$html = $smarty->fetch('admin/users/statsconfig.tpl');

$data['html'] = $html;
json_reply(false, (object) array('message' => false, 'data' => $data));
