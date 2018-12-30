<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'webservices/testclient');
define('SECTION_PAGE', 'wstestclient');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'webservice/lib.php');

define('TITLE', get_string('webservices_title', 'auth.webservice'));
define('SUBSECTIONHEADING', get_string('testclient', 'auth.webservice'));

$protocol  = param_alpha('protocol', '');
$authtype  = param_alpha('authtype', '');
$service  = param_integer('service', 0);
$cancel = param_alpha('cancel_submit', null);
if ($cancel) {
    redirect('/webservice/testclient.php');
}
if ($service != 0) {
    $dbs = get_record('external_services', 'id', $service);
}
$function  = param_integer('function', 0);
if ($function != 0) {
    $dbsf = get_record('external_services_functions', 'id', $function);
}

$elements = array();

// check for web services call results
global $SESSION;
if ($result = $SESSION->get('ws_call_results')) {
    $SESSION->set('ws_call_results', false);
    $result = unserialize($result);
    $elements['wsresults'] = array('type' => 'html', 'value' => '<h3>Results:</h3><pre>' . hsc(var_export($result, true)) . '</pre><br/>');
}

// add protocol choice
$popts = array();
foreach (array('soap', 'xmlrpc', 'rest') as $proto) {
    $enabled = (get_config('webservice_provider_'.$proto.'_enabled') || 0);
    if ($enabled) {
        $popts[$proto] = get_string($proto, 'auth.webservice');
    }
}
$popts_keys = array_keys($popts);
$default_protocol = (empty($protocol) ?  array_shift($popts_keys) : $protocol);
$elements['protocol'] = array(
    'type'         => 'select',
    'title'        => get_string('protocol', 'auth.webservice'),
    'options'      => $popts,
    'defaultvalue' => trim($default_protocol),
    'disabled'     => (!empty($protocol)),
);

// add auth method
$aopts = array();
foreach (array('token', 'user') as $auth) {
    $aopts[$auth] = get_string($auth . 'auth', 'auth.webservice');
}
$default_authtype = (empty($authtype) ? 'token' : $authtype);
$elements['authtype'] = array(
    'type'         => 'select',
    'title'        => get_string('authtype', 'auth.webservice'),
    'options'      => $aopts,
    'defaultvalue' => trim($default_authtype),
    'disabled'     => (!empty($authtype)),
);

$nextaction = get_string('next');
$iterations = 0;

$params = array('protocol=' . $protocol, 'authtype=' . $authtype);
if (!empty($service)) {
    $params[]= 'service=' . $service;
}
if (!empty($function)) {
    $params[]= 'function=' . $function;
}

