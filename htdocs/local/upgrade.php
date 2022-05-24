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

    if ($oldversion < 2021040800) {
        log_debug('Add the availability date to the primary verification blocks');
        $sql = "SELECT id FROM {block_instance} WHERE blocktype = 'verification' AND configdata LIKE '%\"primary\";b:1;%'";
        $records = get_records_sql_array($sql, array());
        if ($records) {
            $count = 0;
            $limit = 150;
            $total = count($records);
            require_once(get_config('docroot').'blocktype/lib.php');
            foreach ($records as $record) {
                $bi = new BlockInstance($record->id);
                $configdata = $bi->get('configdata');
                $configdata['availabilitydate'] = 1638270000;
                $bi->set('configdata', $configdata);
                $bi->commit();
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2021062900) {
        log_debug('Change the default usr setting for contacts / messages to be off by default');
        execute_sql("DELETE FROM {usr_account_preference} WHERE field IN (?, ?)", array('friendscontrol', 'messages'));
    }

    if ($oldversion < 2021081600) {
        log_debug('Add local cron job for a backstop to deal with user not being switched to active');
        $cron = new stdClass();
        $cron->callfunction = 'local_pcnz_doublecheck';
        $cron->minute       = '30';
        $cron->hour         = '22';
        $cron->day          = '*/2';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2022040500) {
        log_debug('Remove the collections that are not needed');
        if ($collections = get_records_sql_array("
            SELECT c.id FROM {collection} c
            JOIN {collection_template} ct ON ct.collection = c.id
            WHERE ct.originaltemplate = 4638 AND c.owner IN (
                SELECT u.id FROM {usr} u WHERE u.username IN('3950','4077','4267','4397','4442','4476','4517','4558','4702','4761','4814','4875','4913','4916','4929','4981','5021','5030','5136','5153','5180','5220','5323','5370','5385','5409','5414','5427','5428','5444','5563','5598','5657','5698','5716','5782','5784','5819','5910','6039','6065','6130','6220','6244','6330','6332','6455','6527','6541','6553','6558','6751','6781','6803','6852','7069','7079','7126','7491','7698','7716','7788','7803','7845','7936','8053','8157','8176','8264','8287','8313','8328','8347','8357','8363','8458','8507','8613','8654','8672','8884','8928','8945','8949','8964','9020','9038','9110','9281','9282','9285','9376','9558','9768','9833','9834','9837','9859','9867','9880','9889','9896','9914','9917','9963','9993','10047','10055','10064','10099','10102','10118','10145','10152','10185','10192','10270','10325','10374','10395','10413','10419','10425','10443','10466','10479','10488','10491','10505','10515','10522','10526','10544','10620','10640','10660','10701','10717','10737','10743','10764','10765','10766','10774','10797','10805','10821','10874','10886','10912','10937','10946','11064','11070','11081','11202','11316','11384','11428','11518','11563','11564','11724','11730','11733','11748','11815','11922','11925','11928','11935','11948','12043','12065','12289','12403','12506','12805','12842')
            )
            ORDER BY c.owner")) {
            require_once(get_config('libroot') . 'collection.php');
            $count = 0;
            $limit = 50;
            $total = count($collections);
            foreach ($collections as $collection) {
                $c = new Collection($collection->id);
                $c->delete(true);
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
        log_debug('Fix up the email address mismatch');
        if ($artefacts = get_records_sql_array("
            SELECT u.email, u.id
            FROM {usr} u
            JOIN {artefact} a ON (a.owner = u.id AND a.artefacttype = 'email')
            JOIN {artefact_internal_profile_email} ai ON ai.artefact = a.id
            WHERE ai.principal = ?
            AND u.email != ai.email
            AND u.email !=''", array(1))) {
            $count = 0;
            $limit = 50;
            $total = count($artefacts);
            foreach ($artefacts as $user) {
                set_user_primary_email($user->id, $user->email, true);
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2022052000) {
        if ($data = check_upgrades('module.beacon')) {
            upgrade_plugin($data);
        }
    }
}
