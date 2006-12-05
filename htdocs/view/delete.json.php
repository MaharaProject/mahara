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
 * @subpackage admin
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$viewid = param_integer('viewid');

if (get_field('view', 'owner', 'id', $viewid) != $USER->get('id')) {
    json_reply('local', get_string('notowner'));
}

delete_records('view_artefact','view',$viewid);
delete_records('view_content','view',$viewid);
delete_records('view_access_community','view',$viewid);
delete_records('view_access_group','view',$viewid);
delete_records('view_access_usr','view',$viewid);
if (!delete_records('view','id',$viewid)) {
    json_reply('local', get_string('deleteviewfailed'));
}

json_reply(false,get_string('viewdeleted'));

?>
