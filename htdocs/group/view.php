<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('groups'));
require_once('group.php');

$id = param_integer('id');
$joincontrol = param_alpha('joincontrol', null);
$pending = param_integer('pending', 0);

if (!$group = get_record('group', 'id', $id)) {
    throw new GroupNotFoundException("Couldn't find group with id $id");
}
$group->ownername = display_name(get_record('usr', 'id', $group->owner));

$membership = user_can_access_group($id);
// $membership is a bit string summing all membership types
$ismember = (bool) ($membership & GROUP_MEMBERSHIP_MEMBER);

if (!empty($joincontrol)) {
    // leave, join, acceptinvite, request
    switch ($joincontrol) {
        case 'leave':
            // make sure they're a member and can leave
            if ($ismember && $group->jointype != 'controlled') {
                group_remove_member($id, $USER->get('id'));
                $SESSION->add_ok_msg(get_string('leftgroup', 'group'));
            } 
            else {
                $SESSION->add_error_msg(get_string('couldnotleavegroup', 'group'));
            }
            break;
        case 'join':
            if (!$ismember && $group->jointype == 'open') {
                group_add_member($id, $USER->get('id'));
                $SESSION->add_ok_msg(get_string('joinedgroup', 'group'));
            }
            else {
                $SESSION->add_error_msg(get_string('couldnotjoingroup', 'group'));
            }
            break;
        case 'acceptinvite':
        case 'declineinvite':
            if (!$request = get_record('group_member_invite', 'member', $USER->get('id'), 'group', $id)) {
                $SESSION->add_error_msg(get_string('groupnotinvited', 'group'));
                break;
            }
            if ($joincontrol == 'acceptinvite') {
                group_add_member($id, $USER->get('id'));
                $message = get_string('groupinviteaccepted', 'group');
            }
            else {
                $message = get_string('groupinvitedeclined', 'group');
            }
            delete_records('group_member_invite', 'member', $USER->get('id'), 'group', $id);
            $SESSION->add_ok_msg($message);
            break;
        case 'request':
            if (!$ismember && $group->jointype == 'request' 
                && !record_exists('group_member_request', 'group', $id, 'member', $USER->get('id'))) {
                $gmr = new StdClass;
                $gmr->reason = param_variable('reason', null);
                $gmr->group = $id;
                $gmr->member = $USER->get('id');
                $gmr->ctime = db_format_timestamp(time());
                $owner = get_record('usr', 'id', $group->owner);
                insert_record('group_member_request', $gmr);
                if (empty($gmr->reason)) {
                    $message = get_string('grouprequestmessage', 'group', 
                                          display_name($USER, $owner), $group->name);
                } 
                else {
                    $message = get_string('grouprequestmessagereason', 'group', 
                                          display_name($USER, $owner), $group->name, $gmr->reason);
                }
                require_once('activity.php');
                activity_occurred('maharamessage', 
                    array('users'   => array($group->owner), 
                          'subject' => get_string('grouprequestsubject', 'group'),
                          'message' => $message,
                          'url'     => get_config('wwwroot') . 'group/view.php?id=' . $id));
                $SESSION->add_ok_msg(get_string('grouprequestsent', 'group'));
            }
            else {
                $SESSION->add_error_msg(get_string('couldnotrequestgroup', 'group'));
            }
            break;
    }
    // redirect, stuff will have changed
    redirect('/group/view.php?id=' . $id);
    exit;
}

$invited   = get_record('group_member_invite', 'group', $id, 'member', $USER->get('id'));
$requested = get_record('group_member_request', 'group', $id, 'member', $USER->get('id'));

$userview = get_config('wwwroot') . 'user/view.php?id=';
$viewview = get_config('wwwroot') . 'view/view.php?id=';
$commview = get_config('wwwroot') . 'group/view.php';

