<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');

define('GROUP', param_integer('id'));
$membershiptype = param_alpha('membershiptype', null);

$group = group_current_group();

define('TITLE', $group->name . ' - ' . get_string('Members', 'group'));

$role = group_user_access($group->id);

if (!empty($membershiptype) && $role != 'admin') {
    throw new AccessDeniedException();
}

// Search related stuff for member pager
$query  = trim(param_variable('query', ''));
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
list($html, $pagination, $count, $offset, $membershiptype) = group_get_membersearch_data($group->id, $query, $offset, $limit, $membershiptype);

$smarty = smarty(array('groupmembersearch'));
$smarty->assign('query', $query);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('membershiptype', $membershiptype);
$smarty->display('group/members.tpl');

?>
