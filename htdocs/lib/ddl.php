<?php // $Id: ddllib.php,v 1.42 2006/10/09 22:28:22 stronk7 Exp $
/**
 *
 * @package mahara
 * @subpackage core
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    Moodle - Modular Object-Oriented Dynamic Learning Environment
 *             http://moodle.com
 *
 *    Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com
 *              (C) 2001-3001 Eloy Lafuente (stronk7) http://contiento.com
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details:
 *
 *             http://www.gnu.org/copyleft/gpl.html
 */

// Mahara hacks
global $CFG;
$CFG->libdir = get_config('libroot');
$CFG->prefix = (isset($CFG->dbprefix)) ? $CFG->dbprefix : '';
// Mahara hacks end


// This library includes all the required functions used to handle the DB
// structure (DDL) independently of the underlying RDBMS in use. All the functions
// rely on the XMLDBDriver classes to be able to generate the correct SQL
// syntax needed by each DB.
//
// To define any structure to be created we'll use the schema defined
// by the XMLDB classes, for tables, fields, indexes, keys and other
// statements instead of direct handling of SQL sentences.
//
// This library should be used, exclusively, by the installation and
// upgrade process of Moodle.
//
// For further documentation, visit http://docs.moodle.org/en/DDL_functions

/// Add required XMLDB constants
    require_once($CFG->libdir . '/xmldb/classes/XMLDBConstants.php');

/// Add main XMLDB Generator
    require_once($CFG->libdir . '/xmldb/classes/generators/XMLDBGenerator.class.php');

/// Add required XMLDB DB classes
    require_once($CFG->libdir . '/xmldb/classes/XMLDBObject.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBFile.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBStructure.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBTable.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBField.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBKey.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBIndex.class.php');
    require_once($CFG->libdir . '/xmldb/classes/XMLDBStatement.class.php');

/// Based on $CFG->dbtype, add the proper generator class
    if (!file_exists($CFG->libdir . '/xmldb/classes/generators/' . $CFG->dbtype . '/' . $CFG->dbtype . '.class.php')) {
        error ('DB Type: ' . $CFG->dbtype . ' not supported by XMLDDB');
    }
    require_once($CFG->libdir . '/xmldb/classes/generators/' . $CFG->dbtype . '/' . $CFG->dbtype . '.class.php');


/// Add other libraries
    require_once($CFG->libdir . '/xmlize.php');
/**
 * Add a new field to a table, or modify an existing one (if oldfield is defined).
 * Warning: Please be careful on primary keys, as this function will eat auto_increments
 *
 * @uses $CFG
 * @uses $db
 * @param string $table the name of the table to modify. (Without the prefix.)
 * @param string $oldfield If changing an existing column, the name of that column.
 * @param string $field The name of the column at the end of the operation.
 * @param string $type The type of the column at the end of the operation. TEXT, VARCHAR, CHAR, INTEGER, REAL, or TINYINT
 * @param string $size The size of that column type. As in VARCHAR($size), or INTEGER($size).
 * @param string $signed For numeric column types, whether that column is 'signed' or 'unsigned'.
 * @param string $default The new default value for the column.
 * @param string $null 'not null', or '' to allow nulls.
 * @param string $after Which column to insert this one after. Not supported on Postgres.
 *
 * @return boolean Wheter the operation succeeded.
 */
