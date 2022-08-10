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

require_once(dirname(__FILE__) . '/Elasticsearch7FilterAcl.php');

/**
 * This class isn't really an ElasticsearchType, although in spirit it's similar. It's used only for the purpose of the "search all"
 * function
 */
class Elasticsearch7Pseudotype_all
{

    /**
     *   To respect the design, 3 searches will be executed:
     *       1st: retrieves the main facet (Text / Media / Portfolio / Users / Group) and the count for each of them
     *       2nd: - retrieves the results of the first non empty facet term for display in the tab
     *            - retrieves the secondary facet to enable / disable the filter items
     *       3nd: - retrieves the results with all filters applied
     *
     * @param string $query_string The user submitted search terms.
     * @param int $limit How many results we are returning.
     * @param int $offset Where we start from.
     * @param array<string,mixed> $options Additional arbitrary options for the search.
     * @param string $mainfacetterm The main facet we are filtering on.
     * @param object $USER The user we are searching as.
     * @return array<string,mixed> The results array.
     */
    public static function search($query_string, $limit, $offset, $options, $mainfacetterm, $USER) {

        // The query > bool > must field is used for weighting results.
        // https://www.elastic.co/guide/en/elasticsearch/reference/7.14/query-filter-context.html
        $must = [];
        // The query > bool > filter is used for filtering.
        $filter = [];

        $result = self::baseResultArray($limit, $offset, $options, $mainfacetterm);

        // These access parameters will be applied to each ES query.
        $accessfilter = new Elasticsearch7FilterAcl($USER);
        $filter += $accessfilter->get_params();

        $client = PluginSearchElasticsearch7::make_client();
        $search_index = PluginSearchElasticsearch7::get_write_indexname();

        // The content of $matching will appear in query > bool > must.
        // https://www.elastic.co/guide/en/elasticsearch/reference/7.14/query-filter-context.html
        // This 'must' clause is used for weighting of the results only.
        if ($result['tagsonly'] === true) {
            $must[] = [
                'match_all' => new \stdClass()
            ];
            // For a tags only search we give extra weight to the tags field.
            $must[] = [
                'match' => [
                    'tags' => $query_string
                ]
            ];
            // We still need to include 'tags' in the query > bool > filter clause.
            $filter['must'][] = [
                'term' => [
                    'tags' => $query_string,
                ],
            ];
        }
        else if (strlen($query_string) <= 0) {
            // Get everything if empty query.
            $must[] = [
                'match_all' => new \stdClass()
            ];
        }
        else {
            $must[] = [
                'match' => [
                    'catch_all' => $query_string,
                ],
            ];
        }

        $results_found = self::processAggregatedResultsForTabs(
            $result,
            $client,
            $search_index,
            $must,
            $filter,
            $USER
        );
        if ($results_found) {
            // Add some extra filters for the Tab being viewed.
            $must[] = [
                "term" => [
                    "mainfacetterm.keyword" => $result['selected'],
                ],
            ];
            if (!empty($result['content-filter-selected']) && $result['content-filter-selected'] != 'all') {
                $must[] = [
                    "term" => [
                        "secfacetterm.keyword" => $result['content-filter-selected'],
                    ],
                ];
            }
            self::processResultsOfSelectedTab(
                $result,
                $client,
                $search_index,
                $must,
                $filter,
                $limit,
                $offset,
                $USER
            );
        }

        return $result;
    }

