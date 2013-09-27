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

safe_require('search', 'internal');

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter','Missing parameter \'query\'');
}

$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);
$allfields = param_boolean('allfields');
$group = param_integer('group', 0);
$includeadmins = param_boolean('includeadmins', true);
$orderby = param_variable('orderby', 'firstname');

$options = array(
    'orderby' => $orderby,
);

if ($group) {
    $options['group'] = $group;
    $options['includeadmins'] = $includeadmins;
    $data = search_user($query, $limit, $offset, $options);
}
else {
    $data = search_user($query, $limit, $offset, $options);
}

if ($data['data']) {
    foreach ($data['data'] as &$result) {
        $result = array('id' => $result['id'], 'name' => $result['name']);
    }
}

json_reply(false, $data);
