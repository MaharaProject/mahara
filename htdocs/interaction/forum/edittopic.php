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
$moderator = false;
$topicid = param_integer('id',0);
if ($topicid==0) {
    unset($topicid);
    define('TITLE', get_string('addtopic','interaction.forum'));
    $forumid = param_integer('forum');
    $forum = get_record_sql(
        'SELECT f.group AS group, f.title
        FROM {interaction_instance} f
        WHERE id = ?
        AND deleted != 1',
        array($forumid)
    );

    if (!$forum) {
        throw new NotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
    }

    $forumtitle = $forum->title;
    $membership = user_can_access_group((int)$forum->group);

    $admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

    $moderator = $admin || is_forum_moderator($forumid);

    if (!$membership) {
        throw new AccessDeniedException(get_string('cantaddtopic', 'interaction.forum'));
    }

    $breadcrumbs = array(
        array(
            get_config('wwwroot') . 'interaction/forum/index.php?group=' . $forum->group,
            get_string('nameplural', 'interaction.forum')
        ),
        array(
            array(
                get_config('wwwroot') . 'interaction/forum/view.php?id=' . $forumid,
                $forum->title
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/edittopic.php?forum=' . $forumid,
                get_string('addtopic', 'interaction.forum')
            )
        )
    );
}

if (isset($topicid)) {
	define('TITLE', get_string('edittopic','interaction.forum'));
    $topic = get_record_sql(
        'SELECT p.subject, p.body, p.topic AS id, t.sticky, t.closed, f.id as forumid, f.group as group, f.title
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t
        ON p.topic = t.id
        AND t.deleted != 1
        INNER JOIN {interaction_instance} f
        ON f.id = t.forum
        AND f.deleted != 1
        WHERE p.parent IS NULL
        AND p.topic = ?',
        array($topicid)
    );
    
    if (!$topic) {
        throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
    }

    $forumid = $topic->forumid;
    $forumtitle = $topic->title;

    $membership = user_can_access_group((int)$topic->group);

    $admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

    $moderator = $admin || is_forum_moderator((int)$forumid);

    if (!$moderator) {
        throw new AccessDeniedException(get_string('cantedittopic', 'interaction.forum'));
    }

    $breadcrumbs = array(
        array(
            get_config('wwwroot') . 'interaction/forum/index.php?group=' . $topic->group,
            get_string('nameplural', 'interaction.forum')
        ),
        array(
            array(
                get_config('wwwroot') . 'interaction/forum/view.php?id=' . $topic->forumid,
                $topic->title
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
                $topic->subject
            ),
            array(
                get_config('wwwroot') . 'interaction/forum/edittopic.php?id=' . $topicid,
                get_string('edittopic', 'interaction.forum')
            )
        )
    );

}

require_once('pieforms/pieform.php');

$editform = array(
    'name'     => 'edittopic',
    'method'   => 'post',
    'elements' => array(
        'subject' => array(
            'type'         => 'text',
            'title'        => get_string('subject', 'interaction.forum'),
            'defaultvalue' => isset($topic) ? $topic->subject : null,
            'rules'        => array(
                'required' => true,
                'maxlength' => 255
            )
        ),
        'body' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('body', 'interaction.forum'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => isset($topic) ? $topic->body : null,
            'rules'        => array( 'required' => true )
        ),
        'sticky' => array(
            'type'         => 'checkbox',
            'title'        => get_string('sticky', 'interaction.forum'),
            'defaultvalue' => isset($topic) && $topic->sticky == 1 ? 'checked' : null
        ),
        'closed' => array(
            'type'         => 'checkbox',
            'title'        => get_string('closed', 'interaction.forum'),
            'defaultvalue' => isset($topic) && $topic->closed == 1 ? 'checked' : null
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value'       => array(
                isset($topic) ? get_string('edit') : get_string('post','interaction.forum'),
                get_string('cancel')
            ),
            'goto'      => get_config('wwwroot') . 'interaction/forum/' . (isset($topic) ? 'topic.php?id='.$topicid : 'view.php?id='.$forumid)
        ),
    ),
);

if(!$moderator){
    unset($editform['elements']['sticky']);
    unset($editform['elements']['closed']);
}

$editform = pieform($editform);

function edittopic_submit(Pieform $form, $values) {
    global $USER;
    $topicid = param_integer('id',0);
    if ($topicid==0) {
        $forumid = param_integer('forum');
        db_begin();
        $topicid = insert_record(
            'interaction_forum_topic',
            (object)array(
                'forum' => $forumid,
                'sticky' => isset($values['sticky']) && $values['sticky'] ? 1 : 0,
                'closed' => isset($values['closed']) && $values['closed'] ? 1 : 0
            ),
            'id',
            true
        );
        insert_record(
            'interaction_forum_post',
            (object)array(
                'topic' => $topicid,
                'poster' => $USER->get('id'),
                'subject' => $values['subject'],
                'body' => $values['body'],
                'ctime' =>  db_format_timestamp(time())
            ),
            'id'
        );
        db_commit();
    }
    else {
        $post = get_record_sql(
            'SELECT id
            FROM {interaction_forum_post}
            WHERE parent IS NULL
            AND topic = ?',
            array($topicid)
        );
        db_begin();
        update_record(
            'interaction_forum_post',
            array(
                'subject' => $values['subject'],
                'body' => $values['body']
            ),
            array('id' => $post->id)
        );
        insert_record(
            'interaction_forum_edit',
            (object)array(
                'user' => $USER->get('id'),
                'post' => $post->id,
                'ctime' => db_format_timestamp(time())
            )
        );
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
    }
    redirect('/interaction/forum/topic.php?id='.$topicid);
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('heading', TITLE);
$smarty->assign('forum', $forumtitle);
$smarty->assign('editform', $editform);
$smarty->display('interaction:forum:edittopic.tpl');

?>
