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
require('view.php');

$view   = param_integer('view');
$view = new View($view);
$change = param_boolean('change', false);
$action = param_alphanumext('action', '');

// we actually ned to process stuff
if ($change) {
    try {
        $returndata = view_process_changes(true);
        json_reply(false, $returndata);
    }
    catch (Exception $e) {
        json_reply(true, $e->getMessage());
    }
}
// else we're just reading data...
switch ($action) {
case 'blocktype_list':
    $category = param_alpha('category');
    // TODO:
    //
    // Where I can get to:
    // Static version: Clicking buttons = response, but no action.
    // Dynamic version: Actions = response, but no action performed.
    //
    // What can be done:
    // [done] Add column: js should make room for it, put in the raw html (note: need to generate column raw html in a function then!)
    // [done] Remove column: js should destroy the existing column, hopefully moving the blocks to other columns (could be implemented as move move delete?)
    // Moving block instances: js should drag+drop, do ajax stub request. static should send response
    // Add block: js should be done by drag and drop. static should respond
    //
    // Where I'm up to:
    // Drag+Drop move blockinstances
    // ...
    // Fix up all the stuff that works with javascript to work in the non javascript version
    // Clean up view_process_changes() and related things
    // ...
    // Blocktype adding to view (but only after the change can be reflected in the database!)
    //
    $data = view_build_blocktype_list($category, true);
    json_reply(false, array('message' => false, 'data' => $data));
    break;
}

json_reply(true, 'Unknown action "' . $action . '"');

?>
