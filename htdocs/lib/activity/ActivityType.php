<?php

/**
 * To implement a new activity type, you must subclass this class. Your subclass
 * MUST at minimum include the following:
 *
 * 1. Override the __construct method with one which first calls parent::__construct
 *    and then populates $this->users with the list of recipients for this activity.
 *
 * 2. Implement the get_required_parameters method.
 */
abstract class ActivityType {

  /**
   * The number of users in a split chunk to notify
   */
  const USERCHUNK_SIZE = 1000;

  /**
   * Who any notifications about this activity should appear to come from
   * @var integer The ID of the person
   */
  protected $fromuser;

  /**
   * When sending notifications, should the email of the person sending it be
   * hidden? (Almost always yes, will cause the email to appear to come from
   * the 'noreply' address)
   * @var boolean
   */
  protected $hideemail = true;

  /**
   * The subject line of the message
   * @var string
   */
  protected $subject;

  /**
   * The body of the message
   * @var string
   */
  protected $message;

  /**
   * Language strings and parameters to build the subject / message with
   * @var object
   */
  protected $strings;

  /**
   * People to send the message to
   * @var array
   */
  protected $users = array();

  /**
   * A URL to display at the bottom of the message
   * @var string
   */
  protected $url;

  /**
   * Alternate text to display for the URL in HTML messages
   * @var string
   */
  protected $urltext;

  /**
   * The ID of the activity type
   * @var integer
   */
  protected $id;

  /**
   * The ID of the activity type
   * @var integer
   * @todo find out how it differs from $id
   */
  protected $type;

  /**
   * The partial class name without the 'ActivityType' prefix, e.g. Usermessage
   * @var string
   */
  protected $activityname;

  /**
   * @var boolean Whether this is being called by the cron job
   */
  protected $cron;

  /**
   * The last person to be notified for a particular queue item
   * when the activity_queue cron emails in bulk
   * @var integer
   */
  protected $last_processed_userid;

  /**
   * The queue item currently being processed via cron
   * @var integer
   */
  protected $activity_queue_id;

  /**
   * Override the normal message process to allow HTML or plaintext email.
   * @var boolean
   */
  protected $overridemessagecontents;

  /**
   * The parent message of a threaded message reply
   * @var integer
   */
  protected $parent;

  /**
   * The default notification method to use when sending the message to a person
   * @var string
   */
  protected $defaultmethod;

  /**
   * Child classes MUST call the parent constructor.
   *
   * Populate $this->users with a list of user records which should receive the
   * message.
   *
   * @param array $data The data needed to send the notification
   * @param boolean $cron Indicates whether this is being called by the cron job
   */
  public function __construct($data, $cron=false) {
      $this->cron = $cron;
      $this->set_parameters($data);
      $this->ensure_parameters();
      $this->activityname = strtolower(substr(get_class($this), strlen('ActivityType')));
  }

  /**
   * Get required parameters for the Activity Type.
   *
   * This method should return an array which names the fields that must be
   * present in the $data that was passed to the class's constructor. It should
   * include all necessary data to determine the recipient(s) of the
   * notification and to determine its content.
   *
   * @return array
   */
  abstract function get_required_parameters();

  /**
   * Get the ID of the ActivityType from the database.
   *
   * @return integer
   */
  public function get_id() {
      if (!isset($this->id)) {
          $tmp = activity_locate_typerecord($this->get_type());
          $this->id = $tmp->id;
      }
      return $this->id;
  }

  /**
   * Get the default method to send the notification.
   *
   * @return string
   */
  public function get_default_method() {
      if (!isset($this->defaultmethod)) {
          $tmp = activity_locate_typerecord($this->get_id());
          $this->defaultmethod = $tmp->defaultmethod;
      }
      return $this->defaultmethod;
  }

  /**
   * Get the partial class name without the 'ActivityType' prefix.
   *
   * e.g., Usermessage.
   *
   * @return string
   */
  public function get_type() {
      $prefix = 'ActivityType';
      return strtolower(substr(get_class($this), strlen($prefix)));
  }

  /**
   * Check to see if any people will receive a notification.
   *
   * @return boolean
   */
  public function any_users() {
      return (is_array($this->users) && count($this->users) > 0);
  }

