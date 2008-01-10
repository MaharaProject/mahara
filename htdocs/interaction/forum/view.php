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
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('pieforms/pieform.php');

$forumid = param_integer('id');
$offset = param_integer('offset', 0);
$userid = $USER->get('id');
$topicsperpage = 25;

// if offset isn't a multiple of $topicsperpage, make it the closest smaller multiple
$offset = (int)($offset / $topicsperpage) * $topicsperpage;

$forum = get_record_sql(
    'SELECT f.title, f.description, f.id, COUNT(t.*), s.forum AS subscribed, g.id AS group, g.name AS groupname, g.owner as groupowner
    FROM {interaction_instance} f
    INNER JOIN {group} g ON g.id = f."group"
    LEFT JOIN {interaction_forum_topic} t ON (t.forum = f.id AND t.deleted != 1 AND t.sticky != 1)
    LEFT JOIN {interaction_forum_subscription_forum} s ON (s.forum = f.id AND s."user" = ?)
    WHERE f.id = ?
    AND f.deleted != 1
    GROUP BY 1, 2, 3, 5, 6, 7, 8',
    array($userid, $forumid)
);

if (!$forum) {
    throw new InteractionInstanceNotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
}

$membership = user_can_access_forum((int)$forumid);
$admin = (bool)($membership & INTERACTION_FORUM_ADMIN);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$membership) {
    throw new AccessDeniedException(get_string('cantviewforums', 'interaction.forum'));
}

define('TITLE', $forum->groupname . ' - ' . $forum->title);

$moderators = get_column_sql(
    'SELECT m.user
    FROM {interaction_forum_moderator} m
    INNER JOIN {group_member} gm ON (m.user = gm.member AND gm.group = ?)
    WHERE m.forum = ?
    ORDER BY m.user',
    array($forum->group, $forumid)
);

// updates the selected topics as subscribed/closed/sticky
if (isset($_POST['checked'])) {
    $checked = array_keys($_POST['checked']);
    // get type based on which button was pressed
    if (isset($_POST['updatetopics1'])) {
        $type = $_POST['type1'];
    }
    else if (isset($_POST['updatetopics2'])) {
        $type = $_POST['type2'];
    }
    // check that user is only messing with topics from this forum
    $alltopics = get_column('interaction_forum_topic', 'id', 'forum', $forumid, 'deleted', 0);
    if ($checked == array_intersect($checked, $alltopics)) { // $checked is a subset of the topics in this forum
        if ($moderator && $type == 'sticky') {
            set_field_select('interaction_forum_topic', 'sticky', 1, 'id IN (' . implode($checked, ',') . ')', array());
            $SESSION->add_ok_msg(get_string('topicstickysuccess', 'interaction.forum'));
        }
        else if ($moderator && $type == 'unsticky') {
            set_field_select('interaction_forum_topic', 'sticky', 0, 'id IN (' . implode($checked, ',') . ')', array());
            $SESSION->add_ok_msg(get_string('topicunstickysuccess', 'interaction.forum'));
        }
        else if ($moderator && $type == 'closed') {
            set_field_select('interaction_forum_topic', 'closed', 1, 'id IN (' . implode($checked, ',') . ')', array());
            $SESSION->add_ok_msg(get_string('topicclosedsuccess', 'interaction.forum'));
        }
        else if ($moderator && $type == 'open') {
            set_field_select('interaction_forum_topic', 'closed', 0, 'id IN (' . implode($checked, ',') . ')', array());
            $SESSION->add_ok_msg(get_string('topicopenedsuccess', 'interaction.forum'));
        }
        else if ($type == 'subscribe' && !$forum->subscribed) {
            db_begin();
            foreach ($checked as $key => $value) {
                insert_record('interaction_forum_subscription_topic',
                    (object) array(
                        'user' => $USER->get('id'),
                        'topic' => $value
                    ));
            }
            db_commit();
            $SESSION->add_ok_msg(get_string('topicsubscribesuccess', 'interaction.forum'));
        }
        else if ($type == 'unsubscribe' && !$forum->subscribed) {
            delete_records_sql('DELETE FROM {interaction_forum_subscription_topic}
                WHERE topic IN (' . implode($checked, ',') . ') AND "user" = ?',
                array($USER->get('id')
            ));
            $SESSION->add_ok_msg(get_string('topicunsubscribesuccess', 'interaction.forum'));
        }
    }
    else { // $checked contains bad values
        $SESSION->add_error_msg(get_string('topicupdatefailed', 'interaction.forum'));
    }
    redirect('/interaction/forum/view.php?id=' . $forumid . '&offset=' . $offset);
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

$forum->subscribe = pieform(array(
    'name' => 'subscribe_forum',
    'autofocus' => false,
    'elements' => array(
        'submit' => array(
            'type' => 'submit',
            'value' => $forum->subscribed ? get_string('unsubscribefromforum', 'interaction.forum') : get_string('subscribetoforum', 'interaction.forum')
        ),
        'forum' => array(
            'type' => 'hidden',
            'value' => $forumid
        ),
        'redirect' => array(
            'type' => 'hidden',
            'value' => 'view'
        ),
        'offset' => array(
            'type' => 'hidden',
            'value' => $offset
        ),
        'type' => array(
            'type' => 'hidden',
            'value' => $forum->subscribed ? 'unsubscribe' : 'subscribe'
        )
    )
));

// gets the info about topics
// the last post is found by taking the max id of the posts in a topic with the max post time
// taking the max id is needed because multiple posts can have the same post time
$sql = 'SELECT t.id, p1.subject, p1.body, p1.poster, p1.deleted, m.user AS moderator, COUNT(p2.*), t.closed, s.topic AS subscribed, p4.id AS lastpost, ' . db_format_tsfield('p4.ctime', 'lastposttime') . ', p4.poster AS lastposter, m2.user AS lastpostermoderator
    FROM interaction_forum_topic t
    INNER JOIN {interaction_forum_post} p1 ON (p1.topic = t.id AND p1.parent IS NULL)
    LEFT JOIN (
        SELECT fm.user, fm.forum
        FROM {interaction_forum_moderator} fm
        INNER JOIN {interaction_instance} f ON (fm.forum = f.id)
        INNER JOIN {group_member} gm ON (gm.group = f.group AND gm.member = fm.user)
    ) m ON (m.forum = t.forum AND p1.poster = m.user)
    INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.deleted != 1)
    LEFT JOIN {interaction_forum_subscription_topic} s ON (s.topic = t.id AND s."user" = ?)
    INNER JOIN (
        SELECT MAX(p2.id) AS post, t.id AS topic
        FROM {interaction_forum_topic} t
        INNER JOIN (
            SELECT MAX(p.ctime) AS ctime, t.id AS topic
            FROM {interaction_forum_topic} t
            INNER JOIN {interaction_forum_post} p ON (p.topic = t.id AND p.deleted = 0)
            GROUP BY 2
        ) p1 ON t.id = p1.topic
        INNER JOIN {interaction_forum_post} p2 ON (p1.topic = p2.topic AND p1.ctime = p2.ctime AND p2.deleted = 0)
        GROUP BY 2
    ) p3 ON p3.topic = t.id
    LEFT JOIN {interaction_forum_post} p4 ON (p4.id = p3.post)
    LEFT JOIN {interaction_forum_topic} t2 ON (p4.topic = t2.id)
    LEFT JOIN (
        SELECT fm.user, fm.forum
        FROM {interaction_forum_moderator} fm
        INNER JOIN {interaction_instance} f ON (fm.forum = f.id)
        INNER JOIN {group_member} gm ON (gm.group = f.group AND gm.member = fm.user)
    ) m2 ON (p4.poster = m2.user AND t2.forum = m2.forum)
    WHERE t.forum = ?
    AND t.sticky = ?
    AND t.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 8, 9, 10, p4.ctime, p4.poster, p4.id, m2.user
    ORDER BY p4.ctime DESC, p4.id DESC';

