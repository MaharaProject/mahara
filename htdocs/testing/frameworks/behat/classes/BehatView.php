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
 * Steps definitions for Mahara views(pages)
 *
 */

require_once(__DIR__ . '/BehatBase.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Mahara view/page interactive step definitions
 *
 */
class BehatView extends BehatBase {

    /**
     * Helper function to get the block div.
     *
     * @throws ElementNotFoundException if node not found.
     * @param string $blocktitle the title of the block e.g. "About me", "My portfolios".
     * @return NodeElement
     */
    protected function get_block($blocktitle) {

        // Avoid problems with quotes.
        $nodetextliteral = $this->escaper->escapeLiteral($blocktitle);
        $exception = new ElementNotFoundException($this->getSession(),
                'The block "' . $blocktitle . '"');
        $xpath = "//div[@id='column-container']"
                    . "//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'blockinstance', ' '))]"
                        . "//span[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'blockinstance-header', ' '))]"
                            . "[normalize-space(.)=" . $nodetextliteral . "]";
        $titlenode = $this->find('xpath', $xpath, $exception);
        $blocknode = $titlenode->getParent()->getParent();

        return $blocknode;
    }

    /**
     * Configure a block
     *
     * @throws ElementNotFoundException if not found.
     * @Given /^I configure the block "(?P<block_title>(?:[^"]|\\")*)"$/
     */
    public function i_configure_block($blocktitle) {
        $block = $this->get_block($blocktitle);
        $exception = new ElementNotFoundException($this->getSession(),
                'The configuration span of block "' . $blocktitle . '"');
        $blockconfigbutton = $this->find('xpath',
                "//span[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'blockinstance-controls', ' '))]"
                    . "//button[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'configurebutton', ' '))]",
                $exception,
                $block
        );
        $blockconfigbutton->press();
    }

    /**
     * Delete a block
     *
     * @throws ElementNotFoundException if not found.
     * @Given /^I delete the block "(?P<block_title>(?:[^"]|\\")*)"$/
     */
    public function i_delete_block($blocktitle) {
        $block = $this->get_block($blocktitle);
        $exception = new ElementNotFoundException($this->getSession(),
                'The configuration span of block "' . $blocktitle . '"');
        $blockconfigbutton = $this->find('xpath',
                "//span[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'blockinstance-controls', ' '))]"
                    . "//button[contains(concat(' ', normalize-space(@class), ' '), concat(' ', 'deletebutton', ' '))]",
                $exception,
                $block
        );
        $blockconfigbutton->press();
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }
}
