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
define('SECTION_PAGE', 'editpost');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('pieforms/pieform.php');
require_once('antispam.php');

$postid = param_integer('id', 0);

if ($postid == 0) { // post reply
    unset($postid);
    $parentid = param_integer('parent');
}
else { // edit post
    $post = get_record_sql(
        'SELECT p.subject, p.body, p.parent, p.topic, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . '
        FROM {interaction_forum_post} p
        WHERE p.id = ?
        AND p.deleted != 1
        AND p.parent IS NOT NULL',
        array($postid)
    );
    if (!$post) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
    }
    $parentid = $post->parent;
}

$parent = get_record_sql(
    'SELECT p.subject, p.body, p.topic, p.parent, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', m.user AS moderator, t.id AS topic, t.forum, t.closed AS topicclosed, p2.subject AS topicsubject, f.group AS "group", f.title AS forumtitle, g.name AS groupname, COUNT(p3.id)
    FROM {interaction_forum_post} p
    INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
    INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m ON (m.forum = f.id AND m.user = p.poster)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = ?)
    INNER JOIN {interaction_forum_post} p3 ON (p.poster = p3.poster AND p3.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p3.topic = t2.id)
    INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
    WHERE p.id = ?
    AND p.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14',
    array(0, $parentid)
);

if (!$parent) {
    throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $parentid));
}

define('GROUP', $parent->group);

$membership = user_can_access_forum((int)$parent->forum);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!isset($postid)) { // post reply
    if (!$membership) {
        throw new AccessDeniedException(get_string('cantaddposttoforum', 'interaction.forum'));
    }
    if (!$moderator && $parent->topicclosed) {
        throw new AccessDeniedException(get_string('cantaddposttotopic', 'interaction.forum'));
    }
    $action = get_string('postreply', 'interaction.forum');
    define('TITLE', $parent->topicsubject . ' - ' . $action);
}
else { // edit post
    // no record for edits to own posts with 30 minutes
    if (user_can_edit_post($post->poster, $post->ctime)) {
        $post->editrecord = false;
        $timeleft = (int)get_config_plugin('interaction', 'forum', 'postdelay') - round((time() - $post->ctime) / 60);
    }
    else if ($moderator) {
        $post->editrecord = true;
    }
    else if (user_can_edit_post($post->poster, $post->ctime, $USER->get('id'), false)) {
        $SESSION->add_error_msg(get_string('postaftertimeout', 'interaction.forum', get_config_plugin('interaction', 'forum', 'postdelay')));
        redirect('/interaction/forum/topic.php?id=' . $parent->topic);
    }
    else {
        throw new AccessDeniedException(get_string('canteditpost', 'interaction.forum'));
    }
    $action = get_string('editpost', 'interaction.forum');
    define('TITLE', $parent->topicsubject . ' - ' . $action);
}

$parent->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $parent->ctime);

$editform = pieform(array(
    'name'     => 'editpost',
    'successcallback' => isset($post) ? 'editpost_submit' : 'addpost_submit',
    'autofocus' => 'body',
    'elements' => array(
        'subject' => array(
            'type'         => 'text',
            'title'        => get_string('Subject', 'interaction.forum'),
            'defaultvalue' => isset($post) ? $post->subject : null,
            'rules'        => array(
                'maxlength' => 255
            ),
            'hidewhenempty' => true,
            'expandtext'    => get_string('clicksetsubject', 'interaction.forum'),
        ),
        'body' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('Body', 'interaction.forum'),
            'rows'         => 18,
            'cols'         => 70,
            'defaultvalue' => isset($post) ? $post->body : null,
            'rules'        => array(
                'required'  => true,
                'maxlength' => 65536,
            ),
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value'       => array(
                isset($post) ? get_string('save') : get_string('Post','interaction.forum'),
                get_string('cancel')
            ),
            'goto'      => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $parent->topic . '#post' . (isset($postid) ? $postid : $parentid)
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $parent->topic
        ),
        'editrecord' => array(
            'type' => 'hidden',
            'value' => isset($post) ? $post->editrecord : false
        )
    ),
));

function editpost_validate(Pieform $form, $values) {
    if ($baddomain = get_first_blacklisted_domain($values['body'])) {
        $form->set_error('body', get_string('blacklisteddomaininurl', 'mahara', $baddomain));
    }
}

function editpost_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $postid = param_integer('id');
    db_begin();
    update_record(
        'interaction_forum_post',
        array(
            'subject' => $values['subject'],
            'body' => PluginInteractionForum::prepare_post_body($values['body'], $postid),
        ),
        array('id' => $postid)
    );
    if ($values['editrecord']) {
        insert_record(
            'interaction_forum_edit',
            (object)array(
                'user' => $USER->get('id'),
                'post' => $postid,
                'ctime' => db_format_timestamp(time())
            )
        );
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('editpostsuccess', 'interaction.forum'));
    redirect('/interaction/forum/topic.php?id=' . $values['topic'] . '#post' . $postid);
}

function addpost_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $parentid = param_integer('parent');
    $post = (object)array(
        'topic'   => $values['topic'],
        'poster'  => $USER->get('id'),
        'parent'  => $parentid,
        'subject' => $values['subject'],
        'body'    => $values['body'],
        'ctime'   =>  db_format_timestamp(time())
    );
    $postid = insert_record('interaction_forum_post', $post, 'id', true);

    // Rewrite the post id into links in the body
    $newbody = PluginInteractionForum::prepare_post_body($post->body, $postid);
    if (!empty($newbody) && $newbody != $post->body) {
        set_field('interaction_forum_post', 'body', $newbody, 'id', $postid);
    }

    $delay = get_config_plugin('interaction', 'forum', 'postdelay');
    if (!is_null($delay) && $delay == 0) {
        PluginInteractionForum::interaction_forum_new_post(array($postid));
    }
    $SESSION->add_ok_msg(get_string('addpostsuccess', 'interaction.forum'));
    redirect('/interaction/forum/topic.php?id=' . $values['topic'] . '#post' . $postid);
}

$smarty = smarty();
$smarty->assign('editform', $editform);
$smarty->assign('parent', $parent);
$smarty->assign('action', $action);
$smarty->assign('groupadmins', group_get_admin_ids($parent->group));

if (isset($inlinejs)) {
    $smarty->assign('INLINEJAVASCRIPT', $inlinejs);
}

if (isset($timeleft)) {
    $smarty->assign('timeleft', $timeleft);
}
$smarty->display('interaction:forum:editpost.tpl');
