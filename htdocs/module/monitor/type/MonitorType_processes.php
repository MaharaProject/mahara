<?php

class MonitorType_processes extends MonitorType {

    /**
     * Get a list of all the long running cron processes from the config table.
     *
     * @return array of: array of long running processes with the attributes:
     *                       process = name of the process that is locked
     *                       cronlock = the name in the config table for the process
     *                       starttimestamp = the timestamp of when it was started
     *                       starttime = formatted data/time of when it was started.
     */
    public static function get_long_running_cron_processes() {
        // get the configuration for the max hours a cron process can run.
        $cronlockhours = PluginModuleMonitor::get_config_value('cronlockhours');
        // convert cronlockhours to seconds and subtract from the current time.
        $maxtime = time() - ($cronlockhours * 60 * 60);

        // Now get any cron processes that have a lock older than $cronlockhours.
        $sql = "SELECT *
                FROM {config}
                WHERE field LIKE ?
                AND value <= ?
                ORDER BY field";
        $params = array('%cron%', $maxtime);
        $records = get_records_sql_array($sql, $params);

        $processes = array();
        if ($records) {
            foreach ($records as $record) {
                // remove the cron job prefix '_cron_lock_'.
                $diplaydateformat = get_string('displaydatetimeformat', 'module.monitor');
                $processes[] = array('process'        => substr($record->field, strlen('_cron_lock_')),
                                     'cronlock'       => $record->field,
                                     'starttimestamp' => $record->value,
                                     'starttime'      => date($diplaydateformat, $record->value),
                                    );
            }
        }

        return $processes;
    }

    /**
     * From the results of the get_long_running_cron_processes, extract the process field into an array.
     *
     * @param array $processes - result from get_long_running_cron_processes().
     *
     * @return array of the attribute 'process' extracted from the $processes array.
     */
    public static function extract_processes($processes) {
        $longprocesses = array();
        if (!empty($processes)) {
            foreach ($processes as $process) {
                $longprocesses[] = $process['process'];
            }
        }
        return $longprocesses;
    }

    /**
     * From the results of the get_long_running_cron_processes, prepare the list of processes
     * to be displayed on the screen.
     *
     * @param array $processes - result from get_long_running_cron_processes().
     * @param int $limi - for pagination
     * @param int $offset - for pgination
     * @return array $data
     */
    public static function format_for_display($processes, $limit, $offset) {
        $data = array();
        $data['tableheadings'] = array(
                array('name' => get_string('processname', 'module.monitor')),
                array('name' => get_string('datestarted', 'module.monitor'), 'class' => 'center'),
        );

        $data['table'] = self::format_for_display_table($processes, $limit, $offset);
        $data['tabletitle'] = get_string('longrunningprocesses', 'module.monitor');

        return $data;
    }

    public static function format_for_display_table($processes, $limit, $offset) {
        global $USER;

        $count = count($processes);
        $pagination = build_pagination(array(
            'id' => 'monitor_pagination',
            'url' => get_config('wwwroot') . 'module/monitor/monitor.php?type=' . PluginModuleMonitor::type_processes,
            'jsonscript' => 'module/monitor/monitor.json.php',
            'datatable' => 'monitor_table',
            'count' => $count,
            'limit' => $limit,
            'offset' => $offset,
            'setlimit' => true,
        ));

        $result = array(
            'count'         => $count,
            'tablerows'     => '',
            'pagination'    => $pagination['html'],
            'pagination_js' => $pagination['javascript'],
        );

        if ($count < 1) {
            return $result;
        }

        $data = array();
        foreach ($processes as $key => $process) {
            if ($key + 1 < $offset) {
                continue;
            }
            if ($key + 1 > $offset + $limit) {
                break;
            }
            $data[$process['process']] = array(
                'process'   => $process['process'],
                'starttime' => $process['starttime'],
            );
        }

        $csvfields = array('process', 'starttime');
        $USER->set_download_file(generate_csv($data, $csvfields), 'longrunningcronprocesses.csv', 'text/csv');
        $result['csv'] = true;

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        $result['tablerows'] = $smarty->fetch('module:monitor:longrunningcronprocesses.tpl');

        return $result;
    }
}