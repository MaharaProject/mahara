<?php

defined('INTERNAL') || die();

$string['pluginname'] = 'Submissions';
$string['Submissions'] = 'Submissions';
$string['submissionstitlegroup'] = '%s - Submissions';

// Table
$string['Group'] = 'Group';
$string['Role'] = 'Role';
$string['Name'] = 'Name';
$string['PreferredName'] = 'Display name';
$string['Portfolio'] = 'Portfolio';
$string['Date'] = 'Date';
$string['Task'] = 'Task';
$string['Evaluator'] = 'Assessor';
$string['Feedback'] = 'Feedback';
$string['Rating'] = 'Rating';
$string['Result'] = 'Result';
$string['State'] = 'Status';

$string['assessor'] = 'Assessor';
$string['submitter'] = 'Author';
$string['release'] = 'To be released';
$string['fix'] = 'Fix';
$string['notevaluated'] = 'Not evaluated';
$string['submitted'] = 'Submitted';
$string['releasing'] = 'Releasing...';
$string['completed'] = 'Released';
$string['revision'] = 'Revision';
$string['evaluated'] = 'Evaluated';

$string['unassignedselectboxitem'] = '- Unassigned -';
$string['releaseandreturn'] = 'Release and return';

// Table messages
$string['releasesubmission'] = 'Complete assessment and release submitted portfolio?';
$string['fixsubmission'] = 'Fix submission?';
$string['tooltip_success'] = 'Pass';
$string['tooltip_remove'] = 'Fail';
$string['tooltip_refresh'] = 'Revise';
$string['tooltip_question'] = 'Not graded';

// Datatables
$string['decimal'] = '';
$string['emptytable'] = 'No data available in table';
$string['info'] = '_START_ to _END_ of _TOTAL_ entries';
$string['infoempty'] = '0 entries';
$string['infofiltered'] = '';
$string['infopostfix'] = '';
$string['thousands'] = ',';
$string['lengthmenu'] = 'Show _MENU_ entries';
$string['loading'] = 'Loading';
$string['processing'] = 'Processing';
$string['search'] = 'Search:';
$string['searchplaceholder'] = 'Enter search term...';
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
$string['quickfilter'] = 'Quick filter';
$string['quickfiltertooltip'] = 'Reset the filters';
$string['Off'] = 'Any';
$string['Reset'] = 'Reset';
$string['Select'] = 'Select';
$string['All'] = 'All';
$string['Search'] = 'Search';
$string['MissingTask'] = 'Missing task';
$string['Unassigned'] = 'Unassigned';
$string['Uncommented'] = 'Uncommented';
$string['Commented'] = 'Commented';
$string['Revision'] = 'Revise';
$string['Success'] = 'Pass';
$string['Fail'] = 'Fail';
$string['Pending'] = 'Missing';
$string['Open'] = 'Open';
$string['Completed'] = 'Completed';
$string['colvislabel'] = 'Configure columns';

// Release form
$string['chooseresult'] = 'Choose result';
$string['noresult'] = 'Revise';
$string['fail'] = 'Fail';
$string['success'] = 'Pass';

// Error messages
$string['lastsubmissionnoevaluationresultcontactinstructor'] = 'Your last submission of this portfolio or the group task has no evaluation result. Contact your instructor for solving this issue. Your submission was automatically released.';
$string['portfolioortaskalreadyevaluated'] = 'The portfolio or group task has already been evaluated. Your submission was automatically released.';
$string['portfoliocurrentlybeingreleased'] = 'The portfolio to this task is currently being released. Wait until it is completely released to submit it again.';
$string['portfolioalreadysubmitted'] = 'The portfolio to this task has already been submitted. Wait until it is released to submit it again.';
$string['submissionnotcreated'] = 'Submission not created: ';
$string['submissionnotcreatedorupdated'] = 'Submission not created or updated: ';

// Exceptions
$string['notallowedtoassesssubmission'] = 'You are not allowed to assess this submission.';
$string['dependingonstatusmayeditupdatedfields'] = 'Depending on the new status you may now edit the updated fields.';
$string['submissionreadonlynotupdated'] = 'Submission not updated due to read-only submission status.';
$string['submissionnotfixedmissingevaluationresult'] = 'Submission not fixed due to missing evaluation result.';
$string['submissionnotreleasedorfixedwrongsubmissionstatus'] = 'Submission not released or fixed due to wrong submission status.';
$string['missingcontrollerhandler'] = 'Missing handler for controller command.';
$string['unsupportedsubmissionownertype'] = 'Unsupported submission portfolio owner type.';
$string['portfoliofieldsmustbesetforsettingtaskfields'] = 'The portfolio element fields must be set for setting the task fields of a submission.';
$string['unsupportedportfoliotype'] = 'Unsupported submission portfolio element type.';
$string['submissionstatuschangedexternally'] = 'Submission status has been changed externally.';
$string['eventgroupnotfound'] = 'Group "%s" from event data not found.';
$string['eventgroupidnotfound'] = 'Group with ID "%s" from event data not found.';
$string['submissionexceptiontitle'] = 'Could not submit portfolio for assessment';
$string['submissionexceptionmessage'] = 'This portfolio cannot be submitted for assessment due to the following reason:';

// Submission released message
$string['actionreleased'] = 'released';
$string['actionfixed'] = 'fixed';
$string['submissionreleased'] = 'Submission "%s" from author %s %s.';

// UrlFlashback
$string['ProceedWithBackRestrictionsToExistingTab'] = 'A submitted portfolio is already open in another tab. You can proceed to this portfolio, but it will not be highlighted in the "Submissions" table when you return. The same applies to the portfolio in the other tab. Do you want to proceed?';
$string['ProceedWithoutFlashbackFunctionality'] = 'A submitted portfolio is already open in another tab. you can proceed to this portfolio, but it will not be highlighted in the "Submissions" table when you return via the browser\'s "Back" button. Do you want to proceed?';

// SubmissionsSettings
$string['shownameaslastnamefirstname'] = 'Show name as "lastname, firstname"';
$string['shownameaslastnamefirstnamedescription'] = 'Show all appearances of names in the submission table in the format "lastname, firstname"';
$string['showportfoliobuttons'] = 'Show portfolio links as buttons';
$string['showportfoliobuttonsdescription'] = 'Change standard Mahara display of portfolio links to buttons for a better optical differentiation to the "Download as archive functionality" in the "Submissions" table .';
$string['retentionperiod'] = 'Retention period for submissions and archived portfolios';
$string['retentionperioddescription'] = 'The period in years beginning with the next year after the release of a portfolio and related archived portfolios are deleted. If this value is set to zero, all submissions and related portfolios will be deleted once the portfolio author account is deleted.';
