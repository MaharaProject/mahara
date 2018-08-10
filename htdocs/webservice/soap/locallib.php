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
 * SOAP web service implementation classes and methods.
 *
 * @package   webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

require_once(get_config('docroot') . 'webservice/lib.php');
use webservice_soap\wsdl;

/**
 * SOAP service server implementation.
 * @author Petr Skoda (skodak)
 */
class webservice_soap_server extends webservice_base_server {

    /** @var mahara_url The server URL. */
    protected $serverurl;

    /** @var  SoapServer The Soap */
    protected $soapserver;

    /** @var  string The response. */
    protected $response;

    /** @var  string The class name of the virtual class generated for this web service. */
    protected $serviceclass;

    /** @var bool WSDL mode flag. */
    protected $wsdlmode;

    /** @var \webservice_soap\wsdl The object for WSDL generation. */
    protected $wsdl;

    /**
     * Constructor
     * @param string $authmethod authentication method of the web service (WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN, ...)
     */
    public function __construct($authmethod) {
        parent::__construct($authmethod);
        // must not cache wsdl - the list of functions is created on the fly
        ini_set('soap.wsdl_cache_enabled', '0');
        $this->wsname = 'soap';
        $this->wsdlmode = false;
    }

    /**
     * This method parses the $_POST and $_GET superglobals and looks for the following information:
     * - User authentication parameters:
     *   - Username + password (wsusername and wspassword), or
     *   - Token (wstoken)
     */
    protected function parse_request() {
        require_once(get_config('docroot') . "webservice/mahara_url.php");
        // Retrieve and clean the POST/GET parameters from the parameters specific to the server.
        parent::set_web_service_call_settings();

        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $this->username = param_variable('wsusername', null);
            $this->password = param_variable('wspassword', null);
            if (!$this->username || !$this->password) {
                $authdata = array();
                if (isset($_SERVER['REQUEST_URI'])) {
                    $uri = parse_url($_SERVER['REQUEST_URI']);
                    $rawquery = preg_replace('/\&amp;/', '&', $uri['query']);
                    parse_str($rawquery, $query);
                    $authdata = array($query['wsusername'], $query['wspassword']);
                }

                if (count($authdata) == 2) {
                    list($this->username, $this->password) = $authdata;
                }
            }
            $this->serverurl = new mahara_url('/webservice/soap/server.php');
            $this->serverurl->param('wsusername', $this->username);
            $this->serverurl->param('wspassword', $this->password);
        }
        else {
            $this->token = param_alphanumext('wstoken', null);
            $this->serverurl = new mahara_url('/webservice/soap/server.php');
            $this->serverurl->param('wstoken', $this->token);
        }

