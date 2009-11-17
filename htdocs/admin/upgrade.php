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
define('INSTALLER', 1);

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'upgrade.php');

$smarty = smarty();

$upgrades = check_upgrades();
if (!empty($upgrades['core']->install)) {
    define('TITLE', get_string('installation', 'admin'));
    $smarty->assign('upgradeheading', get_string('performinginstallation', 'admin'));
}
else {
    define('TITLE', get_string('upgrades', 'admin'));
    $smarty->assign('upgradeheading', get_string('performingupgrades', 'admin'));
}

if (empty($upgrades['disablelogin'])) {
    auth_setup();
}
unset($upgrades['disablelogin']);

if (!$upgrades) {
    die_info(get_string('noupgrades', 'admin'));
}

$loadingicon = $THEME->get_url('images/loading.gif');
$successicon = $THEME->get_url('images/success.gif');
$failureicon = $THEME->get_url('images/failure.gif');

// Remove all files in the smarty and dwoo caches
// TODO post 1.2 remove the smarty part
require_once('file.php');
$basedir = get_config('dataroot') . 'smarty/compile/';
$dh = new DirectoryIterator($basedir);
foreach ($dh as $themedir) {
    if ($themedir->isDot()) continue;
    $themedirname = $basedir . $themedir->getFilename();
    rmdirr($themedirname);
    clearstatcache();
    check_dir_exists($themedirname);
}
$basedir = get_config('dataroot') . 'dwoo/compile/';
$dh = new DirectoryIterator($basedir);
foreach ($dh as $themedir) {
    if ($themedir->isDot()) continue;
    $themedirname = $basedir . $themedir->getFilename();
    rmdirr($themedirname);
    clearstatcache();
    check_dir_exists($themedirname);
}


$loadingstring = json_encode(get_string('upgradeloading', 'admin'));
$installsuccessstring = json_encode(get_string('installsuccess', 'admin'));
$successstring = json_encode(get_string('upgradesuccesstoversion', 'admin'));
$failurestring = json_encode(get_string('upgradefailure', 'admin'));
$coresuccess   = json_encode(get_string('coredatasuccess', 'admin'));
$localsuccess  = json_encode(get_string('localdatasuccess', 'admin'));

// Check if Mahara is being installed. An extra hook is required to insert core
// data if so.
if (!empty($upgrades['core']->install)) {
    $upgrades['firstcoredata'] = true;
    $upgrades['localpreinst'] = true;
    $upgrades['lastcoredata'] = true;
    $upgrades['localpostinst'] = true;
    $smarty->assign('install', true);
}                   

$js = <<< EOJS
            function processNext() {
                var element = todo.shift();

                if (!element) {
                    $('finished').style.visibility = 'visible';
                    return; // done
                }

                $(element).innerHTML = '<img src="{$loadingicon}" alt="' + {$loadingstring} + '" />';

                sendjsonrequest('upgrade.json.php', { 'name': element }, 'GET', function (data) {
                    if ( !data.error ) {
                        var message;
                        if (data.coredata) {
                            message = {$coresuccess};
                        } 
                        else if (data.localdata) {
                            message = {$localsuccess};
                        }
                        else {
                            if (data.install) {
                                message = {$installsuccessstring};
                            }
                            else {
                                message = {$successstring};
                            }
                            message += data.newversion;
                        }
                        $(data.key).innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                        processNext();
                    }
                    else {
                        var message = '';
                        if (data.errormessage) {
                            message = data.errormessage;
                        } 
                        else {
                            message = {$failurestring};
                        }
                        $(data.key).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
                    }
                }, 
                function () {
                    $(element).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + {$failurestring};
                },
                true);
            }

            addLoadEvent( processNext );
EOJS;

uksort($upgrades, 'sort_upgrades');
$js .= "\n" . 'var todo = ' . json_encode(array_keys($upgrades)) . ";\n";
$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign_by_ref('upgrades', $upgrades);
if (isset($upgrades['core'])) {
    $smarty->assign('releaseargs', array($upgrades['core']->torelease, $upgrades['core']->to));
}
$smarty->display('admin/upgrade.tpl');



?>
