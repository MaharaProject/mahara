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

class Elasticsearch7Type_event_log extends Elasticsearch7Type {

    public static $mainfacetterm = 'Event';
    public static $secfacetterm = 'Log';

    public function __construct($data) {

        $this->mapping = array (
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'usr' => NULL,
            'realusr' => NULL,
            'ctime' => NULL,
            'event' => NULL,
            'data' => NULL,
            'resourceid' => NULL,
            'resourcetype' => NULL,
            'parentresourceid' => NULL,
            'parentresourcetype' => NULL,
            'ownerid' => NULL,
            'ownertype' => NULL,
            'yearweek' => NULL,
            'createdbyuser' => NULL,
            'firstname' => NULL,
            'lastname' => NULL,
            'username' => NULL,
            'displayname' => NULL,
        );

        parent::__construct($data);
    }

    /**
     * Fetch a record for an Event.
     *
     * @param string $type
     * @param int $id
     * @param array<string,mixed>|null $map
     *
     * @return bool|object The record or false if not found.
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

        $record->yearweek = date('Y_W', strtotime($record->ctime));

        // Add user details to the record.
        $user = get_record('usr', 'id', $record->usr);
        $record->username = $user->username;
        $record->firstname = $user->firstname;
        $record->lastname = $user->lastname;
        $record->displayname = $user->preferredname;

        // Need to adjust the view shared to group so ownerid is the group id
        // being shared to rather than user id being shared from as the
        // resourceid is the view_access row id not the group id.
        if ($record->event == 'updateviewaccess' && $record->resourcetype == 'group' && $record->ownertype == 'user') {
            $data = json_decode($record->data);
            if (isset($data->rules) && isset($data->rules->group)) {
                $record->ownerid = $data->rules->group;
            }
        }

        // Is the record created by a user?
        $record->createdbyuser = FALSE;
        if ($record->usr === $record->realusr) {
            // This is a non-masquerading event.
            $trigger_events = [
                'createview',
                'createcollection',
                'creategroup',
                'saveartefact',
            ];
            if (in_array($record->event, $trigger_events)) {
                // This event was triggered by a user.
                $record->createdbyuser = TRUE;
            }
        }
        return $record;
    }

    /**
     * @param array<string,mixed> $options
     * @param int $limit
     * @param int $offset
     * @param LiveUser $USER
     * @return array<string,mixed>
     */
    public static function search($options, $limit, $offset, $USER) {

        $result = array(
                'count'   => 0,
                'limit'   => $limit,
                'offset'  => $offset,
                'data'    => false,
                'totalresults' => 0,
                'sort' => array('_score' => 'desc'),
        );

        //      1 - Get the aggregate list of events
        // ------------------------------------------------------------------------------------------
        $records = array();
        $matching = array(
            'match_all' => new \stdClass()
        );

        // Use provided query, filters and range otherwise default to all event_log rows
        if (!empty($options['query']) && is_array($options['query'])) {
            $matching = $options['query'];
        }

        if (!empty($options['filters']) && is_array($options['filters'])) {
            $filters = $options['filters'];
        }
        else {
            $filters = ['range' => ['id' => array('gte' => 1)]];
        }

        if (!empty($options['range']) && is_array($options['range'])) {
            $range = $options['range'];
        }
        else {
            $range = ['range' => ['id' => ['gte' => 1]]];
        }

        if (!empty($options['sort']) && is_array($options['sort'])) {
            $sort = $options['sort'];
        }
        else {
            $sort = ['ctime' => 'desc'];
        }

        if (!empty($options['aggs']) && is_array($options['aggs'])) {
            $aggs = $options['aggs'];
        }
        else {
            $aggs = ['EventType' => ['terms' => ['field' => 'event']]];
        }

        $client = PluginSearchElasticsearch7::make_client();
        $index = PluginSearchElasticsearch7::get_write_indexname();
        $params = [
            'index' => $index,
            'body'  => [
                // We only want aggregations at this point.
                'size'  => 0,
                'query' => [
                    'bool' => [
                        'must' => $matching,
                        'filter' => [
                            'bool' => [
                                'must' => [
                                    $filters,
                                    $range
                                ],
                            ],
                        ],
                    ],
                ],
                'sort' => [
                    $sort,
                ],
                'aggs' => $aggs,
            ],
        ];

        $results = $client->search($params);
        $result['totalresults'] = $results['hits']['total'];
        if ($result['totalresults'] > 0) {
            $result['aggregations'] = $results['aggregations'];
        }
        if ($limit < 1) {
            // We are just wanting the count of results so return now.
            return $result;
        }

        //      2 - Apply filters and retrieve final results
        // ------------------------------------------------------------------------------------------
        // Sort needs to be an array with key = field, value=sort direction.
        if (!empty($options['sort'])) {
            $result['sort'] = array_merge($options['sort'], $result['sort']);
        }
        $sorting = array();
        foreach ($result['sort'] as $key => $val) {
            $sorting[][$key] = array('order' => $val);
        }

        $params = [
            'index' => $index,
            'body'  => [
                'from'  => $offset,
                'size'  => $limit,
                'sort'  => $sorting,
                'query' => [
                    'bool' => [
                        'must' => $matching,
                        'filter' => [
                            'bool' => [
                                'must' => [
                                    $filters,
                                    $range
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $results = $client->search($params);
        $result['count'] = $results['hits']['total'];

        foreach ($results['hits']['hits'] as $hit) {
            $tmp = array();
            $tmp['type'] = $hit['_type'];
            $ES_class = 'Elasticsearch7Type_' . $tmp['type'];

            $tmp['highlight'] = array();
            $tmp = $tmp + $hit['_source'];
            // Get all the data from the DB table.
            $dbrec = $ES_class::get_record_data_by_id($tmp['type'], $tmp['id'], $tmp['highlight']);
            if ($dbrec) {
                $tmp['db'] = $dbrec;
                $tmp['db']->deleted = false;
            }
            else {
                // If the record has been deleted, so just pass the cached data
                // from the search result. Let the template decide how to handle it.
                $tmp['db'] = (object) $tmp;
                $tmp['db']->deleted = true;
            }
            $records[] = $tmp;
        }

        $result['data'] = $records;
        return $result;
    }

    /**
     * Combine search aggregation results into aggregated array structure.
     *
     * To return the count of particular buckets and their sub buckets.
     *
     * @param array<string,int>   $aggmap   The array to hold the mappings
     * @param array<string,mixed> $data     The array containing the elasticaseach result bucket information
     * @param bool                $all      To also return a total count key called 'all'
     * @param array<string>       $buckets  Names of buckets in their nested order
     * @param string              $key      The name of the key to display in $aggmap
     *
     * @return void
     */
    public static function process_aggregations(&$aggmap, $data, $all = false, $buckets=array(), $key='') {

        $countall = 0;
        $bucket = array_shift($buckets);
        if (!empty($data[$bucket]['buckets'])) {
            foreach ($data[$bucket]['buckets'] as $value) {
                $aggmap[$key . $value['key']] = $value['doc_count'];
                if ($all) {
                    $countall += $value['doc_count'];
                }
                if (!empty($buckets)) {
                    self::process_aggregations($aggmap, $value, $all, $buckets, $key . $value['key'] . '|');
                }
            }
        }
        if ($all) {
            $tmp['all'] = $countall;
        }
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
        $type = 'event_log';
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
            'type' => [
                'type' => 'text',
            ],
            'id' => [
                'type' => 'integer',
            ],
            'usr' => [
                'type' => 'integer',
            ],
            'realusr' => [
                'type' => 'integer',
            ],
            'event' => [
                'type' => 'keyword',
            ],
            'data' => [
                'type' => 'text',
            ],
            'resourceid' => [
                'type' => 'integer',
            ],
            'resourcetype' => [
                'type' => 'keyword',
            ],
            'parentresourceid' => [
                'type' => 'integer',
            ],
            'parentresourcetype' => [
                'type' => 'keyword',
            ],
            'ownerid' => [
                'type' => 'integer',
            ],
            'ownertype' => [
                'type' => 'keyword',
            ],
            'ctime' => [
                "type" => "date",
                "format" => "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis",
            ],
            'yearweek' => [
                'type' => 'keyword',
            ],
            'createdbyuser' => [
                'type' => 'boolean',
            ],
        ];
    }

}
