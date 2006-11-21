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
                return TD(null, A({'href': '', 'onclick': 'collapse(' + r.id + '); return false;'},
                                  IMG({'src' : d.minusicon, 'alt' : d.minusalt, 'border': 0})));
            }
            else {
                return TD(null, A({'href': '', 'onclick': 'expand(' + r.id + '); return false;'},
                                  IMG({'src' : d.plusicon, 'alt' : d.plusalt, 'border': 0})));
            }
        },
        function(r) { 
            if (r.url) { 
                return TD(null,A({'href': r.url}, r.name));
            } 
            return TD(null,r.name);
        },
        function (r) {
            return TD(null, INPUT({'type' : 'checkbox', 'class': 'tocheck', 'name': 'view-' + r.id}));
        }
    ]
);

watchlist.type = 'views';
watchlist.statevars.push('type');
watchlist.updateOnLoad();

function changeTitle(title) {
    var titles = { 'views' : '{$viewstring}', 'communities' : '{$communitystring}' };
    $('typeheader').innerHTML  = '{$monitoredstring} ' + titles[title];
}

function collapse(id) {

}

function expand(id) {

}

JAVASCRIPT;

$typechange = 'watchlist.doupdate({\'type\':this.options[this.selectedIndex].value}); changeTitle(this.options[this.selectedIndex].value);';

$smarty = smarty(array('tablerenderer'));
$smarty->assign('site_menu', site_menu());
$smarty->assign('typechange', $typechange);
$smarty->assign('typestr', get_string('viewsandartefacts', 'activity'));
$smarty->assign('selectall', 'toggleChecked(\'tocheck\'); return false;');
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('account/watchlist/index.tpl');


?>