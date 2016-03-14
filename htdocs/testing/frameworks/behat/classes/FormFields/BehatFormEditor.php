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

use Behat\Mink\Element\NodeElement as NodeElement;

require_once(__DIR__ . '/BehatFormTextarea.php');

/**
 * Mahara editor field using TinyMCE.
 *
 */
class BehatFormEditor extends BehatFormTextarea {

    /**
     * Sets the value to a field.
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {

        $lastexception = null;

        // We want the editor to be ready, otherwise the value can not
        // be set and an exception is thrown.
        for ($i = 0; $i < BehatBase::WAIT_FOR_EDITOR_RETRIES; $i++) {
            try {
                // Get tinyMCE editor id if it exists.
                if ($editorid = $this->get_editor_id()) {

                    // Set the value to the iframe and save it to the textarea.
                    $this->session->executeScript('
                        return tinyMCE.get("' . $editorid . '").setContent("' . $value . '");
                    ');

                }
                else {
                    // Set the value to a textarea otherwise.
                    parent::set_value($value);
                }
                return;

            }
            catch (Exception $e) {
                // Catching any kind of exception and ignoring it until times out.
                $lastexception = $e;

                // Waiting 0.1 seconds.
                usleep(100000);
            }
        }

        // If it is not available we throw the last exception.
        if (is_a($lastexception, 'Exception')) {
            throw $lastexception;
        }
    }

    /**
     * Matches the provided value against the current field value.
     *
     * @param string $expectedvalue
     * @return bool The provided value matches the field value?
     */
    public function matches($expectedvalue) {
        // A text editor may silently wrap the content in p tags (or not). Neither is an error.
        return $this->text_matches($expectedvalue) || $this->text_matches('<p>' . $expectedvalue . '</p>');
    }

    /**
     * Returns the field value.
     *
     * @return string
     */
    public function get_value() {

        // Can be be a string value or an exception depending whether the editor loads or not.
        $lastoutcome = '';

        // We want the editor to be ready to return the correct value, sometimes the
        // page loads too fast and the returned value may be '' if the editor didn't
        // have enough time to load completely despite having a different value.
        for ($i = 0; $i < BehatBase::WAIT_FOR_EDITOR_RETRIES; $i++) {
            try {

            // Get tinyMCE editor id if it exists.
                if ($editorid = $this->get_editor_id()) {

                // Save the current iframe value in case default value has been edited.
                    $this->session->executeScript('tinyMCE.get("' . $editorid . '").save();');
                }

                $lastoutcome = $this->field->getValue();

                // We only want to wait until it times out if the value is empty.
                if ($lastoutcome != '') {
                    return $lastoutcome;
                }

            }
            catch (Exception $e) {
                // Catching any kind of exception and ignoring it until times out.
                $lastoutcome = $e;

                // Waiting 0.1 seconds.
                usleep(100000);
            }
        }

        // If it is not available we throw the last exception.
        if (is_a($lastoutcome, 'Exception')) {
            throw $lastoutcome;
        }

        // Return the value if there are no exceptions it will be '' at this point
        return $lastoutcome;
    }

    /**
     * Returns the tinyMCE editor id or false if it is not available.
     *
     * The editor availability depends on the driver running the tests; Goutte
     * can not execute Javascript, also some Mahara settings disables the HTML
     * editor.
     *
     * @return mixed The id of the editor of false if is not available
     */
    protected function get_editor_id() {

        // Non-JS drivers throws exceptions when running JS.
        try {
            $available = $this->session->evaluateScript('return (typeof tinymce != "undefined")');

            // Also checking that it exist a tinyMCE editor for the requested field.
            $editorid = $this->field->getAttribute('id');
            $available = $this->session->evaluateScript('return (typeof tinymce.get("' . $editorid . '") != "undefined")');

        }
        catch (Exception $e) {
            return false;
        }

        // No available if JS drivers returned false.
        if ($available == false) {
            return false;
        }

        return $editorid;
    }

}

