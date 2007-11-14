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

json_headers();

global $USER;

$parentfolder = param_variable('parentfolder', null); // id of parent artefact
$id = param_integer('id');
$name = param_variable('name');
$description = param_variable('description');
$tags = param_variable('tags');
$collideaction = param_variable('collideaction', 'fail');
$adminfiles = param_boolean('adminfiles', false);
$owner = $USER->get('id');

if ($existingid = ArtefactTypeFileBase::file_exists($name, $owner, $parentfolder, $adminfiles)) {
    if ($existingid != $id) {
        if ($collideaction == 'replace') {
            log_debug('deleting ' . $existingid);
            $copy = artefact_instance_from_id($existingid);
            $copy->delete();
        }
        else {
            json_reply('local', get_string('fileexists', 'artefact.file'));
        }
    }
}

$artefact = artefact_instance_from_id($id);
$artefact->set('title', $name);
$artefact->set('description', $description);
$artefact->set('tags', preg_split("/\s*,\s*/", trim($tags)));
$artefact->set('adminfiles', (int) $adminfiles);
$artefact->set('owner', $owner);
$artefact->commit();

json_reply(false, get_string('changessaved', 'artefact.file'));

?>
