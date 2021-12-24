<?php

safe_require('auth', 'ldap');

class MonitorType_ldapsuspendedusers extends MonitorType {

    /**
     * Get a percentage of all users that have been suspended in the last 24 hours
     * by LDAP sync.
     *
     * @return array
     */
    public static function get_ldap_suspendedusers() {

        $data = array();
        $max_percentage = PluginModuleMonitor::get_config_value('ldapsuspendeduserspercentage');

        // Get LDAP suspended users since midnight - when the LDAP sync is scheduled to run.
        $sql = "SELECT COUNT(*) As total_suspended, i.displayname as institutiondisplayname,
                      ai.instancename, ai.id as instanceid
                FROM  {usr} u
                INNER JOIN {auth_instance} ai on u.authinstance = ai.id
                INNER JOIN {institution} i on ai.institution = i.name
                WHERE u.suspendedctime IS NOT NULL
                AND   u.suspendedctime BETWEEN ? AND ?
                AND   LEFT(u.suspendedreason, ?) = ?
                AND   u.deleted = ?
                AND   (u.expiry is null or u.expiry > ?)
                AND   ai.authname = ?
                AND   i.suspended = ?
                AND   (i.expiry > ? OR i.expiry is null)
                GROUP BY i.displayname, ai.instancename, ai.id
                ORDER BY i.displayname, ai.instancename";

        // We want a 24 hour range from midnight last night.
        $date_start = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        $date_end = mktime(23, 59, 59, date('n'), date('j'), date('Y'));
        $params = array(db_format_timestamp($date_start),
                        db_format_timestamp($date_end),
                        strlen(AUTH_LDAP_SUSPENDED_REASON),
                        AUTH_LDAP_SUSPENDED_REASON,
                        0,
                        db_format_timestamp(time()),
                        'ldap',
                        0,
                        db_format_timestamp(time()));
        $records = get_records_sql_array($sql, $params);
        if ($records) {
            foreach ($records as $index => $record) {
                $data[$record->instanceid] = array(
                    'totalsuspendedusers'    => $record->total_suspended,
                );
            }
        }

        $sql = "SELECT COUNT(*) As total_users, i.displayname as institutiondisplayname,
                       ai.instancename, ai.id as instanceid
                FROM  {usr} u
                INNER JOIN {auth_instance} ai on u.authinstance = ai.id
                INNER JOIN {institution} i on ai.institution = i.name
                WHERE u.deleted = ?
                AND   (u.expiry is null or u.expiry > ?)
                AND   ai.authname = ?
                AND   i.suspended = ?
                AND   (i.expiry > ? OR i.expiry is null)
                GROUP BY i.displayname, ai.instancename, ai.id
                ORDER BY i.displayname, ai.instancename";
        $params = array(0,
                        db_format_timestamp(time()),
                        'ldap',
                        0,
                        db_format_timestamp(time()));
        $records = get_records_sql_array($sql, $params);
        if ($records) {
            foreach ($records as $index => $record) {
                $percentage = 0;
                $total_ldap_users = 0;
                $total_ldap_suspended = 0;
                if ($record->total_users > 0) {
                    if (isset($data[$record->instanceid])) {
                        $total_ldap_suspended = $data[$record->instanceid]['totalsuspendedusers'];
                    }
                    $total_ldap_users = $record->total_users;
                    $percentage = round(
                                    (floatval($total_ldap_suspended)
                                    /
                                    floatval($total_ldap_users) * floatval(100))
                                  , 1);
                    if ($percentage > 100) {
                        $percentage = 100;
                    }
                    if ($percentage == 100 && $total_ldap_suspended != $total_ldap_users) {
                        $percentage = 99.9;
                    }
                }

                $data[$record->instanceid] = array(
                    'totalldapusers'         => $record->total_users,
                    'institutiondisplayname' => $record->institutiondisplayname,
                    'instancename'           => $record->instancename,
                    'value'                  => number_format($percentage, 1) . '%',
                    'details'                => number_format($total_ldap_suspended) . ' of ' . number_format($total_ldap_users) . ' LDAP users',
                    'error'                  => ($percentage >= $max_percentage ? true : false),
                );
            }
        }

        return $data;
    }

    /**
     * Give the list of instances retrieved from get_ldap_suspendedusers, return
     * an array of the instances that have errors.
     *
     * @param array $results
     *
     * @return array of the instances with errors extracted from the $results array.
     */
    public static function extract_instances_with_suspended_users_errors($results) {
        $failed = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                if ($result['error']) {
                    $failed[] = $result['instancename'];
                }
            }
        }
        return $failed;
    }

    /**
     * From the results of the get_ldap_instances, prepare the list of instances
     * to be displayed on the screen.
     *
     * @param array $results - result from get_ldap_instances().
     * @param int $limi - for pagination
     * @param int $offset - for pgination
     * @return array $data
     */
    public static function format_for_display($results, $limit, $offset) {
        $data = array();
        $data['tableheadings'] = array(
                array('name' => get_string('institution', 'module.monitor')),
                array('name' => get_string('ldapauthority', 'module.monitor'), 'class' => 'center'),
                array('name' => get_string('status', 'module.monitor'), 'class' => 'centre'),
                array('name' => get_string('details', 'module.monitor'), 'class' => 'centre'),
        );

        $data['table'] = self::format_for_display_table($results, $limit, $offset);
        $data['tabletitle'] = get_string('ldapsuspendeduserstabletitle', 'module.monitor');

        return $data;
    }

    public static function format_for_display_table($results, $limit, $offset) {
        global $USER;

        $count = count($results);
        $pagination = build_pagination(array(
            'id' => 'monitor_pagination',
            'url' => get_config('wwwroot') . 'module/monitor/monitor.php?type=' . PluginModuleMonitor::type_ldapsuspendedusers,
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
        foreach ($results as $key => $item) {
            if ($key + 1 < $offset) {
                continue;
            }
            if ($key + 1 > $offset + $limit) {
                break;
            }
            $data[] = array(
                'institution'   => $item['institutiondisplayname'],
                'ldapauthority' => $item['instancename'],
                'value'         => $item['value'],
                'details'       => $item['details'],
                'error'         => $item['error'],
            );
        }

        $csvfields = array('institution', 'ldapauthority', 'value', 'details');
        $USER->set_download_file(generate_csv($data, $csvfields), 'ldapsuspendedusers.csv', 'text/csv');
        $result['csv'] = true;

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        $result['tablerows'] = $smarty->fetch('module:monitor:ldapsuspendedusers.tpl');

        return $result;
    }
}