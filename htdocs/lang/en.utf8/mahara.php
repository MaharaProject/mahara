<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage lang
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

// General form strings
$string['add']     = 'Add';
$string['cancel']  = 'Cancel';
$string['delete']  = 'Delete';
$string['edit']    = 'Edit';
$string['save']    = 'Save';
$string['submit']  = 'Submit';
$string['update']  = 'Update';
$string['change']  = 'Change';
$string['go']      = 'Go';
$string['default'] = 'Default';
$string['upload']  = 'Upload';
$string['loading'] = 'Loading ...';
$string['errorprocessingform'] = 'There was an error with submitting this form. Please check the marked fields and try again.';

$string['no']     = 'No';
$string['yes']    = 'Yes';
$string['none']   = 'none';

$string['nextpage']  = 'Next page';
$string['prevpage']  = 'Previous page';
$string['firstpage'] = 'First page';
$string['lastpage']  = 'Last page';

$string['accept'] = 'Accept';
$string['reject'] = 'Reject';
$string['sendrequest'] = 'Send request';
$string['reason'] = 'Reason';
$string['select'] = 'Select';

// Quota strings
$string['quota'] = 'Quota';
$string['quotausage'] = 'You have used <span id="quota_used">%s</span> of your <span id="quota_total">%s</span> quota.';

$string['mainmenu'] = 'Main menu';
$string['updatefailed'] = 'Update failed';
$string['declinerequest'] = 'Decline request';

$string['strftimenotspecified']  = 'Not specified';

// auth
$string['accessforbiddentoadminsection'] = 'You are forbidden from accessing the administration section';
$string['accountdeleted'] = 'Sorry, your account has been deletd';
$string['accountexpired'] = 'Sorry, your account has expired';
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
$string['accountsuspended'] = 'Your account has been suspeneded as of %s. The reason for your suspension is:<blockquote>%s</blockquote>';
$string['youraccounthasbeensuspended'] = 'Your account has been suspeneded';
$string['youraccounthasbeenunsuspended'] = 'Your account has been unsuspeneded';
$string['changepassword'] = 'Change Password';
$string['changepasswordinfo'] = 'You are required to change your password before you can proceed.';
$string['confirmpassword'] = 'Confirm password';
$string['cookiesnotenabled'] = 'Your browser does not have cookies enabled, or is blocking cookies from this site. Mahara requires cookies to be enabled before you can log in';
$string['institution'] = 'Institution';
$string['loggedoutok'] = 'You have been logged out successfully';
$string['login'] = 'Login';
$string['loginfailed'] = 'You have not provided the correct credentials to log in. Please check your username and password are correct.';
$string['loginto'] = 'Log in to %s';
$string['newpassword'] = 'New Password';
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
$string['sessiontimedoutpublic'] = 'Your session has timed out. You may <a href="?login">log in</a> to continue browsing';
$string['username'] = 'Username';
$string['preferredname'] = 'Preferred Name';
$string['usernamedescription'] = ' ';
$string['usernamehelp'] = 'The username you have been given to access this system.';
$string['yournewpassword'] = 'Your new password';
$string['yournewpasswordagain'] = 'Your new password again';
$string['invalidsesskey'] = 'Invalid session key';
$string['cantremovedefaultemail'] = 'You cannot remove your primary email address';
$string['mustspecifyoldpassword'] = 'You must specify your current password';
$string['captchatitle'] = 'CAPTCHA Image';
$string['captchadescription'] = 'Enter the characters you see in the picture to the right. Letters are not case sensitive';
$string['captchaincorrect'] = 'Enter the letters as they are shown in the image';

// Misc. register stuff that could be used elsewhere
$string['emailaddress'] = 'Email address';
$string['emailaddressdescription'] = ' ';
$string['firstname'] = 'First name';
$string['firstnamedescription'] = ' ';
$string['lastname'] = 'Last name';
$string['lastnamedescription'] = ' ';
$string['registerstep1description'] = 'Welcome! To use this site you must first register. You must also agree to the <a href="terms.php">terms and conditions</a>. The data we collect here will be stored according to our <a href="privacy.php">privacy statement</a>.';
$string['registerstep3fieldsoptional'] = '<h3>Choose an Optional Profile Image</h3><p>You have now successfully registered with ' . get_config('sitename') . '! You may now choose an optional profile icon to be displayed as your avatar. This image cannot be larger than 300x300 pixels.</p>';
$string['registerstep3fieldsmandatory'] = '<h3>Fill Out Mandatory Profile Fields</h3><p>The following fields are required. You must fill them out before your registration is complete.</p>';
$string['registeringdisallowed'] = 'Sorry, you cannot register for this system at this time';

