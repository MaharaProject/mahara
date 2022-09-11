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

$string['pluginname'] = 'Peer assessment';
$string['Assessment'] = 'Assessment';
$string['Assessments'] = 'Assessments';
$string['assessment'] = 'assessment';
$string['assessments'] = 'assessments';
$string['nassessments'] = array(
    '%s assessment',
    '%s assessments'
);
$string['makeassessmentpublic'] = 'Publish assessment';
$string['attachfile'] = 'Attach file';
$string['messageempty'] = 'Peer assessment is empty';
$string['assessmentsubmitted'] = 'Peer assessment saved';
$string['assessmentsubmitteddraft'] = 'Peer assessment saved as draft';
$string['reallydeletethisassessment'] = 'Delete this peer assessment';
$string['thisassessmentisprivate'] = 'Saved as draft';
$string['assessmentremoved'] = 'Peer assessment deleted';

// peer assessment notifications
$string['deletednotificationsubject'] = 'Peer assessment on page "%s" deleted';
$string['deletedauthornotification1'] = "Your peer assessor, %s, deleted their assessment on page \"%s\". They had written:\n%s";
$string['newassessmentnotificationsubject'] = 'New peer assessment on page "%s"';
$string['feedbacknotificationhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>%s added a peer assessment on %s</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">View this peer assessment online</a></p>
</div>";
$string['feedbacknotificationtext1'] = "%s added a peer assessment on %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see this peer assessment online, follow this link:
%s";
$string['typeassessmentfeedback'] = 'Peer assessment';
$string['nopeerassessmentrequired'] = 'You cannot see the content on this page because it does not require a peer assessment.';
$string['signedoffalertpeermsg'] = 'This page was signed off by %s. You cannot add, edit, or delete your peer assessments any more. If you still need to complete a peer assessment, please contact %s.';
$string['placeassessment'] = 'Place assessments';
$string['verifyassessment'] = 'Verify assessments';
$string['progress_peerassessment'] = array(
    'Add 1 peer assessment to a page',
    'Add %s peer assessments to pages',
);
$string['progress_verify'] = array(
    "Give 1 verification to another person's peer assessment page",
    "Give %s verifications to other people's peer assessment pages",
);
$string['importedassessment'] = "Imported peer assessment";