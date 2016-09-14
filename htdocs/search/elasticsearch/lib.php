<?php
/**
 *
 * @package    mahara
 * @subpackage search-elasticsearch
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
defined('INTERNAL') || die();
// Required because we use the PluginSearchInternal class for some functions
require_once(get_config('docroot') . 'search/internal/lib.php');

function __autoload_elastica ($class) {

    if (substr($class, 0, 8) == 'Elastica') {
        $file = get_config('libroot') . 'elastica/lib/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once($file);
        }
    }
    else if (substr($class, 0, 18) == 'ElasticsearchType_') {
        $file = __DIR__ . '/type/' . $class . '.php';
        if (file_exists($file)) {
            require_once($file);
        }
    }
}
spl_autoload_register('__autoload_elastica');

defined('INTERNAL') || die();

/**
 * The internal search plugin which searches against the
 * Mahara database.
 */
class PluginSearchElasticsearch extends PluginSearch {

    /**
     * Records in search_elasticsearch_queue that haven't been sent to Elasticsearch yet.
     */
    const queue_status_new = 0;
    /**
     * Records in search_elasticsearch_queue that have been sent in bulk to Elasticsearch.
     * These are deleted after being successfully sent, so they'll only be seen in the table
     * if the request to send them failed.
     */
    const queue_status_sent_in_bulk = 1;
    /**
     * Records  in search_elasticsearch_queue that have been sent individually to Elasticsearch.
     * These are deleted after being successfully sent, so they'll only be seen in the table
     * if the individual request to send them failed.
     */
    const queue_status_sent_individually = 2;

    /**
     * This function indicates whether the plugin should take the raw $query string
     * when its group_search_user function is called, or whether it should get the
     * parsed query string.
     *
     * @return boolean
     */
    public static function can_process_raw_group_search_user_queries() {
        // We're just going to pass our data on to PluginSearchInternal, and that needs the raw query
        return true;
    }

    /**
     * Returns search results for users in a particular group
     *
     * It's called by and tightly coupled with get_group_user_search_results() in searchlib.php. Look there for
     * the exact meaning of its parameters and expected return values.
     *
     * Since I haven't had the time to figure them out, we'll just use PluginSearchInternal's version.
     */
    public static function group_search_user($group, $queries, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $sortoptionidx=null) {
        return PluginSearchInternal::group_search_user($group, $queries, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $sortoptionidx);
    }

    /**
     * Returns search results for users in a particular institution
     *
     * We are going to pass this on to PluginSearchInternal to handle returning the correct results
     * in the correct format.
     */
    public static function institutional_admin_search_user($query, $institution, $limit) {
        return PluginSearchInternal::institutional_admin_search_user($query, $institution, $limit);
    }

    /**
     * This function determines whether the plugin is currently available to be chosen
     * as the sitewide search plugin (i.e. get_config('searchplugin'))
     */
    public static function is_available_for_site_setting() {
        return true;
    }

