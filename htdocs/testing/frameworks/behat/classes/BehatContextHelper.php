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
 * Helper to initialise behat contexts from mahara code.
 *
 */


use Behat\Mink\Session as Session,
    Behat\Mink\Mink as Mink;

class BehatContextHelper {

    /**
     * List of already initialized contexts.
     *
     * @var array
     */
    protected static $contexts = array();

    /**
     * @var Mink.
     */
    protected static $mink = false;

    /**
     * Sets the browser session.
     *
     * @param Session $session
     * @return void
     */
    public static function set_session(Session $session) {

        // Set mink to be able to init a context.
        self::$mink = new Mink(array('mink' => $session));
        self::$mink->setDefaultSessionName('mink');
    }

    /**
     * Gets the required context.
     *
     * Getting a context you get access to all the steps
     * that uses direct API calls; steps returning step chains
     * can not be executed like this.
     *
     * @throws coding_exception
     * @param string $classname Context identifier (the class name).
     * @return BehatBase
     */
    public static function get($classname) {

        if (!self::init_context($classname)) {
            throw Exception('The required "' . $classname . '" class does not exist');
        }

        return self::$contexts[$classname];
    }

    /**
     * Initializes the required context.
     *
     * @throws Exception
     * @param string $classname
     * @return bool
     */
    protected static function init_context($classname) {

        if (!empty(self::$contexts[$classname])) {
            return true;
        }

        if (!class_exists($classname)) {
            return false;
        }

        $instance = new $classname();
        $instance->setMink(self::$mink);

        self::$contexts[$classname] = $instance;

        return true;
    }

}