        if ($wsdl = param_integer('wsdl', 0)) {
            $this->wsdlmode = true;
        }
    }

    /**
     * Runs the SOAP web service.
     *
     * @throws WebserviceCodingException
     * @throws MaharaException
     * @throws WebserviceAccessException
     */
    public function run() {
        // We will probably need a lot of memory in some functions.
        raise_memory_limit('128M');

        // Set some longer timeout since operations may need longer time to finish.
        external_api::set_timeout();

        // Set up exception handler.
        set_exception_handler(array($this, 'exception_handler'));

        // Init all properties from the request data.
        $this->parse_request();

        // Authenticate user, this has to be done after the request parsing. This also sets up $USER and $SESSION.
        $this->authenticate_user();

        // Make a list of all functions user is allowed to execute.
        $this->init_service_class();

        if ($this->wsdlmode) {
            // Generate the WSDL.
            $this->generate_wsdl();
        }

        // Handle the SOAP request.
        $this->handle();

        // Session cleanup.
        $this->session_cleanup();
        die;
    }

    /**
     * Load the virtual class needed for the web service.
     *
     * Initialises the virtual class that contains the web service functions that the user is allowed to use.
     * The web service function will be available if the user:
     * - is validly registered in the external_services_users table.
     * - has the required capability.
     * - meets the IP restriction requirement.
     * This virtual class can be used by web service protocols such as SOAP, especially when generating WSDL.
     */
    protected function init_service_class() {
        global $USER;
        // Initialise service methods and struct classes.
        $this->servicemethods = array();
        $this->servicestructs = array();
        $params = array();
        $wscond1 = '';
        $wscond2 = '';
        if ($this->restricted_serviceid) {
            $params = array($this->restricted_serviceid, $this->restricted_serviceid);
            $wscond1 = 'AND s.id = ?';
            $wscond2 = 'AND s.id = ?';
        }
        else if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $wscond1 = 'AND s.restrictedusers = 1';
            $wscond2 = 'AND s.restrictedusers = 1';
        }
        $sql = "SELECT s.*, NULL AS iprestriction
                FROM {external_services} s
                JOIN {external_services_functions} sf ON (sf.externalserviceid = s.id AND s.restrictedusers = 0)
                WHERE s.enabled = 1 " . $wscond1 . "
                UNION
                SELECT s.*, su.iprestriction
                FROM {external_services} s
                JOIN {external_services_functions} sf ON (sf.externalserviceid = s.id AND s.restrictedusers = 1)
                JOIN {external_services_users} su ON (su.externalserviceid = s.id AND su.userid = ?)
                WHERE s.enabled = 1 AND (su.validuntil IS NULL OR su.validuntil < ?) " . $wscond2;
        $params = array_merge($params, array($USER->id, time()));
        $serviceids = array();
        $remoteaddr = getremoteaddr();
        // Query list of external services for the user.
        $rs = get_records_sql_array($sql, $params);
        // Check which service ID to include.
        foreach ($rs as $service) {
            if (isset($serviceids[$service->id])) {
                continue; // Service already added.
            }
            if ($service->iprestriction && !address_in_subnet($remoteaddr, $service->iprestriction)) {
                continue; // Wrong request source ip, sorry.
            }
            $serviceids[$service->id] = $service->id;
        }
        // Generate the virtual class name.
        $classname = 'webservices_virtual_class_000000';
        while (class_exists($classname)) {
            $classname++;
        }
        $this->serviceclass = $classname;
        // Get the list of all available external functions.
        $wsmanager = new webservice();
        $functions = $wsmanager->get_external_functions($serviceids);
        // Generate code for the virtual methods for this web service.
        $methods = '';
        foreach ($functions as $function) {
            $methods .= $this->get_virtual_method_code($function);
        }
        $code = <<<EOD
/**
 * Virtual class web services for user id $USER->id.
 */
class $classname {
    $methods
}
EOD;
        // Load the virtual class definition into memory.
        eval($code);
    }

    /**
     * Returns a virtual method code for a web service function.
     *
     * @param stdClass $function a record from external_function
     * @return string The PHP code of the virtual method.
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function get_virtual_method_code($function) {
        $function = external_api::external_function_info($function);
        // Parameters and their defaults for the method signature.
        $paramanddefaults = array();
        // Parameters for external lib call.
        $params = array();
        $paramdesc = array();
        // The method's input parameters and their respective types.
        $inputparams = array();
        // The method's output parameters and their respective types.
        $outputparams = array();
        foreach ($function->parameters_desc->keys as $name => $keydesc) {
            $param = '$' . $name;
            $paramanddefault = $param;
            if ($keydesc->required == VALUE_OPTIONAL) {
                // It does not make sense to declare a parameter VALUE_OPTIONAL. VALUE_OPTIONAL is used only for array/object key.
                throw new moodle_exception('erroroptionalparamarray', 'webservice', '', $name);
            }
            else if ($keydesc->required == VALUE_DEFAULT) {
                // Need to generate the default, if there is any.
                if ($keydesc instanceof external_value) {
                    if ($keydesc->default === null) {
                        $paramanddefault .= ' = null';
                    }
                    else {
                        switch ($keydesc->type) {
                         case PARAM_BOOL:
                            $default = (int)$keydesc->default;
                            break;
                         case PARAM_INT:
                            $default = $keydesc->default;
                            break;
                            case PARAM_FLOAT;
                            $default = $keydesc->default;
                            break;
                         default:
                            $default = "'$keydesc->default'";
                        }
                        $paramanddefault .= " = $default";
                    }
                }
                else {
                    // Accept empty array as default.
                    if (isset($keydesc->default) && is_array($keydesc->default) && empty($keydesc->default)) {
                        $paramanddefault .= ' = array()';
                    }
                    else {
                        // For the moment we do not support default for other structure types.
                        throw new MaharaException('errornotemptydefaultparamarray', 'webservice', '', $name);
                    }
                }
            }
            $params[] = $param;
            $paramanddefaults[] = $paramanddefault;
            $type = $this->get_phpdoc_type($keydesc);
            $inputparams[$name]['type'] = $type;
            $paramdesc[] = '* @param ' . $type . ' $' . $name . ' ' . $keydesc->desc;
        }
        $paramanddefaults = implode(', ', $paramanddefaults);
        $paramdescstr = implode("\n ", $paramdesc);
        $serviceclassmethodbody = $this->service_class_method_body($function, $params);
        if (empty($function->returns_desc)) {
            $return = '* @return void';
        }
        else {
            $type = $this->get_phpdoc_type($function->returns_desc);
            $outputparams['return']['type'] = $type;
            $return = '* @return ' . $type . ' ' . $function->returns_desc->desc;
        }
        // Now create the virtual method that calls the ext implementation.
        $code = <<<EOD
/**
 */
