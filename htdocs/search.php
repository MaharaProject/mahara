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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require('init.php');
define('TITLE', get_string('search'));

// If there is no query posted, the 'results' section of the page will
// stay invisible until a query is submitted.

$query = param_variable('query','');
$noresults = get_string('noresultsfound');
$wwwroot = get_config('wwwroot');

$javascript = <<<EOF
var results = new TableRenderer(
    'searchresults',
    '{$wwwroot}json/search.php',
    []
);
results.statevars.push('query');
results.statevars.push('type');
results.emptycontent = '{$noresults}';

results.rowfunction = function(r,n,d) {
    if ( d.type == 'community' ) {
        return TR({'class':'r'+(n%2)},
                  TD(null,A({'href':'contacts/communities/view.php?id=' + r.id},r.name)));
    }
    var row = TR({'class':'r'+(n%2)},TD(null,A({'href':'user/view.php?id=' + r.id},r.name)));
    for (var i = 0; i < d.userfields.length; i++) {
        if (r[d.userfields[i]]) {
            if (d.userfields[i] == 'email') {
                appendChildNodes(row, TD(null, map(partial(DIV,null), r[d.userfields[i]])));
            }
            else {
                appendChildNodes(row, TD(null, r[d.userfields[i]]));
            }
        }
        else {
            appendChildNodes(row, TD(null, '-'));
        }
    }
    return row
}


function doSearch() {
    results.query = $('search_query').value;
    results.type  = $('search_type').options[$('search_type').selectedIndex].value;
    results.offset = 0;
    results.doupdate();
}

EOF;

if (isset($_REQUEST['query'])) {
    $javascript .= '    results.query = ' . json_encode($query) . ";\n";
    $javascript .= "    results.updateOnLoad();\n";
}

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('search.tpl');

?>