// Forgot password
$string['cantchangepassword'] = 'Sorry, you are unable to change your password through this interface - please use your institution\'s interface instead';
$string['forgotpassword'] = 'Forgotten your password?';
$string['forgotpasswordtext'] = 'If you have forgotten your password, enter below the primary email address you have listed in your Profile and we will send you a key you can use to give yourself a new password';
$string['passwordreminder'] = 'Password Reminder';
$string['pwchangerequestsent'] = 'You should receive an e-mail shortly with a link you can use to change the password for your account';
$string['forgotpassemailsubject'] = 'Change password request for %s';
$string['forgotpassemailmessagetext'] = 'Dear %s,

A request to reset your password has been received for your %s account.

Please follow the link below to continue the reset process.

' . get_config('wwwroot') . 'forgotpass.php?key=%s

If you did not request a password reset, please ignore this email.

If you have any questsions regarding the above, please feel free to contact
us.

' . get_config('wwwroot') . 'contact.php

Regards, %s Site Administrator

' . get_config('wwwroot') . 'forgotpass.php?key=%s';
$string['forgotpassemailmessagehtml'] = '<p>Dear %s,</p>

<p>A request to reset your password has been received for your %s account.</p>

<p>Please follow the link below to continue the reset process.</p>

<p><a href="' . get_config('wwwroot') . 'forgotpass.php?key=%s">' . get_config('wwwroot') . 'forgotpass.php?key=%s</a></p>

<p>If you did not request a password reset, please ignore this email.</p>

<p>If you have any questsions regarding the above, please feel free to <a href="' . get_config('wwwroot') . 'contact.php">contact us</a>.</p>

<p>Regards, %s Site Administrator</p>

<p><a href="' . get_config('wwwroot') . 'forgotpass.php?key=%s">' . get_config('wwwroot') . 'forgotpass.php?key=%s</a></p>';
$string['forgotpassemailsendunsuccessful'] = 'Sorry, it appears that the e-mail could not be sent successfully. This is our fault, please try again shortly';
$string['forgotpassnosuchemailaddress'] = 'The email address you entered doesn\'t match any users for this site';
$string['forgotpasswordenternew'] = 'Please enter your new password to continue';
$string['nosuchpasswordrequest'] = 'No such password request';
$string['passwordchangedok'] = 'Your password was successfully changed';

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
$string['sendmessage']              = 'Send message';
$string['nosendernamefound']        = 'No sender name was submitted';
$string['emailnotsent']             = 'Failed to send contact email. Error message: "%s"';

// mahara.js
$string['namedfieldempty'] = 'The required field "%s" is empty';
$string['processing']     = 'Processing';
$string['requiredfieldempty'] = 'A required field is empty';
$string['unknownerror']       = 'An unknown error occurred (0x20f91a0)';

// menu
$string['home']       = 'Home';
$string['mycontacts'] = 'My Contacts';

// footer
$string['termsandconditions'] = 'Terms and Conditions';
$string['privacystatement']   = 'Privacy Statement';
$string['about']              = 'About';
$string['contactus']          = 'Contact Us';
$string['myfriends']          = 'My Friends';
$string['myaddressbook']      = 'My Addressbook';
$string['mycommunities']      = 'My Communities';
$string['myownedcommunities'] = 'My Owned Communities';
$string['mygroups']           = 'My Groups';
$string['youareloggedinas']   = 'You are logged in as %s';
$string['unreadmessages'] = 'unread messages';
$string['unreadmessage'] = 'unread message';

// mycontacts

// mygroups
$string['creategroup']               = 'Add new group';
$string['canteditdontown']           = 'You can\'t edit this group because you don\'t own it';
$string['groupname']                 = 'Group name';
$string['groupmembers']              = 'Group members';
$string['savegroup']                 = 'Save group';
$string['groupsaved']                = 'Group saved';
$string['groupcreated']              = 'Group created';
$string['groupalreadyexists']        = 'A group by this name already exists';
$string['groupdescription']          = 'Group description';
$string['editgroup']                 = 'Edit group';
$string['membercount']               = 'Member count';
$string['confirmdeletegroup']        = 'Are you sure you want to delete this group?';
$string['cantdeletegroupdontown']    = 'You can\'t delete this group, you don\'t own it';
$string['deletegroupsuccessful']     = 'Group successfully deleted';

