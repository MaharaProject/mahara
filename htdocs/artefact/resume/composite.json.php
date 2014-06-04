<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'resume');

$limit = param_integer('limit', null);
$offset = param_integer('offset', 0);
$type = param_alpha('type');

$data = array();
$count = 0;

$othertable = 'artefact_resume_' . $type;

$owner = $USER->get('id');

$sql = 'SELECT ar.*, a.owner
    FROM {artefact} a
    JOIN {' . $othertable . '} ar ON ar.artefact = a.id
    WHERE a.owner = ? AND a.artefacttype = ?
    ORDER BY ar.displayorder';

if (!$data = get_records_sql_array($sql, array($owner, $type))) {
    $data = array();
}

// Add artefact attachments it there are any
$datawithattachments = array();
foreach ($data as $record) {
    $sql = 'SELECT a.title, a.id, af.size
            FROM {artefact} a
            JOIN {artefact_file_files} af ON af.artefact = a.id
            JOIN {artefact_attachment} at ON at.attachment = a.id
            WHERE at.artefact = ? AND at.item = ?
            ORDER BY a.title';
    $attachments = get_records_sql_array($sql, array($record->artefact, $record->id));
    $record->attachments = $attachments;
    if (!is_array($attachments)) {
        $record->clipcount = 0;
    }
    else {
        $record->clipcount = count($attachments);
    }
    $datawithattachments[] = $record;
}

$count = count_records('artefact', 'owner', $owner, 'artefacttype', $type);

json_reply(false, array(
    'data' => $data,
    'limit' => $limit,
    'offset' => $offset,
    'count' => $count,
    'type' => $type,
));
