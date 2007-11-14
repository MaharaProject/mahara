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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
global $USER;

json_headers();

$parentfolder   = param_variable('parentfolder', null); // id of parent artefact
$title          = param_variable('name');
$description    = param_variable('description', null);
$tags           = param_variable('tags', null);
$collideaction  = param_variable('collideaction', 'fail');
$adminfiles     = param_boolean('adminfiles', false);

$data = new StdClass;
if ($parentfolder) {
    $data->parent = (int) $parentfolder;
}
$data->title = $title;
$data->description = $description;
$data->tags = $tags;
$data->owner = $USER->get('id');
$data->adminfiles = (int)$adminfiles;

if ($oldid = ArtefactTypeFileBase::file_exists($data->title, $data->owner, $parentfolder, $adminfiles)) {
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
