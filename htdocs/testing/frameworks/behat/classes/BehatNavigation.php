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
 * Navigation steps definitions.
 *
 */

require_once(__DIR__ . '/BehatBase.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\DriverException as DriverException,
    WebDriver\Exception\NoSuchElement as NoSuchElement,
    WebDriver\Exception\StaleElementReference as StaleElementReference,
    Behat\Behat\Context\Step\Given as Given,
    Behat\Behat\Context\Step\When as When,
    Behat\Behat\Context\Step\Then as Then
    ;

/**
 * Navigation steps definitions for mahara
 *
 */
class BehatNavigation extends BehatBase {

    /**
     * Helper function to get main menu item node.
     *
     * @throws ExpectationException if node not found.
     * @param string $menuitemtext the title of menu item e.g. "Dashboard", "Content".
     * @return NodeElement
     */
    protected function get_main_menu_item_node($menuitemtext) {

        // Avoid problems with quotes.
        $nodetextliteral = $this->escaper->escapeLiteral($menuitemtext);
        $exception = new ExpectationException('The menu item "' . $menuitemtext . ' not found or invisible in "', $this->getSession());
        $xpath = "//nav[@id='main-nav']" .
            "//a[normalize-space(.)=" . $nodetextliteral ."]";
        $node = $this->find('xpath', $xpath, $exception);

        return $node;
    }

    /**
     * Helper function to get sub menu item node.
     *
     * @throws ExpectationException if node not found or invisible.
     * @param string $submenuitemtext the title of submenu item e.g. "Profile", "Pages".
     * @param string $menuitemtext the title of menu item e.g. "Content", "Portfolio".
     * @return NodeElement
     */
    protected function get_sub_menu_item_node($submenuitemtext, $menuitemtext) {

        // Avoid problems with quotes.
        $submenuitemtextliteral = $this->escaper->escapeLiteral($submenuitemtext);
        $menuitemtextliteral = $this->escaper->escapeLiteral($menuitemtext);
        $exception = new ExpectationException('The sub menu item "' . $menuitemtext . ' not found or invisible in "', $this->getSession());
        $xpath = "//nav[@id='main-nav']" .
            "//li[contains(normalize-space(.), " . $menuitemtextliteral .")]" .
            "//a[normalize-space(.)=" . $submenuitemtextliteral ."]";
        $node = $this->find('xpath', $xpath, $exception);

        return $node;
    }

    /**
     * Choose a main menu item
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)"$/
     */
    public function i_choose_menu($menuitem) {
        $menuitemnode = $this->get_main_menu_item_node($menuitem);
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a sub menu item in a main menu item
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" in "(?P<mainmenu_item>(?:[^"]|\\")*)"$/
     */
    public function i_choose_submenu($menuitem, $mainmenuitem) {
        $menuitemnode = $this->get_sub_menu_item_node($menuitem, $mainmenuitem);
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Expands the selected node that matches the text.
     *
     * @Given /^I expand "(?P<element_string>(?:[^"]|\\")*)" node$/
     */
    public function i_expand_node($element) {
        if (!$this->running_javascript()) {
            return true;
        }
        $node = $this->get_selected_node('text', $element);
        // Check if the node is a link.
        if (strtolower($node->getTagName()) === 'a') {
            // We just want to expand the node, we don't want to follow it.
            $node = $node->getParent();
        }
        $node->click();
    }

    /**
     * Expands the selected node that matches the text which is located inside the second element.
     *
     * @Given /^I expand "(?P<element_string>(?:[^"]|\\")*)" node in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @param string $element we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     */
    public function i_expand_node_in_the($element, $nodeelement, $nodeselectortype) {
        if (!$this->running_javascript()) {
            return true;
        }
        $node = $this->get_node_in_container('text', $element, $nodeselectortype, $nodeelement);
        // Check if the node is a link.
        if (strtolower($node->getTagName()) === 'a') {
            // We just want to expand the node, we don't want to follow it.
            $node = $node->getParent();
        }
        $node->click();
    }
}