// strings that are used in the js
$releaseviewstr  = get_string('releaseview', 'group');
$tutorstr        = get_string('tutor', 'group');
$memberstr       = get_string('member', 'group');
$removestr       = get_string('remove', 'group');
$declinestr      = get_string('declinerequest', 'group');
$updatefailedstr = get_string('updatefailed', 'group');
$requeststr      = get_string('sendrequest', 'group');
$reasonstr       = get_string('reason', 'group');

// all the permissions stuff
//$tutor          = (int)($membership && ($membership != GROUP_MEMBERSHIP_MEMBER));
$controlled     = (int)($group->jointype == 'controlled');
$request        = (int)($group->jointype == 'request');
$tutor          = (int)(bool)($membership & GROUP_MEMBERSHIP_TUTOR);
$admin          = (int)(bool)($membership & GROUP_MEMBERSHIP_ADMIN);
$staff          = (int)(bool)($membership & GROUP_MEMBERSHIP_STAFF);
$owner          = (int)(bool)($membership & GROUP_MEMBERSHIP_OWNER);
$canupdate      = (int)(bool)($tutor || $staff || $admin || $owner);
$canpromote     = (int)(bool)(($staff || $admin) && $controlled);
$canremove      = (int)(bool)(($tutor && $controlled) || $staff || $admin || $owner);
$canleave       = ($ismember && !$controlled);
$canrequestjoin = (!$ismember && empty($invited) && empty($requested) && $group->jointype == 'request');
$canjoin        = (!$ismember && $group->jointype == 'open' && !$owner);

$javascript = '';
if ($membership) {
    $javascript .= <<<EOF

viewlist = new TableRenderer(
    'group_viewlist',
    'view.json.php',
    [
     function (r) {
         return TD(null, A({'href': '{$viewview}' + r.id}, r.title));
     },
     function (r) {
         return TD(null, A({'href': '{$userview}' + r.owner}, r.ownername));
     },
     function (r,d) {
         if (r.submittedto && {$tutor} == 1) {
             return TD(null, A({'href': '', 'onclick': 'return releaseView(' + r.id + ');'}, '{$releaseviewstr}'));
         }
         return TD(null);
     }
    ]
);


viewlist.type = 'views';
viewlist.submitted = 0;
viewlist.id = $id;
viewlist.statevars.push('type');
viewlist.statevars.push('id');
viewlist.statevars.push('submitted');
viewlist.updateOnLoad();

memberlist = new TableRenderer(
    'memberlist',
    'view.json.php',
    [
     function (r) {
         return TD(null, A({'href': '{$userview}' + r.id}, r.displayname));
     },
EOF;
if ($canupdate) {
    $javascript .= <<<EOF
    'reason',
     function (r) {
         var options = new Array();
         var member = OPTION({'value': 'member'}, '{$memberstr}');
         if (r.request != 1) {
             member.selected = true;
         }
         options.push(member);
         if (r.request) {
             var nonmember = OPTION({'value': 'declinerequest'}, '{$declinestr}');
             nonmember.selected = true;
             options.push(nonmember);
         }
EOF;
    if ($canpromote) {
    $javascript .= <<<EOF
         var tutor = OPTION({'value': 'tutor'}, '{$tutorstr}');
         if (r.tutor == 1) {
             member.selected = false;
             tutor.selected = true;
         }
         options.push(tutor);
EOF;
    }
    if ($canremove) {
        $javascript .= <<<EOF
        if (!r.request) {
            var remove = OPTION({'value': 'remove'}, '{$removestr}');
            options.push(remove);
        }
EOF;
    }
    $javascript .= <<<EOF

         return TD(null, SELECT({'name': 'member-' + r.id, 'class': 'member'}, options));
     }
EOF;
}
$javascript .= <<<EOF
    ]
);
memberlist.id = $id;
memberlist.type='members';
memberlist.pending = 0;
memberlist.statevars.push('type');
memberlist.statevars.push('pending');
memberlist.statevars.push('id');
memberlist.updateOnLoad();

addLoadEvent(function () { hideElement($('pendingreasonheader')); });

function switchPending(force) {
    if (force) {
        pending = force;
        var theOption = filter(
            function (o) { if ( o.value == pending ) return true; return false; },
            $('pendingselect').options
        );
        theOption[0].selected = true;
    } 
    else {
        var pending = $('pendingselect').options[$('pendingselect').selectedIndex].value;
    }
    if (pending == 0) {
        hideElement($('pendingreasonheader'));
    }
    else {
        showElement($('pendingreasonheader'));
    }
    memberlist.pending = pending;
    memberlist.doupdate();
}

function releaseView(id) {
    var pd = {'type': 'release', 'id': '{$group->id}', 'view': id};
    sendjsonrequest('view.json.php', pd, 'GET', function (data) {
        viewlist.doupdate();
    });
    return false;
}

function updateMembership() {
    var pd = {'type': 'membercontrol', 'id': '{$group->id}'};
    var e = getElementsByTagAndClassName(null, 'member');
    for (s in e) {
        pd[e[s].name] = e[s].options[e[s].selectedIndex].value;
    }
    sendjsonrequest('view.json.php', pd, 'GET', function (data) {
        if (memberlist.pending == 1) {
            memberlist.offset = 0;
        }
        memberlist.doupdate();
    });
}
EOF;

}// end of membership only javascript (tablerenderers etc)
$javascript .= <<<EOF

