<?php

require_once('ProductFamily.php');

/**
 * LTI Product family desire2learn class
 */
class LtiAdvantageProductFamilyDesire2learn extends LtiAdvantageProductFamily {

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
      if (!key_exists($this->vendorkey, $data)) {
          $msg = get_string('platformvendorkeyinvalid', 'module.lti_advantage', implode(', ', array_keys($data)));
          throw new WebserviceInvalidResponseException($msg);
      }
      $userdata = $data[$this->vendorkey];
      // Normalise some of the data
      $userdata['userid'] = $userdata['user_id'];
      $this->userdata = $userdata;
  }
}
