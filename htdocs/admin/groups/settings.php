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
define('MENUITEM', 'managegroups/settings');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('groupdefaultsettings', 'admin'));
// Set up default options
$default_group_data = (object) array(
   'open'           => 1,
   'controlled'     => 0,
   'request'        => 0,
   'grouptype'      => 'standard',
   'invitefriends'  => 0,
   'suggestfriends' => 0,
   'editroles'      => 'all',
   'submittableto'  => 0,
   'allowarchives'  => 0,
   'public'         => 0,
   'hidden'         => 0,
   'hidemembers'    => GROUP_HIDE_NONE,
   'hidemembersfrommembers'  => GROUP_HIDE_NONE,
   'groupparticipationreports' => 0,
   'editwindowstart'  => null,
   'editwindowend'    => null,
   'category'       => 0,
   'usersautoadded' => 0,
   'viewnotify'     => GROUP_ROLES_ALL,
   'feedbacknotify' => GROUP_ROLES_ALL,
   'sendnow'        => 0,
);

// Get the current set values from db
$group_data = new stdClass();
foreach ($default_group_data as $k => $v) {
    $opt = get_config_institution('mahara', 'group_' . $k);
    if ($opt !== null) {
        $v = $opt;
        if ($k == 'editwindowstart' || $k == 'editwindowend') {
            $v = strtotime($v);
        }
    }
    $group_data->$k = $v;
}

$groupcategories = get_records_menu('group_category', '', '', 'displayorder', 'id,title');
$groupcategories = $groupcategories ? $groupcategories : array();
$notifyroles = group_get_editroles_options(true);
$notifyroles = $notifyroles ? $notifyroles : array();
$currentdate = getdate();

