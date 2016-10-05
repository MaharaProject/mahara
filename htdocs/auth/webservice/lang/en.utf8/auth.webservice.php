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

$string['webservice'] = 'Web services';
$string['title'] = 'Web services';
$string['description'] = 'Web services-only users authenticated against Mahara\'s database';
$string['webservicesconfig'] = 'Configuration';
$string['webservicesconfigdesc'] = 'Here you can set up the varying web services rules and enable or disable them.';
$string['webserviceconnectionsconfigdesc'] = 'Set up the connection objects that registered plugins can use for communication with external systems';
$string['completeregistration'] = 'Complete registration';
$string['emailalreadytaken'] = 'This email address has already registered here';
$string['iagreetothetermsandconditions'] = 'I agree to the Terms and Conditions.';
$string['passwordformdescription'] = 'Your password must be at least six characters long and contain at least one digit and two letters.';
$string['passwordinvalidform'] = 'Your password must be at least six characters long and contain at least one digit and two letters.';
$string['registeredemailsubject'] = 'You have registered at %s';
$string['registeredemailmessagetext'] = 'Hello %s,

Thank you for registering an account on %s. Please follow this link to
complete the signup process:

%sregister.php?key=%s

The link will expire in 24 hours.

--
Regards,
The %s Team';
$string['registeredemailmessagehtml'] = '<p>Hello %s,</p>
<p>Thank you for registering an account on %s. Please follow this link
to complete the signup process:</p>
<p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p>
<p>The link will expire in 24 hours.</p>

<pre>--
Regards,
The %s Team</pre>';
$string['registeredok'] = '<p>You have successfully registered. Please check your email account for instructions on how to activate your account.</p>';
$string['registrationnosuchkey'] = 'Sorry, there does not seem to be a registration with this key. Perhaps you waited longer than 24 hours to complete your registration? Otherwise, it might be our fault.';
$string['registrationunsuccessful'] = 'Sorry, your registration attempt was unsuccessful. This is our fault, not yours. Please try again later.';
$string['usernamealreadytaken'] = 'Sorry, this username is already taken.';
$string['usernameinvalidform'] = 'Usernames may contain letters, numbers and most common symbols, and must be from 3 to 30 characters in length. Spaces are not allowed.';
$string['usernameinvalidadminform'] = 'Usernames may contain letters, numbers and most common symbols, and must be from 3 to 236 characters in length. Spaces are not allowed.';
$string['youmaynotregisterwithouttandc'] = 'You may not register unless you agree to abide by the <a href="terms.php">Terms and Conditions</a>.';


$string['pluginconnections'] = 'Connection objects';
$string['pcdescription'] = 'Select a connection';
$string['instancelistempty'] = 'No connection objects for this institution.';

$string['addconnection'] = 'Add client connection';
$string['editconnection'] = 'Edit client connection';
$string['clientconnections'] = 'Client connection';
$string['plugin'] = 'Connection plugin';
$string['clienturl'] = 'Web service URL';
$string['password'] = 'Password';
$string['parameters'] = 'Fixed parameters to pass';
$string['certificate'] = 'XML-RPC partner certificate';
$string['enable'] = 'Connection enabled';
$string['json'] = 'JSON encoded';
$string['isfatal'] = 'Is fatal on error';
$string['type'] = 'Web service type';
$string['nameexists'] = "Name already in use";
$string['emptytoken'] = 'Token must be supplied';
$string['emptyuser'] = 'User must be supplied';
$string['emptyuserpass'] = 'Password must be supplied';
$string['emptycert'] = 'Certificate must be supplied';
$string['header'] = 'Header name';
$string['useheader'] = 'Put authentication in header';
$string['invalidauthtypecombination'] = 'Invalid authentication type selected for %s';
$string['emptycertextended'] = 'When using certificate based auth you must also enter a token or username/password';
$string['emptyoauthkey'] = 'Consumer key must be supplied for OAuth1.x';
$string['emptyoauthsecret'] = 'Secret must be supplied for OAuth1.x';
$string['consumer'] = 'Consumer key';
$string['secret'] = 'Secret';

