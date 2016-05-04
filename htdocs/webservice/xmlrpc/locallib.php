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

require_once 'Zend/XmlRpc/Server.php';

/**
* extend XML-RPC Server to add specific functions expected to support
* MNet
*/
class Zend_XmlRpc_Server_Local extends Zend_XmlRpc_Server {

    /**
     * Here we need to be able to add new functions to the call list
     *
     * @param array $functions
     */
    public function addFunctionsAsMethods($functions) {
        foreach ($functions as $key => $function) {

            // do a reflection on the function interface
            $reflection = new Zend_Server_Reflection_Function(new ReflectionFunction($function));

            // build up the method definition
            $definition = new Zend_Server_Method_Definition();
            $definition->setName($key)
                       ->setCallback($this->_buildCallback($reflection))
                       ->setMethodHelp($reflection->getDescription())
                       ->setInvokeArguments($reflection->getInvokeArguments());

            // here is where the parameters really get built up
            foreach ($reflection->getPrototypes() as $proto) {
                $prototype = new Zend_Server_Method_Prototype();
                $prototype->setReturnType($this->_fixType($proto->getReturnType()));
                foreach ($proto->getParameters() as $parameter) {
                    $param = new Zend_Server_Method_Parameter(array(
                        'type'     => $this->_fixType($parameter->getType()),
                        'name'     => $parameter->getName(),
                        'optional' => $parameter->isOptional(),
                    ));
                    if ($parameter->isDefaultValueAvailable()) {
                        $param->setDefaultValue($parameter->getDefaultValue());
                    }
                    $prototype->addParameter($param);
                }
                $definition->addPrototype($prototype);
            }

            // finally add the new function definition to the available call stack
            $this->_table->addMethod($definition, $key);
        }
    }

        /**
    * Generate a server fault
    *
    * Note that the arguments are reverse to those of Zend_XmlRpc_Server_Fault.
    *
    * note: the difference with the Zend server is that we throw a Zend_XmlRpc_Server_Fault exception
    * with the debuginfo integrated to the exception message when DEBUG >= NORMAL
    *
    * If an exception is passed as the first argument, its message and code
    * will be used to create the fault object if it has been registered via
    * {@Link registerFaultException()}.
    *
    * @param  string|Exception $fault
    * @param  string $code XMLRPC Fault Codes
    * @return Zend_XmlRpc_Server_Fault
    */
    public function fault($fault = null, $code = 404) {

        //run the zend code that clean/create a xmlrpcfault
        $xmlrpcfault = parent::fault($fault, $code);

        //intercept any exceptions and add the errorcode and debuginfo (optional)
        $actor = null;
        $details = null;
        if ($fault instanceof Exception) {
           //add the debuginfo to the exception message if debuginfo must be returned
            if (ws_debugging() and isset($fault->debuginfo)) {
                $details = $fault->debuginfo;
                $xmlrpcfault = new Zend_XmlRpc_Server_Fault($fault);
                $xmlrpcfault->setCode($fault->getCode());
                $xmlrpcfault->setMessage($fault->getMessage() . ' | ERRORCODE: ' . $fault->getCode() . ' | DETAILS: ' . $details);
            }
        }

        return $xmlrpcfault;
    }
}

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
class webservice_xmlrpc_server extends webservice_zend_server {

    private $payload_signed = false;
    private $payload_encrypted = false;
    public $publickey = null;

    /**
     * Contructor
     * @param integer $authmethod authentication method one of WEBSERVICE_AUTHMETHOD_*
     */
    public function __construct($authmethod) {
        require_once 'Zend/XmlRpc/Server.php';
        parent::__construct($authmethod, 'Zend_XmlRpc_Server_Local');
        $this->wsname = 'xmlrpc';
    }

    /**
     * Chance for each protocol to modify the function processing list
     */
    protected function fixup_functions() {
        // tell server what extra functions are available
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
        $this->zend_server->addFunctionsAsMethods($functions);
    }

