<?php

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('file.php');
require_once(get_config('docroot') . 'webservice/rest/lib.php');

// Allow CORS requests.
//header('Access-Control-Allow-Origin: *');

// Authenticate the user.
$token = param_variable('wstoken');
$timestamp = param_integer('c');
$viewid = param_integer('v');
$type = param_alphanum('t');

$authmethod = WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN;

// run the dispatcher
$requestmethod = new ReflectionMethod("WebserviceRestServer", "parse_request");
$requestmethod->setAccessible(true);
$authenticatemethod = new ReflectionMethod("WebserviceRestServer", "authenticate_user");
$authenticatemethod->setAccessible(true);
$server = new WebserviceRestServer($authmethod);
$requestmethod->invoke($server);
$authenticatemethod->invoke($server);

// If we get here we are authenticated

require_once('view.php');
$view = new View($viewid);
if (!$view->is_submitted()) {
    throw new NotFoundException(get_string('viewnotsubmitted', 'view'));
}
$uid = $view->get('owner');
$portfoliotype = $view->collection_id() ? 'collection' : 'view';
$portfolioid = $view->collection_id() ? $view->collection_id() : $viewid;
$path = get_config('dataroot') . $type . '/' . $portfoliotype . '/' . $portfolioid . '/mahara-export-' . $type . '-user' . $uid . '-' . $timestamp . '.zip';
$name = $type . '-user' . $uid . '-' . $timestamp . '.zip';
$mimetype = file_mime_type($path);

if (!is_readable($path)) {
    throw new WebserviceFileNotFoundException(get_string('nofilesfound', 'artefact.file'));
}
$options = array('forcedownload' => true);
serve_file($path, $name, $mimetype, $options);
