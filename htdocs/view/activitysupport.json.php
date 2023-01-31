<?php

/**
 *
 * @package    mahara
 * @subpackage outcome activity
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

global $USER;

$view_id = param_integer('viewId', 0);
$activity_id = param_integer('activityId', 0);
$activity_support_type = param_variable('supportType', null);
$activity_support_value = param_variable('supportText', null);

$view = new View($view_id);
if (!can_view_view($view_id)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}
$grouprole = group_user_access($view->get('group'));
$actionsallowed = ($grouprole === 'admin' || $grouprole === 'tutor');
if ($actionsallowed) {
    // Check if there exists any existing support text this type
    $existing_record = get_record(
        'view_activity_support',
        'activity',
        $activity_id,
        'type',
        $activity_support_type
    );

    $record = new stdClass();
    $record->activity = $activity_id;
    $record->type = $activity_support_type;
    $record->author = $USER->id;
    $record->mtime = db_format_timestamp(time());
    $record->value = $activity_support_value;

    if ($existing_record) {
        $record->id = $existing_record->id;
        update_record('view_activity_support', $record);
    }
    else {
        $record->ctime =  db_format_timestamp(time());
        insert_record('view_activity_support', $record, 'id', true);
    }

    $activity_support_data = get_records_assoc(
        'view_activity_support',
        'activity',
        $activity_id,
        'type',
        'type, value'
    );

    $data = array(
        'error'    => false,
        'message'  => 'Support updated!',
        'supportData' => $activity_support_data
    );
    json_reply(false, $data);
}
$data = new stdClass();
json_reply(false, array('data' => $data));
