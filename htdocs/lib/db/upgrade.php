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

function xmldb_core_upgrade($oldversion=0) {

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

    if ($oldversion < 2007082201) {
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
    if ($oldversion < 2007100200) {
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
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFIeldInfo('column', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFIeldInfo('order', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED,  XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('blocktypefk', XMLDB_KEY_FOREIGN, array('blocktype'), 'blocktype_installed', array('name'));
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addKeyInfo('viewcolumnorderuk', XMLDB_KEY_UNIQUE, array('view', 'column', 'order'));

        create_table($table);
        

        // move old block field in view_artefact out of the way
        table_column('view_artefact', 'block', 'oldblock', 'text');

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
        // Note - XMLDB - you are a whore. Hate, Nigel
        execute_sql('ALTER TABLE {view_artefact} ALTER ctime DROP NOT NULL');
        execute_sql('ALTER TABLE {view_artefact} ALTER format DROP NOT NULL');

        // Install all the blocktypes and their categories now, as they'll be 
        // needed for the template migration
        install_blocktype_categories();
        foreach(array('textbox', 'file/image', 'file/filedownload', 'blog/blogpost', 'blog/blog') as $blocktype) {
            $data = check_upgrades("blocktype.$blocktype");
            upgrade_plugin($data);
        }

        // install the view column widths
        install_view_column_widths();

        // Run the template migration
        require_once(get_config('docroot') . 'lib/db/templatemigration.php');
        upgrade_template_migration();

        // TODO - enable this again
        //execute_sql('ALTER TABLE {view_artefact} ALTER block SET NOT NULL');

        $table = new XMLDBTable('view_artefact');
        $field = new XMLDBField('oldblock');
        drop_field($table, $field);

        $table = new XMLDBTable('view');
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
    if ($oldversion < 2007082202) {
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

    return $status;

}

?>
