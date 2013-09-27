<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('searchlib.php');

$params = new StdClass;
$params->query       = trim(param_variable('query', ''));
$params->institution = param_alphanum('institution', null);
$params->lastinstitution = param_alphanum('lastinstitution', null);
$params->requested   = param_integer('requested', null);
$params->invitedby   = param_integer('invitedby', null);
$params->member      = param_integer('member', null);
$limit               = param_integer('limit', 100);

json_headers();
$data = get_institutional_admin_search_results($params, $limit);
$data['error'] = false;
$data['message'] = null;
echo json_encode($data);
exit;
