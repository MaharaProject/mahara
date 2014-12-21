<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['webservice'] = 'Webservice';
$string['title'] = 'Webservice';
$string['description'] = 'Webservice only users Authenticated against Mahara\'s database';
$string['webservicesconfig'] = 'Configuration';
$string['webservicesconfigdesc'] = 'Here you can set up the varying webservice rules and enable/disable the services.';
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

// core webservices strings start here
$string['control_webservices'] = 'Switch ALL WebServices on or off: ';
$string['masterswitch'] = 'WebServices master switch';
$string['formatdate'] = '';
$string['protocolswitches'] = 'Switch On/Off Protocols';
$string['manage_protocols'] = 'Enable or Disable protocols that are to be supported by this installation:';
$string['protocol'] = 'Protocol';
$string['rest'] = 'REST';
$string['soap'] = 'SOAP';
$string['xmlrpc'] = 'XML-RPC';
$string['manage_certificates'] = 'These are the Certificates generated as part of <a href="%s">Networking</a> services. These values are used by Mahara when WS-Security Signatures, and Encryption are enabled for a particular wstoken or service user (Only XML-RPC and legacy MNet).';
$string['certificates'] = 'Networking Certificates';

$string['servicefunctiongroups'] = 'Manage Service Groups';
$string['servicegroup'] = 'Service Group: %s';
$string['sfgdescription'] = 'Build lists of functions into service groups, that can be allocated to users authorised for execution';
$string['name'] = 'Name';
$string['component'] = 'Component';
$string['functions'] = 'Functions';
$string['enableservice'] = 'Enable/disable Service';
$string['existingserviceusers'] = 'Cannot switch to token only users, as service users are linked to this service';
$string['existingtokens'] = 'Cannot switch to authorised service users as token users exist for this service';
$string['usersonly'] = 'Users only';
$string['tokensonly'] = 'Tokens only';
$string['switchtousers'] = 'Switch to Users';
$string['switchtotokens'] = 'Switch to Tokens';

$string['invalidservice'] = 'Invalid Service selected ';
$string['invalidfunction'] = 'Invalid Function selected ';
$string['tokengenerationfailed'] = 'Token generation failed';
$string['parametercannotbevalueoptional'] = 'Parameter cannot be value optional';
$string['invalidresponse'] = 'Invalid response ';
$string['invalidstatedetected'] = 'Invalid state detected ';
$string['codingerror'] = 'Coding error ';
$string['accessextfunctionnotconf'] = 'Access to external function not configured';
$string['missingfuncname'] = 'Missing function name';
$string['invalidretdesc'] = 'Invalid return description';
$string['invalidparamdesc'] = 'Invalid parameters description';
$string['missingretvaldesc'] = 'Missing returned values description';
$string['missingparamdesc'] = 'Missing parameters description';
$string['missingimplofmeth'] = 'Missing implementation method of "%s"';
$string['cannotfindimplfile'] = 'Cannot find file with external function implementation';

$string['apptokens'] = 'Application Connections';
$string['servicetokens'] = 'Manage Service Access Tokens';
$string['tokens'] = 'Service Access Tokens';
$string['users'] = 'Service Users';
$string['stdescription'] = 'Generate access tokens, and allocate users to Service Groups';
$string['username'] = 'User';
$string['owner'] = 'Owner';
$string['servicename'] = 'Service';
$string['generate'] = 'Generate token';
$string['invalidtoken'] = 'Invalid token selected';
$string['token'] = 'Token';
$string['tokenid'] = 'Token "%s"';
$string['invaliduserselected'] = 'Invalid user selected';
$string['invaliduserselectedinstitution'] = 'Invalid user for token institution selected from user search';
$string['noservices'] = 'No services configured';
$string['wssigenc'] = 'Enble WS-Security (XML-RPC Only)';
$string['titlewssigenc'] = 'WSSecurity';
$string['last_access'] = 'Last Access';
$string['verifier'] = 'Verifier Token';
$string['oob'] = 'Out Of Band OAuth Verification';
$string['oobinfo'] = 'The following is your verification code that will authorise your external application to have access to the approved data.  Please copy and paste the code into the associated application prompt to continue.';
$string['instructions'] = 'Instructions';

