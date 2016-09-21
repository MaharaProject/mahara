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

/**
 * General use steps definitions.
 *
 */

require_once(__DIR__ . '/BehatBase.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\DriverException as DriverException,
    WebDriver\Exception\NoSuchElement as NoSuchElement,
    WebDriver\Exception\StaleElementReference as StaleElementReference;

/**
 * Cross plugin steps definitions.
 *
 * Basic web application definitions from MinkExtension and
 * BehatchExtension. Definitions modified according to our needs
 * when necessary and including only the ones we need to avoid
 * overlapping and confusion.
 *
 */
class BehatGeneral extends BehatBase {

    /**
     * Login as a mahara user
     *
     * @Given /^I log in as "(?P<username>(?:[^"]|\\")*)" with password "(?P<password>(?:[^"]|\\")*)"$/
     */
    public function i_login_as($username, $password) {
        $this->visitPath("/");
        $this->wait_until_the_page_is_ready();
        $this->getSession()->getPage()->fillField(
            "login_username",
            $username
        );
        $this->getSession()->getPage()->fillField(
            "login_password",
            $password
        );
        $this->getSession()->getPage()->pressButton("Login");
    }

    /**
     * Log out of Mahara
     *
     * @Given /^I log out$/
     */
    public function i_logout() {
        $this->visitPath("/");
        $this->wait_until_the_page_is_ready();
        $this->i_follow_in_the("Logout", "//header//li[contains(concat(' ', normalize-space(@class), ' '), ' btn-logout ')]", "xpath_element");
    }

    /**
     * Follows the page redirection. Use this step after any action that shows a message and waits for a redirection
     *
     * @Given /^I wait to be redirected$/
     */
    public function i_wait_to_be_redirected() {

        // Xpath and processes based on core_renderer::redirect_message(), core_renderer::$metarefreshtag and
        // moodle_page::$periodicrefreshdelay possible values.
        if (!$metarefresh = $this->getSession()->getPage()->find('xpath', "//head/descendant::meta[@http-equiv='refresh']")) {
            // We don't fail the scenario if no redirection with message is found to avoid race condition false failures.
            return true;
        }

        // Wrapped in try & catch in case the redirection has already been executed.
        try {
            $content = $metarefresh->getAttribute('content');
        }
        catch (NoSuchElement $e) {
            return true;
        }
        catch (StaleElementReference $e) {
            return true;
        }

        // Getting the refresh time and the url if present.
        if (strstr($content, 'url') != false) {

            list($waittime, $url) = explode(';', $content);

            // Cleaning the URL value.
            $url = trim(substr($url, strpos($url, 'http')));

        }
        else {
            // Just wait then.
            $waittime = $content;
        }


        // Wait until the URL change is executed.
        if ($this->running_javascript()) {
            $this->getSession()->wait($waittime * 1000, false);

        }
        else if (!empty($url)) {
            // We redirect directly as we can not wait for an automatic redirection.
            $this->getSession()->getDriver()->getClient()->request('get', $url);

        }
        else {
            // Reload the page if no URL was provided.
            $this->getSession()->getDriver()->reload();
        }
    }

    /**
     * Switches to the specified iframe.
     *
     * @Given /^I switch to "(?P<iframe_name_string>(?:[^"]|\\")*)" iframe$/
     * @param string $iframename
     */
    public function switch_to_iframe($iframename) {

        // We spin to give time to the iframe to be loaded.
        // Using extended timeout as we don't know about which
        // kind of iframe will be loaded.
        $this->spin(
            function($context, $iframename) {
                $context->getSession()->switchToIFrame($iframename);

                // If no exception we are done.
                return true;
            },
            $iframename,
            self::EXTENDED_TIMEOUT
        );
    }

    /**
     * Switches to the main Moodle frame.
     *
     * @Given /^I switch to the main frame$/
     */
    public function switch_to_the_main_frame() {
        $this->getSession()->switchToIFrame();
    }

    /**
     * Switches to the specified window. Useful when interacting with popup windows.
     *
     * @Given /^I switch to "(?P<window_name_string>(?:[^"]|\\")*)" window$/
     * @param string $windowname
     */
    public function switch_to_window($windowname) {
        $this->getSession()->switchToWindow($windowname);
    }

    /**
     * Switches to the main Moodle window. Useful when you finish interacting with popup windows.
     *
     * @Given /^I switch to the main window$/
     */
    public function switch_to_the_main_window() {
        $this->getSession()->switchToWindow();
    }

    /**
     * Accepts the currently displayed alert dialog. This step does not work in all the browsers, consider it experimental.
     * @When /^I accept the alert popup$/
     */
    public function i_accept_alert_popup() {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * Confirm the currently displayed confirm dialog. This step does not work in all the browsers, consider it experimental.
     * @When /^I accept the confirm popup$/
     */
    public function i_accept_confirm_popup() {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * Cancel the currently displayed confirm dialog. This step does not work in all the browsers, consider it experimental.
     * @When /^I cancel the confirm popup$/
     */
    public function i_cancel_confirm_popup() {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }

    /**
     * Fill the text in prompt popup window. This step does not work in all the browsers, consider it experimental.
     * @When /^I fill in "(?P<text>(?:[^"]|\\")*)" for popup$/
     * @param string $text
     */
    public function i_fill_in_for_popup($text) {
        $this->getSession()->getDriver()->getWebDriverSession()->postAlert_text($text);
    }

    /**
     * Assert the text in popup window. This step does not work in all the browsers, consider it experimental.
     * @Then /^I should see "(?P<text>(?:[^"]|\\")*)" in popup$/
     * @param string $text
     * @return bool
     */
    public function i_should_see_in_popup($text) {
        return $text == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
    }

    /**
     * Waits X seconds. Required after an action that requires data from an AJAX request.
     *
     * @Given /^I wait "(?P<seconds_number>\d+)" seconds$/
     * @param int $seconds
     */
    public function i_wait_seconds($seconds) {

        if (!$this->running_javascript()) {
            throw new DriverException('Waits are disabled in scenarios without Javascript support');
        }

        $this->getSession()->wait($seconds * 1000, false);
    }

    /**
     * Waits until the page is completely loaded. This step is auto-executed after every step.
     *
     * @Given /^I wait until the page is ready$/
     */
    public function wait_until_the_page_is_ready() {

        if (!$this->running_javascript()) {
            throw new DriverException('Waits are disabled in scenarios without Javascript support');
        }

        $this->getSession()->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);
    }

    /**
     * Waits until the provided element selector exists in the DOM
     *
     * Using the protected method as this method will be usually
     * called by other methods which are not returning a set of
     * steps and performs the actions directly, so it would not
     * be executed if it returns another step.

     * @Given /^I wait until "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" exists$/
     * @param string $element
     * @param string $selector
     * @return void
     */
    public function wait_until_exists($element, $selectortype) {
        $this->ensure_element_exists($element, $selectortype);
    }

    /**
     * Waits until the provided element does not exist in the DOM
     *
     * Using the protected method as this method will be usually
     * called by other methods which are not returning a set of
     * steps and performs the actions directly, so it would not
     * be executed if it returns another step.

     * @Given /^I wait until "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" does not exist$/
     * @param string $element
     * @param string $selector
     * @return void
     */
    public function wait_until_does_not_exists($element, $selectortype) {
        $this->ensure_element_does_not_exist($element, $selectortype);
    }

    /**
     * Generic mouse over action. Mouse over a element of the specified type.
     *
     * @When /^I hover "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     */
    public function i_hover($element, $selectortype) {

        // Gets the node based on the requested selector type and locator.
        $node = $this->get_selected_node($selectortype, $element);
        $node->mouseOver();
    }

    /**
     * Click on the link or button.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)"$/
     * @param string $link_or_button we look for
     */
    public function i_click_on($link_or_button) {

        // Gets the node based on the requested selector type and locator.
        $node = $this->get_selected_node('link_or_button', $link_or_button);
        $this->ensure_node_is_visible($node);
//         if ($node->getTagName() === 'a') {
//             $path = $node->getAttribute('href');
//             $this->visitPath($path);
//         }
//         else {
            $node->click();
//         }
    }

    /**
     * Press the key.
     *
     * @When /^I press the key "(?P<key>(?:[^"]|\\")*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" field$/
     * @param string $key_press want to simulate pressing
     * @param string $nodeelement Element we focus on
     */
    public function i_key_press($key_press, $nodeelement) {

        if (strtolower($key_press) == 'enter' || strtolower($key_press) == 'return') {
            $key_press = 13;
        }

        $node = $this->get_selected_node('field', $nodeelement);

        $node->keyPress($key_press);
    }

    /**
     * Click on the link or button which is located inside the second element.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @param string $link_or_button we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     */
    public function i_click_on_in_the($link_or_button, $nodeelement, $nodeselectortype) {

        $node = $this->get_node_in_container('link_or_button', $link_or_button, $nodeselectortype, $nodeelement);
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Follow the link which is located inside the second element.
     *
     * @When /^I follow "(?P<link>(?:[^"]|\\")*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @param string $link we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     */
    public function i_follow_in_the($link, $nodeelement, $nodeselectortype) {

        $node = $this->get_node_in_container('link', $link, $nodeselectortype, $nodeelement);
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Press a button which is located inside the second element.
     *
     * @When /^I press "(?P<button>(?:[^"]|\\")*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @param string $button we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     */
    public function i_press_in_the($button, $nodeelement, $nodeselectortype) {

        $node = $this->get_node_in_container('button', $button, $nodeselectortype, $nodeelement);
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Click on the link or button inside a list/table row containing the specified text.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" in "(?P<row_text_string>(?:[^"]|\\")*)" row$/
     * @param string $link_or_button we look for
     * @param string $rowtext The list/table row text
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_row($link_or_button, $rowtext) {

        // The table row container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the row containing the text "' . $rowtext . '"');
        $xpath = "//div[(contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'listrow', ' '))" .
            " or contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'list-group-item', ' ')))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]" .
            "|" .
            "//tr[contains(normalize-space(.), " . $rowtextliteral . ")]";
        $rownode = $this->find('xpath', $xpath, $exception);

        // Looking for the element DOM node inside the specified row.
        list($selector, $locator) = $this->transform_selector('link_or_button', $link_or_button);
        $elementnode = $this->find($selector, $locator, false, $rownode);
        $this->ensure_node_is_visible($elementnode);
        $elementnode->click();
    }

    /**
     * Click a row containing the specified text.
     *
     * @When /^I click the row "(?P<row_text_string>(?:[^"]|\\")*)"$/
     * @param string $rowtext the row text
     * @throws ElementNotFoundException
     */
    public function i_click_row($rowtext) {

        // The table row container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the row containing the text "' . $rowtext . '"');
        $xpath = "//div[(contains(concat(' ', normalize-space(@class), ' '), ' listrow ')" .
                            " or contains(concat(' ', normalize-space(@class), ' '), ' list-group-item '))" .
                        " and contains(normalize-space(.), " . $rowtextliteral . ")]" .
                    "//a[contains(concat(' ', normalize-space(@class), ' '), ' outer-link ')]";
        $rownode = $this->find('xpath', $xpath, $exception);

        //$this->ensure_node_is_visible($rownode);
        //$rownode->click();
        // For some reasons, the Mink function click() and check() do not work
        // Using jQuery as a workaround
        $jscode = "jQuery(\"div.list-group-item:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ") a.outer-link\")[0].click();";
        $this->getSession()->executeScript($jscode);
    }

    /**
     * Click a matrix point by being given a column,row pair
     * NOTE: column and row start from number '0' so the first cell in a table is (0,0)
     *
     * @When I click on the matrix point :matrix_point
     * @param string $matrix_point a column,row value
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function i_click_matrix_point($matrix_point) {
        // Check that we have a valid matrix point
        $point = explode(',', $matrix_point);
        if (empty($point[0]) || empty($point[1]) ||
            !is_numeric($point[0]) || !is_numeric($point[1])) {
            throw new ExpectationException('"' . $matrix_point . '" is not valid. Needs to be like "3,5"', $this->getSession());
        }

        // The table container.
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'Unable to find the point "(' . $matrix_point . ')" in a table with class "tablematrix"');
        $xpath = "//table[(contains(concat(' ', normalize-space(@class), ' '), ' tablematrix '))]" .
                 "/tbody/tr[" . $point[1] . "]/td[" . $point[0] . "]";
        $pointnode = $this->find('xpath', $xpath, $exception);

        // For some reasons, the Mink function click() and check() do not work
        // Using jQuery as a workaround
        $jscode = "jQuery(\".tablematrix tr:eq('" . $point[1] . "') td:eq('" . $point[0] . "') span\").click();";
        $this->getSession()->executeScript($jscode);
    }

    /**
     * Click on the delete button inside a list/table row containing the specified text.
     *
     * @When /^I delete the "(?P<row_text_string>(?:[^"]|\\")*)" row$/
     * @param string $rowtext The list/table row text
     * @throws ElementNotFoundException
     */
    public function i_delete_the_row($rowtext) {

        // The table row container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the delete button in the row containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'list-group-item', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]//button[starts-with(@id, 'delete_')]" .
            "|" .
            "//tr[contains(normalize-space(.), " . $rowtextliteral . ")]//button[starts-with(@id, 'delete_') or starts-with(@name, 'files_filebrowser_delete')]";
        $deletenode = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($deletenode);
        $deletenode->press();
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * Drags and drops the specified element to the specified container. This step does not work in all the browsers, consider it experimental.
     *
     * The steps definitions calling this step as part of them should
     * manage the wait times by themselves as the times and when the
     * waits should be done depends on what is being dragged & dropper.
     *
     * @When /^I drag "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector1_string>(?:[^"]|\\")*)" and drop in "(?P<container_element_string>(?:[^"]|\\")*)" "(?P<selector2_string>(?:[^"]|\\")*)"$/
     * @param string $element
     * @param string $selectortype
     * @param string $containerelement
     * @param string $containerselectortype
     */
    public function i_drag_and_drop_in($element, $selectortype, $containerelement, $containerselectortype) {

        list($sourceselector, $sourcelocator) = $this->transform_selector($selectortype, $element);
        $sourcexpath = $this->getSession()->getSelectorsHandler()->selectorToXpath($sourceselector, $sourcelocator);

        list($containerselector, $containerlocator) = $this->transform_selector($containerselectortype, $containerelement);
        $destinationxpath = $this->getSession()->getSelectorsHandler()->selectorToXpath($containerselector, $containerlocator);

        $this->getSession()->getDriver()->dragTo($sourcexpath, $destinationxpath);
    }

    /**
     * Checks, that the specified element is visible. Only available in tests using Javascript.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>(?:[^"]|\\")*)" should be visible$/
     * @throws ElementNotFoundException
     * @throws ExpectationException
     * @throws DriverException
     * @param string $element
     * @param string $selectortype
     * @return void
     */
    public function should_be_visible($element, $selectortype) {

        if (!$this->running_javascript()) {
            throw new DriverException('Visible checks are disabled in scenarios without Javascript support');
        }

        $node = $this->get_selected_node($selectortype, $element);
        if (!$node->isVisible()) {
            throw new ExpectationException('"' . $element . '" "' . $selectortype . '" is not visible', $this->getSession());
        }
    }

    /**
     * Checks, that the specified element is not visible. Only available in tests using Javascript.
     *
     * As a "not" method, it's performance is not specially good as we should ensure that the element
     * have time to appear.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>(?:[^"]|\\")*)" should not be visible$/
     * @throws ElementNotFoundException
     * @throws ExpectationException
     * @param string $element
     * @param string $selectortype
     * @return void
     */
    public function should_not_be_visible($element, $selectortype) {

        try {
            $this->should_be_visible($element, $selectortype);
            throw new ExpectationException('"' . $element . '" "' . $selectortype . '" is visible', $this->getSession());
        }
        catch (ExpectationException $e) {
            // All as expected.
        }
    }

    /**
     * Checks, that the specified element is visible inside the specified container. Only available in tests using Javascript.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)" should be visible$/
     * @throws ElementNotFoundException
     * @throws DriverException
     * @throws ExpectationException
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     */
    public function in_the_should_be_visible($element, $selectortype, $nodeelement, $nodeselectortype) {

        if (!$this->running_javascript()) {
            throw new DriverException('Visible checks are disabled in scenarios without Javascript support');
        }

        $node = $this->get_node_in_container($selectortype, $element, $nodeselectortype, $nodeelement);
        if (!$node->isVisible()) {
            throw new ExpectationException(
                '"' . $element . '" "' . $selectortype . '" in the "' . $nodeelement . '" "' . $nodeselectortype . '" is not visible',
                $this->getSession()
            );
        }
    }

    /**
     * Checks, that the specified element is not visible inside the specified container. Only available in tests using Javascript.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)" should not be visible$/
     * @throws ElementNotFoundException
     * @throws ExpectationException
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     */
    public function in_the_should_not_be_visible($element, $selectortype, $nodeelement, $nodeselectortype) {

        try {
            $this->in_the_should_be_visible($element, $selectortype, $nodeelement, $nodeselectortype);
            throw new ExpectationException(
                '"' . $element . '" "' . $selectortype . '" in the "' . $nodeelement . '" "' . $nodeselectortype . '" is visible',
                $this->getSession()
            );
        }
        catch (ExpectationException $e) {
            // All as expected.
        }
    }

    /**
     * Checks, that the specified element contains the specified text. When running Javascript tests it also considers that texts may be hidden.
     *
     * @Then /^I should see "(?P<text_string>(?:[^"]|\\")*)" in the "(?P<element_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @throws ElementNotFoundException
     * @throws ExpectationException
     * @param string $text
     * @param string $element Element we look in.
     * @param string $selectortype The type of element where we are looking in.
     */
    public function assert_element_contains_text($text, $element, $selectortype) {

        // Getting the container where the text should be found.
        $container = $this->get_selected_node($selectortype, $element);

        // Looking for all the matching nodes without any other descendant matching the
        // same xpath (we are using contains(., ....).
        $xpathliteral = $this->escaper->escapeLiteral($text);
        $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
            "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

        // Wait until it finds the text inside the container, otherwise custom exception.
        try {
            $nodes = $this->find_all('xpath', $xpath, false, $container);
        }
        catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $text . '" text was not found in the "' . $element . '" element', $this->getSession());
        }

        // If we are not running javascript we have enough with the
        // element existing as we can't check if it is visible.
        if (!$this->running_javascript()) {
            return;
        }

        // We also check the element visibility when running JS tests.
        $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        return true;
                    }
                }

                throw new ExpectationException('"' . $args['text'] . '" text was found in the "' . $args['element'] . '" element but was not visible', $context->getSession());
            },
            array('nodes' => $nodes, 'text' => $text, 'element' => $element)
        );
    }

    /**
     * Checks, that the specified element does not contain the specified text. When running Javascript tests it also considers that texts may be hidden.
     *
     * @Then /^I should not see "(?P<text_string>(?:[^"]|\\")*)" in the "(?P<element_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @throws ElementNotFoundException
     * @throws ExpectationException
     * @param string $text
     * @param string $element Element we look in.
     * @param string $selectortype The type of element where we are looking in.
     */
    public function assert_element_not_contains_text($text, $element, $selectortype) {

        // Getting the container where the text should be found.
        $container = $this->get_selected_node($selectortype, $element);

        // Looking for all the matching nodes without any other descendant matching the
        // same xpath (we are using contains(., ....).
        $xpathliteral = $this->escaper->escapeLiteral($text);
        $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
            "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

        // We should wait a while to ensure that the page is not still loading elements.
        // Giving preference to the reliability of the results rather than to the performance.
        try {
            $nodes = $this->find_all('xpath', $xpath, false, $container);
        }
        catch (ElementNotFoundException $e) {
            // All ok.
            return;
        }

        // If we are not running javascript we have enough with the
        // element not being found as we can't check if it is visible.
        if (!$this->running_javascript()) {
            throw new ExpectationException('"' . $text . '" text was found in the "' . $element . '" element', $this->getSession());
        }

        // We need to ensure all the found nodes are hidden.
        $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        throw new ExpectationException('"' . $args['text'] . '" text was found in the "' . $args['element'] . '" element', $context->getSession());
                    }
                }

                // If all the found nodes are hidden we are happy.
                return true;
            },
            array('nodes' => $nodes, 'text' => $text, 'element' => $element)
        );
    }

    /**
     * Checks, that the first specified element appears before the second one.
     *
     * @Given /^"(?P<preceding_element_string>(?:[^"]|\\")*)" "(?P<selector1_string>(?:[^"]|\\")*)" should appear before "(?P<following_element_string>(?:[^"]|\\")*)" "(?P<selector2_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @param string $preelement The locator of the preceding element
     * @param string $preselectortype The locator of the preceding element
     * @param string $postelement The locator of the latest element
     * @param string $postselectortype The selector type of the latest element
     */
    public function should_appear_before($preelement, $preselectortype, $postelement, $postselectortype) {

        // We allow postselectortype as a non-text based selector.
        list($preselector, $prelocator) = $this->transform_selector($preselectortype, $preelement);
        list($postselector, $postlocator) = $this->transform_selector($postselectortype, $postelement);

        $prexpath = $this->find($preselector, $prelocator)->getXpath();
        $postxpath = $this->find($postselector, $postlocator)->getXpath();

        // Using following xpath axe to find it.
        $msg = '"'.$preelement.'" "'.$preselectortype.'" does not appear before "'.$postelement.'" "'.$postselectortype.'"';
        $xpath = $prexpath.'/following::*[contains(., '.$postxpath.')]';
        if (!$this->getSession()->getDriver()->find($xpath)) {
            throw new ExpectationException($msg, $this->getSession());
        }
    }

    /**
     * Checks, that the first specified element appears after the second one.
     *
     * @Given /^"(?P<following_element_string>(?:[^"]|\\")*)" "(?P<selector1_string>(?:[^"]|\\")*)" should appear after "(?P<preceding_element_string>(?:[^"]|\\")*)" "(?P<selector2_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @param string $postelement The locator of the latest element
     * @param string $postselectortype The selector type of the latest element
     * @param string $preelement The locator of the preceding element
     * @param string $preselectortype The locator of the preceding element
     */
    public function should_appear_after($postelement, $postselectortype, $preelement, $preselectortype) {

        // We allow postselectortype as a non-text based selector.
        list($postselector, $postlocator) = $this->transform_selector($postselectortype, $postelement);
        list($preselector, $prelocator) = $this->transform_selector($preselectortype, $preelement);

        $postxpath = $this->find($postselector, $postlocator)->getXpath();
        $prexpath = $this->find($preselector, $prelocator)->getXpath();

        // Using preceding xpath axe to find it.
        $msg = '"'.$postelement.'" "'.$postselectortype.'" does not appear after "'.$preelement.'" "'.$preselectortype.'"';
        $xpath = $postxpath.'/preceding::*[contains(., '.$prexpath.')]';
        if (!$this->getSession()->getDriver()->find($xpath)) {
            throw new ExpectationException($msg, $this->getSession());
        }
    }

    /**
     * Checks, that element of specified type is disabled.
     *
     * @Then /^the "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should be disabled$/
     * @throws ExpectationException Thrown by BehatBase::find
     * @param string $element Element we look in
     * @param string $selectortype The type of element where we are looking in.
     */
    public function the_element_should_be_disabled($element, $selectortype) {

        // Transforming from steps definitions selector/locator format to Mink format and getting the NodeElement.
        $node = $this->get_selected_node($selectortype, $element);

        if (!$node->hasAttribute('disabled')) {
            throw new ExpectationException('The element "' . $element . '" is not disabled', $this->getSession());
        }
    }

    /**
     * Checks, that element of specified type is enabled.
     *
     * @Then /^the "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should be enabled$/
     * @throws ExpectationException Thrown by BehatBase::find
     * @param string $element Element we look on
     * @param string $selectortype The type of where we look
     */
    public function the_element_should_be_enabled($element, $selectortype) {

        // Transforming from steps definitions selector/locator format to mink format and getting the NodeElement.
        $node = $this->get_selected_node($selectortype, $element);

        if ($node->hasAttribute('disabled')) {
            throw new ExpectationException('The element "' . $element . '" is not enabled', $this->getSession());
        }
    }

    /**
     * Checks the provided element and selector type are readonly on the current page.
     *
     * @Then /^the "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should be readonly$/
     * @throws ExpectationException Thrown by BehatBase::find
     * @param string $element Element we look in
     * @param string $selectortype The type of element where we are looking in.
     */
    public function the_element_should_be_readonly($element, $selectortype) {
        // Transforming from steps definitions selector/locator format to Mink format and getting the NodeElement.
        $node = $this->get_selected_node($selectortype, $element);

        if (!$node->hasAttribute('readonly')) {
            throw new ExpectationException('The element "' . $element . '" is not readonly', $this->getSession());
        }
    }

    /**
     * Checks the provided element and selector type are not readonly on the current page.
     *
     * @Then /^the "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should not be readonly$/
     * @throws ExpectationException Thrown by BehatBase::find
     * @param string $element Element we look in
     * @param string $selectortype The type of element where we are looking in.
     */
    public function the_element_should_not_be_readonly($element, $selectortype) {
        // Transforming from steps definitions selector/locator format to Mink format and getting the NodeElement.
        $node = $this->get_selected_node($selectortype, $element);

        if ($node->hasAttribute('readonly')) {
            throw new ExpectationException('The element "' . $element . '" is readonly', $this->getSession());
        }
    }

    /**
     * Checks the provided element and selector type exists in the current page.
     *
     * This step is for advanced users, use it if you don't find anything else suitable for what you need.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should exists$/
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $element The locator of the specified selector
     * @param string $selectortype The selector type
     */
    public function should_exists($element, $selectortype) {

        // Getting Mink selector and locator.
        list($selector, $locator) = $this->transform_selector($selectortype, $element);

        // Will throw an ElementNotFoundException if it does not exist.
        $this->find($selector, $locator);
    }

    /**
     * Checks that the provided element and selector type not exists in the current page.
     *
     * This step is for advanced users, use it if you don't find anything else suitable for what you need.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should not exists$/
     * @throws ExpectationException
     * @param string $element The locator of the specified selector
     * @param string $selectortype The selector type
     */
    public function should_not_exists($element, $selectortype) {

        try {
            $this->should_exists($element, $selectortype);
            throw new ExpectationException('The "' . $element . '" "' . $selectortype . '" exists in the current page', $this->getSession());
        }
        catch (ElementNotFoundException $e) {
            // It passes.
            return;
        }
    }

    /**
     * This step triggers the cron, through the web interface.
     *
     * It resets the "nextrun" on every cron task, so every cron task will run
     * every time this step is used.
     *
     * @Given /^I trigger (the )?cron$/
     */
    public function i_trigger_cron() {
        set_field('cron', 'nextrun', null);
        foreach(plugin_types() as $plugintype) {
            set_field($plugintype . '_cron', 'nextrun', null);
        }
        $this->getSession()->visit($this->locate_path('/lib/cron.php?urlsecret=' . urlencode(get_config('urlsecret'))));
    }

    /**
     * Checks that an element and selector type exists in another element and selector type on the current page.
     *
     * This step is for advanced users, use it if you don't find anything else suitable for what you need.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should exist in the "(?P<element2_string>(?:[^"]|\\")*)" "(?P<selector2_string>[^"]*)"$/
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $element The locator of the specified selector
     * @param string $selectortype The selector type
     * @param string $containerelement The container selector type
     * @param string $containerselectortype The container locator
     */
    public function should_exist_in_the($element, $selectortype, $containerelement, $containerselectortype) {
        // Get the container node.
        $containernode = $this->get_selected_node($containerselectortype, $containerelement);

        list($selector, $locator) = $this->transform_selector($selectortype, $element);

        // Specific exception giving info about where can't we find the element.
        $locatorexceptionmsg = $element . '" in the "' . $containerelement. '" "' . $containerselectortype. '"';
        $exception = new ElementNotFoundException($this->getSession(), $selectortype, null, $locatorexceptionmsg);

        // Looks for the requested node inside the container node.
        $this->find($selector, $locator, $exception, $containernode);
    }

    /**
     * Checks that an element and selector type does not exist in another element and selector type on the current page.
     *
     * This step is for advanced users, use it if you don't find anything else suitable for what you need.
     *
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should not exist in the "(?P<element2_string>(?:[^"]|\\")*)" "(?P<selector2_string>[^"]*)"$/
     * @throws ExpectationException
     * @param string $element The locator of the specified selector
     * @param string $selectortype The selector type
     * @param string $containerelement The container selector type
     * @param string $containerselectortype The container locator
     */
    public function should_not_exist_in_the($element, $selectortype, $containerelement, $containerselectortype) {
        try {
            $this->should_exist_in_the($element, $selectortype, $containerelement, $containerselectortype);
            throw new ExpectationException('The "' . $element . '" "' . $selectortype . '" exists in the "' .
                $containerelement . '" "' . $containerselectortype . '"', $this->getSession());
        }
        catch (ElementNotFoundException $e) {
            // It passes.
            return;
        }
    }

    /**
     * Visit a Mahara portfolio Page with the specified title
     *
     * @Given /^I go to portfolio page "([^"]*)"$/
     * @Given /^I go to view "([^"]*)"$/
     */
    public function i_go_to_view($title) {
        // Find the page's ID number
        $views = get_records_array('view', 'title', $title, '', 'id');
        if (!$views) {
            throw new Exception(sprintf('Invalid page title. No view found with title "%s".', $title));
        }
        if (count($views) > 1) {
            throw new Exception(sprintf('Invalid page title. More than one view with title "%s".', $title));
        }

        $view = reset($views);

        // success
        $this->visitPath("/view/view.php?id={$view->id}");
    }

    /**
     * Expand a collapsible section containing the specified text.
     *
     * @When /^I expand the section "(?P<text>(?:[^"]|\\")*)"$/
     * @param string $text The text in the section
     * @throws ElementNotFoundException
     */
    public function i_expand_section($text) {

        // Find the section heading link.
        $textliteral = $this->escaper->escapeLiteral($text);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the collapsed section heading containing the text "' . $text . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' collapsible-group ')]" .
                    "//a[contains(concat(' ', normalize-space(@data-toggle), ' '), ' collapse ')" .
                        " and contains(normalize-space(.), " . $textliteral . ")" .
                        " and contains(concat(' ', normalize-space(@class), ' '), ' collapsed ')]";
        $section_heading_link = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($section_heading_link);
        $section_heading_link->click();

    }

    /**
     * Unexpand a collapsible section containing the specified text.
     *
     * @When /^I unexpand the section "(?P<text>(?:[^"]|\\")*)"$/
     * @param string $text The text in the section
     * @throws ElementNotFoundException
     */
    public function i_unexpand_section($text) {

        // Find the section heading link.
        $textliteral = $this->escaper->escapeLiteral($text);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the uncollapsed section heading containing the text "' . $text . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' collapsible-group ')]" .
                    "//a[contains(concat(' ', normalize-space(@data-toggle), ' '), ' collapse ')" .
                        " and contains(normalize-space(.), " . $textliteral . ")" .
                        " and not(contains(concat(' ', normalize-space(@class), ' '), ' collapsed '))]";
        $section_heading_link = $this->find('xpath', $xpath, $exception);

        $this->ensure_node_is_visible($section_heading_link);
        $section_heading_link->click();

    }

    /**
     * Close the modal dialog.
     *
     * @When /^I close the dialog$/
     * @throws ElementNotFoundException
     */
    public function i_close_dialog() {

        // Find the dialog close button.
        $exception = new ElementNotFoundException($this->getSession(), 'dialog');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' modal-dialog ')]" .
                    "//button[contains(concat(' ', normalize-space(@class), ' '), ' close ')]";
        $dialogclosebuttons = $this->find_all('xpath', $xpath, $exception);

        foreach ($dialogclosebuttons as $closebutton) {
            if ($closebutton->isVisible()) {
                $closebutton->click();
                return;
            }
        }

    }

    /**
     * Close the config modal dialog.
     *
     * @When /^I close the config dialog$/
     * @throws ElementNotFoundException
     */
    public function i_close_config_dialog() {

        // Find the config dialog close button.
        $exception = new ElementNotFoundException($this->getSession(), 'dialog');
        $xpath = "//div[@id='configureblock']" .
                 "//div[contains(concat(' ', normalize-space(@class), ' '), ' modal-dialog ')]" .
                 "//button[contains(concat(' ', normalize-space(@class), ' '), ' close ')]";
        $closebutton = $this->find('xpath', $xpath, $exception);
        if ($closebutton->isVisible()) {
            $closebutton->click();
            $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
            return;
        }
    }

