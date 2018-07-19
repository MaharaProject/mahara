<?php
/**
 *
 * @package    mahara
 * @subpackage module-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once((dirname(dirname(__FILE__)) . '/notification/ActivityTypeMultirecipientmessage.php'));

/**
 * retrieves an array with messages that form one threat in that each message
 * was a reply to the next one in line, everything starting with $replyto as
 * the id of the first message. <br/>
 * returns an empty array, if no message can be found - e.g. if all messages
 * have been deleted by user $usr<br/>
 * Only messages are returned that are not yet deleted. If a message in a thread
 * was deleted (also if only by this user), the thread is no longer followed from
 * there on
 *
 * example result:                          <br/>
 * array (                                  <br/>
 *   0 =>                                   <br/>
 *   stdClass::__set_state(array(           <br/>
 *      'id' => '23',                       <br/>
 *      'type' => '2',                      <br/>
 *      'ctime' => '2013-09-12 10:12:26',   <br/>
 *      'subject' => 'some subject',        <br/>
 *      'message' => 'some body',           <br/>
 *      'parent' => NULL,                   <br/>
 *      'userids' =>                        <br/>
 *     array (                              <br/>
 *       0 => '1',                          <br/>
 *       1 => '2',                          <br/>
 *     ),                                   <br/>
 *      'fromid' => '1',                    <br/>
 *   ))                                     <br/>
 * )
 *
 * @param int $msgid the id of the message
 * @param int $usr
 * @return array
 */
function get_message_thread_mr($msgid, $usr = null) {
    global $USER;

    if (null === $usr) {
        $usr = $USER->get('id');
    }

    $message = get_message_mr($usr, $msgid);

    if ((null !== $message) && !empty($message->parent)) {
        $subthread  = get_message_thread_mr($message->parent);
        if ((null !== $subthread) && (sizeof($subthread) > 0)) {
            return array_merge($subthread, array($message));
        }
    }

    if (null === $message) {
        return null;
    }

    return array($message);
}

/**
 *
 * sends a message with text $message and subject $subject to all users referenced
 * in $userids. The message is connected to $parentid and $fromid will be related
 * as the id of the sending user. If $fromid=null is not provided, logged in user
 * is assumed.
 *
 * @param array $usrids the ids of the users, may contain the recipient as well
 * @param string $subject the subject of the message
 * @param string $message the text of the message
 * @param int $parentid the id of the message, this one replies to
 * @param int $fromid the id of the sending user
 * @return int the message id
 */
function send_user_message_mr(array $userids, $subject, $message, $parentid=null, $fromid=null) {
    global $USER;
    $fromuser = $USER;

    if (null === $fromid) {
        $fromid = $USER->get('id');
    }
    else {
        $fromuser = new User();
        $fromuser->find_by_id($fromid);
    }

    if (!in_array($fromuser->id, $userids)) {
        $userids[] = $fromuser->id;
    }

    if (empty($userids)) {
        return true;
    }

    // Check permissions for all recipients
    foreach ($userids as $userid) {
        if (!can_send_message($USER, $userid) && ($userid !== $USER->get('id'))) {
            throw new AccessDeniedException(get_string('cantmessageuser', 'group'));
        }
    }

    $data = new stdClass();
    $data->usrids = $userids;
    $data->subject = $subject;
    $data->message = $message;
    $data->parent = $parentid;
    $data->userfrom = $fromid;

    $activity = new ActivityTypeMultirecipientmessage($data);
    return $activity->notify_users($userids);
}

/**
 *
 * marks all messages in $msgids as read for user $usr (if null is given, logged
 * in user is assumed). If $read=false is given, the message is marked as unread
 *
 * @param array $msgids the ids of the messages to mark as read
 * @param int $usr the user who marks the messages
 * @param bool $read specify if the messages are marked as read or unread
 */
function mark_as_read_mr(array $msgids, $usr = null, $read = true) {
    global $USER;

    if (null === $usr) {
        $usr = $USER->get('id');
    }

    db_begin();
    foreach ($msgids as $msgid) {
        $change = new stdClass();
        $change->read = $read ? '1' : '0';

        $where = array(
            'notification' => $msgid,
            'usr' => $usr,
        );
        update_record('module_multirecipient_userrelation', $change, $where);
    }
    db_commit();
}

/**
 *
 * deletes the messages in $msgids for user $usr. If $usr is not provided,
 * logged in user is assumed.
 *
 * This call should be wrapped in a transaction by the calling function.
 *
 * @global USER $USER the global User-object in case no user-id was provided via $usr
 * @param array $msgids the ids of the messages to retrieve
 * @param int $user the id of the user - to check if he has deleted the message
 */
