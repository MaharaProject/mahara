<?php

require_once('ActivityType.php');

/**
 * Person specific messages that Mahara needs to send.
 *
 * Messages that are sent from one person to another.
 */
class ActivityTypeUsermessage extends ActivityType {

  /**
   * @var integer ID of the person receiving the email.
   */
  protected $userto;

  /**
   * @var integer ID of the person receiving the email.
   */
  protected $userfrom;

  /**
   * Activity class for messages sent directly to users.
   *
   * @param array $data Parameters:
   *                    - userto (int)
   *                    - userfrom (int)
   *                    - subject (string)
   *                    - message (string)
   *                    - parent (int)
   * @param boolean $cron Indicates whether this is being called by the cron job
   */
  public function __construct($data, $cron=false) {
      parent::__construct($data, $cron);
      if ($this->userfrom) {
          $this->fromuser = $this->userfrom;
      }
      $this->users = activity_get_users($this->get_id(), array($this->userto));
      $this->add_urltext(array(
          'key'     => 'Reply',
          'section' => 'group',
      ));
  }

  /**
   * Fetch the subject line text.
   *
   * @param object $user A user object.
   * @return string
   */
  public function get_subject($user) {
      if (empty($this->subject)) {
          return get_string_from_language($user->lang, 'newusermessage', 'group',
                                          display_name($this->userfrom));
      }
      return $this->subject;
  }

  /**
   * Adjust the url for the message.
   *
   * Set the url to use user/sendmessage.php to handle the reply.
   *
   * @param integer $internalid The ID of a notification_internal_activity row.
   * @return boolean true
   */
  protected function update_url($internalid) {
      $this->url = 'user/sendmessage.php?id=' . $this->userfrom . '&replyto=' . $internalid . '&returnto=inbox';
      return true;
  }

  /**
   * Get the minimum required data parameters for this activity type.
   *
   * @return array
   */
  public function get_required_parameters() {
      return array('message', 'userto', 'userfrom');
  }

}
