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

defined('INTERNAL') || die();

function xmlrpc_exception (Exception $e) {
    if (($e instanceof XmlrpcServerException) && get_class($e) == 'XmlrpcServerException') {
        $e->handle_exception();
        return;
    } elseif (($e instanceof MaharaException) && get_class($e) == 'MaharaException') {
        throw new XmlrpcServerException($e->getMessage(), $e->getCode());
        return;
    }
    xmlrpc_error('An unexpected error has occurred: '.$e->getMessage(), $e->getCode());
    log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
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

function generate_token() {
    return sha1(str_shuffle('' . mt_rand(999999,99999999) . microtime(true)));
}

function start_jump_session($peer, $instanceid, $wantsurl="") {
    global $USER;

    $rpc_negotiation_timeout = 15;
    $providers = get_service_providers($USER->authinstance);

    $approved = false;
    foreach ($providers as $provider) {
        if ($provider['wwwroot'] == $peer->wwwroot) {
            $approved = true;
            break;
        }
    }

    if (false == $approved) {
        // This shouldn't happen: the user shouldn't have been presented with 
        // the link
        throw new SystemException('Host not approved for sso');
    }

    // set up the session
    $sso_session = get_record('sso_session',
                              'userid',     $USER->id);
    if ($sso_session == false) {
        $sso_session = new stdClass();
        $sso_session->instanceid = $instanceid;
        $sso_session->userid = $USER->id;
        $sso_session->username = $USER->username;
        $sso_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
        $sso_session->token = generate_token();
        $sso_session->confirmtimeout = time() + $rpc_negotiation_timeout;
        $sso_session->expires = time() + (integer)ini_get('session.gc_maxlifetime');
        $sso_session->sessionid = session_id();
        if (! insert_record('sso_session', $sso_session)) {
            throw new SQLException("database error");
        }
    } else {
        $sso_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
        $sso_session->token = generate_token();
        $sso_session->instanceid = $instanceid;
        $sso_session->confirmtimeout = time() + $rpc_negotiation_timeout;
        $sso_session->expires = time() + (integer)ini_get('session.gc_maxlifetime');
        $sso_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
        $sso_session->sessionid = session_id();
        if (false == update_record('sso_session', $sso_session, array('userid' => $USER->id))) {
            throw new SQLException("database error");
        }
    }

    $wwwroot = dropslash(get_config('wwwroot'));

    // construct the redirection URL
    $url = "{$peer->wwwroot}{$peer->application->ssolandurl}?token={$sso_session->token}&idp={$wwwroot}&wantsurl={$wantsurl}";

    return $url;
}

function api_dummy_method($methodname, $argsarray, $functionname) {
    return call_user_func_array($functionname, $argsarray);
}

function fetch_user_image($username) {
    global $REMOTEWWWROOT;

    $institution = get_field('host', 'institution', 'wwwroot', $REMOTEWWWROOT);

    if (false == $institution) {
        // This should never happen, because if we don't know the host we'll
        // already have exited
        throw new XmlrpcServerException('Unknown error');
    }

    $dbprefix      = get_config('dbprefix');
    $authinstances = auth_get_auth_instances_for_institution($institution);
    $candidates    = array();

    $sql = 'SElECT
                ai.*
            FROM
                '.$dbprefix.'auth_instance ai,
                '.$dbprefix.'auth_instance ai2,
                '.$dbprefix.'auth_instance_config aic
            WHERE
                ai.id = ? AND
                ai.institution = ? AND
                ai2.institution = ai.institution AND
                ai.id = aic.value AND
                aic.field = \'parent\' AND
                aic.instance = ai2.id AND
                ai2.authname = \'xmlrpc\'';

    foreach ($authinstances as $authinstance) {
        if ($authinstance->authname != 'xmlrpc') {
            $records = get_records_sql_array($sql, array($authinstance->id, $institution));
            if (false == $records) {
                continue;
            }
        }
        try {
            $user = new User;
            $user->find_by_instanceid_username($authinstance->id, $username);
            $candidates[$user->id] = $user;
        } catch (Exception $e) {
            // we don't care
            continue;
        } 
    }

    if (count($candidates) != 1) {
        return false;
    }

    $user = array_pop($candidates);
    
    $ic = $user->profileicon;
    if (!empty($ic)) {
        $filename = get_config('dataroot') . 'artefact/internal/profileicons/' . ($user->profileicon % 256) . '/'.$user->profileicon;
        $return = array();
        try {
            $fi = file_get_contents($filename);
        } catch (Exception $e) {
            // meh
        }

        $return['f1'] = base64_encode($fi);

        require_once('file.php');
        $im = get_dataroot_image_path('artefact/internal/profileicons' , $user->profileicon, '100x100');
        $fi = file_get_contents($im);
        $return['f2'] = base64_encode($fi);
        return $return;
    } else {
        // no icon
    }
}

function user_authorise($token, $useragent) {
    global $USER;

    $sso_session = get_record('sso_session', 'token', $token, 'useragent', $useragent);
    if (empty($sso_session)) {
        throw new XmlrpcServerException('No such session exists');
    }

    // check session confirm timeout
    if ($sso_session->expires < time()) {
        throw new XmlrpcServerException('This session has timed out');
    }

    // session okay, try getting the user
    $user = new User();
    try {
        $user->find_by_id($sso_session->userid);
    } catch (Exception $e) {
        throw new XmlrpcServerException('Unable to get information for the specified user');
    }

    require(get_config('docroot') . 'artefact/lib.php');
    require(get_config('docroot') . 'artefact/internal/lib.php');

    $element_list = call_static_method('ArtefactTypeProfile', 'get_all_fields');
    $element_required = call_static_method('ArtefactTypeProfile', 'get_mandatory_fields');

    // load existing profile information
    $profilefields = array();
    $profile_data = get_records_select_assoc('artefact', "owner=? AND artefacttype IN (" . join(",",array_map(create_function('$a','return db_quote($a);'),array_keys($element_list))) . ")", array($USER->get('id')), '','artefacttype, title');

    $email = get_field('artefact_internal_profile_email', 'email', 'owner', $sso_session->userid, 'principal', 1);
    if (false == $email) {
        throw new XmlrpcServerException("No email adress for user");
    }

    $userdata = array();
    $userdata['username']                = $user->username;
    $userdata['email']                   = $email;
    $userdata['auth']                    = 'mnet';
    $userdata['confirmed']               = 1;
    $userdata['deleted']                 = 0;
    $userdata['firstname']               = $user->firstname;
    $userdata['lastname']                = $user->lastname;
    $userdata['city']                    = array_key_exists('city', $profile_data) ? $profile_data['city']->title : '';
    $userdata['country']                 = array_key_exists('country', $profile_data) ? $profile_data['country']->title : '';

    if (is_numeric($user->profileicon)) {
        $filename = get_config('dataroot') . 'artefact/internal/profileicons/' . ($user->profileicon % 256) . '/'.$user->profileicon;
        if (file_exists($filename) && is_readable($filename)) {
            $userdata['imagehash'] = sha1_file($filename);
        }
    }

    get_service_providers($USER->authinstance);

    // Todo: push application name to list of hosts... update Moodle block to display more info, maybe in 'Other' list
    $userdata['myhosts'] = array();
    $userdata['myhosts'][] = array('name'=> $SITE->shortname, 'url' => get_config('wwwroot'), 'count' => 0);

    return $userdata;
}

/**
 * Given a USER, get all Service Providers for that User, based on child auth
 * instances of its canonical auth instance
 */
function get_service_providers($instance) {
    static $cache = array();

    if (defined('INSTALLER')) {
        return array();
    }

    if (array_key_exists($instance, $cache)) {
        return $cache[$instance];
    }

    $dbprefix = get_config('dbprefix');

    $query = '
        SELECT
            h.name,
            a.ssolandurl,
            h.wwwroot,
            aic.instance
        FROM
            '.$dbprefix.'auth_instance_config aic,
            '.$dbprefix.'auth_instance_config aic2,
            '.$dbprefix.'auth_instance_config aic3,
            '.$dbprefix.'host h,
            '.$dbprefix.'application a
        WHERE
          ((aic.value = 1 AND
            aic.field = \'theyautocreateusers\' ) OR
           (aic.value = ?  AND
            aic.field = \'parent\')) AND

            aic.instance = aic2.instance AND
            aic2.field = \'wwwroot\' AND
            aic2.value = h.wwwroot AND

            aic.instance = aic3.instance AND
            aic3.field = \'wessoout\' AND
            aic3.value = \'1\' AND

            a.name = h.appname';
    try {
        $results = get_records_sql_assoc($query, array('value' => $instance));
    } catch (SQLException $e) {
        // Table doesn't exist yet
        return array();
    }

    if (false == $results) {
        $results = array();
    }

    foreach($results as $key => $result) {
        $results[$key] = get_object_vars($result);
    }

    $cache[$instance] = $results;
    return $cache[$instance];
}

function get_public_key($uri, $application=null) {

    static $keyarray = array();
    if (isset($keyarray[$uri])) {
        return $keyarray[$uri];
    }

    $openssl = OpenSslRepo::singleton();

    if (empty($application)) {
        $application = 'moodle';
    }

    $xmlrpcserverurl = get_field('application', 'xmlrpcserverurl', 'name', $application);
    if (empty($xmlrpcserverurl)) {
        throw new XmlrpcClientException('Unknown application');
    } 
    $wwwroot = dropslash(get_config('wwwroot'));

    $rq = xmlrpc_encode_request('system/keyswap', array($wwwroot, $openssl->certificate), array("encoding" => "utf-8"));
    $ch = curl_init($uri . $xmlrpcserverurl);

    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rq);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));

    $raw = curl_exec($ch);
    if (empty($raw)) {
        throw new XmlrpcClientException('CURL connection failed');
    }

    $response_code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response_code_prefix = substr($response_code, 0, 1);

    if ('2' != $response_code_prefix) {
        if ('4' == $response_code_prefix) {
            throw new XmlrpcClientException('Client error code: ', $response_code);
        } elseif ('5' == $response_code_prefix) {
            throw new XmlrpcClientException('An error occurred at the remote server. Code: ', $response_code);
        }
    }

    $res = xmlrpc_decode($raw);
    curl_close($ch);

    // XMLRPC error messages are returned as an array
    // We are expecting a string
    if (!is_array($res)) {
        $keyarray[$uri] = $res;
        $credentials=array();
        if (strlen(trim($keyarray[$uri]))) {
            $credentials = openssl_x509_parse($keyarray[$uri]);
            $host = $credentials['subject']['CN'];
            if (strpos($uri, $host) !== false) {
                return $keyarray[$uri];
            }
        }
    } else {
        throw new XmlrpcClientException($res['faultString'], $res['faultCode']);
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

function xmlenc_envelope_strip(&$xml) {
    $openssl           = OpenSslRepo::singleton();
    $payload_encrypted = true;
    $data              = base64_decode($xml->EncryptedData->CipherData->CipherValue);
    $key               = base64_decode($xml->EncryptedKey->CipherData->CipherValue);
    $payload           = '';    // Initialize payload var
    $payload           = $openssl->openssl_open($data, $key);
    $xml               = parse_payload($payload);
    return $payload;
}

function parse_payload($payload) {
    try {
        $xml = new SimpleXMLElement($payload);
        return $xml;
    } catch (Exception $e) {
        throw new MaharaException('Encrypted payload is not a valid XML document', 6002);
    }
}

function get_peer($wwwroot) {

    $wwwroot = (string)$wwwroot;
    static $peers = array();
    if (isset($peers[$wwwroot])) return $peers[$wwwroot];

    require_once(get_config('libroot') . 'peer.php');
    $peer = new Peer();

    if (!$peer->findByWwwroot($wwwroot)) {
        // Bootstrap unknown hosts?
        throw new MaharaException('We don\'t have a record for your webserver in our database', 6003);
    }
    $peers[$wwwroot] = $peer;
    return $peers[$wwwroot];
}

/**
 * Check that the signature has been signed by the remote host.
 */
function xmldsig_envelope_strip(&$xml) {

    $signature      = base64_decode($xml->Signature->SignatureValue);
    $payload        = base64_decode($xml->object);
    $wwwroot        = (string)$xml->wwwroot;
    $timestamp      = $xml->timestamp;
    $peer           = get_peer($wwwroot);


    // Does the signature match the data and the public cert?
    $signature_verified = openssl_verify($payload, $signature, $peer->certificate);

    if ($signature_verified == 1) {
        // Parse the XML
        try {
            $xml = new SimpleXMLElement($payload);
            return $payload;
        } catch (Exception $e) {
            throw new MaharaException('Signed payload is not a valid XML document', 6007);
        }
    } else {
        throw new MaharaException('An error occurred while trying to verify your message signature', 6004);
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

    // Generate a key resource from the remote_certificate text string
    $publickey = openssl_get_publickey($remote_certificate);

    if ( gettype($publickey) != 'resource' ) {
        // Remote certificate is faulty.
        throw new MaharaException('Could not generate public key resource from certificate', 1);
    }

    // Initialize vars
    $wwwroot = dropslash(get_config('wwwroot'));
    $encryptedstring = '';
    $symmetric_keys = array();

    //      passed by ref ->      &$encryptedstring &$symmetric_keys
    $bool = openssl_seal($message, $encryptedstring, $symmetric_keys, array($publickey));
    $message = base64_encode($encryptedstring);
    $symmetrickey = base64_encode(array_pop($symmetric_keys));
    $zed = 'nothing';

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
        <wwwroot>{$wwwroot}</wwwroot>
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

    $openssl = OpenSslRepo::singleton();
    $wwwroot = dropslash(get_config('wwwroot'));
    $digest = sha1($message);

    $sig = base64_encode($openssl->sign_message($message));
    $message = base64_encode($message);
    $time = time();
    // TODO: Provide RESTful access to our public key as per KeyInfo element

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
                <RetrievalMethod URI="{$wwwroot}/api/xmlrpc/publickey.php"/>
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

    /**
     * Sign a message with our private key so that peers can verify that it came
     * from us.
     *
     * @param  string   $message
     * @return string
     * @access public
     */
    public function sign_message($message) {
        $signature = '';
        $bool      = openssl_sign($message, $signature, $this->keypair['privatekey']);
        return $signature;
    }

    /**
     * Decrypt some data using our private key and an auxiliary symmetric key. 
     * The symmetric key encrypted the data, and then was itself encrypted with
     * our public key.
     * This is because asymmetric keys can only safely be used to encrypt 
     * relatively short messages.
     *
     * @param string   $data
     * @param string   $key
     * @return string
     * @access public
     */
    public function openssl_open($data, $key) {
        $payload = '';
        $isOpen = openssl_open($data, $payload, $key, $this->keypair['privatekey']);

        if (!empty($isOpen)) {
            return $payload;
        } else {
            // Decryption failed... let's try our archived keys
            $openssl_history = $this->get_history();
            foreach($openssl_history as $keyset) {
                $keyresource = openssl_pkey_get_private($keyset['keypair_PEM']);
                $isOpen      = openssl_open($data, $payload, $key, $keyresource);
                if ($isOpen) {
                    // It's an older code, sir, but it checks out
                    // We notify the remote host that the key has changed
                    throw new CryptException($this->keypair['certificate'], 7025);
                }
            }
        }
        throw new CryptException('Invalid certificate', 7025);
    }

    /**
     * Singleton function keeps us from generating multiple instances of this
     * class
     *
     * @return object   The class instance
     * @access public
     */
    public static function singleton() {
        //single instance
        static $instance;

        //if we don't have the single instance, create one
        if (!isset($instance)) {
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
        if (empty($this->keypair)) {
            $this->get_keypair();
            $this->keypair['privatekey'] = openssl_pkey_get_private($this->keypair['keypair_PEM']);
            $this->keypair['publickey']  = openssl_pkey_get_public($this->keypair['certificate']);
        }
        return $this;
    }

    /**
     * Utility function to get old SSL keys from the config table, or create a 
     * blank record if none exists.
     *
     * @return array    Array of keypair hashes
     * @access private
     */
    private function get_history() {
        $openssl_history = get_field('config', 'value', 'field', 'openssl_history');
        if (empty($openssl_history)) {
            $openssl_history = array();
            $record = new stdClass();
            $record->field = 'openssl_history';
            $record->value = serialize($openssl_history);
            insert_record('config', $record);
        } else {
            $openssl_history = unserialize($openssl_history);
        }
        return $openssl_history;
    }

    /**
     * Utility function to stash old SSL keys in the config table. It will retain
     * a max of 'openssl_generations' which is itself a value in config.
     *
     * @param  array    Array of keypair hashes
     * @return bool
     * @access private
     */
    private function save_history($openssl_history) {
        $openssl_generations = get_field('config', 'value', 'field', 'openssl_generations');
        if (empty($openssl_generations)) {
            set_config('openssl_generations', 6);
            $openssl_generations = 6;
        }
        if (count($openssl_history) > $openssl_generations) {
            $openssl_history = array_slice($openssl_history, 0, $openssl_generations);
        }
        return set_config('openssl_history', serialize($openssl_history));
    }

    /**
     * The get Overloader will let you pull out the 'certificate' and 'expires'
     * values
     *
     * @param  string    Name of the value you want
     * @return mixed     The value of the thing you asked for or null (if it 
     *                   doesn't exist or is private)
     * @access public
     */
    public function __get($name) {
        if ('certificate' === $name) return $this->keypair['certificate'];
        if ('expires' === $name)     return $this->keypair['expires'];
        return null;
    }

    /**
     * Get the keypair. If it doesn't exist, create it. If it's out of date, 
     * archive it and create a fresh pair.
     *
     * @param  bool      True if you want to force fresh keys to be generated
     * @return bool     
     * @access private
     */
    private function get_keypair($regenerate = null) {
        $this->keypair = array();
        $records       = null;
        
        if (empty($regenerate)) {
            $records = get_records_select_menu('config', "field IN ('openssl_keypair', 'openssl_keypair_expires')", 'field', 'field, value');
            if (!empty($records)) {
                list($this->keypair['certificate'], $this->keypair['keypair_PEM']) = explode('@@@@@@@@', $records['openssl_keypair']);
                $this->keypair['expires'] = $records['openssl_keypair_expires'];
                if ($this->keypair['expires'] <= time()) {
                    $openssl_history = $this->get_history();
                    array_unshift($openssl_history, $this->keypair);
                    $this->save_history($openssl_history);
                } else {
                    return true;
                }
            }
        }

        // Initialize a new set of SSL keys
        $this->keypair = array();
        $this->generate_keypair();

        // A record for the keys
        $keyrecord = new stdClass();
        $keyrecord->field = 'openssl_keypair';
        $keyrecord->value = implode('@@@@@@@@', $this->keypair);

        // A convenience record for the keys' expire time (UNIX timestamp)
        $expiresrecord        = new stdClass();
        $expiresrecord->field = 'openssl_keypair_expires';

        // Getting the expire timestamp is convoluted, but required:
        $credentials = openssl_x509_parse($this->keypair['certificate']);
        if (is_array($credentials) && isset($credentials['validTo_time_t'])) {
            $expiresrecord->value = $credentials['validTo_time_t'];
            $this->keypair['expires'] = $credentials['validTo_time_t'];
        }

        if (empty($records)) {
                   $result = insert_record('config', $keyrecord);
            return $result & insert_record('config', $expiresrecord);
        } else {
                   $result = update_record('config', $keyrecord,     array('field' => 'openssl_keypair'));
            return $result & update_record('config', $expiresrecord, array('field' => 'openssl_keypair_expires'));
        }
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
    private function generate_keypair() {
        $host = get_hostname_from_uri(get_config('wwwroot'));

        $organization = get_config('sitename');
        $email        = get_config('noreplyaddress');
        $country      = get_config('country');
        $province     = get_config('province');
        $locality     = get_config('locality');

        //TODO: Create additional fields on site setup and read those from 
        //      config. Then remove the next 3 linez
        if (empty($country))  $country  = 'NZ';
        if (empty($province)) $province = 'Wellington';
        if (empty($locality)) $locality = 'Te Aro';

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

class PublicKey {

    private   $credentials = array();
    private   $wwwroot     = '';
    private   $certificate = '';

    function __construct($keystring, $wwwroot) {

        $this->credentials = openssl_x509_parse($keystring);
        $this->wwwroot     = dropslash($wwwroot);
        $this->certificate = $keystring;

        if ($this->credentials == false) {
            throw new CryptException('This is not a valid SSL Certificate: '.$keystring, 1);
            return false;
        } elseif ($this->credentials['subject']['CN'] != $this->wwwroot) {
            throw new CryptException('This certificate does not match the server it claims to represent: '.$this->credentials['subject']['CN'] .', '. $this->wwwroot, 1);
            return false;
        } else {
            return $this->credentials;
        }
    }

    function __get($name) {
        if ('expires' == $name) return $this->credentials['validTo_time_t'];
        return $this->{$name};
    }
}
?>
