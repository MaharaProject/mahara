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

//log_debug('myfiles.json.php');

$limit = param_integer('limit', null);
$offset = param_integer('offset', 0);
$folder = param_integer('folder', null);
$userid = $USER->get('id');

if ($folder) {
    $infolder = ' = ' . $folder;
}
else {
    $infolder = ' IS NULL';
}

// todo: do this in the artefact file class.

$prefix = get_config('dbprefix');
$filedata = get_records_sql_array('SELECT a.id, a.artefacttype, a.mtime, f.size, a.title, a.description
        FROM ' . $prefix . 'artefact a
        LEFT OUTER JOIN ' . $prefix . 'artefact_file_files f ON f.artefact = a.id
        WHERE a.owner = ' . $userid . '
        AND a.parent' . $infolder . "
        AND a.artefacttype IN ('folder','file','image')", '');

if (!$filedata) {
    $filedata = array();
}
else {
    foreach ($filedata as $item) {
        $item->mtime = strftime(get_string('strftimedatetime'),strtotime($item->mtime));
    }
}

// Sort folders before files; then use nat sort order on title.
function fileobjcmp ($a, $b) {
    return strnatcasecmp(($a->artefacttype == 'folder') . $a->title,
                         ($b->artefacttype == 'folder') . $b->title);
}
usort($filedata, "fileobjcmp");

$result = array(
    'count'       => count($filedata),
    'limit'       => $limit,
    'offset'      => $offset,
    'data'        => $filedata,
    'error'       => false,
    'message'     => get_string('filelistloaded'),
);

//log_debug($result);

json_headers();
print json_encode($result);

?>
