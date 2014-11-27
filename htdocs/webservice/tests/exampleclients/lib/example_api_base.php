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

/**
 * This is the base libarary of functions for running each example from the
 * command line.
 * handles command line options, and interactive choices, and then
 * runs the main loop for the chosen example cluster: group, user, institution
 */

// must be run from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])) {
    die('Direct access to this script is forbidden.');
}

$path = realpath('../../libs/zend');
set_include_path($path . PATH_SEPARATOR . get_include_path());

include ("Console/Getopt.php");


include 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::autoload('Zend_Loader');

// disable the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

// get the WSSE SOAP client class
require_once('wsse_soap_client.php');

/**
 *   command line help text
 */
function help_text() {
    return "
    Options are:
        --username     - Web Services simple authentication username
        --password     - Web Services simple authentication password
        --bausername   - basic authentication username to get past basic auth
        --bapassword   - basic authentication password to get past basic auth
        --servicegroup - service group as specified in webservice configuration that contains the necessary functions to call
        --url          - the URL of the Web Service to call eg: http://your.mahara.local.net/webservice/soap/server.php
    ";
}

//fetch arguments
$args = Console_Getopt::readPHPArgv();
//checking errors for argument fetching
if (PEAR::isError($args)) {
    error_log('Invalid arguments (1): ' . help_text());
    exit(1);
}

// remove stderr/stdout redirection args
$args = preg_grep('/2>&1/', $args, PREG_GREP_INVERT);
$console_opt = Console_Getopt::getOpt($args, 'u:p:l:s:', array('username=', 'password=', 'url=', 'servicegroup=', 'bausername=', 'bapassword='));

if (PEAR::isError($console_opt)) {
    error_log('Invalid arguments (2): ' . help_text());
    exit(1);
}

// must supply at least one arg for the action to perform
if (count($args) <= 2) {
    error_log('Invalid arguments: you must atleast specify --username and --password' . help_text());
    exit(1);
}

/**
 * Get and check interactive parameters
 *
 * @param string $param
 * @param string $default
 */
function get_param_console($param, $default) {
    if ($tmp = trim(readline("Enter Mahara $param ($default): "))) {
        $default = $tmp;
    }
    if (empty($default)) {
        die("You must have a $param for executing web services\n");
    }
    return $default;
}

// defaults
global $url, $servicegroup, $username, $password, $bausername, $bapassword;
$bausername = false;
$bapassword = false;

$url = 'https://apistaging.myportfolio.school.nz/webservice/soap/server.php';
if (empty($servicegroup)) {
    $servicegroup = 'Simple User Provisioning';
}
if (empty($username)) {
    $username = false;
}
if (empty($password)) {
    $password = false;
}

// parse back the options
$opts = $console_opt[0];
if (sizeof($opts) > 0) {
    // if at least one option is present
    foreach ($opts as $o) {
        switch ($o[0]) {
            // handle the size option
            case 'u':
            case '--username':
                $username = $o[1];
                break;
            case 'p':
            case '--password':
                $password = $o[1];
                break;
            case 'l':
            case '--url':
                $url = $o[1];
                break;
            case 's':
            case '--servicegroup':
                $servicegroup = $o[1];
                break;
            case '--bausername':
                $bausername = $o[1];
                break;
            case '--bapassword':
                $bapassword = $o[1];
                break;
        }
    }
}


// ensure that we have the necessary options
$username = get_param_console('username', $username);
$password = get_param_console('password', $password);
$servicegroup = get_param_console('servicegroup', $servicegroup);
$url = get_param_console('Mahara Web Services URL', $url);
if (!empty($bausername)) {
    $bausername = get_param_console('username', $bausername);
    $bapassword = get_param_console('password', $bapassword);
}

// declare what we are running with
print "web services url: $url\n";
print "service group: $servicegroup\n";
print "username; $username\n";
print "password: $password\n";
$wsdl = $url . '?wsservice=' . $servicegroup . '&wsdl=1';
print "WSDL URL: $wsdl \n";
print "basic auth username; $bausername\n";
print "basic auth password: $bapassword\n";

// keep looping until user exits
while (1) {
    // now select a function to execute
    print "Select one of functions to execute:\n";
    $cnt = 0;
    $function_list = array_keys($functions);
    foreach ($function_list as $rfunction) {
        print "$cnt. $rfunction\n";
        $cnt++;
    }
    $function_choice = trim(readline("Enter your choice (0.." . (count($function_list) - 1) . " or x for exit):"));
    if (in_array($function_choice, array('x', 'X', 'q', 'Q'))) {
        break;
    }
    if (!preg_match('/^\d+$/', $function_choice) || (int)$function_choice > (count($function_list) - 1)) {
        print "Incorrect choice - aborting\n";
        exit(0);
    }
    $function = $function_list[$function_choice];
    print "Chosen function: $function\n";
    print "Parameters used for execution are: " . var_export($functions[$function], true) . "\n";

    // build the client for execution
    $options = null;
    if ($bausername) {
        print "setting user/pass for basic auth...\n";
        $options = array('login' => $bausername, 'password' => $bapassword);
    }
    $client = new WSSE_Soap_Client($wsdl, $options, $username, $password);

    //make the web service call
    try {
        $result = $client->call($function, $functions[$function]);
        print "Results are: " . var_export($result, true) . "\n";
    }
    catch (Exception $e) {
         print "exception: " . var_export($e, true) . "\n";
    }
}

exit(0);