function table_column($table, $oldfield, $field, $type='integer', $size='10',
                      $signed='unsigned', $default='0', $null='not null', $after='') {
    global $CFG, $db, $empty_rs_cache;

    if (!empty($empty_rs_cache[$table])) {  // Clear the recordset cache because it's out of date
        unset($empty_rs_cache[$table]);
    }

    switch (strtolower($CFG->dbtype)) {

        case 'mysql':
        case 'mysqlt':
        case 'mysqli':

            switch (strtolower($type)) {
                case 'text':
                    $type = 'TEXT';
                    $signed = '';
                    break;
                case 'integer':
                    $type = 'INTEGER('. $size .')';
                    break;
                case 'varchar':
                    $type = 'VARCHAR('. $size .')';
                    $signed = '';
                    break;
                case 'char':
                    $type = 'CHAR('. $size .')';
                    $signed = '';
                    break;
            }

            if (!empty($oldfield)) {
                $operation = 'CHANGE '. $oldfield .' '. $field;
            } else {
                $operation = 'ADD '. $field;
            }

            $default = 'DEFAULT \''. $default .'\'';

            if (!empty($after)) {
                $after = 'AFTER `'. $after .'`';
            }

            return execute_sql('ALTER TABLE '. $CFG->prefix . $table .' '. $operation .' '. $type .' '. $signed .' '. $default .' '. $null .' '. $after);

        case 'postgres':        // From Petri Asikainen
            //Check db-version
            $dbinfo = $db->ServerInfo();
            $dbver = substr($dbinfo['version'],0,3);

            //to prevent conflicts with reserved words
            $realfield = '"'. $field .'"';
            $field = '"'. $field .'_alter_column_tmp"';
            $oldfield = '"'. $oldfield .'"';

            switch (strtolower($type)) {
                case 'tinyint':
                case 'integer':
                    if ($size <= 4) {
                        $type = 'INT2';
                    }
                    if ($size <= 10) {
                        $type = 'INT';
                    }
                    if  ($size > 10) {
                        $type = 'INT8';
                    }
                    break;
                case 'varchar':
                    $type = 'VARCHAR('. $size .')';
                    break;
                case 'char':
                    $type = 'CHAR('. $size .')';
                    $signed = '';
                    break;
            }

            $default = '\''. $default .'\'';

            //After is not implemented in postgesql
            //if (!empty($after)) {
            //    $after = "AFTER '$after'";
            //}

            //Use transactions
            db_begin();

            //Always use temporary column
            execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ADD COLUMN '. $field .' '. $type);
            //Add default values
            execute_sql('UPDATE '. $CFG->prefix . $table .' SET '. $field .'='. $default);


            if ($dbver >= '7.3') {
                // modifying 'not null' is posible before 7.3
                //update default values to table
                if (strtoupper($null) == 'NOT NULL') {
                    execute_sql('UPDATE '. $CFG->prefix . $table .' SET '. $field .'='. $default .' WHERE '. $field .' IS NULL');
                    execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ALTER COLUMN '. $field .' SET '. $null);
                } else {
                    execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ALTER COLUMN '. $field .' DROP NOT NULL');
                }
            }

            execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ALTER COLUMN '. $field .' SET DEFAULT '. $default);

            if ( $oldfield != '""' ) {

                // We are changing the type of a column. This may require doing some casts...
                $casting = '';
                $oldtype = column_type($table, $oldfield);
                $newtype = column_type($table, $field);

                // Do we need a cast?
                if($newtype == 'N' && $oldtype == 'C') {
                    $casting = 'CAST(CAST('.$oldfield.' AS TEXT) AS REAL)';
                }
                else if($newtype == 'I' && $oldtype == 'C') {
                    $casting = 'CAST(CAST('.$oldfield.' AS TEXT) AS INTEGER)';
                }
                else {
                    $casting = $oldfield;
                }

                // Run the update query, casting as necessary
                execute_sql('UPDATE '. $CFG->prefix . $table .' SET '. $field .' = '. $casting);
                execute_sql('ALTER TABLE  '. $CFG->prefix . $table .' DROP COLUMN '. $oldfield);
            }

            execute_sql('ALTER TABLE '. $CFG->prefix . $table .' RENAME COLUMN '. $field .' TO '. $realfield);

            return db_commit();

        default:
            switch (strtolower($type)) {
                case 'integer':
                    $type = 'INTEGER';
                    break;
                case 'varchar':
                    $type = 'VARCHAR';
                    break;
            }

            $default = 'DEFAULT \''. $default .'\'';

            if (!empty($after)) {
                $after = 'AFTER '. $after;
            }

            if (!empty($oldfield)) {
                execute_sql('ALTER TABLE '. $CFG->prefix . $table .' RENAME COLUMN '. $oldfield .' '. $field);
            } else {
                execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ADD COLUMN '. $field .' '. $type);
            }

            execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ALTER COLUMN '. $field .' SET '. $null);
            return execute_sql('ALTER TABLE '. $CFG->prefix . $table .' ALTER COLUMN '. $field .' SET '. $default);
    }
}

/**
 * Given one XMLDBTable, check if it exists in DB (true/false)
 *
 * @param XMLDBTable table to be searched for
 * @return boolean true/false
 */
function table_exists($table) {

    global $CFG, $db;

    $exists = true;

/// Do this function silenty (to avoid output in install/upgrade process)
    $olddbdebug = $db->debug;
    $db->debug = false;

/// Load the needed generator
    $classname = 'XMLDB' . $CFG->dbtype;
    $generator = new $classname();
    $generator->setPrefix($CFG->prefix);
/// Calculate the name of the table
    $tablename = $generator->getTableName($table, false);

/// Search such tablename in DB
    $metatables = $db->MetaTables();
    $metatables = array_flip($metatables);
    $metatables = array_change_key_case($metatables, CASE_LOWER);
    if (!array_key_exists($tablename,  $metatables)) {
        $exists = false;
    }

/// Re-set original debug
    $db->debug = $olddbdebug;

    return $exists;
}

/**
 * Given one XMLDBField, check if it exists in DB (true/false)
 *
 * @uses, $db
 * @param XMLDBTable the table
 * @param XMLDBField the field to be searched for
 * @return boolean true/false
 */
function field_exists($table, $field) {

    global $CFG, $db;

    $exists = true;

/// Do this function silenty (to avoid output in install/upgrade process)
    $olddbdebug = $db->debug;
    $db->debug = false;

/// Check the table exists
    if (!table_exists($table)) {
        $db->debug = $olddbdebug; //Re-set original $db->debug
        return false;
    }

/// Load the needed generator
    $classname = 'XMLDB' . $CFG->dbtype;
    $generator = new $classname();
    $generator->setPrefix($CFG->prefix);
/// Calculate the name of the table
    $tablename = $generator->getTableName($table, false);

/// Get list of fields in table
    $fields = null;
    if ($fields = $db->MetaColumns($tablename)) {
        $fields = array_change_key_case($fields, CASE_LOWER);
    }

    if (!array_key_exists($field->getName(),  $fields)) {
        $exists = false;
    }

/// Re-set original debug
    $db->debug = $olddbdebug;

    return $exists;
}

/**
 * Given one XMLDBIndex, check if it exists in DB (true/false)
 *
 * @uses, $db
 * @param XMLDBTable the table
 * @param XMLDBIndex the index to be searched for
 * @return boolean true/false
 */
