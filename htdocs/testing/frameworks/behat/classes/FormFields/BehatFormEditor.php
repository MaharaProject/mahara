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
 * Moodle editor field.
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

        $editorid = $this->field->getAttribute('id');
        if ($this->running_javascript()) {
            $value = addslashes($value);
            $js = '
var editor = Y.one(document.getElementById("'.$editorid.'editable"));
if (editor) {
    editor.setHTML("' . $value . '");
}
editor = Y.one(document.getElementById("'.$editorid.'"));
editor.set("value", "' . $value . '");
';
            $this->session->executeScript($js);
        }
        else {
            parent::set_value($value);
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
}

