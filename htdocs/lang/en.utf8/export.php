<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['allmydata'] = 'All my data';
$string['chooseanexportformat'] = 'Choose an export format';
$string['exportarchivedescription1'] = 'You will receive a ZIP archive that includes the content, which you selected to export, in both HTML and Leap2A. You can view your portfolio in a browser via the index.html file or import the content in another portfolio platform that supports the Leap2A format.';
$string['exportarchivedescriptionpdf'] = 'You will receive a ZIP archive that includes the content, which you selected to export, in HTML, Leap2A, and PDF. You can view your portfolio in a browser via the index.html, in the exported PDfs files, or import the content in another portfolio platform that supports the Leap2A format.';
$string['clicktopreview'] = 'Click to preview';
$string['collectionstoexport'] = 'Collections to export';
$string['creatingzipfile'] = 'Creating zip file';
$string['Done'] = 'Done';
$string['Export'] = 'Export';
$string['clickheretodownload'] = 'Click here to download it';
$string['continue'] = 'Continue';
$string['startinghtmlexport'] = 'Starting HTML export';
$string['startingleapexport'] = 'Starting Leap2A export';
$string['startingpdfexport'] = 'Starting PDF export';
$string['exportgeneratedsuccessfully'] = 'Export generated successfully. %sClick here to download it%s';
$string['exportgeneratedsuccessfully1'] = 'Export generated successfully.';
$string['exportgeneratedwitherrors'] = 'Export generated with some errors.';
$string['exportingartefactplugindata'] = 'Exporting artefact plugin data';
$string['exportingartefacts'] = 'Exporting artefacts';
$string['exportingartefactsprogress'] = 'Exporting artefacts: %s/%s';
$string['exportingfooter'] = 'Exporting footer';
$string['exportingviews'] = 'Exporting pages';
$string['exportingcollections'] = 'Exporting collections';
$string['exportingviewsprogresshtml'] = 'Exporting pages for HTML: %s/%s';
$string['exportingviewsprogressleap'] = 'Exporting pages for Leap2A: %s/%s';
$string['exportingviewsprogresspdf'] = 'Creating PDFs: %s/%s';
$string['exportportfoliodescription1'] = '<p class="lead">This tool exports all of your portfolio information and pages. It does not export your site settings or any of the content you uploaded or created in groups.
</p><p class="lead">You can export your personal portfolio content. Your account settings or content uploaded or created in groups are not exported.</p>';
$string['exportyourportfolio'] = 'Export your portfolio';
$string['generateexport'] = 'Generate export';
$string['noexportpluginsenabled'] = 'No export plugins have been enabled by the site administrator, so you are unable to use this feature';
$string['justsomecollections'] = 'Just some of my collections';
$string['justsomeviews'] = 'Just some of my pages';
$string['includecomments'] = 'Include comments';
$string['includecommentsdescription'] = 'All comments will be included in the HTML export.';
$string['includeprivatefeedback'] = 'Include private comments';
$string['includeprivatefeedbackdesc1'] = 'If you include private comments in your export, people you share this export with will see them. To keep them private, create an export without comments or don\'t include private comments.';
$string['nonexistentfile'] = "Tried to add non-existent file '%s'";
$string['nonexistentprofileicon'] = "Tried to add non-existent profile icon '%s'";
$string['nonexistentresizedprofileicon'] = "Tried to add non-existent resized profile icon '%s'";
$string['unabletocopyartefact'] = "Unable to copy artefact file '%s'";
$string['unabletocopyprofileicon'] = "Unable to copy profile icon '%s'";
$string['unabletocopyresizedprofileicon'] = "Unable to copy resized profile icon '%s'";
$string['couldnotcreatedirectory'] = "Could not create directory '%s'";
$string['couldnotcreatestaticdirectory'] = "Could not create static directory '%s'";
$string['couldnotcopystaticfile'] = "Could not copy static file '%s'";
$string['couldnotcopyattachment'] = "Could not copy attachment '%s'";
$string['couldnotcopyfilesfromto'] = "Could not copy files from the directory '%s' to '%s'";
$string['couldnotwriteLEAPdata'] = "Could not write Leap2A data to the file";
$string['pleasewaitwhileyourexportisbeinggenerated'] = 'Please wait while your export is being generated...';
$string['reverseselection'] = 'Reverse selection';
$string['selectall'] = 'Select all';
$string['setupcomplete'] = 'Setup complete';
$string['Starting'] = 'Starting';
$string['unabletoexportportfoliousingoptions'] = 'Unable to export a portfolio using the chosen options';
$string['unabletogenerateexport'] = 'Unable to generate export';
$string['viewstoexport'] = 'Pages to export';
$string['whatdoyouwanttoexport'] = 'What do you want to export?';
$string['writingfiles'] = 'Writing files';
$string['youarehere'] = 'You are here';
$string['youmustselectatleastonecollectiontoexport'] = 'You must select at least one collection to export';
$string['youmustselectatleastoneviewtoexport'] = 'You must select at least one page to export';
$string['zipnotinstalled'] = 'Your system does not have the ZIP command. Please install ZIP to enable this feature.';
$string['addedleap2atoexportqueuecollections'] = 'Added some of your collections to the export queue.';
$string['addedleap2atoexportqueueviews'] = 'Added some of your pages to the export queue.';
$string['addedleap2atoexportqueueall'] = 'Added all your data to the export queue.';
$string['exportqueuenotempty'] = 'Items in the export queue for this person. Please wait until they have been archived.';
$string['requeue'] = 'Re-queue';

// Export queue errors
$string['unabletogenerateexport'] = 'Insufficient information';
$string['unabletoexportportfoliousingoptionsadmin1'] = 'The item is not a portfolio object';
$string['exportzipfileerror'] = 'Generating the ZIP file failed: %s';
$string['submissiondirnotwritable'] = 'Cannot write to submission archive directory: %s';
$string['exportarchivesavefailed'] = 'Cannot save export archive information to database';
$string['archivedsubmissionfailed'] = 'Cannot save archived submission information to database';
$string['submissionreleasefailed'] =  'Failed to release submission after archiving';
$string['deleteexportqueueitems'] = 'Failed to remove items from export queue items database table';
$string['deleteexportqueuerow'] = 'Failed to remove items from export queue database table';
$string['exportqueueerrorsadminsubject'] = 'Error running export queue';
$string['exportqueueerrorsadminmessage'] = 'Unable to export row "%s": %s';
$string['exportlitenotwritable'] = 'Export directory "%s" is not writable.';