function index_exists($table, $index) {

    global $CFG, $db;

    $exists = true;

/// Do this function silenty (to avoid output in install/upgrade process)
    $olddbdebug = $db->debug;
    $db->debug = false;

/// Wrap over find_index_name to see if the index exists
    if (!find_index_name($table, $index)) {
        $exists = false;
    }

/// Re-set original debug
    $db->debug = $olddbdebug;

    return $exists;
}

/**
 * Given an XMLDBKey, check if it exists in the database (true/false).
 * NOTE: I wanted to call this key_exists() to keep the pattern of index_exists() et al,
 * but key_exists() is a PHP core alias for array_key_exists()
 * @param XMLDBTable $table
 * @param XMLDBKey $key
 */
function db_key_exists(XMLDBTable $table, XMLDBKey $key) {
    global $CFG, $db;
    // If find_key_name returns boolean false, the key doesn't exist. Otherwise, it does exist.
    return (false !== find_key_name($table, $key));
}

/**
 * Return the DB name of the key described in XMLDBKey, if it exists.
 *
 * @uses, $db
 * @param XMLDBTable the table to be searched
 * @param XMLDBKey the key to be searched
 * @return string key name of false
 */
function find_key_name(XMLDBTable $table, XMLDBKey $key) {
    /* @var $db ADOConnection */
    global $CFG, $db;

    // Do this function silently to avoid output during the install/upgrade process
    $olddbdebug = $db->debug;
    $db->debug = false;
    if (!table_exists($table)) {
        $db->debug = $olddbdebug;
        return false;
    }

    $tablename = get_config('dbprefix') . $table->getName();
    $dbname = get_config('dbname');

    // TODO: upstream this to ADODB?
    // Postgres puts the database name in the "catalog" field. Mysql puts it in "schema"
    if (is_postgres()) {
        $dbfield = 'catalog';
        // The query to find all the columns for a foreign key constraint
        $fkcolsql = "
            SELECT
                ku.column_name,
                ccu.column_name AS refcolumn_name
            FROM
                information_schema.key_column_usage ku
                INNER JOIN information_schema.constraint_column_usage ccu
                    ON ku.constraint_name = ccu.constraint_name
                    AND ccu.constraint_schema = ku.constraint_schema
                    AND ccu.constraint_catalog = ku.constraint_catalog
                    AND ccu.table_catalog = ku.constraint_catalog
                    AND ccu.table_schema = ku.constraint_schema
            WHERE
                ku.constraint_catalog = ?
                AND ku.constraint_name = ?
                AND ku.table_name = ?
                AND ku.table_catalog = ?
                AND ccu.table_name = ?
            ORDER BY ku.ordinal_position, ku.position_in_unique_constraint
        ";
    }
    else {
        $dbfield = 'schema';
        // The query to find all the columns for a foreign key constraint
        $fkcolsql = '
            SELECT
                ku.column_name,
                ku.referenced_column_name AS refcolumn_name
            FROM information_schema.key_column_usage ku
            WHERE
                ku.constraint_schema = ?
                AND ku.constraint_name = ?
                AND ku.table_name = ?
                AND ku.table_schema = ?
                AND ku.referenced_table_name = ?
            ORDER BY ku.ordinal_position, ku.position_in_unique_constraint
        ';
    }
    // Foreign keys have slightly different logic than primary and unique
    $isfk = ($key->getType() == XMLDB_KEY_FOREIGN || $key->getType() == XMLDB_KEY_FOREIGN_UNIQUE);

    $fields = $key->getFields();
    if ($isfk) {
        $reffields = $key->getRefFields();
        $reftable = get_config('dbprefix') . $key->getRefTable();
        // If the XMLDBKey is a foreign key without a ref table, or non-matching fields & ref fields,
        // then it's an invalid XMLDBKey and we know it won't match
        if (!$key->getRefTable() || count($fields) != count($reffields)) {
            log_debug('Invalid XMLDBKey foreign key passed to find_key_name()');
            $db->debug = $olddbdebug;
            return false;
        }
    }

    // Get the main record for the constraint
    $sql = "
        SELECT tc.constraint_name
        FROM
            information_schema.table_constraints tc
        WHERE
            tc.table_name = ?
            AND tc.table_{$dbfield} = ?
            AND tc.constraint_{$dbfield} = ?
            AND tc.constraint_type = ?
    ";
    $keytypes = array(
        XMLDB_KEY_PRIMARY => 'PRIMARY KEY',
        XMLDB_KEY_UNIQUE => 'UNIQUE',
        XMLDB_KEY_FOREIGN => 'FOREIGN KEY',
        XMLDB_KEY_FOREIGN_UNIQUE => 'FOREIGN KEY',
    );
    $params = array($tablename, $dbname, $dbname, $keytypes[$key->getType()]);

    $constraintrec = get_records_sql_array($sql, $params);
    // No constraints of the correct type on this table
    if (!$constraintrec) {
        $db->debug = $olddbdebug;
        return false;
    }

    // Check each constraint to see if it has the right columns
    foreach ($constraintrec as $c) {
        if ($isfk) {
            $colsql = $fkcolsql;
            $colparams = array($dbname, $c->constraint_name, $tablename, $dbname, $reftable);
        }
        else {
            $colsql = "SELECT ku.column_name
                FROM information_schema.key_column_usage ku
                WHERE
                    ku.table_name = ?
                    AND ku.table_{$dbfield} = ?
                    AND ku.constraint_{$dbfield} = ?
                    AND ku.constraint_name = ?
                ORDER BY ku.ordinal_position, ku.position_in_unique_constraint
            ";
            $colparams = array($tablename, $dbname, $dbname, $c->constraint_name);
        }
        $colrecs = get_records_sql_array($colsql, $colparams);

        // Make sure they've got the same number of columns
        if (!$colrecs || count($fields) != count($colrecs)) {
            // No match, try the next one
            continue;
        }

        // Make sure the columns match.
        reset($fields);
        reset($colrecs);
        if ($isfk) {
            reset($reffields);
        }
        while (($field = current($fields)) && ($col = current($colrecs))) {
            if (!$field == $col->column_name) {
                // This constraint has a non-matching column; try the next constraint
                continue 2;
            }
            if ($isfk) {
                $reffield = current($reffields);
                if (!$reffield == $col->refcolumn_name) {
                    // This constraint has a non-matching column; try the next constraint
                    continue 2;
                }
                next($reffields);
            }
            next($fields);
            next($colrecs);
        }

        // If they made it this far, then it's a match!
        $db->debug = $olddbdebug;
        return $c->constraint_name;
    }

    // None matched, so return false
    $db->debug = $olddbdebug;
    return false;
}

