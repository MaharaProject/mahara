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
$string['invalidjson'] = 'This is not a valid .matrix file: Unable to parse the JSON content.';
$string['jsonmissingvars'] = 'This is not a valid .matrix file: Missing "framework" and / or "framework name".';
$string['manuallyremovematrices'] = 'Unable to remove install directory "%s". Please remove it manually.';
$string['changeframeworkproblems'] = 'You cannot change the framework. The following pages have evidence connected to this framework:';
$string['accessdeniednoframework'] = 'This collection cannot show the SmartEvidence page. This can be due to any of the following reasons: The SmartEvidence plugin is not installed / active; the institution you belong to disallowed SmartEvidence; this collection does not have a SmartEvidence framework set; there aren\'t any pages in this collection.';
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
