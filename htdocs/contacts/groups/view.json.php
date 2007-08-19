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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('group.php');

json_headers();

$id        = param_integer('id');
$pending   = param_boolean('pending', 0); // for memberlist
$submitted = param_boolean('submitted', 0); // for viewlist
$type      = param_alpha('type');

$limit   = param_integer('limit', 10);
$offset  = param_integer('offset', 0);

$count = 0;
$data = array();

if (!$membership = user_can_access_group($id)) {
    group_json_empty();
}
$group = get_record('group', 'id', $id);

$dbnow  = db_format_timestamp(time());

switch ($type) {
    case 'views':
        $where = 'WHERE v.submittedto = ?';
        $values = array($id);
        if (!$submitted) {
            $where .= ' OR (
                     a.group = ? 
                     AND ( v.startdate IS NULL OR v.startdate < ? )
                     AND ( v.stopdate IS NULL OR v.stopdate > ? )
                     AND ( a.startdate IS NULL OR a.startdate < ? )
                     AND ( a.stopdate IS NULL OR a.stopdate > ? )
                 )';
            $values[] = $id;
            $values[] = $dbnow;
            $values[] = $dbnow;
            $values[] = $dbnow;
            $values[] = $dbnow;
        }

        $count = count_records_sql('
            SELECT COUNT(DISTINCT id)
            FROM  {view} v
            LEFT OUTER JOIN {view_access_group} a ON a.view=v.id
            ' . $where,
            $values
        );
                                   
        $data = get_records_sql_array('
            SELECT DISTINCT v.*, u.username, u.firstname, u.lastname, u.preferredname, u.id AS usr 
            FROM {view} v
            LEFT OUTER JOIN {view_access_group} a ON a.view=v.id
            INNER JOIN {usr} u ON v.owner = u.id ' . $where, 
            $values,
            $offset,
            $limit
        );
        if (empty($data)) {
            $data = array();
        }
        foreach ($data as $d) {
            $tmp = clone($d);
            $tmp->id = $tmp->usr;
            $d->ownername = display_name($tmp);
        }
        break;
    case 'members':
        $select = 'SELECT u.*,g.tutor ';
        $sql = '    FROM {usr} u JOIN {group_member} g
                        ON g.member = u.id 
                    WHERE g.group = ?';
        if (empty($pending)) { // default behaviour - actual members
            $count = count_records('group_member', 'group', $id);
            $data = get_records_sql_array($select . $sql, array($id), $offset, $limit);
        }
        else {
            if ($membership == GROUP_MEMBERSHIP_MEMBER) {
                group_json_empty();
            }
            $sql = str_replace('group_member', 'group_member_request', $sql);
            $select = 'SELECT u.*, 1 AS request, c.reason';
            $count = count_records('group_member_request', 'group', $id);
            $data = get_records_sql_array($select . $sql, array($id), $offset, $limit);
        }
        if (empty($data)) {
            $data = array();
        }        
        foreach ($data as $d) {
            $d->displayname = display_name($d);
            if (!empty($d->tutor) && $membership == GROUP_MEMBERSHIP_MEMBER) {
                $d->displayname .= ' (' . get_string('tutor') . ')';
            }
        }
        break;
     case 'membercontrol':
         foreach ($_REQUEST as $k => $v) {
             if (preg_match('/member-(\d+)/', $k, $m)) {
                 $user = $m[1];
                 $changed = false;
                 try {
                     switch ($v) {
                         case 'remove':
                             group_remove_user($id, $user);
                             $changed = true;
                             break;
                         case 'member':
                         case 'tutor':
                             if ($cm = get_record('group_member', 'member', $user, 'group', $id)) {
                                 // already a member so just set the flag
                                 if ($v == 'member' && $cm->tutor == 1) {
                                     $changed = true;
                                     set_field('group_member', 'tutor', 0, 'member', $user, 'group', $id);
                                 }
                                 else if ($v == 'tutor' && $cm->tutor == 0) {
                                     $changed = true;
                                     set_field('group_member', 'tutor', 1, 'member', $user, 'group', $id);  
                                 }
                                 // else not changed.
                             }
                             else {
                                 group_add_member($id, $user);
                                 delete_records('group_member_request', 'member', $user, 'group', $id);
                                 $changed = true;
                                 $v = 'added' . $v; // for the string for notify
                             }
                             break;
                         case 'declinerequest':
                             delete_records('group_member_request', 'member', $user, 'group', $id);
                             break;
                     }
                 }
                 catch (SQLException $e) {
                     json_reply(true, get_string('memberchangefailed'));
                 }
                 require_once('activity.php');
                 activity_occurred('maharamessage', 
                     array('users' => array($user),
                           'subject' => get_string('groupmembershipchangesubject', 'mahara', $group->name), 
                           'message' => get_string('groupmembershipchangemessage' . $v),
                           'url'     => get_config('wwwroot') . 'contacts/groups/view.php?id=' . $id));
                                    
             }
         }
         json_reply(false, get_string('memberchangesuccess'));
         break;
     case 'release':
         $view = param_integer('view');
         require_once(get_config('libroot') . 'view.php');
         $view = new View($view);
         $view->release($id);
         json_reply(false, get_string('viewreleasedsuccess'));
         break;
     case 'watchlist':
         if (record_exists('usr_watchlist_group', 'usr', $USER->get('id'), 'group', $group->id)) {
             delete_records('usr_watchlist_group', 'usr', $USER->get('id'), 'group', $group->id);
             json_reply(false, array('message' => get_string('removedgroupfromwatchlist', 'activity'), 'member' => 0));
         }
         else {
             $gwl = new StdClass;
             $gwl->usr = $USER->get('id');
             $gwl->group = $group->id;
             $gwl->ctime = db_format_timestamp(time());
             insert_record('usr_watchlist_group', $gwl);
             json_reply(false, array('message' => get_string('addedgrouptowatchlist', 'activity'), 'member' => 1));
         }
             
         break;
}

if (!$data) {
    $data = array();
}

print json_encode(array(
    'count'      => $count,
    'data'    =>  $data,
    'limit'   => $limit,
    'offset'  => $offset,
    'id'      => $id,
    'type'    => $type,
    'pending' => $pending,
    'submitted' => $submitted)
);

function group_json_empty() {
    global $limit, $offset, $id, $type, $pending, $submitted;
    print json_encode(array(
        'count'     => 0 ,
        'data'      => array(),
        'limit'     => $limit,
        'offset'    => $offset,
        'id'        => $id,
        'type'      => $type,
        'pending'   => $pending,
        'submitted' => $submitted)
    );
    exit;
}


?>
