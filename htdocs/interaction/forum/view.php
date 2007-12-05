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
require_once('group.php');
safe_require('interaction', 'forum');
define('TITLE', get_string('name', 'interaction.forum'));

$forumid = param_integer('id');
$offset = param_integer('offset', 0);
$userid = $USER->get('id');
$topicsperpage = 25;

$group = get_record_sql(
    'SELECT g.id, g.name
    FROM {interaction_instance} f
    INNER JOIN {group} g
    ON g.id = f."group"
    WHERE f.id = ?
    AND f.deleted != 1',
    array($forumid)
);

if (!$group) {
    throw new InteractionInstanceNotFoundException("Couldn't find forum with id $forumid");
}

$membership = user_can_access_group((int)$group->id);

if (!$membership) {
    throw new AccessDeniedException();
}

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator($forumid);

if (isset($_POST['subscribe'])) {
    $values = array_flip($_POST['subscribe']);
    if (isset($values[get_string('subscribe', 'interaction.forum')])) {
        insert_record(
            'interaction_forum_subscription_topic',
            (object)array(
                'topic' => $values[get_string('subscribe', 'interaction.forum')],
                'user' => $USER->get('id')
            )
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_topic',
            'topic', $values[get_string('unsubscribe', 'interaction.forum')],
            'user', $USER->get('id')
        );
    }
}

if ($moderator && isset($_POST['update'])) {
    if (!isset($_POST['sticky']) || !is_numeric(implode(array_keys($_POST['sticky'])))) {
        $_POST['sticky'] = array();
    }
    if (!isset($_POST['closed']) || !is_numeric(implode(array_keys($_POST['closed'])))) {
        $_POST['closed'] = array();
    }
    if (!isset($_POST['prevsticky']) || !is_numeric(implode(array_keys($_POST['prevsticky'])))) {
        $_POST['prevsticky'] = array();
    }
    if (!isset($_POST['prevclosed']) || !is_numeric(implode(array_keys($_POST['prevclosed'])))) {
        $_POST['prevclosed'] = array();
    }
    updatetopics($_POST['sticky'], $_POST['prevsticky'], 'sticky = 1');
    updatetopics($_POST['prevsticky'], $_POST['sticky'], 'sticky = 0');
    updatetopics($_POST['closed'], $_POST['prevclosed'], 'closed = 1');
    updatetopics($_POST['prevclosed'], $_POST['closed'], 'closed = 0');
}

$forum = get_record_sql(
    'SELECT f.title, f.description, f.id, COUNT(t.*), s.forum AS subscribed
    FROM {interaction_instance} f
    LEFT JOIN {interaction_forum_topic} t
    ON t.forum = f.id
    AND t.deleted != 1
    AND t.sticky != 1
    LEFT JOIN {interaction_forum_subscription_forum} s
    ON s.forum = f.id
    AND s."user" = ?
    WHERE f.id = ?
    AND f.deleted != 1
    GROUP BY 1, 2, 3, 5',
    array($userid, $forumid)
);

require_once('pieforms/pieform.php');

$forum->subscribe = pieform(array(
    'name'     => 'subscribe_forum',
    'elements' => array(
        'submit' => array(
            'type'  => 'submit',
            'value' => $forum->subscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
        )
    )
));

$stickytopics = get_records_sql_array(
    'SELECT t.id, p1.subject, p1.poster, COUNT(p2.*), t.closed, s.topic AS subscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_forum_post} p1
    ON p1.topic = t.id
    AND p1.parent IS NULL
    LEFT JOIN {interaction_forum_post} p2
    ON p2.topic = t.id
    AND p2.deleted != 1
    LEFT JOIN {interaction_forum_subscription_topic} s
    ON s.topic = t.id
    AND s."user" = ?
    WHERE t.forum = ?
    AND t.sticky = 1
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 5, 6
    ORDER BY MAX(p2.ctime) DESC',
    array($userid, $forumid)
);

$regulartopics = get_records_sql_array(
    'SELECT t.id, p1.subject, p1.poster, COUNT(p2.*), t.closed, s.topic AS subscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_forum_post} p1
    ON p1.topic = t.id
    AND p1.parent IS NULL
    LEFT JOIN {interaction_forum_post} p2
    ON p2.topic = t.id
    AND p2.deleted != 1
    LEFT JOIN {interaction_forum_subscription_topic} s
    ON s.topic = t.id
    AND s."user" = ?
    WHERE t.forum = ?
    AND t.sticky != 1
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 5, 6
    ORDER BY MAX(p2.ctime) DESC',
    array($userid, $forumid),
    $offset,
    $topicsperpage
);

$pagination = build_pagination(array(
    'url' => 'view.php?id=' . $forumid,
    'count' => $forum->count,
    'limit' => $topicsperpage,
    'offset' => $offset,
));

$smarty = smarty();
$smarty->assign('groupname', $group->name);
$smarty->assign('forum', $forum);
$smarty->assign('moderator', $moderator);
$smarty->assign('admin', $admin);
$smarty->assign('stickytopics', $stickytopics);
$smarty->assign('regulartopics', $regulartopics);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('interaction:forum:view.tpl');

function subscribe_forum_submit(Pieform $form, $values) {
    global $USER;
    $forumid = param_integer('id');
    $offset = param_integer('offset', 0);
    if ($values['submit'] == get_string('subscribe', 'interaction.forum')) {
        insert_record(
            'interaction_forum_subscription_forum',
            (object)array(
                'forum' => $forumid,
                'user' => $USER->get('id')
            )
        );
        delete_records_sql(
            'DELETE FROM {interaction_forum_subscription_topic}
            WHERE topic IN (
                SELECT id
                FROM {interaction_forum_topic}
                WHERE forum = ?
                AND "user" = ?
            )',
            array($forumid, $USER->get('id'))
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_forum',
            'forum', $forumid,
            'user', $USER->get('id')
        );
    }
    redirect('/interaction/forum/view.php?id=' . $forumid . '&offset=' . $offset);
}

function updatetopics($new, $old, $set) {
    $keydiff = array_keys(array_diff_key($new, $old));
    if (!empty($keydiff)) {
        execute_sql(
            'UPDATE {interaction_forum_topic}
            SET ' . $set .
            'WHERE id in (' . implode(',', $keydiff) . ')'
        );
    }
}

?>
