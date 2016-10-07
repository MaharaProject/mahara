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

/**
 * REST web service entry point. The authentication is done via tokens.
 *
 * @package webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Piers Harding
 */

require_once(get_config('docroot').'webservice/rest/locallib.php');
/**
 * Mahara REST client class
 * TODO: XML to PHP
 */
class webservice_rest_client {

    public $serverurl;
    private $auth;
    private $type;
    private $json;
    private $consumer;
    private $token;
    private $headers = array();
    public $connection;
    private $twolegged = false;

    /**
     * Constructor
     * @param string $serverurl a Mahara URL
     * @param array $auth
     */
    public function __construct($serverurl, $auth, $type, $json=false) {
        $this->serverurl = $serverurl;
        $this->set_authentication($auth);
        $this->type = $type;
        $this->json = $json;
    }

    public function set_connection($c) {
        $this->connection = $c;
    }

    /**
     * Set the auth values used to do the REST call
     * @param array $auth
     */
    public function set_authentication($auth) {
        $values = array();
        foreach ($auth as $k => $v) {
            if ($k == 'header') {
                $this->headers[]= $v;
            }
            else {
                $values[]= "$k=" . urlencode($v);
            }
        }
        $this->auth = implode('&', $values);
    }

    /**
     * Set the OAuth consumer details
     * @param array $consumer
     */
    public function set_oauth($consumer, $token) {
        $this->consumer = $consumer;
        $this->token = $token;
    }

    /**
     * Set the OAuth 2Legged consumer details
     * @param array $consumer
     */
    public function set_2legged($consumer, $secret) {
        $this->consumer = $consumer;
        $this->secret = $secret;
        $this->twolegged = true;
    }

    /**
     * Execute client WS request with token authentication
     * @param string $functionname
     * @param array $params
     * @param bool $json
     * @return mixed
     */
    public function call($functionname, $params=array(), $method="POST", $json=null) {
        global $WEBSERVICES_IGNORE_BODY;

        if ($this->type == 'oauth') {
            $url = $this->serverurl . '?wsfunction=' . $functionname;
            if ($functionname) {
                $this->auth = $this->auth .  (empty($this->auth) ? '' : '&') . 'wsfunction=' . $functionname;
            }
            $url = $this->serverurl . '?' . $this->auth . (empty($this->auth) ? '' : '&') . 'alt=json';
            $this->serverurl = $url;
            $body = '';
            $options = array();
            if ($json || $this->json) {
                $url .= '&alt=json';
                $body = json_encode($params);
            }
            else {
                $body = format_postdata_for_curlcall($params);
            }
            // setup the client side OAuth
            if ($this->twolegged) {
                $oauth_options = array(
                    'consumer_key' => $this->consumer,
                    'consumer_secret' => $this->secret,
                );
                $store = OAuthStore::instance("2Leg", $oauth_options);
            }
            else {
                $oauth_options = array(
                    'consumer_key' => $this->consumer->consumer_key,
                    'consumer_secret' => $this->consumer->consumer_secret,
                    'server_uri' => 'http://example.com/webservice/rest/server.php',
                    'request_token_uri' => 'http://example.com/maharadev/webservice/oauthv1.php/request_token',
                    'authorize_uri' => 'http://example.com/webservice/oauthv1.php/authorize',
                    'access_token_uri' => 'http://example.com/webservice/oauthv1.php/access_token',
                );
                $store = OAuthStore::instance("Session", $oauth_options, true);
                $store->addServerToken($this->consumer->consumer_key, 'access', $this->token['token'], $this->token['token_secret'], 1);
            }
            $WEBSERVICES_IGNORE_BODY = true;
            require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthRequester.php');
            $request = new OAuthRequester($url, $method, '', $body);
            $WEBSERVICES_IGNORE_BODY = false;
            $result = $request->doRequest(0);
            if ($result['code'] != 200) {
                throw new Exception('REST OAuth error: ' . var_export($result, true));
            }
            $result = $result['body'];
            if ($json || $this->json) {
                $values = (array)json_decode($result, true);
                return $values;
            }
        }
        else {
            // do a JSON based call - just soooo easy compared to XML/SOAP
            if ($json || $this->json) {
                $data = json_encode($params);
                if ($functionname) {
                    $this->auth = $this->auth .  (empty($this->auth) ? '' : '&') . 'wsfunction=' . $functionname;
                }
                $url = $this->serverurl . '?' . $this->auth . (empty($this->auth) ? '' : '&') . 'alt=json';
                $this->serverurl = $url;
                $hostname = parse_url($url, PHP_URL_HOST);
                $headers = (empty($this->headers) ? "" : implode("\r\n", $this->headers)."\r\n");
                $context = array('http' => array ('method' => $method,
                                                  'header' => "Content-Type: application/json\r\n".
                                                  $headers.
                                                  "Connection: close\r\nContent-Length: " . strlen($data) . "\r\n",
                                                  'content'=>$data,
                                                  'request_fulluri' => true,),
                        );
                if (get_config('disablesslchecks')) {
                    $context['ssl'] = array('verify_host' => false,
                                       'verify_peer' => false,
                                       'verify_peer_name' => false,
                                       'SNI_server_name' => $hostname,
                                       'SNI_enabled'     => true,);
                }
                // echo "<pre>";
                // var_dump($context);
                $context = stream_context_create($context);
                $result = @file_get_contents($url, false, $context);
                // var_dump($result);
                // var_dump($http_response_header);
                $values = (array)json_decode($result, true);
                return $values;
            }

            // default to parsing HTTP parameters
            $this->serverurl = $this->serverurl . '?' . $this->auth . ($functionname ? '&wsfunction=' . $functionname : '');
            $result = webservice_download_file_content($this->serverurl, $this->headers, $params,
                                                         false, 300, 20, get_config('disablesslchecks'), null, false, true);
            log_debug("REST client response: ".var_export($result, true));
        }

        //after the call, for those not using JSON, parseout the results
        // from REST XML response to PHP
        $xml2array = new webservice_xml2array($result);
        $raw = $xml2array->getResult();

        if (isset($raw['EXCEPTION'])) {
            $debug = isset($raw['EXCEPTION']['DEBUGINFO']) ? $raw['EXCEPTION']['DEBUGINFO']['#text'] : '';
            throw new Exception('REST error: ' . $raw['EXCEPTION']['MESSAGE']['#text'] .
                                ' (' . $raw['EXCEPTION']['@class'] . ') ' . $debug);
        }

        $result = array();
        if (isset($raw['RESPONSE'])) {
            $node = $raw['RESPONSE'];
            if (isset($node['MULTIPLE'])) {
                $result = self::recurse_structure($node['MULTIPLE']);
            }
            else if (isset($raw['RESPONSE']['SINGLE'])) {
                $result = self::recurse_structure($raw['RESPONSE']);
            }
            else {
                // empty result ?
                $result = $raw['RESPONSE'];
            }
        }
        return $result;
    }

