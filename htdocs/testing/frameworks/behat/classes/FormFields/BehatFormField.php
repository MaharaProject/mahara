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
    WebDriver\Key;

/**
 * Representation of a form field.
 *
 * Basically an interface with Mink session.
 *
 */
class BehatFormField {

    /**
     * @var Session Behat session.
     */
    protected $session;

    /**
     * @var NodeElement The field DOM node to interact with.
     */
    protected $field;

    /**
     * @var string The field's locator.
     */
    protected $fieldlocator = false;


    /**
     * General constructor with the node and the session to interact with.
     *
     * @param Session $session Reference to Mink session to traverse/modify the page DOM.
     * @param NodeElement $fieldnode The field DOM node
     * @return void
     */
    public function __construct(Session $session, NodeElement $fieldnode) {
        $this->session = $session;
        $this->field = $fieldnode;
    }

    /**
     * Empty the value to a text or textarea field.
     *
     * @return void
     */
    public function empty_value() {
        if (strtolower($this->field->getAttribute('type')) == 'text'
            || $this->field->getTagName() == 'textarea') {
            // Using Ctrl-a and Delete to remove the existing value
            // @TODO: for Mac OS, we should use Command-a to select all text
            $this->field->keyPress('a', 'ctrl');
            $this->field->keyPress(Key::DELETE);
        }
    }

    /**
     * Sets the value to a field.
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {
        // We delegate to the best guess, if we arrived here
        // using the generic BehatFormField is because we are
        // dealing with a fgroup element.
        $instance = $this->guess_type();
        return $instance->set_value($value);
    }

    /**
     * Returns the current value of the select element.
     *
     * @return string
     */
    public function get_value() {
        // We delegate to the best guess, if we arrived here
        // using the generic BehatFormField is because we are
        // dealing with a fgroup element.
        $instance = $this->guess_type();
        return $instance->get_value();
    }

    /**
     * Generic match implementation
     *
     * Will work well with text-based fields, extension required
     * for most of the other cases.
     *
     * @param string $expectedvalue
     * @return bool The provided value matches the field value?
     */
    public function matches($expectedvalue) {
        // We delegate to the best guess, if we arrived here
        // using the generic BehatFormField is because we are
        // dealing with a fgroup element.
        $instance = $this->guess_type();
        return $instance->matches($expectedvalue);
    }

    /**
     * Guesses the element type we are dealing with in case is not a text-based element.
     *
     * This class is the generic field type, BehatFieldManager::get_FormFields()
     * should be able to find the appropiate class for the field type, but
     * in cases like mahara form group elements we can not find the type of
     * the field through the DOM so we also need to take care of the
     * different field types from here. If we need to deal with more complex
     * mahara form elements we will need to refactor this simple HTML elements
     * guess method.
     *
     * @return BehatFormField
     */
    private function guess_type() {
        global $CFG;

        // We default to the text-based field if nothing was detected.
        if (!$type = BehatFieldManager::guess_field_type($this->field, $this->session)) {
            $type = 'text';
        }

        $classname = 'BehatForm' . ucfirst($type);
        $classpath = __DIR__ . '/' . $classname . '.php';
        require_once($classpath);
        return new $classname($this->session, $this->field);
    }

    /**
     * Returns whether the scenario is running in a browser that can run Javascript or not.
     *
     * @return bool
     */
    protected function running_javascript() {
        return get_class($this->session->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
    }

    /**
     * Gets the field internal id used by selenium wire protocol.
     *
     * Only available when running_javascript().
     *
     * @throws SystemException
     * @return int
     */
    protected function get_internal_field_id() {

        if (!$this->running_javascript()) {
            throw new SystemException('You can only get an internal ID using the selenium driver.');
        }

        return $this->session->getDriver()->getWebDriverSession()->element('xpath', $this->field->getXPath())->getID();
    }

    /**
     * Checks if the provided text matches the field value.
     *
     * @param string $expectedvalue
     * @return bool
     */
    protected function text_matches($expectedvalue) {
        if (trim($expectedvalue) != trim($this->get_value())) {
            return false;
        }
        return true;
    }

    /**
     * Gets the field locator.
     *
     * Defaults to the field label but you can
     * specify other locators if you are interested.
     *
     * Public visibility as in most cases will be hard to
     * use this method in a generic way, as fields can
     * be selected using multiple ways (label, id, name...).
     *
     * @throws SystemException
     * @param string $locatortype
     * @return string
     */
    protected function get_field_locator($locatortype = false) {

        if (!empty($this->fieldlocator)) {
            return $this->fieldlocator;
        }

        $fieldid = $this->field->getAttribute('id');

        // Defaults to label.
        if ($locatortype == 'label' || $locatortype == false) {

            $labelnode = $this->session->getPage()->find('xpath', '//label[@for="' . $fieldid . '"]');

            // Exception only if $locatortype was specified.
            if (!$labelnode && $locatortype == 'label') {
                throw new SystemException('Field with "' . $fieldid . '" id does not have a label.');
            }

            $this->fieldlocator = $labelnode->getText();
        }

        // Let's look for the name as a second option (more popular than
        // id's when pointing to fields).
        if (($locatortype == 'name' || $locatortype == false) &&
                empty($this->fieldlocator)) {

            $name = $this->field->getAttribute('name');

            // Exception only if $locatortype was specified.
            if (!$name && $locatortype == 'name') {
                throw new SystemException('Field with "' . $fieldid . '" id does not have a name attribute.');
            }

            $this->fieldlocator = $name;
        }

        // Otherwise returns the id if no specific locator type was provided.
        if (empty($this->fieldlocator)) {
            $this->fieldlocator = $fieldid;
        }

        return $this->fieldlocator;
    }

}
