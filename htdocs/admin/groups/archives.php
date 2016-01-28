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
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'managegroups/archives');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('archivedsubmissions', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'archives');

require_once('searchlib.php');

$search = (object) array(
    'query' => trim(param_variable('query', '')),
    'sortby' => param_alpha('sortby', 'firstname'),
    'sortdir' => param_alpha('sortdir', 'asc'),
);

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

if ($USER->get('admin')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search->institution = param_alphanum('institution', 'all');
}
else {
    $institutionnames = array_keys($USER->get('admininstitutions'));
    $institutions = get_records_select_array(
        'institution',
        'suspended = 0 AND name IN (' . join(',', array_fill(0, count($institutionnames), '?')) . ')',
        $institutionnames,
        'displayname'
    );
}

list($html, $columns, $pagination, $search) = build_admin_archived_submissions_results($search, $offset, $limit);

$js = <<<EOF
addLoadEvent(function() {
    var p = {$pagination['javascript']}

    new UserSearch(p);
})
EOF;

$smarty = smarty(array('adminexportqueue','paginator'), array(), array('ascending' => 'mahara', 'descending' => 'mahara'));
setpageicon($smarty, 'icon-users');

$smarty->assign('search', $search);
$smarty->assign('limit', $limit);
$smarty->assign('institutions', $institutions);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $search['url']);
$smarty->assign('sortby', $search['sortby']);
$smarty->assign('sortdir', $search['sortdir']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/groups/archives.tpl');
