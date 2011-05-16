<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminhome');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'registration.php');
if (get_config('installed')) {
    define('TITLE', get_string('administration', 'admin'));
}
else {
    define('TITLE', get_string('installation', 'admin'));
}
require_once('pieforms/pieform.php');
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

if (!get_config('registration_lastsent')) {
    $register = true;
}

$closed = get_config('siteclosedbyadmin');
$closeform = pieform(array(
    'name'     => 'close_site',
    'renderer' => 'oneline',
    'elements' => array(
        'close' => array(
            'type'  => 'hidden',
            'value' => !$closed,
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string($closed ? 'Open' : 'Close', 'admin'),
        ),
    ),
));

if (empty($upgrades)) {
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
