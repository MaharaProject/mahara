<?php
/**
 * @package    mahara
 * @subpackage test/core
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2012, Petr Skoda {@link http://skodak.org}
 */

/**
 * Tests lock to prevent concurrent executions of the same test suite
 *
 */

require_once(dirname(__DIR__) . '/lib.php');

class TestLock {

    /**
     * @var array Array of resource used for prevention of parallel test execution
     */
    protected static $lockhandles = array();

    /**
     * Prevent parallel test execution - this can not work in Mahara because we modify database and dataroot.
     *
     * Note: do not call manually!
     *
     * @internal
     * @static
     * @param    string  $framework Test framework can be 'phpunit' or 'behat'
     * @return   void
     */
    public static function acquire($framework) {
        global $CFG;

        $datarootpath = $CFG->{$framework . '_dataroot'} . '/' . $framework;
        $lockfile = $datarootpath . '/lock';
        if (!file_exists($datarootpath)) {
            // Dataroot not initialised yet.
            return;
        }
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, 'This file prevents concurrent execution of Mahara ' . $framework . ' tests');
            testing_fix_file_permissions($lockfile);
        }
        if (self::$lockhandles[$framework] = fopen($lockfile, 'r')) {
            $wouldblock = null;
            $locked = flock(self::$lockhandles[$framework], (LOCK_EX | LOCK_NB), $wouldblock);
            if (!$locked) {
                if ($wouldblock) {
                    echo "Waiting for other test execution to complete...\n";
                }
                $locked = flock(self::$lockhandles[$framework], LOCK_EX);
            }
            if (!$locked) {
                fclose(self::$lockhandles[$framework]);
                self::$lockhandles[$framework] = null;
            }
        }
        register_shutdown_function(array('TestLock', 'release'), $framework);
    }

    /**
     * Note: do not call manually!
     * @internal
     * @static
     * @param    string  $framework phpunit|behat
     * @return   void
     */
    public static function release($framework) {
        if (self::$lockhandles[$framework]) {
            flock(self::$lockhandles[$framework], LOCK_UN);
            fclose(self::$lockhandles[$framework]);
            self::$lockhandles[$framework] = null;
        }
    }

}
