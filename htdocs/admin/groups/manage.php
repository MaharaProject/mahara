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
define('MENUITEM', 'managegroups/groups');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$group = get_record_select('group', 'id = ? AND deleted = 0', array(param_integer('id')));

define('TITLE', get_string('administergroups', 'admin'));

$quotasform = pieform(array(
    'name'       => 'groupquotasform',
    'renderer'   => 'div',
    'elements'   => array(
        'groupid' => array(
            'type' => 'hidden',
            'value' => $group->id,
        ),
        'quota'  => array(
            'type' => 'bytes',
            'hiddenlabel' => true,
            'title' => get_string('filequota1', 'admin'),
            'defaultvalue' => $group->quota,
            'description'  => '<p class="text-small text-midtone">' .get_string('groupfilequotadescription', 'admin') . '</p>',
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('save'),
        )
    ),
));

function groupquotasform_submit(Pieform $form, $values) {
    global $SESSION;

    $oldquota = get_field('group', 'quota', 'id', $values['groupid']);
    $group = new StdClass;
    $group->id = $values['groupid'];
    $group->quota = $values['quota'];
    update_record('group', $group);

    if (!empty($values['quota']) && $values['quota'] != $oldquota) {
        // We need to alert group admins that the group may now be over the threshold
        $quotanotifylimit = get_config_plugin('artefact', 'file', 'quotanotifylimit');
        $sqlwhere = " ((g.quotaused / g.quota) * 100) ";
        if (is_postgres()) {
            $sqlwhere = " ((CAST(g.quotaused AS float) / CAST(g.quota AS float)) * 100) ";
        }
        if ($groups = get_records_sql_assoc("SELECT g.id, g.name, g.quota, " . $sqlwhere . " AS quotausedpercent FROM {group} g WHERE " . $sqlwhere . " >= ? AND id = ?", array($quotanotifylimit, $values['groupid']))) {
            require_once(get_config('docroot') . 'artefact/file/lib.php');
            ArtefactTypeFile::notify_groups_threshold_exceeded($groups);
        }
    }

    $SESSION->add_ok_msg(get_string('groupquotaupdated', 'admin'));
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}


$admins = get_column_sql(
    "SELECT gm.member FROM {group_member} gm WHERE gm.role = 'admin' AND gm.group = ?", array($group->id)
);

$groupadminsform = pieform(array(
    'name'       => 'groupadminsform',
    'renderer'   => 'div',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => array(
        'admins' => array(
            'type' => 'userlist',
            'hiddenlabel' => true,
            'title' => get_string('groupadmins', 'group'),
            'defaultvalue' => $admins,
            'lefttitle' => get_string('potentialadmins', 'admin'),
            'righttitle' => get_string('currentadmins', 'admin'),
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('save'),
        ),
    ),
));

function groupadminsform_submit(Pieform $form, $values) {
    global $SESSION, $group, $admins;

    $newadmins = array_diff($values['admins'], $admins);
    $demoted = array_diff($admins, $values['admins']);

    db_begin();
    if ($demoted) {
        $demoted = join(',', array_map('intval', $demoted));
        execute_sql("
            UPDATE {group_member}
            SET role = 'member'
            WHERE role = 'admin' AND \"group\" = ?
                AND member IN ($demoted)",
            array($group->id)
        );
    }
    $dbnow = db_format_timestamp(time());
    foreach ($newadmins as $id) {
        if (group_user_access($group->id, $id)) {
            group_change_role($group->id, $id, 'admin');
        }
        else {
            group_add_user($group->id, $id, 'admin');
        }
    }
    db_commit();

    $SESSION->add_ok_msg(get_string('groupadminsupdated', 'admin'));
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-users');

$smarty->assign('quotasform', $quotasform);
$smarty->assign('groupname', $group->name);
$smarty->assign('managegroupform', $groupadminsform);
$smarty->display('admin/groups/manage.tpl');