public function $function->name($paramanddefaults) {
    $serviceclassmethodbody
}
EOD;
        // Prepare the method information.
        $methodinfo = new stdClass();
        $methodinfo->name = $function->name;
        $methodinfo->inputparams = $inputparams;
        $methodinfo->outputparams = $outputparams;
        $methodinfo->description = ''; // $function->description;
        // Add the method information into the list of service methods.
        $this->servicemethods[] = $methodinfo;
        return $code;
    }

    /**
     * Get the phpdoc type for an external_description object.
     * external_value => int, double or string
     * external_single_structure => object|struct, on-fly generated stdClass name.
     * external_multiple_structure => array
     *
     * @param mixed $keydesc The type description.
     * @return string The PHP doc type of the external_description object.
     */
    protected function get_phpdoc_type($keydesc) {
        $type = null;
        if ($keydesc instanceof external_value) {
            switch ($keydesc->type) {
             case PARAM_BOOL: // 0 or 1 only for now.
             case PARAM_INT:
                $type = 'int';
                break;
                case PARAM_FLOAT;
                $type = 'double';
                break;
             default:
                $type = 'string';
            }
        }
        else if ($keydesc instanceof external_single_structure) {
            $type = $this->generate_simple_struct_class($keydesc);
        }
        else if ($keydesc instanceof external_multiple_structure) {
            $type = 'array';
        }
        return $type;
    }

    /**
     * Generates the method body of the virtual external function.
     *
     * @param stdClass $function a record from external_function.
     * @param array $params web service function parameters.
     * @return string body of the method for $function ie. everything within the {} of the method declaration.
     */
    protected function service_class_method_body($function, $params) {
        // Cast the param from object to array (validate_parameters except array only).
        $castingcode = '';
        $paramsstr = '';
        if (!empty($params)) {
            foreach ($params as $paramtocast) {
                // Clean the parameter from any white space.
                $paramtocast = trim($paramtocast);
                $castingcode .= "    $paramtocast = json_decode(json_encode($paramtocast), true);\n";
            }
            $paramsstr = implode(', ', $params);
        }
        $descriptionmethod = $function->methodname . '_returns()';
        $callforreturnvaluedesc = $function->classname . '::' . $descriptionmethod;
        $methodbody = <<<EOD
$castingcode
    if ($callforreturnvaluedesc == null) {
        $function->classname::$function->methodname($paramsstr);
        return null;
    }
    return external_api::clean_returnvalue($callforreturnvaluedesc, $function->classname::$function->methodname($paramsstr));
EOD;
        return $methodbody;
    }

    /**
     * Generates the WSDL.
     */
    protected function generate_wsdl() {
        // Initialise WSDL.
        $this->wsdl = new wsdl($this->serviceclass, $this->serverurl);
        // Register service struct classes as complex types.
        foreach ($this->servicestructs as $structinfo) {
            $this->wsdl->add_complex_type($structinfo->classname, $structinfo->properties);
        }
        // Register the method for the WSDL generation.
        foreach ($this->servicemethods as $methodinfo) {
            $this->wsdl->register($methodinfo->name, $methodinfo->inputparams, $methodinfo->outputparams, $methodinfo->description);
        }
    }

    /**
     * Handles the web service function call.
     */
    protected function handle() {
        if ($this->wsdlmode) {
            // Prepare the response.
            $this->response = $this->wsdl->to_xml();
            // Send the results back in correct format.
            $this->send_response();
        }
        else {
            $wsdlurl = clone($this->serverurl);
            $wsdlurl->param('wsdl', 1);
            $options = array(
                'uri' => $this->serverurl->out(false)
            );
            // Initialise the SOAP server.
            $this->soapserver = new SoapServer($wsdlurl->out(false), $options);
            if (!empty($this->serviceclass)) {
                $this->soapserver->setClass($this->serviceclass);
                // Get all the methods for the generated service class then register to the SOAP server.
                $functions = get_class_methods($this->serviceclass);
                $this->soapserver->addFunction($functions);
            }

            // Get soap request from raw POST data.
            $soaprequest = file_get_contents('php://input');
            // Handle the request.
            try {
                $this->soapserver->handle($soaprequest);
            }
            catch (Exception $e) {
                $this->fault($e);
            }
        }
    }

    /**
     * Send the error information to the WS client formatted as an XML document.
     *
     * @param Exception $ex the exception to send back
     */
    protected function send_error($ex = null) {
        if ($ex) {
            $info = $ex->getMessage();
            if (isset($ex->debuginfo)) {
                $info .= ' - ' . $ex->debuginfo;
            }
        }
        else {
            $info = 'Unknown error';
        }

        // Initialise new DOM document object.
        $dom = new DOMDocument('1.0', 'UTF-8');

        // Fault node.
        $fault = $dom->createElement('SOAP-ENV:Fault');
        // Faultcode node.
        $fault->appendChild($dom->createElement('faultcode', 'MOODLE:error'));
        // Faultstring node.
        $fault->appendChild($dom->createElement('faultstring', $info));

        // Body node.
        $body = $dom->createElement('SOAP-ENV:Body');
        $body->appendChild($fault);

        // Envelope node.
        $envelope = $dom->createElement('SOAP-ENV:Envelope');
        $envelope->setAttribute('xmlns:SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');
        $envelope->appendChild($body);
        $dom->appendChild($envelope);

        $this->response = $dom->saveXML();
        $this->send_response();
    }

    /**
     * Send the result of function call to the WS client.
     */
    protected function send_response() {
        $this->send_headers();
        echo $this->response;
    }

    /**
     * Internal implementation - sending of page headers.
     */
    protected function send_headers() {
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
        header('Content-Length: ' . strlen($this->response));
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: inline; filename="response.xml"');
    }

    /**
     * Generate a server fault.
     *
     * Note that the parameter order is the reverse of SoapFault's constructor parameters.
     *
     * Moodle note: basically we return the faultactor (errorcode) and faultdetails (debuginfo).
     *
     * If an exception is passed as the first argument, its message and code
     * will be used to create the fault object.
     *
     * @link   http://www.w3.org/TR/soap12-part1/#faultcodes
     * @param  string|Exception $fault
     * @param  string $code SOAP Fault Codes
     */
    public function fault($fault = null, $code = 'Receiver') {
        $allowedfaultmodes = array(
            'VersionMismatch', 'MustUnderstand', 'DataEncodingUnknown',
            'Sender', 'Receiver', 'Server'
        );
        if (!in_array($code, $allowedfaultmodes)) {
            $code = 'Receiver';
        }

        // Intercept any exceptions and add the errorcode and debuginfo (optional).
        $actor = null;
        $details = null;
        $errorcode = 'unknownerror';
        $message = get_string($errorcode);
        if ($fault instanceof Exception) {
            // Add the debuginfo to the exception message if debuginfo must be returned.
            $actor = isset($fault->errorcode) ? $fault->errorcode : null;
            $errorcode = $actor;
            if (ws_debugging()) {
                $message = $fault->getMessage();
                $details = isset($fault->debuginfo) ? $fault->debuginfo : null;
            }
        }
        else if (is_string($fault)) {
            $message = $fault;
        }

        $this->soapserver->fault($code, $message . ' | ERRORCODE: ' . $errorcode, $actor, $details);
    }
}

/**
 * SOAP test client class
 *
 * @package    webservice_soap
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 */
class webservice_soap_test_client implements webservice_test_client_interface {

    /**
     * Execute test client WS request
     *
     * @param string $serverurl server url (including token parameter or username/password parameters)
     * @param string $function function name
     * @param array $params parameters of the called function
     * @return mixed
     */
    public function simpletest($serverurl, $function, $params) {
        require_once(get_config('docroot') . '/webservice/soap/lib.php');
        $client = new webservice_soap_client($serverurl);
        return $client->call($function, $params);
    }
}