$optionform = pieform(array(
    'name'       => 'groupsettings',
    'renderer'   => 'div',
    'class'      => '',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => array(
        'membership' => array(
            'type'         => 'html',
            'value'        => '<h2>' . get_string('Membership', 'group') . '</h2>',
        ),
        'open' => array(
            'type'         => 'switchbox',
            'title'        => get_string('Open', 'group'),
            'description'  => get_string('opendescription', 'group'),
            'defaultvalue' => $group_data->open,
        ),
        'controlled' => array(
            'type'         => 'switchbox',
            'title'        => get_string('Controlled', 'group'),
            'description'  => get_string('controlleddescription', 'group'),
            'defaultvalue' => $group_data->controlled,
        ),
        'request' => array(
            'type'         => 'switchbox',
            'title'        => get_string('request', 'group'),
            'description'  => get_string('requestdescription', 'group'),
            'defaultvalue' => !$group_data->open && $group_data->request,
            'disabled'     => $group_data->open,
        ),
        'grouptype' => array(
            'type'         => 'select',
            'title'        => get_string('Roles', 'group'),
            'options'      => group_get_grouptype_options($group_data->grouptype),
            'defaultvalue' => $group_data->grouptype,
        ),
        'invitefriends' => array(
            'type'         => 'switchbox',
            'title'        => get_string('friendinvitations', 'group'),
            'description'  => get_string('invitefriendsdescription1', 'group'),
            'defaultvalue' => !get_config('friendsnotallowed') && $group_data->invitefriends,
            'disabled'     => get_config('friendsnotallowed'),
        ),
        'suggestfriends' => array(
            'type'         => 'switchbox',
            'title'        => get_string('Recommendations', 'group'),
            'description'  => get_string('suggestfriendsdescription1', 'group'),
            'defaultvalue' => $group_data->suggestfriends && ($group_data->open || $group_data->request),
            'disabled'     => !$group_data->open && !$group_data->request,
        ),
        'pages' => array(
            'type'         => 'html',
            'value'        => '<h2>' . get_string('content') . '</h2>',
        ),
        'editroles' => array(
            'type'         => 'select',
            'options'      => group_get_editroles_options(),
            'title'        => get_string('editroles1', 'group'),
            'description'  => get_string('editrolesdescription2', 'group'),
            'defaultvalue' => $group_data->editroles,
        ),
        'submittableto' => array(
            'type'         => 'switchbox',
            'title'        => get_string('allowsubmissions', 'group'),
            'description'  => get_string('allowssubmissionsdescription1', 'group'),
            'defaultvalue' => $group_data->submittableto,
        ),
        'allowarchives' => array(
            'type'         => 'switchbox',
            'title'        => get_string('allowsarchives', 'group'),
            'description'  => get_string('allowsarchivesdescription1', 'group'),
            'defaultvalue' => $group_data->allowarchives,
            'disabled'     => !$group_data->submittableto,
        ),
        'visibility' => array(
            'type'         => 'html',
            'value'        => '<h2>' . get_string('Visibility') . '</h2>',
        ),
        'public' => array(
            'type'         => 'switchbox',
            'title'        => get_string('publiclyviewablegroup', 'group'),
            'description'  => get_string('publiclyviewablegroupdescription1', 'group'),
            'defaultvalue' => $group_data->public,
        ),
        'hidden' => array(
            'type'         => 'switchbox',
            'title'        => get_string('hiddengroup', 'group'),
            'description'  => get_string('hiddengroupdescription2', 'group'),
            'defaultvalue' => $group_data->hidden,
        ),
        'hidemembers' => array(
            'type'         => 'select',
            'options'      => group_hide_members_options(),
            'title'        => get_string('hidemembers', 'group'),
            'description'  => get_string('hidemembersdescription', 'group'),
            'defaultvalue' => ($group_data->hidemembersfrommembers ? $group_data->hidemembersfrommembers : ($group_data->hidemembers ? $group_data->hidemembers : 0)),
            'disabled'     => $group_data->hidemembersfrommembers,
        ),
        'hidemembersfrommembers' => array(
            'type'         => 'select',
            'options'      => group_hide_members_options(),
            'title'        => get_string('hidemembersfrommembers', 'group'),
            'description'  => get_string('hidemembersfrommembersdescription1', 'group'),
            'defaultvalue' => $group_data->hidemembersfrommembers,
        ),
        'groupparticipationreports' => array(
            'type'         => 'switchbox',
            'title'        => get_string('groupparticipationreports', 'group'),
            'description'  => get_string('groupparticipationreportsdesc1', 'group'),
            'defaultvalue' => $group_data->groupparticipationreports,
        ),
        'editability' => array(
            'type'        => 'html',
            'value'       => '<h2>' . get_string('editability', 'group') . '</h2>',
        ),
        'editwindowstart' => array(
            'type'        => 'calendar',
            'class'        => '',
            'title'        => get_string('windowstart', 'group'),
            'defaultvalue' => $group_data->editwindowstart,
            'description'  => get_string('windowstartdescription', 'group') . ' ' . get_string('windowdatedescriptionadmin', 'group'),
            'minyear'      => $currentdate['year'],
            'maxyear'      => $currentdate['year'] + 20,
            'time'         => true,
            'caloptions'   => array(
                'showsTime'      => true,
            )
        ),
        'editwindowend' => array(
            'type'        => 'calendar',
            'class'        => '',
            'title'        => get_string('windowend', 'group'),
            'defaultvalue' => $group_data->editwindowend,
            'description'  =>  get_string('windowenddescription', 'group') . ' ' . get_string('windowdatedescriptionadmin', 'group'),
            'minyear'      => $currentdate['year'],
            'maxyear'      => $currentdate['year'] + 20,
            'time'         => true,
            'caloptions'   => array(
                'showsTime'      => true,
            )
        ),
        'general' => array(
            'type'         => 'html',
            'value'        => '<h2>' . get_string('general') . '</h2>',
        ),
        'category' => array(
            'type'         => 'select',
            'title'        => get_string('groupcategory', 'group'),
            'options'      => array('0' => get_string('nocategoryselected', 'group')) + $groupcategories,
            'defaultvalue' => $group_data->category,
            'disabled'     => !$groupcategories,
        ),
        'usersautoadded' => array(
            'type'         => 'switchbox',
            'title'        => get_string('usersautoadded', 'group'),
            'description'  => get_string('usersautoaddeddescription1', 'group'),
            'defaultvalue' => $group_data->usersautoadded,
        ),
        'viewnotify' => array(
            'type'         => 'select',
            'title'        => get_string('viewnotify', 'group'),
            'options'      => array(get_string('none', 'admin')) + $notifyroles,
            'description'  => get_string('viewnotifydescription2', 'group'),
            'defaultvalue' => $group_data->viewnotify,
            'disabled'     => !$notifyroles,
        ),
        'feedbacknotify' => array(
            'type'         => 'select',
            'title'        => get_string('commentnotify', 'group'),
            'options'      => array(get_string('none', 'admin')) + $notifyroles,
            'description'  => get_string('commentnotifydescription1', 'group'),
            'defaultvalue' => $group_data->feedbacknotify,
            'disabled'     => !$notifyroles,
        ),
        'sendnow' => array(
            'type'         => 'switchbox',
            'title'        => get_string('allowsendnow', 'group'),
            'description'  => get_string('allowsendnowdescription1', 'group'),
            'defaultvalue' => $group_data->sendnow,
        ),
        'defaultresetheader' => array(
            'type'         => 'html',
            'value'        => '<h2>' . get_string('defaultreset', 'admin') . '</h2>',
        ),
        'defaultreset' => array(
            'type'         => 'switchbox',
            'class'        => 'field-label-bold',
            'title'        => get_string('defaultresetlabel', 'admin'),
            'description'  => get_string('defaultresetdesc', 'admin'),
            'defaultvalue' => false,
        ),
        'submit' => array(
            'class'        => 'btn-primary',
            'type'         => 'submit',
            'value'        => get_string('submit'),
        ),
    )
));

