<?php

require_once('ActivityType.php');

/**
 * Handle notification when removing access from a view.
 *
 * The access needs to be a 1 to 1 share to a user.
 */
class ActivityTypeViewAccessRevoke extends ActivityType {

  protected $destinationuser;
  protected $fromid;
  protected $message;
  protected $originuser;
  protected $string; // this can be empty though
  protected $toid;
  protected $touser;
  protected $viewid;
  protected $viewinfo;
  protected $viewtitle;

  /**
   * @param object $data Parameters:
   *                    - viewid (int)
   *                    - Message (string)
   *                    - Fromid (int)
   *                    - toid (int)
   */
  public function __construct($data, $cron=false) {
      $this->message = $data->message;
      parent::__construct($data, $cron);
      if (!$this->viewinfo = new View($this->viewid)) {
          if (!empty($this->cron)) { // probably deleted already
                return;
          }
          throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->viewid));
      }
      if (!$this->destinationuser = get_user($this->toid)) {
          if (!empty($this->cron)) { // probably deleted already
                return;
          }
          throw new UserNotFoundException(get_string('usernotfound', 'error', $this->touser));
      }
      if (!$this->originuser = get_user($this->fromid)) {
          if (!empty($this->cron)) { // probably deleted already
                return;
          }
          throw new UserNotFoundException(get_string('usernotfound', 'error', $this->fromid));
      }
      $this->url = 'view/share.php';
      $this->users = array($this->destinationuser);
      if ($this->viewinfo->get('collection')) {
          $this->viewtitle = $this->viewinfo->get('collection')->get('name');
      }
      else {
          $this->viewtitle = $this->viewinfo->display_title(true, false, false);
      }
      // Required for html emails to function.
      $this->overridemessagecontents = true;
  }

  public function _getmessage($user, $template) {
      $prefurl = get_config('wwwroot') . 'account/activity/preferences/index.php';
      if (get_config('emailexternalredirect')) {
          $prefurl = append_email_institution($user, $prefurl);
      }

      $sitename = get_config('sitename');
      $fullname = display_name($this->originuser, $user);
      $smarty = smarty_core();
      $smarty->assign('url', (get_config('emailexternalredirect') ? append_email_institution($user, $this->url) : $this->url));
      $smarty->assign('viewtitle', htmlspecialchars_decode($this->viewtitle)); //The htmlspecialcharacters encoding of the title and the message is done in the template.
      $smarty->assign('message', $this->message);
      $smarty->assign('fullname', $fullname);
      $smarty->assign('sitename', $sitename);
      $smarty->assign('prefurl', $prefurl);
      $smarty->assign('revokedbyowner',  $this->is_revoked_by_owner());
      $messagebody = $smarty->fetch($template);
      return $messagebody;
  }

  public function get_message($user) {
      return strip_tags($this->_getmessage($user, 'account/activity/accessrevokeinternal.tpl'));
  }

  public function get_emailmessage($user) {
      return strip_tags($this->_getmessage($user, 'account/activity/accessrevokeemail.tpl'));
  }

  public function get_htmlmessage($user) {
      return $this->_getmessage($user, 'account/activity/accessrevokeemailhtml.tpl');
  }

  public function get_subject($user) {
      // Revoked by owner.
      if ($this->is_revoked_by_owner()) {
          $subject = get_string(
              'ownerhasremovedaccesssubject',
              'collection',
              display_name($this->originuser, $user),
              hsc($this->viewtitle)
          );
      }
      else {
           // Self revoked by other/verifier.
          $subject = get_string(
              'userhasremovedaccesssubject',
              'collection',
              display_name($this->originuser, $user),
              hsc($this->viewtitle)
          );
      }
      return $subject;
  }

  public function get_required_parameters() {
      return array('viewid', 'message', 'fromid', 'toid');
  }

  function is_revoked_by_owner() {
      $portfolioowner = $this->viewinfo->get('owner');
      return $portfolioowner === $this->originuser->id;
  }
}
