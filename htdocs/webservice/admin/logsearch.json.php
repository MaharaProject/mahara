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

define('INTERNAL', 1);
define('JSON', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$action = param_variable('action');

if ($action == 'search') {
    require_once('webservicessearchlib.php');
    $params = new StdClass;
    $params->userquery       = trim(param_variable('userquery', ''));
    $params->functionquery   = trim(param_variable('functionquery', ''));
    $params->institution     = param_alphanum('institution', 'all');
    $params->protocol        = param_alphanum('protocol', 'all');
    $params->authtype        = param_alphanum('authtype', 'all');
    $params->institution_requested = param_alphanum('institution_requested', null);

    $offset  = param_integer('offset', 0);
    $limit   = param_integer('limit', 10);
    $sortby  = param_alpha('sortby', 'timelogged');
    $sortdir = param_alpha('sortdir', 'desc');
    $params->sortby  = $sortby;
    $params->sortdir = $sortdir;
    $params->offset  = $offset;
    $params->limit   = $limit;

    json_headers();
    if (param_boolean('raw', false)) {
        $data = get_log_search_results($params, $offset, $limit, $sortby, $sortdir);
    }
    else {
        $data['data'] = build_webservice_log_search_results($params, $offset, $limit, $sortby, $sortdir);
    }
    $data['error'] = false;
    $data['message'] = null;
    echo json_encode($data);
    exit;
}