/**
 * Display the editting page
 *
 * @When /^I display the page$/
 *
 */
    public function i_display_page() {
        $this->getSession()->executeScript('jQuery("div.with-heading a:contains(\'Display page\')")[0].click();');
    }

/**
 * Jump to next page of a list (pagination)
 *
 * @When I jump to next page of the list :id
 *
 */
    public function i_jump_next_page_of_list($id) {
        $this->getSession()->executeScript('jQuery("div#' . $id . ' a:contains(\'Next page\')")[0].click();');
    }

/**
 * Jump to previous page of a list (pagination)
 *
 * @When I jump to previous page of the list :id
 *
 */
    public function i_jump_prev_page_of_list($id) {
        $this->getSession()->executeScript('jQuery("div#' . $id . ' a:contains(\'Previous page\')")[0].click();');
    }

/**
 * Jump to a page of a list (pagination)
 *
 * @When I jump to page :page of the list :id
 *
 */
    public function i_jump_page_of_list($page, $id) {
        $this->getSession()->executeScript('jQuery("div#' . $id . ' a:contains(\'' . $page . '\')")[0].click();');
    }

/**
 * Delete a Link and resource menu item
 *
 * @When I delete the link and resource menu item :item
 *
 */
    public function i_delete_link_resource_menu_item($item) {
        $this->getSession()->executeScript('jQuery("div#menuitemlist tr:contains(\'' . $item . '\') button:contains(\'Delete\')")[0].click();');
        usleep(10000);
        $this->i_accept_confirm_popup();
        $this->wait_until_the_page_is_ready();
    }

