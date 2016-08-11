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
define('JSON', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$result = get_records_sql_array('SELECT a.id, a.title, a.note, (u.profileicon = a.id) AS isdefault,
        COUNT (DISTINCT aa.artefact) AS attachcount, COUNT(DISTINCT va.view) AS viewcount, COUNT(DISTINCT s.id) AS skincount
    FROM {artefact} a
    LEFT OUTER JOIN {view_artefact} va ON va.artefact = a.id
    LEFT OUTER JOIN {artefact_attachment} aa ON aa.attachment = a.id
    LEFT OUTER JOIN {skin} s ON (s.bodybgimg = a.id OR s.viewbgimg = a.id)
    LEFT OUTER JOIN {usr} u ON (u.id = a.owner)
    WHERE artefacttype = \'profileicon\'
    AND a.owner = ?
    GROUP BY a.id, a.title, a.note, isdefault
    ORDER BY a.id', array($USER->get('id')));

if ($result) {
    foreach ($result as $r) {
        $r->default_str = get_string('setdefaultfor', 'artefact.file', ($r->title ? $r->title : $r->note));
        $r->delete_str = get_string('markfordeletionspecific', 'artefact.file', ($r->title ? $r->title : $r->note));
    }
}

$lastrow = array(
    'id'        => 0,
    'isdefault' => 't',
    'title'     => get_string('standardavatartitle', 'artefact.file'),
    'note'      => get_string('standardavatarnote', 'artefact.file')
);
$usersdefaulticon = record_exists_select('usr', 'profileicon IS NULL AND id = ?', array($USER->get('id')));
if (!$usersdefaulticon) {
    $lastrow['isdefault'] = 'f';
}
if (!$result) {
    $result = array();
}
$result[] = $lastrow;

$data['error'] = false;
$data['data'] = $result;
$data['count'] = ($result) ? count($result) : 0;
json_reply(false, $data);
