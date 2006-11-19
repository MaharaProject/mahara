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
require_once('form.php');

//@todo: Show 'no results found' for an empty query.
$query = param_variable('query');

//@todo: Add form with search box 
$searchform = form(array(
    'name'                => 'search',
    'method'              => 'post',
    'ajaxpost'            => true,
    'ajaxsuccessfunction' => 'updatesearch()',
    'action'              => '',
    'elements'            => array(
        'query' => array(
            'type'           => 'text',
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
    json_reply(false,get_string('querysubmitted'));
}

$equery = json_encode($query);

$javascript = <<<JAVASCRIPT
var results = new TableRenderer(
    'searchresults',
    'results.json.php',
    [
        function(r) { return TD(null,A({'href':'viewuser.php?id=' + r.id},r.displayname)); },
        'institution',
    ]
);

function updatesearch() {
    results.query = $('query').value;
    results.doupdate();
}

results.query = {$equery};
results.statevars.push('query');
results.updateOnLoad();

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('SEARCHFORM', $searchform);
$smarty->display('results.tpl');

?>
