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

// Errors - grep for source
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
define('XMLRPC', 1);
require(dirname(__FILE__).'/lib.php');

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(dirname(__FILE__))).'/init.php');
    require_once(get_config('docroot') . 'api/xmlrpc/dispatcher.php');
    $errors = trim(ob_get_contents());
ob_end_clean();

// If networking is off, return a 404
$networkenabled = get_config('enablenetworking');
if (empty($networkenabled)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Content type for output is never html:
header('Content-type: text/xml; charset=utf-8');
ini_set('display_errors',0);
if(!empty($errors)) throw new XmlrpcServerException('Initialization failed. Non-recoverable error.', 6000);

// PHP 5.2.2: $HTTP_RAW_POST_DATA not populated bug:
// http://bugs.php.net/bug.php?id=41293
if (empty($HTTP_RAW_POST_DATA)) {
    $HTTP_RAW_POST_DATA = file_get_contents('php://input');
}

// A singleton provides our site's SSL info
$openssl = OpenSslRepo::singleton();
$payload           = $HTTP_RAW_POST_DATA;
$payload_encrypted = false;
$payload_signed    = false;

try {
    $xml = new SimpleXMLElement($payload);
} catch (Exception $e) {
    throw new XmlrpcServerException('Payload is not a valid XML document', 6001);
}

// Cascading switch. Kinda.
if($xml->getName() == 'encryptedMessage') {
    $payload_encrypted = true;
    $REMOTEWWWROOT     = (string)$xml->wwwroot;
    $payload           = xmlenc_envelope_strip($xml);
}

if($xml->getName() == 'signedMessage') {
    $payload_signed = true;
    $payload        = xmldsig_envelope_strip($xml);
}

if($xml->getName() == 'methodCall') {
    // $payload ?
    if(empty($xml->methodName)) {
        throw new XmlrpcServerException('Payload is not an XML-RPC document', 6008);
    }

    $Dispatcher = new Dispatcher($payload);

    if ($payload_signed) {
        $response = xmldsig_envelope($Dispatcher->response);
    } else {
        $response = $Dispatcher->response;
    }

    if ($payload_encrypted) {
        $peer     = get_peer($REMOTEWWWROOT);
        $response = xmlenc_envelope($response, $peer->certificate);
    }

    echo $response;

} else {
    throw new XmlrpcServerException('Unrecognized XML document form: ' . var_export($xml,1), 6009);
}

?>
