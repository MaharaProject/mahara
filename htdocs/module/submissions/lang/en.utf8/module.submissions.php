<?php

defined('INTERNAL') || die();

$string['pluginname'] = 'Submissions';
$string['Submissions'] = 'Submissions';

// Table
$string['Group'] = 'Group';
$string['Role'] = 'Role';
$string['Name'] = 'Name';
$string['PreferredName'] = 'Display name';
$string['Portfolio'] = 'Portfolio';
$string['Date'] = 'Date';
$string['Task'] = 'Task';
$string['Evaluator'] = 'Evaluator';
$string['Feedback'] = 'Feedback';
$string['Rating'] = 'Rating';
$string['Result'] = 'Result';
$string['State'] = 'State';

$string['assessor'] = 'Assessor';
$string['submitter'] = 'Submitter';
$string['release'] = 'Release';
$string['fix'] = 'Fix';
$string['notevaluated'] = 'Not evaluated';
$string['submitted'] = 'Submitted';
$string['releasing'] = 'Releasing...';
$string['completed'] = 'Completed';
$string['revision'] = 'Revision';
$string['evaluated'] = 'Evaluated';

$string['unassignedselectboxitem'] = '- Unassigned -';

// Table messages
$string['releasesubmission'] = 'Complete evaluation and release submitted portfolio?';
$string['fixsubmission'] = 'Fix submission?';

// Datatables
$string['decimal'] = '';
$string['emptytable'] = 'No data available in table';
$string['info'] = 'Showing _START_ to _END_ of _TOTAL_ entries';
$string['infoempty'] = 'Showing 0 to 0 of 0 entries';
$string['infofiltered'] = '';
$string['infopostfix'] = '';
$string['thousands'] = ',';
$string['lengthmenu'] = 'Show _MENU_ entries';
$string['loading'] = 'Loading';
$string['processing'] = 'Processing';
$string['search'] = 'Search:';
$string['searchplaceholder'] = 'Enter your search term...';
$string['zerorecords'] = 'No records';
$string['first'] = 'First page';
$string['last'] = 'Last page';
$string['next'] = 'Next page';
$string['previous'] = 'Previous page';
$string['sortascending'] = ': activate to sort column ascending';
$string['sortdescending'] = ': activate to sort column descending';

// Preview
$string['displayportfolio'] = 'Display portfolio';

// Quickfilter
$string['Off'] = 'Any';
$string['Reset'] = 'Reset';
$string['Select'] = 'Select';
$string['All'] = 'All';
$string['Search'] = 'Search';
$string['MissingTask'] = 'Missing task';
$string['Unassigned'] = 'Unassigned';
$string['Uncommented'] = 'uncommented';
$string['Commented'] = 'commented';
$string['Revision'] = 'Revision';
$string['Success'] = 'Success';
$string['Fail'] = 'Fail';
$string['Pending'] = 'Missing';
$string['Open'] = 'Open';
$string['Completed'] = 'Completed';

// Release form
$string['chooseresult'] = 'Choose result';
$string['noresult'] = 'Revision';
$string['fail'] = 'Fail';
$string['success'] = 'Success';

// Error messages
$string['lastsubmissionnoevaluationresultcontactinstructor'] = 'Your last submission of this Portfolio or the group task has no evaluation result. Contact your instructor for solving this issue - Your submission is automatically released.';
$string['portfolioortaskalreadyevaluated'] = 'Portfolio or group task has already been evaluated - Your submission is automatically released.';
$string['portfoliocurrentlybeingreleased'] = 'The Portfolio to this task is currently being released - You have to wait until it is completely released to submit it again.';
$string['portfolioalreadysubmitted'] = 'The Portfolio to this task has already been submitted - You have to wait until it is released to submit it again.';
$string['submissionnotcreated'] = 'Submission not created: ';
$string['submissionnotcreatedorupdated'] = 'Submission not created or updated: ';

// Exceptions
$string['notallowedtoassesssubmission'] = 'You are not allowed to assess this submission.';
$string['dependingonstatusmayeditupdatedfields'] = ' Depending on the new status you may now edit the updated fields.';
$string['submissionreadonlynotupdated'] = 'Submission not updated due to readonly submission status.';
$string['submissionnotfixedmissingevaluationresult'] = 'Submission not fixed due to missing evaluation result.';
$string['submissionnotreleasedorfixedwrongsubmissionstatus'] = 'Submission not released or fixed due to wrong submission status.';
$string['missingcontrollerhandler'] = 'Missing handler for controller command.';
$string['unsupportedsubmissionownertype'] = 'Unsupported submission portfolio owner type.';
$string['portfoliofieldsmustbesetforsettingtaskfields'] = 'The portfolio element fields must be set for setting the task Fields of a submission.';
$string['unsupportedportfoliotype'] = 'Unsupported Submissions portfolio element type.';
$string['submissionstatuschangedexternally'] = 'Submission status has been changed externally.';
$string['eventgroupnotfound'] = 'Group "%s" from event data not found.';
$string['eventgroupidnotfound'] = 'Group with id "%s" from event data not found.';

// Submission released message
$string['actionreleased'] = 'released';
$string['actionfixed'] = 'fixed';
$string['submissionreleased'] = 'Submission "%s" from owner "%s" %s.';

// UrlFlashback
$string['ProceedWithBackRestrictionsToExistingTab'] = 'A Portfolio is already visited from Submissions in another tab. You only can proceed visiting this portfolio without automatic selection on your return to this page. The same applies if returning to the list in the other tab using the browser back button. Proceed?';
$string['ProceedWithoutFlashbackFunctionality'] = 'A Portfolio is already visited from submissions in another tab. You only can proceed visiting this portfolio without automatic selection returning to this page. Proceed?';

// SubmissionsSettings
$string['shownameaslastnamefirstname'] = 'Show name as "lastname, firstname"';
$string['shownameaslastnamefirstnamedescription'] = 'Show all appearances of names in the submission table in the format "lastname, firstname"';
$string['showportfoliobuttons'] = 'Show portfolio links as buttons';
$string['showportfoliobuttonsdescription'] = 'Change standard Mahara display of portfolio links to buttons for a better optical difference to the download as archive functionality in submissions table .';
$string['retentionperiod'] = 'Retention period for submissions and archived portfolios';
$string['retentionperioddescription'] = 'The period in years beginning with the next year after submission release after the submission and related archived portfolios are deleted. If this value is set to zero all submissions and related portfolios will be deleted if the portfolio owner is deleted.';
