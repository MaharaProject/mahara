<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage auth
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

function xmlrpc_exception (Exception $e) {
    if (!($e instanceof XmlrpcServerException) || get_class($e) == 'XmlrpcServerException') {
        xmlrpc_error('An unexpected error has occurred.', 666);
        log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
        die();
    }
    $e->handle_exception();
}

/**
 * Output a valid XML-RPC error message.
 *
 * @param  string   $message              The error message
 * @param  int      $code                 Unique identifying integer
 * @return string                         An XMLRPC error doc
 */
function xmlrpc_error($message, $code) {
    echo <<<EOF
<?xml version="1.0"?>
<methodResponse>
   <fault>
      <value>
         <struct>
            <member>
               <name>faultCode</name>
               <value><int>$code</int></value>
            </member>
            <member>
               <name>faultString</name>
               <value><string>$message</string></value>
            </member>
         </struct>
      </value>
   </fault>
</methodResponse>
EOF;
}

/**
 * Xmlrpc Server exception - must output nice XML stuff to the client
 */
class XmlrpcServerException extends Exception {
    public function handle_exception() {
        xmlrpc_error($this->message, $this->code);
        die();
    }
}

/**
 * Encrypt a message and return it in an XML-Encrypted document
 *
 * This function can encrypt any content, but it was written to provide a system
 * of encrypting XML-RPC request and response messages. The message does not 
 * need to be text - binary data should work.
 * 
 * Asymmetric keys can encrypt only small chunks of data. Usually 1023 or 2047 
 * characters, depending on the key size. So - we generate a symmetric key and 
 * use the asymmetric key to secure it for transport with the data.
 *
 * We generate a symmetric key
 * We encrypt the symmetric key with the public key of the remote host
 * We encrypt our content with the symmetric key
 * We base64 the key & message data.
 * We identify our wwwroot - this must match our certificate's CN
 *
 * Normally, the XML-RPC document will be parceled inside an XML-SIG envelope.
 * We parcel the XML-SIG document inside an XML-ENC envelope.
 *
 * See the {@Link http://www.w3.org/TR/xmlenc-core/ XML-ENC spec} at the W3c
 * site
 *
 * @param  string   $message              The data you want to sign
 * @param  string   $remote_certificate   Peer's certificate in PEM format
 * @return string                         An XML-ENC document
 */
function xmlenc_envelope($message, $remote_certificate) {
    global $cfg;

    // Generate a key resource from the remote_certificate text string
    $publickey = openssl_get_publickey($remote_certificate);

    if ( gettype($publickey) != 'resource' ) {
        // Remote certificate is faulty.
        throw new XmlrpcServerException('Could not generate public key resource from certificate', 1);
        return false;
    }

    // Initialize vars
    $encryptedstring = '';
    $symmetric_keys = array();

    //      passed by ref ->      &$encryptedstring &$symmetric_keys
    $bool = openssl_seal($message, $encryptedstring, $symmetric_keys, array($publickey));
    $message = base64_encode($encryptedstring);
    $symmetrickey = base64_encode(array_pop($symmetric_keys));

    return <<<EOF
<?xml version="1.0" encoding="iso-8859-1"?>
    <encryptedMessage>
        <EncryptedData Id="ED" xmlns="http://www.w3.org/2001/04/xmlenc#">
            <EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#arcfour"/>
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:RetrievalMethod URI="#EK" Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey"/>
                <ds:KeyName>XMLENC</ds:KeyName>
            </ds:KeyInfo>
            <CipherData>
                <CipherValue>$message</CipherValue>
            </CipherData>
        </EncryptedData>
        <EncryptedKey Id="EK" xmlns="http://www.w3.org/2001/04/xmlenc#">
            <EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:KeyName>SSLKEY</ds:KeyName>
            </ds:KeyInfo>
            <CipherData>
                <CipherValue>$symmetrickey</CipherValue>
            </CipherData>
            <ReferenceList>
                <DataReference URI="#ED"/>
            </ReferenceList>
            <CarriedKeyName>XMLENC</CarriedKeyName>
        </EncryptedKey>
        <wwwroot>{$cfg->wwwroot}</wwwroot>
    </encryptedMessage>
EOF;
}