    /**
     * The array structure of the returned results for a search.
     *
     * @param int $limit
     * @param int $offset
     * @param array<string,mixed> $options
     * @param string $mainfacetterm
     *
     * @return array<string,mixed> The base array structure
     */
    private static function baseResultArray($limit, $offset, $options, $mainfacetterm) {
        $result = [
            'count'   => 0,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => false,
            'selected' => false,
            'totalresults' => 0,
            'facets'  => [
                ['term' => 'Text', 'count' => 0, 'display' => 'Text'],
                ['term' => 'Media', 'count' => 0, 'display' => 'Media'],
                ['term' => 'Portfolio', 'count' => 0, 'display' => 'Portfolio'],
                ['term' => 'User', 'count' => 0, 'display' => 'Users'],
                ['term' => 'Group', 'count' => 0, 'display' => 'Group'],
            ],
            'content-filter'  => [
                ['term' => 'all', 'count' => 0, 'display' => 'All'],
                ['term' => 'Audio', 'count' => 0, 'display' => 'Audio'],
                ['term' => 'Comment', 'count' => 0, 'display' => 'Comment'],
                ['term' => 'Document', 'count' => 0, 'display' => 'Document'],
                ['term' => 'Folder', 'count' => 0, 'display' => 'Folder'],
                ['term' => 'Forum', 'count' => 0, 'display' => 'Forum'],
                ['term' => 'Forumpost', 'count' => 0, 'display' => 'Forum post'],
                ['term' => 'Image', 'count' => 0, 'display' => 'Image'],
                ['term' => 'Journal', 'count' => 0, 'display' => 'Journal'],
                ['term' => 'Journalentry', 'count' => 0, 'display' => 'Journal entry'],
                ['term' => 'Note', 'count' => 0, 'display' => 'Note'],
                ['term' => 'Plan', 'count' => 0, 'display' => 'Plan'],
                ['term' => 'Profile', 'count' => 0, 'display' => 'Profile'],
                ['term' => 'Resume', 'count' => 0, 'display' => 'Résumé'],
                ['term' => 'Video', 'count' => 0, 'display' => 'Video'],
                ['term' => 'Wallpost', 'count' => 0, 'display' => 'Wall post'],
                ['term' => 'Collection', 'count' => 0, 'display' => 'Collection'],
                ['term' => 'Page', 'count' => 0, 'display' => 'Page'],
            ],
            'content-filter-selected' => 'all',
            'owner-filter'  => [
                ['term' => 'all', 'count' => 0, 'display' => 'All'],
                ['term' => 'me', 'count' => 0, 'display' => 'Me'],
                ['term' => 'others', 'count' => 0, 'display' => 'Others'],
            ],
            'owner-filter-selected' => 'all',
            'tagsonly' => null,
            'sort' => 'score',
            'license' => 'all',
            'pagination_js' => '',
        ];

        if (!empty($mainfacetterm)) {
            $result['selected'] = $mainfacetterm;
        }

        if (!empty($options['secfacetterm'])) {
            $result['content-filter-selected'] = $options['secfacetterm'];
        }

        if (isset($options['tagsonly']) && $options['tagsonly'] == true) {
            $result['tagsonly'] = true;
        }

        if (!empty($options['sort'])) {
            $result['sort'] = $options['sort'];
        }

        if (!empty($options['owner'])) {
            $result['owner-filter-selected'] = $options['owner'];
        }

        if (!empty($options['license'])) {
            $result['license'] = $options['license'];
        }

        return $result;
    }

