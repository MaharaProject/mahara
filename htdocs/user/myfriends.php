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
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/myfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('myfriends'));

$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 0);
$limit = 25;

$userid = $USER->get('id');
$data = array();
if ($filter == 'current') {
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
else if ($filter == 'pending') {
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
	$filter = 'all';
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

if ($data) {
    foreach ($data as $friend) {
        if (isset($cleanviews[$friend->id])) {
            $friend->views = $cleanviews[$friend->id];
        }
    }
    foreach ($data as $friend) {
        if ($friend->pending) {
            $friend->accept = pieform(array(
                'name' => 'acceptfriend' . $friend->id,
                'successcallback' => 'acceptfriend_submit',
                'renderer' => 'div',
                'autofocus' => 'false',
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'value' => get_string('approverequest', 'group')
                    ),
                    'id' => array(
                        'type' => 'hidden',
                        'value' => $friend->id
                    )
                )
            ));
        }
    }
}

$filterform = pieform(array(
    'name' => 'filter',
    'renderer' => 'oneline',
    'elements' => array(
        'filter' => array(
            'type' => 'select',
            'options' => array(
                'all' => get_string('allfriends', 'group'),
                'current' => get_string('currentfriends', 'group'),
                'pending' => get_string('pendingfriends', 'group')
            ),
            'defaultvalue' => $filter
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('filter')
        )
    )
));

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'user/myfriends.php?filter=' . $filter,
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('friend', 'group'),
    'resultcounttextplural' => get_string('friends', 'group'),
));

if (!$data) {
    if ($filter == 'pending') {
        $message = get_string('nobodyawaitsfriendapproval', 'group');
    }
    else {
        $message = get_string('trysearchingforfriends', 'group', '<a href="' . get_config('wwwroot') . 'user/find.php">', '</a>');
    }
}

function filter_submit(Pieform $form, $values) {
    redirect('/user/myfriends.php?filter=' . $values['filter']);
}

function acceptfriend_submit(Pieform $form, $values) {
	global $USER, $SESSION;

	$user = get_record('usr', 'id', $values['id']);

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());
    $f->usr1 = $user->id;
    $f->usr2 = $USER->get('id');

    // notification info
    $n = new StdClass;
    $n->url = get_config('wwwroot') . 'user/view.php?id=' . $USER->get('id');
    $n->users = array($user->id);
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->message = get_string_from_language($lang, 'friendrequestacceptedmessage', 'group', $displayname, $displayname);
    $n->subject = get_string_from_language($lang, 'friendrequestacceptedsubject', 'group');

    db_begin();
    delete_records('usr_friend_request', 'owner', $USER->get('id'), 'requester', $user->id);
    insert_record('usr_friend', $f);

    db_commit();
    $SESSION->add_ok_msg(get_string('friendformacceptsuccess', 'group'));
    redirect('/user/view.php?id=' . $values['id']);
}

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('users', $data);
$smarty->assign('form', $filterform);
$smarty->assign('pagination', $pagination['html']);
if (isset($message)) {
    $smarty->assign('message', $message);
}
$smarty->display('user/myfriends.tpl');

?>
