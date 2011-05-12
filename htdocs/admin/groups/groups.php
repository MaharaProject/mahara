<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'managegroups/groups');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('administergroups', 'admin'));

require_once('group.php');
require_once('searchlib.php');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = 10;

$data = build_grouplist_html($query, $limit, $offset);

$searchform = pieform(array(
    'name'   => 'search',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $query,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('search'),
        ),
    ),
));

$js = <<< EOF
addLoadEvent(function () {
p = {$data['pagination_js']}
connect('search_submit', 'onclick', function (event) {
    replaceChildNodes('messages');
    var params = {'query': $('search_query').value};
    p.sendQuery(params);
    event.stop();
    });
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('searchform', $searchform);
$smarty->assign('results', $data);
$smarty->display('admin/groups/groups.tpl');

function search_submit(Pieform $form, $values) {
    redirect(get_config('wwwroot') . 'admin/groups/groups.php' . (!empty($values['query']) ? '?query=' . urlencode($values['query']) : ''));
}