$string['webservicelogs'] = 'Web Service Logs';
$string['timetaken'] = 'Time taken';
$string['timelogged'] = 'When';
$string['info'] = 'Info';
$string['errors'] = 'Only Errors';

$string['manageserviceusers'] = 'Manage Service Users';
$string['sudescription'] = 'Allocate users to Service Groups and Institutions.  User must only be configured once.  All users must have the "webservice" authentication method.  The instance of the "webservice" authentication method of the user must be from an institution that they are a member of.';
$string['serviceuser'] = 'Service owner';
$string['serviceusername'] = 'Service owner "%s"';
$string['invalidserviceuser'] = 'Invalid Service User selected';
$string['nouser'] = 'Please select a user';
$string['duplicateuser'] = 'User account is already configured for Web Services';

$string['servicefunctionlist'] = 'Functions allocated against the service';
$string['sfldescription'] = 'Build the list of functions that are available to this service';
$string['functionname'] = 'Function name';
$string['classname'] = 'Class name';
$string['methodname'] = 'Method name';
$string['invalidinput'] = 'Invalid input';
$string['configsaved'] = 'Configuration saved';

$string['webservice'] = 'WebService';
$string['webservices'] = 'WebServices';
$string['webservices_title'] = 'Web Services Configuration';

$string['headingusersearchtoken'] = 'WebServices: Token user search';
$string['headingusersearchuser'] = 'WebServices: Service User search';
$string['usersearchinstructions'] = 'Select a user to associate with a webservice by clicking on the avatar.  You can search for users by clicking on the initials of their first and last names, or by entering a name in the search box. You can also enter an email address in the search box if you would like to search email addresses.';
$string['sha1fingerprint'] = 'SHA1 fingerprint: %s';
$string['md5fingerprint'] = 'MD5 fingerprint: %s';
$string['publickeyexpireson'] = 'Public key expires: %s';

// wsdoc
$string['function'] = 'Function';
$string['description'] = 'Description';
$string['component'] = 'Component';
$string['method'] = 'Method';
$string['class'] = 'Class';
$string['arguments'] = 'Arguments';
$string['invalidparameter'] = 'Invalid parameter value detected, execution can not continue. ';
$string['wsdoc'] = 'Web Service Documentation';

// testclient
$string['testclient'] = 'Web Services test client';
$string['tokenauth'] = 'Token';
$string['userauth'] = 'User';
$string['authtype'] = 'Authentication Type';
$string['sauthtype'] = 'AuthType';
$string['enterparameters'] = 'Enter Function Parameters';
$string['testclientinstructions'] = 'This is the interactive test client facility for Web Services.  This enables you to select a function and then execute it live against the current system.  Please be aware that ANY function you execute will run for real.';
$string['executed'] = 'Function call executed';
$string['invaliduserpass'] = 'Invalid wsusername/wspassword supplied for "%s"';
$string['invalidtoken'] = 'Invalid wstoken supplied';

//oauth server registry
$string['accesstokens'] = 'OAuth access tokens';
$string['notokens'] = 'You have no application tokens';
$string['oauth'] = 'OAuth';
$string['oauthv1sregister'] = 'OAuth Service Registration';
$string['userapplications'] = 'OAuth consumer keys';
$string['accessto'] = 'Access to';
$string['application'] = 'Application';
$string['callback'] = 'Callback URI';
$string['consumer_key'] = 'Consumer Key';
$string['consumer_secret'] = 'Consumer secret';
$string['add'] = 'Add';
$string['application'] = 'Application';
$string['oauthserverdeleted'] = 'Server deleted';
$string['oauthtokendeleted'] = 'Application token deleted';
$string['errorregister'] = 'Server registry failed';
$string['serverkey'] = 'Server key: %s';
$string['application_uri'] = 'Application URI';
$string['application_title'] = 'Application title';
$string['errorupdate'] = 'Update failed';
$string['erroruser'] = 'Invalid user specified';
$string['authorise'] = 'Authorise application access';
$string['oauth_access'] = 'This application will have access to your users details and resources';
$string['oauth_instructions'] = 'If you wish to grant access to this application then "Authorise application access".  If you do not want to grant access the press "Cancel".';

