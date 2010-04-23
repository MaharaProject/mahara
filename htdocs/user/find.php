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
require_once('searchlib.php');
safe_require('search', 'internal');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit  = 10;

$data = search_user($query, $limit, $offset, array('exclude' => $USER->get('id')));
$data['query'] = $query;

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

$admingroups = new StdClass;
$admingroups->controlled = $controlledgroups;
$admingroups->invite = $invite;

build_userlist_html($data, 'find', $admingroups);

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

$js = <<< EOF
addLoadEvent(function () {
    p = {$data['pagination_js']}
    connect('search_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'query': $('search_query').value, 'extradata':serializeJSON({'page':'find'})};
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

$javascript = array('paginator');
if ($admingroups->invite || $admingroups->controlled) {
    array_push($javascript, 'groupbox');
}
$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array(friends_control_sideblock('find'))));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('user/find.tpl');

function search_submit(Pieform $form, $values) {
    redirect('/user/find.php' . (isset($values['query']) ? '?query=' . urlencode($values['query']) : ''));
}

?>
