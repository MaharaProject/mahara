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
 * @subpackage artefact-file
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myfiles');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');

$strings = array('nofilesfound');
$getstring = array();
foreach ($strings as $string) {
    $getstring[$string] = "'" . get_string($string) . "'";
}

$javascript = <<<JAVASCRIPT
var filelist = new TableRenderer(
    'filelist',
    'myfiles.json.php',
    [
     formatname,
     function (r) { return TD(null, '[' + r.type + ']'); },
    ]
);

function formatname(r) {
    if (r.type == 'file') {
        var cell = r.name;
    }
    if (r.type == 'folder') {
        var link = A({'href':'scuzzbag'},r.name);
        link.onclick = function () { stop(); filelist.doupdate({'folder':r.id}); return false; };
        var cell = link;
    }
    return TD(null, cell);
}

filelist.statevars.push('folder');
filelist.emptycontent = {$getstring['nofilesfound']};
filelist.paginate = false;
filelist.updateOnLoad();

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);

$smarty->display('artefact:file:index.tpl');

?>
