<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'inbox');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'multirecipientnotification');
define('SECTION_PAGE', 'inbox');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'multirecipientnotification');

global $USER;
global $THEME;

// Add new title
define('TITLE', get_string('notification', 'artefact.multirecipientnotification'));

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
        split(',', $type),
        function ($a) {global $installedtypes; return isset($installedtypes[$a]);}
    )));
}

require_once(get_config('docroot') . 'lib/activity.php');
// add the new function for outgoing notification
// use the new function to show from - and to user
$activitylist = activitylistin_html($type);

$star = json_encode($THEME->get_url('images/star.png'));
$readicon = json_encode($THEME->get_url('images/readusermessage.png'));
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
    }
    else if (action == 'del') {
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

    sendjsonrequest('indexin.json.php', pd, 'GET', function (data) {
        paginator.updateResults(data);
        updateUnreadCount(data);
    });
}

function showHideMessage(id, table) {
    var message = $('message-' + table + '-' + id);
    if (!message) {
        return;
    }
    jQuery(message).parents("tr.unread").removeClass("unread")
    if (hasElementClass(message, 'hidden')) {
        var unread = getFirstElementByTagAndClassName(
            'input', 'tocheckread', message.parentNode.parentNode
        );
        var unreadicon = getFirstElementByTagAndClassName(
            'img', 'unreadmessage', message.parentNode.parentNode
        );
        if (unread) {
            var pd = {'readone':id, 'table':table};
            sendjsonrequest('indexin.json.php', pd, 'GET', function(data) {
                swapDOM(unread, IMG({'src' : {$star}, 'alt' : {$strread}}));
                if (unreadicon) {
                    swapDOM(unreadicon, IMG({'src' : {$readicon}, 'alt' : getNodeAttribute(unreadicon, 'alt') + ' - ' + {$strread}}));
                };
                updateUnreadCount(data);
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
    sendjsonrequest('indexin.json.php', params, 'GET', function(data) {
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

    db_begin();

    // delete multirecipient-message separately
    $count = 0;
    if (in_array($type, array('all', 'usermessage'))) {
        if ($type !== 'all') {
            $at = activity_locate_typerecord($type);
            $typecond = 'AND {msg}.{type} = ' . $at->id;
        }
        else {
            $typecond = '';
        }

        $query = 'SELECT msg.id AS id
                FROM {artefact_multirecipient_notification} as msg
                INNER JOIN {artefact_multirecipient_userrelation} as rel
                ON msg.id = rel.notification
                AND rel.usr = ?
                AND rel.role = ?
                AND rel.deleted = \'0\'
                ' . $typecond;
        $result = get_records_sql_array($query, array($userid, 'recipient'));
        $msgids = array();
        if (is_array($result)) {
            foreach ($result as $record) {
                $msgids[] = $record->id;
            }
           delete_messages_mr($msgids, $userid);
        }
        $count = count($msgids);
    }

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
    // changed to meesage from usr
    $fromexpression = "FROM {notification_internal_activity} a
        INNER JOIN {activity_type} at ON a.type = at.id
        WHERE a.usr = ? $typesql";
    $values = array($userid);

    $records = get_records_sql_array('SELECT a.id ' . $fromexpression, $values);
    if ($records) {
        $count += sizeof($records);
        $ids = array();
        foreach ($records as $row) {
            $ids[] = $row->id;
        }
        // Remove parent pointers to messages we're about to delete
        execute_sql('
            UPDATE {notification_internal_activity}
            SET parent = NULL
            WHERE parent IN (' . join(',', array_map('db_quote', $ids)) . ')'
        );
        // delete
        execute_sql('
            DELETE FROM {notification_internal_activity}
            WHERE id IN (' . join(',', array_map('db_quote', $ids)) . ')'
        );
        // The update_unread_delete db trigger on notification_internal_activity
        // will update the unread column on the usr table.
    }

    db_commit();
    $SESSION->add_ok_msg(get_string('deletednotifications1', 'activity', $count));
    redirect(get_config('wwwroot') . 'artefact/multirecipientnotification/inbox.php?type=' . $type);
}

$extrastylesheets = $THEME->get_url('style.css', false, 'artefact/multirecipientnotification');
$smarty = smarty(array('paginator'),
    array('<link rel="stylesheet" type="text/css" href="' . $extrastylesheets . '">')
);
$smarty->assign('options', $options);
$smarty->assign('type', $type);
$smarty->assign('INLINEJAVASCRIPT', $javascript);

// Adding the links to out- and inbox
$smarty->assign('PAGEHEADING', TITLE);
// Add urls and titles
$pages = array();
$pages[0]["url"] = "artefact/multirecipientnotification/inbox.php";
$pages[0]["title"] = get_string('labelinbox', 'artefact.multirecipientnotification');
$pages[0]["selected"] = 1;
$pages[1]["url"] = "artefact/multirecipientnotification/outbox.php";
$pages[1]["title"] = get_string('labeloutbox1', 'artefact.multirecipientnotification');
// show urls and titles
$smarty->assign('SUBPAGENAV', $pages);

$smarty->assign('deleteall', $deleteall);
$smarty->assign('activitylist', $activitylist);

// Changed to new tpl
$smarty->display('artefact:multirecipientnotification:indexin.tpl');
