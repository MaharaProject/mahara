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

require_once(__DIR__ . '/BehatBase.php');
require_once(__DIR__ . '/BehatFieldManager.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Behat\Context\Step\When as When,
    Behat\Behat\Context\Step\Then as Then,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Element\NodeElement as NodeElement,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Forms-related steps definitions.
 *
 */
class BehatForms extends BehatBase {

    /**
     * Fills a form with field/value data. More info in http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps.
     *
     * @Given /^I set the following fields to these values:$/
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param TableNode $data
     */
    public function i_set_the_following_fields_to_these_values(TableNode $data) {

        // Expand all fields in case we have.
        $this->expand_all_fields();
        // Wait until all fieldsets are expanded
        $this->wait_for_pending_js();

        $datahash = $data->getRowsHash();

        // The action depends on the field type.
        foreach ($datahash as $locator => $value) {
            $this->set_field_value($locator, $value);
        }
    }

    /**
     * Expands all moodleform's fields, including collapsed fieldsets and advanced fields if they are present.
     * @Given /^I expand all fieldsets$/
     */
    public function i_expand_all_fieldsets() {
        $this->expand_all_fields();
    }

    /**
     * Expands all mahara form fieldsets if they exists and collapsed.
     *
     * Externalized from i_expand_all_fields to call it from
     * other form-related steps without having to use steps-group calls.
     *
     * @return void
     */
    protected function expand_all_fields() {

        // Using jQuery (work properly and run faster then Mink SeleniumDriver)
        $jscode = "jQuery(\"fieldset.collapsed legend a\").each(function(){jQuery(this).trigger('click');});";
        $this->getSession()->executeScript($jscode);

    }

    /**
     * Sets the specified value to the field.
     *
     * @Given /^I set the field "(?P<field_string>(?:[^"]|\\")*)" to "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $field
     * @param string $value
     * @return void
     */
    public function i_set_the_field_to($field, $value) {
        $this->set_field_value($field, $value);
    }

    /**
     * Checks, the field matches the value. More info in http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps.
     *
     * @Then /^the field "(?P<field_string>(?:[^"]|\\")*)" matches value "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $field
     * @param string $value
     * @return void
     */
    public function the_field_matches_value($field, $value) {

        // Get the field.
        $formfield = BehatFieldManager::get_form_field_from_label($field, $this);

        // Checks if the provided value matches the current field value.
        if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                'The \'' . $field . '\' value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                $this->getSession()
            );
        }
    }

    /**
     * Checks, the field does not match the value. More info in http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps.
     *
     * @Then /^the field "(?P<field_string>(?:[^"]|\\")*)" does not match value "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $field
     * @param string $value
     * @return void
     */
    public function the_field_does_not_match_value($field, $value) {

        // Get the field.
        $formfield = BehatFieldManager::get_form_field_from_label($field, $this);

        // Checks if the provided value matches the current field value.
        if ($formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                'The \'' . $field . '\' value matches \'' . $value . '\' and it should not match it' ,
                $this->getSession()
            );
        }
    }

    /**
     * Checks, the provided field/value matches. More info in http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps.
     *
     * @Then /^the following fields match these values:$/
     * @throws ExpectationException
     * @param TableNode $data Pairs of | field | value |
     */
    public function the_following_fields_match_these_values(TableNode $data) {

        // Expand all fields in case we have.
        $this->expand_all_fields();

        $datahash = $data->getRowsHash();

        // The action depends on the field type.
        foreach ($datahash as $locator => $value) {
            $this->the_field_matches_value($locator, $value);
        }
    }

    /**
     * Checks that the provided field/value pairs don't match. More info in http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps.
     *
     * @Then /^the following fields do not match these values:$/
     * @throws ExpectationException
     * @param TableNode $data Pairs of | field | value |
     */
    public function the_following_fields_do_not_match_these_values(TableNode $data) {

        // Expand all fields in case we have.
        $this->expand_all_fields();

        $datahash = $data->getRowsHash();

        // The action depends on the field type.
        foreach ($datahash as $locator => $value) {
            $this->the_field_does_not_match_value($locator, $value);
        }
    }

    /**
     * Checks, that given select box contains the specified option.
     *
     * @Then /^the "(?P<select_string>(?:[^"]|\\")*)" select box should contain "(?P<option_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $select The select element name
     * @param string $option The option text/value. Plain value or comma separated
     *                       values if multiple. Commas in multiple values escaped with backslash.
     */
    public function the_select_box_should_contain($select, $option) {

        $selectnode = $this->find_field($select);
        $multiple = $selectnode->hasAttribute('multiple');
        $optionsarr = array(); // Array of passed value/text options to test.

        if ($multiple) {
            // Can pass multiple comma separated, with valuable commas escaped with backslash.
            foreach (preg_replace('/\\\,/', ',',  preg_split('/(?<!\\\),/', $option)) as $opt) {
                $optionsarr[] = trim($opt);
            }
        }
        else {
            // Only one option has been passed.
            $optionsarr[] = trim($option);
        }

        // Now get all the values and texts in the select.
        $options = $selectnode->findAll('xpath', '//option');
        $values = array();
        foreach ($options as $opt) {
            $values[trim($opt->getValue())] = trim($opt->getText());
        }

        foreach ($optionsarr as $opt) {
            // Verify every option is a valid text or value.
            if (!in_array($opt, $values) && !array_key_exists($opt, $values)) {
                throw new ExpectationException(
                    'The select box "' . $select . '" does not contain the option "' . $opt . '"',
                    $this->getSession()
                );
            }
        }
    }

    /**
     * Checks, that given select box does not contain the specified option.
     *
     * @Then /^the "(?P<select_string>(?:[^"]|\\")*)" select box should not contain "(?P<option_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $select The select element name
     * @param string $option The option text/value. Plain value or comma separated
     *                       values if multiple. Commas in multiple values escaped with backslash.
     */
    public function the_select_box_should_not_contain($select, $option) {

        $selectnode = $this->find_field($select);
        $multiple = $selectnode->hasAttribute('multiple');
        $optionsarr = array(); // Array of passed value/text options to test.

        if ($multiple) {
            // Can pass multiple comma separated, with valuable commas escaped with backslash.
            foreach (preg_replace('/\\\,/', ',',  preg_split('/(?<!\\\),/', $option)) as $opt) {
                $optionsarr[] = trim($opt);
            }
        }
        else {
            // Only one option has been passed.
            $optionsarr[] = trim($option);
        }

        // Now get all the values and texts in the select.
        $options = $selectnode->findAll('xpath', '//option');
        $values = array();
        foreach ($options as $opt) {
            $values[trim($opt->getValue())] = trim($opt->getText());
        }

        foreach ($optionsarr as $opt) {
            // Verify every option is not a valid text or value.
            if (in_array($opt, $values) || array_key_exists($opt, $values)) {
                throw new ExpectationException(
                    'The select box "' . $select . '" contains the option "' . $opt . '"',
                    $this->getSession()
                );
            }
        }
    }

    /**
     * Generic field setter.
     *
     * Internal API method, a generic *I set "VALUE" to "FIELD" field*
     * could be created based on it.
     *
     * @param string $fieldlocator The pointer to the field, it will depend on the field type.
     * @param string $value
     * @return void
     */
    protected function set_field_value($fieldlocator, $value) {

        $fieldlocator = $this->unescapeDoubleQuotes($fieldlocator);
        $field = BehatFieldManager::get_form_field_from_label($fieldlocator, $this);
        $field->set_value($value);
    }

    /**
     * Enable a switch
     *
     * @When /^I enable the switch "(?P<fieldlabel>(?:[^"]|\\")*)"$/
     * @param string $fieldlabel the label of the field
     * @throws ElementNotFoundException
     */
    public function i_enable_switch($fieldlabel) {

        // Find the switch.
        $textliteral = $this->escaper->escapeLiteral($fieldlabel);
        $exception = new ElementNotFoundException($this->getSession(), 'field', null, $fieldlabel);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' switchbox ')" .
                    " and contains(normalize-space(child::label[text()]), " . $textliteral . ")]" .
                    "//input[@type='checkbox']" .
                 "|" .
                "//input[@id=" . $textliteral . "]";
        $switch_node = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($switch_node);
        if (!$switch_node->isChecked()) {
            // For some reasons, the Mink function click() and check() do not work
            // Using jQuery as a workaround
            $jscode = "jQuery(\"div.switchbox:contains(" . $this->escapeDoubleQuotes($textliteral) . ") input[type=checkbox]\")[0].click();";
            $this->getSession()->executeScript($jscode);
        }

    }

    /**
     * Enable a switch by id
     *
     * @When /^I enable the switch by id "(?P<fieldid>(?:[^"]|\\")*)"$/
     * @param string $fieldid of the field
     * @throws ElementNotFoundException
     */
    public function i_enable_switch_by_id($fieldid) {

        // Find the switch.
        $exception = new ElementNotFoundException($this->getSession(), 'field', null, $fieldid);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' switch ')]" .
                    "//input[@type='checkbox' and @id='" . $fieldid . "']";
        $switch_node = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($switch_node);
        if (!$switch_node->isChecked()) {
            // For some reasons, the Mink function click() and check() do not work
            // Using jQuery as a workaround
            $jscode = "jQuery(\"#" . $fieldid . "\")[0].click();";
            $this->getSession()->executeScript($jscode);
        }

    }

    /**
     * Enable switches
     *
     * @When /^I enable the following switches:$/
     * @param TableNode $switches
     * @throws ElementNotFoundException
     */
    public function i_enable_following_switches($switches) {

        foreach ($switches->getRows() as $s) {
            $this->i_enable_switch($s[0]);
        }
    }

    /**
     * Disable a switch
     *
     * @When /^I disable the switch "(?P<fieldlabel>(?:[^"]|\\")*)"$/
     * @param string $fieldlabel the label of the field
     * @throws ElementNotFoundException
     */
    public function i_disable_switch($fieldlabel) {

        // Find the switch.
        $textliteral = $this->escaper->escapeLiteral($fieldlabel);
        $exception = new ElementNotFoundException($this->getSession(), 'field', null, $fieldlabel);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' switchbox ')" .
                    " and contains(normalize-space(child::label[text()]), " . $textliteral . ")]" .
                    "//input[@type='checkbox']";
        $switch_node = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($switch_node);
        if ($switch_node->isChecked()) {
            // For some reasons, the Mink function click() and check() do not work
            // Using jQuery as a workaround
            $jscode = "jQuery(\"div.switchbox:contains(" . $this->escapeDoubleQuotes($textliteral) . ") input[type=checkbox]\")[0].click();";
            $this->getSession()->executeScript($jscode);
        }

    }

    /**
     * Disable a switch by id
     *
     * @When /^I disable the switch by id "(?P<fieldid>(?:[^"]|\\")*)"$/
     * @param string $fieldid of the field
     * @throws ElementNotFoundException
     */
    public function i_disable_switch_by_id($fieldid) {

        // Find the switch.
        $exception = new ElementNotFoundException($this->getSession(), 'field', null, $fieldid);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' switch ')]" .
                    "//input[@type='checkbox' and @id='" . $fieldid . "']";
        $switch_node = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($switch_node);
        if ($switch_node->isChecked()) {
            // For some reasons, the Mink function click() and check() do not work
            // Using jQuery as a workaround
            $jscode = "jQuery(\"#" . $fieldid . "\")[0].click();";
            $this->getSession()->executeScript($jscode);
        }

    }

    /**
     * Disable switches
     *
     * @When /^I disable the following switches:$/
     * @param TableNode $switches
     * @throws ElementNotFoundException
     */
    public function i_disable_following_switches($switches) {

        foreach ($switches->getRows() as $s) {
            $this->i_disable_switch($s[0]);
        }
    }

    /**
     * Need to adjust the position of config form as behat browser can't handle 'transform' css element
     * Which makes the configureblock sit too far down and right to be interacted with correctly
     *
     * @When /^I adjust the config form$/
     */
    public function i_move_config_into_view() {
         $this->getSession()->executeScript('jQuery("#configureblock").css("top", "0%").css("left", "0%");');
    }

}