/**
 * Given one XMLDBIndex, the function returns the name of the index in DB (if exists)
 * of false if it doesn't exist
 *
 * @uses, $db
 * @param XMLDBTable the table to be searched
 * @param XMLDBIndex the index to be searched
 * @return string index name of false
 */
function find_index_name($table, $index) {

    global $CFG, $db;

/// Do this function silenty (to avoid output in install/upgrade process)
    $olddbdebug = $db->debug;
    $db->debug = false;

/// Extract index columns
    $indcolumns = $index->getFields();

/// Check the table exists
    if (!table_exists($table)) {
        $db->debug = $olddbdebug; //Re-set original $db->debug
        return false;
    }

/// Load the needed generator
    $classname = 'XMLDB' . $CFG->dbtype;
    $generator = new $classname();
    $generator->setPrefix($CFG->prefix);
/// Calculate the name of the table
    $tablename = $generator->getTableName($table, false);

    if (empty($indcolumns)) {
        $nocolumnindexname = $generator->getTableName($index);
    }

/// Get list of indexes in table
    $indexes = null;
    if ($indexes = $db->MetaIndexes($tablename)) {
        $indexes = array_change_key_case($indexes, CASE_LOWER);
    }

/// Iterate over them looking for columns coincidence
    if ($indexes) {

        if (isset($nocolumnindexname)) {
            log_debug("Function find_index_name called on an index $nocolumnindexname with no columns.  Attempting match on index names of all indexes on $tablename without columns.");
            if (isset($indexes[$nocolumnindexname]) && empty($indexes[$nocolumnindexname]['columns'])) {
                return $nocolumnindexname;
            }
            return false;
        }

        foreach ($indexes as $indexname => $index) {
            $columns = $index['columns'];
        /// Lower case column names
            $columns = array_flip($columns);
            $columns = array_change_key_case($columns, CASE_LOWER);
            $columns = array_flip($columns);
        /// Check if index matchs queried index
            $diferences = array_merge(array_diff($columns, $indcolumns), array_diff($indcolumns, $columns));
        /// If no diferences, we have find the index
            if (empty($diferences)) {
                $db->debug = $olddbdebug; //Re-set original $db->debug
                return $indexname;
            }
        }
    }
/// Arriving here, index not found
    $db->debug = $olddbdebug; //Re-set original $db->debug
    return false;
}

/**
 * Given one XMLDBTable, the function returns the name of its sequence in DB (if exists)
 * of false if it doesn't exist
 *
 * @param XMLDBTable the table to be searched
 * @return string sequence name of false
 */
function find_sequence_name($table) {

    global $CFG, $db;

    $sequencename = false;

/// Do this function silenty (to avoid output in install/upgrade process)
    $olddbdebug = $db->debug;
    $db->debug = false;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        $db->debug = $olddbdebug; //Re-set original $db->debug
        return false;
    }

/// Check table exists
    if (!table_exists($table)) {
        debugging('Table ' . $table->getName() . ' do not exist. Sequence not found', DEBUG_DEVELOPER);
        $db->debug = $olddbdebug; //Re-set original $db->debug
        return false; //Table doesn't exist, nothing to do
    }

    $sequencename = $table->getSequenceFromDB($CFG->dbtype, $CFG->prefix);

    $db->debug = $olddbdebug; //Re-set original $db->debug
    return $sequencename;
}

/**
 * This function will load one entire XMLDB file, generating all the needed
 * SQL statements, specific for each RDBMS ($CFG->dbtype) and, finally, it
 * will execute all those statements against the DB, to drop all tables.
 *
 * @param $file full path to the XML file to be used
 * @return boolean (true on success, false on error)
 */
