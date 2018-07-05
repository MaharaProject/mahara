<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$id = param_integer('viewid');
if (!can_view_view($id)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
$view = new View($id);
list($tagcount, $alltags) = $view->get_all_tags_for_view();

$smarty = smarty_core();
$smarty->assign('view', $id);
$smarty->assign('owner', $view->get('owner'));
$smarty->assign('tags', $alltags);
$html = $smarty->fetch('taglist.tpl');

json_reply(false, array(
    'message' => null,
    'count' => $tagcount,
    'tags' => $alltags,
    'html' => $html,
));
