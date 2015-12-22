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
 * Provides the core functionality for interacting with Mahara.
 *
 */
use Behat\Behat\Context\Context,
    Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Hook\HookDispatcher;

class BehatMaharaCoreContext extends MinkContext implements MaharaAwareInterface {

    /**
     * Test parameters.
     *
     * @var array
     */
    private $maharaParameters;

    /**
     * Event dispatcher object.
     *
     * @var \Behat\Testwork\Hook\HookDispatcher
     */
    protected $dispatcher;

    /**
     * Current authenticated user.
     *
     * A value of FALSE denotes an anonymous user.
     *
     * @var stdClass|bool
    */
    public $user = FALSE;

    /**
     * Keep track of all users that are created so they can easily be removed.
     *
     * @var array
     */
    protected $users = array();

    /**
     * {@inheritDoc}
     */
    public function setDispatcher(HookDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaharaParameters(array $parameters) {
        $this->maharaParameters = $parameters;
    }

}
