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
define('MENUITEM', 'settings/apps');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'apps');
define('APPS', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('connectedapps'));
define('SUBSECTIONHEADING', get_string('overview'));

$hasapps = apps_get_menu_tabs();

$smarty = smarty();
setpageicon($smarty, 'icon-globe');
$smarty->assign('hasapps', !empty($hasapps));
$smarty->display('account/apps.tpl');
