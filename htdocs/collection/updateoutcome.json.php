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
require_once(get_config('docroot') . 'lib/collection.php');

json_headers();

$outcomeid = param_integer('outcomeid');
$collectionid = param_integer('collectionid');
$update_type = param_alpha('update_type', '');

$progress = trim(param_variable('progress', ''));
$support = (int) param_boolean('support');


$collection = new Collection($collectionid);
// check if user admin or tutor
if (!($collection->get('group') && (
    group_user_access($collection->get('group')) === 'admin' ||
    group_user_access($collection->get('group')) === 'tutor'
  ))) {
  throw new AccessDeniedException();
}

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
  case 'markcomplete':
    $record->complete = 1;
    $record->lastauthor = $USER->get('id');
    $record->lastedit = db_format_timestamp(time());
    break;
  case 'markincomplete':
    $record->complete = 0;
    $record->lastauthor = $USER->get('id');
    $record->lastedit = db_format_timestamp(time());
    break;
}
if ($outcomeid && update_record('outcome', $record, array('id' => $outcomeid))) {
    json_reply(false, get_string('outcomeupdated','collection'));
}

json_reply('local', get_string('outcomeupdatefailed','collection'));
