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
 * Mahara SOAP client class
 */
class webservice_soap_client {

    public $serverurl;
    private $token;
    private $user;
    private $pass;
    private $options;

    /**
     * Constructor
     * @param string $serverurl
     * @param array $token
     * @param array $options PHP SOAP client options - see php.net
     */
    public function __construct($serverurl, $token = null, array $options = null) {
        require_once(get_config('docroot') . "webservice/mahara_url.php");
        $this->serverurl = new mahara_url($serverurl);
        $this->token = isset($token['wstoken']) ? $token['wstoken'] : null;
        $this->user = isset($token['wsusername']) ? $token['wsusername'] : null;
        $this->pass = isset($token['wspassword']) ? $token['wspassword'] : null;
        $this->options = $options ? $options : array();
    }

    /**
     * Set the token used to do the SOAP call
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
    public function call($functionname, $params) {
        if ($this->token) {
            $this->serverurl->param('wstoken', $this->token);
        }
        else if ($this->user) {
            $this->serverurl->param('wsusername', $this->user);
            $this->serverurl->param('wspassword', $this->pass);
        }
        $this->serverurl->param('wsdl', 1);

        // expect 0 based array with numeric indexes
        $params = array_values($params);

        $opts = array(
            'http' => array(
                'user_agent' => 'Mahara SOAP Client'
            )
        );
        if (get_config('productionmode') === false) {
            $opts['ssl'] = array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            );
        }

        $context = stream_context_create($opts);
        $this->options['stream_context'] = $context;
        $this->options['cache_wsdl'] = WSDL_CACHE_NONE;
        $client = new SoapClient($this->serverurl->out(false), $this->options);
        return $client->__soapCall($functionname, $params);
    }
}

/**
 * Extended SOAP client class to handle WSSE authentication extension
 *
 */
class webservice_soap_client_wsse {

    private $username;
    private $password;
    private $options;
    private $serverurl;

    /**
     * Common Soap Client constructor
     *
     * @param callback $doRequestMethod
     * @param string $wsdl
     * @param array $options
     */
    function __construct($doRequestCallback, $wsdl, $options) {
        require_once(get_config('docroot') . "webservice/mahara_url.php");
        $this->serverurl = new mahara_url($wsdl);
        $this->serverurl = $wsdl;
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
            $input_headers=null, &$output_headers=null) {

        $client = new SoapClient($this->serverurl->out(false), $this->options);
        return $client->__soapCall($functionname, $arguments, $options, $this->wssecurity_header());
    }

    /**
     * internal callback overridden with one_way set to 0
     */
    public function __doRequest($request,$location,$action,$version,$one_way = 0) {
        $client = new SoapClient($this->serverurl->out(false), $this->options);
        return $client->__doRequest($request,$location,$action,$version,$one_way);
    }
}