/**
 * Sign a message and return it in an XML-Signature document
 *
 * This function can sign any content, but it was written to provide a system of
 * signing XML-RPC request and response messages. The message will be base64
 * encoded, so it does not need to be text.
 *
 * We compute the SHA1 digest of the message.
 * We compute a signature on that digest with our private key.
 * We link to the public key that can be used to verify our signature.
 * We base64 the message data.
 * We identify our wwwroot - this must match our certificate's CN
 *
 * The XML-RPC document will be parceled inside an XML-SIG document, which holds
 * the base64_encoded XML as an object, the SHA1 digest of that document, and a
 * signature of that document using the local private key. This signature will
 * uniquely identify the RPC document as having come from this server.
 *
 * See the {@Link http://www.w3.org/TR/xmldsig-core/ XML-DSig spec} at the W3c
 * site
 *
 * @param  string   $message              The data you want to sign
 * @return string                         An XML-DSig document
 */
function xmldsig_envelope($message) {
    global $cfg;
    $digest = sha1($message);
    $sig = base64_encode($MNET->sign_message($message));
    $message = base64_encode($message);
    $time = time();

return <<<EOF
<?xml version="1.0" encoding="iso-8859-1"?>
    <signedMessage>
        <Signature Id="MoodleSignature" xmlns="http://www.w3.org/2000/09/xmldsig#">
            <SignedInfo>
                <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#dsa-sha1"/>
                <Reference URI="#XMLRPC-MSG">
                    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                    <DigestValue>$digest</DigestValue>
                </Reference>
            </SignedInfo>
            <SignatureValue>$sig</SignatureValue>
            <KeyInfo>
                <RetrievalMethod URI="{$CFG->wwwroot}/mnet/publickey.php"/>
            </KeyInfo>
        </Signature>
        <object ID="XMLRPC-MSG">$message</object>
        <wwwroot>{$cfg->wwwroot}</wwwroot>
        <timestamp>$time</timestamp>
    </signedMessage>
EOF;

}

class OpenSslRepo {
    
    private $keypair     = array();

    public function openssl_open($data, $key) {
        $this->populate();
        $payload = '';
        $isOpen = openssl_open($data, $payload, $key, $this->keypair['privatekey']);

        if (!empty($isOpen)) {
            return $payload;
        } else {
            // Decryption failed... let's try our archived keys
            $openssl_history = get_field('config', 'value', 'field', 'openssl_history');
            if(empty($openssl_history)) {
                $openssl_history = array();
                $record = new stdClass();
                $record->field = 'openssl_history';
                $record->value = serialize($openssl_history);
                insert_record('config', $record);
            } else {
                $openssl_history = unserialize($openssl_history);
            }
            foreach($openssl_history as $keyset) {
                $keyresource = openssl_pkey_get_private($keyset['keypair_PEM']);
                $isOpen      = openssl_open($data, $payload, $key, $keyresource);
                if ($isOpen) {
                    // It's an older code, sir, but it checks out
                    // We notify the remote host that the key has changed
                    throw new XmlrpcServerException($this->keypair['certificate'], 7025);
                }
            }
        }
    }

    private function populate() {
        if(empty($this->keypair)) {
            $records = get_records_select_menu('config', "field IN ('openssl_keypair', 'openssl_keypair_expires')", 'field', 'field, value');
            list($this->keypair['certificate'], $this->keypair['keypair_PEM']) = explode('@@@@@@@@', $records['openssl_keypair']);
            $this->keypair['expires'] = $records['openssl_keypair_expires'];
            $this->keypair['privatekey'] = openssl_pkey_get_private($this->keypair['keypair_PEM']);
            $this->keypair['publickey']  = openssl_pkey_get_public($this->keypair['certificate']);
        }
    }

    public function __get($name) {
        return null;
    }
}

class Peer {

    private $wwwroot              = '';
    private $deleted              = 0;
    private $ip_address           = '';
    private $name                 = '';
    private $public_key           = '';
    private $public_key_expires   = '';
    private $portno               = 80;
    private $last_connect_time    = 0;
    private $application          = 'moodle';
    private $application_display  = 'Moodle';
    private $xmlrpc_server_url    = '/mnet/xmlrpc/server.php';
    private $error                = array();

    function __construct() {
        return true;
    }