// core webservices strings start here
$string['control_webservices'] = 'Switch web services on or off: ';
$string['webservice_requester_enabled_label'] = 'Web service requester master switch';
$string['webservice_requester_enabled_label2'] = 'Allow outgoing web service requests:';
$string['webservice_provider_enabled_label'] = 'Web service provider master switch';
$string['webservice_provider_enabled_label2'] = 'Accept incoming web service requests:';
$string['formatdate'] = '';
$string['webservice_master_switches'] = 'Enable web service functionality';
$string['connectionsswitch'] = 'Switch managed client connections on or off';
$string['manage_protocols1'] = 'Enable or disable protocols supported as a web services provider:';
$string['protocol'] = 'Protocol';
$string['rest'] = 'REST';
$string['soap'] = 'SOAP';
$string['xmlrpc'] = 'XML-RPC';
$string['manage_certificates'] = 'These are the certificates generated as part of <a href="%s">Networking</a> services. These values are used by Mahara when web services security signatures and encryption are enabled for a particular web services token or service user (only XML-RPC and legacy MNet).';
$string['certificates'] = 'Networking certificates';

$string['servicefunctiongroups'] = 'Manage service groups';
$string['servicegroup'] = 'Service group: %s';
$string['sfgdescription'] = 'Build lists of functions into service groups that can be allocated to users authorised for execution.';
$string['name'] = 'Name';
$string['component'] = 'Component';
$string['customservicegroup'] = '(Custom)';
$string['functions'] = 'Functions';
$string['enableservice'] = 'Enable or disable the service';
$string['restricteduserswarning'] = 'Warning: There are existing token users for this service, who may be unable to access it if you enable "%s".';
$string['tokenuserswarning'] = 'Warning: There are existing token users for this service, who may be unable to access it if you disable "%s".';
$string['usersonly'] = 'Users only';
$string['tokensonly'] = 'Tokens only';
$string['switchtousers'] = 'Switch to users';
$string['switchtotokens'] = 'Switch to tokens';

$string['invalidservice'] = 'Invalid service selected ';
$string['invalidfunction'] = 'Invalid function selected ';
$string['tokengenerationfailed'] = 'Token generation failed';
$string['parametercannotbevalueoptional'] = 'Parameter cannot be value optional';
$string['invalidresponse'] = 'Invalid response';
$string['invalidstatedetected'] = 'Invalid state detected';
$string['codingerror'] = 'Coding error';
$string['accessextfunctionnotconf'] = 'Access to external function not configured';
$string['missingfuncname'] = 'Missing function name';
$string['invalidretdesc'] = 'Invalid return description';
$string['invalidparamdesc'] = 'Invalid parameter description';
$string['missingretvaldesc'] = 'Missing returned values description';
$string['missingparamdesc'] = 'Missing parameter description';
$string['missingimplofmeth'] = 'Missing implementation method of "%s"';
$string['cannotfindimplfile'] = 'Cannot find file with external function implementation';
$string['servicenamemustbeunique'] = 'That name is already in use by another service group.';
$string['serviceshortnamemustbeunique'] = 'That short name is already in use by another service group.';

$string['apptokens'] = 'Application connections';
$string['connections'] = 'Connection manager';
$string['servicetokens'] = 'Manage service access tokens';
$string['tokens'] = 'Service access tokens';
$string['users'] = 'Service users';
$string['stdescription'] = 'Generate access tokens and allocate users to service groups';
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
$string['wssigenc'] = 'Enable web services security (XML-RPC Only)';
$string['titlewssigenc'] = 'Web services security';
$string['last_access'] = 'Last access';
$string['verifier'] = 'Verifier token';
$string['oob'] = 'Out-of-band OAuth verification';
$string['oobinfo'] = 'The following is your verification code that will authorise your external application to have access to the approved data. Please copy and paste the code into the associated application prompt to continue.';
$string['instructions'] = 'Instructions';

$string['webservicelogs'] = 'Web services logs';
$string['webservicelogsnav'] = 'Logs';
$string['timetaken'] = 'Time taken';
$string['timelogged'] = 'When';
$string['info'] = 'Info';
$string['errors'] = 'Only errors';

$string['manageserviceusers'] = 'Manage service users';
$string['sudescription'] = 'Allocate users to service groups and institutions. User must only be configured once. All users must have the "webservice" authentication method. The instance of the "webservice" authentication method of the user must be from an institution that they are a member of.';
$string['serviceuser'] = 'Service owner';
$string['serviceusername'] = 'Service owner "%s"';
$string['invalidserviceuser'] = 'Invalid service user selected';
$string['nouser'] = 'Please select a user';
$string['duplicateuser'] = 'User account is already configured for web services.';

$string['servicefunctionlist'] = 'Functions allocated against the service';
$string['sfldescription'] = 'Build the list of functions that are available to this service.';
$string['functionname'] = 'Function name';
$string['classname'] = 'Class name';
$string['methodname'] = 'Method name';
$string['invalidinput'] = 'Invalid input';
$string['configsaved'] = 'Configuration saved';

