<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Martin Dougiamas <martin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2001-3001 Martin Dougiamas http://dougiamas.com
 * @copyright  additional modifications (c) Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Return a table name, properly prefixed and escaped
 *
 */
function db_table_name($name) {
    return db_quote_identifier(get_config('dbprefix') . $name);
}

/**
 * Searches through a query for strings looking like {name}, to replace with 
 * correctly quoted and prefixed table names
 *
 * @param string $sql The SQL to replace the placeholders in
 * @return string
 */
function db_quote_table_placeholders($sql) {
    return preg_replace_callback('/\{([a-z][a-z0-9_]+)\}/', create_function('$matches', 'return db_table_name($matches[1]);'), $sql);
}

/**
 * Given a table name or other identifier, return it quoted for the appropriate 
 * database engine currently being used
 *
 * @param string $identifier The identifier to quote
 * @return string
 */
function db_quote_identifier($identifier) {
    // Currently, postgres and mysql (in postgres compat. mode) both support 
    // the sql standard "
    if (strpos($identifier, '"') !== false) {
        return $identifier;
    }
    return '"' . $identifier . '"';
}

/**
 * Check whether the db's default character encoding is utf8
 *
 * @return bool
 */
function db_is_utf8() {
    global $db;
    if (!is_a($db, 'ADOConnection')) {
        throw new SQLException('Database connection is not available ');
    }
    if (is_mysql()) {
        $result = $db->Execute("SHOW VARIABLES LIKE 'character_set_database'");
        return $result->fields['Value'] == 'utf8';
    }
    if (is_postgres()) {
        $result = $db->Execute("SHOW SERVER_ENCODING");
        return $result->fields['server_encoding'] == 'UTF8';
    }
    return false;
}

