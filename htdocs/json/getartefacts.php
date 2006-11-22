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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require('view.php');

$view = param_integer('view');
$artefact = param_integer('artefact', 0);
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 10);

json_headers();

// @todo permissions check here
$data = array(
    'count'     => 0,
    'offset'    => $offset,
    'limit'     => $limit,
    'view'      => $view,
    'artefact'  => $artefact,
    );

if (!empty($artefact)) {
    try {
        $a = artefact_instance_from_id($artefact);
        $children = $a->get_children_metadata();
        $data['data'] = array_values($children);
        $data['count'] = count($children);
    }
    catch (Exception $e) {
        json_reply(true, $e->getMessage());
    }
}
else {
    try {
        $v = new View($view);
        $artefacts = $v->get_artefact_metadata();
        $data['data'] = array_values($artefacts);
        $data['count'] = count($artefacts);
    }
    catch (Exception $e) {
        json_reply(true, $e->getMessage());
    }
}
echo json_encode($data);
exit;

?>