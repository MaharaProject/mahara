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
define('PUBLIC', 1);
define('MENUITEM', 'groups/forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'topic');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('pieforms/pieform.php');

$topicid = param_integer('id');

$topic = get_record_sql(
    'SELECT p.subject, p.poster, p.id AS firstpost, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.id, f.group AS groupid, g.name AS groupname, f.id AS forumid, f.title AS forumtitle, t.closed, sf.forum AS forumsubscribed, st.topic AS topicsubscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = 0)
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

define('GROUP', $topic->groupid);

$publicgroup = get_field('group', 'public', 'id', $topic->groupid);
$feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=t&id=' . $topic->id;

$membership = user_can_access_forum((int)$topic->forumid);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

$forumconfig = get_records_assoc('interaction_forum_instance_config', 'forum', $topic->forumid, '', 'field,value');
$indentmode = isset($forumconfig['indentmode']) ? $forumconfig['indentmode']->value : 'full_indent';
$maxindentdepth = isset($forumconfig['maxindent']) ? $forumconfig['maxindent']->value : 10;

if (!$membership
    && !get_field('group', 'public', 'id', $topic->groupid)) {
    throw new GroupAccessDeniedException(get_string('cantviewtopic', 'interaction.forum'));
}
$topic->canedit = $moderator || user_can_edit_post($topic->poster, $topic->ctime);

define('TITLE', $topic->forumtitle . ' - ' . $topic->subject);

$groupadmins = group_get_admin_ids($topic->groupid);

if ($membership && !$topic->forumsubscribed) {
    $topic->subscribe = pieform(array(
        'name'     => 'subscribe_topic',
        'renderer' => 'div',
        'plugintype' => 'interaction',
        'pluginname' => 'forum',
        'autofocus' => false,
        'elements' => array(
            'submit' => array(
               'type'  => 'submit',
               'class' => 'btn-subscribe',
               'value' => $topic->topicsubscribed ? get_string('unsubscribefromtopic', 'interaction.forum') : get_string('subscribetotopic', 'interaction.forum'),
               'help' => true
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
    'SELECT p1.id, p1.parent, p1.poster, p1.subject, p1.body, ' . db_format_tsfield('p1.ctime', 'ctime') . ', p1.deleted, m.user AS moderator, COUNT(p2.id) AS postcount, ' . db_format_tsfield('e.ctime', 'edittime') . ', e.user AS editor, m2.user as editormoderator
    FROM {interaction_forum_post} p1
    INNER JOIN {interaction_forum_topic} t ON (t.id = p1.topic)
    INNER JOIN {interaction_forum_post} p2 ON (p1.poster = p2.poster AND p2.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p2.topic = t2.id)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1 AND f.group = ?)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m ON (m.forum = t.forum AND m.user = p1.poster)
    LEFT JOIN {interaction_forum_edit} e ON e.post = p1.id
    LEFT JOIN {interaction_forum_post} p3 ON p3.id = e.post
    LEFT JOIN {interaction_forum_topic} t3 ON t3.id = p3.topic
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m2 ON (m2.forum = t3.forum AND m2.user = e.user)
    WHERE p1.topic = ?
    GROUP BY 1, 2, 3, 4, 5, p1.ctime, 7, 8, 10, 11, 12, e.ctime
    ORDER BY p1.ctime, p1.id, e.ctime',
    array($topic->groupid, $topicid)
);

// $posts has an object for every edit to a post
// this combines all the edits into a single object for each post
// also formats the edits a bit
$count = count($posts);
for ($i = 0; $i < $count; $i++) {
	$posts[$i]->postcount = get_string('postsvariable', 'interaction.forum', $posts[$i]->postcount);
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

// If the user has internal notifications for this topic, mark them
// all as read.  Obviously there's no guarantee the user will actually
// read all the posts on this page, but better than letting the unread
// notifications grow too fast.  Unfortunately the only way to find
// notifications on this topic is to look for the url of this page.
execute_sql('
    UPDATE {notification_internal_activity}
    SET "read" = 1
    WHERE "read" = 0 AND usr = ? AND url LIKE ? || \'%\' AND type = (
        SELECT id FROM {activity_type} WHERE name = ?
    )',
    array(
        $USER->get('id'),
        get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid . '#post',
        'newpost',
    )
);

// builds the first post (with index 0) which has as children all the posts in the topic
$posts = buildpostlist($posts, $indentmode, $maxindentdepth);

$headers = array();
if ($publicgroup) {
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '" />';
}

$smarty = smarty(array(), $headers, array(), array());
$smarty->assign('topic', $topic);
$smarty->assign('membership', $membership);
$smarty->assign('moderator', $moderator);
$smarty->assign('posts', $posts);
$smarty->display('interaction:forum:topic.tpl');

function buildpostlist(&$posts, $mode, $max_depth) {
    buildsubjects(0, '', $posts);
    switch ($mode) {
        case 'no_indent':
            return buildflatposts($posts);
            break;
        case 'max_indent':
            $new_posts = array();
            buildmaxindentposts(0, $posts, $max_depth);
            $new_posts = buildpost(0, $posts);
            return renderpost($new_posts);
            break;
        case 'full_indent':
        default:
            $new_posts = buildpost(0, $posts);
            return renderpost($new_posts);
            break;
    }
}


/*
 * Sorts posts so that if there parent is too deep will take the nearest ancestor of the right depth
 *
 * @param int $postindex the current index
 * @param array $posts list of posts
 * @param int $max_depth the maximum depth to indent to
 * @oaram int $current_depth the current depth
 * @param int $current_parent the current post parent/ancestor
 */

function buildmaxindentposts($postindex, &$posts, $max_depth, $current_depth = 0, $current_parent = 0) {
    global $moderator, $topic, $groupadmins;
    $localposts = $posts;

    $current_depth++;
    foreach ($localposts as $index => $post) {
        if ($posts[$index]->parent == $posts[$postindex]->id) {
            if ($current_depth < $max_depth) {
                $current_parent = $posts[$postindex]->id;
            } else {
                $posts[$index]->parent = $current_parent;
            }
            buildmaxindentposts($index, $posts, $max_depth, $current_depth, $current_parent);
        }
    }
}

/*
 * Renders a post and its children
 *
 * @param object $post post object
 *
 * @return string html output
 */

function renderpost($post) {
    global $moderator, $topic, $groupadmins;
    $children = array();
    if (isset($post->children) && !empty($post->children)) {
        foreach ($post->children as $index=>$child_post) {
            $children[] = renderpost($child_post);
        }
    }
    $membership = user_can_access_forum((int)$topic->forumid);
    $smarty = smarty_core();
    $smarty->assign('post', $post);
    $smarty->assign('groupadmins', $groupadmins);
    $smarty->assign('children', $children);
    $smarty->assign('moderator', $moderator);
    $smarty->assign('membership', $membership);
    $smarty->assign('closed', $topic->closed);
    return $smarty->fetch('interaction:forum:post.tpl');
}

/**
 * Builds a flat list of posts
 *
 * @param array $posts the posts in the topic
 *
 * @returns string the html for the topc
 */

function buildflatposts(&$posts) {
    $localposts = $posts;
    $first_post = array_shift($localposts);
    if (!isset($first_post->subject) || empty($first_post->subject)) {
        $first_post->subject = get_string('re', 'interaction.forum', '');
    }

    $children = array();
    foreach ($localposts as $index => $post) {
        $children[] = $post;
    }
    $first_post->children = $children;
    return renderpost($first_post);
}

/*
 * Builds subjects for the topic
 *
 * @param int $postindex index of the post
 * @param string $parentsubject subject title of the parent post
 * @param array $posts the posts in the topic
 */

function buildsubjects($postindex, $parentsubject, &$posts) {
    $localposts = $posts;
    if ($posts[$postindex]->subject) {
        $parentsubject = $posts[$postindex]->subject;
    }
    else {
        $posts[$postindex]->subject = get_string('re', 'interaction.forum', $parentsubject);
    }
    foreach ($localposts as $index => $post) {
        if ($posts[$index]->parent == $posts[$postindex]->id) {
            buildsubjects($index, $parentsubject, $posts);
        }
    }
}

/**
 * Sorts children posts into their parent
 * 
 * @param int $postindex the index of the post
 * @param array $posts the posts in the topic
 *
 * @returns array the html for the post
 */

function buildpost($postindex, &$posts){
    global $moderator, $topic, $groupadmins;
    $localposts = $posts;

    $children = array();
    foreach ($localposts as $index => $post) {
        if ($posts[$index]->parent == $posts[$postindex]->id) {
            $children[] = buildpost($index, $posts);
        }
    }
    $posts[$postindex]->children = $children;
    return $posts[$postindex];
}

function subscribe_topic_validate(Pieform $form, $values) {
    if (!is_logged_in()) {
        // This page is public, so the access denied exception will cause a 
        // login attempt
        throw new AccessDeniedException();
    }
}

function subscribe_topic_submit(Pieform $form, $values) {
    global $USER;
    if ($values['type'] == 'subscribe') {
        insert_record(
            'interaction_forum_subscription_topic',
            (object)array(
                'topic' => $values['topic'],
                'user'  => $USER->get('id'),
                'key'   => PluginInteractionForum::generate_unsubscribe_key(),
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
