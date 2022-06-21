<?php

require_once('ActivityTypeWatchlist.php');

/**
 * Deal with the settings of the watchlist block.
 *
 * Extending ActivityTypeWatchlist to reuse the functionality and structure.
 */
class ActivityTypeWatchlistnotification extends ActivityTypeWatchlist {

  /**
   * @var integer The View ID.
   */
  protected $view;

  /**
   * @var array An array of block titles.
   */
  protected $blocktitles = array();

  /**
   * @var integer user ID.
   */
  protected $usr;

  /**
   * @var string
   */
  protected $unsubscribelink;

  /**
   * @var string
   */
  protected $unsubscribetype;

  /**
   * Watchlist notifications class
   * @param object $data Parameters:
   *                    - view (int)
   *                    - blocktitles (array: string)
   *                    - usr (int)
   * @param boolean $cron Indicates whether this is being called by the cron job
   */
  public function __construct($data, $cron) {
      parent::__construct($data, $cron);

      $this->blocktitles = $data->blocktitles;
      $this->usr = $data->usr;
      $this->unsubscribelink = get_config('wwwroot') . 'view/unsubscribe.php?a=watchlist&t=';
      $this->unsubscribetype = 'watchlist';
  }

  /**
   * override function get_message to add information about the changed
   * blockinstances
   *
   * @param object $user  A database user object
   * @return string
   */
  public function get_message($user) {
      $message = get_string_from_language(
              $user->lang,
              'newwatchlistmessageview1',
              'activity',
              $this->viewinfo->get('title'),
              $this->ownerinfo
          );

      try {
          foreach ($this->blocktitles as $blocktitle) {
              $message .= "\n" . get_string_from_language($user->lang, 'blockinstancenotification', 'activity', $blocktitle);
          }
      }
      catch(Exception $exc) {
          log_warn(var_export($exc, true));
      }

      return $message;
  }

  /**
   * Overwrite get_type to obfuscate that we are not really an Activity_type.
   */
  public function get_type() {
      return('watchlist');
  }
}