if (!empty($authtype)) {
    // add service group
    $dbservices = get_records_select_array('external_services', 'enabled = ? AND restrictedusers = ?', array(1, ($authtype == 'token' ? 0 : 1)));
    $sopts = array();
    if (!empty($dbservices)) {
        foreach ($dbservices as $dbservice) {
            $sopts[$dbservice->id] = $dbservice->name . ' (' . ($dbservice->restrictedusers ? get_string('userauth', 'auth.webservice') : get_string('tokenauth', 'auth.webservice')) . ')';
        }
    }
    $sopts_keys = array_keys($sopts);
    $default_service = ($service == 0 ? array_shift($sopts_keys) : $service);
    $elements['service'] = array(
        'type'         => 'select',
        'title'        => get_string('servicename', 'auth.webservice'),
        'options'      => $sopts,
        'defaultvalue' => $default_service,
        'disabled'     => (!empty($service)),
    );


    // finally add function choice
    if ($service != 0 && !empty($dbs)) {
        $dbfunctions = get_records_array('external_services_functions', 'externalserviceid', $dbs->id);
        $fopts = array();
        if (!empty($dbfunctions)) {
            foreach ($dbfunctions as $dbfunction) {
                $fopts[$dbfunction->id] = $dbfunction->functionname;
            }
        }
        $fopts_keys = array_keys($fopts);
        $default_function = ($function == 0 ? array_shift($fopts_keys) : $function);
        $elements['function'] = array(
            'type'         => 'select',
            'title'        => get_string('functions', 'auth.webservice'),
            'options'      => $fopts,
            'defaultvalue' => $default_function,
            'disabled'     => (!empty($function)),
        );
    }

    // we are go - build the form for function parameters
    if ($function != 0 && !empty($dbsf)) {
        $vars = testclient_get_interface($dbsf->functionname);
        $iterationtitle = !empty($vars) ? preg_replace('/_NUM_.*/', '', $vars[0]['name']) : '';
        $elements['spacer'] = array('type' => 'html', 'value' => '<br/><h3>' . get_string('enterparameters', 'auth.webservice') . '</h3>');
        for ($i=0;$i<=$iterations; $i++) {
            if (!empty($vars)) {
                $elements['spacer'] = array('type' => 'html', 'value' => '<br/><h4>' . get_string('iterationtitle', 'auth.webservice', ucfirst($iterationtitle), ($i + 1)) . '</h4>');
            }
            foreach ($vars as $var) {
                $name = preg_replace('/NUM/', $i, $var['name']);
                $title = preg_replace('/^(.*?)_NUM_/', '', $var['name']);
                $title = preg_replace('/_NUM_/', ' / ', $title);
                $type = (trim($var['type']) == 'bool') ? 'switchbox' : 'text';
                $type = (trim($var['type']) == 'file') ? 'file' : $type;
                $rules = array();
                // Because we add in fields before checking form we need to not check for required
                // if selecting 'function name' for first time
                if (isset($_GET['function']) && !empty($_GET['function'])) {
                    if ($var['required'] === 1) {
                        $rules['required'] = true;
                    }
                    if ($var['oneof']) {
                        $rules['oneof'] = $var['oneof'];
                    }
                }
                if ($title == 'institution') {
                    // Let see if we can fetch the exact allowed values
                    $elements[$name] = get_institution_selector();
                }
                else if ($title == 'country') {
                    $countries = getoptions_country();
                    $options = array('' => get_string('nocountryselected')) + $countries;
                    $elements[$name] = array(
                                             'type'         => 'select',
                                             'title'        => $title,
                                             'options'      => $options,
                                             'description'  => $var['desc'],
                                             'rules' => $rules,
                                             );
                }
                else if ($title == 'auth') {
                    $authinstances = auth_get_auth_instances();
                    $options = array();
                    foreach ($authinstances as $authinstance) {
                        $options[$authinstance->authname] = $authinstance->instancename;
                    }
                    $elements[$name] = array(
                                             'type'         => 'select',
                                             'title'        => $title,
                                             'options'      => $options,
                                             'description'  => $var['desc'],
                                             'rules' => $rules,
                                             );
                }
                else if ($title == 'password') {
                    $elements[$name] = array('title' => $title, 'type' => 'password', 'description' => $var['desc'], 'rules' => $rules);
                }
                else if ($title == 'socialprofile' && get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
                   $socialnetworkoptions = array('' => '');
                   safe_require('artefact', 'internal');
                   foreach (ArtefactTypeSocialprofile::$socialnetworks as $socialnetwork) {
                       $socialnetworkoptions[$socialnetwork] = get_string($socialnetwork . '.input', 'artefact.internal');
                   }
                   $elements[$name . '_profiletype'] = array(
                                            'type'         => 'select',
                                            'title'        => $title . '_type',
                                            'options'      => $socialnetworkoptions,
                                            );
                   $elements[$name . '_profileurl'] = array(
                                            'type'         => 'text',
                                            'title'        => $title . '_url',
                                            'description'  => $var['desc'],
                                            'rules'        => $rules,
                                            );
                }
                else {
                    $elements[$name] = array('title' => $title, 'type' => $type, 'description' => $var['desc'], 'rules' => $rules);
                }
            }
        }
        if ($authtype == 'user') {
            $username = param_alphanum('cancel_submit', null) ? '' : param_variable('wsusername', '');
            $password = param_alphanum('cancel_submit', null) ? '' : param_variable('wspassword', '');
            $rules = array('required' => true);
            $elements['wsusername'] = array('title' => 'wsusername', 'type' => 'text', 'value' => $username, 'autocomplete' => 'off', 'rules' => $rules);
            $elements['wspassword'] = array('title' => 'wspassword', 'type' => 'password', 'value' => $password, 'autocomplete' => 'off', 'rules' => $rules);
            if ($username) {
                $params[]= 'wsusername=' . $username;
            }
            if ($password) {
                $params[]= 'wspassword=' . $password;
            }
        }
        else {
            $wstoken = param_alphanum('cancel_submit', null) ? '' : param_variable('wstoken', '');
            $rules = array();
            if (isset($_GET['function']) && !empty($_GET['function'])) {
                $rules = array('required' => true);
            }
            $elements['wstoken'] = array('title' => 'wstoken', 'type' => 'text', 'value' => $wstoken, 'autocomplete' => 'off', 'rules' => $rules);
            if ($wstoken) {
                $params[]= 'wstoken=' . $wstoken;
            }
        }
        $nextaction = get_string('execute', 'auth.webservice');
    }
}

