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

json_headers();

$artefactid  = param_integer('artefact');    // Artefact being moved
$newparentid = param_integer('newparent');   // Folder to move it to

require_once(get_config('docroot') . 'artefact/lib.php');
$artefact = artefact_instance_from_id($artefactid);

global $USER;
$userid = $USER->get('id');

if ($userid != $artefact->get('owner')) {
    json_reply(true, get_string('movefailednotowner', 'artefact.file'));
}
if (!in_array($artefact->get('artefacttype'), PluginArtefactFile::get_artefact_types())) {
    json_reply(true, get_string('movefailednotfileartefact', 'artefact.file'));
}

if ($newparentid > 0) {
    if ($newparentid == $artefactid) {
        json_reply(true, get_string('movefaileddestinationinartefact', 'artefact.file'));
    }
    if ($newparentid == $artefact->get('parent')) {
        json_reply(false, get_string('filealreadyindestination', 'artefact.file'));
    }
    $newparent = artefact_instance_from_id($newparentid);
    if ($userid != $newparent->get('owner')) {
        json_reply(true, get_string('movefailednotowner', 'artefact.file'));
    }
    if ($newparent->get('artefacttype') != 'folder') {
        json_reply(true, get_string('movefaileddestinationnotfolder', 'artefact.file'));
    }
    $nextparentid = $newparent->get('parent');
    while (!empty($nextparentid)) {
        if ($nextparentid != $artefactid) {
            $ancestor = artefact_instance_from_id($nextparentid);
            $nextparentid = $ancestor->get('parent');
        } else {
            json_reply(true, get_string('movefaileddestinationinartefact', 'artefact.file'));
        }
    }
} else { // $newparentid === 0
    if ($artefact->get('parent') == null) {
        json_reply(false, get_string('filealreadyindestination', 'artefact.file'));
    }
    $newparentid = null;
}

if ($artefact->move($newparentid)) {
    json_reply(false, array('message' => null));
}
json_reply(true, get_string('movefailed', 'artefact.file'));

?>