// running webservices messages
$string['accesstofunctionnotallowed'] = 'Access to the function %s() is not allowed. Please check if a service containing the function is enabled. In the service settings: if the service is restricted check that the user is listed. Still in the service settings check for IP restriction or if the service requires a capability.';
$string['accessexception'] = 'Access control exception';
$string['accessnotallowed'] = 'Access to web service not allowed';
$string['addfunction'] = 'Add function';
$string['addfunctions'] = 'Add functions';
$string['addservice'] = 'Add a new service: {$a->name} (id: {$a->id})';
$string['apiexplorer'] = 'API explorer';
$string['arguments'] = 'Arguments';
$string['authmethod'] = 'Authentication method';
$string['context'] = 'Context';
$string['createtoken'] = 'Create token';
$string['createtokenforuser'] = 'Create a token for a user';
$string['createuser'] = 'Create a specific user';
$string['default'] = 'Default to "%s"';
$string['deleteservice'] = 'Delete the service: {$a->name} (id: {$a->id})';
$string['doc'] = 'Documentation';
$string['documentation'] = 'web service documentation';
$string['enabledocumentation'] = 'Enable developer documentation';
$string['enableprotocols'] = 'Enable protocols';
$string['enablews'] = 'Enable web services';
$string['error'] = 'Error: %s';
$string['errorcodes'] = 'Error message';
$string['errorinvalidparam'] = 'The param "%s" is invalid.';
$string['errorinvalidparamsapi'] = 'Invalid external api parameter';
$string['errorinvalidparamsdesc'] = 'Invalid external api description';
$string['errorinvalidresponseapi'] = 'Invalid external api response';
$string['errorinvalidresponsedesc'] = 'Invalid external api response description';
$string['errormissingkey'] = 'Missing required key in single structure: %s';
$string['errornotemptydefaultparamarray'] = 'The web service description parameter named \'%s\' is an single or multiple structure. The default can only be empty array. Check web service description.';
$string['erroronlyarray'] = 'Only arrays accepted.';
$string['erroroptionalparamarray'] = 'The web service description parameter named \'%s\' is an single or multiple structure. It can not be set as VALUE_OPTIONAL. Check web service description.';
$string['errorresponsemissingkey'] = 'Error in response - Missing following required key in a single structure: %s';
$string['errorscalartype'] = 'Scalar type expected, array or object received.';
$string['errorunexpectedkey'] = 'Unexpected keys (%s) detected in parameter array.';
$string['execute'] = 'Execute';
$string['expires'] = 'Expires';
$string['externalservice'] = 'External service';
$string['failedtolog'] = 'Failed to login';
$string['function'] = 'Function';
$string['generalstructure'] = 'General structure';
$string['information'] = 'Information';
$string['invalidaccount'] = 'Invalid web services account - check service user configuration';
$string['invalidextparam'] = 'Invalid external api parameter: %s';
$string['invalidextresponse'] = 'Invalid external api response: %s';
$string['invalidiptoken'] = 'Invalid token - your IP is not supported';
$string['invalidtimedtoken'] = 'Invalid token - token expired';
$string['invalidtoken'] = 'Invalid token - token not found';
$string['invalidtokensession'] = 'Invalid session based token - session not found or expired';
$string['iprestriction'] = 'IP restriction';
$string['key'] = 'Key';
$string['missingpassword'] = 'Missing password';
$string['missingusername'] = 'Missing username';
$string['notoken'] = 'The token list is empty.';
$string['onesystemcontrolling'] = 'One system controlling Mahara with a token';
$string['operation'] = 'Operation';
$string['optional'] = 'Optional';
$string['phpparam'] = 'XML-RPC (PHP structure)';
$string['potusers'] = 'Not authorised users';
$string['print'] = 'Print all';
$string['protocol'] = 'Protocol';
$string['removefunction'] = 'Remove';
$string['required'] = 'Required';
$string['resettokenconfirm'] = 'Do you really want to reset this web service key for <strong>{%s}</strong> on the service <strong>{%s}</strong>?';
$string['response'] = 'Response';
$string['restcode'] = 'REST';
$string['restexception'] = 'REST';
$string['restparam'] = 'REST (POST parameters)';
$string['restrictedusers'] = 'Authorised users only';
$string['fortokenusers'] = 'User Token Access';
$string['usertokens'] = 'Personal user tokens';
$string['serviceaccess'] = 'Service Access';
$string['gen'] = 'Generate';
$string['no_token'] = 'Token not generated';
$string['token_generated'] = 'Token generated';
$string['securitykey'] = 'Security key (token)';
$string['selectedcapability'] = 'Selected';
$string['selectspecificuser'] = 'Select a specific user';
$string['service'] = 'Service';
$string['serviceusers'] = 'Authorised users';
$string['simpleauthlog'] = 'Simple authentication login';
$string['step'] = 'Step';
$string['testclient'] = 'Web service test client';
$string['testclientdescription'] = '* The web service test client <strong>executes</strong> the functions for <strong>REAL</strong>. Do not test functions that you don\'t know. <br/>* All existing web service functions are not yet implemented into the test client. <br/>* In order to check that a user cannot access some functions, you can test some functions that you didn\'t allow.<br/>* To see clearer error messages set the debugging to <strong>{$a->mode}</strong> into {$a->atag}<br/>* Access the {$a->amfatag}.';
$string['testwithtestclient'] = 'Test the service';
$string['tokenauthlog'] = 'Token authentication';
$string['userasclients'] = 'Users as clients with token';
$string['validuntil'] = 'Valid until';
$string['wrongusernamepassword'] = 'Wrong username or password';
$string['institutiondenied'] = 'Access to institution denied';
$string['wsauthnotenabled'] = 'The web service authentication plugin is disabled.';
$string['wsdocumentation'] = 'Web service documentation';
$string['wspassword'] = 'Web service password';
$string['wsusername'] = 'Web service username';

