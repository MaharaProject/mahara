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
define('SECTION_PAGE', 'deletepost');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction' ,'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('pieforms/pieform.php');

$postid = param_integer('id');
$post = get_record_sql(
    'SELECT p.subject, p.body, p.topic, p.parent, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', m.user AS moderator, t.forum, p2.subject AS topicsubject, f.group, f.title AS forumtitle, g.name AS groupname, COUNT(p3.id)
    FROM {interaction_forum_post} p
    INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
    INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = ?)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m ON (m.forum = f.id AND m.user = p.poster)
    INNER JOIN {interaction_forum_post} p3 ON (p.poster = p3.poster AND p3.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p3.topic = t2.id)
    INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
    WHERE p.id = ?
    AND p.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12',
    array(0, $postid)
);

if (!$post) {
    throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
}

$membership = user_can_access_forum((int)$post->forum);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$moderator) {
    throw new AccessDeniedException(get_string('cantdeletepost', 'interaction.forum'));
}

if (!$post->parent) {
    throw new AccessDeniedException(get_string('cantdeletethispost', 'interaction.forum'));
}

define('GROUP', $post->group);

define('TITLE', $post->topicsubject . ' - ' . get_string('deletepost', 'interaction.forum'));
$post->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $post->ctime);

$form = pieform(array(
    'name'     => 'deletepost',
    'renderer' => 'div',
    'autofocus' => false,
    'elements' => array(
        'title' => array(
            'value' => get_string('deletepostsure', 'interaction.forum'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic . '#post' . $postid
        ),
        'post' => array(
            'type' => 'hidden',
            'value' => $postid
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $post->topic
        ),
        'parent' => array(
            'type' => 'hidden',
            'value' => $post->parent
        )
    )
));

function deletepost_submit(Pieform $form, $values) {
    global $SESSION;
    update_record(
        'interaction_forum_post',
        array('deleted' => 1),
        array('id' => $values['post'])
    );
    $SESSION->add_ok_msg(get_string('deletepostsuccess', 'interaction.forum'));
    redirect('/interaction/forum/topic.php?id=' . $values['topic'] . '#post' . $values['parent']);
}

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('post', $post);
$smarty->assign('deleteform', $form);
$smarty->assign('groupadmins', group_get_admin_ids($post->group));
$smarty->display('interaction:forum:deletepost.tpl');
