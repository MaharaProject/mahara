<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['changepassworddesc'] = 'New password';
$string['changepasswordotherinterface'] = 'You may <a href="%s">change your password</a> through a different interface.';
$string['oldpasswordincorrect'] = 'This is not your current password.';

$string['changeusernameheading'] = 'Change username';
$string['changeusername'] = 'New username';
$string['changeusernamedesc'] = 'The username you use to log into %s. Usernames are 3-30 characters long and may contain letters, numbers, and most common symbols excluding spaces.';

$string['usernameexists1'] = 'You can\'t use this username. Please choose another one.';

$string['accountoptionsdesc'] = 'General account options';

$string['changeprofileurl'] = 'Change profile URL';
$string['profileurl'] = 'Profile URL';
$string['profileurldescription'] = 'A personalised URL for your profile page. This field must be 3-30 characters long.';
$string['urlalreadytaken'] = 'This profile URL is already taken. Please choose another one.';

$string['friendsnobody'] = 'Nobody may add me as a friend';
$string['friendsauth'] = 'New friends require my authorisation';
$string['friendsauto'] = 'New friends are automatically authorised';
$string['friendsdescr'] = 'Friends control';
$string['updatedfriendcontrolsetting'] = 'Updated friends control';

$string['wysiwygdescr'] = 'HTML editor';

$string['licensedefault'] = 'Default license';
$string['licensedefaultdescription'] = 'The default license for your content.';
$string['licensedefaultinherit'] = 'Use the institution default';

$string['messagesdescr'] = 'Messages from other users';
$string['messagesnobody'] = 'Do not allow anyone to send me messages';
$string['messagesfriends'] = 'Allow people on my friends list to send me messages';
$string['messagesallow'] = 'Allow anyone to send me messages';

$string['language'] = 'Language';

$string['showviewcolumns'] = 'Show controls to add and remove columns when editing a page';

$string['tagssideblockmaxtags'] = 'Maximum tags in cloud';
$string['tagssideblockmaxtagsdescription'] = 'Maximum number of tags to display in your tag cloud';

$string['enablemultipleblogs1'] = 'Multiple journals';
$string['enablemultipleblogsdescription1']  = 'By default, you have one journal. If you would like to keep more than one journal, turn this option on.';

$string['hiderealname'] = 'Hide real name';
$string['hiderealnamedescription'] = 'Check this box if you have set a display name and do not want other users to be able to find you by your real name in user searches.';

$string['showhomeinfo2'] = 'Dashboard information';
$string['showhomeinfodescription1'] = 'Display information about how to use %s on the dashboard.';

$string['showprogressbar'] = 'Profile completion progress bar';
$string['showprogressbardescription'] = 'Display progress bar and tips on how to complete your %s profile.';

$string['prefssaved']  = 'Preferences saved';
$string['prefsnotsaved'] = 'Failed to save your preferences.';

$string['maildisabled'] = 'Email disabled';
$string['disableemail'] = 'Disable email';
$string['maildisabledbounce'] =<<< EOF
Sending of email to your email address has been disabled as too many messages have been returned to the server.
Please check that your email account is working as expected before you re-enable email in you account preferences at %s.
EOF;
$string['maildisableddescription'] = 'Sending of email to your account has been disabled. You may <a href="%s">re-enable your email</a> from the account preferences page.';

$string['deleteaccountuser']  = 'Delete account of %s';
$string['deleteaccountdescription']  = 'If you delete your account, all your content will be deleted permanently. You cannot get it back. Your profile information and your pages will no longer be visible to other users. The content of any forum posts you have written will still be visible, but your name will no longer be displayed.';
$string['sendnotificationdescription']  = 'A notification will be sent to an administrator, who needs to approve the deletion of your account. If you request to delete your account, all your personal content will be deleted permanently. That means any files you uploaded, journal entries you wrote, and pages and collections you created will be deleted. You cannot get any of that back. If you uploaded files to groups, created journal entries and portfolios, and contributed to forums there, they will stay on the site, but your name will no longer be displayed.';
$string['pendingdeletionsince'] = 'Account pending deletion since %s';
$string['pendingdeletionadminemailsubject'] = "Account deletion request on %s";
$string['resenddeletionadminemailsubject'] = "Reminder of account deletion request on %s";
$string['canceldeletionadminemailsubject'] = "Cancellation of account deletion request on %s";
$string['pendingdeletionadminemailtext'] = "Hello Administrator,