    function init($wwwroot) {
        global $cfg;
        $hostinfo = get_record('host', 'wwwroot', $wwwroot);
        $hostinfo = get_record_sql('
                                    SELECT
                                        host.wwwroot,
                                        host.deleted,
                                        host.ip_address,
                                        host.name,
                                        host.public_key,
                                        host.public_key_expires,
                                        host.portno,
                                        host.last_connect_time,
                                        application.application,
                                        application.application_display,
                                        application.xmlrpc_server_url
                                    FROM
                                        '.$cfg->dbprefix.'host,
                                        '.$cfg->dbprefix.'application
                                    WHERE
                                        host.application_id = application.id');
        if ($hostinfo != false) {
            foreach(get_object_vars($hostinfo) as $key => $value) {
                $this->{$key} = $value;
            }
            return true;
        }
        return false;
    }

    function __get($name) {
        return $this->{$name};
    }

    function bootstrap($wwwroot, $application = 'moodle') {

        if (substr($wwwroot, 0, -1) == '/') {
            $wwwroot = substr($wwwroot, 0, -1);
        }

        if ( ! $this->init($wwwroot) ) {
            $hostname = mnet_get_hostname_from_uri($wwwroot);

            // Get the IP address for that host - if this fails, it will
            // return the hostname string
            $ip_address = gethostbyname($hostname);

            // Couldn't find the IP address?
            if ($ip_address === $hostname && !preg_match('/^\d+\.\d+\.\d+.\d+$/',$hostname)) {
                $this->error[] = array('code' => 2, 'text' => get_string("noaddressforhost", 'mnet'));
                return false;
            }

            $this->name = $wwwroot;

            // TODO: In reality, this will be prohibitively slow... need another
            // default - maybe blank string
            $homepage = file_get_contents($wwwroot);
            if (!empty($homepage)) {
                $count = preg_match("@<title>(.*)</title>@siU", $homepage, $matches);
                if ($count > 0) {
                    $this->name = $matches[1];
                }
            }

            $this->wwwroot              = $wwwroot;
            $this->ip_address           = $ip_address;

            if(empty($pubkey)) {
                $this->public_key       = clean_param(mnet_get_public_key($this->wwwroot, $this->application), PARAM_PEM);
            } else {
                $this->public_key       = clean_param($pubkey, PARAM_PEM);
            }
            $this->public_key_expires   = $this->check_common_name($this->public_key);
            $this->last_connect_time    = 0;
            $this->last_log_id          = 0;
            if ($this->public_key_expires == false) {
                $this->public_key == '';
                return false;
            }
        }

        return true;
    }
}

class PublicKey {
    
    private $credentials = array();
    
    function __construct($keystring) {
        $credentials = openssl_x509_parse($keystring);
        if ($credentials == false) {
            $this->error[] = array('code' => 3, 'text' => get_string("nonmatchingcert", 'mnet', array('','')));
            return false;
        } elseif ($credentials['subject']['CN'] != $this->wwwroot) {
            $a[] = $credentials['subject']['CN'];
            $a[] = $this->wwwroot;
            $this->error[] = array('code' => 4, 'text' => get_string("nonmatchingcert", 'mnet', $a));
            return false;
        } else {
            return $credentials['validTo_time_t'];
        }
    }
}

/**
 * Get the remote machine's SSL Cert
 *
 * @param  string  $uri     The URI of a file on the remote computer, including
 *                          its http:// or https:// prefix
 * @return string           A PEM formatted SSL Certificate.
 */
function mnet_get_public_key($uri, $application=null) {
    global $CFG, $MNET;
    // The key may be cached in the mnet_set_public_key function...
    // check this first
    $key = mnet_set_public_key($uri);
    if ($key != false) {
        return $key;
    }

    if(empty($application)) {
        $this->application = get_record('application', 'name', 'moodle');
    }

    $rq = xmlrpc_encode_request('system/keyswap', array($CFG->wwwroot, $MNET->public_key), array("encoding" => "utf-8"));
    $ch = curl_init($uri. $application->xmlrpc_server_url);

    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rq);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));

    $res = xmlrpc_decode(curl_exec($ch));
    curl_close($ch);

    if (!is_array($res)) { // ! error
        $public_certificate = $res;
        $credentials=array();
        if (strlen(trim($public_certificate))) {
            $credentials = openssl_x509_parse($public_certificate);
            $host = $credentials['subject']['CN'];
            if (strpos($uri, $host) !== false) {
                mnet_set_public_key($uri, $public_certificate);
                return $public_certificate;
            }
        }
    }
    return false;
}
?>