function db_total_size() {
    global $db;
    if (!is_a($db, 'ADOConnection')) {
        throw new SQLException('Database connection is not available ');
    }
    $dbname = db_quote(get_config('dbname'));
    if (is_mysql()) {
        $result = $db->Execute("
            SELECT SUM( data_length + index_length ) AS dbsize
            FROM information_schema.tables
            WHERE table_schema = $dbname
        ");
        return $result->fields['dbsize'];
    }
    if (is_postgres()) {
        $result = $db->Execute("SELECT * FROM pg_database_size($dbname)");
        return $result->fields['pg_database_size'];
    }
    return false;
}

function column_collation_is_default($table, $column) {
    global $db;
    if (!is_a($db, 'ADOConnection')) {
        throw new SQLException('Database connection is not available ');
    }
    if (is_mysql()) {
        $result = $db->Execute("SHOW VARIABLES LIKE 'collation_database'");
        $defaultcollation = $result->fields['Value'];

        $command = 'SHOW FULL COLUMNS FROM ' . db_table_name($table) . ' WHERE field = ?';
        $stmt = $db->Prepare($command);
        $result = $db->Execute($stmt, array($column));
        return $result->fields['Collation'] == $defaultcollation;
    }
    return true;
}

/**
 * Execute a given sql command string
 *
 * Completely general function - it just runs some SQL and reports success.
 *
 * @uses $db
 * @param string $command The sql string you wish to be executed.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return string
 * @throws SQLException
 */
function execute_sql($command, $values=null) {
    global $db;
    
    if (!is_a($db, 'ADOConnection')) {
        throw new SQLException('Database connection is not available ');
    }

    $command = db_quote_table_placeholders($command);

    // @todo need to do more research into this flag - what is it for, we
    // probably want to just turn it off because we can catch the exceptions
    $olddebug = $db->debug;
    $db->debug = false;

    try {
        if (!empty($values) && is_array($values) && count($values) > 0) {
            $stmt = $db->Prepare($command);
            $result = $db->Execute($stmt, $values);
        }
        else {
            $result = $db->Execute($command);
        }
    }
    catch (ADODB_Exception $e) {
        log_debug($e->getMessage() . "Command was: $command");
        $db->debug = $olddebug;
        throw new SQLException('Could not execute command: ' . $command);
    }

    $db->debug = $olddebug;
    return true;
}

/// GENERIC FUNCTIONS TO CHECK AND COUNT RECORDS ////////////////////////////////////////

/**
 * Test whether a record exists in a table where all the given fields match the given values.
 *
 * The record to test is specified by giving up to three fields that must
 * equal the corresponding values.
 *
 * @param string $table The table to check.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return bool true if a matching record exists, else false.
 * @throws SQLException
 */
function record_exists($table, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    return record_exists_sql('SELECT * FROM ' . db_table_name($table) .' '. $select, $values);
}

/**
 * Test whether any records exists in a table which match a particular WHERE clause.
 *
 * This function returns true if at least one record is returned
 * 
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return bool true if a matching record exists, else false.
 * @throws SQLException
 */
function record_exists_select($table, $select='', $values=null) {

    global $CFG;

    if ($select) {
        $select = 'WHERE '.$select;
    }

    return record_exists_sql('SELECT * FROM '. db_table_name($table) . ' ' . $select, $values);
}

/**
 * Test whether a SQL SELECT statement returns any records.
 *
 * This function returns true if at least one record is returned.
 *
 * @param string $sql The SQL statement to be executed. If using $values, placeholder ?s are expected. If not, the string should be escaped correctly.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return bool true if the SQL executes without errors and returns at least one record.
 * @throws SQLException
 */
function record_exists_sql($sql, $values=null) {
    $rs = get_recordset_sql($sql, $values, 0, 1);
    return $rs->RecordCount() > 0;
}

/**
 * Count the records in a table where all the given fields match the given values.
 *
 * @param string $table The table to query.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return int The count of records returned from the specified criteria.
 * @throws SQLException
 */
function count_records($table, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    return count_records_sql('SELECT COUNT(*) FROM '. db_table_name($table) . ' ' . $select, $values);
}

/**
 * Count the records in a table which match a particular WHERE clause.
 *
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
 * @param array $values if using a prepared statement with placeholders in $select, pass values here. optional
 * @param string $countitem The count string to be used in the SQL call. Default is COUNT(*).
 * @return int The count of records returned from the specified criteria.
 * @throws SQLException
 */
function count_records_select($table, $select='', $values=null, $countitem='COUNT(*)') {
    if ($select) {
        $select = 'WHERE ' . $select;
    }
    return count_records_sql('SELECT '. $countitem .' FROM '. db_table_name($table) . ' ' . $select, $values);
}

/**
 * Get the result of a SQL SELECT COUNT(...) query.
 *
 * Given a query that counts rows, return that count. (In fact,
 * given any query, return the first field of the first record
 * returned. However, this method should only be used for the
 * intended purpose.) If an error occurrs, 0 is returned.
 *
 * @uses $db
 * @param string $sql The SQL string you wish to be executed.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return int        The count.
 * @throws SQLException
 */
function count_records_sql($sql, $values=null) {
    $rs = get_recordset_sql($sql, $values);
    return reset($rs->fields);
}

/// GENERIC FUNCTIONS TO GET, INSERT, OR UPDATE DATA  ///////////////////////////////////

/**
 * Get a single record as an object
 *
 * @param string $table The table to select from.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (required if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (required if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (required if field3 is given, else optional).
 * @return mixed a fieldset object containing the first mathcing record, or false if none found.
 * @throws SQLException
 */
function get_record($table, $field1, $value1, $field2=null, $value2=null, $field3=null, $value3=null, $fields='*') {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    return get_record_sql('SELECT ' . $fields . ' FROM ' . db_table_name($table) . ' ' . $select, $values);
}

/**
 * Get a single record as an object using an SQL statement
 *
 * This function is designed to retrieve ONE record. If your query returns more than one record,
 * an exception is thrown. If you want more than one record, use get_records_sql_array or get_records_sql_assoc
 *
 * @param string $sql The SQL string you wish to be executed, should normally only return one record.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return Found record as object. False if not found
 * @throws SQLException
 */
function get_record_sql($sql, $values=null) {
    $limitfrom = 0;
    $limitnum  = 0;
    # regex borrowed from htdocs/lib/adodb/adodb-lib.inc.php
    if (!preg_match('/\sLIMIT\s+[0-9]+/i', $sql)) {
        $limitfrom = 0;
        $limitnum  = 2;
    }

    if (!$rs = get_recordset_sql($sql, $values, $limitfrom, $limitnum)) {
        return false;
    }

    $recordcount = $rs->RecordCount();

    if ($recordcount == 0) {          // Found no records
        return false;
    }
    else if ($recordcount == 1) {    // Found one record 
       return (object)$rs->fields;
    }
    else {                          // Error: found more than one record
        throw new SQLException('get_record_sql found more than one row. If you meant to retrieve more '
            . 'than one record, use get_records_*, otherwise check your code or database for inconsistencies');
    }
}

/**
 * Gets one record from a table, as an object
 *
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @param string $fields A comma separated list of fields to be returned from the chosen table.
 * @return object Returns an array of found records (as objects)
 * @throws SQLException
 */
function get_record_select($table, $select='', $values=null, $fields='*') {
    if ($select) {
        $select = 'WHERE '. $select;
    }
    return get_record_sql('SELECT '. $fields .' FROM ' . db_table_name($table) .' '. $select, $values);
}

/**
 * Get a number of records as an ADODB RecordSet.
 *
 * Selects records from the table $table.
 *
 * If specified, only records where the field $field has value $value are retured.
 *
 * If specified, the results will be sorted as specified by $sort. This
 * is added to the SQL as "ORDER BY $sort". Example values of $sort
 * mightbe "time ASC" or "time DESC".
 *
 * If $fields is specified, only those fields are returned.
 *
 * This function is internal to datalib, and should NEVER should be called directly
 * from general Moodle scripts.  Use get_record, get_records_* etc.
 *
 * If you only want some of the records, specify $limitfrom and $limitnum.
 * The query will skip the first $limitfrom records (according to the sort
 * order) and then return the next $limitnum records. If either of $limitfrom
 * or $limitnum is specified, both must be present.
 *
 * The return value is an ADODB RecordSet object
 * @link http://phplens.com/adodb/reference.functions.adorecordset.html
 * if the query succeeds. If an error occurrs, an exception is thrown.
 *
 * @param string $table the table to query.
 * @param string $field a field to check (optional).
 * @param string $value the value the field must have (required if field1 is given, else optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an ADODB RecordSet object.
 * @throws SQLException
 */
function get_recordset($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $values = null;
    if ($field) {
        $select = db_quote_identifier($field) . " = ?";
        $values = array($value);
    } else {
        $select = '';
    }

    return get_recordset_select($table, $select, $values, $sort, $fields, $limitfrom, $limitnum);
}

/**
 * Get a number of records as an ADODB RecordSet.
 *
 * If given, $select is used as the SELECT parameter in the SQL query,
 * otherwise all records from the table are returned.
 *
 * Other arguments and the return type as for @see function get_recordset.
 *
 * @param string $table the table to query.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an ADODB RecordSet object
 * @throws SQLException
 */
function get_recordset_select($table, $select='', $values=null, $sort='', $fields='*', $limitfrom='', $limitnum='') {
    if ($select) {
        $select = ' WHERE '. $select;
    }

    if ($limitfrom !== '') {
        $limit = 'LIMIT ' . intval($limitnum)  . ' OFFSET ' . intval($limitfrom);
    } else {
        $limit = '';
    }

    if ($sort) {
        $sort = ' ORDER BY '. $sort;
    }

    return get_recordset_sql('SELECT '. $fields .' FROM '. db_table_name($table) . $select . $sort .' '. $limit, $values);
}

/**
 * Get a number of records as an ADODB RecordSet.  $sql must be a complete SQL query.
 * This function is internal to datalib, and should NEVER should be called directly
 * from general Moodle scripts.  Use get_record, get_records_* etc.
 *
 * The return type is as for @see function get_recordset.
 *
 * @uses $db
 * @param string $sql the SQL select query to execute.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an ADODB RecordSet object
 * @throws SQLException
 */
function get_recordset_sql($sql, $values=null, $limitfrom=null, $limitnum=null) {
    global $db;

    if (!is_a($db, 'ADOConnection')) {
        throw new SQLException('Database connection is not available ');
    }

    $sql = db_quote_table_placeholders($sql);

    try {
        if ($limitfrom || $limitnum) {
            ///Special case, 0 must be -1 for ADOdb
            $limitfrom = empty($limitfrom) ? -1 : $limitfrom;
            $limitnum  = empty($limitnum) ? -1 : $limitnum;
            $rs = $db->SelectLimit($sql, $limitnum, $limitfrom, $values);
        } else {
            $rs = false;
            if (!empty($values) && is_array($values) && count($values) > 0) {
                $stmt = $db->Prepare($sql);
                $rs = $db->Execute($stmt, $values);
            } else {
                $rs = $db->Execute($sql);
            }
        }
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $sql, $values));
    }
 
   return $rs;
}

