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
define('MENUITEM', 'adminhome/home');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'registration.php');
if (get_config('installed')) {
    define('TITLE', get_string('administration', 'admin'));
    validate_theme(get_config('theme'));
}
else {
    define('TITLE', get_string('installation', 'admin'));
}
require(get_config('libroot') . 'upgrade.php');
require_once(get_config('libroot') . 'registration.php');

$upgrades = check_upgrades();

if (isset($upgrades['core']) && !empty($upgrades['core']->install)) {
    $smarty = smarty();
    $smarty->assign('installing', true);
    $smarty->assign('releaseargs', array($upgrades['core']->torelease, $upgrades['core']->to));
    $smarty->display('admin/installgpl.tpl');
    exit;
}

if (!get_config('registration_lastsent')
    || get_config('new_registration_policy')) {
    $register = true;
}

$closed = get_config('siteclosedbyadmin');
$closeform = pieform(array(
    'name'     => 'close_site',
    'renderer' => 'oneline',
    'elements' => array(
        'close' => array(
            'type'  => 'hidden',
            'value' => !$closed
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string($closed ? 'Open' : 'Close', 'admin'),
            'class' => $closed ? 'btn-primary' : 'btn-default'
        ),
    ),
));

$clearcachesform = pieform(array(
    'name'     => 'clear_caches',
    'renderer' => 'oneline',
    'autofocus' => 'false',
    'elements' => array(
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('clearcachessubmit', 'admin'),
            'class' => 'btn-default',
        ),
    ),
));

if (get_config('installed')) {
    $sitedata = site_statistics();
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', get_string('administration', 'admin'));

// normal admin page starts here
$smarty->assign('upgrades', $upgrades);
if (isset($sitedata)) {
    $smarty->assign('sitedata', $sitedata);
}

if (isset($register)) {
    $smarty->assign('register', $register);
}

$smarty->assign('closed', $closed);
$smarty->assign('closeform', $closeform);
$smarty->assign('clearcachesform', $clearcachesform);

$smarty->assign('warnings', site_warnings());

$smarty->display('admin/index.tpl');

function close_site_submit(Pieform $form, $values) {
    global $closed;
    if (!$closed && $values['close']) {
        set_config('siteclosedbyadmin', 1);
        require_once(get_config('docroot') . 'auth/session.php');
        remove_all_sessions();
    }
    else if ($closed && !$values['close']) {
        set_config('siteclosedbyadmin', 0);
    }
    redirect(get_config('wwwroot') . 'admin/index.php');
}

function clear_caches_submit() {
    global $SESSION;

    $result = clear_all_caches();

    if (!$result) {
        $SESSION->add_error_msg(get_string('clearingcacheserror', 'admin'));
    }
    else {
        $SESSION->add_ok_msg(get_string('clearingcachessucceed', 'admin'));
    }

    redirect(get_config('wwwroot') . 'admin/index.php');
}
