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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');
define('TITLE', get_string('share', 'view'));
define('SUBSECTIONHEADING', TITLE);
define('MENUITEM', 'groups/share');

define('GROUP', param_integer('group'));
$group = group_current_group();
if (!group_user_can_edit_views($group)) {
    throw new AccessDeniedException();
}

$accesslists = View::get_accesslists(null, $group->id);

$smarty = smarty();
$smarty->assign('heading', $group->name);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('accesslists', $accesslists);
$smarty->display('view/share.tpl');
