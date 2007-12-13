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

$forum = get_record_sql(
    'SELECT f.title, f.description, f.id, COUNT(t.*), s.forum AS subscribed, g.id as group, g.name as groupname
    FROM {interaction_instance} f
    INNER JOIN {group} g ON g.id = f."group"
    LEFT JOIN {interaction_forum_topic} t ON (t.forum = f.id AND t.deleted != 1 AND t.sticky != 1)
    LEFT JOIN {interaction_forum_subscription_forum} s ON (s.forum = f.id AND s."user" = ?)
    WHERE f.id = ?
    AND f.deleted != 1
    GROUP BY 1, 2, 3, 5, 6, 7',
    array($userid, $forumid)
);

if (!$forum) {
    throw new InteractionInstanceNotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
}

$membership = user_can_access_group((int)$forum->group);

if (!$membership) {
    throw new AccessDeniedException(get_string('cantviewforums', 'interaction.forum'));
}

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator($forumid);

if (isset($_POST['topics'])) {
    $topics = array_keys($_POST['topics']);
    if (isset($_POST['checkbox'])) {
        $checked = array_keys($_POST['checkbox']);
    }
    else {
        $checked = array();
    }
    // check that user is only messing with topics from this forum
    $alltopics = get_column('interaction_forum_topic', 'id', 'forum', $forumid, 'deleted', 0);
    if ($topics == array_intersect($topics, $alltopics) && $checked == array_intersect($checked, $topics)) { // $topics and $checked are ok
        $unchecked = array_diff($topics, $checked);
        db_begin();
        if ($moderator && isset($_POST['sticky'])) {
            if (!empty($checked)) {
                set_field_select('interaction_forum_topic', 'sticky', 1, 'id in (' . implode($checked, ',') . ')', array());
            }
            if (!empty($unchecked)) {
                set_field_select('interaction_forum_topic', 'sticky', 0, 'id in (' . implode($unchecked, ',') . ')', array());
            }
            $SESSION->add_ok_msg(get_string('topicstickysuccess', 'interaction.forum'));
        }
        else if ($moderator && isset($_POST['closed'])) {
            if (!empty($checked)) {
                set_field_select('interaction_forum_topic', 'sticky', 1, 'id in (' . implode($checked, ',') . ')', array());
            }
            if (!empty($unchecked)) {
                set_field_select('interaction_forum_topic', 'sticky', 0, 'id in (' . implode($unchecked, ',') . ')', array());
            }
            $SESSION->add_ok_msg(get_string('topicclosedsuccess', 'interaction.forum'));
        }
        else if (isset($_POST['subscribe']) && !$forum->subscribed) {
            if (!empty($unchecked)) {
                delete_records_sql('DELETE FROM {interaction_forum_subscription_topic}
                    WHERE topic in (' . implode($unchecked, ',') . ') AND "user" = ?',
                    array($USER->get('id')
                ));
            }
            foreach ($checked as $key => $value) {
                insert_record('interaction_forum_subscription_topic',
                    (object) array(
                        'user' => $USER->get('id'),
                        'topic' => $value
                    ));
            }
            $SESSION->add_ok_msg(get_string('topicsubscribesuccess', 'interaction.forum'));
        }
        db_commit();
        redirect('/interaction/forum/view.php?id=' . $forumid);
    }
    else {
        $SESSION->add_error_msg(get_string('topicupdatefailed', 'interaction.forum'));
        redirect('/interaction/forum/view.php?id=' . $forumid);
    }
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

require_once('pieforms/pieform.php');

$forum->subscribe = pieform(array(
    'name' => 'subscribe_forum',
    'autofocus' => false,
    'elements' => array(
        'submit' => array(
            'type' => 'submit',
            'value' => $forum->subscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
        ),
        'forum' => array(
            'type' => 'hidden',
            'value' => $forumid
        ),
        'redirect' => array(
            'type' => 'hidden',
            'value' => '/interaction/forum/view.php?id=' . $forumid . '&amp;offset=' . $offset
        )
    )
));

$sql = 'SELECT t.id, p1.subject, p1.poster, p1.body, COUNT(p2.*), t.closed, s.topic AS subscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_forum_post} p1 ON (p1.topic = t.id AND p1.parent IS NULL)
    LEFT JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.deleted != 1)
    LEFT JOIN {interaction_forum_subscription_topic} s ON (s.topic = t.id AND s."user" = ?)
    WHERE t.forum = ?
    AND t.sticky = ?
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 4, 6, 7
    ORDER BY MAX(p2.ctime) DESC';

$stickytopics = get_records_sql_array($sql, array($userid, $forumid, 1));

$regulartopics = get_records_sql_array($sql, array($userid, $forumid, 0), $offset, $topicsperpage);

if ($stickytopics) {
    foreach ($stickytopics as $topic) {
        $topic->body = substr(strip_tags($topic->body), 0, 50);
    }
}

if ($regulartopics) {
    foreach ($regulartopics as $topic) {
        $topic->body = substr(strip_tags($topic->body), 0, 50);
    }
}

$pagination = build_pagination(array(
    'url' => 'view.php?id=' . $forumid,
    'count' => $forum->count,
    'limit' => $topicsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('topiclower', 'interaction.forum'),
    'resultcounttextplural' => get_string('topicslower', 'interaction.forum')
));

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('groupname', $forum->groupname);
$smarty->assign('forum', $forum);
$smarty->assign('moderator', $moderator);
$smarty->assign('admin', $admin);
$smarty->assign('stickytopics', $stickytopics);
$smarty->assign('regulartopics', $regulartopics);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('interaction:forum:view.tpl');
?>
