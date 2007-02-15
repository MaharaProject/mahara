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
    logDebug(r, n, d);
    return TR(null, TD(null, r.title));
};
EOF;

if (!empty($query)) {
    $javascript .= 'results.query = ' . json_encode($query) . ";\n";
    $javascript .= "results.updateOnLoad();\n";
}

$smarty = smarty(array('tablerenderer'));
$smarty->assign('query', $query);
$smarty->assign('artefacttype', $artefacttype);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('selfsearch.tpl');

?>

