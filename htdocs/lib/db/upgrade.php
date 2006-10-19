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

    if ($status && $oldversion < 2006101900) {
        // Insert core configuration option
        $status = $status && set_config('session_timeout', 1800);

        // Add password_change field
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('password_change');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null, null, 0, null);

        $status = $status && add_field($table, $field);

        // Insert the root user
        try {
            log_dbg('trying to insert root user');
            $root = new StdClass;
            $root->username = 'root';
            $root->password = 'mahara';
            $root->password_change = 1;
            insert_record('usr', $root);
        }
        catch (DatalibException $e) {
            return false;
        }
    }

    if ($status && $oldversion < 2006102000) {
        // add release field to installed_artefact and installed_auth
        $artefact_t = new XMLDBTable('installed_artefact');
        $auth_t = new XMLDBTable('installed_auth');

        $field = new XMLDBField('release');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        
        $status = $status && add_field($artefact_t, $field);
        $status = $status && add_field($auth_t, $field);
                                         
    }

    return $status;

}
?>