// my account
$string['account'] =  'My account';
$string['accountprefs'] = 'Preferences';
$string['preferences'] = 'Preferences';
$string['activityprefs'] = 'Activity preferences';
$string['watchlist'] = 'My watchlist';
$string['changepassword'] = 'Change password';
$string['activity'] = 'Recent activity';

// my views
$string['accessstartdate'] = 'Access start date';
$string['accessstopdate'] = 'Access end date';
$string['artefacts'] = 'Artefacts';
$string['createnewview'] = 'Create New View';
$string['deleteviewfailed'] = 'Delete view failed';
$string['deleteviewquestion'] = 'Do you really want to delete this view?';
$string['description'] = 'Description';
$string['editaccess'] = 'Edit Access';
$string['editview'] = 'Edit View';
$string['editviewinformation'] = 'Edit View Information';
$string['myviews'] = 'My Views';
$string['notownerofview'] = 'You are not the owner of this view';
$string['reallyaddaccesstoemptyview'] = 'Your view contains no artefacts.  Do you really want to give these users access to the view?';
$string['saveaccess'] = 'Save Access';
$string['submitview'] = 'Submit View';
$string['submitviewfailed'] = 'Submit view failed';
$string['submitviewquestion'] = 'If you submit this view for assessment, you will not be able to edit the view or any of its associated artefacts until your tutor has finished marking the view.  Are you sure you want to submit this view now?';
$string['viewaccesseditedsuccessfully'] = 'View access saved successfully';
$string['viewdeleted'] = 'View deleted';
$string['views'] = 'Views';
$string['viewsubmitted'] = 'View submitted';
$string['viewsubmittedto'] = 'This view has been submitted to %s';

// access levels
$string['public'] = 'Public';
$string['loggedin'] = 'Logged In Users';
$string['friends'] = 'Friends';
$string['communities'] = 'Communities';
$string['users'] = 'Users';

// view user
$string['fullname'] = 'Full name';
$string['displayname'] = 'Display name';
$string['studentid'] = 'ID number';
$string['inviteusertojoincommunity'] = 'Invite this user to join a community';
$string['addusertocommunity'] = 'Add this user to a community';

$string['emailname'] = 'Mahara System'; // robot! 

$string['config'] = 'Config';

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
$string['profileimage'] = 'Profile image';
$string['primaryemailinvalid'] = 'Your primary email address is invalid';
$string['addemail'] = 'Add email address';

// Search
$string['search'] = 'Search';
$string['advancedsearch'] = 'Advanced search';
$string['query'] = 'Query';
$string['querydescription'] = 'The words to be searched for';
$string['results'] = 'Results';
$string['noresultsfound'] = 'No results found';

// artefact
$string['artefact'] = 'artefact';
$string['Artefact'] = 'Artefact';
$string['artefactnotfound'] = 'Artefact with id %s not found';
$string['created'] = 'Created';
$string['lastmodified'] = 'Last modified';
$string['owner'] = 'Owner';
$string['size'] = 'Size';
$string['title'] = 'Title';
$string['type'] = 'Type';

$string['belongingto'] = 'Belonging to';
$string['allusers'] = 'All users';

// view view
$string['addedtowatchlist'] = 'This %s has been added to your watchlist';
$string['addfeedbackfailed'] = 'Add feedback failed';
$string['addtowatchlist'] = 'Add %s to watchlist';
$string['addtowatchlistwithchildren'] = 'Add entire %s contents to watchlist';
$string['alreadyinwatchlist'] = 'This %s is already in your watchlist';
$string['attachedfileaddedtofolder'] = "The attached file %s has been added to your '%s' folder.";
$string['attachfile'] = "Attach file";
$string['complaint'] = 'Complaint';
$string['date'] = 'Date';
$string['feedback'] = 'Feedback';
$string['feedbackattachdirname'] = 'assessmentfiles';
$string['feedbackattachdirdesc'] = 'Files attached to view assessments';
$string['feedbackattachmessage'] = 'The attached file has been added to your %s folder';
$string['feedbackmadeprivate'] = 'Feedback changed to private';
$string['feedbackonthisartefactwillbeprivate'] = 'Feedback on this artefact will only be visible to the owner.';
$string['feedbackonviewbytutorofcommunity'] = 'Feedback on %s by %s of %s';
$string['feedbacksubmitted'] = 'Feedback submitted';
$string['makepublic'] = 'Make public';
$string['nopublicfeedback'] = 'No public feedback';
$string['notifysiteadministrator'] = 'Notify site administrator';
$string['placefeedback'] = 'Place feedback';
$string['print'] = 'Print';
$string['private'] = 'Private';
$string['makeprivate'] = 'Change to Private';
$string['reportobjectionablematerial'] = 'Report objectionable material';
$string['reportsent'] = 'Your report has been sent';
$string['updatewatchlistfailed'] = 'Update of watchlist failed';
$string['view'] = 'view';
$string['View'] = 'View';
$string['watchlistupdated'] = 'Your watchlist has been updated';

