<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'groups/forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'view');

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
    'SELECT f.title, f.description, f.id, COUNT(t.id) AS topiccount, s.forum AS subscribed, g.id AS groupid, g.name AS groupname, ic.value AS newtopicusers
    FROM {interaction_instance} f
    INNER JOIN {group} g ON (g.id = f."group" AND g.deleted = ?)
    LEFT JOIN {interaction_forum_topic} t ON (t.forum = f.id AND t.deleted != 1 AND t.sticky != 1)
    LEFT JOIN {interaction_forum_subscription_forum} s ON (s.forum = f.id AND s."user" = ?)
    LEFT JOIN {interaction_forum_instance_config} ic ON (f.id = ic.forum AND ic.field = \'createtopicusers\')
    WHERE f.id = ?
    AND f.deleted != 1
    GROUP BY 1, 2, 3, 5, 6, 7, 8',
    array(0, $userid, $forumid)
);

define('GROUP', $forum->groupid);

if (!$forum) {
    throw new InteractionInstanceNotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
}

$membership = user_can_access_forum((int)$forumid);
$admin = (bool)($membership & INTERACTION_FORUM_ADMIN);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);
$publicgroup = get_field('group', 'public', 'id', $forum->groupid);
if (!$membership && !$publicgroup) {
    throw new GroupAccessDeniedException(get_string('cantviewforums', 'interaction.forum'));
}

define('TITLE', $forum->groupname . ' - ' . $forum->title);

$feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=f&id=' . $forum->id;

$moderators = get_column_sql(
    'SELECT gm.user FROM {interaction_forum_moderator} gm
    INNER JOIN {usr} u ON (u.id = gm.user AND u.deleted = 0)
    WHERE gm.forum = ?',
    array($forumid)
);

// updates the selected topics as subscribed/closed/sticky
if ($membership && isset($_POST['checked'])) {
    $checked = array_map('intval', array_keys($_POST['checked']));
    // get type based on which button was pressed
    if (isset($_POST['updatetopics'])) {
        $type = $_POST['type'];
    }
    // check that user is only messing with topics from this forum
    $alltopics = get_column('interaction_forum_topic', 'id', 'forum', $forumid, 'deleted', 0);
    if ($checked == array_intersect($checked, $alltopics)) { // $checked is a subset of the topics in this forum
        form_validate(param_variable('sesskey', null));
        if ($moderator && $type == 'sticky') {
            set_field_select('interaction_forum_topic', 'sticky', 1, 'id IN (' . implode(',', $checked) . ')', array());
            $SESSION->add_ok_msg(get_string('topicstickysuccess', 'interaction.forum'));
        }
        else if ($moderator && $type == 'unsticky') {
            set_field_select('interaction_forum_topic', 'sticky', 0, 'id IN (' . implode(',', $checked) . ')', array());
            $SESSION->add_ok_msg(get_string('topicunstickysuccess', 'interaction.forum'));
        }
        else if ($moderator && $type == 'closed') {
            set_field_select('interaction_forum_topic', 'closed', 1, 'id IN (' . implode(',', $checked) . ')', array());
            $SESSION->add_ok_msg(get_string('topicclosedsuccess', 'interaction.forum'));
        }
        else if ($moderator && $type == 'open') {
            set_field_select('interaction_forum_topic', 'closed', 0, 'id IN (' . implode(',', $checked) . ')', array());
            $SESSION->add_ok_msg(get_string('topicopenedsuccess', 'interaction.forum'));
        }
        else if ($type == 'subscribe' && !$forum->subscribed) {
            db_begin();
            foreach ($checked as $key => $value) {
                if (!record_exists('interaction_forum_subscription_topic', 'user', $USER->get('id'), 'topic', $value)) {
                    insert_record('interaction_forum_subscription_topic',
                        (object) array(
                            'user'  => $USER->get('id'),
                            'topic' => $value,
                            'key'   => PluginInteractionForum::generate_unsubscribe_key(),
                    ));
                }
            }
            db_commit();
            $SESSION->add_ok_msg(get_string('topicsubscribesuccess', 'interaction.forum'));
        }
        else if ($type == 'unsubscribe' && !$forum->subscribed) {
            delete_records_sql('DELETE FROM {interaction_forum_subscription_topic}
                WHERE topic IN (' . implode(',', $checked) . ') AND "user" = ?',
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

if ($membership) {
    $forum->subscribe = pieform(array(
        'name' => 'subscribe_forum',
        'renderer' => 'div',
        'plugintype' => 'interaction',
        'pluginname' => 'forum',
        'autofocus' => false,
        'elements' => array(
            'submit' => array(
                'type' => 'submit',
                'class' => 'btn-subscribe',
                'value' => $forum->subscribed ? get_string('unsubscribefromforum', 'interaction.forum') : get_string('subscribetoforum', 'interaction.forum'),
                'help' => true
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
}

// gets the info about topics
// the last post is found by taking the max id of the posts in a topic with the max post time
// taking the max id is needed because multiple posts can have the same post time
$sql = 'SELECT t.id, p1.subject, p1.body, p1.poster, p1.deleted, m.user AS moderator, COUNT(p2.id) AS postcount, t.closed, s.topic AS subscribed, p4.id AS lastpost, ' . db_format_tsfield('p4.ctime', 'lastposttime') . ', p4.poster AS lastposter, m2.user AS lastpostermoderator
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_forum_post} p1 ON (p1.topic = t.id AND p1.parent IS NULL)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
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
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
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
    'url' => get_config('wwwroot') . 'interaction/forum/view.php?id=' . $forumid,
    'count' => $forum->topiccount,
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

$headers = array();
if ($publicgroup) {
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '" />';
}

$smarty = smarty(array(), $headers, array(), array());
$smarty->assign('heading', $forum->groupname);
$smarty->assign('subheading', $forum->title);
$smarty->assign('forum', $forum);
$smarty->assign('publicgroup', $publicgroup);
$smarty->assign('feedlink', $feedlink);
$smarty->assign('membership', $membership);
$smarty->assign('moderator', $moderator);
$smarty->assign('admin', $admin);
$smarty->assign('groupadmins', group_get_admin_ids($forum->groupid));
$smarty->assign('stickytopics', $stickytopics);
$smarty->assign('regulartopics', $regulartopics);
$smarty->assign('moderators', $moderators);
$smarty->assign('closedicon', $THEME->get_url('images/closed.gif', false, 'interaction/forum'));
$smarty->assign('subscribedicon', $THEME->get_url('images/subscribed.gif', false, 'interaction/forum'));
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
            $topic->lastposttime = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecent'), $topic->lastposttime);
            $topic->feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=t&id=' . $topic->id;
        }
    }
}
