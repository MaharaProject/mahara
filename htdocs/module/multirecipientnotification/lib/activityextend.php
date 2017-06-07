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


require_once(dirname(__FILE__) . '/multirecipientnotification.php');
require_once(dirname(__FILE__) . '/multirecipientnotificationsearch.php');

/**
 * returns an object containing a list of ids of notifications the user has
 * received and the tables where to find the dataelements. Also returns the
 * count of the found notifications
 *
 * @global User $USER
 * @param string $type
 * @param int $limit
 * @param int $offset
 * @return \stdClass
 */
function activitylistin($type='all', $limit=10, $offset=0) {
    global $USER;
    $result = new stdClass();
    $userid = $USER->get('id');
    $searchtext = param_variable('search', null);
    $searcharea = param_variable('searcharea', 'All_data');

    if (isset($searchtext) AND $searchtext !== null) {
        $type = param_variable('type', 'all');
        $searchresults = get_message_search($searchtext, $type, $offset, $limit, "inbox.php", $userid);
        $result->msgidrecords = $searchresults[$searcharea]['data'];
        $result->count = $searchresults[$searcharea]['count'];
        $result->search = true;
    }
    else {
        $typesql = '';
        if ($type != 'all') {
            // Treat as comma-separated list of activity type names
            $types = explode(',', preg_replace('/[^a-z,]+/', '', $type));
            if ($types) {
                $typesql = ' at.name IN (' . join(',', array_map('db_quote', $types)) . ')';
                if (in_array('adminmessages', $types)) {
                    $typesql = '(' . $typesql . ' OR at.admin = 1)';
                }
                $typesql = ' AND ' . $typesql;
            }
        }

        $notificationtargetcolumn = 'usr';
        $notificationtargetrole = 'recipient';

        if (is_postgres()) {
            $readsqlstr = 'CAST(b.read AS INT)';
        }
        else {
            $readsqlstr = 'b.read';
        }

        $msgidquery = "
            (
            SELECT a.id, a.read, a.ctime, 'notification_internal_activity' AS msgtable, subject
            FROM {notification_internal_activity} AS a
            INNER JOIN {activity_type} AS at ON a.type = at.id
            WHERE a." . $notificationtargetcolumn . " = ?
            " . $typesql . "
            )
            UNION
            (
            SELECT a.id, " . $readsqlstr . ", a.ctime, 'module_multirecipient_notification' AS msgtable, subject
            FROM {module_multirecipient_notification} AS a
            INNER JOIN {module_multirecipient_userrelation} AS b
                ON a.id = b.notification
            INNER JOIN {activity_type} AS at ON a.type = at.id
            WHERE b.usr = ?
            AND b.deleted = '0'
            AND b.role = '" . $notificationtargetrole . "'
            " . $typesql . "
            )";

        $countquery = 'SELECT COUNT(*) FROM (' . $msgidquery . ') AS dummytable';
        $result->count = count_records_sql($countquery, array($userid, $userid));

        $msgidquery .= "
        ORDER BY \"read\" ASC, ctime DESC, subject ASC";
        $result->msgidrecords = get_records_sql_array($msgidquery, array($userid, $userid), $offset, $limit);
        $result->search = false;
    }

    if (!is_array($result->msgidrecords)) {
        $result->msgidrecords = array();
        $result->count = 0;
    }
    return $result;
}

/**
 * creates a result-array with the number, limit, offset and notification-type(s)
 * of the returned htmlrepresentation of the notifications, as well as the html
 * representation itself. The return array has the following format:
 *
 * array (
 *   'count' => '17',
 *   'limit' => 10,
 *   'offset' => 0,
 *   'type' => 'all',
 *   'html' => '//html ...
 *   'pagination' => '// html
 *   'pagination_js' => '// javascript
 * )
 *
 * @global User $USER
 * @param type $type
 * @param type $limit
 * @param type $offset
 * @return array
 */
