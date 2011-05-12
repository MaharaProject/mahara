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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/usersearch');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('usersearch', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'usersearch');
require_once('searchlib.php');

$search = (object) array(
    'query'       => trim(param_variable('query', '')),
    'f'           => param_alpha('f', null), // first initial
    'l'           => param_alpha('l', null), // last initial
);

$sortby  = param_alpha('sortby', 'firstname');
$sortdir = param_alpha('sortdir', 'asc');
$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);

if ($USER->get('admin')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search->institution = param_alphanum('institution', 'all');
} else {
    $institutions = get_records_select_array('institution', "name IN ('" . join("','", array_keys($USER->get('admininstitutions'))) . "')", null, 'displayname');
    $search->institution_requested = param_alphanum('institution_requested', 'all');
}

$smarty = smarty(array('adminusersearch'));
$smarty->assign('search', $search);
$smarty->assign('alphabet', explode(',', get_string('alphabet')));
$smarty->assign('institutions', $institutions);
$smarty->assign('results', build_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/search.tpl');
