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

// Required because we use the PluginSearchInternal class for some functions
require_once(get_config('docroot') . 'search/internal/lib.php');
require_once(get_config('libroot') . '/elasticsearch/autoload.php');
require_once(dirname(__FILE__) . '/Elasticsearch7Indexing.php');
require_once(dirname(__FILE__) . '/Elasticsearch7Pseudotype_all.php');

use Elasticsearch\ClientBuilder;

/**
 * The internal search plugin which searches against the
 * Mahara database.
 */
class PluginSearchElasticsearch7 extends PluginSearch {

    /**
     * The minimum version of elasticsearch this plugin is compatible with.
     */
    const elasticsearch_version = '7.12';

    /**
     * Records in search_elasticsearch_7_queue that haven't been sent to Elasticsearch yet.
     */
    const queue_status_new = 0;

    /**
     * Records in search_elasticsearch_7_queue that have been sent in bulk to Elasticsearch.
     * These are deleted after being successfully sent, so they'll only be seen in the table
     * if the request to send them failed.
     */
    const queue_status_sent_in_bulk = 1;

    /**
     * Records in the queue that have been sent individually to Elasticsearch.
     *
     * These are deleted after being successfully sent, so they'll only be seen
     * in the table if the individual request to send them failed.
     */
    const queue_status_sent_individually = 2;

    /**
     * The plugin name for looking up config values.
     */
    const plugin_name = 'elasticsearch7';

    /**
     * Default values for config with defaults.
     */
    const default_config = [
        'host' => '127.0.0.1',
        'port' => '9200',
        'scheme' => 'http',
        'indexname' => 'mahara',
        'shards' => 5,
        'replicashards' => 0,
        'analyzer' => 'mahara_analyzer',
        'types' => 'usr,interaction_instance,interaction_forum_post,group,view,collection,artefact,event_log',
    ];

    ///////////////////////////
    // Overrides for 'extends Plugin'.
    ///////////////////////////

    /**
     * {@inheritDoc}
     *
     * @return string The plugin name.
     */
    public static function get_plugin_display_name() {
        return 'Elasticsearch 7';
    }

    ///////////////////////////
    // Overrides/abstract for 'extends PluginSearch'.
    ///////////////////////////

    /**
     * Search Users.
     *
     * @see PluginSearchInternal::search_user()
     * @param string $query_string       The query string
     * @param integer $limit             How many results to return
     * @param integer $offset            What result to start at (0 == first result)
     * @param array<string,mixed> $data  Filters the user used.
     *
     * @return array<string,mixed>
     */
    public static function search_user($query_string, $limit, $offset=0, $data=array()) {
        return PluginSearchInternal::search_user($query_string, $limit, $offset, $data);
    }

    /**
     * Does this search plugin provide enhanced event log reports?
     *
     * @return bool
     */
    public static function provides_enhanced_event_log_reports() {
        return true;
    }

    /**
     * Does this plugin provide support functions for the Monitor module?
     *
     * @return bool
     */
    public static function monitor_support() {
        return true;
    }

    /**
     * Search Groups.
     *
     * @see PluginSearchInternal::search_group()
     * @param string $query_string The query string.
     * @param integer $limit       How many results to return.
     * @param integer $offset      What result to start at.
     * @param string $type         Which groups to search (all, member, notmember).
     * @param string $category     Category the group belongs to.
     * @param string $institution  The institution the group belongs to.
     *
     * @return array<string,mixed>
     */
    public static function search_group($query_string, $limit, $offset=0, $type='member', $category='', $institution='all') {
        return PluginSearchInternal::search_group($query_string, $limit, $offset, $type, $category, $institution);
    }

    /**
     * Returns search results for users in a particular group.
     *
     * This is handed off to the internal search.
     *
     * @todo Investigate why $constraints is here.
     * @see PluginSearchInternal::group_search_user()
     * @param int $group The group ID.
     * @param string $query_string
     * @param mixed $constraints Does not appear to be used.
     * @param int $offset Where to start from.
     * @param int $limit The number of records to return.
     * @param string $membershiptype Optional. One of 'invite', 'request', 'notinvited', 'nonmember'.
     * @param string $order Optional. Order by 'latest' or 'random'.
     * @param int $friendof Optional. Limit to friends of this user.
     * @param string $sortoptionidx Optional. One of 'adminfirst', 'nameatoz', 'nameztoa', 'firstjoined', 'lastjoined'.
     *
     * @return array<string,mixed> Users found.
     */
    public static function group_search_user($group, $query_string, $constraints, $offset, $limit, $membershiptype='', $order='', $friendof=null, $sortoptionidx=null) {
        return PluginSearchInternal::group_search_user($group, $query_string, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $sortoptionidx);
    }

    /**
     * {@inheritDoc}
     *
     * This is handed off to the internal search.
     *
     * @see PluginSearchInternal::self_search()
     * @param string $query_string  The query string.
     * @param integer $limit        How many results to return.
     * @param integer $offset       What result to start at.
     * @param string $type          Type to search for.
     *
     * @return array<string,mixed>|false
     */
    public static function self_search($query_string, $limit, $offset, $type = 'all') {
        return PluginSearchInternal::self_search($query_string, $limit, $offset, $type);
    }

    ///////////////////////////
    // Functions for class PluginSearchElasticsearch7.
    ///////////////////////////

    // Plugin config.

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public static function has_config() {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,mixed>
     */
    public static function get_config_options() {
        $config = [
            'elements' => [],
        ];

        // Is the plugin enabled?
        $config['elements'] += self::get_config_options_enabled_notice();

        // Check plugin status
        $config['elements'] += self::get_config_options_status();

        // Add Cluster config form elements.
        $config['elements'] += self::get_config_options_cluster_config();

        // Sanity check to prevent the form crashing.
        list($status, $server) = self::get_server_info();
        if ($status == false) {
            // Servers are not up. Return so as to not cause the form to error out.
            return $config;
        }

        // Add Artifact types selection.
        $config['elements'] += self::get_config_options_artefact_types();

        // Add the index reset subform.
        $config['elements'] += self::get_config_options_index_reset();

        return $config;
    }

    /**
     * Validate the config options.
     *
     * If the config field is being hit and we know cron is not running we can
     * use `./mash search-reset-cron-lock` to clear it.
     *
     * @param Pieform $form               The form being validated.
     * @param array<string,mixed> $values The form values
     *
     * @return void
     */
    public static function validate_config_options(Pieform $form, $values) {
        // First check that there isn't an elasticsearch7 cron indexing the site
        if (get_record('config', 'field', '_cron_lock_search_elasticsearch7_cron')) {
            $form->set_error('', get_string('indexingrunning', 'search.elasticsearch7'));
        }
    }

    /**
     * Process our part of the form submission.
     *
     * @param Pieform $form               The form being validated.
     * @param array<string,mixed> $values The form values
     *
     * @return void
     */
    public static function save_config_options(Pieform $form, $values) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        if (!empty($values['all_reset'])) {
            self::get_config_options_index_reset_process($form, $values);
        }
        else {
            self::get_config_options_cluster_config_process($form, $values);
            self::get_config_options_artefact_types_process($form, $values);
        }
        if ($values['cron_state']) {
            self::enable_cron_record();
        }
        else {
            self::disable_cron_record();
        }
    }

    /**
     * The Artefact type selection matrix.
     *
     * @return array<string,mixed> An array of Pieform elements.
     */
    private static function get_config_options_artefact_types() {
        $config = [];
        $types = self::get_artefact_types();
        if (in_array('artefact', $types)) {
            $sql = '
                SELECT DISTINCT name AS artefacttype
                FROM {artefact_installed_type}
                ORDER BY name ASC
            ';
            $rs = get_recordset_sql($sql);
            $artefacttypes = explode(',', self::config_value_or_default('artefacttypes'));

            // The following artefacttypes are auto ticked because the info is already being indexed by the usr table.
            $artefacttypes_toexclude = ['firstname', 'lastname', 'preferredname', 'email', 'studentid'];
            $artefacttypes = array_merge($artefacttypes, $artefacttypes_toexclude);

            // To be valid the artefact types need a hierarchy.
            $artefacttypesmap_array = self::artefacttypesmap_to_array();

            $types_checkbox = array();
            foreach (recordset_to_array($rs) as $record) {
                $is_artefact_type = in_array($record->artefacttype, $artefacttypes);
                $excluded_artefact = in_array($record->artefacttype, $artefacttypes_toexclude);
                $artefact_in_hierarchy = !in_array($record->artefacttype, array_keys($artefacttypesmap_array));
                $types_checkbox[] = [
                    'title'        => $record->artefacttype,
                    'value'        => $record->artefacttype,
                    'defaultvalue' => ($is_artefact_type) ? true : false,
                    'disabled'     => ($excluded_artefact OR $artefact_in_hierarchy) ? true : false,
                ];
            }

            $config['artefacttypes'] = [
                'type'        => 'checkboxes',
                'class'       => 'stacked',
                'title'       => get_string('artefacttypes', 'search.elasticsearch7'),
                'description' => get_string('artefacttypesdescription', 'search.elasticsearch7'),
                'elements'    => $types_checkbox,
            ];

            $config['artefacttypesmap'] = [
                'type'         => 'textarea',
                'rows'         => 10,
                'cols'         => 100,
                'class'        => 'under-label',
                'title'        => get_string('artefacttypesmap', 'search.elasticsearch7'),
                'description'  => get_string('artefacttypesmapdescription', 'search.elasticsearch7'),
                'defaultvalue' => implode("\n", $artefacttypesmap_array),
            ];
        }
        return $config;
    }

    /**
     * Process the config options for Artefact types.
     *
     * @param Pieform $form               The Pieform object.
     * @param array<string,mixed> $values The posted values.
     *
     * @return void
     */
    private static function get_config_options_artefact_types_process($form, $values) {
        set_config_plugin('search', self::plugin_name, 'artefacttypesmap', $values['artefacttypesmap']);

        // To be valid, artefact types need a hierarchy.
        $artefacttypesmap_array = self::artefacttypesmap_to_array();

        // The following artefacttypes are already being indexed by the usr table
        // so we don't want to save them.
        $artefacttypes_toexclude = ['firstname', 'lastname', 'preferredname', 'email'];
        foreach ($artefacttypes_toexclude as $exclude) {
            if (!empty($values['artefacttypes'][$exclude])) {
                unset($values['artefacttypes'][$exclude]);
            }
        }
        $values['artefacttypes'] = array_intersect(
            $values['artefacttypes'],
            array_keys($artefacttypesmap_array)
        );

        $types = explode(',', $values['types']);
        if (in_array('artefact', $types)) {
            $artefacttypes_old = explode(',', self::config_value_or_default('artefacttypes'));
            $result = array_diff($artefacttypes_old, $values['artefacttypes']) + array_diff($values['artefacttypes'], $artefacttypes_old);
            // Result now contains the artefacttypes that have been checked and
            // unchecked.
            foreach ($result as $artefacttype) {
                Elasticsearch7Indexing::requeue_searchtype_contents('artefact', array(), $artefacttype);
            }
            set_config_plugin('search', self::plugin_name, 'artefacttypes', implode(',', $values['artefacttypes']));
        }
    }

