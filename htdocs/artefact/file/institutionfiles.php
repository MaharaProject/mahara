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
require_once(get_config('libroot') . 'institution.php');

$institution = param_alphanum('institution', false);

define('TITLE', get_string('institutionfiles', 'admin'));

$s = institution_selector_for_page($institution,
                                   get_config('wwwroot') . 'artefact/file/institutionfiles.php');

$institution = $s['institution'];

$form = pieform(files_form(null, $institution));
$js = files_js();

$smarty = smarty();

if ($institution === false) {
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

if (!$USER->can_edit_institution($institution)) {
    throw new AccessDeniedException();
}

$smarty->assign('institution', $institution);
$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs'] . $js);
$smarty->assign('heading', TITLE);
$smarty->display('artefact:file:files.tpl');

?>
