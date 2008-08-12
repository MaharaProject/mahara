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
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/institutionfiles');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionfiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once('institution.php');

$institution = param_alphanum('institution', false);

define('TITLE', get_string('institutionfiles', 'admin'));

$s = institution_selector_for_page($institution,
                                   get_config('wwwroot') . 'artefact/file/institutionfiles.php');

$institution = $s['institution'];
$smarty = smarty(array('tablerenderer', 'artefact/file/js/file.js'));

if ($institution === false) {
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

if (!$USER->can_edit_institution($institution)) {
    throw new AccessDeniedException();
}

$javascript = ArtefactTypeFileBase::get_my_files_js(param_integer('folder', null));
$institutionstr = json_encode($institution);
$javascript .= <<<JS
browser.source += '?institution={$institution}';
browser.createfolderscript += '?institution={$institution}';
uploader.uploadscript += '?institution={$institution}';
JS;


$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs'] . $javascript);
$smarty->assign('heading', TITLE);
$smarty->display('artefact:file:index.tpl');

?>
