<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$cert = AuthSaml::get_certificate_path() . 'server.crt';
$key = AuthSaml::get_certificate_path() . 'server.pem';

global $idp_entityid;

$spentityid = get_config_plugin('auth', 'saml', 'spentityid');
if (empty($spentityid)) {
    $spentityid = $_SERVER['HTTP_HOST'].'/mahara';
}

$config = array(

    // This is a authentication source which handles admin authentication.
    'admin' => array(
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ),

    // An authentication source which can authenticate against both SAML 2.0
    // and Shibboleth 1.3 IdPs.
    'default-sp' => array(
        'saml:SP',

        // The entity ID of this SP.
        // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
        'entityID' => $spentityid,

        // The entity ID of the IdP this should SP should contact.
        // Can be NULL/unset, in which case the user will be shown a list of available IdPs.

        // XXX hard code this so that no IdP disco happens
        'idp' => $idp_entityid,

        // The URL to the discovery service.
        // Can be NULL/unset, in which case a builtin discovery service will be used.
        'discoURL' => NULL,

        'encryption.blacklisted-algorithms' => array(),

        'privatekey' => $key,
        'privatekey_pass' => get_config('sitename'),
        'certificate' => $cert,

        'NameIDPolicy' => NULL,

        'redirect.sign' => TRUE,
        'redirect.validate' => TRUE,
    ),

);
