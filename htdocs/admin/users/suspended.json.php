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
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

// NOTE: the check is not done on the 'active' column here, since suspended
// users are by definition not active. However deleted users are filtered out.
$count = get_field_sql('SELECT COUNT(*) FROM {usr} WHERE suspendedcusr IS NOT NULL AND deleted = 0');
$data = get_records_sql_array('SELECT DISTINCT ON (u.suspendedctime, u.id) u.id, u.firstname, u.lastname, u.studentid, u.suspendedreason AS reason,
    i.displayname AS institution, ua.firstname AS cusrfirstname, ua.lastname AS cusrlastname
    FROM {usr} u
    LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
    LEFT OUTER JOIN {institution} i ON (ui.institution = i.name)
    LEFT JOIN {usr} ua on (ua.id = u.suspendedcusr)
    WHERE u.suspendedcusr IS NOT NULL
    AND u.deleted = 0
    ORDER BY u.suspendedctime, u.id
    LIMIT ?
    OFFSET ?', array($limit, $offset));
if (!$data) {
    $data = array();
}
else {
    foreach ($data as &$record) {
        $record->name      = full_name($record);
        $record->firstname = $record->cusrfirstname;
        $record->lastname  = $record->cusrlastname;
        $record->cusrname  = full_name($record);
        unset($record->firstname, $record->lastname);
    }
}

echo json_encode(array(
    'count'    => $count,
    'limit'    => $limit,
    'offset'   => $offset,
    'data'     => $data
));

?>
