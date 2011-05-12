<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/files');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'groupfiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'group.php');
safe_require('artefact', 'file');

define('GROUP', param_integer('group'));
$group = group_current_group();

if (!$role = group_user_access($group->id)) {
    throw new AccessDeniedException();
}
define('TITLE', $group->name . ' - ' . get_string('groupfiles', 'artefact.file'));

require_once(get_config('docroot') . 'interaction/lib.php');

$pagebase = get_config('wwwroot') . 'artefact/file/groupfiles.php?group=' . $group->id;
$form = pieform(ArtefactTypeFileBase::files_form($pagebase, $group->id));
$js = ArtefactTypeFileBase::files_js();

$smarty = smarty();
$smarty->assign('heading', $group->name);
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:file:files.tpl');
