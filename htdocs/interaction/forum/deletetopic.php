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
require('group.php');
$topicid = param_integer('id');

$topic = get_record_sql(
    'SELECT f."group", f.id as forumid, f.title, p.subject, p.body
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f
    ON f.id = t.forum
    AND f.deleted != 1
    INNER JOIN {interaction_forum_post} p
    ON p.topic = t.id
    AND p.parent IS NULL
    WHERE t.id = ?
    AND t.deleted != 1',
    array($topicid)
);

if (!$topic) {
	throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
}

$membership = user_can_access_group((int)$topic->group);

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator((int)$topic->forumid);

if (!$moderator) {
    throw new AccessDeniedException(get_string('cantdeletetopic', 'interaction.forum'));
}

define('TITLE', get_string('deletetopicvariable', 'interaction.forum', $topic->subject));

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'interaction/forum/index.php?group=' . $topic->group,
        get_string('nameplural', 'interaction.forum')
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/view.php?id=' . $topic->forumid,
        $topic->title
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
        $topic->subject
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/deletetopic.php?id=' . $topicid,
        get_string('deletetopic', 'interaction.forum')
    )
);

require_once('pieforms/pieform.php');

$form = pieform(array(
    'name'     => 'deletepost',
    'elements' => array(
        'title' => array(
            'value' => get_string('deletetopicsure', 'interaction.forum'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
        )
    )
));

function deletepost_submit(Pieform $form, $values) {
    $topicid = param_integer('id');
    update_record(
        'interaction_forum_topic',
        array('deleted' => 1),
        array('id' => $topicid)
    );
    $forumid = get_record_sql(
        'SELECT forum
        FROM interaction_forum_topic
        WHERE id = ?',
        array($topicid)
    )->forum;
    redirect('/interaction/forum/view.php?id=' . $forumid);
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('forum', $topic->title);
$smarty->assign('heading', TITLE);
$smarty->assign('deleteform', $form);
$smarty->display('interaction:forum:deletetopic.tpl');

?>
