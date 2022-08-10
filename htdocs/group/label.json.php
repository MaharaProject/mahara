<?php
/**
 * Add personal labels to a group.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'group.php');

$id = param_integer('groupid');
$member = get_field('group_member', 'role', 'member', $USER->get('id'), 'group', $id);

if (!$member) {
    // Not a member of the group - may be due to group not existing or has been deleted
    $html = '<div class="alert alert-danger">' . get_string('grouplabelnotmember', 'group') . '</div>';
}
else {
    // Group exists and is member
    $html = group_label_form($id);
}

json_reply(false, array(
    'message' => null,
    'html' => $html,
));
