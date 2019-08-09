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

/**
 * XML-RPC web service implementation classes and methods.
 *
 * @package   webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

require_once(get_config('docroot') . "webservice/lib.php");

/**
 *  wrapper function for MNet function user_authorise
 *
 * @param string $token
 * @param string $useragent
 *
 * @return array userdata
 */
function webservice_mnet_user_authorise($token, $useragent) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    $userdata = user_authorise($token, $useragent);
    return $userdata;
}

/**
 *  wrapper function for MNet function update_enrolments
 *
 * @param string $username
 * @param array $enrolments
 *
 * @return boolean true
 */
function webservice_mnet_update_enrolments($username, $enrolments) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    $result = xmlrpc_not_implemented();
    return $result;
}

/**
 * Fetch a users image
 *
 * @param string $username
 *
 * @return blob
 */
function webservice_mnet_fetch_user_image($username) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return fetch_user_image($username);
}

/**
 * keep alive server - not implemented
 *
 * @param unknown_type $array
 */
function webservice_mnet_keepalive_server($array) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    $result = xmlrpc_not_implemented();
    return $result;
}

/**
 * Kill off child sessions
 *
 * @param string $username
 * @param string $useragent
 *
 * @return bool
 */
function webservice_mnet_kill_children($username, $useragent) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return kill_children($username, $useragent);
}

/**
 * kill child - not implemented
 *
 * @param unknown_type $username
 * @param unknown_type $useragent
 *
 * @return bool
 */
function webservice_mnet_kill_child($username, $useragent) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    $result = xmlrpc_not_implemented();
    return $result;
}

/**
 * get user views
 *
 * @param string $username
 * @param string $query
 *
 * @return array
 */
function webservice_mnet_get_views_for_user($username, $query=null) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return get_views_for_user($username, $query);
}

/**
 * submit view for assessment
 *
 * @param string $username
 * @param integer $viewid
 *
 * @return bool
 */
function webservice_mnet_submit_view_for_assessment($username, $viewid, $is_collection = false, $lock = true) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return submit_view_for_assessment($username, $viewid, $is_collection, $lock);
}

/**
 * release a submitted view
 *
 * @param unknown_type $viewid
 * @param unknown_type $assessmentdata
 * @param unknown_type $teacherusername
 */
function webservice_mnet_release_submitted_view($viewid, $assessmentdata, $teacherusername) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return release_submitted_view($viewid, $assessmentdata, $teacherusername);
}

/**
 * send a content intent
 *
 * @param unknown_type $username
 */
function webservice_mnet_send_content_intent($username) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return send_content_intent($username);
}

/**
 * send a content ready
 *
 * @param $token
 * @param $username
 * @param $format
 * @param $importdata
 * @param $fetchnow
 */
function webservice_mnet_send_content_ready($token, $username, $format, $importdata, $fetchnow) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return send_content_ready($token, $username, $format, $importdata, $fetchnow);
}

/**
 * get folder files
 *
 * @param string $username
 * @param integer $folderid
 *
 * @return array
 */
function webservice_mnet_get_folder_files($username, $folderid) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return get_folder_files($username, $folderid);
}

/**
 * get file
 *
 * @param string $username
 * @param integer $id
 *
 * @return array
 */
function webservice_mnet_get_file($username, $id) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return get_file($username, $id);
}

/**
 * serach folders and files
 *
 * @param string $username
 * @param string $search
 *
 * @return array
 */
function webservice_mnet_search_folders_and_files($username, $search) {
    require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
    return search_folders_and_files($username, $search);
}

/**
 * list available methods
 *
 * @param string $xmlrpc_method_name
 * @param string $args
 *
 * @return array
 */
function webservice_list_methods($xmlrpc_method_name, $args) {
    require_once(get_config('docroot') . 'api/xmlrpc/dispatcher.php');
    $Dispatcher = new Dispatcher(null, false, false);
    return $Dispatcher->list_methods($xmlrpc_method_name, $args);
}

/**
 * get signature of method
 *
 * @param string $xmlrpc_method_name
 * @param string $methodname
 *
 * @return array
 */
function webservice_method_signature($xmlrpc_method_name, $methodname) {
    require_once(get_config('docroot') . 'api/xmlrpc/dispatcher.php');
    $Dispatcher = new Dispatcher(null, false, false);
    return $Dispatcher->method_signature($xmlrpc_method_name, $methodname);
}

/**
 *
 * @param string $xmlrpc_method_name
 * @param string $methodname
 *
 * @return array
 */
function webservice_method_help($xmlrpc_method_name, $methodname) {
    require_once(get_config('docroot') . 'api/xmlrpc/dispatcher.php');
    $Dispatcher = new Dispatcher(null, false, false);
    return $Dispatcher->method_help($xmlrpc_method_name, $methodname);
}

