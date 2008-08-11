<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/sitefiles');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'sitefiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
define('TITLE', get_string('sitefiles', 'admin'));

$copyright = get_field('site_content', 'content', 'name', 'uploadcopyright');
$wwwroot = get_config('wwwroot');

$javascript = <<<JAVASCRIPT

var copyrightnotice = '{$copyright}';
var browser = new FileBrowser('filelist', '{$wwwroot}artefact/file/myfiles.json.php', {'institution':'mahara'});
var uploader = new FileUploader('uploader', '{$wwwroot}artefact/file/upload.php', {'institution':'mahara'}, 
                                null, null, browser.refresh, browser.fileexists);
browser.changedircallback = uploader.updatedestination;

JAVASCRIPT;

$smarty = smarty(array('tablerenderer', 
                       'artefact/file/js/file.js'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('descriptionstrargs', array('<a href="' . get_config('wwwroot') . 'admin/site/menu.php">', '</a>'));
$smarty->assign('heading', get_string('sitefiles', 'admin'));
$smarty->display('admin/site/files.tpl');

?>
