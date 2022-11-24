<?php
/**
 *
 * @package    mahara
 * @subpackage collection
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$outcomeid = param_integer('outcomeid');
$update_type = param_alpha('update_type', '');

$progress = trim(param_variable('progress', ''));
$support = param_boolean('support', null);

$record = new stdClass();
$record->id = $outcomeid;
switch($update_type) {
  case 'progress':
    $record->progress = $progress;
    $record->lastauthorprogress = $USER->get('id');
    $record->lasteditprogress = db_format_timestamp(time());
    break;
  case 'support':
    $record->support = $support;
    break;
}
if ($outcomeid && update_record('outcome', $record, array('id' => $outcomeid))) {
    json_reply(false, get_string('outcomeupdated','collection'));
}

json_reply('local', get_string('outcomeupdatefailed','collection'));
