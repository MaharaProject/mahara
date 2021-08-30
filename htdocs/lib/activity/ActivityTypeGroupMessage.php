<?php

require_once('ActivityType.php');

/**
 * A class for messages sent relating to groups and group roles.
 */
class ActivityTypeGroupMessage extends ActivityType {

  /**
   * @var integer group ID.
   */
  protected $group;

  /**
   * @var array group roles.
   */
  protected $roles;

  /**
   * @var boolean Whether the group is deleted.
   */
  protected $deletedgroup;

  /**
   * Activity for group messages
   * @param array $data [
   *                      'group' => integer,
   *                      'roles' => array,
   *                      'deletedgroup' => boolean,
   *                    ]
   * @param boolean $cron Indicates if this is being called by the cron job.
   */
  public function __construct($data, $cron=false) {
      require_once(get_config('libroot') . 'group.php');

      parent::__construct($data, $cron);
      $members = group_get_member_ids($this->group, isset($this->roles) ? $this->roles : null, $this->deletedgroup);
      if (!empty($members)) {
          $this->users = activity_get_users($this->get_id(), $members);
      }
  }

  /**
   * Get the minimum required data parameters for this activity type.
   *
   * @return array
   */
  public function get_required_parameters() {
      return array('group');
  }
}