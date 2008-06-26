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
define('MENUITEM', 'groups/mygroups');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'groupfiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('groupfiles', 'artefact.file'));
safe_require('artefact', 'file');

$javascript = ArtefactTypeFileBase::get_my_files_js(param_integer('folder', null));

$groupid = param_integer('group');
$group = get_record_sql('
    SELECT g.id, g.name, g.grouptype, m.role AS userrole
    FROM {group} g INNER JOIN {group_member} m ON g.id = m.group
    WHERE g.id = ' . $groupid . ' AND m.member = ' . $USER->get('id'));

if (!$group) {
    throw new AccessDeniedException();
}

require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('docroot') . 'lib/grouptype/' . $group->grouptype . '.php');

$groupdata = json_encode($group);
$grouproles = json_encode(call_static_method('GroupType' . $group->grouptype, 'get_roles'));

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
    )
);
$smarty->assign('heading', get_string('filesfor', 'artefact.file', $group->name));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('artefact:file:index.tpl');

?>
