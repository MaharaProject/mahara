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

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(__FILE__).'/lib.php');

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))).'/init.php');
    $errors = trim(ob_get_contents());
ob_end_clean();

// Content type for output is never html:
header('Content-type: text/xml; charset=utf-8');

if(!empty($errors)) throw new XmlrpcServerException('Initialization failed. Non-recoverable error.', 6000);

set_exception_handler('xmlrpc_exception');
ini_set('display_errors',0);

$payload = $HTTP_RAW_POST_DATA;

try {
    $xml = new SimpleXMLElement($payload);
} catch (Exception $e) {
    throw new XmlrpcServerException('Payload is not a valid XML document', 6001);
}

$openssl = new OpenSslRepo();

// Cascading switch
switch($xml->getName()){
    case 'encryptedMessage':
        $data = base64_decode($xml->EncryptedData->CipherData->CipherValue);
        $key  = base64_decode($xml->EncryptedKey->CipherData->CipherValue);
        $payload          = '';    // Initialize payload var
        $payload = $openssl->openssl_open($data, $key);
        try {
            $xml = new SimpleXMLElement($payload);
        } catch (Exception $e) {
            throw new XmlrpcServerException('Encrypted payload is not a valid XML document', 6002);
        }
    case 'signedMessage':
        $signature = base64_decode($xml->Signature->SignedInfo->DigestMethod->DigestValue);
        $payload   = base64_decode($xml->object);
        $wwwroot   = base64_decode($xml->wwwroot);
        $timestamp = base64_decode($xml->timestamp);

        

        // Does the signature match the data and the public cert?
        $signature_verified = openssl_verify($payload, $signature, $certificate);
        if ($signature_verified == 1) {
            // Parse the XML
        } elseif ($signature_verified == 0) {
            $currkey = mnet_get_public_key($MNET_REMOTE_CLIENT->wwwroot, $MNET_REMOTE_CLIENT->application->xmlrpc_server_url);
            if($currkey != $certificate) {
                // Has the server updated its certificate since our last 
                // handshake?
                if(!$MNET_REMOTE_CLIENT->refresh_key()) {
                    exit(mnet_server_fault(7026, 'verifysignature-invalid'));
                }
            } else {
                exit(mnet_server_fault(710, 'verifysignature-invalid'));
            }
        } else {
            exit(mnet_server_fault(711, 'verifysignature-error'));
        }
    case 'methodCall':
        // $payload ?
    break;
}

?>