function delete_messages_mr(array $msgids, $usr = null) {
    global $USER;
    if (null === $usr) {
        $usr = $USER->get('id');
    }
    if (count($msgids) <= 0) {
        return;
    }

    $query = '
        UPDATE {module_multirecipient_userrelation}
        SET deleted = \'1\',
            "read" = \'1\'
        WHERE usr = ?
        AND notification in (' . join(',', $msgids) . ')';
    execute_sql($query, array($usr));

    $query = '
        SELECT DISTINCT a.id
        FROM {module_multirecipient_notification} AS a
        LEFT JOIN {module_multirecipient_userrelation} AS ur
            ON a.id = ur.notification AND ur.deleted = \'0\'
        WHERE a.id IN (' . join(',', array_map('db_quote', $msgids)) . ')
        AND ur.id IS NULL';
    $deleteidrecords = get_records_sql_array($query, array());

    $msgids = array();
    if (is_array($deleteidrecords)) {
        foreach ($deleteidrecords as $record) {
            $msgids[] = $record->id;
        }
    }
    if (count($msgids) > 0) {
        $where = '(' . join(',', array_map('db_quote', $msgids)) . ')';
        // First delete references to deleted notifications
        $updatequery = '
            UPDATE {module_multirecipient_notification} notification
            SET parent = NULL
            WHERE notification.parent IN (' . implode(',', array_map('db_quote', $msgids)) . ')';
        execute_sql($updatequery, array());
        delete_records_select('module_multirecipient_userrelation', 'notification IN ' . $where, array());
        delete_records_select('module_multirecipient_notification', 'id IN ' . $where, array());
    }
}

function get_message_ids_mr ($usr = null, $role = 'recipient', $type = null,
        $sortby = null, $limit = 20, $offset = 0) {
    if (null === $type) {
        $at = activity_locate_typerecord('usermessage');
        $type = $at->id;
    }
    else if (!is_int($type)) {
        $at = activity_locate_typerecord($type);
        $type = $at->id;
    }

    $messageids = array();
    if (null === $usr) {
        global $USER;
        $usr = $USER->get('id');
    }

    if (null === $sortby) {
        $sortby = 'ctime';
    }
    $values = array($usr, $role, $type, $sortby);

    $query = '
        SELECT msg.id
        FROM {module_multirecipient_notification} as msg
        INNER JOIN {module_multirecipient_userrelation} as rel
            ON msg.id = rel.notification
            AND rel.usr = ?
            AND rel.role = ?
        WHERE msg.type = ?
        ORDER BY ?';

    $result = get_records_sql_array($query, $values, $offset, $limit);

    if (is_array($result)) {
        foreach ($result as $res) {
            $messageids[] = $res->id;
        }
    }

    return $messageids;
}

/**
 *
 * retrieves the messages for user $usr (or logged in user as default). To
 * view the messages the user has sent, use $role='sender'. Type should always
 * be 2 (usermessage). Limit and offset can be used for paginated views
 * <br/>
 * example result:                          <br/>
 * array (                                  <br/>
 *   0 =>                                   <br/>
 *   stdClass::__set_state(array(           <br/>
 *      'id' => '23',                       <br/>
 *      'type' => '2',                      <br/>
 *      'ctime' => '2013-09-12 10:12:26',   <br/>
 *      'subject' => 'some subject',        <br/>
 *      'message' => 'some body',           <br/>
 *      'parent' => NULL,                   <br/>
 *      'userids' =>                        <br/>
 *     array (                              <br/>
 *       0 => '1',                          <br/>
 *       1 => '2',                          <br/>
 *     ),                                   <br/>
 *      'fromid' => '1',                    <br/>
 *   ))                                     <br/>
 * )                                        <br/>
 *
 * @param int $usr the id of the user
 * @param string $role the role can be <b>sender</b> or <b>recipient</b>
 * @param int $type so far it's always <b>2</b> for usermessage
 * @param string $sortby should be <b>null</b> or <b>ctime ASC</b> or <b>ctime DESC</b>
 * @param int $limit limits the number of results - for paginations for example
 * @param int $offset offset of the first row to return
 * @return array
 */
function get_messages_mr ($usr = null, $role = 'recipient', $type = null,
        $sortby = null, $limit = 20, $offset = 0) {

    $messageids = get_message_ids_mr($usr, $role, $type, $sortby, $limit, $offset);
    if (null === $usr) {
        global $USER;
        $usr = $USER->get('id');
    }

    foreach ($messageids as $msgid) {
        $msg = get_message_mr($usr, $msgid);
        if (null !== $msg) {
            $messages[] = $msg;
        }
    }

    return $messages;
}

