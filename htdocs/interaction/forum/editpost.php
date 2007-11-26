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

define('TITLE', get_string('editpost','interaction.forum'));

$userid = $USER->get('id');

$postid = param_integer('id',0);
$topicid = 0;
if ($postid==0) {
    unset($postid);
    $parentid = param_integer('parent');
    $topic = get_record_sql(
        'SELECT topic
        FROM {interaction_forum_post}
        WHERE id = ?',
        array($parentid)
    );
    if (!$topic) {
        throw new NotFoundException("Couldn't find topic with id $parentid");
    }
    $topicid = $topic->id;
}

if (isset($postid)) {
    $post = get_record_sql(
        'SELECT p.subject, p.body, p.parent, p.topic, p.poster, p.ctime, t.forum, f.group
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t
        ON (p.topic = t.id)
        INNER JOIN {interaction_instance} f
        ON (t.forum = f.id)
        WHERE p.id = ?',
        array($postid)
    );

    if (!$post) {
        throw new NotFoundException("Couldn't find post with id $postid");
    }
    
    $topicid = $post->topic;

    $membership = user_can_access_group((int)$post->group);

    $admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

    $moderator = $admin || is_forum_moderator((int)$post->forum);

    if (!$moderator &&
        ($post->poster != $userid
        || (time() - strtotime($post->ctime)) > (30 * 60))) {
        throw new AccessDeniedException();
    }
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
                isset($post) ? get_string('edit', 'interaction.forum') : get_string('post','interaction.forum'),
                get_string('cancel', 'interaction.forum')
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
            'SELECT topic as id
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
            'SELECT topic as id, poster, ctime as posttime
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
$smarty->assign('editform',$editform);
$smarty->display('interaction:forum:editpost.tpl');

?>
