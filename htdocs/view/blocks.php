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
define('MENUITEM', 'myportfolio/views');
require(dirname(dirname(__FILE__)) . '/init.php');
require('view.php');
define('TITLE', get_string('editblocks', 'view'));

$view = new View(param_integer('id'));

$new = param_boolean('new');
$category = param_alpha('c', '');
// Make the default category the first tab if none is set
if ($category === '') {
    $category = get_field_sql('SELECT "name" FROM {blocktype_category} ORDER BY "name" LIMIT 1');
}

$view->process_changes($category, $new);
$columns = $view->build_columns(true);

$extraconfig = array(
    'stylesheets' => array('style/views.css'),
);
$smarty = smarty(array('views', 'tinytinymce'), array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">'), false, $extraconfig);


// The list of categories for the tabbed interface
$smarty->assign('category_list', View::build_category_list($category, $view, $new));

// The list of blocktypes for the default category
$smarty->assign('blocktype_list', View::build_blocktype_list($category));

// The HTML for the columns in the view
$smarty->assign('columns', $columns);

// Tell smarty we're editing rather than just rendering
$smarty->assign('editing', true);

$smarty->assign('category', $category);
$smarty->assign('new', $new);
$smarty->assign('view', $view->get('id'));

$smarty->assign('can_change_layout', ($view->get('numcolumns') > 1 && $view->get('numcolumns') < 5));

$smarty->display('view/blocks.tpl');

?>
