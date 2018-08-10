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
 * XML-RPC client class
 */
class webservice_xmlrpc_client {

    public $serverurl;
    protected $token;
    protected $user;
    protected $pass;

    /**
     * Constructor
     * @param string $serverurl
     * @param array $auth
     */
    public function __construct($serverurl, $token) {
        require_once(get_config('docroot') . "webservice/mahara_url.php");
        $this->serverurl = new mahara_url($serverurl);
        $this->token = isset($token['wstoken']) ? $token['wstoken'] : null;
        $this->user = isset($token['wsusername']) ? $token['wsusername'] : null;
        $this->pass = isset($token['wspassword']) ? $token['wspassword'] : null;
    }

    /**
     * Set the token used to do the XML-RPC call
     * @param array $token
     */
    public function set_token($token) {
        $this->token = $token;
    }

    /**
     * Execute client WS request
     * @param string $functionname
     * @param array $params
     * @return mixed
     */
    public function call($functionname, $params=array()) {
        if ($this->token) {
            $this->serverurl->param('wstoken', $this->token);
        }
        else if ($this->user) {
            $this->serverurl->param('wsusername', $this->user);
            $this->serverurl->param('wspassword', $this->pass);
        }

        $request = $this->encode_request($functionname, $params);

        // Set the headers.
        $headers = array(
            'Content-Length' => strlen($request),
            'Content-Type' => 'text/xml; charset=utf-8',
            'Host' => $this->serverurl->get_host(),
            'User-Agent' => 'Mahara XML-RPC Client/1.0',
        );
        // Get the response.
        $response = webservice_download_file_content($this->serverurl->out(false), $headers, $request);
        // Decode the response.
        $result = xmlrpc_decode($response);

        if (is_array($result) && xmlrpc_is_fault($result)) {
            throw new MaharaException($result['faultString']);
        }
        return $result;
    }

    /**
     * Generates XML for a method request.
     *
     * @param string $functionname Name of the method to call.
     * @param mixed $params Method parameters compatible with the method signature.
     * @return string
     */
    protected function encode_request($functionname, $params) {
        $outputoptions = array(
            'encoding' => 'utf-8',
            'escaping' => 'markup',
        );
        $params = array_values($params);
        return xmlrpc_encode_request($functionname, $params, $outputoptions);
    }

    /* set the username and password for the wsse header */
    public function setCertificate($publickey) {
        // Ignore this now - as we don't set cert via zend lib anymore
        // $this->publickey = $publickey;
    }
}
