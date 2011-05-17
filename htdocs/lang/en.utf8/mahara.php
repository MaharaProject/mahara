<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

// General form strings
$string['add']     = 'Add';
$string['cancel']  = 'Cancel';
$string['delete']  = 'Delete';
$string['edit']    = 'Edit';
$string['editing'] = 'Editing';
$string['save']    = 'Save';
$string['submit']  = 'Submit';
$string['update']  = 'Update';
$string['change']  = 'Change';
$string['send']    = 'Send';
$string['go']      = 'Go';
$string['default'] = 'Default';
$string['upload']  = 'Upload';
$string['complete']  = 'Complete';
$string['Failed']  = 'Failed';
$string['loading'] = 'Loading ...';
$string['showtags'] = 'Show my tags';
$string['errorprocessingform'] = 'There was an error with submitting this form. Please check the marked fields and try again.';
$string['description'] = 'Description';
$string['remove']  = 'Remove';
$string['Close'] = 'Close';
$string['Help'] = 'Help';
$string['applychanges'] = 'Apply changes';

$string['no']     = 'No';
$string['yes']    = 'Yes';
$string['none']   = 'None';
$string['at'] = 'at';
$string['From'] = 'From';
$string['To'] = 'To';
$string['All'] = 'All';

$string['enable'] = 'Enable';
$string['disable'] = 'Disable';
$string['show'] = 'Show';
$string['hide'] = 'Hide';
$string['pluginenabled'] = 'The plugin has is now visible.';
$string['plugindisabled'] = 'The plugin has been hidden.';
$string['pluginnotenabled'] = 'Plugin is hidden.  You must make the %s plugin visible first.';
$string['pluginexplainaddremove'] = 'Plugins in Mahara are always installed and can be accessed if users know the URLs and would otherwise have access. Rather than enabling and disabling the functionality, plugins are hidden or made visible by clicking on the \'Hide\' or \'Show\' links beside the plugins below.';
$string['pluginexplainartefactblocktypes'] = 'When hiding an \'artefact\' type plugin, the Mahara system also stops the display of the blocks related to it.';

$string['next']      = 'Next';
$string['nextpage']  = 'Next page';
$string['previous']  = 'Previous';
$string['prevpage']  = 'Previous page';
$string['first']     = 'First';
$string['firstpage'] = 'First page';
$string['last']      = 'Last';
$string['lastpage']  = 'Last page';

$string['accept'] = 'Accept';
$string['memberofinstitutions'] = 'Member of %s';
$string['reject'] = 'Reject';
$string['sendrequest'] = 'Send request';
$string['reason'] = 'Reason';
$string['select'] = 'Select';

// Tags
$string['tags'] = 'Tags';
$string['tagsdesc'] = 'Enter comma separated tags for this item.';
$string['tagsdescprofile'] = 'Enter comma separated tags for this item. Items tagged with \'profile\' are displayed in your sidebar.';
$string['youhavenottaggedanythingyet'] = 'You have not tagged anything yet';
$string['mytags'] = 'My Tags';
$string['Tag'] = 'Tag';
$string['itemstaggedwith'] = 'Items tagged with "%s"';
$string['numitems'] = '%s items';
$string['searchresultsfor'] = 'Search results for';
$string['alltags'] = 'All Tags';
$string['sortalpha'] = 'Sort tags alphabetically';
$string['sortfreq'] = 'Sort tags by frequency';
$string['sortresultsby'] = 'Sort results by:';
$string['date'] = 'Date';
$string['dateformatguide'] = 'Use the format YYYY/MM/DD';
$string['datetimeformatguide'] = 'Use the format YYYY/MM/DD HH:MM';
$string['filterresultsby'] = 'Filter results by:';
$string['tagfilter_all'] = 'All';
$string['tagfilter_file'] = 'Files';
$string['tagfilter_image'] = 'Images';
$string['tagfilter_text'] = 'Text';
$string['tagfilter_view'] = 'Pages';
$string['edittags'] = 'Edit Tags';
$string['selectatagtoedit'] = 'Select a tag to edit';
$string['edittag'] = 'Edit <a href="%s">%s</a>';
$string['editthistag'] = 'Edit This Tag';
$string['edittagdescription'] = 'All items in your portfolio tagged "%s" will be updated';
$string['deletetag'] = 'Delete <a href="%s">%s</a>';
$string['confirmdeletetag'] = 'Do you really want to delete this tag from everything in your portfolio?';
$string['deletetagdescription'] = 'Remove this tag from all items in your portfolio';
$string['tagupdatedsuccessfully'] = 'Tag updated successfully';
$string['tagdeletedsuccessfully'] = 'Tag deleted successfully';

$string['selfsearch'] = 'Search My Portfolio';

// Quota strings
$string['quota'] = 'Quota';
$string['quotausage'] = 'You have used <span id="quota_used">%s</span> of your <span id="quota_total">%s</span> quota.';

$string['updatefailed'] = 'Update failed';

$string['strftimenotspecified']  = 'Not specified';

// profile sideblock strings
$string['invitedgroup'] = 'group invitation';
$string['invitedgroups'] = 'group invitations';
$string['logout'] = 'Logout';
$string['pendingfriend'] = 'pending friend';
$string['pendingfriends'] = 'pending friends';
$string['profile'] = 'profile';
$string['views'] = 'Pages';

// Online users sideblock strings
$string['onlineusers'] = 'Online users';
$string['lastminutes'] = 'Last %s minutes';

