<?php

/**
 *
 * @package    mahara
 * @subpackage artefact-checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('blocktype', 'checkpoint');

$blockid = param_integer('blockid', null);
$level = param_integer('level', null);

$block = new BlockInstance($blockid);
$configdata = $block->get('configdata');
$viewid = $block->get('view');
$configdata['level'] = $level;
$configdata['author'] = $USER->get('id');
$configdata['time'] = time();
$block->set('configdata', $configdata);
$block->commit();

// Is the block correct type
if ($block->get('blocktype') != 'checkpoint') {
    json_reply('local', get_string('wrongblocktype', 'view'));
}

$view = $block->get_view();
$viewid = $view->get('id');
// Is the block on a page we can see
if (!can_view_view($viewid)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}

$grouprole = group_user_access($view->get('group'));
$actionsallowed = ($grouprole === 'admin' || $grouprole === 'tutor');
if ($actionsallowed) {
    $record = new stdClass();

    // Should update record on DB here
    if ($level) {
        $smarty = smarty();
        $smarty->assign('actionsallowed', $actionsallowed ? 1 : 0);
        $smarty->assign('saved_achievement_level', $level);
        // TODO get signoff from DB
        $smarty->assign('signedoff', true);
        $smarty->assign('saved_achievement_level', $level);
        $html = $smarty->fetch('blocktype:checkpoint:display_achievement_level.tpl');
        $data = array(
            'html' => $html,
            'error'    => false,
            'message'  => 'Checkpoint level updated!',
        );
        json_reply(false, $data);
    }
}

$data = new stdClass();

json_reply(false, array('data' => $data));
