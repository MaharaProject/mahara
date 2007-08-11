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
define('MENUITEM', 'settings/mywatchlist');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'watchlist');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$viewstring = get_string('views', 'activity');
$groupstring = get_string('groups', 'activity');
$artefactstring = get_string('artefacts', 'activity');
$monitoredstring = get_string('monitored', 'activity');
$allusersstring = get_string('allusers');

$andchildren = ' * ' . get_string('andchildren', 'activity');

$wwwroot = get_config('wwwroot');

$javascript = <<<JAVASCRIPT
var watchlist = new TableRenderer(
    'watchlist',
    'index.json.php', 
    [
        function(r, d) { 
            var url = '';
            if (d.type == 'groups') {
                url = '{$wwwroot}/contacts/groups/view.php?id=' + r.id;
            }
            else if (d.type == 'views') {
                url = '{$wwwroot}/view/view.php?view=' + r.id;
            }
            else {
                url = '{$wwwroot}/view/view.php?view=' + r.view + '&artefact=' + r.id;
            }
            var star = '';
            if (r.recurse) {
                star = ' *';
            }
            return TD(null, A({'href': url}, r.name), star);
        },
        function (r, d) {
            return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck', 'name': 'stop' + d.type + '-' + r.id}));
        },
    ]
);

watchlist.type = 'views';
watchlist.statevars.push('type');
watchlist.watchlist = 1;
watchlist.statevars.push('watchlist');
watchlist.updateOnLoad();
watchlist.rowfunction = function(r, n) { return TR({'id': r.id, 'class': 'view r' + (n % 2)}); }

function changeTitle(title) {
    var titles = { 'views': '{$viewstring}', 'groups': '{$groupstring}', 'artefacts': '{$artefactstring}' };
    $('typeheader').innerHTML  = '{$monitoredstring} ' + titles[title];
    if (title != 'groups') {
        $('typeandchildren').innerHTML = '{$andchildren}';
    }
    else {
        $('typeandchildren').innerHTML = '';
    }
}

function stopmonitoring(form) {
    var e1 = getElementsByTagAndClassName(null,'tocheck',form);
    var e2 = getElementsByTagAndClassName(null,'tocheck-r',form);
    var e = concat(e1, e2);
    var pd = {};
    
    for (cb in e) {
        if (e[cb].checked == true) {
            pd[e[cb].name] = 1;
        }
    }

    pd['stopmonitoring'] = 1;

    sendjsonrequest('index.json.php', pd, 'GET', function (data) {
        if (data.count > 0) {
            watchlist.doupdate();
        }
    }, function () {
        watchlist.doupdate();
    });
}

function statusChange() {
    var typevalue = $('type').options[$('type').selectedIndex].value;
    var uservalue;
    if ($('user').disabled == true) {
        uservalue = undefined;
    } 
    else {
        uservalue = getNodeAttribute($('user').options[$('user').selectedIndex], 'value');
    }

    if (uservalue) {
        watchlist.doupdate({'type': typevalue, 'user': uservalue});
    }
    else {
        watchlist.doupdate({'type': typevalue});
    }
    changeTitle(typevalue); 
    $('messagediv').innerHTML = '';
    if (typevalue == 'groups') {
        $('user').options.length = 0;
        $('user').disabled = true;
    }
    else {
        var pd = {'userlist': typevalue};
        sendjsonrequest('index.json.php', pd, 'GET', function (data) {
            var userSelect = $('user');
            var newOptions = new Array()
            var opt = OPTION(null, '{$allusersstring}');
            if (!uservalue) {
                opt.selected = true;
            }
            newOptions.push(opt);
            forEach (data.users, function (u) {
                var opt = OPTION({'value': u.id}, u.name);
                if (uservalue == u.id) {
                    opt.selected = true;
                }
                newOptions.push(opt);
            });
            userSelect.disabled = false;
            replaceChildNodes(userSelect, newOptions);
        });
    }
}

JAVASCRIPT;

$sql = 'SELECT DISTINCT u.* 
        FROM {usr} u
        JOIN {view} v ON v.owner = u.id 
        JOIN {usr_watchlist_view} w ON w.view = v.id
        WHERE w.usr = ?';

if (!$viewusers = get_records_sql_array($sql, array($USER->get('id')))) {
    $viewusers = array();
}

$smarty = smarty(array('tablerenderer'));
$smarty->assign('viewusers', $viewusers);
$smarty->assign('typestr', get_string('views', 'activity'));
$smarty->assign('selectall', 'toggleChecked(\'tocheck\'); return false;');
$smarty->assign('stopmonitoring', 'stopmonitoring(this); return false;');
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('account/watchlist/index.tpl');


?>
