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
define('TITLE', get_string('topic','interaction.forum'));

$topicid = param_integer('id');

$info = get_record_sql(
    'SELECT p.subject, f.group, f.id as forum, t.closed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f
    ON (t.forum = f.id)
    INNER JOIN {interaction_forum_post} p
    ON (p.topic = t.id AND p.parent IS NULL)
    WHERE t.id = ?',
    array($topicid)
);
$membership = user_can_access_group((int)$info->group);

if (!$membership) {
	throw new AccessDeniedException();
}

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$moderator = $admin || is_forum_moderator((int)$info->forum);

$posts = get_records_sql_array(
    'SELECT p1.id, p1.parent, p1.poster, p1.subject, p1.body, p1.ctime as posttime, COUNT(p2.*), e.ctime as edit, e.user as editor
    FROM interaction_forum_post p1
    INNER JOIN interaction_forum_post p2
    ON (p1.poster = p2.poster
    AND p2.deleted != 1
    AND p2.topic IN (
        SELECT t.id
        FROM interaction_forum_topic t
        WHERE t.deleted != 1
    ))
    LEFT JOIN interaction_forum_edit e
    ON (e.post = p1.id)
    WHERE p1.topic = ?
    AND p1.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 8, 9
    ORDER BY p1.ctime',
    array($topicid)
);

$count = count($posts);
for ($i = 0; $i < $count; $i++) {
	$postedits = array();
    if (!empty($posts[$i]->edit)) {
        $postedits[] = array(
            'edittime' => $posts[$i]->edit,
    	    'editor'   => $posts[$i]->editor
        );
    }
    $temp = $i;
    while (isset($posts[$i+1]) && $posts[$i+1]->id == $posts[$temp]->id) {
    	$i++;
        $postedits[] = array(
            'edittime' => $posts[$i]->edit,
    	    'editor'   => $posts[$i]->editor
        );
        unset($posts[$i]);
    }
    $posts[$temp]->edit = $postedits;
}

foreach ($posts as $post) {
    if ($post->poster == $USER->get('id') && (time() - strtotime($post->posttime)) < (30 * 60)) {
        $post->editor = true;
    }
    else {
    	$post->editor = false;
    }
}

$threadedposts = buildthread(0, '', $posts);

$smarty = smarty();
$smarty->assign('id', $topicid);
$smarty->assign('subject', $info->subject);
$smarty->assign('moderator', $moderator);
$smarty->assign('closed', $info->closed);
$smarty->assign('posts', $threadedposts);
$smarty->display('interaction:forum:topic.tpl');

function buildthread($parent, $parentsubject, &$posts){
	global $moderator;
	global $info;
	if ($posts[$parent]->subject) {
	    $parentsubject = $posts[$parent]->subject;
	}
	else {
        $posts[$parent]->subject = get_string('re', 'interaction.forum') . $parentsubject;
	}
    $children = array();
	foreach ($posts as $index => $post) {
		if ($posts[$index]->parent == $posts[$parent]->id) {
			$children[] = buildthread($index, $parentsubject, $posts);
		}
	}
	$smarty = smarty_core();
	$smarty->assign('post', $posts[$parent]);
	$smarty->assign('children', $children);
    $smarty->assign('moderator', $moderator);
    $smarty->assign('closed', $info->closed);
	return $smarty->fetch('interaction:forum:post.tpl');
}

?>
