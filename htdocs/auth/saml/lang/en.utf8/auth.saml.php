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

$string['certificate'] = 'SAML SP Signing and Encryption Certificate';
$string['manage_certificate'] = 'This is the certificate generated as part of the SAML SP <a href="%s">Metadata</a>.';
$string['nullprivatecert'] = "Could not generate or save the private key";
$string['nullpubliccert'] = "Could not generate or save the public certificate";
$string['defaultinstitution'] = 'Default institution';
$string['description'] = 'Authenticate against a SAML 2.0 IdP service';
$string['disco'] = 'IdP Discovery';
$string['errorbadinstitution'] = 'Institution for connecting user not resolved';
$string['errorbadssphp'] = 'Invalid SimpleSAMLphp session handler - must not be phpsession';
$string['errorbadssphpmetadata'] = 'Invalid SimpleSAMLphp configuration - no IdP metadata configured';
$string['errorbadssphpspentityid'] = 'Invalid Service Provider EntityId';
$string['errorretryexceeded'] = 'Maximum number of retries exceeded (%s) - there must be a problem with the identity service';
$string['errnosamluser'] = 'No user found';
$string['errorssphpsetup'] = 'SAML not set up correctly. Need to first run "make ssphp" from the commandline';
$string['errorbadlib'] = 'SimpleSAMLPHP library\'s "autoloader" file not found at %s.<br>Make sure you install SimpleSAMLphp via "make ssphp" and the file is readable.';
$string['errornomcrypt'] = 'PHP library "mcrypt" must be installed for auth/saml. Make sure you install and activate mcrypt eg:<br>sudo apt-get install php5-mcrypt<br>sudo php5enmod mcrypt<br>Then restart webserver.';
$string['errornomemcache'] = 'A memcache server is needed for auth/saml. Either list the paths to your memcache servers in the $cfg->memcacheservers config variable or install memcache locally.<br>To install the PHP library "memcache" locally:<br>sudo apt-get install php5-memcache<br>sudo php5enmod memcache<br>Then restart webserver.';
$string['errorbadconfig'] = 'SimpleSAMLPHP config directory %s is incorrect.';
$string['errorbadcombo'] = 'You can only choose user auto-creation if you have not selected remoteuser.';
$string['errorbadmetadata'] = 'Badly formed SAML metadata.  Ensure XML contains one valid IdP.';
$string['errorduplicateidp'] = 'IdP (%s) already in use by another institution (%s).  Ensure XML contains one valid and unique IdP.';
$string['errorbadinstitutioncombo'] = 'There is already an existing authentication instance with this institution attribute and institution value combination.';
$string['errormissinguserattributes1'] = 'You seem to be authenticated, but we did not receive the required user attributes. Please check that your Identity Provider releases the first name, surname, and email fields for SSO to %s or inform the administrator.';
$string['errorregistrationenabledwithautocreate'] = 'An institution has registration enabled. For security reasons this excludes user auto-creation.';
$string['errorremoteuser'] = 'Matching on remoteuser is mandatory if usersuniquebyusername is turned off.';
$string['IdPSelection'] = 'IdP Selection';
$string['noidpsfound'] = 'No IdPs found';
$string['institutionattribute'] = 'Institution attribute (contains "%s")';
$string['institutionidp'] = 'Institution IdP SAML Metadata';
$string['institutionvalue'] = 'Institution value to check against attribute';
$string['libchecks'] = 'Checking for correct libraries installed: %s';
$string['link'] = 'Link accounts';
$string['linkaccounts'] = 'Do you want to link remote account %s with local account %s?';
$string['loginlink'] = 'Allow users to link own account';
$string['logintolink'] = 'Local login to %s to link to remote account';
$string['logintolinkdesc'] = '<p><b>You are currently connected with remote user %s. Please log in with your local account to link them together or register if you do not currently have an account on %s.</b></p>';
$string['institutionregex'] = 'Do partial string match with institution shortname';
$string['login'] = 'SSO';
$string['notusable'] = 'Please install the SimpleSAMLPHP SP libraries';
$string['reallyreallysure'] = "You are trying to save the SP metadata for Mahara - this cannot be undone and existing institution configured SAML logins will not work until you have reshared your new metadata with all IdPs";
$string['reset'] = 'Reset Metadata';
$string['resetmetadata'] = 'Reset the certificates for Maharas metadata - caution this cannot be undone and you will have to reshare your metadata with the IdP';
$string['samlfieldforemail'] = 'SSO field for email';
$string['samlfieldforfirstname'] = 'SSO field for first name';
$string['samlfieldforsurname'] = 'SSO field for surname';
$string['spentityid'] = "Service Provider EntityId";
$string['title'] = 'SAML';
$string['updateuserinfoonlogin'] = 'Update user details on login';
$string['userattribute'] = 'User attribute';
$string['simplesamlphplib'] = 'SimpleSAMLPHP lib directory';
$string['simplesamlphpconfig'] = 'SimpleSAMLPHP config directory';
$string['weautocreateusers'] = 'We auto-create users';
$string['remoteuser'] = 'Match username attribute to remote username';
$string['selectidp'] = 'Please select the Identity Provider that you wish to login with.';
