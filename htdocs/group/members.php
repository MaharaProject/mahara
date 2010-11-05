<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('PUBLIC', 1);
define('INTERNAL', 1);
define('MENUITEM', 'groups/members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');

define('GROUP', param_integer('id'));
$membershiptype = param_variable('membershiptype', '');

$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name . ' - ' . get_string('Members', 'group'));

$role = group_user_access($group->id);

if (!empty($membershiptype) && $role != 'admin') {
    throw new AccessDeniedException();
}

$remove = param_variable('removeuser', null);
$member = param_integer('member', null);
if ($remove && $member) {
    // Create the remove user pieform for the user that's being removed.
    // The form's submit function will be called as soon as the form
    // is generated.
    //
    // We do this now because the user could be on the 2nd page of
    // results, so their remove form might never get generated on
    // this page.  And also because generating the rest of the page
    // would be a waste of time -- the submit function just redirects
    // back here.
    group_get_removeuser_form($member, $group->id);
}

// Search related stuff for member pager
$query  = trim(param_variable('query', ''));
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$results = get_group_user_search_results($group->id, $query, $offset, $limit, $membershiptype);
list($html, $pagination, $count, $offset, $membershiptype) = group_get_membersearch_data($results, $group->id, $query, $membershiptype);

// Type-specific instructions
$instructions = '';
if ('admin' == $role) {
    if ('invite' == $group->jointype) {
        $url = get_config('wwwroot') . 'group/inviteusers.php?id=' . GROUP;
        $instructions = get_string('membersdescription:invite', 'group', $url);
    }
    else if ('controlled' == $group->jointype) {
        $url = get_config('wwwroot') . 'group/addmembers.php?id=' . GROUP;
        $instructions = get_string('membersdescription:controlled', 'group', $url);
    }
}

$searchform = pieform(array(
    'name' => 'search',
    'renderer' => 'oneline',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $group->id
        ),
        'membershiptype' => array(
            'type' => 'hidden',
            'value' => $membershiptype
        ),
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $query
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('search')
        )
    )
));

$js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
    connect('search_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'query': $('search_query').value, 'id':$('search_id').value, 'membershiptype':$('search_membershiptype').value};
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

$smarty = smarty(array('paginator'));

if ($role == 'admin') {
    $membershiptypes = array();
    $membershiptypes[] = array(
        'name' => get_string('current', 'group'),
        'link' => empty($membershiptype) ? '' : $CFG->wwwroot.'group/members.php?id='.$group->id
        );
    if ($group->jointype == 'request' && count_records('group_member_request', 'group', $group->id)) {
        $membershiptypes[] = array(
            'name' => get_string('requests', 'group'),
            'link' => $membershiptype == 'request' ? '' : $CFG->wwwroot.'group/members.php?id='.$group->id.'&membershiptype=request'
            );
    }
    if ($group->jointype == 'invite' && count_records('group_member_invite', 'group', $group->id)) {
        $membershiptypes[] = array(
            'name' => get_string('invites', 'group'),
            'link' => $membershiptype == 'invite' ? '' : $CFG->wwwroot.'group/members.php?id='.$group->id.'&membershiptype=invite'
            );
    }
    if (count($membershiptypes) > 1) {
        $smarty->assign('membershiptypes', $membershiptypes);
    }
}

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('heading', $group->name);
$smarty->assign('form', $searchform);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('instructions', $instructions);
$smarty->assign('membershiptype', $membershiptype);
$smarty->display('group/members.tpl');

function search_submit(Pieform $form, $values) {
    redirect('/group/members.php?id=' . $values['id'] . (!empty($values['query']) ? '&query=' . urlencode($values['query']) : '') . (!empty($values['membershiptype']) ? '&membershiptype=' . urlencode($values['membershiptype']) : ''));
}
