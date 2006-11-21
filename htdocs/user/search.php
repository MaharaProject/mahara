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
require(dirname(dirname(__FILE__)).'/init.php');
require_once('pieforms/pieform.php');

/* If there is no query posted, the 'results' section of the page will
   stay invisible until a query is submitted. */

$query = @param_variable('query','');

$searchform = pieform(array(
    'name'                => 'search',
    'method'              => 'post',
    'ajaxpost'            => true,
    'ajaxsuccessfunction' => 'newsearch',
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

function search_submit($values) {
    json_reply(false,'');
}

//@todo: Show 'no results found' for an empty query.
$noresults = get_string('noresultsfound');

$javascript = <<<JAVASCRIPT
var results = new TableRenderer(
    'searchresults',
    'results.json.php',
    [
        function(r) { return TD(null,A({'href':'view.php?id=' + r.id},r.displayname)); },
        'firstname',
        'lastname',
        'email',
    ]
);

function newsearch() {
    showElement($('results'));
    results.query = $('query').value;
    results.offset = 0;
    results.doupdate();
}

results.statevars.push('query');
results.emptycontent = '{$noresults}';

JAVASCRIPT;


if (!empty($query)) {
    $equery = json_encode($query);
    $javascript .= 'results.query = ' . $equery . ";\n";
    $javascript .= "results.updateOnLoad();\n";
}

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('SEARCHFORM', $searchform);
$smarty->assign('QUERYPOSTED', !empty($query));
$smarty->display('user/search.tpl');

?>
