<?php
/**
 *
 * @package    mahara
 * @subpackage export.pdf
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title1'] = 'PDF files of portfolios';
$string['description'] = 'This creates a zipped file containing PDFs of your portfolios. You cannot import this again, but the PDFs are readable in a standard PDF viewer.';
$string['needspdfconfig'] = 'Requires the config.php setting "usepdfexport" to be true.';
$string['needschromeheadless'] = 'Experimental export option that utilises Headless Chrome to print PDFs. Install the latest version of the Chrome or Chromium browser on the server to use this plugin.';
$string['needschromeheadlessphp'] = 'Requires "chrome-php". You can install this via "make pdfexport".';
$string['needspdfcombiner'] = 'Requires either "pdfunite" or "ghostscript" to be able to combine PDFs. You can install "pdfunite" via "apt-get install poppler-utils".';
$string['exportpdfdisabled'] = 'PDF export dependencies are missing. PDF export is disabled. For more information see <a href="%s">\'Plugin administration\'</a>.';
$string['pdffoundcombiner'] = 'Using PDF combiner: %s';
$string['beginpdfviewexport'] = 'Begin the PDF export process';
$string['startuppdfchrome'] = 'Starting up Chrome browser headless...';
$string['pdfchromestarted'] = 'Starting up Chrome browser headless... Done.';
$string['pdfchromestartederror'] = 'Chrome browser was unable to start. See the error log for more information.';