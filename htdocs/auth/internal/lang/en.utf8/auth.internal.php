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
