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
     * @param string $menu the type of menu to look in e.g. "admin", "user".
     * @param string $byid find the menu item based on an id e.g. "mail" for "? unread" messages link to inbox
     * @return NodeElement
     */
    protected function get_main_menu_item_node($menuitemtext, $menu, $byid = false) {

        // Avoid problems with quotes.
        $nodetextliteral = $this->escaper->escapeLiteral($menuitemtext);
        $exception = new ExpectationException('The menu item ' . ($byid ? 'with id ' : '') . '"' . $menuitemtext . '" not found or invisible in "' . $menu . '"', $this->getSession());
        if ($byid) {
            $xpath = "//nav/div[@id='" . $menu . "']" .
              "//a[@id='" . $menuitemtext . "']";
        }
        else {
            $xpath = "//nav/div[@id='" . $menu . "']" .
              "//a[normalize-space(.)=" . $nodetextliteral ."]";
        }
        $node = $this->find('xpath', $xpath, $exception);

        return $node;
    }

    /**
     * Helper function to get sub menu item node.
     *
     * @throws ExpectationException if node not found or invisible.
     * @param string $submenuitemtext the title of submenu item e.g. "Profile", "Pages".
     * @param string $menuitemtext the title of menu item e.g. "Content", "Portfolio".
     * @param string $menu the type of menu to look in e.g. "admin", "user".
     * @return NodeElement
     */
    protected function get_sub_menu_item_node($submenuitemtext, $menuitemtext, $menu) {

        // Avoid problems with quotes.
        $submenuitemtextliteral = $this->escaper->escapeLiteral($submenuitemtext);
        $menuitemtextliteral = $this->escaper->escapeLiteral($menuitemtext);
        $exception = new ExpectationException('The sub menu item "' . $menuitemtext . '" not found or invisible in "' . $menu . '"', $this->getSession());

        $xpath = "//nav/div[@id='" . $menu . "']" .
            "/ul/li[contains(normalize-space(.), " . $menuitemtextliteral .")]" .
            "//li//a[normalize-space(.)=" . $submenuitemtextliteral ."]";
        $node = $this->find('xpath', $xpath, $exception);

        return $node;
    }

    /**
     * Choose inbox from menu
     *
     * @Given /^I choose inbox$/
     */
    public function i_choose_inbox() {
        $exception = new ExpectationException('The menu item inbox not found or invisible', $this->getSession());
        $xpath = "//a[@id='nav-inbox']";
        $inboxnode = $this->find('xpath', $xpath, $exception);
        $inboxnode->click();
    }

    /**
     * Choose a main menu item from Main menu
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" from main menu$/
     */
    public function i_choose_menu_main($menuitem) {
        $menuitemnode = $this->get_main_menu_item_node($menuitem, 'main-nav');
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a main menu item from Admin menu
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" from administration menu$/
     */
    public function i_choose_menu_admin($menuitem) {
        $menuitemnode = $this->get_main_menu_item_node($menuitem, 'main-nav-admin');
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a main menu item from user menu
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" from user menu$/
     */
    public function i_choose_menu_user($menuitem) {
        $menuitemnode = $this->get_main_menu_item_node($menuitem, 'main-nav-user');
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a main menu item from user menu by id
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" from user menu by id$/
     */
    public function i_choose_menu_user_by_id($menuitem) {
        $menuitemnode = $this->get_main_menu_item_node($menuitem, 'main-nav-user', true);
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a sub menu item in admnistration menu item
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" in "(?P<mainmenu_item>(?:[^"]|\\")*)" from administration menu$/
     */
    public function i_choose_submenu_admin($menuitem, $mainmenuitem) {
        $menuitemnode = $this->get_sub_menu_item_node($menuitem, $mainmenuitem, 'main-nav-admin');
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a sub menu item in user menu item
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" in "(?P<mainmenu_item>(?:[^"]|\\")*)" from user menu$/
     */
    public function i_choose_submenu_user($menuitem, $mainmenuitem) {
        $menuitemnode = $this->get_sub_menu_item_node($menuitem, $mainmenuitem, 'main-nav-user');
        $path = $menuitemnode->getAttribute('href');
        $this->visitPath($path);
    }

    /**
     * Choose a sub menu item in a main menu item
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" in "(?P<mainmenu_item>(?:[^"]|\\")*)" from main menu$/
     */
    public function i_choose_submenu_main($menuitem, $mainmenuitem) {
        $menuitemnode = $this->get_sub_menu_item_node($menuitem, $mainmenuitem, 'main-nav');
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
        // Check if the node is wrapped in an inner div
        if ($node->find('css', '.collapse-inline')) {
            // We just want to expand the parent node as this doesn't align top left to the outer-link a link.
            $node = $node->getParent();
        }

        $node->click();
    }

    /**
     * Collapse the selected node that matches the text.
     *
     * @Given /^I collapse "(?P<element_string>(?:[^"]|\\")*)" node$/
     */
    public function i_collapse_node($element) {
        $this->i_expand_node($element);
    }

    /**
     * Collapse the selected node that matches the text which is located inside the second element.
     *
     * @Given /^I collapse "(?P<element_string>(?:[^"]|\\")*)" node in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @Given I collapse :element node in the :property property
     */
    public function i_collapse_node_in_the($element = null, $nodeelement = null, $nodeselectortype = null, $property = null) {
        $this->i_expand_node_in_the($element, $nodeelement, $nodeselectortype, $property);
    }

    /**
     * Expands the selected node that matches the text which is located inside the second element.
     *
     * @Given /^I expand "(?P<element_string>(?:[^"]|\\")*)" node in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)"$/
     * @Given I expand :element node in the :property property
     * @param string $element we look for
     * @param string $nodeelement Element we look in
     * @param string $nodeselectortype The type of selector where we look in
     * @param string $property we look for
     */
    public function i_expand_node_in_the($element = null, $nodeelement = null, $nodeselectortype = null, $property = null) {
        if (!$this->running_javascript()) {
            return true;
        }
        if ($property) {
          $css_locator = get_property($property);
          if (!$css_locator) {
                   throw new ExpectationException('"A property called $property was not found in the properties.php file. Check that file or try passing a css locator directly"',
                   $this->getSession());
          }
          $nodeelement = $css_locator[0];
          $nodeselectortype = $css_locator[1];
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
