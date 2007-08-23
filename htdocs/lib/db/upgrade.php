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

    return $status;

}
?>
