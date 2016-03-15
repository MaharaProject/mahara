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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');

$id     = param_integer('id');
$query  = trim(param_variable('query', ''));
$setlimit = param_boolean('setlimit', false);
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$sortoptionidx = param_alpha('sortoption', 'adminfirst');

define('GROUP', $id);
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$role = group_user_access($group->id);

if (!$USER->get('admin') && !$USER->get('staff')) {
    if (!$role && ($group->hidemembers || $group->hidemembersfrommembers)) {
        json_reply('local', get_string('accessdenied', 'error'));
    }
    if ($role != 'admin' && $group->hidemembersfrommembers) {
        json_reply('local', get_string('accessdenied', 'error'));
    }
}

$membershiptype = param_variable('membershiptype', '');
$friends = param_integer('friends', 0);
if (!empty($membershiptype)) {
    if ($role != 'admin') {
        // Non-admins are allowed to find the 'notinvited' users, but only if 'invitefriends'
        // or 'suggestfriends' is enabled, and they're filtering by their friends list
        if ($membershiptype != 'notinvited' || !$role || !($group->invitefriends || $group->suggestfriends) || !$friends) {
            json_reply('local', get_string('accessdenied', 'error'));
        }
    }
}

$results = get_group_user_search_results(
    $group->id, $query, $offset, $limit, $membershiptype, null,
    $friends ? $USER->get('id') : null,
    $sortoptionidx
);
if (!param_integer('html', 1)) {
    foreach ($results['data'] as &$result) {
        $result = array('id' => $result['id'], 'name' => display_name($result['id'], $USER->get('id')));
    }
    json_reply(false, $results);
}

list($html, $pagination, $count, $offset, $membershiptype) = group_get_membersearch_data($results, $id, $query, $membershiptype, $setlimit, $sortoptionidx);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $count,
        'results' => $count . ' ' . ($count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
        'membershiptype' => $membershiptype,
        'sortoption' => $sortoptionidx,
    )
));
