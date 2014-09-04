<?php
/**
  * Mahara: Electronic portfolio, weblog, resume builder and social networking
  * Copyright (C) 2011 Copyright Holder
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
  * @subpackage module-multirecipientnotification
  * @author     David Ballhausen
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
  */

defined('INTERNAL') || die();

/**
 * At first step it reads out the ids from all messages which where
 * recieved or send by the user with the id $usr.
 * After this it is searching for the searchterm in the recieved
 * messages with the ids
 *
 * example result:
 * array (
 *    All_data =>
 *        stdClass Object(
 *            [count] => 1
 *            [data] => array(
 *                [id]    => 1
 *                [ctime] => 2013-11-28 20:41:40
 *                [msgtable] => notification_internal_activity
 *                [in_subject] => 0
 *                [in_message] => 0
 *                [in_sender] => 0
 *                [in_recipient] => 1
 *            )
 *        )
 *    Recipient =>
 *        stdClass Object(
 *            [count] => 0
 *            [data] => null
 *        )
 *    Sender =>
 *        stdClass Object(
 *            [count] => 02
 *            [data] => null
 *        )
 *    Subject =>
 *        stdClass Object(
 *            [count] => 0
 *            [data] => null
 *        )
 *    Message =>
 *        stdClass Object(
 *            [count] => 0
 *            [data] => null
 *        )
 * )
 *
 * @param int $searchstring what should be searched for
 * @param int $activitytype the messagetype which is shown
 * @param int $offset offset of the first row to return
 * @param int $limit limits the number of results - for paginations for example
 * @param int $location where is to search (Inbox / Outbox)
 * @param int $usr who is searching
 * @return array
 */
