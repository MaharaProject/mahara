<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

if (isset($_POST['view'])) {
    define('PUBLIC', 1);
}

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'resume');

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$type = param_alpha('type');
$view = param_integer('view', 0);

$data = array();
$count = 0;

$othertable = 'artefact_resume_' . $type;

$owner = $USER->get('id');

$sql = 'SELECT ar.*, a.owner
    FROM {artefact} a 
    JOIN {' . $othertable . '} ar ON ar.artefact = a.id
    WHERE a.owner = ? AND a.artefacttype = ?
    ORDER BY ' . call_static_method(generate_artefact_class_name($type), 'get_order_field') . ' DESC
    LIMIT ' . $limit . ' OFFSET ' . $offset;

if (!empty($view)) { 
    if (!can_view_view($view)) {
        throw new AccessDeniedException();
    }
    require_once('view.php');
    $v = new View($view);
    $owner = $v->get('owner');
}

if (!$data = get_records_sql_array($sql, array($owner, $type))) {
    $data = array();
}

$count = count_records('artefact', 'owner', $owner, 'artefacttype', $type);

echo json_encode(array(
    'data' => $data,
    'limit' => $limit,
    'offset' => $offset,
    'count' => $count,
    'type' => $type));

?>

