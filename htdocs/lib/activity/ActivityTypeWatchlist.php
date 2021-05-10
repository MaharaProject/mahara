<?php

require_once('ActivityType.php');

/**
 * For the messages sent relating to watching for changes in portfolios.
 */
class ActivityTypeWatchlist extends ActivityType {

  /**
   * @var integer ID of the view.
   */
  protected $view;

  /**
   * @var string|null Formatted name of the view author.
   */
  protected $ownerinfo;

  /**
   * @var object A View.
   */
  protected $viewinfo;

  /**
   * Watchlist class for watchlist activity.
   *
   * @param array $data Parameters:
   *                    - view (int)
   * @param boolean $cron Indicates whether this is being called by the cron job.
   */
  public function __construct($data, $cron) {
      parent::__construct($data, $cron);

      require_once('view.php');
      if ($this->viewinfo = new View($this->view)) {
          $this->ownerinfo = hsc($this->viewinfo->formatted_owner());
      }
      if (empty($this->ownerinfo)) {
          if (!empty($this->cron)) { // probably deleted already
              return;
          }
          throw new ViewNotFoundException(get_string('viewnotfound', 'error', $this->view));
      }
      $viewurl = $this->viewinfo->get_url(false);

      // MySQL compatibility (sigh...).
      $casturl = 'CAST(? AS TEXT)';
      if (is_mysql()) {
          $casturl = '?';
      }
      $sql = 'SELECT u.*, wv.unsubscribetoken, p.method, ap.value AS lang, ' . $casturl . ' AS url
                  FROM {usr_watchlist_view} wv
                  JOIN {usr} u
                      ON wv.usr = u.id
                  LEFT JOIN {usr_activity_preference} p
                      ON p.usr = u.id
                  LEFT OUTER JOIN {usr_account_preference} ap
                      ON (ap.usr = u.id AND ap.field = \'lang\')
                  WHERE (p.activity = ? OR p.activity IS NULL)
                  AND wv.view = ?
             ';
      $this->users = get_records_sql_array(
          $sql,
          array($viewurl, $this->get_id(), $this->view)
      );

      // Remove the view from the watchlist of users who can no longer see it.
      if ($this->users) {
          $userstodelete = array();
          foreach($this->users as $k => &$u) {
              if (!can_view_view($this->view, $u->id)) {
                  $userstodelete[] = $u->id;
                  unset($this->users[$k]);
              }
          }
          if ($userstodelete) {
              delete_records_select(
                  'usr_watchlist_view',
                  'view = ? AND usr IN (' . join(',', $userstodelete) . ')',
                  array($this->view)
              );
          }
      }

      $this->add_urltext(array('key' => 'View', 'section' => 'view'));
  }

  /**
   * Fetch the subject line text.
   *
   * @param object $user A database user object.
   * @return string
   */
  public function get_subject($user) {
      return get_string_from_language($user->lang, 'newwatchlistmessage', 'activity');
  }

  /**
   * Fetch the body message text.
   *
   * @param object $user A database user object.
   * @return string
   */
  public function get_message($user) {
      return get_string_from_language(
              $user->lang,
              'newwatchlistmessageview1',
              'activity',
              $this->viewinfo->get('title'),
              $this->ownerinfo
          );
  }

  /**
   * Get the minimum required data parameters for this activity type.
   *
   * @return array
   */
  public function get_required_parameters() {
      return array('view');
  }
}
