<?php

require_once('ActivityType.php');

/**
 * Class for the generic sitewide messages that Mahara needs to send.
 *
 * No special activity type required.
 */
class ActivityTypeMaharamessage extends ActivityType {

    /**
     * The generic message class used for most messages.
     *
     * @param array $data [
     *                'subject' => string,
     *                'message' => string,
     *                'users' => array,
     *              ]
     * @param boolean $cron Indicates whether this is being called by the cron job.
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
        $includesuspendedusers = isset($data->includesuspendedusers) && $data->includesuspendedusers;
        $this->users = activity_get_users($this->get_id(), $this->users, null, false, array(), $includesuspendedusers);
    }

    /**
     * Get the minimum required data parameters for this activity type
     * @return array
     */
    public function get_required_parameters() {
        return array('message', 'subject', 'users');
    }

}
