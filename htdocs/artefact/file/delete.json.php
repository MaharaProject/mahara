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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

try {
    $fileid = param_integer('id');
}
catch (ParameterException $e) {
    json_reply('missingparameter',get_string('missingparameter'));
}

$prefix = get_config('dbprefix');

$filerecord = get_record_sql('SELECT a.artefacttype, a.owner, f.name
      FROM ' . $prefix . 'artefact a
      JOIN ' . $prefix . 'artefact_file_files f ON a.id = f.artefact
      WHERE a.id = ' . $fileid);

if ($filerecord->owner != $USER->get('id')) {
    json_reply('local',get_string('notowner'));
}

if (count_records('artefact', 'parent', $fileid) > 0) {
    json_reply('local', get_string('artefacthaschildren'));
}

if (!delete_records('artefact_file_files', 'artefact', $fileid)) {
    json_reply('local', get_string('deletefailed'));
}

if (!delete_records('artefact', 'id', $fileid)) {
    json_reply('local', get_string('deletefailed'));
}

// @todo: Delete the file from the filesystem here

json_reply(false, get_string('filedeleted'));

?>
