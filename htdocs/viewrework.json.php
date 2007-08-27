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
define('JSON', 1);
define('PUBLIC', 1);
require(dirname(__FILE__) . '/init.php');
require(dirname(__FILE__) . '/viewlib.php');

$action = param_alphanumext('action');

switch ($action) {
case 'delete_blockinstance':
    // @todo
    json_reply(false, false);
    break;
case 'blocktype_list':
    $category = param_alpha('category');
    // TODO:
    //
    // Where I can get to:
    // Static version: Clicking buttons = response, but no action. Category browser works, for hard coded block types
    // Dynamic version: Actions = response, but no action performed. Category browser works with ajax for hard coded block types
    //
    // What can be done:
    // Category browser
    // Add column: js should make room for it, put in the raw html (note: need to generate column raw html in a function then!)
    // Remove column: js should destroy the existing column, hopefully moving the blocks to other columns (could be implemented as move move delete?)
    // Moving block instances: js should drag+drop, do ajax stub request. static should send response
    // Add block: js should be done by drag and drop. static should respond
    //
    //
    //
    //
    //
    //
    $data = views_build_blocktype_list($category, true);
    json_reply(false, array('message' => false, 'data' => $data));
    break;
}

json_reply(true, 'Unknown action "' . $action . '"');
?>