$string['webservices_title'] = 'Web services configuration';

$string['headingusersearchtoken'] = 'Web services: Token user search';
$string['headingusersearchuser'] = 'Web services: Service user search';
$string['usersearchinstructions'] = 'Select a user to associate with a web service by clicking on its avatar. You can search for users by clicking on the initials of their first and last names or by entering a name in the search box. You can also enter an email address in the search box if you would like to search email addresses.';
$string['sha1fingerprint'] = 'SHA1 fingerprint: %s';
$string['md5fingerprint'] = 'MD5 fingerprint: %s';
$string['publickeyexpireson'] = 'Public key expires: %s';

// wsdoc
$string['function'] = 'Function';
$string['wsdocdescription'] = 'Description';
$string['component'] = 'Component';
$string['method'] = 'Method';
$string['class'] = 'Class';
$string['arguments'] = 'Arguments';
$string['invalidparameter'] = 'Invalid parameter value detected; execution cannot continue. ';
$string['wsdoc'] = 'Web services documentation';

// testclient
$string['testclient'] = 'Web services test client';
$string['testclientnav'] = 'Test client';
$string['tokenauth'] = 'Token';
$string['userauth'] = 'User';
$string['certauth'] = 'Certificate';
$string['wsseauth'] = 'WSSE';
$string['oauth1auth'] = 'OAuth1.x';
$string['authtype'] = 'Authentication type';
$string['sauthtype'] = 'AuthType';
$string['enterparameters'] = 'Enter function parameters';
$string['testclientinstructions'] = 'This is the interactive test client facility for web services. This enables you to select a function and then execute it live against the current system. Please be aware that ANY function you execute will run for real.';
$string['executed'] = 'Function call executed';
$string['invaliduserpass'] = 'Invalid web services username / web services password supplied for "%s"';
$string['invalidtoken'] = 'Invalid web services token supplied';
$string['iterationtitle'] = '%s iteration: %s';
$string['unabletoruntestclient'] = 'Web service test client needs to be run under https in production mode or have $cfg->productionmode = false in your config.php';

//oauth server registry
$string['accesstokens'] = 'OAuth access tokens';
$string['notokens'] = 'You have no application tokens';
$string['oauth'] = 'OAuth';
$string['oauth1'] = 'OAuth1.x';
$string['oauthv1sregister'] = 'OAuth service registration';
$string['userapplications'] = 'OAuth consumer keys';
$string['accessto'] = 'Access to';
$string['application'] = 'Application';
$string['callback'] = 'Callback URI';
$string['consumer_key'] = 'Consumer key';
$string['consumer_secret'] = 'Consumer secret';
$string['add'] = 'Add';
$string['application'] = 'Application';
$string['oauthserverdeleted'] = 'Server deleted';
$string['oauthtokendeleted'] = 'Application token deleted';
$string['errorregister'] = 'Server registry failed';
$string['application_uri'] = 'Application URI';
$string['application_title'] = 'Application title';
$string['errorupdate'] = 'Update failed';
$string['erroruser'] = 'Invalid user specified';
$string['authorise'] = 'Authorise application access';
$string['oauth_access'] = 'This application will have access to your users\' details and resources';
$string['oauth_instructions'] = 'If you wish to grant access to this application, then click "Authorise application access". If you do not want to grant access, press "Cancel".';

