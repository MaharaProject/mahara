<?php

/**
 * Upgrades for local customisations.
 */

defined('INTERNAL') || die();

function xmldb_local_upgrade($oldversion=0) {
    if ($oldversion < 2020120900) {
        log_debug('Add local cron job for checking and updating users from register');
        $cron = new stdClass();
        $cron->callfunction = 'local_pcnz_sync_users';
        $cron->minute       = '0';
        $cron->hour         = '07,19';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2020120901) {
        $e = new stdClass();
        $e->name = 'apcstatuschange';
        insert_record('event_type', $e);
    }

    if ($oldversion < 2021012602) {
        log_debug('Configure hiderealname account setting');
        if ($userids = get_records_sql_array("SELECT * from {usr} WHERE deleted != 1 AND id != 0")) {
            foreach ($userids as $user) {
                $whereobject = array(
                    'usr' => $user->id,
                    'field' => 'hiderealname',
                );
                $dataobject = array(
                    'usr' => $user->id,
                    'field' => 'hiderealname',
                    'value' => 1,
                );
                ensure_record_exists('usr_account_preference', (object)$whereobject, (object)$dataobject);
            }
        }
    }

    if ($oldversion < 2021012603) {
        log_debug('Install new Montserrat theme fonts');
        require_once(get_config('libroot') . 'skin.php');
        install_skins_default();
    }

    if ($oldversion < 2021021900) {
        log_debug('Add the "pcnz_verification_undo" table');
        $table = new XMLDBTable('pcnz_verification_undo');
        if (!table_exists($table)) {
            $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('block', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('reporter', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            create_table($table);
        }
    }

    if ($oldversion < 2021022300) {
        log_debug('Add columns to the "collection_template" table to track old registration status');
        $table = new XMLDBTable('collection_template');
        $field = new XMLDBField('registrationstatus');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_CHAR, 255);
            add_field($table, $field);
        }
        $field = new XMLDBField('rolloverdate');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
        }
    }
}
