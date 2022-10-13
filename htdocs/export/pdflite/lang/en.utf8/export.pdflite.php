<?php
/**
 *
 * @package    mahara
 * @subpackage export.pdflite
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title1'] = 'PDF lite files of portfolios';
$string['description'] = 'This creates a lightweight zipped file containing PDFs of your portfolios, along with files associated with your portfolio that can be sent to a similarity checker. You cannot import this again, but it is readable in a standard PDF viewer.';
$string['needsinstalledpdfexport'] = 'It requires the "PDF" export plugin to be installed.';
$string['needsactivepdfexport'] = 'It requires the "PDF" export plugin to be enabled.';
$string['exportpdfdisabled'] = 'PDF lite export dependencies are missing. PDF lite export is disabled. For more information see <a href="%s">\'Plugin administration\'</a>.';
$string['isexperimental'] = 'This is an experimental export option that relies on the PDF export option to be installed and enabled.';
$string['beginpdfliteviewexport'] = 'Begin the PDFLite export process';