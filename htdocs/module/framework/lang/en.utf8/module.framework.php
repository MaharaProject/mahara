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
$string['changeframeworkproblems'] = 'You cannot change the framework. The following pages have evidence connected to the previous framework:';
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
$string['assessmenttypecount'] = 'Number of pages that contain the standard element';
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
//json editor strings
$string['copyframework'] = 'Select a framework to copy';
$string['copyexistingframework'] = 'Copy existing framework';
$string['editframework'] = 'Select a framework to edit';
$string['editsavedframework'] = 'Edit saved framework';
$string['editdescription1'] = 'To be editable, a framework needs to be inactive and not currently used in a collection.';
$string['editdescription2'] = 'If you edit a framework, you will change the saved data for that framework.';
$string['copyframeworkdescription'] = 'You can copy any framework that is installed and use it as basis for a new framework file.';
$string['successmessage'] = 'Your framework has been submitted';
$string['titledesc'] = 'The title of the framework should be short. It is displayed as title on the SmartEvidence page as well as in the drop-down menu where people select the framework.';
$string['instdescription'] = 'Select the institution that can use this SmartEvidence framework. You can restrict the use to one institution or allow all of them to access to it.';
$string['frameworktitle'] = 'Title of your framework';
$string['defaultdescription'] = 'Description of your framework';
$string['descriptioninfo'] = 'Write more information describing the framework. You can use simple HTML.';
$string['selfassessed'] = 'Self-assessment';
$string['evidencestatuses'] = 'Evidence statuses';
$string['evidencedesc'] = 'Name the different states that indicate how complete a part of the framework is. There are 4 options with "Begun" indicating that evidence has been submitted. The other 3 are assessment statuses.';
$string['Begun'] = 'Begun';
$string['Incomplete'] = 'Incomplete';
$string['Partialcomplete'] = 'Partially complete';
$string['Completed'] = 'Completed';
$string['standards'] = 'Standards';
$string['shortnamestandard'] = 'The short name for this standard category. It is limited to 100 characters.';
$string['titlestandard'] = 'The title of this standard category. It is limited to 255 characters.';
$string['descstandard'] = 'This description appears when you hover over the standard on the SmartEvidence page. You can use simple HTML.';
$string['descstandarddefault'] = 'Description of this standard category';
$string['standardid'] = 'Standard ID';
$string['standardiddesc'] = 'This should be a whole number.';
$string['standardiddesc1'] = 'Choose the standard which this standard element is a part of.';
$string['standardelements'] = 'Standard elements';
$string['standardelement'] = 'Standard element';
$string['standardelementdesc'] = 'This description appears when you hover over this standard element on the SmartEvidence page. It is also displayed when you select this standard in the "Annotations" block. You can use simple HTML.';
$string['standardelementdefault'] = 'Description of the standard element';
$string['elementid'] = 'Element ID';
$string['elementiddesc'] = 'This is the ID for this standard element. A sequence of numbers is used to indicate hierarchy.';
$string['invalidjsonineditor'] = 'The current form has invalid json. Please scroll down the page to see the specific error.';
$string['validjson'] = 'The current form contents are valid and ok to submit';
$string['moveright'] = 'Move right';
$string['moveleft'] = 'Move left';
$string['deletelast'] = 'Delete last';
$string['deleteall'] = 'Delete all';
$string['selfassesseddescription'] = 'Decide whether a staff member can perform the assessment (default) or whether the portfolio authors can use the standard for self-assessment purposes and select the assessment statuses themselves.';
$string['standardsdescription'] = 'Create the categories in which your framework is divided. Later on, you can assign individual standard elements and sub-elements to a standard category. You must have at least one category.';
$string['standardelementsdescription'] = 'Create the individual standard elements, i.e. descriptors, to which content can be aligned. The standard elements can be put into a hierarchical order if needed by using the "Parent ID" option.';
$string['parentelementid'] = 'Parent element ID';
$string['parentelementdesc'] = 'To make an element that sits under a current standard element in the hierarchy, select it as the parent.';
$string['jsondatanotsubmitted'] = 'Your form data was not submitted to the database, please make sure you save the information you have entered in a separate file, refresh the form and try again or upload the json directly using the "Upload" tab';
$string['editor'] = 'Editor';
$string['Management'] = 'Management';
$string['all'] = 'all';
