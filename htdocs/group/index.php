<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/groupsimin');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'groups');
define('SECTION_PAGE', 'groups');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('groupsimin'));

$viewurl = get_config('wwwroot') . 'group/view.php?id=';
$leftsuccess = get_string('leftgroup');
$leftfailed = get_string('leftgroupfailed');

$javascript = <<<EOF
var grouplist = new TableRenderer(
    'grouplist',
    'getgroups.json.php',
    [
     function (r) {
         return TD(null, A({'href': '{$viewurl}' + r.id}, r.name));
     },
     function (r) {
         if (r.jointype == 'controlled') {
             return TD(null);
         }
         return TD(null, A({'href': '', 'onclick': 'leaveGroup(' + r.id + '); return false;'}, '[X]'));
     }
     ]
);

grouplist.updateOnLoad();

function leaveGroup(id) {
    var pd = {'leave': id}
    sendjsonrequest('groupleave.json.php', pd, 'GET', function (data) {
        if (!data.error) {
            grouplist.doupdate();
        }
    });
}

EOF;
$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('group/index.tpl');

?>
