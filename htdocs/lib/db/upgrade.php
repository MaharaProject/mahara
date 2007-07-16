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

require_once('template.php');

function xmldb_core_upgrade($oldversion=0) {

    $status = true;
    $prefix = get_config('dbprefix');

    if ($oldversion < 2006121400) {

        set_field('community', 'jointype', 'open', 'jointype', 'anyone');
        $table = new XMLDBTable('community');
        $field = new XMLDBField('jointype');
        $field->setAttributes(XMLDB_TYPE_CHAR, 20, false, true, false, true, 
                              array('controlled', 'invite', 'request', 'open'), 'open');
        change_field_default($table, $field);
        change_field_enum($table, $field);
    }

    if ($oldversion < 2006121401) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('expirymailsent');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, false, true, false, null, null, 0);
        add_field($table, $field);
        $field = new XMLDBField('inactivemailsent');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, false, true, false, null, null, 0);
        add_field($table, $field);
    }

    // Add cron jobs for authentication cleanup: partial registrations and account expiries
    if ($oldversion < 2006121500) {
        $registrations = new StdClass;
        $registrations->callfunction = 'auth_clean_partial_registrations';
        $registrations->minute = '0';
        $registrations->hour = '5';
        insert_record('cron', $registrations);

        $expiries = new StdClass;
        $expiries->callfunction = 'auth_handle_account_expiries';
        $expiries->minute = '10';
        $expiries->hour = '5';
        insert_record('cron', $expiries);
    }

    // Merge the usr_suspension table with the usr table, and add a 'active' column and
    // various events and fields to do with this column
    if ($oldversion < 2006121800) {
        $table = new XMLDBTable('usr_suspension');
        drop_table($table);

        $table = new XMLDBTable('usr');

        $field = new XMLDBField('suspendedctime');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        add_field($table, $field);

        $field = new XMLDBField('suspendedreason');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);

        $field = new XMLDBField('suspendedcusr');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, false, false, false);
        add_field($table, $field);

        $field = new XMLDBField('active');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, false, true, false, null, null, 1);
        add_field($table, $field);
        set_field('usr', 'active', 1);

        $key = new XMLDBKey('sus_fk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('suspendedcusr'), 'usr', array('id'));
        add_key($table, $key);

        $event = new StdClass;
        $event->name = 'unsuspenduser';
        insert_record('event_type', $event);
        $event->name = 'undeleteuser';
        insert_record('event_type', $event);
        $event->name = 'expireuser';
        insert_record('event_type', $event);
        $event->name = 'unexpireuser';
        insert_record('event_type', $event);
        $event->name = 'deactivateuser';
        insert_record('event_type', $event);
        $event->name = 'activateuser';
        insert_record('event_type', $event);

        $activecheck = new StdClass;
        $activecheck->plugin = 'internal';
        $activecheck->event = 'suspenduser';
        $activecheck->callfunction = 'update_active_flag';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'unsuspenduser';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'deleteuser';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'undeleteuser';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'expireuser';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'unexpireuser';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'deactivateuser';
        insert_record('auth_event_subscription', $activecheck);
        $activecheck->event = 'activateuser';
        insert_record('auth_event_subscription', $activecheck);

    }

    if ($oldversion < 2006122200) {
        // Creating usr_infectedupload table
        $table = new XMLDBTable('usr_infectedupload');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                             XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('time', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));

        create_table($table);
    }

    if ($oldversion < 2007011200) {
        // Add path to file to the config table
        set_config('pathtofile', '/usr/bin/file');
    }

    if ($oldversion < 2007011500) {
        // Add the 'profileicon' field to the usr table
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('profileicon');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, false, false, false);
        add_field($table, $field);
    }

    if ($oldversion < 2007011600) {
        // Add the 'quota' and 'quotaused' fields to the usr table
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('quota');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, false, false, false);
        add_field($table, $field);
        $field = new XMLDBField('quotaused');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, false, true, null, null, null, 0);
        add_field($table, $field);

        execute_sql('UPDATE ' . $prefix . 'usr SET quota=10485760');
    }

    if ($oldversion < 2007012300) {
        // fix up a broken cron entry...
        set_field('cron', 'minute', '0', 'callfunction', 'rebuild_artefact_parent_cache_complete');
        $c = new StdClass;
        $c->callfunction = 'activity_process_queue';
        $c->minute = '*/5';
        insert_record('cron', $c);
    }

    if ($oldversion < 2007012301) {
        $at = new StdClass;
        $at->name = 'viewaccess';
        $at->admin = 0;
        $at->delay = 1;
        insert_record('activity_type', $at);
    }

    if ($oldversion < 2007012500) {
        $table = new XMLDBTable('usr_watchlist_artefact');
        $field = new XMLDBField('deleted');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, false, true, false, false, false, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2007020300) {
        // artefact_tag table
        $table = new XMLDBTable('artefact_tag');
        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $table->addFieldInfo('tag', XMLDB_TYPE_CHAR, '128', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('artefact', 'tag'));
        create_table($table);

        // view_tag table
        $table = new XMLDBTable('view_tag');
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addFieldInfo('tag', XMLDB_TYPE_CHAR, '128', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('view', 'tag'));
        create_table($table);
    }
    if ($oldversion < 2007020500) {
        insert_record('event_type', (object)array('name' => 'saveartefact'));
        insert_record('event_type', (object)array('name' => 'deleteartefact'));
    }

    if ($oldversion < 2007021600) {
        $template = template_parse('gallery');
        upgrade_template('gallery', $template);
    }

    if ($oldversion < 2007021900) {
        insert_record('event_type', (object)array('name' => 'saveview'));
        insert_record('event_type', (object)array('name' => 'deleteview'));
    }

    if ($oldversion < 2007021902) {
        $template = template_parse('filelist');
        upgrade_template('filelist', $template);
    }

    if ($oldversion < 2007021903) {
        execute_sql("UPDATE {$prefix}artefact
            SET container = 1
            WHERE artefacttype = 'blog'");
    }

    if ($oldversion < 2007021905) {
        $template = template_parse('blogandprofile');
        upgrade_template('blogandprofile', $template);
    }

    if ($oldversion < 2007042500) {
        // migrate everything we had to change to  make mysql happy
        execute_sql("ALTER TABLE {$prefix}cron DROP CONSTRAINT {$prefix}cron_cal_pk"); // can't drop primary keys using xmldb...
        execute_sql("ALTER TABLE {$prefix}cron ADD CONSTRAINT {$prefix}cron_id_pk PRIMARY KEY (id)"); // or add them!
        execute_sql("ALTER TABLE {$prefix}cron ADD CONSTRAINT {$prefix}cron_cal_uk UNIQUE (callfunction)");
        execute_sql("ALTER TABLE {$prefix}community ALTER COLUMN name TYPE varchar (128)");
        execute_sql("ALTER TABLE {$prefix}usr_activity_preference ALTER COLUMN method TYPE varchar(255)");
        execute_sql("ALTER TABLE {$prefix}template_category ALTER COLUMN name TYPE varchar(128)");
        execute_sql("ALTER TABLE {$prefix}template_category ALTER COLUMN parent TYPE varchar(128)");
        execute_sql("ALTER TABLE {$prefix}template ALTER COLUMN name TYPE varchar(128)");
        execute_sql("ALTER TABLE {$prefix}template ALTER COLUMN category TYPE varchar(128)");
        execute_sql("ALTER TABLE {$prefix}view ALTER COLUMN template TYPE varchar(128)");
        execute_sql("ALTER TABLE {$prefix}view_access ALTER COLUMN accesstype SET DEFAULT 'public'");
    }

    // everything up to here we pre mysql support.

    if ($oldversion < 2007062000) {
        execute_sql("INSERT INTO {$prefix}config (field, value) VALUES ('lang', (SELECT value FROM {$prefix}config WHERE field = 'language'))");
        delete_records('config', 'field', 'language');
    }

    if ($oldversion < 2007062900) {
        // application table
        $table = new XMLDBTable('application');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL);
        $table->addFieldInfo('displayname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL);
        $table->addFieldInfo('xmlrpcserverurl', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('ssolandurl', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));
        create_table($table);

        $table = new XMLDBTable('auth_installed');
        //$field = new XMLDBField('name');
        //$field->setAttributes(XMLDB_TYPE_CHAR, 100, null, true, null, null, null);
        //change_field_precision($table, $field);
        
        $field = new XMLDBField('requires_config');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        $field = new XMLDBField('requires_parent');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        $table = new XMLDBTable('auth_instance');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('instancename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('priority', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('authname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);

        $key   = new XMLDBKey("authnamefk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('authname'), 'auth_installed', array('name'));
        add_key($table, $key);
        $key   = new XMLDBKey("institutionfk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        add_key($table, $key);

        $table = new XMLDBTable('auth_instance_config');
        $table->addFieldInfo('instance', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('instance','field'));
        create_table($table);

        $key   = new XMLDBKey("instancefk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('instance'), 'auth_instance', array('id'));
        add_key($table, $key);

        $table = new XMLDBTable('host');
        $table->addFieldInfo('wwwroot', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('ipaddress', XMLDB_TYPE_CHAR, '39', null, XMLDB_NOTNULL);
        $table->addFieldInfo('portno', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 80);
        $table->addFieldInfo('appname', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL);
        $table->addFieldInfo('publickey', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, null, '');
        $table->addFieldInfo('publickeyexpires', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('deleted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('lastconnecttime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('wwwroot'));
        create_table($table);

        $key   = new XMLDBKey("appnamefk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('appname'), 'application', array('name'));
        add_key($table, $key);

        $key   = new XMLDBKey("institutionfk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        add_key($table, $key);

        $table = new XMLDBTable('institution');
        $key   = new XMLDBKey("pluginfk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('authplugin'), 'auth_installed', array('name'));
        drop_key($table, $key);

        $field = new XMLDBField('authplugin');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null, null, '');
        drop_field($table, $field);

        $table = new XMLDBTable('sso_session');
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('instanceid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('username', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->addFieldInfo('useragent', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL);
        $table->addFieldInfo('token', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL);
        $table->addFieldInfo('confirmtimeout', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('expires', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('sessionid', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('userid','instanceid'));
        create_table($table);

        $table = new XMLDBTable('usr');
        $field = new XMLDBField('authinstance');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
        $index = new XMLDBIndex('usernameuk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('username', 'institution'));
        drop_index($table, $index);

        $index = new XMLDBIndex("usernameuk");
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('username', 'authinstance'));
        add_index($table, $index);

        $field = new XMLDBField('sessionid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 32, null, null, null, null, null, '');
        add_field($table, $field);

        $field = new XMLDBField('lastauthinstance');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED);
        add_field($table, $field);

        $key   = new XMLDBKey("lastauthinstancefk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('lastauthinstance'), 'auth_instance', array('id'));
        add_key($table, $key);

        $key   = new XMLDBKey("authinstancefk");
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('authinstance'), 'auth_instance', array('id'));
        add_key($table, $key);

        $record = new stdClass();

        $record->name            = 'mahara';
        $record->displayname     = 'Mahara';
        $record->xmlrpcserverurl = '/api/xmlrpc/server.php';
        $record->ssolandurl      = '/auth/xmlrpc/land.php';
        insert_record('application',$record);

        $record->name            = 'moodle';
        $record->displayname     = 'Moodle';
        $record->xmlrpcserverurl = '/mnet/xmlrpc/server.php';
        $record->ssolandurl      = '/auth/mnet/land.php';
        insert_record('application',$record);

    }
    
    return $status;

}
?>
