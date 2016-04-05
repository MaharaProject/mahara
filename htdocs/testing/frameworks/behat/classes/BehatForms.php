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
        $jscode = "jQuery(\"fieldset.collapsible legend a.collapsed\").each(function(){jQuery(this).trigger('click');});";
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
     * Fills in Select2 field with specified
     *
     * @When /^(?:|I )set the select2 field "(?P<field>(?:[^"]|\\")*)" to "(?P<textValues>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )set the select2 value "(?P<textValues>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function iFillInSelect2Field($field, $textValues) {
        $page = $this->getSession()->getPage();
        $values = array();
        foreach(preg_split('/,\s*/', $textValues) as $value) {
            $option = $page->find('xpath', '//select[@id="' . $field . '"]//option[text()="' . $value . '"]');
            $values[] = $option->getAttribute('value');
        }

        $values = json_encode($values);
        $this->getSession()->executeScript("jQuery('#{$field}').val({$values}).trigger('change');");
    }
    /**
     * Fill Select2 input field
     *
     * @When /^(?:|I )fill in select2 input "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function iFillInSelect2InputWith($field, $value) {
        $page = $this->getSession()->getPage();
        $this->select2OpenField($page, $field);
        $this->select2FillSearchField($page, $field, $value);
    }
    /**
     * Fill Select2 input field and select a value
     *
     * @When /^(?:|I )fill in select2 input "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" and select "(?P<entry>(?:[^"]|\\")*)"$/
     */
    public function iFillInSelect2InputWithAndSelect($field, $value, $entry) {
        $page = $this->getSession()->getPage();
        $this->select2OpenField($page, $field);
        $this->select2FillSearchField($page, $field, $value);
        $this->select2Value($page, $field, $entry);
        $this->getSession()->executeScript("jQuery('#{$field}').trigger('change');");
    }
    /**
     * Open Select2 choice list
     *
     * @param DocumentElement $page
     * @param string          $field
     * @throws \Exception
     */
    private function select2OpenField($page, $field) {
        $fieldName = sprintf('#%s', $field);
        $inputField = $page->find('css', $fieldName);
        if (!$inputField) {
            throw new \Exception(sprintf('No field "%s" found', $field));
        }
        $choice = $inputField->getParent()->find('css', '.select2-selection');
        if (!$choice) {
            throw new \Exception(sprintf('No select2 choice found for "%s"', $field));
        }
        $choice->press();
    }
    /**
     * Fill Select2 search field
     *
     * @param DocumentElement $page
     * @param string          $field
     * @param string          $value
     * @throws \Exception
     */
    private function select2FillSearchField($page, $field, $value) {
        $driver = $this->getSession()->getDriver();
        $select2Input = $this->getSession()->getDriver()->getWebDriverSession()->element('xpath', "//html/descendant-or-self::*[@class and contains(concat(' ', normalize-space(@class), ' '), ' select2-search__field ')]");
        if (!$select2Input) {
            throw new \Exception(sprintf('No field "%s" found', $field));
        }
        $select2Input->postValue(array('value' => array($value)));
        $this->getSession()->wait(10000, "(jQuery('#select2-{$field}-results .loading-results').length === 0)");
    }
    /**
     * Select value in choice list
     *
     * @param DocumentElement $page
     * @param string          $field
     * @param string          $value
     * @throws \Exception
     */
    private function select2Value($page, $field, $value) {
        $chosenResults = $page->findAll('css', '.select2-results ul li');
        foreach ($chosenResults as $result) {
            if ($result->getText() == $value) {
                $result->click();
                return;
            }
        }
        throw new \Exception(sprintf('Value "%s" not found for "%s"', $value, $field));
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
     * Checks, that given select box contains the specified options.
     *
     * @Then /^the "(?P<select_string>(?:[^"]|\\")*)" select box should contain all "(?P<option_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $select The select element name
     * @param string $option The option text/value. Separated by | (pipe).
     */
    public function the_select_box_should_contain_all($select, $option) {

        $selectnode = $this->find_field($select);
        $optionsarr = array(); // Array of passed value/text options to test.

        // Can pass multiple pipe separated values.
        foreach (preg_split('/\|/', $option) as $opt) {
            $optionsarr[] = trim($opt);
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
                 "//input[@id=" . $textliteral . "]" .
                 "|" .
                 "//div[contains(concat(' ', normalize-space(@class), ' '), ' switchbox ')" .
                 " and contains(normalize-space(child::span[text()]), " . $textliteral . ")]" .
                 "//input[@type='checkbox']";
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
                    "//input[@type='checkbox']" .
                 "|" .
                 "//input[@id=" . $textliteral . "]" .
                 "|" .
                 "//div[contains(concat(' ', normalize-space(@class), ' '), ' switchbox ')" .
                 " and contains(normalize-space(child::span[text()]), " . $textliteral . ")]" .
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

}
