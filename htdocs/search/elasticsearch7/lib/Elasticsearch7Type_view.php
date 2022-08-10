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

class Elasticsearch7Type_view extends Elasticsearch7Type {

    public static $mainfacetterm = 'Portfolio';
    public static $secfacetterm = 'Page';

    public function __construct($data) {

        // The field mapping for this content type.
        $this->mapping = array (
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'title' => NULL,
            'description' => NULL,
            'tags' => NULL,
            'owner' => NULL,
            'group' => NULL,
            'institution' => NULL,
            'access' => NULL,
            'ctime' => NULL,
            'sort' => NULL
        );

        parent::__construct ( $data );
    }

    /**
     * Fetch a record for a View.
     *
     * @param string $type
     * @param int $id
     * @param array<string,array<string,string>>|null $map
     *
     * @return bool|object The record or false if not found.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $record = parent::get_record_by_id($type, $id);
        if (!$record) {
            return false;
        }

        if ($record->type == 'progress') {
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
        self::add_sort_for_record($record);

        return $record;
    }

    /**
     * Add Access check info to the Record.
     *
     * @param object $record The Record we are checking access for.
     *
     * @return void
     */
    private static function add_access_for_record($record) {
        $access = self::get_view_access_records($record->id);
        $access_restrictions = self::add_record_access_restrictions($access);
        $record->access = $access_restrictions;
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

        // Set Created by.
        if (intval($record->owner) > 0) {
            $record->createdby = get_record('usr', 'id', $record->owner);
            $record->createdbyname = display_name($record->createdby);
        }
        // Add Tags.
        $tags = get_column('tag', 'tag', 'resourcetype', 'view', 'resourceid', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags [] = $tag;
            }
        }
        else {
            $record->tags = null;
        }
        return $record;
    }

    /**
     * Get all access records relevant at the view being indexed.
     *
     * @param int $viewid the ID of the View we are working with.
     *
     * @return array<object> An array of access records.
     */
    public static function get_view_access_records($viewid) {
        // Fetch all view access records for this view.
        $sql = '
            SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
            FROM {view_access} vac
            JOIN {view} v
                ON v.id = vac.view
            WHERE vac.view = ?
                AND v.type != ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
        ';
        $records = get_records_sql_array(
            $sql,
            [$viewid, 'progress']
        );

        // Add records if we are an isolated institution and our view type is a
        // profile.
        $type_check = get_field('view', 'type', 'id', $viewid);
        if (is_isolated() && $type_check == 'profile') {
            if ($records) {
                foreach ($records as $k => $access) {
                    if ($access->accesstype == 'loggedin') {
                        unset($records[$k]);
                    }
                }
                $records = array_values($records);
            }

            $sql = '
                SELECT v.owner
                FROM {view} v
                JOIN {usr_institution} ui
                    ON ui.usr = v.owner
                WHERE v.id = ?
            ';
            $institution_check = get_records_sql_array($sql, [$viewid]);
            if (!$institution_check) {
                // The Account of the View owner is not a member any
                // institution. We need to add the 'mahara' institution option.
                $records[] = self::build_view_access_record([
                    'view_id' => $viewid,
                    'institution' => 'mahara',
                ]);
            }

            // Add admins.
            $records += self::add_admins_to_view_access_record($viewid);
        }

        return $records;
    }

    /**
     * Requeue content for indexing.
     *
     * Clears the indexing queue table for this type and reloads all usr
     * records for indexing.
     *
     * Note: We do not use the parent::searchtype_contents_requeue_all() here
     * to avoid indexing views owned by usr with id = 0
     *
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return void
     */
    public static function requeue_searchtype_contents($ids = []) {
        $type = 'view';
        $delete_sql = parent::searchtype_contents_delete_sql($type, $ids);
        $insert_sql = parent::searchtype_contents_insert_sql(
            $type,
            '(id != 0 AND (owner != 0 OR "group" !=0))',
            $ids
        );
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
        return [
            'title' => [
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
            'group' => [
                'type' => 'text',
            ],
            'institution' => [
                'type' => 'text',
            ],
        ];
    }
}
