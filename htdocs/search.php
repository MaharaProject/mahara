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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require('init.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'search');
define('SECTION_PAGE', 'search');
define('TITLE', get_string('search'));

// If there is no query posted, the 'results' section of the page will
// stay invisible until a query is submitted.

$query = param_variable('query','');
$noresults = get_string('noresultsfound');
$wwwroot = get_config('wwwroot');

safe_require('artefact', 'internal');
$userfields = ArtefactTypeProfile::get_public_fields();
$userfieldstrings = array(get_string('name'));
foreach ($userfields as $k => $v) {
    $userfieldstrings[] = get_string($k, 'artefact.internal');
}
$userfields = json_encode(array_keys($userfields));
$ncols = count($userfieldstrings);
$userfieldstrings = json_encode($userfieldstrings);

$javascript = <<<EOF
var userfields = {$userfields};
var userfieldstrings = {$userfieldstrings};

var results = new TableRenderer(
    'searchresults',
    '{$wwwroot}json/search.php',
    []
);
results.statevars.push('query');
results.statevars.push('type');
results.emptycontent = '{$noresults}';
results.updatecallback = function (d) {
    if (d.type == 'user') {
        results.linkspan = {$ncols};
        if (!$('userfields')) {
            appendChildNodes(results.thead, TR({'id':'userfields'},
                                               map(partial(TH, null), userfieldstrings)));
        }
    }
    else {
        results.linkspan = 1;
        removeElement('userfields');
        if ($('userfields')) {
            removeElement('userfields');
        }
    }
}

results.rowfunction = function(r,n,d) {
    if ( d.type == 'group' ) {
        return TR({'class':'r'+(n%2)},
                  TD(null,A({'href':'group/view.php?id=' + r.id},r.name)));
    }
    var row = TR({'class':'r'+(n%2)},TD(null,A({'href':'user/view.php?id=' + r.id},r.name)));
    for (var i = 0; i < userfields.length; i++) {
        if (r[userfields[i]]) {
            appendChildNodes(row, TD(null, r[userfields[i]]));
        }
        else {
            appendChildNodes(row, TD(null));
        }
    }
    return row;
}


function doSearch() {
    results.query = $('search_query').value;
    results.type  = $('search_type').options[$('search_type').selectedIndex].value;
    results.offset = 0;
    results.doupdate();
}

EOF;

$smarty = smarty(array('tablerenderer'));

if (isset($_REQUEST['query'])) {
    $javascript .= '    results.query = ' . json_encode($query) . ";\n";
    $javascript .= "    results.updateOnLoad();\n";
    $smarty->assign('search_query_value', $query);
}

$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('search.tpl');

?>
