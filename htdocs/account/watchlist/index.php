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

$viewstring = get_string('viewsandartefacts', 'activity');
$communitystring = get_string('communities', 'activity');
$monitoredstring = get_string('monitored', 'activity');

$savefailed = get_string('stopmonitoringfailed', 'activity');
$savesuccess = get_string('stopmonitoringsuccess', 'activity');

$getartefactsjson = get_config('wwwroot') . 'json/getartefacts.php';

$minusicon = theme_get_image_path('minus.png');
$plusicon  = theme_get_image_path('plus.png');
$minusalt  = get_string('collapse');
$plusalt   = get_string('expand');

$javascript = <<<JAVASCRIPT
var watchlist = new TableRenderer(
    'watchlist',
    'index.json.php', 
    [
        function(r, d) {
            if (r.type == 'community') {
                return TD(null, '');
            }
            if (r.expanded) {
                return TD(null, A({'href': '', 'onclick': 'toggleExpand(' + r.id + ', \'view\'); return false;'},
                                  IMG({'src' : '{$minusicon}', 'alt' : '{$minusalt}',
                                           'border': 0, 'id' : 'viewicon-' + r.id})));
            }
            else {
                return TD(null, A({'href': '', 'onclick': 'toggleExpand(' + r.id + ', \'view\'); return false;'},
                                  IMG({'src' : '{$plusicon}', 'alt' : '{$plusalt}',
                                           'border': 0, 'id' : 'viewicon-' + r.id})));
            }
        },
        function(r) { 
            if (r.url) { 
                return TD(null,A({'href': r.url}, r.title));
            } 
            return TD(null,r.title);
        },
        function (r) {
            if (r.type == 'community') {
                return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck', 'name': 'stopcommunity-' + r.id}));
            }
            else {
                return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck', 'name': 'stopview-' + r.id}));
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
    var titles = { 'views' : '{$viewstring}', 'communities' : '{$communitystring}' };
    $('typeheader').innerHTML  = '{$monitoredstring} ' + titles[title];
}

function toggleExpand(id, type) {
    if ($(type + 'icon-' + id).src == '{$minusicon}') {
        $(type + 'icon-' + id).src = '{$plusicon}';
        $(type + 'icon-' + id).alt = '{$plusalt}';
        removeElement($(type + 'expanded' + id));
        return;
    }

    $(type + 'icon-' + id).src = '{$minusicon}';
    $(type + 'icon-' + id).alt = '{$minusalt}';

    // the first thing to do is find out if we have children
    var url = '{$getartefactsjson}';
    var tablename = type + 'table' + id;
    var newtable = TABLE({'id': tablename});
    var newrow   = TR({'class': type, 'id': type + 'expanded' + id}, TD(), TD({'colspan': 2}, newtable));
    var tr = $(type + 'icon-' + id).parentNode.parentNode.parentNode;
    insertSiblingNodesAfter(tr, newrow); 
    var newtablelist = new TableRenderer(
            tablename, 
            url,
            [
             function(r, d) {
                 if (r.expanded) {
                     return TD(null, A({'href': '', 
                                        'onclick': 'toggleExpand(' + r.id + ', \'artefact\'); return false;'},
                                       IMG({'src' : '{$minusicon}', 'alt' : '{$minusalt}',
                                            'border': 0, 'id' : 'artefacticon-' + r.id})));
                 }
                 else {
                     return TD(null, A({'href': '', 
                                        'onclick': 'toggleExpand(' + r.id + ', \'artefact\'); return false;'},
                                       IMG({'src' : '{$plusicon}', 'alt' : '{$plusalt}',
                                            'border': 0, 'id' : 'artefacticon-' + r.id})));
                 }
             },
             function(r) { 
                 if (r.url) { 
                     return TD(null,A({'href': r.url}, r.title));
                 } 
                 return TD(null,r.title);
             },
             function (r) {
                 return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck', 'name': 'stopartefact-' + r.id}));
             }
            ]
        );
    newtablelist.statevars.push('view');
    if (type == 'artefact') {
        newtablelist.statevars.push('artefact');
        newtablelist.artefact = id;
        newtablelist.view = findViewId(tr);
    }
    else {
        newtablelist.view = id;
    }
    newtablelist.watchlist = 1;
    newtablelist.statevars.push('watchlist');
    newtablelist.rowfunction = function(r, n) { return TR({'id': r.id, 'class': 'artefact'}); }
    newtablelist.paginate = false;
    newtablelist.doupdate();
}

function findViewId(row) {
    
    child = row;
    while (typeof(child.parentNode) != 'undefined' && child.parentNode != null) {
        parent = child.parentNode;
        if (hasElementClass(parent, 'view')) {
            if (parent.id.search(/viewexpanded(\d+)/) != -1) {
                return parent.id.replace(/viewexpanded/,'');
            }
        }
        child = parent;
    }

}

function stopmonitoring(form) {
    var c = 'tocheck';
    var e = getElementsByTagAndClassName(null,'tocheck',form);
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


JAVASCRIPT;

$typechange = 'watchlist.doupdate({\'type\':this.options[this.selectedIndex].value}); changeTitle(this.options[this.selectedIndex].value);';

$smarty = smarty(array('tablerenderer'));
$smarty->assign('site_menu', site_menu());
$smarty->assign('typechange', $typechange);
$smarty->assign('typestr', get_string('viewsandartefacts', 'activity'));
$smarty->assign('selectall', 'toggleChecked(\'tocheck\'); return false;');
$smarty->assign('stopmonitoring', 'stopmonitoring(this); return false;');
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('account/watchlist/index.tpl');


?>
