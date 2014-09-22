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
define('SECTION_PAGE', 'outbox');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'lib/pieforms/pieform.php');
safe_require('artefact', 'multirecipientnotification');

global $THEME;
global $USER;
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
    // ignore activity type newpost, as each recipients notification appears
    // as a single entry for the poster and thus floods his outbox
    if ((!$t->admin) && ('newpost' !== $t->name)) {
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
// use the new function to show from - and to user
$activitylist = activitylistout_html($type);

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

    sendjsonrequest('indexout.json.php', pd, 'GET', function (data) {
        paginator.updateResults(data);
        updateUnreadCount(data);
    });
}

function showHideMessage(id, table) {
    var message = $('message-' + table + '-' + id);
    if (!message) {
        return;
    }
    if (hasElementClass(message, 'hidden')) {
        var unread = getFirstElementByTagAndClassName(
            'input', 'tocheckread', message.parentNode.parentNode
        );
        var unreadicon = getFirstElementByTagAndClassName(
            'img', 'unreadmessage', message.parentNode.parentNode
        );
        if (unread) {
            var pd = {'readone':id, 'table':table};
            sendjsonrequest('indexout.json.php', pd, 'GET', function(data) {
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
    sendjsonrequest('indexout.json.php', params, 'GET', function(data) {
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
    $count = 0;
    if (in_array($type, array('all', 'usermessage'))) {
        if ($type !== 'all') {
            $at = activity_locate_typerecord($type);
            $typecond = 'AND msg.type = ' . $at->id;
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
        $result = get_records_sql_array($query, array($userid, 'sender'));
        $msgids = array();
        if (is_array($result)) {
            foreach ($result as $record) {
                $msgids[] = $record->id;
            }
            db_begin();
            delete_messages_mr($msgids, $userid);
            db_commit();
        }
        $count = count($msgids);
    }
    $SESSION->add_ok_msg(get_string('deletednotifications1', 'artefact.multirecipientnotification', $count));
    redirect(get_config('wwwroot') . 'artefact/multirecipientnotification/outbox.php?type=' . $type);
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
$pages[1]["url"]="artefact/multirecipientnotification/outbox.php";
$pages[1]["title"] = get_string('labeloutbox1', 'artefact.multirecipientnotification');
$pages[1]["selected"] = 1;
// show urls and titles
$smarty->assign('SUBPAGENAV', $pages);
if (param_variable('search', null)!==null) {
    $smarty->assign('searchtext', param_variable('search'));
    $searchresults = get_message_search(param_variable('search'), null, $type, 0, 9999999, "outbox.php", $USER->get('id'));
    $smarty->assign('all_count', $searchresults['ALL_data']['count']);
    $smarty->assign('usr_count', $searchresults['User']['count']);
    $smarty->assign('sub_count', $searchresults['Subject']['count']);
    $smarty->assign('mes_count', $searchresults['Message']['count']);
}
$smarty->assign('deleteall', $deleteall);
$smarty->assign('activitylist', $activitylist);

// Changed to new tpl
$smarty->display('artefact:multirecipientnotification:indexout.tpl');
