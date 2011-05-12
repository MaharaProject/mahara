<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

// Filter for institutional admins:
$instsql = $USER->get('admin') ? '' : ' 
    AND ui.institution IN (' . join(',', array_map('db_quote', array_keys($USER->get('institutions')))) . ')';

// NOTE: the check is not done on the 'active' column here, since suspended
// users are by definition not active. However deleted users are filtered out.
$count = get_field_sql('
    SELECT COUNT(*)
    FROM (
        SELECT u.id
        FROM {usr} u
        LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
        WHERE suspendedcusr IS NOT NULL 
        AND deleted = 0 ' . $instsql . '
        GROUP BY u.id
    ) AS a');

$data = get_records_sql_assoc('
    SELECT 
        u.id, u.firstname, u.lastname, u.studentid, u.suspendedctime, u.suspendedreason AS reason,
        ua.firstname AS cusrfirstname, ua.lastname AS cusrlastname
    FROM {usr} u
    LEFT JOIN {usr} ua on (ua.id = u.suspendedcusr)
    LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
    WHERE u.suspendedcusr IS NOT NULL
    AND u.deleted = 0 ' . $instsql . '
    GROUP BY
        u.id, u.firstname, u.lastname, u.studentid, u.suspendedctime, u.suspendedreason,
        ua.firstname, ua.lastname
    ORDER BY u.suspendedctime, u.id
    LIMIT ?
    OFFSET ?', array($limit, $offset));

if (!$data) {
    $data = array();
}
else {
    $institutions = get_records_sql_array('
        SELECT ui.usr, ui.studentid, i.displayname
        FROM {usr_institution} ui INNER JOIN {institution} i ON ui.institution = i.name
        WHERE ui.usr IN (' . join(',', array_keys($data)) . ')', null);
    if ($institutions) {
        foreach ($institutions as &$i) {
            $data[$i->usr]->institutions[] = $i->displayname;
            $data[$i->usr]->institutionids[] = $i->studentid;
        }
    }
    $data = array_values($data);
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
