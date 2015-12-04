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
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 'all');
$setlimit = param_boolean('setlimit', false);
$groupcategory = param_signed_integer('groupcategory', 0);

$groupsperpage = param_integer('limit', 10);
$offset = (int)($offset / $groupsperpage) * $groupsperpage;

$results = group_get_associated_groups($USER->get('id'), $filter, $groupsperpage, $offset, $groupcategory);

$params = array();
if ($filter != 'all') {
    $params['filter'] = $filter;
}
if ($groupcategory != 0) {
    $params['groupcategory'] = $groupcategory;
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/mygroups.php' . (!empty($params) ? ('?' . http_build_query($params)) : ''),
    'count' => $results['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'setlimit' => true,
    'datatable' => 'mygroups',
    'jsonscript' => 'group/mygroups.json.php',
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

group_prepare_usergroups_for_display($results['groups'], 'mygroups');

$smarty = smarty_core();
$smarty->assign('groups', $results['groups']);
$smarty->assign('filter', $filter);
$html = $smarty->fetch('group/mygroupresults.tpl');

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $results['count'],
        'results' => $results['count'] . ' ' . ($results['count'] == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
    )
));