  /**
   * Fetch the people to be notified.
   *
   * @return array
   */
  public function get_users() {
      return $this->users;
  }

  /**
   * Set supplied data to the class properties.
   *
   * @param array $data  An associative array with keys matching properties of the class
   */
  private function set_parameters($data) {
      foreach ($data as $key => $value) {
          if (property_exists($this, $key)) {
              $this->{$key} = $value;
          }
      }
  }

  /**
   * Sanity check the required parameters.
   *
   * Checks that we have the required properties set before trying to send
   * messages.
   *
   * @throws ParamOutOfRangeException
   */
  private function ensure_parameters() {
      foreach ($this->get_required_parameters() as $param) {
          if (!isset($this->{$param})) {
              // Allow some string parameters to be specified in $this->strings
              if (!in_array($param, array('subject', 'message', 'urltext')) || empty($this->strings->{$param}->key)) {
                  throw new ParamOutOfRangeException(get_string('missingparam', 'activity', $param, $this->get_type()));
              }
          }
      }
  }

  /**
   * Turn ActivityType object into a stdClass object.
   *
   * @return object
   */
  public function to_stdclass() {
     return (object)get_object_vars($this);
  }

  /**
   * Get translated string for the person.
   *
   * This allows us to send email messages in the language the person prefers.
   *
   * @param object $user  A database user object
   * @param string $string The language string key
   * @return string  The translated language string value
   */
  public function get_string_for_user($user, $string) {
      if (empty($string) || empty($this->strings->{$string}->key)) {
          return;
      }
      $args = array_merge(
          array(
              $user->lang,
              $this->strings->{$string}->key,
              empty($this->strings->{$string}->section) ? 'mahara' : $this->strings->{$string}->section,
          ),
          empty($this->strings->{$string}->args) ? array() : $this->strings->{$string}->args
      );
      return call_user_func_array('get_string_from_language', $args);
  }

  /**
   * Optional string to use for the URL link text.
   *
   * @param array $stringdef
   */
  public function add_urltext(array $stringdef) {
      $def = $stringdef;
      if (!is_object($this->strings)) {
          $this->strings = new stdClass();
      }
      $this->strings->urltext = (object) $def;
  }

  /**
   * Fetch the URL link text.
   *
   * @param object $user  A database user object
   * @return string
   */
  public function get_urltext($user) {
      if (empty($this->urltext)) {
          return $this->get_string_for_user($user, 'urltext');
      }
      return $this->urltext;
  }

  /**
   * Fetch the body message text.
   *
   * @param object $user  A database user object
   * @return string
   */
  public function get_message($user) {
      if (empty($this->message)) {
          return $this->get_string_for_user($user, 'message');
      }
      return $this->message;
  }

  /**
   * Fetch the subject line text.
   *
   * @param object $user  A database user object
   * @return string
   */
  public function get_subject($user) {
      if (empty($this->subject)) {
          return $this->get_string_for_user($user, 'subject');
      }
      return $this->subject;
  }

  /**
   * Update URL to point at the current message.
   *
   * Rewrite $this->url with the ID of the internal notification record for
   * this activity. Generally so that you can make a URL that sends the user to
   * the Mahara inbox page for this message.
   *
   * @param int $internalid
   * @return boolean True if $this->url was updated, False if not.
   */
  protected function update_url($internalid) {
      return false;
  }

