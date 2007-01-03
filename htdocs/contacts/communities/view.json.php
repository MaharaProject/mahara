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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('community.php');

$id        = param_integer('id');
$pending   = param_boolean('pending', 0); // for memberlist
$submitted = param_boolean('submitted', 0); // for viewlist
$type      = param_alpha('type');

$limit   = param_integer('limit', 10);
$offset  = param_integer('offset', 0);

$count = 0;
$data = array();

if (!$membership = user_can_access_community($id)) {
    community_json_empty();
}
$prefix = get_config('prefix');
$dbnow  = db_format_timestamp(time());

switch ($type) {
    case 'views':
        $where = 'WHERE a.community = ? 
                     AND ( v.startdate IS NULL OR v.startdate < ? )
                     AND ( v.stopdate IS NULL OR v.stopdate < ? )
                     AND ( a.startdate IS NULL OR a.startdate < ? )
                     AND ( a.stopdate IS NULL OR a.stopdate < ? )';
        $values = array($id, $dbnow, $dbnow, $dbnow, $dbnow);
        if ($submitted) {
            $where .= ' AND v.submittedto = ?';
            $values[] = $id;
        }
            
        $count = count_records_sql('SELECT COUNT(v.id) FROM  ' . $prefix . 'view_access_community a
                                   JOIN ' . $prefix . 'view v ON a.view = v.id ' . $where, $values);
                                   
        $data = get_records_sql_array('SELECT v.*,u.firstname,u.lastname, u.preferredname,u.id AS usr 
                                   FROM ' . $prefix . 'view_access_community a 
                                   JOIN ' . $prefix . 'view v ON a.view = v.id 
                                   JOIN ' . $prefix.'usr u ON v.owner = u.id ' . $where, 
                                      $values, $offset, $limit);
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
        $sql = 'SELECT u.*,c.tutor 
                    FROM ' . $prefix . 'usr u JOIN ' . $prefix . 'community_member c
                        ON c.member = u.id 
                    WHERE c.community = ?';
        if (empty($pending)) { // default behaviour - actual members
            $count = count_records('community_member', 'community', $id);
            $data = get_records_sql_array($sql, array($id), $offset, $limit);
        }
        else {
            if ($membership == COMMUNITY_MEMBERSHIP_MEMBER) {
                community_json_empty();
            }
            $sql = str_replace('community_member', 'community_member_request', $sql);
            $count = count_records('community_member_request', 'community', $id);
            $data = get_records_sql_array($sql, array($id), $offset, $limit);
        }
        if (empty($data)) {
            $data = array();
        }        
        foreach ($data as $d) {
            $d->displayname = display_name($d);
        }
        break;
     case 'membercontrol':
         foreach ($_REQUEST as $k => $v) {
             if (preg_match('/member-(\d+)/', $k, $m)) {
                 $user = $m[1];
                 log_debug("user $user v $v");
                 try {
                     switch ($v) {
                         case 'nonmember':
                             delete_records('usr_watchlist_community', 'usr', $user, 'community', $id);
                             // get all the views in this user's watchlist associated with this community.
                             $views = get_column_sql('SELECT v.id 
                             FROM ' . $prefix . 'view v JOIN ' . $prefix . 'usr_watchlist_view va on va.view = v.id
                             JOIN ' . $prefix . 'view_access_community c ON c.view = v.id');
                             // @todo this is probably a retarded way to do it and should be changed later.
                             foreach ($views as $view) {
                                 db_begin();
                                 delete_record('usr_watchlist_view', 'view', $view, 'usr', $user);
                                 if (can_view_view($view, $user)) {
                                     db_rollback();
                                 }
                                 else {
                                     db_commit();
                                 }
                             }
                             delete_records('community_member', 'member', $user, 'community', $id);
                             break;
                         case 'member':
                             set_field('community_member', 'tutor', 0, 'member', $user, 'community', $id);
                             break;
                         case 'tutor':
                             set_field('community_member', 'tutor', 1, 'member', $user, 'community', $id);
                             break;
                     }
                 }
                 catch (SQLException $e) {
                     json_reply(true, get_string('memberchangefailed'));
                 }
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

function community_json_empty() {
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