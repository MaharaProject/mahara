<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('group.php');
safe_require('interaction' ,'forum');
define('TITLE', get_string('name','interaction.forum'));

$forumid = param_integer('id');
$offset = param_integer('offset', 0);
$userid = $USER->get('id');
$topicsperpage = 25;

$group = get_record_sql(
    'SELECT "group" as id
    FROM {interaction_instance}
    WHERE id = ?',
    array($forumid)
);

$membership = user_can_access_group((int)$group->id);

if (!$membership) {
    throw new AccessDeniedException();
}

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator($forumid);

$forum = get_record_sql(
    'SELECT f.title, f.description, f.id, COUNT(t.*), s.forum as subscribed
    FROM {interaction_instance} f
    LEFT JOIN {interaction_forum_topic} t
    ON (t.forum = f.id)
    LEFT JOIN {interaction_forum_subscription_forum} s
    ON (s.forum = f.id AND s."user" = ?)
    WHERE f.id=?
    GROUP BY 1, 2, 3, 5',
    array($userid, $forumid)
);

require_once('pieforms/pieform.php');

$forum->subscribe = pieform(array(
    'name'     => 'subscribe_forum',
    'elements' => array(
        'submit' => array(
            'type'  => 'submit',
            'value' => $forum->subscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
            )
    )
));

$stickytopics = get_records_sql_array(
    'SELECT t.id, p1.subject, p1.poster, COUNT(p2.*), t.closed, s.topic as subscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_forum_post} p1
    ON (p1.topic = t.id AND p1.parent is null)
    LEFT JOIN {interaction_forum_post} p2
    ON (p2.topic = t.id AND p2.deleted != 1)
    LEFT JOIN {interaction_forum_subscription_topic} s
    ON (s.topic = t.id AND s."user" = ?)
    WHERE t.forum = ?
    AND t.sticky = 1
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 5, 6
    ORDER BY MAX(p2.ctime) DESC',
    array($userid, $forumid)
);

$i=0;
if (!$forum->subscribe) {
    foreach ($stickytopics as $topic) {
        $topic->subscribe = pieform(array(
            'name'     => 'subscribe_topic'.$i++,
            'successcallback' => 'subscribe_topic_submit',
            'elements' => array(
                'submit' => array(
                    'type'  => 'submit',
                    'value' => $topic->subscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
                ),
                'topic' => array(
                    'type' => 'hidden',
                    'value' => $topic->id
                )
            )
        ));
    }
}

$regulartopics = get_records_sql_array(
    'SELECT t.id, p1.subject, p1.poster, COUNT(p2.*), t.closed, s.topic as subscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_forum_post} p1
    ON (p1.topic = t.id AND p1.parent is null)
    LEFT JOIN {interaction_forum_post} p2
    ON (p2.topic = t.id AND p2.deleted != 1)
    LEFT JOIN {interaction_forum_subscription_topic} s
    ON (s.topic = t.id AND s."user" = ?)
    WHERE t.forum = ?
    AND t.sticky != 1
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 5, 6
    ORDER BY MAX(p2.ctime) DESC',
    array($userid, $forumid),
    $offset,
    $topicsperpage
);

$i=0;
if (!$forum->subscribe) {
    foreach ($regulartopics as $topic) {
        $topic->subscribe = pieform(array(
            'name'     => 'subscribe_topic'.$i++,
            'successcallback' => 'subscribe_topic_submit',
            'elements' => array(
                'submit' => array(
                    'type'  => 'submit',
                    'value' => $topic->subscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
                ),
                'topic' => array(
                    'type' => 'hidden',
                    'value' => $topic->id
                )
            )
        ));
    }
}

$pagination = build_pagination(array(
    'url' => 'view.php?id='.$forumid,
    'count' => $forum->count,
    'limit' => $topicsperpage,
    'offset' => $offset,
));

$smarty = smarty();
$smarty->assign('forum', $forum);
$smarty->assign('moderator', $moderator);
$smarty->assign('admin', $admin);
$smarty->assign('stickytopics', $stickytopics);
$smarty->assign('regulartopics', $regulartopics);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('interaction:forum:view.tpl');

function subscribe_forum_submit(Pieform $form, $values) {
    global $USER;
    $forumid = param_integer('id');
    $offset = param_integer('offset', 0);
    if ($values['submit'] == get_string('subscribe', 'interaction.forum')) {
        insert_record(
            'interaction_forum_subscription_forum',
            (object)array(
                'forum' => $forumid,
                'user' => $USER->get('id')
            )
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_forum',
            'forum', $forumid,
            'user', $USER->get('id')
        );
        delete_records_sql(
            'DELETE FROM {interaction_forum_subscription_topic}
            WHERE topic IN (
                SELECT id
                FROM {interaction_forum_topic}
                WHERE forum = ?
            )',
            array($forumid)
        );
    }
    redirect('/interaction/forum/view.php?id=' . $forumid . '&offset=' . $offset);
}

function subscribe_topic_submit(Pieform $form, $values) {
    global $USER;
    $forumid = param_integer('id');
    $offset = param_integer('offset', 0);
    if ($values['submit'] == get_string('subscribe', 'interaction.forum')) {
        insert_record(
            'interaction_forum_subscription_topic',
            (object)array(
                'topic' => $values['topic'],
                'user' => $USER->get('id')
            )
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_topic',
            'topic', $values['topic'],
            'user', $USER->get('id')
        );
    }
    redirect('/interaction/forum/view.php?id=' . $forumid . '&offset=' . $offset);
}

?>