  /**
   * Sending an activity message to a person.
   *
   * @param object $user  A database user object
   * @return void
   */
  public function notify_user($user) {
      $changes = new stdClass();

      $userdata = $this->to_stdclass();
      // Some stuff gets overridden by user specific stuff.
      if (!empty($user->url)) {
          $userdata->url = $user->url;
      }
      if (empty($user->lang) || $user->lang == 'default') {
          $user->lang = get_user_language($user->id);
      }
      if (empty($user->method)) {
          // If method is not set then either the user has selected 'none' or
          // their setting has not been set (so use default).
          if ($record = get_record('usr_activity_preference', 'usr', $user->id, 'activity', $this->get_id())) {
              $user->method = $record->method;
              if (empty($user->method)) {
                  // The user specified 'none' as their notification type.
                  return;
              }
          }
          else {
              $user->method = $this->get_default_method();
              if (empty($user->method)) {
                  // The default notification type is 'none' for this activity
                  // type.
                  return;
              }
          }
      }

      // Always do internal.
      foreach (PluginNotificationInternal::$userdata as &$p) {
          $function = 'get_' . $p;
          $userdata->$p = $this->$function($user);
      }

      $userdata->internalid = PluginNotificationInternal::notify_user($user, $userdata);
      if ($this->update_url($userdata->internalid)) {
          $changes->url = $userdata->url = $this->url;
      }

      if ($user->method != 'internal' || isset($changes->url)) {
          $changes->read = (int) ($user->method != 'internal');
          $changes->id = $userdata->internalid;
          update_record('notification_internal_activity', $changes);
      }

      if ($user->method != 'internal') {
          $method = $user->method;
          safe_require('notification', $method);
          $notificationclass = generate_class_name('notification', $method);
          $classvars = get_class_vars($notificationclass);
          if (!empty($classvars['userdata'])) {
              foreach ($classvars['userdata'] as &$p) {
                  $function = 'get_' . $p;
                  if (!isset($userdata->$p) && method_exists($this, $function)) {
                      $userdata->$p = $this->$function($user);
                  }
              }
          }
          try {
              call_static_method($notificationclass, 'notify_user', $user, $userdata);
          }
          catch (MaharaException $e) {
              static $badnotification = false;
              static $adminnotified = array();
              // We don't mind other notification methods failing, as it'll
              // go into the activity log as 'unread'.
              $changes->read = 0;
              update_record('notification_internal_activity', $changes);
              if (!$badnotification && !($e instanceof EmailDisabledException || $e instanceof InvalidEmailException)) {
                  // Admins should probably know about the error, but to avoid
                  // sending too many similar notifications, save an initial
                  // prefix of the message being sent and throw away subsequent
                  // exceptions with the same prefix.  To cut down on spam,
                  // it's worth missing out on a few similar messages.
                  $k = substr($e, 0, 60);
                  if (!isset($adminnotified[$k])) {
                      $message = (object) array(
                          'users' => get_column('usr', 'id', 'admin', 1),
                          'subject' => get_string('adminnotificationerror1', 'activity'),
                          'message' => $e,
                      );
                      $adminnotified[$k] = 1;
                      $badnotification = true;
                      activity_occurred('maharamessage', $message);
                      $badnotification = false;
                  }
              }
          }
      }
      // The user's unread message count does not need to be updated from
      // $changes->read because of the db trigger on
      // notification_internal_activity.
  }

  /**
   * Sound out notifications to $this->users.
   *
   * Note that, although this has batching properties built into it with
   * USERCHUNK_SIZE, it's also recommended to update a bulk ActivityType's
   * constructor to limit the total number of records pulled from the database.
   *
   * @return integer|void  Returns 0 for cron if successful
   */
  public function notify_users() {
      safe_require('notification', 'internal');
      $this->type = $this->get_id();

      if ($this->cron) {
          // Sort the list of users to notify by userid.
          uasort($this->users, function($a, $b) {return $a->id > $b->id;});
          // Notify a chunk of users.
          $num_processed_users = 0;
          $last_processed_userid = 0;
          foreach ($this->users as $user) {
              if ($this->last_processed_userid && ($user->id <= $this->last_processed_userid)) {
                  continue;
              }
              if ($num_processed_users < ActivityType::USERCHUNK_SIZE) {
                  // Immediately update the last_processed_userid in the
                  // activity_queue to prevent duplicated notifications.
                  $last_processed_userid = $user->id;
                  update_record('activity_queue', array('last_processed_userid' => $last_processed_userid), array('id' => $this->activity_queue_id));
                  $this->notify_user($user);
                  $num_processed_users++;
              }
              else {
                  break;
              }
          }
          return $last_processed_userid;
      }
      else {
          while (!empty($this->users)) {
              $user = array_shift($this->users);
              $this->notify_user($user);
          }
      }
      return 0;
  }
}