    /**
     * Set up zend service class - mainly about fault handling
     *
     * @return void
     */
    protected function init_zend_server() {
        parent::init_zend_server();
        // this exception indicates request failed
        Zend_XmlRpc_Server_Fault::attachFaultException('WebserviceException');
        Zend_XmlRpc_Server_Fault::attachFaultException('MaharaException');
        Zend_XmlRpc_Server_Fault::attachFaultException('UserException');
        Zend_XmlRpc_Server_Fault::attachFaultException('NotFoundException');
        Zend_XmlRpc_Server_Fault::attachFaultException('SystemException');
        Zend_XmlRpc_Server_Fault::attachFaultException('InvalidArgumentException');
        Zend_XmlRpc_Server_Fault::attachFaultException('AccessDeniedException');
        Zend_XmlRpc_Server_Fault::attachFaultException('ParameterException');
        Zend_XmlRpc_Server_Fault::attachFaultException('WebserviceParameterException');
        Zend_XmlRpc_Server_Fault::attachFaultException('WebserviceInvalidParameterException');
        Zend_XmlRpc_Server_Fault::attachFaultException('WebserviceInvalidResponseException');
        if (ws_debugging()) {
            Zend_XmlRpc_Server_Fault::attachFaultException('Exception');
        }
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
     * For XML-RPC - we want to check for enc / sigs
     *
     * @return $xml
     */
    protected function modify_payload() {
        global $HTTP_RAW_POST_DATA;

        $xml = null;

        // check for encryption and signatures
        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN) {
            // we need the token so that we can find the key
            if (!$dbtoken = get_record('external_tokens', 'token', $this->token, 'tokentype', EXTERNAL_TOKEN_PERMANENT)) {
                // log failed login attempts
                throw new WebserviceAccessException(get_string('invalidtoken', 'auth.webservice'));
            }
            // is WS-Security active ?
            if ($dbtoken->wssigenc) {
                $this->publickey = $dbtoken->publickey;
            }
        }
        else if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            // get the user
            $user = get_record('usr', 'username', $this->username);
            if (empty($user)) {
                throw new WebserviceAccessException(get_string('wrongusernamepassword', 'auth.webservice'));
            }
            // get the institution from the external user
            $ext_user = get_record('external_services_users', 'userid', $user->id);
            if (empty($ext_user)) {
                throw new WebserviceAccessException(get_string('wrongusernamepassword', 'auth.webservice'));
            }
            // is WS-Security active ?
            if ($ext_user->wssigenc) {
                $this->publickey = $ext_user->publickey;
            }
        }

        // only both if we can find a public key
        $HTTP_RAW_POST_DATA = file_get_contents('php://input');
        if (!empty($this->publickey)) {
            // A singleton provides our site's SSL info
            require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
            $openssl = OpenSslRepo::singleton();
            $payload                 = $HTTP_RAW_POST_DATA;
            $this->payload_encrypted = false;
            $this->payload_signed    = false;

            try {
                $xml = new SimpleXMLElement($payload);
            } catch (Exception $e) {
                throw new XmlrpcServerException('Payload is not a valid XML document', 6001);
            }

            // Cascading switch. Kinda.
            try {
                if ($xml->getName() == 'encryptedMessage') {
                    $this->payload_encrypted = true;
                    $xml = xmlenc_envelope_strip($xml);
                    $payload = $xml;
                    $xml = new SimpleXMLElement($xml);
                }

                if ($xml->getName() == 'signedMessage') {
                    $this->payload_signed = true;
                    $payload = $this->xmldsig_envelope_strip($xml, $this->publickey);
                }

            }
            catch (CryptException $e) {
                if ($e->getCode() == 7025) {
                    // The key they used to contact us is old, respond with the new key correctly
                    // This sucks. Error handling of our mnet code needs to improve
                    ob_start();
                    xmlrpc_error($e->getMessage(), $e->getCode());
                    $response = ob_get_contents();
                    ob_end_clean();

                    // Sign and encrypt our response, even though we don't know if the
                    // request was signed and encrypted
                    $response = xmldsig_envelope($response);
                    $response = xmlenc_envelope($response, $this->publickey);
                    $payload = $response;
                }
            }
        }
        else {
            $payload = $HTTP_RAW_POST_DATA;
        }

        // if XML has been grabbed already then it must be turned into a request object
        if ($payload) {
            $request = new Zend_XmlRpc_Request();
            $result = $request->loadXML($payload);
            $payload = $request;
        }
        return $payload;
    }

    /**
     * Chance for each protocol to modify the out going
     * raw payload - eg: SOAP encryption and signatures
     *
     * @param string $response The raw response value
     *
     * @return content
     */
    protected function modify_result($response) {
        if (!empty($this->publickey)) {
            // do sigs + encrypt
            require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
            $openssl = OpenSslRepo::singleton();
            if ($this->payload_signed) {
                // Sign and encrypt our response, even though we don't know if the
                // request was signed and encrypted
                $response = xmldsig_envelope($response);
            }
            if ($this->payload_encrypted) {
                $response = xmlenc_envelope($response, $this->publickey);
            }
        }
        return $response;
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
        //zend expects 0 based array with numeric indexes
        $params = array_values($params);

        require_once 'Zend/XmlRpc/Client.php';
        $client = new Zend_XmlRpc_Client($serverurl);
        return $client->call($function, $params);
    }
}
