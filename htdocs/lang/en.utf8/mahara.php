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

$string['about'] = 'About';
$string['home'] = 'Home';
$string['loggedouthome'] = 'Logged out Home';
$string['privacy'] = 'Privacy';
$string['sessiontimedout'] = 'Your session has timed out, please enter your login details to continue';
$string['sessiontimedoutpublic'] = 'Your session has timed out. You may <a href="?login">log in</a> to continue browsing';
$string['termsandconditions'] = 'Terms and conditions';
$string['uploadcopyright'] = 'Upload copyright';
$string['cancel'] = 'Cancel';
$string['sitecontentnotfound'] = '%s text not available';

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

// Admin menu editor
$string['adminfile'] = 'Admin file';
$string['externallink'] = 'External link';
$string['type'] = 'Type';
$string['name'] = 'Name';
$string['noadminfiles'] = 'No admin files';
$string['linkedto'] = 'Linked to';

// Admin site page editor
$string['discardpageedits']   = 'Discard your changes to this page?';
$string['pagecontents']       = 'Text to appear on the page';
$string['pagename']           = 'Page name';
$string['pagetext']           = 'Page text';

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
$string['creategroup']      = 'Add new group';
$string['groupname']        = 'Group name';
$string['groupdescription'] = 'Group description';

// my account
$string['account'] =  'My account';
$string['accountprefs'] = 'Account preferences';
$string['activityprefs'] = 'Activity preferences';
$string['changepassword'] = 'Change password';
$string['activity'] = 'Recent activity';

$string['emailname'] = 'Mahara System'; // robot! 
$string['save'] = 'Save';
?>
