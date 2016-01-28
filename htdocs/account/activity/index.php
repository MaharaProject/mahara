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
define('MENUITEM', 'inbox');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'activity');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('inbox'));

// Make sure the unread message count is up to date whenever the
// user hits this page.
$USER->reload_background_fields();

$installedtypes = get_records_assoc(
    'activity_type', '', '',
    'plugintype,pluginname,name',
    'name,admin,plugintype,pluginname'
);

$options = array(
    'all' => get_string('alltypes', 'activity'),
);

foreach ($installedtypes as &$t) {
    if (!$t->admin) {
        $section = $t->pluginname ? "{$t->plugintype}.{$t->pluginname}" : 'activity';
        $options[$t->name] = get_string('type' . $t->name, $section);
    }
}

if ($USER->get('admin')) {
    $options['adminmessages'] = get_string('typeadminmessages', 'activity');
}

$type = param_variable('type', 'all');
if ($type == '') {
    $type = 'all';
}
if (!isset($options[$type])) {
    // Comma-separated list; filter out anything that's not an installed type
    $type = join(',', array_unique(array_filter(
        explode(',', $type),
        create_function('$a', 'global $installedtypes; return isset($installedtypes[$a]);')
    )));
}

require_once('activity.php');
$activitylist = activitylist_html($type);

$strread = json_encode(get_string('read', 'activity'));

$javascript = <<<JAVASCRIPT


JAVASCRIPT;

$deleteall = pieform(array(
    'name'        => 'delete_all_notifications',
    'method'      => 'post',
    'plugintype'  => 'core',
    'pluginname'  => 'account',
    'elements'    => array(
        'type' => array(
            'type' => 'hidden',
            'value' => $type,
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('deleteallnotifications', 'activity'),
            'confirm' => get_string('reallydeleteallnotifications', 'activity'),
        ),
    ),
));

function delete_all_notifications_submit() {
    global $USER, $SESSION;

    $userid = $USER->get('id');

    $type = param_variable('type', 'all');
    $typesql = '';
    if ($type != 'all') {
        // Treat as comma-separated list of activity type names
        $types = explode(',', preg_replace('/[^a-z,]+/', '', $type));
        if ($types) {
            $typesql = ' at.name IN (' . join(',', array_map('db_quote', $types)) . ')';
            if (in_array('adminmessages', $types)) {
                $typesql = '(' . $typesql . ' OR at.admin = 1)';
            }
            $typesql = ' AND ' . $typesql;
        }
    }

    $from = "
        FROM {notification_internal_activity} a
        JOIN {activity_type} at ON a.type = at.id
        WHERE a.usr = ? $typesql";
    $values = array($userid);

    db_begin();
    $count = 0;
    $records = get_records_sql_array('SELECT a.id ' . $from, $values);
    if ($records) {
        $count = sizeof($records);
        $ids = array();
        foreach ($records as $row) {
            $ids[] = $row->id;
        }
        // Remove parent pointers to messages we're about to delete
        execute_sql('
            UPDATE {notification_internal_activity}
            SET parent = NULL
            WHERE parent IN (
                ' . join(',', array_map('db_quote', $ids)) . '
            )'
        );
        // delete
        execute_sql('
            DELETE FROM {notification_internal_activity}
            WHERE id IN (
                ' . join(',', array_map('db_quote', $ids)) . '
            )'
        );
        // The update_unread_delete db trigger on notification_internal_activity
        // will update the unread column on the usr table.
    }

    db_commit();
    $SESSION->add_ok_msg(get_string('deletednotifications1', 'activity', $count));
    safe_require('module', 'multirecipientnotification');
    if (PluginModuleMultirecipientnotification::is_active()) {
        redirect(get_config('wwwroot') . 'module/multirecipientnotification/inbox.php?type='.$type);
    }
    else {
        redirect(get_config('wwwroot') . 'account/activity/index.php?type='.$type);
    }
}

$smarty = smarty(array('paginator'));
$smarty->assign('options', $options);
$smarty->assign('type', $type);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('deleteall', $deleteall);
$smarty->assign('activitylist', $activitylist);
$smarty->display('account/activity/index.tpl');