// Links and resources sideblock
$string['linksandresources'] = 'Links and Resources';

// auth
$string['accesstotallydenied_institutionsuspended'] = 'Your institution %s, has been suspended.  Until it is unsuspended, you will be unable to log in to %s.
Please contact your institution for help.';
$string['accessforbiddentoadminsection'] = 'You are forbidden from accessing the administration section';
$string['accountdeleted'] = 'Sorry, your account has been deleted';
$string['accountexpired'] = 'Sorry, your account has expired';
$string['accountcreated'] = '%s: New account';
$string['accountcreatedtext'] = 'Dear %s,

A new account has been created for you on %s. Your details are as follows:

Username: %s
Password: %s

Visit %s to get started!

Regards, %s Site Administrator';
$string['accountcreatedchangepasswordtext'] = 'Dear %s,

A new account has been created for you on %s. Your details are as follows:

Username: %s
Password: %s

Once you log in for the first time, you will be asked to change your password.

Visit %s to get started!

Regards, %s Site Administrator';
$string['accountcreatedhtml'] = '<p>Dear %s</p>

<p>A new account has been created for you on <a href="%s">%s</a>. Your details are as follows:</p>

<ul>
    <li><strong>Username:</strong> %s</li>
    <li><strong>Password:</strong> %s</li>
</ul>

<p>Visit <a href="%s">%s</a> to get started!</p>

<p>Regards, %s Site Administrator</p>
';
$string['accountcreatedchangepasswordhtml'] = '<p>Dear %s</p>

<p>A new account has been created for you on <a href="%s">%s</a>. Your details are as follows:</p>

<ul>
    <li><strong>Username:</strong> %s</li>
    <li><strong>Password:</strong> %s</li>
</ul>

<p>Once you log in for the first time, you will be asked to change your password.</p>

<p>Visit <a href="%s">%s</a> to get started!</p>

<p>Regards, %s Site Administrator</p>
';
$string['accountexpirywarning'] = 'Account expiry warning';
$string['accountexpirywarningtext'] = 'Dear %s,

Your account on %s will expire within %s.

We recommend you save the contents of your portfolio using the Export tool. Instructions on using this feature may be found within the user guide.

If you wish to extend your account access or have any questions regarding the above, please feel free to contact us:

%s

Regards, %s Site Administrator';
$string['accountexpirywarninghtml'] = '<p>Dear %s,</p>
    
<p>Your account on %s will expire within %s.</p>

<p>We recommend you save the contents of your portfolio using the Export tool. Instructions on using this feature may be found within the user guide.</p>

<p>If you wish to extend your account access or have any questions regarding the above, please feel free to <a href="%s">Contact Us</a>.</p>

<p>Regards, %s Site Administrator</p>';
$string['institutionmembershipexpirywarning'] = 'Institution membership expiry warning';
$string['institutionmembershipexpirywarningtext'] = 'Dear %s,

Your membership of %s on %s will expire within %s.

If you wish to extend your membership or have any questions regarding the above, please feel free to contact us:

%s

Regards, %s Site Administrator';
$string['institutionmembershipexpirywarninghtml'] = '<p>Dear %s,</p>

<p>Your membership of %s on %s will expire within %s.</p>

<p>If you wish to extend your membership or have any questions regarding the above, please feel free to <a href="%s">Contact Us</a>.</p>

<p>Regards, %s Site Administrator</p>';
$string['institutionexpirywarning'] = 'Institution expiry warning';
$string['institutionexpirywarningtext_institution'] = 'Dear %s,

%s\'s membership of %s will expire within %s.

If you wish to extend your institution\'s membership or have any questions regarding the above, please feel free to contact us:

%s

Regards, %s Site Administrator';
$string['institutionexpirywarninghtml_institution'] = '<p>Dear %s,</p>

<p>%s\'s membership of %s will expire within %s.</p>

<p>If you wish to extend your institution\'s membership or have any questions regarding the above, please feel free to <a href="%s">Contact Us</a>.</p>

<p>Regards, %s Site Administrator</p>';
$string['institutionexpirywarningtext_site'] = 'Dear %s,

The institution \'%s\' will expire within %s.

You may wish to contact them to extend their membership of %s.

Regards, %s Site Administrator';
$string['institutionexpirywarninghtml_site'] = '<p>Dear %s,</p>

<p>The institution \'%s\' will expire within %s.</p>

<p>You may wish to contact them to extend their membership of %s.</p>

<p>Regards, %s Site Administrator</p>';
$string['accountinactive'] = 'Sorry, your account is currently inactive';
$string['accountinactivewarning'] = 'Account inactivity warning';
$string['accountinactivewarningtext'] = 'Dear %s,

Your account on %s will become inactive within %s.

Once inactive, you will not be able to log in until an administrator re-enables your account.

You can prevent your account from becoming inactive by logging in.

Regards, %s Site Administrator';
$string['accountinactivewarninghtml'] = '<p>Dear %s,</p>

<p>Your account on %s will become inactive within %s.</p>

<p>Once inactive, you will not be able to log in until an administrator re-enables your account.</p>

<p>You can prevent your account from becoming inactive by logging in.</p>

