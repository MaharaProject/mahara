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
require_once(__DIR__ . '/properties.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\DriverException as DriverException,
    WebDriver\Exception\NoSuchElement as NoSuchElement,
    WebDriver\Exception\StaleElementReference as StaleElementReference,
    WebDriver\Exception\NoAlertOpenError;

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
            "login_login_username",
            $username
        );
        $this->getSession()->getPage()->fillField(
            "login_login_password",
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
        $this->visitPath("/?logout");
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
     * Switches to the newly opened tab/window. Useful when you do not know name of window/tab.
     *
     * @Given /^I switch to the new window$/
     */
    public function switch_to_the_new_window() {
        $windowNames = $this->getSession()->getWindowNames();
        if (count($windowNames) > 1) {
            $this->getSession()->switchToWindow(end($windowNames));
        }
        else {
            throw new Exception('Only one tab/window available.');
        }
    }

    /**
     * Switches to the main window. Useful when you finish interacting with other windows/tabs.
     *
     * @Given /^I switch to the main window$/
     */
    public function switch_to_the_main_window() {
        $windowNames = $this->getSession()->getWindowNames();
        $this->getSession()->switchToWindow($windowNames[0]);
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
     * Fill the wstoken text in field. This step finds the relevant token and fill in field.
     * @When /^I fill in the wstoken for "([^"]*)" owned by "([^"]*)"$/
     */
    public function i_fill_in_wstoken($service, $user) {
        $tokens = get_records_sql_array("SELECT token FROM {external_services} es
                                         JOIN {external_tokens} et ON et.externalserviceid = es.id
                                         JOIN {usr} u ON u.id = et.userid
                                         WHERE es.name = ?
                                         AND (u.username = ? OR CONCAT(u.firstname, ' ', u.lastname) = ?)", array($service, $user, $user));
        if (!$tokens) {
            throw new Exception(sprintf('Invalid token. No wstoken found for service "%s" owned by "%s".', $service, $user));
        }
        if (count($tokens) > 1) {
            throw new Exception(sprintf('Too many tokens. More than one wstoken exists for user "%s" for service "%s".', $user, $service));
        }
        $token = $tokens[0]->token;
        // success
        $this->getSession()->getPage()->fillField("wstoken", $token);
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
     * Assert the text is not in a popup window. This step does not work in all the browsers, consider it experimental.
     * @Then /^I should not see a popup$/
     * @return bool
     */
    public function i_should_not_see_a_popup() {
        try {
            $text = $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
            throw new Exception('Popup window found when none expected.');
        }
        catch (NoAlertOpenError $e) {
            return true;
        }
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
     * Click on the delete confirm link or button.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" delete button$/
     * @param string $link_or_button we look for
     */
    public function i_click_on_delete($link_or_button) {

        // Gets the node based on the requested selector type and locator.
        $node = $this->get_selected_node('link_or_button', $link_or_button);
        $this->ensure_node_is_visible($node);
        $node->click();
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
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
        // Note: keyPres does not work with all drivers
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
     * Check if the page contains the specified text within viewport
     *
     * @Then I should see :text in :element on the screen
     */
    public function i_see_in_viewport($text, $element) {
        $textliteral = $this->escaper->escapeLiteral($text);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the element "' . $element . '"');
        $xpath = "//" . $element . "[contains(normalize-space(.), " . $textliteral . ")]";
        $node = $this->find('xpath', $xpath, $exception);
        $this->ensure_node_is_visible($node);
        // now that we know it exists on the page and is not a 'hidden' element
        // we check if it is within the viewport
        $textliteraljs = $this->escapeDoubleQuotes($textliteral);
        $jscode = <<<EOF
(function isScrolledIntoView() {
    var elem = jQuery("$element:contains($textliteraljs)")[0];
    var docViewTop = jQuery(window).scrollTop();
    var docViewBottom = docViewTop + jQuery(window).height();
    var elemTop = jQuery(elem).offset().top;
    var elemBottom = elemTop + jQuery(elem).height();
    return (docViewBottom >= elemTop && docViewTop <= elemBottom);
})();
EOF;
        $result = $this->getSession()->evaluateScript("return $jscode");
        if (!$result) {
            throw new Exception("Element $element containing $text not within the viewport.");
        }
    }

    /**
     * Checks the list/table row containing the specified text.
     *
     * @Then I should see :text in the :rowtext row
     * @param string $text we look for
     * @param string $rowtext The list/table row text
     * @throws ElementNotFoundException
     */
    public function i_find_in_row($text, $rowtext) {

        // The table row container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the row containing the text "' . $rowtext . '"');
        $xpath = "//div[(contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'listrow', ' '))" .
            " or contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'list-group-item', ' ')))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]" .
            "|" .
            "//tr[contains(normalize-space(.), " . $rowtextliteral . ")]" .
            "|" .
            "//li[(contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'list-group-item', ' ')))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";

        $rownode = $this->find('xpath', $xpath, $exception);
        // Looking for the element DOM node inside the specified row.
        $elementnode = $this->find('named', array('content', $text));
        $this->ensure_node_is_visible($elementnode);
    }

    /**
     * Checks the list/table row does not contain the specified text.
     *
     * @Then I should not see :text in the :rowtext row
     * @param string $text we look for
     * @param string $rowtext The list/table row text
     * @throws ExpectationException
     */

    public function not_in_row($text, $rowtext) {
        // The table row container.
        try {
            $this->i_find_in_row($text, $rowtext);
            $exists = true;
        }
        catch(Exception $e) {
            $exists = false;
        }
        if ($exists) {
            throw new ExpectationException('"' . $args['text'] . '" text was found in the "' . $args['element'] . '" element', $context->getSession());
        }
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
            "//tr[contains(normalize-space(.), " . $rowtextliteral . ")]" .
            "|" .
            "//li[(contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'list-group-item', ' ')))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";

        $rownode = $this->find('xpath', $xpath, $exception);

        // Looking for the element DOM node inside the specified row.
        list($selector, $locator) = $this->transform_selector('link_or_button', $link_or_button);
        $elementnode = $this->find($selector, $locator, false, $rownode);
        $this->ensure_node_is_visible($elementnode);
        $elementnode->click();
    }

    /**
     * Click on a button in the modal dialog.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" in the dialog$/
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_dialog($link_or_button) {

        // Find the dialog button.
        $exception = new ElementNotFoundException($this->getSession(), 'dialog');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' modal-dialog ')]";
        $rownode = $this->find('xpath', $xpath, $exception);

        list($selector, $locator) = $this->transform_selector('link_or_button', $link_or_button);
        $elementnode = $this->find($selector, $locator, false, $rownode);
        $this->ensure_node_is_visible($elementnode);
        $elementnode->click();
    }

    /**
     * Click on the bottom right menu elipsis inside a list card containing the specified text.
     *
     * @When /^I click on "(?P<row_text_string>(?:[^"]|\\")*)" card menu$/
     * @param string $rowtext The list/table row text
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_card($rowtext) {

        // The card container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the card containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'card', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";
        $rownode = $this->find('xpath', $xpath, $exception);

        // Click on the elipsis button for the card
        $jscode = "jQuery(\"div.card h3:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ")\").siblings('.card-footer').find('.page-controls .moremenu')[0].click();";
        $this->getSession()->executeScript($jscode);
    }

    /**
     * Click on the bottom right collection menu inside a list card containing the specified text.
     *
     * @When /^I click on "(?P<row_text_string>(?:[^"]|\\")*)" card collection$/
     * @param string $rowtext The list/table row text
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_card_collection_box($rowtext) {

        // The card container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the card containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'card', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";
        $rownode = $this->find('xpath', $xpath, $exception);

        // Click on the collection box for the card
        $jscode = "jQuery(\"div.card h3:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ")\").siblings('.card-footer').find('.collection-list')[0].click();";
        $this->getSession()->executeScript($jscode);
    }

    /**
     * Click on the link or button inside a card menu containing the specified text.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" in "(?P<row_text_string>(?:[^"]|\\")*)" card menu$/
     * @param string $link_or_button we look for
     * @param string $rowtext The card menu text
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_card_menu($link_or_button, $rowtext) {

        // The card container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the card containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'card', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";
        $rownode = $this->find('xpath', $xpath, $exception);

        // Click on the elipsis button for the card
        $jscode = "jQuery(\"div.card h3:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ")\").siblings('.card-footer').find('.page-controls a:contains(" . $this->escapeDoubleQuotes($link_or_button) . ")')[0].click();";
        $this->getSession()->executeScript($jscode);
    }

    /**
     * Click on the link or button inside a card access menu containing the specified text.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" in "(?P<row_text_string>(?:[^"]|\\")*)" card access menu$/
     * @param string $link_or_button we look for
     * @param string $rowtext The card menu text
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_card_access_menu($link_or_button, $rowtext) {

        // The card container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the card access containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'card', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";
        $rownode = $this->find('xpath', $xpath, $exception);

        // Click on the elipsis button for the card
        $jscode = "jQuery(\"div.card h3:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ")\").siblings('.card-footer').find('.page-access a:contains(" . $this->escapeDoubleQuotes($link_or_button) . ")')[0].click();";
        $this->getSession()->executeScript($jscode);
    }

    /**
     * Click on the link or button inside a card collection list containing the specified text.
     *
     * @When /^I click on "(?P<link_or_button>(?:[^"]|\\")*)" in "(?P<row_text_string>(?:[^"]|\\")*)" card collection$/
     * @param string $link_or_button we look for
     * @param string $rowtext The card menu text
     * @throws ElementNotFoundException
     */
    public function i_click_on_in_card_collection_menu($link_or_button, $rowtext) {

        // The card container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the card containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'card', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]";
        $rownode = $this->find('xpath', $xpath, $exception);

        // Click on the elipsis button for the card
        $jscode = "jQuery(\"div.card h3:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ")\").siblings('.card-footer').find(\"a:contains(" . $this->escapeDoubleQuotes($link_or_button) . ")\")[0].click();";
        $this->getSession()->executeScript($jscode);
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
     * Click a card header containing the specified text.
     *
     * @When /^I click the card "(?P<row_text_string>(?:[^"]|\\")*)"$/
     * @param string $rowtext the card heading text
     * @throws ElementNotFoundException
     */
    public function i_click_card($rowtext) {

        // The card container.
        $rowtextliteral = $this->escaper->escapeLiteral($rowtext);
        $exception = new ElementNotFoundException($this->getSession(), 'text', null, 'the card containing the text "' . $rowtext . '"');
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'card', ' '))" .
            " and contains(normalize-space(.), " . $rowtextliteral . ")]" .
            "//a[contains(concat(' ', normalize-space(@class), ' '), ' title-link ')]";
        $rownode = $this->find('xpath', $xpath, $exception);

        $jscode = "jQuery(\"div.card h3 a.title-link:contains(" . $this->escapeDoubleQuotes($rowtextliteral) . ")\")[0].click();";
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
        $jscode = "jQuery(\".tablematrix tr:eq('" . $point[1] . "') td:eq('" . $point[0] . "') span\").trigger('click');";
        $this->getSession()->executeScript($jscode);
    }

    /**
    * @Given /^I click on the "(?P<element>(?:[^"]|\\")*)" "(?P<tselectortype>[^"]*)"$/
    *
    * calls function in parent class
    *
    * @param string $element - thing to look for
    * @param string $selectortype - e.g. css/xpath
    */
    public function i_click_on_element($element, $selectortype='css_element') {
        parent::i_click_on_element($element, $selectortype);
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
    * Generic function to take any step that needs to look up the properties
    * file with the syntax "in the <> property" and gets the css locator
    * from the properties.php file.
    * Then uses a switch to get the correct function.
    *
    * @Then /^I (?P<step_funct>.*) "(?P<text_string>(?:[^"]|\\")*)" in the "(?P<property_string>(?:[^"]|\\")*)" property$/
    * @Then /^I (?P<step_funct>.*) "(?P<text_string>(?:[^"]|\\")*)" in the
    * "(?P<property_string>(?:[^"]|\\")*)" property in "(?P<location_string>(?:[^"]|\\")*)"$/
    * @param string $step_funct
    * @param string $text
    * @param string $property
    * @param string $location
    */
    public function get_property_call_funct($step_funct, $text, $property, $location = null) {
        $css_locator = get_property($property, $location);
        // get_property returns null if locator not found
        if (!$css_locator) {
            throw new ExpectationException('"A property called "' . $property . '" was not found in the properties.php file. Check that file or try passing a css locator directly"',
            $this->getSession());
        }

        else {
            $step_funct = $this->switch_action($step_funct);
            // switch covers steps in BehatGeneral that pass a css_locator
            $this->$step_funct($text, $css_locator[0], $css_locator[1]);
        }
    }

    /**
    * @Given I click on the :property property
    * @param string $property
    */
    public function click_on_property($property) {
        $property = get_property($property);
        $this->i_click_on_element($property[0], $property[1]);
    }

    /**
     * @Then /^I should see "(?P<text_string>(?:[^"]|\\")*)" in the
     * "(?P<property_string>(?:[^"]|\\")*)" property in "(?P<location_string>(?:[^"]|\\")*)"$/
     * @param string $text
     * @param string $property
     * @param string $location
     */
    public function should_see_property_in_location($text, $property, $location) {

        $css_locator = get_property_in_location($property, $location);
        if (!$css_locator) {
            throw new ExpectationException('"A property called $property was not found in the properties.php file. Check that file or try passing a css locator directly"',
            $this->getSession());
        }
        else {
            $this->assert_element_contains_text($text, $css_locator[0], $css_locator[1]);
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
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should exist$/
     * @throws ElementNotFoundException Thrown by BehatBase::find
     * @param string $element The locator of the specified selector
     * @param string $selectortype The selector type
     */
    public function should_exist($element, $selectortype) {

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
     * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" should not exist$/
     * @throws ExpectationException
     * @param string $element The locator of the specified selector
     * @param string $selectortype The selector type
     */
    public function should_not_exist($element, $selectortype) {

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
        $this->getSession()->visit($this->locate_path('/lib/cron.php?behattrigger=1&urlsecret=' . urlencode(get_config('urlsecret'))));
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
     * Visit a Mahara Profile Page with the specified owner
     *
     * @Given /^I go to the profile page of "([^"]*)"$/
     */
    public function i_go_to_profile_view($user) {
        // Find the page's ID number
        $views = get_records_sql_array("SELECT v.id FROM {view} v
                                       JOIN {usr} u ON u.id = v.owner
                                       WHERE (u.username = ? OR CONCAT(u.firstname, ' ', u.lastname) = ?)
                                       AND v.type = ?", array($user, $user, 'profile'));
        if (!$views) {
            throw new Exception(sprintf('Invalid user name. No profile view found for "%s".', $user));
        }
        if (count($views) > 1) {
            throw new Exception(sprintf('Invalid useer name. More than one profile view found for "%s".', $user));
        }

        $view = reset($views);

        // success
        $this->visitPath("/view/view.php?id={$view->id}");
    }

    /**
     * Visit a Mahara group Page with the specified Group name
     *
     * @Given /^I go to group "([^"]*)"$/
     */
    public function i_go_to_group($name) {
        // Find the page's ID number
        //get_records_array takes: ($table, $field='', $value='', $sort='', $fields='*', $limitfrom='', $limitnum='')
        $groups = get_records_array('group', 'name', $name, '', 'id');
        if (!$groups) {
            throw new Exception(sprintf('Invalid group name. No group found with name "%s".', $name));
        }
        if (count($groups) > 1) {
            throw new Exception(sprintf('Invalid group name. More than one group with name "%s".', $name));
        }

        $group = reset($groups);

        // success
        $this->visitPath("/group/view.php?id={$group->id}");
    }

    /**
     * Visit a Mahara extension configuration page with the specified name
     *
     * @Given I go to the :plugin plugin :name configuration
     * @Given I go to the :plugin plugin :name configuration :type type
     */
    public function i_go_to_extension_configuration($plugin, $name, $type=null) {
        $path = "/admin/extensions/pluginconfig.php?plugintype=" . $plugin . "&pluginname=" . $name;
        if ($type) {
            $path .= "&type=" . $type;
        }
        $this->visitPath($path);
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
 * Scroll to top of page
 *
 * @When I scroll to the top
 *
 */
    public function i_scroll_to_top() {
        $function = <<<JS
          (function(){
              window.scrollTo(0,0);
              return 1;
          })()
JS;
        try {
            $this->getSession()->wait(5000, $function);
        }
        catch(Exception $e) {
            throw new \Exception("scrollToTop failed");
        }
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
              return 1;
          })()
JS;
        try {
            $this->getSession()->wait(5000, $function);
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
              return 1;
          })()
JS;
        try {
            $this->getSession()->wait(5000, $function);
        }
        catch(Exception $e) {
            throw new \Exception("scrollIntoView failed");
        }
    }

/**
 * Scroll element into view and align top of element with the center of the visible area.
 *
 * @When I scroll to the center of id :id
 *
 */
    public function i_scroll_into_view_center($id) {
        $function = <<<JS
          (function(){
              var elem = document.getElementById("$id");
              var elementRect = elem.getBoundingClientRect();
              var absoluteElementTop = elementRect.top + window.pageYOffset;
              var middle = absoluteElementTop - (window.innerHeight / 2);
              window.scrollTo(0, middle);
              return 1;
          })()
JS;
        try {
            $this->getSession()->wait(5000, $function);
        }
        catch(Exception $e) {
            throw new \Exception("scrollIntoView failed");
        }
    }


/**
 * Check if images exist in the block given its title
 *
 * @Then I should see images within the block :blocktitle
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
     * Check if image with title exists on the page
     *
     * @Then I should see image :imagetitle on the page
     *
     */
    public function i_should_see_image_on_page($imagetitle) {
        // Find the image.
        $imagetitleliteral = $this->escaper->escapeLiteral($imagetitle);
        $exception = new ElementNotFoundException($this->getSession(), 'image');
        $xpath = "//img[contains(concat(' ', normalize-space(@alt), ' '), " . $imagetitleliteral . ")]";
        $image = $this->find('xpath', $xpath, $exception);
        if (!$image->isVisible()) {
            throw new ExpectationException('The image with alt ' . $imagetitleliteral . ' was not visible', $this->getSession());
        }
    }

/**
 * Check if text exist in the block given its title
 *
 * @Then I should see :text in the block :blocktitle
 *
 */
    public function i_should_see_text_in_block($text, $blocktitle) {
        // Find the block.
        $blocktitleliteral = $this->escaper->escapeLiteral($blocktitle);
        $textliteral = $this->escaper->escapeLiteral($text);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' column-content ')]" .
                     "/div[contains(@id,'blockinstance_')" .
                         " and contains(h3, " . $blocktitleliteral . ")]" .
                     "//div[contains(normalize-space(.), " . $textliteral . ")]";
        // Wait until it finds the text inside the block title.
        try {
            $blocktext = $this->find_all('xpath', $xpath);
        }
        catch (ElementNotFoundException $e) {
            throw new ExpectationException('The block with title ' . $blocktitleliteral . ' containing ' . $textliteral . ' was not found', $this->getSession());
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
            array('nodes' => $blocktext, 'text' => $blocktitleliteral)
        );
    }