// Web Service functions errors
$string['nooauth'] = 'Not enabled for OAuth';
$string['accessdenied'] = 'access denied';
$string['accessdeniedforinst'] = ' access denied for institution "%s"';
$string['accessdeniedforinstuser'] = ' access denied for institution "%s" with user "%s"';
$string['accessdeniedforinstgroup'] = ' access denied for institution "%s" on group "%s"';
$string['usernameexists'] = 'Username already exists "%s"';
$string['invalidauthtype'] = 'Invalid authentication type "%s"';
$string['invalidauthtypeuser'] = 'Invalid authentication type "%s" with user "%s"';
$string['instexceedmax'] = 'Institution exceeded max allowed "%s"';
$string['cannotdeleteaccount'] = 'cannot delete account that has been used and is not suspended. User id "%s"';
$string['nousernameorid'] = 'no username or id ';
$string['nousernameoridgroup'] = 'no username or id for group "%s"';
$string['invaliduser'] = 'invalid user "%s"';
$string['invaliduserid'] = 'invalid user id "%s"';
$string['invalidusergroup'] = 'invalid user "%s" for group "%s"';
$string['mustsetauth'] = 'must set auth and institution to update auth on user "%s"';
$string['invalidusername'] = 'Invalid username "%s"';
$string['invalidremoteusername'] = 'Invalid remote username "%s"';
$string['musthaveid'] = 'Must have id, userid or username';
$string['notauthforuseridinstitution'] = 'Not authorised for access to user id "%s" for institution "%s"';
$string['notauthforuseridinstitutiongroup'] = 'Not authorised for access to user id "%s" for institution "%s" to group "%s"';
$string['invalidfavourite'] = 'Invalid favourite "%s"';
$string['groupexists'] = 'Group already exists "%s"';
$string['instmustbeongroup'] = 'institution must be set on group "%s"';
$string['noname'] = 'no name or shortname specified';
$string['catinvalid'] = 'category "%s" invalid';
$string['invalidjointype'] = 'invalid join type combination "%s"';
$string['correctjointype'] = 'must select correct join type open, request, and/or controlled';
$string['groupeditroles'] = 'group edit roles specified "%s" must be one of: %s';
$string['invalidmemroles'] = 'Invalid group membership role "%s" for user "%s"';
$string['groupnotexist'] = 'Group "%s" does not exist';
$string['instmustset'] = 'institution must be set for "%s"';
$string['nogroup'] = 'no group specified';
$string['membersinvalidaction'] = 'invalid action "%s" for user "%s" on group "%s"';
