<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');

$view = new View(param_integer('id'));
$blocks = param_variable('blocks', null);
$blocks = json_decode($blocks);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

require_once(get_config('docroot') . 'blocktype/lib.php');
try {
    foreach ($blocks as $block) {
        $id = $block->id;
        $dimensions = $block->dimensions;

        $bi = new BlockInstance($id);
        // Check if the block_instance belongs to this view
        if ($bi->get('view') != $view->get('id')) {
          throw new AccessDeniedException(get_string('blocknotinview', 'view', $bi->get('id')));
        }

        $bi->set('positionx', $dimensions->newx);
        $bi->set('positiony', $dimensions->newy);
        $bi->set('width', $dimensions->newwidth);
        $bi->set('height', $dimensions->newheight);
        $bi->commit();
    }

    json_reply(false, get_string('blockssizeupdated', 'view'));
}
catch(Exception $e) {
    json_reply(true, $e->getMessage());
}
