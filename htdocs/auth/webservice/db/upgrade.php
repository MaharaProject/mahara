<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
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
 * @copyright  (C) 2006-2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

function xmldb_auth_webservice_upgrade($oldversion=0) {

    $status = true;

    /**
     * Ensure that all the Web Services tables have been created - even if we
     * are transitioning from artefact/webservice to webservice
     */
    if ($oldversion < 2012021001) {
        // Add in the Web Services subsystem
        // ensure that redundant tables are removed from early days of artefact/webservice
        $table = new XMLDBTable('oauth_consumer_token');
        if (table_exists($table)) {
            drop_table($table);
        }
        $table = new XMLDBTable('oauth_consumer_registry');
        if (table_exists($table)) {
            drop_table($table);
        }

        // Create the core services tables
        $table = new XMLDBTable('external_services');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 200, null, null);
            $table->addFieldInfo('enabled', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('restrictedusers', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('tokenusers', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('component', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addIndexInfo('name', XMLDB_INDEX_UNIQUE, array('name'));
            create_table($table);
        }
        $table = new XMLDBTable('external_functions');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 200, null, null);
            $table->addFieldInfo('classname', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addFieldInfo('methodname', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addFieldInfo('classpath', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('component', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addIndexInfo('name', XMLDB_INDEX_UNIQUE, array('name'));
            create_table($table);
        }
        $table = new XMLDBTable('external_services_functions');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
            $table->addFieldInfo('functionname', XMLDB_TYPE_CHAR, 200, null, null);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('externalserviceid', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            create_table($table);
        }
        $table = new XMLDBTable('external_tokens');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 128, null, null);
            $table->addFieldInfo('tokentype', XMLDB_TYPE_INTEGER, 4, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
            $table->addFieldInfo('sid', XMLDB_TYPE_CHAR, 128, null, null);
            $table->addFieldInfo('creatorid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 1);
            $table->addFieldInfo('iprestriction', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('validuntil', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('lastaccess', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('publickey', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('publickeyexpires', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('wssigenc', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            $table->addKeyInfo('externalserviceid', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            $table->addKeyInfo('creatorid', XMLDB_KEY_FOREIGN, array('creatorid'), 'usr', array('id'));
            $table->addIndexInfo('token', XMLDB_INDEX_UNIQUE, array('token'));
            create_table($table);
        }
        $table = new XMLDBTable('external_services_users');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('iprestriction', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('validuntil', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('lastaccess', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('publickey', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('publickeyexpires', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('wssigenc', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('externalserviceid', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            create_table($table);
        }
        $table = new XMLDBTable('external_services_logs');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('timelogged', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
            $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('protocol', XMLDB_TYPE_CHAR, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('auth', XMLDB_TYPE_CHAR, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('functionname', XMLDB_TYPE_CHAR, 200, null, XMLDB_NOTNULL);
            $table->addFieldInfo('timetaken', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('uri', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('info', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('ip', XMLDB_TYPE_CHAR, 45, null, null);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addIndexInfo('externalserviceid', XMLDB_INDEX_NOTUNIQUE, array('externalserviceid'));
            $table->addIndexInfo('institution', XMLDB_INDEX_NOTUNIQUE, array('institution'));
            $table->addIndexInfo('functionname', XMLDB_INDEX_NOTUNIQUE, array('functionname'));
            $table->addIndexInfo('timelogged', XMLDB_INDEX_NOTUNIQUE, array('timelogged'));
            create_table($table);
        }

        // Create the OAuth server authentication tables
        $table = new XMLDBTable('oauth_server_registry');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('consumer_key', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
            $table->addFieldInfo('consumer_secret', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('enabled', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('status', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('requester_name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('requester_email', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('callback_uri', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('application_uri', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('application_title', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('application_descr', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('application_notes', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('application_type', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addFieldInfo('issue_date', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('timestamp', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addKeyInfo('externalserviceid', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            $table->addIndexInfo('uk_consumer_key', XMLDB_INDEX_UNIQUE, array('consumer_key'));
            create_table($table);
        }
        $table = new XMLDBTable('oauth_server_nonce');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('consumer_key', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
            $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL);
            $table->addFieldInfo('nonce', XMLDB_TYPE_CHAR, 80, null, XMLDB_NOTNULL);
            $table->addFieldInfo('timestamp', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addIndexInfo('uk_keys', XMLDB_INDEX_UNIQUE, array('consumer_key', 'token', 'timestamp', 'nonce'));
            create_table($table);
        }
        $table = new XMLDBTable('oauth_server_token');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('osr_id_ref', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL);
            $table->addFieldInfo('token_secret', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL);
            $table->addFieldInfo('token_type', XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, true, array('request', 'access'));
            $table->addFieldInfo('authorized', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('referrer_host', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
            $table->addFieldInfo('callback_uri', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('verifier', XMLDB_TYPE_CHAR, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('token_ttl', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL, null, null, null, "'9999-12-31'");
            $table->addFieldInfo('timestamp', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('fk_ref_id', XMLDB_KEY_FOREIGN, array('osr_id_ref'), 'oauth_server_registry', array('id'));
            $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addIndexInfo('uk_token', XMLDB_INDEX_UNIQUE, array('token'));
            $table->addIndexInfo('i_token_ttl', XMLDB_INDEX_NOTUNIQUE, array('token_ttl'));
            create_table($table);
        }

        // Install a cron job to clean webservices logs
        if (!get_record('cron', 'callfunction', 'webservice_clean_webservice_logs')) {
            $cron = new StdClass;
            $cron->callfunction = 'webservice_clean_webservice_logs';
            $cron->minute       = '5';
            $cron->hour         = '01';
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }

        // ensure that we have a webservice auth_instance
        $authinstance = get_record('auth_instance', 'institution', 'mahara', 'authname', 'webservice');
        if (empty($authinstance)) {
            $authinstance = (object)array(
                   'instancename' => 'webservice',
                    'priority'     => 2,
                    'institution'  => 'mahara',
                    'authname'     => 'webservice',
            );
            insert_record('auth_instance', $authinstance);
        }
        // activate webservices
        foreach (array('soap', 'xmlrpc', 'rest', 'oauth') as $proto) {
            set_config('webservice_'.$proto.'_enabled', 1);
        }
    }

    // sweep for webservice updates everytime
    $status = external_reload_webservices();

    return $status;
}