/**
 * Utility function to turn a result set into an array of records
 *
 * @param object an ADODB RecordSet object.
 * @return mixed an array of objects, or false if the RecordSet was empty.
 * @throws SQLException
 */
function recordset_to_array($rs) {
    if ($rs && $rs->RecordCount() > 0) {
        $array = $rs->GetArray();
        foreach ($array as &$a) {
            $a = (object)$a;
        }
        return $array;
    }
    else {
        return false;
    }
}


//
// Generic data retrieval functions - get_records*
//

/**
 * Utility function to turn a result set into an associative array of records
 * This method turns a result set into a hash of records (keyed by the first
 * field in the result set)
 *
 * @param  object $rs An ADODB RecordSet object.
 * @return mixed  An array of objects, or false if the RecordSet was empty.
 * @throws SQLException
 * @access private
 */
function recordset_to_assoc($rs) {
    if ($rs && $rs->RecordCount() > 0) {
        // First of all, we are going to get the name of the first column
        // to introduce it back after transforming the recordset to assoc array
        // See http://docs.moodle.org/en/XMLDB_Problems, fetch mode problem.
        $firstcolumn = $rs->FetchField(0);
        // Get the whole associative array
        if ($records = $rs->GetAssoc(true)) {
            foreach ($records as $key => $record) {
                $record[$firstcolumn->name] = $key;
                $objects[$key] = (object) $record; 
            }
            return $objects;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Get a number of records as an associative array of objects. (WARNING: this
 * does not return an array, it returns an associative array keyed by the first
 * column in the result set. As a result, you may lose some rows! Please use
 * {@link get_records_*_array} instead where possible)
 *
 * If the query succeeds and returns at least one record, the
 * return value is an array of objects, one object for each
 * record found. The array key is the value from the first
 * column of the result set. The object associated with that key
 * has a member variable for each column of the results.
 *
 * @param string $table the table to query.
 * @param string $field a field to check (optional).
 * @param string $value the value the field must have (requred if field1 is given, else optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws SQLException
 */
function get_records_assoc($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $rs = get_recordset($table, $field, $value, $sort, $fields, $limitfrom, $limitnum);
    return recordset_to_assoc($rs);
}

/**
 * Get a number of records as an array of objects.
 *
 * If the query succeeds and returns at least one record, the
 * return value is an array of objects, one object for each
 * record found. The array key is the value from the first
 * column of the result set. The object associated with that key
 * has a member variable for each column of the results.
 *
 * @param string $table the table to query.
 * @param string $field a field to check (optional).
 * @param string $value the value the field must have (requred if field1 is given, else optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws SQLException
 */
function get_records_array($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $rs = get_recordset($table, $field, $value, $sort, $fields, $limitfrom, $limitnum);
    return recordset_to_array($rs);
}

/**
 * Get a number of records as an associative array of objects.
 *
 * Return value as for @see function get_records_assoc
 *
 * @param string $table the table to query.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws SQLException
 */
function get_records_select_assoc($table, $select='', $values=null, $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $rs = get_recordset_select($table, $select, $values, $sort, $fields, $limitfrom, $limitnum);
    return recordset_to_assoc($rs);
}

/**
 * Get a number of records as an array of objects.
 *
 * Return value as for {@link get_records_array}.
 *
 * @param string $table the table to query.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws SQLException
 */
function get_records_select_array($table, $select='', $values=null, $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $rs = get_recordset_select($table, $select, $values, $sort, $fields, $limitfrom, $limitnum);
    return recordset_to_array($rs);
}

/**
 * Get a number of records as an associative array of objects.
 *
 * Return value as for @see function get_records_assoc
 *
 * @param string $sql the SQL select query to execute.
 * @param array $values When using prepared statements, this is the value array.
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws SQLException
 */
function get_records_sql_assoc($sql,$values, $limitfrom='', $limitnum='') {
    $rs = get_recordset_sql($sql, $values, $limitfrom, $limitnum);
    return recordset_to_assoc($rs);
}

/**
 * Get a number of records as an array of objects.
 *
 * Return value as for {@link get_records_array}
 *
 * @param string $sql the SQL select query to execute.
 * @param array $values When using prepared statements, this is the value array.
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws SQLException
 */
function get_records_sql_array($sql,$values, $limitfrom='', $limitnum='') {
    $rs = get_recordset_sql($sql, $values, $limitfrom, $limitnum);
    return recordset_to_array($rs);
}


//
// Menu related functions - get_records_*_menu
//

/**
 * Utility function used by the following 3 methods.
 *
 * @param object $rs an ADODB RecordSet object with two columns.
 * @return mixed an associative array, or false if an error occured or the RecordSet was empty.
 * @access private
 */
function recordset_to_menu($rs) {
    global $CFG;

    if ($rs && $rs->RecordCount() > 0) {
        $keys = array_keys($rs->fields);
        $key0 = $keys[0];
        if (isset($keys[1])) {
            $key1 = $keys[1];
        }
        else {
            $key1 = $keys[0];
        }
        while (!$rs->EOF) {
            $menu[$rs->fields[$key0]] = $rs->fields[$key1];
            $rs->MoveNext();
        }
        return $menu;
    } else {
        return false;
    }
}

/**
 * Get the first two columns from a number of records as an associative array.
 *
 * Arguments as for {@link get_recordset}.
 *
 * If no errors occur, and at least one records is found, the return value
 * is an associative whose keys come from the first field of each record,
 * and whose values are the corresponding second fields. If no records are found,
 * or an error occurs, false is returned.
 *
 * @param string $table the table to query.
 * @param string $field a field to check (optional).
 * @param string $value the value the field must have (requred if field1 is given, else optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @return mixed an associative array, or false if no records were found or an error occured.
 */
function get_records_menu($table, $field='', $value='', $sort='', $fields='*') {
    $rs = get_recordset($table, $field, $value, $sort, $fields);
    return recordset_to_menu($rs);
}

/**
 * Get the first two columns from a number of records as an associative array.
 *
 * Arguments as for @see function get_recordset_select.
 * Return value as for @see function get_records_menu.
 *
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @param string $sort Sort order (optional) - a valid SQL order parameter
 * @param string $fields A comma separated list of fields to be returned from the chosen table.
 * @return mixed an associative array, or false if no records were found or an error occured.
 */
function get_records_select_menu($table, $select='', $values=null, $sort='', $fields='*') {
    $rs = get_recordset_select($table, $select, $values, $sort, $fields);
    return recordset_to_menu($rs);
}

/**
 * Get the first two columns from a number of records as an associative array.
 *
 * Arguments as for @see function get_recordset_sql.
 * Return value as for @see function get_records_menu.
 *
 * @param string $sql The SQL string you wish to be executed.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return mixed an associative array, or false if no records were found or an error occured.
 */
function get_records_sql_menu($sql,$values=null) {
    $rs = get_recordset_sql($sql,$values);
    return recordset_to_menu($rs);
}


//
// Field related data access - get_field*
//

/**
 * Get a single value from a table row where all the given fields match the given values.
 *
 * @param string $table the table to query.
 * @param string $field the field to return the value of.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return mixed the specified value
 * @throws SQLException
 */
function get_field($table, $field, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    
    return get_field_sql('SELECT ' . $field . ' FROM ' . db_table_name($table) . ' ' . $select, $values);
}

/**
 * Get a single value from a table.
 *
 * @param string $sql an SQL statement expected to return a single value.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return mixed the specified value.
 * @throws SQLException
 */
function get_field_sql($sql, $values=null) {
    $rs = get_recordset_sql($sql, $values);
    if ($rs && $rs->RecordCount() == 1) {
        return reset($rs->fields);
    } else {
        return false;
    }
}


//
// Column related data access - get_column*
//

/**
 * Get a single column from a table where all the given fields match the given values.
 *
 * @param string $table the table to query.
 * @param string $field the field to return the value of.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return mixed the specified value
 * @throws SQLException
 */
function get_column($table, $field, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    
    return get_column_sql('SELECT ' . $field . ' FROM ' . db_table_name($table) . ' ' . $select, $values);
}

/**
 * Get a single column from a table.
 *
 * @param string $sql an SQL statement expected to return a single value.
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return mixed the specified value.
 * @throws SQLException
 */
function get_column_sql($sql, $values=null) {
    global $db;

    $sql = db_quote_table_placeholders($sql);

    try {
        return $db->GetCol($sql, $values);
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $sql, $values));
    }
}


//
// Field related data modification - set_field*
//

/**
 * Set a single field in every table row where all the given fields match the given values.
 *
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param string $newfield the field to set.
 * @param string $newvalue the value to set the field to.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return mixed An ADODB RecordSet object with the results from the SQL call or false.
 * @throws SQLException
 */
function set_field($table, $newfield, $newvalue, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    global $db;

    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);

    return set_field_select($table, $newfield, $newvalue, $select, $values);
}

function set_field_select($table, $newfield, $newvalue, $select, $values) {
    global $db;

    // @todo Catalyst IT Ltd
    if (!empty($select) && !preg_match('/^\s*where/i', $select)) {
        $select = ' WHERE ' . $select;
    }

    $select = db_quote_table_placeholders($select);

    $values = array_merge(array($newvalue), $values);
    $sql = 'UPDATE '. db_table_name($table) .' SET '. db_quote_identifier($newfield)  .' = ? ' . $select;
    try {
        $stmt = $db->Prepare($sql);
        return $db->Execute($stmt, $values);
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $sql, $values));
    }
}