    /**
     * Step 1: Fetch aggregated results for the tabs.
     *
     * @param array<string,mixed> $result Stores the results.
     * @param Elasticsearch\Client $client The Elasticsearch client.
     * @param string $search_index The index we are searching in.
     * @param array<int,mixed> $must Search query weighting
     * @param array<string,mixed> $filter Filters for ACL and tags
     * @param LiveUser $USER The current account performing the search.
     *
     * @return boolean  True if results were found.
     */
    private static function processAggregatedResultsForTabs(&$result, $client, $search_index, $must, $filter, $USER) {
        // Build the search params.
        $params = [
            'index' => $search_index,
            'body'  => [
                // 'size'  => 0, // we only want aggregations at this point
                'query' => [
                    'bool' => [
                        'must' => $must,
                        'filter' => [
                            'bool' => $filter,
                        ],
                    ],
                ],
                'aggs' => [
                    'ContentType' => [
                        'terms' => [
                            'field' => 'mainfacetterm.keyword',
                        ],
                        'aggs' => [
                            'ContentTypeFacet' => [
                                'terms' => [
                                    'field' => 'secfacetterm.keyword',
                                ],
                            ],
                        ],
                    ],
                    'OwnerType' => [
                        'terms' => [
                            'field' => 'owner.keyword',
                        ],
                    ],
                ],
            ],
        ];

        // Search for the aggregated results. This provides the faceted results
        // used to describe the tabs.
        $results = $client->search($params);
        $facets = self::processAggregations($results['aggregations']['ContentType']['buckets'], false);

        // There were no matching documents. Return immediately.
        if (count($facets) == 0) {
            return false;
        }

        array_walk($result['facets'], 'self::processTabs', $facets);

        $ownertypes = self::processAggregations($results['aggregations']['OwnerType']['buckets'], true, $USER);
        array_walk($result['owner-filter'], 'self::processTabs', $ownertypes);

        $selectedFacet = false;
        if ($result['selected']) {
            $selectedFacet = $result['selected'];
        }

        $result['facetByTerm'] = [];
        // Something we can lookup by term.
        foreach ($result['facets'] as $k => $v) {
            $result['facetByTerm'][$v['term']] = $v['count'];
        }

        // Facets with no count aren't selectable in the UI.
        if ($selectedFacet === false || $result['facetByTerm'][$selectedFacet] <= 0 ) {
            $selectedFacet = self::getSelectedFacet($result['facetByTerm']);
        }
        $result['selected'] = $selectedFacet;

        // Get the filters for this facet.
        $allcontenttypes = self::fetchSubAggregation($results['aggregations']['ContentType']['buckets'], 'ContentTypeFacet');
        $contenttypes = self::processAggregations($allcontenttypes[$result['selected']]['buckets'], true);
        array_walk($result['content-filter'], 'self::processTabs', $contenttypes);

        $result['totalresults'] = $results['hits']['total']['value'];

        // Results were found.
        return true;
    }

