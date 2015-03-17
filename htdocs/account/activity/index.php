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
require_once('pieforms/pieform.php');
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

$star = json_encode($THEME->get_image_url('star'));
$readicon = json_encode($THEME->get_image_url('readusermessage'));
$strread = json_encode(get_string('read', 'activity'));

$javascript = <<<JAVASCRIPT

function markread(form, action) {

    var e = getElementsByTagAndClassName(null,'tocheck'+action,form);
    var pd = {};

    for (cb in e) {
        if (e[cb].checked == true) {
            pd[e[cb].name] = 1;
        }
    }

    if (action == 'read') {
        pd['markasread'] = 1;
    } else if (action == 'del') {
        // If deleting, also pass the ids of unread messages, so we can update
        // the unread message count as accurately as possible.
        forEach(getElementsByTagAndClassName('input', 'tocheckread', form), function(cb) {
            pd[cb.name] = 0;
        });
        pd['delete'] = 1;
    }

    if (paginatorData) {
        for (p in paginatorData.params) {
            pd[p] = paginatorData.params[p];
        }
    }

    sendjsonrequest('index.json.php', pd, 'GET', function (data) {
        paginator.updateResults(data);
        updateUnreadCount(data);
    });
}

function showHideMessage(id) {
    var message = $('message-' + id);
    if (!message) {
        return;
    }
    if (hasElementClass(message, 'hidden')) {
        var unread = getFirstElementByTagAndClassName(
            'input', 'tocheckread', message.parentNode.parentNode
        );
        var subject = getFirstElementByTagAndClassName(
            'a', 'inbox-showmessage', message.parentNode
        );
        var unreadText = getFirstElementByTagAndClassName(
            null, 'accessible-hidden', subject
        );
        if (unread) {
            var pd = {'readone':id};
            sendjsonrequest('index.json.php', pd, 'GET', function(data) {
                swapDOM(unread, IMG({'src' : {$star}, 'alt' : {$strread}}));
                updateUnreadCount(data);
                removeElementClass(subject, 'unread');
                removeElement(unreadText);
            });
        }
        removeElementClass(message, 'hidden');
    }
    else {
        addElementClass(message, 'hidden');
    }
}

function changeactivitytype() {
    var delallform = document.forms['delete_all_notifications'];
    delallform.elements['type'].value = this.options[this.selectedIndex].value;
    var params = {'type': this.options[this.selectedIndex].value};
    sendjsonrequest('index.json.php', params, 'GET', function(data) {
        paginator.updateResults(data);
    });
}

// We want the paginator to tell us when a page gets changed.
// @todo: remember checked/unchecked state when changing pages
function PaginatorData() {
    var self = this;
    var params = {};

    this.pageChanged = function(data) {
        self.params = {
            'offset': data.offset,
            'limit': data.limit,
            'type': data.type
        }
    }

    paginatorProxy.addObserver(self);
    connect(self, 'pagechanged', self.pageChanged);
}

var paginator;
var paginatorData = new PaginatorData();

addLoadEvent(function () {
    paginator = {$activitylist['pagination_js']}
    connect('notifications_type', 'onchange', changeactivitytype);
});

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
    redirect(get_config('wwwroot') . 'account/activity/index.php?type='.$type);
}

$smarty = smarty(array('paginator'));
$smarty->assign('options', $options);
$smarty->assign('type', $type);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('deleteall', $deleteall);
$smarty->assign('activitylist', $activitylist);
$smarty->display('account/activity/index.tpl');
