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
    $params->query       = trim(param_variable('query', ''));
    $params->institution = param_alphanum('institution', null);
    $params->f           = param_alpha('f', null);
    $params->l           = param_alpha('l', null);
    $params->institution_requested = param_alphanum('institution_requested', null);

    $offset  = param_integer('offset', 0);
    $limit   = param_integer('limit', 10);
    $sortby  = param_alpha('sortby', 'firstname');
    $sortdir = param_alpha('sortdir', 'asc');
    $token   = param_variable('token', '');
    $suid    = param_variable('suid', '');
    $ouid    = param_variable('ouid', '');
    $params->sortby  = $sortby;
    $params->sortdir = $sortdir;
    $params->offset  = $offset;
    $params->limit   = $limit;

    json_headers();
    if (param_boolean('raw', false)) {
        $data = get_admin_user_search_results($params, $offset, $limit, $sortby, $sortdir);
    }
    else {
        $data['data'] = build_webservice_user_search_results($params, $offset, $limit, $sortby, $sortdir);
    }
    $data['error'] = false;
    $data['message'] = null;
    echo json_encode($data);
    exit;
}
