<?php
/**
 * LTI Product family base class
 *
 * @package    mahara
 * @subpackage module/lit_advantage
 * @author     Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
abstract class LtiAdvantageProductFamily {

  /**
   * Unique ID
   */
  protected $guid;

  /**
   * Product family code
   */
  protected $product_family_code;

  /**
   * name of the tool platform
   * @var string
   */
  protected $name;

  /**
   * The user data
   * @var array
   */
  protected $userdata = array();

  /**
   * Child classes MUST call the parent constructor.
   *
   * @param array $data The parameter data received from an LTI 1.3 get_launch_data() call
   * @param array $vendorkey A vendor key we store in the database
   */
  public function __construct($data, $vendorkey=null) {
      $this->set_parameters($data);
  }

  /**
   * Set supplied data to the class properties.
   *
   * @param array $data  An associative array with keys matching properties of the class
   */
  private function set_parameters($data) {
      if (!empty($data['https://purl.imsglobal.org/spec/lti/claim/tool_platform'])) {
          foreach ($data['https://purl.imsglobal.org/spec/lti/claim/tool_platform'] as $key => $value) {
              if (property_exists($this, $key)) {
                  $this->{$key} = $value;
              }
          }
      }
  }

  /**
   * Fetch the values for user ID and username from the get_launch_data() call
   *
   * @param string $field Get value by name
   * @return mixed The value of the requested $field or all of the userdata if null
   */
  public function get_userdata($field=null) {
      if ($field) {
          return isset($this->userdata[$field]) ? $this->userdata[$field] : null;
      }
      return $this->userdata;
  }
}
