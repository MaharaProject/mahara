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
define('TITLE', get_string('myfiles', 'artefact.file'));
safe_require('artefact', 'file');

$copyright = get_field('site_content', 'content', 'name', 'uploadcopyright');

$javascript = <<<JAVASCRIPT

var copyrightnotice = '{$copyright}';
var browser = new FileBrowser('filelist', 'myfiles.json.php');
var uploader = new FileUploader('uploader', 'upload.php', {}, null, null,
                                browser.refresh, browser.fileexists);
browser.changedircallback = uploader.updatedestination;

JAVASCRIPT;

$smarty = smarty(
    array('tablerenderer', 'artefact/file/js/file.js'),
    array(),
    array(),
    array(
        'sideblocks' => array(
            array(
                'name'   => 'quota',
                'weight' => -10,
                'data'   => array(),
            ),
        ),
    )
);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('artefact:file:index.tpl');

?>
