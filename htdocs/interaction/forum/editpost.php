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
require('group.php');


$userid = $USER->get('id');

$postid = param_integer('id',0);
$topicid = 0;
if ($postid==0) {
    unset($postid);
    define('TITLE', get_string('postreply','interaction.forum'));
    $parentid = param_integer('parent');
    $topic = get_record_sql(
        'SELECT p.topic AS id, p2.subject, t.closed, f.id AS forum, f.title AS forumtitle, f.group
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t
        ON p.topic = t.id
        AND t.deleted != 1
        INNER JOIN {interaction_forum_post} p2
        ON p2.topic = t.id
        AND p2.parent IS NULL
        INNER JOIN {interaction_instance} f
        ON t.forum = f.id
        AND f.deleted != 1
        WHERE p.id = ?
        AND p.deleted != 1',
        array($parentid)
    );

    if (!$topic) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $parentid));
    }

    $membership = user_can_access_group((int)$topic->group);

    $admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

    $moderator = $admin || is_forum_moderator((int)$topic->forum);

    if (!$membership || (!$moderator && $topic->closed)) {
        throw new AccessDeniedException(get_string('cantaddpost', 'interaction.forum'));
    }

    $topicid = $topic->id;
    $topicsubject = $topic->subject;

    $breadcrumbs = array(
        array(
            get_config('wwwroot') . 'interaction/forum/index.php?group=' . $topic->group,
            get_string('nameplural', 'interaction.forum')
        ),
        array(
            array(
                get_config('wwwroot') . 'interaction/forum/view.php?id=' . $topic->forum,
                $topic->forumtitle
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
                $topic->subject
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/editpost.php?parent=' . $parentid,
                get_string('postreply', 'interaction.forum')
            )
        )
    );
}

if (isset($postid)) {
    define('TITLE', get_string('editpost','interaction.forum'));
    $post = get_record_sql(
        'SELECT p.subject, p.body, p.parent, p.topic, p.poster, p.ctime, t.forum, p2.subject AS topicsubject, f.title AS forumtitle, f.group
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t
        ON p.topic = t.id
        AND t.deleted != 1
        INNER JOIN {interaction_forum_post} p2
        ON p2.topic = t.id
        AND p2.parent IS NULL
        INNER JOIN {interaction_instance} f
        ON t.forum = f.id
        AND f.deleted != 1
        WHERE p.id = ?
        AND p.deleted != 1',
        array($postid)
    );

    if (!$post) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
    }

    $topicid = $post->topic;
    $topicsubject = $post->topicsubject;

    $membership = user_can_access_group((int)$post->group);

    $admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

    $moderator = $admin || is_forum_moderator((int)$post->forum);

    if (!$moderator &&
        ($post->poster != $userid
        || (time() - strtotime($post->ctime)) > (30 * 60))) {
        throw new AccessDeniedException(get_string('canteditpost', 'interaction.forum'));
    }

    $breadcrumbs = array(
        array(
            get_config('wwwroot') . 'interaction/forum/index.php?group=' . $post->group,
            get_string('nameplural', 'interaction.forum')
        ),
        array(
            array(
                get_config('wwwroot') . 'interaction/forum/view.php?id=' . $post->forum,
                $post->forumtitle
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
                $topicsubject
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/editpost.php?id=' . $postid,
                get_string('editpost', 'interaction.forum')
            )
        )
    );
}

require_once('pieforms/pieform.php');

$editform = pieform(array(
    'name'     => 'editpost',
    'method'   => 'post',
    'elements' => array(
        'subject' => array(
            'type'         => 'text',
            'title'        => get_string('subject', 'interaction.forum'),
            'defaultvalue' => isset($post) ? $post->subject : null,
            'rules'        => array(
                'maxlength' => 255,
                'required'  => isset($post) && !$post->parent ? true : false
            )
        ),
        'body' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('body', 'interaction.forum'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => isset($post) ? $post->body : null,
            'rules'        => array( 'required' => true )
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value'       => array(
                isset($post) ? get_string('edit') : get_string('post','interaction.forum'),
                get_string('cancel')
            ),
        'goto'      => get_config('wwwroot') . 'interaction/forum/topic.php?id='.$topicid
        ),
    ),
));

function editpost_submit(Pieform $form, $values) {
    global $USER;
    $postid = param_integer('id',0);
    if ($postid==0) {
        $parentid = param_integer('parent');
        $topic = get_record_sql(
            'SELECT topic AS id
            FROM {interaction_forum_post}
            WHERE id = ?',
            array($parentid)
        );
        insert_record(
            'interaction_forum_post',
            (object)array(
                'topic' => $topic->id,
                'poster' => $USER->get('id'),
                'parent' => $parentid,
                'subject' => $values['subject'],
                'body' => $values['body'],
                'ctime' =>  db_format_timestamp(time())
            ),
            'id'
        );
    }
    else {
        $topic = get_record_sql(
            'SELECT topic AS id, poster, ctime AS posttime
            FROM {interaction_forum_post}
            WHERE id = ?',
            array($postid)
        );
        update_record(
            'interaction_forum_post',
            array(
                'subject' => $values['subject'],
                'body' => $values['body']
            ),
            array('id' => $postid)
        );
        if ($topic->poster != $USER->get('id') ||
           (time() - strtotime($topic->posttime)) > (30 * 60)) {
            insert_record(
                'interaction_forum_edit',
                (object)array(
                    'user' => $USER->get('id'),
                    'post' => $postid,
                    'ctime' => db_format_timestamp(time())
                )
            );
        }
    }
    redirect('/interaction/forum/topic.php?id='.$topic->id);
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('topicsubject', $topicsubject);
$smarty->assign('heading', TITLE);
$smarty->assign('topic', $topicsubject);
$smarty->assign('editform', $editform);
$smarty->display('interaction:forum:editpost.tpl');

?>
