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

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('searchlib.php');

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter','Missing parameter \'query\'');
}

$type = param_variable('type', 'all');
$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);

$data = search_selfsearch($query, $limit, $offset, $type);

json_headers();
$data['error'] = false;
$data['message'] = false;
echo json_encode($data);
