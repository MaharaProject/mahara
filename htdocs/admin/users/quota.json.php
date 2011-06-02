<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
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

$record = get_record_sql('SELECT i.name, i.defaultquota FROM {institution} i JOIN {auth_instance} ai ON (i.name = ai.institution) WHERE ai.id = ?', $instid);

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
