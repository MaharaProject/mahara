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
require_once('artefact.php');

$id = param_integer('id');
$format = param_variable('format', FORMAT_ARTEFACT_LISTSELF);
$blockid = param_variable('blockid', null);
$options = param_variable('options', array());

if($blockid) {
    $options['blockid'] = $blockid;
}


$artefact = artefact_instance_from_id($id);
$renderedartefact = $artefact->render($format, $options);

if (!$renderedartefact) {
    json_reply('local', get_string('artefactnotrendered'));
}

$result = array(
    'data' => $renderedartefact['html'],
    'javascript' => $renderedartefact['javascript'],
    'error' => false,
    'message' => false // No message for successful artefact rendering
);

json_headers();
print json_encode($result);

?>
