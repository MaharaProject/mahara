<?php

require_once('ActivityType.php');

/**
 * Contactus class for the contact form activity.
 *
 * This activity type is only available to administrators.
 */
class ActivityTypeContactus extends ActivityType {

  /**
   * @var string Display name for the sender.
   */
  protected $fromname;

  /**
   * @var string Email address of the sender.
   */
  protected $fromemail;

  /**
   * @var boolean Whether to hide the email.
   */
  protected $hideemail = false;

  protected $customheaders;

  /**
   * Activity class for sending the contact us form messages.
   *
   * @param array $data[
   *                'message' => string,
   *                'subject' => string, // (optional)
   *                'fromname' => string,
   *                'fromaddress' => string, // (emailaddress)
   *                'fromuser' => int, // (if a logged in user)
   *              ]
   * @param boolean $cron True if called by the cron job.
   */
  function __construct($data, $cron=false) {
      parent::__construct($data, $cron);
      $this->users = activity_get_users($this->get_id(), null, null, true);
      // check if user is logged in
      if (!empty($this->fromuser)) {
          $this->url = profile_url($this->fromuser, false);
          // check if user belongs to institution
          if (!empty($data->institutions)) {
              $results = activity_get_users($this->get_id(), null, null, null, $data->institutions);
              // check if there is an admin for their institution(s)
              if (!empty($results)) {
                  $this->users = $results;
              }
          }
      }
      // Set the reply-to value so the email servers handle things better
      // this will mean the 'from' is set to the site's default 'from' address
      $this->customheaders = array(
          'Reply-to: ' . $this->fromname . ' <' . $this->fromemail . '>',
      );
  }

  /**
   * Fetch the subject line text.
   *
   * @param object $user  A database user object
   * @return string
   */
  function get_subject($user) {
      return get_string_from_language($user->lang, 'newcontactus', 'activity');
  }

  /**
   * Fetch the body message text.
   *
   * @param object $user  A database user object
   * @return string
   */
  function get_message($user) {
      return get_string_from_language($user->lang, 'newcontactusfrom', 'activity') . ' ' . $this->fromname
          . ' <' . $this->fromemail .'>' . (isset($this->subject) ? ': ' . $this->subject : '')
          . "\n\n" . $this->message;
  }

  /**
   * Get the minimum required data parameters for this activity type.
   *
   * @return array
   */
  public function get_required_parameters() {
      return array('message', 'fromname', 'fromemail');
  }
}
