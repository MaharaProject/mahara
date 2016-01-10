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
 * Checkbox form field.
 *
 */
class BehatFormCheckbox extends BehatFormField {

    /**
     * Sets the value of a checkbox.
     *
     * Anything !empty() is considered checked.
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {

        if (!empty($value) && !$this->field->isChecked()) {

            if (!$this->running_javascript()) {
                $this->field->check();
                return;
            }

            // Check it if it should be checked and it is not.
            $this->field->click();

            // Trigger the onchange event as triggered when 'checking' the checkbox.
            $this->session->getDriver()->triggerSynScript(
                $this->field->getXPath(),
                "Syn.trigger('change', {}, {{ELEMENT}})"
            );

        }
        else if (empty($value) && $this->field->isChecked()) {

            if (!$this->running_javascript()) {
                $this->field->uncheck();
                return;
            }

            // Uncheck if it is checked and shouldn't.
            $this->field->click();

            // Trigger the onchange event as triggered when 'checking' the checkbox.
            $this->session->getDriver()->triggerSynScript(
                $this->field->getXPath(),
                "Syn.trigger('change', {}, {{ELEMENT}})"
            );
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
