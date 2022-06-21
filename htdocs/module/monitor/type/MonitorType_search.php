<?php

class MonitorType_search extends MonitorType {

    /**
     * Does this Monitor Type have its own config?
     *
     * @return boolean
     */
    public static function has_config() {
        return true;
    }

    /**
     * The config specific to the search monitor type.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function config_elements() {
        return [
            'configmonitortype_searchhoursuntilold' => [
                'title' => get_string('configmonitortype_searchhoursuntiloldtitle', 'module.monitor'),
                'description' => get_string('configmonitortype_searchhoursuntilolddescription', 'module.monitor'),
                'type' => 'text',
                'defaultvalue' => PluginModuleMonitor::get_config_value('configmonitortype_searchhoursuntilold'),
                'rules' => array(
                    'integer' => true,
                    'required' => true,
                    'maxlength' => 2,
                    'minvalue' => 1
                ),
            ],
        ];
    }

    /**
     * Allows for saving of our config elements.
     *
     * @param mixed $values
     */
    public static function save_config_options($values) {
        set_config_plugin('module', 'monitor', 'configmonitortype_searchhoursuntilold', $values['configmonitortype_searchhoursuntilold']);
    }

    /**
     * Get the number of records in table search_elasticsearch_queue where status = 0 which means unprocessed.
     * Such return structure is needed for displaying stuff in UI by means of monitor.tpl.
     *
     * @return array of [task, value] with 'task' saying this is unprocessed queue size and 'value' with amount of such records.
     */
    public static function get_unprocessed_queue_size() {
        if ($search_class = does_search_plugin_have('monitor_get_unprocessed_queue_size')) {
            return $search_class::monitor_get_unprocessed_queue_size();
        }
        else {
            return false;
        }
    }

    /**
     * Get the amount of records from search_elasticsearch_queue table which have timestamp older than 1 hour and status <> 0.
     * Those records were sent to Elasticsearch engine for indexing but have been failing for more than 1 hour for some reason.
     * Here we just calculate how many of them we have.
     * Such return structure is needed for displaying stuff in UI by means of monitor.tpl.
     *
     * @return array [task, value] with 'task' saying this is a failed queue size and 'value' designating amount of failed records.
     */
    public static function get_failed_queue_size() {
        if ($search_class = does_search_plugin_have('monitor_get_failed_queue_size')) {
            return $search_class::monitor_get_failed_queue_size();
        }
        else {
            return false;
        }
    }

    // public static function get_hours_to_consider_elasticsearch_record_old() {
    //     $hours = PluginModuleMonitor::get_config_value('hourstoconsiderelasticsearchrecordold');
    //     return $hours;
    // }
    /**
     * Take a min record ID which has status = 0 and time we noticed it was there.
     * Then upon each refresh of stat we check if that record is still there with status = 0.
     * If yes, then we show something on the statistics page.
     * If it is not there anymore.
     * Such return structure is needed for displaying stuff in UI by means of monitor.tpl.
     *
     * @return array [task, value, status] with 'task' saying if queue is old and 'value' as Yes/No.
     */
    public static function is_queue_older_than() {
        if ($search_class = does_search_plugin_have('monitor_is_queue_older_than')) {
            return $search_class::monitor_is_queue_older_than();
        }
        else {
            return false;
        }
    }

    /**
     * Save given record ID and timestamp in plugin's config table for later checking if this record still remains in queue.
     *
     * @param int $lastminsearchid ID of the last
     */
    // public static function set_last_minsearchid($rec=null, $time=null) {
    //     set_config_plugin('module', 'monitor', 'lastminsearchid', $rec);
    //     set_config_plugin('module', 'monitor', 'lastminsearchidtime', $time);
    // }

    /**
     * Get the last unprocessed search record id from config. We always look for the minimal one.
     * If there is no such thing in config, then we get it from the current elasticsearch queue and pretend it's the last one.
     * And in this case we save it in config and return it.
     *
     * @return array of record ID and timestamp when it was set.
     */
    // public static function get_last_unprocessed_search_record_id() {
    //     $result = array(
    //         'id' => get_config_plugin('module', 'monitor', 'lastminsearchid'),
    //         'timestamp' => get_config_plugin('module', 'monitor', 'lastminsearchidtime')
    //     );

    //     if ( $result['id'] && $result['timestamp'] ) {
    //         return $result;
    //     }
    //     else {
    //         // pick it from the current elasticsearch queue and save it for the next use.
    //         if ($curminsearchid = get_field('search_elasticsearch_7_queue', 'min(id)', 'status', '0')) {
    //             self::set_last_minsearchid($curminsearchid, time());
    //         }
    //         return false;
    //     }
    // }

    /**
     * Prepare the data for displaying in UI by means of monitor.tpl.
     *
     * @param array $params This has to be 2dim array to display in UI, eg: [[param1, value1], [param2, value2] ... ]
     * @param null $limit
     * @param null $offset
     * @return array Data suitable for passing to monitor.tpl
     */
    public static function format_for_display($title, $params, $limit = null, $offset = null) {
        $data = array();
        $data['tabletitle'] = $title;
        $data['table'] = self::format_for_display_table($params);

        return $data;
    }

    /**
     * Set some settings for the table we try to display.
     *
     * @param $params This has to be 2dim array to display in UI, eg: [[param1, value1], [param2, value2] ... ]
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public static function format_for_display_table($params, $limit = null, $offset = null) {
        global $USER;

        $smarty = smarty_core();
        $smarty->assign('data', $params);

        $result = array();
        $result['count'] = 1;
        $result['pagination_js'] = '';

        $csvfields = array('task', 'value');
        $USER->set_download_file(generate_csv($params, $csvfields), 'queuestatus.csv', 'text/csv');
        $result['csv'] = true;

        $result['tablerows'] = $smarty->fetch('module:monitor:tasktable.tpl');

        return $result;
    }

    private static function get_message($callback) {
        $search_class = does_search_plugin_have($callback);
        return $search_class::{$callback}();

    }

    public static function get_failed_queue_size_message() {
        return self::get_message('monitor_get_failed_queue_size_message');
    }

    public static function is_queue_older_than_message() {
        return self::get_message('monitor_is_queue_older_than_message');
    }

    public static function checking_search_succeeded_message() {
        return self::get_message('monitor_checking_search_succeeded_message');
    }
}
