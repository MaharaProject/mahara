<?php
/**
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['certificate1'] = 'SAML Service Provider signing and encryption certificate';
$string['manage_certificate1'] = 'This is the certificate generated as part of the SAML Service Provider <a href="%s">Metadata</a>.';
$string['nullprivatecert'] = "Could not generate or save the private key";
$string['nullpubliccert'] = "Could not generate or save the public certificate";
$string['defaultinstitution'] = 'Default institution';
$string['description'] = 'Authenticate against a SAML 2.0 Identity Provider service';
$string['disco'] = 'Identity Provider discovery';
$string['errorbadinstitution'] = 'Institution for connecting user not resolved';
$string['errorbadssphp'] = 'Invalid SimpleSAMLphp session handler: Must not be phpsession';
$string['errorbadssphpmetadata'] = 'Invalid SimpleSAMLphp configuration: No Identity Provider metadata configured';
$string['errorbadssphpspentityid'] = 'Invalid Service Provider entityId';
$string['errorretryexceeded'] = 'Maximum number of retries exceeded (%s): There is a problem with the identity service';
$string['errnosamluser'] = 'No user found';
$string['errorssphpsetup'] = 'SAML is not set up correctly. You Need to run "make ssphp" from the commandline first.';
$string['errorbadlib'] = 'The SimpleSAMLPHP library\'s "autoloader" file was not found at %s.<br>Make sure you install SimpleSAMLphp via "make ssphp" and the file is readable.';
$string['errornomcrypt'] = 'The PHP library "mcrypt" must be installed for auth/saml. Make sure you install and activate mcrypt, e.g.:<br>sudo apt-get install php5-mcrypt<br>sudo php5enmod mcrypt<br>Then restart your web server.';
$string['errornomemcache'] = 'A memcache server is needed for auth/saml. Either list the paths to your memcache servers in the $cfg->memcacheservers config variable or install memcache locally.<br>To install the PHP library "memcache" locally:<br>sudo apt-get install php5-memcache<br>sudo php5enmod memcache<br>Then restart you web server.';
$string['errorbadconfig'] = 'The SimpleSAMLPHP config directory %s is incorrect.';
$string['errorbadmetadata'] = 'Badly formed SAML metadata. Ensure XML contains one valid Identity Provider.';
$string['errorduplicateidp1'] = 'The Identity Provider "%s" is already in use by institution "%s". Ensure the XML contains one valid and unique Identity Provider.';
$string['errorbadinstitutioncombo'] = 'There is already an existing authentication instance with this institution attribute and institution value combination.';
$string['errormissinguserattributes1'] = 'You seem to be authenticated, but we did not receive the required user attributes. Please check that your Identity Provider releases the first name, surname, and email fields for SSO to %s or inform the administrator.';
$string['errorregistrationenabledwithautocreate1'] = 'An institution has registration enabled. For security reasons this excludes user auto-creation, unless you are using remote usernames.';
$string['errorremoteuser1'] = 'Matching on "remoteuser" is mandatory if "usersuniquebyusername" is turned off.';
$string['IdPSelection'] = 'Identity Provider selection';
$string['noidpsfound'] = 'No Identity Providers found';
$string['idpentityid'] = 'Identity Provider entity';
$string['idpprovider'] = 'Provider';
$string['institutionattribute'] = 'Institution attribute (contains "%s")';
$string['institutionidp'] = 'Institution Identity Provider SAML metadata';
$string['institutionvalue'] = 'Institution value to check against attribute';
$string['libchecks'] = 'Checking for correct libraries installed: %s';
$string['link'] = 'Link accounts';
$string['linkaccounts'] = 'Do you want to link the remote account "%s" with the local account "%s"?';
$string['loginlink'] = 'Allow users to link their own account';
$string['logintolink'] = 'Local login to %s to link to remote account';
$string['logintolinkdesc'] = '<p><b>You are currently connected with remote user "%s". Please log in with your local account to link them or register if you do not currently have an account on %s.</b></p>';
$string['logo'] = 'Logo';
$string['institutionregex'] = 'Do partial string match with institution shortname';
$string['login'] = 'SSO';
$string['notusable'] = 'Please install the SimpleSAMLPHP Service Provider libraries';
$string['obsoletesamlplugin'] = 'The auth/saml plugin needs to be reconfigured. Please update the plugin via the <a href="%s">plugin configuration</a> form.';
$string['obsoletesamlinstance'] = 'The SAML authentication instance <a href="%s">%s</a> for institution "%s" needs updating.';
$string['reallyreallysure1'] = "You are trying to save the Service Provider metadata for Mahara. This cannot be undone. Existing SAML logins will not work until you have reshared your new metadata with all Identity Providers.";
$string['reset'] = 'Reset metadata';
$string['resetmetadata'] = 'Reset the certificates for Mahara\'s metadata. This cannot be undone and you will have to reshare your metadata with the Identity Provider.';
$string['samlfieldforemail'] = 'SSO field for email';
$string['samlfieldforfirstname'] = 'SSO field for first name';
$string['samlfieldforsurname'] = 'SSO field for surname';
$string['spentityid'] = "Service Provider entityId";
$string['title'] = 'SAML';
$string['updateuserinfoonlogin'] = 'Update user details on login';
$string['userattribute'] = 'User attribute';
$string['simplesamlphplib'] = 'SimpleSAMLPHP lib directory';
$string['simplesamlphpconfig'] = 'SimpleSAMLPHP config directory';
$string['weautocreateusers'] = 'We auto-create users';
$string['remoteuser'] = 'Match username attribute to remote username';
$string['selectidp'] = 'Please select the Identity Provider that you wish to log in with.';
