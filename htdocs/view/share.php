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
define('TITLE', get_string('sharedbyme', 'view'));
define('MENUITEM', 'share/sharedbyme');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'share');
$accesslists = View::get_accesslists($USER->get('id'));

$smarty = smarty();
setpageicon($smarty, 'icon-share-alt');
$smarty->assign('accesslists', $accesslists);
$smarty->display('view/share.tpl');
