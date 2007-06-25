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
 * @subpackage xmlrpc
 * @author     Donal McMullan <donal@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

class Dispatcher {

    private $params    = array();
    private $callstack = array();
    private $payload   = '';
    private $method    = '';
    private $response  = '';

    private $system_methods  = array('system.listMethods'       => 'listMethods', 
                                     'system/listMethods'       => 'listMethods', 
                                     'system.methodSignature'   => 'methodSignature', 
                                     'system/methodSignature'   => 'methodSignature', 
                                     'system.methodHelp'        => 'methodHelp', 
                                     'system/methodHelp'        => 'methodHelp', 
                                     'system.listServices'      => 'listServices', 
                                     'system/listServices'      => 'listServices', 
                                     'system.keyswap'           => 'keyswap', 
                                     'system/keyswap'           => 'keyswap');

    private $user_methods = array(
        'sso_in' => array(),
        'sso_out' =>array(
            'auth/mnet/auth.php/user_authorise' => 'user_authorise',
            'auth/mnet/auth.php/fetch_user_image' => 'fetch_user_image'
            )
    );

    function __construct($payload) {
        $this->payload = $payload;

        // xmlrpc_decode_request is defined such that the '$method' string is
        // passed in by reference.
        $this->params  = xmlrpc_decode_request($this->payload, $this->method, 'UTF-8');
        $f = fopen('/tmp/web/FUR'.$this->method, 'w');
        fwrite($f, "FUR\n");
        // The method name is not allowed to have a dot, except for a single dot
        // which preceeds the php extension. It can have slashes but it cannot
        // begin with a slash. We specifically don't want .. to be possible.
        if (0 == preg_match("@^[A-Za-z0-9]+/[A-Za-z0-9/_-]+(\.php/)?[A-Za-z0-9_-]+$@",$this->method)) {
            throw new XmlrpcServerException('The function does not exist', 6010);
        }

        // The system methods are treated differently.
        if (array_key_exists($this->method, $this->system_methods)) {

            $xmlrpcserver = xmlrpc_server_create();
            xmlrpc_server_register_method($xmlrpcserver, $this->method, array(&$this, $this->system_methods[$this->method]));

        } else {

            // Security: I'm thinking that we should not return separate errors for
            //           the file not existing, the file not being readable, etc. as
            //           it might provide an opportunity for outsiders to scan the
            //           server for random files. So just a single message/code for
            //           all failures here kthxbye.
            if(strpos($this->method, '/') !== false) {
                $this->callstack  = explode('/', $this->method);
            } else {
                throw new XmlrpcServerException('The function does not exist', 6011);
            }

            foreach ($this->user_methods as $container) {
                if (array_key_exists($this->method, $container)) {
                    $xmlrpcserver = xmlrpc_server_create();
                    $bool = xmlrpc_server_register_method($xmlrpcserver, $this->method, 'api_dummy_method');
                    $this->response = xmlrpc_server_call_method($xmlrpcserver, $payload, $container[$this->method], array("encoding" => "utf-8"));
                    $bool = xmlrpc_server_destroy($xmlrpcserver);
                    return $this->response;
                }
            }

            throw new XmlrpcServerException('No such method');

        }

        $temp = '';
        $this->response = xmlrpc_server_call_method($xmlrpcserver, $payload, $temp, array("encoding" => "utf-8"));
        return $this->response;
    }

    function __get($name) {
        if ($name == 'response') return $this->response;
        return null;
    }

    function keyswap($function, $params) {
        require_once(get_config('libroot') . 'peer.php');

        //TODO: Verify params
        (empty($params[0])) ? $wwwroot = null     : $wwwroot     = $params[0];
        (empty($params[1])) ? $pubkey = null      : $pubkey      = $params[1];
        (empty($params[2])) ? $application = null : $application = $params[2];

        if (get_config('promiscuousmode')) {
            try {
                $peer = new Peer();
                if ($peer->bootstrap($wwwroot, $pubkey, $application)) {
                    $peer->commit();
                }
            } catch (Exception $e) {
                throw new SystemException($e->getMessage(), $e->getCode());
            }
        }
        $openssl = OpenSslRepo::singleton();
        return $openssl->certificate;
    }
}

?>
