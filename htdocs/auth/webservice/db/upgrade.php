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

        $table = new XMLDBTable('external_functions');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 200, null, null);
        $table->addFieldInfo('classname', XMLDB_TYPE_CHAR, 100, null, null);
        $table->addFieldInfo('methodname', XMLDB_TYPE_CHAR, 100, null, null);
        $table->addFieldInfo('classpath', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addFieldInfo('component', XMLDB_TYPE_CHAR, 100, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('nameuk', XMLDB_INDEX_UNIQUE, array('name'));
        create_table($table);

        $table = new XMLDBTable('external_services_functions');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('externalserviceid', XMLDB_TYPE_INTEGER, 10, null, null);
        $table->addFieldInfo('functionname', XMLDB_TYPE_CHAR, 200, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('externalserviceidfk', XMLDB_KEY_FOREIGN, array('externalserviceid'), 'external_services', array('id'));
        create_table($table);

        $table = new XMLDBTable('external_tokens');
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

        $table = new XMLDBTable('external_services_users');
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

        $table = new XMLDBTable('external_services_logs');
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

        // Create the OAuth server authentication tables
        $table = new XMLDBTable('oauth_server_registry');
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

        $table = new XMLDBTable('oauth_server_nonce');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('consumer_key', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
        $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL);
        $table->addFieldInfo('nonce', XMLDB_TYPE_CHAR, 80, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('keysuk', XMLDB_INDEX_UNIQUE, array('consumer_key', 'token', 'ctime', 'nonce'));
        create_table($table);

        $table = new XMLDBTable('oauth_server_token');
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

    // sweep for webservice updates everytime
    $status = external_reload_webservices();

    return $status;
}
