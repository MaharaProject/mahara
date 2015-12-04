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
require_once('searchlib.php');

$query  = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$filter = param_alpha('filter', 'all');

$page = 'myfriends';
if ($extradata = param_variable('extradata', null)) {
    $extradata = json_decode($extradata);
    if ($extradata->page) {
        $page = $extradata->page;
    }
}

if ($page == 'myfriends') {
    $data = search_friend($filter, $limit, $offset);
    $data['filter'] = $filter;
}
else {
    $options = array('exclude' => $USER->get('id'));
    if ($filter == 'myinstitutions') {
        $options['myinstitutions'] = true;
    }
    $data = search_user($query, $limit, $offset, $options);
    $data['query'] = $query;
    if (!empty($options['myinstitutions'])) {
        $data['filter'] = $filter;
    }
}

require_once('group.php');
$admingroups = (bool) group_get_user_admintutor_groups();
build_userlist_html($data, $page, $admingroups);
unset($data['data']);

json_reply(false, array('data' => $data));
