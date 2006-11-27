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
define('SUBMENUITEM', 'watchlist');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$viewstring = get_string('views', 'activity');
$communitystring = get_string('communities', 'activity');
$artefactstring = get_string('artefacts', 'activity');
$monitoredstring = get_string('monitored', 'activity');

$savefailed = get_string('stopmonitoringfailed', 'activity');
$savesuccess = get_string('stopmonitoringsuccess', 'activity');

$getartefactsjson = get_config('wwwroot') . 'json/getartefacts.php';


$recursestr = '[<a href="" onClick="toggleChecked(\'tocheck-r\'); return false;">' 
    . get_string('recurseall', 'activity')
    . '</a>]';
$recursestrjs = str_replace("'", "\'", $recursestr);

$javascript = <<<JAVASCRIPT
var watchlist = new TableRenderer(
    'watchlist',
    'index.json.php', 
    [
        function(r) { 
            if (r.url) { 
                return TD(null,A({'href': r.url}, r.name));
            } 
            return TD(null, r.name);
        },
        function (r, d) {
            return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck', 'name': 'stop' + d.type + '-' + r.id}));
        },
        function (r, d) {
            if (d.type != 'communities') {
                return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck-r', 'name': 'stop' + d.type + '-' + r.id + '-recurse'}));
            }
            else {
                return '';
            }
        }
    ]
);

watchlist.type = 'views';
watchlist.statevars.push('type');
watchlist.watchlist = 1;
watchlist.statevars.push('watchlist');
watchlist.updateOnLoad();
watchlist.rowfunction = function(r, n) { return TR({'id': r.id, 'class': 'view'}); }

function changeTitle(title) {
    var titles = { 'views': '{$viewstring}', 'communities': '{$communitystring}', 'artefacts': '{$artefactstring}' };
    $('typeheader').innerHTML  = '{$monitoredstring} ' + titles[title];
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

    var d = loadJSONDoc('index.json.php', pd);
    d.addCallbacks(function (data) {
        if (data.success) {
            if (data.count > 0) {
                $('messagediv').innerHTML = '$savesuccess';
                watchlist.doupdate();
            }
        }
        if (data.error) {
            $('messagediv').innerHTML = '$savefailed (' + data.error + ')';
        }
    },
                   function () {
            $('messagediv').innerHTML = '$savefailed';
            watchlist.doupdate();
        }
    )
}

function typeChange(element) {
    watchlist.doupdate({'type': element.options[element.selectedIndex].value}); 
    changeTitle(element.options[element.selectedIndex].value); 
    $('messagediv').innerHTML = '';
    if (element.options[element.selectedIndex].value == 'communities') {
        $('recurseheader').innerHTML = '';
    }
    else {
        $('recurseheader').innerHTML = '{$recursestrjs}';
    }

}

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('site_menu', site_menu());
$smarty->assign('typechange', 'typeChange(this);');
$smarty->assign('typestr', get_string('views', 'activity'));
$smarty->assign('selectall', 'toggleChecked(\'tocheck\'); return false;');
$smarty->assign('recursestr', $recursestr);
$smarty->assign('stopmonitoring', 'stopmonitoring(this); return false;');
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('account/watchlist/index.tpl');


?>
