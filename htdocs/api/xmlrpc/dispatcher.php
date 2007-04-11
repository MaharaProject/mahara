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

    function __construct($payload) {
        global $CFG;
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

        // Security: I'm thinking that we should not return separate errors for
        //           the file not existing, the file not being readable, etc. as
        //           it might provide an opportunity for outsiders to scan the
        //           server for random files. So just a single message/code for
        //           all failures here.
        if(strpos($this->method, '/') !== false) {
            $this->callstack  = explode('/', $this->method);
        } else {
            throw new XmlrpcServerException('The function does not exist', 6011);
        }

        $functionname = array_pop($this->callstack);
        $filename     = $CFG->docroot . implode('/', $this->callstack);

        if(!file_exists($filename)) {
           throw new XmlrpcServerException('The function does not exist', 6011);
        }

        if(!is_readable($filename)) {
            throw new XmlrpcServerException('The function does not exist', 6011);
        }

        // Make sure that the fully resolved path really is under docroot
        $realpath = realpath($filename);
        if(0 == preg_match("@^{$CFG->docroot}@", $realpath)) {
            throw new XmlrpcServerException('The function does not exist '.$realpath.' '.$CFG->docroot, 6011);
        }

        // Make sure that the file we are including is called api.php
        if(0 == preg_match("@api.php$@", $realpath)) {
            throw new XmlrpcServerException('The function does not exist '.$realpath.' '.$CFG->docroot, 6011);
        }

        $temp = '';
        $xmlrpcserver = xmlrpc_server_create();

        include_once($filename);

        $this->response = xmlrpc_server_call_method($xmlrpcserver, $payload, $temp, array("encoding" => "utf-8"));
        return $this->response;
    }

    function __get($name) {
        if ($name == 'response') return $this->response;
        return null;
    }
}

?>