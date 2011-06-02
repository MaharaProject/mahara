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
require_once('searchlib.php');
define('TITLE', get_string('myfriends'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'myfriends');

$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 0);
$limit = 10;

$data = search_friend($filter, $limit, $offset);
$data['filter'] = $filter;

$controlledgroups = count_records_sql("SELECT COUNT(g.id)
          FROM {group} g
          JOIN {group_member} gm ON (gm.group = g.id)
          JOIN {grouptype_roles} gtr ON (gtr.grouptype = g.grouptype AND gtr.role = gm.role)
          WHERE gm.member = ?
          AND g.jointype = 'controlled'
          AND (gm.role = 'admin' OR gtr.see_submitted_views = 1)
          AND g.deleted = 0", array($USER->get('id')));

$invite = count_records_sql("SELECT COUNT(g.id)
        FROM {group} g
        JOIN {group_member} gm ON (gm.group = g.id)
        WHERE gm.member = ?
        AND g.jointype = 'invite'
        AND gm.role = 'admin'
        AND g.deleted = 0", array($USER->get('id')));

$admingroups = $controlledgroups || $invite;

build_userlist_html($data, 'myfriends', $admingroups);

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

$js = <<< EOF
addLoadEvent(function () {
    p = {$data['pagination_js']}
    connect('filter_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'filter': $('filter_filter').value};
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

if (!$data['count']) {
    if ($filter == 'pending') {
        $message = get_string('nobodyawaitsfriendapproval', 'group');
    }
    else {
        $message = get_string('trysearchingforfriends', 'group', '<a href="' . get_config('wwwroot') . 'user/find.php">', '</a>');
    }
}

$javascript = array('paginator');
if ($admingroups) {
    array_push($javascript, 'groupbox');
}
$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array(friends_control_sideblock())));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $filterform);
if (isset($message)) {
    $smarty->assign('message', $message);
}
$smarty->display('user/myfriends.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/user/myfriends.php?filter=' . $values['filter']);
}
