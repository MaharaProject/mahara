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

define('INTERNAL', 1);
define('MENUITEM', 'groups/topics');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
safe_require('interaction', 'forum');
define('TITLE', get_string('Topics', 'interaction.forum'));

if (!$USER->is_logged_in()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$from = '
    FROM
        {interaction_forum_topic} t
        JOIN {interaction_instance} f ON t.forum = f.id
        JOIN {group} g ON f.group = g.id
        JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ?)
        JOIN {interaction_forum_post} first ON (first.topic = t.id AND first.parent IS NULL)
        JOIN (
            SELECT DISTINCT ON (topic) topic, id, poster, subject, body, ctime
            FROM {interaction_forum_post} p
            WHERE p.deleted = 0
            ORDER BY topic, ctime DESC
        ) last ON last.topic = t.id';

$where = '
    WHERE g.deleted = 0 AND f.deleted = 0 AND t.deleted = 0';


$count = count_records_sql('SELECT COUNT(*) ' . $from . $where, array($USER->get('id')));

$select = '
    SELECT
        t.id, t.forum AS forumid, f.title AS forumname, g.id AS groupid, g.name AS groupname,
        first.subject AS topicname, first.poster AS firstpostby,
        last.id AS postid, last.poster, last.subject, last.body, last.ctime,
        COUNT(posts) AS postcount';

$from .= '
        LEFT JOIN {interaction_forum_post} posts ON posts.topic = t.id';

$sort = '
    GROUP BY
        t.id, t.forum, f.title, g.id, g.name,
        first.subject, first.poster,
        last.id, last.poster, last.subject, last.body, last.ctime
    ORDER BY last.ctime DESC';

$topics = get_records_sql_array($select . $from . $where . $sort, array($USER->get('id')), $offset, $limit);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/topics.php',
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty();
$smarty->assign('topics', $topics);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('group/topics.tpl');
