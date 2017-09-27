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

$string['pluginname'] = 'Annotation';
$string['Annotation'] = 'Annotation';
$string['Annotations'] = 'Annotations';
$string['annotation'] = 'annotation';
$string['annotations'] = 'annotations';
$string['Annotationfeedback'] = 'Feedback';
$string['annotationfeedback'] = 'feedback';
$string['typeannotationfeedback'] = 'Feedback on annotations';
$string['allowannotationfeedback'] = 'Allow feedback';
$string['approvalrequired'] = 'Feedback is moderated. If you choose to make this feedback public, it will not be visible to others until it is approved by the owner.';

$string['canteditnotauthor'] = 'You are not the author of this feedback.';
$string['annotationfeedbacknotinview'] = 'Feedback %d not in page %d.';
$string['cantedittooold'] = 'You can only edit feedback that is less than %d minutes old.';

$string['cantedithasreplies'] = 'You can only edit the most recent feedback.';
$string['annotationfeedbackmadepublic'] = "Feedback made public";
$string['annotationfeedbackdeletedauthornotification'] = "Your feedback on %s was deleted:\n%s";
$string['annotationfeedbackdeletednotificationsubject'] = 'Feedback on %s deleted';

$string['annotationfeedbackremoved'] = 'Feedback removed.';
$string['editannotationfeedbackdescription'] = 'You can update your feedback if it is less than %d minutes old and has had no newer replies added. After this time you may still be able to delete your feedback and add new feedback.';
$string['annotationfeedbackupdated'] = 'Feedback updated.';

$string['commentremovedbyauthor'] = 'Feedback removed by the author';
$string['commentremovedbyowner'] = 'Feedback removed by the owner';
$string['commentremovedbyadmin'] = 'Feedback removed by an administrator';
$string['editannotationfeedback'] = 'Edit feedback';
$string['placeannotation'] = 'Add annotation';
$string['placeannotationfeedback'] = 'Place feedback';

$string['annotationfeedbacksubmitted'] = 'Feedback submitted.';
$string['annotationfeedbacksubmittedmoderatedanon'] = 'Feedback submitted, awaiting moderation.';
$string['annotationfeedbacksubmittedprivateanon'] = 'Private feedback submitted.';

$string['makepublic'] = 'Make public';
$string['makepublicnotallowed'] = 'You are not allowed to make this feedback public.';
$string['makepublicrequestsubject'] = 'Request to change private feedback to public.';
$string['makepublicrequestbyownermessage'] = '%s has requested that you make your feedback public.';
$string['groupadmins'] = 'Group administrators';
$string['makepublicrequestsent'] = 'A message has been sent to %s to request that the feedback be made public.';
$string['makepublicrequestbyauthormessage'] = '%s has requested that you make their feedback public.';

$string['annotationempty'] = 'This field is required.';
$string['annotationfeedbackempty'] = 'Your feedback is empty. Please enter a message.';

$string['newannotationfeedbacknotificationsubject'] = 'New feedback on %s';
$string['reallydeletethisannotationfeedback'] = 'Are you sure you want to delete this feedback?';
$string['annotationfeedbackisprivate'] = 'This feedback is private.';
$string['youhaverequestedpublic'] = 'You have requested that this feedback be made public.';

$string['annotationfeedbacknotificationhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>%s placed feedback on annotation %s</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">Reply to this feedback online</a></p>
</div>";
$string['annotationfeedbacknotificationtext'] = "%s placed feedback on annotation %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see and reply to the feedback online, follow this link:
%s";
$string['annotationfeedbackdeletedhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>Feedback on annotation %s was removed</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">%s</a></p>
</div>";
$string['annotationfeedbackdeletedtext'] = "Feedback on annotation %s was removed
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see %s online, follow this link:
%s";

$string['artefactdefaultpermissions'] = 'Default annotation permission';
$string['artefactdefaultpermissionsdescription'] = 'The selected artefact types will have feedback enabled on creation. Users can override these settings for individual artefacts.';

$string['annotationinformationerror'] = 'We do not have the right information to display the annotation.';

$string['invalidannotationfeedbacklinkerror'] = 'The feedback must be linked to either an artefact or a page.';
$string['entriesimportedfromleapexport'] = 'Entries imported from a Leap2A export that were not able to be imported elsewhere';
$string['unknownstrategyforimport'] = 'Unknown strategy chosen for importing entry.';
$string['invalidcreateannotationfeedback'] = 'Cannot create a feedback on its own.';
$string['nannotationfeedback'] = array(
    '1 feedback',
    '%s feedback',
);
$string['progress_annotation'] = array(
    'Add 1 annotation to a page',
    'Add %s annotations to pages',
);
$string['progress_annotationfeedback'] = array(
    "Give 1 feedback to another user's annotation",
    "Give %s feedbacks to other users' annotations",
);
$string['duplicatedannotation'] = 'Duplicated annotation';
$string['existingannotation'] = 'Existing feedback';
$string['duplicatedannotationfeedback'] = 'Duplicated annotation';
$string['existingannotationfeedback'] = 'Existing feedback';
$string['private'] = 'Private';
$string['public'] = 'Public';
$string['enteredon'] = 'entered on';
$string['noreflectionentryfound'] = "Cannot find reflection entry for annotation.";
$string['nofeedback'] = "There is no feedback for this annotation yet.";
$string['assessmentchangedto'] = "Assessment: %s";
