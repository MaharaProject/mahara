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
 * Class for text-based fields.
 *
 */
class BehatFormText extends BehatFormField {

    /**
     * Sets the value to a field.
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {
        // For autofocus text input and textarea, we need to reset the existing text first
        if (strpos(strtolower($this->field->getAttribute('class')), 'autofocus') !== false
            || strpos(strtolower($this->field->getAttribute('class')), 'text') !== false) {
            $this->empty_value();
        }
        $this->field->setValue($value);
    }

    /**
     * Returns the current value of the element.
     *
     * @return string
     */
    public function get_value() {
        return $this->field->getValue();
    }

    /**
     * Matches the provided value against the current field value.
     *
     * @param string $expectedvalue
     * @return bool The provided value matches the field value?
     */
    public function matches($expectedvalue) {
        return $this->text_matches($expectedvalue);
    }

}
