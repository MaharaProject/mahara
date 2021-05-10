<?php

require_once('ActivityTypeAdmin.php');

/**
 * Objectionable class for the view objection form activity.
 *
 * This activity type is only available to administrators.
 */
class ActivityTypeObjectionable extends ActivityTypeAdmin {

  /**
   * @var integer|object A View.
   */
  protected $view;

  /**
   * @var integer|object An Artefact.
   */
  protected $artefact;

  /**
   * @var integer User ID of the person reporting the issue.
   */
  protected $reporter;

  /**
   * @var integer Unix timestamp.
   */
  protected $ctime;

  /**
   * @var integer User ID of the person resolving the issue.
   */
  protected $review;

  /**
   * Activity class to send objectionable messages
   * @param array $data[
   *                'message' => string,
   *                'view' => int,
   *                'artefact' => int, // (optional)
   *                'reporter' => int,
   *                'ctime' => int, // (optional)
   *                'review' => int, // (optional)
   *              ]
   * @param boolean $cron Indicates whether this is being called by the cron job
   */
  function __construct($data, $cron=false) {
      parent::__construct($data, $cron);

      require_once(get_config('libroot') . 'view.php');

      $this->view = new View($this->view);

      if (!empty($this->artefact)) {
          require_once(get_config('docroot') . 'artefact/lib.php');
          $this->artefact = artefact_instance_from_id($this->artefact);
      }
      // Notify institutional admins of the view owner.
      $adminusers = array();
      if ($owner = $this->view->get('owner')) {
          if ($institutions = get_column('usr_institution', 'institution', 'usr', $owner)) {
              $adminusers = activity_get_users($this->get_id(), null, null, null, $institutions);
          }
      }
      if (isset($data->touser) && !empty($data->touser)) {
          // Notify user when admin updates objection.
          $owneruser = activity_get_users($this->get_id(), array($data->touser));
          $this->users = array_merge($owneruser, $adminusers);
      }
      else if ($owner = $this->view->get('owner')) {
          if (!empty($adminusers)) {
              $this->users = $adminusers;
          }
      }

      if (empty($this->artefact)) {
          $this->url = $this->view->get_url(false, true) . '&objection=1';
      }
      else {
          $this->url = 'view/view.php?id=' . $this->view->get('id') . '&modal=1&artefact=' .  $this->artefact->get('id') . '&objection=1';
      }

      if (empty($this->strings->subject)) {
          $this->overridemessagecontents = true;
          $viewtitle = $this->view->get('title');
          $this->strings = new stdClass();
          if (empty($this->artefact)) {
              $this->strings->subject = (object) array(
                  'key'     => ($this->review ? 'objectionablereviewview' : 'objectionablecontentview'),
                  'section' => 'activity',
                  'args'    => array($viewtitle, display_default_name($this->reporter)),
              );
          }
          else {
              $title = $this->artefact->get('title');
              $this->strings->subject = (object) array(
                  'key'     => ($this->review ? 'objectionablereviewviewartefact' : 'objectionablecontentviewartefact'),
                  'section' => 'activity',
                  'args'    => array($viewtitle, $title, display_default_name($this->reporter)),
              );
          }
      }
  }

  /**
   * Fetch a Plain Text formatted message to send via email.
   *
   * @param object $user  A database user object.
   * @todo This should be inherited from the abstract class.
   * @return string
   */
  public function get_emailmessage($user) {
      $reporterurl = profile_url($this->reporter);
      $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
      if (empty($this->artefact)) {
          $key = ($this->review ? 'objectionablereviewviewtext' : 'objectionablecontentviewtext');
          return get_string_from_language(
              $user->lang, $key, 'activity',
              $this->view->get('title'), display_default_name($this->reporter), $ctime,
              $this->message, $this->view->get_url(true, true) . "&objection=1", $reporterurl
          );
      }
      else {
          $key = ($this->review ? 'objectionablereviewviewartefacttext' : 'objectionablecontentviewartefacttext');
          return get_string_from_language(
              $user->lang, $key, 'activity',
              $this->view->get('title'), $this->artefact->get('title'), display_default_name($this->reporter), $ctime,
              $this->message, get_config('wwwroot') . "view/view.php?id=" . $this->view->get('id') . '&modal=1&artefact=' . $this->artefact->get('id') . "&objection=1", $reporterurl
          );
      }
  }

  /**
   * Fetch an HTML formatted message to send via email
   * @param object $user  A database user object
   * @todo This should be inherited from abstract class
   * @return string
   */
  public function get_htmlmessage($user) {
      $viewtitle = hsc($this->view->get('title'));
      $reportername = hsc(display_default_name($this->reporter));
      $reporterurl = profile_url($this->reporter);
      $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
      $message = format_whitespace($this->message);
      if (empty($this->artefact)) {
          $key = ($this->review ? 'objectionablereviewviewhtml' : 'objectionablecontentviewhtml');
          return get_string_from_language(
              $user->lang, $key, 'activity',
              $viewtitle, $reportername, $ctime,
              $message, $this->view->get_url(true, true) . "&objection=1", $viewtitle,
              $reporterurl, $reportername
          );
      }
      else {
          $key = ($this->review ? 'objectionablereviewviewartefacthtml' : 'objectionablecontentviewartefacthtml');
          return get_string_from_language(
              $user->lang, $key, 'activity',
              $viewtitle, hsc($this->artefact->get('title')), $reportername, $ctime,
              $message, get_config('wwwroot') . "view/view.php?id=" . $this->view->get('id') . '&modal=1&artefact=' . $this->artefact->get('id') . "&objection=1", hsc($this->artefact->get('title')),
              $reporterurl, $reportername
          );
      }
  }

    /**
     * Get the minimum required data parameters for this activity type.
     *
     * @return array
     */
    public function get_required_parameters() {
        return array('message', 'view', 'reporter');
    }

}
