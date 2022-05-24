<?php

/**
 *
 * @package    mahara
 * @subpackage module_beacon
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
defined('INTERNAL') || die();
/**
 * Wrapper class for curl for module_Beacon
 * Does not deal with proxies.
 */
class beacon_curl {
    public $error;
    public $info;
    public $headers = array();
    private $responseheaders = array();
    public function __construct() {
	$wwwroot = get_config('wwwroot');
    $wwwroot =  rtrim($wwwroot, '/');
	$this->headers = [
            'Accept: application/json',
            "Origin: $wwwroot",
        ];
    }
    public function setHeader($header) {

        $this->headers[] = $header;
    }

    /**
     *  Does a curl post with appropriate haders for a blob of JSON.
     *
     * @param string $url The Url to POST to.
     * @param string $results JSON array to be posted.
     * @return string|bool
     */
    public function post($url, $results) {
        //Flush response headers for new request.
        $this->responseheaders = array();
        $ch = curl_init($url);
        $postheaders = array_merge($this->headers , ['Content-Type: application/json', 'Content-Length: ' . strlen($results)]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $postheaders);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $results);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array (&$this,'parseHeaders'));
        $return = curl_exec($ch);
        $this->info = curl_getinfo($ch);
        $this->error = curl_error($ch);
        curl_close($ch);
        return $return;
    }
    /**
     * Retrieves a resource at URL using CURL with a GET command.
     *
     * @param string $url  URL to pull information from.
     * @return string|bool
     */
    public function get($url) {
        //Flush response headers for new request.
        $this->responseheaders = array();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array (&$this,'parseHeaders'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $return = curl_exec($ch);
        $this->info = curl_getinfo($ch);
        $this->error = curl_error($ch);
        curl_close($ch);
        return $return;
    }
    public function parseHeaders($curlhandle, $header) {
        $length =  strlen($header);
        $keyvalue = explode(':' , $header , 2);
        if ( count($keyvalue) < 2 ) {
            //I am an invalid header with no ':'
            return $length;
        }
        //Put each value with the same key in the same place.
        //Header titles are case insensitive.
        $this->responseheaders[strtolower(trim($keyvalue[0]))][] = trim($keyvalue[1]);

        return $length;
    }
    /**
     *  Returns response http headers from most recent request.
     *  @return Headers Headers from the response to a request.
     */
    public function getResponse(){
        return $this->responseheaders;
    }
}
