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
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('groups'));
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');

$id = param_integer('id');

if (!$group = get_record_select('group', 'id = ? AND deleted = 0', array($id), '*, ' . db_format_tsfield('ctime'))) {
    throw new GroupNotFoundException("Couldn't find group with id $id");
}
$group->ctime = strftime('%e %B %Y', $group->ctime);

$group->admins = get_column_sql("SELECT member
    FROM {group_member}
    WHERE \"group\" = ?
    AND role = 'admin'", array($id));

$role = group_user_access($id);

// Search related stuff for member pager
$query  = trim(param_variable('query', ''));
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 5);
list($html, $pagination, $count, $offset) = group_get_membersearch_data($id, $query, $offset, $limit);

// Latest forums posts
// NOTE: it would be nicer if there was some generic way to get information 
// from any installed interaction. But the only interaction plugin is forum, 
// and group info pages might be replaced with views anyway...
$foruminfo = get_records_sql_array('
    SELECT
        p.id, p.subject, p.body, p.poster, p.topic, t.forum, pt.subject AS topicname
    FROM
        {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic)
        INNER JOIN {interaction_instance} i ON (i.id = t.forum)
        INNER JOIN {interaction_forum_post} pt ON (pt.topic = p.topic AND pt.parent IS NULL)
    WHERE
        i.group = ?
        AND i.deleted = 0
        AND t.deleted = 0
        AND p.deleted = 0
    ORDER BY
        p.ctime DESC
    LIMIT 5;
    ', array($id));
$smarty = smarty(array('paginator', 'groupmembersearch'), array(), array(), array('sideblocks' => array(interaction_sideblock($id, $role))));
$smarty->assign('group', $group);
$smarty->assign('groupid', $id);
$smarty->assign('query', $query);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('pagination_js', $pagination['javascript']);
$smarty->assign('foruminfo', $foruminfo);
$smarty->display('group/view.tpl');

?>
