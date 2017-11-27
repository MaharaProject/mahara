<?php
class ElasticsearchType_event_log extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
            ),
            'secfacetterm' => array (
                    'type' => 'keyword',
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'usr' => array (
                    'type' => 'integer',
                    'copy_to' => 'catch_all'
            ),
            'realusr' => array (
                    'type' => 'integer',
                    'copy_to' => 'catch_all'
            ),
            'event' => array (
                    'type' => 'keyword',
                    'copy_to' => 'catch_all'
            ),
            'data' => array (
                    'type' => 'text',
            ),
            'resourceid' => array (
                    'type' => 'integer',
            ),
            'resourcetype' => array (
                    'type' => 'keyword',
                    'copy_to' => 'catch_all'
            ),
            'parentresourceid' => array (
                    'type' => 'integer',
            ),
            'parentresourcetype' => array (
                    'type' => 'keyword',
                    'copy_to' => 'catch_all'
            ),
            'ownerid' => array (
                    'type' => 'integer',
            ),
            'ownertype' => array (
                    'type' => 'keyword',
            ),
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
            ),
            'yearweek' => array (
                    'type' => 'keyword',
            ),
            'createdbyuser' => array (
                    'type' => 'boolean',
            ),
            'firstname' => array (
                    'type' => 'keyword',
            ),
            'lastname' => array (
                    'type' => 'keyword',
            ),
            'username' => array (
                    'type' => 'keyword',
            ),
            'displayname' => array (
                    'type' => 'keyword',
            ),
    );

    public static $mainfacetterm = 'Event';
    public static $secfacetterm = 'Log';
    public function __construct($data) {
        $this->conditions = array ();

        $this->mapping = array (
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

        parent::__construct ( $data );
    }

    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record) {
            return false;
        }
        $record->secfacetterm = self::$secfacetterm;
        $record->yearweek = date('Y_W', strtotime($record->ctime));
        $user = get_record('usr', 'id', $record->usr);
        $record->username = $user->username;
        $record->firstname = $user->firstname;
        $record->lastname = $user->lastname;
        $record->displayname = $user->preferredname;
        // Need to adjust the view shared to group so ownerid is the group id being shared to
        // rather than user id being shared from as the resourceid is the view_access row id not the group id.
        if ($record->event == 'updateviewaccess' && $record->resourcetype == 'group' && $record->ownertype == 'user') {
            $data = json_decode($record->data);
            if (isset($data->rules) && isset($data->rules->group)) {
                $record->ownerid = $data->rules->group;
            }
        }
        $record->createdbyuser = FALSE;
        if ($record->usr === $record->realusr) {
            // A non-masquerading event
            if (in_array($record->event, array('createview', 'createcollection', 'creategroup', 'saveartefact'))) {
                $data = json_decode($record->data);
                if ($record->event == 'createview' && isset($data->viewtype) && $data->viewtype == 'portfolio') {
                    $record->createdbyuser = TRUE;
                }
                else if ($record->event == 'saveartefact' && ($data->ctime == $data->mtime)) {
                    $record->createdbyuser = TRUE;
                }
                else {
                    $record->createdbyuser = TRUE;
                }
            }
        }
        return $record;
    }

    public static function getRecordDataById($type, $id) {
        global $USER;

        $sql = 'SELECT * FROM {event_log} el WHERE el.id = ?';

        $record = get_record_sql($sql, array($id));
        if (!$record) {
            return false;
        }

        return $record;
    }

    /**
     * @param unknown $options
     * @param unknown $limit
     * @param unknown $offset
     * @param unknown $USER
     * @return multitype:number boolean unknown Ambigous <boolean, NULL> Ambigous <boolean, unknown> multitype:multitype:string number   Ambigous <string, unknown> |multitype:multitype:
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
        $matching = (!empty($options['query']) && is_array($options['query'])) ? $options['query'] : $matching;
        $filters = (!empty($options['filters']) && is_array($options['filters'])) ? $options['filters'] : array('term' => array('secfacetterm' => 'Log'));
        $range = (!empty($options['range']) && is_array($options['range'])) ? $options['range'] : array('range' => array('id' => array('gte' => 1)));
        $sort = (!empty($options['sort']) && is_array($options['sort'])) ? $options['sort'] : array('ctime' => 'desc');
        $aggs = (!empty($options['aggs']) && is_array($options['aggs'])) ? $options['aggs'] : array('EventType' => array('terms' => array('field' => 'event')));

        $client = PluginSearchElasticsearch::make_client();
        $params = array(
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'body'  => array(
                'size'  => 0, // we only want aggregations at this point
                'query' => array(
                    'bool' => array(
                        'must' => $matching,
                        'filter' => array(
                            'bool' => array(
                                'must' => array(
                                    $filters,
                                    $range
                                ),
                            ),
                        ),
                    ),
                ),
                'sort' => array(
                    $sort,
                ),
                'aggs' => $aggs,
            ),
        );

        $results = $client->search($params);
        $result['totalresults'] = $results['hits']['total'];
        if ($result['totalresults'] > 0) {
            $result['aggregations'] = $results['aggregations'];
        }
        if ($limit < 1) {
            // We are just wanting the count of results so return now
            return $result;
        }

        //      2 - Apply filters and retrieve final results
        // ------------------------------------------------------------------------------------------
        // Sort needs to be an array with key = field, value=sort direction
        if (!empty($options['sort'])) {
            $result['sort'] = array_merge($options['sort'], $result['sort']);
        }
        $sorting = array();
        foreach ($result['sort'] as $key => $val) {
            $sorting[][$key] = array('order' => $val);
        }

        $params = array(
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'body'  => array(
                'from'  => $offset,
                'size'  => $limit,
                'sort'  => $sorting,
                'query' => array(
                    'bool' => array(
                        'must' => $matching,
                        'filter' => array(
                            'bool' => array(
                                'must' => array(
                                    $filters,
                                    $range
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $results = $client->search($params);
        $result['count'] = $results['hits']['total'];

        foreach ($results['hits']['hits'] as $hit) {
            $tmp = array();
            $tmp['type'] = $hit['_type'];
            $ES_class = 'ElasticsearchType_' . $tmp['type'];

            $tmp['highlight'] = array();
            $tmp = $tmp + $hit['_source'];
            // Get all the data from the DB table
            $dbrec = $ES_class::getRecordDataById($tmp['type'], $tmp['id'], $tmp['highlight']);
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
     * To return the count of particular buckets and their sub buckets
     *
     * @param array    $aggmap   The array to hold the mappings
     * @param array    $data     The array containing the elasticaseach result bucket information
     * @param bool     $all      To also return a total count key called 'all'
     * @param array    $buckets  Names of buckets in their nested order
     * @param string   $key      The name of the key to display in $aggmap
     *
     * @return array   $aggmap
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
}
