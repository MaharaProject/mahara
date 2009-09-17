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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/myfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require('searchlib.php');
define('TITLE', get_string('myfriends'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'myfriends');

$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 0);
$limit = 10;

$userid = $USER->get('id');
$data = array();
if ($filter == 'current') {
    $count = count_records_sql('SELECT COUNT(usr1) FROM {usr_friend}
        JOIN {usr} u1 ON (u1.id = usr1 AND u1.deleted = 0)
        JOIN {usr} u2 ON (u2.id = usr2 AND u2.deleted = 0)
        WHERE usr1 = ? OR usr2 = ?',
        array($userid, $userid)
    );
    $data = get_column_sql('SELECT u.id, u.firstname, u.lastname
        FROM (
            SELECT usr1 AS id FROM {usr_friend} WHERE usr2 = ?
            UNION SELECT usr2 AS id FROM {usr_friend} WHERE usr1 = ?
        ) f
        JOIN {usr} u ON f.id = u.id AND u.deleted = 0
        ORDER BY firstname, lastname, id
        LIMIT ? OFFSET ?', array($userid, $userid, $limit, $offset)
    );
    if (!$data || !$views = get_views(array_keys($data), null, null)) {
        $views = array();
    }
}
else if ($filter == 'pending') {
	$count = count_records_sql('SELECT COUNT(owner) FROM {usr_friend_request}
	    JOIN {usr} u ON (u.id = requester AND u.deleted = 0)
	    WHERE owner = ?',
	    array($userid)
	);
    $data = get_column_sql(
        'SELECT requester FROM {usr_friend_request}
        JOIN {usr} ON (requester = id AND deleted = 0) WHERE owner = ?
        ORDER BY firstname, lastname, id
        LIMIT ?
        OFFSET ?', array($userid, $limit, $offset)
    );
}
else {
	$filter = 'all';
    $count = count_records_sql('SELECT COUNT(usr1) FROM {usr_friend}
            JOIN {usr} u1 ON (u1.id = usr1 AND u1.deleted = 0)
            JOIN {usr} u2 ON (u2.id = usr2 AND u2.deleted = 0)
            WHERE usr1 = ? OR usr2 = ?',
            array($userid, $userid)
        )
        + count_records_sql('SELECT COUNT(owner) FROM {usr_friend_request}
	        JOIN {usr} u ON (u.id = requester AND u.deleted = 0)
	        WHERE owner = ?',
	        array($userid)
        );
    $data = get_column_sql(
        'SELECT f.id FROM (
            SELECT requester AS id, \'1\' AS status FROM {usr_friend_request} WHERE owner = ?
            UNION SELECT usr2 AS id, \'2\' AS status FROM {usr_friend} WHERE usr1 = ?
            UNION SELECT usr1 AS id, \'2\' AS status FROM {usr_friend} WHERE usr2 = ?
        ) f
        JOIN {usr} u ON (f.id = u.id AND u.deleted = 0)
        ORDER BY status, firstname, lastname, u.id
        LIMIT ?
        OFFSET ?', array($userid, $userid, $userid, $limit, $offset)
    );
    if (!$data || !$views = get_views(array_keys($data), null, null)) {
        $views = array();
    }
}

if ($data) {
    $userlist = join(',', $data);
    $data = get_users_data($userlist);
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

$smarty = smarty(array(), array(), array(), array('sideblocks' => array(friends_control_sideblock())));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('users', $data);
$smarty->assign('form', $filterform);
$smarty->assign('pagination', $pagination['html']);
if (isset($message)) {
    $smarty->assign('message', $message);
}
$smarty->display('user/myfriends.tpl');

?>
