<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
require('init.php');
define('TITLE', get_string('selfsearch'));

$query         = param_variable('query','');
$artefacttype  = param_variable('artefacttype','all');

$enc_jsonscript = json_encode(get_config('wwwroot') . 'json/selfsearch.php');
$enc_noresults  = json_encode(get_string('noresultsfound'));
$enc_pages = json_encode(get_string('listedinpages', 'view'));

$javascript = <<<EOF
var results = new TableRenderer(
    'searchresults',
    $enc_jsonscript,
    []
);
results.statevars.push('query');
results.statevars.push('type');
results.emptycontent = {$enc_noresults};
results.rowfunction = function (r, rownumber, d) {

    var titleElement;
    if (r.links && r.links._default) {
        titleElement = [jQuery('<h3>',{'class': 'title'}), jQuery('<a>', {'href': r.links._default, 'text': r.title})];
        delete r.links._default;
    }
    else {
        titleElement = [jQuery('<h3>', {'class': 'title', 'text': r.title})];
    }

    for ( var k in r.links ) {
        var link = jQuery('<a>', {'href': r.links[k], 'text': k});
        titleElement.push(link);
    }

    if (r.views) {
        var viewsList = jQuery('<ul>');
        var viewsElement = jQuery('<div>').append(jQuery('<label>').append($enc_pages), viewsList);
        for ( var k in r.views ) {
            var link = jQuery('<a>',{'href': r.views[k]}).append(k);
            viewsList.append(jQuery('<li>').append(link));
        }
    }

    var descriptionElement = jQuery('<p>');
    descriptionElement.html(r.summary);

    return jQuery('<tr>', {'class': 'r' + (rownumber % 2)}).append(
      jQuery('<td>').append(
        titleElement, descriptionElement, viewsElement
      ))[0];
};

function dosearch(e) {
    results.query = jQuery('#search_query').val();
    results.offset = 0;

    results.doupdate();
}
EOF;

if ($query != '') {
    $javascript .= 'results.query = ' . json_encode($query) . ";\n";
    $javascript .= "results.updateOnLoad();\n";
}
else {
    $javascript .= 'results.query = \'\';';
}

$smarty = smarty(array('tablerenderer'), array(), array(), array('sidebars' => true));
$smarty->assign('query', $query);
$smarty->assign('artefacttype', $artefacttype);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('selfsearch.tpl');
