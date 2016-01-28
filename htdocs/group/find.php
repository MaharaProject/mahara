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
define('MENUITEM', 'groups/find');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('findgroups'));
require_once('group.php');
require_once('searchlib.php');
$filter = param_alpha('filter', 'canjoin');
$offset = param_integer('offset', 0);
$groupcategory = param_signed_integer('groupcategory', 0);
$groupsperpage = 10;
$query = param_variable('query', '');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'find');

// check that the filter is valid, if not default to 'all'
if (in_array($filter, array('member', 'notmember', 'canjoin'))) {
    $type = $filter;
}
else { // all or some other text
    $filter = 'all';
    $type = 'all';
}
$elements = array();
$queryfield = array(
            'title' => get_string('search') . ': ',
            'hiddenlabel' => false,
            'type' => 'text',
            'class' => 'with-dropdown js-with-dropdown',
            'defaultvalue' => $query);
$filterfield = array(
            'title' => get_string('filter') . ': ',
            'hiddenlabel' => false,
            'type' => 'select',
            'class' => 'dropdown-connect js-dropdown-connect',
            'options' => array(
                'canjoin'   => get_string('groupsicanjoin', 'group'),
                'notmember' => get_string('groupsnotin', 'group'),
                'member'    => get_string('groupsimin', 'group'),
                'all'       => get_string('allgroups', 'group')
            ),
            'defaultvalue' => $filter);

$elements['searchwithin'] = array(
    'type' => 'fieldset',
    'class' => 'dropdown-group js-dropdown-group',
    'elements' => array(
        'query' => $queryfield,
        'filter' => $filterfield
    )
);


if (get_config('allowgroupcategories')
    && $groupcategories = get_records_menu('group_category','','','displayorder', 'id,title')
) {
    $options[0] = get_string('allcategories', 'group');
    $options[-1] = get_string('categoryunassigned', 'group');
    $options += $groupcategories;

    $groupcategoryfield = array(
        'title'        => get_string('groupcategory', 'group'),
        'hiddenlabel'  => false,
        'type'         => 'select',
        'options'      => $options,
        'defaultvalue' => $groupcategory,
        'class'        => 'input-small'
    );

    $searchfield = array(
        'type' => 'submit',
        'class' => 'btn-primary input-group-btn no-label button',
        'value' => get_string('search')
    );

    $elements['formgroupcategory'] = array(
        'type' => 'fieldset',
        'class' => 'input-group',
        'elements' => array(
            'groupcategory' => $groupcategoryfield,
            'search' => $searchfield
        )
    );

} else {

    $elements['searchfield'] = array(
        'type' => 'submit',
        'class' => 'btn-primary no-label',
        'value' => get_string('search')
    );

}


$searchform = pieform(array(
    'name'   => 'search',
    'checkdirtychange' => false,
    'method' => 'post',
    'class' => 'form-inline with-heading',
    'elements' => $elements
    )
);

$groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory);

// gets more data about the groups found by search_group
// including type if the user is associated with the group in some way
if ($groups['data']) {
    $groupids = array();
    foreach ($groups['data'] as $group) {
        $groupids[] = $group->id;
    }
    $groups['data'] =  get_records_sql_array("
        SELECT g1.id, g1.name, g1.description, g1.public, g1.jointype, g1.request, g1.grouptype, g1.submittableto,
            g1.hidemembers, g1.hidemembersfrommembers, g1.urlid, g1.role, g1.membershiptype, g1.membercount, COUNT(gmr.member) AS requests,
            g1.editwindowstart, g1.editwindowend
        FROM (
            SELECT g.id, g.name, g.description, g.public, g.jointype, g.request, g.grouptype, g.submittableto,
                g.hidemembers, g.hidemembersfrommembers, g.urlid, t.role, t.membershiptype, COUNT(gm.member) AS membercount,
                g.editwindowstart, g.editwindowend
            FROM {group} g
            LEFT JOIN {group_member} gm ON (gm.group = g.id)
            LEFT JOIN (
                SELECT g.id, 'admin' AS membershiptype, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
                UNION
                SELECT g.id, 'member' AS membershiptype, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ? AND gm.role != 'admin')
                UNION
                SELECT g.id, 'invite' AS membershiptype, gmi.role
                FROM {group} g
                INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
                UNION
                SELECT g.id, 'request' AS membershiptype, NULL as role
                FROM {group} g
                INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
            ) t ON t.id = g.id
            WHERE g.id IN (" . implode($groupids, ',') . ')
            GROUP BY g.id, g.name, g.description, g.public, g.jointype, g.request, g.grouptype, g.submittableto,
                g.hidemembers, g.hidemembersfrommembers, g.urlid, t.role, t.membershiptype, g.editwindowstart, g.editwindowend
        ) g1
        LEFT JOIN {group_member_request} gmr ON (gmr.group = g1.id)
        GROUP BY g1.id, g1.name, g1.description, g1.public, g1.jointype, g1.request, g1.grouptype, g1.submittableto,
            g1.hidemembers, g1.hidemembersfrommembers, g1.urlid, g1.role, g1.membershiptype, g1.membercount, g1.editwindowstart, g1.editwindowend
        ORDER BY g1.name',
        array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'))
    );
}

group_prepare_usergroups_for_display($groups['data'], 'find');

$params = array();
$params['filter'] = $filter;
if ($groupcategory != 0) {
    $params['groupcategory'] = $groupcategory;
}
if ($query) {
    $params['query'] = $query;
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/find.php' . ($params ? ('?' . http_build_query($params)) : ''),
    'count' => $groups['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'datatable' => 'findgroups',
    'jsonscript' => 'group/find.json.php',
    'setlimit' => true,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

function search_submit(Pieform $form, $values) {
    redirect('/group/find.php?filter=' . $values['filter'] . ((isset($values['query']) && ($values['query'] != '')) ? '&query=' . urlencode($values['query']) : '') . (!empty($values['groupcategory']) ? '&groupcategory=' . intval($values['groupcategory']) : ''));
}

$smarty = smarty(array('paginator'));
$smarty->assign('groups', $groups['data']);
$html = $smarty->fetch('group/mygroupresults.tpl');
$smarty->assign('groupresults', $html);
$smarty->assign('form', $searchform);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('pagination_js', $pagination['javascript']);
$smarty->display('group/find.tpl');
