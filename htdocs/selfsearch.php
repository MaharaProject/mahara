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
define('TITLE', get_string('selfsearch'));

$query         = param_variable('query','');
$artefacttype  = param_variable('artefacttype','all');

$enc_jsonscript = json_encode(get_config('wwwroot') . 'json/selfsearch.php');
$enc_noresults  = json_encode(get_string('noresultsfound'));

$javascript = <<<EOF
var results = new TableRenderer(
    'selfsearchresults',
    $enc_jsonscript,
    []
);
results.statevars.push('query');
results.statevars.push('type');
results.emptycontent = {$enc_noresults};
results.rowfunction = function (r, n, d) {

    var titleElement
    if (r.links._default) {
        titleElement = [H3(null, A({'href': r.links._default}, r.title))];
        delete r.links._default;
    }
    else {
        titleElement = [H3(null, A(null, r.title))];
    }

    for ( var k in r.links ) {
        var button = BUTTON(null, k);
        connect(button, 'onclick', partial(
            function (link) { document.location.href = link },
            r.links[k]
        ));
        titleElement.push(button);
    }

    var descriptionElement = P(null);
    descriptionElement.innerHTML = r.summary;

    return TR(null, TD(null,
        titleElement,
        descriptionElement
    ));
};

function dosearch(e) {
    results.query = $('search_query').value;
    results.offset = 0;

    results.doupdate();
}
EOF;

if (!empty($query)) {
    $javascript .= 'results.query = ' . json_encode($query) . ";\n";
    $javascript .= "results.updateOnLoad();\n";
}
else {
    $javascript .= 'results.query = \'\';';
}

$smarty = smarty(array('tablerenderer'));
$smarty->assign('query', $query);
$smarty->assign('artefacttype', $artefacttype);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('selfsearch.tpl');

?>