//
// Delete based functions - delete_records*
//

/**
 * Delete the records from a table where all the given fields match the given values.
 *
 * @uses $db
 * @param string $table the table to delete from.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return mixed An ADODB RecordSet object with the results from the SQL call or false.
 * @throws SQLException
 */
function delete_records($table, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    global $db;

    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);

    $sql = 'DELETE FROM '. db_table_name($table) . ' ' . $select;
    try {
        $stmt = $db->Prepare($sql);
        return $db->Execute($stmt,$values);
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $sql, $values));
    }
}

/**
 * Delete one or more records from a table
 *
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call (used to define the selection criteria).
 * @param array $values When using prepared statements, this is the value array (optional).
 * @return object A PHP standard object with the results from the SQL call.
 * @throws SQLException
 */
function delete_records_select($table, $select='',$values=null) {
    if ($select) {
        $select = 'WHERE '.$select;
    }
    return delete_records_sql('DELETE FROM '. db_table_name($table) .' '. $select, $values);
}

/**
 * @todo <nigel> This function does nothing delete specific. The functionality 
 * it has with the $values parameter should be merged with the execute_sql 
 * function
 */
function delete_records_sql($sql, $values=null) {
    global $db;

    $sql = db_quote_table_placeholders($sql);

    try {
        $result = false;
        if (!empty($values) && is_array($values) && count($values) > 0) {
            $stmt = $db->Prepare($sql);
            $result = $db->Execute($stmt, $values);
        } else {
            $result = $db->Execute($sql);
        }
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $sql, $values));
    }
    return $result;
}