function get_message_search($searchstring, $type, $offset, $limit, $location, $usr = null) {
    global $USER;
    $result = null;
    $return = null;
    $searchstring = '%' . strtolower(trim($searchstring)) . '%';
    $categories = array('in_subject', 'in_message', 'in_sender', 'in_recipient');

    if (null === $usr) {
        $usr = $USER->get('id');
    }

    $typesql = '';
    if ($type != 'all') {
        // Treat as comma-separated list of activity type names
        $types = explode(',', preg_replace('/[^a-z,]+/', '', $type));
        if ($types) {
            $typesql = ' atype.name IN (' . join(',', array_map('db_quote', $types)) . ')';
            if (in_array('adminmessages', $types)) {
                $typesql = '(' . $typesql . ' OR atype.admin = 1)';
            }
            $typesql = ' AND ' . $typesql;
        }
    }

    if ($location=="inbox.php") {
        $query_old_where = "notif.usr = '" . $usr . "'";
        $query_new_where = "relation.usr = '" . $usr . "' AND relation.role = 'recipient'";
    }
    else {
        $query_old_where = "notif.from = '" . $usr . "'";
        $query_new_where = "relation.usr = '" . $usr . "' AND relation.role = 'sender'";
    }

    $query_ids = "(
            SELECT notif.id, 'notification_internal_activity' AS msgtable
            FROM {notification_internal_activity} AS notif
            WHERE " . $query_old_where . "
        )
        UNION
        (
            SELECT notif.id, 'module_multirecipient_notification' AS msgtable
            FROM {module_multirecipient_notification} AS notif
            INNER JOIN {module_multirecipient_userrelation} AS relation
            ON notif.id = relation.notification
            WHERE " . $query_new_where . " AND relation.deleted = '0'
        )";
    $query_old_where = array();
    $query_new_where = array();
    $records_ids = get_records_sql_array($query_ids, array());
    if (!empty($records_ids)) {
        foreach ($records_ids as $record) {
            if ($record->msgtable === 'notification_internal_activity') {
                $query_old_where []= "notif.id = '" . $record->id . "'";
            }
            else {
                $query_new_where []= "notif.id = '" . $record->id . "'";
            }
        }
    }
    $query = "";
    $variables = array();

    if (count($query_old_where) > 0) {
        $query .= "
            SELECT notif.id, notif.ctime, 'notification_internal_activity' AS msgtable,";
        if (is_mysql()) {
            $query .= "
                IF (LOWER(notif.subject) LIKE ?, 1, 0) AS in_subject,
                IF (LOWER(notif.message) LIKE ?, 1, 0) AS in_message,
                IF (LOWER(CONCAT(uuser.username, ' ', uuser.firstname, ' ', uuser.lastname)) LIKE ?, 1, 0) AS in_sender,
                IF (LOWER(CONCAT(fuser.username, ' ', fuser.firstname, ' ', fuser.lastname)) LIKE ?, 1, 0) AS in_recipient";
        }
        else {
            $query .= "
                CASE WHEN LOWER(notif.subject) LIKE ? THEN 1 ELSE 0 END As in_subject,
                CASE WHEN LOWER(notif.message) LIKE ? THEN 1 ELSE 0 END AS in_message,
                CASE WHEN LOWER(uuser.username || ' ' || uuser.firstname || ' ' || uuser.lastname) LIKE ? THEN 1 ELSE 0 END AS in_sender,
                CASE WHEN LOWER(fuser.username || ' ' || fuser.firstname || ' ' || fuser.lastname) LIKE ? THEN 1 ELSE 0 END AS in_recipient
            ";
        }
        // Add variables for the ?-placeholders, it's 4, both times
        for ($i = 0; $i < 4; $i++) {
            $variables []= $searchstring;
        }

        $query .= "
            FROM {notification_internal_activity} AS notif
            INNER JOIN {activity_type} AS atype
            ON notif.type = atype.id";
        if ('outbox.php' === $location) {
            $query .= " AND atype.name != 'newpost'";
        }
        $query .= "
            LEFT JOIN {usr} AS uuser
            ON uuser.id = notif.from
            INNER JOIN {usr} AS fuser
            ON fuser.id = notif.usr
            " . $typesql . "
            WHERE (" . (count($query_old_where) > 0 ? join(' OR ', $query_old_where) : "TRUE") . ")";
    if (is_mysql()) {
        $query .= "
            HAVING (LOWER(in_subject) + LOWER(in_message) + LOWER(in_sender) + LOWER(in_recipient) > 0)";
    }
    else {
        $query .= "
            AND (
                LOWER(notif.subject) LIKE ?
                OR LOWER(notif.message) LIKE ?
                OR LOWER(uuser.username || ' ' || uuser.firstname || ' ' || uuser.lastname) LIKE ?
                OR LOWER(fuser.username || ' ' || fuser.firstname || ' ' || fuser.lastname) LIKE ?
            )";
            // for the postgres-condition we have another 4 placeholders
            for ($i = 0; $i < 4; $i++) {
                $variables []= $searchstring;
            }
    }
        if (count($query_new_where) > 0) {
            $query .= "
                )
                UNION
                (
                ";
        }
    }

    if (count($query_new_where) > 0) {
        $query .= "
            SELECT notif.id, notif.ctime, 'module_multirecipient_notification' AS msgtable,";
        if (is_mysql()) {
            $query .= "
                IF (LOWER(notif.subject) LIKE ?, 1, 0) AS in_subject,
                IF (LOWER(notif.message) LIKE ?, 1, 0) AS in_message,
                IF (relation.role = 'sender' AND LOWER(CONCAT(usr.username, ' ', usr.firstname, ' ', usr.lastname)) LIKE ?, 1, 0) AS in_sender,
                IF (relation.role = 'recipient' AND LOWER(CONCAT(usr.username, ' ', usr.firstname, ' ', usr.lastname)) LIKE ?, 1, 0) AS in_recipient";
        }
        else {
            $query .= "
                CASE WHEN LOWER(notif.subject) LIKE ? THEN 1 ELSE 0 END AS in_subject,
                CASE WHEN LOWER(notif.message) LIKE ? THEN 1 ELSE 0 END AS in_message,
                CASE WHEN relation.role = 'sender' AND LOWER(usr.username || ' ' || usr.firstname || ' ' || usr.lastname) LIKE ? THEN 1 ELSE 0 END AS in_sender,
                CASE WHEN relation.role = 'recipient' AND LOWER(usr.username || ' ' || usr.firstname || ' ' || usr.lastname) LIKE ? THEN 1 ELSE 0 END AS in_recipient";
        }
        // Add variables for the ?-placeholders, it's 4, both times
        for ($i = 0; $i < 4; $i++) {
            $variables []= $searchstring;
        }
        $query .= "
            FROM {module_multirecipient_notification} AS notif
            INNER JOIN {module_multirecipient_userrelation} AS relation
            ON notif.id = relation.notification
            INNER JOIN {usr} as usr
            ON usr.id = relation.usr
            INNER JOIN {activity_type} AS atype
            ON notif.type = atype.id
            " . $typesql . "
            WHERE (" . (count($query_new_where) > 0 ? join(' OR ', $query_new_where) : "TRUE") . ")";
        if (is_mysql()) {
            $query .= "
                HAVING (LOWER(in_subject) + LOWER(in_message) + LOWER(in_sender) + LOWER(in_recipient) > 0)";
        }
        else {
            $query .= "
            AND (
                LOWER(notif.subject) LIKE ?
                OR LOWER(notif.message) LIKE ?
                OR (relation.role = 'sender' AND LOWER(usr.username || ' ' || usr.firstname || ' ' || usr.lastname) LIKE ?)
                OR (relation.role = 'recipient' AND LOWER(usr.username || ' ' || usr.firstname || ' ' || usr.lastname) LIKE ?)
            )";
            // Add variables for the ?-placeholders, it's 4, both times
            for ($i = 0; $i < 4; $i++) {
                $variables []= $searchstring;
            }
        }
    }

    if (strlen($query) > 0) {
        $query = '(' . $query . ')
            ORDER BY ctime DESC';
        $records = get_records_sql_array($query, $variables);
        if (!empty($records)) {
            foreach ($records as $record) {
                foreach ($categories as $category) {
                    if ('1' === $record->$category) {
                        $result[$category][$record->msgtable . '+' . $record->id] = $record;
                    }
                }
                $result['all'][$record->msgtable . '+' . $record->id] = $record;
            }
        }
    }
    // All_data
    if (!empty($result['all'])) {
        $return['All_data']['count'] = count($result['all']);
        $return['All_data']['data'] = array_slice($result['all'], $offset, $limit);
    }
    else {
        $return['All_data']['count'] = 0;
        $return['All_data']['data'] = null;
    }

    // recipient
    if (!empty($result['in_recipient'])) {
        $return['Recipient']['count'] = count($result['in_recipient']);
        $return['Recipient']['data'] = array_slice($result['in_recipient'], $offset, $limit);
    }
    else {
        $return['Recipient']['count'] = 0;
        $return['Recipient']['data'] = null;
    }

    // Sender
    if (!empty($result['in_sender'])) {
        $return['Sender']['count'] = count($result['in_sender']);
        $return['Sender']['data'] = array_slice($result['in_sender'], $offset, $limit);
    }
    else {
        $return['Sender']['count'] = 0;
        $return['Sender']['data'] = null;
    }

    // Subject
    if (!empty($result['in_subject'])) {
        $return['Subject']['count'] = count($result['in_subject']);
        $return['Subject']['data'] = array_slice($result['in_subject'], $offset, $limit);
    }
    else {
        $return['Subject']['count'] = 0;
        $return['Subject']['data'] = null;
    }

    // Message
    if (!empty($result['in_message'])) {
        $return['Message']['count'] = count($result['in_message']);
        $return['Message']['data'] = array_slice($result['in_message'], $offset, $limit);
    }
    else {
        $return['Message']['count'] = 0;
        $return['Message']['data'] = null;
    }
    return $return;
}