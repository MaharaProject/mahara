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
    'SELECT p.subject, p.topic, p.parent, t.forum, p2.subject as topicsubject, f.group
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
    throw new NotFoundException("Couldn't find post with id $postid");
}

$membership = user_can_access_group((int)$post->group);

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator((int)$post->forum);

if (!$moderator) {
    throw new AccessDeniedException();
}

define('TITLE', get_string('deletepost', 'interaction.forum', $post->subject));

require_once('pieforms/pieform.php');

$form = pieform(array(
    'name'     => 'deletepost',
    'elements' => array(
        'title' => array(
            'value' => get_string('deletepostsure', 'interaction.forum'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic,
        )
    )
));

function deletepost_submit(Pieform $form, $values) {
    global $postid;
    global $post;
    update_record(
        'interaction_forum_post',
        array('deleted' => 1),
        array('id' => $postid)
    );
    redirect('/interaction/forum/topic.php?id=' . $post->topic);
}

$smarty = smarty();
$smarty->assign('topicsubject', $post->topicsubject);
$smarty->assign('heading', TITLE);
$smarty->assign('deleteform', $form);
$smarty->display('interaction:forum:deletepost.tpl');

?>
