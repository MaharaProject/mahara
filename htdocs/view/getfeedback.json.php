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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$view     = param_integer('view');
$artefact = param_integer('artefact', null);
$limit    = param_integer('limit', 5);
$offset   = param_integer('offset', 0);

$prefix = get_config('dbprefix');

if ($artefact) {
    $table = 'artefact';
    $artefactfield = 'artefact';
    $whereartefactclause = ' AND artefact = ' . $artefact;
}
else {
    $table = 'view';
    $artefactfield = null;
    $whereartefactclause = '';
}

$owner = get_field($table, 'owner', 'id', $artefact ? $artefact : $view);
$table .= '_feedback';
if ($owner == $USER->get('id')) {
    $count = count_records($table, 'view', $view, $artefactfield, $artefact);
    $publicclause = '';
}
else {
    $count = count_records($table, 'public', 1, 'view', $view, $artefactfield, $artefact);
    $publicclause = ' AND public = 1';
}

$feedback = get_records_sql_array('SELECT id, author, ctime, message, public
    FROM ' . $prefix . $table . '
    WHERE view = ' . $view . $whereartefactclause . $publicclause . '
    ORDER BY id DESC', '', $offset, $limit);

$data = array();
if ($feedback) {
    foreach ($feedback as $record) {
        $data[] = array(
            'id'              => $record->id,
            'ownedbythisuser' => ( get_field('view', 'owner', 'id', $view) == $USER->get('id') ? true : false ),
            'table'           => $table,
            'name'            => display_name($record->author),
            'date'            => format_date(strtotime($record->ctime), 'strftimedate'),
            'message'         => $record->message,
            'public'          => $record->public
        );
    }
}

$result = array(
    'count'       => $count,
    'limit'       => $limit,
    'offset'      => $offset,
    'data'        => $data,
);

json_headers();
print json_encode($result);


?>
