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
require_once(get_config('libroot') . '/elasticsearch/autoload.php');

use Elasticsearch\ClientBuilder;

function __autoload_elasticsearchtypes ($class) {
    if (substr($class, 0, 18) == 'ElasticsearchType_') {
        $file = __DIR__ . '/type/' . $class . '.php';
        if (file_exists($file)) {
            require_once($file);
        }
    }
}
spl_autoload_register('__autoload_elasticsearchtypes', true);

/**
 * The internal search plugin which searches against the
 * Mahara database.
 */
class PluginSearchElasticsearch extends PluginSearch {

    /**
     * The minimum version of elasticsearch this plugin is compatible with.
     */
    const elasticsearch_version = '5.0';

    /**
     * The version of elasticsearch-php this plugin is compatible with.
     */
    const elasticsearchphp_version = '5.0';

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
     */        // log contents of the result of var_dump( $object )
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
     * This function determines whether the plugin is current        // log contents of the result of var_dump( $object )ly available to be chosen
     * as the sitewide search plugin (i.e. get_config('searchplugin'))
     */
    public static function is_available_for_site_setting() {
        return true;
    }

    /**
     * This function determines if we can connect to the elasticsearch server with supplied host and port
     */
    public static function can_connect() {
        list ($status, $info) = self::elasticsearch_server();
        return $status;
    }

    /**
     * This function returns elasticsearch server information at supplied host and port
     * We can't use the $ESClient as we need to check if we are trying to connect to either an older or current server so will run curl commands directly
     * @param string $option An optional param to get status about a specific status, eg cluster health
     * @param string $index  An optional param to get status about a specific status for a particular index, eg indices status
     * @return array containing $canconnect bool    - whether we can connect to elasticsearch at host/port
     *                          $server     object  - information about the server request
     */
    public static function elasticsearch_server($option=null, $index=null) {
        $clientops = self::get_client_config('write');
        $host = $clientops['hosts'][0];
        $url = $host['host'] . ':' . $host['port'];
        if (!empty($host['username'])) {
            $url = $host['username'] . ':' . $host['password'] . '@' . $url;
        }
        if (!empty($host['scheme'])) {
            $url = $host['scheme'] . '://' . $url;
        }

        switch ($option) {
            case "clusterhealth":
                $url .= '/_cluster/health';
                break;
            case "indexhealth":
                $url .= '/_cat/indices?format=json';
                break;
            default:
                $clientopts['curlopts'][CURLOPT_NOBODY] = true;
        }

        $url .= '/' . get_config_plugin('search', 'elasticsearch', 'indexname') . '?format=json';
        $curlopts = array(CURLOPT_URL => $url) + $clientops['curlopts'];
        $server = mahara_http_request($curlopts, true);
        $canconnect = false;
        if (!empty($server->info) && !empty($server->info['http_code'])) {
            if ($server->info['http_code'] != '200') {
                $server->error = get_string('servererror', 'search.elasticsearch', $server->info['http_code']);
            }
            else {
                $canconnect = true;
            }
        }
        if (!empty($server->data)) {
            $server->data = json_decode($server->data);
            if (!empty($server->data->error)) {
                $server->error = $server->data->status . ': ' . $server->data->error->reason;
            }
            else {
                if ($index && is_array($server->data)) {
                    // we need to find the data for particular index
                    $thisindex = null;
                    foreach ($server->data as $key => $data) {
                        if (isset($data->index) && $data->index == $index) {
                            $thisindex = $server->data[$key];
                            break;
                        }
                    }
                    $server->data = $thisindex;
                }
                if (!empty($server->data->version) && !empty($server->data->version->number)) {
                    if (version_compare($server->data->version->number, self::elasticsearch_version) === -1) {
                        $server->error = get_string('elasticsearchtooold', 'search.elasticsearch', $server->data->version->number, self::elasticsearch_version);
                    }
                }
            }
        }
        return array($canconnect, $server);
    }

    /**
     * This function determines whether the plugin allows a search box to display for
     * non-logged in users - only useful if results returned by search are allowed to
     * be seen by the public
     */
    public static function publicform_allowed() {
        return true;
    }