// communities
$string['createcommunity'] = 'Create Community';
$string['communitymemberrequests'] = 'Pending membership requests';
$string['addcommunity'] = 'Add new community';
$string['sendinvitation'] = 'Send invite';
$string['invitetocommunitysubject'] = 'You were invited to join a community';
$string['invitetocommunitymessage'] = '%s has invited you to join a community, \'%s\'.  Click on the link below for more information.';
$string['inviteuserfailed'] = 'Failed to invite the user';
$string['userinvited'] = 'Invite sent';
$string['addedtocommunitysubject'] = 'You were added to a community';
$string['addedtocommunitymessage'] = '%s has added you to a community, \'%s\'.  Click on the link below to see the community';
$string['adduserfailed'] = 'Failed to add the user';
$string['useradded'] = 'User added';
$string['editcommunity'] = 'Edit Community';
$string['savecommunity'] = 'Save Community';
$string['communitysaved'] = 'Community Saved Successfully';
$string['communityname'] = 'Community Name';
$string['invalidcommunity'] = 'The community doesn\'t exist';
$string['communitydescription'] = 'Community Description';
$string['membershiptype'] = 'Community Membership Type';
$string['membershiptype.controlled'] = 'Controlled Membership';
$string['membershiptype.invite']     = 'Invite Only';
$string['membershiptype.request']    = 'Request Membership';
$string['membershiptype.open']       = 'Open Membership';
$string['pendingmembers']            = 'Pending Members';
$string['reason']                    = 'Reason';
$string['approve']                   = 'Approve';
$string['reject']                    = 'Reject';
$string['communityalreadyexists']    = 'A Community by this name already exists';
$string['owner'] = 'Owner';
$string['members'] = 'Members';
$string['memberrequests'] = 'Membership requests';
$string['submittedviews'] = 'Submitted views';
$string['releaseview'] = 'Release view';
$string['tutor'] = 'Tutor';
$string['tutors'] = 'Tutors';
$string['member'] = 'Member';
$string['remove'] = 'Remove';
$string['updatemembership'] = 'Update membership';
$string['memberchangefailed'] = 'Failed to update some membership information';
$string['memberchangesuccess'] = 'Membership status changed successfully';
$string['viewreleasedsubject'] = 'Your view has been released';
$string['viewreleasedmessage'] = 'The view that you submitted to community %s has been released back to you by %s';
$string['viewreleasedsuccess'] = 'View was released successfully';
$string['communitymembershipchangesubject'] = 'Community membership: %s';
$string['communitymembershipchangemessagetutor'] = 'You have been promoted to a tutor in this community';
$string['communitymembershipchangemessagemember'] = 'You have been demoted from a tutor in this community';
$string['communitymembershipchangemessageremove'] = 'You have been removed from this community';
$string['communitymembershipchangemessagedeclinerequest'] = 'Your request to join this community has been declined';
$string['communitymembershipchangedmessageaddedtutor'] = 'You have been added as a tutor in this community';
$string['communitymembershipchangedmessageaddedmember'] = 'You have been added as a member in this community';
$string['leavecommunity'] = 'Leave this community';
$string['joincommunity'] = 'Join this community';
$string['requestjoincommunity'] = 'Request to join this community';
$string['communityhaveinvite'] = 'You have been invited to join this community';
$string['communitynotinvited'] = 'You have not been invited to join this community';
$string['communityinviteaccepted'] = 'Invite accepted successfully! You are now a community member';
$string['communityinvitedeclined'] = 'Invite declined successfully!';
$string['acceptinvitecommunity'] = 'Accept';
$string['declineinvitecommunity'] = 'Decline';
$string['leftcommunity'] = 'You have now left this community';
$string['leftcommunityfailed'] = 'Leaving community failed';
$string['couldnotleavecommunity'] = 'You cannot leave this community';
$string['joinedcommunity'] = 'You are now a community member';
$string['couldnotjoincommunity'] = 'You cannot join this community';
$string['communityrequestsent'] = 'Community membership request sent';
$string['couldnotrequestcommunity'] = 'Could not send community membership request';
$string['communityjointypeopen'] = 'Membership to this community is open';
$string['communityjointypecontrolled'] = 'Membership to this community  is controlled.  You cannot join this community';
$string['communityjointypeinvite'] = 'Membership to this community is invite only';
$string['communityjointyperequest'] = 'Membership to this community is by request only';
$string['communityrequestsubject'] = 'New community membership request';
$string['communityrequestmessage'] = '%s has sent a membership request to join the community %s';
$string['communityrequestmessagereason'] = '%s has sent a membership request to join the community %s with the reason %s.';