/**
 * search folders and files
 *
 * @param string $wwwroot
 * @param string $pubkey
 * @param string $application
 *
 * @return array
 */
function webservice_keyswap($wwwroot = '', $pubkey = '', $application = '') {
    require_once(get_config('docroot') . 'api/xmlrpc/dispatcher.php');
    return Dispatcher::keyswap(null, array($wwwroot, $pubkey, $application));
}

/**
 * list available services
 *
 * @return array
 */
function webservice_list_services() {
    require_once(get_config('docroot') . 'api/xmlrpc/dispatcher.php');
    $Dispatcher = new Dispatcher(null, false, false);
    return $Dispatcher->list_services();
}

/**
 * XML-RPC service server implementation.
 * @author Petr Skoda (skodak)
 */
class webservice_xmlrpc_server extends webservice_base_server {

    private $payload_signed = false;
    private $payload_encrypted = false;
    public $publickey = null;

    /**
     * Contructor
     * @param integer $authmethod authentication method one of WEBSERVICE_AUTHMETHOD_*
     */
    public function __construct($authmethod) {
        parent::__construct($authmethod);
        $this->wsname = 'xmlrpc';
    }

    /**
     * Chance for each protocol to modify the function processing list
     */
    // Looks to be mapping moodle paths to their equivalent mahara functions.
    // - do we need this anymore?
    protected function fixup_functions() {
        $functions = array(
                    'auth/mnet/auth.php/user_authorise' => 'webservice_mnet_user_authorise',
                    'auth/mnet/auth.php/update_enrolments' => 'webservice_mnet_update_enrolments',
                    'auth/mnet/auth.php/fetch_user_image' => 'webservice_mnet_fetch_user_image',
                    'auth/mnet/auth.php/keepalive_server' => 'webservice_mnet_keepalive_server',
                    'auth/mnet/auth.php/kill_children' => 'webservice_mnet_kill_children',
                    'auth/mnet/auth.php/kill_child' => 'webservice_mnet_kill_child',
                    'mod/mahara/rpclib.php/get_views_for_user' => 'webservice_mnet_get_views_for_user',
                    'mod/mahara/rpclib.php/submit_view_for_assessment' => 'webservice_mnet_submit_view_for_assessment',
                    'mod/mahara/rpclib.php/release_submitted_view' => 'webservice_mnet_release_submitted_view',
                    'portfolio/mahara/lib.php/send_content_intent' => 'webservice_mnet_send_content_intent',
                    'portfolio/mahara/lib.php/send_content_ready' => 'webservice_mnet_send_content_ready',
                    'repository/mahara/repository.class.php/get_folder_files' => 'webservice_mnet_get_folder_files',
                    'repository/mahara/repository.class.php/get_file' => 'webservice_mnet_get_file',
                    'repository/mahara/repository.class.php/search_folders_and_files' => 'webservice_mnet_search_folders_and_files',
                    'system/listMethods'       => 'webservice_list_methods',
                    'system/methodSignature'   => 'webservice_method_signature',
                    'system/methodHelp'        => 'webservice_method_help',
                    'system.listServices'      => 'webservice_list_services',
                    'system/listServices'      => 'webservice_list_services',
                    'system.keyswap'           => 'webservice_keyswap',
                    'system/keyswap'           => 'webservice_keyswap',
            );
        return $functions;
    }

    /**
     * Check that the signature has been signed by the remote host.
     */
    private function xmldsig_envelope_strip($xml, $certificate) {

        $signature      = base64_decode($xml->Signature->SignatureValue);
        $payload        = base64_decode($xml->object);

        // Does the signature match the data and the public cert?
        $signature_verified = openssl_verify($payload, $signature, $certificate);

        if ($signature_verified == 1) {
            // Parse the XML
            try {
                $xml = new SimpleXMLElement($payload);
                return $payload;
            }
            catch (Exception $e) {
                throw new MaharaException('Signed payload is not a valid XML document', 6007);
            }
        }

        throw new MaharaException('An error occurred while trying to verify your message signature', 6004);
    }

    /**
     * This method parses the request input, it needs to get:
     *  1/ user authentication - username+password or token
     *  2/ function name
     *  3/ function parameters
     */
    protected function parse_request() {
        // Retrieve and clean the POST/GET parameters from the parameters specific to the server.
        parent::set_web_service_call_settings();
        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $this->username = isset($_GET['wsusername']) ? $_GET['wsusername'] : null;
            $this->password = isset($_GET['wspassword']) ? $_GET['wspassword'] : null;
        }
        else {
            $this->token = isset($_GET['wstoken']) ? $_GET['wstoken'] : null;
        }

