<?php

safe_require('auth', 'ldap');

class MonitorType_ldaplookup extends MonitorType {

    const ldap_username = 'john.smith';
    const ldap_password = 'xxxxxxxxxx';

    /**
     * Get a list of all the ldap auth instances from the auth_instance table.
     *
     * @return array
     */
    public static function get_ldap_instances() {

        // Get all the LDAP instances for active institutions.
        $sql = "SELECT ai.id as instanceid, ai.instancename, ai.institution,
                       i.displayname as institutiondisplayname
                FROM {auth_instance} ai
                INNER JOIN {institution} i on ai.institution = i.name
                WHERE ai.authname = ?
                AND i.suspended = ?
                AND (i.expiry > ? OR i.expiry is null)
                ORDER BY i.displayname, ai.instancename";
        $params = array('ldap', '0', db_format_timestamp(time()));
        $records = get_records_sql_array($sql, $params);

        $instances = array();
        if ($records) {
            foreach ($records as $index => $record) {
                $instances[] = array('instanceid'             => $record->instanceid,
                                     'instancename'           => $record->instancename,
                                     'institution'            => $record->institution,
                                     'institutiondisplayname' => $record->institutiondisplayname,
                                    );
                // Check the status of this LDAP instance.
                list($instances[$index]['status'], $instances[$index]['statusmessage']) = self::check_ldap_status($record->instanceid);
            }
        }

        return $instances;
    }

    /**
     * Check the LDAP status for the instanceid provided.
     *
     * @param int $instanceid - LDAP auth instance id from the auth_instance table.
     * @return array: 1st param: boolean true = success; false = failed.
     *                2nd param: string message
     */
    public static function check_ldap_status($instanceid) {
        global $CFG;
        try {
            $ldap = new AuthLdap($instanceid);
            // We don't care if the user/password are correct.
            // As long as there is not an error thrown.

            // Create a dummy user object to pass to authenticate_user_account().
            $user = new stdClass();
            $user->id = -1;     // Make sure the user will not get updated by some fluke match.
            $user->username = self::ldap_username;

            $userobj = new User();
            $userobj->from_stdclass($user);

            // Disable warning messages.
            $temp = $CFG->log_warn_targets;
            self::set_log_warning_target();
            $ldap->authenticate_user_account($userobj, self::ldap_password);
            $CFG->log_warn_targets = $temp;

            return array(true, get_string('ldapstatussuccess', 'module.monitor'));
        }
        catch (Exception $e) {
            return array(false, get_string('ldapstatusfail', 'module.monitor', $e->getMessage()));
        }
    }

    private static function set_log_warning_target() {
        global $CFG;
        if (is_cli()) {
            // Disable the warning if LDAP fails on the command line.
            $CFG->log_warn_targets = LOG_TARGET_SCREEN;
        }
        else {
            // Disable warning messages to the screen.
            $CFG->log_warn_targets = LOG_TARGET_ERRORLOG;
        }
    }

    /**
     * Give the list of instances retrieved from get_ldap_instances, return
     * an array of the instances that have failed.
     *
     * @param array $instances
     *
     * @return array of the failed instances extracted from the $instances array.
     */
    public static function extract_failed_instances($instances) {
        $failed = array();
        if (!empty($instances)) {
            foreach ($instances as $instance) {
                if (!$instance['status']) {
                    $failed[] = $instance['instancename'];
                }
            }
        }
        return $failed;
    }

    /**
     * From the results of the get_ldap_instances, prepare the list of instances
     * to be displayed on the screen.
     *
     * @param array $instances - result from get_ldap_instances().
     * @param int $limi - for pagination
     * @param int $offset - for pgination
     * @return array $data
     */
    public static function format_for_display($instances, $limit, $offset) {
        $data = array();
        $data['tableheadings'] = array(
                array('name' => get_string('institution', 'module.monitor')),
                array('name' => get_string('ldapauthority', 'module.monitor'), 'class' => 'center'),
                array('name' => get_string('ldapstatus', 'module.monitor'), 'class' => 'center'),
                array('name' => get_string('ldapstatusmessage', 'module.monitor'), 'class' => 'center'),
        );

        $data['table'] = self::format_for_display_table($instances, $limit, $offset);
        $data['tabletitle'] = get_string('ldapstatustabletitle', 'module.monitor');

        return $data;
    }

    public static function format_for_display_table($instances, $limit, $offset) {
        global $USER;

        $count = count($instances);
        $pagination = build_pagination(array(
            'id' => 'monitor_pagination',
            'url' => get_config('wwwroot') . 'module/monitor/monitor.php?type=' . PluginModuleMonitor::type_ldaplookup,
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
        foreach ($instances as $key => $instance) {
            if ($key + 1 < $offset) {
                continue;
            }
            if ($key + 1 > $offset + $limit) {
                break;
            }
            $data[] = array(
                'institution'       => $instance['institutiondisplayname'],
                'ldapauthority'     => $instance['instancename'],
                'ldapstatus'        => $instance['status'],
                'ldapstatusdesc'    => self::get_status_description($instance['status']),
                'ldapstatusmessage' => $instance['statusmessage'],
            );
        }

        $csvfields = array('institution', 'ldapauthority', 'ldapstatusdesc', 'ldapstatusmessage');
        $USER->set_download_file(generate_csv($data, $csvfields), 'ldaplookupstatus.csv', 'text/csv');
        $result['csv'] = true;

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        $result['tablerows'] = $smarty->fetch('module:monitor:ldaplookup.tpl');

        return $result;
    }

    /**
     * Based on the status, provide a word description.
     * @param boolean $status true/false
     *
     * @return string - a word description for the status.
     */
    private static function get_status_description($status) {
        if ($status) {
            return get_string('statussuccess', 'module.monitor');
        }
        else {
            return get_string('statusfail', 'module.monitor');
        }
    }
}