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
        $cron->minute       = '*';
        $cron->hour         = '*';
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
}
