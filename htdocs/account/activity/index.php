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
if ($USER->get('admin')) {
    $admintypes = get_records_array('activity_type');
    $types[] = (object)array('name' => 'adminmessages');
}

$readsavefail = get_string('failedtomarkasread', 'activity');
$readsave = get_string('markedasread', 'activity');
$morestr = get_string('more...');

$javascript = <<<JAVASCRIPT
var activitylist = new TableRenderer(
    'activitylist',
    'index.json.php', 
    [
        function(r) { 
            if (r.message) {
                var messagemore = DIV({'id' : 'message-' + r.id, 'style': 'display:none'});
                messagemore.innerHTML = r.message;
                if (r.url) {
                    appendChildNodes(messagemore, BR(null), A({'href' : r.url, 'class': 's'}, '{$morestr}'));
                }
                return TD(null, A({'href': '', 'onclick': 'showHideMessage(' + r.id + '); return false;'}, r.subject),
                          messagemore);
            }
            else if (r.url) { 
                return TD(null, A({'href': r.url}, r.subject));
            } 
            else {
                return TD(null, r.subject);
            }
        },
        'type',
        'date',
        function (r, d) {
            if (r.read == 1) {
                return TD({'class': 'center'},IMG({'src' : d.star, 'alt' : d.unread}));
            }
            else {
                return TD({'class': 'center'}, INPUT({'type' : 'checkbox', 'class' : 'tocheck', 'name' : 'unread-' + r.id}));
            }
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
    
    sendjsonrequest('index.json.php', pd, 'GET', function (data) {
        if (!data.error) {
            if (data.count > 0) {
                activitylist.doupdate();
                var oldcount = parseInt($('headerunreadmessagecount').innerHTML);
                var newcount = (oldcount - data.count);
                var messagenode = $('headerunreadmessages');
                if (newcount == 1) { // jump through hoops to change between plural and singular
                    messagenode.innerHTML = get_string('unreadmessage');
                } 
                else {
                    messagenode.innerHTML = get_string('unreadmessages');
                }
                $('headerunreadmessagecount').innerHTML = newcount;
            }
        }
    }, function () {
        activitylist.doupdate();
    });
}

function showHideMessage(id) {
    if (getStyle('message-' + id, 'display') == 'none') {
        showElement('message-' + id);
    }
    else {
        hideElement('message-' + id);
    }
}

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('selectall', 'toggleChecked(\'tocheck\'); return false;');
$smarty->assign('markread', 'markread(this); return false;');
$smarty->assign('typechange', 'activitylist.doupdate({\'type\':this.options[this.selectedIndex].value});');
$smarty->assign('types', $types);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('account/activity/index.tpl');
?>