    /**
     * This function determines if we can connect to the supplied host and port
     */
    public static function can_connect() {
        $host = get_config_plugin('search', 'elasticsearch', 'host');
        $port = get_config_plugin('search', 'elasticsearch', 'port');
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * This function determines whether the plugin allows a search box to display for
     * non-logged in users - only useful if results returned by search are allowed to
     * be seen by the public
     */
    public static function publicform_allowed() {
        return true;
    }

    /**
     * Generates the search form used in the page headers
     * @return string
     */
    public static function header_search_form() {
        return pieform(array(
                'name'                => 'usf',
                'action'              => get_config('wwwroot') . 'search/elasticsearch/index.php',
                'renderer'            => 'oneline',
                'autofocus'           => false,
                'validate'            => false,
                'presubmitcallback'   => '',
                'class'               => 'header-search-form',
                'elements'            => array(
                        'query' => array(
                                'type'           => 'text',
                                'defaultvalue'   => '',
                                'title'          => get_string('pagetitle', 'search.elasticsearch'),
                                'placeholder'    => get_string('pagetitle', 'search.elasticsearch'),
                                'hiddenlabel'    => true,
                        ),
                        'submit' => array(
                            'type' => 'button',
                            'class' => 'btn-primary input-group-btn',
                            'usebuttontag' => true,
                            'value' => '<span class="icon icon-search icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">'. get_string('go') . '</span>',
                        )
                )
        ));
    }

    public static function can_be_disabled() {
        return true;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {

        if (get_config('searchplugin') == 'elasticsearch') {
            $smarty = smarty_core();
            $smarty->assign('notice', get_string('noticeenabled', 'search.elasticsearch', get_config('wwwroot').'admin/site/options.php?fs=searchsettings'));
            $enabledhtml = $smarty->fetch('Search:elasticsearch:confignotice.tpl');
            unset($smarty);
        }
        else {
            $smarty = smarty_core();
            $smarty->assign('notice', get_string('noticenotenabled', 'search.elasticsearch', get_config('wwwroot').'admin/site/options.php?fs=searchsettings'));
            $enabledhtml = $smarty->fetch('Search:elasticsearch:configwarning.tpl');
            unset($smarty);
        }
        if (!self::can_connect()) {
            $smarty = smarty_core();
            $smarty->assign('notice', get_string('noticenotactive', 'search.elasticsearch', get_config_plugin('search', 'elasticsearch', 'host'), get_config_plugin('search', 'elasticsearch', 'port')));
            $enabledhtml .= $smarty->fetch('Search:elasticsearch:configwarning.tpl');
            unset($smarty);
        }

        $config = array(
            'elements' => array(
                'enablednotice' => array(
                    'type'         => 'html',
                    'value'        => $enabledhtml,
                ),
                'host' => array(
                    'title'        => get_string('host', 'search.elasticsearch'),
                    'description'  => get_string('hostdescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => get_config_plugin('search', 'elasticsearch', 'host'),
                    'help'         => true,
                ),
                'port' => array(
                    'title'        => get_string('port', 'search.elasticsearch'),
                    'description'  => get_string('portdescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => get_config_plugin('search', 'elasticsearch', 'port'),
                    'help'         => true,
                ),
                'username' => array(
                    'title'        => get_string('username', 'search.elasticsearch'),
                    'description'  => get_string('usernamedescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => (
                            get_config_plugin('search', 'elasticsearch', 'username')
                            ? get_config_plugin('search', 'elasticsearch', 'username')
                            : get_string('confignotset', 'search.elasticsearch')
                    ),
                    'help'         => true,
                ),
                'password' => array(
                    'title'        => get_string('password', 'search.elasticsearch'),
                    'description'  => get_string('passworddescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => (
                            get_config_plugin('search', 'elasticsearch', 'password')
                            ? get_string('passwordlength', 'search.elasticsearch', strlen(get_config_plugin('search', 'elasticsearch', 'password')))
                            : get_string('confignotset', 'search.elasticsearch')
                    ),
                    'help'         => true,
                ),
                'indexname' => array(
                    'title'        => get_string('indexname', 'search.elasticsearch'),
                    'description'  => get_string('indexnamedescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => get_config_plugin('search', 'elasticsearch', 'indexname'),
                    'help'         => true,
                ),
                'bypassindexname' => array(
                    'title'        => get_string('bypassindexname', 'search.elasticsearch'),
                    'description'  => get_string('bypassindexnamedescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'help'         => true,
                    'value'        => (
                            get_config_plugin('search', 'elasticsearch', 'bypassindexname')
                            ? get_config_plugin('search', 'elasticsearch', 'bypassindexname')
                            : get_string('confignotset', 'search.elasticsearch')
                    ),
                ),
                'analyzer' => array(
                    'title'        => get_string('analyzer', 'search.elasticsearch'),
                    'description'  => get_string('analyzerdescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => get_config_plugin('search', 'elasticsearch', 'analyzer'),
                    'help'         => true,
                ),
                'types' =>  array(
                    'title'        => get_string('types', 'search.elasticsearch'),
                    'description'  => get_string('typesdescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'size'         => '80',
                    'value' => get_config_plugin('search', 'elasticsearch', 'types'),
                    'help'         => true,
                ),
                'cronlimit' => array(
                    'title'        => get_string('cronlimit', 'search.elasticsearch'),
                    'description'  => get_string('cronlimitdescription', 'search.elasticsearch'),
                    'type'         => 'text',
                    'defaultvalue' => get_config_plugin('search', 'elasticsearch', 'cronlimit'),
                ),
            ),
        );

        $types = explode(',', get_config_plugin('search', 'elasticsearch', 'types'));


        // if artefact => show list of artefacttype
        if (in_array('artefact', $types)) {

            $rs = get_recordset_sql('SELECT DISTINCT name AS artefacttype FROM {artefact_installed_type} ORDER BY name ASC');
            $artefacttypes = explode(',', get_config_plugin('search', 'elasticsearch', 'artefacttypes'));
            // the following artefacttypes are auto ticked because the info is already being indexed by the usr table
            $artefacttypes_toexclude = array('firstname', 'lastname', 'preferredname', 'email');
            $artefacttypes = array_merge($artefacttypes, $artefacttypes_toexclude);
            // to be valid, artefact types need a hierarchy
            $artefacttypesmap_array = self::elasticsearchartefacttypesmap_to_array();
            $types_checkbox = array();
            foreach (recordset_to_array($rs) as $record) {
                $types_checkbox[] = array(
                                        'title'        => $record->artefacttype,
                                        'value'        => $record->artefacttype,
                                        'defaultvalue' => in_array($record->artefacttype, $artefacttypes) ? true : false,
                                        'disabled'     => in_array($record->artefacttype, $artefacttypes_toexclude) OR
                                                            !in_array($record->artefacttype, array_keys($artefacttypesmap_array)) ? true : false,
                                    );
            }

            $config['elements']['artefacttypes'] = array(
                'type'         => 'checkboxes',
                'class'        => 'stacked',
                'title'        => get_string('artefacttypelegend', 'search.elasticsearch'),
                'description'  => get_string('artefacttypedescription', 'search.elasticsearch'),
                'elements'     => $types_checkbox,
            );

            $config['elements']['artefacttypesmap'] = array(
                'type'         => 'textarea',
                'rows'         => 10,
                'cols'         => 100,
                'class'        => 'under-label',
                'title'        => get_string('artefacttypemaplegend', 'search.elasticsearch'),
                'description'  => get_string('artefacttypemapdescription', 'search.elasticsearch'),
                'defaultvalue' => implode("\n", $artefacttypesmap_array),
            );

        }


        if (count($types) > 0) {
            $item_by_type_in_queue = array();
            $rs = get_records_sql_array('SELECT type, count(*) AS total FROM {search_elasticsearch_queue} GROUP BY type', array());
            if ($rs) {
                foreach ($rs as $record) {
                    $item_by_type_in_queue[$record->type] = $record->total;
                }
            }

            // Create the buttons that let an admin reset individual sub-indexes.
            $resetelements = array();
            // TODO: Make single-searchtype reset work properly. For now we'll just comment it out, leaving only
            // "reset all" available.
            $resetelements['resetdescription'] = array(
                'type' => 'html',
                'value' => get_string('resetdescription','search.elasticsearch')
            );
            foreach ($types as $type) {
                $key = $type;
                $keyreset = $type . 'reset';
                $count_in_queue = isset($item_by_type_in_queue[$type]) ? ' (' . $item_by_type_in_queue[$type] . ')' : '(0)';
                $resetelements[$keyreset] =  array(
                    'title' => $type,
                    'type' => 'html',
                    'value' => $count_in_queue,
//                    'title'        =>   $type . $count_in_queue,
//                    'type'         => 'submit',
//                    'defaultvalue' => get_string('reset', 'search.elasticsearch'),
                );
            }
            // And on the end, a special one to reset all the indexes.
            $resetelements['allreset'] = array(
                'title' => get_string('resetallindexes', 'search.elasticsearch'),
                'type' => 'submit',
                'class' => 'btn-default',
                'defaultvalue' => get_string('reset', 'search.elasticsearch'),
            );

            $config['elements']['resetindex'] = array(
                'type' => 'fieldset',
                'class' => 'last',
                'legend' => get_string('resetlegend', 'search.elasticsearch'),
                'elements' => $resetelements,
                'collapsible' => true
            );
        }

        return $config;
    }

    public static function validate_config_options(Pieform $form, $values) {
        // First check that there isn't an elasticsearch cron indexing the site
        if (get_record('config', 'field', '_cron_lock_search_elasticsearch_cron')) {
            $form->set_error(null, get_string('indexingrunning', 'search.elasticsearch'));
        }
    }

    public static function save_config_options(Pieform $form, $values) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        set_config_plugin('search', 'elasticsearch', 'cronlimit', $values['cronlimit']);

        // Changes in artefact types:
        //       - we need to add the newly selected artefact types (for indexing)
        //       - we need to removed artefact types that have been unchecked (to remove them from the index)
        // I wanted to use the "delete by query" feature of Elastic search (http://www.elasticsearch.org/guide/reference/api/delete-by-query/)
        // but it was not very reliable. According to the docs:  it is not recommended to delete "large chunks of the data in an index".
        // So I decided to remove data by Id.

        set_config_plugin('search', 'elasticsearch', 'artefacttypesmap', $values['artefacttypesmap']);
        // to be valid, artefact types need a hierarchy
        $artefacttypesmap_array = self::elasticsearchartefacttypesmap_to_array();
        // the following artefacttypes are already being indexed by the usr table so we don't want to save them
        $artefacttypes_toexclude = array('firstname', 'lastname', 'preferredname', 'email');
        foreach ($artefacttypes_toexclude as $exclude) {
            if (!empty($values['artefacttypes'][$exclude])) {
                unset($values['artefacttypes'][$exclude]);
            }
        }
        $values['artefacttypes'] = array_intersect($values['artefacttypes'], array_keys($artefacttypesmap_array));

        $types = explode(',', $values['types']);
        if (in_array('artefact', $types)) {
            $artefacttypes_old = explode(',', get_config_plugin('search', 'elasticsearch', 'artefacttypes'));
            $result = array_diff($artefacttypes_old, $values['artefacttypes']) + array_diff($values['artefacttypes'], $artefacttypes_old);
            // result now contains the artefacttypes that have been checked and unchecked
            foreach ($result as $artefacttype) {
                ElasticsearchIndexing::requeue_searchtype_contents('artefact', $artefacttype);
            }
            set_config_plugin('search', 'elasticsearch', 'artefacttypes', implode(',', $values['artefacttypes']));
        }

        // If they chose to reset all the indexes, do that.
        if (isset($values['allreset'])) {
            // set the cron lock before beginning re index to stop the cron indexing at same time
            $start = time();
            insert_record('config', (object) array('field' => '_cron_lock_search_elasticsearch_cron', 'value' => $start));

            self::reset_all_searchtypes();
            // Send the first batch of records to the elasticsearch server now, for instant gratification
            self::index_queued_items();

            // free the cron lock
            delete_records('config', 'field', '_cron_lock_search_elasticsearch_cron', 'value', $start);
        }
        // TODO: Make single-searchtype reset work properly. For now we'll just comment this out in hopes
        // it will aid a future developer.
//         else {
//             // If they clicked any "reset index" button, then reset only that index.
//             // We'll  loop through the index types, in order to check for the form detail for each one
//             $types = explode(',', $values['types']);
//             foreach ($types as $type) {
//                 // Check whether they chose to reset the index on this type
//                 $type = trim($type);
//                 $keyreset = $type . 'reset';
//                 if (isset($values[$keyreset]) AND $values[$keyreset] == 'reset') {
//                     // Queues every matching record for this index type
//                     ElasticsearchIndexing::requeue_searchtype_contents($type);
//                 }
//                 // Reset the mappings for (only) this type
//                 self::set_mapping($type);
//                 ElasticsearchIndexing::create_triggers($type);
//             }
//         }

        return true;
    }

    /**
     * This function gets called when the sitewide search plugin is switched to
     * this one. It's the chance for the plugin to do any post-configuration
     * initialization it might need. (The same stuff you'd probably do after
     * changing the plugin's configuration via its extension config page.)
     *
     */
    public static function initialize_sitewide() {
        if (self::can_connect()) {
            self::reset_all_searchtypes();
            return true;
        }
        return false;
    }

    /**
     * This function gets called when the sitewide search plugin is switched
     * away from this one.
     *
     * We'll use this opportunity to disable the triggers and clear out the queue
     * table. Otherwise, it will forever swell since we're no longer running
     * the cron.
     */
    public static function cleanup_sitewide() {
        $enabledtypes = explode(',', get_config_plugin('search', 'elasticsearch', 'types'));
        // (re)create the mappings and the overall site index
        foreach ($enabledtypes as $type) {
            ElasticsearchIndexing::drop_triggers($type);
        }
        ElasticSearchIndexing::drop_trigger_functions();
        delete_records('search_elasticsearch_queue');
    }

    /**
     * Resets all the searchtypes in the following ways:
     *  - Deletes and re-creates the elasticsearch index on the server
     *  - Re-creates the trigger functions
     *    - This will also drop the triggers for all types (even those that aren't in use)
     *  - Creates all triggers for those types that are in use
     *  - Tells the elasticsearch server to drop and re-create the index
     *  - Tells the elasticsearch server to re-create the "mapping" for each type
     *  - Loads every record for that type into the queue table, for the cron to chug away at them
     */
    public static function reset_all_searchtypes() {
        ElasticSearchIndexing::create_index();
        ElasticsearchIndexing::create_trigger_functions();
        $enabledtypes = explode(',', get_config_plugin('search', 'elasticsearch', 'types'));
        // (re)create the mappings and the overall site index
        foreach ($enabledtypes as $type) {
            ElasticsearchIndexing::create_triggers($type);
            ElasticsearchIndexing::requeue_searchtype_contents($type);
            self::set_mapping($type);
        }
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('search', 'elasticsearch', 'host', '127.0.0.1');
            set_config_plugin('search', 'elasticsearch', 'port', '9200');
            set_config_plugin('search', 'elasticsearch', 'indexname', 'mahara');
            set_config_plugin('search', 'elasticsearch', 'analyzer', 'mahara_analyzer');
            set_config_plugin('search', 'elasticsearch', 'types', 'usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,collection');
            set_config_plugin('search', 'elasticsearch', 'cronlimit', '50000');

            $elasticsearchartefacttypesmap = file_get_contents(__DIR__ . '/elasticsearchartefacttypesmap.txt');
            set_config_plugin('search', 'elasticsearch', 'artefacttypesmap', $elasticsearchartefacttypesmap);
        }
    }

    public static function elasticsearchartefacttypesmap_to_array() {
        $artefacttypesmap_array = explode("\n", get_config_plugin('search', 'elasticsearch', 'artefacttypesmap'));
        $tmp = array();
        foreach ($artefacttypesmap_array as $key => $value) {
            $tmpkey = explode("|", $value);
            if (count($tmpkey) == 3) {
                $tmp[$tmpkey[0]] = $value;
            }
        }
        ksort($tmp, SORT_STRING);
        return $tmp;
    }


    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'cron',
                'hour'         => '*',
                'minute'       => '*/5',
            ),
        );
    }

    public static function cron() {

        // Only run the cron if this plugin is the active search plugin
        if (get_config('searchplugin') !== 'elasticsearch') {
            return;
        }

        // store the last time the function was executed: eg: 2013-04-11 16:45:30
        $timestamp = date('Y-m-d H:i:s');
        $last_run = get_config_plugin('search', 'elasticsearch', 'lastrun');
        if (isset($last_run)) {
            ElasticsearchIndexing::add_to_queue_access($last_run, $timestamp);
        }

        set_config_plugin('search', 'elasticsearch', 'lastrun', $timestamp);
        // process the queue
        self::index_queued_items();

    }

    /**
     * Creates the "mapping" for this searchtype, on the elasticsearch server
     *
     * TODO: it would be good to be able to make elasticsearch just delete the contents of one
     * searchtype, perhaps identified by its mapping? But I haven't been able to figure out how
     * to do that, so instead you can only delete the whole index at once.
     * @param string $type
     */
    private static function set_mapping($type) {
        // usr,interaction_instance,interaction_forum_post,view,group,artefact
        $ES_class = 'ElasticsearchType_' . $type;
        if ($ES_class::$mappingconf === false) {
            return false;
        }

        $elasticaClient = self::make_client();
        $elasticaIndex = $elasticaClient->getIndex(self::get_write_indexname());
        $elasticaAnalyzer = get_config_plugin('search', 'elasticsearch', 'analyzer');
        if (!isset($elasticaAnalyzer)) {
            $elasticaAnalyzer = 'mahara_analyzer';
        }

        // Load type
        $elasticaType = $elasticaIndex->getType($type);

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        // we use mahara_analyzer created through elastica
        $mapping->setParam('index_analyzer', $elasticaAnalyzer);
        $mapping->setParam('search_analyzer', $elasticaAnalyzer);

        // Define boost field
        //$mapping->setParam('_boost', array('name' => '_boost', 'null_value' => 1.0));

        // Set mapping
        $mapping->setProperties($ES_class::$mappingconf);

        // Send mapping to type
        $mapping->send();
    }


    /**
     * Sends records from the queue table into the elasticsearch server
     */
    public static function index_queued_items() {

        $cronlimit = intval(get_config_plugin('search', 'elasticsearch', 'cronlimit'));
        if ($cronlimit <= 0) {
            $limitfrom = $limitto = '';
        }
        else {
            $limitfrom = 0;
            $limitto = $cronlimit;
        }

        $requestlimit = intval(get_config_plugin('search', 'elasticsearch', 'requestlimit'));
        if ($requestlimit <= 0) {
            // If they specified no request limit, just use a really big number. This is easier
            // than writing special code just to handle the case where there's no limit.
            $requestlimit = 1000;
        }

        $redolimit = intval(get_config_plugin('search', 'elasticsearch', 'redolimit'));
        if ($redolimit <= 0) {
            // If they've set redolimit to 0, they don't want to retry failed records at all
            $redolimit = 0;
            $redoablecount = 0;
        }
        else {
            // Find out how many failed records there are
            // (Since any sent in bulk will be deleted if the request processed successfully, any remaining ones
            // are failed records.)
            $redoablecount = count_records('search_elasticsearch_queue', 'status', self::queue_status_sent_in_bulk);
            $redolimit = min($redolimit, $redoablecount);
            if ($limitto) {
                $redolimit = min($redolimit, $limitto);
                $limitto -= $redolimit;
            }
        }
        $records = get_records_array('search_elasticsearch_queue', 'status', self::queue_status_new, 'id', '*', $limitfrom, $limitto);

        if (!$records && !$redolimit) {
            return;
        }

        $elasticaClient = self::make_client();
        $indexname = self::get_write_indexname();
        $elasticaIndex = $elasticaClient->getIndex($indexname);
        $artefacttypesmap_array = self::elasticsearchartefacttypesmap_to_array();

        if ($records) {
            list($documents, $deletions) = self::preprocess_queued_items($records, $artefacttypesmap_array);

            // Delete in bulk
            if ($deletions) {
                $delcount = 0;
                foreach ($deletions as $docs) {
                    $delcount += count($docs);
                }
                log_info("  {$delcount} deletions to index in bulk...");
                self::send_queued_items_in_bulk(
                    $deletions,
                    function($records, $type) use ($elasticaClient, $indexname) {
                        return $elasticaClient->deleteIds($records, $indexname, $type);
                    },
                    $requestlimit
                );
            }
            // Send in bulk
            if ($documents) {
                $doccount = 0;
                foreach ($documents as $docs) {
                    $doccount += count($docs);
                }
                log_info("  {$doccount} documents to index in bulk...");
                self::send_queued_items_in_bulk(
                    $documents,
                    function($records, $type) use ($elasticaClient, $elasticaIndex) {
                        return $elasticaClient->addDocuments($records, $elasticaIndex);
                    },
                    $requestlimit
                );
            }
        }

        // Now, pick up any failed ones
        $records = get_records_array('search_elasticsearch_queue', 'status', self::queue_status_sent_in_bulk, 'id', '*', 0, $redolimit);
        if ($records) {
            list($documents, $deletions) = self::preprocess_queued_items($records, $artefacttypesmap_array);

            // Delete individually
            if ($deletions) {
                $delcount = 0;
                foreach ($deletions as $docs) {
                    $delcount += count($docs);
                }
                log_info("  {$delcount} deletions to index individually...");
                self::send_queued_items_individually(
                    $deletions,
                    function($record, $type) use ($elasticaClient, $indexname) {
                        return $elasticaClient->deleteIds(array($record), $indexname, $type);
                    },
                    $requestlimit
                );
            }

            // Send individually
            if ($documents) {
                $doccount = 0;
                foreach ($documents as $docs) {
                    $doccount += count($docs);
                }
                log_info("  {$doccount} documents to index individually...");
                self::send_queued_items_individually(
                    $documents,
                    function($record, $type) use ($elasticaClient, $elasticaIndex) {
                        return $elasticaClient->addDocuments(array($record), $elasticaIndex);
                    },
                    $requestlimit
                );
            }
        }

        // Refresh Index
        $elasticaIndex->refresh();
    }

    /**
     * Process a set of records from search_elasticsearch_queue and sort them into
     * items to insert and delete into the Elasticsearch index.
     * @param array $records
     * @param array $artefacttypesmap_array
     * @return array()
     */
    private static function preprocess_queued_items($records, $artefacttypesmap_array) {
        $documents = array();
        $deletions = array();
        foreach ($records as $record) {
            $deleteitem = false;
            $ES_class = 'ElasticsearchType_' . $record->type;
            if ($record->type == 'artefact') {
                $dbrecord = $ES_class::getRecordById($record->type, $record->itemid, $artefacttypesmap_array);
            }
            else {
                $dbrecord = $ES_class::getRecordById($record->type, $record->itemid);
            }

            // If the record has been physically deleted from the DB or if its artefacttype is not selected
            if ($dbrecord == false) {
                $deleteitem = true;
            }
            else {
                $item = new $ES_class($dbrecord);
                $deleteitem = $item->getisDeleted();
            }

            // Mark item for bulk deletion from index
            if ($deleteitem == true) {
                $deletions[$record->type][$record->id] = $record->itemid;
            }
            // Add item for bulk index
            else {
                $documents[$record->type][$record->id] = new \Elastica\Document($record->itemid, $item->getMapping(), $record->type);
            }
        }
        return array(
            $documents,
            $deletions
        );
    }

    /**
     * Uploat a set of items to Elasticsearch in bulk
     * @param array $documents A multi-dimensional array. The top level has keys representing elasticsearch document types.
     * Each of these has a value which is an array of actual Elasticsearch documents or deletion requests, with their
     * key being the matching record in the search_elasticsearch_queue table.
     * @param callback $processfunction A callback function  to bulk-request each slice of documetns
     */
    private static function send_queued_items_in_bulk($documents, $processfunction, $requestlimit) {
        $uploadcount = 0;
        $batchcount = 0;
        $errorcount = 0;

        // Bulk insert into index
        foreach ($documents as $type => $docs) {
            for ($i = 0; $i < count($docs); $i += $requestlimit) {
                $requestdocs = array_slice($docs, $i, $requestlimit, true);
                $ids = array_keys($requestdocs);
                $questionmarks = implode(',', array_fill(0, count($ids), '?'));
                $time = db_format_timestamp(time());

                // Mark them before sending, in case the request fails.
                $sql = 'UPDATE {search_elasticsearch_queue} SET status = ?, lastprocessed = ? WHERE id IN (' . $questionmarks . ')';
                execute_sql(
                        $sql,
                        array_merge(
                                array(
                                        self::queue_status_sent_in_bulk,
                                        $time
                                ),
                                $ids
                        )
                );

                // Send them
                try {
                    $batchcount++;
                    $uploadcount += count($requestdocs);
                    if ($batchcount % 10 == 0) {
                        log_info("    batches: {$batchcount}; records: {$uploadcount}; errors: {$errorcount}");
                    }
                    $response = $processfunction($requestdocs, $type);

                    if ($response->hasError()) {
                        log_warn("Error from Elasticsearch trying to send bulk request at time {$time}: " . $response->getError());
                        $errorcount++;
                    }
                    else {
                        // Delete them (since they've been sent successfully)
                        delete_records_select('search_elasticsearch_queue', 'id IN (' . $questionmarks. ')', $ids);
                    }
                }
                catch (Exception $e) {
                    $errorcount++;
                    log_warn('Exception sending elasticsearch request at time ' . $time . ': ' . $e->getMessage() );
                }
            }
        }
        log_info("    batches: {$batchcount}; records: {$uploadcount}; errors: {$errorcount}");
        if ($errorcount) {
            log_info("    The records in the {$errorcount} errored batches will be queued for individual indexing");
        }
     }


    /**
     * Upload a set of items to Elasticsearch individually
     * @param array $documents A multi-dimensional array. The top level has keys representing elasticsearch document types.
     * Each of these has a value which is an array of actual Elasticsearch documents or deletion requests, with their
     * key being the matching record in the search_elasticsearch_queue table.
     * @param callback $processfunction A callback function  to bulk-request each slice of documetns
     */
    private static function send_queued_items_individually($documents, $processfunction, $requestlimit) {
        $uploadcount = 0;
        $errorcount = 0;

        // Bulk insert into index
        foreach ($documents as $type => $docs) {
            foreach ($docs as $queueid => $doc) {
                update_record(
                    'search_elasticsearch_queue',
                    (object) array(
                        'id' => $queueid,
                        'status' => self::queue_status_sent_individually,
                        'lastprocessed' => db_format_timestamp(time())
                    )
                );
                // Send it
                try {
                    $uploadcount++;
                    if ($uploadcount % 20 == 0) {
                        log_info("    uploads: {$uploadcount}; errors: {$errorcount}");
                    }
                    $response = $processfunction($doc, $type);

                    if ($response->hasError()) {
                        $errorcount++;
                        log_warn("Error from Elasticsearch trying to send individual record {$queueid}: " . $response->getError());
                    }
                    else {
                        // No errors! Go ahead and delete it from the queue
                        delete_records('search_elasticsearch_queue', 'id', $queueid);
                    }
                }
                catch (Exception $e) {
                    $errorcount++;
                    log_warn('Exception sending elasticsearch record ' . $queueid . ': ' . $e->getMessage() );
                }
            }
        }
        log_info("    uploads: {$uploadcount}; errors: {$errorcount}");
    }


    public static function search_all ($query_string, $limit, $offset = 0, $options=array(), $mainfacetterm = null, $subfacet = null) {
        global $USER;
        return ElasticsearchPseudotype_all::search($query_string, $limit, $offset, $options, $mainfacetterm, $USER);
    }

    public static function search_user($query_string, $limit, $offset = 0, $data=array()) {
        return PluginSearchInternal::search_user($query_string, $limit, $offset, $data);
    }

    public static function search_group($query_string, $limit, $offset=0, $type='member', $category='', $institution='all') {
        // Given the results depends on the user the SQL search makes more sense here than Elastic Search
        // So  I'll just call the PluginSearchInternal related function
        return PluginSearchInternal::search_group($query_string, $limit, $offset, $type, $category, $institution);
    }

    public static function self_search($query_string, $limit, $offset, $type = 'all') {
        // call the PluginSearchInternal related function
        return PluginSearchInternal::self_search($query_string, $limit, $offset, $type);
    }

    public static function admin_search_user($query_string, $constraints, $offset, $limit,
                                             $sortfield, $sortdir) {

        // We need to fudge some stuff before sending it on, because get_admin_user_search_results()
        // in lib/searchlib.php has some hard-coded special functionality for the internal search plugin
        if (is_array($query_string) && count($query_string)>0) {
            $query_string = $query_string[0]['string'];
        }
        else {
            $query_string = "";
        }

        return PluginSearchInternal::admin_search_user($query_string, $constraints, $offset, $limit,
                                             $sortfield, $sortdir);

   }

   /**
    * Creates an \Elastica\Client object, filling in the host and
    * port with the values from the elasticsearch plugin's admin screen.
    * If you wanted to make other changes to how we connect to elasticsearch,
    * this would be a good place to do it.
    *
    * @return \Elastica\Client
    */
   public static function make_client() {
       $config = array();
       $config['host'] = get_config_plugin('search', 'elasticsearch', 'host');
       $config['port'] = get_config_plugin('search', 'elasticsearch', 'port');
       if ($username = get_config_plugin('search', 'elasticsearch', 'username')) {
           $password = get_config_plugin('search', 'elasticsearch', 'password');
           // Thank you Wikipedia: http://en.wikipedia.org/wiki/Basic_access_authentication#Client_side
           $authheader = 'Basic ' . base64_encode("{$username}:{$password}");
           $config['headers'] = array('Authorization'=>$authheader);
       }
       return new \Elastica\Client($config);
   }


   /**
    * Return the name of the index to use for writing. Basically, this is bypassindexname
    * if that is supplied, and indexname if not.
    *
    * @return string
    */
   public static function get_write_indexname() {
       // If they provided bypassindexname, then use that, otherwise use indexname.
       // The purpose of bypassindexname is to allow a site to reindex data into a new
       // index, while still using the old index for searching.
       $indexname = get_config_plugin('search', 'elasticsearch', 'bypassindexname');
       if (!$indexname) {
           $indexname = get_config_plugin('search', 'elasticsearch', 'indexname');
       }
       return $indexname;
   }

   /**
    * Builds the "results" table seen on the universal search results page
    * @param unknown_type $data
    */
   public static function build_results_html(&$data) {

       $smarty = smarty_core();
       $smarty->assign('data', isset($data['data']) ? $data['data'] : null);

       $params = array();
       if (isset($data['query'])) {
           $params['query'] = $data['query'];
       }
       if (isset($data['selected'])) {
           $params['mainfacetterm'] = $data['selected'];
       }
       if (isset($data['content-filter-selected'])) {
           $params['secfacetterm'] = $data['content-filter-selected'];
       }
       if (isset($data['owner-filter-selected'])) {
           $params['owner'] = $data['owner-filter-selected'];
       }
       if (isset($data['tagsonly'])) {
           $params['tagsonly'] = $data['tagsonly'];
       }
       if (isset($data['sort'])) {
           $params['sort'] = $data['sort'];
       }
       if (isset($data['license'])) {
           $params['license'] = $data['license'];
       }
       if (!isset($data['count'])) {
           $data['count'] = 0;
       }
       if (!isset($data['limit'])) {
           $data['limit'] = 0;
       }
       if (!isset($data['offset'])) {
           $data['offset'] = 0;
       }
       $smarty->assign('offset', $data['offset']);

       $resultcounttextsingular = get_string('record', 'search.elasticsearch');
       $resultcounttextplural = get_string('records', 'search.elasticsearch');

       if (isset($data['facets'])) {
           $smarty->assign('facets', $data['facets']);
       }
       if (isset($data['content-filter'])) {
           $smarty->assign('contentfilter', $data['content-filter']);
       }
       if (isset($data['content-filter-selected'])) {
           $smarty->assign('contentfilterselected', $data['content-filter-selected']);
       }
       if (isset($data['owner-filter'])) {
           $smarty->assign('ownerfilter', $data['owner-filter']);
       }
       if (isset($data['owner-filter-selected'])) {
           $smarty->assign('owner', $data['owner-filter-selected']);
       }
       if (isset($data['tagsonly'])) {
           $smarty->assign('tagsonly', $data['tagsonly']);
       }
       if (isset($data['selected'])) {
           $smarty->assign('selected', $data['selected']);
       }
       if (isset($data['sort'])) {
           $smarty->assign('sort', $data['sort']);
       }
       if (isset($data['limit'])) {
           $smarty->assign('limit', $data['limit']);
       }
       if (isset($data['offset'])) {
           $smarty->assign('offset', $data['offset']);
       }
       if (isset($data['license'])) {
           $smarty->assign('license', $data['license']);
       }
       if (isset($data['totalresults'])) {
           $smarty->assign('totalresults', $data['totalresults']);
       }

       // Only show licence if Text or Media tab is selected and license metadata site config is set
       if (isset($data['license_on']) && isset($data['license_options']) && isset($data['selected']) && ($data['selected'] == 'Media' || $data['selected'] == 'Text')) {
           $smarty->assign('license_on', $data['license_on']);
           $smarty->assign('license_options', $data['license_options']);
       }


       if (isset($data['type'])) {
           $smarty->assign('type', $data['type']);
       }
       $smarty->assign('query', $params['query']);

       $data['tablerows'] = $smarty->fetch('Search:elasticsearch:searchresults.tpl');

       $pagination = build_pagination(array(
               'id' => 'elasticsearch_pagination',
               'url' => get_config('wwwroot') . 'search/elasticsearch/index.php?' . http_build_query($params),
               'jsonscript' => 'search/elasticsearch/json/elasticsearch.php',
               'datatable' => 'universalsearchresult',
               'count' => $data['count'],
               'setlimit' => $data['limit'],
               'limit' => $data['limit'],
               'offset' => $data['offset'],
               'jumplinks' => 6,
               'numbersincludeprevnext' => 2,
               'resultcounttextsingular' => $resultcounttextsingular,
               'resultcounttextplural' => $resultcounttextplural,
               'extradata' => array('page' => 'index'),
       ));
       $data['pagination'] = $pagination['html'];
       $data['pagination_js'] = $pagination['javascript'];
   }

    /**
     * Fix the $query string for things that can break elasticsearch.
     * @param string $query
     *
     * @return string
     */
    public function clean_query($query) {
        $query = stripslashes($query); // to remove any backslashes
        $badchars = array(
            '"',
            '[',
            ']',
            '{',
            '}',
            '~',
            '^',
            '(',
            ')',
            '-',
            '+',
            '/',
            '!',
            ':'
        );
        foreach ($badchars as $bad) {
            // Replace with a space.
            $query = preg_replace('/\\'.$bad.'/',' ',$query);
        }
        return $query;
    }
}

/**
 *    This class encapsulates the ACL filters
 */
class ElasticsearchFilterAcl extends \Elastica\Filter\BoolOr
{

    private $user;

    public function __construct($user) {

        $this->user = $user;

        //    No ACL          (artefacts that don't implement ACL)
        $elasticaFilterNoACL = new \Elastica\Filter\Missing('access.general');
        $this->addFilter($elasticaFilterNoACL);

        //    GENERAL         (public - loggedin - friends)
        //        public
        $elasticaFilterGeneral = new \Elastica\Filter\Term(array('access.general' => 'public'));
        $this->addFilter($elasticaFilterGeneral);

        //        loggedin
        if ($this->user->is_logged_in()) {
            $elasticaFilterGeneral = new \Elastica\Filter\Term(array('access.general' => 'loggedin'));
            $this->addFilter($elasticaFilterGeneral);

            //        friends: pass a list of friends => check if access.general = friends and the owner is a friend of the current user
            if ($friends = $this->getFriendsList()) {
                $elasticaFilterAnd  = new \Elastica\Filter\BoolAnd();
                $elasticaFilterGeneral = new \Elastica\Filter\Term(array('access.general' => 'friends'));
                $elasticaFilterAnd->addFilter($elasticaFilterGeneral);
                $elasticaFilterGeneral = new \Elastica\Filter\Terms('owner', $friends);
                $elasticaFilterAnd->addFilter($elasticaFilterGeneral);
                $this->addFilter($elasticaFilterAnd);
            }

        }

        //    INSTITUTIONS     (array of institutions that have access to the artefact)
        $user_institutions = array_keys($this->user->get('institutions'));
        if ($user_institutions && count($user_institutions) > 0) {
            $elasticaFilterInstitutions = new \Elastica\Filter\Terms('access.institutions', $user_institutions);
            $this->addFilter($elasticaFilterInstitutions);
        }

        //     GROUPS             (array of groups that have access to the artefact) groups [all/admin/member]
        if ($groups = $this->getGroupsList()) {
            $roles = $this->getExistingRoles();
            foreach($roles AS $role){
                if (isset($groups[$role]) && count($groups[$role])) {
                    $elasticaFilterGroup[$role] = new \Elastica\Filter\Terms('access.groups.'.$role, $groups[$role]);
                    $this->addFilter($elasticaFilterGroup[$role]);
                }
            }
        }
        //     USRS             (array of user ids that have access to the artefact)
        if ($this->user->is_logged_in()) {
            // if owner
            $elasticaFilterOwner = new \Elastica\Filter\Term(array('owner' => $this->user->get('id')));
            $this->addFilter($elasticaFilterOwner);
            // in access.usrs list
            $elasticaFilterUsr = new \Elastica\Filter\Term(array('access.usrs' => $this->user->get('id')));
            $this->addFilter($elasticaFilterUsr);
        }



    }

    private function getFriendsList(){
        $list = array();
        $friends = get_friends($this->user->get('id'), 0, 0);
        if ($friends && array_key_exists('data', $friends) && is_array($friends['data'])) {
            foreach ($friends['data'] as $friend) {
                $list[] = $friend->id;
            }
        }
        return $list;
    }

    private function getGroupsList(){
        $list = array();
        foreach (group_get_user_groups($this->user->get('id')) as $group) {
            $list[$group->role][] = $group->id;
            $list['member'][] = $group->id;
        }
        return $list;
    }

    private function getExistingRoles(){
        $rs = get_recordset_sql('SELECT DISTINCT role FROM {grouptype_roles}');
        $roles = array('all');
        foreach (recordset_to_array($rs) as $record) {
            $roles[] = $record->role;
        }
        return $roles;
    }
}


/**
 * Represents one of the "types" that elasticsearch can search against. These are "types"
 * in the elasticsearch sense: http://www.elasticsearch.org/guide/reference/api/search/indices-types/
 *
 * The currently active types are stored in the "search->elasticsearch->types" config variable.
 *
 * This isn't quite a fully fleshed-out Mahara plugin type, although it is an expandable area.
 * One noteable limitation is that under the current implementation, the type name must match
 * up exactly with a Mahara table. Though since all the operations are read-only, you could
 * work around that with a view.
 */
abstract class ElasticsearchType
{
    /**
     * @var string The name of this search type. Should match the name of the class, and the name of a DB table
     */
    public static $type = null;

    protected $item_to_index;
    protected $mapping;

    private static $mysqltriggeroperations = array('insert', 'update', 'delete');

    /**
     *   $conditions was originally used to filter only active, non deleted records to insert into the queue,
     *   but as we will now use it to determine if the record has to be indexed or removed from the index.
     */
    protected $conditions;
    protected $isDeleted;
    public static $mappingconf = false;
    public static $mainfacetterm = false;   // The main facet will not be based on the index type but on a custom grouping
    public static $subfacetterm = false;

    public function __construct($data){

        $this->item_to_index = $data;
        $this->setMapping();
        $this->isDeleted = false;
        $this->setisDeleted();

    }

    public function setMapping(){
        foreach ($this->mapping as $key => $value) {
            $this->mapping[$key] = $this->item_to_index->$key;
        }
    }

    public function getMapping(){
        return $this->mapping;
    }

    /**
     *   set if the record has to be indexed or removed from the index
     */
    public function setisDeleted(){

        if (count($this->conditions) > 0) {
            foreach ($this->conditions as $key => $value) {
                if ($this->item_to_index->$key != $value) {
                    $this->isDeleted = true;
                }
            }
        }
    }

    /**
     *   check if the record has to be indexed or removed from the index
     */
    public function getisDeleted(){
        return $this->isDeleted;
    }

    /**
     *   get the info from the DB for indexing
     */
    public static function getRecordById($type, $id){
        $record = get_record($type, 'id', $id);
        if ($record) {
            // we need to set block_instance creation time later (using view ctime)
            if ($type != 'block_instance') {
                $record->ctime = self::checkctime($record->ctime);
            }
            $record->mainfacetterm = static::$mainfacetterm;
        }
        return $record;
    }

    /**
     *   get the info from the DB for display
     */
    public static function getRecordDataById($type, $id){
        return get_record($type, 'id', $id);
    }

    /**
     * Build the access object
     */
    public static function access_process($records) {

        // For general: 3 levels public > loggedin > friends (this is accesstype in view_access). Objectionable is excluded for now
        // general will be set to the less restrictive of the 3 options
        $levels = array('friends', 'loggedin', 'public');
        $types = array('usr', 'group', 'institution');
        $access = array('general' => 'none'); // access is by default denied to everyone

        if (!$records) {
            return $access;
        }

        foreach ($records as $record) {
            if (isset($record->accesstype) and in_array($record->accesstype, $levels)) {
                if (array_search($record->accesstype, $levels) >= array_search($access['general'], $levels)) {
                    $access['general'] =  $record->accesstype;
                }
            }
            // If accesstype is null, only 1 of the 3 properties institution, group, usr is set
            else if (!isset($record->accesstype)) {
                foreach ($types as $type) {
                    if (isset($record->$type)) {
                        // If type is group, role can be null (all), admin, or member
                        if ($type == 'group') {
                            $role = isset($record->role) ? $record->role : 'all';
                            $access[$type . 's'][$role][] = $record->$type;
                            if ($role == 'all') {
                                // add member and admin roles. 'all' does not seem to find them.
                                $access[$type . 's']['member'][] = $record->$type;
                                $access[$type . 's']['admin'][] = $record->$type;
                            }
                        }
                        else
                            $access[$type . 's'][] = $record->$type;
                    }
                }
            }
        }

        if ($access['general'] == 'loggedin' OR  $access['general'] == 'public') {
            $access = array('general' => $access['general']);
        }

        return $access;
    }

    /**
     * check that the date format is Y-m-d H:i:s - some dates have the following format 2011-07-29 16:13:56.017725
     */
    public static function checkctime($ctime){
        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) ([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $ctime)) {
            $ctime = date('Y-m-d H:i:s', strtotime($ctime));
        }
        return $ctime;
    }

    /**
     * Executes the SQL to create any triggers needed by this search type
     */
    public static function create_triggers() {
        $type = $this::$type;
        if (is_postgres()) {
            $sql = "DROP FUNCTION IF EXISTS {search_elasticsearch_{$type}}() CASCADE;";
            execute_sql($sql);

            $sql = "CREATE TRIGGER {search_elasticsearch_{$type}} BEFORE INSERT OR UPDATE OR DELETE ON {{$type}}
                FOR EACH ROW EXECUTE PROCEDURE {search_elasticsearch_queue_trigger}()";
            execute_sql($sql);
        }
        else {
            foreach (self::$mysqltriggeroperations as $operation) {
                $sql = "DROP TRIGGER IF EXISTS {search_elasticsearch_{$type}_{$operation}};";
                execute_sql($sql);

                $oldid = ($operation == 'insert' ? 'null' : 'OLD.id');
                $newid = ($operation == 'delete' ? 'null' : 'NEW.id');
                $tablename = get_config('dbprefix') . ($type);

                // For inserts, the NEW.id is not available until AFTER the record is insereted.
                $triggertime = ($operation == 'insert' ? 'AFTER' : 'BEFORE');
                $sql = "CREATE TRIGGER {search_elasticsearch_{$type}_{$operation}} {$triggertime} {$operation} ON {{$type}}
                    FOR EACH ROW CALL {search_elasticsearch_queue_trigger}('{$tablename}', '{$operation}', {$oldid}, {$newid})";
                execute_sql($sql);
            }
        }
    }
}

/**
 * This class isn't really an ElasticsearchType, although in spirit it's similar. It's used only for the purpose of the "search all"
 * function
 */
class ElasticsearchPseudotype_all
{

    /**
     *   To respect the design, 3 searches will be executed:
     *       1st:    retrieves the main facet (Text / Media / Portfolio / Users / Group) and the count for each of them
     *       2nd:    - retrieves the results of the first non empty facet term for display in the tab
     *               - retrieves the secondary facet to enable / disable the filter items
     *       3nd:    - retrieves the results with all filters applied
     * @param unknown $query_string
     * @param unknown $limit
     * @param unknown $offset
     * @param unknown $options
     * @param unknown $mainfacetterm
     * @param unknown $USER
     * @return multitype:number boolean unknown Ambigous <boolean, NULL> Ambigous <boolean, unknown> multitype:multitype:string number   Ambigous <string, unknown> |multitype:multitype:
     */
    public static function search($query_string, $limit, $offset, $options, $mainfacetterm, $USER) {

        $result = array(
                'count'   => 0,
                'limit'   => $limit,
                'offset'  => $offset,
                'data'    => false,
                'selected' => (isset($mainfacetterm) && strlen($mainfacetterm)> 0) ? $mainfacetterm : false,
                'totalresults' => 0,
                'facets'  => array(
                        array('term' => "Text", 'count' => 0, 'display' => "Text"),
                        array('term' => "Media", 'count' => 0, 'display' => "Media"),
                        array('term' => "Portfolio", 'count' => 0, 'display' => "Portfolio"),
                        array('term' => "User", 'count' => 0, 'display' => "Users"),
                        array('term' => "Group", 'count' => 0, 'display' => "Group"),
                ),
                'content-filter'  => array(
                        array('term' => "all", 'count' => 0, 'display' => "All"),
                        array('term' => "Audio", 'count' => 0, 'display' => "Audio"),
                        array('term' => "Comment", 'count' => 0, 'display' => "Comment"),
                        array('term' => "Document", 'count' => 0, 'display' => "Document"),
                        array('term' => "Folder", 'count' => 0, 'display' => "Folder"),
                        array('term' => "Forum", 'count' => 0, 'display' => "Forum"),
                        array('term' => "Forumpost", 'count' => 0, 'display' => "Forum post"),
                        array('term' => "Image", 'count' => 0, 'display' => "Image"),
                        array('term' => "Journal", 'count' => 0, 'display' => "Journal"),
                        array('term' => "Journalentry", 'count' => 0, 'display' => "Journal entry"),
                        array('term' => "Note", 'count' => 0, 'display' => "Note"),
                        array('term' => "Plan", 'count' => 0, 'display' => "Plan"),
                        array('term' => "Profile", 'count' => 0, 'display' => "Profile"),
                        array('term' => "Resume", 'count' => 0, 'display' => "Résumé"),
                        array('term' => "Video", 'count' => 0, 'display' => "Video"),
                        array('term' => "Wallpost", 'count' => 0, 'display' => "Wall post"),
                        array('term' => "Collection", 'count' => 0, 'display' => "Collection"),
                        array('term' => "Page", 'count' => 0, 'display' => "Page"),
                ),
                'content-filter-selected' => (isset($options['secfacetterm']) && strlen($options['secfacetterm'])> 0) ? $options['secfacetterm'] : 'all',
                'owner-filter'  => array(
                        array('term' => "all", 'count' => 0, 'display' => "All"),
                        array('term' => "me", 'count' => 0, 'display' => "Me"),
                        array('term' => "others", 'count' => 0, 'display' => "Others"),
                ),
                'owner-filter-selected' => (isset($options['owner']) && strlen($options['owner'])> 0) ? $options['owner'] : 'all',
                'tagsonly' => (isset($options['tagsonly']) && ($options['tagsonly'] == true)) ? true : Null,
                'sort' => (isset($options['sort']) && strlen($options['sort'])> 0) ? $options['sort'] : 'score',
                'license' => (isset($options['license']) && strlen($options['license'])> 0) ? $options['license'] : 'all',
        );

        if (strlen($query_string) <= 0) {
            return $result;
        }

        //      1- Get main facet
        // ------------------------------------------------------------------------------------------

        $records = array();

        $elasticaClient = PluginSearchElasticsearch::make_client();
        $elasticaIndex = $elasticaClient->getIndex(get_config_plugin('search', 'elasticsearch', 'indexname'));

        $elasticaQueryString = new \Elastica\Query\QueryString();
        $elasticaAnalyzer = get_config_plugin('search', 'elasticsearch', 'analyzer');
        $elasticaQueryString->setAnalyzer($elasticaAnalyzer);
        $elasticaQueryString->setDefaultOperator('AND');
        $elasticaQueryString->setQuery($query_string);
        // if tags only => set fields to tags
        if ($result['tagsonly'] === true) {
            $elasticaQueryString->setFields(array('tags'));
        }

        // Create the $elasticaQuery object
        $elasticaQuery = new \Elastica\Query();
        $elasticaQuery->setFrom($offset);
        $elasticaQuery->setLimit($limit);

        $elasticaFilterAnd  = new \Elastica\Filter\BoolAnd();

        // Apply ACL filters
        $elasticaFilterACL   = new ElasticsearchFilterAcl($USER);
        $elasticaFilterAnd->addFilter($elasticaFilterACL);
        $elasticaFilteredQuery = new \Elastica\Query\Filtered($elasticaQueryString, $elasticaFilterAnd);
        $elasticaQuery->setQuery($elasticaFilteredQuery);

        // Define a new facet: mainFacetTerm  - WARNING: don't forget to apply the same filter to the facet
        $elasticaFacet  = new \Elastica\Facet\Terms('mainFacetTerm');
        $elasticaFacet->setField('mainfacetterm');
        $elasticaFacet->setOrder('count');
        $elasticaFacet->setFilter($elasticaFilterAnd);
        $elasticaQuery->addFacet($elasticaFacet);

        $elasticaResultSet  = $elasticaIndex->search($elasticaQuery);
        $result['totalresults']    = $elasticaResultSet->getTotalHits();

        $elasticaFacets = $elasticaResultSet->getFacets();

        $facets = self::process_facets($elasticaFacets['mainFacetTerm']['terms']);
        if (count($facets) == 0) {
            return $result;
        }
        array_walk($result['facets'], 'self::process_tabs', $facets);
        if ($result['selected'] === false || $facets[$result['selected']] == 0) {
            $result['selected'] = self::get_selected_facet($result['facets']);
        }


        //      2- Retrieve results of selected facet
        // ------------------------------------------------------------------------------------------

        $elasticaFilterType = new \Elastica\Filter\Term(array('mainfacetterm' => $result['selected']));
        $elasticaFilterAnd->addFilter($elasticaFilterType);

        $elasticaFilteredQuery = new \Elastica\Query\Filtered($elasticaQueryString, $elasticaFilterAnd);
        $elasticaQuery->setQuery($elasticaFilteredQuery);

        // Define a new facet: secFacetTerm  - WARNING: don't forget to apply the same filter to the facet
        $elasticaFacet = new \Elastica\Facet\Terms('secFacetTerm');
        $elasticaFacet->setField('secfacetterm');
        $elasticaFacet->setOrder('count');
        $elasticaFacet->setFilter($elasticaFilterAnd);
        $elasticaQuery->addFacet($elasticaFacet);

        // Sorting
        // Sorting is defined on a per field level, so we must make sure the field exists in the mapping
        $sort = explode('_', $result['sort']);
        if ($sort[0] == 'score') {
            $sort[0] = '_score';
        }
        // set the second column to sort by the score (to break any 'ties').
        $elasticaQuery->setSort(array(
            array($sort[0] => array('order' => (isset($sort[1]) ? $sort[1] : 'desc'))),
            array('_score' => array('order' => 'desc')),
            )
        );

        $elasticaResultSet  = $elasticaIndex->search($elasticaQuery);
        $result['count']    = $elasticaResultSet->getTotalHits();

        $elasticaFacets = $elasticaResultSet->getFacets();
        $facets = $elasticaFacets['secFacetTerm']['terms'];
        $facets = self::process_facets($elasticaFacets['secFacetTerm']['terms']);
        array_walk($result['content-filter'], 'self::process_tabs', $facets);
        // set the count of "all" to the total hits
        $result['content-filter'][0]['count'] = $result['count'];


        //      3- Apply filters and retrieve final results
        // ------------------------------------------------------------------------------------------

        // Apply Content filter if different from "all"
        if ($result['content-filter-selected'] != 'all') {
            $elasticaFilterContent = new \Elastica\Filter\Term(array('secfacetterm' => $result['content-filter-selected']));
            $elasticaFilterAnd->addFilter($elasticaFilterContent);
        }
        // Apply Owner filter if different from "all"
        if ($result['owner-filter-selected'] != 'all') {
            $uid = $USER->get('id');
            $elasticaFilterOwner = new \Elastica\Filter\Term(array('owner' => $uid));
            if ($result['owner-filter-selected'] == 'others') {
                $elasticaFilterOwner = new \Elastica\Filter\BoolNot($elasticaFilterOwner);
            }
            $elasticaFilterAnd->addFilter($elasticaFilterOwner);
        }
        // Apply license filter if different from "all"
        if ($result['license'] != 'all') {
            $elasticaFilterLicense = new \Elastica\Filter\Term(array('license' => $result['license']));
            $elasticaFilterAnd->addFilter($elasticaFilterLicense);
        }

        $elasticaFilteredQuery = new \Elastica\Query\Filtered($elasticaQueryString, $elasticaFilterAnd);
        $elasticaQuery->setQuery($elasticaFilteredQuery);
        $elasticaResultSet  = $elasticaIndex->search($elasticaQuery);
        $elasticaResults    = $elasticaResultSet->getResults();
        $result['count']    = $elasticaResultSet->getTotalHits();

        foreach ($elasticaResults as $elasticaResult) {
            $tmp = array();
            $tmp['type'] = $elasticaResult->getType();
            $ES_class = 'ElasticsearchType_' . $tmp['type'];
            $tmp = $tmp + $elasticaResult->getData();
            // Get all the data from the DB table
            $dbrec = $ES_class::getRecordDataById($tmp['type'], $tmp['id']);
            if ($dbrec) {
                $tmp['db'] = $dbrec;
                $tmp['db']->deleted = false;
            }
            else {
                // If the record has been deleted, so just pass the cached data
                // from the search result. Let the template decide how to handle
                // it.
                $tmp['db'] = (object) $tmp;
                $tmp['db']->deleted = true;
            }
            $records[] = $tmp;
        }

        $result['data'] = $records;

        return $result;
    }

    public static function process_facets($data) {
        $tmp = array();
        foreach ($data as $key => $value) {
            $tmp[$value['term']] = $value['count'];
        }
        return $tmp;
    }

    public static function process_tabs(&$item, $key, $data) {
        if (isset($data[$item['term']])) {
            $item['count'] = $data[$item['term']];
        }
    }

    public static function get_selected_facet($data) {
        foreach ($data as $key => $value) {
            if ($value['count'] > 0) {
                return $value['term'];
            }
        }
    }
}


/**
 * A class that holds static functions relating to Indexing
 */
class ElasticsearchIndexing {

    // mysql tables require a trigger for each operation - insert, update, delete.
    private static $mysqltriggeroperations = array('insert', 'update', 'delete');

    /**
     * Creates the Index on the elasticsearch server itself, first dropping if it already
     * exists.
     */
    public static function create_index() {
        $esclient = PluginSearchElasticsearch::make_client();
        $elasticaIndex = $esclient->getIndex(PluginSearchElasticsearch::get_write_indexname());
        // Create the index, deleting it first if it already exists
        $elasticaIndex->create(
            array(
// Uncomment the following if you want to overwrite the number of shards/replicas set by ElasticSearch's
// default, or the settings specified in elasticsearch.yml file.
//                'number_of_shards' => 5,
//                'number_of_replicas' => 1,
                'analysis' => array(
                    'analyzer' => array(
                        'mahara_analyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'pattern', // define token separators as any non-alphanumeric character
                            'filter' => array('standard', 'lowercase', 'stop', 'maharaSnowball'),
                            'char_filter' => array('maharaHtml'),
                        ),
                    ),
                    'filter' => array(
                        'maharaSnowball' => array(
                            'type' => 'snowball',
                            'language' => 'English',
                        )
                    ),
                    'char_filter' => array(
                        'maharaHtml' => array(
                            'type' => 'html_strip',
                            'read_ahead' => '1024',
                        )
                    ),
                )
            ),
            true
        );
    }

    /**
     * Clears the indexing queue table for this searchtype, and then re-loads it with every matching item in the
     * database.
     * @param string $type The elasticsearch search type
     * @param string $artefacttype (Optional) If the search type is Artefact, this is the artefact subtype
     */
    public static function requeue_searchtype_contents($type, $artefacttype = null) {

        $condition = '';
        $insert_sql = "INSERT INTO {search_elasticsearch_queue} (itemid, type) SELECT id, '" . $type . "' FROM {" . $type . "}";
        if ($type == 'view') {
            $insert_sql .= ' WHERE (id != 0 AND (owner != 0 OR "group" !=0))';
        }
        else if ($type == 'usr') {
            $insert_sql .= ' WHERE id != 0';
        }
        else if ($type == 'block_instance') {
            $insert_sql .= " WHERE blocktype = 'text'";
        }

        if ($type == 'artefact') {
            $condition = " AND artefacttype IN ";
            $condition .= isset($artefacttype) ? "('$artefacttype')" : self::artefacttypes_filter_string();

            $insert_sql = "INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype)
                            SELECT id, 'artefact', artefacttype FROM {artefact} WHERE " . substr($condition, 4);
        }

        $sql = "DELETE FROM {search_elasticsearch_queue} WHERE type = ? $condition";
        execute_sql($sql, array($type));

        execute_sql($insert_sql);
    }


    /**
     *   Check if access changed between the last time the function was called (view_access table) and
     *   add items to the queue
     */
    public static function add_to_queue_access($last_run, $timestamp) {

        $artefacttypes_str = self::artefacttypes_filter_string();

        execute_sql("
            INSERT INTO {search_elasticsearch_queue} (itemid, type)
            SELECT view, 'view'
            FROM {view_access} WHERE startdate BETWEEN '{$last_run}' AND '{$timestamp}'
            OR stopdate BETWEEN  '{$last_run}' AND '{$timestamp}'
            ;"
        );

        execute_sql("
            INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype)
            SELECT var.artefact, 'artefact', a.artefacttype
            FROM {view_access} vac
            INNER JOIN {view_artefact} var ON var.view = vac.view
            INNER JOIN {artefact} a ON var.artefact = a.id
            WHERE
                (
                    vac.startdate BETWEEN '{$last_run}' AND '{$timestamp}'
                    OR vac.stopdate BETWEEN  '{$last_run}' AND '{$timestamp}'
                )
                AND a.artefacttype IN {$artefacttypes_str}
            ;"
        );
    }

