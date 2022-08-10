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

class Elasticsearch7Type_interaction_instance extends Elasticsearch7Type {

    public static $mainfacetterm = 'Text';
    public static $secfacetterm = 'Forum';

    public function __construct($data) {
        $this->conditions = ['deleted' => false];

        $this->mapping = array(
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'title' => NULL,
            'description' => NULL,
            'access' => NULL,
            'ctime' => NULL,
            'sort' => NULL
        );

        parent::__construct($data);
    }

    /**
     * Fetch a record for an Interaction Instance.
     *
     * These are top level forum posts
     *
     * @param string $type
     * @param int $id
     * @param array<string,array<string,string>>|null $map
     *
     * @return bool|object The record or false if not found.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $record = parent::get_record_by_id($type, $id);
        if (!$record || $record->deleted) {
            return false;
        }

        // Set the Main Facet term.
        $record->mainfacetterm = self::$mainfacetterm;

        // Add the secondary facet term.
        $record->secfacetterm = self::$secfacetterm;

        // Add index source to the record.
        self::add_index_source_type_for_record($record, __CLASS__);

        // Add access info.
        self::add_access_for_record($record);

        // Add sort info.
        self::add_sort_for_record($record, 'title');

        return $record;
    }

    /**
     * Add Access check info to the Record.
     *
     * @param object $record The Record we are checking access for.
     *
     * @return void
     */
    public static function add_access_for_record($record) {
        $public = get_field('group', 'public', 'id', $record->group);
        $record->access['general'] = (!empty($public)) ? 'public' : 'none';
        $record->access['groups']['member'] = $record->group;
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
        if (!$record || $record->deleted) {
            return false;
        }
        return $record;
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
        $type = 'interaction_instance';
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
            'title' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'description' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
        ];
    }

}
