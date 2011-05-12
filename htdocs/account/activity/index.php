<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
        split(',', $type),
        create_function('$a', 'global $installedtypes; return isset($installedtypes[$a]);')
    )));
}

require_once('activity.php');
$activitylist = activitylist_html($type);

$star = json_encode($THEME->get_url('images/star.png'));
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
        pd['delete'] = 1;
    }

    if (paginatorData) {
        for (p in paginatorData.params) {
            pd[p] = paginatorData.params[p];
        }
    }
    
    sendjsonrequest('index.json.php', pd, 'GET', function (data) {
        paginator.updateResults(data);
        if (data.newunreadcount && typeof(data.newunreadcount) != 'undefined') {
            updateUnreadCount(data.newunreadcount, 'reset');
        }
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
        if (unread) {
            var pd = {'readone':id};
            sendjsonrequest('index.json.php', pd, 'GET', function(data) {
                swapDOM(unread, IMG({'src' : {$star}, 'alt' : {$strread}}));
                updateUnreadCount(1, 'decrement');
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
        $types = split(',', preg_replace('/[^a-z,]+/', '', $type));
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
    }

    db_commit();
    $SESSION->add_ok_msg(get_string('deletednotifications', 'activity', $count));
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
