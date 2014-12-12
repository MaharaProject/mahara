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
define('MENUITEM', 'configextensions/webservices/logs');
define('SECTION_PAGE', 'webservicelogs');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('webservicessearchlib.php');
define('TITLE', get_string('webservicelogs', 'auth.webservice'));
require_once('pieforms/pieform.php');


$sortby  = param_alpha('sortby', 'timelogged');
$sortdir = param_alpha('sortdir', 'desc');
$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);

$search = (object) array(
    'userquery'      => trim(param_variable('userquery', '')),
    'functionquery'  => trim(param_variable('functionquery', '')),
    'protocol'       => trim(param_alphanum('protocol', 'all')),
    'authtype'       => trim(param_alphanum('authtype', 'all')),
    'onlyerrors'     => ('on' == param_alphanum('onlyerrors', 'off') ? 1 : 0),
    'sortby'         => $sortby,
    'sortdir'        => $sortdir,
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

$smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
safe_require('auth', 'webservice');

$smarty->assign('search', $search);
$smarty->assign('alphabet', explode(',', get_string('alphabet')));
$smarty->assign('cancel', get_string('cancel'));
$smarty->assign('institutions', $institutions);
$smarty->assign('protocols', array('REST', 'XML-RPC', 'SOAP'));
$smarty->assign('authtypes', array('TOKEN', 'USER', 'OAUTH'));
list($html, $columns, $searchurl, $pagination) = build_webservice_log_search_results($search, $offset, $limit, $sortby, $sortdir);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $searchurl['url']);
$smarty->assign('sortby', $searchurl['sortby']);
$smarty->assign('sortdir', $searchurl['sortdir']);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('auth:webservice:webservicelogs.tpl');
