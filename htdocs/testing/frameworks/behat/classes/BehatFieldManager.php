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

use Behat\Mink\Session as Session,
    Behat\Mink\Element\NodeElement as NodeElement,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\MinkExtension\Context\RawMinkContext as RawMinkContext;

/**
 * Helper to interact with mahara form fields.
 *
 */
class BehatFieldManager {

    /**
     * Gets an instance of the form field from it's label
     *
     * @param string $label
     * @param RawMinkContext $context
     * @return BehatFormField
     */
    public static function get_form_field_from_label($label, RawMinkContext $context) {

        // There are mahara form elements that are not directly related with
        // a basic HTML form field, we should also take care of them.
        try {
            // The DOM node.
            $fieldnode = $context->find_field($label);
        }
        catch (ElementNotFoundException $fieldexception) {

            // Looking for labels that points to filemanagers.
            try {
                $fieldnode = $context->find_filemanager($label);
            }
            catch (ElementNotFoundException $filemanagerexception) {
                // We want the generic 'field' exception.
                throw $fieldexception;
            }
        }

        // The behat field manager.
        return self::get_form_field($fieldnode, $context->getSession());
    }

    /**
     * Gets an instance of the form field.
     *
     * Not all the fields are part of a mahara form, in this
     * cases it fallsback to the generic form field. Also note
     * that this generic field type is using a generic setValue()
     * method from the Behat API, which is not always good to set
     * the value of form elements.
     *
     * @param NodeElement $fieldnode
     * @param Session $session The behat browser session
     * @return BehatFormField
     */
    public static function get_form_field(NodeElement $fieldnode, Session $session) {

        // Get the field type if is part of a maharaform.
        if (self::is_maharaform_field($fieldnode)) {
            $type = self::get_field_node_type($fieldnode, $session);
        }

        // If is not a maharaforms field use the base field type.
        if (empty($type)) {
            $type = 'field';
        }

        return self::get_field_instance($type, $fieldnode, $session);
    }

    /**
     * Returns the appropiate BehatFormField according to the provided type.
     *
     * It defaults to BehatFormField.
     *
     * @param string $type The field type (checkbox, date_selector, text...)
     * @param NodeElement $fieldnode
     * @param Session $session The behat session
     * @return BehatFormField
     */
    public static function get_field_instance($type, NodeElement $fieldnode, Session $session) {

        // If the field is not part of a maharaform, we should still try to find out
        // which field type are we dealing with.
        if ($type == 'field' &&
                $guessedtype = self::guess_field_type($fieldnode, $session)) {
            $type = $guessedtype;
        }

        $classname = 'BehatForm' . ucfirst($type);

        // Fallsback on the type guesser if nothing specific exists.
        $classpath = __DIR__ . '/FormFields/' . $classname . '.php';
        if (!file_exists($classpath)) {
            $classname = 'BehatFormField';
            $classpath = __DIR__ . '/FormFields/' . $classname . '.php';
        }

        // Returns the instance.
        require_once($classpath);
        return new $classname($session, $fieldnode);
    }

    /**
     * Guesses a basic field type and returns it.
     *
     * This method is intended to detect HTML form fields when no
     * maharaform-specific elements have been detected.
     *
     * @param NodeElement $fieldnode
     * @param Session $session
     * @return string|bool The field type or false.
     */
    public static function guess_field_type(NodeElement $fieldnode, Session $session) {

        // Textareas are considered text based elements.
        $tagname = strtolower($fieldnode->getTagName());
        if ($tagname == 'textarea') {

            // If there is an iframe with $id + _ifr there a TinyMCE editor loaded.
            $xpath = '//iframe[@id="' . $fieldnode->getAttribute('id') . '_ifr"]';
            if ($session->getPage()->find('xpath', $xpath)) {
                return 'editor';
            }
            return 'textarea';

        }
        else if ($tagname == 'input') {
            $type = $fieldnode->getAttribute('type');
            switch ($type) {
                case 'text':
                case 'password':
                case 'email':
                case 'file':
                    return 'text';
                case 'checkbox':
                    $xpath = "//input[@id='" . $fieldnode->getAttribute('id') . "'" .
                                    "and contains(concat(' ', normalize-space(@class), ' '), ' switchbox ')]";
                    if ($session->getPage()->find('xpath', $xpath)) {
                        return 'switchbox';
                    }
                    else {
                        return 'checkbox';
                    }
                    break;
                case 'radio':
                    return 'radio';
                    break;
                default:
                    // Here we return false because all text-based
                    // fields should be included in the first switch case.
                    return false;
            }

        }
        else if ($tagname == 'select') {
            // Select tag.
            return 'select';
        }

        // We can not provide a closer field type.
        return false;
    }

    /**
     * Check if the field is a maharaform field type.
     *
     * Note that there are fields inside maharaforms that are not
     * maharaform element; this method can not detect this, this will
     * be managed by get_field_node_type, after failing to find the form
     * element element type.
     *
     * @param NodeElement $fieldnode
     * @return bool
     */
    protected static function is_maharaform_field(NodeElement $fieldnode) {

        // We already waited when getting the NodeElement and we don't want an exception if it's not part of a maharaform.
        $parentformfound = $fieldnode->find('xpath',
            "/ancestor::fieldset" .
            "/ancestor::form[contains(concat(' ', normalize-space(@class), ' '), ' pieform ')]"
        );

        return ($parentformfound != false);
    }

    /**
     * Find the special type of a mahara field
     *
     * We look for the class to detect the correct type
     *
     * @param NodeElement $fieldnode The current node.
     * @param Session $session The behat browser session
     * @return mixed A NodeElement if we continue looking for the element type and String or false when we are done.
     */
    protected static function get_field_node_type(NodeElement $fieldnode, Session $session) {

        $specialfieldnodetypes = array(
            'artefactchooser', 'authlist', 'autocomplete',
            'calendar', 'color', 'emaillist', 'expiry', 'filebrowser', 'files', 'image',
            'multitext', 'password', 'rolepermissions', 'tags', 'userlist', 'weight', 'wysiwyg',
        );
        if ($class = $fieldnode->getAttribute('class')) {

            if (in_array($class, $specialfieldnodetypes)) {
                return $class;
            }

        }

        return false;
    }

}
