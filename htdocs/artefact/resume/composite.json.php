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
 * @subpackage artefact-resume
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$type = param_alpha('type');

$data = array();
$count = 0;

$prefix = get_config('dbprefix');
$othertable = 'artefact_resume_' . $type;

$sql = 'SELECT ar.*, a.owner
    FROM ' . $prefix . 'artefact a 
    JOIN ' . $prefix . $othertable . ' ar ON ar.artefact = a.id
    WHERE a.owner = ? AND a.artefacttype = ?
    LIMIT ' . $limit . ' OFFSET ' . $offset;
if (!$data = get_records_sql_array($sql, array($USER->get('id'), $type))) {
    $data = array();
}
foreach ($data as &$row) {
    foreach (array('date', 'startdate', 'enddate') as $key) {
        if (array_key_exists($key, $row)) {
            $row->{$key} = format_date(strtotime($row->{$key}), 'strftimedate', 'current', 'artefact.resume');
        }
    }
}
$count = count_records('artefact', 'owner', $USER->get('id'), 'artefacttype', $type);
echo json_encode(array(
    'data' => $data,
    'limit' => $limit,
    'offset' => $offset,
    'count' => $count,
    'type' => $type));

?>

