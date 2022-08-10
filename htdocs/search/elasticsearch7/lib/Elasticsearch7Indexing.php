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
 * A class that holds static functions relating to Indexing
 */
class Elasticsearch7Indexing {

    /**
     * Creates the Index on the elasticsearch server itself, first dropping if it already
     * exists.
     *
     * @return void
     */
    public static function create_index() {
        // Drop the index if it already exists.
        $indexname = PluginSearchElasticsearch7::get_write_indexname();
        $params = array('index' => $indexname);
        $ESClient = PluginSearchElasticsearch7::make_client('write');
        $index = $ESClient->indices();

        if ($index->exists($params)) {
            $index->delete($params);
        }

        $shards = PluginSearchElasticsearch7::config_value_or_default('shards');
        $replicashards = PluginSearchElasticsearch7::config_value_or_default('replicashards');
        // Create the index.
        $params = array(
            'index' => $indexname,
            'body'  => array(
                'settings' => array(
                    'number_of_shards' => $shards,
                    'number_of_replicas' => $replicashards,
                    'analysis' => array(
                        'analyzer' => array(
                            'mahara_analyzer' => array(
                                'type' => 'custom',
                                // Define token separators as any non-alphanumeric
                                // character.
                                'tokenizer' => 'standard',
                                'filter' => array('lowercase', 'stop', 'maharaSnowball'),
                                'char_filter' => array('maharaHtml'),
                            ),
                            'whitespace_analyzer' => array(
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace',
                                'filter'    => array('lowercase', 'stop'),
                            ),
                        ),
                        'filter' => array(
                            'maharaSnowball' => array(
                                'type' => 'snowball',
                                'language' => 'English',
                            ),
                        ),
                        'char_filter' => array(
                            'maharaHtml' => array(
                                'type' => 'html_strip',
                                'read_ahead' => '1024',
                            )
                        ),
                    ),
                ),
            ),
        );
        try {
            $index->create($params);
            // Add mapping.
            self::create_mapping($index);
        }
        catch (Exception $e) {
            log_warn($e->getMessage());
        }
    }

    /**
     * Create the properties mapping.
     *
     * Call once the index has been created to do things like setting fields
     * that need to be copied to the 'catch_all' field for full search.
     *
     * @param mixed $index
     *
     * @return void
     */
    private static function create_mapping($index) {
        // Build the mapping array.
        $indexname = PluginSearchElasticsearch7::get_write_indexname();
        $properties = [];
        $enabledtypes = explode(',', PluginSearchElasticsearch7::config_value_or_default('types'));
        foreach ($enabledtypes as $type) {
            $ES_class = 'Elasticsearch7Type_' . $type;
            $map = $ES_class::get_mapping_properties();
            if ($map) {
                $properties = array_merge($properties, $map);
            }
        }

        // Add the catch all field.
        $catch_all = [
            'catch_all' => [
                'type' => 'text',
            ],
        ];
        $properties = array_merge($properties, $catch_all);

        $mapping = [
            'index' => $indexname,
            'body' => [
                '_source' => [
                    'enabled' => true,
                ],
                'properties' => $properties,
            ],
        ];
        $result = $index->putMapping($mapping);
    }

    /**
     * Requeue content for a search type
     *
     * Clears the indexing queue table for this searchtype, and then re-loads
     * it with every matching item in the database. This should be overridden
     * in the individual Elasticsearch7Type_* classes. If not, the @TODO
     * message will be presented.
     *
     * @param string $type The elasticsearch search type
     * @param array<int> $ids Optional array of ids to index
     * @param string $artefacttype (Optional) If the search type is Artefact, this is the artefact subtype
     *
     * @return void
     */
    public static function requeue_searchtype_contents($type, $ids = [], $artefacttype = null) {
        $ES_class = 'Elasticsearch7Type_' . $type;
        if (class_exists($ES_class) || method_exists($ES_class, 'requeue_searchtype_contents')) {
            $ES_class::requeue_searchtype_contents($ids, $artefacttype);
        }
        else {
            log_debug('Triggered in - ' . __CLASS__ . '::' . __FUNCTION__ . "(${type}): line " . __LINE__);
            log_debug("@TODO - $ES_class::requeue_searchtype_contents();");
        }
    }

