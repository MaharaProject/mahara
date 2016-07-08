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

require_once 'Zend/Soap/Client.php';

/**
 * Mahara SOAP client class
 */
class webservice_soap_client extends Zend_Soap_Client {

    public $serverurl;
    public $wsdl;
    public $wsdlfile;
    public $connection;

    /**
     * Constructor
     * @param string $serverurl
     * @param array $auth
     * @param array $options PHP SOAP client options - see php.net
     */
    public function __construct($serverurl, $auth, $options = null) {
        $this->serverurl = $serverurl;
        $values = array();
        foreach ($auth as $k => $v) {
            $values[]= "$k=" . urlencode($v);
        }
        $values []= 'wsdl=1';
        $this->auth = implode('&', $values);
        $this->wsdl = $this->serverurl . "?" . $this->auth;

        libxml_disable_entity_loader(true);
        $tempfile = tempnam(sys_get_temp_dir(), "wsdl_");
        if (($fp = fopen($tempfile, "w")) == false) {
            throw new Exception('Could not create local WDSL file ('.$tempfile.')');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->wsdl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        if (get_config('disablesslchecks')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        }

        if (($xml = curl_exec($ch)) === false) {
            fclose($fp);
            unlink($tempfile);

            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        fwrite($fp, $xml);
        fclose($fp);
        $this->wsdlfile = $tempfile;
        unset($options['wdsl_local_copy']);
        unset($options['wdsl_force_local_copy']);
        parent::__construct($this->wsdlfile, $options);
    }

    function __destruct() {
        unlink($this->wsdlfile);
    }

    public function set_connection($c) {
        $this->connection = $c;
    }

    /**
     * Set the token used to do the SOAP call
     * @param array $auth
     */
    public function set_auth($auth) {
        $values = array();
        foreach ($auth as $k => $v) {
            $values[]= "$k=" . urlencode($v);
        }
        $values []= 'wsdl=1';
        $this->auth = implode('&', $values);
        $this->wsdl = $this->serverurl . "?" . $this->auth;
        $this->setWsdl($this->wsdl);
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
        libxml_disable_entity_loader(false);
        $result = $this->__call($functionname, $params);
        libxml_disable_entity_loader(true);

        return $result;
    }

}

/**
 * Extended SOAP client class to handle WSSE authentication extension
 *
 */
class webservice_soap_client_wsse extends Zend_Soap_Client_Common {

    private $username;
    private $password;
    public $connection;
    public $serverurl;

    /**
     * Common Soap Client constructor
     *
     * @param callback $doRequestMethod
     * @param string $wsdl
     * @param array $options
     */
    function __construct($doRequestCallback, $wsdl, $options) {
        $this->serverurl = $wsdl;

        libxml_disable_entity_loader(false);
        parent::__construct($doRequestCallback, $wsdl, $options);
        libxml_disable_entity_loader(true);
    }
    public function set_connection($c) {
        $this->connection = $c;
    }

    /*Generates de WSSecurity header*/
    private function wssecurity_header() {

        /* The timestamp. The computer must be on time or the server you are
         * connecting may reject the password digest for security.
         */
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        /* A random word. The use of rand() may repeat the word if the server is
         * very loaded.
         */
        $nonce = mt_rand();
        /* This is the right way to create the password digest. Using the
         * password directly may work also, but it's not secure to transmit it
         * without encryption. And anyway, at least with axis+wss4j, the nonce
         * and timestamp are mandatory anyway.
         */
        $passdigest = base64_encode(
                pack('H*',
                        sha1(
                                pack('H*', $nonce) . pack('a*',$timestamp) .
                                pack('a*',$this->password))));

        $auth = '
<wsse:Security env:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.'.
'org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
<wsse:UsernameToken>
    <wsse:Username>' . $this->username . '</wsse:Username>
    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-'.
'wss-username-token-profile-1.0#PasswordDigest">' . $passdigest . '</wsse:Password>
    <wsse:Nonce>' . base64_encode(pack('H*', $nonce)) . '</wsse:Nonce>
    <wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-' .
'200401-wss-wssecurity-utility-1.0.xsd">' . $timestamp . '</wsu:Created>
   </wsse:UsernameToken>
</wsse:Security>
';
        $auth = '
<wsse:Security env:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.' .
'org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
<wsse:UsernameToken>
    <wsse:Username>' . $this->username . '</wsse:Username>
    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-' .
'wss-username-token-profile-1.0#PasswordText">' . $this->password . '</wsse:Password>
   </wsse:UsernameToken>
</wsse:Security>
';

        /* XSD_ANYXML (or 147) is the code to add xml directly into a SoapVar.
         * Using other codes such as SOAP_ENC, it's really difficult to set the
         * correct namespace for the variables, so the axis server rejects the
         * xml.
         */
        $authvalues = new SoapVar($auth,XSD_ANYXML);
        $header = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-" .
            "200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvalues,
                true);

        return $header;
    }

    /* It's necessary to call it if you want to set a different user and
     * password
     */
    public function __setUsernameToken($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }


    /* Overwrites the original method adding the security header. As you can
     * see, if you want to add more headers, the method needs to be modifyed
     */
    public function __soapCall($function_name, $arguments, $options=null,
            $input_headers=null, $output_headers=null) {

        $result = parent::__soapCall($function_name, $arguments, $options,
                $this->wssecurity_header());

        return $result;
    }

    /**
     * internal callback overridden with one_way set to 0
     *
     * @see Zend_Soap_Client_Common::__doRequest()
     */
    public function __doRequest($request,$location,$action,$version,$one_way = 0) {
        return parent::__doRequest($request,$location,$action,$version,$one_way);
    }
}
