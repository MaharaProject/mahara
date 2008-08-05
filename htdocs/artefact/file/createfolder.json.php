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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');

json_headers();

$parentfolder   = param_variable('parentfolder', null); // id of parent artefact
$title          = param_variable('name');
$description    = param_variable('description', null);
$tags           = param_variable('tags', null);
$collideaction  = param_variable('collideaction', 'fail');
$institution    = param_alpha('institution', null);
$group          = param_integer('group', null);

$data = new StdClass;
if ($parentfolder) {
    $data->parent = (int) $parentfolder;
}
$data->title = $title;
$data->description = $description;
$data->tags = $tags;
$data->owner = null;
if ($institution) {
    $data->institution = $institution;
} else if ($group) {
    require_once(get_config('docroot') . 'artefact/lib.php');
    require_once(get_config('docroot') . 'lib/group.php');
    if ($parentfolder && !$USER->can_edit_artefact(artefact_instance_from_id($parentfolder))) {
        json_reply('local', get_string('cannoteditfolder', 'artefact.file'));
    } else if (!$parentfolder && !group_user_access($group)) {
        json_reply('local', get_string('usernotingroup', 'mahara'));
    }
    $data->group = $group;
    $data->rolepermissions = json_decode(param_variable('permissions'));
} else {
    $data->owner = $USER->get('id');
}

if ($oldid = ArtefactTypeFileBase::file_exists($data->title, $data->owner, $parentfolder, $institution, $group)) {
    if ($collideaction == 'replace') {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $obj = artefact_instance_from_id($oldid);
        $obj->delete();
    }
    else {
        json_reply('local', get_string('fileexists', 'artefact.file'));
    }
}

$f = new ArtefactTypeFolder(0, $data);
$f->set('dirty', true);
$f->commit();

json_reply(false, get_string('foldercreated', 'artefact.file'));


?>