    /**
     * The cluster config section.
     *
     * @return array<string,array<string,mixed>> The Pieform elements for the cluster config.
     */
    private static function get_config_options_cluster_config() {
        $config = [];
        $cluster_config = [];
        $cluster_config['host'] = [
            'title'       => get_string('host', 'search.elasticsearch7'),
            'description' => get_string('hostdescription', 'search.elasticsearch7', self::config_value_or_default('host', true)),
            'type'        => 'html',
            'value'       => self::config_value_or_default('host'),
            'help'        => true,
        ];
        $cluster_config['port'] = [
            'title'       => get_string('port', 'search.elasticsearch7'),
            'description' => get_string('portdescription', 'search.elasticsearch7', self::config_value_or_default('port', true)),
            'type'        => 'html',
            'value'       => self::config_value_or_default('port'),
            'help'        => true,
        ];
        $cluster_config['scheme'] = [
            'title'       => get_string('scheme', 'search.elasticsearch7'),
            'description' => get_string('schemedescription', 'search.elasticsearch7', self::config_value_or_default('scheme', true)),
            'type'        => 'html',
            'value'       => self::config_value_or_default('scheme'),
            'help'        => true,
        ];
        $cluster_config['username'] = [
            'title'       => get_string('username', 'search.elasticsearch7'),
            'description' => get_string('usernamedescription', 'search.elasticsearch7'),
            'type'        => 'html',
            'value'       => (
                               self::config_value_or_default('username')
                               ? self::config_value_or_default('username')
                               : get_string('confignotset', 'search.elasticsearch7')
                             ),
            'help'        => true,
        ];
        $cluster_config['password'] = [
            'title'       => get_string('password', 'search.elasticsearch7'),
            'description' => get_string('passworddescription', 'search.elasticsearch7'),
            'type'        => 'html',
            'value'       => (
                               self::config_value_or_default('password')
                               ? get_string('passwordlength', 'search.elasticsearch7', strlen(self::config_value_or_default('password')))
                               : get_string('confignotset', 'search.elasticsearch7')
                             ),
            'help'        => true,
        ];
        $cluster_config['indexingusername'] = [
            'title'       => get_string('indexingusername', 'search.elasticsearch7'),
            'description' => get_string('indexingusernamedescription', 'search.elasticsearch7'),
            'type'        => 'html',
            'value'       => (
                               self::config_value_or_default('indexingusername')
                               ? self::config_value_or_default('indexingusername')
                               : get_string('confignotset', 'search.elasticsearch7')
                             ),
            'help'        => true,
        ];
        $cluster_config['indexingpassword'] = [
            'title'       => get_string('indexingpassword', 'search.elasticsearch7'),
            'description' => get_string('indexingpassworddescription', 'search.elasticsearch7'),
            'type'        => 'html',
            'value'       => (
                               self::config_value_or_default('indexingpassword')
                               ? get_string('passwordlength', 'search.elasticsearch7', strlen(self::config_value_or_default('indexingpassword')))
                               : get_string('confignotset', 'search.elasticsearch7')
                             ),
            'help'                 => true,
        ];
        $cluster_config['indexname'] = [
            'title'       => get_string('indexname', 'search.elasticsearch7'),
            'description' => get_string('indexnamedescription', 'search.elasticsearch7', self::config_value_or_default('indexname', true)),
            'type'        => 'html',
            'value'       => self::config_value_or_default('indexname'),
            'help'        => true,
        ];
        $cluster_config['bypassindexname'] = [
            'title'       => get_string('bypassindexname', 'search.elasticsearch7'),
            'description' => get_string('bypassindexnamedescription', 'search.elasticsearch7'),
            'type'        => 'html',
            'help'        => true,
            'value'       => (
                               self::config_value_or_default('bypassindexname')
                               ? self::config_value_or_default('bypassindexname')
                               : get_string('confignotset', 'search.elasticsearch7')
                             ),
        ];
        $cluster_config['analyzer'] = [
            'title'       => get_string('analyzer', 'search.elasticsearch7'),
            'description' => get_string('analyzerdescription', 'search.elasticsearch7', self::config_value_or_default('analyzer', true)),
            'type'        => 'html',
            'value'       => self::config_value_or_default('analyzer'),
            'help'        => true,
        ];
        $cluster_config['types'] = [
            'title'       => get_string('types', 'search.elasticsearch7'),
            'description' => get_string('typesdescription', 'search.elasticsearch7', self::config_value_or_default('types', true)),
            'type'        => 'html',
            'size'        => '80',
            'value'       => self::config_value_or_default('types'),
            'help'        => true,
        ];

        $config['clusterconfig'] = [
            'type'        => 'fieldset',
            'legend'      => get_string('clusterconfig', 'search.elasticsearch7'),
            'elements'    => $cluster_config,
            'collapsible' => true,
            'collapsed'   => true,
        ];

        $config['cronlimit'] = [
            'title'        => get_string('cronlimit', 'search.elasticsearch7'),
            'description'  => get_string('cronlimitdescription', 'search.elasticsearch7'),
            'type'         => 'text',
            'defaultvalue' => self::config_value_or_default('cronlimit'),
        ];
        $config['shards'] = [
            'title'        => get_string('shards', 'search.elasticsearch7'),
            'description'  => get_string('shardsdescription', 'search.elasticsearch7'),
            'type'         => 'text',
            'defaultvalue' => self::config_value_or_default('shards'),
        ];
        $config['replicashards'] = [
            'title'        => get_string('replicashards', 'search.elasticsearch7'),
            'description'  => get_string('replicashardsdescription', 'search.elasticsearch7'),
            'type'         => 'text',
            'defaultvalue' => self::config_value_or_default('replicashards'),
        ];

        // Add a switch to set the cron state.
        $cron_record = get_record('search_cron', 'plugin', 'elasticsearch7');
        $config['cron_state'] = [
            'type' => 'switchbox',
            'title' => get_string('cronstatetitle', 'search.elasticsearch7'),
            'defaultvalue' => ($cron_record)?true:false,
            'wrapperclass' => 'switch-wrapper-inline',
            'labelhtml' => '<span class="pseudolabel">' . get_string('cronstatetitle', 'search.elasticsearch7') .'</span>',
            'description' => get_string('cronstatedescription', 'search.elasticsearch7'),
        ];
        return $config;
    }

    /**
     * Process the config options for the Cluster Config.
     *
     * @param Pieform $form The Pieform object.
     * @param array<string,mixed> $values The posted values.
     *
     * @return void
     */
    private static function get_config_options_cluster_config_process($form, $values) {
        set_config_plugin('search', self::plugin_name, 'cronlimit', $values['cronlimit']);

        // Set the shard / replica values.
        $shards = (int) $values['shards'];
        $shards = empty($shards) ? self::default_config['shards'] : $shards;
        set_config_plugin('search', self::plugin_name, 'shards', (int) $shards);
        set_config_plugin('search', self::plugin_name, 'replicashards', (int) $values['replicashards']);
    }

    /**
     * Enable cron.
     *
     * @return void
     */
    private static function enable_cron_record() {
        $check = get_record('search_cron', 'plugin', 'elasticsearch7');
        if ($check) {
            // Already enabled.
            return;
        }
        // We did not find a record. Add one.
        $config = self::get_cron();
        $config = $config[0];
        $record = new stdClass;
        $record->plugin = 'elasticsearch7';
        $record->nextrun = null;
        $record->minute = $config->minute;
        $record->hour = $config->hour;
        $record->day = '*';
        $record->month = '*';
        $record->dayofweek = '*';
        $record->callfunction = $config->callfunction;
        insert_record('search_cron', $record);
    }

    /**
     * Remove the cron record.
     *
     * @return void
     */
    private static function disable_cron_record() {
        delete_records('search_cron', 'plugin', 'elasticsearch7');
    }

    /**
     * The HTML for the current status of items the plugin needs.
     *
     * @todo Make this status function available from the Plugin class.
     * @return array<string,array<string,mixed>> The HTML of any notices.
     */
    private static function get_config_options_status() {
        $config = [];
        $item_statuses = [];
        // Callbacks for a status item.
        $methods = get_class_methods('PluginSearchElasticsearch7');
        // To add a status check create a function that starts with
        // plugin_status_[thing](). This should return an array with the
        // following keys:
        // * title (for display)
        // * class (for markup)
        // * message (for display)
        // * result (A value from 0 to 1 for how successful the check was)
        $callbacks = array_filter($methods, 'PluginSearchElasticsearch7::is_plugin_status');

        $item_count = 0;
        $item_status_total = 0;
        foreach ($callbacks as $callback) {
            $result = self::{$callback}();
            $item_statuses[] = [
                'title' => $result['title'],
                'class' => $result['class'],
                'type' => 'html',
                'value' => $result['message'],
            ];
            $item_count++;
            $item_status_total += $result['result'];
        }

        $result_percent = ($item_status_total / $item_count) * 100;
        $config['pluginstatus'] = [
            'type'        => 'fieldset',
            'legend'      => get_string('pluginstatus', 'search.elasticsearch7', $result_percent . '%'),
            'elements'    => $item_statuses,
            'collapsible' => true,
            'collapsed'   => true,
        ];
        return $config;
    }

    /**
     * The HTML for the current state of the ES7 server that is configured.
     *
     * @return array<string,string> The HTML of any notices.
     */
    private static function get_config_options_enabled_notice() {
        $config = [];
        $enabledhtml = '';
        $state = 'ok';
        list($status, $server) = self::get_server_info();
        if (!$status) {
            // The server is not active, did not respond, or is not configured yet.
            $state = 'notice';
            if (!empty($server->error)) {
                $notice = $server->error;
            }
            else {
                $notice = get_string('noticenotactive', 'search.elasticsearch7', self::config_value_or_default('host'), self::config_value_or_default('port'));
            }
            $enabledhtml .= self::get_formatted_notice($notice, 'warning');
        }
        else {
            // We can reach the server. How is it looking?
            list($status, $health) = self::get_server_info('clusterhealth');
            if (!empty($health->data) && $health->data->status != 'green') {
                // We can reach the cluster, but there is something wrong.
                $enabledhtml .= self::get_formatted_notice(get_string('clusterstatus', 'search.elasticsearch7', $health->data->status), 'warning');
                if ($health->data->status == 403) {
                    $enabledhtml .= self::get_formatted_notice(get_string('clusterstatus', 'search.elasticsearch7', $health->error), 'danger');
                }
                else if ($health->data->unassigned_shards) {
                    $enabledhtml .= self::get_formatted_notice(get_string('unassignedshards', 'search.elasticsearch7', $health->data->unassigned_shards), 'warning');
                }
                $state = 'notice';
            }
            $index = self::config_value_or_default('indexname');
            list($status, $health) = self::get_server_info('indexhealth', $index);
            if (!empty($health->data)) {
                if (isset($health->data->status) && $health->data->status == '403') {
                    $enabledhtml .= self::get_formatted_notice(get_string('indexstatusunknown', 'search.elasticsearch7', $index, $health->data->status), 'warning');
                }
                else if (isset($health->data->health) && $health->data->health != 'green') {
                    $enabledhtml .= self::get_formatted_notice(get_string('indexstatusbad', 'search.elasticsearch7', $index, $health->data->health), 'warning');
                }
                $state = 'notice';
            }
        }
        if (get_config('searchplugin') == 'elasticsearch7') {
            $enabledhtml .= self::get_formatted_notice(get_string('noticeenabled', 'search.elasticsearch7', get_config('wwwroot') . 'admin/site/options.php?fs=searchsettings'), $state);
        }
        else {
            $enabledhtml .= self::get_formatted_notice(get_string('noticenotenabled', 'search.elasticsearch7', get_config('wwwroot').'admin/site/options.php?fs=searchsettings'), 'warning');
        }
        if (!empty($enabledhtml)) {
            $config['enablednotice'] = [
                'type' => 'html',
                'value' => $enabledhtml,
            ];
        }
        return $config;
    }