$inlinejs = <<<EOF
jQuery(function($) {
    $("#groupsettings_controlled").on("click", function() {
        if (this.checked) {
            $("#groupsettings_request").prop("disabled", false);
            $("#groupsettings_open").prop("checked", false);
            if (!$("#groupsettings_request").attr("checked")) {
                $("#groupsettings_suggestfriends").prop("checked", false);
                $("#groupsettings_suggestfriends").prop("disabled", true);
            }
        }
    });
    $("#groupsettings_open").on("click", function() {
        if (this.checked) {
            $("#groupsettings_controlled").prop("checked", false);
            $("#groupsettings_request").prop("checked", false);
            $("#groupsettings_request").prop("disabled", true);
            $("#groupsettings_suggestfriends").prop("disabled", false);
        }
        else {
            $("#groupsettings_request").prop("disabled", false);
            if (!$("#groupsettings_request").attr("checked")) {
                $("#groupsettings_suggestfriends").prop("checked", false);
                $("#groupsettings_suggestfriends").prop("disabled", true);
            }
        }
    });
    $("#groupsettings_submittableto").on("click", function() {
        if (this.checked) {
            $("#groupsettings_allowarchives").prop("disabled", false);
        }
        else {
            $("#groupsettings_allowarchives").prop("checked", false);
            $("#groupsettings_allowarchives").prop("disabled", true);
        }
    });
    $("#groupsettings_request").on("click", function() {
        if (this.checked) {
            $("#groupsettings_suggestfriends").prop("disabled", false);
        }
        else {
            if (!$("#groupsettings_open").attr("checked")) {
                $("#groupsettings_suggestfriends").prop("checked", false);
                $("#groupsettings_suggestfriends").prop("disabled", true);
            }
        }
    });
    $("#groupsettings_invitefriends").on("click", function() {
        if (this.checked) {
            if ($("#groupsettings_request").attr("checked") || $("#groupsettings_open").attr("checked")) {
                $("#groupsettings_suggestfriends").prop("disabled", false);
            }
            $("#groupsettings_suggestfriends").prop("checked", false);
        }
    });
    $("#groupsettings_suggestfriends").on("click", function() {
        if (this.checked) {
            $("#groupsettings_invitefriends").prop("checked", false);
        }
    });
    $("#groupsettings_hidemembersfrommembers").on("change", function() {
        if ($("#groupsettings_hidemembersfrommembers option:selected").val() != "0") {
            $("#groupsettings_hidemembers").prop("selectedIndex", $("#groupsettings_hidemembersfrommembers option:selected").val());
            $("#groupsettings_hidemembers").prop("disabled", "disabled");
        }
        else {
            $("#groupsettings_hidemembers").prop("disabled", false);
        }
    });
    $("#groupsettings_submittableto").on("click", function() {
        if (this.checked) {
            $("#groupsettings_allowarchives").prop("disabled", false);
        }
        else {
            $("#groupsettings_allowarchives").prop("checked", false);
            $("#groupsettings_allowarchives").prop("disabled", true);
        }
    });
});
EOF;

function groupsettings_validate(Pieform $form, $values) {
    if (empty($values['defaultreset'])) {
        if (!empty($values['open'])) {
            if (!empty($values['controlled'])) {
                $form->set_error('open', get_string('membershipopencontrolled', 'group'));
            }
            if (!empty($values['request'])) {
                $form->set_error('request', get_string('membershipopenrequest', 'group'));
            }
        }
        if (!empty($values['invitefriends']) && !empty($values['suggestfriends'])) {
            $form->set_error('invitefriends', get_string('suggestinvitefriends', 'group'));
        }
        if (!empty($values['suggestfriends']) && empty($values['open']) && empty($values['request'])) {
            $form->set_error('suggestfriends', get_string('suggestfriendsrequesterror', 'group'));
        }
        if (!empty($values['allowarchives']) && empty($values['submittableto'])) {
            $form->set_error('allowarchives', get_string('allowsarchiveserror', 'group'));
        }
    }
}

function groupsettings_submit(Pieform $form, $values) {
    global $SESSION;

    if (!empty($values['defaultreset'])) {
        execute_sql("DELETE FROM {institution_config} WHERE institution = ? AND field LIKE ? || '%'", array('mahara', 'group_'));
    }
    else {
        $ignore = array('membership', 'pages', 'visibility', 'general', 'defaultresetheader', 'submit', 'sesskey');
        $text = array('grouptype', 'editroles');
        foreach ($values as $k => $v) {
            if (!in_array($k, $ignore)) {
                if (!in_array($k, $text)) {
                    $v = (int)$v;  // save boolean as 0 or 1
                }
                if ($v === '' || $v === null) {
                    // remove empty options
                    execute_sql("DELETE FROM {institution_config} WHERE institution = ? AND field = ?", array('mahara', 'group_' . $k));
                }
                else {
                    if ($k == 'editwindowstart' || $k == 'editwindowend') {
                        $v = db_format_timestamp($v);
                    }
                    set_config_institution('mahara', 'group_' . $k, $v);
                }
            }
        }
    }
    $SESSION->add_ok_msg(get_string('savedgroupconfigsuccessfully', 'admin'));
    redirect('/admin/groups/settings.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-users-cog');

$smarty->assign('PAGEHEADING', hsc(get_string('groupdefaultsettings', 'admin')));
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('optionform', $optionform);
$smarty->display('admin/groups/groupsettings.tpl');
