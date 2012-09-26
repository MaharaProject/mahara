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

$group = get_record('group', 'id', $topic->groupid);
$publicgroup = $group->public;
$ineditwindow = group_within_edit_window($group);
$feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=t&id=' . $topic->id;

$membership = user_can_access_forum((int)$topic->forumid);
$moderator = $ineditwindow && (bool)($membership & INTERACTION_FORUM_MOD);

$forumconfig = get_records_assoc('interaction_forum_instance_config', 'forum', $topic->forumid, '', 'field,value');
$indentmode = isset($forumconfig['indentmode']) ? $forumconfig['indentmode']->value : 'full_indent';
$maxindentdepth = isset($forumconfig['maxindent']) ? $forumconfig['maxindent']->value : 10;

if (!$membership
    && !get_field('group', 'public', 'id', $topic->groupid)) {
    throw new GroupAccessDeniedException(get_string('cantviewtopic', 'interaction.forum'));
}
$topic->canedit = ($moderator || user_can_edit_post($topic->poster, $topic->ctime)) && $ineditwindow;

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
// posts pagination params
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$postid = param_integer('post', 0);
if (!empty($postid)) {
    // validates the $postid
    $post = get_record('interaction_forum_post', 'id', $postid, 'deleted', '0', null, null, 'id, path');
    if (!$post) {
        throw new NotFoundException("The post with ID '$postid' is not found or deleted!");
    }
    // caculated offset value to jump to the page of the post
    $offset = count_records_select('interaction_forum_post', 'topic = ? AND path < ?', array($topicid, $post->path));
    $offset = $offset - ($offset % $limit);
    redirect(get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid . '&offset=' . $offset . '&limit=' . $limit . '#post' . $postid);
}

$posts = get_records_sql_array(
    'SELECT p.id, p.parent, p.path, p.poster, p.subject, p.body, ' . db_format_tsfield('p.ctime', 'ctime') . ', p.deleted
    FROM {interaction_forum_post} p
    WHERE p.topic = ?
    ORDER BY p.path, p.ctime, p.id',
    array($topicid),
    $offset,
    $limit
);
// Get extra info of posts
foreach ($posts as $post) {
    // Get the number of posts
    $post->postcount = get_postcount($post->poster);

    $post->canedit = $post->parent && ($moderator || user_can_edit_post($post->poster, $post->ctime)) && $ineditwindow;
    $post->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $post->ctime);
    // Get post edit records
    $post->edit = get_postedits($post->id);
    // Get moderator info
    $post->moderator = is_moderator($post->poster)? $post->poster : null;
    // Update the subject of posts
    $post->subject = !empty($post->subject) ? $post->subject : get_string('re', 'interaction.forum', get_ancestorpostsubject($post->id));
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
        get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid . '&post=',
        'newpost',
    )
);

// renders a page of posts
$posts = buildpostlist($posts, $indentmode, $maxindentdepth);
// adds posts pagination
$postcount = count_records_sql('SELECT COUNT(id) FROM {interaction_forum_post} WHERE topic = ?', array($topicid));
$pagination = build_pagination(array(
        'url' => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
        'count' => $postcount,
        'limit' => $limit,
        'offset' => $offset,
));

$headers = array();
if ($publicgroup) {
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '">';
}

$smarty = smarty(array(), $headers, array(), array());
$smarty->assign('topic', $topic);
$smarty->assign('membership', $membership);
$smarty->assign('moderator', $moderator);
$smarty->assign('posts', $posts);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('interaction:forum:topic.tpl');

/*
 * Render a page of posts
 *
 * @param array $posts list of posts
 * @param string $mode ('no_indent', 'max_indent', 'full_indent')
 * @param int $max_depth the maximum depth to indent to
 */
function buildpostlist($posts, $mode, $max_depth) {
    switch ($mode) {
        case 'no_indent':
            $max_depth = 1;
            break;
        case 'max_indent':
            break;
        case 'full_indent':
        default:
            $max_depth = -1;
            break;
    }
    $html = '';
    foreach ($posts as $post) {
        // calculates the indent tabs for the post
        $indent = ($max_depth == 1) ? 1 : count(explode('/', $post->path, $max_depth));
        $html .= renderpost($post, $indent);
    }
    return $html;
}


/*
 * Renders a post
 *
 * @param object $post post object
 * @param int $indent indent value
 * @return string html output
 */

function renderpost($post, $indent) {
    global $moderator, $topic, $groupadmins, $membership, $ineditwindow;

    $smarty = smarty_core();
    $smarty->assign('post', $post);
    $smarty->assign('width', 100 - $indent*2);
    $smarty->assign('groupadmins', $groupadmins);
    $smarty->assign('moderator', $moderator);
    $smarty->assign('membership', $membership);
    $smarty->assign('closed', $topic->closed);
    $smarty->assign('ineditwindow', $ineditwindow);
    return $smarty->fetch('interaction:forum:post.tpl');
}

/*
 * Return the subject for the topic
 *
 * @param int $postid the ID of the post
 *
 * @return string the subject
 */

function get_ancestorpostsubject($postid) {
    while ($ppost = get_record_sql(
           'SELECT p1.id, p1.subject
            FROM {interaction_forum_post} p1
            INNER JOIN {interaction_forum_post} p2 ON (p1.id = p2.parent)
            WHERE p2.id = ?', array($postid))) {
        if (!empty ($ppost->subject)) {
            return $ppost->subject;
        }
        $postid = $ppost->id;
    }
    return null;
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

/* Return the number of posts submitted by a poster
 *
 * @param int $posterid ID of the poster
 * @return int the number of posts
 */
function get_postcount($posterid) {
    return get_string('postsvariable', 'interaction.forum', count_records_sql(
       'SELECT COUNT(id)
        FROM {interaction_forum_post}
        WHERE deleted != 1 AND poster = ?', array($posterid)));
}

/* Return the edit records of a post
 *
 * @param int $postid ID of the post
 * @return array the edit records
 */
function get_postedits($postid) {
    ($postedits = get_records_sql_array(
       'SELECT ' . db_format_tsfield('e.ctime', 'edittime') . ', e.user AS editor, m2.user AS editormoderator
        FROM {interaction_forum_edit} e
        LEFT JOIN {interaction_forum_post} p ON p.id = e.post
        LEFT JOIN {interaction_forum_topic} t ON t.id = p.topic
        LEFT JOIN (
            SELECT m.forum, m.user
            FROM {interaction_forum_moderator} m
            INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
        ) m2 ON (m2.forum = t.forum AND m2.user = e.user)
        WHERE e.post = ?
        ORDER BY e.ctime',
        array($postid)
    )) || ($postedits = array());
    $editrecs = array();
    foreach ($postedits as $postedit) {
        $postedit->edittime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $postedit->edittime);
        $editrecs[] = array('editormoderator' => $postedit->editormoderator, 'editor' => $postedit->editor, 'edittime' => $postedit->edittime, );
    }
    return $editrecs;
}

/* Check if the poster is the moderator of the forum in which the post is
 *
 * @param int $postid ID of the post
 * @return true if yes, false if else
 */
function is_moderator($postid) {
    return (count_records_sql(
       'SELECT COUNT(m.user)
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
        INNER JOIN {interaction_instance} f ON (m.forum = f.id AND f.deleted != 1)
        INNER JOIN {interaction_forum_topic} t ON (t.forum = f.id)
        INNER JOIN {interaction_forum_post} p ON (p.topic = t.id AND p.poster = m.user)
        WHERE p.id = ?',
        array($postid)) == 1);
}
