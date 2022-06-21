<?php

require_once('ActivityTypeAdmin.php');

/**
 * For uploading of files identified as potential viruses.
 *
 * This activity type is only available to administrators
 */
class ActivityTypeVirusRepeat extends ActivityTypeAdmin {

  /**
   * @var string username
   */
  protected $username;

  /**
   * @var string fullname
   */
  protected $fullname;

  /**
   * @var integer user ID
   */
  protected $userid;

  /**
   * Activity class for sending a virus message to administrators.
   *
   * @param array $data The data needed to send the notification.
   * @param boolean $cron Indicates whether this is being called by the cron job.
   */
  public function __construct($data, $cron=false) {
      parent::__construct($data, $cron);
  }

  /**
   * Fetch the subject line text.
   *
   * @param object $user A user object.
   * @return string
   */
  public function get_subject($user) {
      $userstring = $this->username . ' (' . $this->fullname . ') (userid:' . $this->userid . ')' ;
      return get_string_from_language($user->lang, 'virusrepeatsubject', 'mahara', $userstring);
  }

  /**
   * Fetch the body message text.
   *
   * @param object $user A user object.
   * @return string
   */
  public function get_message($user) {
      return get_string_from_language($user->lang, 'virusrepeatmessage');
  }

  /**
   * Get the minimum required data parameters for this activity type.
   *
   * @return array
   */
  public function get_required_parameters() {
      return array('username', 'fullname', 'userid');
  }
}
