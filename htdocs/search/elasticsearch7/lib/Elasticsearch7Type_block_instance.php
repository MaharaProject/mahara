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

class Elasticsearch7Type_block_instance extends Elasticsearch7Type {

    public static $mainfacetterm = 'Text';
    public static $secfacetterm = 'Document';

    public function __construct($data) {

        $this->mapping = array (
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'title' => NULL,
            'description' => NULL,
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
     * Return a fully described Artifact record.
     *
     * {@inheritDoc}
     *
     * @param string $type The type of this record.
     * @param int $id The ID of the record we are processing.
     * @param array<string,string>|null $map The Artefact Type map.
     *
     * @return object|false The fully defined record to index.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $record = parent::get_record_by_id($type, $id);
        if (!$record) {
            return false;
        }

        // Set the Main Facet term.
        $record->mainfacetterm = self::$mainfacetterm;

        // Add the secondary facet term.
        $record->secfacetterm = self::$secfacetterm;

        // Add index source to the record.
        self::add_index_source_type_for_record($record, __CLASS__);

        $data = self::get_record_data_by_id($type, $id);
        if (!$data) {
            // This block instance is not on an View.
            return false;
        }

        // Block instances do not have certain fields. We get these values from
        // the view they are on or from their config data.
        $record->ctime = parent::checkctime($data->ctime);
        // Not all block instances have description (currently it is the
        // config['text'] when available.)
        $record->description = isset($data->description) ? $data->description : '';
        $record->owner = $data->owner;
        $record->group = $data->group;
        $record->institution = $data->institution;

        // Access: get all the views where the artefact is included.
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
    public static function add_access_for_record($record) {
        // Access: get all the views where the block_instance is included
        $access = self::get_view_access_records($record->id);
        $access_restrictions = self::add_record_access_restrictions($access);
        $record->access = $access_restrictions;
    }

    /**
     * Return the data for a single record of the specified type.
     *
     * @todo: refactor. This is way too large.
     *
     * @param string $type The type of record.
     * @param int $id      The id of the record.
     *
     * @return object|bool The record, or false if not found.
     */
    public static function get_record_data_by_id($type, $id) {
        global $USER;

        $sql = '
            SELECT
                bi.id,
                bi.view AS view_id,
                bi.title,
                bi.configdata,
                v.owner,
                v.institution,
                v.group,
                v.ctime
            FROM {block_instance} bi
            JOIN {view} v
                ON v.id = bi.view
            WHERE bi.id = ?
        ';
        $params = [$id];

        $record = get_record_sql( $sql, $params);
        if (!$record) {
            return false;
        }

        require_once(get_config('docroot') . 'blocktype/lib.php');

        // Load this block.
        $bi = new BlockInstance($id);
        $configdata = $bi->get('configdata');

        $eols = ["\r\n", "\n", "\r"];

        $record->title = str_replace(
            $eols,
            ' ',
            strip_tags($record->title)
        );
        if (is_array($configdata)) {
            // We can only deal with blocktypes that have a 'text' configdata
            // for description at this point.
            $record->description = array_key_exists('text', $configdata) ?
                str_replace(
                    $eols,
                    ' ',
                    strip_tags($configdata['text'])
                ) :
                '';
        }

        // If user is owner update the record link.
        if ($USER->get('id') == $record->owner) {
            $record->link = 'view/view.php?id=' . $record->view_id;
        }

        // Get the view info the block is on.
        $sql = '
            SELECT v.id AS id, v.title AS title
            FROM {view} v
            WHERE v.id = ?
        ';
        $params = [$record->view_id];

        $views = get_records_sql_array($sql, $params);
        if ($views) {
            $record_views = [];
            foreach ($views as $view) {
                if (isset($view->id)) {
                    $record_views[$view->id] = $view->title;
                }
            }
            $record->views = $record_views;
        }

        return $record;
    }

    /**
     * Get all access records of views in which the block_instance is included.
     *
     * @param int $blockid The ID of the Block we're checking.
     *
     * @return array<object>
     */
    public static function get_view_access_records($blockid) {
        $sql = '
            SELECT
                va.view AS view_id,
                va.accesstype,
                va.group,
                va.role,
                va.usr,
                va.institution
            FROM {view_access} va
            JOIN {block_instance} bi
                ON bi.view = va.view
            WHERE bi.id = ?
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
        ';
        $params = [$blockid];
        $records = get_records_sql_array($sql, $params);

        $sql = '
            SELECT v.type
            FROM {view} v
            JOIN {block_instance} bi
                ON bi.view = v.id
            WHERE bi.id = ?
        ';
        $params = [$blockid];
        $view_type = get_field_sql($sql, $params);
        if (is_isolated() && $view_type == 'profile') {
            if ($records) {
                foreach ($records as $k => $access) {
                    if ($access->accesstype == 'loggedin') {
                        unset($records[$k]);
                    }
                }
                $records = array_values($records);
            }

            // View ID this block instance belongs to.
            $viewid = get_field('block_instance', 'view', 'id', $blockid);

            // Check if the view has an Institution.
            $sql = '
                SELECT v.owner
                FROM {view} v
                JOIN {block_instance} bi
                    ON bi.view = v.id
                JOIN {usr_institution} ui
                    ON ui.usr = v.owner
                WHERE bi.id = ?
            ';
            $params = [$blockid];
            $view_has_institution = get_records_sql_array($sql, $params);
            if (!$view_has_institution) {
                // Member of no institution so need to add the 'mahara'
                // institution option.
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
     * @inheritDoc
     *
     * @return array<string,array<string,string>> The map of fields to types.
     */
    public function field_type_map() {
        $ret = parent::field_type_map();
        return $ret;
    }

    /**
     * Requeue content for indexing.
     *
     * Clears the indexing queue table for this type and reloads all usr
     * records for indexing.
     *
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return void
     */
    public static function requeue_searchtype_contents($ids = []) {
        $type = 'block_instance';
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
