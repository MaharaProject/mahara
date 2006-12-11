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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');

// If there is no query posted, the 'results' section of the page will
// stay invisible until a query is submitted.

$query = @param_variable('query','');

$searchform = pieform(array(
    'name'                => 'search',
    'method'              => 'post',
    'validate'            => false,
    'action'              => '',
    'elements'            => array(
        'query' => array(
            'type'           => 'text',
            'id'             => 'query',
            'title'          => get_string('query'),
            'description'    => get_string('querydescription'),
            'defaultvalue'   => $query,
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('search')
        ),
    )
));

// Empty functions for handling the search form submissions. One is for the
// form on this page and the other is for the overall search form. They're
// not actually used, instead the onsubmit event of the form is captured to
// get the results via the tablerenderer.
function search_submit($values) {
}

function searchform_submit($values) {
}

$noresults = get_string('noresultsfound');
$wwwroot = get_config('wwwroot');

$javascript = <<<EOF
var results = new TableRenderer(
    'searchresults',
    '{$wwwroot}json/usersearch.php',
    [
        function(r) { return TD(null,A({'href':'view.php?id=' + r.id},r.name)); },
    ]
);
results.statevars.push('query');
results.emptycontent = '{$noresults}';

addLoadEvent(function() {
    connect($('search'), 'onsubmit', function (e) {
        showElement($('results'));
        results.query = $('search_query').value;
        results.offset = 0;
        results.doupdate();
        e.stop();
    });


EOF;

if (isset($_REQUEST['query'])) {
    $javascript .= '    results.query = ' . json_encode($query) . ";\n";
    $javascript .= "    showElement($('results'));\n";
    $javascript .= "    results.updateOnLoad();\n";
}
$javascript .= "});\n";

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('SEARCHFORM', $searchform);
$smarty->assign('QUERYPOSTED', !empty($query));
$smarty->display('user/search.tpl');

?>
