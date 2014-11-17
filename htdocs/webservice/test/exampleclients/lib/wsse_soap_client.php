<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Test the different web service protocols.
 *
 * @author     Piers Harding
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package web service
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 */

//create the soap client instance
class WSSoapClient extends Zend_Soap_Client_Common {
    private $username;
    private $password;

    /*Generates the WSSecurity header*/
    private function wssecurity_header() {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $nonce = mt_rand();
        $passdigest = base64_encode(pack('H*', sha1(
                                pack('H*', $nonce) . pack('a*',$timestamp) .
                                pack('a*',$this->password))));
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
        $authvalues = new SoapVar($auth,XSD_ANYXML);
        $header = new SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvalues, true);
        return $header;
    }

    /* set the username and password for the wsse header */
    public function setUsernamePassword($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /* Overwrites the original method adding the security header */
    public function __soapCall($function_name, $arguments, $options=null, $input_headers=null, $output_headers=null) {
        return parent::__soapCall($function_name, $arguments, $options, $this->wssecurity_header());
    }
}


/**
 * Class to encapsulate the need for a specialised SOAP client
 * for WSSE header extensions
 *
 * @author piers
 *
 */
class WSSE_Soap_Client extends Zend_Soap_Client {

    private $soapClient;
    /**
     * Constructor
     * @param string $wsdl URL to WSDL
     * @param string $username WSSE Username
     * @param string $password WSSE Password
     */
    public function __construct($wsdl, $options = null, $username=null, $password=null) {
        parent::__construct($wsdl, $options);
        $this->soapClient = new WSSoapClient(array($this, '_doRequest'), $wsdl, array_merge($this->getOptions(), ($options ? $options : array())));
        if ($username && $password) {
            $this->soapClient->setUsernamePassword($username, $password);
        }
        $this->setSoapClient($this->soapClient);
    }

    /* set the username and password for the wsse header */
    public function setUsernamePassword($username, $password) {
        $this->soapClient->setUsernamePassword($username, $password);
    }

    /**
     * Execute client WS request
     * @param string $functionname
     * @param array $params
     * @return mixed
     */
    public function call($functionname, $params) {
        //zend expects 0 based array with numeric indexes
        $params = array_values($params);

        //traditional Zend soap client call (integrating the token into the URL)
        $result = $this->__call($functionname, $params);

        return $result;
    }
}
