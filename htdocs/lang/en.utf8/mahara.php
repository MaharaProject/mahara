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
$string['add']    = 'Add';
$string['cancel'] = 'Cancel';
$string['delete'] = 'Delete';
$string['edit']   = 'Edit';
$string['save']   = 'Save';
$string['submit'] = 'Submit';
$string['update'] = 'Update';
$string['change'] = 'Change';

$string['no']     = 'no';
$string['yes']    = 'yes';
$string['none']   = 'none';

$string['nextpage']  = 'Next page';
$string['prevpage']  = 'Previous page';
$string['firstpage'] = 'First page';
$string['lastpage']  = 'Last page';

$string['mainmenu'] = 'Main menu';

// auth
$string['accessforbiddentoadminsection'] = 'You are forbidden from accessing the administration section';
$string['accountexpired'] = 'Sorry, your account has expired';
$string['accountsuspended'] = 'Your account has been suspeneded as of %s. The reason for your suspension is:<blockquote>%s</blockquote>';
$string['changepassword'] = 'Change Password';
$string['changepasswordinfo'] = 'You are required to change your password before you can proceed.';
$string['confirmpassword'] = 'Confirm password';
$string['cookiesnotenabled'] = 'Your browser does not have cookies enabled, or is blocking cookies from this site. Mahara requires cookies to be enabled before you can log in';
$string['institution'] = 'Institution';
$string['institutiondescription'] = 'Your institution';
$string['loggedoutok'] = 'You have been logged out successfully';
$string['login'] = 'Log In';
$string['logon'] = 'Log On';
$string['loginfailed'] = 'You have not provided the correct credentials to log in. Please check your username and password are correct.';
$string['loginto'] = 'Log in to %s';
$string['newpassword'] = 'New Password';
$string['oldpassword'] = 'Old Password';
$string['password'] = 'Password';
$string['passworddescription'] = 'Your password';
$string['passwordhelp'] = 'The password you use to access the system';
$string['passwordnotchanged'] = 'You did not change your password, please choose a new password';
$string['passwordsaved'] = 'Your new password has been saved';
$string['passwordsdonotmatch'] = 'The passwords do not match';
$string['passwordtooeasy'] = 'Your password is too easy! Please choose a harder password';
$string['register'] = 'Register';
$string['sessiontimedout'] = 'Your session has timed out, please enter your login details to continue';
$string['sessiontimedoutpublic'] = 'Your session has timed out. You may <a href="?login">log in</a> to continue browsing';
$string['username'] = 'Username';
$string['usernamedescription'] = 'Your username';
$string['usernamehelp'] = 'The username you have been given to access this system.';
$string['yournewpassword'] = 'Your new password';
$string['yournewpasswordagain'] = 'Your new password again';

// Misc. register stuff that could be used elsewhere
$string['emailaddress'] = 'Email address';
$string['emailaddressdescription'] = 'Your email address';
$string['firstname'] = 'First name';
$string['firstnamedescription'] = 'Your first name';
$string['lastname'] = 'Last name';
$string['lastnamedescription'] = 'Your last name';
$string['password2description'] = 'Your password again';
$string['registerdescription'] = 'Welcome! To use this site you must first register. You must also agree to the <a href="terms.php">terms and conditions</a>. The data we collect here will be stored according to our <a href="privacy.php">privacy statement</a>.';
$string['registeringdisallowed'] = 'Sorry, you cannot register for this system at this time';

// Forgot password
$string['cantchangepassword'] = 'Sorry, you are unable to change your password through this interface - please use your institution\'s interface instead';
$string['forgotpassword'] = 'Forgotten your password?';
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
$string['forgotpassnosuchemailaddress'] = 'Invalid email address entered';
$string['nosuchpasswordrequest'] = 'No such password request';
$string['passwordchangedok'] = 'Your password was successfully changed';

// Expiry times
$string['noenddate'] = 'No end date';
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
$string['submitcontactinformation'] = 'Submit contact information';
$string['nosendernamefound']        = 'No sender name was submitted';
$string['emailnotsent']             = 'Failed to send contact email. Error message: "%s"';
$string['contactinformationsent']   = 'Your contact information has been sent';

// mahara.js
$string['namedfieldempty'] = 'The required field "%s" is empty';
$string['processingform']     = 'Processing form';
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
$string['submitview'] = 'Submit View';
$string['submitviewquestion'] = 'If you submit this view for assessment, you will not be able to edit the view or any of its associated artefacts until your tutor has finished marking the view.  Are you sure you want to submit this view now?';
$string['viewdeleted'] = 'View deleted';
$string['views'] = 'Views';
$string['viewsubmitted'] = 'View submitted';
$string['viewsubmittedto'] = 'This view has been submitted to %s';

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

$string['belongingto'] = 'Belonging to';
$string['allusers'] = 'All users';

// view view
$string['addedtowatchlist'] = 'This %s has been added to your watchlist';
$string['addfeedbackfailed'] = 'Add feedback failed';
$string['addtowatchlist'] = 'Add %s to watchlist';
$string['addtowatchlistwithchildren'] = 'Add %s and children to watchlist';
$string['alreadyinwatchlist'] = 'This %s is already in your watchlist';
$string['complaint'] = 'Complaint';
$string['date'] = 'Date';
$string['feedbacksubmitted'] = 'Feedback submitted';
$string['makepublic'] = 'Make public';
$string['nopublicfeedback'] = 'No public feedback';
$string['notifysiteadministrator'] = 'Notify site administrator';
$string['placefeedback'] = 'Place feedback';
$string['print'] = 'Print';
$string['reportobjectionablematerial'] = 'Report objectionable material';
$string['reportsent'] = 'Your report has been sent';
$string['updatewatchlistfailed'] = 'Update of watchlist failed';
$string['view'] = 'view';
$string['View'] = 'View';

?>
