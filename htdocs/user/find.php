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
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('findfriends'));
require('searchlib.php');
safe_require('search', 'internal');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit  = 10;

$data = search_user($query, $limit, $offset, array('exclude' => $USER->get('id')));

if ($data['data']) {
    $userlist = join(',', array_map(create_function('$u','return $u[\'id\'];'), $data['data']));
    $data['data'] = get_users_data($userlist);
}

$searchform = pieform(array(
    'name' => 'search',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $query
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('search')
        )
    )
));

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'user/find.php?query=' . $query,
    'count' => $data['count'],
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('user', 'group'),
    'resultcounttextplural' => get_string('users', 'group'),
));

if ($query && !$data['count']) {
    // Search run, no results
    $message = get_string('nosearchresultsfound', 'group');
}

$smarty = smarty(array(), array(), array(), array('sideblocks' => array(friends_control_sideblock('find'))));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('users', $data['data']);
$smarty->assign('form', $searchform);
$smarty->assign('pagination', $pagination['html']);
if (isset($message)) {
    $smarty->assign('message', $message);
}
$smarty->display('user/find.tpl');

function search_submit(Pieform $form, $values) {
    redirect('/user/find.php' . (isset($values['query']) ? '?query=' . urlencode($values['query']) : ''));
}

?>
