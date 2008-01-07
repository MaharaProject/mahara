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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/groupsiown');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'ownedgroups');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('groupsiown'));

$viewurl = get_config('wwwroot') . 'group/view.php?id=';
$editurl = get_config('wwwroot') . 'group/edit.php?id=';
$editstr = json_encode(get_string('edit'));
$edithelp = get_help_icon('core', 'groups', null, null, null, 'groupeditlink');
$deletestr = json_encode(get_string('delete'));
$confirmdelete_hasviews = json_encode(get_string('groupconfirmdeletehasviews', 'group'));
$confirmdelete = json_encode(get_string('groupconfirmdelete', 'group'));

$javascript = <<<EOF
var grouplist = new TableRenderer(
    'grouplist',
    'getgroups.json.php',
    [
     function (r) {
         return TD(null, A({'href': '{$viewurl}' + r.id}, r.name));
     },
     function (r) {
         if (r.requestcount == 0) {
             return TD(null);
         }
         return TD(null, A({'href': '{$viewurl}' + r.id + '&pending=1#members'}, r.requestcount));
     },
     function (r) {
         var help = SPAN(null);
         if (r._rownumber == 1) {
             help.innerHTML = '{$edithelp}';
         }

        var editButton = BUTTON({'type': 'button', 'class': 'button'}, {$editstr});
        connect(editButton, 'onclick', function(e) {
            e.stop();
            window.location = '{$editurl}' + r.id;
        });

        var deleteButton = BUTTON({'type':'button', 'class': 'button'}, {$deletestr});
        connect(deleteButton, 'onclick', function (e) {
            e.stop();

            var message = (r.hasviews > 0) ?
                {$confirmdelete_hasviews} :
                {$confirmdelete};

            if (!confirm(message)) {
                return;
            }
            sendjsonrequest(
                'owned.json.php',
                {
                    'action': 'delete',
                    'id': r.id
                },
                'POST',
                function (data) {
                    grouplist.doupdate();
                }
            );
        });

         return TD(null, editButton, help, deleteButton);
     }
     ]
);

grouplist.updateOnLoad();
grouplist.owned = 1;
grouplist.statevars.push('owned');

EOF;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);

$smarty->display('group/owned.tpl');

?>
