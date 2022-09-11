<?php

/**
 *
 * @package    mahara
 * @subpackage search-elasticsearch
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
/**
 * Represents one of the "types" that Elasticsearch 7 can search against.
 *
 * These are "types" in the elasticsearch sense:
 * http://www.elasticsearch.org/guide/reference/api/search/indices-types/
 *
 * The currently active types are stored in the "search->elasticsearch->types"
 * config variable.
 *
 * This isn't quite a fully fleshed-out Mahara plugin type, although it is an
 * expandable area. One notable limitation is that under the current
 * implementation, the type name must match up exactly with a Mahara table.
 * Though since all the operations are read-only, you could work around that
 * with a view.
 */
abstract class Elasticsearch7Type {

    /**
     * Should match the name of the class, and the name of a DB table.
     *
     * @var string The name of this search type.
     */
    public static $type = null;

    /**
     * The item we are indexing.
     *
     * @var object
     */
    protected $item_to_index;

    /**
     * How we map data into Elasticsearch.
     *
     * @var array<string,mixed>
     */
    protected $mapping;

    /**
     * Triggers for MySQL.
     *
     * @todo Is this still used?
     * @var array<string>
     */
    private static $mysqltriggeroperations = ['insert', 'update', 'delete'];

    /**
     * The $conditions var was originally used to filter only active,
     * non-deleted records to insert into the queue, but as we will now use it
     * to determine if the record has to be indexed or removed from the index.
     *
     * @var array<string,bool>
     */
    protected $conditions = [];

    /**
     * @var boolean
     */
    protected $isDeleted;

    /**
     * @var bool
     */
    public static $mappingconf = false;

    /**
     * The main facet will not be based on the index type but on a custom
     * grouping.
     *
     * @var bool|string
     */
    public static $mainfacetterm = false;

    /**
     * @var bool|string
     */
    public static $secfacetterm = false;

    /**
     * Constructor for the base class for content types.
     *
     * @param mixed $data
     */
    public function __construct($data) {

        $this->item_to_index = $data;
        $this->setMapping();
        $this->setIsDeleted();

    }

    /**
     * Load values from the Item into the mapping array.
     *
     * The $this->mapping is defined in the _construct() call. This method will
     * take the content of the record (the item_to_index) and load any matching
     * values onto $this->mapping.
     *
     * @return void
     */
    public function setMapping() {
        foreach ($this->mapping as $key => $value) {
            if (is_null($this->item_to_index->{$key})) {
                // If the value in $key is NULL the copy_to errors out.
                // The fix is merged in, but not released yet.
                // https://github.com/elastic/elasticsearch/pull/76665/files
                unset($this->mapping[$key]);
            }
            else {
                $this->mapping[$key] = $this->item_to_index->{$key};
            }
        }
    }

    /**
     * Return the minimal amount of mapping.
     *
     * ES7 dynamically assigns anything not explicitly set so we can just focus
     * on things like dates and keywords.
     *
     * @return array<string,array<string,string>> The map of fields to types.
     */
    public function field_type_map() {
        // Everything has a main and secondary facet term.
        $ret = [
            'mainfacetterm' => ['type' => 'text'],
            'secfacetterm' => ['type' => 'text'],
        ];
        return $ret;
    }

    /**
     * Return the record mapping.
     *
     * @return array<string,mixed> The mapping for the item.
     */
    public function getMapping() {
        return $this->mapping;
    }

    /**
     * Set if the record has to be indexed or removed from the index.
     *
     * @return void
     */
    public function setIsDeleted() {
        if (count($this->conditions) > 0) {
            foreach ($this->conditions as $key => $value) {
                if ($this->item_to_index->{$key} != $value) {
                    $this->isDeleted = true;
                }
            }
        }
    }

    /**
     * Check if the record has to be indexed or removed from the index.
     *
     * @return boolean Return the isDeleted value.
     */
    public function getIsDeleted() {
        return $this->isDeleted;
    }

