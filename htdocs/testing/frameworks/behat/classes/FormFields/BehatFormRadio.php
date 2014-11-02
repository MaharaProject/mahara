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

require_once(__DIR__ . '/BehatFormCheckbox.php');

/**
 * Radio input form field.
 *
 * Extends BehatFormCheckbox as the set_value() behaviour
 * is the same and it behaves closer to a checkbox than to
 * a text field.
 *
 * This form field type can be added to forms as any other
 * mahara form element, but it does not make sense without
 * a group of radio inputs, so is hard to find it alone and
 * detect it by behat_field_manager::get_form_field(), where is useful
 * is when the default BehatFormField class is being used, it
 * finds a input[type=radio] and it delegates set_value() and
 * get_value() to behat_form_radio.
 *
 */
class BehatFormRadio extends BehatFormCheckbox {

    /**
     * Returns the radio input value attribute.
     *
     * Here we can not extend BehatFormCheckbox because
     * isChecked() does internally a (bool)getValue() and
     * it is not good for radio buttons.
     *
     * @return string The value attribute
     */
    public function get_value() {
        return (bool)$this->field->getAttribute('checked');
    }

    /**
     * Sets the value of a radio
     *
     * Partially overwriting BehatFormCheckbox
     * implementation as when JS is disabled we
     * can not check() and we should use setValue()
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {

        if ($this->running_javascript()) {
            parent::set_value($value);
        }
        else {
            // Goutte does not accept a check nor a click in an input[type=radio].
            $this->field->setValue($this->field->getAttribute('value'));
        }
    }

    /**
     * Returns whether the provided value matches the current value or not.
     *
     * @param string $expectedvalue
     * @return bool
     */
    public function matches($expectedvalue = false) {
        return $this->text_matches($expectedvalue);
    }
}