function uninstall_from_xmldb_file($file) {
    global $CFG, $db;

    $status = true;
    $xmldb_file = new XMLDBFile($file);

    if (!$xmldb_file->fileExists()) {
        throw new InstallationException($xmldb_file->path . " doesn't exist.");
    }

    $loaded = $xmldb_file->loadXMLStructure();
    if (!$loaded || !$xmldb_file->isLoaded()) {
        throw new InstallationException("Could not load " . $xmldb_file->path);
    }

    $structure = $xmldb_file->getStructure();

    if ($tables = array_reverse($structure->getTables())) {
        foreach ($tables as $table) {
            // for MySQL, skip dropping indices and keys
            // as they will be dropped when the table is dropped
            if (!is_mysql() && $indexes = $table->getIndexes()) {
                foreach ($indexes as $index) {
                    if ($index->getName() == 'usernameuk' && is_postgres()) {
                        // this is a giant hack, but adodb cannot handle resolving
                        // the column for indexes that include lower() or something similar
                        // and i can't find a nice way to do it.
                        execute_sql('DROP INDEX {usr_use_uix}');
                        continue;
                    }
                    drop_index($table, $index);
                }
            }
            if (!is_mysql() && $keys = $table->getKeys()) {
                $sortkeys = array();
                foreach ($keys as $key) {
                    $sortkeys[] = $key->type;
                }
                array_multisort($sortkeys, SORT_DESC, $keys);
                foreach ($keys as $key) {
                    if (!is_postgres() && $key->type != XMLDB_KEY_FOREIGN && $key->type != XMLDB_KEY_FOREIGN_UNIQUE) {
                        // Skip keys for MySQL because these will be
                        // dropped when the table is dropped
                        continue;
                    }
                    drop_key($table, $key);
                }
            }
            drop_table($table);
        }
    }

    return true;
}

/**
 * This function will load one entire XMLDB file, generating all the needed
 * SQL statements, specific for each RDBMS ($CFG->dbtype) and, finally, it
 * will execute all those statements against the DB.
 *
 * @uses $CFG, $db
 * @param $file full path to the XML file to be used
 * @return boolean (true on success, false on error)
 */
function install_from_xmldb_file($file) {

    global $CFG, $db;

    $status = true;
    $xmldb_file = new XMLDBFile($file);

    if (!$xmldb_file->fileExists()) {
        throw new InstallationException($xmldb_file->path . " doesn't exist.");
    }

    $loaded = $xmldb_file->loadXMLStructure();
    if (!$loaded || !$xmldb_file->isLoaded()) {
        throw new InstallationException("Could not load " . $xmldb_file->path);
    }

    $structure = $xmldb_file->getStructure();

    if (!$sqlarr = $structure->getCreateStructureSQL($CFG->dbtype, $CFG->prefix, false)) {
        return true; //Empty array = nothing to do = no error
    }

    if (!execute_sql_arr($sqlarr)) {
        log_debug($sqlarr);
        throw new SQLException("Failed to install (check logs for xmldb errors)");
    }

    return true;
}

/**
 * This function will create the table passed as argument with all its
 * fields/keys/indexes/sequences, everything based in the XMLDB object.
 * Before creating the table, the function will check it doesn't exist.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function create_table($table, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }

/// Check table doesn't exist
    if (table_exists($table)) {
        debugging('Table ' . $table->getName() . ' exists. Create skipped', DEBUG_DEVELOPER);
        return true; //Table exists, nothing to do
    }

    if(!$sqlarr = $table->getCreateTableSQL($CFG->dbtype, $CFG->prefix, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will drop the table passed as argument
 * and all the associated objects (keys, indexes, constaints, sequences, triggers)
 * will be dropped too.
 * Before dropping the table, the function will check it exists.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function drop_table($table, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }

/// Check table exists
    if (!table_exists($table)) {
        debugging('Table ' . $table->getName() . ' does not exist. Delete skipped', DEBUG_DEVELOPER);
        return true; // Table doesn't exist, nothing to do.
    }

    if(!$sqlarr = $table->getDropTableSQL($CFG->dbtype, $CFG->prefix, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will create the temporary table passed as argument with all its
 * fields/keys/indexes/sequences, everything based in the XMLDB object
 *
 * TRUNCATE the table immediately after creation. A previous process using
 * the same persistent connection may have created the temp table and failed to
 * drop it. In that case, the table will exist, and create_temp_table() will
 * will succeed.
 *
 * NOTE: The return value is the tablename - some DBs (MSSQL at least) use special
 * names for temp tables.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return string tablename on success, false on error
 */
function create_temp_table($table, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }

/// Check table doesn't exist
    if (table_exists($table)) {
        debugging('Table ' . $table->getName() .
                  ' already exists. Create skipped', DEBUG_DEVELOPER);
        return $table->getName(); //Table exists, nothing to do
    }

    if (!$sqlarr = $table->getCreateTableSQL($CFG->dbtype, $CFG->prefix, false)) {
        return $table->getName(); //Empty array = nothing to do = no error
    }

    $sqlarr = preg_replace('/^CREATE/', "CREATE TEMPORARY", $sqlarr);

    if (execute_sql_arr($sqlarr, $continue, $feedback)) {
        return $table->getName();
    }
    else {
        return false;
    }
}

