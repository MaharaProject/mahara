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
        $xpath = "//div[@id='main-nav']" .
            "/ul[@id='nav']" .
            "/li" .
            "/span/a[normalize-space(.)=" . $nodetextliteral ."]";
        $node = $this->find('xpath', $xpath, $exception);

        return $node;
    }

    /**
     * Helper function to get sub menu item node.
     *
     * @throws ExpectationException if node not found.
     * @param string $menuitemtext the title of menu item e.g. "Profile", "Pages".
     * @return NodeElement
     */
    protected function get_sub_menu_item_node($menuitemtext) {

        // Avoid problems with quotes.
        $nodetextliteral = $this->escaper->escapeLiteral($menuitemtext);
        $exception = new ExpectationException('The menu item "' . $menuitemtext . ' not found or invisible in "', $this->getSession());
        $xpath = "//div[@id='sub-nav']" .
            "/ul" .
            "/li" .
            "/span/a[normalize-space(.)=" . $nodetextliteral ."]";
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
        $menuitemnode->click();
    }

    /**
     * Choose a sub menu item in a main menu item
     *
     * @Given /^I choose "(?P<menu_item>(?:[^"]|\\")*)" in "(?P<mainmenu_item>(?:[^"]|\\")*)"$/
     */
    public function i_choose_submenu($menuitem, $mainmenuitem) {
        $menuitemnode = $this->get_main_menu_item_node($mainmenuitem);
        $menuitemnode->click();
        $this->getSession()->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);
        $menuitemnode = $this->get_sub_menu_item_node($menuitem);
        $menuitemnode->click();
    }

}
