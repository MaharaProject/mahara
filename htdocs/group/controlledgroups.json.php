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
 * @subpackage core
 * @author     Ruslan Kabalin <ruslan.kabalin@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 *
 */


define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

$userid = param_integer('userid');
$groupdata = array();
$initialgroups = array('controlled' => array(), 'invite' => array());

  /* Get (a) controlled membership groups,
     (b) request membership groups where the displayed user has requested membership,
     where the logged in user either:
     1. is a group admin, or;
     2. has a role in the list of roles who are allowed to assess submitted views for the given grouptype
     @return array  A data structure containing results looking like ...
     *         $results = array(
     *                   array(
     *                       g*            => mixed, //all group data
     *                       role          => string, // logged-in user's role
     *                       member        => bool, // destination user's group membership
     *                       memberrole    => string, // destination user's role in the group
     *                   ),
     *                   array(...),
     *           );
*/
$controlled = get_records_sql_array("SELECT g.*, gm.role,
          (SELECT 1 FROM {group_member} gm1 WHERE gm1.member = ? AND gm1.group = g.id) AS member,
          (SELECT gm1.role FROM {group_member} gm1 WHERE gm1.member = ? AND gm1.group = g.id) AS memberrole
          FROM {group} g
          JOIN {group_member} gm ON (gm.group = g.id)
          JOIN {grouptype_roles} gtr ON (gtr.grouptype = g.grouptype AND gtr.role = gm.role)
          LEFT JOIN {group_member_request} gmr ON (gmr.member = ? AND gmr.group = g.id)
          WHERE gm.member = ?
          AND (g.jointype = 'controlled' OR (g.jointype = 'request' AND gmr.member = ?))
          AND (gm.role = 'admin' OR gtr.see_submitted_views = 1)
          AND g.deleted = 0", array($userid, $userid, $userid, $USER->get('id'), $userid));

if ($controlled) {
    foreach ($controlled as &$g) {
        if ($g->member) {
            $g->checked = true;
            $initialgroups['controlled'][] = $g->id;
            if ($g->role != 'admin') {
                $g->disabled = true;
            }
        }
    }
    $groupdata['controlled'] = $controlled;
}

  /* Get 'Invite olny' groups where the logged in user is a group admin.
     @return array  A data structure containing results looking like ...
     *         $results = array(
     *                   array(
     *                       g*            => mixed, //all group data
     *                       role          => string, // logged-in user's role
     *                       member        => bool, // destination user's group membership
     *                       invited       => bool, // destination user's invite status
     *                   ),
     *                   array(...),
     *           );
*/
$invite = get_records_sql_array("SELECT g.*, gm.role,
        (SELECT 1 FROM {group_member_invite} gi WHERE gi.member = ? AND gi.group = g.id) AS invited,
        (SELECT 1 FROM {group_member} gm1 WHERE gm1.member = ? AND gm1.group = g.id) AS member
        FROM {group} g
        JOIN {group_member} gm ON (gm.group = g.id)
        WHERE gm.member = ?
        AND g.jointype = 'invite'
        AND gm.role = 'admin'
        AND g.deleted = 0", array($userid, $userid, $USER->get('id')));

if ($invite) {
    foreach ($invite as &$g) {
        if ($g->member || $g->invited) {
            $g->checked = true;
            $g->disabled = true;
            $initialgroups['invite'][] = $g->id;
        }
    }
    $groupdata['invite'] = $invite;
}

$smarty = smarty_core();
$smarty->assign('data', $groupdata);
$smarty->assign('userid', $userid);

$data['data'] = array(
    'html' => $smarty->fetch('group/editgroupmembership.tpl'),
    'initialgroups' => $initialgroups,
);
$data['error'] = false;
$data['message'] = null;
json_reply(false, $data);
