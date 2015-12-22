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

/**
 * Behat accepts hooks after and before each
 * suite, feature, scenario and step.
 *
 * This methods are used by Behat CLI command.
 *
 */


require_once(__DIR__ . '/BehatBase.php');

use Behat\Behat\Event\SuiteEvent as SuiteEvent,
    Behat\Behat\Event\FeatureEvent as FeatureEvent,
    Behat\Behat\Event\ScenarioEvent as ScenarioEvent,
    Behat\Behat\Event\StepEvent as StepEvent,
    Behat\Mink\Exception\DriverException as DriverException,
    WebDriver\Exception\NoSuchWindow as NoSuchWindow,
    WebDriver\Exception\UnexpectedAlertOpen as UnexpectedAlertOpen,
    WebDriver\Exception\UnknownError as UnknownError,
    WebDriver\Exception\CurlExec as CurlExec,
    WebDriver\Exception\NoAlertOpenError as NoAlertOpenError;

use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
/**
 * Hooks to the behat process.
 *
 * Implement hooks after and before each
 * suite, feature, scenario and step for Mahara
 *
 * Throws generic Exception because they are captured by Behat.
 *
 */
class BehatHooks extends BehatBase {

    /**
     * @var For actions that should only run once.
     */
    protected static $initprocessesfinished = false;

    /**
     * Some exceptions can only be caught in a before or after step hook,
     * they can not be thrown there as they will provoke a framework level
     * failure, but we can store them here to fail the step in i_look_for_exceptions()
     * which result will be parsed by the framework as the last step result.
     *
     * @var Null or the exception last step throw in the before or after hook.
     */
    protected static $currentstepexception = null;

    /**
     * If we are saving any kind of dump on failure we should use the same parent dir during a run.
     *
     * @var The parent dir name
     */
    protected static $faildumpdirname = false;

