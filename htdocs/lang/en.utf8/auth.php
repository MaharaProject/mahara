<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

// IMAP
$string['host'] = 'Hostname or address';
$string['wwwroot'] = 'WWW root';
$string['protocol'] = 'Protocol';
$string['port'] = 'Port';
$string['changepasswordurl'] = 'Password-change URL';
$string['cannotremove']  = "We cannot remove this authentication plugin.\nIt is the only plugin that exists for this institution.";
$string['cannotremoveinuse']  = "We cannot remove this authentication plugin. It is being used by some people.\nYou must update their records before you can remove this plugin.";
$string['saveinstitutiondetailsfirst'] = 'Please save the institution details before configuring authentication plugins.';

$string['editauthority'] = 'Edit an authority';
$string['addauthority']  = 'Add an authority';

$string['updateuserinfoonlogin'] = 'Update personal information on login';
$string['updateuserinfoonlogindescription'] = 'Retrieve personal information from the remote server and update your local record each time the person logs in.';
$string['xmlrpcserverurl'] = 'XML-RPC server URL';
$string['ipaddress'] = 'IP address';
$string['shortname'] = 'Short name for your site';
$string['name'] = 'Site name';
$string['nodataforinstance1'] = 'Could not find data for authentication instance "%s".';
$string['authname'] = 'Authority name';
$string['weautocreateusers'] = 'We auto-create accounts';
$string['theyautocreateusers'] = 'They auto-create accounts';
$string['parent'] = 'Parent authority';
$string['wessoout'] = 'We SSO out';
$string['weimportcontent'] = 'We import content';
$string['weimportcontentdescription'] = '(some applications only)';
$string['theyssoin'] = 'They SSO in';
$string['authloginmsg2'] = "When you have not chosen a parent authority, enter a message to display when somebody tries to log in via the login form.";
$string['authloginmsgnoparent'] = "Enter a message to display when somebody tries to log in via the login form.";
$string['application'] = 'Application';
$string['cantretrievekey'] = 'An error occurred while retrieving the public key from the remote server.<br>Please ensure that the Application and WWW root fields are correct and that networking is enabled on the remote host.';
$string['ssodirection'] = 'SSO direction';
$string['active'] = 'Active';
$string['errorunabletologin'] = 'You are unable to login';
$string['errorcertificateinvalidwwwroot'] = 'This certificate claims to be for %s, but you are trying to use it for %s.';
$string['errorcouldnotgeneratenewsslkey'] = 'Could not generate a new SSL key. Are you sure that both openssl and the PHP module for openssl are installed on this machine?';
$string['errnoauthinstances']   = 'We do not seem to have any authentication plugin instances configured for the host at %s.';
$string['errornotvalidsslcertificate'] = 'This is not a valid SSL certificate.';
$string['errnoxmlrpcinstances'] = 'We do not seem to have any XML-RPC authentication plugin instances configured for the host at %s.';
$string['errnoxmlrpcwwwroot']   = 'We do not have a record for any host at %s.';
$string['errnoxmlrpcuser1']      = "We were unable to authenticate you at this time. Possible reasons might be:

    * Your SSO session might have expired. Go back to the other application and click the link to sign into %s again.
    * You may not be allowed to SSO to %s. Please check with your administrator if you think you should be allowed to.";

$string['toomanytries'] = 'You have exceeded the maximum login attempts. This account has been locked for up to 5 minutes.';
$string['unabletosigninviasso'] = 'Unable to sign in via SSO.';
$string['xmlrpccouldnotlogyouin'] = 'Sorry, we could not log you in.';
$string['xmlrpccouldnotlogyouindetail1'] = 'Sorry, we could not log you into %s at this time. Please try again shortly. If the problem persists, contact your administrator.';

$string['requiredfields'] = 'Required profile fields';
$string['requiredfieldsset'] = 'Required profile fields set';
$string['primaryemaildescription'] = 'The primary email address. You will receive an email containing a clickable link – follow this to validate the address and log in to the system';
$string['validationprimaryemailsent'] = 'A validation email has been sent. Please click the link inside this to validate the address';
$string['noauthpluginconfigoptions'] = 'There are no configuration options associated with this plugin.';

$string['hostwwwrootinuse'] = 'WWW root already in use by another institution (%s).';

// Error messages for external authentication usernames
$string['duplicateremoteusername'] = 'This external authentication username is already in use by %s. External authentication usernames must be unique within an authentication method.';
$string['duplicateremoteusernameformerror'] = 'External authentication usernames must be unique within an authentication method.';
$string['cannotjumpasmasqueradeduser'] = 'You cannot jump to another application whilst masquerading as somebody else.';

// Shared warning messages.
$string['warninstitutionregistration'] = '$cfg->usersuniquebyusername is turned on but registration is allowed for an institution. For security reasons all institutions need to have \'Registration allowed\' turned off. To adjust this via the web interface you will need to temporarily set $cfg->usersuniquebyusername = false.';
$string['warninstitutionregistrationinstitutions'] = array(
    0 => "The following institution has registration enabled:\n  %2\$s",
    1 => "The following institutions have registration enabled:\n  %2\$s",
);
$string['warnmultiinstitutionsoff'] = '$cfg->usersuniquebyusername is turned on but the site option \'People allowed multiple institutions\' is off. This makes no sense, as people will then change institutions every time they log in from somewhere else. Please turn this setting on in "Administration → Configure site → Institution settings".';
$string['alternativelogins'] = 'Administration login';
$string['unabletosigninviasso'] = 'Unable to sign in via external authentication (SSO)';

$string['nullprivatecert'] = "Could not generate the private key";
$string['nullpubliccert'] = "Could not generate the public certificate";
