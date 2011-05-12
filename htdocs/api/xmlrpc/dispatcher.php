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

class Dispatcher {

    private $params    = array();
    private $callstack = array();
    private $payload   = '';
    private $method    = '';
    private $response  = '';

    private $system_methods  = array('system.listMethods'       => 'list_methods',
                                     'system/listMethods'       => 'list_methods',
                                     'system.methodSignature'   => 'method_signature', 
                                     'system/methodSignature'   => 'method_signature', 
                                     'system.methodHelp'        => 'method_help', 
                                     'system/methodHelp'        => 'method_help', 
                                     'system.listServices'      => 'list_services', 
                                     'system/listServices'      => 'list_services', 
                                     'system.keyswap'           => 'keyswap', 
                                     'system/keyswap'           => 'keyswap');

    private $services = array(
        'sso_in' => array(),
        'sso_out' =>array(
            'auth/mnet/auth.php/user_authorise' => 'user_authorise',
            'auth/mnet/auth.php/fetch_user_image' => 'fetch_user_image',
            'auth/mnet/auth.php/update_enrolments' => 'xmlrpc_not_implemented',
            'auth/mnet/auth.php/keepalive_server' => 'xmlrpc_not_implemented',
            'auth/mnet/auth.php/kill_children' => 'kill_children',
            'auth/mnet/auth.php/kill_child' => 'xmlrpc_not_implemented',
            // Lines added for the mahara assignment type plugin for Moodle; the first three
            // are for an old version that require a patched Moodle and will eventually be removed.
            // All of these should be pulled in from the artefact plugin.
            'mod/assignment/type/mahara/rpclib.php/get_views_for_user' => 'get_views_for_user',
            'mod/assignment/type/mahara/rpclib.php/submit_view_for_assessment' => 'submit_view_for_assessment',
            'mod/assignment/type/mahara/rpclib.php/release_submitted_view' => 'release_submitted_view',
            'mod/mahara/rpclib.php/get_views_for_user' => 'get_views_for_user',
            'mod/mahara/rpclib.php/submit_view_for_assessment' => 'submit_view_for_assessment',
            'mod/mahara/rpclib.php/release_submitted_view' => 'release_submitted_view',
            ),
        'portfolio_in' => array(
            'portfolio/mahara/lib.php/send_content_intent' => 'send_content_intent',
            'portfolio/mahara/lib.php/send_content_ready' => 'send_content_ready',
            ),
        'repository_out' => array(
            'repository/mahara/repository.class.php/get_folder_files' => 'get_folder_files',
            'repository/mahara/repository.class.php/get_file' => 'get_file',
            'repository/mahara/repository.class.php/search_folders_and_files' => 'search_folders_and_files'
            )
    );

    private $methodhelp = array(
        'user_authorise'   => 'Given an authentication token and a useragent hash, look for a record we\'ve created that associates those values with a single user. If we find it, return that user\'s details to the remote host',
        'fetch_user_image' => 'Given a username, return the default profile picture for that user.'
    );

    private $methodsig = array(
        'user_authorise'   => array(
                                array(
                                    array('type' => 'array', 
                                          'description' => '$userdata Array of user info for remote host'
                                          ), 
                                    array('type' => 'string',
                                          'description' => 'token - The unique ID provided by remotehost.'
                                          ),
                                    array('type' => 'string',
                                          'description' => 'useragent - User Agent string.'
                                          )
                                     )
                               ),

        'fetch_user_image' => array(
                                array(
                                    array('type' => 'string', 
                                          'description' => 'The encoded image.'
                                          ), 
                                    array('type' => 'string',
                                          'description' => 'username - The id of the user.'
                                          )
                                      )
                               ),
        'send_content_intent' => array(
                                array(
                                    array('type' => 'string',
                                          'description' => 'The username of the user on the remote system (previously sent in jump/land request)'
                                        ),
                                    )
                                ),
        'get_folder_files' => array(
                                array(
                                    array('type' => 'array',
                                          'description' => 'The Moodle File picker path + list of files for a specific Mahara folder'
                                        )
                                    )
                                ),
        'search_folders_and_files' => array(
                                array(
                                    array('type' => 'array',
                                          'description' => 'list of files/folders matching the search'
                                        )
                                    )
                                ),
        'get_file' => array(
                                array(
                                    array('type' => 'array',
                                          'description' => 'The file content encoded in base 64 + file name'
                                        )
                                    )
                                )
    );

    function __construct($payload, $payload_signed, $payload_encrypted) {

        $this->payload = $payload;

        // xmlrpc_decode_request is defined such that the '$method' string is
        // passed in by reference.
        $this->params  = xmlrpc_decode_request($this->payload, $this->method, 'UTF-8');

        // The method name is not allowed to have a dot, except for a single dot
        // which preceeds the php extension. It can have slashes but it cannot
        // begin with a slash. We specifically don't want .. to be possible.
        if (0 == preg_match("@^[A-Za-z0-9]+/[A-Za-z0-9/_-]+(\.php/)?[A-Za-z0-9_-]+$@",$this->method)) {
            throw new XmlrpcServerException('The function does not exist', 6010);
        }

        if (($payload_signed && $payload_encrypted) || $this->method == 'system/keyswap') {
            // The remote server's credentials checked out.
            // You might want to enable some methods for unsigned/unencrypted
            // transport
        } else {
            // For now, we throw an exception
            throw new XmlrpcServerException('The signature on your message was not valid', 6005);
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
            if (strpos($this->method, '/') !== false) {
                $this->callstack  = explode('/', $this->method);
            } else {
                throw new XmlrpcServerException('The function does not exist', 6011);
            }

            // Read custom xmlrpc functions from local
            if (function_exists('local_xmlrpc_services')) {
                foreach (local_xmlrpc_services() as $name => $localservices) {
                    $this->services[$name] = array_merge($this->services[$name], $localservices);
                }
            }

            foreach ($this->services as $container) {
                if (array_key_exists($this->method, $container)) {
                    $xmlrpcserver = xmlrpc_server_create();
                    $bool = xmlrpc_server_register_method($xmlrpcserver, $this->method, 'api_dummy_method');
                    $this->response = xmlrpc_server_call_method($xmlrpcserver, $payload, $container[$this->method], array("encoding" => "utf-8"));
                    $bool = xmlrpc_server_destroy($xmlrpcserver);
                    return $this->response;
                }
            }

            throw new XmlrpcServerException('No such method: ' . $this->method);

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

    function list_methods($xmlrpc_method_name, $args = null) {
        $list = array();
        if (empty($args)) {
            foreach ($this->services as $service => $methods) {
                $list = array_merge($list, $methods);
            }
            return $list;
        } else {
            return $this->services[$args[0]];
        }
    }

    function list_services() {
        $list = array();
        foreach ($this->services as $service => $methods) {
            $list[] = array('name' => $service, 'apiversion' => 1, 'publish' => 1, 'subscribe' => 1);
        }
        return $list;
    }

    function method_signature($xmlrpc_method_name, $methodname) {
        error_log(var_export(array('A',$xmlrpc_method_name, $methodname[0]),1));
        return $this->methodsig[$methodname[0]];
    }

    function method_help($xmlrpc_method_name, $methodname) {
        error_log(var_export(array('B',$xmlrpc_method_name, $methodname[0]),1));
        return $this->methodhelp[$methodname[0]];
    }
}
