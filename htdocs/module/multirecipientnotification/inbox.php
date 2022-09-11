<?php
/**
 *
 * @package    mahara
 * @subpackage module-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'inbox');
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'multirecipientnotification');
define('SECTION_PAGE', 'inbox');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'multirecipientnotification');
define('SUBSECTIONHEADING', get_string('labelinbox',  'module.multirecipientnotification'));

global $USER;
global $THEME;

if (!PluginModuleMultirecipientnotification::is_active()) {
    $redirTarget = get_config('wwwroot') . 'account/activity/inbox.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        // change path
        $redirTarget .='?' . $_SERVER['QUERY_STRING'];
    }
    redirect($redirTarget);
    exit;
}

// Add new title
define('TITLE', get_string('notification', 'module.multirecipientnotification'));

// Make sure the unread message count is up to date whenever the
// user hits this page.
$USER->reload_background_fields();

$installedtypes = get_records_assoc(
    'activity_type', '', '',
    'plugintype,pluginname,name',
    'name,"admin",plugintype,pluginname'
);

$options = array();
// check if user is an admin in at least one group and if so they can see group admin filter options
$groupadmin = false;
$grouproles = $USER->get('grouproles');
if (array_search('admin', $grouproles) !== false) {
    $groupadmin = true;
}

foreach ($installedtypes as $t) {
    if (!$t->admin || $USER->get('admin') || ($groupadmin && $t->pluginname == 'forum')) {
        $section = $t->pluginname ? "{$t->plugintype}.{$t->pluginname}" : 'activity';
        $options[$t->name] = get_string('type' . $t->name, $section);
    }
}

if ($USER->get('admin')) {
    $options['adminmessages'] = get_string('typeadminmessages', 'activity');
}

// sort activitytypes now, when they have been translated
uasort($options, 'strcmp');
// ... and add the element for 'all types' to the beginning
$options = array_merge(array('all' => get_string('alltypes', 'activity')), $options);
$type = param_variable('type', 'all');
if ($type == '') {
    $type = 'all';
}
if (!isset($options[$type])) {
    // Comma-separated list; filter out anything that's not an installed type
    $type = join(',', array_unique(array_filter(
        explode(',', $type),
        function ($a) {global $installedtypes; return isset($installedtypes[$a]);}
    )));
}

require_once(get_config('docroot') . 'lib/activity.php');
// add the new function for outgoing notification
// use the new function to show from - and to user
$activitylist = activitylistin_html($type);

$strread = json_encode(get_string('read', 'activity'));
$strnodelete = json_encode(get_string('nodelete', 'activity'));

$paginationjavascript = <<<JAVASCRIPT

// NOTE: most js is in the notification.js file, but we found
// this part much more difficult to relocate
function changeactivitytype() {
    var delallform = document.forms['delete_all_notifications'];
    delallform.elements['type'].value = this.options[this.selectedIndex].value;
    var params = {'type': this.options[this.selectedIndex].value};
    sendjsonrequest('indexin.json.php', params, 'GET', function(data) {
        jQuery("#activitylist span.countresults").remove();
        jQuery("#activitylist th").each(function() {
                var link = jQuery(this).find('a');
                if (link.length >= 1) {
                    var headertext = link.text().trim();
                    jQuery(this).html(headertext);
                }
            });
        jQuery("input#search").val('');
        paginator.updateResults(data);
    });
}

jQuery(function($) {
// We want the paginator to tell us when a page gets changed.
  function PaginatorData() {
      var self = this;
      var params = {};
      this.pageChanged = function(ev, data) {
          self.params = {
              'offset': data.offset,
              'limit': data.limit,
              'type': data.type
          }
      }
      paginatorProxy.addObserver(self);
      $(self).on('pagechanged', self.pageChanged);
  }
  window.paginatorData = new PaginatorData();

  window.paginator = {$activitylist['pagination_js']}
});
JAVASCRIPT;

$externalmsg = param_integer('msg', 0);
$externalmsgtype = param_variable('msgtype', '');
$externalmsgtype = preg_replace('/[^a-z0-9_]+/i', '', $externalmsgtype);
if ($externalmsg && $externalmsgtype) {
    $paginationjavascript .= '

jQuery(function($) {
    $(document).ready(function() {
        $("#notification-' . $externalmsgtype . '-' . $externalmsg . '").collapse("show");
    });
});';
}

$deleteall = pieform(array(
    'name'        => 'delete_all_notifications',
    'class'       => 'form-deleteall visually-hidden',
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
            'class' => 'deleteallnotifications',
            'value' => get_string('deleteallnotifications', 'activity'),
            'confirm' => get_string('reallydeleteallnotifications', 'activity'),
        ),
    ),
));

function delete_all_notifications_submit() {
    global $USER, $SESSION;
    $userid = $USER->get('id');
    $type = param_variable('type', 'all');

    $plugins = plugin_all_installed();
    foreach ($plugins as $key => $plugin) {
        $classname = generate_class_name($plugin->plugintype, $plugin->name);
        safe_require($plugin->plugintype, $plugin->name);
        if (!is_callable(array($classname, 'notification_delete'))) {
            unset ($plugins[$key]);
        }
    }

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
                FROM {module_multirecipient_notification} as msg
                INNER JOIN {module_multirecipient_userrelation} as rel
                ON msg.id = rel.notification
                AND rel.usr = ?
                AND rel.role = ?
                AND rel.deleted = 0
                ' . $typecond;
        $result = get_records_sql_array($query, array($userid, 'recipient'));
        $msgids = array();
        if (is_array($result)) {
            foreach ($result as $record) {
                $msgids[] = $record->id;
            }
           delete_messages_mr($msgids, $userid);

            foreach ($plugins as $plugin) {
                $classname = generate_class_name($plugin->plugintype, $plugin->name);
                call_static_method($classname, 'notification_delete', $msgids, $userid, 'module_multirecipient_notification');
            }
        }
        $count = count($msgids);
    }

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

        // And make sure any plugins that want to handle it can do so.
        foreach ($plugins as $plugin) {
            $classname = generate_class_name($plugin->plugintype, $plugin->name);
            call_static_method($classname, 'notification_delete', $ids, $userid, 'notification_internal_activity');
        }
    }

    db_commit();
    $SESSION->add_ok_msg(get_string('deletednotifications1', 'activity', $count));
    redirect(get_config('wwwroot') . 'module/multirecipientnotification/inbox.php?type=' . $type);
}

$smarty = smarty(array('paginator'));
$smarty->assign('options', $options);
$smarty->assign('type', $type);
$smarty->assign('INLINEJAVASCRIPT', $paginationjavascript);

// show urls and titles
define('NOTIFICATION_SUBPAGE', 'inbox');
$smarty->assign('SUBPAGENAV', PluginModuleMultirecipientnotification::submenu_items());
setpageicon($smarty, 'icon-inbox');
$searchtext = param_variable('search', null);
$searcharea = param_variable('searcharea', null);

$searchdata = new stdClass();
$searchdata->searchtext = $searchtext;
$searchdata->searcharea = $searcharea;
$searchdata->searchurl = 'inbox.php?type=' . $type . '&search=' . $searchtext . '&searcharea=';
$searchdata->tabs = array();
if ($searchtext !== null) {
    $searchresults = get_message_search($searchtext, $type, 0, null, "inbox.php", $USER->get('id'));
    unset($searchresults['Recipient']);
    foreach ($searchresults as $section => $value) {
        $term = new stdClass();
        $term->name = $section;
        $term->count = $value['count'];
        switch ($section) {
            case 'All_data': $term->tag = 'labelall'; break;
            case 'Sender': $term->tag = 'fromuser'; break;
            case 'Subject': $term->tag = 'subject'; break;
            case 'Message': $term->tag = 'labelmessage';
        }
        $searchdata->tabs[] = $term;
    }
}
$smarty->assign('searchdata', $searchdata);
$smarty->assign('deleteall', $deleteall);
$smarty->assign('activitylist', $activitylist);

// Changed to new tpl
$smarty->display('module:multirecipientnotification:indexin.tpl');
