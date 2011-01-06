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
define('MENUITEM', 'groups/views');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');
require_once('group.php');
define('TITLE', get_string('sharedviews', 'view'));

$params = array(
    'query' => param_variable('query', null),
    'tag'   => param_variable('tag', null),
);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$searchform = pieform(array(
    'name' => 'search',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $params['query'],
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('search')
        )
    )
));

$data = View::shared_to_user($params['query'], $params['tag'], $limit, $offset);

$querystring = http_build_query($params);

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (empty($querystring) ? '' : '?' . $querystring),
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty(array('paginator'));
$smarty->assign('loggedin', $USER->is_logged_in());
$smarty->assign('views', $data->data);
$smarty->assign('searchform', $searchform);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('view/sharedviews.tpl');
exit;

function search_submit(Pieform $form, $values) {
    redirect('/view/sharedviews.php' . (empty($values['query']) ? '' : '?query=' . urlencode($values['query'])));
}
