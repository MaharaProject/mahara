<?php
/**
 *
 * @package    mahara
 * @subpackage module-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'inbox');
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'multirecipientnotification');
define('SECTION_PAGE', 'outbox');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'multirecipientnotification');
define('SUBSECTIONHEADING', get_string('labeloutbox1',  'module.multirecipientnotification'));

global $THEME;
global $USER;
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
foreach ($installedtypes as $t) {
    // ignore activity type newpost, as each recipients notification appears
    // as a single entry for the poster and thus floods his outbox
    if ((!$t->admin || $USER->get('admin')) && ('newpost' !== $t->name)) {
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
// use the new function to show from - and to user
$activitylist = activitylistout_html($type);

$strread = json_encode(get_string('read', 'activity'));
$strnodelete = json_encode(get_string('nodelete', 'activity'));

$paginationjavascript = <<<JAVASCRIPT

// NOTE: most js is in the notification.js file, but we found
// this part much more difficult to relocate

function toggleMessageDisplay(table, id) {
    var messages = jQuery('#message-' + table + '-' + id);
    if (messages.length <= 0) {
        return;
    }
    var rows = messages.parents("tr");
    if (rows.length > 0) {
        if (jQuery(rows[0]).find(".messagedisplaylong.d-none").length > 0) {
            jQuery(rows[0]).find(".messagedisplaylong").removeClass("d-none");
            jQuery(rows[0]).find(".messagedisplayshort").addClass("d-none");
        }
        else {
            jQuery(rows[0]).find(".messagedisplaylong").addClass("d-none");
            jQuery(rows[0]).find(".messagedisplayshort").removeClass("d-none");
        }
    }
}

function changeactivitytype() {
    var delallform = document.forms['delete_all_notifications'];
    delallform.elements['type'].value = this.options[this.selectedIndex].value;
    var params = {'type': this.options[this.selectedIndex].value};
    sendjsonrequest('indexout.json.php', params, 'GET', function(data) {
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


$deleteall = pieform(array(
    'name'        => 'delete_all_notifications',
    'class'       => 'form-deleteall sr-only',
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
                FROM {module_multirecipient_notification} as msg
                INNER JOIN {module_multirecipient_userrelation} as rel
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
    $SESSION->add_ok_msg(get_string('deletednotifications1', 'module.multirecipientnotification', $count));
    redirect(get_config('wwwroot') . 'module/multirecipientnotification/outbox.php?type=' . $type);
}

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-paper-plane');
$smarty->assign('options', $options);
$smarty->assign('type', $type);

$smarty->assign('INLINEJAVASCRIPT', $paginationjavascript);

// show urls and titles
define('NOTIFICATION_SUBPAGE', 'outbox');
$smarty->assign('SUBPAGENAV', PluginModuleMultirecipientnotification::submenu_items());
$searchtext = param_variable('search', null);
$searcharea = param_variable('searcharea', null);

$searchdata = new stdClass();
$searchdata->searchtext = $searchtext;
$searchdata->searcharea = $searcharea;
$searchdata->searchurl = 'outbox.php?type=' . $type . '&search=' . $searchtext . '&searcharea=';
$searchdata->all_count = 0;
$searchdata->sender_count = 0;
$searchdata->recipient_count = 0;
$searchdata->sub_count = 0;
$searchdata->mes_count = 0;
if ($searchtext !== null) {
    $searchresults = get_message_search($searchtext, $type, 0, 9999999, "outbox.php", $USER->get('id'));
    $searchdata->all_count = $searchresults['All_data']['count'];
    $searchdata->sender_count = $searchresults['Sender']['count'];
    $searchdata->recipient_count = $searchresults['Recipient']['count'];
    $searchdata->sub_count = $searchresults['Subject']['count'];
    $searchdata->mes_count = $searchresults['Message']['count'];
}
$smarty->assign('searchdata', $searchdata);
$smarty->assign('deleteall', $deleteall);
$smarty->assign('activitylist', $activitylist);

// Changed to new tpl
$smarty->display('module:multirecipientnotification:indexout.tpl');
