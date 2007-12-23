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

$postid = param_integer('id', 0);
if ($postid == 0) { // post reply
    unset($postid);
    $parentid = param_integer('parent');

    $parent = get_record_sql(
        'SELECT p.subject, p.body, p.topic, p.parent, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', m.user AS moderator, t.id AS topicid, t.forum, t.closed AS topicclosed, p2.subject AS topicsubject, f.group, f.title AS forumtitle, g.name AS groupname, g.owner AS groupowner, COUNT(p3.*)
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
        INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
        INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
        LEFT JOIN {interaction_forum_moderator} m ON (m.user = p.poster AND m.forum = f.id)
        INNER JOIN {group} g ON g.id = f.group
        INNER JOIN {interaction_forum_post} p3 ON (p.poster = p3.poster AND p3.deleted != 1)
        INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p3.topic = t2.id)
        INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
        WHERE p.id = ?
        AND p.deleted != 1
        GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15',
        array($parentid)
    );

    if (!$parent) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $parentid));
    }

    $membership = user_can_access_group((int)$parent->group);

    $admin = (bool)($membership & (GROUP_MEMBERSHIP_OWNER | GROUP_MEMBERSHIP_ADMIN | GROUP_MEMBERSHIP_STAFF));

    $moderator = $admin || is_forum_moderator((int)$parent->forum);

    if (!$membership) {
        throw new AccessDeniedException(get_string('cantaddposttoforum', 'interaction.forum'));
    }
    if (!$moderator && $parent->topicclosed) {
        throw new AccessDeniedException(get_string('cantaddposttotopic', 'interaction.forum'));
    }

    define('TITLE', $parent->topicsubject . ' - ' . get_string('postreply','interaction.forum'));
    $topicid = $parent->topicid;

    $breadcrumbs = array(
        array(
            get_config('wwwroot') . 'group/view.php?id=' . $parent->group,
            $parent->groupname
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/index.php?group=' . $parent->group,
            get_string('nameplural', 'interaction.forum')
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/view.php?id=' . $parent->forum,
            $parent->forumtitle
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
            $parent->topicsubject
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/editpost.php?parent=' . $parentid,
            get_string('postreply', 'interaction.forum')
        )
    );
}
else { // edit post
    $post = get_record_sql(
        'SELECT p.subject, p.body, p.parent, p.topic, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.forum, p2.subject AS topicsubject, f.title AS forumtitle, f.group, g.name AS groupname
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
        INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
        INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
        INNER JOIN {group} g ON g.id = f.group
        WHERE p.id = ?
        AND p.deleted != 1
        AND p.parent IS NOT NULL',
        array($postid)
    );

    $parent = get_record_sql(
        'SELECT p.subject, p.body, p.topic, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', m.user AS moderator, g.owner AS groupowner, COUNT(p2.*)
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
        INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
        LEFT JOIN {interaction_forum_moderator} m ON (m.user = p.poster AND m.forum = f.id)
        INNER JOIN {group} g ON g.id = f.group
        INNER JOIN {interaction_forum_post} p2 ON (p.poster = p2.poster AND p2.deleted != 1)
        WHERE p.id = ?
        AND p.deleted != 1
        GROUP BY 1, 2, 3, 4, 5, 6, 7',
        array($post->parent)
    );

    if (!$post) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
    }

    $topicid = $post->topic;

    $membership = user_can_access_group((int)$post->group);

    $admin = (bool)($membership & (GROUP_MEMBERSHIP_OWNER | GROUP_MEMBERSHIP_ADMIN | GROUP_MEMBERSHIP_STAFF));

    $moderator = $admin || is_forum_moderator((int)$post->forum);

    // no record for edits to own posts with 30 minutes
    if (user_can_edit_post($post->poster, $post->ctime)) {
        $post->editrecord = false;
    }
    else if ($moderator) {
        $post->editrecord = true;
    }
    else {
        throw new AccessDeniedException(get_string('canteditpost', 'interaction.forum'));
    }

    define('TITLE', $post->topicsubject . ' - ' . get_string('editpost','interaction.forum'));

    $breadcrumbs = array(
        array(
            get_config('wwwroot') . 'group/view.php?id=' . $post->group,
            $post->groupname
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/index.php?group=' . $post->group,
            get_string('nameplural', 'interaction.forum')
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/view.php?id=' . $post->forum,
            $post->forumtitle
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
            $post->topicsubject
        ),
        array(
            get_config('wwwroot') . 'interaction/forum/editpost.php?id=' . $postid,
            get_string('editpost', 'interaction.forum')
        )
    );
}

$parent->ctime = strftime(get_string('strftimerecentfull'), $parent->ctime);

// Javascript to hide the subject box if it has nothing in it, with a link you 
// click to expand it.
$clicksetsubject = json_encode(get_string('clicksetsubject', 'interaction.forum'));
$inlinejs = <<<EOF
addLoadEvent(function() {
    var subjectInput = $('editpost_subject');
    if (subjectInput.value == '') {
        hideElement(subjectInput);
        var expandLink = A({'href': ''}, {$clicksetsubject});
        connect(expandLink, 'onclick', function(e) {
            showElement(subjectInput);
            subjectInput.focus();
            e.stop();
            removeElement(expandLink);
        });
        insertSiblingNodesBefore(subjectInput, expandLink);
    }
    tinyMCE.execCommand('mceFocus',false,'mce_editor_0');
});
EOF;

require_once('pieforms/pieform.php');

$editform = pieform(array(
    'name'     => 'editpost',
    'successcallback' => isset($post) ? 'editpost_submit' : 'addpost_submit',
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
        'topic' => array(
            'type' => 'hidden',
            'value' => $topicid
        ),
        'editrecord' => array(
            'type' => 'hidden',
            'value' => isset($post) ? $post->editrecord : false
        )
    ),
));

function editpost_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $postid = param_integer('id');
    db_begin();
    update_record(
        'interaction_forum_post',
        array(
            'subject' => $values['subject'],
            'body' => $values['body']
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
    redirect('/interaction/forum/topic.php?id=' . $values['topic']);
}

function addpost_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $parentid = param_integer('parent');
    insert_record(
        'interaction_forum_post',
        (object)array(
            'topic' => $values['topic'],
            'poster' => $USER->get('id'),
            'parent' => $parentid,
            'subject' => $values['subject'],
            'body' => $values['body'],
            'ctime' =>  db_format_timestamp(time())
        )
    );
    $SESSION->add_ok_msg(get_string('addpostsuccess', 'interaction.forum'));
    redirect('/interaction/forum/topic.php?id=' . $values['topic']);
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('heading', TITLE);
$smarty->assign('editform', $editform);
    $smarty->assign('parent', $parent);
if (isset($inlinejs)) {
    $smarty->assign('INLINEJAVASCRIPT', $inlinejs);
}
$smarty->display('interaction:forum:editpost.tpl');

?>
