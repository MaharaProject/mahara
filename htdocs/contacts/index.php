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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'mycontacts');
define('SUBMENUITEM', 'myfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('myfriends'));

$wwwroot    = get_config('wwwroot');
$profileurl = $wwwroot . 'thumb.php?type=profileicon&size=40x40&id=';
$viewurl    = $wwwroot . 'user/view.php?id=';

$viewsavailable = get_string('viewsavailable');
$viewavailable = get_string('viewavailable');
$remove = get_string('removefromfriendslist');
$accept = get_string('accept');
$reject = get_string('reject');
$allviews = get_string('allviews');
$friendcontrolfailed = get_string('friendlistfailure');
$enc_confirm_remove = json_encode(get_string('confirmremovefriend'));

$inlinejs = <<<EOF
var friendslist = new TableRenderer(
    'friendslist',
    'index.json.php',
    [
     function (r) {
         return TD(null, IMG({'src': '{$profileurl}' + r.id}));
     },
     function (r) {
         return TD(null, A({'href': '{$viewurl}' + r.id}, r.name));
     },
     function (r, d) {
         if (d.pending) {
             var reason = '';
             if (r.reason) {
                 reason = r.reason;
             }
             return [ TD(null, reason), 
                      TD(null, A({'href': '', 'onclick': 'friendControl(\'accept\', ' + r.id + '); return false'}, '{$accept}')),
                      TD({'id': 'pending-' + r.id}, 
                         A({'href': '', 'onclick': 'showReject(' + r.id + '); return false'}, '{$reject}')) ];
            
         }
         else {
             var viewcol;
             if (typeof(d.views) == 'object' && d.views[r.id] && countKeys(d.views[r.id]) > 0) {
                 var len = countKeys(d.views[r.id]);
                 var views = '';
                 if (len == 1) {
                     views = len + ' {$viewavailable}';
                 }
                 else {
                     views = len + ' {$viewsavailable}';
                 }
                 var theLink = A({'href': ''}, views);
                 connect(theLink, 'onclick', partial(expandViews, d.views[r.id], r.id));
                 connect(theLink, 'onclick', function (e) { e.stop(); } );
                 viewcol = TD({'id': 'friend-' + r.id}, theLink);
             }
             else {
                 views = '0 {$viewsavailable}';
                 viewcol = TD(null, views);
             }
             return [ viewcol, TD(null, A({'href': '', 'onclick': 'friendControl(\'remove\', ' + r.id + '); return false;'}, '{$remove}')) ];
         }
     }
    ]
);                                
friendslist.pending = 0;
friendslist.statevars.push('pending');
friendslist.updateOnLoad();

function friendControl(type, id, reason) {
    var pd = {'id': id, 'control': 1};

    if (type == 'remove' && !confirm({$enc_confirm_remove})) {
        logDebug(type, id);
        return false;
    }

    if (type == 'reject') {
        type = 'accept';
        pd['rejectsubmit'] = 'reject';
        if (reason) {
            pd['rejectreason'] = reason;
        }
    }
    pd['type'] = type;

    var d = loadJSONDoc('index.json.php', pd);
    d.addCallbacks(
        function (data) {
            $('messagediv').innerHTML = data.message;
        },
        function () {
            $('messagediv').innerHTML = '{$friendcontrolfailed}';
        }
    );
    friendslist.doupdate();
}

function showReject(id) {
    if ($('row-reject-' + id)) {
        removeElement('row-reject-' + id);
        return;
    }
    var tr = TR({'id': 'row-reject-' + id}, 
                TD(null),
                TD({'colspan': 3}, 
                   INPUT({'type': 'text', 'name': 'reject-reason-' + id, 'id': 'reject-reason-' + id}),
                   INPUT({'type': 'button', 'value': '{$reject}', 
                          'onclick': 'friendControl(\'reject\', ' + id + ', $(\'reject-reason-' + id + '\').value);'})));
    insertSiblingNodesAfter($('pending-' + id).parentNode, tr);
}

function expandViews(views, id) {
    if ($('row-views-' + id)) {
        removeElement('row-views-' + id);
        forEach (views, function (view) {
            removeElement('row-views-view-' + view.id);
        });
        return false;
    }
    var ts = [];
    forEach (views, function(view) {
        ts.push(TR({'id': 'row-views-view-' + view.id},
                   TD(null),
                   TD({'colspan': 3},
                      A({'href': '{$wwwroot}/view/view.php?view=' + view.id}, view.title))));
    });
    ts.push(TR({'id': 'row-views-' + id}, TD(null), 
               TD({'colspan': 3}, 
                  A({'href':  '{$wwwroot}/user/view.php?id=' + id}, '{$allviews}'))));
    insertSiblingNodesAfter($('friend-' + id).parentNode, ts);
    return false;
}

EOF;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('pendingchange', '$(\'messagediv\').innerHTML=\'\';friendslist.doupdate({\'pending\':this.options[this.selectedIndex].value});');
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->display('contacts/index.tpl');

?>
