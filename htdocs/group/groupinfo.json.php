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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'group.php');
safe_require('artefact', 'file');

$id = param_integer('id');
$group = get_record('group', 'id', $id);

$group->admins = get_column_sql("SELECT member
    FROM {group_member}
    WHERE \"group\" = ?
    AND \"role\" = 'admin'", array($group->id));

$filecounts = ArtefactTypeFileBase::count_user_files(null, $group->id, null);

$group->settingsdescription = group_display_settings($group);

$smarty = smarty_core();
$smarty->assign('group', $group);
$smarty->assign('membercount', count_records('group_member', 'group', $group->id));
$smarty->assign('viewcount', count_records('view', 'group', $group->id));
$smarty->assign('filecount', $filecounts->files);
$smarty->assign('foldercount', $filecounts->folders);
ob_start();
$smarty->display('group/groupdata.tpl');
$html = ob_get_contents();
ob_end_clean();

json_reply(false, array(
    'message' => null,
    'html' => $html,
));
