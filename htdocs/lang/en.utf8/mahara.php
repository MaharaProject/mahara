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

$string['sessiontimedout'] = 'Your session has timed out, please enter your login details to continue';
$string['sessiontimedoutpublic'] = 'Your session has timed out. You may <a href="?login">log in</a> to continue browsing';
$string['cancel'] = 'Cancel';

$string['nextpage'] = 'Next page';
$string['prevpage'] = 'Previous page';

// auth
$string['accountexpired'] = 'Sorry, your account has expired';
$string['accountsuspended'] = 'Your account has been suspeneded as of %s. The reason for your suspension is:<blockquote>%s</blockquote>';
$string['confirmpassword'] = 'Confirm password';
$string['loggedoutok'] = 'You have been logged out successfully';
$string['login'] = 'Log In';
$string['loginfailed'] = 'You have not provided the correct credentials to log in. Please check your username and password are correct.';
$string['password'] = 'Password';
$string['passworddesc'] = 'Your password';
$string['passwordnotchanged'] = 'You did not change your password, please choose a new password';
$string['passwordsaved'] = 'Your new password has been saved';
$string['passwordsdonotmatch'] = 'The passwords do not match';
$string['passwordtooeasy'] = 'Your password is too easy! Please choose a harder password';
$string['username'] = 'Username';
$string['usernamedesc'] = 'Your username';
$string['usernamehelp'] = 'The username you have been given to access this system.';

// Registration
$string['registeredemailsubject'] = 'You have registered at Mahara';
$string['registeredemailmessagetext'] = 'Congratulations!

You have successfully registered an account! Please follow this link to
complete the signup process:

' . get_config('wwwroot') . 'register.php?key=%s';
$string['registeredemailmessagehtml'] = '<p>Congratulations!</p>
<p>You have successfully registered an account! Please follow this link
to complete the signup process:</p>
<p><a href="' . get_config('wwwroot') . 'register.php?key=%s">'
. get_config('wwwroot') . 'register.php?key=%s</a></p>';
$string['registeredok'] = '<p>You have successfully registered. Please check your e-mail account for instructions on how to activate your account</p>';
$string['registrationnosuchkey'] = 'Sorry, there does not seem to be a registration with this key. Perhaps you waited longer than 24 hours to complete your registration? Otherwise, it might be our fault.';

// Forgot password
$string['pwchangerequestsent'] = 'You should receive an e-mail shortly with a link you can use to change the password for your account';
$string['forgotpassemailsubject'] = 'Change password request for Mahara';
$string['forgotpassemailmessagetext'] = 'Dear $fullname,

A request to reset your password has been received for your $sitename account.

Please follow the link below to continue the reset process.

' . get_config('wwwroot') . 'forgotpass.php?key=%s

If you did not request a password reset, please ignore this email.

If you have any questsions regarding the above, please feel free to contact
us.

' . get_config('wwwroot') . 'contact.php

Regards, $sitename Site Administrator

' . get_config('wwwroot') . 'forgotpass.php?key=%s';
$string['forgotpassemailmessagehtml'] = '<p>Dear $fullname,</p>

<p>A request to reset your password has been received for your $sitename account.</p>

<p>Please follow the link below to continue the reset process.</p>

<p><a href="' . get_config('wwwroot') . 'forgotpass.php?key=%s">' . get_config('wwwroot') . 'forgotpass.php?key=%s</a></p>

<p>If you did not request a password reset, please ignore this email.</p>

<p>If you have any questsions regarding the above, please feel free to <a href="' . get_config('wwwroot') . 'contact.php">contact us</a>.</p>

<p>Regards, $sitename Site Administrator</p>

<p><a href="' . get_config('wwwroot') . 'forgotpass.php?key=%s">' . get_config('wwwroot') . 'forgotpass.php?key=%s</a></p>';

// Site options
$string['allowpublicviews'] = 'Allow public views';
$string['allowpublicviewsdescription'] = 'If set to yes, views are accessable by the public.  If set to no, only logged in users will be able to look at views';
$string['artefactviewinactivitytime'] = 'Artefact view inactivity time';
$string['artefactviewinactivitytimedescription'] = 'The time after which an inactive view or artefact will be moved to the InactiveContent area';
$string['contactaddress'] = 'Contact address';
$string['contactaddressdescription'] = 'The email address to which messages from the Contact Us form will be sent';
$string['language'] = 'Language';
$string['sitelanguagedescription'] = 'The default language for the site';
$string['pathtoclam'] = 'Path to clam';
$string['pathtoclamdescription'] = 'The filesystem path to clamscan or clamdscan';
$string['sessionlifetime'] = 'Session lifetime';
$string['sessionlifetimedescription'] = 'Time in minutes after which an inactive logged in user will be automatically logged out';
$string['theme'] = 'Theme';
$string['sitethemedescription'] = 'The theme for the site';
$string['viruschecking'] = 'Virus checking';
$string['viruscheckingdescription'] = 'If checked, virus checking will be enabled for all uploaded files using ClamAV';
$string['updatesiteoptions'] = 'Update site options';
$string['siteoptionsset'] = 'Site options have been updated';
$string['setsiteoptionsfailed'] = 'Failed setting the %s option';
// Expiry times
$string['noenddate'] = 'No end date';
$string['days']      = 'days';
$string['weeks']     = 'weeks';
$string['months']    = 'months';
$string['years']     = 'years';
// Boolean site option
$string['no']        = 'no';
$string['yes']       = 'yes';

// Admin menu editor
//$string['menueditor']    = 'Menu editor';
$string['adminfile']     = 'Admin file';
$string['externallink']  = 'External link';
$string['type']          = 'Type';
$string['name']          = 'Name';
$string['noadminfiles']  = 'No admin files';
$string['linkedto']      = 'Linked to';
$string['add']           = 'Add';
$string['delete']        = 'Delete';
$string['edit']          = 'Edit';
$string['loggedinmenu']  = 'Logged in menu';
$string['loggedoutmenu'] = 'Logged out menu';

// Site content
$string['about']               = 'About';
$string['discardpageedits']    = 'Discard your changes to this page?';
$string['home']                = 'Home';
$string['loggedouthome']       = 'Logged out Home';
$string['privacy']             = 'Privacy statement';
$string['pagecontents']        = 'Text to appear on the page';
$string['pagename']            = 'Page name';
$string['pagetext']            = 'Page text';
$string['sitecontentnotfound'] = '%s text not available';
$string['termsandconditions']  = 'Terms and conditions';
$string['uploadcopyright']     = 'Upload copyright statement';

// Contact us form
$string['name']                     = 'Name';
$string['email']                    = 'Email';
$string['subject']                  = 'Subject';
$string['message']                  = 'Message';
$string['submitcontactinformation'] = 'Submit contact information';

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
$string['accountprefs'] = 'Account preferences';
$string['activityprefs'] = 'Activity preferences';
$string['changepassword'] = 'Change password';
$string['activity'] = 'Recent activity';

$string['emailname'] = 'Mahara System'; // robot! 
$string['save'] = 'Save';
$string['update'] = 'Update';
?>
