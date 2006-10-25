<?php
/**
 * This program is part of mahara
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
define('INSTALLER', 1);

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'upgrade.php');

$upgrades = check_upgrades();
if (!$upgrades) {
    die_info(get_string('noupgrades', 'admin'));
}

$js = 'var todo = ' . json_encode(array_keys($upgrades)) . ";\n";
$loadingicon = theme_get_image_path('loading.gif');
$successicon = theme_get_image_path('success.gif');
$failureicon = theme_get_image_path('failure.gif');

$loadingstring = get_string('upgradeloading', 'admin');
$successstring = get_string('upgradesuccess', 'admin');
$failurestring = get_string('upgradefailure', 'admin');

$js .= <<< EOJS
            function processNext() {
                var element = todo.shift();

                if ( ! element ) {
                    // we're done
                    // @todo this needs work:
                    //   - should only hit upgrade.json.php with install message
                    //     if we are actually installing - can check $upgrades
                    //     in this file for that
                    loadJSONDoc('upgrade.json.php', { 'install' : 1 });
                    // @todo do as a deferred on the above call
                    $('finished').style.display = 'block';
                    return;
                }

                var d = loadJSONDoc('upgrade.json.php', { 'name': element });

                $(element).innerHTML = '<img src="{$loadingicon}" alt="{$loadingstring}" />';

                d.addCallback(function (data) {
                    if ( data.success ) {
                        var message = '{$successstring}' + data.newversion;
                        $(data.key).innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                    }
                    else {
                        var message = '';
                        if (data.errormessage) {
                            message = data.errormessage;
                        } 
                        else {
                            message = '{$failurestring}';
                        }
                        $(data.key).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
                    }
                    processNext();
                });
                d.addErrback(function () {
                    $(element).innerHTML = '<img src="{$failureicon}" alt=":(" /> {$failurestring}';
                });
            }

            addLoadEvent( processNext );
EOJS;

$smarty = smarty(array('mochikit'));
$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign_by_ref('upgrades', $upgrades);
$smarty->display('admin/upgrade.tpl');

?>
