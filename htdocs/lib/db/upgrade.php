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

defined('INTERNAL') || die();

function xmldb_core_upgrade($oldversion=0) {
    global $SESSION;
    raise_time_limit(120);
    raise_memory_limit('256M');

    $status = true;

    if ($oldversion < 2009022700) {
        // Get rid of all blocks with position 0 caused by 'about me' block on profile views
        if (count_records('block_instance', 'order', 0) && !count_records_select('block_instance', '"order" < 0')) {
            if (is_mysql()) {
                $ids = get_column_sql('
                    SELECT i.id FROM {block_instance} i
                    INNER JOIN (SELECT view, "column" FROM {block_instance} WHERE "order" = 0) z
                        ON (z.view = i.view AND z.column = i.column)'
                );
                execute_sql('UPDATE {block_instance} SET "order" =  -1 * "order" WHERE id IN (' . join(',', $ids) . ')');
            } else {
                execute_sql('UPDATE {block_instance} SET "order" =  -1 * "order" WHERE id IN (
                    SELECT i.id FROM {block_instance} i
                    INNER JOIN (SELECT view, "column" FROM {block_instance} WHERE "order" = 0) z
                        ON (z.view = i.view AND z.column = i.column))'
                );
            }
            execute_sql('UPDATE {block_instance} SET "order" = 1 WHERE "order" = 0');
            execute_sql('UPDATE {block_instance} SET "order" = -1 * ("order" - 1) WHERE "order" < 0');
        }
    }

    if ($oldversion < 2009031000) {
        reload_html_filters();
    }

    if ($oldversion < 2009031300) {
        $table = new XMLDBTable('institution');

        $expiry = new XMLDBField('expiry');
        $expiry->setAttributes(XMLDB_TYPE_DATETIME);
        add_field($table, $expiry);

        $expirymailsent = new XMLDBField('expirymailsent');
        $expirymailsent->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $expirymailsent);

        $suspended = new XMLDBField('suspended');
        $suspended->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $suspended);

        // Insert a cron job to check for soon expiring and expired institutions
        if (!record_exists('cron', 'callfunction', 'auth_handle_institution_expiries')) {
            $cron = new StdClass;
            $cron->callfunction = 'auth_handle_institution_expiries';
            $cron->minute       = '5';
            $cron->hour         = '9';
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2009031800) {

        // Files can only attach blogpost artefacts, but we would like to be able to attach them
        // to other stuff.  Rename the existing attachment table artefact_blog_blogpost_file to
        // artefact_file_attachment so we don't end up with many tables doing the same thing.
        execute_sql("ALTER TABLE {artefact_blog_blogpost_file} RENAME TO {artefact_attachment}");

        if (is_postgres()) {
            // Ensure all of the indexes and constraints are renamed
            execute_sql("
            ALTER TABLE {artefact_attachment} RENAME blogpost TO artefact;
            ALTER TABLE {artefact_attachment} RENAME file TO attachment;

            ALTER INDEX {arteblogblogfile_blofil_pk} RENAME TO {arteatta_artatt_pk};
            ALTER INDEX {arteblogblogfile_blo_ix} RENAME TO {arteatta_art_ix};
            ALTER INDEX {arteblogblogfile_fil_ix} RENAME TO {arteatta_att_ix};

            ALTER TABLE {artefact_attachment} DROP CONSTRAINT {arteblogblogfile_blo_fk};
            ALTER TABLE {artefact_attachment} ADD CONSTRAINT {arteatta_art_fk} FOREIGN KEY (artefact) REFERENCES {artefact}(id);

            ALTER TABLE {artefact_attachment} DROP CONSTRAINT {arteblogblogfile_fil_fk};
            ALTER TABLE {artefact_attachment} ADD CONSTRAINT {arteatta_att_fk} FOREIGN KEY (attachment) REFERENCES {artefact}(id);
            ");
        }
        else if (is_mysql()) {
            execute_sql("ALTER TABLE {artefact_attachment} DROP FOREIGN KEY {arteblogblogfile_blo_fk}");
            execute_sql("ALTER TABLE {artefact_attachment} DROP INDEX {arteblogblogfile_blo_ix}");
            execute_sql("ALTER TABLE {artefact_attachment} CHANGE blogpost artefact BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {artefact_attachment} ADD CONSTRAINT {arteatta_art_fk} FOREIGN KEY {arteatta_art_ix} (artefact) REFERENCES {artefact}(id)");

            execute_sql("ALTER TABLE {artefact_attachment} DROP FOREIGN KEY {arteblogblogfile_fil_fk}");
            execute_sql("ALTER TABLE {artefact_attachment} DROP INDEX {arteblogblogfile_fil_ix}");
            execute_sql("ALTER TABLE {artefact_attachment} CHANGE file attachment BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {artefact_attachment} ADD CONSTRAINT {arteatta_att_fk} FOREIGN KEY {arteatta_att_ix} (attachment) REFERENCES {artefact}(id)");
        }

        // Drop the _pending table. From now on files uploaded as attachments will become artefacts
        // straight away.  Hopefully changes to the upload/file browser form will make it clear to
        // the user that these attachments sit in his/her files area as soon as they are uploaded.
        $table = new XMLDBTable('artefact_blog_blogpost_file_pending');
        drop_table($table);
    }

    if ($oldversion < 2009040900) {
        // The view access page has been putting the string 'null' in as a group role in IE.
        set_field('view_access_group', 'role', null, 'role', 'null');
    }

    if ($oldversion < 2009040901) {
        $table = new XMLDBTable('import_installed');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('release', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('active', XMLDB_TYPE_INTEGER,  1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));

        create_table($table);

        $table = new XMLDBTable('import_cron');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('minute', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('hour', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('day', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('dayofweek', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('month', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('nextrun', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'callfunction'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'import_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('import_config');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'field'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'import_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('import_event_subscription');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'import_installed', array('name'));
        $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
        $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('plugin', 'event', 'callfunction'));

        create_table($table);

        $table = new XMLDBTable('export_installed');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('release', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('active', XMLDB_TYPE_INTEGER,  1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));

        create_table($table);

        $table = new XMLDBTable('export_cron');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('minute', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('hour', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('day', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('dayofweek', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('month', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('nextrun', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'callfunction'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'export_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('export_config');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'field'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'export_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('export_event_subscription');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'export_installed', array('name'));
        $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
        $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('plugin', 'event', 'callfunction'));

        create_table($table);
    }

    if ($oldversion < 2009050700) {
        if ($data = check_upgrades('export.html')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('export.leap')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('import.leap')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2009051200) {
        // Rename submittedto column to submittedgroup
        if (is_postgres()) {
            execute_sql("ALTER TABLE {view} RENAME submittedto TO submittedgroup");
        }
        else if (is_mysql()) {
            execute_sql("ALTER TABLE {view} DROP FOREIGN KEY {view_sub_fk}");
            execute_sql("ALTER TABLE {view} DROP INDEX {view_sub_ix}");
            execute_sql("ALTER TABLE {view} CHANGE submittedto submittedgroup BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {view} ADD CONSTRAINT {view_sub_fk} FOREIGN KEY {view_sub_ix} (submittedgroup) REFERENCES {group}(id)");
        }

        // Add submittedhost column for views submitted to remote moodle hosts
        $table = new XMLDBTable('view');
        $field = new XMLDBField('submittedhost');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, null);
        add_field($table, $field);

        // Do this manually because xmldb tries to create a key with the same name (view_sub_vk) as an existing one, and fails.
        if (is_postgres()) {
            execute_sql("ALTER TABLE {view} ADD CONSTRAINT {view_subh_fk} FOREIGN KEY (submittedhost) REFERENCES {host}(wwwroot)");
            execute_sql("CREATE INDEX {view_subh_ix} ON {view} (submittedhost)");
        }
        else if (is_mysql()) {
            execute_sql("ALTER TABLE {view} ADD CONSTRAINT {view_subh_fk} FOREIGN KEY {view_subh_ix} (submittedhost) REFERENCES {host}(wwwroot)");
        }
    }

    if ($oldversion < 2009051201) {
        // Invisible view access keys for roaming moodle teachers
        $table = new XMLDBTable('view_access_token');
        $field = new XMLDBField('visible');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
    }

    if ($oldversion < 2009052700) {
        // Install a cron job to clean out old exports
        $cron = new StdClass;
        $cron->callfunction = 'export_cleanup_old_exports';
        $cron->minute       = '0';
        $cron->hour         = '3,13';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2009070600) {
        // This was forgotten as part of the 1.0 -> 1.1 upgrade
        if ($data = check_upgrades('blocktype.file/html')) {
              upgrade_plugin($data);
        };
    }

    if ($oldversion < 2009070700) {
        foreach (array('addfriend', 'removefriend', 'addfriendrequest', 'removefriendrequest') as $eventtype) {
            $event = (object) array('name' => $eventtype);
            ensure_record_exists('event_type', $event, $event);
        }
    }

    if ($oldversion < 2009070900) {
        if (is_mysql()) {
            execute_sql("ALTER TABLE {usr} DROP FOREIGN KEY {usr_las_fk}");
            execute_sql("ALTER TABLE {usr} DROP INDEX {usr_las_ix}");
        }
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('lastauthinstance');
        drop_field($table, $field);
    }

    if ($oldversion < 2009080600) {
        $table = new XMLDBTable('view');
        $index = new XMLDBIndex('view_own_type_uix');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('owner'));
        if (!index_exists($table, $index)) {
            // Delete duplicate profile views if there are any, then add an index
            // that will prevent it happening again - but only on postgres, as it's
            // the only db that supports partial indexes
            if ($viewdata = get_records_sql_array("
                SELECT owner, id
                FROM {view}
                WHERE owner IN (
                    SELECT owner
                    FROM {view}
                    WHERE type = 'profile'
                    GROUP BY owner
                    HAVING COUNT(*) > 1
                )
                AND type = 'profile'
                ORDER BY owner, id", array())) {

                require_once('view.php');
                $seen = array();
                foreach ($viewdata as $record) {
                    $seen[$record->owner][] = $record->id;
                }

                foreach ($seen as $owner => $views) {
                    // Remove the first one, which is their real profile view
                    array_shift($views);
                    foreach ($views as $viewid) {
                        delete_records('artefact_feedback','view',$viewid);
                        delete_records('view_feedback','view',$viewid);
                        delete_records('view_access','view',$viewid);
                        delete_records('view_access_group','view',$viewid);
                        delete_records('view_access_usr','view',$viewid);
                        delete_records('view_access_token', 'view', $viewid);
                        delete_records('view_autocreate_grouptype', 'view', $viewid);
                        delete_records('view_tag','view',$viewid);
                        delete_records('usr_watchlist_view','view',$viewid);
                        if ($blockinstanceids = get_column('block_instance', 'id', 'view', $viewid)) {
                            foreach ($blockinstanceids as $id) {
                                if (table_exists(new XMLDBTable('blocktype_wall_post'))) {
                                    delete_records('blocktype_wall_post', 'instance', $id);
                                }
                                delete_records('view_artefact', 'block', $id);
                                delete_records('block_instance', 'id', $id);
                            }
                        }
                        delete_records('view','id', $viewid);
                    }
                }
            }
            if (is_postgres()) {
                execute_sql("CREATE UNIQUE INDEX {view_own_type_uix} ON {view}(owner) WHERE type = 'profile'");
            }
        }
    }

    if ($oldversion < 2009080601) {
        execute_sql("DELETE FROM {group_member_invite} WHERE \"group\" NOT IN (SELECT id FROM {group} WHERE jointype = 'invite')");
        execute_sql("DELETE FROM {group_member_request} WHERE \"group\" NOT IN (SELECT id FROM {group} WHERE jointype = 'request')");
    }

    if ($oldversion < 2009081800) {
        $event = (object)array(
            'name' => 'creategroup',
        );
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2009082400) {
        $table = new XMLDBTable('usr_registration');
        $field = new XMLDBField('username');
        drop_field($table, $field);
        $field = new XMLDBField('salt');
        drop_field($table, $field);
        $field = new XMLDBField('password');
        drop_field($table, $field);
    }

    if ($oldversion < 2009082600) {
        $captcha = get_config('captcha_on_contact_form');
        set_config('captchaoncontactform', (int) (is_null($captcha) || $captcha));
        $captcha = get_config('captcha_on_register_form');
        set_config('captchaonregisterform', (int) (is_null($captcha) || $captcha));
    }

    if ($oldversion < 2009090700) {
        set_config('showselfsearchsideblock', 1);
        set_config('showtagssideblock', 1);
        set_config('tagssideblockmaxtags', 20);
    }

    if ($oldversion < 2009092100) {
        if ($data = check_upgrades('import.file')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('blocktype.creativecommons')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2009092900) {
        $event = (object)array(
            'name' => 'deleteartefacts',
        );
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2009101600) {
        require_once(get_config('docroot').'/lib/stringparser_bbcode/lib.php');
        // Remove bbcode formatting from existing feedback
        if ($records = get_records_sql_array("SELECT * FROM {view_feedback} WHERE message LIKE '%[%'", array())) {
            foreach ($records as &$r) {
                if (function_exists('parse_bbcode')) {
                    $r->message = parse_bbcode($r->message);
                }
                update_record('view_feedback', $r);
            }
        }
        if ($records = get_records_sql_array("SELECT * FROM {artefact_feedback} WHERE message LIKE '%[%'", array())) {
            foreach ($records as &$r) {
                if (function_exists('parse_bbcode')) {
                    $r->message = parse_bbcode($r->message);
                }
                update_record('artefact_feedback', $r);
            }
        }
    }

    if ($oldversion < 2009102100) {
        // Now the view_layout table has to have records for all column widths
        $record = (object)array(
            'columns' => 1,
            'widths'  => '100',
        );
        insert_record('view_layout', $record);
        $record = (object)array(
            'columns' => 5,
            'widths'  => '20,20,20,20,20',
        );
        insert_record('view_layout', $record);
    }

    if ($oldversion < 2009102200) {
        if (!count_records_select('activity_type', 'name = ? AND plugintype IS NULL AND pluginname IS NULL', array('groupmessage'))) {
            insert_record('activity_type', (object) array('name' => 'groupmessage', 'admin' => 0, 'delay' => 0));
        }
    }

    if ($oldversion < 2009102900) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('sessionid');
        drop_field($table, $field);
    }

    if ($oldversion < 2009110500) {
        set_config('creategroups', 'all');
    }

    if ($oldversion < 2009110900) {
        // Fix export cronjob so it runs 12 hours apart
        execute_sql("UPDATE {cron} SET hour = '3,15' WHERE callfunction = 'export_cleanup_old_exports'");

        // Cron job to clean old imports
        $cron = new StdClass;
        $cron->callfunction = 'import_cleanup_old_imports';
        $cron->minute       = '0';
        $cron->hour         = '4,16';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2009111200) {
        $table = new XMLDBTable('artefact_internal_profile_email');
        $field = new XMLDBField('mailssent');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2009111201) {
        $table = new XMLDBTable('artefact_internal_profile_email');
        $field = new XMLDBField('mailsbounced');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2009120100) {
        // Fix for bug in 1.1 => 1.2 upgrade which may have inserted
        // a second groupmessage activity_type record
        $records = get_records_select_array('activity_type', 'name = ? AND plugintype IS NULL AND pluginname IS NULL', array('groupmessage'), 'id');
        if ($records && count($records) > 1) {
            for ($i = 1; $i < count($records); $i++) {
                delete_records('activity_queue', 'type', $records[$i]->id);
                delete_records('notification_internal_activity', 'type', $records[$i]->id);
                delete_records('notification_emaildigest_queue', 'type', $records[$i]->id);
                delete_records('usr_activity_preference', 'activity', $records[$i]->id);
                delete_records('activity_type', 'id', $records[$i]->id);
            }
        }
    }

    if ($oldversion < 2009120900) {
        $table = new XMLDBTable('view');
        $field = new XMLDBField('theme');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, null);
        add_field($table, $field);
    }

    if ($oldversion < 2010011300) {
        // Clean up the mess left behind by failing to delete blogposts in a transaction
        try {
            include_once(get_config('docroot') . 'artefact/lib.php');
            if (function_exists('rebuild_artefact_parent_cache_dirty')) {
                rebuild_artefact_parent_cache_dirty();
            }
        }
        catch (Exception $e) {
            log_debug('Upgrade 2010011300: rebuild_artefact_parent_cache_dirty failed.');
        }
        execute_sql("
            INSERT INTO {artefact_blog_blogpost} (blogpost)
            SELECT id FROM {artefact} WHERE artefacttype = 'blogpost' AND id NOT IN (
                SELECT blogpost FROM {artefact_blog_blogpost}
            )");
    }

    if ($oldversion < 2010012701) {
        set_config('userscanchooseviewthemes', 1);
    }

    if ($oldversion < 2010021500) {
        if ($data = check_upgrades('blocktype.recentforumposts')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2010031000) {
        // For existing sites, preserve current user search behaviour:
        // Users are only searchable by their display names.
        set_config('userscanhiderealnames', 1);
        execute_sql("
            INSERT INTO {usr_account_preference} (usr, field, value)
            SELECT u.id, 'hiderealname', 1
            FROM {usr} u LEFT JOIN {usr_account_preference} p ON (u.id = p.usr AND p.field = 'hiderealname')
            WHERE NOT u.preferredname IS NULL AND u.preferredname != '' AND p.field IS NULL
        ");
    }

    if ($oldversion < 2010040700) {
        // Set antispam defaults
        set_config('formsecret', get_random_key());
        if (!function_exists('checkdnsrr')) {
            require_once(get_config('docroot') . 'lib/antispam.php');
        }
        if(checkdnsrr('test.uribl.com.black.uribl.com', 'A')) {
            set_config('antispam', 'advanced');
        }
        else {
            set_config('antispam', 'simple');
        }
        set_config('spamhaus', 0);
        set_config('surbl', 0);
    }

    if ($oldversion < 2010040800) {
        $table = new XMLDBTable('view');
        $field = new XMLDBField('submittedtime');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
        add_field($table, $field);
    }

    if ($oldversion < 2010041200) {
        delete_records('config', 'field', 'captchaoncontactform');
        delete_records('config', 'field', 'captchaonregisterform');
    }

    if ($oldversion < 2010041201) {
        $sql = "
            SELECT u.id
            FROM {usr} u
            LEFT JOIN {artefact} a
                ON (a.owner = u.id AND a.artefacttype = 'blog')
            WHERE u.id > 0
            GROUP BY u.id
            HAVING COUNT(a.id) != 1";

        $manyblogusers = get_records_sql_array($sql, array());

        if ($manyblogusers) {
            foreach($manyblogusers as $u) {
                $where = (object)array(
                    'usr' => $u->id,
                    'field' => 'multipleblogs',
                );
                $data = (object)array(
                    'usr' => $u->id,
                    'field' => 'multipleblogs',
                    'value' => 1,
                );
                ensure_record_exists('usr_account_preference', $where, $data);
            }
        }
    }

    if ($oldversion < 2010041600 && table_exists(new XMLDBTable('view_feedback'))) {
        // Add author, authorname to artefact table
        $table = new XMLDBTable('artefact');
        $field = new XMLDBField('author');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10');
        add_field($table, $field);

        $key = new XMLDBKey('authorfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('author'), 'usr', array('id'));
        add_key($table, $key);

        table_column('artefact', null, 'authorname', 'text', null, null, null, '');

        if (is_postgres()) {
            execute_sql("ALTER TABLE {artefact} ALTER COLUMN authorname DROP DEFAULT");
            set_field('artefact', 'authorname', null);
            execute_sql('UPDATE {artefact} SET authorname = g.name FROM {group} g WHERE "group" = g.id');
            execute_sql("UPDATE {artefact} SET authorname = CASE WHEN institution = 'mahara' THEN ? ELSE i.displayname END FROM {institution} i WHERE institution = i.name", array(get_config('sitename')));
        }
        else {
            execute_sql("UPDATE {artefact} a, {group} g SET a.authorname = g.name WHERE a.group = g.id");
            execute_sql("UPDATE {artefact} a, {institution} i SET a.authorname = CASE WHEN a.institution = 'mahara' THEN ? ELSE i.displayname END WHERE a.institution = i.name", array(get_config('sitename')));
        }
        execute_sql('UPDATE {artefact} SET author = owner WHERE owner IS NOT NULL');

        execute_sql('ALTER TABLE {artefact} ADD CHECK (
            (author IS NOT NULL AND authorname IS NULL    ) OR
            (author IS NULL     AND authorname IS NOT NULL)
        )');

        // Move feedback activity type to artefact plugin
        execute_sql("
            UPDATE {activity_type}
            SET plugintype = 'artefact', pluginname = 'comment'
            WHERE name = 'feedback'
        ");

        // Install the comment artefact
        if ($data = check_upgrades('artefact.comment')) {
            upgrade_plugin($data);
        }

        // Flag all views & artefacts to enable/disable comments
        table_column('artefact', null, 'allowcomments', 'integer', 1);
        table_column('view', null, 'allowcomments', 'integer', 1, null, 1);
        // Initially allow comments on blogposts, images, files
        set_field_select('artefact', 'allowcomments', 1, 'artefacttype IN (?,?,?)', array('blogpost', 'image', 'file'));

        // Convert old feedback to comment artefacts
        if ($viewfeedback = get_records_sql_array('
            SELECT f.*, v.id AS viewid, v.owner, v.group, v.institution
            FROM {view_feedback} f JOIN {view} v ON f.view = v.id', array())) {
            foreach ($viewfeedback as &$f) {
                if ($f->author > 0) {
                    $f->authorname = null;
                }
                else {
                    $f->author = null;
                    if (empty($f->authorname)) {
                        $f->authorname = '?';
                    }
                }
                $artefact = (object) array(
                    'artefacttype' => 'comment',
                    'owner'        => $f->owner,
                    'group'        => $f->group,
                    'institution'  => $f->institution,
                    'author'       => $f->author,
                    'authorname'   => $f->authorname,
                    'title'        => get_string('Comment', 'artefact.comment'),
                    'description'  => $f->message,
                    'ctime'        => $f->ctime,
                    'atime'        => $f->ctime,
                    'mtime'        => $f->ctime,
                );
                $aid = insert_record('artefact', $artefact, 'id', true);
                $comment = (object) array(
                    'artefact'     => $aid,
                    'private'      => 1-$f->public,
                    'onview'       => $f->viewid,
                );
                insert_record('artefact_comment_comment', $comment);
                if (!empty($f->attachment)) {
                    insert_record('artefact_attachment', (object) array(
                        'artefact'   => $aid,
                        'attachment' => $f->attachment
                    ));
                }
            }
        }

        // We are throwing away the view information from artefact_feedback.
        // From now on all artefact comments appear together and are not
        // tied to a particular view.
        if ($artefactfeedback = get_records_sql_array('
            SELECT f.*, a.id AS artefactid, a.owner, a.group, a.institution
            FROM {artefact_feedback} f JOIN {artefact} a ON f.artefact = a.id', array())) {
            foreach ($artefactfeedback as &$f) {
                if ($f->author > 0) {
                    $f->authorname = null;
                }
                else {
                    $f->author = null;
                    if (empty($f->authorname)) {
                        $f->authorname = '?';
                    }
                }
                $artefact = (object) array(
                    'artefacttype' => 'comment',
                    'owner'        => $f->owner,
                    'group'        => $f->group,
                    'institution'  => $f->institution,
                    'author'       => $f->author,
                    'authorname'   => $f->authorname,
                    'title'        => get_string('Comment', 'artefact.comment'),
                    'description'  => $f->message,
                    'ctime'        => $f->ctime,
                    'atime'        => $f->ctime,
                    'mtime'        => $f->ctime,
                );
                $aid = insert_record('artefact', $artefact, 'id', true);
                $comment = (object) array(
                    'artefact'     => $aid,
                    'private'      => 1-$f->public,
                    'onartefact'   => $f->artefactid,
                );
                insert_record('artefact_comment_comment', $comment);
            }
        }

        // Drop feedback tables
        $table = new XMLDBTable('view_feedback');
        drop_table($table);
        $table = new XMLDBTable('artefact_feedback');
        drop_table($table);

        // Add site setting for anonymous comments
        set_config('anonymouscomments', 1);
    }

    if ($oldversion < 2010041900 && !table_exists(new XMLDBTable('site_data'))) {
        // Upgrades for admin stats pages

        // Table for collection of historical stats
        $table = new XMLDBTable('site_data');
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, XMLDB_NOTNULL);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('ctime','type'));
        create_table($table);

        // Insert cron jobs to save site data
        $cron = new StdClass;
        $cron->callfunction = 'cron_site_data_weekly';
        $cron->minute       = 55;
        $cron->hour         = 23;
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = 6;
        insert_record('cron', $cron);

        $cron = new StdClass;
        $cron->callfunction = 'cron_site_data_daily';
        $cron->minute       = 51;
        $cron->hour         = 23;
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);

        // Put best guess at installation time into config table.
        set_config('installation_time', get_field_sql("SELECT MIN(ctime) FROM {site_content}"));

        // Save the current time so we know when we started collecting stats
        set_config('stats_installation_time', db_format_timestamp(time()));

        // Add ctime to usr table for daily count of users created
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('ctime');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
        add_field($table, $field);

        // Add visits column to view table
        $table = new XMLDBTable('view');
        $field = new XMLDBField('visits');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // Add table to store daily view visits
        $table = new XMLDBTable('view_visit');
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addIndexInfo('ctimeix', XMLDB_INDEX_NOTUNIQUE, array('ctime'));
        create_table($table);

        // Insert a cron job to check for new versions of Mahara
        $cron = new StdClass;
        $cron->callfunction = 'cron_check_for_updates';
        $cron->minute       = rand(0, 59);
        $cron->hour         = rand(0, 23);
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2010042600) {
        // @todo: Move to notification/internal
        $table = new XMLDBTable('notification_internal_activity');
        $field = new XMLDBField('parent');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10');
        add_field($table, $field);

        $key = new XMLDBKey('parentfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('parent'), 'notification_internal_activity', array('id'));
        add_key($table, $key);

        $field = new XMLDBField('from');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10');
        add_field($table, $field);

        $key = new XMLDBKey('fromfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('from'), 'usr', array('id'));
        add_key($table, $key);

        // Set from column for old user messages
        $usermessages = get_records_array(
            'notification_internal_activity',
            'type',
            get_field('activity_type', 'id', 'name', 'usermessage')
        );
        if ($usermessages) {
            foreach ($usermessages as &$m) {
                if (preg_match('/sendmessage\.php\?id=(\d+)/', $m->url, $match)) {
                    set_field('notification_internal_activity', 'from', $match[1], 'id', $m->id);
                }
            }
        }
    }

    if ($oldversion < 2010042602 && !get_record('view_type', 'type', 'dashboard')) {
        insert_record('view_type', (object)array(
            'type' => 'dashboard',
        ));
        if ($data = check_upgrades('blocktype.inbox')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('blocktype.newviews')) {
            upgrade_plugin($data);
        }
        // Install system dashboard view
        require_once(get_config('libroot') . 'view.php');
        $dbtime = db_format_timestamp(time());
        $viewdata = (object) array(
            'type'        => 'dashboard',
            'owner'       => 0,
            'numcolumns'  => 2,
            'ownerformat' => FORMAT_NAME_PREFERREDNAME,
            'title'       => get_string('dashboardviewtitle', 'view'),
            'template'    => 1,
            'ctime'       => $dbtime,
            'atime'       => $dbtime,
            'mtime'       => $dbtime,
        );
        $id = insert_record('view', $viewdata, 'id', true);
        $accessdata = (object) array('view' => $id, 'accesstype' => 'loggedin');
        insert_record('view_access', $accessdata);
        $blocktypes = array(
            array(
                'blocktype' => 'newviews',
                'title' => get_string('title', 'blocktype.newviews'),
                'column' => 1,
                'config' => array(
                    'limit' => 5,
                ),
            ),
            array(
                'blocktype' => 'myviews',
                'title' => get_string('title', 'blocktype.myviews'),
                'column' => 1,
                'config' => null,
            ),
            array(
                'blocktype' => 'inbox',
                'title' => get_string('inboxblocktitle'),
                'column' => 2,
                'config' => array(
                    'feedback' => true,
                    'groupmessage' => true,
                    'institutionmessage' => true,
                    'maharamessage' => true,
                    'usermessage' => true,
                    'viewaccess' => true,
                    'watchlist' => true,
                    'maxitems' => '5',
                ),
            ),
            array(
                'blocktype' => 'inbox',
                'title' => get_string('topicsimfollowing'),
                'column' => 2,
                'config' => array(
                    'newpost' => true,
                    'maxitems' => '5',
                ),
            ),
        );
        $installed = get_column_sql('SELECT name FROM {blocktype_installed}');
        $weights = array(1 => 0, 2 => 0);
        foreach ($blocktypes as $blocktype) {
            if (in_array($blocktype['blocktype'], $installed)) {
                $weights[$blocktype['column']]++;
                insert_record('block_instance', (object) array(
                    'blocktype'  => $blocktype['blocktype'],
                    'title'      => $blocktype['title'],
                    'view'       => $id,
                    'column'     => $blocktype['column'],
                    'order'      => $weights[$blocktype['column']],
                    'configdata' => serialize($blocktype['config']),
                ));
            }
        }
    }

    if ($oldversion < 2010042603) {
        execute_sql('ALTER TABLE {usr} ADD COLUMN showhomeinfo SMALLINT NOT NULL DEFAULT 1');
        set_config('homepageinfo', 1);
    }

    if ($oldversion < 2010042604) {
        // @todo: Move to notification/internal
        $table = new XMLDBTable('notification_internal_activity');
        $field = new XMLDBField('urltext');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2010051000) {
        set_field('activity_type', 'delay', 1, 'name', 'groupmessage');
    }

    if ($oldversion < 2010052000) {
        $showusers = get_config('showonlineuserssideblock');
        set_config('showonlineuserssideblock', (int) (is_null($showusers) || $showusers));
    }

    if ($oldversion < 2010060300) {
        // Add table to associate users with php session ids
        $table = new XMLDBTable('usr_session');
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('session', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('session'));
        $table->addIndexInfo('usrix', XMLDB_INDEX_NOTUNIQUE, array('usr'));
        create_table($table);
    }

    if ($oldversion < 2010061100) {
        set_config('registerterms', 1);
    }

    if ($oldversion < 2010061800) {
        insert_record('view_type', (object)array(
            'type' => 'grouphomepage',
        ));
        if ($data = check_upgrades('blocktype.groupmembers')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('blocktype.groupinfo')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('blocktype.groupviews')) {
            upgrade_plugin($data);
        }

        $dbtime = db_format_timestamp(time());
        // create a system template for group homepage views
        require_once(get_config('libroot') . 'view.php');
        $viewdata = (object) array(
            'type'        => 'grouphomepage',
            'owner'       => 0,
            'numcolumns'  => 1,
            'template'    => 1,
            'title'       => get_string('grouphomepage', 'view'),
            'ctime'       => $dbtime,
            'atime'       => $dbtime,
            'mtime'       => $dbtime,
        );
        $id = insert_record('view', $viewdata, 'id', true);
        $accessdata = (object) array('view' => $id, 'accesstype' => 'loggedin');
        insert_record('view_access', $accessdata);
        $blocktypes = array(
            array(
                'blocktype' => 'groupinfo',
                'title' => '',
                'column' => 1,
                'config' => null,
            ),
            array(
                'blocktype' => 'recentforumposts',
                'title' => get_string('latestforumposts', 'interaction.forum'),
                'column' => 1,
                'config' => null,
            ),
            array(
                'blocktype' => 'groupviews',
                'title' => get_string('Views', 'view'),
                'column' => 1,
                'config' => null,
            ),
            array(
                'blocktype' => 'groupmembers',
                'title' => get_string('Members', 'group'),
                'column' => 1,
                'config' => null,
            ),
        );
        $installed = get_column_sql('SELECT name FROM {blocktype_installed}');
        foreach ($blocktypes as $k => $blocktype) {
            if (!in_array($blocktype['blocktype'], $installed)) {
                unset($blocktypes[$k]);
            }
        }
        $weights = array(1 => 0);
        foreach ($blocktypes as $blocktype) {
            $weights[$blocktype['column']]++;
            insert_record('block_instance', (object) array(
                'blocktype'  => $blocktype['blocktype'],
                'title'      => $blocktype['title'],
                'view'       => $id,
                'column'     => $blocktype['column'],
                'order'      => $weights[$blocktype['column']],
                'configdata' => serialize($blocktype['config']),
            ));
        }

        // add a default group homepage view for all groups in the system
        unset($viewdata->owner);
        $viewdata->template = 0;

        if (!$groups = get_records_array('group', '', '', '', 'id,public')) {
            $groups = array();
        }
        foreach ($groups as $group) {
            $viewdata->group = $group->id;
            $id = insert_record('view', $viewdata, 'id', true);
            insert_record('view_access', (object) array(
                'view' => $id,
                'accesstype' => $group->public ? 'public' : 'loggedin',
            ));
            insert_record('view_access_group', (object) array(
                'view' => $id,
                'group' => $group->id,
            ));
            $weights = array(1 => 0);
            foreach ($blocktypes as $blocktype) {
                $weights[$blocktype['column']]++;
                insert_record('block_instance', (object) array(
                    'blocktype'  => $blocktype['blocktype'],
                    'title'      => $blocktype['title'],
                    'view'       => $id,
                    'column'     => $blocktype['column'],
                    'order'      => $weights[$blocktype['column']],
                    'configdata' => serialize($blocktype['config']),
                ));
            }
        }
    }
    if ($oldversion < 2010062502) {
        //new feature feedback control on views
        $table = new XMLDBTable('view_access');

        $field = new XMLDBField('allowcomments');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        $field = new XMLDBField('approvecomments');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);

        // Add comment approval to view/artefact (default 0)
        $field = new XMLDBField('approvecomments');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);

        $table = new XMLDBTable('view');
        add_field($table, $field);

        $table = new XMLDBTable('artefact');
        add_field($table, $field);

        // view_access_(group|usr|token) tables are getting wide with duplicated columns,
        // so just create all the necessary columns in view_access and move stuff there
        $table = new XMLDBTable('view_access');

        $field = new XMLDBField('accesstype');
        $field->setAttributes(XMLDB_TYPE_CHAR, 16, null, null);
        change_field_notnull($table, $field);

        $field = new XMLDBField('group');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null);
        add_field($table, $field);

        $field = new XMLDBField('role');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
        add_field($table, $field);

        $field = new XMLDBField('usr');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null);
        add_field($table, $field);

        $field = new XMLDBField('token');
        $field->setAttributes(XMLDB_TYPE_CHAR, 100, null, null);
        add_field($table, $field);

        $field = new XMLDBField('visible');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);

        // Copy data to view_access
        execute_sql('
            INSERT INTO {view_access} (view, accesstype, "group", role, startdate, stopdate)
            SELECT view, NULL, "group", role, startdate, stopdate FROM {view_access_group}'
        );
        execute_sql('
            INSERT INTO {view_access} (view, accesstype, usr, startdate, stopdate)
            SELECT view, NULL, usr, startdate, stopdate FROM {view_access_usr}'
        );
        execute_sql('
            INSERT INTO {view_access} (view, accesstype, token, visible, startdate, stopdate)
            SELECT view, NULL, token, visible, startdate, stopdate FROM {view_access_token}'
        );

        // Add foreign keys
        $key = new XMLDBKey('groupfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
        add_key($table, $key);

        $key = new XMLDBKey('usrfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        add_key($table, $key);

        $index = new XMLDBIndex('tokenuk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('token'));
        add_index($table, $index);

        // Exactly one of accesstype, group, usr, token must be not null
        execute_sql('ALTER TABLE {view_access} ADD CHECK (
            (accesstype IS NOT NULL AND "group" IS NULL     AND usr IS NULL     AND token IS NULL) OR
            (accesstype IS NULL     AND "group" IS NOT NULL AND usr IS NULL     AND token IS NULL) OR
            (accesstype IS NULL     AND "group" IS NULL     AND usr IS NOT NULL AND token IS NULL) OR
            (accesstype IS NULL     AND "group" IS NULL     AND usr IS NULL     AND token IS NOT NULL)
        )');

        // Drop old tables
        $table = new XMLDBTable('view_access_group');
        drop_table($table);
        $table = new XMLDBTable('view_access_usr');
        drop_table($table);
        $table = new XMLDBTable('view_access_token');
        drop_table($table);

        // Insert explicit tutor access records for submitted views
        if (!$submittedviews = get_records_sql_array('
            SELECT v.id, v.submittedgroup, g.grouptype
            FROM {view} v JOIN {group} g ON (v.submittedgroup = g.id AND g.deleted = 0)',
            array()
        )) {
            $submittedviews = array();
        }
        $roles = array();
        foreach ($submittedviews as $v) {
            if (!isset($roles[$v->grouptype])) {
                $rs = get_column('grouptype_roles', 'role', 'grouptype', $v->grouptype, 'see_submitted_views', 1);
                $roles[$v->grouptype] = empty($rs) ? array() : $rs;
            }
            foreach ($roles[$v->grouptype] as $role) {
                $accessrecord = (object) array(
                    'view'            => $v->id,
                    'group'           => $v->submittedgroup,
                    'role'            => $role,
                    'visible'         => 0,
                    'allowcomments'   => 1,
                    'approvecomments' => 0,
                );
                ensure_record_exists('view_access', $accessrecord, $accessrecord);
            }
        }
    }

    if ($oldversion < 2010070700) {
        $table = new XMLDBTable('group_category');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('title', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('displayorder', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);

        $table = new XMLDBTable('group');
        $field = new XMLDBField('category');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);

        $key = new XMLDBKey('categoryfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('category'), 'group_category', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2010071300) {
        set_config('searchusernames', 1);
    }

    if ($oldversion < 2010071500) {
        reload_html_filters();
    }

    if ($oldversion < 2010071600) {
        if (is_postgres()) {
            // change_field_enum should do this
            execute_sql('ALTER TABLE {view_access} DROP CONSTRAINT {viewacce_acc_ck}');
        }
        $table = new XMLDBTable('view_access');
        $field = new XMLDBField('accesstype');
        $field->setAttributes(XMLDB_TYPE_CHAR, 16, null, null, null, XMLDB_ENUM, array('public', 'loggedin', 'friends', 'objectionable'));
        change_field_enum($table, $field);
    }

    if ($oldversion < 2010071900) {
        $table = new XMLDBTable('group');
        $field = new XMLDBField('viewnotify');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
    }

    if ($oldversion < 2010081000) {

        // new table collection
        $table = new XMLDBTable('collection');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('owner', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('navigation', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('owner'), 'usr', array('id'));
        create_table($table);

        // new table collection_view
        $table = new XMLDBTable('collection_view');
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('displayorder', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('view'));
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN, array('collection'), 'collection', array('id'));
        create_table($table);

        // Drop unique constraint on token column of view_access
        $table = new XMLDBTable('view_access');
        $index = new XMLDBIndex('tokenuk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('token'));
        drop_index($table, $index);
        $index = new XMLDBIndex('tokenix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('token'));
        add_index($table, $index);

    }

    if ($oldversion < 2010081001) {
        if ($data = check_upgrades('artefact.plans')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('blocktype.plans/plans')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2010081100) {
        if ($data = check_upgrades('blocktype.navigation')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2010082000) {
        delete_records_select('config', "field IN ('usersrank', 'groupsrank', 'viewsrank')");
    }

    if ($oldversion < 2010091300) {
        // Cron job missing from installs post 2010041900
        if (!record_exists('cron', 'callfunction', 'cron_check_for_updates')) {
            $cron = new StdClass;
            $cron->callfunction = 'cron_check_for_updates';
            $cron->minute       = rand(0, 59);
            $cron->hour         = rand(0, 23);
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2010091500) {
        // Previous version of 2010040800 upgrade created the submittedtime
        // column not null (see bug #638550)
        $table = new XMLDBTable('view');
        $field = new XMLDBField('submittedtime');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
        change_field_notnull($table, $field);
        // Our crappy db is full of redundant data (submittedtime depends on
        // submittedhost or submittedgroup) so it's easy to correct this.
        execute_sql("
            UPDATE {view} SET submittedtime = NULL
            WHERE submittedtime IS NOT NULL AND submittedgroup IS NULL AND submittedhost IS NULL"
        );
    }

    if ($oldversion < 2010100702) {
        // Add general notification cleanup cron
        if (!record_exists('cron', 'callfunction', 'cron_clean_internal_activity_notifications')) {
            $cron = new StdClass;
            $cron->callfunction = 'cron_clean_internal_activity_notifications';
            $cron->minute       = 45;
            $cron->hour         = 22;
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2010110800) {
        // Encrypt all passwords with no set salt values
        $sql = "SELECT * FROM {usr}
                WHERE salt IS NULL OR salt = ''";
        if ($passwords = get_records_sql_array($sql, array())) {
            foreach ($passwords as $p) {
                $p->salt = substr(md5(rand(1000000, 9999999)), 2, 8);
                $p->password = sha1($p->salt . $p->password);
                update_record('usr', $p);
            }
        }
    }

    if ($oldversion < 2010122200) {
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('priority');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
        set_field('institution', 'priority', 0, 'name', 'mahara');
    }

    if ($oldversion < 2010122201) {
        $table = new XMLDBTable('view');
        $field = new XMLDBField('accessconf');
        $field->setAttributes(XMLDB_TYPE_CHAR, 40, XMLDB_UNSIGNED, null);
        add_field($table, $field);
    }

    if ($oldversion < 2010122700) {
        $table = new XMLDBTable('view_access');
        $index = new XMLDBIndex('accesstypeix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('accesstype'));
        add_index($table, $index);
    }

    if ($oldversion < 2011012800) {
        reload_html_filters();
    }

    if ($oldversion < 2011032500) {
        // Uninstall solr plugin; it's moving to contrib until it's fixed up.
        delete_records('search_cron', 'plugin', 'solr');
        delete_records('search_event_subscription', 'plugin', 'solr');
        delete_records('search_config', 'plugin', 'solr');
        delete_records('search_installed', 'name', 'solr');
        $searchplugin = get_config('searchplugin');
        if ($searchplugin == 'solr') {
            set_config('searchplugin', 'internal');
        }
    }

    if ($oldversion < 2011041800) {
        // Remove titles from system dashboard, group homepage blocks, so new users/groups
        // get blocks with automatically generated, translatable default titles.
        $systemdashboard = get_field('view', 'id', 'owner', 0, 'type', 'dashboard');
        set_field_select(
            'block_instance', 'title', '',
            "view = ? AND blocktype IN ('newviews','myviews','inbox')",
            array($systemdashboard)
        );

        $systemgrouphomepage = get_field('view', 'id', 'owner', 0, 'type', 'grouphomepage');
        set_field_select(
            'block_instance', 'title', '',
            "view = ? AND blocktype IN ('recentforumposts','groupviews','groupmembers')",
            array($systemgrouphomepage)
        );
    }

    if ($oldversion < 2011042000) {
        // Create empty variables in database for email configuration
        set_config('smtphosts', '');
        set_config('smtpport', '');
        set_config('smtpuser', '');
        set_config('smtppass', '');
        set_config('smtpsecure', '');
        $SESSION->add_info_msg('Email settings now can be configured via Site settings, however they may be overriden by those set in the config file. If you have no specific reason to use config file email configuration, please consider moving them to Site settings area.');
    }

    if ($oldversion < 2011050300) {
        if (get_config('httpswwwroot')) {
            // Notify users about httpswwwroot removal if it is still set
            $SESSION->add_info_msg('HTTPS logins have been deprecated, you need to remove the httpswwwroot variable from config file and switch your wwwroot to https so that the whole site is served over HTTPS.<br>See <a href="https://bugs.launchpad.net/mahara/+bug/646713">https://bugs.launchpad.net/mahara/+bug/646713</a> for more details.', 0);
        }
    }

    if ($oldversion < 2011050600) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('username');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        // This drops the unique index on the username column in postgres.
        // See upgrade 2011051800.
        change_field_precision($table, $field);
    }

    if ($oldversion < 2011051700) {
        // Create new "external" category
        insert_record('blocktype_category', (object) array('name' => 'external'));

        // Migrate existing blocktypes to the new category
        set_field('blocktype_installed_category', 'category', 'external', 'category', 'feeds');
        set_field('blocktype_installed_category', 'category', 'external', 'blocktype', 'externalvideo');
        set_field('blocktype_installed_category', 'category', 'external', 'blocktype', 'googleapps');

        // Delete old "feeds" category
        delete_records('blocktype_category', 'name', 'feeds');
    }

    if ($oldversion < 2011051800) {
        // Restore index that may be missing due to upgrade 2011050600.
        $table = new XMLDBTable('usr');
        $index = new XMLDBIndex('usr_use_uix');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('username'));
        if (!index_exists($table, $index)) {
            if (is_postgres()) {
                // For postgres, create the index on the lowercase username, the way it's
                // done in core_postinst().
                execute_sql('CREATE UNIQUE INDEX {usr_use_uix} ON {usr}(LOWER(username))');
            }
            else {
                $index = new XMLDBIndex('usernameuk');
                $index->setAttributes(XMLDB_INDEX_UNIQUE, array('username'));
                add_index($table, $index);
            }
        }
    }

    if ($oldversion < 2011052300) {
        if ($data = check_upgrades("blocktype.googleapps")) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2011061100) {
        // This block fixes an issue of upgrading from 1.4_STABLE to master
        // version number is date after 1.4_STABLE

        // 2011052400
        // add_field checks if field exists
        $table = new XMLDBTable('view_access');
        $field = new XMLDBField('ctime');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
        add_field($table, $field);

        // 2011053100
        // add_field checks if field exists
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('defaultquota');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);

        // 2011053101
        // add_field checks if field exists
        $table = new XMLDBTable('group');
        $field = new XMLDBField('quota');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);
        $field = new XMLDBField('quotaused');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // 2011060700
        // add_field checks if field exists
        $table = new XMLDBTable('view');
        $field = new XMLDBField('retainview');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // 2011060701
        // site setting to limit online users count
        if (!get_config('onlineuserssideblockmaxusers')) {
            set_config('onlineuserssideblockmaxusers', 10);
        }

        // 2011060701
        // add_field checks if field exists
        // instiutional setting to limit online users type
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('showonlineusers');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 2);
        add_field($table, $field);
    }

    if ($oldversion < 2011061300) {
        // Add more indexes to the usr table for user searches
        if (is_postgres()) {
            $table = new XMLDBTable('usr');
            $index = new XMLDBIndex('usr_fir_ix');
            $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('firstname', 'lastname', 'preferredname', 'studentid', 'email'));
            if (!index_exists($table, $index)) {
                execute_sql('CREATE INDEX {usr_fir_ix} ON {usr}(LOWER(firstname))');
                execute_sql('CREATE INDEX {usr_las_ix} ON {usr}(LOWER(lastname))');
                execute_sql('CREATE INDEX {usr_pre_ix} ON {usr}(LOWER(preferredname))');
                execute_sql('CREATE INDEX {usr_stu_ix} ON {usr}(LOWER(studentid))');
                execute_sql('CREATE INDEX {usr_ema_ix} ON {usr}(LOWER(email))');
            }
        }
    }

    if ($oldversion < 2011061400) {
        // Add institution to group table
        $table = new XMLDBTable('group');
        $field = new XMLDBField('institution');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
        add_field($table, $field);

        $key = new XMLDBKey('institutionfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        add_key($table, $key);

        // Add shortname to group table
        $table = new XMLDBTable('group');
        $field = new XMLDBField('shortname');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
        add_field($table, $field);

        $index = new XMLDBIndex('shortnameuk');
        $index->setAttributes(XMLDB_KEY_UNIQUE, array('institution', 'shortname'));
        add_index($table, $index);

    }

    if ($oldversion < 2011061500) {
        // Add favourites
        $table = new XMLDBTable('favorite');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('owner', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('shortname', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, XMLDB_NOTNULL);
        $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('ownerfk', XMLDB_KEY_FOREIGN, array('owner'), 'usr', array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $table->addIndexInfo('ownershortuk', XMLDB_INDEX_UNIQUE, array('owner', 'shortname'));
        create_table($table);

        $table = new XMLDBTable('favorite_usr');
        $table->addFieldInfo('favorite', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('favorite,usr'));
        $table->addKeyInfo('favoritefk', XMLDB_KEY_FOREIGN, array('favorite'), 'favorite', array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        create_table($table);
    }

    if ($oldversion < 2011062100) {
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('allowinstitutionpublicviews');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
    }

    if ($oldversion < 2011062200) {
        $table = new XMLDBTable('usr_tag');
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('tag', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('usr', 'tag'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        create_table($table);
    }

    if ($oldversion < 2011062300) {
        // Install a cron job to generate the sitemap
        if (!record_exists('cron', 'callfunction', 'cron_sitemap_daily')) {
            $cron = new StdClass;
            $cron->callfunction = 'cron_sitemap_daily';
            $cron->minute       = '0';
            $cron->hour         = '1';
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2011062400) {
        // self-registration per institution confrimation setting
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('registerconfirm');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);

        $table = new XMLDBTable('usr_registration');
        $field = new XMLDBField('pending');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        $table = new XMLDBTable('usr_registration');
        $field = new XMLDBField('reason');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2011062700) {
        set_config('dropdownmenu', 0);
    }

    if ($oldversion < 2011070500) {
        // Add profileicon foreign key to artefact table, first clearing any bad profileicon
        // values out of usr.
        execute_sql("
            UPDATE {usr} SET profileicon = NULL
            WHERE NOT profileicon IN (SELECT id FROM {artefact} WHERE artefacttype = 'profileicon')"
        );

        $table = new XMLDBTable('usr');
        $key = new XMLDBKey('profileiconfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('profileicon'), 'artefact', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2011070501) {
        // Add logo to institution table
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('logo');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10');
        add_field($table, $field);

        $key = new XMLDBKey('logofk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('logo'), 'artefact', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2011072200) {
        if (is_postgres()) {
            execute_sql("
                UPDATE {group}
                SET quota = CASE WHEN f.quotaused < 52428800 THEN 52428800 ELSE f.quotaused + 52428800 END,
                    quotaused = f.quotaused
                FROM (
                    SELECT g.id AS id, COALESCE(gf.quotaused, 0) AS quotaused
                    FROM {group} g
                        LEFT OUTER JOIN (
                            SELECT a.group, SUM(aff.size) AS quotaused
                            FROM {artefact} a JOIN {artefact_file_files} aff ON a.id = aff.artefact
                            WHERE NOT a.group IS NULL
                            GROUP BY a.group
                        ) gf ON gf.group = g.id
                    WHERE g.quota IS NULL AND g.quotaused = 0 AND g.deleted = 0
                ) f
                WHERE {group}.id = f.id"
            );
        }
        else {
            execute_sql("
                UPDATE {group}, (
                    SELECT g.id AS id, COALESCE(gf.quotaused, 0) AS quotaused
                    FROM {group} g
                        LEFT OUTER JOIN (
                            SELECT a.group, SUM(aff.size) AS quotaused
                            FROM {artefact} a JOIN {artefact_file_files} aff ON a.id = aff.artefact
                            WHERE NOT a.group IS NULL
                            GROUP BY a.group
                        ) gf ON gf.group = g.id
                    WHERE g.quota IS NULL AND g.quotaused = 0 AND g.deleted = 0
                ) f
                SET quota = CASE WHEN f.quotaused < 52428800 THEN 52428800 ELSE f.quotaused + 52428800 END,
                    {group}.quotaused = f.quotaused
                WHERE {group}.id = f.id"
            );
        }
    }

    if ($oldversion < 2011072600) {
        // Add tables to store custom institution styles
        // Currently only institutions can use them, but merge this with skin tables later...
        $table = new XMLDBTable('style');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('title', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('css', XMLDB_TYPE_TEXT);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);

        $table = new XMLDBTable('style_property');
        $table->addFieldInfo('style', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('style', 'field'));
        $table->addKeyInfo('stylefk', XMLDB_KEY_FOREIGN, array('style'), 'style', array('id'));
        create_table($table);

        $table = new XMLDBTable('institution');
        $field = new XMLDBField('style');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10');
        add_field($table, $field);

        $key = new XMLDBKey('stylefk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('style'), 'style', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2011082200) {
        // Doing a direct insert of the new artefact type instead of running upgrade_plugin(), in order to support the
        // transition from old profile fields to the new socialprofile artefact in Mahara 1.10
        if (!record_exists('artefact_installed_type', 'name', 'html', 'plugin', 'internal')) {
            insert_record('artefact_installed_type', (object)array('name'=>'html', 'plugin'=>'internal'));
        }
        // Move the textbox blocktype into artefact/internal
        set_field('blocktype_installed', 'artefactplugin', 'internal', 'name', 'textbox');
        if ($data = check_upgrades("blocktype.internal/textbox")) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2011082300) {
        // Add institution to view_access table
        $table = new XMLDBTable('view_access');

        $field = new XMLDBField('institution');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
        if (!field_exists($table, $field)) {
            add_field($table, $field);

            // Add foreign key
            $key = new XMLDBKey('institutionfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            add_key($table, $key);

            if (is_postgres()) {
                // Update constraint checks
                execute_sql('ALTER TABLE {view_access} DROP CONSTRAINT {view_access_check}');
                execute_sql('ALTER TABLE {view_access} ADD CHECK (
                    (accesstype IS NOT NULL AND "group" IS NULL     AND usr IS NULL     AND token IS NULL     AND institution IS NULL    ) OR
                    (accesstype IS NULL     AND "group" IS NOT NULL AND usr IS NULL     AND token IS NULL     AND institution IS NULL    ) OR
                    (accesstype IS NULL     AND "group" IS NULL     AND usr IS NOT NULL AND token IS NULL     AND institution IS NULL    ) OR
                    (accesstype IS NULL     AND "group" IS NULL     AND usr IS NULL     AND token IS NOT NULL AND institution IS NULL    ) OR
                    (accesstype IS NULL     AND "group" IS NULL     AND usr IS NULL     AND token IS NULL     AND institution IS NOT NULL))');
            }
            else {
                // MySQL doesn't support these types of constraints
            }
        }
    }

    if ($oldversion < 2011082400) {
        // Add cron entry for cache cleanup
        $cron = new StdClass;
        $cron->callfunction = 'file_cleanup_old_cached_files';
        $cron->minute       = '0';
        $cron->hour         = '1';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2011082401) {
        // Set config value for logged-in profile view access
        set_config('loggedinprofileviewaccess', 1);
    }

    if ($oldversion < 2011083000) {
        // Jointype changes
        $table = new XMLDBTable('group');
        $field = new XMLDBField('request');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
        set_field('group', 'request', 1, 'jointype', 'request');

        // Turn all request & invite groups into the 'approve' type
        $field = new XMLDBField('jointype');
        $field->setAttributes(
            XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, XMLDB_ENUM,
            array('open', 'controlled', 'request', 'invite', 'approve'), 'open'
        );
        if (is_postgres()) {
            execute_sql('ALTER TABLE {group} DROP CONSTRAINT {grou_joi_ck}');
        }
        change_field_enum($table, $field);

        set_field('group', 'jointype', 'approve', 'jointype', 'request');
        set_field('group', 'jointype', 'approve', 'jointype', 'invite');

        $field->setAttributes(
            XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, XMLDB_ENUM,
            array('open', 'controlled', 'approve'), 'open'
        );
        if (is_postgres()) {
            execute_sql('ALTER TABLE {group} DROP CONSTRAINT {grou_joi_ck}');
        }
        change_field_enum($table, $field);

        // Move view submission from grouptype to group
        $field = new XMLDBField('submittableto');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
        execute_sql("UPDATE {group} SET submittableto = 1 WHERE grouptype IN (SELECT name FROM {grouptype} WHERE submittableto = 1)");

        $table = new XMLDBTable('grouptype');
        $field = new XMLDBField('submittableto');
        drop_field($table, $field);

        // Any group can potentially take submissions, so make sure someone can assess them
        set_field('grouptype_roles', 'see_submitted_views', 1, 'role', 'admin');

        // Move group view editing permission from grouptype_roles to the group table
        $table = new XMLDBTable('group');
        $field = new XMLDBField('editroles');
        $field->setAttributes(
            XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, XMLDB_ENUM,
            array('all', 'notmember', 'admin'), 'all'
        );
        add_field($table, $field);
        execute_sql("
            UPDATE {group} SET editroles = 'notmember' WHERE grouptype IN (
                SELECT grouptype FROM {grouptype_roles} WHERE role = 'member' AND edit_views = 0
            )"
        );

        $table = new XMLDBTable('grouptype_roles');
        $field = new XMLDBField('edit_views');
        drop_field($table, $field);
    }

    if ($oldversion < 2011090900) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('password');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        change_field_type($table, $field, true, true);
    }

    if ($oldversion < 2011091200) {
        // Locked group views (only editable by group admins)
        $table = new XMLDBTable('view');
        $field = new XMLDBField('locked');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
        set_field('view', 'locked', 1, 'type', 'grouphomepage');

        // Setting to hide groups from the "Find Groups" listing
        $table = new XMLDBTable('group');
        $field = new XMLDBField('hidden');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // Setting to hide group members
        $field = new XMLDBField('hidemembers');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // Setting to hide group members from members
        $field = new XMLDBField('hidemembersfrommembers');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // Allow group members to invite friends
        $field = new XMLDBField('invitefriends');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // Allow group members to recommend the group to friends
        $field = new XMLDBField('suggestfriends');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2011091300) {
        $table = new XMLDBTable('blocktype_category');
        $field = new XMLDBField('sort');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, null);
        add_field($table, $field);
        execute_sql("UPDATE {blocktype_category} SET sort = ? WHERE name = ?", array('0', 'fileimagevideo'));
        execute_sql("UPDATE {blocktype_category} SET sort = ? WHERE name = ?", array('1', 'blog'));
        execute_sql("UPDATE {blocktype_category} SET sort = ? WHERE name = ?", array('2', 'general'));
        execute_sql("UPDATE {blocktype_category} SET sort = ? WHERE name = ?", array('3', 'internal'));
        execute_sql("UPDATE {blocktype_category} SET sort = ? WHERE name = ?", array('4', 'resume'));
        execute_sql("UPDATE {blocktype_category} SET sort = ? WHERE name = ?", array('5', 'external'));
        $index = new XMLDBIndex('sortuk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE,array('sort'));
        add_index($table, $index, false);
    }

    if ($oldversion < 2011092600) {
        // Move the taggedposts blocktype into artefact/blog/blocktype
        set_field('blocktype_installed', 'artefactplugin', 'blog', 'name', 'taggedposts');
    }

    if ($oldversion < 2011102700) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('logintries');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        // Every 5 minutes, reset everyone's login attempts to 0
        $cron = new StdClass;
        $cron->callfunction = 'user_login_tries_to_zero';
        $cron->minute       = '*/5';
        $cron->hour         = '*';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2011111500) {
        $table = new XMLDBTable('blocktype_installed_category');
        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('blocktype'));
        add_key($table, $key);
    }

    if ($oldversion < 2011120200) {
        if ($data = check_upgrades('blocktype.blog/taggedposts')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('blocktype.watchlist')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2012011300) {
        $table = new XMLDBTable('group_member');
        $field = new XMLDBField('method');
        $field->setAttributes(XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL, null, null, null, 'internal');
        add_field($table, $field);
    }

    if ($oldversion < 2012021000) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('unread');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2012021700) {
        $sql = "
            FROM {usr} u JOIN {auth_instance} ai ON (u.authinstance = ai.id)
            WHERE u.deleted = 0 AND ai.authname = 'internal' AND u.password != '*' AND u.salt != '*'";
        $pwcount = count_records_sql("SELECT COUNT(*) " . $sql);
        $sql = "
            SELECT u.id, u.password, u.salt" . $sql . " AND u.id > ?
            ORDER BY u.id";
        $done = 0;
        $lastid = 0;
        $limit = 2000;
        while ($users = get_records_sql_array($sql, array($lastid), 0, $limit)) {
            foreach ($users as $user) {
                // Wrap the old hashed password inside a SHA512 hash ($6$ is the identifier for SHA512)
                $user->password = crypt($user->password, '$6$' . substr(md5(get_config('passwordsaltmain') . $user->salt), 0, 16));

                // Drop the salt from the password as it may contain secrets that are not stored in the db
                // for example, the passwordsaltmain value
                $user->password = substr($user->password, 0, 3) . substr($user->password, 3+16);
                set_field('usr', 'password', $user->password, 'id', $user->id);
                remove_user_sessions($user->id);
                $lastid = $user->id;
            }
            $done += count($users);
            log_debug("Upgrading stored passwords: $done/$pwcount");
            set_time_limit(30);
        }
    }

    if ($oldversion < 2012022100) {
        reload_html_filters();
    }

    if ($oldversion < 2012042600 && !table_exists(new XMLDBTable('iframe_source'))) {
        // Tables for configurable safe iframe sources

        $table = new XMLDBTable('iframe_source_icon');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('domain', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));
        create_table($table);

        $iframedomains = array(
            'YouTube'      => 'www.youtube.com',
            'Vimeo'        => 'vimeo.com',
            'SlideShare'   => 'www.slideshare.net',
            'Glogster'     => 'www.glogster.com',
            'WikiEducator' => 'wikieducator.org',
            'Voki'         => 'voki.com',
        );
        foreach ($iframedomains as $name => $domain) {
            insert_record('iframe_source_icon', (object) array('name' => $name, 'domain' => $domain));
        }

        $table = new XMLDBTable('iframe_source');
        $table->addFieldInfo('prefix', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('prefix'));
        $table->addKeyInfo('namefk', XMLDB_KEY_FOREIGN, array('name'), 'iframe_source_icon', array('name'));
        create_table($table);

        $iframesources = array(
            'www.youtube.com/embed/'                   => 'YouTube',
            'player.vimeo.com/video/'                  => 'Vimeo',
            'www.slideshare.net/slideshow/embed_code/' => 'SlideShare',
            'www.glogster.com/glog/'                   => 'Glogster',
            'www.glogster.com/glog.php'                => 'Glogster',
            'edu.glogster.com/glog/'                   => 'Glogster',
            'edu.glogster.com/glog.php'                => 'Glogster',
            'wikieducator.org/index.php'               => 'WikiEducator',
            'voki.com/php/'                            => 'Voki',
        );
        foreach ($iframesources as $prefix => $name) {
            insert_record('iframe_source', (object) array('prefix' => $prefix, 'name' => $name));
        }

        $iframeregexp = '%^https?://(' . str_replace('.', '\.', implode('|', array_keys($iframesources))) . ')%';
        set_config('iframeregexp', $iframeregexp);
    }

    if ($oldversion < 2012042800) {
        $table = new XMLDBTable('usr_registration');
        $field = new XMLDBField('extra');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2012051500) {
        $table = new XMLDBTable('usr_registration');
        $field = new XMLDBField('authtype');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null, null, 'internal');
        add_field($table, $field);
        $key = new XMLDBKey('authtype');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('authtype'), 'auth_installed', array('name'));
        add_key($table, $key);
    }

    if ($oldversion < 2012053100) {
        // Clean url fields for usr, group, and view tables.
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('urlid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 30, null, null);
        add_field($table, $field);

        $index = new XMLDBIndex('urliduk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid'));
        add_index($table, $index);

        $table = new XMLDBTable('group');
        $field = new XMLDBField('urlid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 30, null, null);
        add_field($table, $field);

        $index = new XMLDBIndex('urliduk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid'));
        add_index($table, $index);

        $table = new XMLDBTable('view');
        $field = new XMLDBField('urlid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 100, null, null);
        add_field($table, $field);

        $index = new XMLDBIndex('urliduk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid', 'owner', 'group', 'institution'));
        add_index($table, $index);
    }

    if ($oldversion < 2012060100) {
        // Collection submission
        $table = new XMLDBTable('collection');

        $field = new XMLDBField('submittedgroup');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);

        $field = new XMLDBField('submittedhost');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255);
        add_field($table, $field);

        $field = new XMLDBField('submittedtime');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        add_field($table, $field);

        $key = new XMLDBKey('submittedgroupfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('submittedgroup'), 'group', array('id'));
        add_key($table, $key);

        $key = new XMLDBKey('submittedhostfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('submittedhost'), 'host', array('wwwroot'));
        add_key($table, $key);
    }

    if ($oldversion < 2012062900) {
        // Add site registration data tables
        $table = new XMLDBTable('site_registration');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('time', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);

        $table = new XMLDBTable('site_registration_data');
        $table->addFieldInfo('registration_id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('registration_id', 'field'));
        $table->addKeyInfo('regdatafk', XMLDB_KEY_FOREIGN, array('registration_id'), 'site_registration', array('id'));
        create_table($table);
    }

    if ($oldversion < 2012062901) {
        // Add institution registration data tables
        $table = new XMLDBTable('institution_registration');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('time', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        create_table($table);

        $table = new XMLDBTable('institution_registration_data');
        $table->addFieldInfo('registration_id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('registration_id', 'field'));
        $table->addKeyInfo('regdatafk', XMLDB_KEY_FOREIGN, array('registration_id'), 'institution_registration', array('id'));
        create_table($table);

        // Install a cron job to collection institution registration data
        $cron = new StdClass;
        $cron->callfunction = 'cron_institution_registration_data';
        $cron->minute       = rand(0,59);
        $cron->hour         = rand(0,23);
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = rand(0,6);
        insert_record('cron', $cron);
    }

    if ($oldversion < 2012062902) {
        // Add institution stats table
        $table = new XMLDBTable('institution_data');
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('ctime','institution','type'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        create_table($table);

        // Insert cron jobs to save institution data
        $cron = new StdClass;
        $cron->callfunction = 'cron_institution_data_weekly';
        $cron->minute       = 55;
        $cron->hour         = 23;
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = 6;
        insert_record('cron', $cron);

        $cron = new StdClass;
        $cron->callfunction = 'cron_institution_data_daily';
        $cron->minute       = 51;
        $cron->hour         = 23;
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2012070200) {
        $table = new XMLDBTable('collection');
        $field = new XMLDBField('group');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, null, null);
        add_field($table, $field);
        $field = new XMLDBField('institution');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null);
        add_field($table, $field);
        $field = new XMLDBField('owner');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null);
        change_field_notnull($table, $field);
        // For PostgresSQL, change_field_notnull of $field=owner with precision = 10 BIGINT(10)
        // will add a temporary column, move data from owner column, remove the column 'owner'
        // and then rename the temporary column to 'owner'. Therefore, all indexes and foreign keys
        // related to column 'owner' will be removed
        if (is_postgres()) {
            $key = new XMLDBKey('owner');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('owner'), 'usr', array('id'));
            add_key($table, $key);
        }
        $key = new XMLDBKey('group');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
        add_key($table, $key);
        $key = new XMLDBKey('institution');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        add_key($table, $key);
        // Add constraints
        execute_sql('ALTER TABLE {collection} ADD CHECK (
            (owner IS NOT NULL AND "group" IS NULL     AND institution IS NULL) OR
            (owner IS NULL     AND "group" IS NOT NULL AND institution IS NULL) OR
            (owner IS NULL     AND "group" IS NULL     AND institution IS NOT NULL)
        )');
    }

    if ($oldversion < 2012070300) {
        $table = new XMLDBTable('group');
        $field = new XMLDBField('groupparticipationreports');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2012080200) {
        $sql = "
            FROM {usr} u JOIN {auth_instance} ai ON (u.authinstance = ai.id)
            WHERE u.deleted = 0 AND ai.authname = 'internal' AND u.password != '*' AND u.salt != '*'
            AND u.password NOT LIKE '$%'";
        $pwcount = count_records_sql("SELECT COUNT(*) " . $sql);
        $sql = "
            SELECT u.id, u.password, u.salt" . $sql . " AND u.id > ?
            ORDER BY u.id";
        $done = 0;
        $lastid = 0;
        $limit = 2000;
        while ($users = get_records_sql_array($sql, array($lastid), 0, $limit)) {
            foreach ($users as $user) {
                // Wrap the old hashed password inside a SHA512 hash ($6$ is the identifier for SHA512)
                $user->password = crypt($user->password, '$6$' . substr(md5(get_config('passwordsaltmain') . $user->salt), 0, 16));

                // Drop the salt from the password as it may contain secrets that are not stored in the db
                // for example, the passwordsaltmain value
                $user->password = substr($user->password, 0, 3) . substr($user->password, 3+16);
                set_field('usr', 'password', $user->password, 'id', $user->id);
                remove_user_sessions($user->id);
                $lastid = $user->id;
            }
            $done += count($users);
            log_debug("Upgrading stored passwords: $done/$pwcount");
            set_time_limit(30);
        }
    }

    if ($oldversion < 2012080300) {
        // For multi-tokens we need '|' aka pipe characters either side of their old single token
        execute_sql('UPDATE {usr_account_preference} SET value = \'|\' || value || \'|\'
                            WHERE field=\'mobileuploadtoken\' AND NOT value ' . db_ilike() . '\'|%|\'');
    }

    if ($oldversion < 2012080600) {
        // Every minute, poll an imap mailbox to see if there are new mail bounces
        $cron = new StdClass;
        $cron->callfunction = 'check_imap_for_bounces';
        $cron->minute       = '*';
        $cron->hour         = '*';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2012080601) {
        $table = new XMLDBTable('group');
        $field = new XMLDBField('editwindowstart');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        add_field($table, $field);

        $field = new XMLDBField('editwindowend');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        add_field($table, $field);
    }

    if ($oldversion < 2013011700) {
        set_config('defaultregistrationexpirylifetime', 1209600);
    }

    if ($oldversion < 2013012100) {
        $event = (object)array(
            'name' => 'loginas',
        );
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2013012101) {
        $table = new XMLDBTable('event_log');
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('realusr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('data', XMLDB_TYPE_TEXT, null, null, null);
        $table->addFieldInfo('time', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('realusrfk', XMLDB_KEY_FOREIGN, array('realusr'), 'usr', array('id'));
        create_table($table);

        $cron = new StdClass;
        $cron->callfunction = 'cron_event_log_expire';
        $cron->minute       = 7;
        $cron->hour         = 23;
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2013020500) {
        $table = new XMLDBTable('artefact');
        $field = new XMLDBField('license');
        $field->setAttributes(XMLDB_TYPE_CHAR,255);
        add_field($table, $field);
        $field = new XMLDBField('licensor');
        $field->setAttributes(XMLDB_TYPE_CHAR,255);
        add_field($table, $field);
        $field = new XMLDBField('licensorurl');
        $field->setAttributes(XMLDB_TYPE_CHAR,255);
        add_field($table, $field);

        $table = new XMLDBTable('institution');
        $field = new XMLDBField('licensedefault');
        $field->setAttributes(XMLDB_TYPE_CHAR,255);
        add_field($table, $field);
        $field = new XMLDBField('licensemandatory');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        $table = new XMLDBTable('artefact_license');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('displayname', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addFieldInfo('shortname', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addFieldInfo('icon', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));
        create_table($table);
    }

    if ($oldversion < 2013020501) {
        require_once('license.php');
        install_licenses_default();
    }

    if ($oldversion < 2013032202) {
        require_once(get_config('libroot').'license.php');
        set_field('usr_account_preference', 'value', LICENSE_INSTITUTION_DEFAULT, 'field', 'licensedefault', 'value', '-');
    }

    if ($oldversion < 2013050700) {
        $table = new XMLDBTable('collection_tag');
        $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('tag', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('collection', 'tag'));
        $table->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN, array('collection'), 'collection', array('id'));
        create_table($table);
    }

    if ($oldversion < 2013062600) {
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('dropdownmenu');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2013081400) {
        // We've made a change to how update_safe_iframe_regex() generates the regex
        // Call this function to make sure the stored value reflects that change.
        update_safe_iframe_regex();
    }

    if ($oldversion < 2013082100) {
        log_debug('Update database for flexible page layouts feature');
        log_debug('1. Create table view_rows_columns');
        $table = new XMLDBTable('view_rows_columns');
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('row', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addFieldInfo('columns', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        create_table($table);

        log_debug('2. Remake the table view_layout as view_layout_columns');
        $table = new XMLDBTable('view_layout_columns');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('columns', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addFieldInfo('widths', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('columnwidthuk', XMLDB_KEY_UNIQUE, array('columns', 'widths'));
        create_table($table);

        log_debug('3. Alter table view_layout');
        $table = new XMLDBTable('view_layout');
        $field = new XMLDBField('rows');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
        $field = new XMLDBField('iscustom');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
        $field = new XMLDBField('layoutmenuorder');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        log_debug('4. Create table view_layout_rows_columns');
        $table = new XMLDBTable('view_layout_rows_columns');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('viewlayout', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('row', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addFieldInfo('columns', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('rowfk', XMLDB_KEY_FOREIGN, array('viewlayout'), 'view_layout', array('id'));
        $table->addKeyInfo('columnsfk', XMLDB_KEY_FOREIGN, array('columns'), 'view_layout_columns', array('id'));
        create_table($table);

        log_debug('5. Create table usr_custom_layout');
        $table = new XMLDBTable('usr_custom_layout');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('layout', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('layoutfk', XMLDB_KEY_FOREIGN, array('layout'), 'view_layout', array('id'));
        create_table($table);

        log_debug('6. Convert existing view_layout records into new-style view_layouts with just one row');
        $oldlayouts = get_records_array('view_layout', '', '', 'id', 'id, columns, widths');
        foreach ($oldlayouts as $layout) {
            // We don't actually need to populate the "rows", "iscustom" or "layoutmenuorder" columns,
            // because their defaults take care of that.

            // Check to see if there's a view_layout_columns record that matches its widths.
            $colsid = get_field('view_layout_columns', 'id', 'widths', $layout->widths);
            if (!$colsid) {
                $colsid = insert_record(
                        'view_layout_columns',
                        (object) array(
                                'columns' => $layout->columns,
                                'widths' => $layout->widths
                        ),
                        'id',
                        true
                );
            }

            // Now insert a record for it in view_layout_rows_columns, to represent its one row
            insert_record(
                'view_layout_rows_columns',
                (object) array(
                        'viewlayout' => $layout->id,
                        'row' => 1,
                        'columns' => $colsid
                )
            );

            // And also it needs a record in usr_custom_layout saying it belongs to the root user
            insert_record('usr_custom_layout', (object)array(
                'usr'    => 0,
                'layout' => $layout->id,
            ));

        }

        log_debug('7. Drop the obsolete view_layout.columns and view_layout.widths fields');
        $table = new XMLDBTable('view_layout');
        $field = new XMLDBField('columns');
        drop_field($table, $field);
        $field = new XMLDBField('widths');
        drop_field($table, $field);

        log_debug('8. Update default values for tables view_layout, view_layout_columns and view_layout_rows_columns');
        install_view_layout_defaults();

        log_debug('9. Update the table "block_instance"');
        $table = new XMLDBTable('block_instance');
        $field = new XMLDBField('row');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 1);
        // This one tends to take a while...
        set_time_limit(30);
        add_field($table, $field);
        set_time_limit(30);

        // Refactor the block_instance.viewcolumnorderuk key so it includes row.
        $key = new XMLDBKey('viewcolumnorderuk');
        $key->setAttributes(XMLDB_KEY_UNIQUE, array('view', 'column', 'order'));
        // If this particular site has been around since before Mahara 1.2, this
        // will actually have been created as a unique index rather than a unique
        // key, so check for that first.
        $indexname = find_index_name($table, $key);
        if (preg_match('/uix$/', $indexname)) {
            $index = new XMLDBIndex($indexname);
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('view', 'column', 'order'));
            drop_index($table, $index);
        }
        else {
            drop_key($table, $key);
        }
        $key = new XMLDBKey('viewrowcolumnorderuk');
        $key->setAttributes(XMLDB_KEY_UNIQUE, array('view', 'row', 'column', 'order'));
        add_key($table, $key);

        log_debug('10. Add a "numrows" column to the views table.');
        // The default value of "1" will be correct
        // for all existing views, because they're using the old one-row layout style
        $table = new XMLDBTable('view');
        $field = new XMLDBField('numrows');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);

        log_debug('11. Update the table "view_rows_columns" for existing pages');
        execute_sql('INSERT INTO {view_rows_columns} ("view", "row", "columns") SELECT v.id, 1, v.numcolumns FROM {view} v');
    }

    if ($oldversion < 2013091900) {
        // Create skin table...
        $table = new XMLDBTable('skin');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('title', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT);
        $table->addFieldInfo('owner', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 10, 'private', XMLDB_NOTNULL);
        $table->addFieldInfo('viewskin', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('bodybgimg', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('viewbgimg', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME);
        $table->addFieldInfo('mtime', XMLDB_TYPE_DATETIME);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('ownerfk', XMLDB_KEY_FOREIGN, array('owner'), 'usr', array('id'));
        create_table($table);

        // Create skin_favorites table...
        $table = new XMLDBTable('skin_favorites');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('user', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('favorites', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('userfk', XMLDB_KEY_FOREIGN, array('user'), 'usr', array('id'));
        create_table($table);

        // Create skin_fonts table...
        $table = new XMLDBTable('skin_fonts');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('title', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('licence', XMLDB_TYPE_CHAR, 255);
        $table->addFieldInfo('notice', XMLDB_TYPE_TEXT);
        $table->addFieldInfo('previewfont', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('variants', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('fonttype', XMLDB_TYPE_CHAR, 10, 'site', XMLDB_NOTNULL);
        $table->addFieldInfo('onlyheading', XMLDB_TYPE_INTEGER, 1, 0, XMLDB_NOTNULL);
        $table->addFieldInfo('fontstack', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('genericfont', XMLDB_TYPE_CHAR, 10, null, XMLDB_NOTNULL, null, XMLDB_ENUM, array('cursive', 'fantasy', 'monospace', 'sans-serif', 'serif'));
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('nameuk', XMLDB_KEY_UNIQUE, array('name'));
        create_table($table);

        // Set column 'skin' to 'view' table...
        $table = new XMLDBTable('view');
        $field = new XMLDBField('skin');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);

        require_once(get_config('libroot').'skin.php');
        install_skins_default();
    }

    if ($oldversion < 2013091901) {
        // Add a "skins" table to institutions to record whether they've enabled skins or not
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('skins');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1, 'dropdownmenu');
        add_field($table, $field);
    }

    if ($oldversion < 2013092300) {
        $table = new XMLDBTable('import_entry_requests');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('importid', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('entryid', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('entryparent', XMLDB_TYPE_CHAR, 255, null, null);
        $table->addFieldInfo('strategy', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ownerid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('entrytype', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('entrytitle', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('entrycontent', XMLDB_TYPE_TEXT, null, null, null);
        $table->addFieldInfo('duplicateditemids', XMLDB_TYPE_TEXT, null, null, null);
        $table->addFieldInfo('existingitemids', XMLDB_TYPE_TEXT, null, null, null);
        $table->addFieldInfo('artefactmapping', XMLDB_TYPE_TEXT, null, null, null);
        $table->addFieldInfo('decision', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('owneridfk', XMLDB_KEY_FOREIGN, array('ownerid'), 'usr', array('id'));
        create_table($table);
    }

    if ($oldversion < 2013092600) {
        //  When uploading file as attachment and attaching it to an artefact, the artefact id
        //  (in artefact field) and uploaded file artefact id (in attachment filed) are stored.
        //  For Resume composite types (educationhistory, employmenthistory, books, etc.) this
        //  is not enough. So we have to add item field to differentiate between e.g. different
        //  employments in employmenhistory and to which employment the user actually whishes to
        //  attach certain attachment...
        $table = new XMLDBTable('artefact_attachment');
        $field = new XMLDBField('item');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);
    }

    if ($oldversion < 2013112100) {
        // Add a new column 'last_processed_userid' to the table 'activity_queue' in order to
        // split multiple user activity notifications into chunks
        $table = new XMLDBTable('activity_queue');
        $field = new XMLDBField('last_processed_userid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        add_field($table, $field);
        $key = new XMLDBKey('last_processed_useridfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('last_processed_userid'), 'usr', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2013112600) {
        // If a mahara site was upgraded from 1.0 then keys for the following tables
        // may be missing so we will check for them and if missing add them.

        // Normally when we create a foreign key, we create an index alongside it.
        // If these keys were created by the 1.1 upgrade script, they will be missing
        // those indexes. To get the index and the key in place, we have to re-create
        // the key.

        $table = new XMLDBTable('artefact_access_usr');

        $index = new XMLDBIndex('usrfk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('usr'));
        if (!index_exists($table, $index)) {
            $field = new XMLDBField('usr');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            try {
                change_field_type($table, $field, true, true);
            }
            catch (SQLException $e) {
                log_warn("Couldn't change artefact_access_usr.usr column to NOT NULL (it probably contains some NULL values)");
            }

            $key = new XMLDBKey('usrfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column artefact_access_usr.usr referencing usr.id (the column probably contains some nonexistent user id's");
            }
        }

        $index = new XMLDBIndex('artefactfk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('artefact'));
        if (!index_exists($table, $index)) {
            $field = new XMLDBField('artefact');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            try {
                change_field_type($table, $field, true, true);
            }
            catch (SQLException $e) {
                log_warn("Couldn't change artefact_access_usr.artefact column to NOT NULL (it probably contains some NULL values)");
            }
            $key = new XMLDBKey('artefactfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column artefact_access_usr.artefact referencing artefact.id (the column probably contains some nonexistent artefact id's)");
            }
        }

        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('usr', 'artefact'));
        if (!db_key_exists($table, $key)) {
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a primary key on table artefact_access_usr across columns (usr, artefact). (Probably the table contains some non-unique values in those columns)");
            }
        }

        $table = new XMLDBTable('artefact_access_role');

        $index = new XMLDBIndex('artefactfk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('artefact'));
        if (!index_exists($table, $index)) {
            $field = new XMLDBField('artefact');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            try {
                change_field_type($table, $field, true, true);
            }
            catch (SQLException $e) {
                log_warn("Couldn't change artefact_access_role.artefact column to NOT NULL (it probably contains some NULL values)");
            }
            $key = new XMLDBKey('artefactfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column artefact_access_role.artefact referencing artefact.id (the column probably contains some nonexistente artefact id's)");
            }
        }

        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('role', 'artefact'));
        if (!db_key_exists($table, $key)) {
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a primary key on table artefact_access_role across columns (role, artefact). (Probably there are some non-unique values in those columns.)");
            }
        }

        $table = new XMLDBTable('artefact_attachment');

        $index = new XMLDBIndex('artefactfk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('artefact'));
        if (!index_exists($table, $index)) {
            try {
                add_index($table, $index);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a non-unique index on column artefact_attachment.artefact");
            }
        }

        $table = new XMLDBTable('group');

        $key = new XMLDBKey('grouptypefk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('grouptype'), 'grouptype', array('name'));
        if (!db_key_exists($table, $key)) {
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column group.grouptype referencing grouptype.name (the column probably contains some nonexistent grouptypes)");
            }
        }

        $table = new XMLDBTable('grouptype_roles');

        $index = new XMLDBIndex('grouptypefk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('grouptype'));
        if (!index_exists($table, $index)) {
            $key = new XMLDBKey('grouptypefk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('grouptype'), 'grouptype', array('name'));
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column grouptype_roles.grouptype referencing grouptype.name (the column probably contains some nonexistent grouptypes");
            }
        }

        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('grouptype', 'role'));
        if (!db_key_exists($table, $key)) {
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a primary key on table grouptype_roles across columns (grouptype, role). (Probably there are some non-unique values in those columns.)");
            }
        }

        $table = new XMLDBTable('view_autocreate_grouptype');

        $index = new XMLDBIndex('viewfk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('view'));
        if (!index_exists($table, $index)) {
            $field = new XMLDBField('view');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            try {
                change_field_type($table, $field, true, true);
            }
            catch (SQLException $e) {
                log_warn("Couldn't change column view_autocreate_grouptype.view to NOT NULL (probably the column contains some NULL values)");
            }
            $key = new XMLDBKey('viewfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column view_autocreate_grouptype.view referencing view.id (probably the column contains some nonexistent view IDs");
            }
        }

        $index = new XMLDBIndex('grouptypefk');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('grouptype'));
        if (!index_exists($table, $index)) {
            $key = new XMLDBKey('grouptypefk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('grouptype'), 'grouptype', array('name'));
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a foreign key on column view_autocreate_grouptype.grouptype referencing grouptype.name (probably the column contains some nonexistent grouptypes");
            }
        }

        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('view', 'grouptype'));
        if (!db_key_exists($table, $key)) {
            try {
                add_key($table, $key);
            }
            catch (SQLException $e) {
                log_warn("Couldn't set a primary key on table view_autocreate_grouptype across columns (view, grouptype). (Probably those columns contain some non-unique values.)");
            }
        }

    }

    if ($oldversion < 2013121300) {
        // view_rows_columns can be missing the 'id' column if upgrading from version
        // earlier than v1.8 and because we are adding a sequential primary column after
        // the table is already made we need to
        // - check that the column doesn't exist then add it without key or sequence
        // - update the values for the new id column to be sequential
        // - then add the primary key and finally make the column sequential
        if ($records = get_records_sql_array('SELECT * FROM {view_rows_columns}', array())) {
            if (empty($records[0]->id)) {
                $table = new XMLDBTable('view_rows_columns');
                $field = new XMLDBField('id');
                $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 1, 'view');
                add_field($table, $field);
                $x = 1;
                foreach ($records as $record) {
                    execute_sql('UPDATE {view_rows_columns} SET id = ? WHERE view = ? AND row = ? AND columns = ?',
                                array($x, $record->view, $record->row, $record->columns));
                    $x++;
                }
                // we can't add a sequence on a field unless it has a primary key
                $key = new XMLDBKey('primary');
                $key->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
                add_key($table, $key);
                $field = new XMLDBField('id');
                $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
                change_field_type($table, $field);
                // but when we change field type postgres drops the keys for the column so we need
                // to add the primary key back again - see line 2205 for more info
                if (is_postgres()) {
                    $key = new XMLDBKey('primary');
                    $key->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
                    add_key($table, $key);
                }
            }
        }
    }

    if ($oldversion < 2014010700) {

        // If the usr_custom_layout.group column exists, it indicates that we this patch has already
        // been run and we should skip it.
        $table = new XMLDBTable('usr_custom_layout');
        $field = new XMLDBField('group');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, null, null, 'usr');
        if (!field_exists($table, $field)) {
            // Add a log output line here so that we can tell whether this patch ran or not.
            log_debug('Correcting custom layout table structures.');

            // fix issue where custom layouts saved in groups, site pages and institutions
            // were set to have usr = 0 because view owner was null
            $table = new XMLDBTable('usr_custom_layout');
            $field = new XMLDBField('usr');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null);
            change_field_notnull($table, $field);
            // For PostgresSQL, change_field_notnull creates a temporary column, moves data to new temp column
            // and then renames the temp column to 'usr'. Therefore, all indexes and foreign keys
            // related to column 'owner' will be removed
            if (is_postgres()) {
                $key = new XMLDBKey('usr');
                $key->setAttributes(XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
                add_key($table, $key);
            }

            $field = new XMLDBField('group');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, null, null, 'usr');
            add_field($table, $field);
            $key = new XMLDBKey('groupfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
            add_key($table, $key);
            $field = new XMLDBField('institution');
            $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null, 'group');
            add_field($table, $field);
            $key = new XMLDBKey('institutionfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
            add_key($table, $key);

            // update previous records
            // get custom layouts with usr = 0 which are not in default set
            $groupcustomlayouts = get_records_sql_array('SELECT ucl.layout FROM {usr_custom_layout} ucl
                                                         LEFT JOIN {view_layout} vl ON vl.id = ucl.layout
                                                         WHERE usr = 0 AND iscustom = 1
                                                         ORDER BY ucl.id', array());
            if ($groupcustomlayouts != false) {
                foreach ($groupcustomlayouts as $groupcustomlayout) {
                    // find views using this custom layout
                    $views = get_records_array('view', 'layout', $groupcustomlayout->layout, '', 'owner, "group", institution');
                    if ($views != false) {
                        foreach ($views as $view) {
                            $group = $view->group;
                            $institution = $view->institution;
                            $owner = (!empty($institution) || !empty($group)) ? null : $view->owner;
                            $data = (object) array(
                                'usr' => $owner,
                                'group' => $group,
                                'institution' => $institution,
                                'layout' => $groupcustomlayout->layout,
                            );
                            $where = clone $data;
                            ensure_record_exists('usr_custom_layout', $where, $data);
                        }
                    }
                    // now remove this custom layout
                    $removedrecords = delete_records('usr_custom_layout', 'usr', '0', 'layout', $groupcustomlayout->layout);
                }
            }
        }
    }

    if ($oldversion < 2014010800) {
        $table = new XMLDBTable('institution_config');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $table->addIndexInfo('instfielduk', XMLDB_INDEX_UNIQUE, array('institution', 'field'));

        create_table($table);
    }

    if ($oldversion < 2014010801) {
        // adding institution column to allow for different site content for each institution
        $table = new XMLDBTable('site_content');
        $field = new XMLDBField('institution');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
        add_field($table, $field);

        // resetting the primary key and updating what is currently there to be
        // the 'mahara' institution's site pages
        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('name'));
        drop_key($table, $key);

        execute_sql("UPDATE {site_content} SET institution = ?", array('mahara'));

        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('name', 'institution'));
        add_key($table, $key);

        $key = new XMLDBKey('institutionfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        add_key($table, $key);

        // now add the default general pages for each existing institution with the values of
        // the 'mahara' institution. These can them be altered via Administration -> Institutions -> General pages
        $sitecontentarray = array();
        $sitecontents = get_records_array('site_content', 'institution', 'mahara');
        foreach ($sitecontents as $sitecontent) {
            $sitecontentarray[$sitecontent->name] = $sitecontent->content;
        }
        $pages = site_content_pages();
        $now = db_format_timestamp(time());
        $institutions = get_records_array('institution');
        foreach ($institutions as $institution) {
            if ($institution->name != 'mahara') {
                foreach ($pages as $name) {
                    $page = new stdClass();
                    $page->name = $name;
                    $page->ctime = $now;
                    $page->mtime = $now;
                    $page->content = $sitecontentarray[$name];
                    $page->institution = $institution->name;
                    insert_record('site_content', $page);
                    $pageconfig = new stdClass();
                    $pageconfig->institution = $institution->name;
                    $pageconfig->field = 'sitepages_' . $name;
                    $pageconfig->value = 'mahara';
                    insert_record('institution_config', $pageconfig);
                }
            }
        }
    }

    if ($oldversion < 2014021100) {
        // Reset the view's skin value, if the skin does not exist
        execute_sql("UPDATE {view} v SET skin = NULL WHERE v.skin IS NOT NULL AND NOT EXISTS (SELECT id FROM {skin} s WHERE v.skin = s.id)");
    }

    if ($oldversion < 2014021200) {
        // Adding new Creative Commons 4.0 licenses.
        // CC4.0 will be added only if:
        // -- The CC4.0 URL doesn't already exist;
        // -- And CC3.0 hasn't been deleted earlier.

        $license = new stdClass();
        $license->name = 'http://creativecommons.org/licenses/by-sa/4.0/';
        $license->displayname = get_string('licensedisplaynamebysa', 'install');
        $license->shortname = get_string('licenseshortnamebysa', 'install');
        $license->icon = 'license:by-sa.png';
        $version30 = 'http://creativecommons.org/licenses/by-sa/3.0/';
        if (!record_exists('artefact_license', 'name', $license->name) && record_exists('artefact_license', 'name', $version30) ) {
            insert_record('artefact_license', $license);
        }

        $license = new stdClass();
        $license->name = 'http://creativecommons.org/licenses/by/4.0/';
        $license->displayname = get_string('licensedisplaynameby', 'install');
        $license->shortname = get_string('licenseshortnameby', 'install');
        $license->icon = 'license:by.png';
        $version30 = 'http://creativecommons.org/licenses/by/3.0/';
        if (!record_exists('artefact_license', 'name', $license->name) && record_exists('artefact_license', 'name', $version30) ) {
            insert_record('artefact_license', $license);
        }

        $license = new stdClass();
        $license->name = 'http://creativecommons.org/licenses/by-nd/4.0/';
        $license->displayname = get_string('licensedisplaynamebynd', 'install');
        $license->shortname = get_string('licenseshortnamebynd', 'install');
        $license->icon = 'license:by-nd.png';
        $version30 = 'http://creativecommons.org/licenses/by-nd/3.0/';
        if (!record_exists('artefact_license', 'name', $license->name) && record_exists('artefact_license', 'name', $version30) ) {
            insert_record('artefact_license', $license);
        }

        $license = new stdClass();
        $license->name = 'http://creativecommons.org/licenses/by-nc-sa/4.0/';
        $license->displayname = get_string('licensedisplaynamebyncsa', 'install');
        $license->shortname = get_string('licenseshortnamebyncsa', 'install');
        $license->icon = 'license:by-nc-sa.png';
        $version30 = 'http://creativecommons.org/licenses/by-nc-sa/3.0/';
        if (!record_exists('artefact_license', 'name', $license->name) && record_exists('artefact_license', 'name', $version30) ) {
            insert_record('artefact_license', $license);
        }

        $license = new stdClass();
        $license->name = 'http://creativecommons.org/licenses/by-nc/4.0/';
        $license->displayname = get_string('licensedisplaynamebync', 'install');
        $license->shortname = get_string('licenseshortnamebync', 'install');
        $license->icon = 'license:by-nc.png';
        $version30 = 'http://creativecommons.org/licenses/by-nc/3.0/';
        if (!record_exists('artefact_license', 'name', $license->name) && record_exists('artefact_license', 'name', $version30) ) {
            insert_record('artefact_license', $license);
        }

        $license = new stdClass();
        $license->name = 'http://creativecommons.org/licenses/by-nc-nd/4.0/';
        $license->displayname = get_string('licensedisplaynamebyncnd', 'install');
        $license->shortname = get_string('licenseshortnamebyncnd', 'install');
        $license->icon = 'license:by-nc-nd.png';
        $version30 = 'http://creativecommons.org/licenses/by-nc-nd/3.0/';
        if (!record_exists('artefact_license', 'name', $license->name) && record_exists('artefact_license', 'name', $version30) ) {
            insert_record('artefact_license', $license);
        }
    }

    if ($oldversion < 2014022400) {
        // Make sure artefacts are properly locked for submitted views.
        // Can be a problem for older sites
        $submitted = get_records_sql_array("SELECT v.owner FROM {view_artefact} va
                        LEFT JOIN {view} v on v.id = va.view
                        LEFT JOIN {artefact} a on a.id = va.artefact
                        WHERE (v.submittedgroup IS NOT NULL OR v.submittedhost IS NOT NULL)", array());
        if ($submitted) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            foreach ($submitted as $record) {
                ArtefactType::update_locked($record->owner);
            }
        }
    }

    if ($oldversion < 2014022600) {
        $table = new XMLDBTable('host');
        $field = new XMLDBField('portno');
        drop_field($table, $field);
    }

    if ($oldversion < 2014032400) {
        $table = new XMLDBTable('group');
        $field = new XMLDBField('sendnow');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2014032500) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('probation');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2014032600) {
        set_config('watchlistnotification_delay', 20);

        if (!table_exists(new XMLDBTable('watchlist_queue'))) {
            $table = new XMLDBTable('watchlist_queue');
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('block', XMLDB_TYPE_INTEGER, 10, null, false);
            $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->addFieldInfo('changed_on', XMLDB_TYPE_DATETIME,  null, null, XMLDB_NOTNULL);
            $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
            $table->addKeyInfo('blockfk', XMLDB_KEY_FOREIGN, array('block'), 'block_instance', array('id'));
            $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            create_table($table);
        }

        // new event type: delete blockinstance
        $e = new stdClass();
        $e->name = 'deleteblockinstance';
        ensure_record_exists('event_type', $e, $e);

        // install the core event subscriptions
        $subs = array(
            array(
                'event'         => 'blockinstancecommit',
                'callfunction'  => 'watchlist_record_changes',
            ),
            array(
                'event'         => 'deleteblockinstance',
                'callfunction'  => 'watchlist_block_deleted',
            ),
            array(
                'event'         => 'saveartefact',
                'callfunction'  => 'watchlist_record_changes',
            ),
            array(
                'event'         => 'saveview',
                'callfunction'  => 'watchlist_record_changes',
            ),
        );

        foreach ($subs as $sub) {
            ensure_record_exists('event_subscription', (object)$sub, (object)$sub);
        }

        // install the cronjobs...
        $cron = new stdClass();
        $cron->callfunction = 'watchlist_process_notifications';
        $cron->minute       = '*';
        $cron->hour         = '*';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        ensure_record_exists('cron', $cron, $cron);
    }

    if ($oldversion < 2014032700) {
        // Remove bad data created by the upload user via csv where users in no institution
        // have 'licensedefault' set causing an error
        execute_sql("DELETE FROM {usr_account_preference} WHERE FIELD = 'licensedefault' AND usr IN (
                        SELECT u.id FROM {usr} u
                        LEFT JOIN {usr_institution} ui ON ui.usr = u.id
                        WHERE ui.institution = 'mahara' OR ui.institution is null
                     )");
    }

    if ($oldversion < 2014040300) {
        // Figure out where the magicdb is, and stick with that.
        require_once(get_config('libroot') . 'file.php');
        update_magicdb_path();
    }

    // Add id field and corresponding index to institution table.
    if ($oldversion < 2014040400) {

        $table = new XMLDBTable('institution');

        // Add id field.
        $field = new XMLDBField('id');
        if (!field_exists($table, $field)) {
            // Field.
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 1, 'name');
            add_field($table, $field);

            // Update ids.
            $institutions = get_records_array('institution');
            $x = 1;
            foreach ($institutions as $institution) {
                execute_sql('UPDATE {institution} SET id = ? WHERE name = ?', array($x, $institution->name));
                $x++;
            }

            $key = new XMLDBKey('inst_id_uk');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('id'));
            add_key($table, $key);

            // Add sequence.
            $field = new XMLDBField('id');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            change_field_type($table, $field);

            // In postgres, keys and indexes are removed when a field is changed ("Add sequence" above), so add the key back.
            if (is_postgres()) {
                $key = new XMLDBKey('inst_id_uk');
                $key->setAttributes(XMLDB_KEY_UNIQUE, array('id'));
                add_key($table, $key);
            }
        }
    }

    if ($oldversion < 2014041401) {
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('registerallowed');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, '0');
        change_field_default($table, $field);
    }

    if ($oldversion < 2014041600) {
        // Add allownonemethod and defaultmethod fields to activity_type table.
        $table = new XMLDBTable('activity_type');

        $field = new XMLDBField('allownonemethod');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null, null, 1, 'delay');
        add_field($table, $field);

        $field = new XMLDBField('defaultmethod');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null, 'email', 'allownonemethod');
        add_field($table, $field);

        // Allow null method in usr_activity_preference.
        // Null indicates "none", no record indicates "not yet set" so use the default.
        $table = new XMLDBTable('usr_activity_preference');
        $field = new XMLDBField('method');
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null);
        change_field_notnull($table, $field);
    }

    // Add about me block to existing profile template.
    if ($oldversion < 2014043000) {
        $systemprofileviewid = get_field('view', 'id', 'owner', 0, 'type', 'profile');

        // Find out how many blocks already exist.
        $maxorder = get_field_sql(
                'select max("order") from {block_instance} where "view"=? and "row"=? and "column"=?',
                array($systemprofileviewid, 1, 1)
        );

        // Create the block at the end of the cell.
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $aboutme = new BlockInstance(0, array(
            'blocktype'  => 'profileinfo',
            'title'      => get_string('aboutme', 'blocktype.internal/profileinfo'),
            'view'       => $systemprofileviewid,
            'row'        => 1,
            'column'     => 1,
            'order'      => $maxorder + 1,
        ));
        $aboutme->commit();

        // Move the block to the start of the cell.
        require_once(get_config('libroot') . 'view.php');
        $view = new View($systemprofileviewid);
        $view->moveblockinstance(array('id' => $aboutme->get('id'), 'row' => 1, 'column' => 1, 'order' => 1));
    }

    if ($oldversion < 2014050901) {
        require_once(get_config('docroot') . 'artefact/lib.php');

        // First drop artefact_parent_cache table.
        $table = new XMLDBTable('artefact_parent_cache');
        drop_table($table, true);

        // Remove cron jobs from DB.
        delete_records('cron', 'callfunction', 'rebuild_artefact_parent_cache_dirty');
        delete_records('cron', 'callfunction', 'rebuild_artefact_parent_cache_complete');

        // Add path field to artefact table.
        $table = new XMLDBTable('artefact');
        $field = new XMLDBField('path');
        $field->setAttributes(XMLDB_TYPE_CHAR, '1024', null, null, null, null, null);
        add_field($table, $field);

        // Fill the new field with path data.
        // Set all artefacts to the path they'd have if they have no parent.
        log_debug('Filling in parent artefact paths');
        if (get_config('searchplugin') == 'elasticsearch') {
            log_debug('Dropping elasticsearch artefact triggers');
            require_once(get_config('docroot') . 'search/elasticsearch/lib.php');
            ElasticsearchIndexing::drop_triggers('artefact');
        }
        $count = 0;
        $limit = 1000;
        $limitsmall = 200;
        $total = count_records_select('artefact', 'path IS NULL AND parent IS NULL');
        for ($i = 0; $i <= $total; $i += $limitsmall) {
            if (is_mysql()) {
                execute_sql("UPDATE {artefact} SET path = CONCAT('/', id) WHERE path IS NULL AND parent IS NULL LIMIT " . $limitsmall);
            }
            else {
                // Postgres can only handle limit in subquery
                execute_sql("UPDATE {artefact} SET path = '/' || id WHERE id IN (SELECT id FROM {artefact} WHERE path IS NULL AND parent IS NULL LIMIT " . $limitsmall . ")");
            }
            $count += $limitsmall;
            if (($count % $limit) == 0 || $count >= $total) {
                if ($count > $total) {
                    $count = $total;
                }
                log_debug("$count/$total");
                set_time_limit(30);
            }
        }
        $newcount = count_records_select('artefact', 'path IS NULL');
        if ($newcount) {
            $childlevel = 0;
            do {
                $childlevel++;
                $lastcount = $newcount;
                log_debug("Filling in level-{$childlevel} child artefact paths");
                if (is_postgres()) {
                    execute_sql("
                        UPDATE {artefact}
                        SET path = p.path || '/' || {artefact}.id
                        FROM {artefact} p
                        WHERE
                            {artefact}.parent=p.id
                            AND {artefact}.path IS NULL
                            AND p.path IS NOT NULL
                    ");
                }
                else {
                    execute_sql("
                        UPDATE
                            {artefact} a
                            INNER JOIN {artefact} p
                            ON a.parent = p.id
                        SET a.path=p.path || '/' || a.id
                        WHERE
                            a.path IS NULL
                            AND p.path IS NOT NULL
                    ");
                }
                $newcount = count_records_select('artefact', 'path IS NULL');
                // There may be some bad records whose paths can't be filled in,
                // so stop looping if the count stops going down.
            } while ($newcount > 0 && $newcount < $lastcount);
            log_debug("Done filling in child artefact paths");
        }
        if (get_config('searchplugin') == 'elasticsearch') {
            log_debug("Add triggers back in");
            ElasticsearchIndexing::create_triggers('artefact');
        }
    }

    // Make objectionable independent of view_access page.
    if ($oldversion < 2014060300) {
        log_debug("Create 'objectionable' table.");
        $table = new XMLDBTable('objectionable');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('objecttype', XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL);
        $table->addFieldInfo('objectid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('reportedby', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('report', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        $table->addFieldInfo('reportedtime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('resolvedby', XMLDB_TYPE_INTEGER, 10, null, null);
        $table->addFieldInfo('resolvedtime', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('reporterfk', XMLDB_KEY_FOREIGN, array('reportedby'), 'usr', array('id'));
        $table->addKeyInfo('resolverfk', XMLDB_KEY_FOREIGN, array('resolvedby'), 'usr', array('id'));
        $table->addIndexInfo('objectix', XMLDB_INDEX_NOTUNIQUE, array('objectid', 'objecttype'));

        create_table($table);

        // Migrate data to a new format.
        // Since we don't have report or name of the user, use root ID.
        // Table 'notification_internal_activity' contains data that is
        // not possible to extract in any reasonable way.
        $objectionable = get_records_array('view_access', 'accesstype', 'objectionable');

        db_begin();

        log_debug('Migrating objectionable records to new format');
        if (!empty($objectionable)) {
            $count = 0;
            $limit = 1000;
            $total = count($objectionable);
            foreach ($objectionable as $record) {
                $todb = new stdClass();
                $todb->objecttype = 'view';
                $todb->objectid   = $record->view;
                $todb->reportedby = 0;
                $todb->report = '';
                $todb->reportedtime = ($record->ctime) ? $record->ctime : format_date(time());
                if (!empty($record->stopdate)) {
                    // Since we can't get an ID of a user who resolved an issue, use root ID.
                    $todb->resolvedby = 0;
                    $todb->resolvedtime = $record->stopdate;
                }
                insert_record('objectionable', $todb);
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }

        // Delete data from 'view_access' table as we don't need it any more.
        delete_records('view_access', 'accesstype', 'objectionable');

        db_commit();

        log_debug("Drop constraint on 'view_access'");
        // Need to run this to avoid contraints problems on Postgres.
        if (is_postgres()) {
            execute_sql('ALTER TABLE {view_access} DROP CONSTRAINT {viewacce_acc_ck}');
        }

        log_debug("Update 'view_access' accesstype");
        // Update accesstype in 'view_access' not to use 'objectionable'.
        $table = new XMLDBTable('view_access');
        $field = new XMLDBField('accesstype');
        $field->setAttributes(XMLDB_TYPE_CHAR, 16, null, null, null, XMLDB_ENUM, array('public', 'loggedin', 'friends'));
        change_field_enum($table, $field);
    }

    if ($oldversion < 2014060500) {
        log_debug("Add 'artefact_access' table.");
        $table = new XMLDBTable('artefact_access');
        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('accesstype', XMLDB_TYPE_CHAR, 16, null, null, null, XMLDB_ENUM, array('public', 'loggedin', 'friends'));
        $table->addFieldInfo('group', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $table->addKeyInfo('groupfk', XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $table->addIndexInfo('accesstypeix', XMLDB_INDEX_NOTUNIQUE, array('accesstype'));

        create_table($table);
    }

    if ($oldversion < 2014061100) {
        log_debug('Add module related tables');
        $table = new XMLDBTable('module_installed');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('release', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('active', XMLDB_TYPE_INTEGER,  1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));

        create_table($table);

        $table = new XMLDBTable('module_cron');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('minute', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('hour', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('day', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('dayofweek', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('month', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('nextrun', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'callfunction'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'module_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('module_config');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'field'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'module_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('module_event_subscription');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'module_installed', array('name'));
        $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
        $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('plugin', 'event', 'callfunction'));

        create_table($table);
    }

    if ($oldversion < 2014062000) {
        log_debug('Fix up auth_clean_expired_password_requests cron');
        $where = array('callfunction' => 'auth_clean_expired_password_requests');
        $data = array('callfunction' => 'auth_clean_expired_password_requests',
                      'minute' => '5',
                      'hour' => '0',
                      'day' => '*',
                      'month' => '*',
                      'dayofweek' => '*',
                      );
        ensure_record_exists('cron', (object)$where, (object)$data);
    }

    if ($oldversion < 2014062500) {
        log_debug("Add 'feedbacknotify' option to 'group' table");
        require_once(get_config('libroot') . 'group.php');
        $table = new XMLDBTable('group');
        $field = new XMLDBField('feedbacknotify');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, GROUP_ROLES_ALL);
        if (!field_exists($table, $field)) {
            add_field($table, $field);
        }
    }

    if ($oldversion < 2014073100) {
        log_debug('Delete leftover data which are not associated to any institution');
        // Institution collections
        $collectionids = get_column_sql('
            SELECT id
            FROM {collection} c
            WHERE c.institution IS NOT NULL
                AND NOT EXISTS (SELECT 1 FROM {institution} i WHERE i.name = c.institution)');
        if ($collectionids) {
            require_once(get_config('libroot') . 'collection.php');
            $count = 0;
            $limit = 200;
            $total = count($collectionids);
            foreach ($collectionids as $collectionid) {
                $collection = new Collection($collectionid);
                $collection->delete();
                $count++;
                if (($count % $limit) == 0) {
                    log_debug("Deleting leftover collections: $count/$total");
                    set_time_limit(30);
                }
            }
            log_debug("Deleting leftover collections: $count/$total");
        }

        log_debug('Delete leftover custom layouts / usr registration');
        // Institution custom layouts and registration
        delete_records_sql('
            DELETE FROM {usr_custom_layout}
            WHERE {usr_custom_layout}.institution IS NOT NULL
                AND NOT EXISTS (SELECT 1 FROM {institution} i WHERE i.name = {usr_custom_layout}.institution)');
        delete_records_sql('
            DELETE FROM {usr_registration}
            WHERE {usr_registration}.institution IS NOT NULL
                AND NOT EXISTS (SELECT 1 FROM {institution} i WHERE i.name = {usr_registration}.institution)');
    }

    if ($oldversion < 2014081900) {
        log_debug("Check blocktype 'text' is installed");
        if ($data = check_upgrades('blocktype.text')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2014091600) {
        log_debug('Allow anonymous pages');
        $table = new XMLDBTable('view');
        $field = new XMLDBField('anonymise');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
        set_config('allowanonymouspages', 0);
    }


    if ($oldversion < 2014091800) {
        log_debug("Add 'allowarchives' column to the 'group' table");
        $table = new XMLDBTable('group');

        $field = new XMLDBField('allowarchives');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        log_debug("Add 'submittedstatus' column to 'view' table");
        $table = new XMLDBTable('view');

        $field = new XMLDBField('submittedstatus');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0, 'submittedtime');
        add_field($table, $field);

        log_debug("Need to update the submitted status for any existing views that are submitted");
        execute_sql('UPDATE {view} SET submittedstatus = 1 WHERE submittedgroup IS NOT NULL
                    AND submittedtime IS NOT NULL');
        log_debug("Add 'submittedstatus' column to 'collection' table");
        $table = new XMLDBTable('collection');

        $field = new XMLDBField('submittedstatus');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0, 'submittedtime');
        add_field($table, $field);

        log_debug('Need to update the submitted status for any existing collections that are submitted');
        execute_sql('UPDATE {collection} SET submittedstatus = 1 WHERE submittedgroup IS NOT NULL
                    AND submittedtime IS NOT NULL');

        log_debug('Adding the export queue / submission tables');
        // Add export queue table - each export is one row.
        $table = new XMLDBTable('export_queue');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 50);
        $table->addFieldInfo('exporttype', XMLDB_TYPE_CHAR, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('starttime', XMLDB_TYPE_DATETIME);
        $table->addFieldInfo('externalid', XMLDB_TYPE_CHAR, 255);
        $table->addFieldInfo('submitter', XMLDB_TYPE_INTEGER, 10); // for when the submitter is not the owner

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('submitterfk', XMLDB_KEY_FOREIGN, array('submitter'), 'usr', array('id'));

        create_table($table);

        // Add export queue items table which maps what views/collections/artefacts relate to the queue item.
        $table = new XMLDBTable('export_queue_items');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('exportqueueid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('exportqueuefk', XMLDB_KEY_FOREIGN, array('exportqueueid'), 'export_queue', array('id'));
        $table->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN, array('collection'), 'collection', array('id'));
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));

        create_table($table);

        // Add export archive table to hold info that will allow one to download the zip file
        $table = new XMLDBTable('export_archive');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('filename', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('filetitle', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('filepath', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('submission', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));

        create_table($table);

        // Add archived submissions table to hold submission info
        $table = new XMLDBTable('archived_submissions');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('archiveid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('group', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('externalhost', XMLDB_TYPE_CHAR, 50);
        $table->addFieldInfo('externalid', XMLDB_TYPE_CHAR, 255);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('groupfk', XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
        $table->addKeyInfo('archivefk', XMLDB_KEY_FOREIGN, array('archiveid'), 'export_archive', array('id'));

        create_table($table);

        // install the cronjob to process export queue
        $cron = new StdClass;
        $cron->callfunction = 'export_process_queue';
        $cron->minute = '*/6';
        $cron->hour = '*';
        $cron->day = '*';
        $cron->month = '*';
        $cron->dayofweek = '*';
        ensure_record_exists('cron', $cron, $cron);

        // install the cronjob to clean up deleted archived submissions items
        $cron = new StdClass;
        $cron->callfunction = 'submissions_delete_removed_archive';
        $cron->minute = '15';
        $cron->hour = '1';
        $cron->day = '1';
        $cron->month = '*';
        $cron->dayofweek = '*';
        ensure_record_exists('cron', $cron, $cron);
    }

    if ($oldversion < 2014092300) {
        log_debug('Add the socialprofile artefacttype');
        // Need to insert directly into the table instead of running upgrade_plugin(), so that we can transition
        // all the old social network artefact types into the new unified socialprofile type before deleting
        // the old types from artefact_installed_type
        insert_record('artefact_installed_type', (object)array('name'=>'socialprofile', 'plugin'=>'internal'));

        // Convert existing messaging types to socialprofile types.
        $oldmessagingfieldsarray = array('icqnumber', 'msnnumber', 'aimscreenname',
                                         'yahoochat', 'skypeusername', 'jabberusername');
        $oldmessagingfields = implode(',', array_map('db_quote', $oldmessagingfieldsarray));

        $sql = "SELECT * FROM {artefact}
                WHERE artefacttype IN (" . $oldmessagingfields . ")";
        if ($results = get_records_sql_assoc($sql, array())) {
            $count = 0;
            $limit = 1000;
            $total = count($results);
            safe_require('artefact', 'internal');
            foreach ($results as $result) {
                $i = new ArtefactTypeSocialprofile($result->id, (array)$result);
                $i->set('artefacttype', 'socialprofile');
                switch ($result->artefacttype) {
                    case 'aimscreenname':
                        $i->set('note', 'aim');
                        $i->set('description', get_string('aim', 'artefact.internal'));
                        break;
                    case 'icqnumber':
                        $i->set('note', 'icq');
                        $i->set('description', get_string('icq', 'artefact.internal'));
                        break;
                    case 'jabberusername':
                        $i->set('note', 'jabber');
                        $i->set('description', get_string('jabber', 'artefact.internal'));
                        break;
                    case 'msnnumber':
                    case 'skypeusername':
                        // MSN no longer exists and has been replaced by Skype.
                        $i->set('note', 'skype');
                        $i->set('description', get_string('skype', 'artefact.internal'));
                        break;
                    case 'yahoochat':
                        $i->set('note', 'yahoo');
                        $i->set('description', get_string('yahoo', 'artefact.internal'));
                        break;
                }
                $i->set('title', $result->title);
                $i->commit();
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }

        $sql = "SELECT value FROM {search_config} WHERE plugin='elasticsearch' AND field='artefacttypesmap'";
        if ($result = get_field_sql($sql, array())) {
            log_debug('Clean up elasticsearch fields for the old messaging fields');
            $artefacttypesmap_array = explode("\n", $result);
            $elasticsearchartefacttypesmap = array();
            foreach ($artefacttypesmap_array as $key => $value) {
                $tmpkey = explode("|", $value);
                if (count($tmpkey) == 3) {
                    if (!in_array($tmpkey[0], $oldmessagingfieldsarray)) {
                        // we're going to keep this one.
                        $elasticsearchartefacttypesmap[] = $value;
                    }
                }
            }
            // add socialprofile field.
            $elasticsearchartefacttypesmap[] = "socialprofile|Profile|Text";
            // now save the data excluding the old messaging fields.
            set_config_plugin('search', 'elasticsearch', 'artefacttypesmap', implode("\n", $elasticsearchartefacttypesmap));
        }

        log_debug('Delete unused, but still installed artefact types');
        delete_records_select("artefact_installed_type", "name IN (" . $oldmessagingfields . ")");

        log_debug('Install the social profile blocktype so users can see their migrated data');
        if ($data = check_upgrades('blocktype.internal/socialprofile')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2014092300) {
        log_debug("Install 'multirecipientnotification' plugin");
        if ($data = check_upgrades('module.multirecipientnotification')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2014101300) {
        log_debug("Make sure default notifications are not set to 'none'");
        // Make sure the 'system messages' and 'messages from other users' have a notification method set
        // It was possible after earlier upgrades to set method to 'none'.
        // Also make sure old defaultmethod is respected.
        $activitytypes = get_records_assoc('activity_type');
        foreach ($activitytypes as $type) {
            $type->defaultmethod = get_config('defaultnotificationmethod') ? get_config('defaultnotificationmethod') : 'email';
            if ($type->name == 'maharamessage' || $type->name == 'usermessage') {
                $type->allownonemethod = 0;
            }
            update_record('activity_type', $type);
        }

        // Make sure users have their 'system messages' and 'messages from other users' notification method set
        if ($useractivities = get_records_sql_assoc("SELECT * FROM {activity_type} at, {usr_activity_preference} uap
                                                     WHERE at.id = uap.activity
                                                     AND at.name IN ('maharamessage', 'usermessage')
                                                     AND (method IS NULL OR method = '')", array())) {
            foreach ($useractivities as $activity) {
                $userprefs = new stdClass();
                $userprefs->method = $activity->defaultmethod;
                update_record('usr_activity_preference', $userprefs, array('usr' => $activity->usr, 'activity' => $activity->activity));
            }
        }
    }

    if ($oldversion < 2014101500) {
        log_debug('Place skin fonts in their correct directories');
        if ($fonts = get_records_assoc('skin_fonts', 'fonttype', 'google')) {
            $fontpath = get_config('dataroot') . 'skins/fonts/';
            foreach ($fonts as $font) {
                // if google font is not already in subdir
                if (!is_dir($fontpath . $font->name)) {
                    if (file_exists($fontpath . $font->previewfont)) {
                        // we need to create the subdir and move the file into it
                        $newfontpath = $fontpath . $font->name . '/';
                        check_dir_exists($newfontpath, true, true);
                        rename ($fontpath . $font->previewfont, $newfontpath . $font->previewfont);
                        // and move the license file if it exists also
                        if (file_exists($fontpath . $font->licence)) {
                            rename ($fontpath . $font->licence, $newfontpath . $font->licence);
                        }
                    }
                    else {
                        // the file is not there for some reason so we might as well delete the font from the db
                        $result = delete_records('skin_fonts', 'name', $font->name);
                        if ($result !== false) {
                            // Check to see if the font is being used in a skin. If it is remove it from
                            // the skin's viewskin data
                            $skins = get_records_array('skin');
                            if (is_array($skins)) {
                                foreach ($skins as $skin) {
                                    $options = unserialize($skin->viewskin);
                                    foreach ($options as $key => $option) {
                                        if (preg_match('/font_family/', $key) && $option == $font->name) {
                                            require_once(get_config('docroot') . 'lib/skin.php');
                                            $skinobj = new Skin($skin->id);
                                            $viewskin = $skinobj->get('viewskin');
                                            $viewskin[$key] = false;
                                            $skinobj->set('viewskin', $viewskin);
                                            $skinobj->commit();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if ($oldversion < 2014101501) {
        log_debug('Unlock root user grouphomepage template in case it is locked');
        set_field('view', 'locked', 0, 'type', 'grouphomepage', 'owner', 0);
    }

    if ($oldversion < 2014110500) {
        log_debug('Add cacheversion and assign random string');
        // Adding cacheversion, as an arbitrary number appended to the end of JS & CSS files in order
        // to tell cacheing software when they've been updated. (Without having to use the Mahara
        // minor version for that purpose.)
        // Set this to a random starting number to make minor version slightly harder to detect
        if (!get_config('cacheversion')) {
            set_config('cacheversion', rand(1000, 9999));
        }
    }

    if ($oldversion < 2014110700) {
        log_debug("Add in 'shortcut' category to 'blocktype_category'");
        // Increment all the existing sorts by 1 to make room...
        $cats = get_records_array('blocktype_category', '', '', 'sort desc');
        foreach ($cats as $cat) {
            $cat->sort = $cat->sort + 1;
            update_record('blocktype_category', $cat, 'name');
        }

        $todb = new stdClass();
        $todb->name = 'shortcut';
        $todb->sort = '0';
        insert_record('blocktype_category', $todb);
    }

    if ($oldversion < 2014112700) {
        log_debug("Fix up group homepages so that no duplicate 'groupview' blocks are present");
        // Need to find the group homepages that have more than one groupview on them
        // and merge their data into one groupview as we shouldn't allow more than one groupview block
        // as it breaks pagination

        // First get any pages that have more than one groupview on them
        // and find the status of the groupview blocks
        if ($records = get_records_sql_array("SELECT v.id AS view, bi.id AS block FROM {view} v
            INNER JOIN {block_instance} bi ON v.id = bi.view
            WHERE v.id IN (
                SELECT v.id FROM {view} v
                 INNER JOIN {block_instance} bi ON v.id = bi.view
                 WHERE bi.blocktype = 'groupviews'
                  AND v.type = 'grouphomepage'
                 GROUP BY v.id
                 HAVING COUNT(v.id) > 1
            )
            AND bi.blocktype='groupviews'
            ORDER BY v.id, bi.id", array())) {
                require_once(get_config('docroot') . 'blocktype/lib.php');
                $lastview = 0;
                // set default
                $info = array();
                $x = -1;
                foreach ($records as $record) {
                    if ($lastview != $record->view) {
                        $x++;
                        $info[$x]['in']['showgroupviews'] = 0;
                        $info[$x]['in']['showsharedviews'] = 0;
                        $info[$x]['in']['view'] = $record->view;
                        $info[$x]['in']['block'] = $record->block;
                        $lastview = $record->view;
                    }
                    else {
                        $info[$x]['out'][] = $record->block;
                    }
                    $bi = new BlockInstance($record->block);
                    $configdata = $bi->get('configdata');
                    if (!empty($configdata['showgroupviews'])) {
                        $info[$x]['in']['showgroupviews'] = 1;
                    }
                    if (!empty($configdata['showsharedviews'])) {
                        $info[$x]['in']['showsharedviews'] = 1;
                    }
                }

                // now that we have info on the state of play we need to save one of the blocks
                // with correct data and delete the not needed blocks
                $count = 0;
                $limit = 1000;
                $total = count($info);
                foreach ($info as $item) {
                    $bi = new BlockInstance($item['in']['block']);
                    $configdata = $bi->get('configdata');
                    $configdata['showgroupviews'] = $item['in']['showgroupviews'];
                    $configdata['showsharedviews'] = $item['in']['showsharedviews'];
                    $bi->set('configdata', $configdata);
                    $bi->commit();
                    foreach ($item['out'] as $old) {
                        $bi = new BlockInstance($old);
                        $bi->delete();
                    }
                    $count++;
                    if (($count % $limit) == 0 || $count == $total) {
                        log_debug("$count/$total");
                        set_time_limit(30);
                    }
                }
        }
    }

    if ($oldversion < 2014121200) {
        log_debug('Remove layout preview thumbs directory');
        require_once('file.php');
        $layoutdir = get_config('dataroot') . 'images/layoutpreviewthumbs';
        if (file_exists($layoutdir)) {
            rmdirr($layoutdir);
        }
    }

    if ($oldversion < 2015013000) {
        log_debug("Add a 'sortorder' column to 'blocktype_installed_category'");
        // Add a sortorder column to blocktype_installed_category
        $table = new XMLDBTable('blocktype_installed_category');

        $field = new XMLDBField('sortorder');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 100000, 'category');
        add_field($table, $field);
    }

    if ($oldversion < 2015021000) {
        log_debug('Need to update any dashboard pages to not have skins');
        // and seen as we are updating and selecting from the same table
        // we need to use a temptable for it to work in mysql
        execute_Sql("UPDATE {view} SET skin = NULL WHERE id IN ( SELECT vid FROM (SELECT id AS vid FROM {view} WHERE type = 'dashboard' AND skin IS NOT NULL) AS temptable)");
    }

    if ($oldversion < 2015021900) {
        log_debug('Remove bbcode formatting from existing wall posts');
        require_once(get_config('docroot').'/lib/stringparser_bbcode/lib.php');
        if ($records = get_records_sql_array("SELECT id, text FROM {blocktype_wall_post} WHERE text LIKE '%[%'", array())) {
            foreach ($records as &$r) {
                $r->text = parse_bbcode($r->text);
                update_record('blocktype_wall_post', $r);
            }
        }
    }

    if ($oldversion < 2015030400) {
        log_debug("Update search config settings");
        if (get_config('searchusernames') === 1) {
            set_config('nousernames', 0);
        }
        else {
            set_config('nousernames', 1);
        }
        delete_records('config', 'field', 'searchusernames');
    }

    if ($oldversion < 2015032600) {
        log_debug("Update block categories for plugins");
        if ($blocktypes = plugins_installed('blocktype', true)) {
            foreach ($blocktypes as $bt) {
                install_blocktype_categories_for_plugin(blocktype_single_to_namespaced($bt->name, $bt->artefactplugin));
            }
        }
    }

    if ($oldversion < 2015033000) {
        log_debug("Updating TinyMCE emoticon locations in mahara database");
        // Seeing as tinymce has moved the location of the emoticons
        // we need to fix up a few places where users could have added emoticons.
        // $replacements is $value['table'] = table, $value['column'] = column
        $replacements = array(array('table' => 'view',
                              'column' => 'description'),
                        array('table' => 'artefact',
                              'column' => 'title'),
                        array('table' => 'artefact',
                              'column' => 'description'),
                        array('table' => 'group',
                              'column' => 'description'),
                        array('table' => 'interaction_forum_post',
                              'column' => 'body'),
                        array('table' => 'notification_internal_activity',
                              'column' => 'message'),
                        array('table' => 'blocktype_wall_post',
                              'column' => 'text'),
                        array('table' => 'site_content',
                              'column' => 'content'));
        foreach ($replacements as $key => $value) {
            execute_sql("UPDATE {" . $value['table'] . "} SET " . $value['column'] . " = REPLACE(" . $value['column'] . ", '/emotions/img', '/emoticons/img') WHERE " . $value['column'] . " LIKE '%/emotions/img%'");
        }
        // we need to handle block_instance configdata in a special way
        if ($results = get_records_sql_array("SELECT id FROM {block_instance} WHERE configdata LIKE '%/emotions/img%'", array())) {
            log_debug("Updating 'block_instance' data for TinyMCE");
            require_once(get_config('docroot') . 'blocktype/lib.php');
            $count = 0;
            $limit = 1000;
            $total = count($results);
            foreach ($results as $result) {
                $bi = new BlockInstance($result->id);
                $configdata = $bi->get('configdata');
                foreach ($configdata as $key => $value) {
                    $configdata[$key] = preg_replace('/\/emotions\/img/', '/emoticons/img', $value);
                }
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

    if ($oldversion < 2015041400) {
        log_debug('Force install of annotation and webservices plugins');
        if ($data = check_upgrades('artefact.annotation')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('auth.webservice')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2015042800) {
        log_debug('Clear Dwoo cache of unescaped institution names');
        require_once('dwoo/dwoo/dwooAutoload.php');
        @unlink(get_config('dataroot') . 'dwoo/compile/default' . get_config('docroot') . 'theme/raw/' . 'templates/view/accesslistrow.tpl.d'.Dwoo_Core::RELEASE_TAG.'.php');
        @unlink(get_config('dataroot') . 'dwoo/compile/default' . get_config('docroot') . 'theme/raw/' . 'templates/admin/users/accesslistitem.tpl.d'.Dwoo_Core::RELEASE_TAG.'.php');
    }

    if ($oldversion < 2015071500) {
        log_debug('Expanding the size of the import_entry_requests.entrycontent column');
        $table = new XMLDBTable('import_entry_requests');
        $field = new XMLDBField('entrycontent');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);
    }

    if ($oldversion < 2015072000) {
        // If we are upgrading from a site built before 2014092300 straight to 15.10
        // then the plugin won't exist as an artefact.
        if (table_exists(new XMLDBTable('artefact_multirecipient_userrelation'))) {
            log_debug('Change installation of artefact plugin multirecipentNotification to plugin module.');

            // first, drop the old triggers
            db_drop_trigger('update_unread_insert2', 'artefact_multirecipient_userrelation');
            db_drop_trigger('update_unread_update2', 'artefact_multirecipient_userrelation');
            db_drop_trigger('update_unread_delete2', 'artefact_multirecipient_userrelation');

            // rename tables artefact_multirecipientnotifiaction_notification and
            // Table: artefact_multirecipient_userrelation to module-prefix
            execute_sql("ALTER TABLE {artefact_multirecipient_notification} RENAME TO {module_multirecipient_notification}");
            execute_sql("ALTER TABLE {artefact_multirecipient_userrelation} RENAME TO {module_multirecipient_userrelation}");

            if (is_postgres()) {
                // Rename seq artefact_multirecipientnotifiaction_notification_id_seq and
                // artefact_multirecipient_userrelation_id_seq
                execute_sql("ALTER SEQUENCE {artefact_multirecipient_notification_id_seq} RENAME TO {module_multirecipient_notification_id_seq}");
                execute_sql("ALTER SEQUENCE {artefact_multirecipient_userrelation_id_seq} RENAME TO {module_multirecipient_userrelation_id_seq}");
            }

            //move event_subscrition entries for artefact plugin
            //multirecipientnotification to table module_event_subscription
            $subscriptions = get_records_array('artefact_event_subscription', 'plugin', 'multirecipientnotification');
            delete_records('artefact_event_subscription', 'plugin', 'multirecipientnotification');
            delete_records('artefact_installed_type', 'plugin', 'multirecipientnotification');
            $installrecord = get_record('artefact_installed', 'name', 'multirecipientnotification');
            if (is_object($installrecord)) {
                insert_record('module_installed', $installrecord);
                delete_records('artefact_installed', 'name', 'multirecipientnotification');
            }
            if (is_array($subscriptions)) {
                foreach ($subscriptions as $subscription) {
                    insert_record('module_event_subscription', $subscription, 'id');
                }
            }

            // recreate trigger
            safe_require('module', 'multirecipientnotification');
            PluginModuleMultirecipientnotification::postinst(0);
        }
    }

    if ($oldversion < 2015081000) {
        log_debug('Add user_login_data table to record when a user logs in');
        $table = new XMLDBTable('usr_login_data');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrloginfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        create_table($table);

        // Insert info about current users's logins
        $results = get_records_sql_array("SELECT id,lastlogin FROM {usr} WHERE deleted = 0 AND lastlogin IS NOT NULL");
        $count = 0;
        $limit = 1000;
        $total = count($results);
        foreach ($results as $result) {
            insert_record('usr_login_data', (object) array('usr' => $result->id, 'ctime' => $result->lastlogin));
            $count++;
            if (($count % $limit) == 0 || $count == $total) {
                log_debug("$count/$total");
                set_time_limit(30);
            }
        }
    }

    if ($oldversion < 2015081700) {
        // In 15.10, we changed the registration site policy.
        // We need to remind the site admins to register the site again with the new policy.
        log_debug('Remind the site admins to register the site again with the new policy');
        if (get_config('new_registration_policy') != -1) {
            set_config('new_registration_policy', true);
        }
        if (get_config('registration_sendweeklyupdates')) {
            set_config('registration_sendweeklyupdates', false);
        }
    }

    if ($oldversion < 2015082500) {
        // Add a site default portfolio page template
        log_debug('Add a site default portfolio page template');
        require_once('view.php');
        // Need to make sure 'root' user has admin privs for this task
        // and then make sure it doesn't afterwards
        update_record('usr', array('admin' => 1), array('id' => 0));
        install_system_portfolio_view();
        update_record('usr', array('admin' => 0), array('id' => 0));
    }

    if ($oldversion < 2015091700) {
        log_debug('Update cached customizable theme CSS');

        $styles = get_records_array('institution', 'theme', 'custom', 'id', 'displayname, style');
        if ($styles) {
            foreach ($styles as $newinstitution) {
                $styleid = $newinstitution->style;

                $properties = array();
                $record = (object) array('style' => $styleid);
                $proprecs = get_records_array('style_property', 'style', $styleid, 'field', 'field, value');
                foreach ($proprecs as $p) {
                    $properties[$p->field] = $p->value;
                }

                // Update the css
                $smarty = smarty_core();
                $smarty->assign('data', $properties);
                set_field('style', 'css', $smarty->fetch('customcss.tpl'), 'id', $styleid);
            }
        }
    }

    if ($oldversion < 2015100200) {
        log_debug('Upgrade comment plugin for threaded comments');
        if ($data = check_upgrades('artefact.comment')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2015110600) {
        log_debug('Expanding the size of the activity_queue.data column');
        $table = new XMLDBTable('activity_queue');
        $field = new XMLDBField('data');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);
    }

    if ($oldversion < 2016012800) {
        log_debug('Upgrade wall block and add wall notifications to default inbox block');
        if ($data = check_upgrades('blocktype.wall')) {
            upgrade_plugin($data);
        }
        // Find the inbox block from the dashboard template page
        $foundit = false;
        $dashboardtemplateid = get_field('view', 'id', 'type', 'dashboard', 'owner', 0, 'template', 1);
        $defaultinboxblocks = get_records_select_array('block_instance', 'view=? AND blocktype=?', array($dashboardtemplateid, 'inbox'));
        $specialinboxtitle = get_string('topicsimfollowing');
        if ($defaultinboxblocks) {
            safe_require('blocktype', 'inbox');
            foreach ($defaultinboxblocks as $blockrec) {
                // There are two default inbox blocks. One has just "newpost" notifications, which gives it a special title.
                // We want the other one.
                $bi = new BlockInstance($blockrec->id, $blockrec);
                if ($bi->get_title() == $specialinboxtitle) {
                    continue;
                }
                else {
                    $oldconfigdata = $blockrec->configdata;
                    $newconfigdata = unserialize($oldconfigdata);
                    $newconfigdata['wallpost'] = true;
                    $newconfigdata = serialize($newconfigdata);
                    log_debug('Updating all user inbox blocks that are still on the default settings');
                    execute_sql('UPDATE {block_instance} SET configdata=? WHERE blocktype=? AND configdata=?', array($newconfigdata, 'inbox', $oldconfigdata));
                    $foundit = true;
                }
            }
        }
        if (!$foundit) {
            log_debug('Couldn\'t find default inbox block, so it won\'t be updated.');
        }
    }

    if ($oldversion < 2016021200) {
        // Expanding the size of all the columns containing serialized data
        // to avoid errors with MySQL/MariaDB.

        log_debug('Expanding the size of the block_instance.configdata');
        $table = new XMLDBTable('block_instance');
        $field = new XMLDBField('configdata');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the config.value');
        $table = new XMLDBTable('config');
        $field = new XMLDBField('value');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the import_entry_requests.artefactmapping');
        $table = new XMLDBTable('import_entry_requests');
        $field = new XMLDBField('artefactmapping');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the import_entry_requests.duplicateditemids');
        $table = new XMLDBTable('import_entry_requests');
        $field = new XMLDBField('duplicateditemids');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the import_entry_requests.existingitemids');
        $table = new XMLDBTable('import_entry_requests');
        $field = new XMLDBField('existingitemids');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the import_queue.data');
        $table = new XMLDBTable('import_queue');
        $field = new XMLDBField('data');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the skin.viewskin');
        $table = new XMLDBTable('skin');
        $field = new XMLDBField('viewskin');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the skin_favorites.favorites');
        $table = new XMLDBTable('skin_favorites');
        $field = new XMLDBField('favorites');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the skin_fonts.variants');
        $table = new XMLDBTable('skin_fonts');
        $field = new XMLDBField('variants');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);

        log_debug('Expanding the size of the usr_registration.extra');
        $table = new XMLDBTable('usr_registration');
        $field = new XMLDBField('extra');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);
    }

    if ($oldversion < 2016030300) {
        log_debug('Removing obsolete Netherlands Antilles ".an" country type');
        safe_require('artefact', 'internal');
        if ($results = get_records_sql_assoc("SELECT * FROM {artefact} WHERE artefacttype = ? AND title = ?", array('country','an'))) {
            foreach ($results as $result) {
                $classname = generate_artefact_class_name($result->artefacttype);
                $a = new $classname($result->id);
                $a->delete();
            }
        }
    }

    if ($oldversion < 2016030400) {
        log_debug('Sorting out block_instance sort order drift');
        // There was an issue with the sorting of blocks (Bug #1523719) that existed since
        // Sept 2007, commit 02fb5d96 where the max order number does not equal the number
        // of blocks in the cell
        set_time_limit(120);
        if ($results = get_records_sql_array('SELECT b.id, b.view, b.row, b.column, b.order, maxorder, countorder
                                              FROM {block_instance} b
                                              JOIN (SELECT view AS sview, "row" AS srow, "column" AS scol, COUNT("order") AS countorder, MAX("order") AS maxorder
                                                  FROM {block_instance} GROUP BY view, "row", "column") AS myview
                                                ON myview.sview = b.VIEW AND myview.srow = b.row AND myview.scol = b.column
                                              WHERE maxorder != countorder
                                              ORDER BY b.view, b.row, b.column, b.order', array())) {
            // Structure the info into a more usable format
            $updates = array();
            foreach ($results as $r) {
                $updates[$r->view][$r->row][$r->column][] = array('order' => $r->order, 'id' => $r->id);
            }

            // There's a uniqueness constraint on the block_instance.order column, which gets in the way
            // when re-ordering them.
            //
            // If there are no negative values for block_instance.order (there shouldn't be) then we
            // can temporarily move the orders into negative numbers to get them "out of the way" while
            // re-ordering things.
            //
            // If there are negative sortorders, however, then we will just drop and re-create the index.
            $dropkey = record_exists_select('block_instance', '"order" < 0');
            if ($dropkey) {
                // Now recreating index again.
                $table = new XMLDBTable('block_instance');
                $constraint = new XMLDBKey('viewrowcolumnorderuk');
                $constraint->setAttributes(XMLDB_KEY_UNIQUE, array('view', 'row', 'column', 'order'));
                drop_key($table, $constraint);
            }

            // Now deal with the results
            foreach ($updates as $view => $grid) {
                foreach ($grid as $row => $columns) {
                    foreach ($columns as $column => $blocks) {
                        if (!$dropkey) {
                            foreach ($blocks as $key => $block) {
                                // First move them out of the way to avoid uniqueness clash
                                execute_sql('UPDATE {block_instance} SET "order" = ? WHERE id = ?', array(($block['order'] * -1), $block['id']));
                            }
                        }
                        foreach ($blocks as $key => $block) {
                            // Then update them with true order
                            execute_sql('UPDATE {block_instance} SET "order" = ? WHERE id = ?', array(($key + 1), $block['id']));
                        }
                    }
                }
                set_time_limit(30);
            }

            if ($dropkey) {
                add_key($table, $constraint);
            }
        }
    }

    if ($oldversion < 2016030600) {
        log_debug('Forcing the multirecipient notification plugin to be the default one');
        $result = get_field('module_installed', 'active', 'name', 'multirecipientnotification');
        if ($result === '0') {
            set_field('module_installed', 'active', 1, 'name', 'multirecipientnotification');
        }
        log_debug('Multirecipient notifications plugin active');
    }

    if ($oldversion < 2016031600) {
        log_debug('Removing the obsolete "view.numcolumns"  column');
        $table = new XMLDBTable('view');
        $field = new XMLDBField('numcolumns');
        drop_field($table, $field);
    }

    if ($oldversion < 2016032900) {
        log_debug('Expanding the size of the event_log.data');
        $table = new XMLDBTable('event_log');
        $field = new XMLDBField('data');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);
    }

    if ($oldversion < 2016033100) {
        log_debug('Upgrade openbadgedisplayer plugin');
        if ($data = check_upgrades('blocktype.openbadgedisplayer')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2016042100) {
        log_debug('Making site pages owned by "mahara" institution');
        // Change ownership of system templates: profile, dashboard, and group homepage
        // to institution = 'mahara' and template = 2
        execute_sql('
            UPDATE {view}
                SET owner = NULL, institution = ?, template = ?
            WHERE owner = ? AND template = ?'
        , array('mahara', 2, 0, 1));
    }

    if ($oldversion < 2016051600) {
        // Get all the group view blocks from groups that have 'Allow submissions' set to true.
        $sql = "SELECT bi.id, bi.configdata
                FROM {block_instance} bi
                INNER JOIN {view} v ON v.id = bi.view
                INNER JOIN {group} g ON g.id = v.group
                WHERE bi.blocktype = 'groupviews'
                AND v.type = 'grouphomepage'
                AND g.submittableto = 1";
        $groupviews = get_records_sql_array($sql, array());

        if ($groupviews) {
            log_debug("Processing 'Group page' blocks to set the allow submitted configuration if not already set");
            $count = 0;
            $limit = 1000;
            $total = count($groupviews);

            foreach ($groupviews as $groupview) {
                $configdata = unserialize($groupview->configdata);
                if (!isset($configdata['showsubmitted'])) {
                    $configdata['showsubmitted'] = 1;
                    set_field('block_instance', 'configdata', serialize($configdata), 'id', $groupview->id);
                }
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2016060800) {
        log_debug('Add an "mtime" field to usr_session table');
        $table = new XMLDBTable('usr_session');
        $field = new XMLDBField('mtime');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        if (!field_exists($table, $field)) {
            add_field($table, $field);
            // Fill in starting value for existing sessions.
            execute_sql('UPDATE {usr_session} SET mtime=ctime');
        }

        log_debug('Limit session_timeout to 30 days.');
        if (get_config('session_timeout') > 60 * 60 * 24 * 30) {
            set_config('session_timeout', 60 * 60 * 24 * 30);
        }
    }

    if ($oldversion < 2016061700) {
        log_debug('Add a "clearcaches" event');
        $e = new StdClass;
        $e->name = 'clearcaches';
        insert_record('event_type', $e);
    }

    if ($oldversion < 2016062200) {
        require_once(get_config('docroot') . 'lib/group.php');
        log_debug('Assign a unique shortname for each existing group that doesn\'t have one.');

        $groups = get_records_select_array(
            'group',
            "(shortname IS NULL OR shortname = '') AND deleted = 0"
        );

        if (!empty($groups)) {
            foreach ($groups as $group) {
                $group->shortname = group_generate_shortname($group->name);
                update_record('group', $group, 'id');
            }
        }
    }

    if ($oldversion < 2016062900) {
        log_debug('Assign an istitution for each existing group that doesn\'t have one.');
        $groups = execute_sql("UPDATE {group} SET institution = 'mahara'
                               WHERE (institution IS NULL OR institution = '') AND deleted = 0", array());
    }

    if ($oldversion < 2016070500) {
        log_debug('Extend sso_session.sessionid to 64 characters because we now use SHA-256 session ids.');
        $table = new XMLDBTable('sso_session');
        $field = new XMLDBField('sessionid');
        $field->setType(XMLDB_TYPE_CHAR);
        $field->setLength(64);
        $field->setNotNull(true);
        change_field_precision($table, $field);
    }

    if ($oldversion < 2016070700) {
        require_once('file.php');
        log_debug('Remove obsolete dataroot/smarty directory');
        rmdirr(get_config('dataroot') . 'smarty');
    }

    if ($oldversion < 2016070800) {
        log_debug('Add client_connections_institution table');
        $table = new XMLDBTable('client_connections_institution');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, false, XMLDB_NOTNULL);
        $table->addFieldInfo('plugintype', XMLDB_TYPE_CHAR, 255, false, XMLDB_NOTNULL);
        $table->addFieldInfo('pluginname', XMLDB_TYPE_CHAR, 255, false, XMLDB_NOTNULL);
        $table->addFieldInfo('priority', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('class', XMLDB_TYPE_CHAR, 255, false, XMLDB_NOTNULL);
        $table->addFieldInfo('connection', XMLDB_TYPE_CHAR, 255, false, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, false, XMLDB_NOTNULL);
        $table->addFieldInfo('url', XMLDB_TYPE_TEXT, 'small', false, XMLDB_NOTNULL);
        $table->addFieldInfo('username', XMLDB_TYPE_CHAR, 255, false);
        $table->addFieldInfo('password', XMLDB_TYPE_CHAR, 255, false);
        $table->addFieldInfo('consumer', XMLDB_TYPE_CHAR, 255, false);
        $table->addFieldInfo('secret', XMLDB_TYPE_CHAR, 255, false);
        $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 255, false);
        $table->addFieldInfo('header', XMLDB_TYPE_CHAR, 255, false);
        $table->addFieldInfo('useheader', XMLDB_TYPE_INTEGER, 1, false, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('certificate', XMLDB_TYPE_TEXT, 'small', false);
        $table->addFieldInfo('parameters', XMLDB_TYPE_TEXT, 'small', false);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 10, false, XMLDB_NOTNULL, null, null, null, 2);
        $table->addFieldInfo('authtype', XMLDB_TYPE_CHAR, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('json', XMLDB_TYPE_INTEGER, 1, false, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('enable', XMLDB_TYPE_INTEGER, 1, false, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('isfatal', XMLDB_TYPE_INTEGER, 1, false, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('version', XMLDB_TYPE_CHAR, 255, false);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('connectionk', XMLDB_INDEX_UNIQUE, array('name', 'class', 'connection', 'institution'));
        $table->addKeyInfo('institution', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        create_table($table);
        clear_menu_cache();
    }

    if ($oldversion < 2016070801) {
        log_debug('Adjusting the profile "introduction" field to store the tinymce data in description field rather than title field for consistency');
        if ($results = get_records_array('artefact', 'artefacttype', 'introduction', 'id', 'id')) {
            safe_require('artefact', 'internal');
            $count = 0;
            $limit = 1000;
            $total = count($results);
            foreach ($results as $result) {
                $introduction = new ArtefactTypeIntroduction($result->id);
                $introduction->commit();
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2016072200) {
        log_debug('Add primary key to view_access table');

        // See if we need to add the id column
        $table = new XMLDBTable('view_access');
        $field = new XMLDBField('id');
        if (!field_exists($table, $field)) {
            log_debug('Making a temp copy and adding id column');
            execute_sql('CREATE TEMPORARY TABLE {temp_view_access} AS SELECT DISTINCT * FROM {view_access}', array());
            if (is_mysql()) {
                // We've disabled the db_start() method for our MySQL driver, but since we're truncating view_access,
                // we really should start a transaction manually at least.
                execute_sql('START TRANSACTION');
            }
            execute_sql('TRUNCATE {view_access}', array());

            if (is_mysql()) {
                // MySQL requires the auto-increment column to be a primary key right away.
                execute_sql('ALTER TABLE {view_access} ADD id BIGINT(10) NOT NULL auto_increment PRIMARY KEY FIRST');
            }
            else {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
                add_field($table, $field);
            }

            log_debug('Adding back in the view_access information');
            // We will do in chuncks for large sites.
            $count = 0;
            $x = 0;
            $limit = 1000;
            $total = count_records('temp_view_access');
            for ($i = 0; $i <= $total; $i += $limit) {
                if (is_postgres()) {
                    $limitsql = ' OFFSET ' . $i . ' LIMIT ' . $limit;
                }
                else {
                    $limitsql = ' LIMIT ' . $i . ',' . $limit;
                }
                execute_sql('INSERT INTO {view_access} (view, accesstype, startdate, stopdate, allowcomments, approvecomments, "group", role, usr, token, visible, ctime, institution) SELECT view, accesstype, startdate, stopdate, allowcomments, approvecomments, "group", role, usr, token, visible, ctime, institution FROM {temp_view_access}' . $limitsql, array());
                $count += $limit;
                if (($count % ($limit *10)) == 0 || $count >= $total) {
                    if ($count > $total) {
                        $count = $total;
                    }
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
                set_time_limit(30);
            }
            if (is_mysql()) {
                execute_sql('COMMIT');
            }
            execute_sql('DROP TABLE {temp_view_access}', array());

            if (!is_mysql()) {
                log_debug('Adding primary key index to view_access.id column');
                $key = new XMLDBKey('primary');
                $key->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
                add_key($table, $key);
            }
        }
    }

    if ($oldversion < 2016072500) {
        log_debug('Drop obsolete column "accessconf" from "view" table');
        $table = new XMLDBTable('view');
        $field = new XMLDBField('accessconf');
        if (field_exists($table, $field)) {
            drop_field($table, $field);
        }

        log_debug('Updating usr.suspendedcusr from 0 to a valid site admin ID for users suspended via a cron task.');
        $admins = get_site_admins();
        $suspendinguserid = $admins[0]->id;
        set_field('usr', 'suspendedcusr', $suspendinguserid, 'suspendedcusr', 0);
    }

    if ($oldversion < 2016082400) {
        log_debug('Add a "ctime" column to import_entry_requests table.');
        $table = new XMLDBTable('import_entry_requests');
        $field = new XMLDBField('ctime');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        if (!field_exists($table, $field)) {
            add_field($table, $field);
            // Fill in starting value for existing rows.
            execute_sql('UPDATE {import_entry_requests} SET ctime = ?', array(db_format_timestamp(time())));
        }
    }

    if ($oldversion < 2016090200) {
        log_debug('Add a "framework" field to the collection table');
        $table = new XMLDBTable('collection');
        $field = new XMLDBField('framework');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        if (!field_exists($table, $field)) {
            add_field($table, $field);
        }
    }

    if ($oldversion < 2016090203) {
      log_debug('Add iframe url in iframe_source table for glogster educational new url pattern');
      delete_records('iframe_source', 'prefix', 'edu.glogster.com/glog/');
      delete_records('iframe_source', 'prefix', 'edu.glogster.com//glog/');
      if (!get_field('iframe_source', 'prefix', 'prefix', 'edu.glogster.com//?glog/') &&
          get_field('iframe_source_icon', 'name', 'name', 'Glogster')) {
          // Only insert the new Gloster URL if the icon has not been deleted by the user.
          insert_record('iframe_source', (object) array('prefix' => 'edu.glogster.com//?glog/', 'name' => 'Glogster'));
      }
      update_safe_iframe_regex();
    }

    if ($oldversion < 2016090206) {
        $cur_max_execution_time = @ini_get('max_execution_time');
        ini_set('max_execution_time', 0);
        log_debug('Fix broken user data for existing users created via webservices');
        if ($studentids = get_records_sql_array("
            SELECT u.id, u.studentid, 1 AS makenew FROM {usr} u
            WHERE (u.studentid IS NOT NULL AND u.studentid != '')
            AND NOT EXISTS (
                SELECT id FROM {artefact}
                WHERE artefacttype = 'studentid'
                AND owner = u.id
            )
            UNION
            SELECT u.id, u.studentid, 0 AS makenew FROM {usr} u
            JOIN {artefact} a ON (a.owner = u.id AND a.artefacttype='studentid')
            WHERE (u.studentid IS NOT NULL AND u.studentid != '')
            AND u.studentid != a.title", array())) {
            foreach ($studentids as $info) {
                set_profile_field($info->id, 'studentid', $info->studentid, (bool) $info->makenew);
            }
        }

        if ($preferrednames = get_records_sql_array("
            SELECT u.id, u.preferredname, 1 AS makenew FROM {usr} u
            WHERE (u.preferredname IS NOT NULL AND u.preferredname != '')
            AND NOT EXISTS (
                SELECT id FROM {artefact}
                WHERE artefacttype = 'preferredname'
                AND owner = u.id
            )
            UNION
            SELECT u.id, u.preferredname, 0 AS makenew FROM {usr} u
            JOIN {artefact} a ON (a.owner = u.id AND a.artefacttype='preferredname')
            WHERE (u.preferredname IS NOT NULL AND u.preferredname != '')
            AND u.preferredname != a.title", array())) {
            foreach ($preferrednames as $info) {
                set_profile_field($info->id, 'preferredname', $info->preferredname, (bool) $info->makenew);
            }
        }
        ini_set('max_execution_time', $cur_max_execution_time);
    }

    if ($oldversion < 2016090209) {
        log_debug('Removing any watchlist items for root user as they are not needed');
        delete_records('usr_watchlist_view', 'usr', 0);
    }

    if ($oldversion < 2016090210) {
        require_once(get_config('docroot') . 'lib/group.php');
        log_debug('Make sure all existing groups have a unique shortname.');
        $groups = get_records_sql_array("
            SELECT id, name, shortname FROM {group} WHERE shortname IN (
                SELECT shortname FROM {group} GROUP BY shortname HAVING COUNT(shortname) > 1
            )", array());
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $shortname = group_generate_shortname($group->shortname);
                if ($shortname != $group->shortname) {
                    log_warn('Duplicate group shortname "' . $group->shortname . '" for group "' . $group->name . '" changed to "' . $shortname . '"', true, false);
                    update_record('group', array('shortname' => $shortname), array('id' => $group->id));
                }
            }
        }

        log_debug('Changing group uniqueness constraint from (institution,shortname) to just (shortname)');
        // All shortnames should be unique now so we need to alter the unique key
        $table = new XMLDBTable('group');
        $index = new XMLDBIndex('shortnameuk');
        $index->setAttributes(XMLDB_KEY_UNIQUE, array('institution', 'shortname'));
        drop_index($table, $index);
        $index = new XMLDBIndex('shortnameuk');
        $index->setAttributes(XMLDB_KEY_UNIQUE, array('shortname'));
        add_index($table, $index);

    }

    return $status;
}
