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

require_once(dirname(__FILE__) . '/Elasticsearch7Type.php');

class Elasticsearch7Type_collection extends Elasticsearch7Type {
    public static $mainfacetterm = 'Portfolio';
    public static $secfacetterm = 'Collection';

    public function __construct($data) {

        $this->mapping = array (
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'name' => NULL,
            'description' => NULL,
            'tags' => NULL,
            'owner' => NULL,
            'group' => NULL,
            'institution' => NULL,
            'access' => NULL,
            'ctime' => NULL,
            'sort' => NULL,
        );

        parent::__construct($data);
    }

    /**
     * Return a fully populated record.
     *
     * @param string $type
     * @param int $id
     * @param array<string,array<string,string>>|null $map
     *
     * @return bool|object The record or false if not found.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $record = parent::get_record_by_id($type, $id);
        if (! $record) {
            return false;
        }

        // Set the Main Facet term.
        $record->mainfacetterm = self::$mainfacetterm;

        // Add the secondary facet term.
        $record->secfacetterm = self::$secfacetterm;

        // Add index source to the record.
        self::add_index_source_type_for_record($record, __CLASS__);

        // Add tags.
        self::add_tags_for_record($record, $type);

        // Add view_access info.
        self::add_access_for_record($record);

        // Add sort info.
        self::add_sort_for_record($record, 'name');

        return $record;
    }

    /**
     * Return the data for a single record of the specified type.
     *
     * @param string $type The type of record.
     * @param int $id      The id of the record.
     *
     * @return object|bool The record, or false if not found.
     */
    public static function get_record_data_by_id($type, $id) {
        $record = parent::get_record_data_by_id($type, $id);
        if (!$record) {
            return false;
        }

        // Add viewid of first view in collection.
        self::add_first_view_id_in_collection($record);

        // Add owner info.
        self::add_owner_for_record($record);

        // Add views in this collection.
        self::add_views_for_record($record);

        return $record;
    }

    /**
     * Add Views in this Collection to the record.
     *
     * @param object $record
     */
    public static function add_views_for_record($record) {
        $sql = 'SELECT v.id, v.title
            FROM {view} v
            LEFT OUTER JOIN {collection_view} cv ON cv.view = v.id
            WHERE cv.collection = ?
            AND v.type != ?';

        $views = recordset_to_array(get_recordset_sql(
            $sql,
            [$record->id, 'progress']
        ));
        if ($views) {
            $record_views = array ();
            foreach ($views as $view) {
                if (isset($view->id)) {
                    $record_views[$view->id] = $view->title;
                }
            }
            $record->views = $record_views;
        }
    }

    /**
     * Add owner info to the Record.
     *
     * @param object $record The Record we are checking.
     */
    private static function add_owner_for_record($record) {
        if (intval($record->owner) > 0) {
            $record->createdby = get_record('usr', 'id', $record->owner);
            $record->createdbyname = display_name($record->createdby);
        }
    }

    /**
     * Return view id of first view in collection.
     *
     * @param object $record The record we are working with.
     */
    public static function add_first_view_id_in_collection($record) {
        $viewid = get_field_sql('
            SELECT v.id
            FROM {view} v
            JOIN {collection_view} cv ON v.id = cv.view
            WHERE cv.collection = ?
            ORDER BY cv.displayorder ASC
            LIMIT 1',
            array($record->id)
        );
        $record->viewid = $viewid;
    }

    /**
     * Add Access check info to the Record.
     *
     * @param object $record The Record we are checking access for.
     *
     * @return void
     */
    private static function add_access_for_record($record) {
        $access = self::get_collection_access_records($record->id);
        // @todo is this not needed here?
        $access_restrictions = self::add_record_access_restrictions($access);
        $record->access = $access_restrictions;
    }

    /**
     * Get all view access records relevant to the collection.
     *
     * @param int $id The ID of the record we are working with.
     *
     * @return array<object>
     */
    public static function get_collection_access_records($id) {
        $sql = '
            SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
            FROM {view_access} vac
            INNER JOIN {collection_view} vcol
                ON vac.view = vcol.view
            INNER JOIN {view} v
                ON v.id = vcol.view
            WHERE vcol.collection = ?
            AND v.type != ?
            AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
            AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
        ';

        return get_records_sql_array(
            $sql,
            [$id, 'progress']
        );
    }

    /**
     * Requeue content for indexing.
     *
     * Clears the indexing queue table for this type and reloads all usr
     * records for indexing.
     *
     * @todo requeue only $ids
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return void
     */
    public static function requeue_searchtype_contents($ids = []) {
        $type = 'collection';
        parent::searchtype_contents_requeue_all($type, $ids);
    }

    /**
     * Map fields that need actions taken on them.
     *
     * Currently we list fields that are copied to the 'catch_all' field.
     *
     * @return array<string,array<string,string>> The property mapping.
     */
    public static function get_mapping_properties() {
        return [
            'name' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'description' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'tags' => [
                'type' => 'text',
            ],
        ];
    }

}