/**
 *
 * loads the message with id $msgid with users from the database. The user-id
 * is provided to assure that the message hasn't been deleted
 *
 * example result:
 *   stdClass::__set_state(array(
 *      'id' => '23',
 *      'type' => '2',
 *      'ctime' => '2013-09-12 10:12:26',
 *      'subject' => 'some subject',
 *      'message' => 'some body',
 *      'parent' => NULL,
 *      'userids' =>
 *     array (
 *       0 => '1',
 *       1 => '2',
 *     ),
 *      'fromid' => '1',
 *   ))
 *
 * @param int $usr the id of the viewing user
 * @param int $msgid the id of the notification
 * @return stdClass
 */
function get_message_mr($usr, $msgid) {
    $query = "
        SELECT a.*, at.name AS type
        FROM {module_multirecipient_notification} AS a
        INNER JOIN {activity_type} AS at ON a.type = at.id
        WHERE a.id = ?";
    $message = get_record_sql($query, array($msgid));
    if (false === $message) {
        return null;
    }
    $userrelations = get_records_assoc('module_multirecipient_userrelation', 'notification', $message->id, 'role ASC');
    if (false === $userrelations) {
        return null;
    }
    $message->userids = array();
    $message->fromid = -1;
    foreach ($userrelations as $userrel) {
        if ($userrel->usr == $usr) {
            if ('1' === $userrel->deleted) {
                return null;
            }
            else if ('1' === $userrel->read) {
                $message->read = 1;
            }
            else {
                $message->read = 0;
            }
        }
        if ('sender' === $userrel->role) {
            $message->fromid = $userrel->usr;
        }
        else {
            if (isset($userrel->usr)) {
                $message->userids[] = $userrel->usr;
            }
            else {
                $message->userids[] = 0;
            }
        }
    }
    if (((int) $usr !== (int) $message->fromid) && !in_array($usr, $message->userids)) {
        return null;
    }
    $message->message = format_whitespace($message->message);
    return $message;
}

/**
 *
 * loads the messages with ids $msgids with users from the database. The user-id
 * is provided to assure that the message hasn't been deleted. The msg-ids serve
 * as index in the return-array
 *
 * example result:
 * array(
 *   '23' => stdClass::__set_state(array(
 *      'id' => '23',
 *      'type' => '2',
 *      'ctime' => '2013-09-12 10:12:26',
 *      'subject' => 'some subject',
 *      'message' => 'some body',
 *      'parent' => NULL,
 *      'userids' =>
 *     array (
 *       0 => '1',
 *       1 => '2',
 *     ),
 *      'fromid' => '1',
 *   ))
 * )
 *
 * @param int $usr
 * @param array $msgids
 * @return stdClass
 */
function get_messages_by_ids_mr($usr, array $msgids) {

    $query = "
        SELECT a.*, at.name AS {type}
        FROM {module_multirecipient_notification} AS a
        INNER JOIN {activity_type} AS at ON a.type = at.id
        WHERE a.id IN (" . join(',', array_map('db_quote', $msgids)) . ")";
    $messages = get_records_sql_array($query, array());
    if (false === $messages) {
        return array();
    }
    foreach ($messages as $msg) {
        $msg->userids = array();
        $return [$msg->id]= $msg;
    }
    $userrelations = get_records_sql_array('SELECT *
        FROM {module_multirecipient_userrelation}
        WHERE notification IN (' . join(',', array_map('db_quote', array_keys($return))) . ')
        ORDER BY role ASC', array());
    if (false === $userrelations) {
        return null;
    }

    foreach ($userrelations as $userrel) {
        $msgid = $userrel->notification;
        if (!array_key_exists($msgid, $return)) {
            continue;
        }
        if (($userrel->usr == $usr) && ('1' === $userrel->deleted)) {#
            $return = array_diff_assoc($return, array($msgid => $return[$msgid]));
            continue;
        }
        if ('sender' === $userrel->role) {
            $return[$msgid]->fromid = $userrel->usr;
        }
        else {
            $return[$msgid]->userids[] = $userrel->usr;
        }
    }
    foreach (array_keys($return) as $msgid) {
        if (($usr !== $return[$msgid]->fromid) && !in_array($usr, $return[$msgid]->userids)) {
            $return = array_diff_assoc($return, array($msgid => $return[$msgid]));
        }
    }
    return $return;
}