/**
 * This function will rename the table passed as argument
 * Before renaming the index, the function will check it exists
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param string new name of the index
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function rename_table($table, $newname, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }

/// Check table exists
    if (!table_exists($table)) {
        debugging('Table ' . $table->getName() . ' do not exist. Rename skipped', DEBUG_DEVELOPER);
        return true; //Table doesn't exist, nothing to do
    }

/// Check newname isn't empty
    if (!$newname) {
        debugging('New name for table ' . $index->getName() . ' is empty! Rename skipped', DEBUG_DEVELOPER);
        return true; //Table doesn't exist, nothing to do
    }

    if(!$sqlarr = $table->getRenameTableSQL($CFG->dbtype, $CFG->prefix, $newname, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will add the field to the table passed as arguments.
 * Before creating the field, the function will check it doesn't exist.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function add_field(XMLDBTable $table, $field, $continue=true, $feedback=true) {

    global $CFG, $db, $INSERTRECORD_TABLE_COLUMNS;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($field)) != 'xmldbfield') {
        return false;
    }

/// Check the field doesn't exist
    if (field_exists($table, $field)) {
        debugging('Field ' . $field->getName() . ' exists. Create skipped', DEBUG_DEVELOPER);
        return true;
    }

    if(!$sqlarr = $table->getAddFieldSQL($CFG->dbtype, $CFG->prefix, $field, false)) {
        return true; //Empty array = nothing to do = no error
    }

    unset($INSERTRECORD_TABLE_COLUMNS[$table->getName()]);
    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will drop the field from the table passed as arguments.
 * Before dropping the field, the function will check it exists.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (just the name is mandatory)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function drop_field($table, $field, $continue=true, $feedback=true) {

    global $CFG, $db, $INSERTRECORD_TABLE_COLUMNS;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($field)) != 'xmldbfield') {
        return false;
    }

/// Check the field exists
    if (!field_exists($table, $field)) {
        debugging('Field ' . $field->getName() . ' does not exist. Delete skipped', DEBUG_DEVELOPER);
        return true;
    }

    if(!$sqlarr = $table->getDropFieldSQL($CFG->dbtype, $CFG->prefix, $field, false)) {
        return true; //Empty array = nothing to do = no error
    }

    unset($INSERTRECORD_TABLE_COLUMNS[$table->getName()]);
    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will change the type of the field in the table passed as arguments
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function change_field_type($table, $field, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($field)) != 'xmldbfield') {
        return false;
    }

    if(!$sqlarr = $table->getAlterFieldSQL($CFG->dbtype, $CFG->prefix, $field, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will change the precision of the field in the table passed as arguments
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function change_field_precision($table, $field, $continue=true, $feedback=true) {

/// Just a wrapper over change_field_type. Does exactly the same processing
    return change_field_type($table, $field, $continue, $feedback);
}

/**
 * This function will change the unsigned/signed of the field in the table passed as arguments
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function change_field_unsigned($table, $field, $continue=true, $feedback=true) {

/// Just a wrapper over change_field_type. Does exactly the same processing
    return change_field_type($table, $field, $continue, $feedback);
}

/**
 * This function will change the nullability of the field in the table passed as arguments
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function change_field_notnull($table, $field, $continue=true, $feedback=true) {

/// Just a wrapper over change_field_type. Does exactly the same processing
    return change_field_type($table, $field, $continue, $feedback);
}

/**
 * This function will change the enum status of the field in the table passed as arguments
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function change_field_enum($table, $field, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($field)) != 'xmldbfield') {
        return false;
    }

    if(!$sqlarr = $table->getModifyEnumSQL($CFG->dbtype, $CFG->prefix, $field, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}
/**
 * This function will change the default of the field in the table passed as arguments
 * One null value in the default field means delete the default
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField field object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function change_field_default($table, $field, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($field)) != 'xmldbfield') {
        return false;
    }

    if(!$sqlarr = $table->getModifyDefaultSQL($CFG->dbtype, $CFG->prefix, $field, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will rename the field in the table passed as arguments
 * Before renaming the field, the function will check it exists
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBField index object (full specs are required)
 * @param string new name of the field
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function rename_field($table, $field, $newname, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($field)) != 'xmldbfield') {
        return false;
    }

/// Check field isn't id. Renaming over that field is not allowed
    if ($field->getName() == 'id') {
        debugging('Field ' . $field->getName() . ' cannot be renamed. Rename skipped', DEBUG_DEVELOPER);
        return true; //Field is "id", nothing to do
    }

/// Check field exists
    if (!field_exists($table, $field)) {
        debugging('Field ' . $field->getName() . ' do not exist. Rename skipped', DEBUG_DEVELOPER);
        return true; //Field doesn't exist, nothing to do
    }

/// Check newname isn't empty
    if (!$newname) {
        debugging('New name for field ' . $field->getName() . ' is empty! Rename skipped', DEBUG_DEVELOPER);
        return true; //Field doesn't exist, nothing to do
    }

    if(!$sqlarr = $table->getRenameFieldSQL($CFG->dbtype, $CFG->prefix, $field, $newname, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will create the key in the table passed as arguments.
 * Before creating the key, the function will check it doesn't exist.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBKey index object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function add_key($table, $key, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($key)) != 'xmldbkey') {
        return false;
    }

    // Check key doesn't exist.
    if (db_key_exists($table, $key)) {
        debugging('Key ' . $key->getName() . ' exists. Create skipped', DEBUG_DEVELOPER);
        return true; // Key exists, nothing to do.
    }

    if(!$sqlarr = $table->getAddKeySQL($CFG->dbtype, $CFG->prefix, $key, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will drop the key in the table passed as arguments.
 * Before dropping the key, the function will check it exists.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBKey key object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function drop_key($table, $key, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($key)) != 'xmldbkey') {
        return false;
    }

    // Check key exists.
    if (!db_key_exists($table, $key)) {
        debugging('Key ' . $key->getName() . ' does not exist. Delete skipped', DEBUG_DEVELOPER);
        return true; // Key doesn't exist, nothing to do.
    }

    if(!$sqlarr = $table->getDropKeySQL($CFG->dbtype, $CFG->prefix, $key, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will rename the key in the table passed as arguments
 * Experimental. Shouldn't be used at all in normal installation/upgrade!
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBKey key object (full specs are required)
 * @param string new name of the key
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function rename_key($table, $key, $newname, $continue=true, $feedback=true) {

    global $CFG, $db;

    debugging('rename_key() is one experimental feature. You must not use it in production!', DEBUG_DEVELOPER);

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($key)) != 'xmldbkey') {
        return false;
    }

/// Check newname isn't empty
    if (!$newname) {
        debugging('New name for key ' . $key->getName() . ' is empty! Rename skipped', DEBUG_DEVELOPER);
        return true; //Key doesn't exist, nothing to do
    }

    if(!$sqlarr = $table->getRenameKeySQL($CFG->dbtype, $CFG->prefix, $key, $newname, false)) {
        debugging('Some DBs do not support key renaming (MySQL, PostgreSQL, MsSQL). Rename skipped', DEBUG_DEVELOPER);
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will create the index in the table passed as arguments
 * Before creating the index, the function will check it doesn't exist.
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBIndex index object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function add_index($table, $index, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($index)) != 'xmldbindex') {
        return false;
    }

/// Check index doesn't exist
    if (index_exists($table, $index)) {
        debugging('Index ' . $index->getName() . ' exists. Create skipped', DEBUG_DEVELOPER);
        return true; //Index exists, nothing to do
    }

    if(!$sqlarr = $table->getAddIndexSQL($CFG->dbtype, $CFG->prefix, $index, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will drop the index in the table passed as arguments
 * Before dropping the index, the function will check it exists
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBIndex index object (full specs are required)
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function drop_index($table, $index, $continue=true, $feedback=true) {

    global $CFG, $db;

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($index)) != 'xmldbindex') {
        return false;
    }

    // Check index exists.
    if (!index_exists($table, $index)) {
        debugging('Index ' . $index->getName() . ' does not exist. Delete skipped', DEBUG_DEVELOPER);
        return true; //Index doesn't exist, nothing to do
    }

    if(!$sqlarr = $table->getDropIndexSQL($CFG->dbtype, $CFG->prefix, $index, false)) {
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * This function will rename the index in the table passed as arguments
 * Before renaming the index, the function will check it exists
 * Experimental. Shouldn't be used at all!
 *
 * @uses $CFG, $db
 * @param XMLDBTable table object (just the name is mandatory)
 * @param XMLDBIndex index object (full specs are required)
 * @param string new name of the index
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @return boolean true on success, false on error
 */
