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
require_once(get_config('docroot') . 'artefact/lib.php');

$id = param_integer('id');
$artefact = param_integer('artefact');
$direction = param_alpha('direction');

$a = artefact_instance_from_id($artefact);

if ($a->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

$table = $a->get_other_table_name();
$ids = get_column_sql('
    SELECT r.id FROM {artefact} a, {'.$table.'} r
    WHERE a.id = r.artefact AND a.artefacttype = ? AND a.owner = ?
    ORDER BY r.displayorder', array($a->get('artefacttype'), $a->get('owner')));

foreach ($ids as $k => $v) {
    if ($v == $id) {
        $oldorder = $k;
        break;
    }
}

if ($direction == 'up' && $oldorder > 0) {
    $neworder = array_merge(array_slice($ids, 0, $oldorder - 1),
                            array($id, $ids[$oldorder-1]),
                            array_slice($ids, $oldorder+1));
}
else if ($direction == 'down' && ($oldorder + 1 < count($ids))) {
    $neworder = array_merge(array_slice($ids, 0, $oldorder),
                            array($ids[$oldorder+1], $id),
                            array_slice($ids, $oldorder+2));
}

if (isset($neworder)) {
    foreach ($neworder as $k => $v) {
        set_field($table, 'displayorder', $k, 'id', $v);
    }
    $a->set('mtime', time());
    $a->commit();
}

json_reply(null, true);
