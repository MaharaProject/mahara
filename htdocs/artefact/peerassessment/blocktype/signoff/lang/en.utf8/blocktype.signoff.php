<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-signoff
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Sign-off';
$string['description'] = 'A block to display page sign-off and verification options';
$string['placeholder'] = 'This block\'s content is displayed below the page heading rather than in a block itself on the page.';
$string['signoff'] = 'Sign off';
$string['signoffdesc'] = 'The portfolio owner can sign off a page when all requirements have been met to indicate that it is ready for assessment.';

$string['verify'] = 'Verify';
$string['verifydesc'] = 'Decide whether a moderator needs to verify this page as part of the portfolio assessment process.';
$string['signedoff'] = 'Signed off';
$string['verified'] = 'Verified';

$string['signoffpagetitle'] = 'Sign off page';
$string['signoffpagedesc'] = 'Select "Yes" to sign off this page and indicate that you have met all requirements. Select "No" to abort.';
$string['signoffpageundodesc'] = 'If you select "Yes", you will remove the signed off status. That will also remove the verification if that had been part of the assessment work flow. Select "No" to abort.';
$string['signoffpageconfirm'] = 'Confirm this action?';

$string['verifypagetitle'] = 'Verify page';
$string['verifypagedesc'] = 'Select "Yes" to verify that the portfolio owner has met all requirements for this page. Select "No" to return to the page without verifying it.';

$string['updatesignoff'] = 'Update page sign-off';
$string['updateverify'] = 'Update page verification';

$string['removedverifynotificationsubject'] = 'Verification for %s removed';
$string['removedverifynotification'] = 'The owner of the page %s has removed their sign-off. Therefore, your verification has also been removed. Please go to the page to see if it is ready to be marked as verified again.';

$string['signoffviewupdated'] = 'Sign-off status updated';
$string['verifyviewupdated'] = 'Verification status updated';
$string['wrongsignoffviewrequest'] = 'You do not have permission to perform the requested action';
