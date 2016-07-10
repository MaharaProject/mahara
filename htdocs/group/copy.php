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

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'copy');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$groupid = param_integer('id');
$return = param_alphanum('return', null);

// Check the group exists
if (!get_record('group', 'id', $groupid)) {
    throw new GroupNotFoundException(get_string('groupnotfound', 'group', $groupid));
}

// Check for group role of the user doing the copying
$userid = $USER->get('id');
$role = group_user_access($groupid, $userid);
if (!($USER->get('admin') || $role == 'admin')) {
    throw new AccessDeniedException();
}

group_copy($groupid, $return);
