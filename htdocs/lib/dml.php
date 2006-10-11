<?php 
/**
 * This program is part of moodle
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
 * @subpackage core or plugintype/pluginname
 * @author     Your Name <you@example.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2001-3001 Martin Dougiamas http://dougiamas.com
 * @copyright  additional modifications (c) Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Execute a given sql command string
 *
 * Completely general function - it just runs some SQL and reports success.
 *
 * @uses $db
 * @param string $command The sql string you wish to be executed.
 * @return string
 * @throws DatalibException
 */
function execute_sql($command) {
    global $db;
    
    if (!is_a($db, 'ADOConnection')) {
        throw new DatalibException('Database connection is not available ');
    }

    // @todo need to do more research into this flag - what is it for, we
    // probably want to just turn it off because we can catch the exceptions
    $olddebug = $db->debug;
    $db->debug = false;

    try {
        $result = $db->Execute($command);
    }
    catch (ADODB_Exception $e) {
        log_dbg($e->getMessage() . "Command was: $command");
        $db->debug = $olddebug;
        throw new DatalibException('Could not execute command: ' . $command);
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
 * @throws DatalibException
 */
function record_exists($table, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    return record_exists_sql('SELECT * FROM ' . get_config('dbprefix') . $table .' '. $select, $values);
}

/**
 * Test whether a SQL SELECT statement returns any records.
 *
 * This function returns true if at least one record is returned.
 *
 * @param string $sql The SQL statement to be executed. If using $values, placeholder ?s are expected. If not, the string should be escaped correctly.
 * @param array $values When using prepared statements, this is the value array. Optional.
 * @return bool true if the SQL executes without errors and returns at least one record.
 * @throws DatalibException
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
 * @throws DatalibException
 */
function count_records($table, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    return count_records_sql('SELECT COUNT(*) FROM '. get_config('dbprefix') . $table . ' ' . $select, $values);
}

/**
 * Count the records in a table which match a particular WHERE clause.
 *
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
 * @param array $values if using a prepared statement with placeholders in $select, pass values here. optional
 * @param string $countitem The count string to be used in the SQL call. Default is COUNT(*).
 * @return int The count of records returned from the specified criteria.
 * @throws DatalibException
 */
// NOTE: commented out until required
/*
function count_records_select($table, $select='', $values=null, $countitem='COUNT(*)') {
    if ($select) {
        $select = 'WHERE ' . $select;
    }
    return count_records_sql('SELECT '. $countitem .' FROM '. get_config('dbprefix') . $table . ' ' . $select, $values);
}
*/

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
 * @return int        The count.
 * @throws DatalibException
 */
// NOTE: commented out until required
/*
function count_records_sql($sql, $values=null) {
    $rs = get_recordset_sql($sql, $values);
    return reset($rs->fields);
}
*/

/// GENERIC FUNCTIONS TO GET, INSERT, OR UPDATE DATA  ///////////////////////////////////

/**
 * Get a single record as an object
 *
 * @param string $table The table to select from.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return mixed a fieldset object containing the first mathcing record, or false if none found.
 * @throws DatalibException
 */
function get_record($table, $field1, $value1, $field2=null, $value2=null, $field3=null, $value3=null, $fields='*') {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    return get_record_sql('SELECT ' . $fields . ' FROM ' . get_config('dbprefix') . $table . ' ' . $select, $values);
}

/**
 * Get a single record as an object using an SQL statement
 *
 * This function is designed to retrieve ONE record. If your query returns more than one record,
 * an exception is thrown. If you want more than one record, use get_records_sql.
 *
 * @param string $sql The SQL string you wish to be executed, should normally only return one record.
 * @param bool $expectmultiple If the SQL cannot be written to conviniently return just one record,
 *      set this to true to hide the debug message.
 * @param bool $nolimit sometimes appending ' LIMIT 1' to the SQL causes an error. Set this to true
 *      to stop your SQL being modified. This argument should probably be deprecated.
 * @return Found record as object. False if not found
 * @throws DatalibException
 */
function get_record_sql($sql, $values=null) {
    $limitfrom = 0;
    $limitnum  = 2;

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
        throw new DatalibException('get_record_sql found more than one row. If you meant to retrieve more '
            . 'than one record, use get_records_*, otherwise check your code or database for inconsistencies');
    }
}

/**
 * Gets one record from a table, as an object
 *
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values If using placeholder ? in $select, pass values here.
 * @param string $fields A comma separated list of fields to be returned from the chosen table.
 * @return object Returns an array of found records (as objects)
 * @throws DatalibException
 */
function get_record_select($table, $select='', $values=null, $fields='*') {
    if ($select) {
        $select = 'WHERE '. $select;
    }
    return get_record_sql('SELECT '. $fields .' FROM '. get_config('dbprefix') . $table .' '. $select, $values);
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
 * from general Moodle scripts.  Use get_record, get_records etc.
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
 * @param string $value the value the field must have (requred if field1 is given, else optional).
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an ADODB RecordSet object.
 * @throws DatalibException
 */
function get_recordset($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $values = null;
    if ($field) {
        $select = "$field = ?";
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
 * @param array $values If using placeholder ?s in $select, pass values here.
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an ADODB RecordSet object
 * @throws DatalibException
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

    return get_recordset_sql('SELECT '. $fields .' FROM '. get_config('dbprefix') . $table . $select . $sort .' '. $limit, $values);
}

/**
 * Get a number of records as an ADODB RecordSet.  $sql must be a complete SQL query.
 * This function is internal to datalib, and should NEVER should be called directly
 * from general Moodle scripts.  Use get_record, get_records etc.
 *
 * The return type is as for @see function get_recordset.
 *
 * @uses $db
 * @param string $sql the SQL select query to execute.
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an ADODB RecordSet object
 * @throws DatalibException
 */
function get_recordset_sql($sql, $values=null, $limitfrom=null, $limitnum=null) {
    global $db;

    if (!is_a($db, 'ADOConnection')) {
        throw new DatalibException('Database connection is not available ');
    }

    try {
        if ($limitfrom || $limitnum) {
            ///Special case, 0 must be -1 for ADOdb
            $limitfrom = empty($limitfrom) ? -1 : $limitfrom;
            $limitnum  = empty($limitnum) ? -1 : $limitnum;
            $rs = $db->SelectLimit($sql, $limitnum, $limitfrom,$values);
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
        $message = 'Failed to get a recordset: ' . $e->getMessage() . "Command was: $sql";
        log_dbg($message);
        throw new DatalibException($message);
    }
 
   return $rs;
}

/**
 * Utility function used by the following 4 methods.
 *
 * @param object an ADODB RecordSet object.
 * @return mixed mixed an array of objects, or false if the RecordSet was empty.
 * @throws DatalibException
 */
function recordset_to_array($rs) {
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
 * @throws DatalibException
 */
function get_records($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $rs = get_recordset($table, $field, $value, $sort, $fields, $limitfrom, $limitnum);
    return recordset_to_array($rs);
}

/**
 * Get a number of records as an array of objects.
 *
 * Return value as for @see function get_records.
 *
 * @param string $table the table to query.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param array $values if using placeholder ? in $select, pass values here.
 * @param string $sort an order to sort the results in (optional, a valid SQL ORDER BY parameter).
 * @param string $fields a comma separated list of fields to return (optional, by default all fields are returned).
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws DatalibException
 */
function get_records_select($table, $select='', $values=null, $sort='', $fields='*', $limitfrom='', $limitnum='') {
    $rs = get_recordset_select($table, $select, $values, $sort, $fields, $limitfrom, $limitnum);
    return recordset_to_array($rs);
}

/**
 * Get a number of records as an array of objects.
 *
 * Return value as for @see function get_records.
 *
 * @param string $sql the SQL select query to execute.
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return mixed an array of objects, or false if no records were found.
 * @throws DatalibException
 */
function get_records_sql($sql,$values, $limitfrom='', $limitnum='') {
    $rs = get_recordset_sql($sql, $values, $limitfrom, $limitnum);
    return recordset_to_array($rs);
}

/**
 * Utility function used by the following 3 methods.
 *
 * @param object an ADODB RecordSet object with two columns.
 * @return mixed an associative array, or false if an error occured or the RecordSet was empty.
 */
// NOTE: commented out until  a reason can be found for them
/*
function recordset_to_menu($rs) {
    global $CFG;

    if ($rs && $rs->RecordCount() > 0) {
        $keys = array_keys($rs->fields);
        $key0=$keys[0];
        $key1=$keys[1];
        while (!$rs->EOF) {
            $menu[$rs->fields[$key0]] = $rs->fields[$key1];
            $rs->MoveNext();
        }
        return $menu;
    } else {
        return false;
    }
}
 */
/**
 * Get the first two columns from a number of records as an associative array.
 *
 * Arguments as for @see function get_recordset.
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
/*
function get_records_menu($table, $field='', $value='', $sort='', $fields='*') {
    $rs = get_recordset($table, $field, $value, $sort, $fields);
    return recordset_to_menu($rs);
}
*/

/**
 * Get the first two columns from a number of records as an associative array.
 *
 * Arguments as for @see function get_recordset_select.
 * Return value as for @see function get_records_menu.
 *
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @param string $sort Sort order (optional) - a valid SQL order parameter
 * @param string $fields A comma separated list of fields to be returned from the chosen table.
 * @return mixed an associative array, or false if no records were found or an error occured.
 */
/*
function get_records_select_menu($table, $select='', $values=null, $sort='', $fields='*') {
    $rs = get_recordset_select($table, $select, $values, $sort, $fields);
    return recordset_to_menu($rs);
}
*/

/**
 * Get the first two columns from a number of records as an associative array.
 *
 * Arguments as for @see function get_recordset_sql.
 * Return value as for @see function get_records_menu.
 *
 * @param string $sql The SQL string you wish to be executed.
 * @return mixed an associative array, or false if no records were found or an error occured.
 */
/*
function get_records_sql_menu($sql,$values=null) {
    $rs = get_recordset_sql($sql,$values);
    return recordset_to_menu($rs);
}
*/

/**
 * Get a single value from a table row where all the given fields match the given values.
 *
 * @param string $table the table to query.
 * @param string $return the field to return the value of.
 * @param string $field1 the first field to check (optional).
 * @param string $value1 the value field1 must have (requred if field1 is given, else optional).
 * @param string $field2 the second field to check (optional).
 * @param string $value2 the value field2 must have (requred if field2 is given, else optional).
 * @param string $field3 the third field to check (optional).
 * @param string $value3 the value field3 must have (requred if field3 is given, else optional).
 * @return mixed the specified value
 * @throws DatalibException
 */
function get_field($table, $field, $field1, $value1, $field2=null, $value2=null, $field3=null, $value3=null) {
    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);
    
    $rs = get_recordset_sql($sql, $values);
    if ($rs->RecordCount() == 1) {
        return reset($rs->fields);
    } else {
        throw new DatalibException('Got more than one row when getting a field value');
    }
    //return get_field_sql('SELECT ' . $field . ' FROM ' . get_config('dbprefix') . $table . ' ' . $select, $values);
}

/**
 * Get a single value from a table row where a particular select clause is true.
 *
 * @param string $table the table to query.
 * @param string $return the field to return the value of.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call.
 * @return mixed the specified value.
 * @throws DatalibException
 */
// NOTE: Commented out until an overwhelming reason is found to uncomment them
/*
function get_field_select($table, $return, $select, $values=null) {
    if ($select) {
        $select = 'WHERE '. $select;
    }
    return get_field_sql('SELECT ' . $return . ' FROM ' . get_config('dbprefix') . $table . ' ' . $select, $values);
}
 */
/**
 * Get a single value from a table.
 *
 * @param string $sql an SQL statement expected to return a single value.
 * @return mixed the specified value.
 * @throws DatalibException
 */
// NOTE: Commented out until an overwhelming reason is found to uncomment them
/*
function get_field_sql($sql, $values=null) {
    $rs = get_recordset_sql($sql, $values);
    if ($rs && $rs->RecordCount() == 1) {
        return reset($rs->fields);
    } else {
        return false;
    }
}
*/

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
 * @throws DatalibException
 */
function set_field($table, $newfield, $newvalue, $field1, $value1, $field2=null, $value2=null, $field3=null, $value3=null) {
    global $db;

    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($newfield, $value1, $value2, $value3);

    try {
        $stmt = $db->Prepare('UPDATE '. get_config('dbprefix') . $table .' SET '. $newfield  .' = ? ' . $select);
        return $db->Execute($stmt, $values);
    }
    catch (ADODB_Exception $e) {
        throw new DatalibException($e->getMessage());
    }
}

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
 * @throws DatalibException
 */
// NOTE: UNTESTED with new exception stuff. Needs a database first... :)
function delete_records($table, $field1=null, $value1=null, $field2=null, $value2=null, $field3=null, $value3=null) {
    global $db;

    $select = where_clause_prepared($field1, $field2, $field3);
    $values = where_values_prepared($value1, $value2, $value3);

    try {
        $stmt = $db->Prepare('DELETE FROM '. get_config('dbprefix') . $table . ' ' . $select);
        return $db->Execute($stmt,$values);
    }
    catch (ADODB_Exception $e) {
        throw new DatalibException($e->getMessage());
    }
}

/**
 * Delete one or more records from a table
 *
 * @uses $db
 * @param string $table The database table to be checked against.
 * @param string $select A fragment of SQL to be used in a where clause in the SQL call (used to define the selection criteria).
 * @return object A PHP standard object with the results from the SQL call.
 * @throws DatalibException
 */
// NOTE: UNTESTED with new exception stuff. Needs a database first... :)
function delete_records_select($table, $select='',$values=null) {
    global $db;
    if ($select) {
        $select = 'WHERE '.$select;
    }

    try {
        $result = false;
        if (!empty($values) && is_array($values) && count($values) > 0) {
            $stmt = $db->Prepare('DELETE FROM '. get_config('dbprefix') . $table .' '. $select);
            $result = $db->Execute($stmt,$values);
        } else {
            $result = $db->Execute('DELETE FROM '. get_config('dbprefix') . $table .' '. $select);
        }
    }
    catch (ADODB_Exception $e) {
        throw new DatalibException($e->getMessage());
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
 * @param bool $returnpk Should the id of the newly created record entry be returned? If this option is not requested then true/false is returned.
 * @param string $primarykey The primary key of the table we are inserting into (almost always "id")
 * @throws DatalibException
 */
// NOTE: UNTESTED with new exception stuff. Needs a database first... :)
function insert_record($table, $dataobject, $primarykey=false, $returnpk=false) {
    global $db;
    static $table_columns;
    
    // Determine all the fields in the table
    if (is_array($table_columns) && array_key_exists($table, $table_columns)) {
        $columns = $table_columns[$table];
    } else {
        if (!$columns = $db->MetaColumns(get_config('dbprefix') . $table)) {
            return false;
        }
        $table_columns[$table] = $columns;
    }
    
    if (!empty($primarykey)) {
        unset($dataobject->{$primarykey});
        if (!empty($returnpk)) {
            $pksql = "SELECT NEXTVAL('" . get_config('dbprefix') . "{$table}_{$primarykey}_seq')";
            if ($nextval = (int)get_field_sql($pksql)) {
                $setfromseq = true;
                $dataobject->{$primarykey} = $nextval;
            }
        }
    }

    $data = (array)$dataobject;

  // Pull out data matching these fields
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
    $insertSQL = 'INSERT INTO '. get_config('dbprefix') . $table .' (';
    $fields = '';
    $values = '';
    foreach ($ddd as $key => $value) {
        $count++;
        $fields .= $key;
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
        throw new DatalibException($e->getMessage());
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
            $oidsql = 'SELECT '. $primarykey .' FROM '. get_config('dbprefix') . $table .' WHERE oid = '. $id;
            $rs = $db->Execute($oidsql);
            if ($rs->RecordCount() == 1) {
                return (integer)$rs->fields[0];
            }
            throw new DatalibException('WTF: somehow got more than one record when searching for a primary key');
        }
        catch (ADODB_Exception $e) {
            throw new DatalibException($e->getMessage());
        }
    }

    return (integer)$id;
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
 * @return bool
 * @throws DatalibException
 */
// NOTE: UNTESTED with new exception throwing stuff. need a database first... :)
function update_record($table, $dataobject, $where=null) {

    global $db;

    if (empty($where) && !isset($dataobject->id) ) { 
        // nothing to put in the where clause and we don't want to update everything
        throw new DatalibException('reeeeowwww! hhhssssssss');
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
        // @todo throw hissy fit
        foreach ($where as $field) {
            if (!isset($dataobject[$field])) {
                throw new DatalibException('Field in where clause not in the update object');
            }
            $wherefields[] = $field;
            $wherevalues[] = $dataobject->{$field};
            unset($dataobject->{$field});
        }
    } else {
        throw new DatalibException('the $where object is in a very odd form');
    }

    static $table_columns;
    
    // Determine all the fields in the table
    if (is_array($table_columns) && isset($table_columns[$table])) {
        $columns = $table_columns[$table];
    } else {
        if (!$columns = $db->MetaColumns(get_config('dbprefix') . $table)) {
            throw new DatalibException('Could not get columns for table ' . $table);
        }
        $table_columns[$table] = $columns;
    }

    $data = (array)$dataobject;

    // Pull out data matching these fields
    foreach ($columns as $column) {
        if (!in_array($column->name,$wherefields) && isset($data[$column->name]) ) {
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
        $update .= $key .' = ? ';
        if ($count < $numddd) {
            $update .= ', ';
        }
        $values[] = $value;
    }

    $whereclause = '';
    $count = 0;
    $numdd = count($wherefields);

    foreach ($wherefields as $field) {
        $count++;
        $whereclause .= $field .' = ? ';
        if ($count < $numddd) {
            $whereclause .= ', ';
        }
    }

    try { 
        $stmt = $db->Prepare('UPDATE '. get_config('dbprefix') . $table .' SET '. $update .' WHERE ' . $whereclause);
        $rs = $db->Execute($stmt,array_merge($values, $wherevalues));
        return true;
    }
    catch (ADODB_Exception $e) {
        throw new DatalibException($e->getMessage());
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
        $select = "WHERE $field1 = '$value1'";
        if ($field2) {
            $select .= " AND $field2 = '$value2'";
            if ($field3) {
                $select .= " AND $field3 = '$value3'";
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
 * @todo nigel test with phpdoc - can these functions be marked as private? Does
 * phpdoc do the right thing?
 */
function where_clause_prepared($field1='', $field2='', $field3='') {
    $select = '';
    if (!empty($field1)) {
        $select = " WHERE $field1 = ? ";
        if (!empty($field2)) {
            $select .= " AND $field2 = ? ";
            if (!empty($field3)) {
                $select .= " AND $field3 = ? ";
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
function where_values_prepared($value1=null, $value2=null, $value3=null) {
    $values = array();
    if (isset($value1)) {
        $values[] = $value1;
        if (isset($value2)) {
            $values[] = $value2;
            if (isset($value3)) {
                $values[] = $value3;
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
// NOTE: commented out until a good reason is found for it. The implemenation looks shoddy at best anyway...
/*
function column_type($table, $column) {
    global $db;

    if(!$rs = $db->Execute('SELECT ' . $column.' FROM ' . get_config('dbprefix') . $table . ' WHERE 1=2')) {
        return false;
    }

    $field = $rs->FetchField(0);
    return $rs->MetaType($field->type);
}
*/

/**
 * This function will execute an array of SQL commands, returning
 * true/false if any error is found and stopping/continue as desired.
 * It's widely used by all the ddllib.php functions
 *
 * @param array sqlarr array of sql statements to execute
 * @param boolean continue to specify if must continue on error (true) or stop (false)
 * @param boolean feedback to specify to show status info (true) or not (false)
 * @param boolean true if everything was ok, false if some error was found
 */
// NOTE: if this is used by ddllib then it should be in there, NOT in here. If the first
// person who discovers this comment can verify that ddllib still needs it, then they
// can move it.
/*
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
 */

/**
 * This function, called from setup.php includes all the configuration
 * needed to properly work agains any DB. It setups connection encoding
 * and some other variables.
 */
function configure_dbconnection() {
    global $db;

    $db->Execute("SET NAMES 'utf8'");

    // more later..
}

function  is_postgres() {
    return (strpos(get_config('dbtype'), 'postgres' === 0));
}

?>
