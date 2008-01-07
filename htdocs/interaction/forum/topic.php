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
safe_require('interaction', 'forum');
require_once('group.php');

$topicid = param_integer('id');

$topic = get_record_sql(
    'SELECT p.subject, p.poster, p.id AS firstpost, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.id, f.group, g.name AS groupname, f.id AS forumid, f.title AS forumtitle, t.closed, sf.forum AS forumsubscribed, st.topic AS topicsubscribed, g.owner
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    INNER JOIN {group} g ON g.id = f.group
    INNER JOIN {interaction_forum_post} p ON (p.topic = t.id AND p.parent IS NULL)
    LEFT JOIN {interaction_forum_subscription_forum} sf ON (sf.forum = f.id AND sf.user = ?)
    LEFT JOIN {interaction_forum_subscription_topic} st ON (st.topic = t.id AND st.user = ?)
    WHERE t.id = ?
    AND t.deleted != 1',
    array($USER->get('id'), $USER->get('id'), $topicid)
);

if (!$topic) {
    throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
}

$membership = user_can_access_group((int)$topic->group);

if (!$membership) {
    throw new AccessDeniedException(get_string('cantviewtopic', 'interaction.forum'));
}

$admin = (bool)($membership & (GROUP_MEMBERSHIP_OWNER | GROUP_MEMBERSHIP_ADMIN | GROUP_MEMBERSHIP_STAFF));

$moderator = $admin || is_forum_moderator((int)$topic->forumid);

$topic->canedit = $moderator || user_can_edit_post($topic->poster, $topic->ctime);

define('TITLE', $topic->forumtitle . ' - ' . $topic->subject);

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'group/view.php?id=' . $topic->group,
        $topic->groupname
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/index.php?group=' . $topic->group,
        get_string('nameplural', 'interaction.forum')
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/view.php?id=' . $topic->forumid,
        $topic->forumtitle
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
        $topic->subject
    )
);

require_once('pieforms/pieform.php');

if (!$topic->forumsubscribed) {
    $topic->subscribe = pieform(array(
        'name'     => 'subscribe',
        'autofocus' => false,
        'elements' => array(
            'submit' => array(
               'type'  => 'submit',
               'value' => $topic->topicsubscribed ? get_string('unsubscribefromtopic', 'interaction.forum') : get_string('subscribetotopic', 'interaction.forum')
            ),
            'topic' => array(
                'type' => 'hidden',
                'value' => $topicid
            ),
            'type' => array(
                'type' => 'hidden',
                'value' => $topic->topicsubscribed ? 'unsubscribe' : 'subscribe'
            )
        )
   ));
}

$posts = get_records_sql_array(
    'SELECT p1.id, p1.parent, p1.poster, p1.subject, p1.body, ' . db_format_tsfield('p1.ctime', 'ctime') . ', p1.deleted, m.user AS moderator, COUNT(p2.*), ' . db_format_tsfield('e.ctime', 'edittime') . ', e.user AS editor, m2.user as editormoderator
    FROM {interaction_forum_post} p1
    INNER JOIN {interaction_forum_topic} t ON (t.id = p1.topic)
    INNER JOIN {interaction_forum_post} p2 ON (p1.poster = p2.poster AND p2.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p2.topic = t2.id)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1 AND f.group = ?)
    LEFT JOIN (
        SELECT fm.user, fm.forum
        FROM {interaction_forum_moderator} fm
        INNER JOIN {interaction_instance} f ON (fm.forum = f.id)
        INNER JOIN {group_member} gm ON (gm.group = f.group AND gm.member = fm.user)
    ) m oN (t.forum = m.forum AND p1.poster = m.user)
    LEFT JOIN {interaction_forum_edit} e ON e.post = p1.id
    LEFT JOIN {interaction_forum_post} p3 ON p3.id = e.post
    LEFT JOIN {interaction_forum_topic} t3 ON t3.id = p3.topic
    LEFT JOIN (
        SELECT fm.user, fm.forum
        FROM {interaction_forum_moderator} fm
        INNER JOIN {interaction_instance} f ON (fm.forum = f.id)
        INNER JOIN {group_member} gm ON (gm.group = f.group AND gm.member = fm.user)
    ) m2 ON t3.forum = m2.forum AND e.user = m2.user
    WHERE p1.topic = ?
    GROUP BY 1, 2, 3, 4, 5, p1.ctime, 7, 8, 10, 11, 12, e.ctime
    ORDER BY p1.ctime, p1.id, e.ctime',
    array($topic->group, $topicid)
);

// $posts has an object for every edit to a post
// this combines all the edits into a single object for each post
// also formats the edits a bit
$count = count($posts);
for ($i = 0; $i < $count; $i++) {
    $posts[$i]->canedit = $posts[$i]->parent && ($moderator || user_can_edit_post($posts[$i]->poster, $posts[$i]->ctime));
    $posts[$i]->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $posts[$i]->ctime);
    $postedits = array();
    if ($posts[$i]->editor) {
        $postedits[] = array('editor' => $posts[$i]->editor, 'edittime' => relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $posts[$i]->edittime), 'moderator' => $posts[$i]->editormoderator);
    }
    $temp = $i;
    while (isset($posts[$i+1]) && $posts[$i+1]->id == $posts[$temp]->id) { // while the next object is the same post
        $i++;
        $postedits[] = array('editor' => $posts[$i]->editor, 'edittime' => relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $posts[$i]->edittime), 'moderator' => $posts[$i]->editormoderator);
        unset($posts[$i]);
    }
    $posts[$temp]->edit = $postedits;
}

// builds the first post (with index 0) which has as children all the posts in the topic
$posts = buildpost(0, '', $posts);

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('heading', TITLE);
$smarty->assign('topic', $topic);
$smarty->assign('moderator', $moderator);
$smarty->assign('posts', $posts);
$smarty->display('interaction:forum:topic.tpl');

/**
 * builds a post (including its children)
 *
 * @param int $postindex the index of the post
 * @param string $parentsubject the subject of the parent
 * @param array $posts the posts in the topic
 *
 * @returns string the html for the post
 */

function buildpost($postindex, $parentsubject, &$posts){
    global $moderator, $topic;
    $localposts = $posts;
    if ($posts[$postindex]->subject) {
        $parentsubject = $posts[$postindex]->subject;
    }
    else {
        $posts[$postindex]->subject = get_string('re', 'interaction.forum', $parentsubject);
    }
    $children = array();
    foreach ($localposts as $index => $post) {
        if ($posts[$index]->parent == $posts[$postindex]->id) {
            $children[] = buildpost($index, $parentsubject, $posts);
        }
    }
    $smarty = smarty_core();
    $smarty->assign('post', $posts[$postindex]);
    $smarty->assign('groupowner', $topic->owner);
    $smarty->assign('children', $children);
    $smarty->assign('moderator', $moderator);
    $smarty->assign('closed', $topic->closed);
    return $smarty->fetch('interaction:forum:post.tpl');
}

function subscribe_submit(Pieform $form, $values) {
    global $USER;
    if ($values['type'] == 'subscribe') {
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
    redirect('/interaction/forum/topic.php?id=' . $values['topic']);
}

?>
