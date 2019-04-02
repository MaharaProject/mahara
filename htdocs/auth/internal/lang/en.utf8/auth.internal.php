<?php
/**
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['internal'] = 'Internal';
$string['title'] = 'Internal';
$string['description'] = 'Authenticate against Mahara\'s database';

$string['completeregistration'] = 'Complete registration';
$string['emailalreadytaken'] = 'This email address has already been registered here.';
$string['emailalreadytakenbyothers'] = 'This email address has been taken by another user.';
$string['iagreetothetermsandconditions'] = 'I agree to the Terms and Conditions.';
$string['passwordinvalidform1'] = 'Your password must be at least %s characters long. Passwords are case sensitive and must be different from your username. Your password must contain %s.';
$string['recaptcharegistertitle'] = 'reCAPTCHA challenge';
$string['recaptcharegisterdesc2'] = 'Please tick the "I\'m not a robot" box if you want to continue.';
$string['registeredemailsubject'] = 'You have registered at %s';
$string['registeredemailmessagetext'] = 'Hi %s,

Thank you for registering an account on %s. Please follow this link to
complete the sign-up process:

%sregister.php?key=%s

The link will expire in 24 hours.

--
Regards,
The %s Team';
$string['registeredemailmessagehtml'] = '<p>Hi %s,</p>
<p>Thank you for registering an account on %s. Please follow this link
to complete the sign-up process:</p>
<p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p>
<p>The link will expire in 24 hours.</p>

<pre>--
Regards,
The %s Team</pre>';
$string['registeredok'] = '<p>You have successfully registered. Please check your email account for instructions on how to activate your account</p>';
$string['registrationexpiredkey'] = 'Sorry, your key has expired. Perhaps you waited longer than 24 hours to complete your registration? Otherwise, it might be our fault.';
$string['registrationnosuchid'] = 'Sorry, this registration key does not exist. Perhaps it is already activated?';
$string['registrationnosuchkey1'] = 'Sorry, we don\'t have a key that matches your link. Perhaps your email program mangled it?';
$string['registrationunsuccessful'] = 'Sorry, your registration attempt was unsuccessful. This is our fault, not yours. Please try again later.';
$string['usernamealreadytaken1'] = 'Sorry, you can\'t use this username. Please choose a new one.';
$string['usernameinvalidform'] = 'Usernames may contain letters, numbers and most common symbols and must be from 3 to 30 characters long. Spaces are not allowed.';
$string['usernameinvalidadminform'] = 'Usernames may contain letters, numbers and most common symbols and must be from 3 to 236 characters long. Spaces are not allowed.';
$string['youmaynotregisterwithouttandc'] = 'You may not register unless you agree to abide by the <a href="#user_acceptterms">Terms and Conditions</a>.';

// pending institution registrations
$string['confirmcancelregistration'] = 'Are you sure you want to cancel this registration? Doing so will result in your request being removed from the system.';
$string['confirmemailsubject'] = 'Confirm email for registration at %s';
$string['approvalemailsubject'] = 'Request for registration at %s received';
$string['approvalemailmessagetext'] = 'Hi %s,

Thank you for requesting an account on %s. The institution administrator
has been notified of your application. You will receive another email as
soon as your application has been considered.

--
Regards,
The %s Team';
$string['approvalemailmessagehtml'] = '<p>Hi %s,</p>
<p>Thank you for requesting an account on %s. The institution administrator
has been notified of your application. You will receive another email as
soon as your application has been considered.</p>

<pre>--
Regards,
The %s Team</pre>';
$string['emailconfirmedok'] = '<p>You have successfully confirmed your email. You will be notified with further registration details soon.</p>';
$string['registrationcancelledok'] = 'You have successfully cancelled your registration application.';
$string['registrationconfirm'] = 'Confirm registration?';
$string['registrationconfirmdescription'] = 'Registration must be approved by institution administrators.';
$string['registrationdeniedemailsubject'] = 'Registration attempt at %s denied.';
$string['registrationdeniedmessage'] = 'Hello %s,

We have received your application for joining our institution on %s and
decided not to grant you access.

If you think that this decision was incorrect, please get in touch with me
via email.

Regards
%s';
$string['registrationdeniedmessagereason'] = 'Hello %s,

We have received your application for joining our institution on %s and decided
not to grant you access for the following reason:

%s

If you think that this decision was incorrect, please get in touch with me
via email.

Regards
%s';
$string['registeredokawaitingemail2'] = 'You have successfully submitted your application for registration. The institution administrator has been notified, and you will receive an email as soon as your application has been processed.';
$string['registrationreason'] = 'Registration reason';
$string['registrationreasondesc1'] = 'The reason for requesting registration with your chosen institution and any other details you think might be useful for the administrator in processing your application.';

$string['pendingregistrationadminemailsubject'] = "New user registration for institution '%s' at %s.";
$string['pendingregistrationadminemailtext'] = "Hi %s,

A new user has requested to join the institution '%s'.

Because you are listed as an administrator of this institution you need to approve or deny this registration request. To do this, select the following link: %s

You will need to approve or deny this registration request within %s.

Details of the registration request follow:

Name: %s
Email: %s
Registration reason:
%s

--
Regards,
The %s Team";
$string['pendingregistrationadminemailhtml'] = "<p>Hi %s,</p>
<p>A new user has requested to join the institution '%s'.</p>
<p>Because you are listed as an administrator of this institution you need to approve or deny this registration request. To do this, select the following link: <a href='%s'>%s</a></p>
<p>You will need to approve or deny this registration request within %s.</p>
<p>Details of the registration request follows:</p>
<p>Name: %s</p>
<p>Email: %s</p>
<p>Registration reason:</p>
<p>%s</p>
<pre>--
Regards,
The %s Team</pre>";


// pending user account deletion
$string['userdeletionnosuchid'] = 'Sorry, this deletion request does not exist. Perhaps it has already been evaluated?';
$string['userdeletiondeniedmessage'] = 'Hello %s,

We have received your request to delete your user account on %s and
decided not to delete your data.

If you think that this decision was incorrect, please get in touch with me
via email.

Regards
%s';
$string['userdeletiondeniedmessagereason'] = 'Hello %s,

We have received your request to delete your user account on %s and decided
not to delete your data for the following reason:

%s

If you think that this decision was incorrect, please get in touch with me
via email.

Regards
%s';
$string['userdeletiondeniedemailsubject'] = 'User account deletion attempt at %s denied.';
$string['userdeletionemailsubject'] = 'Your user account was deleted from %s';
$string['userdeletionemailmessagetext'] = 'Hello %s,

Your user account was deleted successfully from %s.

--
Regards,
The %s Team';
$string['userdeletionemailmessagehtml'] = '<p>Hello %s,</p>
<p>Your user account was deleted successfully from %s.</p>

<pre>--
Regards,
The %s Team</pre>';
