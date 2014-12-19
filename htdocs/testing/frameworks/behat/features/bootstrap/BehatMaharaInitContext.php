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
 * Mahara init context class.
 *
 */
require_once(dirname(dirname(__DIR__)) . '/classes/BehatHooks.php');
require_once(dirname(dirname(__DIR__)) . '/classes/BehatGeneral.php');
require_once(dirname(dirname(__DIR__)) . '/classes/BehatNavigation.php');
require_once(dirname(dirname(__DIR__)) . '/classes/BehatForms.php');
require_once(dirname(dirname(__DIR__)) . '/classes/BehatDataGenerators.php');

use Behat\Behat\Context\BehatContext,
    Behat\MinkExtension\Context\MinkContext;

class BehatMaharaInitContext extends MinkContext {

    /**
     * Initializes subcontexts
     *
     * @param  array $parameters context parameters (set them up through behat.yml)
     * @return void
     */
    public function __construct(array $parameters) {
        // Initialize must have subcontexts
        $this->useContext('BehatHooks', new BehatHooks($parameters));
        $this->useContext('BehatGeneral', new BehatGeneral($parameters));
        $this->useContext('BehatNavigation', new BehatNavigation($parameters));
        $this->useContext('BehatForms', new BehatForms($parameters));
        $this->useContext('BehatDataGenerators', new BehatDataGenerators($parameters));
        $this->useContext('mahara', new MaharaContext($parameters));
    }

}
