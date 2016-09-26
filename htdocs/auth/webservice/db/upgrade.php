<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function xmldb_auth_webservice_upgrade($oldversion=0) {

    $status = true;

    /**
     * Ensure that all the Web Services tables have been created - even if we
     * are transitioning from artefact/webservice to webservice
     */
    if ($oldversion < 2014112800) {
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
        if (table_exists($table)) {
            log_debug('Upgrading "external_services" table');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
            $field = new XMLDBField('mtime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);

            if ($results = get_records_sql_array("SELECT id, timecreated, timemodified FROM {external_services}", array())) {
                foreach ($results as $result) {
                    execute_sql("UPDATE {external_services} SET ctime = ?, mtime = ? WHERE id = ?",
                                array(db_format_timestamp($result->timecreated), db_format_timestamp($result->timemodified), $result->id));
                }
            }

            $field = new XMLDBField('timecreated');
            drop_field($table, $field);
            $field = new XMLDBField('timemodified');
            drop_field($table, $field);

            $index = new XMLDBIndex('name');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('name'));
            drop_index($table, $index);
            $index = new XMLDBIndex('nameuk');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('name'));
            add_index($table, $index);
        }
        else {
            log_debug('Adding "external_services" table');
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 200, null, null);
            $table->addFieldInfo('enabled', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('restrictedusers', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('tokenusers', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addFieldInfo('component', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addIndexInfo('nameuk', XMLDB_INDEX_UNIQUE, array('name'));
            create_table($table);
        }

        $table = new XMLDBTable('external_functions');
        if (table_exists($table)) {
            log_debug('Upgrading "external_functions" table');
            $index = new XMLDBIndex('name');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('name'));
            drop_index($table, $index);
            $index = new XMLDBIndex('nameuk');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('name'));
            add_index($table, $index);
        }
        else {
            log_debug('Adding "external_functions" table');
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 200, null, null);
            $table->addFieldInfo('classname', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addFieldInfo('methodname', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addFieldInfo('classpath', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('component', XMLDB_TYPE_CHAR, 100, null, null);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addIndexInfo('nameuk', XMLDB_INDEX_UNIQUE, array('name'));
            create_table($table);
        }

        $table = new XMLDBTable('external_services_functions');
        if (table_exists($table)) {
            log_debug('Upgrading "external_services_functions" table');
            $key = new XMLDBKey('externalserviceid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('externalserviceidfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            add_key($table, $key);
        }
        else {
            log_debug('Adding "external_services_functions" table');
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
            $table->addFieldInfo('functionname', XMLDB_TYPE_CHAR, 200, null, null);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('externalserviceidfk', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            create_table($table);
        }

        $table = new XMLDBTable('external_tokens');
        if (table_exists($table)) {
            log_debug('Updating "external_tokens" table');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
            $field = new XMLDBField('mtime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
            if ($tresults = get_records_sql_array("SELECT id, timecreated, lastaccess FROM {external_tokens}", array())) {
                foreach ($tresults as $tresult) {
                    execute_sql("UPDATE {external_tokens} SET ctime = ?, mtime = ? WHERE id = ?",
                                array(db_format_timestamp($tresult->timecreated), db_format_timestamp($tresult->lastaccess), $tresult->id));
                }
            }

            $field = new XMLDBField('timecreated');
            drop_field($table, $field);
            $field = new XMLDBField('lastaccess');
            drop_field($table, $field);

            $key = new XMLDBKey('userid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('useridfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            add_key($table, $key);

            $key = new XMLDBKey('externalserviceid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('externalserviceidfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            add_key($table, $key);

            $key = new XMLDBKey('creatorid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('creatoridfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            add_key($table, $key);

            $index = new XMLDBIndex('token');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('token'));
            drop_index($table, $index);
            $index = new XMLDBIndex('tokenuk');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('token'));
            add_index($table, $index);
        }
        else {
            log_debug('Adding "external_tokens" table');
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
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('publickey', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('publickeyexpires', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('wssigenc', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('useridfk', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            $table->addKeyInfo('externalserviceidfk', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            $table->addKeyInfo('creatoridfk', XMLDB_KEY_FOREIGN, array('creatorid'), 'usr', array('id'));
            $table->addIndexInfo('tokenuk', XMLDB_INDEX_UNIQUE, array('token'));
            create_table($table);
        }

        $table = new XMLDBTable('external_services_users');
        if (table_exists($table)) {
            log_debug('Upgrade "external_services_users" table');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
            $field = new XMLDBField('mtime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);

            if ($uresults = get_records_sql_array("SELECT id, timecreated, lastaccess FROM {external_services_users}", array())) {
                foreach ($uresults as $uresult) {
                    execute_sql("UPDATE {external_services_users} SET ctime = ?, mtime = ? WHERE id = ?",
                                array(db_format_timestamp($uresult->timecreated), db_format_timestamp($uresult->lastaccess), $uresult->id));
                }
            }

            $field = new XMLDBField('timecreated');
            drop_field($table, $field);
            $field = new XMLDBField('lastaccess');
            drop_field($table, $field);

            $key = new XMLDBKey('userid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('useridfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            add_key($table, $key);

            $key = new XMLDBKey('externalserviceid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('externalserviceidfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            add_key($table, $key);
        }
        else {
            log_debug('Adding "external_services_users" table');
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
            $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('iprestriction', XMLDB_TYPE_CHAR, 255, null, null);
            $table->addFieldInfo('validuntil', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('publickey', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('publickeyexpires', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, null);
            $table->addFieldInfo('wssigenc', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('externalserviceidfk', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            $table->addKeyInfo('useridfk', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            create_table($table);
        }

        $table = new XMLDBTable('external_services_logs');
        if (table_exists($table)) {
            log_debug('Upgrade "external_services_logs" table');
            $key = new XMLDBKey('userid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('useridfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            add_key($table, $key);
        }
        else {
            log_debug('Adding "external_services_logs" table');
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
            $table->addKeyInfo('useridfk', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addIndexInfo('externalserviceid', XMLDB_INDEX_NOTUNIQUE, array('externalserviceid'));
            $table->addIndexInfo('institution', XMLDB_INDEX_NOTUNIQUE, array('institution'));
            $table->addIndexInfo('functionname', XMLDB_INDEX_NOTUNIQUE, array('functionname'));
            $table->addIndexInfo('timelogged', XMLDB_INDEX_NOTUNIQUE, array('timelogged'));
            create_table($table);
        }

        // Create the OAuth server authentication tables
        $table = new XMLDBTable('oauth_server_registry');
        if (table_exists($table)) {
            log_debug('Upgrade "oauth_server_registry" table');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
            $field = new XMLDBField('mtime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);

            execute_sql("UPDATE {oauth_server_registry} SET ctime = issue_date, mtime = timestamp", array());

            $field = new XMLDBField('issue_date');
            drop_field($table, $field);
            $field = new XMLDBField('timestamp');
            drop_field($table, $field);

            $key = new XMLDBKey('uk_consumer_key');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('consumer_key'));
            drop_key($table, $key);
            $key = new XMLDBKey('consumerkeyuk');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('consumer_key'));
            add_key($table, $key);

            $key = new XMLDBKey('userid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('useridfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            add_key($table, $key);

            $key = new XMLDBKey('externalserviceid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('externalserviceidfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            add_key($table, $key);
        }
        else {
            log_debug('Adding "oauth_server_registry" table');
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
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('useridfk', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addKeyInfo('externalserviceidfk', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
            $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            $table->addIndexInfo('consumerkeyuk', XMLDB_INDEX_UNIQUE, array('consumer_key'));
            create_table($table);
        }

        $table = new XMLDBTable('oauth_server_nonce');
        if (table_exists($table)) {
            log_debug('Upgrading "oauth_server_nonce" table');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);

            execute_sql("UPDATE {oauth_server_nonce} SET ctime = timestamp", array());

            $key = new XMLDBKey('uk_keys');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('consumer_key','token','timestamp','nonce'));
            drop_key($table, $key);
            $key = new XMLDBKey('keysuk');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('consumer_key','token','ctime','nonce'));
            add_key($table, $key);

            $field = new XMLDBField('timestamp');
            drop_field($table, $field);
        }
        else {
            log_debug('Adding "oauth_server_nonce" table');
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('consumer_key', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
            $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL);
            $table->addFieldInfo('nonce', XMLDB_TYPE_CHAR, 80, null, XMLDB_NOTNULL);
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addIndexInfo('keysuk', XMLDB_INDEX_UNIQUE, array('consumer_key', 'token', 'ctime', 'nonce'));
            create_table($table);
        }

        $table = new XMLDBTable('oauth_server_token');
        if (table_exists($table)) {
            log_debug('Upgrading "oauth_server_token" table');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);

            execute_sql("UPDATE {oauth_server_token} SET ctime = timestamp", array());

            $field = new XMLDBField('timestamp');
            drop_field($table, $field);
            $key = new XMLDBKey('uk_token');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('token'));
            drop_key($table, $key);
            $key = new XMLDBKey('tokenuk');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('token'));
            add_key($table, $key);

            $key = new XMLDBKey('fk_ref_id');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('osr_id_ref'), 'oauth_server_registry', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('osrrefidfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('osr_id_ref'), 'oauth_server_registry', array('id'));
            add_key($table, $key);

            $key = new XMLDBKey('userid');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            drop_key($table, $key);
            $key = new XMLDBKey('useridfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            add_key($table, $key);
        }
        else {
            log_debug('Adding "oauth_server_token" table');
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
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('osrrefidfk', XMLDB_KEY_FOREIGN, array('osr_id_ref'), 'oauth_server_registry', array('id'));
            $table->addKeyInfo('useridfk', XMLDB_KEY_FOREIGN, array('userid'), 'usr', array('id'));
            $table->addIndexInfo('tokenuk', XMLDB_INDEX_UNIQUE, array('token'));
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
            set_config('webservice_' . $proto . '_enabled', 1);
        }
    }

    if ($oldversion < 2016071400) {
        log_debug('Updating DB names of webservice config fields');

        $configstochange = array(
            'webservice_enabled' => 'webservice_provider_enabled',
            'webservice_soap_enabled' => 'webservice_provider_soap_enabled',
            'webservice_xmlrpc_enabled' => 'webservice_provider_xmlrpc_enabled',
            'webservice_rest_enabled' => 'webservice_provider_rest_enabled',
            'webservice_oauth_enabled' => 'webservice_provider_oauth_enabled',
            'webservice_connections_enabled' => 'webservice_requester_enabled'
        );
        foreach ($configstochange as $old => $new) {

            set_config(
                $new,
                get_config($old)
            );
            delete_records('config', 'field', $old);
        }
    }

    if ($oldversion < 2016090700) {
        log_debug('Adding shortname column to external_services table');
        $table = new XMLDBTable('external_services');
        $field = new XMLDBField('shortname');
        $field->setAttributes(XMLDB_TYPE_CHAR, 200, null, null, null, null, null, null, '', 'name');
        if (!field_exists($table, $field)) {
            add_field($table, $field);

            $index = new XMLDBIndex('shortnamecompuix');
            $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('shortname', 'component'));
            add_index($table, $index);
        }

        log_debug('Clearing out "component" field from old example service groups');
        set_field('external_services', 'component', '', 'component', 'webservice');

        log_debug('Add optional "api version" field to external_services');
        $table = new XMLDBTable('external_services');
        $field = new XMLDBField('apiversion');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null, null, null, null, null, 'component');
        if (!field_exists($table, $field)) {
            add_field($table, $field);
        }

        // The old code listed the components with "/webservice"
        // on the end of them, e.g. "artefact/internal/webservice".
        // This is redundant and makes for a poorer user interface.
        log_debug('Change in "component" format for plugins');
        $oldtail = '/webservice';
        $length = strlen($oldtail);

        // Functions
        execute_sql(
            "UPDATE {external_functions}
            SET
                component = LEFT(
                    component,
                    LENGTH(component) - {$length}
                )
            WHERE
                component <> 'webservice'
                AND RIGHT(
                    component,
                    {$length}
                ) = '{$oldtail}'
            "
        );

        // Services
        execute_sql(
            "UPDATE {external_services}
            SET
                component = LEFT(
                    component,
                    LENGTH(component) - {$length}
                )
            WHERE
                component <> 'webservice'
                AND RIGHT(
                    component,
                    {$length}
                ) = '{$oldtail}'
            "
        );

        log_debug('adding client info fields to external_tokens table');
        $table = new XMLDBTable('external_tokens');

        $field = new XMLDBField('clientname');
        $field->setAttributes(XMLDB_TYPE_CHAR, 200);
        add_field($table, $field);

        $field = new XMLDBField('clientenv');
        $field->setAttributes(XMLDB_TYPE_CHAR, 200);
        add_field($table, $field);

        $field = new XMLDBField('clientguid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 128);
        add_field($table, $field);
    }

    if ($oldversion < 2016101100) {
        log_debug('Make external_tokens.institution nullable');
        $table = new XMLDBTable('external_tokens');
        $field = new XMLDBField('institution');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
        change_field_notnull($table, $field, false);

        log_debug('Allow null institution in external_services_logs');
        $table = new XMLDBTable('external_services_logs');
        $field = new XMLDBField('institution');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255);
        change_field_notnull($table, $field, false);
    }

    // sweep for webservice updates everytime
    $status = external_reload_webservices();

    return $status;
}
