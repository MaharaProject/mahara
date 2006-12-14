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

defined('INTERNAL') || die();

function xmldb_core_upgrade($oldversion=0) {

    $status = true;

    if ($oldversion < 2006121400) {

        set_field('community', 'jointype', 'open', 'jointype', 'anyone');
        $table = new XMLDBTable('community');
        $field = new XMLDBField('jointype');
        $field->setAttributes(XMLDB_TYPE_CHAR, 20, false, true, false, true, 
                              array('controlled', 'invite', 'request', 'open'), 'open');
        change_field_default($table, $field);
        change_field_enum($table, $field);
    }

    return $status;

}
?>
