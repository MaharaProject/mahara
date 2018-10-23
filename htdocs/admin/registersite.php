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
require(get_config('libroot') . 'registration.php');
define('TITLE', get_string('Registration', 'admin'));

// This runs register_site in registration.php, which is what displays the form and the button for emails.
if (!get_config('registration_lastsent')
    || get_config('new_registration_policy')) {
    $register = register_site();
    if (get_config('new_registration_policy')) {
        $registration_update = get_string('newregistrationpolicyinfo', 'admin');
    }
}
else {
    $registered = register_site(true);
    $firstregistered = (get_config('registration_firstsent'));
    // The $firstregistered might be false if site registered before we kept this info. Otherwise format as date.
    if ($firstregistered) {
        $firstregistered = format_date($firstregistered);
    }
}

$smarty = smarty();

setpageicon($smarty, 'icon-star');

if (isset($register)) {
    $smarty->assign('register', $register);
}
else if (isset($registered)) {
    $smarty->assign('registered', $registered);
    $smarty->assign('firstregistered', $firstregistered);
}

$js = <<<EOF
jQuery(function($) {
    function update_weeklyupdates_options() {
        var showweeklyupdates = $('#register_registeryesno').prop('checked');
        if (showweeklyupdates) {
            $('#register_sendweeklyupdates_container').removeClass('hidden');
        }
        else {
            $('#register_sendweeklyupdates_container').addClass('hidden');
        }
    }
    $('#register_registeryesno').on('click', update_weeklyupdates_options);
    update_weeklyupdates_options();
});
EOF;

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/registersite.tpl');