    /**
     * Delete the trigger functions used by elasticsearch. Since we're deleting by
     * CASCADE, this will also delete the triggers which use these functions
     */
    public static function drop_trigger_functions() {
        if (is_postgres()) {
            $sql = 'DROP FUNCTION IF EXISTS {search_elasticsearch_queue_trigger}() CASCADE;';
            execute_sql($sql);

            $sql = 'DROP FUNCTION IF EXISTS {search_elasticsearch_queue_artefact_trigger}() CASCADE;';
            execute_sql($sql);

            $sql = 'DROP FUNCTION IF EXISTS {search_elasticsearch_queue2_trigger}() CASCADE;';
            execute_sql($sql);
        }
        else {
            $sql = 'DROP PROCEDURE IF EXISTS {search_elasticsearch_queue_trigger};';
            execute_sql($sql);

            $sql = 'DROP PROCEDURE IF EXISTS {search_elasticsearch_queue_artefact_trigger};';
            execute_sql($sql);

            $sql = 'DROP PROCEDURE IF EXISTS {search_elasticsearch_queue2_trigger};';
            execute_sql($sql);
        }
    }

    /**
     * Creates trigger functions used by elasticsearch. These detect indexable records being inserted, deleted, or updated, and put
     * records in search_elasticsearch_queue to tell the cron job to pass them on to the elasticsearch server.
     *
     * Note that these functions won't do anything until you call create_trigger(). The same trigger function is used
     * for multiple triggers, which is the reason why it's in this separate PHP function.
     *
     * NOTE that calling this function deletes ALL the elasticsearch triggers, because it deletes the trigger
     * functions, and that cascades to deleting the triggers that use them. So make sure you recreate the
     * triggers you want to retain, after you call this function.
     */
    public static function create_trigger_functions() {
        // Delete the trigger functions first.
        // NOTE: This also deletes ALL the elasticsearch triggers.
        self::drop_trigger_functions();

        $artefacttypes_str = self::artefacttypes_filter_string();

        // We'll use this to trim the prefix from table names before inserting them into
        // search_elasticsearch_queue.type
        $dbprefix = get_config('dbprefix');
        $prefixlength = strlen($dbprefix);
        if ($prefixlength) {
            $tablewithoutprefix = (is_postgres() ? "RIGHT(TG_TABLE_NAME, -{$prefixlength})" : "SUBSTRING(tablename, " . ($prefixlength + 1) . ")");
        }
        else {
            $tablewithoutprefix= (is_postgres() ? 'TG_TABLE_NAME' : "tablename");
        }

        //----------------------------------------------------------------------------------------------------
        // search_elasticsearch_queue_trigger
        // For the usr, interaction_instance, interaction_forum_post, group, and view types
        //  - Set it to monitor the table the type is named after
        //  - On an INSERT, UPDATE, or DELETE, just inserts the record ID and the name of the type in the queue table
        //  - When you modify a view, you also insert a record for every artefact in that view
        if (is_postgres()) {
            $sql = 'CREATE FUNCTION {search_elasticsearch_queue_trigger}() RETURNS trigger AS $search_elasticsearch_queue_trigger$
                BEGIN
                    IF (TG_OP=\'DELETE\') THEN
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = OLD.id AND type = '.$tablewithoutprefix.') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (OLD.id, '.$tablewithoutprefix.');
                        END IF;
                        IF (TG_TABLE_NAME=\'' . $dbprefix . 'view\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT u.id, \'usr\' AS type FROM {usr} u
                            INNER JOIN {view} v ON v.owner = u.id
                            WHERE v.type = \'profile\'
                                AND v.id = OLD.id
                                AND NOT EXISTS (
                                    SELECT q.id FROM {search_elasticsearch_queue} q
                                    WHERE q.type = \'usr\' AND q.itemid = u.id
                                );
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT va.artefact, \'artefact\' AS type
                            FROM {view_artefact} va
                            INNER JOIN {artefact} a ON va.artefact = a.id
                            WHERE va.view = OLD.id
                                AND va.artefact NOT IN (SELECT itemid FROM {search_elasticsearch_queue} WHERE type = \'artefact\')
                                AND a.artefacttype IN ' . $artefacttypes_str .';
                        END IF;
                        RETURN OLD;
                    ELSE
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = NEW.id AND type = ' . $tablewithoutprefix . ') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (NEW.id, ' . $tablewithoutprefix . ');
                        END IF;
                        IF (TG_TABLE_NAME=\'' . $dbprefix . 'view\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT u.id, \'usr\' AS type FROM {usr} u
                            INNER JOIN {view} v ON v.owner = u.id
                            WHERE v.type = \'profile\'
                                AND v.id = NEW.id
                                AND NOT EXISTS (
                                    SELECT q.id FROM {search_elasticsearch_queue} q
                                    WHERE q.type = \'usr\' AND q.itemid = u.id
                                );
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT va.artefact, \'artefact\' AS type
                            FROM {view_artefact} va
                            INNER JOIN {artefact} a ON va.artefact = a.id
                            WHERE va.view = NEW.id
                            AND va.artefact NOT IN (SELECT itemid FROM {search_elasticsearch_queue} WHERE type = \'artefact\')
                            AND a.artefacttype IN ' . $artefacttypes_str .';
                        END IF;
                        RETURN NEW;
                    END IF;
                END;
                $search_elasticsearch_queue_trigger$ LANGUAGE plpgsql;';
        }
        else {
            $sql = 'CREATE PROCEDURE {search_elasticsearch_queue_trigger}
                        (tablename varchar(64), operation varchar(10), oldid bigint, newid bigint)
                BEGIN
                    IF (operation=\'delete\') THEN
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = oldid AND type = '.$tablewithoutprefix.') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (oldid, '.$tablewithoutprefix.');
                        END IF;
                        IF (tablename=\'' . $dbprefix . 'view\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT u.id, \'usr\' AS type FROM {usr} u
                            INNER JOIN {view} v ON v.owner = u.id
                            WHERE v.type = \'profile\'
                                AND v.id = oldid
                                AND NOT EXISTS (
                                    SELECT q.id FROM {search_elasticsearch_queue} q
                                    WHERE q.type = \'usr\' AND q.itemid = u.id
                                );
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT va.artefact, \'artefact\' AS type
                            FROM {view_artefact} va
                            INNER JOIN {artefact} a ON va.artefact = a.id
                            WHERE va.view = oldid
                                AND va.artefact NOT IN (SELECT itemid FROM {search_elasticsearch_queue} WHERE type = \'artefact\')
                                AND a.artefacttype IN ' . $artefacttypes_str .';
                        END IF;
                    ELSE
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = newid AND type = ' . $tablewithoutprefix . ') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (newid, ' . $tablewithoutprefix . ');
                        END IF;
                        IF (tablename=\'' . $dbprefix . 'view\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT u.id, \'usr\' AS type FROM {usr} u
                            INNER JOIN {view} v ON v.owner = u.id
                            WHERE v.type = \'profile\'
                                AND v.id = newid
                                AND NOT EXISTS (
                                    SELECT q.id FROM {search_elasticsearch_queue} q
                                    WHERE q.type = \'usr\' AND q.itemid = u.id
                                );
                            INSERT INTO {search_elasticsearch_queue} (itemid, type)
                            SELECT va.artefact, \'artefact\' AS type
                            FROM {view_artefact} va
                            INNER JOIN {artefact} a ON va.artefact = a.id
                            WHERE va.view = newid
                            AND va.artefact NOT IN (SELECT itemid FROM {search_elasticsearch_queue} WHERE type = \'artefact\')
                            AND a.artefacttype IN ' . $artefacttypes_str .';
                        END IF;
                    END IF;
                END';
        }
        execute_sql($sql);

        //----------------------------------------------------------------------------------------------------
        // search_elasticsearch_queue_artefact_trigger
        // For the artefact type
        //   - Set it to monitor the artefact table
        //   - The main difference from the search_elasticsearch_queue_trigger, is that it also populates the
        //     artefacttype column in the queue table.
        if (is_postgres()) {
            $sql = 'CREATE FUNCTION {search_elasticsearch_queue_artefact_trigger}() RETURNS trigger AS $search_elasticsearch_queue_artefact_trigger$
                BEGIN
                    IF (TG_OP=\'DELETE\') THEN
                        IF (OLD.artefacttype IN ' . $artefacttypes_str . ') AND NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = OLD.id AND type = ' . $tablewithoutprefix . ') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype) VALUES (OLD.id, ' . $tablewithoutprefix . ', OLD.artefacttype);
                        END IF;
                        RETURN OLD;
                    ELSE
                        IF (NEW.artefacttype IN ' . $artefacttypes_str . ') AND NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = NEW.id AND type = '.$tablewithoutprefix.') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype) VALUES (NEW.id, '.$tablewithoutprefix.', NEW.artefacttype);
                        END IF;
                        RETURN NEW;
                    END IF;
                END;
                $search_elasticsearch_queue_artefact_trigger$ LANGUAGE plpgsql;';
        }
        else {
            $sql = 'CREATE PROCEDURE {search_elasticsearch_queue_artefact_trigger}
                        (tablename varchar(64), operation varchar(10), oldid bigint, oldartefacttype varchar(255), newid bigint, newartefacttype varchar(255))
                BEGIN
                    IF (operation=\'delete\') THEN
                        IF (oldartefacttype IN ' . $artefacttypes_str . ') AND NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = oldid AND type = ' . $tablewithoutprefix . ') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype) VALUES (oldid, ' . $tablewithoutprefix . ', oldartefacttype);
                        END IF;
                    ELSE
                        IF (newartefacttype IN ' . $artefacttypes_str . ') AND NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = newid AND type = ' . $tablewithoutprefix . ') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype) VALUES (newid, ' . $tablewithoutprefix . ', newartefacttype);
                        END IF;
                    END IF;
                END';
        }
        execute_sql($sql);

        //----------------------------------------------------------------------------------------------------
        // search_elasticsearch_queue2_trigger
        //   - For the view_artefact table
        //   - Whenever that table is modified, add a record into the queue table for the artefact mentioned
        if (is_postgres()) {
            $sql = 'CREATE FUNCTION {search_elasticsearch_queue2_trigger}() RETURNS trigger AS $search_elasticsearch_queue2_trigger$
                BEGIN
                    IF (TG_OP=\'DELETE\') THEN
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = OLD.artefact AND type = \'artefact\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (OLD.artefact, \'artefact\');
                        END IF;
                        RETURN OLD;
                    ELSE
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = NEW.artefact AND type = \'artefact\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (NEW.artefact, \'artefact\');
                        END IF;
                        RETURN NEW;
                    END IF;
                END;
                $search_elasticsearch_queue2_trigger$ LANGUAGE plpgsql;';
        }
        else {
            $sql = 'CREATE PROCEDURE {search_elasticsearch_queue2_trigger}
                        (tablename varchar(64), operation varchar(10), oldartefact bigint, newartefact bigint)
                BEGIN
                    IF (operation=\'delete\') THEN
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = oldartefact AND type = \'artefact\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (oldartefact, \'artefact\');
                        END IF;
                    ELSE
                        IF NOT EXISTS (SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = newartefact AND type = \'artefact\') THEN
                            INSERT INTO {search_elasticsearch_queue} (itemid, type) VALUES (newartefact, \'artefact\');
                        END IF;
                    END IF;
                END';
        }
        execute_sql($sql);
    }

    /**
     * Drop the triggers for a Mahara search type.
     *
     * @param string $type the Mahara searchtype to drop the trigger(s) for
     */
    public static function drop_triggers($type) {
        // The artefact type uses different trigger functions than the other types
        if ($type == 'artefact') {
            if (is_postgres()) {
                $sql = "DROP FUNCTION IF EXISTS {search_elasticsearch_{$type}}() CASCADE;";
                execute_sql($sql);

                $sql = "DROP FUNCTION IF EXISTS {search_elasticsearch_view_artefact}() CASCADE;";
                execute_sql($sql);
            }
            else {
                // need to drop 3 triggers for INSERT, UPDATE and DELETE
                foreach (self::$mysqltriggeroperations as $operation) {
                    $sql = "DROP TRIGGER IF EXISTS {search_elasticsearch_{$type}_{$operation}};";
                    execute_sql($sql);

                    $sql = "DROP TRIGGER IF EXISTS {search_elasticsearch_view_artefact_{$operation}};";
                    execute_sql($sql);
                }
            }
        }
        else {
            if (is_postgres()) {
                $sql = "DROP FUNCTION IF EXISTS {search_elasticsearch_{$type}}() CASCADE;";
                execute_sql($sql);
            }
            else {
                // need to drop 3 triggers for INSERT, UPDATE and DELETE
                foreach (self::$mysqltriggeroperations as $operation) {
                    $sql = "DROP TRIGGER IF EXISTS {search_elasticsearch_{$type}_{$operation}}";
                    execute_sql($sql);
                }
            }
        }
    }

    /**
     * Creates the trigger for a Mahara search type. The actual trigger function was created by create_trigger_functions();
     * If we were to make the ElasticsearchType into a more fully fleshed-out plugin, we'd want to devolve
     * this to the plugin itself.
     *
     * @param string $type The Mahara searchtype to (re) create the trigger for
     */
    public static function create_triggers($type) {
        self::drop_triggers($type);
        // The artefact type uses different trigger functions than the other types
        if ($type == 'artefact') {
            if (is_postgres()) {
                $sql = "CREATE TRIGGER {search_elasticsearch_{$type}} BEFORE INSERT OR UPDATE OR DELETE ON {{$type}}
                        FOR EACH ROW EXECUTE PROCEDURE {search_elasticsearch_queue_artefact_trigger}()";
                execute_sql($sql);

                $sql = "CREATE TRIGGER {search_elasticsearch_view_artefact} BEFORE INSERT OR UPDATE OR DELETE ON {view_artefact}
                        FOR EACH ROW EXECUTE PROCEDURE {search_elasticsearch_queue2_trigger}()";
                execute_sql($sql);
            }
            else {
                foreach (self::$mysqltriggeroperations as $operation) {
                    $oldid = ($operation == 'insert' ? 'null' : 'OLD.id');
                    $newid = ($operation == 'delete' ? 'null' : 'NEW.id');
                    $oldartefacttype = ($operation == 'insert' ? 'null' : 'OLD.artefacttype');
                    $newartefacttype = ($operation == 'delete' ? 'null' : 'NEW.artefacttype');
                    $oldartefact = ($operation == 'insert' ? 'null' : 'OLD.artefact');
                    $newartefact = ($operation == 'delete' ? 'null' : 'NEW.artefact');
                    // For inserts, the NEW.id is not available until AFTER the record is insereted.
                    $triggertime = ($operation == 'insert' ? 'AFTER' : 'BEFORE');
                    // To remove confusion, include the table prefix if it exists as we'll be
                    // passing the actual table name to the stored procedure.
                    $tablename = get_config('dbprefix') . $type;
                    $viewtablename = get_config('dbprefix') . 'view_artefact';

                    // create 3 triggers on the artefact table.
                    $sql = "CREATE TRIGGER {search_elasticsearch_{$type}_{$operation}} {$triggertime} {$operation} ON {{$type}}
                            FOR EACH ROW CALL {search_elasticsearch_queue_artefact_trigger}('{$tablename}', '{$operation}', {$oldid}, {$oldartefacttype}, {$newid}, {$newartefacttype})";
                    execute_sql($sql);

                    // create 3 triggers on the view_artefact table.
                    $sql = "CREATE TRIGGER {search_elasticsearch_view_artefact_{$operation}} {$triggertime} {$operation} ON {view_artefact}
                            FOR EACH ROW CALL {search_elasticsearch_queue2_trigger}('{$viewtablename}', '{$operation}', {$oldartefact}, {$newartefact})";
                    execute_sql($sql);
                }
            }
        }
        else {
            if (is_postgres()) {
                $sql = "CREATE TRIGGER {search_elasticsearch_{$type}} BEFORE INSERT OR UPDATE OR DELETE ON {{$type}}
                    FOR EACH ROW EXECUTE PROCEDURE {search_elasticsearch_queue_trigger}()";
                execute_sql($sql);
            }
            else {
                // create the 3 triggers on the table.
                foreach (self::$mysqltriggeroperations as $operation) {
                    $oldid = ($operation == 'insert' ? 'null' : 'OLD.id');
                    $newid = ($operation == 'delete' ? 'null' : 'NEW.id');
                    // For inserts, the NEW.id is not available until AFTER the record is insereted.
                    $triggertime = ($operation == 'insert' ? 'AFTER' : 'BEFORE');
                    // To remove confusion, include the table prefix if it exists as we'll be
                    // passing the actual table name to the stored procedure.
                    $tablename = get_config('dbprefix') . ($type);

                    $sql = "CREATE TRIGGER {search_elasticsearch_{$type}_{$operation}} {$triggertime} {$operation} ON {{$type}}
                            FOR EACH ROW CALL {search_elasticsearch_queue_trigger}('{$tablename}', '{$operation}', {$oldid}, {$newid})";
                    execute_sql($sql);
                }
            }
        }
    }

    public static function artefacttypes_filter_string() {

        $artefacttypes = explode(',', get_config_plugin('search', 'elasticsearch', 'artefacttypes'));
                $artefacttypes_str = '';
                foreach ($artefacttypes as $artefacttype) {
                $artefacttypes_str .= '\'' . $artefacttype . '\', ';
        }
        $artefacttypes_str = '(' . substr($artefacttypes_str, 0, strlen($artefacttypes_str)-2) . ')';

        return $artefacttypes_str;

    }
}
