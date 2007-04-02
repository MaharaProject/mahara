<?php

class Request {

    private $error     = array();
    private $request   = array();
    private $callstack = array();
    private $payload   = '';
    private $method    = '';

    function __construct($payload) {
        global $CFG;
        $this->payload = $payload;
        $this->method  = '';

        // xmlrpc_decode_request is defined such that the '$method' string is
        // passed in by reference.
        $this->request = xmlrpc_decode_request($this->payload, $this->method);

        $this->Dispatcher = new Dispatcher($this->request);
/*
        if (0 == preg_match("@^[A-Za-z0-9]+/[A-Za-z0-9/_-]+(\.php/)?[A-Za-z0-9_-]+$@",$this->method)) {
            $this->error = array(0 => 'The function does not exist');
            throw new XmlrpcServerException('The function does not exist', 6010);
            return false;
        }

        if(strpos($this->method, '/') !== false) {
            $this->callstack  = explode('/', $this->method);
        } elseif(strpos($this->method, '.') !== false) {
            $this->callstack  = explode('.', $this->method);
        } else {
            throw new XmlrpcServerException('The function does not exist', 6011);
            return false;
        }

        $functionname = array_pop($this->callstack);
        $filename     = implode('/', $this->callstack);

        //if(!file_exists($filename.'.php')
*/
        return true;
    }

    function __get($name) {
        if(isset($this->{$name})) return $this->{$name};
        return null;
    }
}

class Dispatcher {
    function __construct($payload) {
        $zed = 'anything';
        $xmlrpcserver = xmlrpc_server_create();
        xmlrpc_server_register_method($xmlrpcserver, 'examples.getTime', 'time');
        xmlrpc_server_register_method($xmlrpcserver, 'examples.getTime', 'netdate');
        $response = xmlrpc_server_call_method($xmlrpcserver, $payload, $zed, array("encoding" => "utf-8"));
        //$response = mnet_server_prepare_response($response);
        var_dump($response);
    }
}

function netdate($method, $params, $hostinfo) {
    echo $method.', '. $params .', '. $hostinfo;
    return date($params[0]);
}
?>
