<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype/mygroups
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * returns all user's groups for the mygroups block
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('blocktype', 'mygroups');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$offset = param_integer('offset', 0);
$id = param_integer('id');
$blockid = param_integer('block');
$view = new View($id);

if (!can_view_view($view)) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$bi = new Blockinstance($blockid);
$configdata = $bi->get('configdata');
$sort = !empty($configdata['sort']) ? $configdata['sort'] : null;
$limit = !empty($configdata['limitto']) ? $configdata['limitto'] : null;
$baseurl = $bi->get_view()->get_url();
$baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $bi->get('id');

$userid = $bi->get_view()->get('owner');
if (!$userid) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$smarty = smarty_core();

// Group stuff
if (!empty($limit)) {
    list($usergroups, $count) = group_get_user_groups($userid, null, $sort, $limit, $offset);
}
else {
    $usergroups = group_get_user_groups($userid, null, $sort);
    $count = count($usergroups);
}

foreach ($usergroups as $group) {
    $group->roledisplay = get_string($group->role, 'grouptype.' . $group->grouptype);
}
$groups = array('data' => $usergroups,
                'count' => $count,
                'limit' => $limit,
                'offset' => $offset,
                );
$pagination = array(
                    'baseurl' => $baseurl,
                    'id' => 'mygroups_pagination',
                    'datatable' => 'usergroupstable',
                    'jsonscript' => 'blocktype/mygroups/mygroups.json.php',
                    'resultcounttextsingular' => get_string('group', 'group'),
                    'resultcounttextplural' => get_string('groups', 'group'),
                    );
PluginBlocktypeMygroups::render_items($groups, 'blocktype:mygroups:mygroupslist.tpl', $configdata, $pagination);

json_reply(false, array('data' => $groups));