    /**
     * The Index Reset widget.
     *
     * @return array<string,array<string,mixed>> The Index Reset widget pieform elements.
     */
    private static function get_config_options_index_reset() {
        $config = [];
        try {
            $types = self::get_artefact_types();
            if (count($types) > 0) {
                // Fetch the items currently in the queue for indexing.
                $item_by_type_in_queue = [];
                $item_by_type_in_index = [];
                $sql = 'SELECT type, count(*) AS total FROM {search_elasticsearch_7_queue} GROUP BY type';
                $rs = get_records_sql_array($sql, []);
                if ($rs) {
                    foreach ($rs as $record) {
                        $item_by_type_in_queue[$record->type] = $record->total;
                    }
                }

                for ($i = 0; $i < count($types); $i++) {
                    $item_by_type_in_index[$types[$i]] = self::count_type_in_index($types[$i]);
                }

                // Create the buttons that let an admin reset individual sub-indexes.
                $resetelements = array();

                $resetelements['reset_description'] = [
                    'type' => 'html',
                    'value' => get_string('resetdescription','search.elasticsearch7')
                ];

                // TODO: Make single-searchtype reset work properly. For now we'll
                // just comment it out, leaving only "reset all" available.
                $table = [];
                foreach ($types as $type) {
                    $keyreset = $type . '_reset';
                    $items_in_queue =  isset($item_by_type_in_queue[$type]) ? $item_by_type_in_queue[$type] : 0;
                    $items_in_index =  isset($item_by_type_in_index[$type]) ? $item_by_type_in_index[$type] : 0;
                    $table[] = [
                        'type' => $type,
                        'itemsToQueue' => $items_in_queue,
                        'itemsInIndex' => $items_in_index
                    ];
                    $smarty_table = smarty_core();
                    $smarty_table->assign('table', $table);
                    $rendered_table = $smarty_table->fetch('search:elasticsearch7:table.tpl');
                }

                $resetelements[$keyreset] = [
                    'type' => 'html',
                    'value' => $rendered_table,
                ];

                // And on the end, a special one to reset all the indexes.
                $resetelements['all_reset'] = [
                    'title' => get_string('resetallindexes', 'search.elasticsearch7'),
                    'type' => 'submit',
                    'class' => 'btn-secondary',
                    'defaultvalue' => get_string('reset', 'search.elasticsearch7'),
                ];

                $config['resetindex'] = [
                    'type' => 'fieldset',
                    'class' => 'last',
                    'legend' => get_string('resetlegend', 'search.elasticsearch7'),
                    'elements' => $resetelements,
                    'collapsible' => true,
                ];
            }
        }
        catch (Exception $e) {
            $config['noindex'] = [
                'type' => 'fieldset',
                'class' => 'last',
                'legend' => get_string('connectionerror', 'search.elasticsearch7'),
                'elements' => [
                    [
                        'type' => 'html',
                        'value' => get_string('systemmessage', 'search.elasticsearch7') . $e->getMessage(),
                    ],
                ],
            ];
        }
        return $config;
    }

    /**
     * Return the number of documents found for a given indexsourcetype.
     *
     * @param strint $type The index type we are counting
     *
     * @return int The number of documents found.
     */
    public static function count_type_in_index($type) {
        // Build the total in index of this type.
        $params = [
            'index' => self::get_write_indexname(),
            'body' => [
                'query' => [
                    'match' => [
                        'indexsourcetype' => $type,
                    ],
                ],
            ],
        ];
        $es = self::make_client();
        $count = $es->count($params);
        if (empty($count)) {
            return 0;
        }
        else {
            return $count['count'];
        }
    }

    /**
     * Index Reset was clicked on the config form.
     *
     * @param Pieform $form               The Pieform object.
     * @param array<string,mixed> $values The posted values.
     *
     * @return void
     */
    private static function get_config_options_index_reset_process($form, $values) {
        // If they chose to reset all the indexes, do that.
        if (!isset($values['all_reset'])) {
            return;
        }
        self::reset_search_index();
    }

    /**
     * Check if cron thinks it is already processing.
     *
     * @return int The timestamp of the current lock.
     */
    private static function check_cron_lock() {
        return get_record('config', 'field', '_cron_lock_search_elasticsearch7_cron');
    }

    /**
     * Lock cron so we do not try processing if already running.
     *
     * @return int The start time. Used to unlock.
     */
    private static function lock_cron() {
        $start = time();
        $lock_record = new stdClass;
        $lock_record->field = '_cron_lock_search_elasticsearch7_cron';
        $lock_record->value = $start;
        insert_record('config', $lock_record);
        return $start;
    }

    /**
     * @param int $start
     *
     * @return void
     */
    private static function unlock_cron($start) {
        delete_records(
            'config',
            'field',
            '_cron_lock_search_elasticsearch7_cron',
            'value',
            $start
        );
    }

    /**
     * Perform a hard reset of the search index.
     *
     * This is destructive. It will delete the index, recreate the index, and
     * requeue all indexable content for reloading into the index.
     *
     * @return void
     */
    private static function reset_search_index() {

        // Set the cron lock before beginning re index to stop the cron
        // indexing at same time.
        $locked_time = self::lock_cron();

        self::reset_all_searchtypes();

        error_log("finished resetting.");

        // Send the first batch of records to the elasticsearch server now, for instant gratification
        self::index_queued_items();

        // Free the cron lock.
        self::unlock_cron($locked_time);
    }

    /**
     * Helper functions.
     *
     * @TODO Could, or should, these be moved to the Plugin class?
     */


    /**
     * @return array<string> The artifact types we
     */
    public static function get_artefact_types() {
        return explode(',', self::config_value_or_default('types'));
    }

    /**
     * @param string $notice The text of the notice to show.
     * @param string $type The type of notice to show.
     * @param string $heading An optional heading.
     * @param bool $is_admin Add the 'admin-warning' class.
     *
     * @return string The parsed template as an HTML string.
     */
    public static function get_formatted_notice($notice, $type, $heading = '', $is_admin = false) {
        $class = [];

        if ($is_admin) {
            $class[] = 'admin-warning';
        }
        switch ($type) {
            case 'danger':
                $class[] = 'alert alert-danger';
                break;

            case 'warning':
                $class[] = 'alert alert-warning';
                break;

            case 'ok':
                $class[] = 'alert alert-success';
                break;

            default:
                $class[] = 'alert alert-default';
        }

        $smarty = smarty_core();
        $smarty->assign('class', implode(' ', $class));
        if (!empty($heading)) {
            $smarty->assign('heading', $heading);
        }
        $smarty->assign('notice', $notice);
        $html = $smarty->fetch('Search:elasticsearch7:notice.tpl');
        unset($smarty);
        return $html;
    }


    /**
     * @param string $key The config key we are returning.
     * @param bool $fetch_default Are we only returning the default?
     *
     * @return mixed The config value, the default, or null.
     */
    public static function config_value_or_default($key, $fetch_default = false) {
        $default = null;
        $val = false;
        if (get_config('installed')) {
            $val = get_config_plugin('search', self::plugin_name, $key);
        }
        if (array_key_exists($key, self::default_config)) {
            $default = self::default_config[$key];
        }
        if ($fetch_default) {
            return $default;
        }
        else if ($val) {
            return $val;
        }
        else {
            return $default;
        }
    }

    // Server comms.

