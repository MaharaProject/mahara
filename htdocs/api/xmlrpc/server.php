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
 * @subpackage core
 * @author     Donal McMullan <donal@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// Errors
// 6000     Initialization failed. Non-recoverable error.
// 6001     Payload is not a valid XML document
// 6002     Encrypted payload is not a valid XML document
// 6003     We don\'t have a record for your webserver in our database
// 6004     An error occurred while trying to verify your message signature
// 6005     The signature on your message was not valid
// 6006     The signature on your message was not valid
// 6007     Signed payload is not a valid XML document
// 6008     Payload is not an XML-RPC document
// 6009     Unrecognized XML document form
// 6010     The function does not exist
// 6011     The function does not exist

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(__FILE__).'/lib.php');


// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))).'/init.php');
    require_once($CFG->docroot .'/api/xmlrpc/request.php');
    $errors = trim(ob_get_contents());
ob_end_clean();

// Content type for output is never html:
//header('Content-type: text/xml; charset=utf-8');
header('Content-type: text/plain; charset=utf-8');

if(!empty($errors)) throw new XmlrpcServerException('Initialization failed. Non-recoverable error.', 6000);

set_exception_handler('xmlrpc_exception');
ini_set('display_errors',0);
error_reporting(E_ALL);
ini_set('display_errors', true);
/*
$f = new Peer();
$f->init($CFG->wwwroot);
$f->bootstrap($CFG->wwwroot, 'mahara');
$f->commit();
exit;
*/
//$payload = $HTTP_RAW_POST_DATA;
$payload =  '';

/**/
$openssl = OpenSslRepo::singleton();
$payload = '<?xml version="1.0"?>
<methodCall>
   <methodName>examples.getTime</methodName>
   <params>
      <param>
         <value><string>d/m/Y H:i:s</string></value>
         </param>
      </params>
   </methodCall>';
$payload = xmldsig_envelope($payload);
$payload = xmlenc_envelope($payload, $openssl->certificate);
/**/

try {
    $xml = new SimpleXMLElement($payload);
} catch (Exception $e) {
    throw new XmlrpcServerException('Payload is not a valid XML document', 6001);
}

// Cascading switch
if($xml->getName() == 'encryptedMessage') {
    $data = base64_decode($xml->EncryptedData->CipherData->CipherValue);
    $key  = base64_decode($xml->EncryptedKey->CipherData->CipherValue);
    $payload = '';    // Initialize payload var
    $payload = $openssl->openssl_open($data, $key);
    try {
        $xml = new SimpleXMLElement($payload);
    } catch (Exception $e) {
        throw new XmlrpcServerException('Encrypted payload is not a valid XML document', 6002);
    }
}



if($xml->getName() == 'signedMessage') {

    $signature = base64_decode($xml->Signature->SignatureValue);
    $payload   = base64_decode($xml->object);
    $wwwroot   = $xml->wwwroot;
    $timestamp = $xml->timestamp;

    $Peer = new Peer();
    if(!$Peer->init($wwwroot)) {
        // Bootstrap unknown hosts?
        throw new XmlrpcServerException('We don\'t have a record for your webserver in our database', 6003);
    }

    // Does the signature match the data and the public cert?
    $signature_verified = openssl_verify($payload, $signature, $Peer->public_key->certificate);

    if ($signature_verified == 1) {
        // Parse the XML
        try {
            $xml = new SimpleXMLElement($payload);
        } catch (Exception $e) {
            throw new XmlrpcServerException('Signed payload is not a valid XML document', 6007);
        }
    } elseif ($signature_verified == 0) {
        $currkey = get_public_key($MNET_REMOTE_CLIENT->wwwroot, $MNET_REMOTE_CLIENT->application->xmlrpc_server_url);
        if($currkey != $certificate) {
            // Has the server updated its certificate since our last 
            // handshake?
            if(!$MNET_REMOTE_CLIENT->refresh_key()) {
                throw new XmlrpcServerException('The signature on your message was not valid', 6006);
            }
        } else {
            throw new XmlrpcServerException('The signature on your message was not valid', 6005);
        }
    } else {
        throw new XmlrpcServerException('An error occurred while trying to verify your message signature', 6004);
    }
}

if($xml->getName() == 'methodCall') {
    // $payload ?
    if(empty($xml->methodName)) {
        throw new XmlrpcServerException('Payload is not an XML-RPC document', 6008);
    }
    //$Request = new Request($payload);
    $Dispatcher = new Dispatcher($payload);
    //var_dump($Request); 
} else {
    throw new XmlrpcServerException('Unrecognized XML document form', 6009);
}

?>