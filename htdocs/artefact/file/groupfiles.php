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
define('MENUITEM', 'groups/files');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'group.php');
safe_require('artefact', 'file');

$javascript = ArtefactTypeFileBase::get_my_files_js(param_integer('folder', null));

$groupid = param_integer('group');
if (!$group = get_record('group', 'id', $groupid, 'deleted', 0)) {
    throw new GroupNotFoundException("Couldn't find group with id $groupid");
}
if (!group_user_access($groupid)) {
    throw new AccessDeniedException();
}
define('TITLE', $group->name . ' - ' . get_string('groupfiles', 'artefact.file'));

require_once(get_config('docroot') . 'interaction/lib.php');

$groupdata = json_encode($group);
$grouproles = json_encode(array_values(group_get_role_info($groupid)));

$javascript .= <<<GROUPJS
var group = {$groupdata};
group.roles = {$grouproles};
browser.setgroup({$groupid});
uploader.setgroup({$groupid});
GROUPJS;

$smarty = smarty(
    array('tablerenderer', 'artefact/file/js/file.js'),
    array(),
    array(),
    array(
        'sideblocks' => array(
            interaction_sideblock($groupid),
        ),
        'group' => $group,
    )
);
$smarty->assign('heading', $group->name . ' - ' . get_string('Files', 'artefact.file'));
$smarty->assign('groupid', $groupid);
$smarty->assign('grouptabs', group_get_menu_tabs($group));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('artefact:file:index.tpl');

?>
