<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');
$type       = param_alpha('type', null);

$result = array();

safe_require($plugintype, $pluginname);

if ($type) {
    $classname = generate_artefact_class_name($type);
}
else {
    $classname = generate_class_name($plugintype, $pluginname);
}

if (call_static_method($classname, 'has_config_info')) {
    $info = call_static_method($classname, 'get_config_info');
    $result['info_header'] = $info['header'];
    $result['info_body'] = $info['body'];
    json_reply(null, array('data' => $result));
}
else {
    json_reply(true, array('message' => get_string('noinformation')));
}