/**
 * Insert a record into a table and return the "id" field if required
 *
 * If the return ID isn't required, then this just reports success as true/false.
 * $dataobject is an object containing needed data
 *
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param array $dataobject A data object with values for one or more fields in the record
 * @param string $primarykey The primary key of the table we are inserting into (almost always "id")
 * @param bool $returnpk Should the id of the newly created record entry be returned? If this option is not requested then true/false is returned.
 * @throws SQLException
 */
function insert_record($table, $dataobject, $primarykey=false, $returnpk=false) {
    // $INSERTRECORD_NOCACHE is yet another work around of dmllib/adodb's ineptitude.
    // It's all nice to cache the table columns lookup, but what if the table 
    // columns change over the life of the page load? This happens when an 
    // upgrade is running. All of a sudden, the table_column cache is out of 
    // date and we can't insert new data properly.
    // Temporary solution: set INSERTRECORD_NOCACHE to true before your calls 
    // that need a new lookup, and unset it afterwards
    global $db, $INSERTRECORD_NOCACHE;
    static $table_columns;
    
    // Determine all the fields in the table
    if (empty($INSERTRECORD_NOCACHE) && is_array($table_columns) && array_key_exists($table, $table_columns)) {
        $columns = $table_columns[$table];
    }
    else {
        if (!$columns = $db->MetaColumns(get_config('dbprefix') . $table)) {
            throw new SQLException('Table "' . get_config('dbprefix') . $table . '" does not appear to exist');
        }
        $table_columns[$table] = $columns;
    }
    
    if (!empty($primarykey)) {
        unset($dataobject->{$primarykey});
        if (!empty($returnpk) && is_postgres()) {
            $pksql = "SELECT NEXTVAL('" . get_config('dbprefix') . "{$table}_{$primarykey}_seq')";
            if ($nextval = (int)get_field_sql($pksql)) {
                $setfromseq = true;
                $dataobject->{$primarykey} = $nextval;
            }
        }
    }

    $data = (array)$dataobject;

  // Pull out data matching these fields
    $ddd = array();
    foreach ($columns as $column) {
        if (isset($data[$column->name])) {
            if ($column->name == $primarykey && empty($setfromseq)) {
                continue;
            }
            $ddd[$column->name] = $data[$column->name];
        }
    }

    // Construct SQL queries
    $numddd = count($ddd);
    $count = 0;
    $insertSQL = 'INSERT INTO '. db_table_name($table) .' (';
    $fields = '';
    $values = '';
    foreach ($ddd as $key => $value) {
        $count++;
        $fields .= '"' . $key . '"';
        $values .= '?';
        if ($count < $numddd) {
            $fields .= ', ';
            $values .= ', ';
        }
    }
    $insertSQL .= $fields.') VALUES ('.$values.')';

    // Run the SQL statement
    try {
        $stmt = $db->Prepare($insertSQL);
        $rs = $db->Execute($stmt,$ddd);
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $insertSQL, $ddd));
    }

    // If a return ID is not needed then just return true now
    if (empty($returnpk)) {
        return true;
    }

    // We already know the record PK if it's been passed explicitly,
    // or if we've retrieved it from a sequence (Postgres).
    if (!empty($dataobject->{$primarykey})) {
        return $dataobject->{$primarykey};
    }

    // This only gets triggered with non-Postgres databases
    // however we have some postgres fallback in case we failed 
    // to find the sequence.
    $id = $db->Insert_ID();  

    if (is_postgres()) {
        // try to get the primary key based on id
        try {
            $oidsql = 'SELECT ' . $primarykey . ' FROM '. db_table_name($table) . ' WHERE oid = ' . $id;
            $rs = $db->Execute($oidsql);
            if ($rs->RecordCount() == 1) {
                return (integer)$rs->fields[0];
            }
            throw new SQLException('WTF: somehow got more than one record when searching for a primary key');
        }
        catch (ADODB_Exception $e) {
            throw new SQLException("Trying to get pk from oid failed: " 
                                   . $e->getMessage() . " sql was $oidsql");
        }
    }

    return (integer)$id;
}

