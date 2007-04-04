<?php

xmlrpc_server_register_method($xmlrpcserver, 'auth/xmlrpc/api.php/getTime', 'getTime');

function getTime($method, $params, $hostinfo) {
    // echo $method.', '. $params .', '. $hostinfo;
    return date($params[0]);
}
?>
