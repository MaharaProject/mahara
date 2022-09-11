<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
    if ($oldversion < 2020040600) {
        log_debug('Update the View table to add coverimage column');
        $table = new XMLDBTable('view');
        if (table_exists($table)) {
            log_debug('Adding coverimage to view table');
            $field = new XMLDBField('coverimage');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
                add_field($table, $field);
                $table->addKeyInfo('coverimagefk', XMLDB_KEY_FOREIGN, array('coverimage'), 'artefact', array('id'));
            }
        }
        log_debug('Update the Collection table to add coverimage column');
        $table = new XMLDBTable('collection');
        if (table_exists($table)) {
            log_debug('Adding coverimage to collection table');
            $field = new XMLDBField('coverimage');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
                add_field($table, $field);
                $table->addKeyInfo('coverimagefk', XMLDB_KEY_FOREIGN, array('coverimage'), 'artefact', array('id'));
            }
        }
    }

    if ($oldversion < 2020050600) {
        log_debug('Fixing skins for new format options');
        if ($skins = get_column('skin', 'id')) {
            require_once('skin.php');
            safe_require('artefact', 'file');
            foreach ($skins as $skinid) {
                $skinobj = new Skin($skinid);
                $viewskin = $skinobj->get('viewskin');
                if (!isset($viewskin['view_block_header_font'])) {
                    $viewskin['view_block_header_font'] = '';
                }
                if (!isset($viewskin['view_block_header_font_color'])) {
                    $viewskin['view_block_header_font_color'] = '';
                }
                $skinobj->set('viewskin', $viewskin);
                $skinobj->commit();
                set_time_limit(30);
            }
        }
    }

    if ($oldversion < 2020050800) {
        log_debug('Moving page description to a text block on the top of the page');
        require_once(get_config('docroot') . 'lib/view.php');
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $sql = "SELECT v.id FROM {view} v
                LEFT JOIN {group} g ON v.group = g.id
                WHERE (v.group IS NULL OR g.deleted = 0)
                AND v.template != ?
                AND v.description IS NOT NULL";
        $viewids = get_column_sql($sql, array(View::SITE_TEMPLATE));

        $count = 0;
        $limit = 1000;
        $total = count($viewids);
        foreach ($viewids as $viewid) {
            $viewobj = new View($viewid);
            // check if the view has new layout and description
            if ($viewobj->uses_new_layout() && $description = $viewobj->get('description')) {
                if ($newdescription = can_extract_description_text($description)) {
                    $viewobj->set('description', $newdescription);
                    $viewobj->commit();
                }
                else {
                    // get all the blocks in the view and move them 1 row down
                    if ($blockids = get_column('block_instance', 'id', 'view', $viewid)) {
                        foreach ($blockids as $blockid) {
                            $bi = new BlockInstance($blockid);
                            $y = $bi->get('positiony');
                            $bi->set('positiony', $y + 1);
                            $bi->commit();
                        }
                    }
                    // add the description block at the top
                    $viewobj->description_to_block();
                    //remove description from view
                    $viewobj->set('description', '');
                    $viewobj->commit();
                }
            }
            $count++;
            if (($count % $limit) == 0 || $count == $total) {
                log_debug("$count/$total");
                set_time_limit(30);
            }
        }
    }

    if ($oldversion < 2020051300) {
        $table = new XMLDBTable('collection');
        if (table_exists($table)) {
            log_debug('Adding progress completion column to collection table');
            $field = new XMLDBField('progresscompletion');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
            }
        }

        $table = new XMLDBTable('institution');
        if (table_exists($table)) {
            log_debug('Adding progress completion column to institution table');
            $field = new XMLDBField('progresscompletion');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
            }
        }
    }

    if ($oldversion < 2020060400) {
        $table = new XMLDBTable('artefact_plans_plan');
        if (table_exists($table)) {
            log_debug('Remove sequence from artefact_plans_plan "artefact" field');
            $key = new XMLDBKey('artefactfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
            drop_key($table, $key);
            $field = new XMLDBField('artefact');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            change_field_default($table, $field);
            $key = new XMLDBKey('primary');
            $key->setAttributes(XMLDB_KEY_PRIMARY, array('artefact'));
            add_key($table, $key);
            $key = new XMLDBKey('artefactfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
            add_key($table, $key);
        }

        $table = new XMLDBTable('artefact_plans_task');
        if (table_exists($table)) {
            log_debug('Remove sequence from artefact_plans_task "artefact" field');
            $key = new XMLDBKey('artefactfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
            drop_key($table, $key);
            $field = new XMLDBField('artefact');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            change_field_default($table, $field);
            $key = new XMLDBKey('primary');
            $key->setAttributes(XMLDB_KEY_PRIMARY, array('artefact'));
            add_key($table, $key);
            $key = new XMLDBKey('artefactfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
            add_key($table, $key);
        }
    }

    if ($oldversion < 2020060500) {
        log_debug('Add quickedit column to blocktype_installed table');
        $table = new XMLDBTable('blocktype_installed');
        if (table_exists($table)) {
            $field = new XMLDBField('quickedit');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
                // set quick edit to 'true' for text block
                set_field('blocktype_installed', 'quickedit', 1, 'name', 'text');
            }
        }
    }

    if ($oldversion < 2020060501) {
        log_debug('Adding locktemplate column to view table');
        $table = new XMLDBTable('view');
        $field = new XMLDBField('locktemplate');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            add_field($table, $field);
        }
        log_debug('Adding view_instructions_lock');
        $table = new XMLDBTable('view_instructions_lock');
        if (!table_exists($table)) {
            $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10, null, true);
            $table->addFieldInfo('originaltemplate', XMLDB_TYPE_INTEGER, 10, null, true);
            $table->addFieldInfo('locked', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('view'));
            $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));

            create_table($table);
        }
    }

    if ($oldversion < 2020061700) {
        $table = new XMLDBTable('view');
        if (table_exists($table)) {
            log_debug('Remove host FK from view table');
            $key = new XMLDBKey('submittedhostfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('submittedhost'), 'host', array('wwwroot'));
            drop_key($table, $key);
        }

        $table = new XMLDBTable('collection');
        if (table_exists($table)) {
            log_debug('Remove host FK from collection table');
            $key = new XMLDBKey('submittedhostfk');
            $key->setAttributes(XMLDB_KEY_FOREIGN, array('submittedhost'), 'host', array('wwwroot'));
            drop_key($table, $key);
        }
    }

    if ($oldversion < 2020063000) {
        log_debug('create client_connections_config table to hold extra configuration information');
        $table = new XMLDBTable('client_connections_institution');
        if (table_exists($table)) {
            $field = new XMLDBField('id');
            $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            change_field_unsigned($table, $field);
            if (!is_mysql()) {
                log_debug('Adding primary key index back after editing id column');
                $key = new XMLDBKey('primary');
                $key->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
                add_key($table, $key);
            }
        }
        $table = new XMLDBTable('client_connections_config');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->addFieldInfo('connection', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
            $table->addFieldInfo('field', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
            $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('ccifk', XMLDB_KEY_FOREIGN, array('connection'), 'client_connections_institution', array('id'));
            create_table($table);
        }
    }

    // set customethemeupdate to true for Bug 1893159s
    if ($oldversion < 2020083100) {
        $custom_themes = get_records_sql_array("SELECT name FROM {institution} WHERE theme = ?", array('custom'));
        if ($custom_themes) {
            log_debug('Setting update flag for custom themes');
            // set_config_institution requires the Institution class.
            require_once(get_config('docroot') . 'lib/institution.php');
            foreach ($custom_themes as $inst) {
                set_config_institution($inst->name, 'customthemeupdate', true);
            }
        }
    }

    if ($oldversion < 2020091000) {
        log_debug('Adding unique key to tag table');
        $table = new XMLDBTable('tag');
        // Add the new unique index
        $index = new XMLDBIndex('taguk');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('tag', 'resourcetype', 'resourceid'));
        if (!index_exists($table, $index)) {
            // make sure there are no doubleups in tags
            if ($taginfo = get_records_sql_array("SELECT tag, resourcetype, resourceid, ownertype, ownerid
                                                   FROM {tag}
                                                   GROUP BY tag, resourcetype, resourceid, ownertype, ownerid
                                                   HAVING COUNT(*) > 1")) {
                // we have duplicates so we need to delete all but the first one
                foreach ($taginfo as $tag) {
                    $ids = get_column_sql("SELECT t.id FROM {tag} t WHERE t.tag = ? AND t.resourcetype = ?
                                           AND t.resourceid = ? AND t.ownertype = ? AND t.ownerid = ?",
                                          array($tag->tag, $tag->resourcetype, $tag->resourceid, $tag->ownertype, $tag->ownerid));
                    array_shift($ids);
                    execute_sql("DELETE FROM {tag} WHERE id IN (" . implode(', ', $ids) . ")");
                }
            }
            add_index($table, $index);
        }
    }

    if ($oldversion < 2020091600) {
        log_debug('Install new Maroon theme fonts');
        require_once(get_config('libroot') . 'skin.php');
        install_skins_default();
    }

    if ($oldversion < 2020092100) {
        log_debug('Adjust archived_submissions table');
        $table = new XMLDBTable('archived_submissions');
        $field = new XMLDBField('group');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
        change_field_notnull($table, $field);
    }

    // Set collation for view table - description field and block_instance table - configdata field for Bug 1895259
    if ($oldversion < 2020093000) {
        if (is_mysql()) {
            log_debug('Fix certain fields to have correct collation');
            $columns = array(0 => array('table' => 'view',
                                        'value' => 'description'),
                             1 => array('table' => 'view',
                                        'value' => 'instructions'),
                             2 => array('table' => 'block_instance',
                                        'value' => 'configdata'),
                             3 => array('table' => 'import_entry_requests',
                                        'value' => 'entrycontent')
                            );
            foreach ($columns as $column) {
                $charset = get_field_sql("SELECT character_set_name FROM information_schema.columns
                                          WHERE table_schema = '" . get_config('dbname') . "'
                                          AND table_name = '" . get_config('dbprefix') . $column['table'] . "'
                                          AND column_name = ?", array($column['value']));
                if ($charset && !preg_match('/utf8mb4/', $charset)) {
                    execute_sql('ALTER TABLE {' . $column['table'] . '} MODIFY ' . $column['value'] . ' text CHARSET utf8mb4');
                }
            }
        }
    }

    if ($oldversion < 2020101200) {
        log_debug('Add new fields to export_queue and archived submissions tables');
        $table = new XMLDBTable('export_queue');
        if (table_exists($table)) {
            $field = new XMLDBField('externalname');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_CHAR, 255);
                add_field($table, $field);
            }
            $field = new XMLDBField('externalurl');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_TEXT, 'big');
                add_field($table, $field);
            }
        }
        $table = new XMLDBTable('archived_submissions');
        if (table_exists($table)) {
            $field = new XMLDBField('externalname');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_CHAR, 255);
                add_field($table, $field);
            }
            $field = new XMLDBField('externalurl');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_TEXT, 'big');
                add_field($table, $field);
            }
        }
    }

    if ($oldversion < 2020111600) {
        log_debug('Adjust navigation block on collection pages in old format');
        // Find all the pages that have a navigation block on them and the navigation block is saved with information in
        // the block_instance_dimension table that also have other blocks on the page that don't have information in the
        // block_inatance_dimension table - indicating that the navigation block was saved wrong and needs fixing up
        if ($records = get_records_sql_array("SELECT abi.view, (
                                                  SELECT COUNT(*) FROM {block_instance} bbi WHERE bbi.view = abi.view
                                              ) AS block_count,
                                              COUNT(abid.block) AS block_dimension_count
                                              FROM {block_instance_dimension} abid
                                              JOIN {block_instance} abi ON abi.id = abid.block
                                              WHERE abi.view IN (
                                                  SELECT bi.view FROM {block_instance} bi
                                                  JOIN {block_instance_dimension} bid ON bid.block = bi.id
                                                  WHERE bi.blocktype = 'navigation'
                                              )
                                              GROUP BY abi.view HAVING COUNT(abid.block) = 1
                                              AND (
                                                   SELECT COUNT(*) FROM {block_instance} bbi WHERE bbi.view = abi.view
                                              ) > 1")) {
            foreach ($records as $record) {
                // Now find the block id that needs fixing
                $blockid = get_field_sql("SELECT b.block FROM {block_instance_dimension} b
                                          WHERE b.block IN (
                                              SELECT id FROM {block_instance} WHERE view = ?
                                          )", array($record->view));
                // Update it with old layout info
                $order = get_field_sql("SELECT MAX(bi.order) + 1 FROM {block_instance} bi WHERE bi.view = ?", array($record->view));
                execute_sql("UPDATE {block_instance}
                             SET \"row\" = ?, \"column\" = ?, \"order\" = ?
                             WHERE id = ?", array(1, 1, $order, $blockid));
                // Remove the new dimension info
                execute_sql("DELETE FROM {block_instance_dimension} WHERE block = ?", array($blockid));
            }
        }
    }

    if ($oldversion < 2020121700) {
        log_debug('Remove not null restriction for "usr" field in "artefact_peer_assessment"');
        $table = new XMLDBTable('artefact_peer_assessment');
        $field = new XMLDBField('usr');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, false);
        change_field_notnull($table, $field);
    }

    if ($oldversion < 2021021701) {
        log_debug('Change the constraint on view_instruction_lock.originaltemplate field');
        $table = new XMLDBTable('view_instructions_lock');
        $field = new XMLDBField('originaltemplate');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, false);
        change_field_notnull($table, $field);

        $key = new XMLDBKey('templatefk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('originaltemplate'), 'view', array('id'));
        if (db_key_exists($table, $key)) {
            drop_key($table, $key);
        }
    }

    if ($oldversion < 2021040800) {
        log_debug('Adding verifier role');
        $roles = array('verifier' => 1);
        foreach ($roles as $role => $state) {
            $obj = new stdClass();
            $obj->role              = $role;
            $obj->see_block_content = $state;
            insert_record('usr_access_roles', $obj);
        }
    }

    if ($oldversion < 2021040801) {
        log_debug('Add "lock" column to collection');
        $table = new XMLDBTable('collection');
        $field = new XMLDBField('lock');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
            add_field($table, $field);
        }
    }

    if ($oldversion < 2021040802) {
        log_debug('Add "progress" page type');
        ensure_record_exists('view_type',
            (object)array('type' => 'progress'),
            (object)array('type' => 'progress')
        );
        if ($data = check_upgrades('blocktype.verification')) {
            upgrade_plugin($data);
            install_blocktype_extras();
        }
        // Create default progress template
        set_field('usr', 'admin', 1, 'username', 'root');
        install_system_progress_view();
        set_field('usr', 'admin', 0, 'username', 'root');
        // Make sure any existing progress collections now get the 'progress' page
        if ($collections = get_records_sql_array("SELECT c.id FROM {collection} c
                                                  WHERE c.progresscompletion = 1
                                                  AND NOT EXISTS (
                                                      SELECT v.id FROM {collection_view} cv
                                                      JOIN {view} v ON v.id = cv.view
                                                      WHERE v.type = 'progress'
                                                      AND cv.collection = c.id
                                                  )")) {
            require_once(get_config('libroot') . 'collection.php');
            $count = 0;
            $limit = 500;
            $total = count($collections);
            foreach ($collections as $collection) {
                $c = new Collection($collection->id);
                $c->add_progresscompletion_view();
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2021040803) {
        log_debug('Adding verifiedprogress event type');
        $event = (object) array( "name" => "verifiedprogress");
        ensure_record_exists('event_type', $event, $event);
    }


    if ($oldversion < 2021041500) {
        $table = new XMLDBTable('collection');
        if (table_exists($table)) {
            log_debug('Adding autocopytemplate column to collection table');
            $field = new XMLDBField('autocopytemplate');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
            }
            log_debug('Adding template column to collection table');
            $field = new XMLDBField('template');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
            }
        }
    }

    if ($oldversion < 2021042200) {
        log_debug('Add new activity type for sending view access revocation notifications');
        execute_sql("INSERT INTO {activity_type}
         (name, admin, delay, allownonemethod, defaultmethod)
         VALUES('viewaccessrevoke', 0, 0, 0, 'email') ");
    }

    if ($oldversion < 2021042201) {
        log_debug('Adding removeviewaccess event type');
        $event = (object) array( "name" => "removeviewaccess");
        ensure_record_exists('event_type', $event, $event);
    }

    if ($oldversion < 2021042300) {
        log_debug('Add the "collection_template" table to map copied collection to their original template');
        $table = new XMLDBTable('collection_template');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
            $table->addFieldInfo('originaltemplate', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN, array('collection'), 'collection', array('id'));
            $table->addKeyInfo('templatefk', XMLDB_KEY_FOREIGN, array('originaltemplate'), 'collection', array('id'));
            create_table($table);
        }
    }

    if ($oldversion < 2021042301) {
        log_debug('Adding in some indexes for the "event_log" table');
        $index = new XMLDBIndex('resourceix');
        $table = new XMLDBTable('event_log');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('resourcetype', 'resourceid'));
        if (!index_exists($table, $index)) {
            add_index($table, $index);
        }
        $index = new XMLDBIndex('parentix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('parentresourcetype', 'parentresourceid'));
        if (!index_exists($table, $index)) {
            add_index($table, $index);
        }
        $index = new XMLDBIndex('ownerix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('ownertype', 'ownerid'));
        if (!index_exists($table, $index)) {
            add_index($table, $index);
        }
    }

    if ($oldversion < 2021042302) {
        log_debug('Creating table view_copy_queue');
        $table = new XMLDBTable('view_copy_queue');
        if (!table_exists($table)) {
            $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10);
            $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10);
            $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, false, XMLDB_NOTNULL);
            $table->addFieldInfo('ctime', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
            $table->addFieldInfo('status', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 0);
            $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN, array('collection'), 'collection', array('id'));
            $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
            $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));

            $table->addIndexInfo('statusix', XMLDB_INDEX_NOTUNIQUE, array('status'));

            create_table($table);
        }

        if (!get_record('cron', 'callfunction', 'portfolio_auto_copy')) {
            log_debug('Create cron job to process portfolio copies');
            $cron = new stdClass();
            $cron->callfunction = 'portfolio_auto_copy';
            $cron->minute       = '*';
            $cron->hour         = '*';
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2021042600) {
        log_debug('set cron_institution_data_weekly time');
        execute_sql("UPDATE {cron} SET minute = '0',hour='0',dayofweek='1' WHERE callfunction = 'cron_institution_data_weekly'");
    }

    if ($oldversion < 2021042700) {
        $table = new XMLDBTable('group');
        if (table_exists($table)) {
            log_debug('Adding "grouparchivereports" column to "group" table');
            $field = new XMLDBField('grouparchivereports');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
            }
        }
    }

    if ($oldversion < 2021051300) {
        log_debug('Add column to the "collection_template" table to track unlock / rollover date');
        $table = new XMLDBTable('collection_template');
        $field = new XMLDBField('rolloverdate');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_DATETIME, null, null);
            add_field($table, $field);
        }
        if (!get_record('cron', 'callfunction', 'collection_rollover') && !get_record('cron', 'callfunction', 'unlock_collections_by_rollover')) {
            log_debug('Add cron job for unlocking collections by rollover date');
            $cron = new stdClass();
            $cron->callfunction = 'unlock_collections_by_rollover';
            $cron->minute       = '0';
            $cron->hour         = '3';
            $cron->day          = '*';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2021063000) {
        log_debug('Sorting mismatch with unlock collection cron name');
        if (get_record('cron', 'callfunction', 'collection_rollover')) {
            execute_sql("UPDATE {cron} SET callfunction = ? WHERE callfunction = ?", array('unlock_collections_by_rollover', 'collection_rollover'));
        }
    }

    if ($oldversion < 2021080200) {
        // Check whether institution uses configurable theme and flag them to be resaved to get new css changes
        $custom_themes = get_records_sql_array("SELECT name FROM {institution} WHERE theme = ?", array('custom'));
        if ($custom_themes) {
            require_once(get_config('docroot') . 'lib/institution.php');
            foreach ($custom_themes as $inst) {
                set_config_institution($inst->name, 'customthemeupdate', true);
            }
        }
    }

    if ($oldversion < 2021121500) {
        log_debug('Updating description text block image src attributes missing text parameter in url');
        require_once(get_config('docroot') . 'blocktype/lib.php');
        require_once(get_config('libroot') . 'embeddedimage.php');
        // Find block instances which might need updating.
        $sql = "SELECT b.id, b.configdata, v.group, v.owner
                  FROM {block_instance} b
            INNER JOIN {view} v on b.view = v.id
                 WHERE blocktype = ?
                   AND configdata LIKE CONCAT('%description=', b.view, '%')
                 ORDER BY b.id";
        // Find the total count of block instances (for logging purposes).
        $sqlcount = "SELECT COUNT(b.id)
                       FROM {block_instance} b
                 INNER JOIN {view} v on b.view = v.id
                      WHERE blocktype = ?
                        AND configdata LIKE CONCAT('%description=', b.view, '%')";

        $total = get_field_sql($sqlcount, array('text'));
        $changes = 0;
        $limit = 100;
        $offset = 0;
        while ($total > 0 && $records = get_records_sql_array($sql, array('text'), $offset, $limit)) {
            $offset += count($records);
            foreach($records as $record) {
                $configdata = unserialize($record->configdata);
                if (
                    isset($configdata['text']) &&
                    !empty($configdata['text']) &&
                    $configdata['text'] !== (
                        $newtext = EmbeddedImage::prepare_embedded_images(
                            $configdata['text'],
                            'text',
                            $record->id,
                            $record->group,
                            $record->owner
                        )
                    )
                ) {
                    // Update the text block_instance with the $newtext.
                    $bi = new BlockInstance($record->id);
                    $configdata['text'] = $newtext;
                    $bi->set('configdata', $configdata);
                    $bi->commit();
                    $changes++;
                }
            }

            log_debug("$offset/$total");
        }

        // If we haven't found any results notify.
        if ($total === 0) {
            log_debug('Found no related block instances');
        }
        else {
            log_debug("{$changes} of {$total} block_instances configdata text have been updated with a text parameter in src attribute");
        }
    }

    if ($oldversion < 2022020400) {
        // As this SQL query can be a little slow we bump the timeout limit to 5 minutes
        set_time_limit(300);
        log_debug('Fetching potential broken pre-gridstack layouts ...');
        $results = get_records_sql_array("
            SELECT f.view, f.row, f.columns, f.maxcolumn
            FROM (
                SELECT vrc.view, vrc.row, vrc.columns, (
                    SELECT MAX(bi.column)
                    FROM {block_instance} bi
                    WHERE bi.view = vrc.view
                    AND bi.row = vrc.row
                ) AS maxcolumn,
                CASE WHEN (
                    SELECT width
                    FROM {block_instance_dimension} bid
                    JOIN {block_instance} bi ON bi.id = bid.block
                    WHERE bi.view = vrc.view
                    LIMIT 1
                ) > 0 THEN 1 ELSE 0 END AS hasdimension
                FROM {view_rows_columns} vrc
                WHERE vrc.columns < (
                    SELECT MAX(bi.column)
                    FROM {block_instance} bi
                    WHERE bi.view = vrc.view
                    AND bi.row = vrc.row
                )
                ORDER BY vrc.view, vrc.row
            ) AS f
            WHERE f.hasdimension = 0"
        );
        if (!empty($results)) {
            log_debug('Fixing up pre-gridstack layouts that have incorrect column information');
            $count = 0;
            $limit = 100;
            $total = count($results);
            foreach ($results as $r) {
                // Because we can't tell which column they meant to put the block we will
                // place it in the last column of that row
                execute_sql("
                    UPDATE {block_instance} SET \"column\" = ?
                    WHERE \"column\" > ? AND \"row\" = ? AND view = ?",
                    array($r->columns, $r->columns, $r->row, $r->view)
                );
                // Lets sort out any order problems
                $blockcolumns = get_column_sql("SELECT DISTINCT bi.column FROM {block_instance} bi WHERE bi.view = ? AND bi.row = ?", array($r->view, $r->row));
                foreach ($blockcolumns as $column) {
                    $blocks = get_column_sql("SELECT bi.id FROM {block_instance} bi WHERE bi.view = ? AND bi.row = ? AND bi.column = ? ORDER BY bi.order", array($r->view, $r->row, $column));
                    foreach ($blocks as $k => $blockid) {
                        execute_sql("UPDATE {block_instance} SET \"order\" = ? WHERE id = ?", array($k + 1, $blockid));
                    }
                }
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2022030400) {
        log_debug("Add the allow comments by default setting");
        // Check that it is not alreay set in the database
        if (get_field('config', 'value', 'field', 'allowcommentsbydefault') === false) {
            set_config('allowcommentsbydefault', 1);
        }
    }

    if ($oldversion < 2022030900) {
        log_debug("Fix up signoff blocks that are missing their db entry");
        if ($results = get_records_sql_array("
                SELECT DISTINCT(v.id) FROM {view} v
                JOIN {block_instance} bi ON bi.view = v.id
                LEFT JOIN {view_signoff_verify} vsv ON vsv.view = v.id
                WHERE bi.blocktype = ?
                AND vsv.view IS NULL", array('signoff')
            )) {
            foreach ($results as $result) {
                ensure_record_exists('view_signoff_verify', (object) array('view' => $result->id), (object) array('view' => $result->id), 'id', true);
            }
        }
    }

    if ($oldversion < 2022031500) {
        $table = new XMLDBTable('usr_institution');
        if (table_exists($table)) {
            log_debug('Adding supportadmin field to usr_institution table');
            $field = new XMLDBField('supportadmin');
            if (!field_exists($table, $field)) {
                $field->setAttributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
                add_field($table, $field);
            }
        }
    }

    if ($oldversion < 2022032100) {
        if (!get_record('cron', 'callfunction', 'file_cleanup_old_temp_files')) {
            log_debug('Add cron job for cleaning up older dataroot temp files');
            // Every second day, clean out any older temp files from the dataroot
            $cron = new stdClass();
            $cron->callfunction = 'file_cleanup_old_temp_files';
            $cron->minute       = '0';
            $cron->hour         = '2';
            $cron->day          = '*/2';
            $cron->month        = '*';
            $cron->dayofweek    = '*';
            insert_record('cron', $cron);
        }
    }

    if ($oldversion < 2022032200) {
        $table = new XMLDBTable('lti_assessment');
        if (table_exists($table)) {
            log_debug("Make sure groups associated with LTI assessment have 'submittableto' set to true");
            execute_sql("
                UPDATE {group}
                SET submittableto = 1
                WHERE id IN (
                    SELECT foo.id FROM (
                        SELECT g1.id
                        FROM {lti_assessment} l
                        JOIN {group} g1 ON g1.id = l.group
                        WHERE g1.submittableto = 0
                    ) AS foo
                )");
        }
    }

    if ($oldversion < 2022041300) {
        if ($records = get_records_sql_array("
            SELECT a.id, ae.email FROM {artefact} a
            JOIN {artefact_internal_profile_email} ae ON ae.artefact = a.id
            WHERE a.artefacttype = ?
            AND a.title != ae.email", array('email'))) {
            log_debug('Need to fix up email info drift');
            $count = 0;
            $limit = 100;
            $total = count($records);
            foreach ($records as $record) {
                execute_sql("UPDATE {artefact} SET title = ? WHERE id = ?", array($record->email, $record->id));
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2022041400) {
        log_debug('Alter the "usr_institution_migrate" table to allow for longer token value');
        $table = new XMLDBTable('usr_institution_migrate');
        if (table_exists($table)) {
            $field = new XMLDBField('token');
            $field->setAttributes(XMLDB_TYPE_CHAR, 8);
            change_field_precision($table, $field);
        }
    }

    if ($oldversion < 2022051800) {
        log_debug('Alter the "webservice" instances to match the external apps');
        $externalapps = get_records_sql_array('SELECT * FROM {oauth_server_registry}');
        $existingauth = get_records_sql_array('SELECT * FROM {auth_instance}');
        if ($externalapps) {
            foreach ($externalapps as $key => $app) {
                foreach ($existingauth as $auth) {
                    if ($app->application_title == $auth->instancename && $app->institution == $auth->institution) {
                        unset($externalapps[$key]);
                    }
                }
            }
            foreach ($externalapps as $key => $app) {
                $nextpriority = get_field_sql("SELECT MAX(priority) +1 FROM {auth_instance} WHERE institution = ?", array($app->institution));
                $authinstance = (object)array(
                    'instancename' => $app->application_title,
                    'priority'     => $nextpriority,
                    'institution'  => $app->institution,
                    'authname'     => 'webservice',
                    'active'       => 1,
                );
                insert_record('auth_instance', $authinstance);
            }
        }
    }

    if ($oldversion < 2022061500) {
        if (get_config('eventloglevel') === 'masq') {
            set_config('eventloglevel', 'masquerade');
            log_warn(get_string('updateeventlogconfigoption', 'admin'), true, false);
        }
        log_warn(get_string('registrationisoptout', 'admin'), true, false);
        set_config('new_registration_policy', true);
        if (!get_config('registration_sendweeklyupdates')) {
            require_once('registration.php');
            list($status, $message) = register_again(true);
            if ($status == 'error') {
                log_warn($message, true, false);
            }
            else {
                log_info($message);
            }
        }
    }

    if ($oldversion < 2022090100) {
        if ($records = get_column_sql("SELECT id FROM {view} WHERE theme IS NOT NULL")) {
            log_debug('Tidy up user chosen themes for portfolios');
            require_once(get_config('docroot') . 'lib/view.php');
            $count = 0;
            $limit = 100;
            $total = count($records);
            foreach ($records as $id) {
                $view = new View($id);
                if (!$view->is_themeable()) {
                    execute_sql("UPDATE {view} SET theme = NULL WHERE id = ?", array($id));
                }
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    if ($oldversion < 2022090900) {
        log_debug('Update the license URLS to be https');
        execute_sql("UPDATE {artefact_license} SET name = REPLACE(name, 'http://creativecommons.org/', 'https://creativecommons.org/')");
        execute_sql("UPDATE {artefact_license} SET name = REPLACE(name, 'https://www.gnu.org/', 'https://www.gnu.org/')");
        execute_sql("UPDATE {artefact} SET license = REPLACE(license, 'http://creativecommons.org/', 'https://creativecommons.org/')");
        execute_sql("UPDATE {artefact} SET license = REPLACE(license, 'https://www.gnu.org/', 'https://www.gnu.org/')");
    }

    return $status;
}