        // Get the XML-RPC request data.
        $rawpostdata = $this->fetch_input_content();
        $methodname = null;
        // Decode the request to get the decoded parameters and the name of the method to be called.
        $decodedparams = xmlrpc_decode_request(trim($rawpostdata), $methodname, 'UTF-8');
        $methodinfo = external_api::external_function_info($methodname);
        $methodparams = array_keys($methodinfo->parameters_desc->keys);
        $methodvariables = [];
        // Add the decoded parameters to the methodvariables array.
        if (is_array($decodedparams)) {
            foreach ($decodedparams as $index => $param) {
                // See MDL-53962 - XML-RPC requests will usually be sent as an array (as in, one with indicies).
                // We need to use a bit of "magic" to add the correct index back. Zend used to do this for us.
                $methodvariables[$methodparams[$index]] = $param;
            }
        }
        $this->functionname = $methodname;
        $this->parameters = $methodvariables;
    }
    /**
     * Fetch content from the client.
     *
     * @return string
     */
    protected function fetch_input_content() {
        return file_get_contents('php://input');
    }
    /**
     * Prepares the response.
     */
    protected function prepare_response() {
        try {
            if (!empty($this->function->returns_desc)) {
                $validatedvalues = external_api::clean_returnvalue($this->function->returns_desc, $this->returns);
                $encodingoptions = array(
                    "encoding" => "UTF-8",
                    "verbosity" => "no_white_space",
                    // See MDL-54868.
                    "escaping" => ["markup"]
                );
                // We can now convert the response to the requested XML-RPC format.
                $this->response = xmlrpc_encode_request(null, $validatedvalues, $encodingoptions);
            }
        }
        catch (MaharaException $ex) {
            $this->response = $this->generate_error($ex);
        }
    }
    /**
     * Send the result of function call to the WS client.
     */
    protected function send_response() {
        $this->prepare_response();
        $this->send_headers();
        echo $this->response;
    }
    /**
     * Send the error information to the WS client.
     *
     * @param Exception $ex
     */
    protected function send_error($ex = null) {
        $this->response = $this->generate_error($ex);
        $this->send_headers();
        echo $this->response;
    }
    /**
     * Sends the headers for the XML-RPC response.
     */
    protected function send_headers() {
        // Standard headers.
        header('HTTP/1.1 200 OK');
        header('Connection: close');
        header('Content-Length: ' . strlen($this->response));
        header('Content-Type: text/xml; charset=utf-8');
        header('Date: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header('Server: Moodle XML-RPC Server/1.0');
        // Other headers.
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
        // Allow cross-origin requests only for Web Services.
        // This allow to receive requests done by Web Workers or webapps in different domains.
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: false');
    }
    /**
     * Generate the XML-RPC fault response.
     *
     * @param Exception $ex The exception.
     * @param int $faultcode The faultCode to be included in the fault response
     * @return string The XML-RPC fault response xml containing the faultCode and faultString.
     */
    protected function generate_error(Exception $ex, $faultcode = 404) {
        $error = $ex->getMessage();
        if (!empty($ex->errorcode)) {
            // The faultCode must be an int, so we obtain a hash of the errorcode then get an integer value of the hash.
            $faultcode = base_convert(md5($ex->errorcode), 16, 10);
            // We strip the $code to 8 digits (and hope for no error code collisions).
            // Collisions should be pretty rare, and if needed the client can retrieve
            // the accurate errorcode from the last | in the exception message.
            $faultcode = substr($faultcode, 0, 8);
            // Add the debuginfo to the exception message if debuginfo must be returned.
            if (ws_debugging() and isset($ex->debuginfo)) {
                $error .= ' | DEBUG INFO: ' . $ex->debuginfo . ' | ERRORCODE: ' . $ex->errorcode;
            }
            else {
                $error .= ' | ERRORCODE: ' . $ex->errorcode;
            }
        }
        $fault = array(
            'faultCode' => (int) $faultcode,
            'faultString' => $error
        );
        $encodingoptions = array(
            "encoding" => "UTF-8",
            "verbosity" => "no_white_space",
            // See MDL-54868.
            "escaping" => ["markup"]
        );
        return xmlrpc_encode_request(null, $fault, $encodingoptions);
    }
}

/**
 * XML-RPC test client class
 */
class webservice_xmlrpc_test_client implements webservice_test_client_interface {
    /**
     * Execute test client WS request
     * @param string $serverurl
     * @param string $function
     * @param array $params
     * @return mixed
     */
    public function simpletest($serverurl, $function, $params) {
        $params = array_values($params);

        require_once(get_config('docroot') . "webservice/mahara_url.php");
        $url = new mahara_url($serverurl);
        $token = $url->get_param('wstoken');
        require_once(get_config('docroot') . '/webservice/xmlrpc/lib.php');
        $client = new webservice_xmlrpc_client($serverurl, $token);
        return $client->call($function, $params);
    }
}