    /**
     * Get the record from the DB for indexing.
     *
     * @param string $type The type of record we are fetching.
     * @param int $id The ID of the record we are fetching.
     *
     * @return object|bool The Record we will be working with.
     */
    public static function get_record_by_id($type, $id) {
        $record = get_record($type, 'id', "$id");
        if (!$record) {
            return false;
        }

        // Set the main Facet term.
        $record->mainfacetterm = static::$mainfacetterm;

        // We need to set block_instance creation time later (using view
        // ctime).
        if ($type != 'block_instance') {
            $record->ctime = self::checkctime($record->ctime);
        }

        return $record;
    }

    /**
     * Get the info from the DB for display.
     *
     * @param string $type The type of record we are fetching.
     * @param int $id The ID of the record we are fetching.
     *
     * @return object The Record we will be working with.
     */
    public static function get_record_data_by_id($type, $id) {
        return get_record($type, 'id', "$id");
    }

    /**
     * Build the access array.
     *
     * @param array<object> $records An array or record objects.
     *
     * @return array<string,mixed> The access array.
     */
    public static function add_record_access_restrictions($records) {
        // For general: 3 levels public > loggedin > friends
        // This is accesstype in view_access.
        // Objectionable is excluded for now.
        // General will be set to the less restrictive of the 3 options.
        $levels = ['friends', 'loggedin', 'public'];
        $types = ['usr', 'group', 'institution'];
        // Access is by default denied to everyone.
        $access = ['general' => 'none'];

        if (!$records) {
            return $access;
        }

        foreach ($records as $record) {

            if (isset($record->accesstype) and in_array($record->accesstype, $levels)) {
                // We have a desired access level for this record.
                if (array_search($record->accesstype, $levels) >= array_search($access['general'], $levels)) {
                    $access['general'] =  $record->accesstype;
                }
            }
            else if (!isset($record->accesstype)) {
                // If accesstype is null, only 1 of the 3 properties
                // 'institution', 'group', or 'usr' is set.
                foreach ($types as $type) {
                    if (isset($record->$type)) {
                        // If type is group, role can be null (all), admin, or member
                        if ($type == 'group') {
                            $role = isset($record->role) ? $record->role : 'all';
                            $access[$type . 's'][$role][] = $record->$type;
                            if ($role == 'all') {
                                // Add member and admin roles. 'all' does not
                                // seem to find them.
                                $access[$type . 's']['member'][] = $record->$type;
                                $access[$type . 's']['admin'][] = $record->$type;
                            }
                        }
                        else {
                            $access[$type . 's'][] = $record->$type;
                        }
                    }
                }
            }
        }

        if ($access['general'] == 'loggedin' ||  $access['general'] == 'public') {
            $access = array('general' => $access['general']);
        }

        return $access;
    }

