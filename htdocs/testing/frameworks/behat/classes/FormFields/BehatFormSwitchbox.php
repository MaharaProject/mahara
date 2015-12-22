<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

require_once(__DIR__ . '/BehatFormField.php');

/**
 * Switchbox form field.
 *
 */
class BehatFormSwitchbox extends BehatFormField {

    /**
     * Sets the value of a switchbox.
     *
     * Anything !empty() is considered checked.
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {

        $needclick = false;
        if (!empty($value) && !$this->field->isChecked()) {

            if (!$this->running_javascript()) {
                $this->field->check();
                return;
            }

            $needclick = true;

        }
        else if (empty($value) && $this->field->isChecked()) {

            if (!$this->running_javascript()) {
                $this->field->uncheck();
                return;
            }

            $needclick = true;

        }

        if ($needclick) {
            // For some reasons, the Mink function click() and check() do not work
            // Using jQuery as a workaround
            $jscode = "jQuery(\"div.switchbox:contains(" . $this->get_field_locator() . ") input[type=checkbox]\")[0].click();";
            $this->session->executeScript($jscode);
        }
    }

    /**
     * Returns whether the field is checked or not.
     *
     * @return bool True if it is checked and false if it's not.
     */
    public function get_value() {
        return $this->field->isChecked();
    }

    /**
     * Is it enabled?
     *
     * @param string $expectedvalue Anything !empty() is considered checked.
     * @return bool
     */
    public function matches($expectedvalue = false) {

        $ischecked = $this->field->isChecked();

        // Any non-empty value provided means that it should be checked.
        if (!empty($expectedvalue) && $ischecked) {
            return true;
        }
        else if (empty($expectedvalue) && !$ischecked) {
            return true;
        }

        return false;
    }

}
