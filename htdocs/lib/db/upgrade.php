<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_core_upgrade($oldversion=0) {
    ini_set('max_execution_time', 120); // Let's be safe
    ini_set('memory_limit', '64M');

    $status = true;

    // we're saved by minupgradefrom, so upgrades previous to the most recent minupgradefrom can be always deleted.
    
    if ($oldversion < 2007081700) {
        // Remove groups from the system
        $table = new XMLDBTable('view_access_group');
        drop_table($table);

        $table = new XMLDBTable('usr_group_member');
        drop_table($table);

        $table = new XMLDBTable('usr_group');
        drop_table($table);
    }

    if ($oldversion < 2007081701) {
        // Rename the community tables to group
        if (is_postgres()) {
            // Done manually for postgres to ensure all of the indexes and constraints are renamed
            execute_sql("
            ALTER TABLE {community} RENAME TO {group};
            ALTER TABLE {community_id_seq} RENAME TO {group_id_seq};
            ALTER INDEX {comm_id_pk} RENAME TO {grou_id_pk};
            ALTER INDEX {comm_nam_uix} RENAME TO {grou_nam_uix};
            ALTER INDEX {comm_own_ix} RENAME TO {grou_own_ix};
            ALTER TABLE {group} DROP CONSTRAINT {comm_joi_ck};
            ALTER TABLE {group} ADD CONSTRAINT {grou_joi_ck} CHECK (jointype::text = 'controlled'::text OR jointype::text = 'invite'::text OR jointype::text = 'request'::text OR jointype::text = 'open'::text);
            ALTER TABLE {group} DROP CONSTRAINT {comm_own_fk};
            ALTER TABLE {group} ADD CONSTRAINT {grou_own_fk} FOREIGN KEY (\"owner\") REFERENCES {usr}(id);
            ");
             
            execute_sql("
            ALTER TABLE {community_member} RENAME TO {group_member};
            ALTER TABLE {group_member} RENAME community TO \"group\";
            ALTER INDEX {commmemb_commem_pk} RENAME TO {groumemb_gromem_pk};
            ALTER INDEX {commmemb_com_ix} RENAME TO {groumemb_gro_ix};
            ALTER INDEX {commmemb_mem_ix} RENAME TO {groumemb_mem_ix};
            ALTER TABLE {group_member} DROP CONSTRAINT {commmemb_com_fk};
            ALTER TABLE {group_member} ADD CONSTRAINT {groumemb_gro_fk}  FOREIGN KEY (\"group\") REFERENCES {group}(id);
            ALTER TABLE {group_member} DROP CONSTRAINT {commmemb_mem_fk};
            ALTER TABLE {group_member} ADD CONSTRAINT {groumemb_mem_fk} FOREIGN KEY (\"member\") REFERENCES {usr}(id);
            ");

            execute_sql("
            ALTER TABLE {community_member_request} RENAME TO {group_member_request};
            ALTER TABLE {group_member_request} RENAME community TO \"group\";
            ALTER INDEX {commmembrequ_commem_pk} RENAME TO {groumembrequ_gromem_pk};
            ALTER INDEX {commmembrequ_com_ix} RENAME TO {groumembrequ_gro_ix};
            ALTER INDEX {commmembrequ_mem_ix} RENAME TO {groumembrequ_mem_ix};
            ALTER TABLE {group_member_request} DROP CONSTRAINT {commmembrequ_com_fk};
            ALTER TABLE {group_member_request} ADD CONSTRAINT {groumembrequ_gro_fk} FOREIGN KEY (\"group\") REFERENCES {group}(id);
            ALTER TABLE {group_member_request} DROP CONSTRAINT {commmembrequ_mem_fk};
            ALTER TABLE {group_member_request} ADD CONSTRAINT {groumembrequ_mem_fk} FOREIGN KEY (member) REFERENCES {usr}(id);
            ");

            execute_sql("
            ALTER TABLE {community_member_invite} RENAME TO {group_member_invite};
            ALTER TABLE {group_member_invite} RENAME community TO \"group\";
            ALTER INDEX {commmembinvi_commem_pk} RENAME TO {groumembinvi_gromem_pk};
            ALTER INDEX {commmembinvi_com_ix} RENAME TO {groumembinvi_gro_ix};
            ALTER INDEX {commmembinvi_mem_ix} RENAME TO {groumembinvi_mem_ix};
            ALTER TABLE {group_member_invite} DROP CONSTRAINT {commmembinvi_com_fk};
            ALTER TABLE {group_member_invite} ADD CONSTRAINT {groumembinvi_gro_fk} FOREIGN KEY (\"group\") REFERENCES {group}(id);
            ALTER TABLE {group_member_invite} DROP CONSTRAINT {commmembinvi_mem_fk};
            ALTER TABLE {group_member_invite} ADD CONSTRAINT {groumembinvi_mem_fk} FOREIGN KEY (member) REFERENCES {usr}(id);
            ");

            execute_sql("
            ALTER TABLE {usr_watchlist_community} RENAME TO {usr_watchlist_group};
            ALTER TABLE {usr_watchlist_group} RENAME community TO \"group\";
            ALTER INDEX {usrwatccomm_usrcom_pk} RENAME TO {usrwatcgrou_usrgro_pk};
            ALTER INDEX {usrwatccomm_com_ix} RENAME TO {usrwatcgrou_gro_ix};
            ALTER INDEX {usrwatccomm_usr_ix} RENAME TO {usrwatcgrou_usr_ix};
            ALTER TABLE {usr_watchlist_group} DROP CONSTRAINT {usrwatccomm_com_fk};
            ALTER TABLE {usr_watchlist_group} ADD CONSTRAINT {usrwatcgrou_com_fk} FOREIGN KEY (\"group\") REFERENCES {group}(id);
            ALTER TABLE {usr_watchlist_group} DROP CONSTRAINT {usrwatccomm_usr_fk};
            ALTER TABLE {usr_watchlist_group} ADD CONSTRAINT {usrwatcgrou_usr_fk} FOREIGN KEY (usr) REFERENCES {usr}(id);
            ");

            execute_sql("
            ALTER TABLE {view_access_community} RENAME TO {view_access_group};
            ALTER TABLE {view_access_group} RENAME community TO \"group\";
            ALTER INDEX {viewaccecomm_com_ix} RENAME TO {viewaccegrou_gro_ix};
            ALTER INDEX {viewaccecomm_vie_ix} RENAME TO {viewaccegrou_vie_ix};
            ALTER TABLE {view_access_group} DROP CONSTRAINT {viewaccecomm_com_fk};
            ALTER TABLE {view_access_group} ADD CONSTRAINT {viewaccegrou_gro_fk} FOREIGN KEY (\"group\") REFERENCES {group}(id);
            ALTER TABLE {view_access_group} DROP CONSTRAINT {viewaccecomm_vie_fk};
            ALTER TABLE {view_access_group} ADD CONSTRAINT {viewaccegrou_vie_fk} FOREIGN KEY (\"view\") REFERENCES {view}(id);
            ");
        }
    }

    if ($oldversion < 2007082100) {
        // Fix two mistakes in the 0.8 upgrade:
        //
        // 1) Only the internal institituion had an internal auth instance added for it. This should have meant that all users not in the internal institution were locked out, but...
        // 2) All users were assigned to be in the internal auth instance, regardless of what institution they were in

        $users = get_records_array('usr', '', '', '', 'id, username, institution, authinstance');
        if ($users) {
            $authinstances = get_records_assoc('auth_instance', '', '', '', 'institution, id');
            foreach (array_keys($authinstances) as $key) {
                $authinstances[$key] = $authinstances[$key]->id;
            }
            foreach ($users as $user) {
                if (!isset($authinstances[$user->institution])) {
                    // There does not seem to be an authinstance set up for 
                    // this user's institution. We should fix that now.
                    $authinstance = (object)array(
                        'instancename' => 'internal',
                        'priority'     => 1,
                        'institution' => $user->institution,
                        'authname'     => 'internal'
                    );
                    
                    $authinstances[$user->institution] = insert_record('auth_instance', $authinstance, 'id', true);
                }
                if ($user->authinstance != $authinstances[$user->institution]) {
                    // Fix the user's authinstance
                    $user->authinstance = $authinstances[$user->institution];
                    update_record('usr', $user, 'id');
                }
            }
        }
    }

    if ($oldversion < 2007082200) {
        // Remove watchlist functionality apart from watching views
        $table = new XMLDBTable('usr_watchlist_group');
        drop_table($table);

        $table = new XMLDBTable('usr_watchlist_artefact');
        drop_table($table);

        $table = new XMLDBTable('usr_watchlist_view');
        $field = new XMLDBField('recurse');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, false, true, false, null, null, 1);
        drop_field($table, $field);
    }

    // Add the 'blockinstancecommit' event type
    if ($oldversion < 2007082201) {
        $event = (object)array(
            'name' => 'blockinstancecommit',
        );
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2007082202) {
        // Rename the community tables to group - mysql version.
        // This is really quite hacky. You can't rename columns with a foreign 
        // key on them, so you have to drop the key, rename the column, re-add 
        // the key and hope that nobody inserted anything inconsistent in the 
        // time you were renaming the column
        if (is_mysql()) {
            execute_sql("ALTER TABLE {community} RENAME TO {group}");

            execute_sql("ALTER TABLE {community_member} RENAME TO {group_member}");
            execute_sql("ALTER TABLE {group_member} DROP FOREIGN KEY commmemb_com_fk");
            execute_sql("ALTER TABLE {group_member} CHANGE community \"group\" BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {group_member} ADD FOREIGN KEY(\"group\") REFERENCES \"group\"(id)");

            execute_sql("ALTER TABLE {community_member_request} RENAME TO {group_member_request}");
            execute_sql("ALTER TABLE {group_member_request} DROP FOREIGN KEY commmembrequ_com_fk");
            execute_sql("ALTER TABLE {group_member_request} CHANGE community \"group\" BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {group_member_request} ADD FOREIGN KEY(\"group\") REFERENCES \"group\"(id)");

            execute_sql("ALTER TABLE {community_member_invite} RENAME TO {group_member_invite}");
            execute_sql("ALTER TABLE {group_member_invite} DROP FOREIGN KEY commmembinvi_com_fk");
            execute_sql("ALTER TABLE {group_member_invite} CHANGE community \"group\" BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {group_member_invite} ADD FOREIGN KEY(\"group\") REFERENCES \"group\"(id)");

            execute_sql("DROP TABLE {usr_watchlist_community}");

            execute_sql("ALTER TABLE {view_access_community} RENAME TO {view_access_group}");
            execute_sql("ALTER TABLE {view_access_group} DROP FOREIGN KEY viewaccecomm_com_fk");
            execute_sql("ALTER TABLE {view_access_group} CHANGE community \"group\" BIGINT(10) DEFAULT NULL");
            execute_sql("ALTER TABLE {view_access_group} ADD FOREIGN KEY(\"group\") REFERENCES \"group\"(id)");
        }
    }

    // VIEW REWORK MIGRATION
    if ($oldversion < 2007100203) {
        $table = new XMLDBTable('view_layout');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('columns', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('widths',  XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('columnwidthuk', XMLDB_KEY_UNIQUE, array('columns', 'widths'));

        create_table($table);

        $table = new XMLDBTable('view');
        $field = new XMLDBField('numcolumns');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, null);
        add_field($table, $field);
        set_field('view', 'numcolumns', 3);
        $field->setAttributes(XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        change_field_notnull($table, $field);

        $field = new XMLDBField('layout');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null);
        add_field($table, $field);
        $key = new XMLDBKEY('layoutfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('layout'), 'view_layout', array('id'));
        add_key($table, $key);

    
        // plugin contract tables for new blocktype plugin type.
        $table = new XMLDBTable('blocktype_category');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addkeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));

        create_table($table);

        $table = new XMLDBTable('blocktype_installed');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('release', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('active', XMLDB_TYPE_INTEGER,  1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('artefactplugin', XMLDB_TYPE_CHAR, 255);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));
        $table->addKeyInfo('artefactpluginfk', XMLDB_KEY_FOREIGN, array('artefactplugin'), 'artefact_installed', array('name'));

        create_table($table);
       
        $table = new XMLDBTable('blocktype_cron');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('minute', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('hour', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('day', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('dayofweek', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('month', XMLDB_TYPE_CHAR, 25, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('nextrun', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'callfunction'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'blocktype_installed', array('name'));
        
        create_table($table); 

        $table = new XMLDBTable('blocktype_config');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'field'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'blocktype_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('blocktype_event_subscription');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'blocktype_installed', array('name'));
        $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
        $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('plugin', 'event', 'callfunction'));

        create_table($table);

        $table = new XMLDBTable('blocktype_installed_category');
        $table->addFieldInfo('blocktype', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('category', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('blocktypefk', XMLDB_KEY_FOREIGN, array('blocktype'), 'blocktype_installed', array('name'));
        $table->addKeyInfo('categoryfk', XMLDB_KEY_FOREIGN, array('category'), 'blocktype_category', array('name'));

        create_table($table);
        
        $table = new XMLDBTable('block_instance');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('blocktype', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('title', XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('configdata', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFIeldInfo('column', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFIeldInfo('order', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED,  XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('blocktypefk', XMLDB_KEY_FOREIGN, array('blocktype'), 'blocktype_installed', array('name'));
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addKeyInfo('viewcolumnorderuk', XMLDB_KEY_UNIQUE, array('view', 'column', 'order'));

        create_table($table);
        

        // move old block field in view_artefact out of the way
        table_column('view_artefact', 'block', 'oldblock', 'text', '', '', null);

        $table = new XMLDBTable('view_artefact');
        $field = new XMLDBField('block');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null);
        add_field($table, $field);
        $key = new XMLDBKey('blockfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('block'), 'block_instance', array('id'));
        add_key($table, $key);

        // These fields will be dropped after the template migration. However, 
        // given that the table needs to be used by block instances being 
        // created, make the fields nullable during that time.
        // Note - XMLDB - you are a whore. Hate, Catalyst IT Ltd
        if (is_postgres()) {
            execute_sql('ALTER TABLE {view_artefact} ALTER ctime DROP NOT NULL');
            execute_sql('ALTER TABLE {view_artefact} ALTER format DROP NOT NULL');
        }
        else {
            execute_sql('ALTER TABLE {view_artefact} CHANGE ctime ctime DATETIME');
            execute_sql('ALTER TABLE {view_artefact} CHANGE format format TEXT');
        }

        // Install all the blocktypes and their categories now, as they'll be 
        // needed for the template migration
        install_blocktype_categories();
        foreach(array(
            'textbox', 'externalfeed', 'externalvideo',
            'file/image', 'file/filedownload', 'file/folder', 'file/internalmedia',
            'blog/blogpost', 'blog/blog', 'blog/recentposts',
            'resume/resumefield', 'resume/entireresume',
            'internal/profileinfo', 'internal/contactinfo') as $blocktype) {
            $data = check_upgrades("blocktype.$blocktype");
            upgrade_plugin($data);
        }

        // install the view column widths
        install_view_column_widths();

        // Run the template migration
        require_once(get_config('docroot') . 'lib/db/templatemigration.php');
        upgrade_template_migration();

        delete_records_select('view_artefact', 'block IS NULL');
        if (is_postgres()) {
            execute_sql('ALTER TABLE {view_artefact} ALTER block SET NOT NULL');
        }
        else {
            execute_sql('ALTER TABLE {view_artefact} CHANGE block block BIGINT(10) UNSIGNED NOT NULL');
        }

        $table = new XMLDBTable('view_artefact');
        $field = new XMLDBField('oldblock');
        drop_field($table, $field);

        $table = new XMLDBTable('view');

        // Especially for MySQL cos it's "advanced"
        $key = new XMLDBKey('templatefk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('template'), 'template', array('name'));
        drop_key($table, $key);

        $field = new XMLDBField('template');
        drop_field($table, $field);

        $table = new XMLDBTable('view_content');
        drop_table($table);

        $table = new XMLDBTable('template');
        drop_table($table);
        
        $table = new XMLDBTable('template_category');
        drop_table($table);

        $table = new XMLDBTable('view_artefact');
        $field = new XMLDBField('ctime');
        drop_field($table, $field);

        $field = new XMLDBField('format');
        drop_field($table, $field);
    
    }

    // Move files in dataroot into an 'originals' directory, and remove any 
    // cached images
    if ($oldversion < 2007082204) {
        require('file.php');
        foreach(array('artefact/file', 'artefact/internal/profileicons') as $dir) {
            $datadir = get_config('dataroot') . $dir;
            check_dir_exists("$datadir/originals");
            check_dir_exists("$datadir/resized");
            foreach (new DirectoryIterator($datadir) as $folder) {
                $name = $folder->getFilename();
                if (preg_match('/^\d+$/', $name)) {
                    log_debug("$dir: Moving folder {$name} to ${datadir}/originals");
                    rename("{$datadir}/{$name}", "{$datadir}/originals/{$name}");
                }
                else if (preg_match('/^\d+x\d+$/', $name)) {
                    log_debug("$dir: Deleting folder {$name}");
                    rmdirr("{$datadir}/{$name}");
                }
            }
        }

        // Last part - setting config variables for max width/height for images
        set_config('imagemaxwidth', 1024);
        set_config('imagemaxheight', 1024);
    }

    if ($oldversion < 2007082205) {
        set_field('activity_queue', 'type', 'viewaccess', 'type', 'newview');
        set_field('notification_internal_activity', 'type', 'viewaccess', 'type', 'newview');
        set_field('notification_emaildigest_queue', 'type', 'viewaccess', 'type', 'newview');
        delete_records('usr_activity_preference', 'activity', 'newview');
        delete_records('activity_type', 'name', 'newview');
    }

    // 0.9 - 1.0 upgrade begins here
    // Version numbers begin at one day after the release date of 0.9 - 
    // although many were actually done before that date. A side effect of 
    // parallel development

    if ($oldversion < 2007120800) {
        // Institution message activity type
        insert_record('activity_type', (object) array(
            'name' => 'institutionmessage',
            'admin' => 0,
            'delay' => 0
        ));
        if (in_array('email', array_map(create_function('$a', 'return $a->name;'),
                                        plugins_installed('notification')))) {
            $method = 'email';
        } else {
            $method = 'internal';
        }
        foreach (get_column('usr', 'id') as $userid) {
            insert_record('usr_activity_preference', (object) array(
                'usr' => $userid,
                'activity' => 'institutionmessage',
                'method' => $method
            ));
        }
    }

    if ($oldversion < 2007120801) {
        // create interaction tables

        $table = new XMLDBTable('interaction_installed');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('release', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        $table->addFieldInfo('active', XMLDB_TYPE_INTEGER,  1, null, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));

        create_table($table);
       
        $table = new XMLDBTable('interaction_cron');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('minute', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('hour', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('day', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('dayofweek', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('month', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('nextrun', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'callfunction'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'interaction_installed', array('name'));
        
        create_table($table); 

        $table = new XMLDBTable('interaction_config');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'field'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'interaction_installed', array('name'));

        create_table($table);

        $table = new XMLDBTable('interaction_event_subscription');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, 
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, null, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'interaction_installed', array('name'));
        $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
        $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('plugin', 'event', 'callfunction'));

        create_table($table);

        $table = new XMLDBTable('interaction_instance');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, null, null, null);
        $table->addFieldInfo('group', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('creator', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFIeldInfo('deleted', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'interaction_installed', array('name'));
        $table->addKeyInfo('groupfk', XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
        $table->addKeyInfo('creatorfk', XMLDB_KEY_FOREIGN, array('creator'), 'usr', array('id'));

        create_table($table);

    }

    if ($oldversion < 2007120802) {
        // move the activitytype table around
        $fks = array(
            'activity_queue' => 'type',
            'usr_activity_preference' => 'activity',
            'notification_internal_activity' => 'type',
            'notification_emaildigest_queue' => 'type',
        );
        // drop all the keys that fk to us
        foreach ($fks as $table => $field) {
            $xmldbtable = new XMLDBTable($table);
            $xmldbkey = new XMLDBKey($field . 'fk');
            $xmldbkey->setAttributes(XMLDB_KEY_FOREIGN, array($field), 'activity_type', array('name'));
            drop_key($xmldbtable, $xmldbkey);
        }

        // drop the primary key
        $typetable = new XMLDBTable('activity_type');
        $typekey = new XMLDBKey('primary');
        $typekey->setAttributes(XMLDB_KEY_PRIMARY, array('name'));
        drop_key($typetable, $typekey);

        if (is_mysql()) {
            // MySQL doesn't let us create a auto_increment (sequence) column 
            // unless it is also a key; an operation that XMLDB doesn't support
            execute_sql('ALTER TABLE {activity_type} ADD id BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY');
        }
        else {
            // create the new field
            $typefield = new XMLDBField('id');
            $typefield->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            add_field($typetable, $typefield);

            // create the new primary key
            $typekey = new XMLDBKey('primary');
            $typekey->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
            add_key($typetable, $typekey);
        }

        // MySQL hasn't dropped the primary key at this point, despite one of the fields disappearing
        if (is_mysql()) {
            $xmldbtable = new XMLDBTable('usr_activity_preference');
            $xmldbkey = new XMLDBKey('primary');
            $xmldbkey->setAttributes(XMLDB_KEY_PRIMARY, array('usr'));
            drop_key($xmldbtable, $xmldbkey);
        }

        foreach ($fks as $table => $field) {
            $xmldbtable = new XMLDBTable($table);
            $xmldbfield = new XMLDBField($field . 'new');
            $xmldbfield->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null);
            add_field($xmldbtable, $xmldbfield);
            $sql = 'UPDATE {' . $table . '} 
                SET ' . $field . 'new = 
                (SELECT id FROM {activity_type} t where ' . $field . ' = t.name)';
            execute_sql($sql);
            $xmldbfield->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            change_field_notnull($xmldbtable, $xmldbfield);
            drop_field($xmldbtable, new XMLDBField($field));
            rename_field($xmldbtable, $xmldbfield, $field);
            $xmldbkey = new XMLDBKey($field . 'fk');
            $xmldbkey->setAttributes(XMLDB_KEY_FOREIGN, array($field), 'activity_type', array('id'));
            add_key($xmldbtable, $xmldbkey);
        }

        // special case... 
        $table = new XMLDBTable('usr_activity_preference');
        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('usr', 'activity'));
        add_key($table, $key);
    
        // and now add the new fields to the activity_type table
        $pluginfield = new XMLDBField('plugintype');
        $pluginfield->setAttributes(XMLDB_TYPE_CHAR, 25);
        add_field($typetable, $pluginfield);
        $pluginfield = new XMLDBField('pluginname');
        $pluginfield->setAttributes(XMLDB_TYPE_CHAR, 255);
        add_field($typetable, $pluginfield);

        // and the unique key
        $key = new XMLDBKey('namepluginuk');
        $key->setAttributes(XMLDB_KEY_UNIQUE, array('name', 'plugintype', 'pluginname'));
        add_key($typetable, $key);
    }

    if ($oldversion < 2007121002) {

        $table = new XMLDBTable('usr_institution');
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null);
        $table->addFieldInfo('expiry', XMLDB_TYPE_DATETIME, null, null);
        $table->addFieldInfo('studentid', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('staff', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('admin', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('expirymailsent', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $table->addKeyInfo('usrinstitutionuk', XMLDB_KEY_PRIMARY, array('usr', 'institution'));
        create_table($table);

        $table = new XMLDBTable('usr_institution_request');
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('confirmedusr', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('confirmedinstitution', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('studentid', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $table->addKeyInfo('usrinstitutionuk', XMLDB_KEY_PRIMARY, array('usr', 'institution'));
        create_table($table);

        // From now on usernames will be unique, and remote xmlrpc
        // usernames will not be the same as local usernames.  The
        // mapping from remote usernames to local usr records will be
        // stored in auth_remote_user
        $table = new XMLDBTable('auth_remote_user');
        $table->addFieldInfo('authinstance', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('remoteusername', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('localusr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('authinstance', 'remoteusername'));
        $table->addKeyInfo('authinstancefk', XMLDB_KEY_FOREIGN, array('authinstance'), 'auth_instance', array('id'));
        $table->addKeyInfo('localusrfk', XMLDB_KEY_FOREIGN, array('localusr'), 'usr', array('id'));
        create_table($table);

        $theyssoin = get_column('auth_instance_config', 'instance', 'field', 'theyssoin', 'value', 1);
        if (empty($theyssoin)) {
            $theyssoin = array();
            $authparents = false;
        } else {
            $authparents = get_records_select_array('auth_instance_config', "
                field = 'parent' AND instance IN (" . join(',', $theyssoin) . ')');
        }

        $authchildren = array();
        if ($authparents) {
            foreach ($authparents as $a) {
                $authchildren[$a->value][] = $a->instance;
            }
        }

        $users = get_records_sql_array('
            SELECT
                u.id, u.username, u.institution, u.lastlogin, u.expiry, u.studentid, u.staff, u.admin,
                u.email, u.firstname, u.lastname, u.authinstance, ai.authname
            FROM {usr} u
            INNER JOIN {auth_instance} ai ON u.authinstance = ai.id', null);

        if ($users) {
            $usernames = get_column_sql("SELECT DISTINCT username FROM {usr}");
            $newusernames = array();
            $renamed = array();
            // Create new usr_institution records for every non-default institution
            // Make usernames unique
            foreach ($users as $u) {
                if ($u->institution != 'mahara') {
                    $ui = (object) array(
                        'usr' => $u->id,
                        'institution' => $u->institution,
                        'expiry' => $u->expiry,
                        'studentid' => $u->studentid,
                        'staff' => $u->staff,
                        'admin' => $u->admin
                    );
                    insert_record('usr_institution', $ui);
                }
                if (!isset($newusernames[$u->username])) {
                    $newname = $u->username;
                } else { // Rename the user
                    // Append digits keeping total length <= 30
                    $i = 1;
                    $newname = substr($u->username, 0, 29) . $i;
                    while (isset($newusernames[$newname]) || isset($usernames[$newname])) {
                        $i++;
                        $newname = substr($u->username, 0, 30 - floor(log10($i)+1)) . $i;
                    }
                    set_field('usr', 'username', $newname, 'id', $u->id);
                    $renamed[$newname] = $u;
                }
                $newusernames[$newname] = true;
                // Enter record in xmlrpc username list.
                if ($u->authname == 'xmlrpc' && in_array($u->authinstance, $theyssoin)
                    || $u->authname == 'imap' && isset($authchildren[$u->authinstance])) {
                    insert_record('auth_remote_user', (object) array(
                        'authinstance'   => $u->authinstance,
                        'remoteusername' => $u->username,
                        'localusr'       => $u->id,
                    ));
                    $u->ssoonly = $u->authname == 'xmlrpc';
                }
            }
        }

        if (is_postgres()) {
            execute_sql('DROP INDEX {usr_useaut_uix}');
        }
        else {
            execute_sql('DROP INDEX {usr_useaut_uix} ON {usr}');
            //execute_sql('DROP INDEX {usr_ins_ix} ON {usr}'); // this must be manually dropped for mysql
        }
        execute_sql('CREATE UNIQUE INDEX {usr_use_uix} ON {usr} (username)');
        //execute_sql('ALTER TABLE {usr} DROP COLUMN institution');


        // Rename users
        if (!empty($renamed)) {
            // Notify changed usernames to administrator
            $report = '# Each line in this file is in the form "institution old_username new_username"'."\n";
            $message = "Mahara now requires usernames to be unique.\n";
            $message .= "Some usernames on your site were changed during the upgrade:\n\n";
            foreach ($renamed as $newname => $olduser) {
                $report .= "$olduser->institution $olduser->username $newname\n";
                $message .= "Institution: $olduser->institution\n"
                    . "Old username: $olduser->username\n"
                    . "New username: $newname\n\n";
            }
            $sitename = get_config('sitename');
            $file = get_config('dataroot') . 'user_migration_report.txt';
            if (file_put_contents($file, $report)) {
                $message .= "\n" . 'A copy of this list has been saved to the file ' . $file;
            }
            global $USER;
            email_user($USER, null, $sitename . ': User migration', $message);
            // Notify changed usernames to users
            $usermessagestart = "Your username at $sitename has been changed:\n\n";
            $usermessageend = "\n\nNext time you visit the site, please login using your new username.";
            foreach ($renamed as $newname => $olduser) {
                // Don't notify sso-only users; they don't need to know
                // their usernames.
                if (empty($olduser->email) || !empty($olduser->ssoonly)) {
                    continue;
                }
                email_user($olduser, null, $sitename, $usermessagestart
                           . "Old username: $olduser->username\nNew username: $newname"
                           . $usermessageend);
            }
        }

        // Move site-wide stuff from institution table to config table
        $default = get_record('institution', 'name', 'mahara');
        $c = new StdClass;
        $c->field = 'defaultaccountinactivewarn';
        $c->value = empty($default->defaultaccountinactivewarn) ? 604800 : $default->defaultaccountinactivewarn;
        insert_record('config', $c);
        if (!empty($default->defaultaccountlifetime)) {
            $c->field = 'defaultaccountlifetime';
            $c->value = $default->defaultaccountlifetime;
            insert_record('config', $c);
        }

        if (!empty($default->defaultaccountinactiveexpire)) {
            $c->field = 'defaultaccountinactiveexpire';
            $c->value = $default->defaultaccountinactiveexpire;
            insert_record('config', $c);
        }

        execute_sql('ALTER TABLE {institution} DROP COLUMN defaultaccountlifetime;');
        execute_sql('ALTER TABLE {institution} DROP COLUMN defaultaccountinactiveexpire;');
        execute_sql('ALTER TABLE {institution} DROP COLUMN defaultaccountinactivewarn;');


        // New columns for institution table
        execute_sql('ALTER TABLE {institution} ADD COLUMN theme varchar(255)');
        set_field('institution', 'theme', get_config('theme'));

        if (is_mysql()) {
            $type = 'BIGINT(10)';
        }
        else {
            $type = 'INTEGER';
        }
        execute_sql('ALTER TABLE {institution} ADD COLUMN defaultmembershipperiod ' . $type);
        execute_sql('ALTER TABLE {institution} ADD COLUMN maxuseraccounts ' . $type);

    }

    if ($oldversion < 2008011400) {
        $table = new XMLDBTable('group');
        $field = new XMLDBField('deleted');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2008012400) {
        $blocktypes = get_column_sql(
            'SELECT DISTINCT blocktype
            FROM {blocktype_installed_category}
            WHERE category IN (\'file\', \'images\', \'multimedia\')'
        );
        delete_records_sql(
            'DELETE FROM {blocktype_installed_category}
            WHERE category IN (\'file\', \'images\', \'multimedia\')'
        );
        delete_records_sql(
            'DELETE FROM {blocktype_category}
            WHERE name IN (\'file\', \'images\', \'multimedia\')'
        );

        // Force the install of the new 'fileimagevideo' blocktype
        install_blocktype_categories();
    }

    if ($oldversion < 2008012401) {
        execute_sql('ALTER TABLE {usr_registration} ADD COLUMN lang TEXT');
    }

    if ($oldversion < 2008012500) {
        // _Really_ remove the institution field this time (also from install.xml!)
        if (is_mysql()) {
            // MySQL isn't smart enough to drop the FK first
            execute_sql('ALTER TABLE {usr} DROP FOREIGN KEY {usr_ins_fk}');
            execute_sql('ALTER TABLE {usr} DROP COLUMN institution');
        }
        else {
            $table = new XMLDBTable('usr');
            $field = new XMLDBField('institution');
            $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null, null, 'mahara');
            drop_field($table, $field);
        }

        // Remove the updateuserinfoonlogin column from the institution table. 
        // It's copied from Moodle, and not used by Mahara
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('updateuserinfoonlogin');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, null, null, null, null, 0);
        drop_field($table, $field);
    }

    if ($oldversion < 2008021200) {
        // Install the new auth plugins and the group forums
        if ($data = check_upgrades('auth.none')) {
            upgrade_plugin($data);
        }

        if ($data = check_upgrades('auth.ldap')) {
            upgrade_plugin($data);
        }

        if ($data = check_upgrades('interaction.forum')) {
            upgrade_plugin($data);
        }
    }

    // We discovered that username case insensitivity was not being enforced at 
    // most of the entry points to the system at which users can be created. 
    // This problem manifested itself as users who had the same LOWER(username) 
    // as another not being able to log in. The fix is to implement the checks, 
    // rename the "duplicate" users and add a constraint on the database so it 
    // can't happen again
    if ($oldversion < 2008040202) {
        $renamed = $newusernames = $oldusernames = array();
        $allusers = get_records_array('usr', '', '', 'id', 'id, username');

        $usernamemapping = array();
        foreach ($allusers as $user) {
            $oldusernames[] = $user->username;
            $usernamemapping[strtolower($user->username)][] = array('id' => $user->id, 'username' => $user->username);
        }

        foreach ($usernamemapping as $lcname => $users) {
            if (count($users) == 1) {
                continue;
            }

            // Uhohes. Rename the user(s) who were created last
            $skippedfirst = false;
            foreach ($users as $user) {
                if (!$skippedfirst) {
                    $skippedfirst = true;
                    continue;
                }

                $userobj = new User();
                $userobj->find_by_id($user['id']);

                // Append digits keeping total length <= 30
                $i = 1;
                $newname = substr($user['username'], 0, 29) . $i;
                while (isset($newusernames[$newname]) || isset($oldusernames[$newname])) {
                    $i++;
                    $newname = substr($user['username'], 0, 30 - floor(log10($i)+1)) . $i;
                }
                set_field('usr', 'username', $newname, 'id', $user['id']);
                $newusernames[$newname] = true;

                $renamed[$newname] = $userobj;
                log_debug(" * Renamed {$user['username']} to $newname");
            }
        }

        if (!empty($renamed)) {
            // Notify changed usernames to administrator
            $report = '# Each line in this file is in the form "old_username new_username"'."\n";
            $message = "Mahara now requires usernames to be unique, case insensitively.\n";
            $message .= "Some usernames on your site were changed during the upgrade:\n\n";
            foreach ($renamed as $newname => $olduser) {
                $report .= "$olduser->username $newname\n";
                $message .= "Old username: $olduser->username\n"
                    . "New username: $newname\n\n";
            }
            $sitename = get_config('sitename');
            $file = get_config('dataroot') . 'user_migration_report_2.txt';
            if (file_put_contents($file, $report)) {
                $message .= "\n" . 'A copy of this list has been saved to the file ' . $file;
            }
            global $USER;
            email_user($USER, null, $sitename . ': User migration', $message);
            // Notify changed usernames to users
            $usermessagestart = "Your username at $sitename has been changed:\n\n";
            $usermessageend = "\n\nNext time you visit the site, please login using your new username.";
            foreach ($renamed as $newname => $olduser) {
                if ($olduser->email == '') {
                    continue;
                }
                log_debug("Attempting to notify $newname ($olduser->email) of their new username...");
                email_user($olduser, null, $sitename . ': User name changed', $usermessagestart
                           . "Old username: $olduser->username\nNew username: $newname"
                           . $usermessageend);
            }
        }

        // Now we know all usernames are unique over their lowercase values, we 
        // can put an index in so data doesn't get all inconsistent next time
        if (is_postgres()) {
            execute_sql('DROP INDEX {usr_use_uix}');
            execute_sql('CREATE UNIQUE INDEX {usr_use_uix} ON {usr}(LOWER(username))');
        }
        else {
            // MySQL cannot create indexes over functions of columns. Too bad 
            // for it. We won't drop the existing index because that offers a 
            // large degree of protection, but when MySQL finally supports this 
            // we will be able to add it
        }


        // Install a cron job to delete old session files
        $cron = new StdClass;
        $cron->callfunction = 'auth_remove_old_session_files';
        $cron->minute       = '30';
        $cron->hour         = '20';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2008040203) {
        // Install a cron job to recalculate user quotas
        $cron = new StdClass;
        $cron->callfunction = 'recalculate_quota';
        $cron->minute       = '15';
        $cron->hour         = '2';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    if ($oldversion < 2008040204) {
        if (is_postgres()) {
            execute_sql('ALTER TABLE {usr_friend_request} RENAME COLUMN reason TO message');
        }
        else if (is_mysql()) {
            execute_sql('ALTER TABLE {usr_friend_request} CHANGE reason message TEXT');
        }
    }

    if ($oldversion < 2008080400) {
        // Group type refactor
        log_debug('GROUP TYPE REFACTOR');

        execute_sql('ALTER TABLE {group} ADD grouptype CHARACTER VARYING(20)');
        execute_sql('ALTER TABLE {group_member} ADD role CHARACTER VARYING(255)');

        $groups = get_records_array('group');
        if ($groups) {
            require_once(get_config('docroot') . 'grouptype/lib.php');
            require_once(get_config('docroot') . 'grouptype/standard/lib.php');
            require_once(get_config('docroot') . 'grouptype/course/lib.php');
            foreach ($groups as $group) {
                log_debug("Migrating group {$group->name} ({$group->id})");

                // Establish the new group type
                if ($group->jointype == 'controlled') {
                    $group->grouptype = 'course';
                }
                else {
                    $group->grouptype = 'standard';
                }

                execute_sql('UPDATE {group} SET grouptype = ? WHERE id = ?', array($group->grouptype, $group->id));
                log_debug(' * new group type is ' . $group->grouptype);

                // Convert group membership information to roles
                foreach (call_static_method('GroupType' . $group->grouptype, 'get_roles') as $role) {
                    if ($role == 'admin') {
                        // It would be nice to use ensure_record_exists here, 
                        // but because ctime is not null we have to provide it 
                        // as data, which means the ctime would be updated if 
                        // the record _did_ exist
                        if (get_record('group_member', 'group', $group->id, 'member', $group->owner)) {
                            execute_sql("UPDATE {group_member}
                                SET role = 'admin'
                                WHERE \"group\" = ?
                                AND member = ?", array($group->id, $group->owner));
                        }
                        else {
                            // In old versions of Mahara, there did not need to 
                            // be a record in the group_member table for the 
                            // owner
                            $data = (object) array(
                                'group'  => $group->id,
                                'member' => $group->owner,
                                'ctime'  => db_format_timestamp(time()),
                                'role' => 'admin',
                            );
                            insert_record('group_member', $data);
                        }
                        log_debug(" * marked user {$group->owner} as having the admin role");
                    }
                    else {
                        // Setting role instances for tutors and members
                        $tutorflag = ($role == 'tutor') ? 1 : 0;
                        execute_sql('UPDATE {group_member}
                            SET role = ?
                            WHERE "group" = ?
                            AND member != ?
                            AND tutor = ?', array($role, $group->id, $group->owner, $tutorflag));
                        log_debug(" * marked appropriate users as being {$role}s");
                    }
                }
            }
        }


        if (is_postgres()) {
            execute_sql('ALTER TABLE {group} ALTER grouptype SET NOT NULL');
            execute_sql('ALTER TABLE {group_member} ALTER role SET NOT NULL');
        }
        else if (is_mysql()) {
            execute_sql('ALTER TABLE {group} MODIFY grouptype CHARACTER VARYING(20) NOT NULL');
            execute_sql('ALTER TABLE {group_member} MODIFY role CHARACTER VARYING(255) NOT NULL');
        }

        if (is_mysql()) {
            execute_sql('ALTER TABLE {group} DROP FOREIGN KEY {grou_own_fk}');
        }

        execute_sql('ALTER TABLE {group} DROP owner');
        execute_sql('ALTER TABLE {group_member} DROP tutor');


        // Adminfiles become "institution-owned artefacts"
        execute_sql("ALTER TABLE {artefact} ADD COLUMN institution CHARACTER VARYING(255);");

        if (is_postgres()) {
            execute_sql("ALTER TABLE {artefact} ALTER COLUMN owner DROP NOT NULL;");
        }
        else if (is_mysql()) {
            execute_sql("ALTER TABLE {artefact} MODIFY owner BIGINT(10) NULL;");
        }

        execute_sql("ALTER TABLE {artefact} ADD CONSTRAINT {arte_ins_fk} FOREIGN KEY (institution) REFERENCES {institution}(name);");
        execute_sql("UPDATE {artefact} SET institution = 'mahara', owner = NULL WHERE id IN (SELECT artefact FROM {artefact_file_files} WHERE adminfiles = 1)");
        execute_sql("ALTER TABLE {artefact_file_files} DROP COLUMN adminfiles");
        execute_sql('ALTER TABLE {artefact} ADD COLUMN "group" BIGINT');
        execute_sql('ALTER TABLE {artefact} ADD CONSTRAINT {arte_gro_fk} FOREIGN KEY ("group") REFERENCES {group}(id)');


        // New artefact permissions for use with group-owned artefacts
        execute_sql('CREATE TABLE {artefact_access_role} (
            role VARCHAR(255) NOT NULL,
            artefact INTEGER NOT NULL REFERENCES {artefact}(id),
            can_view SMALLINT NOT NULL,
            can_edit SMALLINT NOT NULL,
            can_republish SMALLINT NOT NULL
        );');
        execute_sql('CREATE TABLE {artefact_access_usr} (
            usr INTEGER NOT NULL REFERENCES {usr}(id),
            artefact INTEGER NOT NULL REFERENCES {artefact}(id),
            can_republish SMALLINT
        );');


        // grouptype tables
        execute_sql("CREATE TABLE {grouptype} (
            name VARCHAR(20) PRIMARY KEY,
            submittableto SMALLINT NOT NULL,
            defaultrole VARCHAR(255) NOT NULL DEFAULT 'member'
        );");
        execute_sql("INSERT INTO {grouptype} (name,submittableto) VALUES ('standard',0)");
        execute_sql("INSERT INTO {grouptype} (name,submittableto) VALUES ('course',1)");

        execute_sql('CREATE TABLE {grouptype_roles} (
            grouptype VARCHAR(20) NOT NULL REFERENCES {grouptype}(name),
            edit_views SMALLINT NOT NULL DEFAULT 1,
            see_submitted_views SMALLINT NOT NULL DEFAULT 0,
            role VARCHAR(255) NOT NULL
        );');
        execute_sql("INSERT INTO {grouptype_roles} (grouptype,edit_views,see_submitted_views,role) VALUES ('standard',1,0,'admin')");
        execute_sql("INSERT INTO {grouptype_roles} (grouptype,edit_views,see_submitted_views,role) VALUES ('standard',1,0,'member')");
        execute_sql("INSERT INTO {grouptype_roles} (grouptype,edit_views,see_submitted_views,role) VALUES ('course',1,0,'admin')");
        execute_sql("INSERT INTO {grouptype_roles} (grouptype,edit_views,see_submitted_views,role) VALUES ('course',1,1,'tutor')");
        execute_sql("INSERT INTO {grouptype_roles} (grouptype,edit_views,see_submitted_views,role) VALUES ('course',0,0,'member')");

        if (is_postgres()) {
            $table = new XMLDBTable('group');
            $key = new XMLDBKey('grouptypefk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('grouptype'), 'grouptype', array('name'));
            add_key($table, $key);
        }
        else if (is_mysql()) {
            // Seems to refuse to create foreign key, not sure why yet
            execute_sql("ALTER TABLE {group} ADD INDEX {grou_gro_ix} (grouptype);");
            // execute_sql("ALTER TABLE {group} ADD CONSTRAINT {grou_gro_fk} FOREIGN KEY (grouptype) REFERENCES {grouptype} (name);");
        }


        // Group views
        execute_sql('ALTER TABLE {view} ADD COLUMN "group" BIGINT');
        execute_sql('ALTER TABLE {view} ADD CONSTRAINT {view_gro_fk} FOREIGN KEY ("group") REFERENCES {group}(id)');
        if (is_postgres()) {
            execute_sql('ALTER TABLE {view} ALTER COLUMN owner DROP NOT NULL');
            execute_sql('ALTER TABLE {view} ALTER COLUMN ownerformat DROP NOT NULL');
        }
        else if (is_mysql()) {
            execute_sql('ALTER TABLE {view} MODIFY owner BIGINT(10) NULL');
            execute_sql('ALTER TABLE {view} MODIFY ownerformat TEXT NULL');
        }
        execute_sql('ALTER TABLE {view_access_group} ADD COLUMN role VARCHAR(255)');
        execute_sql("UPDATE {view_access_group} SET role = 'tutor' WHERE tutoronly = 1");
        execute_sql('ALTER TABLE {view_access_group} DROP COLUMN tutoronly');


        // grouptype plugin tables
        $table = new XMLDBTable('grouptype_installed');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('release', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        $table->addFieldInfo('active', XMLDB_TYPE_INTEGER,  1, null, XMLDB_NOTNULL, null, null, null, 1);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('name'));
        create_table($table);
       
        $table = new XMLDBTable('grouptype_cron');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('minute', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('hour', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('day', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('dayofweek', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('month', XMLDB_TYPE_CHAR, 25, null, XMLDB_NOTNULL, null, null, null, '*');
        $table->addFieldInfo('nextrun', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'callfunction'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'grouptype_installed', array('name'));
        create_table($table); 

        $table = new XMLDBTable('grouptype_config');
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('plugin', 'field'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'grouptype_installed', array('name'));
        create_table($table);

        $table = new XMLDBTable('grouptype_event_subscription');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, 
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('plugin', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, null, XMLDB_NOTNULL);
        $table->addFieldInfo('callfunction', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('pluginfk', XMLDB_KEY_FOREIGN, array('plugin'), 'grouptype_installed', array('name'));
        $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
        $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('plugin', 'event', 'callfunction'));
        create_table($table);

        if ($data = check_upgrades('grouptype.standard')) {
            upgrade_plugin($data);
        }
        if ($data = check_upgrades('grouptype.course')) {
            upgrade_plugin($data);
        }


        // Group invitations take a role
        execute_sql('ALTER TABLE {group_member_invite} ADD COLUMN role VARCHAR(255)');


    }

    if ($oldversion < 2008081300) {
        $table = new XMLDBTable('import_queue');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('host', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('queue', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, '1');
        $table->addFieldInfo('ready', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('expirytime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('format', XMLDB_TYPE_CHAR, 50, null, null);
        $table->addFieldInfo('data', XMLDB_TYPE_TEXT, 'large', null, null);
        $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 40, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('hostfk', XMLDB_KEY_FOREIGN, array('host'), 'host', array('wwwroot'));

        create_table($table);
        // Install a cron job to process the queue
        $cron = new StdClass;

        $cron->callfunction = 'import_process_queue';
        $cron->minute       = '*/5';
        $cron->hour         = '*';
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = '*';
        insert_record('cron', $cron);
    }

    return $status;

}

?>