// friendslist
$string['reasonoptional'] = 'Reason (optional)';
$string['request'] = 'Request';

$string['friendformaddsuccess'] = 'Added %s to your friends list';
$string['friendformremovesuccess'] = 'Removed %s from your friends list';
$string['friendformrequestsuccess'] = 'Sent a friendship request to %s';
$string['friendformacceptsuccess'] = 'Accepted friend request';
$string['friendformrejectsuccess'] = 'Rejected friend request';

$string['addtofriendslist'] = 'Add to friends';
$string['requestfriendship'] = 'Request friendship';

$string['addedtofriendslistsubject'] = 'New friend';
$string['addedtofriendslistmessage'] = '%s added you as a friend! This means that %s is also on your friend list now too. '
    . ' Click on the link below to see their profile page';

$string['requestedfriendlistsubject'] = 'New friend request';
$string['requestedfriendlistmessage'] = '%s has requested that you add them as a friend.  '
    .' You can either do this from the link below, or from your friends list page';

$string['requestedfriendlistmessagereason'] = '%s has requested that you add them as a friend.'
    .' You can either do this from the link below, or from your friends list page'
    . 'Their reason was: ';

$string['removefromfriendslist'] = 'Remove from friends';
$string['confirmremovefriend'] = 'Are you sure you want to remove this user from your friends list?';
$string['removedfromfriendslistsubject'] = 'Removed from friends list';
$string['removedfromfriendslistmessage'] = '%s has removed you from their friends list.';
$string['removedfromfriendslistmessagereason'] = '%s has removed you from their friends list.  Their reason was: ';

$string['friendshipalreadyrequested'] = 'You have requested to be added to %s\'s friends list';
$string['friendshipalreadyrequestedowner'] = '%s has requested to be added to your friends list';
$string['rejectfriendshipreason'] = 'Reason for rejecting request';

$string['friendrequestacceptedsubject'] = 'Friend request accepted';
$string['friendrequestacceptedmessage'] = '%s has accepted your friend request and they have been added to your friends list'; 
$string['friendrequestrejectedsubject'] = 'Friend request rejected';
$string['friendrequestrejectedmessage'] = '%s has rejected your friend request.';
$string['friendrequestrejectedmessagereason'] = '%s has rejected your friend request.  Their reason was: ';

$string['currentfriends'] = 'Friends';
$string['pendingfriends'] = 'Pending friends';

$string['friendlistfailure'] = 'Failed to modify your friends list';
$string['userdoesntwantfriends'] = 'This user doesn\'t want any new friends';

$string['friend'] = 'Friend';
$string['profileicon'] = 'Profile Icon';

// general views stuff
$string['viewavailable'] = 'View available';
$string['viewsavailable'] = 'Views available';
$string['allviews'] = 'All views';

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
$string['virusfounduser'] = 'The file you have uploaded, %s, has been scanned by a virus checker and found to be infected! Your file upload was NOT successful.';
$string['virusrepeatsubject'] = 'Warning: %s is a repeat virus uploader.';
$string['virusrepeatmessage'] = 'The user %s has uploaded multiple files which have been scanned by a virus checker and found to be infected.';

$string['youraccounthasbeensuspended'] = 'Your account has been suspended';
$string['youraccounthasbeensuspendedtext'] = 'Your account has been suspended'; // @todo: more info?
$string['youraccounthasbeenunsuspended'] = 'Your account has been unsuspended';
$string['youraccounthasbeenunsuspendedtext'] = 'Your account has been unsuspended'; // @todo: more info?


?>
