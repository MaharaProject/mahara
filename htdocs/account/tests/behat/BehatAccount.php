<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from mahara Behat, 2013 David Monllaó
 *
 */
require_once(dirname(dirname(dirname(__DIR__))) . '/testing/frameworks/behat/classes/BehatBase.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Account steps definitions.
 *
 */
class BehatAccount extends BehatBase {

    /**
     * Sets the specified account settings. A table with | Setting label | value | is expected.
     *
     * @Given /^the following account settings have set:$/
     * @param TableNode $table
     */
    public function i_set_the_following_account_settings_values(TableNode $table) {
        // @TODO implement this step definition
    }
}