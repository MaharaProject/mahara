<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-verification
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Portfolio review';
$string['description'] = 'Display review details';
$string['primarystatement'] = 'Primary statement';
$string['primarystatementdescription'] = 'If you make this statement your primary statement, it will be displayed more prominently on the page. If there is already a primary statement on the page, you cannot add another one.';
$string['blockcontent'] = 'Statement text';
$string['blockcontentdescription'] = 'Write the text of the statement you want to have approved. The reviewer sees a check box to confirm the statement. If you leave this field empty, enable the comment field so reviewers can write their own statement.';
$string['textdescription'] = 'Write the text of your statement. The reviewer sees a check box to confirm the statement. If you leave this field empty, enable the comment field so reviewers can write their own statement.';
$string['addcomment'] = 'Add review comment';
$string['addcommentdescription'] = 'When you allow a comment, the reviewer has a text field available to add their own statement. This can be used together with a default statement. A comment statement can be made no matter whether the portfolio is locked or not.';
$string['displayverifiername'] = 'Display name of reviewer';
$string['displayverifiernamedescription'] = 'Decide whether you want to display the name of the reviewer when they approved the statement. A time stamp is always displayed.';
$string['availableto'] = 'Available to';
$string['availabletodescription'] = 'Select the role or roles that a reviewer must have to approve the statement. If you leave this field empty, no special role is needed, and anybody who has access to the portfolio can approve the statement or provide a comment. Roles do not accumulate.';

$string['groupadmin'] = 'Group administrator';
$string['grouptutor'] = 'Group tutor';
$string['groupmember'] = 'Group member';
$string['availabilitydate'] = 'Availability date';
$string['availabilitydatedescription'] = 'Select the date from when the statement shall be available. Before then, the reviewer will see an alert letting them know that they cannot perform the review until that date. That date is also mentioned in the access notification. If you don\'t add a date, the statement can be made at any point in time.';
$string['lockportfolio'] = 'Lock portfolio';
$string['lockportfoliodescription'] = 'Decide whether the portfolio shall be locked when the statement has been confirmed. If there are multiple statements, the portfolio is locked when the first one is confirmed that allows locking. Statement comments (if selected) can still be made.';
$string['notification'] = 'Notification to author';
$string['notificationdescription'] = 'Send a notification to the portfolio author when the statement has been confirmed and / or a comment has been added.';
$string['resetstatement'] = 'Reset statement';
$string['resetstatementdescription'] = 'Select the role or roles that a person must have to reset the statement. If no role is selected, nobody can reset the statement.';
$string['availabilitydatemessage'] = 'You cannot yet approve the statement or add a comment if that is allowed. This is possible after %s.';

$string['toverifyspecific'] = 'Review "%s"';
$string['verifiedspecific'] = '"%s" is reviewed';

$string['verificationmodaltitle'] = 'Review: %s';
$string['verificationchecklist'] = '<p>After you have confirmed this statement, you cannot revert your decision.</p>';
$string['verificationchecklistnames'] = '<p>After you have confirmed this statement, you cannot revert your decision. If you wish to do so, contact one of the following: %s by asking to have your statement reset under the "More options" button.</p>';
$string['verificationchecklistlocking'] = '<p>After you have confirmed this statement,</p>
<ul>
<li>the portfolio author will not be able to make any changes to their portfolio.</li>
<li>the portfolio author cannot add another reviewer.</li>
<li>you cannot revert your decision.</li>
</ul>';
$string['verificationchecklistlockingnames'] = '<p>After you have confirmed this statement,</p>
<ul>
<li>the portfolio author will not be able to make any changes to their portfolio.</li>
<li>the portfolio author cannot add another reviewer.</li>
<li>you cannot revert your decision. If you wish to do so, contact one of the following: %s by asking to have your statement reset under the "More options" button.</li>
</ul>';
$string['unverify'] = 'If you continue, this review will be removed.';
$string['verifiedon'] = 'Reviewed on %s';
$string['verifiedonby'] = '<a href="%s">%s</a> on %s';
$string['savedraft'] = 'Save draft';
$string['publish'] = 'Publish';
$string['cancel'] = 'Cancel';
$string['addcommentdescriptionhtml'] = '
<div><strong>Save draft:</strong> Only you can view your comment. You can still make changes.<br>
<strong>Publish:</strong> Everybody who has access to the portfolio can view your comment. You cannot revert a published comment to draft status.</div>';
$string['addcommentchecklist'] = 'After you have published this statement, you cannot revert it.';
$string['addcommentchecklistnames'] = 'After you have published this statement, you cannot revert it. If you wish to do so, contact one of the following: %s by asking to have your statement reset under the "More options" button.';
$string['addcommentchecklistlocking'] = 'After you have published this statement,
- the portfolio author will not be able to make any changes to their portfolio.
- the portfolio author cannot add another reviewer.
- you cannot revert your statement.';
$string['addcommentchecklistlockingnames'] = 'After you have published this statement,
- the portfolio author will not be able to make any changes to their portfolio.
- the portfolio author cannot add another reviewer.
- you cannot revert your statement. If you wish to do so, contact one of the following: %s by asking to have your statement reset under the "More options" button.';

$string['commentformplaceholder'] = '<i>The review comment form will appear here when the person who can write a comment views this page.</i>';
$string['verifycomment'] = 'Add comment';
$string['addcommentsuccess'] = 'Adding comment to "%s" was successful.';
$string['addcommentsuccessdraft'] = 'Adding draft comment to "%s" was successful.';

// notification
$string['verifymessagesubjectnoname'] = 'A statement has been confirmed on your portfolio:';
$string['verifymessagenoname'] = 'The following statement has been confirmed on %s:
%s';
$string['verifymessagesubject'] = '%s has confirmed a statement on your portfolio';
$string['verifymessage'] = '%s has confirmed the following statement on %s:
%s';
$string['blockneedscollection'] = 'This blocktype needs to be put on the portfolio completion page in a collection to display correctly.';