<p>Regards, %s Site Administrator</p>';
$string['accountsuspended'] = 'Your account has been suspended as of %s. The reason for your suspension is:<blockquote>%s</blockquote>';
$string['youraccounthasbeensuspended'] = 'Your account has been suspended';
$string['youraccounthasbeenunsuspended'] = 'Your account has been unsuspended';
$string['changepasswordinfo'] = 'You are required to change your password before you can proceed.';
$string['chooseusernamepassword'] = 'Choose your username and password';
$string['chooseusernamepasswordinfo'] = 'You need a username and password to log in to %s.  Please choose them now.';
$string['confirmpassword'] = 'Confirm password';
$string['javascriptnotenabled'] = 'Your browser does not have javascript enabled for this site. Mahara requires javascript to be enabled before you can log in';
$string['cookiesnotenabled'] = 'Your browser does not have cookies enabled, or is blocking cookies from this site. Mahara requires cookies to be enabled before you can log in';
$string['institution'] = 'Institution';
$string['loggedoutok'] = 'You have been logged out successfully';
$string['login'] = 'Login';
$string['loginfailed'] = 'You have not provided the correct credentials to log in. Please check your username and password are correct.';
$string['loginto'] = 'Log in to %s';
$string['newpassword'] = 'New Password';
$string['nosessionreload'] = 'Reload the page to log in';
$string['oldpassword'] = 'Current Password';
$string['password'] = 'Password';
$string['passworddescription'] = ' ';
$string['passwordhelp'] = 'The password you use to access the system';
$string['passwordnotchanged'] = 'You did not change your password, please choose a new password';
$string['passwordsaved'] = 'Your new password has been saved';
$string['passwordsdonotmatch'] = 'The passwords do not match';
$string['passwordtooeasy'] = 'Your password is too easy! Please choose a harder password';
$string['register'] = 'Register';
$string['sessiontimedout'] = 'Your session has timed out, please enter your login details to continue';
$string['sessiontimedoutpublic'] = 'Your session has timed out. You may <a href="%s">log in</a> to continue browsing';
$string['sessiontimedoutreload'] = 'Your session has timed out. Reload the page to log in again';
$string['username'] = 'Username';
$string['preferredname'] = 'Display Name';
$string['usernamedescription'] = ' ';
$string['usernamehelp'] = 'The username you have been given to access this system.';
$string['youaremasqueradingas'] = 'You are masquerading as %s.';
$string['yournewpassword'] = 'Your new password. Passwords must be at least six characters long and contain at least one digit and two letters';
$string['yournewpasswordagain'] = 'Your new password again';
$string['invalidsesskey'] = 'Invalid session key';
$string['cannotremovedefaultemail'] = 'You cannot remove your primary email address';
$string['emailtoolong'] = 'E-mail addresses cannot be longer that 255 characters';
$string['mustspecifyoldpassword'] = 'You must specify your current password';
$string['Site'] = 'Site';

// Misc. register stuff that could be used elsewhere
$string['emailaddress'] = 'Email address';
$string['emailaddressdescription'] = ' ';
$string['firstname'] = 'First name';
$string['firstnamedescription'] = ' ';
$string['lastname'] = 'Last name';
$string['lastnamedescription'] = ' ';
$string['studentid'] = 'ID number';
$string['displayname'] = 'Display name';
$string['fullname'] = 'Full name';
$string['registerwelcome'] = 'Welcome! To use this site you must first register.';
$string['registeragreeterms'] = 'You must also agree to the <a href="terms.php">terms and conditions</a>.';
$string['registerprivacy'] = 'The data we collect here will be stored according to our <a href="privacy.php">privacy statement</a>.';
$string['registerstep3fieldsoptional'] = '<h3>Choose an Optional Profile Image</h3><p>You have now successfully registered with %s! You may now choose an optional profile picture to be displayed as your avatar.</p>';
$string['registerstep3fieldsmandatory'] = '<h3>Fill Out Mandatory Profile Fields</h3><p>The following fields are required. You must fill them out before your registration is complete.</p>';
$string['registeringdisallowed'] = 'Sorry, you cannot register for this system at this time';
$string['membershipexpiry'] = 'Membership expires';
$string['institutionfull'] = 'The institution you have chosen is not accepting any more registrations.';
$string['registrationnotallowed'] = 'The institution you have chosen does not allow self-registration.';
$string['registrationcomplete'] = 'Thank you for registering at %s';
$string['language'] = 'Language';

// Forgot password
$string['cantchangepassword'] = 'Sorry, you are unable to change your password through this interface - please use your institution\'s interface instead';
$string['forgotusernamepassword'] = 'Forgotten your username or password?';
$string['forgotusernamepasswordtext'] = '<p>If you have forgotten your username or password, enter the email address listed in your profile and we will send you a message you can use to give yourself a new password.</p>
<p>If you know your username and have forgotten your password, you can also enter your username instead.</p>';
$string['lostusernamepassword'] = 'Lost Username/Password';
$string['emailaddressorusername'] = 'Email address or username';
$string['pwchangerequestsent'] = 'You should receive an e-mail shortly with a link you can use to change the password for your account';
$string['forgotusernamepasswordemailsubject'] = 'Username/Password details for %s';
$string['forgotusernamepasswordemailmessagetext'] = 'Dear %s,

A username/password request has been made for your account on %s.

Your username is %s.

If you wish to reset your password, please follow the link below:

%s

If you did not request a password reset, please ignore this email.

If you have any questions regarding the above, please feel free to contact us:

%s

Regards, %s Site Administrator';
$string['forgotusernamepasswordemailmessagehtml'] = '<p>Dear %s,</p>

