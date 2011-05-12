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
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'deletetopic');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction' ,'forum');
require_once('group.php');
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . 'interaction/lib.php');

$topicid = param_integer('id');
$returnto = param_alpha('returnto', 'topic');

$topic = get_record_sql(
    'SELECT f.group, f.id AS forumid, f.title, g.name AS groupname, p.poster, p.subject, p.body, COUNT(p2.id), ' . db_format_tsfield('p.ctime', 'ctime') . ', t.closed, m.user AS moderator
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f ON (f.id = t.forum AND f.deleted != 1)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = ?)
    INNER JOIN {interaction_forum_post} p ON (p.topic = t.id AND p.parent IS NULL)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m ON (m.forum = t.forum AND m.user = p.poster)
    INNER JOIN {interaction_forum_post} p2 ON (p.poster = p2.poster AND p2.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p2.topic = t2.id)
    INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
    WHERE t.id = ?
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 9, 10, 11',
    array(0, $topicid)
);

if (!$topic) {
    throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
}

define('GROUP', $topic->group);

$membership = user_can_access_forum((int)$topic->forumid);

$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

$topic->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $topic->ctime);

if (!$moderator) {
    throw new AccessDeniedException(get_string('cantdeletetopic', 'interaction.forum'));
}

define('TITLE', $topic->title . ' - ' . get_string('deletetopicvariable', 'interaction.forum', $topic->subject));

$form = pieform(array(
    'name'     => 'deletetopic',
    'renderer' => 'div',
    'autofocus' => false,
    'elements' => array(
        'title' => array(
            'value' => get_string('deletetopicsure', 'interaction.forum'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . ($returnto == 'view' ? 'interaction/forum/view.php?id=' . $topic->forumid : 'interaction/forum/topic.php?id=' . $topicid),
        ),
        'forum' => array(
            'type' => 'hidden',
            'value' => $topic->forumid
        )
    )
));

function deletetopic_submit(Pieform $form, $values) {
    global $SESSION;
    $topicid = param_integer('id');
    // mark topic as deleted
    update_record(
        'interaction_forum_topic',
        array('deleted' => 1),
        array('id' => $topicid)
    );
    // mark relevant posts as deleted
    update_record(
        'interaction_forum_post',
        array('deleted' => 1),
        array('topic' => $topicid)
    );
    $SESSION->add_ok_msg(get_string('deletetopicsuccess', 'interaction.forum'));
    redirect('/interaction/forum/view.php?id=' . $values['forum']);
}

$smarty = smarty();
$smarty->assign('forum', $topic->title);
$smarty->assign('subheading', TITLE);
$smarty->assign('topic', $topic);
$smarty->assign('groupadmins', group_get_admin_ids($topic->group));
$smarty->assign('deleteform', $form);
$smarty->display('interaction:forum:deletetopic.tpl');
