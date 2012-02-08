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
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$query  = param_variable('query', null);
$tag    = param_variable('tag', null);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$queryparams = array();

if (!empty($tag)) {
    $queryparams['tag'] = $tag;
    $query = null;
}
else if (!empty($query)) {
    $queryparams['query'] = $query;
}

$sortoptions = array(
    'lastchanged',
    'mtime',
    'ownername',
    'title',
);

if (!in_array($sort = param_alpha('sort', 'lastchanged'), $sortoptions)) {
    $sort = 'lastchanged';
}
if ($sort !== 'lastchanged') {
    $queryparams['sort'] = $sort;
}
$sortdir = ($sort == 'lastchanged' || $sort == 'mtime') ? 'desc' : 'asc';

$shareoptions = array(
    'user',
    'friend',
    'group',
    'institution',
    'loggedin',
    'public',
);

$share = param_variable('share', array());
if (is_array($share)) {
    $share = $queryparams['share'] = array_intersect($share, $shareoptions);
}
else {
    $share = null;
}

$data = View::shared_to_user($query, $tag, $limit, $offset, $sort, $sortdir, $share);

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (empty($queryparams) ? '' : ('?' . http_build_query($queryparams))),
    'jsonscript' => '/json/sharedviews.php',
    'datatable' => 'sharedviewlist',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty_core();
$smarty->assign_by_ref('views', $data->data);
$data->tablerows = $smarty->fetch('view/sharedviewrows.tpl');
$data->pagination = $pagination['html'];
$data->pagination_js = $pagination['javascript'];

json_reply(false, array('data' => $data));
