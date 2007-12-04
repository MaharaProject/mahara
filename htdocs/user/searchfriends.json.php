<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require('searchlib.php');

safe_require('search', 'internal');

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter','Missing parameter \'query\'');
}

$limit  = param_integer('limit', 20);
$offset = param_integer('offset', 0);

$data = search_user($query, $limit, $offset);

if ($data['data']) {
    $userlist = '(' . join(',', array_map(create_function('$u','return $u[\'id\'];'), $data['data'])) . ')';

    $sql = 'SELECT u.id, 0 AS pending,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'friendscontrol\'), \'auth\') AS friendscontrol,
                (SELECT 1 FROM {usr_friend} WHERE ((usr1 = ? AND usr2 = u.id) OR (usr2 = ? AND usr1 = u.id))) AS friend,
                (SELECT 1 FROM {usr_friend_request} fr WHERE fr.requester = ? AND fr.owner = u.id) AS requestedfriendship
                FROM {usr} u
                WHERE u.id IN ' . $userlist . '
            UNION
            SELECT u.id, 1 AS pending,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages,
                NULL AS friendscontrol,
                NULL AS friend,
                NULL AS requestedfriendship
                FROM {usr} u 
                JOIN {usr_friend_request} fr ON fr.requester = u.id
                WHERE fr.owner = ?
                AND u.id IN ' . $userlist;
    $userid = $USER->get('id');
    $otherdata = get_records_sql_assoc($sql, array($userid, $userid, $userid, $userid));

    foreach ($data['data'] as &$record) {
        if (isset($record['introduction'])) {
            $record['introduction'] = format_introduction($record['introduction']);
        }

        foreach ($otherdata as $userid => $otherrecord) {
            if ($record['id'] == $userid) {
                $record = array_merge($record, get_object_vars($otherrecord));
                unset($otherdata[$userid]);
            }
        }
        $record['messages'] = ($record['messages'] == 'allow' || $record['friend'] && $record['messages'] == 'friends' || $USER->get('admin')) ? 1 : 0;
    }
}

json_reply(false, $data);

?>