    /**
     * function for walking down the peculiar nested structure of
     * the REST response XML
     *
     * @param array $node
     */
    private static function recurse_structure($node) {
        $result = array();
        if (isset($node['SINGLE']['KEY'])) {
            foreach ($node['SINGLE']['KEY'] as $element) {
                if (isset($element['MULTIPLE'])) {
                    $item[$element['@name']] = self::recurse_structure($element['MULTIPLE']);
                }
                else {
                    $item[$element['@name']] = (isset($element['VALUE']['#text']) ? $element['VALUE']['#text'] : '');
                }
            }
            $result[]= $item;
        }
        else {
            if (isset($node['SINGLE'])) {
                foreach ($node['SINGLE'] as $single) {
                    $item = array();
                    $single = array_shift($single);
                    foreach ($single as $element) {
                        if (isset($element['MULTIPLE'])) {
                            $item[$element['@name']] = self::recurse_structure($element['MULTIPLE']);
                        }
                        else {
                            $item[$element['@name']] = (isset($element['VALUE']['#text']) ? $element['VALUE']['#text'] : '');
                        }
                    }
                    $result[]= $item;
                }
            }
        }
        return $result;
    }

}

/**
 * class for converting the REST XML response document to PHP array
 *
 * @author A K Chauhan <- thanks for the example!
 *
 */
class webservice_xml2array {

    /**
     * Constructor for XML parser
     */
    public function __construct($xml) {
        if (is_string($xml)) {
            $this->dom = new DOMDocument;
            $this->dom->loadXml($xml);
        }
        else {
            $this->dom = false;
        }
    }

    /**
     * Starting point for recursive routine that accumulates PHP values out of
     * the XML document
     *
     * @param object $node of XML
     * @return array
     */
    function _process($node) {
        $occurance = array();
        $result = array();
        if (empty($node)) {
            return $result;
        }

        if (!empty($node->childNodes)) {
            foreach ($node->childNodes as $child) {
                if (empty($occurance[$child->nodeName])) {
                    $occurance[$child->nodeName] = 0;
                }
                $occurance[$child->nodeName]++;
            }
        }

        if ($node->nodeType == XML_TEXT_NODE) {
            $result = html_entity_decode(htmlspecialchars($node->nodeValue, ENT_COMPAT, 'UTF-8'),
                                         ENT_COMPAT, 'ISO-8859-15');
        }
        else {
            if ($node->hasChildNodes()) {
                $children = $node->childNodes;

                for ($i=0; $i<$children->length; $i++) {
                    $child = $children->item($i);

                    if ($child->nodeName != '#text') {
                        if ($occurance[$child->nodeName] > 1) {
                            $result[$child->nodeName][] = $this->_process($child);
                        }
                        else {
                            $result[$child->nodeName] = $this->_process($child);
                        }
                    }
                    else if ($child->nodeName == '#text') {
                        $text = $this->_process($child);

                        if (trim($text) != '') {
                            $result[$child->nodeName] = $this->_process($child);
                        }
                    }
                }
            }

            if ($node->hasAttributes()) {
                $attributes = $node->attributes;

                if (!is_null($attributes)) {
                    foreach ($attributes as $key => $attr) {
                        $result["@" . $attr->name] = $attr->value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * sort of wrapper for the mainline
     *
     * @return array PHP values parsed out of XML
     */
    function getResult() {
        return $this->_process($this->dom);
    }
}