/**
 * Check if images does not exist in the block given its title
 *
 * @Then I should not see images within the block :blocktitle
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

/**
 * Check if text exist in the block given its title
 *
 * @Then I should not see :text in the block :blocktitle
 *
 */
    public function i_should_not_see_text_in_block($text, $blocktitle) {
        // Find the block.
        $blocktitleliteral = $this->escaper->escapeLiteral($blocktitle);
        $textliteral = $this->escaper->escapeLiteral($text);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' column-content ')]" .
                     "/div[contains(@id,'blockinstance_')" .
                         " and contains(h3, " . $blocktitleliteral . ")]" .
                     "//div[count(descendant::*[contains(normalize-space(.), " . $textliteral . ")]) = 0]";
        // Wait until it finds the text inside the block title.
        try {
            $blocktext = $this->find_all('xpath', $xpath);
        }
        catch (ElementNotFoundException $e) {
            throw new ExpectationException('The block with title ' . $blocktitleliteral . ' containing ' . $textliteral . ' was found', $this->getSession());
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
            array('nodes' => $blocktext, 'text' => $blocktitleliteral)
        );
    }

    /**
      * Pauses the scenario until the user presses a key. Useful when debugging a scenario locally
      * but not meant for automated runs.
      *
      * @Then /^(?:|I )insert breakpoint$/
      */

    public function i_insert_breakpoint() {

         fwrite(STDOUT, "\n\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m\n");
         while (fgets(STDIN, 1024) == '') {
         }

         fwrite(STDOUT, "\033[u");
         return;
     }

     /**
     * Echos a line to the console to indicate where the test has reached in a scenario.
     * For debugging tests without using a breakpoint. Ok for automated runs.
     *
     * @Then /^(?:\I )echo the line "([^"]*)"$/
     */

     public function i_echo_the_line($text){

        fwrite(STDOUT, "\n\033[93m DEBUG: $text\033[0m\n\n");
      }

      /**
      * Takes a date in a format strtotime() can take and looks for it
      * in the specified css element. You can pass a date format as a string
      * defined in langconfig.php or directly as a php date() format.
      *
      * @Then I should see the date :date in the :element element with the format :format
      */
    public function i_should_see_date($date, $element, $format = null) {
      if (string_exists($format, 'langconfig')) {
        $date = format_date(strtotime($date), $format);
      }
      else {
        $date = date($format, strtotime($date));
      }
      $this->assertSession()->elementTextContains('css', $element, $date);
    }

    /**
    * Takes a date in a format strtotime() can take and adds it to a field
    * in the specified css element. You can pass a date format as a string
    * defined in langconfig.php or directly as a php date() format.
    *
    * @Then I fill in :element with :date date
    * @Then I fill in :element with :date date in the format :format
    */
    public function i_fill_in_date($date, $element, $format = null) {
      if (string_exists($format, 'langconfig')) {
        $date = format_date(strtotime($date), $format);
      }
      else if ($format == null) {
      }
      else {
        $date = date($format, strtotime($date));
      }
      $this->getSession()->getPage()->fillField($element, $date);
    }

    /**
     * Mimic the clicking of the unsubscribe link in the email
     * by supplying what user and page it was for
     *
     * @Then I unsubscribe from :page owned by :user
     *
     */
    public function i_unsubscribe_via_link($page, $user) {
        $tokens = get_records_sql_array("SELECT unsubscribetoken FROM {usr_watchlist_view} wv
                                         JOIN {usr} u ON u.id = wv.usr
                                         JOIN {view} v ON v.id = wv.view
                                         WHERE (u.username = ? OR CONCAT(u.firstname, ' ', u.lastname) = ?)
                                         AND v.title = ?", array($user, $user, $page));
        if (!$tokens) {
            throw new Exception(sprintf('Invalid token. No unsubscribetoken found for page "%s" owned by "%s".', $page, $user));
        }
        if (count($tokens) > 1) {
            throw new Exception(sprintf('Too many tokens. More than one unsubscribetoken exists for user "%s" for page "%s".', $user, $page));
        }
        $token = $tokens[0]->unsubscribetoken;
        // Go to the unsubscribe page
        $this->visitPath("/view/unsubscribe.php?a=watchlist&t={$token}");
    }

    /**
    * Switch to assign the secondary function to be called by a
    * generic primary function
    *
    */
    private function switch_action($action) {
        switch ($action) {
            case "click on":
                $funct = "i_click_on_in_the";
                break;
            case "follow":
                $funct = "i_follow_in_the";
                break;
            case "press":
                $funct = "i_press_in_the";
                break;
            case "should see":
                $funct = "assert_element_contains_text";
                break;
            case "should not see":
                $funct = "assert_element_not_contains_text";
                break;
        }
        return $funct;
    }

    /**
     * Allows interaction with comments in a list using text
     * contained in the comment, as the id tags are dynamic.
     *
     * @Then /^I (?P<action>.*) "(?P<element>(?:[^"]|\\")*)" in the "(?P<text>(?:[^"]|\\")*)" comment$/
     *
     * @param string $action the first element of the step used to call
     *              a secondary function.
     * @param string $element part of the comment id to interact with
     * @param string $text part of the comment text
     */
    public function i_interact_comment($action, $element, $text) {
        $xpath = "//*[@id=\"feedbacktable\"]/*/div[contains(normalize-space(.), '$text')]";
        $action = $this->switch_action($action);
        $this->$action($element, $xpath, "xpath_element");
    }

}
