<?php

require_once('ActivityType.php');

/**
 * For the messages sent relating to being granted access to portfolios.
 *
 * This one only deals with new access and not the revocation of access.
 */
class ActivityTypeViewAccess extends ActivityType {

  /**
   * @var integer View ID.
   */
  protected $view;

  /**
   * @var array Contains ids of users that had access before the change.
   */
  protected $oldusers;

  /**
   * @var array Contains ids of all the views being changed.
   */
  protected $views;

  /**
   * @var string Title of the view.
   */
  private $title;

  /**
   * @var string Formatted name of the author.
   */
  private $ownername;

  /**
   * The activity class for saving / updating view access.
   *
   * @param array $data Parameters:
   *                    - view (int)
   *                    - oldusers (array)
   * @param boolean $cron Indicates whether this is being called by the cron job.
   */
  public function __construct($data, $cron=false) {
      parent::__construct($data, $cron);
      if (!$viewinfo = new View($this->view)) {
          if (!empty($this->cron)) { // probably deleted already
              return;
          }
          throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
      }
      if ($this->views && $this->views[0] && $this->views[0]['collection_id']) {
          require_once('collection.php');
          if (!$collectioninfo = new Collection($this->views[0]['collection_id'])) {
              if (!empty($this->cron)) { // probably deleted already
                  return;
              }
              throw new ViewNotFoundException(get_string('collectionnotfound', 'error', $this->views[0]['collection_id']));
          }
      }

      // Default url.
      $this->url = 'view/sharedviews.php';
      // If we are dealing with one portfolio update url to go to that
      // portfolio page.
      if (!$this->views) {
          // We are dealing with a single page.
          $this->url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;
          $this->add_urltext(array('key' => 'Portfolio', 'section' => 'view'));
      }
      else {
          // Check to see if it's just one collection.
          if ($collectionids = array_column($this->views, 'collection_id')) {
              if (count(array_unique($collectionids)) === 1) {
                  if ($this->views[0]['collection_url']) {
                      $this->url = $this->views[0]['collection_url'];
                      $this->add_urltext(array('key' => 'Collection', 'section' => 'view'));
                  }
              }
          }
      }

      $this->users = array_diff_key(
          activity_get_viewaccess_users($this->view),
          $this->oldusers
      );
      if (!$viewinfo->get_collection()) {
          $this->title = $viewinfo->get('title');
      }
      $this->ownername = $viewinfo->formatted_owner();
      $this->overridemessagecontents = true;
  }

  /**
   * Fetch the subject line text based on the number of portfolio titles.
   *
   * @param object $user A database user object.
   * @return string
   */
  public function get_subject($user) {
      $subject = get_string('newaccessubjectdefault', 'activity');
      if ($titles = $this->get_view_titles_urls($user)) {
          // Covers collection(s), page(s) and combination of both.
          if ($this->ownername) {
              $subject = get_string('newaccesssubjectname', 'activity', count($titles), $this->ownername);
          }
          else {
              $subject = get_string('newaccesssubject', 'activity', count($titles));
          }
      }
      else {
          // Dealing with a single page.
          if ($this->ownername) {
              $subject = get_string('newaccesssubjectname', 'activity', 1, $this->ownername);
          }
          else {
              $subject = get_string('newaccesssubject', 'activity', 1);
          }
      }
      return $subject;
  }

  /**
   * Fetch message based on the access rules.
   *
   * @param object $user A database user object.
   * @return string
   */
  public function get_view_access_message($user) {
      $accessdates = activity_get_viewaccess_user_dates($this->view, $user->id);
      $accessdatemessage = '';
      $fromdate = format_date(strtotime($accessdates['mindate']), 'strftimedate');
      $todate = format_date(strtotime($accessdates['maxdate']), 'strftimedate');
      if (!empty($accessdates['mindate']) && !empty($accessdates['maxdate'])) {
          $accessdatemessage .= get_string_from_language($user->lang, 'messageaccessfromto1', 'activity', $fromdate, $todate);
      }
      else if (!empty($accessdates['mindate'])) {
          $accessdatemessage .= get_string_from_language($user->lang, 'messageaccessfrom1', 'activity', $fromdate);
      }
      else if (!empty($accessdates['maxdate'])) {
          $accessdatemessage .= get_string_from_language($user->lang, 'messageaccessto1', 'activity', $todate);
      }
      else {
          $accessdatemessage = false;
      }
      return $accessdatemessage;
  }

