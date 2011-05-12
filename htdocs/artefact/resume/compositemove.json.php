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
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    SELECT id FROM {'.$table.'}
    WHERE artefact = ?
    ORDER BY displayorder', array($artefact));

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
