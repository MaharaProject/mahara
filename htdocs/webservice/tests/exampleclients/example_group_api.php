<?php
/**
 * Test the different web service protocols.
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// must be run from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])) {
    die('Direct access to this script is forbidden.');
}

// pull in the test data
include_once('lib/example_test_data_group.php');

// set defaults
global $servicegroup, $username, $password;
$servicegroup = 'Simple Group Provisioning';

// run it
include_once('lib/example_api_base.php');
