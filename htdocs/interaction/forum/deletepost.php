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
safe_require('interaction' ,'forum');
require_once('group.php');

$postid = param_integer('id');
$post = get_record_sql(
    'SELECT p.subject, p.body, p.topic, p.parent, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.forum, p2.subject AS topicsubject, f.group, f.title AS forumtitle, g.name AS groupname, COUNT(p3.*)
    FROM {interaction_forum_post} p
    INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
    INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    INNER JOIN {group} g ON g.id = f.group
    INNER JOIN {interaction_forum_post} p3 ON (p.poster = p3.poster AND p3.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p3.topic = t2.id)
    INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
    WHERE p.id = ?
    AND p.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11',
    array($postid)
);


if (!$post) {
    throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
}

$membership = user_can_access_group((int)$post->group);

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator((int)$post->forum);

if (!$moderator) {
    throw new AccessDeniedException(get_string('cantdeletepost', 'interaction.forum'));
}

define('TITLE', get_string('deletepost', 'interaction.forum') . ' - ' . $post->topicsubject);
$post->ctime = strftime(get_string('strftimerecentfull'), $post->ctime);

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'group/view.php?id=' . $post->group,
        $post->groupname,
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
        get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic,
        $post->topicsubject
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/deletepost.php?id=' . $postid,
        get_string('deletepost', 'interaction.forum')
    )
);

require_once('pieforms/pieform.php');

$form = pieform(array(
    'name'     => 'deletepost',
    'autofocus' => false,
    'elements' => array(
        'title' => array(
            'value' => get_string('deletepostsure', 'interaction.forum'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic,
        ),
        'post' => array(
            'type' => 'hidden',
            'value' => $postid
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $post->topic
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
    redirect('/interaction/forum/topic.php?id=' . $values['topic']);
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('heading', TITLE);
$smarty->assign('post', $post);
$smarty->assign('deleteform', $form);
$smarty->display('interaction:forum:deletepost.tpl');

?>
