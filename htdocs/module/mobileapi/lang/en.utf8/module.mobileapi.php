<?php
/**
 *
 * @package    mahara
 * @subpackage module-mobileapi
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['autoconfiguredesc'] = 'Automatically enable settings and configurations needed for the mobile apps API.';
$string['autoconfiguretitle'] = 'Auto-configure mobile apps API';
$string['configstep'] = 'Conguration item';
$string['configstepstatus'] = 'Status';
$string['manualtokensdesc'] = 'Users can generate web service access tokens manually in order to copy and paste them into an app. Normally, an app should be able to generate the tokens for users automatically. However, some authentication plugins may not allow for that.';
$string['manualtokenstitle'] = 'Manual token generation';
$string['mobileapiserviceexists'] = 'Mobile API service group is registered';
$string['mobileapiserviceconfigured'] = 'Mobile API service enabled, "%s" disabled, "%s" enabled';
$string['noticeenabled'] = 'The Mahara mobile apps API is enabled.';
$string['noticenotenabled'] = 'The Mahara mobile apps API is <b>not</b> enabled.';
$string['notreadylabel'] = 'Not ready';
$string['readylabel'] = 'Ready';
$string['restprotocolenabled'] = 'REST protocol enabled';
$string['servicenotallowed'] = 'The credentials you have provided are not authorized to access this functionality.';
$string['webserviceproviderenabled'] = 'Incoming web service requests allowed';

// User management of webservice access tokens
$string['mytokensmenutitle1'] = 'Mahara Mobile';
$string['mytokenspagetitle1'] = 'Mahara Mobile tokens';
$string['mytokenspagedesc'] = 'These applications can access your account.';
$string['nopersonaltokens'] = 'You have not granted access to any applications.';
$string['clientinfo'] = 'App';
$string['token'] = 'Access token';
$string['tokencreated'] = 'Created';
$string['tokenmanuallycreated'] = 'Manually created';
$string['clientnotspecified'] = '(Unknown)';
$string['generateusertoken'] = 'Generate an app access token';
$string['appaccessrevoked'] = 'Access revoked';