    /**
     * Check that the date format.
     *
     * Ensure the date format is Y-m-d H:i:s.  Some dates have the following
     * format 2011-07-29 16:13:56.017725
     *
     * @param string $ctime
     *
     * @return string The correctly formatted time.
     */
    public static function checkctime($ctime) {
        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) ([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $ctime)) {
            $ctime = date('Y-m-d H:i:s', strtotime($ctime));
        }
        return $ctime;
    }

    /**
     * @param object $record The record we are processing.
     * @param string $name The name we are checking.
     *
     * @return void
     */
    public static function add_index_source_type_for_record($record, $name) {
        $key = 'Elasticsearch7Type_';
        $offset = strlen($key);
        if (strpos($name, $key) === 0) {
            // We were passed the class name.
            $type = substr($name, $offset);
            $record->indexsourcetype = $type;
        }
        else {
            // It is not a class name. Assume the value is what we want set.
            $record->indexsourcetype = $name;
        }
    }

    /**
     * Add tags to the record.
     *
     * @param object $record The record we are processing.
     * @param string $resourcetype The resource type we are looking for.
     *
     * @return void
     */
    public static function add_tags_for_record($record, $resourcetype) {
        $tags = get_column ( 'tag', 'tag', 'resourcetype', $resourcetype, 'resourceid', $record->id );
        if ( $tags != false ) {
            foreach ( $tags as $tag ) {
                $record->tags[] = $tag;
            }
        }
        else {
            $record->tags = null;
        }
    }

    /**
     * Add the content to sort on to the record.
     *
     * By default this is the title parameter.
     *
     * @param object $record The record being processed.
     * @param string $field  The field we are processing.
     *
     * @return void
     */
    public static function add_sort_for_record($record, $field = 'title') {
        $record->sort = strtolower(strip_tags($record->{$field}));
    }

    /**
     * Add site admins to the access control records.
     *
     * @param int $view_id
     *
     * @return array<int,object> An array of access record objects.
     */
    public static function add_admins_to_view_access_record($view_id) {
        $records = [];
        // Allow site admins to be able to see profile pages of all users.
        $site_admin_ids = get_column('usr', 'id', 'admin', "1");
        foreach ($site_admin_ids as $admin_id) {
            $records[] = self::build_view_access_record([
                'view_id' => $view_id,
                'usr' => $admin_id,
            ]);
        }
        return $records;
    }

    /**
     * Build an access record from a minimal array.
     *
     * @param array<string,int|string> $params An array of key/val pairs for the access record.
     *
     * @return object A single access record.
     */
    public static function build_view_access_record($params) {
        $access_record = new StdClass();
        $access_record->view_id = null;
        $access_record->accesstype = null;
        $access_record->group = null;
        $access_record->role = null;
        $access_record->usr = null;
        $access_record->institution = null;

        foreach ($params as $key => $val) {
            if (!empty($access_record->{$key})) {
                $access_record->{$key} = $val;
            }
        }

        return $access_record;
    }

    /**
     * Common SQL for the delete statement.
     *
     * @see Elasticsearch7Indexing::requeue_searchtype_contents()
     * @param string $type The type/table we are working with.
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return string The SQL to build the delete statement from.
     */
    public static function searchtype_contents_delete_sql($type, $ids = []) {
        $sql = "DELETE FROM {search_elasticsearch_7_queue} WHERE type = '{$type}'";
        $sql .= self::add_requeue_where($ids, 'itemid');
        return $sql;
    }

    /**
     * Common SQL for the insert statement.
     *
     * Starting the build of the SQL that will populate the queue table.
     *
     * @see Elasticsearch7Indexing::requeue_searchtype_contents()
     * @param string $type The type/table we are working with.
     * @param string $additional_filter Any extra filter on the source records.
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return string The SQL to build the insert statement from.
     */
    public static function searchtype_contents_insert_sql($type, $additional_filter = '', $ids = []) {
        $sql = "INSERT INTO {search_elasticsearch_7_queue} (itemid, type) " .
            "SELECT id, '" . $type . "' FROM {" . $type . "}";
        // Were we passed an additional filter?
        if (!empty($additional_filter)) {
            $sql .= " WHERE ${additional_filter}";
        }
        // Are we restricting this to a subset of IDs?
        $sql .= self::add_requeue_where($ids, 'id', empty($additional_filter));

        return $sql;
    }

    /**
     * Requeue content by type for indexing.
     *
     * Clears the indexing queue table for this type and reloads all
     * records for indexing.
     *
     * @param string $type The type of item we are requeuing.
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return void
     */
    public static function searchtype_contents_requeue_all($type, $ids = []) {
        $delete_sql = self::searchtype_contents_delete_sql($type, $ids);
        $insert_sql = self::searchtype_contents_insert_sql($type, '', $ids);
        execute_sql($delete_sql);
        execute_sql($insert_sql);
    }

    /**
     * Map fields that need actions taken on them.
     *
     * Currently we list fields that are copied to the 'catch_all' field.
     *
     * @return array<string,array<string,string>> The property mapping.
     */
    public static function get_mapping_properties() {
        log_debug('@TODO: ' . __CLASS__ . '::' . __FUNCTION__);
        return [];
    }

    /**
     * Add a WHERE clause to filter on the provided $ids.
     *
     * @param array<int> $ids The list of IDs, or an empty array.
     * @param string $filter_key This is id by default, but delete will use itemid
     * @param bool $add_where Should we return the WHERE part?
     *
     * @return string The filter for IDs
     */
    public static function add_requeue_where($ids, $filter_key = 'id', $add_where = false) {
        $where = '';
        if (!empty($ids)) {
            // Reverse the sort order of $ids so the most recent items are
            // indexed first.
            rsort($ids);
            // Are we adding to an existing WHERE or starting one?
            if ($add_where) {
                $where = ' WHERE ';
            }
            else {
                $where = ' AND ';
            }
            $where .= $filter_key . ' IN (' . implode(',', $ids) . ')';
        }
        return $where;
    }

}
