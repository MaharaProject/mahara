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
define('SECTION_PAGE', 'edittopic');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('pieforms/pieform.php');
require_once('antispam.php');

$userid = $USER->get('id');
$topicid = param_integer('id', 0);
$returnto = param_alpha('returnto', 'topic');

if ($topicid == 0) { // new topic
    unset($topicid);
    $forumid = param_integer('forum');
}
else { // edit topic
    $topic = get_record_sql(
        'SELECT p.subject, p.id AS postid, p.body, p.poster, p.topic AS id, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.sticky, t.closed, f.id AS forum
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
        INNER JOIN {interaction_instance} f ON (f.id = t.forum AND f.deleted != 1)
        WHERE p.parent IS NULL
        AND p.topic = ?',
        array($topicid)
    );
    $forumid = $topic->forum;

    if (!$topic) {
        throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
    }
}

$forum = get_record_sql(
    'SELECT f.group AS groupid, f.title, g.name AS groupname, g.grouptype
    FROM {interaction_instance} f
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = 0)
    WHERE f.id = ?
    AND f.deleted != 1',
    array($forumid)
);

if (!$forum) {
    throw new NotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
}

$forumconfig = get_records_assoc('interaction_forum_instance_config', 'forum', $forumid, '', 'field,value');

define('GROUP', $forum->groupid);
$membership = user_can_access_forum((int)$forumid);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$membership || ($forumconfig['createtopicusers']->value == 'moderators' && !$moderator)) {
    throw new AccessDeniedException(get_string('cantaddtopic', 'interaction.forum'));
}

if (!isset($topicid)) { // new topic
    define('TITLE', $forum->title . ' - ' . get_string('addtopic','interaction.forum'));
}

else { // edit topic
    define('TITLE', $forum->title . ' - ' . get_string('edittopic','interaction.forum'));

    // no record for edits to own posts with 30 minutes
    if (user_can_edit_post($topic->poster, $topic->ctime)) {
        $topic->editrecord = false;
        $timeleft = (int)get_config_plugin('interaction', 'forum', 'postdelay') - round((time() - $topic->ctime) / 60);
    }
    else if ($moderator) {
        $topic->editrecord = true;
    }
    else if (user_can_edit_post($topic->poster, $topic->ctime, $USER->get('id'), false)) {
        $SESSION->add_error_msg(get_string('postaftertimeout', 'interaction.forum', get_config_plugin('interaction', 'forum', 'postdelay')));
        redirect('/interaction/forum/topic.php?id=' . $topicid);
    }
    else {
        throw new AccessDeniedException(get_string('cantedittopic', 'interaction.forum'));
    }
}

$editform = array(
    'name'     => isset($topic) ? 'edittopic' : 'addtopic',
    'method'   => 'post',
    'autofocus' => isset($topic) ? 'body' : 'subject',
    'elements' => array(
        'subject' => array(
            'type'         => 'text',
            'title'        => get_string('Subject', 'interaction.forum'),
            'defaultvalue' => isset($topic) ? $topic->subject : null,
            'rules'        => array(
                'required' => true,
                'maxlength' => 255
            )
        ),
        'body' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('Body', 'interaction.forum'),
            'rows'         => 18,
            'cols'         => 70,
            'defaultvalue' => isset($topic) ? $topic->body : null,
            'rules'        => array(
                'required'  => true,
                'maxlength' => 65536,
            ),
        ),
        'sticky' => array(
            'type'         => 'checkbox',
            'title'        => get_string('Sticky', 'interaction.forum'),
            'description'  => get_string('stickydescription', 'interaction.forum'),
            'defaultvalue' => isset($topic) && $topic->sticky == 1 ? 'checked' : null
        ),
        'closed' => array(
            'type'         => 'checkbox',
            'title'        => get_string('Closed', 'interaction.forum'),
            'description'  => get_string('closeddescription', 'interaction.forum'),
            'defaultvalue' => isset($topic) ? $topic->closed : !empty($forumconfig['closetopics']->value),
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value'       => array(
                isset($topic) ? get_string('edit') : get_string('Post','interaction.forum'),
                get_string('cancel')
            ),
            'goto'      => get_config('wwwroot') . 'interaction/forum/' . (isset($topic) && $returnto != 'view'  ? 'topic.php?id='.$topicid : 'view.php?id='.$forumid)
        ),
        'post' => array(
            'type' => 'hidden',
            'value' => isset($topic) ? $topic->postid : false
        ),
        'editrecord' => array(
            'type' => 'hidden',
            'value' => isset($topic) ? $topic->editrecord : false
        )
    ),
);

