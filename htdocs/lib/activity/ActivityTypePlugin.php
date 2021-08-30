<?php

require_once('ActivityType.php');

/**
 * Plugin abstract class for adding activity types via a plugin.
 *
 * When making new ActivityType for your plugin they should extend this class.
 */
abstract class ActivityTypePlugin extends ActivityType {

  /**
   * Fetch the plugin type, e.g. 'artefact'.
   */
  abstract public function get_plugintype();

  /**
   * Fetch the plugin name, e.g. 'comment'.
   */
  abstract public function get_pluginname();

  /**
   * Get the class name based on plugin type and name.
   *
   * @return string
   */
  public function get_type() {
      $prefix = 'ActivityType' . $this->get_plugintype() . $this->get_pluginname();
      return strtolower(substr(get_class($this), strlen($prefix)));
  }

  /**
   * Get the ID of the plugin type from the database.
   *
   * @return integer
   */
  public function get_id() {
      if (!isset($this->id)) {
          $tmp = activity_locate_typerecord($this->get_type(), $this->get_plugintype(), $this->get_pluginname());
          $this->id = $tmp->id;
      }
      return $this->id;
  }
}
