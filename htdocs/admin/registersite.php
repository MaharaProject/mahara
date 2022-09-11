<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminhome/registersite');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');


require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'registration.php');
define('TITLE', get_string('Registration', 'admin'));

// This runs register_site in lib/registration.php, which is what displays the form.

$registration_update = get_config('new_registration_policy') ? get_string('newregistrationpolicyinfo1', 'admin') : '';
$registerinfo = register_site();

// See if the site is currently registered
$isregistered = get_config('registration_sendweeklyupdates');
// The $firstregistered might be false if site registered before we began to keep this information.
$firstregistered = get_config('registration_firstsent');
if ($firstregistered) {
    $firstregistered = format_date($firstregistered);
}

$smarty = smarty();
setpageicon($smarty, 'icon-star');
$smarty->assign('registrationupdate', $registration_update);
$smarty->assign('isregistered', $isregistered);
$smarty->assign('registerinfo', $registerinfo);
$smarty->assign('firstregistered', $firstregistered);


$smarty->display('admin/registersite.tpl');
