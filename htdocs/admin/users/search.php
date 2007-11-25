<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configusers/usersearch');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('usersearch', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'usersearch');
require('searchlib.php');

$search = (object) array(
    'query'       => trim(param_variable('query', '')),
    'institution' => param_alphanum('institution', 'all'),
    'f'           => param_alpha('f', null), // first initial
    'l'           => param_alpha('l', null), // last initial
);

$sortby  = param_alpha('sortby', 'firstname');
$sortdir = param_alpha('sortdir', 'asc');
$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);

$smarty = smarty(array('adminusersearch'));
$smarty->assign('search', $search);
$smarty->assign('alphabet', explode(',', get_string('alphabet')));
$smarty->assign('institutions', get_records_array('institution'));
$smarty->assign('results', build_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir));
$smarty->display('admin/users/search.tpl');

?>
