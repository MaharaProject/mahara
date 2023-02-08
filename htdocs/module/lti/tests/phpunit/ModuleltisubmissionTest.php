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
require_once(get_config('docroot') . 'module/lti/lib.php');

class ModuleltisubmissionTest extends MaharaUnitTest {
  /**
   * Test that the LTI grade is formatted correctly.
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
   * https://bugs.launchpad.net/mahara/+bug/2004852
   *
   * @return void
   */
  function testGradeNumberFormat() {
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

      $grade = 95;
      $grade = ModuleLtiSubmission::format_grade($grade);

      $this->assertIsString($grade, "ModuleLtiSubmission::format_grade() function did not return a string.");
      $this->assertStringContainsString('.', $grade, "ModuleLtiSubmission::format_grade() function did not return a string with a period.");
    }
    // Return the current locale to what it was.
    setlocale(LC_ALL, $current_locale);
  }
}