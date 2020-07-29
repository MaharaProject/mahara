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

function xmldb_auth_saml_upgrade($oldversion=0) {

    $status = true;

    if ($oldversion < 2017071800) {
        //For legacy installs we default to rsa-sha1 as that was the default previously, although we would
        //ideally like them to use rsa-256
        set_config_plugin('auth', 'saml', 'sigalgo', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    }

    if ($oldversion < 2017082900) {
        // Set library version to download
        set_config_plugin('auth', 'saml', 'version', '1.14.16');
    }

    if ($oldversion < 2017102600) {
        // Set library version to download
        set_config_plugin('auth', 'saml', 'version', '1.14.17');
    }

    if ($oldversion < 2017122000) {
        // Set library version to download
        set_config_plugin('auth', 'saml', 'version', '1.15.0');
    }

    if ($oldversion < 2018021600) {
        // Set library version to download
        set_config_plugin('auth', 'saml', 'version', '1.15.1');
    }
    if ($oldversion < 2018080300) {
        set_config_plugin('auth', 'saml', 'version', '1.16.1');
    }
    if ($oldversion < 2019011100) {
        set_config_plugin('auth', 'saml', 'version', '1.16.3');
    }
    if ($oldversion < 2019011101) {
        set_config_plugin('auth', 'saml', 'version', '1.17.6');
    }
    if ($oldversion < 2019011102) {
        set_config_plugin('auth', 'saml', 'version', '1.17.7');
    }
    if ($oldversion < 2020030100) {
        set_config_plugin('auth', 'saml', 'version', '1.18.4');
    }
    if ($oldversion < 2020030101) {
         set_config_plugin('auth', 'saml', 'version', '1.18.7');
    }

    return $status;
}