User %s has requested the deletion of their account from the site.

You are listed as an administrator in an institution to which the user belongs. You can decide whether to approve or deny the deletion request. To do this, select the following link: %s

Details of the account deletion request follow:

Name: %s
Email: %s
Reason: %s

--
Regards,
The %s Team";
$string['pendingdeletionadminemailhtml'] = "<p>Hello Administrator,</p>
<p>User %s has requested the deletion of their account from the site.</p>
<p>You are listed as an administrator in an institution to which the user belongs. You can decide whether to approve or deny the deletion request. To do this, select the following link: <a href='%s'>%s</a></p>
<p>Details of the account deletion request follow:</p>
<p>Name: %s</p>
<p>Email: %s</p>
<p>Reason: %s</p>
<pre>--
Regards,
The %s Team</pre>";

$string['accountdeleted']  = 'Your account has been deleted.';
$string['resenddeletionnotification'] = 'Resend deletion notification';
$string['resenddeletionadminemailtext'] = "Hello Administrator,

This is a reminder that user %s has requested the deletion of their account from the site.

You are listed as an administrator in an institution to which the user belongs. You can decide whether to approve or deny the deletion request. To do this, select the following link: %s

Details of the account deletion request follow:

Name: %s
Email: %s
Message: %s

--
Regards,
The %s Team";
$string['resenddeletionadminemailhtml'] = "<p>Hello Administrator,</p>
<p>This is a reminder that user % has requested the deletion of their account from the site.</p>
<p>You are listed as an administrator in an institution to which the user belongs. You can decide whether to approve or deny the deletion request. To do this, select the following link: <a href='%s'>%s</a></p>
<p>Details of the account deletion request follow:</p>
<p>Name: %s</p>
<p>Email: %s</p>
<p>Message: %s</p>
<pre>--
Regards,
The %s Team</pre>";

$string['pendingdeletionemailsent'] = 'Sent notification to institution administrators';
$string['cancelrequest'] = 'Cancel request';
$string['deleterequestcanceled'] = 'The request to delete your user account has been cancelled.';
$string['canceldeletionrequest'] = 'Cancel deletion request';
$string['canceldeletionrequestconfirmation'] = 'This will cancel the request to the institution administrators for deleting the account of %s. Are you sure you want to continue?';
$string['canceldeletionadminemailtext'] = "Hello Administrator,

User %s has cancelled the request to delete their account from the site.

You are listed as an administrator in an institution to which the user belongs.

Details of the cancelled request follow:

Name: %s
Email: %s

--
Regards,
The %s Team";
$string['canceldeletionadminemailhtml'] = "<p>Hello Administrator,</p>
<p>User %s has cancelled the request to delete their account from the site.</p>
<p>You are listed as an administrator in an institution to which the user belongs.</p>
<p>Details of the cancelled request follow:</p>
<p>Name: %s</p>
<p>Email: %s</p>
<pre>--
Regards,
The %s Team</pre>";

$string['resizeonuploaduserdefault1'] = 'Resize large images on upload';
$string['resizeonuploaduserdefaultdescription2'] = '"Automatic resizing of images" is enabled by default. Images larger than the maximum dimensions will be resized when they are uploaded. You can disable this default setting for each image upload individually.';

$string['devicedetection'] = 'Device detection';
$string['devicedetectiondescription'] = 'Enable mobile device detection when browsing this site.';
$string['noprivacystatementsaccepted'] = 'This account has not accepted any current privacy statements.';
