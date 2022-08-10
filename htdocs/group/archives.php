<?php
/**
 * Group archive page.
 *
 * @package    mahara
 * @subpackage core
 * @author     Melissa Draper (Catalyst IT Limited) <melissa@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('group.php');
require_once('searchlib.php');

define('TITLE', get_string('archives', 'group'));
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'archives');
define('GROUP', param_integer('group'));
define('SUBSECTIONHEADING', get_string('grouparchivereportsheading', 'group'));

$wwwroot = get_config('wwwroot');
$limit = param_integer('limit', 0);
$limit = user_preferred_limit($limit, 'itemsperpage');
$offset = param_integer('offset', 0);
$search = (object) array(
    'query' => trim(param_variable('query', '')),
    'sortby' => param_alpha('sortby', 'firstname'),
    'sortdir' => param_alpha('sortdir', 'asc'),
    'group' => param_integer('group'),
);

$group = group_current_group();
$role = group_user_access($group->id);
if (!group_role_can_access_archives($group, $role)) {
    throw new AccessDeniedException();
}

$institutions = array($group->institution);

list($html, $columns, $pagination, $search) = build_group_archived_submissions_results($search, $offset, $limit);

$js = <<<EOF
jQuery(function() {
    var p = {$pagination['javascript']}

    new UserSearch(p);
})
EOF;

$smarty = smarty(array('adminusersearch', 'adminexportqueue','paginator'), array(), array('ascending' => 'mahara', 'descending' => 'mahara'));
$smarty->assign('search', $search);
$smarty->assign('query', trim(param_variable('query', '')));
$smarty->assign('limit', $limit);
$smarty->assign('group', $group->id);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $search['urlshort']);
$smarty->assign('sortby', $search['sortby']);
$smarty->assign('sortdir', $search['sortdir']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('group/archives.tpl');
