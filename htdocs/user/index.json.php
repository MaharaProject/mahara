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


$filter   = param_integer('filter');
$limit    = param_integer('limit', 10);
$offset   = param_integer('offset', 0);
$control  = param_boolean('control');

$userid = $USER->get('id');


if ($control) {
    // just process the form post stuff.
    $values = array();
    try {
        $values['type']         = param_alpha('type');
        $values['id']           = param_integer('id');
        $values['rejectreason'] = param_variable('rejectreason', null);
        $values['rejectsubmit'] = param_alpha('rejectsubmit', null);
        $values['reason']       = param_variable('reason', null);
    }
    catch (ParameterException $e) {
        json_reply(true, $e->getMessage());
    }
    $user = get_record('usr', 'id', $values['id']);

    if ($values['type'] == 'message') {
        try {
            send_user_message($user, param_variable('message'));
            json_reply(false, get_string('messagesent'));
        }
        catch (AccessDeniedException $e) {
            json_reply(true, get_string('messagenotsent'));
        }
        exit;
    }

    if ($values['type'] == 'request') {
        if ($values['id'] == $USER->get('id')) {
            json_reply(true, get_string('cannotrequestfriendshipwithself'));
        }
        if (get_account_preference($values['id'], 'friendscontrol') == 'nobody') {
            json_reply(true, get_string('userdoesntwantfriends'));
        }
    }

    friend_submit(null, $values);
    exit;
}


// normal processing (getting friends list)
$data = array();
if ($filter == 1) {
    $count = count_records_select('usr_friend', 'usr1 = ? OR usr2 = ?', array($userid, $userid));
    $sql = 'SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname,
            (SELECT a.title FROM {artefact} a WHERE a.owner = u.id AND a.artefacttype = \'introduction\') AS introduction,
            COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages
            FROM {usr} u 
            WHERE u.id IN (
                SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                FROM {usr_friend} WHERE (usr1 = ? OR usr2 = ?))
            ORDER BY u.id';
    $data = get_records_sql_assoc($sql, array($userid, $userid, $userid), $offset, $limit);
    if (!$data || !$views = get_views(array_keys($data), null, null)) {
        $views = array();
    }
}
else if ($filter == 2) {
    $count = count_records('usr_friend_request', 'owner', array($userid));
    $sql = 'SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, fr.reason, 1 AS pending,
            (SELECT a.title FROM {artefact} a WHERE a.owner = u.id AND a.artefacttype = \'introduction\') AS introduction,
            COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages
            FROM {usr} u 
            JOIN {usr_friend_request} fr ON fr.requester = u.id
            WHERE fr.owner = ?
            ORDER BY u.id';
    $data = get_records_sql_array($sql, array($userid), $offset, $limit);
    $views = array();
}
else {
    $count = count_records_select('usr_friend', 'usr1 = ? OR usr2 = ?', array($userid, $userid))
           + count_records('usr_friend_request', 'owner', array($userid));
    $sql = 'SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, NULL as reason, 0 AS pending,
                (SELECT a.title FROM {artefact} a WHERE a.owner = u.id AND a.artefacttype = \'introduction\') AS introduction,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages
                FROM {usr} u
                WHERE u.id IN (
                    SELECT (CASE WHEN usr1 = ? THEN usr2 ELSE usr1 END) AS userid 
                    FROM {usr_friend} WHERE (usr1 = ? OR usr2 = ?))
            UNION
            SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, fr.reason, 1 AS pending,
                (SELECT a.title FROM {artefact} a WHERE a.owner = u.id AND a.artefacttype = \'introduction\') AS introduction,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages
                FROM {usr} u 
                JOIN {usr_friend_request} fr ON fr.requester = u.id
                WHERE fr.owner = ?
            ORDER BY pending DESC, id';
    $data = get_records_sql_assoc($sql, array($userid, $userid, $userid, $userid));
    if (!$data || !$views = get_views(array_keys($data), null, null)) {
        $views = array();
    }
}

if ($data) {
    $data = array_values($data);
    foreach ($data as $d) {
        $d->name  = display_name($d);
        if (isset($d->introduction)) {
            $d->introduction = format_introduction($d->introduction);
        }
        $d->messages = ($d->messages == 'allow' || is_friend($userid, $d->id) && $d->messages == 'friends' || $USER->get('admin')) ? 1 : 0;
    }
}


$viewcount = array_map('count', $views);
// since php is so special and inconsistent, we can't use array_map for this because it breaks the top level indexes.
$cleanviews = array();
foreach ($views as $userindex => $viewarray) {
    $cleanviews[$userindex] = array_slice($viewarray, 0, 5);

    // Don't reveal any more about the view than necessary
    foreach ($cleanviews as $userviews) {
        foreach ($userviews as &$view) {
            foreach (array_keys(get_object_vars($view)) as $key) {
                if ($key != 'id' && $key != 'title') {
                    unset($view->$key);
                }
            }
        }
    }

}

json_reply(false, array(
    'count'   => $count,
    'limit'   => $limit,
    'offset'  => $offset,
    'data'    => $data,
    'filter'  => $filter,
    'views'   => $cleanviews,
    'numviews' => $viewcount,
));

?>