    /**
     * Make sure the test site is installed and enabled for behat tests.
     *
     * @static
     * @throws Exception
     * @BeforeSuite
     */
    public static function before_suite($event) {
        global $CFG, $db, $SESSION, $USER, $THEME;

        // Defined only when the behat CLI command is running, the mahara init setup process will
        // read this value and switch to $CFG->behat_dataroot and $CFG->behat_dbprefix instead of
        // the normal site.
        define('BEHAT_UTIL', 1);

        define('INTERNAL', 1);
        define('CLI', 1);

        // With BEHAT_TEST we will be using $CFG->behat_* instead of $CFG->dataroot, $CFG->dbprefix and $CFG->wwwroot.
        require_once(dirname(dirname(dirname(dirname(__DIR__)))) . '/init.php');

        // Now that we are in Mahara env.
        require_once('upgrade.php');
        require_once('file.php');
        require_once(dirname(dirname(dirname(__DIR__))) . '/classes/TestLock.php');
        require_once(__DIR__ . '/util.php');

        // Initialize and enable the test site if possible
        $statuscode = BehatTestingUtil::get_test_env_status();
        switch ($statuscode) {
            case BEHAT_MAHARA_EXITCODE_OUTOFDATEDB:
                BehatTestingUtil::drop_site();
            case BEHAT_MAHARA_EXITCODE_NOTINSTALLED:
                BehatTestingUtil::install_site();
            case BEHAT_MAHARA_EXITCODE_NOTENABLED:
                BehatTestingUtil::start_test_mode();
            case 0:
                break;
            default:
                throw new Exception($statuscode.'The test site is not ready to test.
    Please run php ' . dirname(dirname(dirname(dirname(__DIR__)))) . 'testing/frameworks/behat/cli/init.php to initialize the test site');;
            break;
        }

        if (!empty($CFG->behat_faildump_path) && !is_writable($CFG->behat_faildump_path)) {
            throw new Exception('You set $CFG->behat_faildump_path to a non-writable directory');
        }
    }

    /**
     * Clean test database and dataroot and disable the test environment.
     *
     * @static
     * @throws Exception
     * @AfterSuite
     */
    public static function after_suite($event) {
        global $CFG, $db, $SESSION, $USER, $THEME;

        // Check if the test environment is ready: dataroot, database, server
        if (!defined('BEHAT_TEST')) {
            throw new Exception('The test site is not enabled for behat testing');
        }

        //BehatTestingUtil::drop_site();
        BehatTestingUtil::stop_test_mode();
    }

    /**
     * Resets the test environment.
     *
     * @throws Exception If here we are not using the test database it should be because of a coding error
     * @BeforeScenario
     */
    public function before_scenario($event) {
        global $CFG;

         // Check if the test environment is ready: dataroot, database, server
        if (!defined('BEHAT_TEST')) {
            throw new Exception('The test site is not enabled for behat testing');
        }

        // Check if the browser is running and supports javascript
        $moreinfo = 'More info in ' . BehatCommand::DOCS_URL . '#Running_tests';
        $driverexceptionmsg = 'Selenium server is not running, you need to start it to run tests that involve Javascript. ' . $moreinfo;
        try {
            $session = $this->getSession();
        }
        catch (CurlExec $e) {
            // Exception thrown by WebDriver, so only @javascript tests will be caugth; in
            throw new Exception($driverexceptionmsg);
        }
        catch (DriverException $e) {
            throw new Exception($driverexceptionmsg);
        }
        catch (UnknownError $e) {
            // Generic 'I have no idea' Selenium error. Custom exception to provide more feedback about possible solutions.
            throw new Exception($e);
        }

        // Register the named selectors for mahara
        if (self::is_first_scenario()) {
            BehatSelectors::register_mahara_selectors($session);
            BehatContextHelper::set_session($session);
            // Reset the browser
            $session->restart();
            // Run all test with medium (1024x768) screen size, to avoid responsive problems.
            $this->resize_window('medium');
        }

        // Reset $SESSION.
        $_SESSION = array();
        $SESSION = new stdClass();
        $_SESSION['SESSION'] =& $SESSION;

        BehatTestingUtil::reset_database();
        BehatTestingUtil::reset_dataroot();

        // Reset the nasty strings list used during the last test.
        //NastyStrings::reset_used_strings();

        // Set current user is admin

        // Start always in the the homepage.
        try {
            // Let's be conservative as we never know when new upstream issues will affect us.
            $session->visit($this->locate_path('/'));
        }
        catch (UnknownError $e) {
            throw new Exception($e);
        }

        // Checking that the root path is a mahara test site.
        if (!self::$initprocessesfinished) {
            $notestsiteexception = new Exception('The base URL (' . $CFG->wwwroot . ') is not a behat test site, ' .
                'ensure you started the built-in web server in the correct directory or your web server is correctly started and set up');
            $this->find("xpath", "//head/child::title[contains(., '" . BehatTestingUtil::BEHATSITENAME . "')]", $notestsiteexception);

            self::$initprocessesfinished = true;
        }
    }

    /**
     * Wait for JS to complete before beginning interacting with the DOM.
     *
     * Executed only when running against a real browser. We wrap it
     * all in a try & catch to forward the exception to i_look_for_exceptions
     * so the exception will be at scenario level, which causes a failure, by
     * default would be at framework level, which will stop the execution of
     * the run.
     *
     * @BeforeStep
     */
//     public function before_step(BeforeStepScope $scope) {

//         if ($this->running_javascript()) {
//             try {
//                 $this->wait_for_pending_js();
//                 self::$currentstepexception = null;
//             }
//             catch (Exception $e) {
//                 self::$currentstepexception = $e;
//             }
//         }
//     }

    /**
     * Wait for JS to complete after finishing the step.
     *
     * With this we ensure that there are not AJAX calls
     * still in progress.
     *
     * Executed only when running against a real browser. We wrap it
     * all in a try & catch to forward the exception to i_look_for_exceptions
     * so the exception will be at scenario level, which causes a failure, by
     * default would be at framework level, which will stop the execution of
     * the run.
     *
     * Take screenshot if the step failed
     *
     * This includes creating an HTML dump of the content if there was a failure.
     *
     * @AfterStep
     */
    public function after_step(AfterStepScope $scope) {
        global $CFG;

        if ($this->running_javascript()) {
//            && in_array($scope->getStep()->getKeywordType(), array('Given', 'When'))) {
            try {
                $this->wait_for_pending_js();
                self::$currentstepexception = null;
            }
            catch (UnexpectedAlertOpen $e) {
                self::$currentstepexception = $e;

                // Accepting the alert so the framework can continue properly running
                // the following scenarios. Some browsers already closes the alert, so
                // wrapping in a try & catch.
                try {
                    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
                }
                catch (Exception $e) {
                    // Catching the generic one as we never know how drivers reacts here.
                }
            }
            catch (Exception $e) {
                self::$currentstepexception = $e;
            }
        }
        if (!empty($CFG->behat_faildump_path) &&
                $scope->getTestResult()->getResultCode() === 99) {
            $this->take_contentdump($scope);
        }
    }

    /**
     * Getter for self::$faildumpdirname
     *
     * @return string
     */
    protected function get_run_faildump_dir() {
        return self::$faildumpdirname;
    }

    /**
     * Take screenshot when a step fails.
     *
     * @throws Exception
     * @param AfterStepScope $scope
     */
    protected function take_screenshot(AfterStepScope $scope) {
        // Goutte can't save screenshots.
        if (!$this->running_javascript()) {
            return false;
        }

        list ($dir, $filename) = $this->get_faildump_filename($scope, 'png');
        $this->saveScreenshot($filename, $dir);
    }

    /**
     * Take a dump of the page content when a step fails.
     *
     * @throws Exception
     * @param AfterStepScope $scope
     */
    protected function take_contentdump(AfterStepScope $scope) {
        list ($dir, $filename) = $this->get_faildump_filename($scope, 'html');

        $fh = fopen($dir . DIRECTORY_SEPARATOR . $filename, 'w');
        fwrite($fh, $this->getSession()->getPage()->getContent());
        fclose($fh);
    }

    /**
     * Determine the full pathname to store a failure-related dump.
     *
     * This is used for content such as the DOM, and screenshots.
     *
     * @param AfterStepScope $scope
     * @param String $filetype The file suffix to use. Limited to 4 chars.
     */
    protected function get_faildump_filename(AfterStepScope $scope, $filetype) {
        global $CFG;

        // All the contentdumps should be in the same parent dir.
        if (!$faildumpdir = self::get_run_faildump_dir()) {
            $faildumpdir = self::$faildumpdirname = date('Ymd_His');

            $dir = $CFG->behat_faildump_path . DIRECTORY_SEPARATOR . $faildumpdir;

            if (!is_dir($dir) && !mkdir($dir, $CFG->directorypermissions, true)) {
                // It shouldn't, we already checked that the directory is writable.
                throw new Exception('No directories can be created inside $CFG->behat_faildump_path, check the directory permissions.');
            }
        }
        else {
            // We will always need to know the full path.
            $dir = $CFG->behat_faildump_path . DIRECTORY_SEPARATOR . $faildumpdir;
        }

        // The scenario title + the failed step text.
        // We want a i-am-the-scenario-title_i-am-the-failed-step.$filetype format.
        $filename = $scope->getStep()->getParent()->getTitle() . '_' . $scope->getStep()->getText();
        $filename = preg_replace('/([^a-zA-Z0-9\_]+)/', '-', $filename);

        // File name limited to 255 characters. Leaving 4 chars for the file
        // extension as we allow .png for images and .html for DOM contents.
        $filename = substr($filename, 0, 250) . '.' . $filetype;

        return array($dir, $filename);
    }

    /**
     * Internal step definition to find exceptions, debugging() messages and PHP debug messages.
     *
     * Part of BehatHooks class as is part of the testing framework, is auto-executed
     * after each step so no features will splicitly use it.
     *
     * @Given /^I look for exceptions$/
     * @throw Exception Unknown type, depending on what we caught in the hook or basic \Exception.
     */
    public function i_look_for_exceptions() {

        // If the step already failed in a hook throw the exception.
        if (!is_null(self::$currentstepexception)) {
            throw self::$currentstepexception;
        }

        // Wrap in try in case we were interacting with a closed window.
        try {

            // Exceptions.
            $exceptionsxpath = "//div[@data-rel='fatalerror']";
            // Debugging messages.
            $debuggingxpath = "//div[@data-rel='debugging']";
            // PHP debug messages.
            $phperrorxpath = "//div[@data-rel='phpdebugmessage']";
            // Any other backtrace.
            $othersxpath = "(//*[contains(., ': call to ')])[1]";

            $xpaths = array($exceptionsxpath, $debuggingxpath, $phperrorxpath, $othersxpath);
            $joinedxpath = implode(' | ', $xpaths);

            // Joined xpath expression. Most of the time there will be no exceptions, so this pre-check
            // is faster than to send the 4 xpath queries for each step.
            if (!$this->getSession()->getDriver()->find($joinedxpath)) {
                return;
            }

            // Exceptions.
            if ($errormsg = $this->getSession()->getPage()->find('xpath', $exceptionsxpath)) {

                // Getting the debugging info and the backtrace.
                $errorinfoboxes = $this->getSession()->getPage()->findAll('css', 'div.alert-error');
                // If errorinfoboxes is empty, try find notifytiny (original) class.
                if (empty($errorinfoboxes)) {
                    $errorinfoboxes = $this->getSession()->getPage()->findAll('css', 'div.notifytiny');
                }
                $errorinfo = $this->get_debug_text($errorinfoboxes[0]->getHtml()) . "\n" .
                    $this->get_debug_text($errorinfoboxes[1]->getHtml());

                $msg = "mahara exception: " . $errormsg->getText() . "\n" . $errorinfo;
                throw new \Exception(html_entity_decode($msg));
            }

            // Debugging messages.
            if ($debuggingmessages = $this->getSession()->getPage()->findAll('xpath', $debuggingxpath)) {
                $msgs = array();
                foreach ($debuggingmessages as $debuggingmessage) {
                    $msgs[] = $this->get_debug_text($debuggingmessage->getHtml());
                }
                $msg = "debugging() message/s found:\n" . implode("\n", $msgs);
                throw new \Exception(html_entity_decode($msg));
            }

            // PHP debug messages.
            if ($phpmessages = $this->getSession()->getPage()->findAll('xpath', $phperrorxpath)) {

                $msgs = array();
                foreach ($phpmessages as $phpmessage) {
                    $msgs[] = $this->get_debug_text($phpmessage->getHtml());
                }
                $msg = "PHP debug message/s found:\n" . implode("\n", $msgs);
                throw new \Exception(html_entity_decode($msg));
            }

            // Any other backtrace.
            // First looking through xpath as it is faster than get and parse the whole page contents,
            // we get the contents and look for matches once we found something to suspect that there is a backtrace.
            if ($this->getSession()->getDriver()->find($othersxpath)) {
                $backtracespattern = '/(line [0-9]* of [^:]*: call to [\->&;:a-zA-Z_\x7f-\xff][\->&;:a-zA-Z0-9_\x7f-\xff]*)/';
                if (preg_match_all($backtracespattern, $this->getSession()->getPage()->getContent(), $backtraces)) {
                    $msgs = array();
                    foreach ($backtraces[0] as $backtrace) {
                        $msgs[] = $backtrace . '()';
                    }
                    $msg = "Other backtraces found:\n" . implode("\n", $msgs);
                    throw new \Exception(htmlentities($msg));
                }
            }

        }
        catch (NoSuchWindow $e) {
            // If we were interacting with a popup window it will not exists after closing it.
        }
    }

    /**
     * Converts HTML tags to line breaks to display the info in CLI
     *
     * @param string $html
     * @return string
     */
    protected function get_debug_text($html) {

        // Replacing HTML tags for new lines and keeping only the text.
        $notags = preg_replace('/<+\s*\/*\s*([A-Z][A-Z0-9]*)\b[^>]*\/*\s*>*/i', "\n", $html);
        return preg_replace("/(\n)+/s", "\n", $notags);
    }

    /**
     * Returns whether the first scenario of the suite is running
     *
     * @return bool
     */
    protected static function is_first_scenario() {
        return !(self::$initprocessesfinished);
    }

    /**
     * Throws an exception after appending an extra info text.
     *
     * @throws Exception
     * @param UnknownError $exception
     * @return void
     */
    protected function throw_unknown_exception(UnknownError $exception) {
        $text = get_string('unknownexceptioninfo', 'tool_behat');
        throw new Exception($text . PHP_EOL . $exception->getMessage());
    }

}

