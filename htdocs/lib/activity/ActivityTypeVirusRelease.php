<?php

require_once('ActivityTypeAdmin.php');

/**
 * For the notification about potential virus file being dealt with.
 *
 * This activity type is only available to administrators.
 */
class ActivityTypeVirusRelease extends ActivityTypeAdmin {

    /**
     * Activity class for sending virus message replies.
     *
     * @param array $data The data needed to send the notification.
     * @param boolean $cron Indicates whether this is being called by the cron job.
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
    }

    /**
     * Get the minimum required data parameters for this activity type.
     * @return array
     */
    public function get_required_parameters() {
        return array();
    }
}
