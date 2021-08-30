<?php

require_once('ActivityType.php');

/**
 * Abstract class for the activity types only available to administrators.
 *
 * When making a new admin only activity type this should be the parent class.
 */
abstract class ActivityTypeAdmin extends ActivityType {

  /**
   * Activity class for sending messages to administrators.
   *
   * @param array $data The data needed to send the notification
   * @param boolean $cron Indicates whether this is being called by the cron job
   */
  public function __construct($data, $cron=false) {
      parent::__construct($data, $cron);
      $this->users = activity_get_users($this->get_id(), null, null, true);
  }
}