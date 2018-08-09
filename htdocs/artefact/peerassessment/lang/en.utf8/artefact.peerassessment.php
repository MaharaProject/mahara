<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['pluginname'] = 'Peer assessment';
$string['Assessment'] = 'Assessment';
$string['Assessments'] = 'Assessments';
$string['assessment'] = 'assessment';
$string['assessments'] = 'assessments';
$string['makeassessmentpublic'] = 'Publish assessment';
$string['attachfile'] = 'Attach file';
$string['messageempty'] = 'Peer assessment is empty';
$string['assessmentsubmitted'] = 'Peer assessment saved';
$string['assessmentsubmitteddraft'] = 'Peer assessment saved as draft';
$string['reallydeletethisassessment'] = 'Delete this peer assessment';
$string['thisassessmentisprivate'] = 'Saved as draft';
$string['assessmentremoved'] = 'Peer assessment deleted';
$string['assessmentviewupdated'] = 'Peer assessment status updated';
$string['wrongassessmentviewrequest'] = 'You do not have permission to perform the requested action';

// peer assessment notifications
$string['deletednotificationsubject'] = 'Peer assessment on page "%s" deleted';
$string['deletedauthornotification'] = "Your peer assessment on page \"%s\" was deleted. You had written:\n%s";
$string['newassessmentnotificationsubject'] = 'New peer assessment on page "%s"';
$string['feedbacknotificationhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>%s added a peer assessment on %s</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">View this peer assessment online</a></p>
</div>";
$string['feedbacknotificationtext'] = "%s add a peer assessment on %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see this peer assessment online, follow this link:
%s";
$string['typeassessmentfeedback'] = 'Peer assessment';
