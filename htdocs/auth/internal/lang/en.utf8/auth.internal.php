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
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['internal'] = 'Internal';
$string['title'] = 'Internal';
$string['description'] = 'Authenticate against Mahara\'s database';

$string['completeregistration'] = 'Complete Registration';
$string['emailalreadytaken'] = 'This e-mail address has already registered here';
$string['iagreetothetermsandconditions'] = 'I agree to the Terms and Conditions';
$string['passwordformdescription'] = 'Your password must be at least six characters long and contain at least one digit and two letters';
$string['passwordinvalidform'] = 'Your password must be at least six characters long and contain at least one digit and two letters';
$string['registeredemailsubject'] = 'You have registered at %s';
$string['registeredemailmessagetext'] = 'Hi %s,

Thank you for registering an account on %s. Please follow this link to
complete the signup process:

%sregister.php?key=%s

The link will expire in 24 hours.

--
Regards,
The %s Team';
$string['registeredemailmessagehtml'] = '<p>Hi %s,</p>
<p>Thank you for registering an account on %s. Please follow this link
to complete the signup process:</p>
<p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p>
<p>The link will expire in 24 hours.</p>

<pre>--
Regards,
The %s Team</pre>';
$string['registeredok'] = '<p>You have successfully registered. Please check your e-mail account for instructions on how to activate your account</p>';
$string['registrationnosuchkey'] = 'Sorry, there does not seem to be a registration with this key. Perhaps you waited longer than 24 hours to complete your registration? Otherwise, it might be our fault.';
$string['registrationunsuccessful'] = 'Sorry, your registration attempt was unsuccessful. This is our fault, not yours. Please try again later.';
$string['usernamealreadytaken'] = 'Sorry, this username is already taken';
$string['usernameinvalidform'] = 'Usernames may contain letters, numbers and most common symbols, and must be from 3 to 30 characters in length.  Spaces are not allowed.';
$string['usernameinvalidadminform'] = 'Usernames may contain letters, numbers and most common symbols, and must be from 3 to 236 characters in length.  Spaces are not allowed.';
$string['youmaynotregisterwithouttandc'] = 'You may not register unless you agree to abide by the <a href="terms.php">Terms and Conditions</a>';

// pending institution registrations
$string['confirmcancelregistration'] = 'Are you sure you want to cancel this registration? Doing so will result in your request being removed from the system.';
$string['confirmemailsubject'] = 'Confirm email for registration at %s';
$string['confirmemailmessagetext'] = 'Hi %s,

Thank you for requesting to register an account on %s. Please select the
following link to confirm your email address. The institution administrator
will then be notified of your application. You will receive an email whether
your registration was approved or not.

%sregister.php?key=%s

The link will expire in 24 hours.

--
Regards,
The %s Team';
$string['confirmemailmessagehtml'] = '<p>Hi %s,</p>
<p>Thank you for registering an account on %s. Please select the following link to confirm your email address.
The institution adminstrator will be notified and your application will be considered for approval and you
will be notified of the outcome.</p>
<p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p>
<p>The link will expire in 24 hours.</p>

<pre>--
Regards,
The %s Team</pre>';
$string['emailconfirmedok'] = '<p>You have successfully confirmed your email. You will be notified with further registration details soon.</p>';
$string['registrationcancelledok'] = 'You have successfully cancelled your registration application.';
$string['registrationconfirm'] = 'Confirm registration?';
$string['registrationconfirmdescription'] = 'Registration must be approved by institution adminstrators.';
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
$string['registeredokawaitingemail'] = 'You have successfully submitted your application for registration.  An email should have been sent to you in order to confirm your email and continue the process.';
$string['registrationreason'] = 'Registration Reason';
$string['registrationreasondesc'] = 'The reason for requesting registration with your chosen institution and any other details you think might be useful for the administrator in processing your application.  The registration cannot be completed without this information.';

$string['pendingregistrationadminemailsubject'] = "New user registration for institution '%s' at %s.";
$string['pendingregistrationadminemailtext'] = "Hi %s,

A new user has requested to join the institution '%s'.

Because you are listed as an admin of this institution you need to approve or deny this registration request. To do this, select the following link: %s

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
<p>Because you are listed as an admin of this institution you need to approve or deny this registration request. To do this, select the following link: <a href='%s'>%s</a></p>
<p>Details of the registration request follows:</p>
<p>Name: %s</p>
<p>Email: %s</p>
<p>Registration reason:</p>
<p>%s</p>
<pre>--
Regards,
The %s Team</pre>";
