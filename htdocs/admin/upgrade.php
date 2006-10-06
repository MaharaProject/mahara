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

define('INTERNAL',1);

require(dirname(dirname(__FILE__)).'/init.php');

$upgrades = check_upgrades();

$js = 'var todo = ' . json_encode(array_keys($upgrades)) . ";\n";
$js .= <<< EOJS
            function processNext() {
                var element = todo.shift();

                if ( ! element ) {
                    // we're done
                    return;
                }

                var d = loadJSONDoc('upgrade.json.php', { 'name': element });

                $(element).innerHTML = 'working... ';

                d.addCallback(function (data) {
                    if ( data.success ) {
                        $(data.key).innerHTML = 'Done! Upgraded to version ' + data.newversion;
                    }
                    else {
                        $(data.key).innerHTML = 'Poo, Error: ' + data.errormessage;
                    }
                    processNext();
                });
            }

            addLoadEvent( processNext );
EOJS;

// @todo<nigel>: given that this is PHP5, I am unsure of the need for & here - will check this out
$smarty = &smarty(array('mochikit'));
$smarty->assign('INLINEJAVASCRIPT',$js);


$smarty->assign_by_ref('upgrades',$upgrades);
$smarty->display('admin/upgrade.tpl');



?>
