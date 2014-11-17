<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Moodle Pty Ltd (http://moodle.com)
 * Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Test the different web service protocols.
 *
 * @author     Piers Harding
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package    web service
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
