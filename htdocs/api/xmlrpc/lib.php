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
    var_dump($e);exit;
    if (!($e instanceof XmlrpcServerException) || get_class($e) != 'XmlrpcServerException') {
        xmlrpc_error('An unexpected error has occurred: '.$e->getMessage(), $e->getCode());
        log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
        die();
    }
    $e->handle_exception();
}

function get_hostname_from_uri($uri = null) {
    $count = preg_match("@^(?:http[s]?://)?([A-Z0-9\-\.]+).*@i", $uri, $matches);
    if ($count > 0) return $matches[1];
    return false;
}

function dropslash($wwwroot) {
    if (substr($wwwroot, -1, 1) == '/') {
        return substr($wwwroot, 0, -1);
    }
    return $wwwroot;
}

function get_public_key($uri, $application=null) {
    return '-----BEGIN CERTIFICATE-----
MIICwjCCAiugAwIBAgIBADANBgkqhkiG9w0BAQQFADCBpjELMAkGA1UEBhMCTlox
EzARBgNVBAgTCldlbGxpbmd0b24xDzANBgNVBAcTBlRlIEFybzEPMA0GA1UEChMG
TWFoYXJhMQ8wDQYDVQQLEwZNYWhhcmExIzAhBgNVBAMTGmh0dHA6Ly9tYWhhcmEu
bWFob29kbGUuY29tMSowKAYJKoZIhvcNAQkBFhtub3JlcGx5QG1haGFyYS5tYWhv
b2RsZS5jb20wHhcNMDcwMzI5MjE0NTI4WhcNMDcwNDI2MjE0NTI4WjCBpjELMAkG
A1UEBhMCTloxEzARBgNVBAgTCldlbGxpbmd0b24xDzANBgNVBAcTBlRlIEFybzEP
MA0GA1UEChMGTWFoYXJhMQ8wDQYDVQQLEwZNYWhhcmExIzAhBgNVBAMTGmh0dHA6
Ly9tYWhhcmEubWFob29kbGUuY29tMSowKAYJKoZIhvcNAQkBFhtub3JlcGx5QG1h
aGFyYS5tYWhvb2RsZS5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBANYR
k7JNrSqQcSK1hH5cdpJ+MinhhnjcpTu/MXZXhkOejZ4JRdpQqFd9J+JW1MiTDeet
3l6nuB/Tt87wgf1vKO7WYV/Go/E3AHAp8WqeA+9z070zD+n1/Th5le+76RKNd28N
C+chgNrvVB4hGgm9rwUsaXHolIx+jm58A+OR7SAbAgMBAAEwDQYJKoZIhvcNAQEE
BQADgYEAnD2Sj+xAVx7cUtbZD2uCy2X0cfPUI8MxbF3MoUW/8YexAqVBTGnzBHR0
8lFK4lVupRAxT6tjwSWxWxaBFfUDkGkVPIP28xcpz9/AgYbCbLunZmU/9qBAK/p9
qQHy3ds1DIOoP02RSOt/zHh6lzmGEy+KzBd/cq7EwtzesrtKZk8=
-----END CERTIFICATE-----';
    global $CFG;
echo 'a';
    static $keyarray = array();
    if (isset($keyarray[$uri])) {
        return $keyarray[$uri];
    }

    $openssl = OpenSslRepo::singleton();

    if(empty($application)) {
        $this->application = get_record('application', 'application', 'moodle');
    }
echo ' b';
    $wwwroot = dropslash($CFG->wwwroot);
echo ' c';
    $rq = xmlrpc_encode_request('system/keyswap', array($wwwroot, $openssl->certificate), array("encoding" => "utf-8"));
    $ch = curl_init($uri. $application->xmlrpc_server_url);
echo ' d';
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rq);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));

    $res = xmlrpc_decode(curl_exec($ch));
echo "\nRes:\n$res\n\n";
    curl_close($ch);

    if (!is_array($res)) { // ! error
        $keyarray[$uri] = $res;
        $credentials=array();
        if (strlen(trim($keyarray[$uri]))) {
            $credentials = openssl_x509_parse($keyarray[$uri]);
            $host = $credentials['subject']['CN'];
            if (strpos($uri, $host) !== false) {
                mnet_set_public_key($uri, $keyarray[$uri]);
                return $keyarray[$uri];
            }
        }
    }
    return false;
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
        var_dump($this);
