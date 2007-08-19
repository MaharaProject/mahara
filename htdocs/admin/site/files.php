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
 * @subpackage admin
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/adminfiles');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'adminfiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
define('TITLE', get_string('adminfiles', 'admin'));

$copyright = get_field('site_content', 'content', 'name', 'uploadcopyright');
$wwwroot = get_config('wwwroot');

$javascript = <<<JAVASCRIPT

var copyrightnotice = '{$copyright}';
var browser = new FileBrowser('filelist', '{$wwwroot}artefact/file/myfiles.json.php', {'adminfiles':true});
var uploader = new FileUploader('uploader', '{$wwwroot}artefact/file/upload.php', {'adminfiles':true}, 
                                null, null, browser.refresh, browser.fileexists);
browser.changedircallback = uploader.updatedestination;

JAVASCRIPT;

$smarty = smarty(array('tablerenderer', 
                       'artefact/file/js/file.js'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('admin/site/files.tpl');

?>
