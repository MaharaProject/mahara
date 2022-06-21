<?php

require_once('ActivityType.php');

/**
 * A class for the specific institution messages that Mahara needs to send.
 */
class ActivityTypeInstitutionmessage extends ActivityType {

  /**
   * @var string Type of message.
   */
  protected $messagetype;

  /**
   * @var object Institution.
   */
  protected $institution;

  /**
   * @var string The username.
   */
  protected $username;

  /**
   * @var string Display name.
   */
  protected $fullname;

  /**
   * Activity class for institution messages
   *
   * @param array $data The data needed to send the notification
   * @param boolean $cron Indicates whether this is being called by the cron job
   */
  public function __construct($data, $cron=false) {
      parent::__construct($data, $cron);
      if ($this->messagetype == 'request') {
          $this->url = 'admin/users/institutionusers.php';
          $this->users = activity_get_users($this->get_id(), null, null, null,
                                            array($this->institution->name));
          $this->add_urltext(array('key' => 'institutionmembers', 'section' => 'admin'));
      }
      else if ($this->messagetype == 'invite') {
          $this->url = 'account/institutions.php';
          $this->users = activity_get_users($this->get_id(), $this->users);
          $this->add_urltext(array('key' => 'institutionmembership', 'section' => 'mahara'));
      }
  }

  /**
   * Fetch the language to send the message in.
   *
   * If the user has no set choice, use the institution language.
   *
   * @param object $user  A database user object.
   */
  private function get_language($user) {
      $userlang = get_account_preference($user->id, 'lang');
      if ($userlang === 'default') {
          if (!isset($this->institution->language) || $this->institution->language === '' || $this->institution->language === 'default') {
              return get_config('lang');
          }
          else {
              return $this->institution->language;
          }
      }
      else {
          return $userlang;
      }
  }

  /**
   * Fetch the subject line text based on the message type.
   *
   * @param object $user  A database user object.
   * @return string The subject line of the message.
   */
  public function get_subject($user) {
      $lang = $this->get_language($user);
      if ($this->messagetype == 'request') {
          $userstring = $this->fullname . ' (' . $this->username . ')';
          return get_string_from_language($lang, 'institutionrequestsubject', 'activity', $userstring,
            $this->institution->displayname);
      }
      else if ($this->messagetype == 'invite') {
          return get_string_from_language($lang, 'institutioninvitesubject', 'activity',
            $this->institution->displayname);
      }
      return '';
  }

  /**
   * Fetch the body message text based on the message type.
   *
   * @param object $user  A database user object
   * @return string The message body.
   */
  public function get_message($user) {
      $lang = $this->get_language($user);
      if ($this->messagetype == 'request') {
          return $this->get_subject($user) .' '. get_string_from_language($lang, 'institutionrequestmessage', 'activity', $this->url);
      }
      else if ($this->messagetype == 'invite') {
          return $this->get_subject($user) .' '. get_string_from_language($lang, 'institutioninvitemessage', 'activity', $this->url);
      }
      return '';
  }

  /**
   * Get the minimum required data parameters for this activity type
   * @return array
   */
  public function get_required_parameters() {
      return array('messagetype', 'institution');
  }
}
