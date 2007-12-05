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
define('TITLE', get_string('topic','interaction.forum'));

$topicid = param_integer('id');

$topic = get_record_sql(
    'SELECT p.subject, f.group, f.id AS forumid, f.title as forumtitle, t.closed, t.id, sf.forum AS forumsubscribed, st.topic AS topicsubscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f
    ON t.forum = f.id
    AND f.deleted != 1
    INNER JOIN {interaction_forum_post} p
    ON p.topic = t.id
    AND p.parent IS NULL
    LEFT JOIN {interaction_forum_subscription_forum} sf
    ON sf.forum = f.id
    AND sf.user = ?
    LEFT JOIN {interaction_forum_subscription_topic} st
    ON st.topic = t.id
    AND st.user = ?
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

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator((int)$topic->forumid);

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'interaction/forum/index.php?group=' . $topic->group,
        get_string('nameplural', 'interaction.forum')
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/view.php?id=' . $topic->forumid,
        $topic->forumtitle
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/topic?id=' . $topic->id,
        $topic->subject
    )
);

require_once('pieforms/pieform.php');

if (!$topic->forumsubscribed) {
    $topic->subscribe = pieform(array(
        'name'     => 'subscribe',
        'elements' => array(
            'submit' => array(
               'type'  => 'submit',
               'value' => $topic->topicsubscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
            )
        )
   ));
}

$posts = get_records_sql_array(
    'SELECT p1.id, p1.parent, p1.poster, p1.subject, p1.body, p1.ctime AS posttime, p1.deleted, COUNT(p2.*), e.ctime AS edit, e.user AS editor
    FROM {interaction_forum_post} p1
    INNER JOIN {interaction_forum_post} p2
    ON p1.poster = p2.poster
    AND p2.deleted != 1
    INNER JOIN {interaction_forum_topic} t
    ON t.deleted != 1
    AND p2.topic = t.id
    INNER JOIN {interaction_instance} f
    ON t.forum = f.id
    AND f.deleted != 1
    AND f.group = ?
    LEFT JOIN {interaction_forum_edit} e
    ON (e.post = p1.id)
    WHERE p1.topic = ?
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 9, 10
    ORDER BY p1.ctime',
    array($topic->group, $topicid)
);

$count = count($posts);
for ($i = 0; $i < $count; $i++) {
    $postedits = array();
    if (!empty($posts[$i]->edit)) {
        $postedits[] = array(
            'edittime' => $posts[$i]->edit,
            'editor'   => $posts[$i]->editor
        );
    }
    $temp = $i;
    while (isset($posts[$i+1]) && $posts[$i+1]->id == $posts[$temp]->id) {
        $i++;
        $postedits[] = array(
            'edittime' => $posts[$i]->edit,
            'editor'   => $posts[$i]->editor
        );
        unset($posts[$i]);
    }
    $posts[$temp]->edit = $postedits;
}

foreach ($posts as $post) {
    if ($post->poster == $USER->get('id') && (time() - strtotime($post->posttime)) < (30 * 60)) {
        $post->editor = true;
    }
    else {
        $post->editor = false;
    }
}

$threadedposts = buildthread(0, '', $posts);

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('topic', $topic);
$smarty->assign('moderator', $moderator);
$smarty->assign('posts', $threadedposts);
$smarty->display('interaction:forum:topic.tpl');

function buildthread($parent, $parentsubject, &$posts){
    global $moderator;
    global $topic;
    if ($posts[$parent]->subject) {
        $parentsubject = $posts[$parent]->subject;
    }
    else {
        $posts[$parent]->subject = get_string('re', 'interaction.forum', $parentsubject);
    }
    $children = array();
    foreach ($posts as $index => $post) {
        if ($posts[$index]->parent == $posts[$parent]->id) {
            $children[] = buildthread($index, $parentsubject, $posts);
        }
    }
    $smarty = smarty_core();
    $smarty->assign('post', $posts[$parent]);
    $smarty->assign('children', $children);
    $smarty->assign('moderator', $moderator);
    $smarty->assign('closed', $topic->closed);
    return $smarty->fetch('interaction:forum:post.tpl');
}

function subscribe_submit(Pieform $form, $values) {
    global $USER;
    global $topicid;
    if ($values['submit'] == get_string('subscribe', 'interaction.forum')) {
        insert_record(
            'interaction_forum_subscription_topic',
            (object)array(
                'topic' => $topicid,
                'user' => $USER->get('id')
            )
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_topic',
            'topic', $topicid,
            'user', $USER->get('id')
        );
    }
    redirect('/interaction/forum/topic.php?id=' . $topicid);
}

?>