/**
 * Scroll element into view and align top of element with the top of the visible area.
 *
 * @When I scroll to the id :id
 *
 */
    public function i_scroll_into_view($id) {
        $function = <<<JS
          (function(){
              var elem = document.getElementById("$id");
              elem.scrollIntoView(true);
          })()
JS;
        try {
            $this->getSession()->executeScript($function);
        }
        catch(Exception $e) {
            throw new \Exception("scrollIntoView failed");
        }
    }

/**
 * Scroll element into view and align bottom of element with the bottom of the visible area.
 *
 * @When I scroll to the base of id :id
 *
 */
    public function i_scroll_into_view_base($id) {
        $function = <<<JS
          (function(){
              var elem = document.getElementById("$id");
              elem.scrollIntoView(false);
          })()
JS;
        try {
            $this->getSession()->executeScript($function);
        }
        catch(Exception $e) {
            throw new \Exception("scrollIntoView failed");
        }
    }

/**
 * Check if images exist in the block given its title
 *
 * @Then I should see images in the block :blocktitle
 *
 */
    public function i_should_see_images_block($blocktitle) {
        // Find the block.
        $blocktitleliteral = $this->escaper->escapeLiteral($blocktitle);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' column-content ')]" .
                     "/div[contains(@id,'blockinstance_')" .
                         " and contains(h3, " . $blocktitleliteral . ")]//img";
        // Wait until it finds the text inside the block title.
        try {
            $blockimages = $this->find_all('xpath', $xpath);
        }
        catch (ElementNotFoundException $e) {
            throw new ExpectationException('The block with title ' . $blocktitleliteral . ' was not found', $this->getSession());
        }

        // If we are not running javascript we have enough with the
        // element existing as we can't check if it is visible.
        if (!$this->running_javascript()) {
            return;
        }

        // We also check the element visibility when running JS tests.
        $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        return true;
                    }
                }

                throw new ExpectationException('The block with title ' . $args['text'] . ' was not visible', $context->getSession());
            },
            array('nodes' => $blockimages, 'text' => $blocktitleliteral)
        );
    }

