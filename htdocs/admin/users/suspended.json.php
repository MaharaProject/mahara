<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$type = param_alpha('type', 'suspended');
$setlimit = param_boolean('setlimit', false);

// Filter for institutional admins:
$instsql = $USER->get('admin') ? '' : '
    AND ui.institution IN (' . join(',', array_map('db_quote', array_keys($USER->get('institutions')))) . ')';

$count = get_field_sql('
    SELECT COUNT(*)
    FROM (
        SELECT u.id
        FROM {usr} u
        LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
        WHERE ' . ($type == 'expired' ? 'u.expiry < current_timestamp' : 'suspendedcusr IS NOT NULL') . '
        AND deleted = 0 ' . $instsql . '
        GROUP BY u.id
    ) AS a');

$data = get_records_sql_assoc('
    SELECT
        u.id, u.firstname, u.lastname, u.studentid, u.suspendedctime, u.suspendedreason AS reason,
        ua.firstname AS cusrfirstname, ua.lastname AS cusrlastname, ' . db_format_tsfield('u.expiry', 'expiry') . '
    FROM {usr} u
    LEFT JOIN {usr} ua on (ua.id = u.suspendedcusr)
    LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
    WHERE ' . ($type == 'expired' ? 'u.expiry < current_timestamp' : 'u.suspendedcusr IS NOT NULL') . '
    AND u.deleted = 0 ' . $instsql . '
    GROUP BY
        u.id, u.firstname, u.lastname, u.studentid, u.suspendedctime, u.suspendedreason,
        ua.firstname, ua.lastname, u.expiry
    ORDER BY ' . ($type == 'expired' ? 'u.expiry' : 'u.suspendedctime') . ', u.id
    LIMIT ?
    OFFSET ?', array($limit, $offset));

if (!$data) {
    $data = array();
}
else {
    $institutions = get_records_sql_array('
        SELECT ui.usr, ui.studentid, i.displayname
        FROM {usr_institution} ui INNER JOIN {institution} i ON ui.institution = i.name
        WHERE ui.usr IN (' . join(',', array_keys($data)) . ')', array());
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
        $record->expiry    = $record->expiry ? format_date($record->expiry, 'strftimew3cdate') : '-';
        unset($record->firstname, $record->lastname);
    }
}

$smarty = smarty_core();
$smarty->assign('data', $data);
$html = $smarty->fetch('admin/users/suspendresults.tpl');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'admin/users/suspended.php?type=' . $type,
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
    'setlimit' => $setlimit,
    'datatable' => 'suspendedlist',
    'jsonscript' => 'admin/users/suspended.json.php',
));

json_reply(false, array(
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $count,
        'results' => $count . ' ' . ($count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
    )
));