<p>A username/password request has been made for your account on %s.</p>

<p>Your username is <strong>%s</strong>.</p>

<p>If you wish to reset your password, please follow the link below:</p>

<p><a href="%s">%s</a></p>

<p>If you did not request a password reset, please ignore this email.</p>

<p>If you have any questions regarding the above, please feel free to <a href="%s">contact us</a>.</p>

<p>Regards, %s Site Administrator</p>';
$string['forgotpassemailsendunsuccessful'] = 'Sorry, it appears that the e-mail could not be sent successfully. This is our fault, please try again shortly';
$string['forgotpassemailsentanyway'] = 'An e-mail was sent to the address stored for this user, but the address may not be correct or the recipient server is returning messages. Please contact your Mahara administrator to reset your password if you do not receive the e-mail.';
$string['forgotpassnosuchemailaddressorusername'] = 'The email address or username you entered doesn\'t match any users for this site';
$string['forgotpasswordenternew'] = 'Please enter your new password to continue';
$string['nosuchpasswordrequest'] = 'No such password request';
$string['passwordchangedok'] = 'Your password was successfully changed';

// Reset password when moving from external to internal auth.
$string['noinstitutionsetpassemailsubject'] = '%s: Membership of %s';
$string['noinstitutionsetpassemailmessagetext'] = 'Dear %s,

You are no longer a member of %s.
You may continue to use %s with your current username %s, but you must set a new password for your account.

Please follow the link below to continue the reset process.

%sforgotpass.php?key=%s

If you have any questions regarding the above, please feel free to contact
us.

%scontact.php

Regards, %s Site Administrator

%sforgotpass.php?key=%s';
$string['noinstitutionsetpassemailmessagehtml'] = '<p>Dear %s,</p>

<p>You are no longer a member of %s.</p>
<p>You may continue to use %s with your current username %s, but you must set a new password for your account.</p>

<p>Please follow the link below to continue the reset process.</p>

<p><a href="%sforgotpass.php?key=%s">%sforgotpass.php?key=%s</a></p>

<p>If you have any questions regarding the above, please feel free to <a href="%scontact.php">contact us</a>.</p>

<p>Regards, %s Site Administrator</p>

<p><a href="%sforgotpass.php?key=%s">%sforgotpass.php?key=%s</a></p>';
$string['debugemail'] = 'NOTICE: This e-mail was intended for %s <%s> but has been sent to you as per the "sendallemailto" configuration setting.';
$string['divertingemailto'] = 'Diverting email to %s';


// Expiry times
$string['noenddate'] = 'No end date';
$string['day']       = 'day';
$string['days']      = 'days';
$string['weeks']     = 'weeks';
$string['months']    = 'months';
$string['years']     = 'years';
// Boolean site option

// Site content pages
$string['sitecontentnotfound'] = '%s text not available';

// Contact us form
$string['name']                     = 'Name';
$string['email']                    = 'Email';
$string['subject']                  = 'Subject';
$string['message']                  = 'Message';
$string['messagesent']              = 'Your message has been sent';
$string['nosendernamefound']        = 'No sender name was submitted';
$string['emailnotsent']             = 'Failed to send contact email. Error message: "%s"';

// mahara.js
$string['namedfieldempty'] = 'The required field "%s" is empty';
$string['processing']     = 'Processing';
$string['requiredfieldempty'] = 'A required field is empty';
$string['unknownerror']       = 'An unknown error occurred (0x20f91a0)';

// menu
$string['home']        = 'Home';
$string['Content']     = 'Content';
$string['myportfolio'] = 'Portfolio';
$string['settings']    = 'Settings';
$string['myfriends']          = 'My Friends';
$string['findfriends']        = 'Find Friends';
$string['groups']             = 'Groups';
$string['mygroups']           = 'My Groups';
$string['findgroups']         = 'Find Groups';
$string['returntosite']       = 'Return to Site';
$string['siteadministration'] = 'Site Administration';
$string['institutionadministration'] = 'Institution Administration';

$string['unreadmessages'] = 'unread messages';
$string['unreadmessage'] = 'unread message';

$string['siteclosed'] = 'The site is temporarily closed for a database upgrade.  Site administrators may log in.';
$string['siteclosedlogindisabled'] = 'The site is temporarily closed for a database upgrade.  <a href="%s">Perform the upgrade now.</a>';

// footer
$string['termsandconditions'] = 'Terms and Conditions';
$string['privacystatement']   = 'Privacy Statement';
$string['about']              = 'About';
$string['contactus']          = 'Contact Us';

// my account
$string['account'] =  'Settings';
$string['accountprefs'] = 'Preferences';
$string['preferences'] = 'Preferences';
$string['activityprefs'] = 'Activity preferences';
$string['changepassword'] = 'Change password';
$string['notifications'] = 'Notifications';
$string['inbox'] = 'Inbox';
$string['gotoinbox'] = 'Go to inbox';
$string['institutionmembership'] = 'Institution Membership';
$string['institutionmembershipdescription'] = 'If you are a member of any institutions, they will be listed here. You may also request membership of institutions, and accept or decline pending membership invitations.';
$string['youareamemberof'] = 'You are a member of %s';
$string['leaveinstitution'] = 'Leave institution';
$string['reallyleaveinstitution'] = 'Are you sure you want to leave this institution?';
$string['youhaverequestedmembershipof'] = 'You have requested membership of %s';
$string['cancelrequest'] = 'Cancel request';
$string['youhavebeeninvitedtojoin'] = 'You have been invited to join %s';
$string['confirminvitation'] = 'Confirm invitation';
$string['joininstitution'] = 'Join institution';
$string['decline'] = 'Decline';
$string['requestmembershipofaninstitution'] = 'Request membership of an institution';
$string['optionalinstitutionid'] = 'Institution ID (optional)';
$string['institutionmemberconfirmsubject'] = 'Institution membership confirmation';
$string['institutionmemberconfirmmessage'] = 'You have been added as a member of %s.';
$string['institutionmemberrejectsubject'] = 'Institution membership request declined';
$string['institutionmemberrejectmessage'] = 'Your request for membership of %s was declined.';
$string['Memberships'] = 'Memberships';
$string['Requests'] = 'Requests';
$string['Invitations'] = 'Invitations';

