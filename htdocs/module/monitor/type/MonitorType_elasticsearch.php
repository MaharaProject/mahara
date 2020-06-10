<?php

class MonitorType_elasticsearch extends MonitorType {

    /**
     * Get the number of records in table search_elasticsearch_queue where status = 0 which means unprocessed.
     * Such return structure is needed for displaying stuff in UI by means of monitor.tpl.
     *
     * @return array of [task, value] with 'task' saying this is unprocessed queue size and 'value' with amount of such records.
     */
    public static function get_unprocessed_queue_size() {
        $size = count_records('search_elasticsearch_queue', 'status', '0');
        $data = array(
            'task' => get_string('unprocessedqueuesize', 'module.monitor'),
            'value' => $size,
        );
        return $data;
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
        $time = db_format_timestamp(time() - 3600);

        $sql = "SELECT count(id)
                FROM {search_elasticsearch_queue}
                WHERE lastprocessed IS NOT NULL
                AND lastprocessed < ?
                AND status <> 0";
        $params = array($time);
        $size = get_field_sql($sql, $params);

        $data = array(
            'task' => get_string('failedqueuesize', 'module.monitor'),
            'value' => $size,
        );

        return $data;
    }

    public static function get_hours_to_consider_elasticsearch_record_old() {
        $hours = PluginModuleMonitor::get_config_value('hourstoconsiderelasticsearchrecordold');
        return $hours;
    }
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
        $hours = self::get_hours_to_consider_elasticsearch_record_old();
        $seconds = $hours * 60 * 60;

        $result = array(
            'task'  => get_string('queuehasolditems', 'module.monitor', $hours, $hours),
            'value'  => get_string('no', 'module.monitor'),
            'status' => false,
        );

        $rec = self::get_last_unprocessed_search_record_id();
        if ( $rec && $rec['timestamp'] + $seconds < time() ) {
            // now need to check if the same $rec is still in the queue with status 0.
            if ( get_field('search_elasticsearch_queue', 'id', 'id', $rec['id'], 'status', '0') ) {
                $result['value'] = get_string('yes', 'module.monitor');
                $result['status'] = true;
                return $result;
            }
            else { // otherwise it has gone already and we don't consider it as "old".
                // pick it from the current elasticsearch queue and save it for the next use.
                if ($curminsearchid = get_field('search_elasticsearch_queue', 'min(id)', 'status', '0')) {
                    self::set_last_minsearchid($curminsearchid, time());
                }
                else {
                    self::set_last_minsearchid();
                }
                return $result;
            }
        }
        else { // then rec is empty or is not old
            return $result;
        }
    }

    /**
     * Save given record ID and timestamp in plugin's config table for later checking if this record still remains in queue.
     *
     * @param int $lastminsearchid ID of the last
     */
    public static function set_last_minsearchid($rec=null, $time=null) {
        set_config_plugin('module', 'monitor', 'lastminsearchid', $rec);
        set_config_plugin('module', 'monitor', 'lastminsearchidtime', $time);
    }

    /**
     * Get the last unprocessed search record id from config. We always look for the minimal one.
     * If there is no such thing in config, then we get it from the current elasticsearch queue and pretend it's the last one.
     * And in this case we save it in config and return it.
     *
     * @return array of record ID and timestamp when it was set.
     */
    public static function get_last_unprocessed_search_record_id() {
        $result = array(
            'id' => get_config_plugin('module', 'monitor', 'lastminsearchid'),
            'timestamp' => get_config_plugin('module', 'monitor', 'lastminsearchidtime')
        );

        if ( $result['id'] && $result['timestamp'] ) {
            return $result;
        }
        else {
            // pick it from the current elasticsearch queue and save it for the next use.
            if ($curminsearchid = get_field('search_elasticsearch_queue', 'min(id)', 'status', '0')) {
                self::set_last_minsearchid($curminsearchid, time());
            }
            return false;
        }
    }

    /**
     * Prepare the data for displaying in UI by means of monitor.tpl.
     *
     * @param array $params This has to be 2dim array to display in UI, eg: [[param1, value1], [param2, value2] ... ]
     * @param null $limit
     * @param null $offset
     * @return array Data suitable for passing to monitor.tpl
     */
    public static function format_for_display($params, $limit = null, $offset = null) {
        $data = array();
        $data['tabletitle'] = get_string('queuestatus', 'module.monitor');
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

        $result['tablerows'] = $smarty->fetch('module:monitor:elasticsearch.tpl');

        return $result;
    }
}
