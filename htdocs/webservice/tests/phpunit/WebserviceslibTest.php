<?php
/**
 *
 * @package    mahara
 * @subpackage tests
 * @author     Gold <gold@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

require_once(get_config('docroot') . 'webservice/lib.php');
// A test class to call in the unit test.
class WebservicePHPUnitTestServer extends WebserviceBaseServer {
    protected function parse_request() { }
    protected function send_response() { }
    protected function send_error($ex = null) { }
}

/**
 * Test functions in htdocs/webservices/lib.php
 */
class WebserviceslibTest extends MaharaUnitTest {

  /**
   * Test that we can write to the webservices event log.
   *
   * The test requires that the server has one of the following locales:
   * * Dutch
   * * de_DE@euro
   * * de_DE
   * * de
   * * ge
   * * de_DE.UTF-8
   *
   * If the locale is not available on the server, the test will be skipped.
   *
   * This is in response to bug 1945537 where numbers were impacted by the
   * locale settings on the server.
   * https://bugs.launchpad.net/mahara/+bug/1945537
   *
   * @return void
   */
  function testRunDateFormat() {
    // Get the current locale.
    $current_locale = setlocale(LC_ALL, 0);
    // Set the locale to something that uses a comma as a decimal separator.
    $locale = setlocale(LC_NUMERIC, 'Dutch', 'de_DE@euro', 'de_DE', 'de', 'ge', 'de_DE.UTF-8');
    if ($locale === false) {
      // The locale is not available on this system.
      $this->markTestSkipped('None of the locales ("Dutch", "de_DE@euro", "de_DE", "de", "ge", "de_DE.UTF-8") are available on this system.');
    }
    else {
      // Get the localeconv for the current locale.
      $localeconv = localeconv();
      // Test that the locale does not have a period as a decimal separator.
      $this->assertStringNotContainsString('.', $localeconv['decimal_point'], "The localeconv decimal_point is a period when it should not be that.");

      // Instantiate the test class.
      $test = new WebservicePHPUnitTestServer('dummyauth');
      $time_start = microtime(true);
      // pause for half(ish) a second.
      usleep(5040302);
      $time_end = microtime(true);
      // This calculation should result in a value like 0,5040302 for this locale.
      $time_taken = $time_end - $time_start;
      // Build the log object.
      $log = (object)  array(
        'timelogged' => time(),
        'userid' => 1,
        'externalserviceid' => 1,
        'institution' => 'phpunit_WEBSERVICE_INSTITUTION',
        'protocol' => 'REST',
        'auth' => 'phpunit',
        'functionname' => __FUNCTION__,
        'timetaken' => number_format($time_taken, 5, '.', ''),
        'uri' => '/REQUEST_URI?phpunit=1',
        'info' => '',
        'ip' => '127.0.0.1'
      );
      // The log_webservice_call function should return an integer.
      $this->assertIsInt($test::log_webservice_call($log), "The log_webservice_call function did not return an integer.");
    }
    // Return the current locale to what it was.
    setlocale(LC_ALL, $current_locale);
  }
}