<?php

/**
 * Retrieve achievement information when sign-off is true
 *
 * @package    mahara
 * @subpackage checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$view_id = param_integer('id', 0);
$block_id = param_integer('block', 0);
$view = new View($view_id);
$block = new BlockInstance($block_id);
if (!($view->get('group') && (group_user_access($view->get('group'))
    ))) {
    throw new AccessDeniedException();
}
if ($block->get('view') != $view_id) {
    throw new AccessDeniedException();
}
if ($block) {
    $configdata = $block->get('configdata');
    $author = new User();
    $author->find_by_id($configdata['author']);
    $configdata['author'] = display_name($author, null, true);
    $configdata['time'] = format_date($configdata['time']);
    $configdata['level'] = get_field_sql("SELECT aal.value FROM {view_activity_achievement_levels} aal
                                          JOIN {view_activity} va ON va.id = aal.activity
                                          WHERE va.view = ? AND aal.type = ?", array($view_id, $configdata['level']));
    $smarty = smarty();
    $smarty->assign('configdata', $configdata);

    $html = $smarty->fetch('blocktype:checkpoint:achievement_levels_result.tpl');
    json_reply(false, array(
        'message' => null,
        'data' => array(
            'html' => $html
        )
    ));
}
json_reply('local', get_string('nooutcometypes', 'collection'));