$elements['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array($nextaction, get_string('cancel')),
            'class' => 'btn-primary',
            'goto'  => get_config('wwwroot') . 'webservice/testclient.php',
        );
if (!empty($elements['protocol']['options'])) {
    $form = pieform(array(
        'name'            => 'testclient',
        'renderer'        => 'div',
        'successcallback' => 'testclient_submit',
        'elements'        => $elements,
    ));
}
else {
    $form = '';
}
$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');

safe_require('auth', 'webservice');

$smarty->assign('form', $form);

// Check that webservices is enabled
$smarty->assign('disabled', (get_config('webservice_provider_enabled') ? false : true));
$smarty->assign('disabledhttps', ((!is_https() && get_config('productionmode')) ? true : false));
$smarty->assign('disabledprotocols', (empty($elements['protocol']['options']) ? get_config('wwwroot') . 'webservice/admin/index.php' : false));
$smarty->display('auth:webservice:testclient.tpl');
die;

/**
 * get the interface definition for the function
 *
 * @param string $functionname
 * @return array $vars
 */
function testclient_get_interface($functionname) {
    $fdesc = webservice_function_info($functionname);
    $strs = explode('|', testclient_parameters($fdesc->parameters_desc, ''));
    $vars = array();
    foreach ($strs as $str) {
        if (empty($str)) continue;
        list($name, $type) = explode('=', $str);
        $name = preg_replace('/\]\[/', '_', $name);
        $name = preg_replace('/[\]\[]/', '', $name);
        $desc = testclient_parameters_desc($fdesc, $name);
        $required = testclient_parameters_desc($fdesc, $name, 'required');
        $oneof = testclient_parameters_desc($fdesc, $name, 'oneof');
        $vars[]= array('name' => $name, 'type' => $type, 'desc' => $desc, 'required' => $required, 'oneof' => $oneof);
    }
    return $vars;
}

function testclient_parameters_desc($fdesc, $name, $type='desc') {
    // Do we have any parameter_desc information?
    $name = explode('_NUM_', $name);
    if (!isset($fdesc->parameters_desc) && !isset($fdesc->parameters_desc->keys[$name[0]])) {
        return null;
    }
    // Do we have any information for the field we want?
    if (count($name) > 1 && isset($fdesc->parameters_desc->keys[$name[0]]->content->keys[$name[1]])) {
        if (count($name) == 2) {
            $result = $fdesc->parameters_desc->keys[$name[0]]->content->keys[$name[1]]->$type;
        }
        else if (count($name) == 3) {
            $result = $fdesc->parameters_desc->keys[$name[0]]->content->keys[$name[1]]->content->keys[$name[2]]->$type;
        }
        return $result;
    }
    return null;
}

