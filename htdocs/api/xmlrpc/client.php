<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage xmlrpc
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');

class Client {

    private $requesttext      = '';
    private $signedrequest    = '';
    private $encryptedrequest = '';
    private $timeout          = 60;
    private $params           = array();
    private $method           = '';

    function __construct() {
        return true;
    }

    function set_method($method) {
        if (is_string($method) && preg_match("@^[A-Za-z0-9]+/[A-Za-z0-9/_-]+(\.php/)?[A-Za-z0-9_-]+$@", $method)) {
            $this->method = $method;
        }
        return $this;
    }

    function send($wwwroot, $use_cached_peer=true) {
        $this->peer     = get_peer($wwwroot, $use_cached_peer);
        $this->response = '';
        $URL = $this->peer->wwwroot . $this->peer->application->xmlrpcserverurl;

        $ch = curl_init( $URL );

        $this->requesttext = xmlrpc_encode_request($this->method, $this->params, array("encoding" => "utf-8"));
        $this->signedrequest = xmldsig_envelope($this->requesttext);
        $this->encryptedrequest = xmlenc_envelope($this->signedrequest, $this->peer->certificate);

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mahara');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encryptedrequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8", 'Expect: '));

        if (strpos($URL, 'https://') === 0) {
            if ($cainfo = get_config('cacertinfo')) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
            }
        }

        $timestamp_send    = time();
        $this->rawresponse = curl_exec($ch);

        $response_code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response_code_prefix = substr($response_code, 0, 1);
    
        if ('2' != $response_code_prefix) {
            if ('4' == $response_code_prefix) {
                throw new XmlrpcClientException('Client error code: '. $response_code);
            }
            else if ('5' == $response_code_prefix) {
                throw new XmlrpcClientException('An error occurred at the remote server. Code: '. $response_code);
            }
        }

        $timestamp_receive = time();
        $remote_timestamp  = null;

        $curl_errno = curl_errno($ch);
        if ($curl_errno || $this->rawresponse == false) {
            throw new XmlrpcClientException('Curl error: ' . $curl_errno . ': ' . curl_error($ch));
            return false;
        }

        try {
            $xml = new SimpleXMLElement(trim($this->rawresponse));
        }
        catch (Exception $e) {
            log_debug($this->rawresponse);
            throw new XmlrpcClientException('Payload is not a valid XML document (payload is above)', 6001);
        }

        try {
            if ($xml->getName() == 'encryptedMessage') {
                $payload_encrypted = true;
                $wwwroot           = (string)$xml->wwwroot;
                // Strip encryption, using an older code is OK, because we're the client. 
                // The server is able to respond with the correct key, be we're not
                $payload           = xmlenc_envelope_strip($xml, true);
            }

            if ($xml->getName() == 'signedMessage') {
                $payload_signed   = true;
                $remote_timestamp = $xml->timestamp;
                $payload          = xmldsig_envelope_strip($xml);
            }
        }
        catch (CryptException $e) {
            throw new XmlrpcClientException("An error occured while decrypting a message sent by $wwwroot. Unable to authenticate the user.");
        }

        if ($xml->getName() == 'methodResponse') {
            $this->response = xmlrpc_decode($payload);

            // Margin of error is the time it took the request to complete.
            $margin_of_error  = $timestamp_receive - $timestamp_send;

            // Guess the time gap between sending the request and the remote machine
            // executing the time() function. Marginally better than nothing.
            $hysteresis       = ($margin_of_error) / 2;

            if (!empty($remote_timestamp)) {
                $remote_timestamp = $remote_timestamp - $hysteresis;
                $time_offset      = $remote_timestamp - $timestamp_send;
                if ($time_offset > self::get_max_server_time_difference()) {
                    throw new XmlrpcClientException('Time drift ('.$margin_of_error.', '.$time_offset.') is too large.');
                }
            }

            if (is_array($this->response) && array_key_exists('faultCode', $this->response)) {
                if ($this->response['faultCode'] == 7025) {
                    log_info('Remote application has sent us a new public key');
                    // The remote application sent back a new public key, the 
                    // old one must have expired
                    if (array_key_exists('faultString', $this->response)) {
                        $details = openssl_x509_parse($this->response['faultString']);
                        if (isset($details['validTo_time_t'])) {
                            $updateobj = (object)array(
                                'publickey' => $this->response['faultString'],
                                'publickeyexpires' => $details['validTo_time_t'],
                            );
                            $whereobj = (object)array(
                                'wwwroot' => $wwwroot,
                            );
                            update_record('host', $updateobj, $whereobj);
                            log_info('New key has been imported. Valid until ' . date('Y/m/d h:i:s', $details['validTo_time_t']));

                            // Send request again. But don't use the cached 
                            // peer, look it up again now we've changed the 
                            // public key
                            $this->send($wwwroot, false);
                        }
                        else {
                            throw new XmlrpcClientException('Could not parse new public key');
                        }
                    }
                    else {
                        throw new XmlrpcClientException('Remote site claims to have sent a public key, but they LIE');
                    }
                }
                throw new XmlrpcClientException('Unknown error occured: ' . $this->response['faultCode'] . ': ' . $this->response['faultString']);
            }

            // Clean up so object can be re-used.
            $this->requesttext      = '';
            $this->signedrequest    = '';
            $this->encryptedrequest = '';
            $this->params           = array();
            $this->method           = '';
            return true;
        } else {
            throw new XmlrpcClientException('Unrecognized XML document form: ' . $payload);
        }
    }

    /**
     * Add a parameter to the array of parameters.
     *
     * @param  string  $argument    A transport ID, as defined in lib.php
     * @param  string  $type        The argument type, can be one of:
     *                              none
     *                              empty
     *                              base64
     *                              boolean
     *                              datetime
     *                              double
     *                              int
     *                              string
     *                              array
     *                              struct
     *                              In its weakly-typed wisdom, PHP will (currently)
     *                              ignore everything except datetime and base64
     * @return bool                 True on success
     */
    function add_param($argument, $type = 'string') {

        $allowed_types = array('none',
                               'empty',
                               'base64',
                               'boolean',
                               'datetime',
                               'double',
                               'int',
                               'i4',
                               'string',
                               'array',
                               'struct');
        if (!in_array($type, $allowed_types)) {
            return false;
        }

        if ($type != 'datetime' && $type != 'base64') {
            $this->params[] = $argument;
            return $this;
        }

        // Note weirdness - The type of $argument gets changed to an object with
        // value and type properties.
        // bool xmlrpc_set_type ( string &value, string type )
        xmlrpc_set_type($argument, $type);
        $this->params[] = $argument;
        return $this;
    }

    private static function get_max_server_time_difference() {
        $max_time_difference = (int)get_config('xmlrpcmaxservertimedifference');
        if ($max_time_difference < 15) {
            if ($max_time_difference != 0) {
                // Someone deliberately set it to less than 15 seconds
                log_warn('Minimum value allowed for "max_server_time_difference" is 15 seconds');
            }
            $max_time_difference = 15;
        }
        if ($max_time_difference > 300) {
            log_warn('Maximum value allowed for "max_server_time_difference" is 300 seconds');
            $max_time_difference = 300;
        }
        return $max_time_difference;
    }
}
