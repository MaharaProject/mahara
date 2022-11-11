<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['ltiserviceexists'] = 'The LTI Advantage service group is registered.';
$string['maharaltiadvantage'] = 'LTI Advantage';
$string['short_name'] = 'Short name';
$string['short_namedescription'] = 'Maximum of 30 characters. This short name is the name for the LTI Provider that is displayed in the "Submitted to" column in the "Current submissions" table.';
$string['short_namecannotbeempty'] = 'Short name cannot be empty.';
$string['issuer'] = 'Issuer';
$string['client_id'] = 'Client ID';
$string['platform_login_auth_endpoint'] = 'OpenID Connect authentication endpoint';
$string['platformvendorkeytitle'] = 'Platform vendor';
$string['platformvendorkeyoptionnone'] = 'Select a platform';
$string['platformvendorkeyoptionbrightspace'] = 'Brightspace';
$string['platformvendorkeycannotbeempty'] = 'Please select or enter a platform vendor';
$string['platformvendorissnotfound'] = 'The platform did not send an \'iss\' in the data returned. The key is missing.';
$string['platformvendorkeyinvalid'] = 'The platform vendor key was not found in the request. Keys in the returned request include: %s';
$string['platformvendorkeynotfound'] = 'The \'iss\' (%s) from the platform does not have a matching \'Issuer\' in the \'lti_advantage_registration\' database table.';
$string['productfamilycodenotfound'] = 'The platform product family code was not found in the request.';
$string['productfamilycodeunknown'] = 'The platform product family code "%s" is not known to Mahara.';
$string['platform_service_auth_endpoint'] = 'Platform OAuth2 access token URL';
$string['platform_jwks_endpoint'] = 'Platform keyset URL';
$string['platform_auth_provider'] = 'Platform OAuth2 audience';
$string['deployment_id'] = 'Deployment ID';
$string['oauth2'] = 'OAuth2';
$string['nullprivatecert'] = "Could not generate or save the private key";
$string['nullpubliccert'] = "Could not generate or save the public certificate";
$string['groupname'] = '%s';
$string['usercreationupdate'] = 'Group members will be updated shortly. Please come back in a few minutes.';
$string['domain'] = 'Domain';
$string['redirecturl'] = 'Redirect URLs';
$string['openidconnectlogin'] = 'OpenID Connect login URL';
$string['keyset'] = 'Keyset URL';
$string['deployments'] = 'Deployments';
$string['deploymentsdesc'] = 'Deployment IDs from the LTI platform';
$string['issueralreadyinuse'] = 'Issuer is already set to another client connection';
$string['deploymentidcannotbesame'] = 'Deployment ID fields cannot be the same.';
$string['deployment1_idalreadyinuse'] = 'This deployment ID is already in use.';
$string['deployment2_idalreadyinuse'] = 'This deployment ID is already in use.';
$string['deployment3_idalreadyinuse'] = 'This deployment ID is already in use.';
$string['deployment1_title'] = 'Main deployment ID';
$string['deployment2_title'] = 'Additional deployment ID 1';
$string['deployment3_title'] = 'Additional deployment ID 2';
$string['deployment1_description'] = 'If your platform only has one deployment ID, use this field.';
$string['deployment2_description'] = 'Enter an additional deployment ID if needed, e.g. if you want to use separate deployments for NRPS or deep links.';
$string['deployment3_description'] = 'Enter an additional deployment ID if needed, e.g. if you want to use separate deployments for NRPS or deep links.';
$string['deeplinkportfoliostitle'] = 'Portfolios';
$string['deeplinknoportfolios'] = 'You have no portfolios.';

# Modal language strings
$string['confirmareyousure'] = 'Are you sure?';
$string['confirmwarning1'] = 'If you submit \'%s\', a copy will be made and submitted for assessment. You can continue editing your original portfolio. However, you will not be able to edit the submitted copy until it is released. Do you want to continue?';
$string['confirmbtntxtconfirm'] = 'Confirm';
$string['confirmbtntxtcancel'] = 'Cancel';