    /**
     * Return the elasticsearch server information at supplied host and port.
     *
     * We can't use the $ESClient as we need to check if we are trying to connect
     * to either an older or current server so will run curl commands directly.
     *
     * @param string $option What is being asked, eg cluster health
     * @param string $index  The particular index, eg indices status
     * @return array<mixed>  $data [
     *                           $canconnect => bool   // Was the connection successful?
     *                           $server      => object // Information about the server request.
     *                       ]
     */
    public static function get_server_info($option=null, $index=null) {
        $clientops = self::get_client_config('write');
        $host = $clientops['hosts'][0];

        if (empty($host['host'])) {
            // We are not configured.
            return [false, $host];
        }

        $host_part = $host['host'] . ':' . $host['port'];
        $user = '';
        $scheme = 'http://';

        if (!empty($host['username'])) {
            $user = $host['username'] . ':' . $host['password'] . '@';
        }
        if (!empty($host['scheme'])) {
            $scheme = $host['scheme'] . '://';
        }

        switch ($option) {
            case "clusterhealth":
                $path = '/_cluster/health';
                break;

            case "indexhealth":
                $path = '/_cat/indices?format=json';
                break;

            case "indexstatus":
                $indexname = self::config_value_or_default('indexname');
                $path = '/' . $indexname . '/_stats';
                break;

            default:
                $clientopts['curlopts'][CURLOPT_NOBODY] = true;
                $indexname = self::config_value_or_default('indexname');
                $path = '/' . $indexname . '?format=json';
        }

        $url = $scheme . $user . $host_part . $path;

        $curlopts = array(CURLOPT_URL => $url) + $clientops['curlopts'];
        $server = mahara_http_request($curlopts, true);
        if (!empty($server->data)) {
            $data = json_decode($server->data);
            if (!empty($data->error->type) && $data->error->type == 'index_not_found_exception') {
                // We don't have an index yet.  Try and create it.
                Elasticsearch7Indexing::create_index();
                // Try the server again. Let this attempt fall through to the
                // actual state reporting.
                $server = mahara_http_request($curlopts, true);
            }
        }
        $canconnect = false;
        if (!empty($server->info) && !empty($server->info['http_code'])) {
            if ($server->info['http_code'] != '200') {
                $server->error = get_string('servererror', 'search.elasticsearch7', $server->info['http_code']);
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
                    // We need to find the data for particular index.
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
                        $server->error = get_string('elasticsearchtooold', 'search.elasticsearch7', $server->data->version->number, self::elasticsearch_version);
                    }
                }
            }
        }
        return [$canconnect, $server];
    }

    /**
     * @param string $type Read or write.
     *
     * @return array<string,mixed> The host and cURL options.
     */
    public static function get_client_config($type='read') {
        $host = self::config_value_or_default('host');
        $port = self::config_value_or_default('port');

        $this_host = [
            'host' => $host,
            'port' => $port
        ];

        // Build array of curlopts.
        $elasticclientcurlopts = [];
        $elasticclientcurlopts[CURLOPT_CONNECTTIMEOUT] = 3;

        // Prep the config we will use for readability later.
        $username = self::config_value_or_default('username');
        $password = self::config_value_or_default('password');
        $indexingusername = self::config_value_or_default('indexingusername');
        $indexingpassword = self::config_value_or_default('indexingpassword');
        $scheme = self::config_value_or_default('scheme');
        $ignoressl = self::config_value_or_default('ignoressl');

        $in_production_mode = get_config('productionmode');
        $proxyaddress = get_config('proxyaddress');
        $proxyauthmodel = get_config('proxyauthmodel');
        $proxyauthcredentials = get_config('proxyauthcredentials');

        if (!empty($username)) {
            // We will be setting the CURLOPT_USERPWD.
            if ($type == 'write' && !empty($indexingusername)) {
                // Overload $username/$password with the specific pair for indexing.
                $username = $indexingusername;
                $password = $indexingpassword;
            }

            $this_host['username'] = $username;
            $this_host['password'] = $password;
            $elasticclientcurlopts[CURLOPT_USERPWD] = $username . ':' . $password;
        }

        if (!empty($scheme)) {
            $this_host['scheme'] = $scheme;
            if (!$in_production_mode && $ignoressl) {
                // Ignore verifying the SSL certificate.
                $elasticclientcurlopts[CURLOPT_SSL_VERIFYHOST] = false;
                $elasticclientcurlopts[CURLOPT_SSL_VERIFYPEER] = false;
            }
        }

        if (!empty($proxyaddress)) {
            $elasticclientcurlopts[CURLOPT_PROXY] = $proxyaddress;
            $elasticclientcurlopts[CURLOPT_HTTPHEADER] = ['Transfer-Encoding: chunked'];
            if (!empty($proxyauthmodel) && !empty($proxyauthcredentials)) {
                // @TODO: actually do something with $proxy_authmodel.
                $elasticclientcurlopts[CURLOPT_PROXYUSERPWD] = $proxyauthcredentials;
            }
        }

        return [
            'hosts' => [$this_host],
            'curlopts' => $elasticclientcurlopts,
        ];

    }

    /**
     * Use the raw string of the query.
     *
     * This function indicates whether the plugin should take the raw $query string
     * when its group_search_user function is called, or whether it should get the
     * parsed query string.
     *
     * @return boolean True if we can just use the raw string.
     */
    public static function can_process_raw_group_search_user_queries() {
        return true;
    }

    /**
     * Returns search results for users in a particular institution
     *
     * @see PluginSearchInternal::institutional_admin_search_user()
     * @todo Can we move all these PluginSearchInternal call to PluginSearch?
     * @param mixed $query string       A search term.
     * @param mixed $institution string The institution shortname.
     * @param mixed $limit int          The number of items to return.
     *
     * @return array<string,mixed>
     */
    public static function institutional_admin_search_user($query, $institution, $limit) {
        return PluginSearchInternal::institutional_admin_search_user($query, $institution, $limit);
    }

    /**
     * Generates the search form used in the page headers.
     *
     * The presence of this method overrides the default internal search form
     * in the page header.
     *
     * @return string
     */
    public static function header_search_form() {
        $action = get_config('wwwroot') . 'search/elasticsearch7/index.php';
        $title = get_string('pagetitle', 'search.elasticsearch7');
        $placeholder = get_string('pagetitle', 'search.elasticsearch7');
        return pieform(array(
            'name'                => 'usf',
            'action'              => $action,
            'renderer'            => 'oneline',
            'autofocus'           => false,
            'validate'            => false,
            'presubmitcallback'   => '',
            'class'               => 'header-search-form',
            'elements'            => array(
                'query' => array(
                    'type'           => 'text',
                    'defaultvalue'   => '',
                    'title'          => $title,
                    'placeholder'    => $placeholder,
                    'hiddenlabel'    => true,
                ),
                'submit' => array(
                    'type' => 'button',
                    'class' => 'btn-secondary input-group-append',
                    'usebuttontag' => true,
                    'value' => '<span class="icon icon-search" role="presentation" aria-hidden="true"></span><span class="visually-hidden">'. get_string('go') . '</span>',
                )
            )
        ));
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
        delete_records('search_elasticsearch_7_queue');
    }

    /**
     * Reset all search types.
     *
     * Resets all the searchtypes in the following ways:
     *   - Deletes and re-creates the elasticsearch index on the server.
     *   - Re-creates the trigger functions
     *     - This will also drop the triggers for all types (even those that
     *       aren't in use).
     *   - Creates all triggers for those types that are in use.
     *   - Tells the elasticsearch server to drop and re-create the index.
     *   - Tells the elasticsearch server to re-create the "mapping" for each
     *     type.
     *   - Loads every record for that type into the queue table, for the cron
     *     to chug away at them.
     *
     * @return void
     */
    public static function reset_all_searchtypes() {
        // Deletes and re-creates the elasticsearch index on the server.
        Elasticsearch7Indexing::create_index();
        $enabledtypes = explode(',', self::config_value_or_default('types'));
        // Create, or recreate, the mappings, triggers and the overall site
        // index.
        foreach ($enabledtypes as $type) {
            // Requeue content for indexing.
            $ES_class = 'Elasticsearch7Type_' . $type;
            $ES_class::requeue_searchtype_contents();
        }
    }

    /**
     * Parse the user defined Artifact Types map.
     *
     * This parses the Artefact types hierarchy field on the ES7 Plugin config
     * form.
     *
     * The field is a text area of rows deliniated by newlines. Each row is
     * is exploded on a pipe character.
     *
     * There are 3 fields per row:
     *
     * 1. The mahara content type
     * 2. The secondary facet type for Elasticsearch
     * 3. The main facet type for Elasticsearch
     *
     * @see Elasticsearch7Type_artefact::add_facet_terms_for_record()
     *
     * @return array<string,string>
     */
    public static function artefacttypesmap_to_array() {
        $artefacttypesmap = self::config_value_or_default('artefacttypesmap');
        $artefacttypesmap_array = explode("\n", $artefacttypesmap);
        $ret = [];
        foreach ($artefacttypesmap_array as $key => $value) {
            $tmpkey = explode("|", $value);
            if (count($tmpkey) == 3) {
                $ret[$tmpkey[0]] = $value;
            }
        }
        ksort($ret, SORT_STRING);
        return $ret;
    }

    /**
     * Return cron settings.
     *
     * @return array<object>
     */
    public static function get_cron() {
        $cron = new stdClass;
        $cron->callfunction = 'cron';
        $cron->hour = '*';
        $cron->minute = '4-59/5';
        return [$cron];
    }

    /**
     * Run cron tasks.
     *
     * @return void
     */
    public static function cron() {

        // Only run the cron if this plugin is the active search plugin.
        if (get_config('searchplugin') !== 'elasticsearch7') {
            return;
        }

        // Store the last time the function was executed: eg: 2013-04-11 16:45:30
        $timestamp = date('Y-m-d H:i:s');
        $last_run = get_config_plugin('search', 'elasticsearch7', 'lastrun');
        if (isset($last_run)) {
            Elasticsearch7Indexing::add_to_queue_access($last_run, $timestamp);
        }

        set_config_plugin('search', 'elasticsearch7', 'lastrun', $timestamp);
        // Process the queue.
        self::index_queued_items();
    }

    /**
     * Add item to queue for indexing.
     *
     * Passes through to Elasticsearch7Indexing for processing.
     *
     * @param int $id The ID of the item to index
     * @param string $table The table the item is from.
     * @param string|null $artefacttype An optional artefact type.
     *
     * @return void
     */
    public static function add_to_queue($id, $table, $artefacttype=null) {
        Elasticsearch7Indexing::add_to_queue($id, $table, $artefacttype);
    }



    /**
     * Index items in the queue table.
     *
     * Processes items in the queue table taking into account cron limits.
     * When doing batch processing $ESClient can be passed in so it doesn't
     * need to be rebuilt all the time.
     *
     * @param object|bool $ESClient Reuse an existing client if passed in.
     *
     * @return void
     */
    public static function index_queued_items($ESClient = false) {

        $limitfrom = '';
        $limitto = '';

        $cronlimit = intval(self::config_value_or_default('cronlimit'));
        if ($cronlimit > 0) {
            $limitfrom = 0;
            $limitto = $cronlimit;
        }

        $requestlimit = intval(self::config_value_or_default('requestlimit'));
        if ($requestlimit <= 0) {
            // If they specified no request limit, just use a really big
            // number. This is easier than writing special code just to handle
            // the case where there's no limit.
            $requestlimit = 1000;
        }

        $redolimit = intval(self::config_value_or_default('redolimit'));
        if ($redolimit <= 0) {
            // If they've set redolimit to 0, they don't want to retry failed
            // records at all.
            $redolimit = 0;
            $redoablecount = 0;
        }
        else {
            // Find out how many failed records there are. Since any sent in
            // bulk will be deleted if the request processed successfully, any
            // remaining ones are failed records.
            $redoablecount = count_records(
                'search_elasticsearch_7_queue',
                'status',
                self::queue_status_sent_in_bulk
            );
            $redolimit = min($redolimit, $redoablecount);
            if ($limitto) {
                $redolimit = min($redolimit, $limitto);
                $limitto -= $redolimit;
            }
        }
        // Fetch the records we'll try to insert on this request.
        $records = get_records_array(
            'search_elasticsearch_7_queue',
            'status',
            self::queue_status_new,
            'id',
            '*',
            $limitfrom,
            $limitto
        );

        if (!$records && !$redolimit) {
            return;
        }

        // Fetch the search client.
        if ($ESClient == false) {
          $ESClient = self::make_client('write');
        }

        // Fetch user defined artefact map.
        $artefacttypesmap_array = self::artefacttypesmap_to_array();

        // Process Records from the queue.
        if ($records) {
            list($documents, $deletions) = self::preprocess_queued_items($records, $artefacttypesmap_array);

            // Delete in bulk.
            if ($deletions) {
                $delcount = 0;
                foreach ($deletions as $docs) {
                    $delcount += count($docs);
                }
                log_info("  {$delcount} deletions to index in bulk...");
                self::send_queued_items_in_bulk(
                    $deletions,
                    'process_bulk_deletions',
                    $ESClient,
                    $requestlimit
                );
            }

            // Insert in bulk.
            if ($documents) {
                $doccount = 0;
                foreach ($documents as $docs) {
                    $doccount += count($docs);
                }
                log_info("  {$doccount} documents to index in bulk...");
                self::send_queued_items_in_bulk(
                    $documents,
                    'process_bulk_insertions',
                    $ESClient,
                    $requestlimit
                );
            }
        }

        // Now, pick up any failed ones
        $records = get_records_array('search_elasticsearch_7_queue', 'status', self::queue_status_sent_in_bulk, 'id', '*', 0, $redolimit);
        if ($records) {
            list($documents, $deletions) = self::preprocess_queued_items($records, $artefacttypesmap_array);

            // Delete individually.
            if ($deletions) {
                $delcount = 0;
                foreach ($deletions as $docs) {
                    $delcount += count($docs);
                }
                log_info("  {$delcount} deletions to index individually...");
                self::send_queued_items_individually(
                    $deletions,
                    'process_single_deletion',
                    $ESClient
                );
            }

            // Send individually.
            if ($documents) {
                $doccount = 0;
                foreach ($documents as $docs) {
                    $doccount += count($docs);
                }
                log_info("  {$doccount} documents to index individually...");
                self::send_queued_items_individually(
                    $documents,
                    'process_single_insertion',
                    $ESClient
                );
            }
        }

        // Refresh Index.
        $search_index = PluginSearchElasticsearch7::get_write_indexname();
        $ESClient
            ->indices()
            ->refresh(['index' => $search_index]);
    }

    /**
     * Post install actions.
     *
     * @param int $prevversion Version timestamp
     *
     * @return bool
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('search', 'elasticsearch7', 'host', '127.0.0.1');
            set_config_plugin('search', 'elasticsearch7', 'port', '9200');
            set_config_plugin('search', 'elasticsearch7', 'indexname', 'mahara');
            set_config_plugin('search', 'elasticsearch7', 'analyzer', 'mahara_analyzer');
            set_config_plugin('search', 'elasticsearch7', 'types', 'usr,interaction_instance,interaction_forum_post,group,view,collection,artefact,block_instance');
            set_config_plugin('search', 'elasticsearch7', 'cronlimit', '1500');
            set_config_plugin('search', 'elasticsearch7', 'shards', 5);
            set_config_plugin('search', 'elasticsearch7', 'replicashards', 0);
            $elasticsearchartefacttypesmap = file_get_contents(__DIR__ . '/elasticsearchartefacttypesmap.txt');
            set_config_plugin('search', 'elasticsearch7', 'artefacttypesmap', $elasticsearchartefacttypesmap);
        }
        return true;
    }

    /**
     * Process a set of records from the search_elasticsearch_7_queue.
     *
     * Processes records from the ES7 queue and sorts them into items to insert
     * into or delete from the Elasticsearch index.
     *
     * @param array<object> $records
     * @param array<string,string> $artefacttypesmap_array
     *
     * @return array<array<string,mixed>>
     */
    private static function preprocess_queued_items($records, $artefacttypesmap_array) {
        $documents = [];
        $deletions = [];
        $index = PluginSearchElasticsearch7::get_write_indexname();
        foreach ($records as $record) {
            $deleteitem = false;
            // Each record type has its own class to process it.
            $ES_class = 'Elasticsearch7Type_' . $record->type;
            if (!class_exists($ES_class)) {
                // If new record types are added this will inform us we need to
                // add a class for them.
                log_debug('Class not found: ' . $ES_class);
            }
            // Check if this class is one that we can fetch a record for.
            if (method_exists($ES_class, 'get_record_by_id')) {
                // If a record type requires any extra processing add a new
                // case for it.
                switch ($record->type) {
                    case 'artefact':
                        // The Main and Secondary facet terms for Artefacts are
                        // configured via Artefact types hierarchy on the plugin
                        // config form. These are passed in via the
                        // $artefacttypesmap_array array.
                        $dbrecord = $ES_class::get_record_by_id(
                            $record->type,
                            $record->itemid,
                            $artefacttypesmap_array
                        );
                        break;

                    default:
                        $dbrecord = $ES_class::get_record_by_id(
                            $record->type,
                            $record->itemid
                        );
                }
            }
            else {
                log_debug('@TODO: ' . $ES_class . '::get_record_by_id()');
                $dbrecord = false;
            }

            // If the record has been physically deleted from the DB or if its
            // artefacttype is not selected set $deleteitem.
            if ($dbrecord == false) {
                $deleteitem = true;
            }
            else {
                $item = new $ES_class($dbrecord);
                $deleteitem = $item->getIsDeleted();
                if ($deleteitem == false) {
                    // Add item for bulk index.
                    $documents[$record->type][$record->id] = [
                        'index' => $index,
                        'type'  => $record->type,
                        'id'    => $record->itemid,
                        'body'  => $item->getMapping(),
                    ];
                }
            }

            // Mark item for bulk deletion from index.
            if ($deleteitem == true) {
                $deletions[$record->type][$record->id] = $record->itemid;
            }
        }
        return [$documents, $deletions];
    }

    /**
     * Send a set of items to Elasticsearch in bulk.
     *
     * The $documents top level of the array has keys representing
     * elasticsearch document types.
     * Each of these has a value which is an array of actual Elasticsearch
     * documents or deletion requests, with their key being the matching record
     * in the search_elasticsearch_7_queue table.
     *
     * @param array<string,array<int,mixed>> $documents The documents we are bulk indexing.
     * @param string $process_function The function to process these Documents.
     * @param object $ESClient The Elasticsearch client.
     * @param int $requestlimit The number of items to process in each request.
     *
     * @return void
     */
    private static function send_queued_items_in_bulk($documents, $process_function, $ESClient, $requestlimit) {
        $uploadcount = 0;
        $batchcount = 0;
        $errorcount = 0;

        // Bulk insert into index.
        foreach ($documents as $type => $docs) {
            log_info('Processing: ' . $type . ' with ' . $process_function . '()');
            for ($i = 0; $i < count($docs); $i += $requestlimit) {
                $requestdocs = array_slice($docs, $i, $requestlimit, true);
                $ids = array_keys($requestdocs);
                $questionmarks = implode(',', array_fill(0, count($ids), '?'));
                $time = db_format_timestamp(time());

                // Mark them before sending, in case the request fails.
                $sql = 'UPDATE {search_elasticsearch_7_queue} SET status = ?, lastprocessed = ? WHERE id IN (' . $questionmarks . ')';
                execute_sql(
                    $sql,
                    array_merge(
                        [self::queue_status_sent_in_bulk, $time],
                        $ids
                    )
                );

                // Send them.
                try {
                    $batchcount++;
                    $uploadcount += count($requestdocs);
                    if ($batchcount % 10 == 0) {
                        log_info("    batches: {$batchcount}; records: {$uploadcount}; errors: {$errorcount}");
                    }
                    $response = self::$process_function($requestdocs, $type, $ESClient);

                    $ESError = false;
                    $ESErrors = [];
                    if (!empty($response['errors']) && $response['errors']) {
                        $ESError = $response['errors'];
                        for ($j = 0; $j < count($response['items']); $j++) {
                            if (!empty($response['items'][$j]['index']['error']['reason'])) {
                                $ESErrors[] = $response['items'][$j]['index']['error']['reason'];
                            }
                        }
                        $ESErrors = array_unique($ESErrors);
                    }

                    if (!empty($ESError)) {
                        log_warn("Error from Elasticsearch trying to send bulk request at time {$time}: " . $ESError);
                        $errorcount++;
                        if (!empty($ESErrors)) {
                            log_warn("Error messages: " . implode(", ", $ESErrors));
                        }
                    }
                    else {
                        // Delete them since they've been sent successfully.
                        delete_records_select('search_elasticsearch_7_queue', 'id IN (' . $questionmarks. ')', $ids);
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
     * @param array<int> $records The record IDs for deletion.
     * @param string $type Used to build the ID we are deleting.
     * @param object $ESClient The Elasticsearch client.
     *
     * @return array<mixed>
     */
    private static function process_bulk_deletions($records, $type, $ESClient) {
        $params = [];
        $index = PluginSearchElasticsearch7::get_write_indexname();
        foreach ($records as $record) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $index,
                    '_id'    => $type . $record,
                ],
            ];
        }

        return $ESClient->bulk($params);
    }

    /**
     * @param array<array<string,mixed>> $records The record IDs for deletion.
     * @param string $type Used to build the ID we are deleting.
     * @param object $ESClient The Elasticsearch client.
     *
     * @return array<mixed> The result of the processing.
     */
    private static function process_bulk_insertions($records, $type, $ESClient) {
        $params = ['body' => []];
        $index = PluginSearchElasticsearch7::get_write_indexname();
        foreach ($records as $record) {
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                    '_id'    => $type . $record['id'],
                ],
            ];
            $params['body'][] = $record['body'];
        }

        $response = $ESClient->bulk($params);

        return $response;
    }

    /**
     * Send a set of items to Elasticsearch individually.
     *
     * The $documents array:
     *   The top level has keys representing elasticsearch document types. Each
     *   of these has a value which is an array of actual Elasticsearch
     *   documents or deletion requests, with their key being the matching
     *   record in the search_elasticsearch_7_queue table.
     *
     * @param array<string,array<string,mixed>> $documents A multi-dimensional array.
     * @param string $process_function Callback function to process documents.
     * @param object $ESClient The search client.
     *
     * @return void
     */
    private static function send_queued_items_individually($documents, $process_function, $ESClient) {
        $uploadcount = 0;
        $errorcount = 0;

        // Bulk insert into index.
        foreach ($documents as $type => $docs) {
            foreach ($docs as $queueid => $doc) {
                $this_record = [];
                $this_record['id'] = $queueid;
                $this_record['status'] = self::queue_status_sent_individually;
                $this_record['lastprocessed'] = db_format_timestamp(time());
                update_record(
                    'search_elasticsearch_7_queue',
                    $this_record
                );
                // Send it.
                try {
                    $uploadcount++;
                    if ($uploadcount % 20 == 0) {
                        // Report every 20 records.
                        log_info("    uploads: {$uploadcount}; errors: {$errorcount}");
                    }
                    $response = self::$process_function($doc, $type, $ESClient);
                    $ESError=false;
                    if (isset($response['errors'])) {
                        $ESError = $response['errors'];
                    }

                    if (!empty($ESError)) {
                        $errorcount++;
                        log_warn('Error from Elasticsearch trying to send individual record ' . $queueid . ': ' . $ESError);
                    }
                    else {
                        // No errors! Go ahead and delete it from the queue.
                        delete_records('search_elasticsearch_7_queue', 'id', $queueid);
                    }
                }
                catch (Exception $e) {
                    $errorcount++;
                    log_warn('Exception sending elasticsearch record ' . $queueid . ': ' . $e->getMessage() );
                }
            }
        }
        // Report the final state of uploads vs errors.
        log_info("    uploads: {$uploadcount}; errors: {$errorcount}");
    }

    /**
     * Process a single record deletion.
     *
     * @param int $id The record id we are deleting. Builds the index ID.
     * @param string $type The type of record. Builds the index ID.
     * @param object $ESClient The Elastic Search client.
     *
     * @return array<mixed> The result from the Client.
     */
    private static function process_single_deletion($id, $type, $ESClient) {
        // https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/deleting_documents.html
        $params = [
            'index' => PluginSearchElasticsearch7::get_write_indexname(),
            'id'    => $type . $id,
        ];

        return $ESClient->delete($params);
    }

    /**
     * Process a single record deletion.
     *
     * @param array<string,mixed> $record The record id we are deleting. Builds the index ID.
     * @param string $type The type of record. Builds the index ID.
     * @param object $ESClient The Elastic Search client.
     *
     * @return array<mixed> The result from the Client.
     */
    private static function process_single_insertion($record, $type, $ESClient) {
        $index = PluginSearchElasticsearch7::get_write_indexname();
        $id = $type . $record['id'];
        $body = (array) $record['body'];
        $body['type'] = $record['type'];

        $params = [
            'index' => $index,
            'type'  => 'doc',
            'id'    => $id,
            'body'  => $body,
        ];

        return $ESClient->index($params);
    }


    /**
     * Search all the things.
     *
     * @param string $query_string The search terms.
     * @param int $limit How many we are returning.
     * @param int $offset Where we start the results from.
     * @param array<string,mixed> $options Additional search options.
     * @param string $mainfacetterm The main facet term we are filtering on.
     * @param string $subfacet The secondary facet term we are filtering on.
     *
     * @return array<string,mixed>
     */
    public static function search_all($query_string, $limit, $offset = 0, $options = [], $mainfacetterm = null, $subfacet = null) {
        global $USER;
        return Elasticsearch7Pseudotype_all::search(
            $query_string,
            $limit,
            $offset,
            $options,
            $mainfacetterm,
            $USER
        );
    }

    /**
     * @param array<string,mixed> $options The search options.
     * @param int $limit                   The number of results to return.
     * @param int $offset                  Where to start from in the results.
     *
     * @return array<string,mixed>
     */
    public static function search_events($options=array(), $limit = 10, $offset = 0) {
        global $USER;
        return Elasticsearch7Type_event_log::search($options, $limit, $offset, $USER);
    }

    /**
     * Return users for the Admin People page.
     *
     * This is handed off to the Internal search call.
     *
     * @see PluginSearchInternal::admin_search_user()
     * @see PluginSearchElasticsearch::admin_search_user()
     * @param array<array<string,string>> $query_string The raw or parsed query string.
     * @param array<array<array<string,string>>> $constraints A list of constraints on the search results.
     * @param int $offset Where to start in the results.
     * @param int $limit Number of rows to return.
     * @param string $sortfield Which of the output columns to sort by
     * @param string $sortdir Sort direction. DESC or ASC.
     *
     * @return array<string,mixed> The results of the search.
     */
    public static function admin_search_user($query_string, $constraints, $offset, $limit, $sortfield, $sortdir) {
        // We need to fudge some stuff before sending it on because
        // get_admin_user_search_results() in lib/searchlib.php has some
        // hard-coded special functionality for the internal search plugin.
        if (is_array($query_string) && count($query_string) > 0) {
            $query_string = $query_string[0]['string'];
        }
        else {
            $query_string = "";
        }

        return PluginSearchInternal::admin_search_user($query_string, $constraints, $offset, $limit, $sortfield, $sortdir);
    }

    /**
     * Creates an \Elasticsearch\Client object, filling in the host and
     * port with the values from the elasticsearch plugin's admin screen.
     * If you wanted to make other changes to how we connect to elasticsearch,
     * this would be a good place to do it.
     *
     * @param string $type Expects 'read' or 'write'.
     *
     * @return Elasticsearch\Client
     */
    public static function make_client($type='read') {
        $clientopts = self::get_client_config($type);
        $clientBuilder = ClientBuilder::create();
        $clientBuilder
            ->setHosts($clientopts['hosts'])
            ->setConnectionParams([
                'client' => [
                    'curl' => $clientopts['curlopts']
                ]
            ]);
        if (!empty($clientopts['hosts'][0]['username'])) {
            $clientBuilder->setBasicAuthentication(
                $clientopts['hosts'][0]['username'],
                $clientopts['hosts'][0]['password']
            );
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
        $indexname = self::config_value_or_default('bypassindexname');
        if (!$indexname) {
            $indexname = self::config_value_or_default('indexname');
        }
        return $indexname;
    }

    /**
     * Builds the "results" table seen on the universal search results page.
     *
     * The results are updated on the $data array.
     *
     * @param array<string,mixed> $data
     *
     * @return void
     */
    public static function build_results_html(&$data) {

       $smarty = smarty_core();
       $smarty->assign('thispath', 'search/elasticsearch7/index.php');
       $smarty->assign('data', !empty($data['data']) ? $data['data'] : null);

       $params = array();
       if (isset($data['query'])) {
           $params['query'] = $data['query'];
       }
       else {
        $params['query'] = '';
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

       $resultcounttextsingular = get_string('record', 'search.elasticsearch7');
       $resultcounttextplural = get_string('records', 'search.elasticsearch7');

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

       // Only show licence if Text or Media tab is selected and license
       // metadata site config is set.
       if (isset($data['license_on']) &&
           isset($data['license_options']) &&
           isset($data['selected']) &&
           ($data['selected'] == 'Media' || $data['selected'] == 'Text')
        ) {
           $smarty->assign('license_on', $data['license_on']);
           $smarty->assign('license_options', $data['license_options']);
       }

       if (isset($data['type'])) {
           $smarty->assign('type', $data['type']);
       }
       $smarty->assign('query', $params['query']);

       $data['tablerows'] = $smarty->fetch('Search:elasticsearch7:searchresults.tpl');

       $pagination = build_pagination(array(
            'id' => 'elasticsearch_pagination',
            'url' => get_config('wwwroot') . 'search/index.php?' . http_build_query($params),
            'jsonscript' => 'search/elasticsearch7/json/search.php',
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
    * Tweak the searchform array.
    *
    * @param array<string,mixed> $searchform The Pieform array.
    *
    * @return void
    */
    public static function tweak_searchform(&$searchform) {
        $classes = explode(' ', $searchform['class']);
        $classes[] = 'elasticsearch-form';
        $classes[] = 'elasticsearch7-form';
        $classes = array_unique($classes);
        $searchform['class'] =  implode(' ', $classes);
    }

    /**
    * Tweak the searchform JS array.
    *
    * @param array<string> $javascript
    *
    * @return void
    */
    public static function tweak_searchform_js(&$javascript) {
        $javascript[] = 'search/elasticsearch7/js/filter.js';
        $javascript = array_unique($javascript);
    }

    /**
    * Tweak the settings form JS array.
    *
    * @param array<string> $javascript
    *
    * @return void
    */
    public static function tweak_settingsform_js(&$javascript) {
        $javascript[] = "js/mahara.js";
        $javascript[] = 'search/elasticsearch7/js/settings.js';
        $javascript = array_unique($javascript);
    }

    /**
    * Callback to determine if a string is a plugin status.
    *
    * @param string $string The string being checked.
    * @return bool True if this is a plugin status callback.
    */
    private static function is_plugin_status($string) {
        if (strpos($string, 'plugin_status_') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Plugin Status checks.
     *
     * These callbacks need to return an array with the following keys.
     *
     * The div wrapping a status check is using the Bootstrap alert classes.
     * The class in the HTML is of the form 'alert alert-success'.
     * Expected values
     * * success - OK
     * * warning - Not quite right, but still functioning.
     * * danger  - An error that prevents the system from working.
     *
     * title (string): The name of this check.
     * class (string): A bootstrap 'alert alert-[class]'.
     * message (string): A message explaining the status.
     * result (float): A value from 0 to 1 depending on how successful the
     *   checks were.
     */

    /**
     * Check the status of cron.
     *
     * @return array<string,mixed> The status for this item.
     */
    private static function plugin_status_cron() {
        $status_class = 'warning';
        $message = '@TODO';
        $result = 0;

        // Check that we have a search_cron record.
        $cron_record = get_record('search_cron', 'plugin', 'elasticsearch7');
        if ($cron_record) {
            if ($cron_record->nextrun > date('Y-m-d H:i:s')) {
                // Cron is set to run in the future.
                $result = 1;
                $status_class = 'success';
                $message = "OK";
            }
            else if (is_null($cron_record->nextrun)) {
                $status_class = 'warning';
                $result = 0.75;
                $message = "Cron is set but has not run yet. Run cron or check again later.";
            }
            else if ($cron_record->nextrun < date('Y-m-d H:i:s', strtotime("+15 minutes"))) {
                $status_class = 'success';
                $result = 1;
                $message = "Cron is set in the next 15 minutes.";
            }
            else {
                $message = "Cron is set but appears to be stuck in the past. Next run is set to " . $cron_record->nextrun;
                // We at least found the record.
                $result = 0.5;
            }
        }
        else {
            $message = 'Elasticsearch7 is not setup to run on cron. Please reinstall the plugin.';
        }

        $status = [
            'title' => get_string('pluginstatustitlecron', 'search.elasticsearch7'),
            'class' => 'alert alert-' . $status_class,
            'message' => $message,
            'result' => $result,
        ];
        return $status;
    }

    /**
     * Check the status of access to the server.
     *
     * @return array<string,mixed> The status for this item.
     */
    private static function plugin_status_access() {
        $status_class = 'success';
        $message = 'OK';
        $result = 1;

        list($status, $server) = self::get_server_info('clusterhealth');

        if (!$status) {
            $errorstr = get_string('error', 'search.elasticsearch7');
            if (isset($server->data->error->reason)) {
                $errorstr .= $server->data->error->reason;
            }
            else if (isset($server->error)) {
                $errorstr .= $server->error;
                if (strpos($server->error, 'SSL certificate problem') !== false) {
                    $errorstr .= '. ' . get_string('pluginstatusignoresslerror', 'search.elasticsearch7');
                }
            }
            else {
                $errorstr .= get_string('errorunknown', 'search.elasticsearch7');
            }
            $message = $errorstr;
            $result = 0;
            $status_class = 'danger';
        }

        $status = [
            'title' => get_string('pluginstatustitleaccess', 'search.elasticsearch7'),
            'class' => 'alert alert-' . $status_class,
            'message' => $message,
            'result' => $result,
        ];
        return $status;
    }

    /**
     * Check the status of the server.
     *
     * @return array<string,mixed> The status for this item.
     */
    private static function plugin_status_server_health() {
        $status_class = 'danger';
        $message = '@TODO';
        $result = 0;

        list($status, $server) = self::get_server_info();

        if ($status) {
            $result = 1;
            $message = 'OK';
            $status_class = 'success';
        }
        else {
            $message = "Server(s) are not running or not accessible.";
        }

        $status = [
            'title' => get_string('pluginstatustitleserver_health', 'search.elasticsearch7'),
            'class' => 'alert alert-' . $status_class,
            'message' => $message,
            'result' => $result,
        ];
        return $status;
    }

    /**
     * Check the status of the cluster health.
     *
     * @return array<string,mixed> The status for this item.
     */
    private static function plugin_status_cluster_health() {
        $status_class = 'danger';
        $message = '@TODO';
        $result = 0;

        list($status, $server) = self::get_server_info('clusterhealth');

        if ($status) {
            switch ($server->data->status) {
                case 'green':
                    $message = 'OK';
                    $result = 1;
                    $status_class = 'success';
                    break;

                case 'yellow':
                    $message = 'All primary shards are assigned, but one or more replica shards are unassigned. If a node in the cluster fails, some data could be unavailable until that node is repaired.';
                    $result = 0.75;
                    $status_class = 'warning';
                    break;

                case 'red':
                    $message = 'One or more primary shards are unassigned, so some data is unavailable. This can occur briefly during cluster startup as primary shards are assigned.';
                    $result = 0.5;
                    $status_class = 'warning';
                    break;

                default:
                    $message = 'Unknown status';
                    $result = 0;

            }

        }
        else {
            $message = "Server(s) are not running or not accessible.";
        }

        $status = [
            'title' => get_string('pluginstatustitlecluster_health', 'search.elasticsearch7'),
            'class' => 'alert alert-' . $status_class,
            'message' => $message,
            'result' => $result,
        ];
        return $status;
    }

    /**
     * Return report results for group activity.
     *
     * @param string $start The date in YYY-mm-dd format.
     * @param string $end The date in YYY-mm-dd format.
     * @param string $sorttype What we are sorting on, if anything.
     * @param int $count
     * @param string $sortdesc The direction from the URL.
     *
     * @return array<int,array<mixed>> The $aggmap and $groupids.
     */
    public static function report_group_stats_table($start, $end, $sorttype, $count, $sortdesc) {
        $aggmap = [];
        $groupids = [];

        switch ($sorttype) {
            case "groupcomments":
                $sortdirection = array('EventTypeCount' => $sortdesc);
                $sortorder = "(doc.event.value == 'saveartefact' && doc.resourcetype.value == 'comment' && doc.ownertype.value == 'group') ? 1 : 0";
                $sorttypeaggmap = '|saveartefact|comment|group';
                break;

            case "sharedviews":
                $sortdirection = array('EventTypeCount' => $sortdesc);
                $sortorder = "(doc.event.value == 'updateviewaccess' && doc.resourcetype.value == 'group' && doc.ownertype.value == 'user') ? 1 : 0";
                $sorttypeaggmap = '|updateviewaccess|group|user';
                break;

            case "sharedcomments":
                $sortdirection = array('EventTypeCount' => $sortdesc);
                $sortorder = "(doc.event.value == 'sharedcommenttogroup' && doc.resourcetype.value == 'comment' && doc.ownertype.value == 'group') ? 1 : 0";
                $sorttypeaggmap = '|sharedcommenttogroup|comment|group';
                break;

            default:
                $sortdirection = [];
                $sortorder = 1;
                $sorttypeaggmap = '';
        }


        $params = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'ctime' => [
                                'gte' => $start . ' 00:00:00',
                                'lte' => $end . ' 23:59:59',
                            ],
                        ],
                    ],
                    "should" => [
                        'multi_match' => [
                            'query' => 'group',
                            'fields' => [
                                'ownertype',
                                'resourcetype',
                            ],
                        ],
                    ],
                    "must" => [
                        "term" => [
                            "indexsourcetype" => "event_log",
                        ],
                    ],
                ],
            ],
            'aggs' => [
                'GroupId' => [
                    'terms' => [
                        'field' => 'ownerid',
                        'order' => $sortdirection,
                        'size' => $count,
                    ],
                    'aggs' => [
                        'EventType' => [
                            'terms' => [
                                'field' => 'event',
                                'min_doc_count' => 0,
                            ],
                            'aggs' => [
                                'ResourceType' => [
                                    'terms' => [
                                        'field' => 'resourcetype',
                                    ],
                                    'aggs' => [
                                        'OwnerType' => [
                                            'terms' => [
                                                'field' => 'ownertype',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (empty($sortdirection)) {
            unset($params['aggs']['GroupId']['terms']['order']);
        }

        $aggregates = PluginSearchElasticsearch7::search_events($params, 0, 0);
        if ($aggregates['totalresults'] > 0) {
            Elasticsearch7Type_event_log::process_aggregations($aggmap, $aggregates['aggregations'], true, array('GroupId', 'EventType', 'ResourceType', 'OwnerType'));
            if (!empty($aggregates['aggregations']['GroupId']['buckets'])) {
                $groups = array_slice($aggregates['aggregations']['GroupId']['buckets'], 0, null, true);
                foreach($groups as $k => $group) {
                    if (isset($aggmap[$group['key'] . $sorttypeaggmap]) && $aggmap[$group['key'] . $sorttypeaggmap] > 0) {
                        $groupids[$k] = $group['key'];
                    }
                }
            }
        }
        return [$aggmap, $groupids];
    }

    /**
     * Return report results for collaboration activity.
     *
     * @param array<int> $usrids The User IDs we may be filtering on.
     * @param string $start The date in YYY-mm-dd format.
     * @param string $end The date in YYY-mm-dd format.
     *
     * @return array<array<mixed>> The $aggmap and $aggregates.
     */
    public static function report_collaboration_stats_table($usrids, $start, $end) {
        $params = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'ctime' => [
                                'gte' => $start . ' 00:00:00',
                                'lt' => $end . ' 00:00:00'
                            ]
                        ]
                    ]
                ]
            ],
            'sort' => [
                'ctime' => 'desc'
            ],
            'aggs' => [
                'YearWeek' => [
                    'terms' => [
                        'field' => 'yearweek',
                    ],
                    'aggs' => [
                        'EventType' => [
                            'terms' => [
                                'field' => 'event',
                            ],
                            'aggs' => [
                                'ResourceType' => [
                                    'terms' => [
                                        'field' => 'resourcetype',
                                    ],
                                    'aggs' => [
                                        'ParentResourceType' => [
                                            'terms' => [
                                                'field' => 'parentresourcetype',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (!empty($usrids)) {
            $params['query'] = [
                'terms' => [
                    'usr' => $usrids
                ],
            ];
        }

        $aggregates = PluginSearchElasticsearch7::search_events($params, 0, 0);
        $aggmap = [];
        if ($aggregates['totalresults'] > 0) {
            Elasticsearch7Type_event_log::process_aggregations($aggmap, $aggregates['aggregations'], true, array('YearWeek', 'EventType', 'ResourceType', 'ParentResourceType'));
        }
        return [$aggmap, $aggregates];
    }

    /**
     * Return report results on Event Log activity.
     *
     * @param array<int> $usrids
     * @param array<string,array<mixed>> $result
     * @param string $sortdirection
     * @param string $sortdesc The direction from the URL.
     * @param string $sortorder
     * @param string $sortname
     * @param int $count
     *
     * @return array<array<mixed>>
     */
    public static function report_useractivity_stats_table($usrids, $result, $sortdirection, $sortdesc, $sortorder, $sortname, $count) {
        $aggmap = [];
        $params = [

            'bool' => [
                'must' => [
                    ['terms' => ['usr' => $usrids]],
                ],
                'filter' => [
                    'range' => [
                        'ctime' => [
                            'gte' => $result['settings']['start'] . ' 00:00:00',
                            'lte' => $result['settings']['end'] . ' 23:59:59',
                            'relation' => 'within',
                        ]
                    ]
                ]
            ],

            'aggs' => [
                'UsrId' => [
                    'terms' => [
                        'field' => 'usr',
                        'order' => $sortdirection,
                        'size' => $count,
                    ],
                    'aggs' => [
                        'EventType' => [
                            'terms' => [
                                'field' => 'event',
                                'min_doc_count' => 0,
                            ],
                        ],
                        'LastLogin' => [
                            'max' => [
                                'field' => 'ctime'
                            ],
                        ],
                        'LastActivity' => [
                            'max' => [
                                'field' => "id",
                            ],
                        ],
                    ],
                ]
            ]
        ];
        if (empty($sortdirection)) {
            unset($params['aggs']['UsrId']['terms']['order']);
        }

        $aggregates = self::search_events($params, 0, 0);
        if ($aggregates['totalresults']['value'] > 0) {
            foreach ($aggregates['aggregations']['UsrId']['buckets'] as $k => $usr) {
                $user = new User();
                $user->find_by_id($usr['key']);
                $aggregates['aggregations']['UsrId']['buckets'][$k]['firstname'] = $user->get('firstname');
                $aggregates['aggregations']['UsrId']['buckets'][$k]['lastname'] = $user->get('lastname');
                $aggregates['aggregations']['UsrId']['buckets'][$k]['username'] = $user->get('username');
                $aggregates['aggregations']['UsrId']['buckets'][$k]['preferredname'] = $user->get('preferredname');
            }
            if (!empty($sortname)) {
                usort($aggregates['aggregations']['UsrId']['buckets'], function ($a, $b) use ($sortname) {
                    return strnatcasecmp($a[$sortname], $b[$sortname]);
                });
                if ($sortdesc == 'desc') {
                    $aggregates['aggregations']['UsrId']['buckets'] = array_reverse($aggregates['aggregations']['UsrId']['buckets']);
                }
            }
            Elasticsearch7Type_event_log::process_aggregations($aggmap, $aggregates['aggregations'], true, array('UsrId', 'EventType'));
        }
        return [$aggmap, $aggregates];
    }

    /**
     * Check the status of the index.
     *
     * @return array<string,mixed> The status for this item.
     */
    private static function plugin_status_index_status() {
        $status_class = 'default';
        $message = 'OK';
        $result = 0;

        list($status, $server) = self::get_server_info('indexstatus');

        switch ($server->info['http_code']) {
            case 200:
                $status_class = 'success';
                $result = 1;
                break;

            case 403:
                $status_class = 'danger';
                $message = $server->error . '. ' . get_string('pluginstatusmessageindex404', 'search.elasticsearch7');
                break;

            case 404:
                $status_class = 'danger';
                $message = $server->error . '. ' . get_string('pluginstatusmessageindex404', 'search.elasticsearch7');
                break;

            default:
                $status_class = 'warning';
        }

        $status = [
            'title' => get_string('pluginstatustitleindexstatus', 'search.elasticsearch7'),
            'class' => 'alert alert-' . $status_class,
            'message' => $message,
            'result' => $result,
        ];
        return $status;
    }

    /**
     * Return commands for mash
     *
     * @see The Mahara Shell
     * @return array<array<string,mixed>> The commands array.
     */
    public static function mashGetCommands() {
        $commands = [];
        if (defined('CLI')) {
            $commands[] = [
                'title' => 'Search Queue',
                'description' => 'a summary of items in the search queue that are ready for indexing.',
                'name' => 'search-queue',
                'shortname' => 'sq',
                'method' => 'cliSearchQueue',
                'options' => [
                    'follow' => [
                        'alias' => 'f',
                        'default' => true,
                        'help' => 'Continuous monitoring of the queue.  CTRL-c to exit.'
                    ],
                    'delay' => [
                        'alias' => 'd',
                        'filter' => 'int',
                        'default' => 2,
                        'help' => 'Number of seconds between refreshes. Minimum 2. Works with --follow.'
                    ]
                ]
            ];
            $commands[] = [
                'title' => 'Reset search index',
                'description' => 'WARNING: destructive action. This will delete the index and requeue all content for indexing.',
                'name' => 'search-reset-index',
                'method' => 'cliResetIndex',
            ];
            $commands[] = [
                'title' => 'Fast index',
                'description' => 'Indexing without webserver restricted batching.',
                'name' => 'search-fast-index',
                'method' => 'cliFastIndex',
            ];
            $commands[] = [
                'title' => 'Reset search cron lock',
                'description' => 'Manually remove the cron lock.',
                'name' => 'search-reset-cron-lock',
                'method' => 'cliResetCronLock',
            ];
            $commands[] = [
                'title' => 'Show Elasticsearch config',
                'description' => 'View the current Elasticsearch config the site is using.',
                'name' => 'search-show-config',
                'method' => 'cliShowConfig',
            ];
        }
        return $commands;
    }

    /**
     * Presents a summary of the content yet to be indexed.
     *
     * @param array $args CLI arguments.
     *
     * @return bool|void
     */
    public static function cliSearchQueue($args) {
        if (!defined('CLI')) {
            return;
        }

        $types = self::get_artefact_types();
        $item_by_type_in_queue = [];
        if (count($types) > 0) {
            $sql = 'SELECT type, count(*) AS total FROM search_elasticsearch_7_queue GROUP BY type';
            $rs = get_records_sql_array($sql, []);
            if ($rs) {
                foreach ($rs as $record) {
                    $item_by_type_in_queue[$record->type] = $record->total;
                }
            }
        }
        if (count($item_by_type_in_queue)) {
            foreach ($types as $type) {
                if (array_key_exists($type, $item_by_type_in_queue)) {
                    $msg = sprintf("%' 25s : %' 5d", $type, $item_by_type_in_queue[$type]);
                    echo $msg;
                }
            }
        }
        else {
            echo "There are no items in the queue for indexing.";
        }
        return true;
    }

    /**
     * "Clicks" the Reset all button on the admin form.
     *
     * @param object $cli The CLI class.
     *
     * @return bool|void
     */
    public static function cliResetIndex($cli) {
        if (!defined('CLI')) {
            return;
        }

        self::reset_search_index();
        return true;
    }

    /**
     * Unlock cron when it gets jammed.
     *
     * Deletes _cron_lock_search_elasticsearch7_cron from the config table.
     *
     * @param object $cli The CLI class.
     *
     * @return bool|void
     */
    public static function cliResetCronLock($cli) {
        if (!defined('CLI')) {
            return;
        }

        delete_records('config', 'field', '_cron_lock_search_elasticsearch7_cron');
        return true;
    }

    /**
     * Performs rapid indexing.
     *
     * Not running through the webserver means we can skip a lot of batching.
     *
     * @param object $cli The CLI class.
     *
     * @return bool|void
     */
    public static function cliFastIndex($cli) {
        if (!defined('CLI')) {
            return;
        }

        // First check that there isn't an elasticsearch cron indexing the site
        if (self::check_cron_lock()) {
            $msg = get_string('indexingrunning', 'search.elasticsearch7') .
                "\n" .
                get_string('indexingrunningtry', 'search.elasticsearch7');
            $cli->cli_exit($msg, true);
        }

        $start_time = time();
        // Lock cron so it does not mess with out processing.
        $cron_locked_at = self::lock_cron();

        $sql = "SELECT COUNT(*) FROM {search_elasticsearch_7_queue} WHERE status != ?";
        $total = $remaining = count_records_sql($sql, [2]);
        $ESClient = self::make_client('write');
        $t = 0;
        while ($remaining) {
            // Process the queue. This will take into account the cron limits
            // which is why we're in a loop.
            $t = self::timer($t, 'Start');
            self::index_queued_items($ESClient);
            $t = self::timer($t, 'End');

            // Check if there are more to process.
            $sql = "SELECT COUNT(*) FROM {search_elasticsearch_7_queue} WHERE status != ?";
            $remaining = count_records_sql($sql, [2]);
            $cli->cli_print($remaining . ' of ' . $total . ' remaining.');
        }

        // Unlock cron.
        self::unlock_cron($cron_locked_at);
        // Let cron know it can run on the next request.
        update_record(
            'search_cron',
            ['nextrun' => NULL],
            [
                'plugin' => 'elasticsearch7',
                'callfunction' => 'cron'
            ]
        );

        return true;
    }

    public static function cliShowConfig($cli) {
        global $CFG;
        $config = (array)$CFG;
        $ret = [];
        // Used for layout in the sprintf template.
        $key_length = 0;
        foreach ($config as $key => $val) {
            if (strpos($key, 'elasticsearch') !== false) {
                $ret[$key] = $val;
                if ($key_length < strlen($key)) {
                    $key_length = strlen($key);
                }
            }
        }
        $key_length++;
        $template = "%-" . $key_length . "s : %.s";
        foreach ($ret as $key => $val) {
            $cli->cli_print(sprintf($template, $key, $val));
        }
    }

    /**
     * Rudimentary timer.
     *
     * @param int $timestamp The timestamp to work from.
     * @param string $label The label to display.
     *
     * @return int The new timestamp.
     */
    public static function timer($timestamp, $label) {
        $now = time();
        if ($timestamp) {
            $time_passed = $now - $timestamp;
            echo sprintf("%' 25s : %' 5d seconds\n", $label, $time_passed);
        }

        return $now;
    }

    /**
     * Returns the list of tasks available to the Monitor module.
     *
     * @return array<string> The list of callback functions for monitor tasks.
     */
    public static function monitor_task_list() {
        return [
            'monitor_get_failed_queue_size',
            'monitor_is_queue_older_than',
            'monitor_get_unprocessed_queue_size',
        ];
    }

    /**
     * Return the monitor table title.
     *
     * @return string
     */
    public static function monitor_title() {
        return get_string('monitorqueuestatus', 'search.elasticsearch7');
    }

    public static function monitor_subnav_title() {
        return get_string('monitorsubnavtitle', 'search.elasticsearch7');
    }

    /**
     * Report on records in a failed status state.
     *
     * Check out queue table for items with a status <> 0 and a lastprocessed
     * greater than an hour.
     *
     * @see htdocs/module/monitor/monitor.php
     * @return array<string,string>
     */
    public static function monitor_get_failed_queue_size() {
        $ret = [];
        $params = [];

        // Should this be a configurable var?
        $params[] = db_format_timestamp(time() - 3600);

        $sql = "SELECT count(id)
                FROM {search_elasticsearch_7_queue}
                WHERE lastprocessed IS NOT NULL
                AND lastprocessed < ?
                AND status <> 0";
        $ret['value'] = get_field_sql($sql, $params);
        $ret['task'] = get_string('monitorfailedqueuesize', 'search.elasticsearch7');

        return $ret;
    }

    /**
     * Take a min record ID which has status = 0 and time seen.
     *
     * Upon each refresh of stat we check if that record is still there with
     * status = 0.
     * If yes, then we show something on the statistics page.
     * If it is not there anymore.
     *
     * The 'status' and 'hours' are additional keys for the CLI.
     *
     * @return array<string,mixed> A task with an additional 'status' and 'hours' key.
     */
    public static function monitor_is_queue_older_than() {
        $hours = PluginModuleMonitor::get_config_value('configmonitortype_searchhoursuntilold');
        $seconds = $hours * 60 * 60;
        $ret = [
            'task'  => get_string('monitorqueuehasolditems', 'search.elasticsearch7', $hours, $hours),
            'value'  => get_string('no', 'mahara'),
            'status' => false,
        ];

        // Get the last unprocessed search record id from config.
        $rec = self::get_last_unprocessed_search_record_id();
        if ($rec && $rec['timestamp'] + $seconds < time()) {
            // Now we need to check if the same $rec is still in the queue with
            // status 0.
            $id_check = get_field(
                'search_elasticsearch_7_queue',
                'id',
                'id',
                $rec['id'],
                'status',
                '0'
            );
            if ($id_check) {
                $ret['value'] = get_string('yes', 'mahara');
                $ret['status'] = true;
                return $ret;
            }
            else {
                // Otherwise it has gone already and we don't consider it as
                // "old". Pick it from the current elasticsearch7 queue and
                // save it for the next use.
                $currentminimalsearchid = get_field(
                    'search_elasticsearch_7_queue',
                    'min(id)',
                    'status',
                    '0'
                );
                if ($currentminimalsearchid) {
                    self::set_last_minimalsearchid($currentminimalsearchid, time());
                }
                else {
                    // The queue is empty of items with a status of 0.
                    self::clear_last_minimalsearchid();
                }
                return $ret;
            }
        }
        else {
            // Then $rec is empty or is not old.
            return $ret;
        }
        return $ret;
    }

    /**
     * Get the last unprocessed search record id from config.
     *
     * We always look for the minimal one. If there is no such thing in config,
     * then we get it from the current elasticsearch queue and pretend it's the
     * last one. And in this case we save it in config and return it.
     *
     * @return array|bool Array of record ID and timestamp when set or false.
     */
    public static function get_last_unprocessed_search_record_id() {
        $result = array(
            'id' => get_config_plugin('module', 'monitor', 'searchlastminimalsearchid'),
            'timestamp' => get_config_plugin('module', 'monitor', 'searchlastminimalsearchidtime')
        );
        if ($result['id'] && $result['timestamp']) {
            return $result;
        }
        else {
            // Pick it from the current elasticsearch queue and save it for the next use.
            $currentminimalsearchid = get_field(
                'search_elasticsearch_7_queue',
                'min(id)',
                'status',
                '0'
            );
            if ($currentminimalsearchid) {
                self::set_last_minimalsearchid($currentminimalsearchid, time());
            }
            return false;
        }
    }

    /**
     * Save given record ID and timestamp in plugin's config table.
     *
     * @param int $id ID we are recording.
     * @param int $time Timestamp to record with the ID.
     */
    public static function set_last_minimalsearchid($id=null, $time=null) {
        set_config_plugin('module', 'monitor', 'searchlastminimalsearchid', $id);
        set_config_plugin('module', 'monitor', 'searchlastminimalsearchidtime', $time);
    }

    /**
     * Clear the last minimal search ID.
     *
     * @return void
     */
    public static function clear_last_minimalsearchid() {
        self::set_last_minimalsearchid();
    }

    public static function monitor_get_unprocessed_queue_size() {
        $ret = [];
        $size = count_records('search_elasticsearch_7_queue', 'status', '0');
        $ret = array(
            'task' => get_string('monitorunprocessedqueuesize', 'search.elasticsearch7'),
            'value' => $size,
        );
        return $ret;
    }

    /**
     * Message to display the queue size check fails.
     *
     * @return string
     */
    public static function monitor_get_failed_queue_size_message() {
        return get_string('cligetfailedqueuesizemessage', 'search.elasticsearch7');
    }

    /**
     * Message to display if the queue has old items in it.
     *
     * @return string
     */
    public static function monitor_is_queue_older_than_message() {
        $hours = PluginModuleMonitor::get_config_value('configmonitortype_searchhoursuntilold');
        return get_string('cliisqueueolderthanmessage', 'search.elasticsearch7', $hours, $hours);
    }

    /**
     * Message to display if the search queue is in a good state.
     *
     * @return string
     */
    public static function monitor_checking_search_succeeded_message() {
        return get_string('clicheckingsearchsucceededmessage', 'search.elasticsearch7');
    }

}

$indexed_types = explode(',', PluginSearchElasticsearch7::config_value_or_default('types'));
for ($i = 0; $i < count($indexed_types); $i++) {
    $index_file_path = dirname(__FILE__) . '/Elasticsearch7Type_' . $indexed_types[$i] . '.php';
    require_once($index_file_path);
}