    /**
     * Process results of the selected tab.
     *
     * Run the query with a Main Facet on the filter.
     *
     * @param array<string,mixed> $result
     * @param Elasticsearch\Client $client
     * @param string $search_index
     * @param array<int,mixed> $must
     * @param array<int,mixed> $filter
     * @param int $limit
     * @param int $offset
     * @param LiveUser $USER
     *
     * @return void
     */
    private static function processResultsOfSelectedTab(&$result, $client, $search_index, $must, $filter, $limit, $offset, $USER) {
        $sorting = [];

        $sort = explode('_', $result['sort']);
        // Add the initial sort criteria if appropriate.
        switch ($sort[0]) {
            case 'sort':
                // Sort by Name.
                $sorting[] = ['sort.keyword' => $sort[1]];
                break;

            case 'ctime':
                // Sort by creation time.
                $sorting[] = [
                    'ctime' => [
                        'order' => $sort[1],
                    ],
                ];
                break;
        }

        // Sort by "Relevance". If we're also sorting on sort or ctime this
        // will be a secondary sort criteria.
        $sorting[] = '_score';

        // Apply Owner filter if different from "all".
        // We can't apply this as a filter but as a 'must_not'.
        $mustnot = [];
        $uid = $USER->get('id');
        switch ($result['owner-filter-selected']) {
            case "others":
                $mustnot[] = [
                    'term' => [
                        'owner' => $uid,
                    ],
                ];
                break;

            case "me":
                $must[] = [
                    'term' => [
                        'owner' => $uid,
                    ]
                ];
                break;
        }

        $params = [
            'index' => $search_index,
            'body'  => [
                'from'  => $offset,
                'size'  => $limit,
                'sort'  => $sorting,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'bool' => $filter,
                        ]
                    ]
                ],
            ],
        ];

        if (!empty($mustnot)) {
            $params['body']['query']['bool']['must_not'] = $mustnot;
        }
        if (!empty($must)) {
            $params['body']['query']['bool']['must'] = $must;
        }
        $results = $client->search($params);

        # We have results for Text/Users/Groups. Hitting the search front page returns none.
        $result['count'] = $results['hits']['total']['value'];

        $records = [];

        foreach ($results['hits']['hits'] as $hit) {
            $tmp = $hit['_source'];
            // Type is referenced elsewhere where the returned data is used.
            $tmp['type'] = $tmp['indexsourcetype'];
            $ES_class = 'Elasticsearch7Type_' . $tmp['type'];
            if (!class_exists($ES_class)) {
                log_debug('Class not found: ' . $ES_class);
            }

            // Store highlighted fields if there is any
            $tmp['highlight'] = array();
            if (!empty($hit['highlight']) && !empty($hit['highlight']['description'])) {
                $tmp['highlight'] = $hit['highlight']['description'];
            }

            // Get all the data from the DB table
            if (method_exists($ES_class, 'get_record_data_by_id')) {
                $dbrec = $ES_class::get_record_data_by_id($tmp['type'], $tmp['id']);
            }
            else {
                $dbrec = false;
                log_debug("@TODO: $ES_class::get_record_data_by_id()");
            }
            if ($dbrec) {
                $tmp['db'] = $dbrec;
                $tmp['db']->deleted = false;
                $highlight = false;
                if (!empty($tmp['highlight'])) {
                    $highlights = array_map([__CLASS__, 'cleanUpHighlight'], $tmp['highlight']);
                    $highlight = implode(' ... ', $highlights);
                    $chr = mb_substr(strip_tags($highlight), 0, 1, "UTF-8");
                    $starts_with_upper = (mb_strtolower($chr, "UTF-8") != $chr);
                    if (substr($highlight, 0, 1) !== '<' && !$starts_with_upper) {
                        $highlight = '... ' . $highlight;
                    }
                    if (substr($highlight, -1, 1) !== '.' && substr($highlight, -1, 1) !== '>') {
                        $highlight = $highlight . ' ...';
                    }
                }
                $tmp['db']->highlight = $highlight;
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
    }

    /**
     * Callback to remove broken highlight spans from search result strings.
     *
     * @param string $str The string being processed.
     *
     * @return string The cleaned up string
     */
    private static function cleanUpHighlight($str) {
        $str = strip_tags($str, '<span>');
        if (strpos($str, '<') > strpos($str, '>')) {
            // We probably have a broken close tag so will strip it out.
            $str = substr($str, strpos($str, '>') + 1);
        }
        return $str;
    }

    /**
     * Get the sub aggregated structure for an aggregation.
     *
     * @param array<int,mixed> $data The data we are inspecting.
     * @param string $subagg Name of the sub aggregation
     *
     * @return array<string,mixed>
     */
    public static function fetchSubAggregation(array $data, $subagg) {
        $ret = array();
        foreach ($data as $value) {
            if (isset($value[$subagg])) {
                $ret[$value['key']] = $value[$subagg];
            }
        }
        return $ret;
    }

    /**
     * Combine search results into aggregated structure.
     *
     * @param array<int,mixed> $data The data we are processing.
     * @param bool $all   To also return a total count key called 'all'
     * @param LiveUser|boolean $USER  user object to filter the owners 'me' vs 'others'
     *
     * @return array<string,mixed>
     */
    public static function processAggregations(array $data, $all = false, $USER = false) {
        $ret = array();
        $countall = 0;
        foreach ($data as $value) {
            $ret[$value['key']] = $value['doc_count'];
            if ($USER) {
                if ((int)$USER->get('id') === (int)$value['key']) {
                    $ret['me'] = $value['doc_count'];
                }
                else {
                    $ret['others'] = $value['doc_count'];
                }
            }
            if ($all) {
                $countall += $value['doc_count'];
            }
        }
        if ($all) {
            $ret['all'] = $countall;
        }

        return $ret;
    }

    /**
     * Update tabs with result counts.
     *
     * @param array<string,mixed> $item The tab being processed.
     * @param int $key Unused, but sent as part of the array_walk() callback.
     * @param array<string,int> $data The Tabs with item count.
     *
     * @return void
     */
    public static function processTabs(&$item, $key, $data) {
        if (isset($data[$item['term']])) {
            $item['count'] = $data[$item['term']];
        }
    }

    /**
     * Return the first entry in $data which has a non-zero count.
     *
     * @param array<string,int> $data An array of term, count pairs.
     *
     * @return string A facet name.
     */
    public static function getSelectedFacet($data) {
        $default = '';
        foreach ($data as $key => $val) {
            if (empty($default)) {
                $default = $key;
            }
            if ($val > 0) {
                return $key;
            }
        }
        return $default;
    }

}
