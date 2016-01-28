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
define('ADMIN', 1);
define('MENUITEM', 'configsite/share');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
define('TITLE', get_string('share', 'view'));

$accesslists = View::get_accesslists(null, null, 'mahara');

$smarty = smarty();
setpageicon($smarty, 'icon-key');

$smarty->assign('accesslists', $accesslists);
$smarty->display('view/share.tpl');
