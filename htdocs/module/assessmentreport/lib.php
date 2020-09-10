<?php

defined('INTERNAL') || die();


/**
 * module plugin class. Used for registering the plugin and his functions.
 */
class PluginModuleAssessmentreport extends PluginModule {
    /**
     * Is the plugin activated or not?
     *
     * @return boolean true, if the plugin is activated, otherwise false
     */
    public static function is_active() {
        $active = false;
        if (get_field('module_installed', 'active', 'name', 'assessmentreport')) {
            $active = true;
        }
        return $active;
    }

    /**
     * API-Function get the Plugin ShortName
     *
     * @return string ShortName of the plugin
     */
    public static function get_plugin_name() {
        return 'assessmentreport';
    }

    /**
     * Hook addsubmission event.
     *
     * @return array
     */
    public static function get_event_subscriptions() {
        return array(
            (object) array(
                'plugin'        => self::get_plugin_name(),
                'event'         => 'addsubmission',
                'callfunction'  => 'observe_on_addsubmission',
            ),
            (object) array (
                'plugin' => self::get_plugin_name(),
                'event' => 'releasesubmission',
                'callfunction'  => 'observe_on_releasesubmission',
            ),
        );
    }

    /**
     * @param $event
     * @param $data
     * @throws CollectionNotFoundException
     * @throws SQLException
     * @throws ViewNotFoundException
     */
    public static function observe_on_releasesubmission($event, $data) {
        $releaseuserid = ($data['releaseuser'] instanceof User) ? $data['releaseuser']->get('id') : $data['releaseuser']->id;
        $item = ($data['eventfor'] == "collection" ? new Collection($data['id']) : new View($data['id']));
        if (!empty($data['groupname'])) {
            $group = get_record('group', 'name', $data['groupname']);
        }
        else {
            $group = false;
        }
        $historyobj = (object) array(
            'userid'        => $item ? $item->get('owner') : null,
            'event'         => $data['eventfor'],
            'itemid'        => $item ? $item->get('id') : null,
            'datereleased'  => date('Y-m-d H:i:s'),
            'groupid'       => $group ? $group->id : null,
            'markerid'      => $releaseuserid
        );

        if ($updateitem = get_record_sql('
              SELECT * FROM {module_assessmentreport_history}
              WHERE userid = ?
              AND itemid = ?
              AND event = ?
              AND groupid = ?
              AND markerid IS NULL ORDER BY id DESC LIMIT 1',
            array(
                $historyobj->userid,
                $historyobj->itemid,
                $historyobj->event,
                $historyobj->groupid)
            )) {
            $historyobj->id = $updateitem->id;
            update_record('module_assessmentreport_history', $historyobj);
        }
    }

    /**
     * @param $event
     * @param $data
     * @throws CollectionNotFoundException
     * @throws SQLException
     * @throws ViewNotFoundException
     */
    public static function observe_on_addsubmission($event, $data) {
        $item = ($data['eventfor'] == "collection" ? new Collection($data['id']) : new View($data['id']));
        if (!empty($data['groupname'])) {
            $group = get_record('group', 'name', $data['groupname']);
        }
        else {
            $group = false;
        }
        $historyobj = (object) array(
            'userid'        => $item ? $item->get('owner') : null,
            'event'         => $data['eventfor'],
            'itemid'        => $item ? $item->get('id') : null,
            'datesubmitted' => date('Y-m-d H:i:s'),
            'groupid'       => $group ? $group->id : null
        );

        insert_record('module_assessmentreport_history', $historyobj);

        $type = get_string('page', 'module.assessmentreport');
        if ($data['eventfor'] == "collection") {
            $type = get_string('collection', 'collection');
        }
        $subject = get_string('subject', 'module.assessmentreport', $type);
        $message = get_string('message', 'module.assessmentreport', $type, $data['name'], $data['groupname'], format_date(strtotime("now"), 'strftimedatetimesuffix'));

        activity_occurred(
            'maharamessage',
            array(
                'users' => array($item->get('owner')),
                'subject' => $subject,
                'message' => $message
            )
        );
    }

    /**
     * We want this module to be the default notification module so we
     * will prevent it being disabled.
     */
    public static function can_be_disabled() {
        return true;
    }

    /**
     * @param int $prevversion
     * @return bool|void
     */
    public static function postinst($prevversion) {

    }
}
