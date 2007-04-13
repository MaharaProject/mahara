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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'mycontacts');
define('SUBMENUITEM', 'mycommunities');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'contacts');
define('SECTION_PAGE', 'communities');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('mycommunities'));

$viewurl = get_config('wwwroot') . 'contacts/communities/view.php?id=';
$leftsuccess = get_string('leftcommunity');
$leftfailed = get_string('leftcommunityfailed');

$javascript = <<<EOF
var communitylist = new TableRenderer(
    'communitylist',
    'getcommunities.json.php',
    [
     function (r) {
         return TD(null, A({'href': '{$viewurl}' + r.id}, r.name));
     },
     function (r) {
         if (r.jointype == 'controlled') {
             return TD(null);
         }
         return TD(null, A({'href': '', 'onclick': 'leaveCommunity(' + r.id + '); return false;'}, '[X]'));
     }
     ]
);

communitylist.updateOnLoad();

function leaveCommunity(id) {
    var pd = {'leave': id}
    sendjsonrequest('communityleave.json.php', pd, 'GET', function (data) {
        if (!data.error) {
            communitylist.doupdate();
        }
    }, function () {
        watchlist.doupdate();
    });
}

EOF;
$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('contacts/communities/index.tpl');

?>