// running webservices messages
$string['accesstofunctionnotallowed'] = 'Access to the function %s() is not allowed. Please check if a service containing the function is enabled. In the service settings: If the service is restricted, check that the user is listed. Still in the service settings check for IP restriction or if the service requires a capability.';
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
$string['default'] = 'Default %s';
$string['deleteservice'] = 'Delete the service: {$a->name} (id: {$a->id})';
$string['doc'] = 'Documentation';
$string['documentation'] = 'web services documentation';
$string['enabledocumentation'] = 'Enable developer documentation';
$string['enableprotocols'] = 'Enable protocols';
$string['enablews'] = 'Enable web services';
$string['error'] = 'Error: %s';
$string['errorcodes'] = 'Error message';
$string['errorinvalidparam'] = 'The param "%s" is invalid.';
$string['errorinvalidparamsapi'] = 'Invalid external api parameter';
$string['errorinvalidparamsdesc'] = 'Invalid external API description';
$string['errorinvalidresponseapi'] = 'Invalid external API response';
$string['errorinvalidresponsedesc'] = 'Invalid external API response description';
$string['errormissingkey'] = 'Missing required key in single structure: %s';
$string['errornotemptydefaultparamarray'] = 'The web service description parameter named \'%s\' is a single or multiple structure. The default can only be an empty array. Check web service description.';
$string['erroronlyarray'] = 'Only arrays accepted.';
$string['erroroptionalparamarray'] = 'The web service description parameter named \'%s\' is a single or multiple structure. It cannot be set as VALUE_OPTIONAL. Check web service description.';
$string['errorresponsemissingkey'] = 'Error in response: Missing following required key in a single structure: %s';
$string['errorscalartype'] = 'Scalar type expected, array or object received.';
$string['errorunexpectedkey'] = 'Unexpected keys (%s) detected in parameter array.';
$string['execute'] = 'Execute';
$string['expires'] = 'Expires';
$string['externalservice'] = 'External service';
$string['function'] = 'Function';
$string['generalstructure'] = 'General structure';
$string['information'] = 'Information';
$string['invalidlogin'] = 'Failed to log you in. Please check your username and password.';
$string['invalidaccount'] = 'Invalid web services account: Check service user configuration';
$string['invalidextparam'] = 'Invalid external API parameter: %s';
$string['invalidextresponse'] = 'Invalid external API response: %s';
$string['invalidiptoken'] = 'Invalid token: Your IP is not supported';
$string['invalidtimedtoken'] = 'Invalid token: Token expired';
$string['invalidtoken'] = 'Invalid token: Token not found';
$string['invalidtokensession'] = 'Invalid session based token: Session not found or expired';
$string['iprestriction'] = 'IP restriction';
$string['list'] = 'list of';
$string['key'] = 'Key';
$string['missingpassword'] = 'Missing password';
$string['missingusername'] = 'Missing username';
$string['notoken'] = 'The token list is empty.';
$string['nowsprotocolsenabled'] = 'No web service protocols are enabled. You need at least one <a href="%s">protocol</a> enabled.';
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
$string['fortokenusers'] = 'User token access';
$string['usertokens'] = 'Personal user tokens';
$string['serviceaccess'] = 'Service access';
$string['tokenclient'] = 'Client app';
$string['tokenclientunknown'] = '(Not specified)';
$string['tokenmanuallycreated'] = 'Manually created';
$string['gen'] = 'Generate';
$string['no_token'] = 'Token not generated';
$string['token_generated'] = 'Token generated';
$string['securitykey'] = 'Security key (token)';
$string['selectedcapability'] = 'Selected';
$string['selectspecificuser'] = 'Select a specific user';
$string['service'] = 'Service';
$string['serviceusers'] = 'Authorised users';
$string['servicenamelabel'] = 'Name';
$string['servicenamedesc'] = 'A human-readable name for this service group.';
$string['serviceshortnamelabel'] = 'Short name';
$string['serviceshortnamedesc'] = 'A machine-readable name for this service group. This is the name that will be used if an external service needs to refer to this service group.';
$string['servicecomponentnote'] = 'This service provides functionality for the component: %s';
$string['simpleauthlog'] = 'Simple authentication login';
$string['step'] = 'Step';
$string['testclient'] = 'Web service test client';
$string['testclientdescription'] = '* The web service test client <strong>executes</strong> the functions for <strong>REAL</strong>. Do not test functions that you don\'t know. <br/>* All existing web service functions are not yet implemented into the test client. <br/>* In order to check that a user cannot access some functions, you can test some functions that you didn\'t allow.<br/>* To see clearer error messages, set the debugging to <strong>{$a->mode}</strong> into {$a->atag}<br/>* Access the {$a->amfatag}.';
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
$string['webservicesenabled'] = 'Web services enabled';
$string['webservicesnotenabled'] = 'You need to enable at least one protocol';

// Web Service functions errors
$string['nooauth'] = 'Not enabled for OAuth';
$string['accessdenied'] = 'access denied';
$string['accessdeniedforinst'] = ' access denied for institution "%s"';
$string['accessdeniedforinstuser'] = ' access denied for institution "%s" with user "%s"';
$string['accessdeniedforinstgroup'] = ' access denied for institution "%s" on group "%s"';
$string['usernameexists'] = 'Username already exists "%s"';
$string['invalidauthtype'] = 'Invalid authentication type "%s"';
$string['invalidauthtypeuser'] = 'Invalid authentication type "%s" with user "%s"';
$string['invalidsocialprofile'] = 'Invalid social profile "%s"';
$string['instexceedmax'] = 'Institution exceeded maximum allowed "%s"';
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
$string['passwordmustbechangedviawebsite'] = 'You need to change your password. Please log in via a web browser in order to update your password.';
$string['featuredisabled'] = 'This web services feature is not enabled. Please contact your site administrator for more information.';
