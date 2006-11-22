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
define('MENUITEM', 'account');
define('SUBMENUITEM', 'activity');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$types = get_records_array('activity_type', 'admin', 0);

$readsavefail = get_string('failedtomarkasread', 'activity');
$readsave = get_string('markedasread', 'activity');

$javascript = <<<JAVASCRIPT
var activitylist = new TableRenderer(
    'activitylist',
    'index.json.php', 
    [
        function(r) { 
            if (r.url) { 
                return TD(null,A({'href': r.url}, r.message));
            } 
            return TD(null,r.message);
        },
        'type',
        'date',
        function (r, d) {
            if (r.read == 1) {
                return TD(null,IMG({'src' : d.star, 'alt' : d.unread}));
            }
            return TD(null);
        },
        function (r) {
            if (r.read == 0) {
                return TD(null, INPUT({'type' : 'checkbox', 'class' : 'tocheck', 'name' : 'unread-' + r.id}));
            }
            return TD(null);
        }
    ]
);

activitylist.type = 'all';
activitylist.statevars.push('type');
activitylist.updateOnLoad();

function markread(form) {
    
    var c = 'tocheck';
    var e = getElementsByTagAndClassName(null,'tocheck',form);
    var pd = {};
    
    for (cb in e) {
        if (e[cb].checked == true) {
            pd[e[cb].name] = 1;
        }
    }

    pd['markasread'] = 1;
    
    var d = loadJSONDoc('index.json.php', pd);
    d.addCallbacks(function (data) {
        if (data.success) {
            if (data.count > 0) {
                $('messagediv').innerHTML = '$readsave';
                activitylist.doupdate();
            }
        }
        if (data.error) {
            $('messagediv').innerHTML = '$readsavefail(' + data.error + ')';
        }
    },
                   function () {
            $('messagediv').innerHTML = '$readsavefail';
            activitylist.doupdate();
        }
    )
}

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('site_menu', site_menu());
$smarty->assign('selectall', 'toggleChecked(\'tocheck\'); return false;');
$smarty->assign('markread', 'markread(this); return false;');
$smarty->assign('typechange', 'activitylist.doupdate({\'type\':this.options[this.selectedIndex].value});');
$smarty->assign('types', $types);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('account/activity/index.tpl');
?>
