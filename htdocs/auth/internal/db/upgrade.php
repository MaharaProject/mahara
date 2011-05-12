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
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

function xmldb_auth_internal_upgrade($oldversion=0) {
    if ($oldversion < 2007062900) {

        $auth_instance = new stdClass();
        $auth_instance->instancename='internal';
        $auth_instance->priority='1';
        $auth_instance->institution='mahara';
        $auth_instance->authname='internal';
        $auth_instance->id = insert_record('auth_instance',$auth_instance, 'id', true);

        if (empty($auth_instance->id)) {
            return false;
        }

        $table = new XMLDBTable('usr');
        $key   = new XMLDBKey("authinstancefk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('authinstance'), 'auth_instance', array('id'));
        add_key($table, $key);

        return true;
    }

    return true;
}