function rename_index($table, $index, $newname, $continue=true, $feedback=true) {

    global $CFG, $db;

    debugging('rename_index() is one experimental feature. You must not use it in production!', DEBUG_DEVELOPER);

    $status = true;

    if (strtolower(get_class($table)) != 'xmldbtable') {
        return false;
    }
    if (strtolower(get_class($index)) != 'xmldbindex') {
        return false;
    }

/// Check index exists
    if (!index_exists($table, $index)) {
        debugging('Index ' . $index->getName() . ' do not exist. Rename skipped', DEBUG_DEVELOPER);
        return true; //Index doesn't exist, nothing to do
    }

/// Check newname isn't empty
    if (!$newname) {
        debugging('New name for index ' . $index->getName() . ' is empty! Rename skipped', DEBUG_DEVELOPER);
        return true; //Index doesn't exist, nothing to do
    }

    if(!$sqlarr = $table->getRenameIndexSQL($CFG->dbtype, $CFG->prefix, $index, $newname, false)) {
        debugging('Some DBs do not support index renaming (MySQL). Rename skipped', DEBUG_DEVELOPER);
        return true; //Empty array = nothing to do = no error
    }

    return execute_sql_arr($sqlarr, $continue, $feedback);
}

/**
 * Return structure info of tables from a xmldb file
 *
 * @param string $file
 * @return array(XMLDBTable)
 * @throws InstallationException
 */
function get_tables_from_xmldb_file($file) {
    global $CFG, $db;

    $status = true;
    $xmldb_file = new XMLDBFile($file);

    if (!$xmldb_file->fileExists()) {
        throw new InstallationException($xmldb_file->path . " doesn't exist.");
    }

    $loaded = $xmldb_file->loadXMLStructure();
    if (!$loaded || !$xmldb_file->isLoaded()) {
        throw new InstallationException("Could not load " . $xmldb_file->path);
    }

    $structure = $xmldb_file->getStructure();

    return array_reverse($structure->getTables());

}

/**
 * Return structure info of tables from mahara xmldb files
 *
 * @return array(XMLDBTable)
 */
