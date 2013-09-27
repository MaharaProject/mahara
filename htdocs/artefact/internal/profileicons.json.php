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

$result = get_records_sql_array('SELECT a.id, a.title, a.note, (u.profileicon = a.id) AS isdefault
    FROM {artefact} a
    LEFT OUTER JOIN {usr} u
    ON (u.id = a.owner)
    WHERE artefacttype = \'profileicon\'
    AND a.owner = ?
    ORDER BY a.id', array($USER->get('id')));

if(!$result) {
    $result = array();
}

json_headers();
$data['error'] = false;
$data['data'] = $result;
$data['count'] = ($result) ? count($result) : 0;
echo json_encode($data);
