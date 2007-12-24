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

$groupid = param_integer('group');

if (!record_exists('group', 'id', $groupid)) {
    throw new GroupNotFoundException(get_string('groupnotfound', 'group', $groupid));
}

$group = get_record('group', 'id', $groupid);

$membership = user_can_access_group($groupid);

if (!$membership) {
    throw new AccessDeniedException(get_string('cantviewforums', 'interaction.forum'));
}

$admin = (bool)($membership & (GROUP_MEMBERSHIP_OWNER | GROUP_MEMBERSHIP_ADMIN | GROUP_MEMBERSHIP_STAFF));

define('TITLE', $group->name . ' - ' . get_string('nameplural', 'interaction.forum'));

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'group/view.php?id=' . $groupid,
        $group->name
    ),
    array(
        get_config('wwwroot') . 'interaction/forum/index.php?group=' . $groupid,
        get_string('nameplural', 'interaction.forum')
    )
);

$forums = get_records_sql_array(
    'SELECT f.id, f.title, f.description, m.user AS moderator, COUNT(t.*), s.forum AS subscribed
    FROM {interaction_instance} f
    LEFT JOIN {interaction_forum_moderator} m ON (m.forum = f.id)
    LEFT JOIN {interaction_forum_topic} t ON (t.forum = f.id AND t.deleted != 1)
    INNER JOIN {interaction_forum_instance_config} c ON (c.forum = f.id AND c.field = \'weight\')
    LEFT JOIN {interaction_forum_subscription_forum} s ON (s.forum = f.id AND s."user" = ?)
    WHERE f.group = ?
    AND f.deleted != 1
    GROUP BY 1, 2, 3, 4, 6, c.value
    ORDER BY c.value',
    array($USER->get('id'), $groupid)
);

// query gets a new forum object for every moderator of that forum
// this combines all moderators together into one object per forum
if ($forums) {
    $count = count($forums);
    for ($i = 0; $i < $count; $i++) {
        $forums[$i]->moderators = array();
        if ($forums[$i]->moderator) {
            $forums[$i]->moderators[] = $forums[$i]->moderator;
        }
        $temp = $i;
        while (isset($forums[$i+1]) && $forums[$i+1]->id == $forums[$temp]->id) {
            $i++;
            $forums[$temp]->moderators[] = $forums[$i]->moderator;
            unset($forums[$i]);
        }
   }
}

require_once('pieforms/pieform.php');

$i = 0;
if ($forums) {
    foreach ($forums as $forum) {
        $forum->subscribe = pieform(array(
            'name'     => 'subscribe'.$i++,
            'successcallback' => 'subscribe_forum_submit',
            'autofocus' => false,
            'elements' => array(
                'submit' => array(
                    'type'  => 'submit',
                    'value' => $forum->subscribed ? get_string('Unsubscribe', 'interaction.forum') : get_string('Subscribe', 'interaction.forum')
                ),
                'forum' => array(
                    'type' => 'hidden',
                    'value' => $forum->id
                ),
                'redirect' => array(
                    'type' => 'hidden',
                    'value' => 'index'
                ),
                'group' => array(
                    'type' => 'hidden',
                    'value' => $groupid
                ),
                'type' => array(
                    'type' => 'hidden',
                    'value' => $forum->subscribed ? 'unsubscribe' : 'subscribe'
                )
            )
        ));
    }
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('groupid', $groupid);
$smarty->assign('groupowner', $group->owner);
$smarty->assign('heading', TITLE);
$smarty->assign('admin', $admin);
$smarty->assign('forums', $forums);
$smarty->display('interaction:forum:index.tpl');

?>