//        xmlrpc_error($this->message, $this->code);
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
    global $CFG;

    // Generate a key resource from the remote_certificate text string
    $publickey = openssl_get_publickey($remote_certificate);

    if ( gettype($publickey) != 'resource' ) {
        // Remote certificate is faulty.
        throw new XmlrpcServerException('Could not generate public key resource from certificate', 1);
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
        <wwwroot>{$CFG->wwwroot}</wwwroot>
        <X1>$zed</X1>
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
    echo 'Signing: '.$message."\n\n";
    global $CFG;
    $openssl = OpenSslRepo::singleton();
    $wwwroot = dropslash($CFG->wwwroot);
    $digest = sha1($message);
    echo '$digest: '."\n".$digest."\n";
    $sig = base64_encode($openssl->sign_message($message));
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
                <RetrievalMethod URI="{$wwwroot}/mnet/publickey.php"/>
            </KeyInfo>
        </Signature>
        <object ID="XMLRPC-MSG">$message</object>
        <wwwroot>{$wwwroot}</wwwroot>
        <timestamp>$time</timestamp>
    </signedMessage>
EOF;

}

/**
 * Good candidate to be a singleton
 */
class OpenSslRepo {

    private $keypair = array();

    public function sign_message($message) {
        $signature = '';
        $bool      = openssl_sign($message, $signature, $this->keypair['privatekey']);
        return $signature;
    }

    public function openssl_open($data, $key) {
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

    public static function singleton() {
        //single instance
        static $instance;

        //if we don't have the single instance, create one
        if(!isset($instance)) {
            $instance = new OpenSslRepo();
        }
        return($instance);
    }

    /**
     * This is a singleton - don't try to create an instance by doing:
     * $openssl = new OpenSslRepo();
     * Instead, use:
     * $openssl = OpenSslRepo::singleton();
     * 
     */
    private function __construct() {
        if(empty($this->keypair)) {

            $records = get_records_select_menu('config', "field IN ('openssl_keypair', 'openssl_keypair_expires')", 'field', 'field, value');
            if(empty($records)) {
                $this->mnet_generate_keypair();

                $newrecord = new stdClass();
                $newrecord->field = 'openssl_keypair';
                $newrecord->value = implode('@@@@@@@@', $this->keypair);
                insert_record('config',$newrecord);

                $newrecord = new stdClass();
                $newrecord->field = 'openssl_keypair_expires';

                $credentials = openssl_x509_parse($this->keypair['certificate']);
                $host = $credentials['subject']['CN'];
                if(is_array($credentials) && isset($credentials['validTo_time_t'])) {
                    $newrecord->value = $credentials['validTo_time_t'];
                }

                insert_record('config',$newrecord);
            } else {
                list($this->keypair['certificate'], $this->keypair['keypair_PEM']) = explode('@@@@@@@@', $records['openssl_keypair']);
                $this->keypair['expires'] = $records['openssl_keypair_expires'];
            }
            $this->keypair['privatekey'] = openssl_pkey_get_private($this->keypair['keypair_PEM']);
            $this->keypair['publickey']  = openssl_pkey_get_public($this->keypair['certificate']);
        }
        return $this;
    }

    public function __get($name) {
        if('certificate' === $name) return $this->keypair['certificate'];
        return null;
    }

    /**
     * Generate public/private keys and store in the config table
     *
     * Use the distinguished name provided to create a CSR, and then sign that CSR
     * with the same credentials. Store the keypair you create in the config table.
     * If a distinguished name is not provided, create one using the fullname of
     * 'the course with ID 1' as your organization name, and your hostname (as
     * detailed in $CFG->wwwroot).
     *
     * @param   array  $dn  The distinguished name of the server
     * @return  string      The signature over that text
     */
    private function mnet_generate_keypair() {
        global $CFG;
        $host = get_hostname_from_uri($CFG->wwwroot);

        $organization = get_config('sitename');
        $email        = get_config('noreplyaddress');
        $country      = get_config('country');
        $province     = get_config('province');
        $locality     = get_config('locality');

        //TODO: Create additional fields on site setup and read those from 
        //      config. Then remove the next 3 linez
        if(empty($country))  $country  = 'NZ';
        if(empty($province)) $province = 'Wellington';
        if(empty($locality)) $locality = 'Te Aro';

        $dn = array(
           "countryName" => $country,
           "stateOrProvinceName" => $province,
           "localityName" => $locality,
           "organizationName" => $organization,
           "organizationalUnitName" => 'Mahara',
           "commonName" => get_config('wwwroot'),
           "emailAddress" => $email
        );

        // ensure we remove trailing slashes
        $dn["commonName"] = preg_replace(':/$:', '', $dn["commonName"]);

        $new_key = openssl_pkey_new();
        $csr_rsc = openssl_csr_new($dn, $new_key, array('private_key_bits',2048));
        $selfSignedCert = openssl_csr_sign($csr_rsc, null, $new_key, 28 /*days*/);
        unset($csr_rsc); // Free up the resource

        // We export our self-signed certificate to a string.
        openssl_x509_export($selfSignedCert, $this->keypair['certificate']);
        openssl_x509_free($selfSignedCert);

        // Export your public/private key pair as a PEM encoded string. You
        // can protect it with an optional passphrase if you wish.
        $export = openssl_pkey_export($new_key, $this->keypair['keypair_PEM'] /* , $passphrase */);
        openssl_pkey_free($new_key);
        unset($new_key); // Free up the resource

        return $this;
    }
}

/*
class LocalHost extends Peer {
    function __construct() {
        return true;
    }

    function init() {
        global $CFG;
        $exists = parent::init(dropslash($CFG->wwwroot));
        if($exists) return true;

        return $this->bootstrap(dropslash($CFG->wwwroot), 'mahara');

    }
}
*/

class Peer {