$stickytopics = get_records_sql_array($sql, array($userid, $forumid, 1));

$regulartopics = get_records_sql_array($sql, array($userid, $forumid, 0), $offset, $topicsperpage);

setup_topics($stickytopics);
setup_topics($regulartopics);

$pagination = build_pagination(array(
    'url' => 'view.php?id=' . $forumid,
    'count' => $forum->count,
    'limit' => $topicsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('topiclower', 'interaction.forum'),
    'resultcounttextplural' => get_string('topicslower', 'interaction.forum')
));

$inlinejavascript = <<<EOF
addLoadEvent(function() {
    forEach(getElementsByTagAndClassName('input', 'topic-checkbox'), function(checkbox) {
        var tr = getFirstParentByTagAndClassName(checkbox, 'tr', null);
        var origColour = tr.style.backgroundColor;
        connect(checkbox, 'onclick', function(e) {
            if (tr.style.backgroundColor == origColour) {
                tr.style.backgroundColor = '#ffc';
            }
            else {
                tr.style.backgroundColor = origColour;
            }
        });
    });
});
EOF;

$smarty = smarty(array(), array(), array(), array('sideblocks' => array(interaction_sideblock($forum->group))));
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('heading', TITLE);
$smarty->assign('forum', $forum);
$smarty->assign('moderator', $moderator);
$smarty->assign('admin', $admin);
$smarty->assign('stickytopics', $stickytopics);
$smarty->assign('regulartopics', $regulartopics);
$smarty->assign('moderators', $moderators);
$smarty->assign('groupowner', $forum->groupowner);
$smarty->assign('closedicon', theme_get_url('images/closed.gif', 'interaction/forum/'));
$smarty->assign('subscribedicon', theme_get_url('images/subscribed.gif', 'interaction/forum/'));
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', $inlinejavascript);
$smarty->display('interaction:forum:view.tpl');

/**
 * format body
 * format lastposttime
 */
function setup_topics(&$topics) {
    if ($topics) {
        foreach ($topics as $topic) {
            $more = false;
            $nextbreak = strpos($topic->body, '<p', 1);
            if ($nextbreak !== false) {
                $topic->body = substr($topic->body, 0, $nextbreak);
                $more = true;
            }
            $nextbreak = strpos($topic->body, '<br', 1);
            if ($nextbreak !== false) {
                $topic->body = substr($topic->body, 0, $nextbreak);
                $more = true;
            }
            $topic->body = strip_tags($topic->body);
            $topic->body = html_entity_decode($topic->body); // no things like &nbsp; only take up one character
            // take the first 50 chars, then up to the first space (max length 60 chars)
            if (strlen($topic->body) > 60) {
                $topic->body = substr($topic->body, 0, 60);
                $nextspace = strpos($topic->body, ' ', 50);
                if ($nextspace !== false) {
                    $topic->body = substr($topic->body, 0, $nextspace);
                }
                $more = true;
            }
            if ($more) {
                $topic->body .= '...';
            }
            $topic->body = htmlspecialchars($topic->body);
            $topic->lastposttime = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecent'), $topic->lastposttime);
        }
    }
}

?>