/**
 * Inserts a record, only if the record does not already exist. 
 * If the record DOES exist, it is updated.
 *
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param array $whereobject A data object with values for one or more fields in the record (to determine whether the record exists or not)
 * @param array $dataobject A data object with values for one or more fields in the record (to be inserted or updated)
 * @param string $primarykey The primary key of the table we are inserting into (almost always "id")
 * @param bool $returnpk Should the id of the newly created record entry be returned? If this option is not requested then true/false is returned.
 * @throws SQLException
 */
function ensure_record_exists($table, $whereobject, $dataobject, $primarykey=false, $returnpk=false) {
    $columns = (array)$whereobject;
    $field = '*';
    $where = array();
    $toreturn = false;

    foreach ($columns as $key => $value) {
        if ($field == '*') {
            $field = $key;
        }

        $where[] = db_quote_identifier($key) . ' = ' . db_quote($value);
    }

    $where = implode(' AND ', $where);

    if (is_postgres()) {
        $where .= ' FOR UPDATE ';
    }
    else {
        // @TODO maybe some mysql specific stuff here
    }
        
    db_begin();
    if ($exists = get_record_select($table, $where)) {
        if ($returnpk) {
            $toreturn = $exists->{$primarykey};
        }
        else {
            $toreturn = true;
        }
        if ($dataobject && $dataobject != $whereobject) { // we want to update it)
            update_record($table, $dataobject, $whereobject);
        }
    }
    else {
        $toreturn = insert_record($table, $dataobject, $primarykey, $returnpk);
    }
    db_commit();
    return $toreturn;
}

/**
 * Update a record in a table
 *
 * $dataobject is an object containing needed data
 * Relies on $dataobject having a variable "id" to
 * specify the record to update
 *
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param array $dataobject An object with contents equal to fieldname=>fieldvalue. Must have an entry for 'id' to map to the table specified.
 * @param mixed $where defines the WHERE part of the upgrade. Can be string (key) or array (keys) or hash (keys/values).
 * If the first two, values are expected to be in $dataobject. 
 * @return bool
 * @throws SQLException
 */
function update_record($table, $dataobject, $where=null) {

    global $db;

    if (is_object($dataobject)) {
        $dataobject = clone $dataobject;
    }

    if (empty($where)) {
        $where = 'id';
        if (!isset($dataobject->id) ) { 
            // nothing to put in the where clause and we don't want to update everything
            throw new SQLException('update_record called with no where clause and no ID');
        }
    }

    $wherefields = array();
    $wherevalues = array();
    $values = array();

    if (is_string($where)) { 
        // treat it like a stack (ie, field in dataobject)
        $where = array($where);
    }

    if (is_object($where) || is_hash($where)) {
        // the values are contained in the where ...
        foreach ((array)$where as $field => $value) {
            $wherefields[] = $field;
            $wherevalues[] = $value;
            unset($dataobject->{$field});
        }
    }
    else if (is_array($where)) {
        // look for the values in $dataobject and complain bitterly if they're not there
        foreach ($where as $field) {
            if (!isset($dataobject->{$field})) {
                throw new SQLException('Field in where clause not in the update object');
            }
            $wherefields[] = $field;
            $wherevalues[] = $dataobject->{$field};
            unset($dataobject->{$field});
        }
    } else {
        throw new SQLException('the $where object is in a very odd form');
    }

    static $table_columns;
    
    // Determine all the fields in the table
    if (is_array($table_columns) && isset($table_columns[$table])) {
        $columns = $table_columns[$table];
    } else {
        if (!$columns = $db->MetaColumns(get_config('dbprefix') . $table)) {
            throw new SQLException('Could not get columns for table ' . $table);
        }
        $table_columns[$table] = $columns;
    }

    $data = (array)$dataobject;

    // Pull out data matching these fields
    $ddd = array();
    foreach ($columns as $column) {
        if (!in_array($column->name, $wherefields) && array_key_exists($column->name, $data) ) {
            $ddd[$column->name] = $data[$column->name];
            // PostgreSQL bytea support
            if (is_postgres() && $column->type == 'bytea') {
                $ddd[$column->name] = $db->BlobEncode($ddd[$column->name]);
            }
        }
    }

    // Construct SQL queries
    $numddd = count($ddd);
    $count = 0;
    $update = '';

    foreach ($ddd as $key => $value) {
        $count++;
        $update .= db_quote_identifier($key) .' = ? ';
        if ($count < $numddd) {
            $update .= ', ';
        }
        $values[] = $value;
    }

    $whereclause = '';
    $count = 0;
    $numddd = count($wherefields);

    foreach ($wherefields as $field) {
        $count++;
        $whereclause .= db_quote_identifier($field) .' = ? ';
        if ($count < $numddd) {
            $whereclause .= ' AND ';
        }
    }

    $sql = 'UPDATE '. db_table_name($table) .' SET '. $update .' WHERE ' . $whereclause;
    try { 
        $stmt = $db->Prepare($sql);
        $rs = $db->Execute($stmt,array_merge($values, $wherevalues));
        return true;
    }
    catch (ADODB_Exception $e) {
        throw new SQLException(create_sql_exception_message($e, $sql, array_merge($values, $wherevalues)));
    }
}


