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

require_once('Zend/XmlRpc/Client.php');
require_once('Zend/Http/Client.php');
require_once('Zend/Http/Client/Adapter/Socket.php');

/**
 * XML-RPC client class
 */
class webservice_xmlrpc_client extends Zend_XmlRpc_Client {

    public $serverurl;
    public $conection;
    private $publickey;

    /**
     * Constructor
     * @param string $serverurl
     * @param array $auth
     */
    public function __construct($serverurl, $auth) {
        $this->serverurl = $serverurl;
        $this->set_auth($auth);
        if (get_config('disablesslchecks')) {
            $hostname = parse_url($serverurl, PHP_URL_HOST);
            $context = array('http' => array ('method' => 'POST',
                                        'request_fulluri' => true,),
                                );
            $context['ssl'] = array('verify_host' => false,
                               'verify_peer' => false,
                               'verify_peer_name' => false,
                               'SNI_server_name' => $hostname,
                               'SNI_enabled'     => true,);
            $context = stream_context_create($context);
            $client = new Zend_Http_Client($this->_serverAddress);
            $adapter = new Zend_Http_Client_Adapter_Socket($this->_serverAddress);
            $client->setAdapter($adapter);
            $adapter->setStreamContext($context);
            parent::__construct($this->_serverAddress, $client);
        }
        else {
            parent::__construct($this->_serverAddress);
        }

    }

    public function set_connection($c) {
        $this->connection = $c;
    }

    /**
     * Set the token used to do the XML-RPC call
     * @param array $auth
     */
    public function set_auth($auth) {
        $values = array();
        foreach ($auth as $k => $v) {
            $values[]= "$k=" . urlencode($v);
        }
        $this->auth = implode('&', $values);
        $this->_serverAddress = $this->serverurl . '?' . $this->auth;
    }

    /**
     * Execute client WS request
     * @param string $functionname
     * @param array $params
     * @return mixed
     */
    public function call($functionname, $params=array()) {
        //zend expects 0 based array with numeric indexes
        $params = array_values($params);

        //traditional Zend soap client call (integrating the token into the URL)
        $result = parent::call($functionname, $params);

        return $result;
    }

    /* set the username and password for the wsse header */
    public function setCertificate($publickey) {
        $this->publickey = $publickey;
    }

    /**
     * Perform an XML-RPC request and return a response.
     *
     * @param Zend_XmlRpc_Request $request
     * @param null|Zend_XmlRpc_Response $response
     * @return void
     * @throws Zend_XmlRpc_Client_HttpException
     */
    public function doRequest($request, $response = null) {
        $this->_lastRequest = $request;

        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $http = $this->getHttpClient();
        if ($http->getUri() === null) {
            $http->setUri($this->_serverAddress);
        }

        $http->setHeaders(array(
            'Content-Type: text/xml; charset=utf-8',
            'Accept: text/xml',
        ));

        if ($http->getHeader('user-agent') === null) {
            $http->setHeaders(array('User-Agent: Zend_XmlRpc_Client'));
        }

        $xml = $this->_lastRequest->__toString();
        if ($this->publickey) {
            require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
            $openssl = OpenSslRepo::singleton();
            $xml = xmldsig_envelope($xml);
            $xml = xmlenc_envelope($xml, $this->publickey);
        }
        $http->setRawData($xml);
        $httpResponse = $http->request(Zend_Http_Client::POST);

        if (! $httpResponse->isSuccessful()) {
            /**
             * Exception thrown when an HTTP error occurs
             * @see Zend_XmlRpc_Client_HttpException
             */
            require_once('Zend/XmlRpc/Client/HttpException.php');
            throw new Zend_XmlRpc_Client_HttpException(
                                    $httpResponse->getMessage(),
                                    $httpResponse->getStatus());
        }

        if ($response === null) {
            $response = new Zend_XmlRpc_Response();
        }
        $this->_lastResponse = $response;
        $payload = $httpResponse->getBody();

        try {
            $xml = new SimpleXMLElement($payload);
        }
        catch (Exception $e) {
            throw new XmlrpcServerException('Payload is not a valid XML document', 6001);
        }

        // Cascading switch. Kinda.
        try {
            if ($xml->getName() == 'encryptedMessage') {
                $payload = xmlenc_envelope_strip($xml);
                $xml = new SimpleXMLElement($payload);
            }

            if ($xml->getName() == 'signedMessage') {
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
                $xml = $response;
            }
        }

        $this->_lastResponse->loadXml($payload);
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
}
