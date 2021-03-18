<?php
/**
 *
 * @package    mahara
 * @subpackage local
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Portfolio verification';
$string['description'] = 'Display verification details';
$string['blockcontentdescription'] = 'Write the text of the statement you want to have approved. The verifier clicks a check box to confirm the statement. If you leave this field empty, enable the comment field so verifiers can write their own statement.';
$string['textdescription'] = 'Write the text of your statement. The verifier clicks a check box to confirm the statement. If you leave this field empty, enable the comment field so verifiers can write their own statement.';
$string['addcommentdescription'] = 'When you allow a comment, the verifier has a text field available to add their own statement. This can be used together with a default statement. A comment can be made no matter whether the portfolio is locked or not.';
$string['displayverifiername'] = 'Display name of verifier';
$string['displayverifiernamedescription'] = 'Decide whether you want to display the name of the verifier when they approved the statement. A time stamp is always displayed.';
$string['availabletodescription'] = 'Select the role or roles that a person must have to approve the statement. If you leave this field empty, no special role is needed, and anybody who has access to the portfolio can verify a default statement or provide a comment. Roles do not accumulate.';
$string['availabilitydatedescription'] = 'Select the date from when the statement shall be available. Before then, the verifier will see an alert letting them know that they cannot perform the verification until that date. That date is also mentioned in the access notification. If you don\'t add a date, the statement can be made at any point in time.';
$string['toverifyspecific'] = 'Verify "%s"';
$string['verifiedspecific'] = '"%s" is verified';
$string['verificationmodaltitle'] = 'Complete: %s';
$string['verificationchecklist'] = '<p>After you have confirmed this statement, you cannot revert your verification.</p>';
$string['verificationchecklistlockingnames'] = '<p>After you have confirmed this statement,</p>
<ul>
<li>the portfolio author will not be able to make any changes to their portfolio.</li>
<li>the portfolio author cannot add another verifier.</li>
<li>you cannot revert your verification. If you wish to do so, click the "More options" button and select "Reset statement". The following can action the reset: %s.</li>
</ul>';
$string['unverify'] = 'If you continue, this verification will be removed.';
$string['verifiedon'] = 'Verified on %s';
$string['addcommentchecklistlocking'] = 'After you have published this statement,
- the portfolio author will not be able to make any changes to their portfolio.
- the portfolio author cannot add another verifier.
- you cannot revert your statement.';
$string['addcommentchecklistlockingnames'] = 'After you have published this statement,
- the portfolio author will not be able to make any changes to their portfolio.
- the portfolio author cannot add another verifier.
- you cannot revert your statement. If you wish to do so, you will need to contact one of the following: %s.';
