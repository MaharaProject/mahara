<?php
/**
 * Test the different web service protocols.
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

# require_once 'PHPUnit/Framework.php';

/**
 * phpunit test class that loads all the tests in the current directory
 * and then runs them.
 */
class RunAllTests {

    /**
     * Load all tests
     */
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Mahara Web Services TestSuite');
        $tests = preg_grep('/RunAllTests|TestBase/', glob(dirname(__FILE__) . '/*.php'), PREG_GREP_INVERT);
        foreach ($tests as $test) {
            error_log('adding test: ' . $test);
            $test = basename($test);
            $parts = explode('.', $test);
            error_log("Setting up: $parts[0]\n");
            require_once($test);
            $suite->addTestSuite($parts[0]);
        }
        return $suite;
    }
}