function joinRequestControl() {
    var form = P({'id': 'joinrequestextras'},
                 '{$reasonstr}: ', 
                 FORM({'method': 'post', 'action': '{$commview}'}, 
                      INPUT({'type': 'hidden', 'name': 'id', 'value': {$id}}),
                      INPUT({'type': 'hidden', 'name': 'joincontrol', 'value': 'request'}),
                      INPUT({'type': 'text', 'name': 'reason'}),
                      ' ',
                      INPUT({'type': 'submit', 'class': 'submit', 'value': '{$requeststr}'})));
    insertSiblingNodesAfter('joinrequest', form);
    return false;
}

EOF;

if (!empty($pending) && $canupdate && $request) {
    $javascript .= <<<EOF
addLoadEvent(function () { switchPending(1) });
EOF;
}

$interactiontypes = array_flip(
    array_map(
        create_function('$a', 'return $a->name;'),
        plugins_installed('interaction')
    )
);

if (!$interactions = get_records_select_array('interaction_instance', 
    '"group" = ? AND deleted = ?', array($id, 0), 
    'plugin, ctime', 'id, plugin, title')) {
    $interactions = array();
}

foreach ($interactions as $i) {
    if (!is_array($interactiontypes[$i->plugin])) {
        $interactiontypes[$i->plugin] = array();
    }
    $interactiontypes[$i->plugin][] = $i;
}

// Sort them according to how the plugin wants them sorted
if ($interactiontypes) {
    foreach ($interactiontypes as $plugin => &$interactions) {
        safe_require('interaction', $plugin);
        $classname = generate_class_name('interaction', $plugin);
        if (method_exists($classname, 'sideblock_sort')) {
            $interactions = call_static_method($classname, 'sideblock_sort', $interactions);
        }
    }
}

// Add a sideblock for group interactions
$sideblock = array(
    'name' => 'groupinteractions',
    'weight' => -5,
    'data' => $interactiontypes,
);

$smarty = smarty(array('tablerenderer'), array(), array(), array('sideblocks' => array($sideblock)));

$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('member', $membership);
$smarty->assign('tutor', $tutor);
$smarty->assign('controlled', $controlled);
$smarty->assign('request', $request);
$smarty->assign('canjoin', $canjoin);
$smarty->assign('canrequestjoin', $canrequestjoin);
$smarty->assign('canleave', $canleave);
$smarty->assign('canpromote', $canpromote);
$smarty->assign('canupdate', $canupdate);
$smarty->assign('canacceptinvite', $invited);
$smarty->assign('group', $group);
$smarty->assign('hasmembers', group_has_members($group->id));
$smarty->display('group/view.tpl');


?>