/**
 * Return indented REST param description
 * @param object $paramdescription
 * @param string $paramstring
 * @return string the html to diplay
 */
function testclient_parameters($paramdescription, $paramstring) {
    $brakeline = '|';
    /// description object is a list
    if ($paramdescription instanceof external_multiple_structure) {
        $paramstring = $paramstring . '[NUM]';
        $return = testclient_parameters($paramdescription->content, $paramstring);
        return $return;
    }
    else if ($paramdescription instanceof external_single_structure) {
        /// description object is an object
        $singlestructuredesc = "";
        $initialparamstring = $paramstring;
        foreach ($paramdescription->keys as $attributname => $attribut) {
            $paramstring = $initialparamstring . '[' . $attributname . ']';
            $singlestructuredesc .= testclient_parameters(
                            $paramdescription->keys[$attributname], $paramstring);
        }
        return $singlestructuredesc;
    }
    else {
        /// description object is a primary type (string, integer)
        $paramstring = $paramstring . '=';
        switch ($paramdescription->type) {
            case PARAM_BOOL:
                $type = 'bool';
                break;
            case PARAM_INT:
                $type = 'int';
                break;
            case PARAM_FLOAT:
                $type = 'double';
                break;
            case PARAM_FILE:
                $type = 'file';
                break;
            default:
                $type = 'string';
        }

        return $paramstring . " " . $type . $brakeline;
    }
}

/**
 * recurse into structured parameter names to figure out PHP hash structure
 *
 * @param array ref $inputs
 * @param array $parts
 * @param string $value
 * @param string $type
 */
function testclient_build_inputs(&$inputs, $parts, $value, $type=null) {
    $part = array_shift($parts);

    if (empty($parts)) {
        if ((trim($type) == 'int' && $value == '0') || !empty($value)) {
            $inputs[$part] = $value;
        }
        return;
    }
    // eg: users_0_id
    if (preg_match('/^\d+$/', $part)) {
        // we have a real array
        $part = (int)$part;
    }
    if (!isset($inputs[$part])) {
        $inputs[$part] = array();
    }
    testclient_build_inputs($inputs[$part], $parts, $value, $type);
}

/**
 * submit callback
 *
 * @param Pieform $form
 * @param array $values
 */
