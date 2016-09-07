<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
else if ($query != '') {
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

$data = View::shared_to_user($query, $tag, $limit, $offset, $sort, $sortdir, $share, $USER->get('id'));

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (empty($queryparams) ? '' : ('?' . http_build_query($queryparams))),
    'jsonscript' => 'json/sharedviews.php',
    'datatable' => 'sharedviewlist',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'setlimit' => true,
    'jumplinks' => 8,
    'numbersincludeprevnext' => 2,
));

$smarty = smarty_core();
$smarty->assign('views', $data->data);
$data->tablerows = $smarty->fetch('view/sharedviewrows.tpl');
$data->pagination = $pagination['html'];
$data->pagination_js = $pagination['javascript'];

json_reply(false, array('data' => $data));
