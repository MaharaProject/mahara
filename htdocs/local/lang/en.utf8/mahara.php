<?php
/**
 *
 * @package    mahara
 * @subpackage local
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
defined('INTERNAL') || die();

$string['accountcreatedtext'] = 'Dear %s,

A new account has been created for you on the Pharmacy Council %s site. The site is used for recording your annual recertification requirements. Your details are as follows:

Username: %s
Password: %s

Visit %s to get started!

Regards, the %s site administrator';
$string['accountcreatedchangepasswordtext'] = 'Dear %s,

A new account has been created for you on the Pharmacy Council %s site. The site is used for recording your annual recertification requirements. Your personal recertification portfolio will be available in your account by the end of the day.

Your details are as follows:

Username: %s
Password: %s

When you log in for the first time, you will be asked to change your password.

Visit %s to get started!

Regards, the %s site administrator';
$string['accountcreatedhtml'] = '<p>Dear %s</p>

<p>A new account has been created for you on the Pharmacy Council <a href="%s">%s</a> site. The site is used for recording your annual recertification requirements. Your details are as follows:</p>

<ul>
    <li><strong>Username:</strong> %s</li>
    <li><strong>Password:</strong> %s</li>
</ul>

<p>Visit <a href="%s">%s</a> to get started!</p>

<p>Regards, the %s site administrator</p>
';
$string['accountcreatedchangepasswordhtml'] = '<p>Dear %s</p>

<p>A new account has been created for you on the Pharmacy Council <a href="%s">%s</a> site. The site is used for recording your annual recertification requirements. Your personal recertification portfolio will be available in your account by the end of the day.</p>

<p>Your details are as follows:</p>

<ul>
    <li><strong>Username:</strong> %s</li>
    <li><strong>Password:</strong> %s</li>
</ul>

<p>When you log in for the first time, you will be asked to change your password.</p>

<p>Visit <a href="%s">%s</a> to get started!</p>

<p>Regards, the %s site administrator</p>
';

$string['youraccounthasbeensuspendedtextcron'] = 'Your account on %s has been suspended as a result of a change to your registration status on the Pharmacy Council public register.';
$string['pcnz_youraccounthasbeensuspendedreasontextcron'] = 'Change to your registration status on the Pharmacy Council public register';

$string['legal']              = 'Terms and conditions';

$string['createsubtitle'] = 'Add material to your portfolio';

$string['howto'] = 'How to';
$string['howtosubtitle'] = 'Find guidance and instructions';

$string['memberofinstitutions'] = 'Registrant, %s';
$string['youareamemberof'] = 'You are a registrant in %s.';

$string['reportobjectionablematerial'] = 'Contact the administrator about this page';

$string['forgotusernamepassword'] = 'Forgot your password?';
$string['forgotusernamepasswordtextprimaryemail'] = '<p>If you have forgotten your password, enter your email address or Pharmacy Council registration number, and we will send you a message you can use to give yourself a new password.</p>';
$string['forgotusernamepasswordemailsubject'] = 'Username and password details for %s';
$string['forgotusernamepasswordemailmessagetext'] = 'Dear %s,

A password request has been made for your account on %s.

Your username is %s.

If you wish to reset your password, please follow the link below:

%s

If you did not request a password reset, please ignore this email.

If you have any questions regarding the above, please feel free to contact us:

%s

Regards, the %s site administrator';
$string['forgotusernamepasswordemailmessagehtml'] = '<p>Dear %s,</p>

<p>A password request has been made for your account on %s.</p>

<p>Your username is <strong>%s</strong>.</p>

<p>If you wish to reset your password, please follow the link below:</p>

<p><a href="%s">%s</a></p>

<p>If you did not request a password reset, please ignore this email.</p>

<p>If you have any questions regarding the above, please feel free to <a href="%s">contact us</a>.</p>

<p>Regards, the %s site administrator</p>';
$string['lostusernamepassword'] = 'Lost password';
$string['emailaddressorusername'] = 'Email address or registration number';

$string['institutionmembership'] = 'Institutions';
$string['institutionmembershipdescription'] = 'If you are registered in any institution, they will be listed here. You may also request to join other institutions and accept or decline pending invitations.';
$string['youareamemberof'] = 'You are registered in institution "%s".';
$string['Memberships'] = 'Registrations';

$string['Help'] = 'Manual';
$string['helpfor'] = 'Manual for "%s"';

$string['pendingfriend'] = 'pending contact';
$string['pendingfriends'] = 'pending contacts';
$string['myfriends']          = 'My friends';

$string['yournewpassword1'] = 'Your new password. The password must be at least %s characters long. Passwords are case sensitive and must be different from your username. %s<br/>
For good security, consider using a passphrase. A passphrase is a sentence rather than a single word, e.g. your favourite quote or two (or more) of your favourite things, taking the above requirements for characters into account.';
$string['passworddescription1.ul'] = 'It must contain <ul><li>upper case letters [A-Z]</li><li>lower case letters [a-z]</li></ul>';
$string['passworddescription1.uln'] = 'It must contain at minimum <ul><li>1 upper case letter [A-Z]</li><li>1 lower case letter [a-z]</li><li>1 number [0-9]';
$string['passworddescription1.ulns'] = 'It must contain at minimum <ul><li>1 upper case letter [A-Z]</li><li>1 lower case letter [a-z]</li><li>1 number [0-9]</li><li>1 symbol, e.g. ! . @ # $ & * - _ </li></ul>';