function activitylistin_html($type='all', $limit=10, $offset=0) {
    global $USER;
    $userid = $USER->get('id');

    $activitylist = activitylistin($type, $limit, $offset);

    $pagination = build_pagination(array(
        'id'         => 'activitylist_pagination',
        'url'        => get_config('wwwroot') . 'module/multirecipientnotification/inbox.php?type=' . hsc($type),
        'jsonscript' => 'module/multirecipientnotification/indexin.json.php',
        'datatable'  => 'activitylist',
        'count'      => $activitylist->count,
        'limit'      => $limit,
        'setlimit'   => true,
        'offset'     => $offset,
        'jumplinks'  =>  6,
        'numbersincludeprevnext' => 2,
    ));

    $result = array(
        'count'         => $activitylist->count,
        'limit'         => $limit,
        'offset'        => $offset,
        'type'          => $type,
        'html'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($activitylist->count < 1) {
        return $result;
    }

    $records = array();
    foreach ($activitylist->msgidrecords as $msgidrecord) {
        // old messages without plugin
        if ($msgidrecord->msgtable == 'notification_internal_activity') {
            $recordsarray = get_records_sql_array("SELECT a.*, at.name AS type, at.plugintype, at.pluginname
                                      FROM {notification_internal_activity} a
                                      INNER JOIN {activity_type} at ON a.type = at.id
                                      WHERE a.id = ?", array($msgidrecord->id));
            if (1 !== count($recordsarray)) {
                log_warn('inconsistent message-id in notification_internal_activity, id: ' . $msgidrecord->id);
                continue;
            }
            $record = $recordsarray[0];

            // in the inbox, the logged in user should be the recipient of all
            // notifications
            if (!isset($record->usr)) {
                $record->usr = $USER->get('id');
            }

            // read out receiver name
            $record->tousr = array(
                array(
                    'display' => display_name($record->usr),
                    'link' => null,
                ),
            );
            $record->canreply = false;
            $record->canreplyall = false;
            $record->startnewthread = true;

            // read out sender name
            $record->fromusrlink = false;
            if ('usermessage' === $record->type) {
                $record->url = false;
                $record->urltext = false;
            }
            if (isset($record->from)) {
                $record->fromusr = $record->from;
                $fromuser = get_user($record->fromusr);
                $record->fromusrlink = false;
                if ($fromuser->deleted === '0') {
                    $record->fromusrlink = profile_url($record->fromusr);
                    if ('usermessage' === $record->type) {
                        $record->canreply = true;
                    }
                }
            }
            else {
              $record->fromusr = 0;
            }

            $record->date = format_date(strtotime($record->ctime), 'strfdaymonthyearshort');
            $section = empty($record->plugintype) ? 'activity' : "{$record->plugintype}.{$record->pluginname}";
            $record->strtype = get_string('type' . $record->type, $section);
            $record->message = format_notification_whitespace($record->message);
            // used to identify notification as internal for json-calls
            $record->table = 'notification_internal_activity';
            $record->url = preg_replace('|^' . get_config('wwwroot') . '|', '', $record->url); // Remove the wwwroot form url as it will ba added again in template
            $records[] = $record;
        // messages from plugin
        }
        else if ($msgidrecord->msgtable === 'module_multirecipient_notification') {
            $record = get_message_mr($userid, $msgidrecord->id);
            if (null === $record) {
                continue;
            }
            $record->strtype = $record->type;
            $record->date = format_date(strtotime($record->ctime), 'strfdaymonthyearshort');
            $record->tousr = array();

            // We fill $record->tousr with an array per userentry, that holds the
            // display name of the user and the link to the users profile, if
            // applicable - we don't link to the logged in user himself or to
            // deleted users. Those will be summed up in a single entry at the
            // end of the list
            $deletedcount = 0;
            for ($i = 0; $i < count($record->userids); $i++) {
                if (get_user($record->userids[$i])->deleted) {
                    $deletedcount ++;
                }
                else {
                    $tousrarray = array(
                        'username' => display_username(get_user_for_display($record->userids[$i])),
                        'display' => display_name($record->userids[$i]),
                        'link' => profile_url($record->userids[$i]),
                    );
                    if ($record->userids[$i] === $USER->get('id')) {
                        $tousrarray['link'] = null;
                    }
                    $record->tousr[] = $tousrarray;
                }
            }
            if ($deletedcount > 0) {
                $record->tousr[] = array(
                    'username' => $deletedcount . ' ' . get_string('deleteduser', 'module.multirecipientnotification'),
                    'display' => $deletedcount . ' ' . get_string('deleteduser', 'module.multirecipientnotification'),
                    'link' => null,
                );
            }
            // add link to reply to all users in the conversation, if there are
            // more than one of them
            $record->canreply = false;
            $record->startnewthread = false;
            if (count($record->userids) > 1) {
                $record->canreplyall = true;
            }
            else {
                $record->canreplyall = false;
            }
            // preformat from user, add link to reply to sender only
            if (isset($record->fromid)) {
                $record->fromusr = $record->fromid;
                $fromuser = get_user($record->fromid);
                $record->fromusrlink = false;
                if ($fromuser->deleted === '0') {
                    $record->canreply = true;
                    $record->fromusrlink = profile_url($record->fromid);
                }
            }
            else {
              $record->fromusr = 0;
            }
            $record->message = format_notification_whitespace($record->message);
            // used to identify notification as from this plugin for json-calls
            $record->table = 'module_multirecipient_notification';
            $records[] = $record;
        }
    }

    $smarty = smarty_core();
    $smarty->assign('data', $records);
    $smarty->assign('USER', $USER);
    $smarty->assign('maxnamestrlength', PluginModuleMultirecipientnotification::MAX_USERNAME_IN_LIST_LENGTH);
    $result['html'] = $smarty->fetch('module:multirecipientnotification:activitylistin.tpl');

    return $result;
}

/**
 * return an object with a list of records to feed the inbox-blocktype template.
 * The object has the following format:
 *
 * stdClass::__set_state(array(
 *   'records' =>
 *   array (
 *     0 =>
 *     stdClass::__set_state(array(
 * ...
 *
 *   ),
 *    'count' => '17',
 * ))
 *
 *
 * @param type $type
 * @param type $limit
 * @param type $offset
 * @return array
 */
function activityblocklistin($type='all', $limit=10, $offset=0) {
    global $USER;
    $userid = $USER->get('id');
    $return = new stdClass();
    $return->records = array();

    $activitylist = activitylistin($type, $limit, $offset);
    $return->count = $activitylist->count;

    foreach ($activitylist->msgidrecords as $msgidrecord) {
        // old messages without plugin
        if ($msgidrecord->msgtable == 'notification_internal_activity') {
            $recordsarray = get_records_sql_array("SELECT a.*, at.name AS type, at.plugintype, at.pluginname
                                      FROM {notification_internal_activity} a
                                      INNER JOIN {activity_type} at ON a.type = at.id
                                      WHERE a.id = ?", array($msgidrecord->id));
            if (1 !== count($recordsarray)) {
                continue;
            }
            $record = $recordsarray[0];
            $record->canreply = false;
            $record->canreplyall = false;
            $record->startnewthread = true;

            // read out sender name
            if ('usermessage' === $record->type) {
                $record->url = false;
                $record->urltext = false;
            }
            if (isset($record->from)) {
                $record->fromusr = $record->from;
                $fromuser = get_user($record->fromusr);
                if ($fromuser->deleted === '0') {
                    if ('usermessage' === $record->type) {
                        $record->canreply = true;
                    }
                }
            }
            else {
              $record->fromusr = 0;
            }
            $record->return = null;

            $section = empty($record->plugintype) ? 'activity' : "{$record->plugintype}.{$record->pluginname}";
            $record->strtype = get_string('type' . $record->type, $section);
            $record->message = format_notification_whitespace($record->message);
            // used to identify notification as internal for json-calls
            $record->table = 'notification_internal_activity';
            $return->records[] = $record;
        // messages from plugin
        }
        else if ($msgidrecord->msgtable === 'module_multirecipient_notification') {
            $record = get_message_mr($userid, $msgidrecord->id);
            if (null === $record) {
                continue;
            }
            $record->strtype = $record->type;
            $record->tousr = array();

            $record->canreply = false;
            $record->startnewthread = false;
            if (count($record->userids) > 1) {
                $record->canreplyall = true;
            }
            else {
                $record->canreplyall = false;
            }
            // preformat from user
            if (isset($record->fromid)) {
                $record->fromusr = $record->fromid;
                $fromuser = get_user($record->fromid);
                if ($fromuser->deleted === '0') {
                    $record->canreply = true;
                }
            }
            else {
              $record->fromusr = 0;
            }
            $record->message = format_notification_whitespace($record->message);
            // used to identify notification as from this plugin for json-calls
            $record->table = 'module_multirecipient_notification';
            $return->records[] = $record;
        }
    }

    return ($return);
}

/**
 * returns an object containing a list of ids of notifications the user has
 * sent and the tables where to find the dataelements. Also returns the
 * count of the found notifications
 *
 * @param string $type
 * @param int $limit
 * @param int $offset
 * @return \stdClass
 */
function activitylistout($type='all', $limit=10, $offset=0) {
    global $USER;
    $result = new stdClass();
    $userid = $USER->get('id');
    $searchtext = param_variable('search', null);
    $searcharea = param_variable('searcharea', 'All_data');

    if (isset($searchtext) AND $searchtext !== null) {
        $type = param_variable('type', 'all');
        $searchresults = get_message_search($searchtext, $type, $offset, $limit, "outbox.php", $userid);
        $result->msgidrecords = $searchresults[$searcharea]['data'];
        $result->count = $searchresults[$searcharea]['count'];
        $result->search = true;
    }
    else {
        $typesql = '';
        if ($type != 'all') {
            // Treat as comma-separated list of activity type names
            $types = explode(',', preg_replace('/[^a-z,]+/', '', $type));
            if ($types) {
                $typesql = ' at.name IN (' . join(',', array_map('db_quote', $types)) . ')';
                if (in_array('adminmessages', $types)) {
                    $typesql = '(' . $typesql . ' OR at.admin = 1)';
                }
                $typesql = ' AND ' . $typesql;
            }
        }
        $notificationtargetcolumn = 'from';
        $notificationtargetrole = 'sender';

        $msgidquery = "
            (
            SELECT a.id, a.ctime, 'notification_internal_activity' AS msgtable, subject
            FROM {notification_internal_activity} AS a
            INNER JOIN {activity_type} AS at ON a.type = at.id
            WHERE a." . $notificationtargetcolumn . " = ?
            " . $typesql . "
            AND at.name != 'newpost'
            )
            UNION
            (
            SELECT a.id, a.ctime, 'module_multirecipient_notification' AS msgtable, subject
            FROM {module_multirecipient_notification} AS a
            INNER JOIN {module_multirecipient_userrelation} AS b
                ON a.id = b.notification
            INNER JOIN {activity_type} AS at ON a.type = at.id
            WHERE b.usr = ?
            AND b.deleted = '0'
            AND b.role = '" . $notificationtargetrole . "'
            " . $typesql . "
            )";
        $countquery = 'SELECT COUNT(*) FROM (' . $msgidquery . ') AS dummytable';
        $result->count = count_records_sql($countquery, array($userid, $userid));

        $msgidquery .= "
        ORDER BY ctime DESC, subject ASC";
        $result->msgidrecords = get_records_sql_array($msgidquery, array($userid, $userid), $offset, $limit);
        $result->search = false;
    }

    if (!is_array($result->msgidrecords)) {
        $result->msgidrecords = array();
        $result->count = 0;
    }
    return $result;
}

/**
 * creates a result-array with the number, limit, offset and notification-type(s)
 * of the returned htmlrepresentation of the notifications in the outbox, as well
 * as the html representation itself. The return array has the following format:
 *
 * array (
 *   'count' => '17',
 *   'limit' => 10,
 *   'offset' => 0,
 *   'type' => 'all',
 *   'html' => '//html ...
 *   'pagination' => '// html
 *   'pagination_js' => '// javascript
 * )
 *
 * @global User $USER
 * @param type $type
 * @param type $limit
 * @param type $offset
 * @return array
 */
function activitylistout_html($type='all', $limit=10, $offset=0) {
    global $USER;
    $userid = $USER->get('id');
    $activitylist = activitylistout($type, $limit, $offset);

    $pagination = build_pagination(array(
        'id'         => 'activitylist_pagination',
        'url'        => get_config('wwwroot') . 'module/multirecipientnotification/outbox.php?type=' . hsc($type),
        'jsonscript' => 'module/multirecipientnotification/indexout.json.php',
        'datatable'  => 'activitylist',
        'count'      => $activitylist->count,
        'limit'      => $limit,
        'offset'     => $offset,
        'jumplinks'  =>  6,
        'numbersincludeprevnext' => 2,
        'setlimit'   => true,
    ));

    $result = array(
        'count'         => $activitylist->count,
        'limit'         => $limit,
        'offset'        => $offset,
        'type'          => $type,
        'html'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($activitylist->count < 1) {
       return $result;
    }

    foreach ($activitylist->msgidrecords as $msgidrecord ) {
        if ($msgidrecord->msgtable == 'notification_internal_activity') {
            $recordsarray = get_records_sql_array("SELECT a.*, at.name AS type, at.plugintype, at.pluginname
                                      FROM {notification_internal_activity} a
                                      INNER JOIN {activity_type} at ON a.type = at.id
                                      WHERE a.id = ?", array($msgidrecord->id));
            if (1 !== count($recordsarray)) {
                log_warn('inconsistent message-id in notification_internal_activity, id: ' . $msgidrecord->id);
                continue;
            }
            $record = $recordsarray[0];
            $record->self = false;
            $record->canreplyall = false;
            $record->canreply = false;
            $record->startnewthread = true;
            // read out receiver name
            if (isset($record->usr)) {
                $tousrarray = array(
                    'display' => display_name($record->usr),
                    'link' => null,
                );
                if (!get_user($record->usr)->deleted) {
                    $tousrarray['link'] = profile_url($record->usr);
                    $record->canreply = true;
                }
                $record->tousr = array (
                    $tousrarray,
                );
                $record->self = ($record->from == $USER->get('id'));
            }
            else {
                $record->tousr = array(
                    array(
                        'display' => get_string('system'),
                        'link' => null,
                    ),
                );
                $record->self = true;
            }
            // read out sender name
            if (isset($record->from)) {
                $record->fromusr = $record->from;
            }
            else {
                // we're in the outbox, so basically, this should hold for all messages
                $record->fromusr = $USER->get('id');
            }

            $record->date = format_date(strtotime($record->ctime), 'strfdaymonthyearshort');
            $section = empty($record->plugintype) ? 'activity' : "{$record->plugintype}.{$record->pluginname}";
            $record->strtype = get_string('type' . $record->type, $section);
            $record->message = format_notification_whitespace($record->message);
            // used to identify notification as internal for json-calls
            $record->table = 'notification_internal_activity';
            $records[] = $record;
        }
        else if ($msgidrecord->msgtable === 'module_multirecipient_notification') {
            $record = get_message_mr($userid, $msgidrecord->id);
            if (null === $record) {
                continue;
            }
            $record->strtype = $record->type;
            $record->date = format_date(strtotime($record->ctime), 'strfdaymonthyearshort');

            // We fill $record->tousr with an array per userentry, that holds the
            // display name of the user and the link to the users profile, if
            // applicable - we don't link to deleted users. Those will be summed
            // up in a single entry at the end of the list
            $deletedcount = 0;
            $record->self = false;
            $record->canreply = false;
            $record->canreplyall = false;
            $record->startnewthread = false;
            for ($i = 0; $i < count($record->userids); $i++) {
                $tousr = get_user($record->userids[$i]);
                if ($tousr->deleted) {
                    $deletedcount ++;
                }
                else {
                    $record->tousr[] = array(
                        'username' => display_username(get_user_for_display($record->userids[$i])),
                        'display' => display_name($record->userids[$i]),
                        'link' => profile_url($record->userids[$i]),
                    );
                }
            }
            if ($deletedcount > 0) {
                $record->tousr[] = array(
                    'username' => $deletedcount . ' ' . get_string('deleteduser', 'module.multirecipientnotification'),
                    'display' => $deletedcount . ' ' . get_string('deleteduser', 'module.multirecipientnotification'),
                    'link' => null,
                );
            }
            if ($deletedcount < count($record->userids)) {
                if ((count($record->userids) - $deletedcount) == 1) {
                    $record->canreply = true;
                    $record->self = ($record->fromid == $USER->get('id'));
                }
                else {
                    $record->canreplyall = true;
                }
            }

            if (isset($record->fromid)) {
                $record->fromusr = $record->fromid;
            }
            else {
                $record->fromusr = 0;
            }
            $record->message = format_notification_whitespace($record->message);
            // used to identify notification as from this plugin for json-calls
            $record->table = 'module_multirecipient_notification';
            $records[] = $record;
        }
    }

    $smarty = smarty_core();
    $smarty->assign('data', $records);
    $smarty->assign('USER', $USER);
    $smarty->assign('maxnamestrlength', PluginModuleMultirecipientnotification::MAX_USERNAME_IN_LIST_LENGTH);
    $result['html'] = $smarty->fetch('module:multirecipientnotification:activitylistout.tpl');

    return $result;
}
