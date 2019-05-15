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

    /**
     * Only have the upgrade steps after the $version->minupgradeversion value
     * If you are needing to see the older upgrade code look at an older version of Mahara
     */
    if ($oldversion < 2017051100) {
        if ($records = get_records_sql_assoc("SELECT aa.annotation, va.view
                                              FROM {artefact_annotation} aa
                                              JOIN {view_artefact} va ON va.artefact = aa.annotation
                                              WHERE aa.view != va.view", array())) {
            log_debug('Fix artefact_annotation table data for copied collections');
            foreach ($records as $record) {
                $data = (object)array(
                    'annotation'    => $record->annotation,
                    'view'          => $record->view,
                );
                update_record('artefact_annotation', $data, 'annotation');
            }
        }
    }

    if ($oldversion < 2017052300) {
        // These are the records with passwords in the data.
        if ($records = get_records_sql_array("SELECT event, data, time
                                          FROM {event_log}
                                          WHERE event = ?
                                          AND POSITION(',\"password\":\"\",' IN data) = 0
                                         ", array('createuser'))
        ) {
            log_debug('Remove sensitive data from event_log');
            $count = 0;
            $limit = 1000;
            $total = count($records);
            foreach ($records as $record) {
                $where = clone $record;
                $data = json_decode($record->data);
                if (isset($data->password)) {
                    unset($data->password);
                    $cleandata = json_encode($data);
                    $record->data = $cleandata;
                    update_record('event_log', $record, $where);
                    set_field('usr', 'passwordchange', 1, 'username', $data->username);
                }
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
        // These are the records with empty passwords in the data.
        // No need for them to reset the password.
        $wheresql = " WHERE event = ?
            AND POSITION(',\"password\":\"\",' IN data) > 0";
        $sql_count = "SELECT COUNT(*)
                  FROM {event_log}" . $wheresql;
        if ($count = get_field_sql($sql_count, array('createuser'))) {
            $sql = "UPDATE {event_log}
                SET data = REPLACE(data, ',\"password\":\"\"', '')" . $wheresql;
            execute_sql($sql, array('createuser'));
            log_debug("$count records also cleaned up");
        }
    }

    if ($oldversion < 2017061200) {
        log_debug('Add new logoxs column in institution table for small logos');
        $table = new XMLDBTable('institution');
        $field = new XMLDBField('logoxs');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
            add_field($table, $field);
        }
    }

    if ($oldversion < 2017062000) {
        if ($records = get_records_sql_array("SELECT bi.id AS blockid
                                              FROM {group} g
                                              JOIN {view} v ON (v.group = g.id AND v.type = 'grouphomepage')
                                              JOIN {block_instance} bi ON (bi.view = v.id AND bi.blocktype = 'groupviews')
                                              WHERE g.submittableto = 1
                                              AND bi.configdata NOT LIKE '%showsubmitted%'", array())) {
            safe_require('blocktype', 'groupviews');
            drop_elasticsearch_triggers();
            log_debug('Update submittable groups to display submissions in block by default');
            // We can only update those blocks where a decision hasn't yet been made rather than include blocks
            // that have showsubmitted set to false as that may be a valid choice by the group administrator.
            $count = 0;
            $limit = 1000;
            $total = count($records);
            foreach ($records as $record) {
                $bi = new BlockInstance($record->blockid);
                $configdata = $bi->get('configdata');
                $configdata['showsubmitted'] = 1;
                $bi->set('configdata', $configdata);
                $bi->commit();
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
            create_elasticsearch_triggers();
        }
    }

    if ($oldversion < 2017071100) {
        log_debug('Fix up missing creation time for users');
        // Guess their creation time from their earliest view
        if ($results = get_records_sql_array("SELECT u.id, (
                SELECT MIN(v.ctime) FROM {view} v WHERE v.owner = u.id)
           AS starttime FROM {usr} u WHERE u.deleted = 0 AND u.ctime IS NULL AND u.id != 0")) {
            foreach ($results as $result) {
                execute_sql("UPDATE {usr} SET ctime = ? WHERE id = ?", array($result->starttime, $result->id));
            }
        }
    }

    if ($oldversion < 2017082900) {
        log_debug('Fix missing property in group Forums');
        $sql = "INSERT INTO {interaction_forum_instance_config} (forum,field,value)
            SELECT DISTINCT forum, 'createtopicusers', 'members'
            FROM {interaction_forum_instance_config}
            WHERE forum NOT IN (
                SELECT DISTINCT forum FROM {interaction_forum_instance_config}
                WHERE field = 'createtopicusers'
            )";
        execute_sql($sql);
    }

    if ($oldversion < 2017090400) {
        if ($data = get_config_plugin('blocktype', 'internalmedia', 'enabledtypes')) {
            $types = unserialize($data);
            if (($key = array_search('swf', $types)) !== false) {
                unset($types[$key]);
                $typestr = serialize($types);
                set_config_plugin('blocktype', 'internalmedia', 'enabledtypes', $typestr);
            }
        }
    }

    if ($oldversion < 2017090800) {
        log_debug('Clear menu cache for removal of menu items');
        clear_menu_cache();
    }

    if ($oldversion < 2017090800) {
        log_debug('Add new fields to "event_log" table');
        // Instead of recording event resource id information in the 'data' json blob
        // we will add it to it's own columns for easier searching / faster retrieval
        // We will record if necessary the resourcetype/resourceid (and parenttype/parentid if necessary)
        // And for elasticsearch we will need to add an id column to the table and change 'time' to 'ctime'.

        $table = new XMLDBTable('event_log');
        $field = new XMLDBField('id');
        if (!field_exists($table, $field)) {
            log_debug('Adding id column');
            if (is_mysql()) {
                // MySQL requires the auto-increment column to be a primary key right away.
                execute_sql('ALTER TABLE {event_log} ADD id BIGINT(10) NOT NULL auto_increment PRIMARY KEY FIRST');
            }
            else {
                // This will auto-populate the id column without having to create a temp table.
                execute_sql('ALTER TABLE {event_log} ADD COLUMN id BIGSERIAL PRIMARY KEY');
            }

            // Add 'ctime' field and drop 'time' field
            log_debug('Adding ctime column');
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);

            $sql = "UPDATE {event_log} SET ctime = time";
            execute_sql($sql);

            // now set ctime attribute to NOT NULL.
            $field = new XMLDBField('ctime');
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            change_field_notnull($table, $field);

            $field = new XMLDBField('time');
            drop_field($table, $field);

            log_debug('Adding resourceid column');
            $field = new XMLDBField('resourceid');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
            add_field($table, $field);

            log_debug('Adding resourcetype column');
            $field = new XMLDBField('resourcetype');
            $field->setAttributes(XMLDB_TYPE_CHAR, 255);
            add_field($table, $field);

            log_debug('Adding parentresourceid column');
            $field = new XMLDBField('parentresourceid');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
            add_field($table, $field);

            log_debug('Adding parentresourcetype column');
            $field = new XMLDBField('parentresourcetype');
            $field->setAttributes(XMLDB_TYPE_CHAR, 255);
            add_field($table, $field);

            log_debug('Adding ownerid column');
            $field = new XMLDBField('ownerid');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
            add_field($table, $field);

            log_debug('Adding ownertype column');
            $field = new XMLDBField('ownertype');
            $field->setAttributes(XMLDB_TYPE_CHAR, 255);
            add_field($table, $field);
        }

        log_debug('Adjust existing "event_log" data to fit new table structure');
        $db_version = get_db_version();
        if (is_mysql() && mysql_get_type() == 'mysql' && version_compare($db_version, '5.7.8', '>=')) {
            // Update the event_log table using the json datatype
            // by converting the data field to json.
            // This datatype was introduced in Mysql 5.7.8.
            log_debug('Adjust existing "event_log" data for "saveview" and "deleteview" events');
            $sql = "UPDATE {event_log} e
                    LEFT JOIN {view} v ON v.id = JSON_EXTRACT( CAST( e.data AS JSON ), '$.id')
                    SET e.resourceid   = JSON_EXTRACT( CAST( e.data AS JSON ), '$.id'),
                        e.resourcetype = 'view',
                        e.ownerid      = v.owner,
                        e.ownertype    = CASE WHEN v.owner IS NULL THEN NULL ELSE 'view' END
                    WHERE e.event IN ('saveview', 'deleteview')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "userjoinsgroup" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.group' ),
                        resourcetype = 'group',
                        ownerid      = JSON_EXTRACT( CAST( data AS JSON ), '$.group' ),
                        ownertype    = 'group'
                    WHERE event IN ('userjoinsgroup')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "creategroup" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.id' ),
                        resourcetype = 'group',
                        ownerid      = JSON_EXTRACT( CAST( data AS JSON ), '$.id' ),
                        ownertype    = 'group'
                    WHERE event IN ('creategroup')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "saveartefact" and "deleteartefact" events');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.id' ),
                        resourcetype = JSON_EXTRACT( CAST( data AS JSON), '$.artefacttype' )
                    WHERE event IN ('saveartefact', 'deleteartefact')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "blockinstancecommit" and "deleteblockinstance" events');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.id' ),
                        resourcetype = JSON_EXTRACT( CAST( data AS JSON), '$.blocktype' )
                    WHERE event IN ('blockinstancecommit', 'deleteblockinstance')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "addfriend" and "removefriend" events');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.friend' ),
                        resourcetype = 'friend'
                    WHERE event IN ('addfriend', 'removefriend')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "addfriendrequest" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.owner' ),
                        resourcetype = 'friend'
                    WHERE event IN ('addfriendrequest')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "removefriendrequest" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = JSON_EXTRACT( CAST( data AS JSON ), '$.requester' ),
                        resourcetype = 'friend'
                    WHERE event IN ('removefriendrequest')";
            execute_sql($sql);
        }
        else if (is_postgres() && version_compare($db_version, '9.2.0', '>=')) {
            // Update the event_log table using the json datatype
            // by converting the data field to json.
            // This datatype was introduced in Postgres 9.2.
            log_debug('Adjust existing "event_log" data for "saveview" and "deleteview" events');
            $sql = "UPDATE {event_log} e2
                    SET resourceid   = CAST( CAST( e.data AS JSON )->>'id' AS INTEGER ),
                        resourcetype = 'view',
                        ownerid      = v.owner,
                        ownertype    = CASE WHEN v.owner IS NULL THEN NULL ELSE 'view' END
                    FROM {event_log} e
                    LEFT JOIN {view} v ON v.id = CAST( CAST( e.data AS JSON )->>'id' AS BIGINT)
                    WHERE e.event IN ('saveview', 'deleteview')
                    AND e2.id = e.id";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "userjoinsgroup" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'group' AS INTEGER ),
                        resourcetype = 'group',
                        ownerid      = CAST( CAST( data AS JSON )->>'group' AS BIGINT ),
                        ownertype    = 'group'
                    WHERE event IN ('userjoinsgroup')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "creategroup" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'id' AS INTEGER ),
                        resourcetype = 'group',
                        ownerid      = CAST( CAST( data AS JSON )->>'id' AS BIGINT ),
                        ownertype    = 'group'
                    WHERE event IN ('creategroup')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "saveartefact" and "deleteartefact" events');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'id' AS INTEGER ),
                        resourcetype = CAST( data AS JSON)->>'artefacttype'
                    WHERE event IN ('saveartefact', 'deleteartefact')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "blockinstancecommit" and "deleteblockinstance" events');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'id' AS INTEGER ),
                        resourcetype = CAST( data AS JSON)->>'blocktype'
                    WHERE event IN ('blockinstancecommit', 'deleteblockinstance')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "addfriend" and "removefriend" events');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'friend' AS INTEGER ),
                        resourcetype = 'friend'
                    WHERE event IN ('addfriend', 'removefriend')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "addfriendrequest" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'owner' AS INTEGER ),
                        resourcetype = 'friend'
                    WHERE event IN ('addfriendrequest')";
            execute_sql($sql);

            log_debug('Adjust existing "event_log" data for "removefriendrequest" event');
            $sql = "UPDATE {event_log}
                    SET resourceid   = CAST( CAST( data AS JSON )->>'requester' AS INTEGER ),
                        resourcetype = 'friend'
                    WHERE event IN ('removefriendrequest')";
            execute_sql($sql);
        }
        else {
            // This is an older database. Can't use json datatype.
            // Will need to loop through all the records and adjust.
            // As there can be very many rows we need to do the adjusting in chuncks.
            log_debug('Loop through all records and adjust');
            $count = 0;
            $limit = 10000;
            $chunk = 5000;
            $total = count_records_select('event_log', 'data != ?', array('{}'));
            if ($total > 0) {
                for ($i = 0; $i <= $total; $i += $chunk) {
                    $results = get_records_sql_array("SELECT id, event, data FROM {event_log}", array(), $count, $chunk);
                    foreach ($results as $result) {
                        $data = json_decode($result->data);
                        $where = clone $result;
                        switch ($result->event) {
                            case 'saveview':
                            case 'deleteview':
                                $result->resourceid = $data->id;
                                $result->resourcetype = 'view';
                                break;
                            case 'userjoinsgroup':
                                $result->resourceid = $data->group;
                                $result->resourcetype = 'group';
                                break;
                            case 'creategroup':
                                $result->resourceid = $data->id;
                                $result->resourcetype = 'group';
                                break;
                            case 'saveartefact':
                            case 'deleteartefact':
                                // Make sure there is actually an id and artefacttype.
                                if (isset($data->id)) {
                                    $result->resourceid = $data->id;
                                }
                                if (isset($data->artefacttype)) {
                                    $result->resourcetype = $data->artefacttype;
                                }
                                break;
                            case 'deleteartefacts':
                                // These hold multiple ids. Can't do much with them.
                                // Will leave them as is.
                                break;
                            case 'blockinstancecommit':
                            case 'deleteblockinstance':
                                $result->resourceid = $data->id;
                                $result->resourcetype = $data->blocktype;
                                break;
                            case 'addfriend':
                            case 'removefriend':
                                $result->resourceid = $data->friend;
                                $result->resourcetype = 'friend';
                                break;
                            case 'addfriendrequest':
                                $result->resourceid = $data->owner;
                                $result->resourcetype = 'friend';
                                break;
                            case 'removefriendrequest':
                                $result->resourceid = $data->requester;
                                $result->resourcetype = 'friend';
                                break;
                        }
                        list ($ownerid, $ownertype) = event_find_owner_type($result);
                        $result->ownerid = $ownerid;
                        $result->ownertype = $ownertype;
                        unset($result->id); // No reason to update the ID.
                        update_record('event_log', $result, array('id'=>$where->id));
                    }
                    $count += $chunk;
                    if (($count % $limit) == 0 || $count >= $total) {
                        if ($count > $total) {
                            $count = $total;
                        }
                        log_debug("$count/$total");
                        set_time_limit(30);
                    }
                }
            }
        }

        log_debug('Add new logging events');
        $newevents = array('createview',
                           'createcollection',
                           'updatecollection',
                           'deletecollection',
                           'addsubmission',
                           'releasesubmission',
                           'updateviewaccess',
                           'sharedcommenttogroup');
        foreach ($newevents as $newevent) {
            $event = (object)array(
                'name' => $newevent,
            );
            ensure_record_exists('event_type', $event, $event);
        }
    }

    if ($oldversion < 2017092100) {
        log_debug('Remove obsolete default notification method');
        delete_records('config', 'field', 'defaultnotificationmethod');
    }

    if ($oldversion < 2017092200) {
        // This code taken directly from browserid plugin, which we've just deleted so we need
        // to run the move users to 'internal' auth here
        if ($instances = get_records_array('auth_instance', 'authname', 'browserid', 'id')) {
            log_debug('Re-assigning users from "Persona" to "Internal" authentication');
            foreach ($instances as $authinst) {
                // Are there any users with this auth instance?
                if (record_exists('usr', 'authinstance', $authinst->id)) {
                    // Find the internal auth instance for this institution
                    $internal = get_field('auth_instance', 'id', 'authname', 'internal', 'institution', $authinst->institution);
                    if (!$internal) {
                        // Institution has no internal auth instance. Create one.
                        $todb = new stdClass();
                        $todb->instancename = 'internal';
                        $todb->authname = 'internal';
                        $todb->active = 1;
                        $todb->institution = $authinst->institution;
                        $todb->priority = $authinst->priority;
                        $internal = insert_record('auth_instance', $todb, 'id', true);
                    }
                    // Set the password & salt for Persona users to "*", which means "no password set"
                    update_record('usr', (object)array('password' => '*', 'salt' => '*'), array('authinstance' => $authinst->id));
                    set_field('usr', 'authinstance', $internal, 'authinstance', $authinst->id);
                    set_field('usr_registration', 'authtype', 'internal', 'authtype', 'browserid');
                }
                // Delete the Persona auth instance
                delete_records('auth_remote_user', 'authinstance', $authinst->id);
                delete_records('auth_instance_config', 'instance', $authinst->id);
                delete_records('auth_instance', 'id', $authinst->id);
                // Make it no longer be the parent authority to any auth instances
                delete_records('auth_instance_config', 'field', 'parent', 'value', $authinst->id);
            }
        }
        log_debug('Removing "Persona" authentication plugin');
        delete_records('auth_config', 'plugin', 'browserid');
        delete_records('auth_cron', 'plugin', 'browserid');
        delete_records('auth_event_subscription', 'plugin', 'browserid');
        delete_records('auth_installed', 'name', 'browserid');
    }

    if ($oldversion < 2017092500) {
        log_debug('Clear all caches to allow regeneration of session directories');
        clear_all_caches(true);
    }

    if ($oldversion < 2017092600) {
        log_debug('Add primary key to site_content table');

        // See if we need to add the id column
        $table = new XMLDBTable('site_content');
        $field = new XMLDBField('id');
        if (!field_exists($table, $field)) {
            log_debug('Making a temp copy and adding id column');
            execute_sql('CREATE TEMPORARY TABLE {temp_site_content} AS SELECT DISTINCT * FROM {site_content}', array());
            execute_sql('TRUNCATE {site_content}', array());

            // Drop the current primary key as we will move it to the id column
            $key = new XMLDBKey('primary');
            $key->setAttributes(XMLDB_KEY_PRIMARY, array('name', 'institution'));
            drop_key($table, $key);

            if (is_mysql()) {
                // MySQL requires the auto-increment column to be a primary key right away.
                execute_sql('ALTER TABLE {site_content} ADD id BIGINT(10) NOT NULL auto_increment PRIMARY KEY FIRST');
            }
            else {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
                add_field($table, $field);
            }

            log_debug('Adding back in the site_content information');
            // We will do in chuncks for large sites.
            $count = 0;
            $x = 0;
            $limit = 1000;
            $total = count_records('temp_site_content');
            for ($i = 0; $i <= $total; $i += $limit) {
                if (is_postgres()) {
                    $limitsql = ' OFFSET ' . $i . ' LIMIT ' . $limit;
                }
                else {
                    $limitsql = ' LIMIT ' . $i . ',' . $limit;
                }
                execute_sql('INSERT INTO {site_content} (name, content, ctime, mtime, mauthor, institution) SELECT name, content, ctime, mtime, mauthor, institution FROM {temp_site_content}' . $limitsql, array());
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
            execute_sql('DROP TABLE {temp_site_content}', array());

            if (!is_mysql()) {
                log_debug('Adding primary key index to site_content.id column');
                $key = new XMLDBKey('primary');
                $key->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
                add_key($table, $key);
            }
            // Add the old key as new unique index
            $index = new XMLDBIndex('nameinstuk');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('name', 'institution'));
            add_index($table, $index);
        }

        if ($results = get_records_sql_array("SELECT id, content FROM {site_content} WHERE content LIKE '%<img%'")) {
            log_debug('Make sure images in static pages are embedded images');
            require_once('embeddedimage.php');
            foreach ($results as $result) {
                // Update the page text with any embedded image info
                $result->content = EmbeddedImage::prepare_embedded_images($result->content, 'staticpages', $result->id);
                update_record('site_content', $result, array('id'));
            }
        }
    }

    if ($oldversion < 2017102700) {
        log_debug('Add an "useragent" field to usr_session table');
        $table = new XMLDBTable('usr_session');
        $field = new XMLDBField('useragent');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('small');
        if (!field_exists($table, $field)) {
            add_field($table, $field);
        }
    }

    if ($oldversion < 2017103100) {
        log_debug('Remove MaharaDroid configurations');
        // remove allowmobileuploads from config, used only by old mobile api
        delete_records('config','field','allowmobileuploads');
        // remove mobileuploadtoken from account settings, used only by old mobile api
        // for new mobile api, we save tokens in table external_tokens
        delete_records('usr_account_preference','field','mobileuploadtoken');
    }

    if ($oldversion < 2017110600) {
        log_debug('Add Voki filters to DB');
        reload_html_filters();
    }

    if ($oldversion < 2018010300) {
        log_debug('Anonymising remaining deleted user data');
        $sql = "UPDATE {usr}
            SET username = CONCAT(MD5(username), 1000000 + id),
            email = CONCAT(MD5(email), 1000000 + id)
            WHERE deleted = 1 ";
        execute_sql($sql);
    }

    if ($oldversion < 2018010400) {
        log_debug('Adding new event type "userleavesgroup"');
        $event = (object)array(
            'name'  => 'userleavesgroup'
        );
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2018010500) {
        log_debug('Make site_content mauthor field have a user id by defaut');
        // Defaults to usr = 0 on install
        execute_sql("UPDATE {site_content} SET mauthor = 0 WHERE mauthor IS NULL");
        $table = new XMLDBTable('site_content');
        $key = new XMLDBKEY('mauthorfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('mauthor'), 'usr', array('id'));
        drop_key($table, $key);

        $field = new XMLDBField('mauthor');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        change_field_notnull($table, $field);

        $key = new XMLDBKEY('mauthorfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('mauthor'), 'usr', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2018010600) {
        log_debug('Create "site_content_version" table');

        $table = new XMLDBTable('site_content_version');
        create_table($table);

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        $table->addFieldInfo('content', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL);
        $table->addFieldInfo('author', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('version', XMLDB_TYPE_CHAR, 15, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('institutionfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $table->addKeyInfo('authorfk', XMLDB_KEY_FOREIGN, array('author'), 'usr', array('id'));

        create_table($table);
    }

    if ($oldversion < 2018010700) {
        log_debug('Move the site and institution Privacy statement from the site_content table to the site_content_version table');
        //For all istitutions, get the values of the "Use site default" option regarding the privacy page
        $instconfigs = get_records_sql_assoc("SELECT institution, value FROM {institution_config}
                                                WHERE field = 'sitepages_privacy'");
        if ($records = get_records_array('site_content', 'name', 'privacy')) {
            foreach ($records as $data) {
                if ($data->institution == 'mahara' || $instconfigs[$data->institution]->value == $data->institution) {
                    $record = new stdClass;
                    $record->type = 'privacy';
                    $record->content = $data->content;
                    $record->author = $data->mauthor;
                    $record->institution = $data->institution;
                    $record->version = '1.0';
                    $record->ctime = db_format_timestamp(time());
                    insert_record('site_content_version', $record);
                }
                delete_records('site_content', 'id', $data->id);
            }
        }
    }

    if ($oldversion < 2018011000) {
        log_debug('Create "usr_agreement" table');

        $table = new XMLDBTable('usr_agreement');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, true);
        $table->addFieldInfo('sitecontentid', XMLDB_TYPE_INTEGER, 10, null, true);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('agreed', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('sitecontentfk', XMLDB_KEY_FOREIGN, array('sitecontentid'), 'site_content_version', array('id'));

        create_table($table);
    }

    if ($oldversion < 2018013000) {
        log_debug('Auto accept the privacy agreement for all site admins');
        $sitecontentid = get_field('site_content_version', 'id', 'type', 'privacy', 'institution', 'mahara');
        $admins = get_site_admins();
        foreach ($admins as $admin) {
            save_user_reply_to_agreement($admin->id, $sitecontentid, 1);
        }
    }

    if ($oldversion < 2018013001) {
        log_debug('Move the site and institution terms and conditions  from the site_content table to the site_content_version table');
        //For all istitutions, get the values of the "Use site default" option regarding the termsandconditions page
        $instconfigs = get_records_sql_assoc("SELECT institution, value FROM {institution_config}
                                        WHERE field = 'sitepages_termsandconditions'");
        if ($records = get_records_array('site_content', 'name', 'termsandconditions')) {
            foreach ($records as $data) {
                if ($data->institution == 'mahara' || $instconfigs[$data->institution]->value == $data->institution) {
                    $record = new stdClass;
                    $record->type = 'termsandconditions';
                    $record->content = $data->content;
                    $record->author = $data->mauthor;
                    $record->institution = $data->institution;
                    $record->version = '1.0';
                    $record->ctime = db_format_timestamp(time());

                    insert_record('site_content_version', $record);
                }
                delete_records('site_content', 'id', $data->id);
            }
        }

        log_debug('Auto accept the terms and conditions for all site admins');
        $sitecontentid = get_field('site_content_version', 'id', 'type', 'termsandconditions', 'institution', 'mahara');
        $admins = get_site_admins();
        foreach ($admins as $admin) {
            save_user_reply_to_agreement($admin->id, $sitecontentid, 1);
        }
    }

    if ($oldversion < 2018021500) {
        log_debug('Remove privacy statement and T&C conditions custom links from footer');
        // remove custom links
        if ($footercustomlinks = get_config('footercustomlinks')) {
            $footercustomlinks = unserialize($footercustomlinks);
            $removedlinks = array();
            foreach ($footercustomlinks as $key => $customlink) {
                if ($key == 'termsandconditions' || $key == 'privacystatement') {
                    $removedlinks[] = $customlink;
                    unset($footercustomlinks[$key]);
                }
            }
            if ($removedlinks) {
                $SESSION->add_error_msg(get_string('removefooterlinksupgradewarning', 'mahara', implode(', ', $removedlinks)));
            }
            set_config('footercustomlinks', serialize($footercustomlinks));
        }
    }

    if ($oldversion < 2018021600) {
        log_debug('Add an "usr_pendingdeletion" table');
        $table = new XMLDBTable('usr_pendingdeletion');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('reason', XMLDB_TYPE_TEXT);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));

        create_table($table);

        log_debug('Clean old alwaysallowselfdelete configuration setting');
        execute_sql("DELETE FROM {config} WHERE field = ?", array('alwaysallowselfdelete'));
    }

    if ($oldversion < 2018021601) {
        log_debug('Remove auto-added users from deleted groups if added post deletion');
        if ($records = get_records_sql_array("SELECT id, SUBSTR(name, POSITION('.deleted.' IN name) + LENGTH('.deleted.')) AS deltime
                                              FROM {group} WHERE deleted = 1")) {
            foreach ($records as $key => $record) {
                if (is_numeric($record->deltime)) {
                    $timestamp = date('Y-m-d H:i:s', $record->deltime);
                    delete_records_sql('DELETE FROM {group_member} WHERE "group" = ? AND ctime >= ?', array($record->id, $timestamp));
                }
            }
        }
    }

    if ($oldversion < 2018022100) {
        log_debug('Combine footer links T&C and Privacy into Legal link');

        if ($enabledfooterlinks = get_config('footerlinks')) {
            $enabledfooterlinks = unserialize($enabledfooterlinks);
            $enablelegal = false;
            if (in_array('termsandconditions', $enabledfooterlinks)) {
                $key = array_search('termsandconditions', $enabledfooterlinks);
                unset($enabledfooterlinks[$key]);
                $enablelegal = true;
            }
            if (in_array('privacystatement', $enabledfooterlinks)) {
                $key = array_search('privacystatement', $enabledfooterlinks);
                unset($enabledfooterlinks[$key]);
                $enablelegal = true;
            }
            // if T&C or privacy links were shown on the footer, then show legal link
            if ($enablelegal) {
                $enabledfooterlinks[] = 'legal';
                set_config('footerlinks', serialize($enabledfooterlinks));
            }
        }
    }

    if ($oldversion < 2018022200) {
        log_debug('Removing confusing blockinstance title information');
        execute_sql("UPDATE {block_instance} SET title = '' WHERE blocktype IN ('myviews', 'mygroups', 'myfriends', 'wall')");
    }

    if ($oldversion < 2018022300) {
        log_debug('Add "manualhelp" as a default footer link');
        $footerlinkstr = get_config('footerlinks');
        $footerlinks = unserialize($footerlinkstr);
        if (!in_array('manualhelp', $footerlinks)) {
            $footerlinks[] = 'manualhelp';
        }
        $footerlinkstr = serialize($footerlinks);
        set_config('footerlinks', $footerlinkstr);
    }

    if ($oldversion < 2018022400) {
        log_debug('Create a new "unsubscribetoken" field in "usr_watchlist_view" table');
        $table = new XMLDBTable('usr_watchlist_view');
        $field = new XMLDBField('unsubscribetoken');
        $field->setAttributes(XMLDB_TYPE_CHAR, 24);
        add_field($table, $field);

        $index = new XMLDBIndex('unsubscribetokenix');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('unsubscribetoken'));
        add_index($table, $index);
    }

    if ($oldversion < 2018022500) {
        log_debug('Upgrade artefact/file plugin');
        if ($data = check_upgrades('artefact.file')) {
            upgrade_plugin($data);
        }
    }

    if ($oldversion < 2018022503) {
        if (!get_config('passwordpolicy')) {
            log_debug('Force users to change their password to fix new password policy');
            execute_sql("UPDATE {usr} SET passwordchange = 1
                         WHERE authinstance IN (
                             SELECT ai.id
                             FROM {auth_instance} ai
                             WHERE ai.authname = 'internal'
                         )
                         AND id != 0"); // Ignore the root user
            set_config('passwordpolicy', '8_ulns');
        }
    }

    if ($oldversion < 2018022516) {
        log_debug('offset some troublesome cron jobs');
        execute_sql("UPDATE {cron} SET minute = ? WHERE callfunction = ?", array('2-59/5', 'user_login_tries_to_zero'));
        execute_sql("UPDATE {interaction_cron} SET minute = ? WHERE plugin = ? AND callfunction = ?", array('3-59/30', 'forum', 'interaction_forum_new_post'));
        execute_sql("UPDATE {search_cron} SET minute = ? WHERE plugin = ? AND callfunction = ?", array('4-59/5', 'elasticsearch', 'cron'));
    }

    return $status;
}
