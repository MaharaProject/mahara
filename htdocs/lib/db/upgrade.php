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
 * @subpackage core or plugintype/pluginname
 * @author     Your Name <you@example.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_core_upgrade($oldversion=0) {

    $status = true;

    if ($status && $oldversion < 2006101700) {
        // Creating the usr table with basic fields required for authentication
        $table = new XMLDBTable('usr');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('username', XMLDB_TYPE_CHAR, '100', XMLDB_NOTNULL,
            null, null, null, null);
        $table->addFieldInfo('password', XMLDB_TYPE_CHAR, '40', XMLDB_NOTNULL,
            null, null, null, null);
        $table->addFieldInfo('salt', XMLDB_TYPE_CHAR, '8', XMLDB_NOTNULL,
            null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('usernameuk', XMLDB_KEY_UNIQUE, array('LOWER(username)'));

        $status = $status && create_table($table);
    }


    return $status;

}
?>
