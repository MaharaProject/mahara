<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */


define('INTERNAL', 1);
define('MENUITEM', 'content/plans');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

define('TITLE', get_string('Plans','artefact.plans'));

if (!PluginArtefactPlans::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('plans','artefact.plans')));
}

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$plans = ArtefactTypePlan::get_plans($offset, $limit);
ArtefactTypePlan::build_plans_list_html($plans);

$js = <<< EOF
addLoadEvent(function () {
    {$plans['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('plans', $plans);
$smarty->assign('strnoplansaddone',
    get_string('noplansaddone', 'artefact.plans',
    '<a href="' . get_config('wwwroot') . 'artefact/plans/new.php">', '</a>'));
$smarty->assign('PAGEHEADING', hsc(get_string("Plans", "artefact.plans")));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:plans:index.tpl');
