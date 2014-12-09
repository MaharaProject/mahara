<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/pluginadminwebservices');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
$plugintype = 'auth';
$pluginname = 'webservice';
define('SECTION_PLUGINTYPE', $plugintype);
define('SECTION_PLUGINNAME', $pluginname);
define('SECTION_PAGE', 'pluginconfig');

require_once('webservicessearchlib.php');

// validate the incoming token
$token  = param_variable('token', '');
$suid   = param_variable('suid', '');
$ouid   = param_variable('ouid', '');

// did the user cancel
if (param_alpha('cancel_submit', 'empty') != 'empty') {
    if ($ouid) {
        redirect('/webservice/admin/oauthv1sregister.php?ouid=' . $ouid);
    }
    else if ($suid) {
        redirect('/webservice/admin/userconfig.php?suid=' . $suid);
    }
    else {
        redirect('/webservice/admin/tokenconfig.php?token=' . $token);
    }
}

$sortby  = param_alpha('sortby', 'firstname');
$sortdir = param_alpha('sortdir', 'asc');
$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);

$search = (object) array(
    'query'          => trim(param_variable('query', '')),
    'f'              => param_alpha('f', null),
    'l'              => param_alpha('l', null),
    'sortby'         => $sortby,
    'sortdir'        => $sortdir,
    'loggedin'       => 'any',
    'loggedindate'   => strftime(get_string('strftimedatetimeshort')),
    'duplicateemail' => false,
    'offset'         => $offset,
    'limit'          => $limit,
);

if ($USER->get('admin')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search->institution = param_alphanum('institution', 'all');
}
else {
    $institutions = get_records_select_array('institution', "name IN ('" . join("','", array_keys($USER->get('admininstitutions'))) . "')", null, 'displayname');
    $search->institution_requested = param_alphanum('institution_requested', 'all');
}

$smarty = smarty(array(get_config('wwwroot') . 'webservice/admin/js/usersearch.js'));
$smarty->assign('token_id', $token);
$smarty->assign('token', $token);
$smarty->assign('suid', $suid);
$smarty->assign('ouid', $ouid);
$smarty->assign('search', $search);
$smarty->assign('alphabet', explode(',', get_string('alphabet')));
$smarty->assign('cancel', get_string('cancel'));
$smarty->assign('institutions', $institutions);
list($html, $columns, $searchurl, $pagination) = build_webservice_user_search_results($search, $offset, $limit, $sortby, $sortdir);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $searchurl['url']);
$smarty->assign('sortby', $searchurl['sortby']);
$smarty->assign('sortdir', $searchurl['sortdir']);

if ($token) {
    $heading = get_string('headingusersearchtoken', 'auth.webservice');
}
else {
    $heading = get_string('headingusersearchuser', 'auth.webservice');
}
$smarty->assign('PAGEHEADING', $heading);
$smarty->display('auth:webservice:search.tpl');