    /**
     * Add items to the queue for indexing.
     *
     * Indexes Pages, Artifacts, and Block instances between $last_run and
     * $timestamp unless $views is set.  If $views is set then the items in
     * $views will be processed.
     *
     * The variables $last_run and $timestamp are timestamp strings of the
     * form "Y-m-d H:i:s".
     *
     * @param string $last_run Timestamp of the last run.
     * @param string $timestamp Timestamp of this run.
     * @param array<int> $views A list of view IDs.
     *
     * @return void
     */
    public static function add_to_queue_access($last_run, $timestamp, $views = array()) {
        $artefacttypes_str = self::artefacttypes_filter_string();
        // Set the WHERE clause and any JOINs we need.
        if (!empty($views)) {
            // We have an array of view IDs. Process these specific items.
            $joinstr = '';
            $wherestr = "v.id IN (" . implode(',', array_values($views)) . ")";
        }
        else {
            // Fall back to processing all items between last run and the
            // timestamp.
            $joinstr = "INNER JOIN {view_access} vac ON vac.view = v.id ";
            $wherestr = "vac.startdate BETWEEN '{$last_run}' AND '{$timestamp}'
                        OR vac.stopdate BETWEEN '{$last_run}' AND '{$timestamp}'
                        OR vac.ctime BETWEEN '{$last_run}' AND '{$timestamp}'";
        }

        // Add matching Pages to the queue.
        execute_sql("
            INSERT INTO {search_elasticsearch_7_queue} (itemid, type)
                SELECT v.id, 'view'
                FROM {view} v
                " . $joinstr . "
                WHERE " . $wherestr . ";
        ");

        // Add matching Artefacts to the queue.
        execute_sql("
            INSERT INTO {search_elasticsearch_7_queue} (itemid, type, artefacttype)
                SELECT var.artefact, 'artefact', a.artefacttype
                FROM {view} v
                " . $joinstr . "
                INNER JOIN {view_artefact} var ON var.view = v.id
                INNER JOIN {artefact} a ON var.artefact = a.id
                WHERE (" . $wherestr . ")
                AND a.artefacttype IN {$artefacttypes_str};
        ");

        // Add matching Block instances to the queue.
        execute_sql("
            INSERT INTO {search_elasticsearch_7_queue} (itemid, type)
                SELECT b.id, 'block_instance'
                FROM {view} v
                " . $joinstr . "
                INNER JOIN {block_instance} b ON v.id = b.view
                WHERE (" . $wherestr . ")
                AND b.blocktype IN ('text');
        ");
    }

    /**
     * Add bulk items to the queue.
     *
     * The items in the array are of the form:
     * [
     *   id => int,
     *   table => string,
     *   artefacttype => string
     * ]
     *
     * @param array<int,object> $items Array of objects.
     *
     * @return void
     */
    public static function bulk_add_to_queue($items) {
        if (is_array($items)) {
            foreach ($items as $k => $item) {
                $artefacttype = !empty($item->artefacttype) ? $item->artefacttype : null;
                self::add_to_queue($item->id, $item->table, $artefacttype);
            }
        }
    }

    /**
     * Add item to queue for indexing.
     *
     * @param int $id The ID of the item to index
     * @param string $table The table the item is from.
     * @param string|null $artefacttype An optional artefact type.
     *
     * @return void
     */
    public static function add_to_queue($id, $table, $artefacttype=null) {
        $artefacttypes_str = self::artefacttypes_filter_string();

        // The view_artefact table is an edge case.
        if ($table == 'view_artefact') {
            $sql = "INSERT INTO {search_elasticsearch_7_queue} (itemid, type, artefacttype)
                    SELECT va.artefact, ? AS type, a.artefacttype FROM {view_artefact} va
                        INNER JOIN {artefact} a ON a.id = va.artefact
                        WHERE va.id = ?
                        AND NOT EXISTS (
                            SELECT 1 FROM {search_elasticsearch_7_queue}
                            WHERE itemid = va.artefact
                        )
                        AND a.artefacttype IN " . $artefacttypes_str;
            execute_sql($sql, ['artefact', $id]);
            return;
        }

        // Add the item to the queue.
        $sql = "INSERT INTO {search_elasticsearch_7_queue} (itemid, type)
                    SELECT ?, ? FROM (SELECT 1) AS dummytable
                        WHERE NOT EXISTS (
                            SELECT 1 FROM {search_elasticsearch_7_queue} WHERE itemid = ? AND type = ?
                        )";
        execute_sql($sql, [$id, $table, $id, $table]);

        // If queued item is a 'view' we need to update the user and artefacts
        if ($table == 'view') {
            $sql = "INSERT INTO {search_elasticsearch_7_queue} (itemid, type)
                        SELECT u.id, ? AS type
                        FROM {usr} u
                        INNER JOIN {view} v ON v.owner = u.id
                        WHERE v.type = ?
                            AND v.id = ?
                            AND NOT EXISTS (
                                SELECT q.id FROM {search_elasticsearch_7_queue} q
                                WHERE q.type = ? AND q.itemid = u.id
                            )";
            execute_sql($sql, ['usr', 'profile', $id, 'usr']);

            $sql = "INSERT INTO {search_elasticsearch_7_queue} (itemid, type)
                        SELECT va.artefact, ? AS type
                        FROM {view_artefact} va
                        INNER JOIN {artefact} a ON va.artefact = a.id
                        WHERE va.view = ?
                            AND va.artefact NOT IN (
                                SELECT itemid
                                FROM {search_elasticsearch_7_queue}
                                WHERE type = ?
                            )
                            AND a.artefacttype IN " . $artefacttypes_str;
            execute_sql($sql, ['artefact', $id, 'artefact']);
        }

        $artefacttypes = explode(',', get_config_plugin('search', 'elasticsearch7', 'artefacttypes'));
        if ($artefacttype && in_array($artefacttype, $artefacttypes)) {
            $sql = "INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype)
                        SELECT ?, ?, ?
                        FROM (SELECT 1) AS dummytable
                        WHERE NOT EXISTS (
                            SELECT 1
                            FROM {search_elasticsearch_queue}
                            WHERE itemid = ?
                                AND type = ?
                                AND artefacttype = ?
                        )";
            execute_sql($sql, [$id, $table, $artefacttype, $id, $table, $artefacttype]);
        }
    }

    /**
     * Build an SQL string suitable for a WHERE IN clause.
     *
     * @return string
     */
    public static function artefacttypes_filter_string() {
        $artefacttypes = explode(',', PluginSearchElasticsearch7::config_value_or_default('artefacttypes'));
        $artefacttypes_str = '';
        foreach ($artefacttypes as $artefacttype) {
            $artefacttypes_str .= '\'' . $artefacttype . '\', ';
        }
        $artefacttypes_str = '(' . substr($artefacttypes_str, 0, strlen($artefacttypes_str)-2) . ')';

        return $artefacttypes_str;
    }

}