  /**
   * Fetch the titles of all the views.
   *
   * @param object $user A database user object.
   * @return array|false A nested array of titles and urls.
   */
  public function get_view_titles_urls($user) {
      $items = array();
      if (!empty($this->views)) {
          // Handle collection(s), page(s) and combination of both.
          $views = $this->views;
          foreach ($views as $view) {
              if ($view['collection_id']) {
                  // Collections.
                  $url = $view['collection_url'];
                  if (get_config('emailexternalredirect')) {
                      $url = append_email_institution($user, $url);
                  }
                  $items[$view['collection_id']] = [
                      'name' => $view['collection_name'],
                      'url'  => $url,
                  ];
              }
              else {
                  // Pages outside of collections.
                  $url = get_config('wwwroot') . 'view/view.php?id=' . $view['id'];
                  if (get_config('emailexternalredirect')) {
                      $url = append_email_institution($user, $url);
                  }
                  $items[$view['id']] = [
                      'name' => $view['title'],
                      'url' => $url,
                  ];
             }
          }
          return $items;
      }
      return false;
  }

  /**
   * Internal function to get a formatted message based on the template.
   *
   * @param object $user A database user object.
   * @param string $template Name of the .tpl file.
   * @return string Message body.
   */
  public function _getmessage($user, $template) {
      $accessitems = array();
      if ($items = $this->get_view_titles_urls($user)) {
          $accessitems = $items;
      }
      else {
          //we are dealing with a single page
          $url = get_config('wwwroot') . 'view/view.php?id=' . $this->view;
          if (get_config('emailexternalredirect')) {
              $url = append_email_institution($user, $url);
          }
          $accessitems[$this->view] = [
              'name' => $this->title,
              'url' => $url,
          ];
      }
      $accessdatemessage = ($this->view && $user->id) ? $this->get_view_access_message($user) : null;
      $prefurl = get_config('wwwroot') . 'account/activity/preferences/index.php';
      if (get_config('emailexternalredirect')) {
          $prefurl = append_email_institution($user, $prefurl);
      }
      $sitename = get_config('sitename');

      $smarty = smarty_core();
      $smarty->assign('accessitems', $accessitems);
      $smarty->assign('accessdatemsg', $accessdatemessage . "\n");
      $smarty->assign('url', (get_config('emailexternalredirect') ? append_email_institution($user, $this->url) : $this->url));
      $smarty->assign('sitename', $sitename);
      $smarty->assign('prefurl', $prefurl);
      $messagebody = $smarty->fetch($template);

      return $messagebody;
  }

  /**
   * Fetch the body message text.
   *
   * @param object $user A database user object.
   * @return string
   */
  public function get_message($user) {
      return strip_tags($this->_getmessage($user, 'account/activity/accessinternal.tpl'));
  }

  /**
   * Fetch a Plain Text formatted message to send via email.
   *
   * @param object $user A database user object.
   * @todo This should be inherited from an abstract class.
   * @return string
   */
  public function get_emailmessage($user) {
      return strip_tags($this->_getmessage($user, 'account/activity/accessemail.tpl'));
  }

  /**
   * Fetch an HTML formatted message to send via email.
   *
   * @param object $user A database user object.
   * @todo This should be inherited from an abstract class.
   * @return string
   */
  public function get_htmlmessage($user) {
      return $this->_getmessage($user, 'account/activity/accessemail.tpl');
  }

  /**
   * Get the minimum required data parameters for this activity type.
   *
   * @return array
   */
  public function get_required_parameters() {
      return array('view', 'oldusers');
  }
}