$string['config'] = 'Config';

$string['sendmessage'] = 'Send message';
$string['spamtrap'] = 'Spam trap';
$string['formerror'] = 'There was an error processing your submission. Please try again.';
$string['formerroremail'] = 'Contact us at %s if you continue to have problems.';
$string['blacklisteddomaininurl'] = 'A url in this field contains the blacklisted domain %s.';

$string['notinstallable'] = 'Not installable!';
$string['installedplugins'] = 'Installed plugins';
$string['notinstalledplugins'] = 'Not installed plugins';
$string['plugintype'] = 'Plugin type';

$string['settingssaved'] = 'Settings saved';
$string['settingssavefailed'] = 'Failed to save settings';

$string['width'] = 'Width';
$string['height'] = 'Height';
$string['widthshort'] = 'w';
$string['heightshort'] = 'h';
$string['filter'] = 'Filter';
$string['expand'] = 'Expand';
$string['collapse'] = 'Collapse';
$string['more...'] = 'More ...';
$string['nohelpfound'] = 'There was no help found for this item';
$string['nohelpfoundpage'] = 'There was no help found for this page';
$string['couldnotgethelp'] = 'An error occurred trying to retrieve the help page';
$string['profileimage'] = 'Profile image';
$string['primaryemailinvalid'] = 'Your primary email address is invalid';
$string['addemail'] = 'Add email address';

// Search
$string['search'] = 'Search';
$string['searchusers'] = 'Search Users';
$string['Query'] = 'Query';
$string['query'] = 'Query';
$string['querydescription'] = 'The words to be searched for';
$string['result'] = 'result';
$string['results'] = 'results';
$string['Results'] = 'Results';
$string['noresultsfound'] = 'No results found';
$string['users'] = 'Users';

// artefact
$string['artefact'] = 'artefact';
$string['Artefact'] = 'Artefact';
$string['Artefacts'] = 'Artefacts';
$string['artefactnotfound'] = 'Artefact with id %s not found';
$string['artefactnotrendered'] = 'Artefact not rendered';
$string['nodeletepermission'] = 'You do not have permission to delete this artefact';
$string['noeditpermission'] = 'You do not have permission to edit this artefact';
$string['Permissions'] = 'Permissions';
$string['republish'] = 'Publish';
$string['view'] = 'Page';
$string['artefactnotpublishable'] = 'Artefact %s is not publishable in page %s';

$string['belongingto'] = 'Belonging to';
$string['allusers'] = 'All users';
$string['attachment'] = 'Attachment';

// Upload manager
$string['quarantinedirname'] = 'quarantine';
$string['clammovedfile'] = 'The file has been moved to a quarantine directory.';
$string['clamdeletedfile'] = 'The file has been deleted';
$string['clamdeletedfilefailed'] = 'The file could not be deleted';
$string['clambroken'] = 'Your administrator has enabled virus checking for file uploads but has misconfigured something.  Your file upload was NOT successful. Your administrator has been emailed to notify them so they can fix it.  Maybe try uploading this file later.';
$string['clamemailsubject'] = '%s :: Clam AV notification';
$string['clamlost'] = 'Clam AV is configured to run on file upload, but the path supplied to Clam AV, %s, is invalid.';
$string['clamfailed'] = 'Clam AV has failed to run.  The return error message was %s. Here is the output from Clam:';
$string['clamunknownerror'] = 'There was an unknown error with clam.';
$string['image'] = 'Image';
$string['filenotimage'] = 'The file you uploaded is not valid image. It must be a PNG, JPEG or GIF file.';
$string['uploadedfiletoobig'] = 'The file was too big. Please ask your administrator for more information.';
$string['notphpuploadedfile'] = 'The file was lost in the upload process. This should not happen, please contact your administrator for more information.';
$string['virusfounduser'] = 'The file you have uploaded, %s, has been scanned by a virus checker and found to be infected! Your file upload was NOT successful.';
$string['fileunknowntype'] = 'The type of your uploaded file could not be determined. Your file may be corrupted, or it could be a configuration problem. Please contact your administrator.';
$string['virusrepeatsubject'] = 'Warning: %s is a repeat virus uploader.';
$string['virusrepeatmessage'] = 'The user %s has uploaded multiple files which have been scanned by a virus checker and found to be infected.';

