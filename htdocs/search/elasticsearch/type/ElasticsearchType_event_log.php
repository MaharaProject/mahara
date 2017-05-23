<?php
class ElasticsearchType_event_log extends ElasticsearchType {
    public static $mappingconf = array (
            'mainfacetterm' => array (
                    'type' => 'keyword',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'secfacetterm' => array (
                    'type' => 'keyword',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'id' => array (
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'usr' => array (
                    'type' => 'integer',
                    'include_in_all' => TRUE
            ),
            'realusr' => array (
                    'type' => 'integer',
                    'include_in_all' => TRUE
            ),
            'event' => array (
                    'type' => 'keyword',
                    'index' => 'not_analyzed',
                    'include_in_all' => TRUE
            ),
            'data' => array (
                    'type' => 'text',
                    'include_in_all' => FALSE
            ),
            'resourceid' => array (
                    'type' => 'integer',
                    'include_in_all' => FALSE
            ),
            'resourcetype' => array (
                    'type' => 'text',
                    'include_in_all' => TRUE
            ),
            'parentresourceid' => array (
                    'type' => 'integer',
                    'include_in_all' => FALSE
            ),
            'parentresourcetype' => array (
                    'type' => 'text',
                    'include_in_all' => TRUE
            ),
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
                    'include_in_all' => FALSE
            )
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
        );

        parent::__construct ( $data );
    }
    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record) {
            return false;
        }
        $record->secfacetterm = self::$secfacetterm;
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
        $searchfield = '_all';

        $matching = array(
            'match_all' => new \stdClass()
        );
        // Use provided filters and range otherwise default to all event_log rows
        $filters = (!empty($options['filters']) && is_array($options['filters'])) ? $options['filters'] : array('term' => array('secfacetterm' => 'Log'));
        $range = (!empty($options['range']) && is_array($options['range'])) ? $options['range'] : array('range' => array('id' => array('gte' => 1)));

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
                'aggs'  => array(
                    'EventType' => array(
                        'terms' => array(
                            'field' => 'event',
                        ),
                    ),
                ),
            ),
        );

        $results = $client->search($params);
        $result['totalresults'] = $results['hits']['total'];

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

}