function get_tables_from_xmldb() {
    static $tables = array();
    if (!empty($tables)) {
        return $tables;
    }
    // Get database structure from plugins' tables
    foreach (array_reverse(plugin_types_installed()) as $t) {
        if ($installed = plugins_installed($t, true)) {
            foreach ($installed  as $p) {
                if (!empty($p->artefactplugin)) {
                    $location = get_config('docroot') . 'artefact/' . $p->artefactplugin . '/' . $t . '/' . $p->name . '/db/';
                }
                else {
                    $location = get_config('docroot') . $t . '/' . $p->name. '/db/';
                }
                if (is_readable($location . 'install.xml')) {
                    $tables = array_merge($tables, get_tables_from_xmldb_file($location . 'install.xml'));
                }
            }
        }
    }
    $tables = array_merge($tables, get_tables_from_xmldb_file(get_config('docroot') . 'lib/db/install.xml'));

    return $tables;
}

/**
 * Return all columns of a table in current db
 *
 * @param string $tablename not including the dbprefix
 * @return array of ADOFieldObject, with name as array key
 */
function get_columns($tablename) {

    global $CFG, $db;

    $fulltablename = $CFG->dbprefix . $tablename;
    $columns = $db->MetaColumns($fulltablename);
    // Update the field auto_increment if postgres
    // Only apply for "ID" field
    if (is_postgres() && isset($columns['ID'])) {
        $idcolumn = $columns['ID'];
        if (isset($idcolumn->default_value)
            && strpos($idcolumn->default_value, 'nextval(') !== false ) {
            if (record_exists($tablename)) {
                $rec = get_record_sql('SELECT last_value FROM "' . $fulltablename . '_id_seq"');
                $idcolumn->auto_increment = $rec->last_value + 1;
            }
            else {
                $idcolumn->auto_increment = 1;
            }
        }
        $columns['ID'] = $idcolumn;
    }
    return $columns;
}

/**
 * Return current foreign key constraints in given table
 *
 * @param string $tablename not including the dbprefix
 * @return array of array(
 *     'constraintname' => string
 *     'table' => string
 *     'fields' => array
 *     'reftable' => string
 *     'reffields' => array
 *     )
 */
function get_foreign_keys($tablename) {
    global $CFG;

    $tablename = $CFG->dbprefix . $tablename;
    $foreignkeys = array();
    // Get foreign key constraints from information_schema tables
    if (is_postgres()) {
        $dbfield = 'catalog';
        // The query to find all the columns for a foreign key constraint
        $fkcolsql = "
            SELECT
                ku.column_name,
                ccu.table_name AS reftable_name,
                ccu.column_name AS refcolumn_name
            FROM
                information_schema.key_column_usage ku
                INNER JOIN information_schema.constraint_column_usage ccu
                    ON ku.constraint_name = ccu.constraint_name
                    AND ccu.constraint_schema = ku.constraint_schema
                    AND ccu.constraint_catalog = ku.constraint_catalog
                    AND ccu.table_catalog = ku.constraint_catalog
                    AND ccu.table_schema = ku.constraint_schema
            WHERE
                ku.constraint_catalog = ?
                AND ku.constraint_name = ?
                AND ku.table_name = ?
                AND ku.table_catalog = ?
            ORDER BY ku.ordinal_position, ku.position_in_unique_constraint
        ";
    }
    else {
        $dbfield = 'schema';
        // The query to find all the columns for a foreign key constraint
        $fkcolsql = '
            SELECT
                ku.column_name,
                ku.referenced_table_name AS reftable_name,
                ku.referenced_column_name AS refcolumn_name
            FROM information_schema.key_column_usage ku
            WHERE
                ku.constraint_schema = ?
                AND ku.constraint_name = ?
                AND ku.table_name = ?
                AND ku.table_schema = ?
            ORDER BY ku.ordinal_position, ku.position_in_unique_constraint
        ';
    }
    $sql = "
        SELECT tc.constraint_name
        FROM information_schema.table_constraints tc
        WHERE
            tc.table_name = ?
            AND tc.table_{$dbfield} = ?
            AND tc.constraint_{$dbfield} = ?
            AND tc.constraint_type = ?
    ";
    $dbname = get_config('dbname');
    if ($constraintrec = get_records_sql_array($sql, array($tablename, $dbname, $dbname, 'FOREIGN KEY'))) {
        // Get foreign key constraint info
        foreach ($constraintrec as $c) {
            $fields = array();
            $reftable = '';
            $reffields = array();
            if ($colrecs = get_records_sql_array($fkcolsql, array($dbname, $c->constraint_name, $tablename, $dbname))) {
                foreach ($colrecs as $colrec) {
                    if (empty($reftable)) {
                        $reftable = $colrec->reftable_name;
                    }
                    $fields[] = $colrec->column_name;
                    $reffields[] = $colrec->refcolumn_name;
                }
            }
            if (!empty($fields) && !empty($reftable) && !empty($reffields)) {
                $foreignkeys[] = array(
                    'table'          => $tablename,
                    'constraintname' => $c->constraint_name,
                    'fields'         => $fields,
                    'reftable'       => $reftable,
                    'reffields'      => $reffields,
                );
            }
        }
    }

    return $foreignkeys;
}

/**
 * Return the server info
 *
 * @return an array of containing two elements 'description' and 'version'
 */
function get_server_info() {

    global $CFG, $db;

    return $db->ServerInfo();
}