    public $wwwroot              = '';
    public $deleted              = 0;
    public $ip_address           = '';
    public $name                 = '';
    public $public_key;
    public $public_key_expires   = '';
    public $portno               = 80;
    public $last_connect_time    = 0;
    public $application          = 'moodle';
    public $application_display  = 'Moodle';
    public $xmlrpc_server_url    = '/mnet/xmlrpc/server.php';
    public $error                = array();

    function __construct() {
        return true;
    }

    function init($wwwroot) {
        global $cfg;
        $wwwroot = dropslash($wwwroot);
        $hostinfo = get_record('host', 'wwwroot', $wwwroot);
        $query = '
                                    SELECT
                                        host.wwwroot,
                                        host.deleted,
                                        host.ip_address,
                                        host.name,
                                        host.public_key,
                                        host.public_key_expires,
                                        host.portno,
                                        host.last_connect_time,
                                        application.shortname,
                                        application.name,
                                        application.xmlrpc_server_url
                                    FROM
                                        '.$cfg->dbprefix.'host,
                                        '.$cfg->dbprefix.'application
                                    WHERE
                                        host.application = application.shortname AND
                                        host.wwwroot = ?';

        $hostinfo = get_record_sql($query, $wwwroot);

        if ($hostinfo != false) {
            foreach(get_object_vars($hostinfo) as $key => $value) {
                $this->{$key} = $value;
            }
            $this->public_key = new PublicKey($this->public_key, $this->wwwroot);
            return true;
        }
        return false;
    }

    function __get($name) {
        return $this->{$name};
    }

    function delete() {
        $this->deleted = 1;
    }

    function commit() {
        $host = new stdClass();
        $host->wwwroot              = $this->wwwroot;
        $host->deleted              = $this->deleted;
        $host->ip_address           = $this->ip_address;
        $host->name                 = $this->name;
        $host->public_key           = $this->public_key->certificate;
        $host->public_key_expires   = $this->public_key_expires;
        $host->portno               = $this->portno;
        $host->last_connect_time    = $this->last_connect_time;
        $host->application          = $this->application;
        var_dump($host);
        return insert_record('host',$host);
    }

    function bootstrap($wwwroot, $application = 'moodle') {

        $wwwroot = dropslash($wwwroot);

        if ( ! $this->init($wwwroot) ) {

            $hostname = get_hostname_from_uri($wwwroot);

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

            $this->application = get_field('application', 'name', 'name', $application);

            $this->application = $application;

            $this->wwwroot              = $wwwroot;
            $this->ip_address           = $ip_address;

            $this->public_key       = new PublicKey(get_public_key($this->wwwroot, $this->application), $this->wwwroot);

            $this->public_key_expires   = $this->public_key->expires;
            $this->last_connect_time    = 0;
            $this->last_log_id          = 0;
            if (false == $this->public_key->expires) {
                $this->public_key == null;
                return false;
            }
        }

        return true;
    }
}

class PublicKey {

    private $credentials = array();
    private $wwwroot     = '';
    public  $certificate = '';

    function __construct($keystring, $wwwroot) {
        
        $this->credentials = openssl_x509_parse($keystring);
        $this->wwwroot     = dropslash($wwwroot);
        $this->certificate = $keystring;

        if ($this->credentials == false) {
            throw new XmlrpcServerException('This is not a valid SSL Certificate', 1);
            return false;
        } elseif ($this->credentials['subject']['CN'] != $this->wwwroot) {
            throw new XmlrpcServerException('This certificate does not match the server it claims to represent: '.$this->credentials['subject']['CN'] .', '. $this->wwwroot, 1);
            return false;
        } else {
            return $this->credentials;
        }
    }

    function __get($name) {
        if('expires' == $name) return $this->credentials['validTo_time_t'];
        return $this->{$name};
    }
}
?>