$string['phpuploaderror'] = 'An error occurred during file upload: %s (Error code %s)';
$string['phpuploaderror_1'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
$string['phpuploaderror_2'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
$string['phpuploaderror_3'] = 'The uploaded file was only partially uploaded.';
$string['phpuploaderror_4'] = 'No file was uploaded.';
$string['phpuploaderror_6'] = 'Missing a temporary folder.';
$string['phpuploaderror_7'] = 'Failed to write file to disk.';
$string['phpuploaderror_8'] = 'File upload stopped by extension.';
$string['adminphpuploaderror'] = 'A file upload error was probably caused by your server configuration.';

$string['youraccounthasbeensuspendedtext2'] = 'Your account at %s has been suspended by %s.'; // @todo: more info?
$string['youraccounthasbeensuspendedreasontext'] = "Your account at %s has been suspended by %s. Reason:\n\n%s";
$string['youraccounthasbeenunsuspendedtext2'] = 'Your account at %s has been unsuspended. You may once again log in and use the site.'; // can't provide a login link because we don't know how they log in - it might be by xmlrpc

// size of stuff
$string['sizemb'] = 'MB';
$string['sizekb'] = 'KB';
$string['sizegb'] = 'GB';
$string['sizeb'] = 'b';
$string['bytes'] = 'bytes';

// countries

$string['country.af'] = 'Afghanistan';
$string['country.ax'] = 'Åland Islands';
$string['country.al'] = 'Albania';
$string['country.dz'] = 'Algeria';
$string['country.as'] = 'American Samoa';
$string['country.ad'] = 'Andorra';
$string['country.ao'] = 'Angola';
$string['country.ai'] = 'Anguilla';
$string['country.aq'] = 'Antarctica';
$string['country.ag'] = 'Antigua and Barbuda';
$string['country.ar'] = 'Argentina';
$string['country.am'] = 'Armenia';
$string['country.aw'] = 'Aruba';
$string['country.au'] = 'Australia';
$string['country.at'] = 'Austria';
$string['country.az'] = 'Azerbaijan';
$string['country.bs'] = 'Bahamas';
$string['country.bh'] = 'Bahrain';
$string['country.bd'] = 'Bangladesh';
$string['country.bb'] = 'Barbados';
$string['country.by'] = 'Belarus';
$string['country.be'] = 'Belgium';
$string['country.bz'] = 'Belize';
$string['country.bj'] = 'Benin';
$string['country.bm'] = 'Bermuda';
$string['country.bt'] = 'Bhutan';
$string['country.bo'] = 'Bolivia';
$string['country.ba'] = 'Bosnia and Herzegovina';
$string['country.bw'] = 'Botswana';
$string['country.bv'] = 'Bouvet Island';
$string['country.br'] = 'Brazil';
$string['country.io'] = 'British Indian Ocean Territory';
$string['country.bn'] = 'Brunei Darussalam';
$string['country.bg'] = 'Bulgaria';
$string['country.bf'] = 'Burkina Faso';
$string['country.bi'] = 'Burundi';
$string['country.kh'] = 'Cambodia';
$string['country.cm'] = 'Cameroon';
$string['country.ca'] = 'Canada';
$string['country.cv'] = 'Cape Verde';
$string['country.ky'] = 'Cayman Islands';
$string['country.cf'] = 'Central African Republic';
$string['country.td'] = 'Chad';
$string['country.cl'] = 'Chile';
$string['country.cn'] = 'China';
$string['country.cx'] = 'Christmas Island';
$string['country.cc'] = 'Cocos (Keeling) Islands';
$string['country.co'] = 'Colombia';
$string['country.km'] = 'Comoros';
$string['country.cg'] = 'Congo';
$string['country.cd'] = 'Congo, The Democratic Republic of The';
$string['country.ck'] = 'Cook Islands';
$string['country.cr'] = 'Costa Rica';
$string['country.ci'] = 'Cote D\'ivoire';
$string['country.hr'] = 'Croatia';
$string['country.cu'] = 'Cuba';
$string['country.cy'] = 'Cyprus';
$string['country.cz'] = 'Czech Republic';
$string['country.dk'] = 'Denmark';
$string['country.dj'] = 'Djibouti';
$string['country.dm'] = 'Dominica';
$string['country.do'] = 'Dominican Republic';
$string['country.ec'] = 'Ecuador';
$string['country.eg'] = 'Egypt';
$string['country.sv'] = 'El Salvador';
$string['country.gq'] = 'Equatorial Guinea';
$string['country.er'] = 'Eritrea';
$string['country.ee'] = 'Estonia';
$string['country.et'] = 'Ethiopia';
$string['country.fk'] = 'Falkland Islands (Malvinas)';
$string['country.fo'] = 'Faroe Islands';
$string['country.fj'] = 'Fiji';
$string['country.fi'] = 'Finland';
$string['country.fr'] = 'France';
$string['country.gf'] = 'French Guiana';
$string['country.pf'] = 'French Polynesia';
$string['country.tf'] = 'French Southern Territories';
$string['country.ga'] = 'Gabon';
$string['country.gm'] = 'Gambia';
$string['country.ge'] = 'Georgia';
$string['country.de'] = 'Germany';
$string['country.gh'] = 'Ghana';
$string['country.gi'] = 'Gibraltar';
$string['country.gr'] = 'Greece';
$string['country.gl'] = 'Greenland';
$string['country.gd'] = 'Grenada';
$string['country.gp'] = 'Guadeloupe';
$string['country.gu'] = 'Guam';
$string['country.gt'] = 'Guatemala';
$string['country.gg'] = 'Guernsey';
$string['country.gn'] = 'Guinea';
$string['country.gw'] = 'Guinea-bissau';
$string['country.gy'] = 'Guyana';
$string['country.ht'] = 'Haiti';
$string['country.hm'] = 'Heard Island and Mcdonald Islands';
$string['country.va'] = 'Holy See (Vatican City State)';
$string['country.hn'] = 'Honduras';
$string['country.hk'] = 'Hong Kong';
$string['country.hu'] = 'Hungary';
$string['country.is'] = 'Iceland';
$string['country.in'] = 'India';
$string['country.id'] = 'Indonesia';
$string['country.ir'] = 'Iran, Islamic Republic of';
$string['country.iq'] = 'Iraq';
$string['country.ie'] = 'Ireland';
$string['country.im'] = 'Isle of Man';
$string['country.il'] = 'Israel';
$string['country.it'] = 'Italy';
$string['country.jm'] = 'Jamaica';
$string['country.jp'] = 'Japan';
$string['country.je'] = 'Jersey';
$string['country.jo'] = 'Jordan';
$string['country.kz'] = 'Kazakhstan';
$string['country.ke'] = 'Kenya';
$string['country.ki'] = 'Kiribati';
$string['country.kp'] = 'Korea, Democratic People\'s Republic of';
$string['country.kr'] = 'Korea, Republic of';
$string['country.kw'] = 'Kuwait';
$string['country.kg'] = 'Kyrgyzstan';
$string['country.la'] = 'Lao People\'s Democratic Republic';
$string['country.lv'] = 'Latvia';
$string['country.lb'] = 'Lebanon';
$string['country.ls'] = 'Lesotho';
$string['country.lr'] = 'Liberia';
$string['country.ly'] = 'Libyan Arab Jamahiriya';
$string['country.li'] = 'Liechtenstein';
$string['country.lt'] = 'Lithuania';
$string['country.lu'] = 'Luxembourg';
$string['country.mo'] = 'Macao';
$string['country.mk'] = 'Macedonia, The Former Yugoslav Republic of';
$string['country.mg'] = 'Madagascar';
$string['country.mw'] = 'Malawi';
$string['country.my'] = 'Malaysia';
$string['country.mv'] = 'Maldives';
$string['country.ml'] = 'Mali';
$string['country.mt'] = 'Malta';
$string['country.mh'] = 'Marshall Islands';
$string['country.mq'] = 'Martinique';
$string['country.mr'] = 'Mauritania';
$string['country.mu'] = 'Mauritius';
$string['country.yt'] = 'Mayotte';
$string['country.mx'] = 'Mexico';
$string['country.fm'] = 'Micronesia, Federated States of';
$string['country.md'] = 'Moldova, Republic of';
$string['country.mc'] = 'Monaco';
$string['country.mn'] = 'Mongolia';
$string['country.ms'] = 'Montserrat';
$string['country.ma'] = 'Morocco';
$string['country.mz'] = 'Mozambique';
$string['country.mm'] = 'Myanmar';
$string['country.na'] = 'Namibia';
$string['country.nr'] = 'Nauru';
$string['country.np'] = 'Nepal';
$string['country.nl'] = 'Netherlands';
$string['country.an'] = 'Netherlands Antilles';
$string['country.nc'] = 'New Caledonia';
$string['country.nz'] = 'New Zealand';
$string['country.ni'] = 'Nicaragua';
$string['country.ne'] = 'Niger';
$string['country.ng'] = 'Nigeria';
$string['country.nu'] = 'Niue';
$string['country.nf'] = 'Norfolk Island';
$string['country.mp'] = 'Northern Mariana Islands';
$string['country.no'] = 'Norway';
$string['country.om'] = 'Oman';
$string['country.pk'] = 'Pakistan';
$string['country.pw'] = 'Palau';
$string['country.ps'] = 'Palestinian Territory, Occupied';
$string['country.pa'] = 'Panama';
$string['country.pg'] = 'Papua New Guinea';
$string['country.py'] = 'Paraguay';
$string['country.pe'] = 'Peru';
$string['country.ph'] = 'Philippines';
$string['country.pn'] = 'Pitcairn';
$string['country.pl'] = 'Poland';
$string['country.pt'] = 'Portugal';
$string['country.pr'] = 'Puerto Rico';
$string['country.qa'] = 'Qatar';
$string['country.re'] = 'Reunion';
$string['country.ro'] = 'Romania';
$string['country.ru'] = 'Russian Federation';
$string['country.rw'] = 'Rwanda';
$string['country.sh'] = 'Saint Helena';
$string['country.kn'] = 'Saint Kitts and Nevis';
$string['country.lc'] = 'Saint Lucia';
$string['country.pm'] = 'Saint Pierre and Miquelon';
$string['country.vc'] = 'Saint Vincent and The Grenadines';
$string['country.ws'] = 'Samoa';
$string['country.sm'] = 'San Marino';
$string['country.st'] = 'Sao Tome and Principe';
$string['country.sa'] = 'Saudi Arabia';
$string['country.sn'] = 'Senegal';
$string['country.cs'] = 'Serbia and Montenegro';
$string['country.sc'] = 'Seychelles';
$string['country.sl'] = 'Sierra Leone';
$string['country.sg'] = 'Singapore';
$string['country.sk'] = 'Slovakia';
$string['country.si'] = 'Slovenia';
$string['country.sb'] = 'Solomon Islands';
$string['country.so'] = 'Somalia';
$string['country.za'] = 'South Africa';
$string['country.gs'] = 'South Georgia and The South Sandwich Islands';
$string['country.es'] = 'Spain';
$string['country.lk'] = 'Sri Lanka';
$string['country.sd'] = 'Sudan';
$string['country.sr'] = 'Suriname';
$string['country.sj'] = 'Svalbard and Jan Mayen';
$string['country.sz'] = 'Swaziland';
$string['country.se'] = 'Sweden';
$string['country.ch'] = 'Switzerland';
$string['country.sy'] = 'Syrian Arab Republic';
$string['country.tw'] = 'Taiwan, Province of China';
$string['country.tj'] = 'Tajikistan';
$string['country.tz'] = 'Tanzania, United Republic of';
$string['country.th'] = 'Thailand';
$string['country.tl'] = 'Timor-leste';
$string['country.tg'] = 'Togo';
$string['country.tk'] = 'Tokelau';
$string['country.to'] = 'Tonga';
$string['country.tt'] = 'Trinidad and Tobago';
$string['country.tn'] = 'Tunisia';
$string['country.tr'] = 'Turkey';
$string['country.tm'] = 'Turkmenistan';
$string['country.tc'] = 'Turks and Caicos Islands';
$string['country.tv'] = 'Tuvalu';
$string['country.ug'] = 'Uganda';
$string['country.ua'] = 'Ukraine';
$string['country.ae'] = 'United Arab Emirates';
$string['country.gb'] = 'United Kingdom';
$string['country.us'] = 'United States';
$string['country.um'] = 'United States Minor Outlying Islands';
$string['country.uy'] = 'Uruguay';
$string['country.uz'] = 'Uzbekistan';
$string['country.vu'] = 'Vanuatu';
$string['country.ve'] = 'Venezuela';
$string['country.vn'] = 'Viet Nam';
$string['country.vg'] = 'Virgin Islands, British';
$string['country.vi'] = 'Virgin Islands, U.S.';
$string['country.wf'] = 'Wallis and Futuna';
$string['country.eh'] = 'Western Sahara';
$string['country.ye'] = 'Yemen';
$string['country.zm'] = 'Zambia';
$string['country.zw'] = 'Zimbabwe';

$string['nocountryselected'] = 'No Country Selected';

// general stuff that doesn't really fit anywhere else
$string['system'] = 'System';
$string['done'] = 'Done';
$string['back'] = 'Back';
$string['backto'] = 'Back to %s';
$string['alphabet'] = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
$string['formatpostbbcode'] = 'You can format your post using BBCode. %sLearn more%s';
$string['Created'] = 'Created';
$string['Updated'] = 'Updated';
$string['Total'] = 'Total';
$string['Visits'] = 'Visits';
$string['after'] = 'after';
$string['before'] = 'before';

// import related strings (maybe separated later)
$string['importedfrom'] = 'Imported from %s';
$string['incomingfolderdesc'] = 'Files imported from other networked hosts';
$string['remotehost'] = 'Remote host %s';

$string['Copyof'] = 'Copy of %s';

// Profile views
$string['loggedinusersonly'] = 'Allow logged in users only';
$string['allowpublicaccess'] = 'Allow public access';
$string['thisistheprofilepagefor'] = 'This is the profile page for %s';
$string['viewmyprofilepage']  = 'View profile page';
$string['editmyprofilepage']  = 'Edit profile page';
$string['usersprofile'] = "%s's Profile";
$string['profiledescription'] = 'Your profile page is what others see when they click on your name or profile picture';

// Dashboard views
$string['mydashboard'] = 'My Dashboard';
$string['editdashboard'] = 'Edit';
$string['usersdashboard'] = "%s's Dashboard";
$string['dashboarddescription'] = 'Your dashboard page is what you see on the homepage when you first log in. Only you have access to it';
$string['topicsimfollowing'] = "Topics I'm Following";
$string['recentactivity'] = 'Recent Activity';
$string['mymessages'] = 'My Messages';

$string['pleasedonotreplytothismessage'] = "Please do not reply to this message.";
$string['deleteduser'] = 'Deleted user';

$string['theme'] = 'Theme';
$string['choosetheme'] = 'Choose theme...';

// Home page info block
$string['Hide'] = 'Hide';
$string['createcollect'] = 'Create and Collect';
$string['createcollectsubtitle'] = 'Develop your portfolio';
$string['updateyourprofile'] = 'Update your <a href="%s">Profile</a>';
$string['uploadyourfiles'] = 'Upload your <a href="%s">Files</a>';
$string['createyourresume'] = 'Create your <a href="%s">Resume</a>';
$string['publishablog'] = 'Publish a <a href="%s">Journal</a>';
$string['Organise'] = 'Organise';
$string['organisesubtitle'] = 'Showcase your portfolio';
$string['organisedescription'] = 'Organise your portfolio into <a href="%s">pages.</a>  Create different pages for different audiences - you choose the elements to include.';
$string['sharenetwork'] = 'Share and Network';
$string['sharenetworksubtitle'] = 'Find friends and join groups';
$string['findfriendslinked'] = 'Find <a href="%s">Friends</a>';
$string['joingroups'] = 'Join <a href="%s">Groups</a>';
$string['sharenetworkdescription'] = '<br>Control your privacy.';
$string['howtodisable'] = 'You have hidden the information box.  You can control its visibility in <a href="%s">Settings</a>.';

// Blocktype
$string['setblocktitle'] = 'Set a block title';