function testclient_submit(Pieform $form, $values) {
    global $SESSION, $params, $iterations, $function, $dbsf;

    if (($values['authtype'] == 'token' && !empty($values['wstoken'])) ||
        ($values['authtype'] == 'user' && !empty($values['wsusername']) && !empty($values['wspassword']))) {
        $vars = testclient_get_interface($dbsf->functionname);
        $inputs = array();
        for ($i=0;$i<=$iterations; $i++) {
            foreach ($vars as $var) {
                $name = preg_replace('/NUM/', $i, $var['name']);
                if (preg_match('/_socialprofile$/', $name)) {
                    // we are dealing with a special case where two fields make up the one artefact
                    $subname = $name . '_profiletype';
                    $parts = explode('_', $subname);
                    testclient_build_inputs($inputs, $parts, $values[$subname]);
                    $subname = $name . '_profileurl';
                    $parts = explode('_', $subname);
                    testclient_build_inputs($inputs, $parts, $values[$subname]);
                }
                else {
                    $parts = explode('_', $name);
                    testclient_build_inputs($inputs, $parts, $values[$name], $var['type']);
                }
            }
        }

        if ($values['authtype'] == 'token') {
           // check token
           $dbtoken = get_record('external_tokens', 'token', $values['wstoken']);
           if (empty($dbtoken)) {
               $SESSION->add_error_msg(get_string('invalidtoken', 'auth.webservice'));
               redirect('/webservice/testclient.php?' . implode('&', $params));
           }
        }
        else {
            // check user is a valid web services account
           $dbuser = get_record('usr', 'username', $values['wsusername']);
           if (empty($dbuser)) {
               $SESSION->add_error_msg(get_string('invaliduser', 'auth.webservice', $values['wsusername']));
               redirect('/webservice/testclient.php?' . implode('&', $params));
           }
            // special web service login
            safe_require('auth', 'webservice');

           // do password auth
            $ext_user = get_record('external_services_users', 'userid', $dbuser->id);
            if (empty($ext_user)) {
               $SESSION->add_error_msg(get_string('invaliduser', 'auth.webservice', $values['wsusername']));
               redirect('/webservice/testclient.php?' . implode('&', $params));
            }
            // determine the internal auth instance
            $auth_instance = get_record('auth_instance', 'institution', $ext_user->institution, 'authname', 'webservice', 'active', 1);
            if (empty($auth_instance)) {
               $SESSION->add_error_msg(get_string('invaliduser', 'auth.webservice', $values['wsusername']));
               redirect('/webservice/testclient.php?' . implode('&', $params));
            }
            // authenticate the user
            $auth = new AuthWebservice($auth_instance->id);
            if (!$auth->authenticate_user_account($dbuser, $values['wspassword'], 'webservice')) {
                // log failed login attempts
               $SESSION->add_error_msg(get_string('invaliduserpass', 'auth.webservice', $values['wsusername']));
               redirect('/webservice/testclient.php?' . implode('&', $params));
            }
        }
        // now build the test call
        switch ($values['protocol']) {
            case 'rest':
                require_once(get_config('docroot') . '/webservice/rest/lib.php');
                $client = new webservice_rest_client(get_config('wwwroot')
                                . 'webservice/rest/server.php',
                                 ($values['authtype'] == 'token' ? array('wstoken' => $values['wstoken']) :
                                                      array('wsusername' => $values['wsusername'], 'wspassword' => $values['wspassword'])), $values['authtype'], true);

                break;

            case 'xmlrpc':
                require_once(get_config('docroot') . 'webservice/xmlrpc/lib.php');
                $client = new webservice_xmlrpc_client(get_config('wwwroot')
                        . 'webservice/xmlrpc/server.php',
                         ($values['authtype'] == 'token' ? array('wstoken' => $values['wstoken']) :
                                              array('wsusername' => $values['wsusername'], 'wspassword' => $values['wspassword'])));
                break;

            case 'soap':
                require_once(get_config('docroot') . 'webservice/soap/lib.php');
                //force SOAP synchronous mode
                $client = new webservice_soap_client(get_config('wwwroot') . 'webservice/soap/server.php',
                                ($values['authtype'] == 'token' ? array('wstoken' => $values['wstoken']) :
                                                     array('wsusername' => $values['wsusername'], 'wspassword' => $values['wspassword'])),
                                array("features" => SOAP_WAIT_ONE_WAY_CALLS, 'stream_context' => webservice_create_context(get_config('wwwroot') . 'webservice/soap/server.php')));
                break;
        }

        try {
            $results = $client->call($dbsf->functionname, $inputs);
            $url = $client->serverurl;
            if ($client->serverurl instanceof mahara_url) {
                $url = $client->serverurl->raw_out(false);
            }
            $results = array('url' => $url,
                             'results' => $results,
                             'inputs' => $inputs);
        } catch (Exception $e) {
            $results = "exception: " . $e->getMessage();
            # split the string up by sentances and error code for easier reading
            $results = preg_replace('/(\.|\|)/', "\n", $results);
        }

        $SESSION->set('ws_call_results', serialize($results));
        $SESSION->add_ok_msg(get_string('executed', 'auth.webservice'));
    }

    redirect('/webservice/testclient.php?' . implode('&', $params));
}