/**
 * Check if images does not exist in the block given its title
 *
 * @Then I should not see images in the block :blocktitle
 *
 */
    public function i_should_not_see_images_block($blocktitle) {
        // Find the block.
        $blocktitleliteral = $this->escaper->escapeLiteral($blocktitle);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' column-content ')]" .
                     "/div[contains(@id,'blockinstance_')" .
                         " and contains(h3, " . $blocktitleliteral . ")]" .
                         "[count(descendant::img) = 0]";
        // Wait until it finds the text inside the block title.
        try {
            $blockimages = $this->find_all('xpath', $xpath);
        }
        catch (ElementNotFoundException $e) {
            throw new ExpectationException('The block with title ' . $blocktitleliteral . ' was not found', $this->getSession());
        }

        // If we are not running javascript we have enough with the
        // element existing as we can't check if it is visible.
        if (!$this->running_javascript()) {
            return;
        }

        // We also check the element visibility when running JS tests.
        $this->spin(
            function($context, $args) {

                foreach ($args['nodes'] as $node) {
                    if ($node->isVisible()) {
                        return true;
                    }
                }

                throw new ExpectationException('The block with title ' . $args['text'] . ' was not visible', $context->getSession());
            },
            array('nodes' => $blockimages, 'text' => $blocktitleliteral)
        );
    }

}
