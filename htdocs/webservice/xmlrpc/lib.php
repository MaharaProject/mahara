<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Moodle Pty Ltd (http://moodle.com)
 * Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Piers Harding
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once 'Zend/XmlRpc/Client.php';

/**
 * XML-RPC client class
 */
class webservice_xmlrpc_client extends Zend_XmlRpc_Client {

    private $serverurl;

    /**
     * Constructor
     * @param string $serverurl
     * @param array $auth
     */
    public function __construct($serverurl, $auth) {
        $this->serverurl = $serverurl;
        $this->set_auth($auth);
        parent::__construct($this->_serverAddress);
    }

    /**
     * Set the token used to do the XML-RPC call
     * @param array $auth
     */
    public function set_auth($auth) {
        $values = array();
        foreach ($auth as $k => $v) {
            $values[]= "$k=" . urlencode($v);
        }
        $this->auth = implode('&', $values);
        $this->_serverAddress = $this->serverurl . '?' . $this->auth;
    }

    /**
     * Execute client WS request
     * @param string $functionname
     * @param array $params
     * @return mixed
     */
    public function call($functionname, $params) {
        //zend expects 0 based array with numeric indexes
        $params = array_values($params);

        //traditional Zend soap client call (integrating the token into the URL)
        $result = parent::call($functionname, $params);

        return $result;
    }
}
