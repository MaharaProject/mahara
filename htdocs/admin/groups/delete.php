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
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('MENUITEM', 'managegroups/groups');

$groupid = param_integer('id');
$group = get_record_sql("SELECT g.name FROM {group} g WHERE g.id = ? AND g.deleted = 0", array($groupid));

define('TITLE', get_string('deletespecifiedgroup', 'group', $group->name));

$form = pieform(array(
    'name' => 'admindeletegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-secondary',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'admin/groups/groups.php',
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('message', get_string('groupconfirmdelete', 'group'));
$smarty->assign('form', $form);
$smarty->display('group/delete.tpl');

function admindeletegroup_submit(Pieform $form, $values) {
    global $SESSION, $groupid;
    require_once(get_config('libroot') . 'group.php');
    group_delete($groupid);
    $SESSION->add_ok_msg(get_string('deletegroup', 'group'));
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}
