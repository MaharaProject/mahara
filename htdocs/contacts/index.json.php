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

require(dirname(dirname(__FILE__)) . '/init.php');


$pending  = param_boolean('pending');
$limit    = param_integer('limit', 10);
$offset   = param_integer('offset', 0);
$control  = param_boolean('control');

$prefix = get_config('dbprefix');
$userid = $USER->get('id');


if ($control) {
    // just process the form post stuff.
    $values = array();
    try {
        $values['type']         = param_alpha('type');
        $values['id']           = param_integer('id');
        $values['rejectreason'] = param_variable('rejectreason', null);
        $values['rejectsubmit'] = param_alpha('rejectsubmit', null);
    }
    catch (ParameterException $e) {
        json_reply(true, $e->getMessage());
    }
    $user = get_record('usr', 'id', $values['id']);
    friend_submit(null, $values);
    exit;
}


// normal processing (getting friends list)
if (empty($pending)) {
    $count = count_records_select('usr_friend', 'usr1 = ? OR usr2 = ?', array($userid, $userid));
    $sql = 'SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.staff
            FROM ' . $prefix . 'usr u 
            WHERE u.id IN (
                SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                FROM ' . $prefix . 'usr_friend WHERE (usr1 = ? OR usr2 = ?))';
    if (!$data = get_records_sql_assoc($sql, array($userid, $userid, $userid), $offset, $limit)) {
        $data = array();
    }
    if (!$views = get_views(array_keys($data), null, null)) {
        $views = array();
    }
    $data = array_values($data);
}
else {
    $count = count_records('usr_friend_request' , 'owner', array($userid));
    $sql = 'SELECT u.id, u.firstname, u.lastname, u.preferredname, u.username, fr.reason
            FROM ' . $prefix . 'usr u 
            JOIN ' . $prefix . 'usr_friend_request fr ON fr.requester = u.id
            WHERE fr.owner = ?';
    $data = get_records_sql_array($sql, array($userid), $offset, $limit);
    $views = array();
}

if (empty($data)) {
    $data = array();
}

foreach ($data as $d) {
    $d->name  = display_name($d);
}

json_headers();
print json_encode(array(
    'count'   => $count,
    'limit'   => $limit,
    'offset'  => $offset,
    'data'    => $data,
    'pending' => $pending,
    'views'   => array_map('array_values', $views),
));
exit;


?>
