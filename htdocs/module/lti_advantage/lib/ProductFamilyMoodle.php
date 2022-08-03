<?php

require_once('ProductFamily.php');

/**
 * LTI Product family desire2learn class
 */
class LtiAdvantageProductFamilyMoodle extends LtiAdvantageProductFamily {

  /**
   * Vendor key
   * @var string
   */
  protected $vendorkey;

  /**
   * Desire2Learn class for product family
   *
   * @param array $data The parameter data received from an LTI 1.3 get_launch_data() call
   * @param array $vendorkey A vendor key we store in the database
   */
  public function __construct($data, $vendorkey) {
      parent::__construct($data, null);
      $this->vendorkey = $vendorkey;
      $this->set_userdata($data);
  }

  /**
   * Set the values for user ID and username from the get_launch_data() call
   *
   * @param array $data The parameter data received from an LTI 1.3 get_launch_data() call
   */
  public function set_userdata($data) {
      $userdata = array();
      $userdata['userid'] = $data['sub'];
      $userdata['username'] = $data['https://purl.imsglobal.org/spec/lti/claim/ext']['user_username'];
      $this->userdata = $userdata;
  }
}