    /**        // log contents of the result of var_dump( $object )
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
                            'class' => 'btn-secondary input-group-append',
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

    public static function get_formatted_notice($notice, $type) {
        $smarty = smarty_core();
        $smarty->assign('notice', $notice);
        $html = $smarty->fetch('Search:elasticsearch:config' . $type . '.tpl');
        unset($smarty);
        return $html;
    }

    public static function get_config_options() {
        $enabledhtml = '';
        $state = 'ok';
        list($status, $server) = self::elasticsearch_server();
        if (!$status) {
            $state = 'notice';
            $notice = get_string('noticenotactive', 'search.elasticsearch', get_config_plugin('search', 'elasticsearch', 'host'), get_config_plugin('search', 'elasticsearch', 'port'));
            if (!empty($server->error)) {
                $notice = $server->error;
            }
            $enabledhtml .= self::get_formatted_notice($notice, 'warning');
        }
        else {
            list($status, $health) = self::elasticsearch_server('clusterhealth');
            if (!empty($health->data) && $health->data->status != 'green') {
                $enabledhtml .= self::get_formatted_notice(get_string('clusterstatus', 'search.elasticsearch', $health->data->status, $health->data->unassigned_shards), 'warning');
                $state = 'notice';
            }
            $index = get_config_plugin('search', 'elasticsearch', 'indexname');
            list($status, $health) = self::elasticsearch_server('indexhealth', $index);
            if (!empty($health->data)) {
                if (isset($health->data->status) && $health->data->status == '403') {
                    $enabledhtml .= self::get_formatted_notice(get_string('indexstatusunknown', 'search.elasticsearch', $index, $health->data->status), 'warning');
                }
                else if (isset($health->data->health) && $health->data->health != 'green') {
                    $enabledhtml .= self::get_formatted_notice(get_string('indexstatusbad', 'search.elasticsearch', $index, $health->data->health), 'warning');
                }
                $state = 'notice';
            }
        }
        if (get_config('searchplugin') == 'elasticsearch') {
            $enabledhtml .= self::get_formatted_notice(get_string('noticeenabled', 'search.elasticsearch', get_config('wwwroot') . 'admin/site/options.php?fs=searchsettings'), $state);
        }
        else {
            $enabledhtml .= self::get_formatted_notice(get_string('noticenotenabled', 'search.elasticsearch', get_config('wwwroot').'admin/site/options.php?fs=searchsettings'), 'warning');
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
                'scheme' => array(
                    'title'        => get_string('scheme', 'search.elasticsearch'),
                    'description'  => get_string('schemedescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => (
                            get_config_plugin('search', 'elasticsearch', 'scheme')
                            ? get_config_plugin('search', 'elasticsearch', 'scheme')
                            : get_string('confignotset', 'search.elasticsearch')
                    ),
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
                'indexingusername' => array(
                    'title'        => get_string('indexingusername', 'search.elasticsearch'),
                    'description'  => get_string('indexingusernamedescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => (
                            get_config_plugin('search', 'elasticsearch', 'indexingusername')
                            ? get_config_plugin('search', 'elasticsearch', 'indexingusername')
                            : get_string('confignotset', 'search.elasticsearch')
                    ),
                    'help'         => true,
                ),
                'indexingpassword' => array(
                    'title'        => get_string('indexingpassword', 'search.elasticsearch'),
                    'description'  => get_string('indexingpassworddescription', 'search.elasticsearch'),
                    'type'         => 'html',
                    'value'        => (
                            get_config_plugin('search', 'elasticsearch', 'indexingpassword')
                            ? get_string('passwordlength', 'search.elasticsearch', strlen(get_config_plugin('search', 'elasticsearch', 'indexingpassword')))
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
                'shards' => array(
                    'title'        => get_string('shards', 'search.elasticsearch'),
                    'description'  => get_string('shardsdescription', 'search.elasticsearch'),
                    'type'         => 'text',
                    'defaultvalue' => get_config_plugin('search', 'elasticsearch', 'shards'),
                ),
                'replicashards' => array(
                    'title'        => get_string('replicashards', 'search.elasticsearch'),
                    'description'  => get_string('replicashardsdescription', 'search.elasticsearch'),
                    'type'         => 'text',
                    'defaultvalue' => get_config_plugin('search', 'elasticsearch', 'replicashards'),
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
                );
            }
            // And on the end, a special one to reset all the indexes.
            $resetelements['allreset'] = array(
                'title' => get_string('resetallindexes', 'search.elasticsearch'),
                'type' => 'submit',
                'class' => 'btn-secondary',
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
        // Set the shard / replica values
        $shards = (int) $values['shards'];
        $shards = empty($shards) ? 5 : $shards; // we can't have no shards so set to default
        set_config_plugin('search', 'elasticsearch', 'shards', (int) $shards);
        set_config_plugin('search', 'elasticsearch', 'replicashards', (int) $values['replicashards']);

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

            error_log("finished resetting.");

            // Send the first batch of records to the elasticsearch server now, for instant gratification
            self::index_queued_items();

            error_log("finished indexing queued items");

            // free the cron lock
            delete_records('config', 'field', '_cron_lock_search_elasticsearch_cron', 'value', $start);
        }
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
        $mappings = array();
        // (re)create the mappings and the overall site index
        foreach ($enabledtypes as $type) {
            ElasticsearchIndexing::create_triggers($type);
            ElasticsearchIndexing::requeue_searchtype_contents($type);
            $ES_class = 'ElasticsearchType_' . $type;
            if ($ES_class::$mappingconfv6 === false) {
                error_log("mapping $type missing - will ignore");
                continue;
            }
            $mappings[]= $ES_class::$mappingconfv6;
        }
        self::set_mapping($mappings);
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('search', 'elasticsearch', 'host', '127.0.0.1');
            set_config_plugin('search', 'elasticsearch', 'port', '9200');
            set_config_plugin('search', 'elasticsearch', 'indexname', 'mahara');
            set_config_plugin('search', 'elasticsearch', 'analyzer', 'mahara_analyzer');
            set_config_plugin('search', 'elasticsearch', 'types', 'usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,collection');
            set_config_plugin('search', 'elasticsearch', 'cronlimit', '50000');
            set_config_plugin('search', 'elasticsearch', 'shards', 5);
            set_config_plugin('search', 'elasticsearch', 'replicashards', 1);
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
                'minute'       => '4-59/5',
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
     * Creates the "mapping" for the 'doc' mappingtype on the elasticsearch server
     *
     * @param array $mappings Array of old style mapping types that we want to merge to make one type
     */
    private static function set_mapping($mappings) {

        if (!is_array($mappings) || empty($mappings)) {
            error_log('wrong mapping info');
            return false;
        }
        $docmapping = array();
        foreach ($mappings as $maptype) {
            foreach ($maptype as $k => $v) {
                if (!isset($docmapping[$k])) {
                    $docmapping[$k] = $v;
                }
                else {
                    $docmapping[$k] = array_replace_recursive($docmapping[$k], $v);
                }
            }
        }
        $ESAnalyzer = get_config_plugin('search', 'elasticsearch', 'analyzer');
        // In version 6.x there is no catchall '_all' field so we now map '$type_all' for the different types
        // and instead of doing full search on '_all' we do multi_match on 'catch_all' to achieve same functionality.
        $docmapping['catch_all'] = array(
            'type' => 'text',
            'analyzer'  => $ESAnalyzer,
            'search_analyzer' => $ESAnalyzer,
            'store' => true
        );

        $mappingparams = array(
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'type' => 'doc', // Only allowed one type mapping in version 6.x
            'body' => array(
                'doc' => array(
                    '_source' => array(
                        'enabled' => true
                    ),
                    'properties' => $docmapping
                )
            )
        );
        $ESClient = self::make_client('write');
        // Set mapping on index type.
        $ESClient->indices()->putMapping($mappingparams);
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

        $ESClient = self::make_client('write');
        /*
        $indexname = self::get_write_indexname();
        $elasticaIndex = $elasticaClient->getIndex($indexname);
        */
        $artefacttypesmap_array = self::elasticsearchartefacttypesmap_to_array();

        if ($records) {
            // TODO: translate preprocess_queued_items also.
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
                    function($records, $type) use ($ESClient) {
                        $params = array();
                        foreach ($records as $record) {
                            $params['body'][] = [
                                    'delete' => [
                                            '_index' => PluginSearchElasticsearch::get_write_indexname(),
                                            '_type'  => 'doc',
                                            '_id'    => $type.$record,
                                    ],
                            ];
                        }

                    return $ESClient->bulk($params);
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
                // TODO: translate send_queued_items also.
                self::send_queued_items_in_bulk(
                    $documents,
                    function($records, $type) use ($ESClient) {
                        $params = array();
                        foreach ($records as $record) {
                            $params['body'][] = [
                                'index' => [
                                    '_index' => PluginSearchElasticsearch::get_write_indexname(),
                                    '_type'  => 'doc',
                                    '_id'    => $type.$record['id'],
                                ],
                            ];
                            $record['body']['type'] = $record['type'];
                            $params['body'][] = (array)$record['body'];
                        }

                        return $ESClient->bulk($params);
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
                    function($record, $type) use ($ESClient) {
                        $params = array(
                                'index' => PluginSearchElasticsearch::get_write_indexname(),
                                'type'  => 'doc',
                                'id'    => $type.$record,
                        );

                        return $ESClient->delete($params);
                    }
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
                    function($record, $type) use ($ESClient) {
                        $record['body']['type'] = $record['type'];
                        $params = array(
                            'index' => PluginSearchElasticsearch::get_write_indexname(),
                            'type'  => 'doc',
                            'id'    => $type.$record['id'],
                            'body'  => (array)$record['body'],
                        );

                        return $ESClient->index($params);
                    }
                );
            }
        }

        // Refresh Index
        $ESClient->indices()->refresh(array('index' => PluginSearchElasticsearch::get_write_indexname()));
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
                $documents[$record->type][$record->id] = array(
                    'index' => PluginSearchElasticsearch::get_write_indexname(),
                    'type'  => $record->type,
                    'id'    => $record->itemid,
                    'body'  => $item->getMapping(),
                );
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

                    $ESError=false;
                    if ( isset( $response['errors'] ) ) {
                        $ESError=$response['errors'];
                    }

                    if (!empty($ESError)) {
                        log_warn("Error from Elasticsearch trying to send bulk request at time {$time}: " . $ESError);
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
    private static function send_queued_items_individually($documents, $processfunction) {
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
                    $ESError=false;
                    if ( isset( $response['errors'] ) ) {
                        $ESError=$response['errors'];
                    }

                    if ( !empty( $ESError ) ) {
                        $errorcount++;
                        log_warn("Error from Elasticsearch trying to send individual record {$queueid}: " . $ESError);
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

    public static function search_events ($options=array(), $limit = 10, $offset = 0) {
        global $USER;
        return ElasticsearchType_event_log::search($options, $limit, $offset, $USER);
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

   public static function get_client_config($type='read') {
       $host = get_config_plugin('search', 'elasticsearch', 'host');
       $port = get_config_plugin('search', 'elasticsearch', 'port');

       $hosts = array(
                    array(
                        'host' => $host,
                        'port' => $port
                    )
       );

       // Build array of curlopts
       $elasticclientcurlopts = [];
       $elasticclientcurlopts[CURLOPT_CONNECTTIMEOUT] = 3;

       if ($username = get_config_plugin('search', 'elasticsearch', 'username')) {
           $password = get_config_plugin('search', 'elasticsearch', 'password');
           if ($type == 'write' && $indexingusername = get_config_plugin('search', 'elasticsearch', 'indexingusername')) {
               $username = $indexingusername;
               $password = get_config_plugin('search', 'elasticsearch', 'indexingpassword');
           }
           $hosts[0]['username'] = $username;
           $hosts[0]['password'] = $password;
           $elasticclientcurlopts[CURLOPT_USERPWD] = $username . ':' . $password;
       }

       if (get_config_plugin('search', 'elasticsearch', 'scheme')) {
           $hosts[0]['scheme'] = get_config_plugin('search', 'elasticsearch', 'scheme');
           if (!get_config('productionmode') && get_config_plugin('search', 'elasticsearch', 'ignoressl')) {
               // Ignore verifying the SSL certificate
               $elasticclientcurlopts[CURLOPT_SSL_VERIFYHOST] = false;
               $elasticclientcurlopts[CURLOPT_SSL_VERIFYPEER] = false;
           }
       }

       if (get_config('proxyaddress')) {
           $elasticclientcurlopts[CURLOPT_PROXY] = get_config('proxyaddress');
           $elasticclientcurlopts[CURLOPT_HTTPHEADER] = ['Transfer-Encoding: chunked'];
           if (get_config('proxyauthmodel') && get_config('proxyauthcredentials')) {
               // @TODO: actually do something with $proxy_authmodel.
               $elasticclientcurlopts[CURLOPT_PROXYUSERPWD] = get_config('proxyauthcredentials');
           }
       }
       return array('hosts' => $hosts, 'curlopts' => $elasticclientcurlopts);
   }

   /**
    * Creates an \Elastica\Client object, filling in the host and
    * port with the values from the elasticsearch plugin's admin screen.
    * If you wanted to make other changes to how we connect to elasticsearch,
    * this would be a good place to do it.
    *
    * @return \Elastica\Client
    */
   public static function make_client($type='read') {
       $clientopts = self::get_client_config($type);

       $clientBuilder = ClientBuilder::create();

       // php versions < 5.6.6 dont have JSON_PRESERVE_ZERO_FRACTION defined
       if (version_compare(phpversion(), '5.6.6', '<') || !defined('JSON_PRESERVE_ZERO_FRACTION')) {
           $clientBuilder->setHosts($clientopts['hosts'])->setConnectionParams(['client' => ['curl' => $clientopts['curlopts']]])->allowBadJSONSerialization();
       }
       else {
           $clientBuilder->setHosts($clientopts['hosts'])->setConnectionParams(['client' => ['curl' => $clientopts['curlopts']]]);
       }
       $client = $clientBuilder->build();

       return $client;
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
       $smarty->assign('data', !empty($data['data']) ? $data['data'] : null);

       $params = array();
       if (isset($data['query'])) {
           $params['query'] = $data['query'];
       }
       if (isset($data['selected'])) {
           $params['mainfacetterm'] = $data['selected'];
           $smarty->assign('selected', $data['selected']);
       }
       if (isset($data['content-filter-selected'])) {
           $params['secfacetterm'] = $data['content-filter-selected'];
           $smarty->assign('contentfilterselected', $data['content-filter-selected']);
       }
       if (isset($data['owner-filter-selected'])) {
           $params['owner'] = $data['owner-filter-selected'];
           $smarty->assign('owner', $data['owner-filter-selected']);
       }
       if (isset($data['tagsonly'])) {
           $params['tagsonly'] = $data['tagsonly'];
           $smarty->assign('tagsonly', $data['tagsonly']);
       }
       if (isset($data['sort'])) {
           $params['sort'] = $data['sort'];
           $smarty->assign('sort', $data['sort']);
       }
       if (isset($data['license'])) {
           $params['license'] = $data['license'];
           $smarty->assign('license', $data['license']);
       }
       if (!isset($data['count'])) {
           $data['count'] = 0;
       }

       if (!isset($data['limit'])) {
           $data['limit'] = 0;
       }
       $smarty->assign('limit', $data['limit']);
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
       if (isset($data['owner-filter'])) {
           $smarty->assign('ownerfilter', $data['owner-filter']);
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
class ElasticsearchFilterAcl
{
    private $user;
    private $params = array();

    public function __construct($user) {
        $this->user = $user;

        // GENERAL         (public - loggedin - friends)
        // public
        $elasticaFilterGeneral = array(
                'term' => array(
                        'access.general' => 'public',
                ),
        );
        $this->params['should'][] = $elasticaFilterGeneral;

        // loggedin
        if ($this->user->is_logged_in()) {
            $elasticaFilterGeneral = array(
                    'term' => array(
                            'access.general' => 'loggedin',
                    ),
            );
            $this->params['should'][] = $elasticaFilterGeneral;

            // friends: pass a list of friends => check if access.general = friends and the owner is a friend of the current user
            if ($friends = $this->getFriendsList()) {
                $elasticaFilterGeneral = array(
                        'bool' => array(
                                'must' => array(
                                        array(
                                                'term' => array(
                                                        'access.general' => 'friends',
                                                ),
                                        ),
                                        array(
                                                'terms' => array(
                                                        'owner' => $friends,
                                                ),
                                        ),
                                ),
                        ),
                );
                $this->params['should'][] = $elasticaFilterGeneral;
            }

            if ($members = $this->getMembersList()) {
                // All groups: pass a list of groups => check if access.general = groups
                //                                      and the owner is a member of the same group as the current user.

                $elasticaFilterGeneral = array(
                        'bool' => array(
                                'must' => array(
                                        array(
                                                'term' => array(
                                                        'access.general' => 'groups',
                                                ),
                                        ),
                                        array(
                                                'terms' => array(
                                                        'owner' => $members,
                                                ),
                                        ),
                                ),
                        ),
                );
                $this->params['should'][] = $elasticaFilterGeneral;
            }

            //    INSTITUTIONS     (array of institutions that have access to the artefact)
            $user_institutions = array_keys($this->user->get('institutions'));
            if ($user_institutions && count($user_institutions) > 0) {
                $elasticaFilterInstitutions = array(
                        'terms' => array(
                                'access.institutions' => $user_institutions,
                        ),
                );
                $this->params['should'][] = $elasticaFilterInstitutions;
            }
            else if (empty($user_institutions) && is_isolated()) {
                $elasticaFilterInstitutions = array(
                        'terms' => array(
                                'access.institutions' => array('mahara'),
                        ),
                );
                $this->params['should'][] = $elasticaFilterInstitutions;
            }

            // GROUPS (array of groups that have access to the artefact)
            if ($groups = $this->getGroupsList()) {
                $elasticaFilterGroup = [];
                $roles = $this->getExistingRoles();
                foreach($roles AS $role){
                    if (isset($groups[$role]) && count($groups[$role])) {
                        $elasticaFilterGroup[] = array('terms' => array('access.groups.' . $role => $groups[$role]));
                    }
                }
                $this->params['should'][] = $elasticaFilterGroup;
            }

            // USRS (array of user ids that have access to the artefact)
            // if owner
            $elasticaFilterOwner = array(
                    'term' => array(
                            'owner' => $this->user->get('id'),
                    ),
            );
            $this->params['should'][] = $elasticaFilterOwner;

            // in access.usrs list
            $elasticaFilterUsr = array(
                    'term' => array(
                            'access.usrs' => $this->user->get('id'),
                    ),
            );
            $this->params['should'][] = $elasticaFilterUsr;
        }

    }

    public function get_params() {
       return $this->params;
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

    /** NEW: from totara.  Might not be needed in mahara? **/
    private function getMembersList() {
        $list = get_column_sql('SELECT DISTINCT gm2.member FROM {group_member} gm1
                               JOIN {group_member} gm2 ON gm1.group = gm2.group
                               WHERE gm1.member = ? AND gm2.member <> ?',
                array($this->user->get('id'), $this->user->get('id')));
        if (!empty($list)) {
            return $list;
        }
        return array();
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
    public static function getRecordById($type, $id, $map = null) {
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

        // These access parameters will be applied to each ES query.
        $accessfilter = new ElasticsearchFilterAcl($USER);

        $accessparams = $accessfilter->get_params();

        //      1 - Get the aggregate lists of content / filter / owner types
        // ------------------------------------------------------------------------------------------
        $records = array();
        $searchfield = 'catch_all';
        if ($result['tagsonly'] === true) {
            $searchfield = 'tags';
        }

        $matching = array(
            'match' => array(
                $searchfield => $query_string,
            )
        );

        if (strlen($query_string) <= 0) {
            // Get everything if empty query.
            $matching = array(
                'match_all' => new \stdClass()
            );
        }

        $client = PluginSearchElasticsearch::make_client();
        $params = array(
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'body'  => array(
                'size'  => 0, // we only want aggregations at this point
                'query' => array(
                    'bool' => array(
                        'must' => $matching,
                        'filter' => array('bool' => $accessparams),
                    ),
                ),
                'aggs'  => array(
                    'ContentType' => array(
                        'terms' => array(
                            'field' => 'mainfacetterm',
                        ),
                        'aggs' => array(
                            'ContentTypeFacet' => array(
                                'terms' => array(
                                    'field' => 'secfacetterm',
                                ),
                            ),
                        ),
                    ),
                    'OwnerType' => array(
                        'terms' => array(
                            'field' => 'owner',
                        ),
                    ),
                ),
            ),
        );

        $results = $client->search($params);
        $facets = self::processAggregations($results['aggregations']['ContentType']['buckets'], false);
        // no matching documents.  return immediately.
        if (count($facets) == 0) {
            return $result;
        }
        array_walk($result['facets'], 'self::processTabs', $facets);

        $ownertypes = self::processAggregations($results['aggregations']['OwnerType']['buckets'], true, $USER);
        array_walk($result['owner-filter'], 'self::processTabs', $ownertypes);

        $selectedFacet = false;
        if ($result['selected']) {
            $selectedFacet = $result['selected'];
        }

        $result['facetByTerm'] = array();
        // something we can lookup by term
        foreach ($result['facets'] as $k => $v) {
            $result['facetByTerm'][$v['term']] = $v['count'];
        }

        // Facets with no count aren't selectable in the UI.
        if ($selectedFacet === false || $result['facetByTerm'][$selectedFacet] <= 0 ) {
            $selectedFacet = self::getSelectedFacet($result['facetByTerm']);
        }
        $result['selected'] = $selectedFacet;
        // Get the filters for this facet
        $allcontenttypes = self::fetchSubAggregation($results['aggregations']['ContentType']['buckets'], 'ContentTypeFacet');
        $contenttypes = self::processAggregations($allcontenttypes[$result['selected']]['buckets'], true);
        array_walk($result['content-filter'], 'self::processTabs', $contenttypes);

        $result['totalresults'] = $results['hits']['total'];

        //      2 - Apply filters and retrieve final results
        // ------------------------------------------------------------------------------------------
        $sort = explode('_', $result['sort']);
        if ($sort[0] == 'score') {
            $sort[0] = '_score';
        }
        $sorting = array(
            array($sort[0] => array('order' => (isset($sort[1]) ? $sort[1] : 'desc'))),
            array('_score' => array('order' => 'desc'))
        );

        $filter = array(array('bool' => $accessparams)); // Apply the same access restrictions.
        if ($result['content-filter-selected'] != 'all') {
            $filter[] = array(
                'term' => array(
                    'secfacetterm' => $result['content-filter-selected'],
                ),
            );
        }

        // Apply Owner filter if different from "all".
        // We can't apply this as a filter but as a 'must_not'
        $mustnot = array();
        if ($result['owner-filter-selected'] != 'all') {
            $uid = $USER->get('id');
            if ($result['owner-filter-selected'] == 'others') {
                $mustnot[] = array(
                    'term' => array(
                        'owner' => $uid,
                    ),
                );
            }
            else {
                $filter[] = array(
                    'term' => array(
                        'owner' => $uid,
                    ),
                );
            }
        }

        // Applying filter for the selected facet
        $filter[] = array(
            'term' => array(
                'mainfacetterm' => $result['selected'],
            )
        );

        $params = array(
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'body'  => array(
                'from'  => $offset,
                'size'  => $limit,
                'sort'  => $sorting,
                'query' => array(
                     'bool' => array(
                        'must' => $matching,
                        'must_not' => $mustnot,
                        'filter' => $filter,
                    ),
                ),
                'highlight' => array(
                    'require_field_match' => false,
                    'pre_tags'            => array('<span class="search-highlight">'),
                    'post_tags'           => array('</span>'),
                    'number_of_fragments' => 2, // Returns first two matches.
                    'fragment_size'       => 100,
                    'fields'              => array(
                        'description' => new \stdClass(),
                    ),
                ),
            ),
        );

        $results = $client->search($params);
        $result['count'] = $results['hits']['total'];

        function starts_with_upper($str) {
            $chr = mb_substr ($str, 0, 1, "UTF-8");
            return mb_strtolower($chr, "UTF-8") != $chr;
        }

        $cleanuphighlight = function($str) {
            $str = strip_tags($str, '<span>');
            if (strpos($str, '<') > strpos($str, '>')) {
                // we probably have a broken close tag so will strip it out
                $str = substr($str, strpos($str, '>') + 1);
            }
            return $str;
        };

        foreach ($results['hits']['hits'] as $hit) {
            $tmp = array();
            $tmp['type'] = $hit['_source']['type'];
            $ES_class = 'ElasticsearchType_' . $tmp['type'];

            // Store highlighted fields if there is any
            $tmp['highlight'] = array();
            if (!empty($hit['highlight']) && !empty($hit['highlight']['description'])) {
                $tmp['highlight'] = $hit['highlight']['description'];
            }

            $tmp = $tmp + $hit['_source'];
            // Get all the data from the DB table
            $dbrec = $ES_class::getRecordDataById($tmp['type'], $tmp['id']);
            if ($dbrec) {
                $tmp['db'] = $dbrec;
                $tmp['db']->deleted = false;
                $highlight = false;
                if (!empty($tmp['highlight'])) {
                    $highlights = array_map($cleanuphighlight, $tmp['highlight']);
                    $highlight = implode(' ... ', $highlights);
                    if (substr($highlight, 0, 1) !== '<' && !starts_with_upper(strip_tags($highlight))) {
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

        return $result;
    }

    /**
     * Get the sub aggregated structure for an aggregation
     *
     * @param $data
     * @param string   $subagg   Name of the sub aggregation
     *
     * @return array
     */
    public static function fetchSubAggregation($data, $subagg) {
        $tmp = array();
        foreach ($data as $value) {
            if (isset($value[$subagg])) {
                $tmp[$value['key']] = $value[$subagg];
            }
        }
        return $tmp;
    }

    /**
     * Combine search results into aggregated structure.
     *
     * @param $data
     * @param bool     $all   To also return a total count key called 'all'
     * @param object   $USER  user object to filter the owners 'me' vs 'others'
     *
     * @return array
     */
    public static function processAggregations($data, $all = false, $USER = false) {

        $tmp = array();
        $countall = 0;
        foreach ($data as $value) {
            $tmp[$value['key']] = $value['doc_count'];
            if ($USER) {
                if ((int)$USER->get('id') === (int)$value['key']) {
                    $tmp['me'] = $value['doc_count'];
                }
                else {
                    $tmp['others'] = $value['doc_count'];
                }
            }
            if ($all) {
                $countall += $value['doc_count'];
            }
        }
        if ($all) {
            $tmp['all'] = $countall;
        }

        return $tmp;
    }

    /**
     * Return filter options from results.
     *
     * @param $item
     * @param $key
     * @param $data
     */
    public static function processTabs(&$item, $key, $data) {
        if (isset($data[$item['term']])) {
            $item['count'] = $data[$item['term']];
        }
    }

    /**
     * Return aggregations that are not empty
     *
     * @param $data
     *
     * @return mixed
     */
    public static function getSelectedAggregations($data) {
        foreach ($data as $key => $value) {
            if ($value['count'] > 0) {
                return $value['term'];
            }
        }
    }

    /**
     * Return the first entry in $data which has a non-zero count.
     *
     *  @param $data - an array with term, count and display keys.
     *  @return a facet name.
     */
    public static function getSelectedFacet($data) {
        foreach ($data as $key => $value) {
            if ($value > 0) {
                return $key;
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
        // Drop the index if it already exists.
        $params = array('index' => PluginSearchElasticsearch::get_write_indexname());
        $ESClient = PluginSearchElasticsearch::make_client('write');

        if ($ESClient->indices()->exists($params)) {
            $ESClient->indices()->delete($params);
        }

        // Create the index.
        $params = array(
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'body'  => array(
                'settings' => array(
                'number_of_shards' => get_config_plugin('search', 'elasticsearch', 'shards'),
                'number_of_replicas' => get_config_plugin('search', 'elasticsearch', 'replicashards'),
                'analysis' => array(
                    'analyzer' => array(
                        'mahara_analyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'lowercase', // define token separators as any non-alphanumeric character
                            'filter' => array('standard', 'stop', 'maharaSnowball'),
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
                )
            ),
            true
          )
        );

        $ESClient->indices()->create($params);

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
     *   add items to the queue. Or pass in an array of views to work wih (useful when all view_access rules
     *   deleted for view)
     */
    public static function add_to_queue_access($last_run, $timestamp, $views = array()) {

        $artefacttypes_str = self::artefacttypes_filter_string();
        if (!empty($views)) {
            $joinstr = '';
            $wherestr = " v.id IN (" . implode(',', array_values($views)) . ")";
        }
        else {
            $joinstr = " INNER JOIN {view_access} vac ON vac.view = v.id ";
            $wherestr = " vac.startdate BETWEEN '{$last_run}' AND '{$timestamp}'
                          OR vac.stopdate BETWEEN '{$last_run}' AND '{$timestamp}'
                          OR vac.ctime BETWEEN '{$last_run}' AND '{$timestamp}'";
        }

        execute_sql("
            INSERT INTO {search_elasticsearch_queue} (itemid, type)
            SELECT v.id, 'view'
            FROM {view} v
            " . $joinstr . "
            WHERE " . $wherestr . ";"
        );

        execute_sql("
            INSERT INTO {search_elasticsearch_queue} (itemid, type, artefacttype)
            SELECT var.artefact, 'artefact', a.artefacttype
            FROM {view} v
            " . $joinstr . "
            INNER JOIN {view_artefact} var ON var.view = v.id
            INNER JOIN {artefact} a ON var.artefact = a.id
            WHERE (" . $wherestr . ")
            AND a.artefacttype IN {$artefacttypes_str}
            ;"
        );

        // Deal with text blocks
        execute_sql("
            INSERT INTO {search_elasticsearch_queue} (itemid, type)
            SELECT b.id, 'block_instance'
            FROM {view} v
            " . $joinstr . "
            INNER JOIN {block_instance} b ON v.id = b.view
            WHERE (" . $wherestr . ")
            AND b.blocktype IN ('text')
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
