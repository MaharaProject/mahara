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
 * @subpackage artefact-resume
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require('artefact.php');

$id = param_integer('id');
$artefact = param_integer('artefact');


$a = artefact_instance_from_id($artefact);

if ($a->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

delete_records($a->get_other_table_name(), 'id', $id);
$count = count_records($a->get_other_table_name(), 'artefact', $artefact);
if (empty($count)) {
    $a->delete();
}
else {
    $a->set('mtime', time());
    $a->commit();
}

json_reply(null, get_string('compositedeleted', 'artefact.resume'));

?>

