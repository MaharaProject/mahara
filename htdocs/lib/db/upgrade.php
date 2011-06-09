<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_core_upgrade($oldversion=0) {
    global $SESSION;
    ini_set('max_execution_time', 120); // Let's be safe
    raise_memory_limit('256M');

    $INNODB = (is_mysql()) ? ' TYPE=innodb' : '';
    $status = true;

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
        if (field_exists(new XMLDBTable('usr_friend_request'), new XMLDBField('reason'))) {
            if (is_postgres()) {
                execute_sql('ALTER TABLE {usr_friend_request} RENAME COLUMN reason TO message');
            }
            else if (is_mysql()) {
                execute_sql('ALTER TABLE {usr_friend_request} CHANGE reason message TEXT');
            }
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
        )' . $INNODB);
        execute_sql('CREATE TABLE {artefact_access_usr} (
            usr INTEGER NOT NULL REFERENCES {usr}(id),
            artefact INTEGER NOT NULL REFERENCES {artefact}(id),
            can_republish SMALLINT
        )' . $INNODB);


        // grouptype tables
        execute_sql("CREATE TABLE {grouptype} (
            name VARCHAR(20) PRIMARY KEY,
            submittableto SMALLINT NOT NULL,
            defaultrole VARCHAR(255) NOT NULL DEFAULT 'member'
        )" . $INNODB);
        execute_sql("INSERT INTO {grouptype} (name,submittableto) VALUES ('standard',0)");
        execute_sql("INSERT INTO {grouptype} (name,submittableto) VALUES ('course',1)");

        execute_sql('CREATE TABLE {grouptype_roles} (
            grouptype VARCHAR(20) NOT NULL REFERENCES {grouptype}(name),
            edit_views SMALLINT NOT NULL DEFAULT 1,
            see_submitted_views SMALLINT NOT NULL DEFAULT 0,
            role VARCHAR(255) NOT NULL
        )' . $INNODB);
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

    if ($oldversion < 2008081101) {
        execute_sql("ALTER TABLE {view} ADD COLUMN institution CHARACTER VARYING(255);");
        execute_sql("ALTER TABLE {view} ADD CONSTRAINT {view_ins_fk} FOREIGN KEY (institution) REFERENCES {institution}(name);");
        execute_sql("ALTER TABLE {view} ADD COLUMN template SMALLINT NOT NULL DEFAULT 0;");
    }

    if ($oldversion < 2008081102) {
        execute_sql("ALTER TABLE {view} ADD COLUMN copynewuser SMALLINT NOT NULL DEFAULT 0;");
        execute_sql('CREATE TABLE {view_autocreate_grouptype} (
            view INTEGER NOT NULL REFERENCES {view}(id),
            grouptype VARCHAR(20) NOT NULL REFERENCES {grouptype}(name)
        )' . $INNODB);
    }

    if ($oldversion < 2008090100) {
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

    if ($oldversion < 2008090800) {
        $table = new XMLDBTable('artefact_log');
        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, null);
        $table->addFieldInfo('time', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('title', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('parent', XMLDB_TYPE_INTEGER, 10, null, null);
        $table->addFieldInfo('created', XMLDB_TYPE_INTEGER, 1, null, null);
        $table->addFieldInfo('deleted', XMLDB_TYPE_INTEGER, 1, null, null);
        $table->addFieldInfo('edited', XMLDB_TYPE_INTEGER, 1, null, null);
        $table->addIndexInfo('artefactix', XMLDB_INDEX_NOTUNIQUE, array('artefact'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        create_table($table);
    }

    if ($oldversion < 2008091500) {
        // NOTE: Yes, this number is bigger than the number for the next upgrade
        // The next upgrade got committed first. It deletes all users properly, 
        // but the usr table has a 30 character limit on username, which can be 
        // violated when people with long usernames are deleted
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('username');
        $field->setAttributes(XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        change_field_precision($table, $field);
    }

    if ($oldversion < 2008091200) {
        // Some cleanups for deleted users, based on the new model of handling them
        if ($userids = get_column('usr', 'id', 'deleted', 1)) {
            foreach ($userids as $userid) {
                // We want to append 'deleted.timestamp' to some unique fields in the usr 
                // table, so they can be reused by new accounts
                $fieldstomunge = array('username', 'email');
                $datasuffix = '.deleted.' . time();

                $user = get_record('usr', 'id', $userid, null, null, null, null, implode(', ', $fieldstomunge));

                $deleterec = new StdClass;
                $deleterec->id = $userid;
                $deleterec->deleted = 1;
                foreach ($fieldstomunge as $field) {
                    if (!preg_match('/\.deleted\.\d+$/', $user->$field)) {
                        $deleterec->$field = $user->$field . $datasuffix;
                    }
                }

                // Set authinstance to default internal, otherwise the old authinstance can be blocked from deletion
                // by deleted users.
                $authinst = get_field('auth_instance', 'id', 'institution', 'mahara', 'instancename', 'internal');
                if ($authinst) {
                    $deleterec->authinstance = $deleterec->lastauthinstance = $authinst;
                }

                update_record('usr', $deleterec);

                // Because the user is being deleted, but their email address may be wanted 
                // for a new user, we change their email addresses to add 
                // 'deleted.[timestamp]'
                execute_sql("UPDATE {artefact_internal_profile_email}
                             SET email = email || ?
                             WHERE owner = ? AND NOT email LIKE '%.deleted.%'", array($datasuffix, $userid));

                // Remove remote user records
                delete_records('auth_remote_user', 'localusr', $userid);
            }
        }
    }

    if ($oldversion < 2008091601) {
        $table = new XMLDBTable('event_subscription');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
            $table->addFieldInfo('event', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addFieldInfo('callfunction',  XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);

            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('eventfk', XMLDB_KEY_FOREIGN, array('event'), 'event_type', array('name'));
            $table->addKeyInfo('subscruk', XMLDB_KEY_UNIQUE, array('event', 'callfunction'));

            create_table($table);
 
            insert_record('event_subscription', (object)array('event' => 'createuser', 'callfunction' => 'activity_set_defaults'));

            $table = new XMLDBTable('view_type');
            $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('type'));

            create_table($table);

            $viewtypes = array('portfolio', 'profile');
            foreach ($viewtypes as $vt) {
                insert_record('view_type', (object)array(
                    'type' => $vt,
                ));
            }

            $table = new XMLDBTable('blocktype_installed_viewtype');
            $table->addFieldInfo('blocktype', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addFieldInfo('viewtype', XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('blocktype', 'viewtype'));
            $table->addKeyInfo('blocktypefk', XMLDB_KEY_FOREIGN, array('blocktype'), 'blocktype_installed', array('name'));
            $table->addKeyInfo('viewtypefk', XMLDB_KEY_FOREIGN, array('viewtype'), 'view_type', array('type'));

            create_table($table);

            $table = new XMLDBTable('view');
            $field = new XMLDBField('type');
            $field->setAttributes(XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, null);
            add_field($table, $field);
            $key = new XMLDBKey('typefk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('type'), 'view_type', array('type'));
            add_key($table, $key);
            set_field('view', 'type', 'portfolio');
            $field->setAttributes(XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            change_field_notnull($table, $field);

            if ($blocktypes = plugins_installed('blocktype', true)) {
                foreach ($blocktypes as $bt) {
                    install_blocktype_viewtypes_for_plugin(blocktype_single_to_namespaced($bt->name, $bt->artefactplugin));
                }
            }
        }
    }

    if ($oldversion < 2008091603) {
        foreach(array('myviews', 'mygroups', 'myfriends', 'wall') as $blocktype) {
            $data = check_upgrades("blocktype.$blocktype");
            if ($data) {
                upgrade_plugin($data);
            }
        }
        if (!get_record('view', 'owner', 0, 'type', 'profile')) {
            // First ensure system user has id = 0; In older MySQL installations it may be > 0
            $sysuser = get_record('usr', 'username', 'root');
            if ($sysuser && $sysuser->id > 0 && !count_records('usr', 'id', 0)) {
                set_field('usr', 'id', 0, 'id', $sysuser->id);
            }
            // Install system profile view
            require_once(get_config('libroot') . 'view.php');
            $dbtime = db_format_timestamp(time());
            $viewdata = (object) array(
                'type'        => 'profile',
                'owner'       => 0,
                'numcolumns'  => 2,
                'ownerformat' => FORMAT_NAME_PREFERREDNAME,
                'title'       => get_string('profileviewtitle', 'view'),
                'description' => '',
                'template'    => 1,
                'ctime'       => $dbtime,
                'atime'       => $dbtime,
                'mtime'       => $dbtime,
            );
            $id = insert_record('view', $viewdata, 'id', true);
            $accessdata = (object) array('view' => $id, 'accesstype' => 'loggedin');
            insert_record('view_access', $accessdata);
            $blocktypes = array('myviews' => 1, 'mygroups' => 1, 'myfriends' => 2, 'wall' => 2);  // column ids
            $installed = get_column_sql('SELECT name FROM {blocktype_installed} WHERE name IN (' . join(',', array_map('db_quote', array_keys($blocktypes))) . ')');
            $weights = array(1 => 0, 2 => 0);
            foreach (array_keys($blocktypes) as $blocktype) {
                if (in_array($blocktype, $installed)) {
                    $weights[$blocktypes[$blocktype]]++;
                    insert_record('block_instance', (object) array(
                        'blocktype'  => $blocktype,
                        'title'      => get_string('title', 'blocktype.' . $blocktype),
                        'view'       => $id,
                        'column'     => $blocktypes[$blocktype],
                        'order'      => $weights[$blocktypes[$blocktype]],
                    ));
                }
            }
        }
    }

    if ($oldversion < 2008091604) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('lastlastlogin');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
        add_field($table, $field);
    }

    if ($oldversion < 2008092000) {
        $table = new XMLDBTable('usr');
        $field = new XMLDBField('lastaccess');
        $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
        add_field($table, $field);
    }

    // The previous upgrade forces the user to be logged out.  The
    // next upgrade should probably set disablelogin = false and
    // minupgradefrom = 2008092000 in version.php.

    if ($oldversion < 2008101500) {
        // Remove event subscription for new user accounts to have a default 
        // profile view created, they're now created on demand
        execute_sql("DELETE FROM {event_subscription} WHERE event = 'createuser' AND callfunction = 'install_default_profile_view';");
    }

    if ($oldversion < 2008101602) {
        $artefactdata = get_config('dataroot') . 'artefact/';
        if (is_dir($artefactdata . 'file/profileicons')) {
            throw new SystemException("Upgrade 2008101602: $artefactdata/file/profileicons already exists!");
        }

        // Move artefact/internal/profileicons directory to artefact/file
        set_field('artefact_installed_type', 'plugin', 'file', 'name', 'profileicon');
        set_field('artefact_config', 'plugin', 'file', 'field', 'profileiconwidth');
        set_field('artefact_config', 'plugin', 'file', 'field', 'profileiconheight');

        if (is_dir($artefactdata . 'internal/profileicons')) {
            if (!is_dir($artefactdata . 'file')) {
                mkdir($artefactdata . 'file');
            }
            if (!rename($artefactdata . 'internal/profileicons', $artefactdata . 'file/profileicons')) {
                throw new SystemException("Failed moving $artefactdata/internal/profileicons to $artefactdata/file/profileicons");
            }

            // Insert artefact_file_files records for all profileicons
            $profileicons = get_column('artefact', 'id', 'artefacttype', 'profileicon');
            if ($profileicons) {
                foreach ($profileicons as $a) {
                    $filename = $artefactdata . 'file/profileicons/originals/' . ($a % 256) . '/' . $a;
                    if (file_exists($filename)) {
                        $filesize = filesize($filename);
                        $imagesize = getimagesize($artefactdata . 'file/profileicons/originals/' . ($a % 256) . '/' . $a);
                        insert_record('artefact_file_files', (object) array('artefact' => $a, 'fileid' => $a, 'size' => $filesize));
                        insert_record('artefact_file_image', (object) array('artefact' => $a, 'width' => $imagesize[0], 'height' => $imagesize[1]));
                    } else {
                        log_debug("Profile icon artefact $a has no file on disk at $filename");
                    }
                }
            }
        }
    }

    if ($oldversion < 2008102200) {
        $table = new XMLDBTable('view_access_token');
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
        $table->addFieldInfo('token', XMLDB_TYPE_CHAR, 100, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('startdate', XMLDB_TYPE_DATETIME, null, null);
        $table->addFieldInfo('stopdate', XMLDB_TYPE_DATETIME, null, null);
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('token'));
        create_table($table);
    }

    if ($oldversion < 2008102400) {
        // Feedback can be left by anon users with a view token, so feedback author must be nullable
        $table = new XMLDBTable('view_feedback');
        if (is_mysql()) {
            execute_sql("ALTER TABLE {view_feedback} DROP FOREIGN KEY {viewfeed_aut_fk}");
            execute_sql('ALTER TABLE {view_feedback} MODIFY author BIGINT(10) NULL');
        }
        else {
            $field = new XMLDBField('author');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED);
            change_field_notnull($table, $field);
        }
        $key = new XMLDBKEY('authorfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('author'), 'usr', array('id'));
        add_key($table, $key);

        $table = new XMLDBTable('artefact_feedback');
        if (is_mysql()) {
            execute_sql("ALTER TABLE {artefact_feedback} DROP FOREIGN KEY {artefeed_aut_fk}");
            execute_sql('ALTER TABLE {artefact_feedback} MODIFY author BIGINT(10) NULL');
        }
        else {
            $field = new XMLDBField('author');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED);
            change_field_notnull($table, $field);
        }
        $key = new XMLDBKEY('authorfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('author'), 'usr', array('id'));
        add_key($table, $key);

        table_column('view_feedback', null, 'authorname', 'text', null, null, null, '');
        table_column('artefact_feedback', null, 'authorname', 'text', null, null, null, '');
    }

    if ($oldversion < 2008110700) {
        $table = new XMLDBTable('group');
        $field = new XMLDBField('public');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        set_config('createpublicgroups', 'admins');
    }

    if ($oldversion < 2008111102) {
        set_field('grouptype_roles', 'see_submitted_views', 1, 'grouptype', 'course', 'role', 'admin');
    }

    if ($oldversion < 2008111200) {
        // Event subscription for auto adding users to groups
        insert_record('event_subscription', (object)array('event' => 'createuser', 'callfunction' => 'add_user_to_autoadd_groups'));

        $table = new XMLDBTable('group');
        $field = new XMLDBField('usersautoadded');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2008111201) {
        $event = (object)array(
            'name' => 'userjoinsgroup',
        );
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2008110400) {
        // Correct capitalisation of internal authinstance for 'no institution', only if it hasn't changed previously
        execute_sql("UPDATE {auth_instance} SET instancename = 'Internal' WHERE institution = 'mahara' AND authname = 'internal' AND instancename = 'internal'");
    }

    if ($oldversion < 2008121500) {
        // Make sure the system profile view is marked as a template and is 
        // allowed to be copied by everyone
        require_once('view.php');
        execute_sql("UPDATE {view} SET template = 1 WHERE owner = 0 AND type = 'profile'");
        $viewid = get_field('view', 'id', 'owner', 0, 'type', 'profile');
        delete_records('view_access', 'view', $viewid);
        insert_record('view_access', (object) array('view' => $viewid, 'accesstype' => 'loggedin'));
    }

    if ($oldversion < 2008122300) {
        // Delete all activity_queue entries older than 2 weeks. Designed to 
        // prevent total spammage caused by the activity queue processing bug
        delete_records_select('activity_queue', 'ctime < ?', array(db_format_timestamp(time() - (86400 * 14))));
    }

    if ($oldversion < 2009011500) {
        // Make the "port" column larger so it can handle any port number
        $table = new XMLDBTable('host');
        $field = new XMLDBField('portno');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 80);
        change_field_precision($table, $field);
    }

    if ($oldversion < 2009021600) {
        // Add constraints on view and artefact tables to make sure that of the 
        // owner/group/institution fields, only one is set at any given time

        // First, we make blind assumptions in order to tweak the data into 
        // being valid. In theory, there shouldn't be much danger because most 
        // people will upgrade from 1.0 to 1.1, and thus never have invalid 
        // data in their tables.
        execute_sql('UPDATE {artefact} SET owner = NULL WHERE institution IS NOT NULL');
        execute_sql('UPDATE {artefact} SET "group" = NULL WHERE institution IS NOT NULL');
        execute_sql('UPDATE {artefact} SET owner = NULL WHERE "group" IS NOT NULL');
        execute_sql('UPDATE {view} SET owner = NULL WHERE institution IS NOT NULL');
        execute_sql('UPDATE {view} SET "group" = NULL WHERE institution IS NOT NULL');
        execute_sql('UPDATE {view} SET owner = NULL WHERE "group" IS NOT NULL');

        // Now add the constraints. MySQL parses check constraints but doesn't 
        // actually apply them. So these protections will only apply if you use 
        // Postgres. You did read the installation instruction's 
        // recommendations that you use postgres, didn't you?
        execute_sql('ALTER TABLE {artefact} ADD CHECK (
            (owner IS NOT NULL AND "group" IS NULL     AND institution IS NULL) OR
            (owner IS NULL     AND "group" IS NOT NULL AND institution IS NULL) OR
            (owner IS NULL     AND "group" IS NULL     AND institution IS NOT NULL)
        )');
        execute_sql('ALTER TABLE {view} ADD CHECK (
            (owner IS NOT NULL AND "group" IS NULL     AND institution IS NULL) OR
            (owner IS NULL     AND "group" IS NOT NULL AND institution IS NULL) OR
            (owner IS NULL     AND "group" IS NULL     AND institution IS NOT NULL)
        )');
    }

    if ($oldversion < 2009021700) {
        try {
            include_once('xmlize.php');
            $newlist = xmlize(file_get_contents(get_config('libroot') . 'htmlpurifiercustom/filters.xml'));
            $filters = $newlist['filters']['#']['filter'];
            foreach ($filters as &$f) {
                $f = (object) array(
                    'site' => $f['#']['site'][0]['#'],
                    'file' => $f['#']['filename'][0]['#']
                );
            }
            $filters[] = (object) array('site' => 'http://www.youtube.com', 'file' => 'YouTube');
            set_config('filters', serialize($filters));
        }
        catch (Exception $e) {
            log_debug('Upgrade 2009021700: failed to load html filters');
        }
    }

    if ($oldversion < 2009021701) {
        // Make sure that all views that can be copied have loggedin access
        // This upgrade just fixes potentially corrupt data caused by running a 
        // beta version then upgrading it
        if ($views = get_column('view', 'id', 'copynewuser', '1')) {
            $views[] = 1;
            foreach ($views as $viewid) {
                if (!record_exists('view_access', 'view', $viewid, 'accesstype', 'loggedin')) {
                    // We're not checking that access dates are null (aka
                    // it can always be accessed), but the chance of people
                    // needing this upgrade are slim anyway
                    insert_record('view_access', (object) array(
                        'view' => $viewid,
                        'accesstype' => 'loggedin',
                        'startdate' => null,
                        'stopdate'  => null,
                    ));
                }
            }
        }
    }

    if ($oldversion < 2009021900) {
        // Generate a unique installation key
        set_config('installation_key', get_random_key());
    }

    if ($oldversion < 2009021901) {
        // Insert a cron job to send registration data to mahara.org
        $cron = new StdClass;
        $cron->callfunction = 'cron_send_registration_data';
        $cron->minute       = rand(0, 59);
        $cron->hour         = rand(0, 23);
        $cron->day          = '*';
        $cron->month        = '*';
        $cron->dayofweek    = rand(0, 6);
        insert_record('cron', $cron);
    }

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
                                if (table_exists('blocktype_wall_post')) {
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

    if ($oldversion < 2010012700) {
        set_config('viewmicroheaders', 1);
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

        $manyblogusers = get_records_sql_array($sql, null);

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
                'title' => get_string('recentactivity'),
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
        // See upgrade 2011051701.
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

    if ($oldversion < 2011051701) {
        // Restore index that may be missing due to upgrade 2011050600.
        $table = new XMLDBTable('usr');
        $index = new XMLDBIndex('usr_use_uix');
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

    if ($oldversion < 2011052700) {
        if ($data = check_upgrades("blocktype.googleapps")) {
            upgrade_plugin($data);
        }
    }

    return $status;
}
