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
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'viewrework');
require('init.php');
require('viewlib.php');
require('view.php');
define('TITLE', 'Views Rework [DANGER construction site]');

$view = param_integer('view');
$view = new View($view);
view_process_changes();

$smarty = smarty(array('views'), array('<link rel="stylesheet" href="views.css" type="text/css">'));

// FIXME: we can't know the first category is 'aboutme'
$category = param_alpha('category', 'aboutme');

// The list of categories for the tabbed interface
$smarty->assign('category_list', view_build_category_list($category, $view));

// The list of blocktypes for the default category
$smarty->assign('blocktype_list', view_build_blocktype_list($category));

// The HTML for the columns in the view
$smarty->assign('columns', view_build_columns($view));

$smarty->display('viewrework.tpl');

?>
