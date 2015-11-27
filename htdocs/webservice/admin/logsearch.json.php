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
    $params->offset          = param_integer('offset', 0);
    $params->limit           = param_integer('limit', 10);
    $params->setlimit        = param_integer('setlimit', false);
    $params->onlyerrors      = param_integer('onlyerrors', 0);
    $params->sortby          = param_alpha('sortby', 'timelogged');
    $params->sortdir         = param_alpha('sortdir', 'desc');

    if (param_boolean('raw', false)) {
        json_headers();
        $data['error'] = false;
        $data['message'] = null;
        $data = get_log_search_results($params);
        echo json_encode($data);
        exit;
    }
    else {
        list($html, $columns, $searchurl, $pagination) = build_webservice_log_search_results($params);
    }

    json_reply(false, array(
        'message' => null,
        'data' => array(
            'tablerows' => $html,
            'pagination' => $pagination['html'],
            'pagination_js' => $pagination['javascript'],
            'offset' => $params->offset,
            'setlimit' => $params->setlimit,
        )
    ));
}
