<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
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
 * @author     Piers Harding
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminhome/pluginadminwebservices');
// define('MENUITEM', 'webservice/logs');
// define('SECTION_PLUGINTYPE', 'core');
// define('SECTION_PLUGINNAME', 'admin');
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
} else {
    $institutions = get_records_select_array('institution', "name IN ('" . join("','", array_keys($USER->get('admininstitutions'))) . "')", null, 'displayname');
    $search->institution_requested = param_alphanum('institution_requested', 'all');
}

$smarty = smarty(array(get_config('wwwroot') . 'webservice/admin/js/usersearch.js'), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
safe_require('auth', 'webservice');
PluginAuthWebservice::menu_items($smarty, 'webservice/logs');
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
// global $THEME;
// $THEME->templatedirs[]= get_config('docroot') . 'auth/webservice/theme/raw/';
// $smarty->display('webservice/webservicelogs.tpl');
$smarty->display('../../../auth/webservice/theme/raw/webservicelogs.tpl');
