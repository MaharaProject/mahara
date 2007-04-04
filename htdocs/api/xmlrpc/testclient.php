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
error_reporting(E_ALL);
ini_set('display_errors', true);
define('PUBLIC', 1);
error_reporting(E_ALL);
ini_set('display_errors', true);
require(dirname(__FILE__).'/lib.php');
error_reporting(E_ALL);
ini_set('display_errors', true);
require(dirname(dirname(dirname(__FILE__))).'/init.php');

error_reporting(E_ALL);
ini_set('display_errors', true);
header('Content-type: text/plain; charset=utf-8');

$client = new Client();
$client->addParam('d/m/Y H:i:s', 'string');
$client->setMethod('auth/xmlrpc/api.php/getTime');
$client->send('http://mahara.mahoodle.com');

class Client {

    private $requesttext      = '';
    private $signedrequest    = '';
    private $encryptedrequest = '';
    private $params           = array();

    function __construct() {
        return true;
    }

    function setMethod($method) {
        if(is_string($method) && preg_match("@^[A-Za-z0-9]+/[A-Za-z0-9/_-]+(\.php/)?[A-Za-z0-9_-]+$@", $method)) {
            $this->method = $method;
        }
        return $this;
    }

    function send($wwwroot) {
        $this->peer   = new Peer();

        // var_dump($this->peer);

        $this->peer->init($wwwroot);

        $ch = curl_init( $this->peer->wwwroot . $this->peer->xmlrpc_server_url );
        $this->requesttext = xmlrpc_encode_request($this->method, $this->params, array("encoding" => "utf-8"));
        $rq = $this->requesttext;
        $rq = xmldsig_envelope($rq);
        $this->signedrequest = $rq;
        $rq = xmlenc_envelope($rq, $this->peer->certificate);
        $this->encryptedrequest = $rq;

        // echo $rq;

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rq);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));

        $timestamp_send    = time();
        $this->rawresponse = curl_exec($ch);
        $timestamp_receive = time();

        echo "RAWRESPONSE:\n".$this->rawresponse."\nEOF";

        
        $this->rawresponse;

        if ($this->rawresponse == false) {
            $this->error[] = curl_errno($ch) .':'. curl_error($ch);
            return false;
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
    function addParam($argument, $type = 'string') {

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
            return true;
        }

        // Note weirdness - The type of $argument gets changed to an object with
        // value and type properties.
        // bool xmlrpc_set_type ( string &value, string type )
        xmlrpc_set_type($argument, $type);
        $this->params[] = $argument;
        return true;
    }
}

?>