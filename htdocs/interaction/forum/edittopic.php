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
    'SELECT f.group AS group, f.title, g.name AS groupname
    FROM {interaction_instance} f
    INNER JOIN {group} g ON g.id = f.group
    WHERE f.id = ?
    AND f.deleted != 1',
    array($forumid)
);
if (!$forum) {
    throw new NotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
}

$membership = user_can_access_forum((int)$forumid);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$membership) {
    throw new AccessDeniedException(get_string('cantaddtopic', 'interaction.forum'));
}

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'group/view.php?id=' . $forum->group,
        $forum->groupname
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/index.php?group=' . $forum->group,
        get_string('nameplural', 'interaction.forum')
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/view.php?id=' . $forumid,
        $forum->title
    )
);

if (!isset($topicid)) { // new topic
    define('TITLE', $forum->title . ' - ' . get_string('addtopic','interaction.forum'));
    $breadcrumbs[] = array(
        get_config('wwwroot') . 'interaction/forum/edittopic.php?forum=' . $forumid,
        get_string('addtopic', 'interaction.forum')
    );
}

else { // edit topic
    define('TITLE', $forum->title . ' - ' . get_string('edittopic','interaction.forum'));
    $breadcrumbs[] = array(
        get_config('wwwroot') . 'interaction/forum/edittopic.php?id=' . $topicid,
        get_string('edittopic', 'interaction.forum')
    );

    // no record for edits to own posts with 30 minutes
    if (user_can_edit_post($topic->poster, $topic->ctime)) {
        $topic->editrecord = false;
    }
    else if ($moderator) {
        $topic->editrecord = true;
    }
    else {
        throw new AccessDeniedException(get_string('cantedittopic', 'interaction.forum'));
    }
}

require_once('pieforms/pieform.php');

$editform = array(
    'name'     => isset($topic) ? 'edittopic' : 'addtopic',
    'method'   => 'post',
    'autofocus' => false,
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
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => isset($topic) ? $topic->body : null,
            'rules'        => array( 'required' => true )
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
            'defaultvalue' => isset($topic) && $topic->closed == 1 ? 'checked' : null
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
    insert_record(
        'interaction_forum_post',
        (object)array(
            'topic' => $topicid,
            'poster' => $USER->get('id'),
            'subject' => $values['subject'],
            'body' => $values['body'],
            'ctime' =>  db_format_timestamp(time())
        )
    );
    db_commit();
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
                'body' => $values['body']
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
        redirect('/interaction/forum/view.php?id=' . $topic->forumid);
    }
    else {
        redirect('/interaction/forum/topic.php?id=' . $topicid);
    }
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('heading', TITLE);
$smarty->assign('editform', $editform);
$smarty->display('interaction:forum:edittopic.tpl');

?>