/**
 * Prepare a SQL WHERE clause to select records where the given fields match the given values.
 *
 * Prepares a where clause of the form
 *     WHERE field1 = value1 AND field2 = value2 AND field3 = value3
 * except that you need only specify as many arguments (zero to three) as you need.
 *
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 */
function where_clause($field1='', $value1='', $field2='', $value2='', $field3='', $value3='') {
    if ($field1) {
        $select = "WHERE \"$field1\" = '$value1'";
        if ($field2) {
            $select .= " AND \"$field2\" = '$value2'";
            if ($field3) {
                $select .= " AND \"$field3\" = '$value3'";
            }
        }
    } else {
        $select = '';
    }
    return $select;
}

/**
 * Prepares a SQL WHERE clause to select records where the given fields match some values.
 * Uses ? as placeholders for prepared statments
 * 
 * @param string $field1 the first field to check (optional).
 * @param string $field2 the second field to check (optional).
 * @param string $field3 the third field to check (optional).
 * @private
 */
function where_clause_prepared($field1='', $field2='', $field3='') {
    $select = '';
    if (!empty($field1)) {
        $select = " WHERE \"$field1\" = ? ";
        if (!empty($field2)) {
            $select .= " AND \"$field2\" = ? ";
            if (!empty($field3)) {
                $select .= " AND \"$field3\" = ? ";
            }
        }
    } 
    return $select;
}

/*
 * Useful helper function to only push optional values into the array 
 * for prepared statements to avoid empty slots.
 * all parameters are optional.
 *
 * @private
 */
function where_values_prepared($value1=null, $value2=null, $value3=null, $value4=null) {
    $values = array();
    if (isset($value1)) {
        $values[] = $value1;
        if (isset($value2)) {
            $values[] = $value2;
            if (isset($value3)) {
                $values[] = $value3;
                if (isset($value4)) {
                    $values[] = $value4;
                }
            }
        }
    }
    return $values;
}

/**
 * Get the data type of a table column, using an ADOdb MetaType() call.
 *
 * @uses $db
 * @param string $table The name of the database table
 * @param string $column The name of the field in the table
 * @return string Field type or false if error
 */
function column_type($table, $column) {
    global $db;

    if(!$rs = $db->Execute('SELECT ' . $column.' FROM ' . db_table_name($table) . ' WHERE 1=2')) {
        return false;
    }

    $field = $rs->FetchField(0);
    return $rs->MetaType($field->type);
}

/**
 * This function will execute an array of SQL commands, returning
 * true/false if any error is found and stopping/continue as desired.
 * It's widely used by all the ddllib.php functions
 * 
 * @private
 * @param array sqlarr array of sql statements to execute
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @param boolean true if everything was ok, false if some error was found
 */
// in dml not ddl because we want to keep ddl 'clean upstream' - p

function execute_sql_arr($sqlarr, $continue=true, $feedback=true) {

    if (!is_array($sqlarr)) {
        return false;
    }

    $status = true;
    foreach($sqlarr as $sql) {
        if (!execute_sql($sql, $feedback)) {
            $status = false;
            if (!$continue) {
                break;
            }
        }
    }
    return $status;
}

/**
 * Format the timestamp $ts in the format the database accepts; this can be a
 * Unix integer timestamp or an ISO format Y-m-d H:i:s. Uses the fmtTimeStamp
 * field, which holds the format to use. If null or false or '' is passed in,
 * it will be converted to an SQL null.
 * 
 * Returns the timestamp as a quoted string.
 *
 * @param ts	a timestamp in Unix date time format.
 *
 * @return  timestamp string in database timestamp format
 */
function db_format_timestamp($ts) {
    global $db;

    // Otherwise $db->BindTimeStamp() returns the string 'null', which is not 
    // what we want
    if (empty($ts)) {
        return null;
    }
    return $db->BindTimeStamp($ts);
}

/**
 * Given a field name, this returns a function call suitable for the current
 * database to return a unix timestamp
 * 
 * @param field the field to apply the function to
 * @param as    what to name the field (optional, defaults to $field). If false,
 *              no naming is done.
 *
 * @return  timestamp string in database timestamp format
 */
function db_format_tsfield($field, $as = null) {
    $tsfield = '';
    if (is_postgres()) {
        $tsfield = "FLOOR(EXTRACT(EPOCH FROM $field))";
    }
    else if (is_mysql()) {
        $tsfield = "IF($field >= '1970-01-01', UNIX_TIMESTAMP($field), TIMESTAMPDIFF(SECOND, '1970-01-01', $field))";
    }
    else {
        throw new SQLException('db_format_tsfield() is not implemented for your database engine (' . get_config('dbtype') . ')');
    }

    if ($as === null) {
        $as = $field;
    }

    if (!empty($as)) {
        $tsfield .= " AS $as";
    }

    return $tsfield;
}

/**
 * This function, called from setup.php includes all the configuration
 * needed to properly work agains any DB. It setups connection encoding
 * and some other variables.
 */
function configure_dbconnection() {
    global $db, $CFG;

    if (!empty($CFG->perftolog) || !empty($CFG->perftofoot)) {
        $db->fnExecute = 'increment_perf_db';
        $db->fnCacheExecute = 'increment_perf_db_cached';
    }

    $db->Execute("SET NAMES 'utf8'");

    if (is_mysql()) {
        $db->Execute("SET SQL_MODE='POSTGRESQL'");
    }

    // more later..
}

