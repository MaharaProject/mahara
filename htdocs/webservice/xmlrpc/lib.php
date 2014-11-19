<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
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
