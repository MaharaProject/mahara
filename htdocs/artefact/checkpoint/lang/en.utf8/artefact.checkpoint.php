<?php

/**
* Strings for the checkpoint blocktype associated to group activity pages
*
* @package    mahara
* @subpackage blocktype-checkpoint
* @author     Catalyst IT Limited <mahara@catalyst.net.nz>
* @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
* @copyright  For copyright information on Mahara, please see the README file distributed with this software.
*
*/
defined('INTERNAL') || die();

$string['pluginname'] = 'Checkpoint';
$string['Feedback'] = 'Comment';
$string['feedback'] = 'comment';
$string['comments'] = 'comments';
$string['nfeedback'] = array(
    '%s comment',
    '%s comments'
);
$string['feedbacksubmitted'] = 'Checkpoint comment saved';
$string['reallydeletethisfeedback'] = 'Do you really want to delete this checkpoint comment? You cannot undo this action.';
$string['feedbackremoved'] = 'Checkpoint comment deleted';
$string['assessmentremovedfailed'] = 'Checkpoint comment failed to delete';

$string['achievementlevel'] = 'Achievement level';
$string['level'] = 'level';
$string['level_cap'] = 'Level';
$string['save'] = 'Save';
$string['achievementauthor'] = 'Verified by';
$string['achievementleveltime'] = 'Verified on';

// Profile completion
$string['checkpointfeedback'] = 'Checkpoint feedback';
$string['placeassessment'] = 'Place checkpoints';
$string['progress_checkpointfeedback'] = array(
    'Add 1 checkpoint feedback',
    'Add %s checkpoint feedbacks',
);

$string['deletednotificationsubject'] = 'Checkpoint comment on "%s" deleted';
$string['deletedauthornotification1'] = "Your checkpoint comment on \"%s\" was deleted. You had written:\n%s";
$string['newfeedbacknotificationsubject'] = 'New checkpoint comment on page "%s"';
$string['feedbacknotificationhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>%s added a checkpoint comment on \"%s\"</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>
<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">View this checkpoint comment online</a></p>
</div>";
$string['feedbacknotificationtext1'] = "%s added a checkpoint comment on \"%s\"
%s
-------------------------------------------------------------------------

%s

-------------------------------------------------------------------------
To see this checkpoint comment online, follow this link:
%s";

$string['commentremovedbyuser'] = 'Comment removed by <a href="%s">%s</a>';
$string['typefeedback'] = 'Feedback on checkpoints';