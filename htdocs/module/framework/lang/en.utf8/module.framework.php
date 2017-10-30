<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['Framework'] = 'SmartEvidence framework';
$string['frameworknav'] = 'SmartEvidence';
$string['frameworks'] = 'Installed frameworks';
$string['frameworkdesc'] = 'Choose the competency framework that you want to associate with your portfolio.';
$string['frameworksdesc'] = 'List of frameworks that are installed in the system. Inactive frameworks are not listed unless the collection is already associated with the framework when it was active.';
$string['taskscompleted'] = 'Tasks completed';
$string['addpages'] = 'Add more pages to this collection if you want them to show up here in the SmartEvidence map.';
$string['addframework'] = 'Add framework';
$string['frameworkupdated'] = 'Framework updated';
$string['matrixfile'] = 'Matrix file';
$string['matrixfiledesc'] = 'The .matrix file containing the JSON encoded framework';
$string['notvalidmatrixfile'] = 'This is not a valid .matrix file.';
$string['matrixfilenotfound'] = 'No valid .matrix file selected.';
$string['invalidjson'] = 'This is not a valid .matrix file: Unable to parse the JSON content.';
$string['jsonmissingvars'] = 'This is not a valid .matrix file: Missing "framework" and / or "framework name".';
$string['manuallyremovematrices'] = 'Unable to remove install directory "%s". Please remove it manually.';
$string['changeframeworkproblems'] = 'You cannot change the framework. The following pages have evidence connected to this framework:';
$string['accessdeniednoframework'] = 'This collection cannot show the SmartEvidence page. This can be due to any of the following reasons:
<ul><li>The SmartEvidence plugin is not installed / active.</li>
<li>The institution you belong to disallowed SmartEvidence.</li>
<li>This collection does not have a SmartEvidence framework set.</li>
<li>There aren\'t any pages in this collection.</li>
</ul>';
$string['firstviewlink'] = 'Please navigate to the <a href="%s">first page</a> of the collection.';
$string['noframeworkselected'] = 'None';
$string['matrixpointupdated'] = "SmartEvidence updated";
$string['matrixpointinserted'] = "SmartEvidence added";
$string['standard'] = 'Standard';
$string['standarddesc'] = 'Select the standard this evidence addresses. You can type into the box to search the standards.';
$string['annotationclash'] = 'There is already an annotation block on the page for this standard';
$string['needtoactivate'] = 'The annotation plugin needs to be activated. Please ask your site administrator to do so.';
$string['studentannotation'] = "Annotation:";
$string['assessment'] = 'Assessment';
$string['begun'] = 'Ready for assessment';
$string['incomplete'] = 'Doesn\'t meet the standard';
$string['partialcomplete'] = 'Partially meets the standard';
$string['completed'] = 'Meets the standard';
$string['usedincollections'] = 'Used in collections';
$string['selfassess'] = 'Self-assess';
$string['uploadframeworkdesc1'] = 'Upload a JSON encoded .matrix file. See the <a href="https://git.mahara.org/mahara/mahara/blob/16.10_STABLE/test/behat/upload_files/example.matrix">Mahara git repository</a> for an example of the markup and the <a href="http://manual.mahara.org/en/16.10/administration/smartevidence.html#create-a-framework-file">Mahara user manual</a> for an explanation of the individual components.';
$string['savematrix'] = 'Upload matrix';
$string['frameworkmissing'] = 'Framework not found';
$string['activeframework'] = 'Active framework';
$string['displaystatusestitle'] = 'Display assessment statuses';
$string['displaystatusestitledetail'] = 'Decide which of the assessment statuses you want to display.';
$string['upgradeplugin'] = 'The SmartEvidence plugin needs to be upgraded to enable these settings.';

$string['noannotation'] = 'There are no annotations on page "%s" for standard element "%s".';
$string['addannotation'] = 'Add an annotation for standard "%s" to page "%s".';
$string['readyforassessmentcount'] = 'Number of pages that contain the standard element ready for assessment:';
$string['dontmatchcount'] = 'Number of pages that contain the incompleted standard element:';
$string['partiallycompletecount'] = 'Number of pages that contain the partially completed standard element:';
$string['completedcount'] = 'Number of pages that contain the complete standard element:';
$string['tabledesc'] = 'Below is the structure for the collection\'s SmartEvidence matrix.';
$string['standardbegin'] = 'Beginning of a standard section';
$string['uncollapsesection'] = 'Click to show the table section for standard "%s".';
$string['collapsesection'] = 'Click to hide the section for standard "%s".';
$string['collapsedsection'] = 'This table section for the standard is hidden.';
$string['gonextpages'] = 'Click the "Next" button to display more pages of the collection in the SmartEvidence matrix.';
$string['goprevpages'] = 'Click the "Previous" button to display pages in the SmartEvidence matrix that come earlier in the collection.';
$string['headerelements'] = 'Column header: Standard elements';
$string['headerreadyforassessmentcount'] = 'Column header: Number of pages that contain the standard element ready for assessment';
$string['headernotmatchcount'] = 'Column header: Number of pages that contain the incomplete standard element';
$string['headerpartiallycompletecount'] = 'Column header: Number of pages that contain the partially completed standard element';
$string['headercompletedcount'] = 'Column header: Number of pages that contain the completed standard element';
$string['headerpage'] = 'Column header: Page title';
$string['headerrow'] = 'Row header: Standard element';
$string['showelementdetails'] = 'Click to show standard element details.';
$string['statusdetail'] = 'Page "%s": %s';