function is_postgres() {
    return (strpos(get_config('dbtype'), 'postgres') === 0);
}

function is_mysql() {
    return (strpos(get_config('dbtype'), 'mysql') === 0);
}

/**
 * function to convert an array to
 * an array of placeholders (?) 
 * with the right number of values
 *
 * @param array $array input array
 */
function db_array_to_ph($array) {
    return array_pad(array(), count($array), '?');
}

// This is used by the SQLException, to detect if there is a transaction when
// an error occurs, so it can roll the transaction back
$GLOBALS['_TRANSACTION_LEVEL'] = 0;

/**
 * This function starts a smart transaction
 * 
 */
function db_begin() {
    global $db;

    $GLOBALS['_TRANSACTION_LEVEL']++;
    $db->StartTrans();
}

/**
 * This function commits a smart transaction
 *
 * If the transaction has failed for any reason, an exception is thrown.
 *
 */
function db_commit() {
    global $db;
    $GLOBALS['_TRANSACTION_LEVEL']--;

    if ($GLOBALS['_TRANSACTION_LEVEL'] == 0) {

        if ($db->HasFailedTrans()) {
            $db->CompleteTrans();
            throw new SQLException('Transaction Failed');
        }
    }

    return $db->CompleteTrans();
}

/**
 * This function rolls back a smart transaction
 */
function db_rollback() {
    global $db;
    $db->FailTrans();
    for ($i = $GLOBALS['_TRANSACTION_LEVEL']; $i >= 0; $i--) {
        $db->CompleteTrans();
    }
    $GLOBALS['_TRANSACTION_LEVEL'] = 0;
}

/**
 * This function escapes a single value suitable for insertion into an SQL
 * string
 *
 * @param string The value to escape
 *
 * @returns string the escaped value
 */
function db_quote($value) {
    global $db;

    return $db->Quote($value);
}

function create_sql_exception_message($e, $sql, $values) {
    $message = 'Failed to get a recordset: ' . $e->getMessage() . "Command was: $sql";
    if (is_array($values) && count($values) > 0) {
        $message .= ' and values was (' . implode(',', $values) . ')';
    }
    return $message;
}

function &increment_perf_db($db, $sql, $inputarray) {
    global $PERF;

    // searching for these rather than just select as subqueries may have select in them.
    if (preg_match('/^(update|insert|delete|alter|create)/i', trim($sql))) {
        $PERF->dbwrites++;
    }
    else {
        $PERF->dbreads++;
    }

    $null = null;
    return $null;
}

function increment_perf_db_cached($db, $secs2cache, $sql, $inputarray) {
    global $PERF;
    $PERF->dbcached++;
}

/**
 * Gives the caller the ability to disable logging of SQL exceptions in the 
 * SQLException constructor.
 *
 * This is only used by the config loading code to prevent spurious errors 
 * about the config table not existing going to the logs. If you are going to 
 * use this function, you had better have a very good reason!
 *
 * @param bool $status Whether to ignore logging exceptions or not. If null, 
 *                     you can retrieve the current value of this setting
 */
function db_ignore_sql_exceptions($status=null) {
    global $DB_IGNORE_SQL_EXCEPTIONS;

    // Initialise it if being called for the first time
    if ($DB_IGNORE_SQL_EXCEPTIONS === null) {
        $DB_IGNORE_SQL_EXCEPTIONS = false;
    }

    // Return the value if asked for
    if ($status === null) {
        return $DB_IGNORE_SQL_EXCEPTIONS;
    }

    $DB_IGNORE_SQL_EXCEPTIONS = (bool)$status;
}

/**
 * Returns the SQL keyword required to do LIKE in a case insensitive fashion.
 *
 * MySQL, as long as you use a case insensitive collation (as is the default), 
 * uses LIKE for this, while real databases use ILIKE.
 */
function db_ilike() {
    if (is_mysql()) {
        return 'LIKE';
    }
    return 'ILIKE';
}

function db_random() {
    if (is_postgres()) {
        return 'RANDOM()';
    }
    else if (is_mysql()) {
        return 'RAND()';
    }
}

/**
 * Search and replace strings in the entire database.
 * Used for xhtml upgrade, but can also be used, for eg, to change links.
 * This function prints every query
 * (Adapted from moodle function of same name)
 *
 * @param array $replacearray keys = search, values = replacements.
 */
function db_replace($replacearray) {

    global $db;

    /// Turn off time limits, sometimes upgrades can be slow.
    @set_time_limit(0);
    @ob_implicit_flush(true);
    while(@ob_end_flush());

    if (!$tables = $db->Metatables() ) {    // No tables yet at all.
        return false;
    }
    foreach ($tables as $table) {

        if (in_array($table, array('config', 'adodb_logsql'))) {      // Don't process these
            continue;
        }

        if ($columns = $db->MetaColumns($table, false)) {
            $db->debug = true;
            foreach ($columns as $column => $data) {
                if (in_array($data->type, array('text','mediumtext','longtext','varchar'))) {  // Text stuff only
                    foreach ($replacearray as $s => $r) {
                        $db->execute('UPDATE ' . db_quote_table_placeholders('{' . $table . '}') . '
                            SET ' . db_quote_identifier($column) . ' = REPLACE(' . db_quote_identifier($column) . ', ?, ?)'
                            , array($s, $r));
                    }
                }
            }
            $db->debug = false;
        }
    }
}
