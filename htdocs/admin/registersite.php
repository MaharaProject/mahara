<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminhome/registersite');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');

require(dirname(dirname(__FILE__)).'/init.php');

require_once('pieforms/pieform.php');
require(get_config('libroot') . 'registration.php');
define('TITLE', get_string('Register', 'admin'));

if (!get_config('registration_lastsent')) {
    $register = register_site();
}

$smarty = smarty();

if (isset($register)) {
    $smarty->assign('register', $register);
}

$smarty->display('admin/registersite.tpl');