if(!$moderator){
    unset($editform['elements']['sticky']);
    unset($editform['elements']['closed']);
}

$editform = pieform($editform);

function addtopic_validate(Pieform $form, $values) {
    if ($baddomain = get_first_blacklisted_domain($values['body'])) {
        $form->set_error('body', get_string('blacklisteddomaininurl', 'mahara', $baddomain));
    }
}

function edittopic_validate(Pieform $form, $values) {
    if ($baddomain = get_first_blacklisted_domain($values['body'])) {
        $form->set_error('body', get_string('blacklisteddomaininurl', 'mahara', $baddomain));
    }
}

function addtopic_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $forumid = param_integer('forum');
    db_begin();
    $topicid = insert_record(
        'interaction_forum_topic',
        (object)array(
            'forum' => $forumid,
            'sticky' => isset($values['sticky']) && $values['sticky'] ? 1 : 0,
            'closed' => isset($values['closed']) && $values['closed'] ? 1 : 0
        ), 'id', true
    );
    $post = (object)array(
        'topic'   => $topicid,
        'poster'  => $USER->get('id'),
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

    if (!record_exists('interaction_forum_subscription_forum', 'user', $USER->get('id'), 'forum', $forumid)) {
        insert_record('interaction_forum_subscription_topic', (object)array(
            'user'  => $USER->get('id'),
            'topic' => $topicid,
            'key'   => PluginInteractionForum::generate_unsubscribe_key(),
        ));
    }
    db_commit();
    $delay = get_config_plugin('interaction', 'forum', 'postdelay');
    if (!is_null($delay) && $delay == 0) {
        PluginInteractionForum::interaction_forum_new_post(array($postid));
    }
    $SESSION->add_ok_msg(get_string('addtopicsuccess', 'interaction.forum'));
    redirect('/interaction/forum/topic.php?id='.$topicid);
}

function edittopic_submit(Pieform $form, $values) {
    global $SESSION, $USER, $topic;
    $topicid = param_integer('id');
    $returnto = param_alpha('returnto', 'topic');
    db_begin();
    // check the post content actually changed
    // otherwise topic could have been set as sticky/closed
    $postchanged = $values['subject'] != $topic->subject || $values['body'] != $topic->body;
    if ($postchanged) {
        update_record(
            'interaction_forum_post',
            array(
                'subject' => $values['subject'],
                'body' => PluginInteractionForum::prepare_post_body($values['body'], $values['post']),
            ),
            array('id' => $values['post'])
        );
    }
    if ($values['editrecord'] && $postchanged) {
        insert_record(
            'interaction_forum_edit',
            (object)array(
                'user' => $USER->get('id'),
                'post' => $values['post'],
                'ctime' => db_format_timestamp(time())
            )
        );
    }
    if(isset($values['sticky'])){
        update_record(
            'interaction_forum_topic',
            array(
                'sticky' => isset($values['sticky']) && $values['sticky'] == 1 ? 1 : 0,
                'closed' => isset($values['closed']) && $values['closed'] == 1 ? 1 : 0
            ),
            array('id' => $topicid)
        );
    }
    db_commit();
    $SESSION->add_ok_msg(get_string('edittopicsuccess', 'interaction.forum'));
    if ($returnto == 'view') {
        redirect('/interaction/forum/view.php?id=' . $topic->forum);
    }
    else {
        redirect('/interaction/forum/topic.php?id=' . $topicid);
    }
}

$smarty = smarty();
$smarty->assign('heading', $forum->groupname);
$smarty->assign('subheading', TITLE);
$smarty->assign('editform', $editform);
if (isset($timeleft)) {
    $smarty->assign('timeleft', $timeleft);
}
$smarty->display('interaction:forum:edittopic.tpl');
