<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'upgrade.php');

$smarty = smarty();

$upgrades = check_upgrades();
if (empty($upgrades['disablelogin'])) {
    auth_setup();
}
unset($upgrades['disablelogin']);

if (!$upgrades) {
    die_info(get_string('noupgrades', 'admin'));
}

$js = 'var todo = ' . json_encode(array_keys($upgrades)) . ";\n";
$loadingicon = theme_get_url('loading.gif');
$successicon = theme_get_url('success.gif');
$failureicon = theme_get_url('failure.gif');

$loadingstring = get_string('upgradeloading', 'admin');
$installsuccessstring = get_string('installsuccess', 'admin');
$successstring = get_string('upgradesuccesstoversion', 'admin');
$failurestring = get_string('upgradefailure', 'admin');
$coresuccess   = get_string('coredatasuccess', 'admin');

// Check if Mahara is being installed. An extra hook is required to insert core
// data if so.
if (!empty($upgrades['core']->install)) {
    $smarty->assign('install', true);
    $installjs =<<< EOJS
                    var d = loadJSONDoc('upgrade.json.php', { 'install' : 1 });
                    
                    $('coredata').innerHTML = '<img src="{$loadingicon}" alt="{$loadingstring}" />';
                    
                    d.addCallbacks(function (data) {
                        if ( !data.error ) {
                            var message = '{$coresuccess}';
                            if (data.message) {
                                message += ' (' + data.message + ')';
                            }
                            $('coredata').innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                            $('finished').style.visibility = 'visible';
                        }
                        else {
                            var message = '';
                            if (data.message) {
                                message = data.message;
                            } 
                            else {
                                message = '{$failurestring}';
                            }
                            $('coredata').innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
                        }
                    }, function () {
                        $('coredata').innerHTML = '<img src="{$failureicon}" alt=":(" /> {$failurestring}';
                    });

EOJS;
}
else {
    $installjs = '';
}
                    
$js .= <<< EOJS
            function processNext() {
                var element = todo.shift();

                if ( ! element ) {
                    // we're done
                    $installjs
                    return;
                }

                var d = loadJSONDoc('upgrade.json.php', { 'name': element });

                $(element).innerHTML = '<img src="{$loadingicon}" alt="{$loadingstring}" />';

                d.addCallbacks(function (data) {
                    if ( !data.error ) {
                        var message;
                        if (data.message.install) {
                            message = '{$installsuccessstring}';
                        }
                        else {
                            message = '{$successstring}';
                        }
                        message += data.message.newversion;
                        $(data.message.key).innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                    }
                    else {
                        var message = '';
                        if (data.message.errormessage) {
                            message = data.message.errormessage;
                        } 
                        else {
                            message = '{$failurestring}';
                        }
                        $(data.message.key).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
                    }
                    processNext();
                }, function () {
                    $(element).innerHTML = '<img src="{$failureicon}" alt=":(" /> {$failurestring}';
                });
            }

            addLoadEvent( processNext );
EOJS;

$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign_by_ref('upgrades', $upgrades);
if (isset($upgrades['core'])) {
    $smarty->assign('releaseargs', array($upgrades['core']->torelease, $upgrades['core']->to));
}
$smarty->display('admin/upgrade.tpl');

?>
