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

use Behat\Mink\Selector\Xpath\Escaper;

/**
 * Mahara-specific selectors.
 */

class BehatSelectors {

    /**
     * @var Allowed types when using text selectors arguments.
     */
    protected static $allowedtextselectors = array(
        'dialogue' => 'dialogue',
        'block' => 'block',
        'region' => 'region',
        'table_row' => 'table_row',
        'table' => 'table',
        'fieldset' => 'fieldset',
        'css_element' => 'css_element',
        'xpath_element' => 'xpath_element'
    );

    /**
     * @var Allowed types when using selector arguments.
     */
    protected static $allowedselectors = array(
        'dialogue' => 'dialogue',
        'block' => 'block',
        'region' => 'region',
        'table_row' => 'table_row',
        'link' => 'link',
        'button' => 'button',
        'link_or_button' => 'link_or_button',
        'select' => 'select',
        'checkbox' => 'checkbox',
        'radio' => 'radio',
        'file' => 'file',
        'filemanager' => 'filemanager',
        'optgroup' => 'optgroup',
        'option' => 'option',
        'table' => 'table',
        'field' => 'field',
        'fieldset' => 'fieldset',
        'text' => 'text',
        'css_element' => 'css_element',
        'xpath_element' => 'xpath_element'
    );

    /**
     * Behat by default comes with XPath, CSS and named selectors,
     * named selectors are a mapping between names (like button) and
     * xpaths that represents that names and includes a placeholder that
     * will be replaced by the locator. These are mahara's own xpaths.
     *
     * @var XPaths for mahara elements.
     */
    protected static $maharaselectors = array(
        'text' => <<<XPATH
//*[contains(., %locator%)][count(./descendant::*[contains(., %locator%)]) = 0]
XPATH
        , 'dialogue' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' mahara-dialogue ') and
    normalize-space(descendant::div[
        contains(concat(' ', normalize-space(@class), ' '), ' mahara-dialogue-hd ')
        ]) = %locator%] |
//div[contains(concat(' ', normalize-space(@class), ' '), ' yui-dialog ') and
    normalize-space(descendant::div[@class='hd']) = %locator%]
XPATH
        , 'block' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), concat(' ', %locator%, ' '))] | //div[contains(concat(' ', normalize-space(@class), ' '), ' block ')]/descendant::h2[normalize-space(.) = %locator%]/ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' block ')]
XPATH
        , 'region' => <<<XPATH
//*[self::div | self::section | self::aside | self::header | self::footer][./@id = %locator%]
XPATH
        , 'table_row' => <<<XPATH
.//tr[contains(normalize-space(.), %locator%)]
XPATH
        , 'filemanager' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' ffilemanager ')]/descendant::input[@id = //label[contains(normalize-space(string(.)), %locator%)]/@for]
XPATH
);

    /**
     * Returns the behat selector and locator for a given mahara selector and locator
     *
     * @param string $selectortype The mahara selector type, which includes mahara selectors
     * @param string $element The locator we look for in that kind of selector
     * @param Session $session The Mink opened session
     * @return array Contains the selector and the locator expected by Mink.
     */
    public static function get_behat_selector($selectortype, $element, Behat\Mink\Session $session) {

        $element = self::fix_step_argument($element);
        $escaper = new Escaper();
        // CSS and XPath selectors locator is one single argument.
        if ($selectortype == 'css_element' || $selectortype == 'xpath_element') {
            $selector = str_replace('_element', '', $selectortype);
            $locator = $element;
        }
        else {
            // Named selectors uses arrays as locators including the type of named selector.
            $locator = array($selectortype, $escaper->escapeLiteral($element));
            $selector = 'named';
        }

        return array($selector, $locator);
    }

    /**
     * Adds mahara selectors as behat named selectors.
     *
     * @param Session $session The mink session
     * @return void
     */
    public static function register_mahara_selectors(Behat\Mink\Session $session) {

        foreach (self::get_mahara_selectors() as $name => $xpath) {
            $session->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath($name, $xpath);
        }
    }

    /**
     * Allowed selectors getter.
     *
     * @return array
     */
    public static function get_allowed_selectors() {
        return self::$allowedselectors;
    }

    /**
     * Allowed text selectors getter.
     *
     * @return array
     */
    public static function get_allowed_text_selectors() {
        return self::$allowedtextselectors;
    }

    /**
     * Mahara selectors attribute accessor.
     *
     * @return array
     */
    protected static function get_mahara_selectors() {
        return self::$maharaselectors;
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    protected static function fix_step_argument($argument){
        return str_replace('\\"', '"', $argument);
    }
}
