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
define('TITLE', get_string('nameplural', 'interaction.forum'));

$groupid = param_integer('group');

if (!record_exists('group', 'id', $groupid)) {
	throw new GroupNotFoundException(get_string('groupnotfound', 'group', $groupid));
}

$groupname = get_record_sql(
    'SELECT name
    FROM {group}
    WHERE id = ?',
    array($groupid)
)->name;

$membership = user_can_access_group($groupid);

if (!$membership) {
    throw new AccessDeniedException(get_string('cantviewforums', 'interaction.forum'));
}

$admin = (bool)($membership & GROUP_MEMBERSHIP_OWNER);

$breadcrumbs = array(
    array(
        get_config('wwwroot') . 'interaction/forum/index.php?group=' . $groupid,
        get_string('nameplural', 'interaction.forum')
    ),
    array()
);

$forums = get_records_sql_array(
    'SELECT f.id, f.title, f.description, COUNT(t.*), s.forum AS subscribed
    FROM {interaction_instance} f
    LEFT JOIN {interaction_forum_topic} t
    ON t.forum = f.id
    AND t.deleted != 1
    INNER JOIN {interaction_forum_instance_config} c
    ON c.forum = f.id
    AND c.field = \'weight\'
    LEFT JOIN {interaction_forum_subscription_forum} s
    ON s.forum = f.id
    AND s."user" = ?
    WHERE f.group = ?
    AND f.deleted != 1
    GROUP BY 1, 2, 3, 5, c.value
    ORDER BY c.value',
    array($USER->get('id'), $groupid)
);

require_once('pieforms/pieform.php');

$i = 0;
if ($forums) {
    foreach ($forums as $forum) {
        $forum->subscribe = pieform(array(
            'name'     => 'subscribe'.$i++,
            'successcallback' => 'subscribe_submit',
            'elements' => array(
                'submit' => array(
                    'type'  => 'submit',
                    'value' => $forum->subscribed ? get_string('unsubscribe', 'interaction.forum') : get_string('subscribe', 'interaction.forum')
                ),
                'forum' => array(
                    'type' => 'hidden',
                    'value' => $forum->id
                )
            )
        ));
    }
}

function subscribe_submit(Pieform $form, $values) {
    global $USER;
    global $groupid;
    if ($values['submit'] == get_string('subscribe', 'interaction.forum')) {
        insert_record(
            'interaction_forum_subscription_forum',
            (object)array(
                'forum' => $values['forum'],
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
            array($values['forum'], $USER->get('id'))
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_forum',
            'forum', $values['forum'],
            'user', $USER->get('id')
        );
    }
    redirect('/interaction/forum/index.php?group=' . $groupid);
}

$smarty = smarty();
$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('groupid', $groupid);
$smarty->assign('groupname', $groupname);
$smarty->assign('admin', $admin);
$smarty->assign('forums', $forums);
$smarty->display('interaction:forum:index.tpl');

?>
