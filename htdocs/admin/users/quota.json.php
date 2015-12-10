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
define('INSTITUTIONALADMIN', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform/elements/bytes.php');


$instid = param_integer('instid');
$disabled = param_boolean('disabled', false);
$definst = get_field('auth_instance', 'id', 'institution', 'mahara');

$record = get_record_sql('SELECT i.name, i.defaultquota FROM {institution} i JOIN {auth_instance} ai ON (i.name = ai.institution) WHERE ai.id = ?', array($instid));

if (!$USER->get('admin') && !$USER->is_institutional_admin($record->name)) {
    json_reply(true, 'You are not an administrator for institution '.$record->name);
    return;
}

if ($definst && $instid == $definst) {
    $quota = get_config_plugin('artefact', 'file', 'defaultquota');
}
else {
    $quota = $record->defaultquota;
    if (!$quota) {
        $quota = get_config_plugin('artefact', 'file', 'defaultquota');
    }
}

$data = array(
    'data' => ($disabled ? display_size($quota) : pieform_element_bytes_get_bytes_from_bytes($quota)),
    'error' => false,
    'message' => null,
);
json_reply(false, $data);
