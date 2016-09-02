<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'framework');

global $USER;

if (!is_plugin_active('framework', 'module')) {
    json_reply(true, get_string('needtoactivate', 'module.framework'));
}
if (!$USER->get('admin')) {
    json_reply(true, get_string('accessdenied'));
}
else {
    form_validate(param_variable('sesskey', null));

    $id = param_integer('id');
    $enabledval = param_alphanum('enabled', false);
    $enabled = ($enabledval == 'on' || $enabledval == 1) ? 1 : 0;

    // need to update the active status
    if (set_field('framework', 'active', $enabled, 'id', $id)) {
        json_reply(false, array('data' => array('ok' => true)));
    }
}

