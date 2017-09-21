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

    /**
     */
    if ($oldversion < 2017071800) {
        //For legacy installs we default to rsa-sha1 as that was the default previously, although we would
        //ideally like them to use rsa-256
        set_config_plugin('auth', 'saml', 'sigalgo', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    }
    return $status;
}
