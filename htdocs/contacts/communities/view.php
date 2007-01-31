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
define('MENUITEM', 'mycontacts');
define('SUBMENUITEM', 'mycommunities');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('mycommunities'));
require_once('community.php');

$id = param_integer('id');
$joincontrol = param_alpha('joincontrol', null);

if (!$community = get_record('community', 'id', $id)) {
    throw new CommunityNotFoundException("Couldn't find community with id $id");
}
$community->ownername = display_name(get_record('usr', 'id', $community->owner));

$membership = user_can_access_community($id);
// $membership is a bit string summing all membership types
$ismember = (bool) ($membership & COMMUNITY_MEMBERSHIP_MEMBER);

if (!empty($joincontrol)) {
    // leave, join, acceptinvite, request
    switch ($joincontrol) {
        case 'leave':
            // make sure they're a member and can leave
            if ($ismember && $community->jointype != 'controlled') {
                community_remove_member($id, $USER->get('id'));
                $SESSION->add_ok_msg(get_string('leftcommunity'));
            } 
            else {
                $SESSION->add_error_msg(get_string('couldnotleavecommunity'));
            }
            break;
        case 'join':
            if (!$ismember && $community->jointype == 'open') {
                community_add_member($id, $USER->get('id'));
                $SESSION->add_ok_msg(get_string('joinedcommunity'));
            }
            else {
                $SESSION->add_error_msg(get_string('couldnotjoincommunity'));
            }
            break;
        case 'acceptinvite':
        case 'declineinvite':
            if (!$request = get_record('community_member_invite', 'member', $USER->get('id'), 'community', $id)) {
                $SESSION->add_error_msg(get_string('communitynotinvited'));
                break;
            }
            if ($joincontrol == 'acceptinvite') {
                community_add_member($id, $USER->get('id'));
                $message = get_string('communityinviteaccepted');
            }
            else {
                $message = get_string('communityinvitedeclined');
            }
            delete_records('community_member_invite', 'member', $USER->get('id'), 'community', $id);
            $SESSION->add_ok_msg($message);
            break;
        case 'request':
            if (!$ismember && $community->jointype == 'request' 
                && !record_exists('community_member_request', 'community', $id, 'member', $USER->get('id'))) {
                $cmr = new StdClass;
                $cmr->reason = param_variable('reason', null);
                $cmr->community = $id;
                $cmr->member = $USER->get('id');
                $cmr->ctime = db_format_timestamp(time());
                $owner = get_record('usr', 'id', $community->owner);
                insert_record('community_member_request', $cmr);
                if (empty($cmr->reason)) {
                    $message = get_string('communityrequestmessage', 'mahara', 
                                          display_name($USER, $owner), $community->name);
                } 
                else {
                    $message = get_string('communityrequestmessagereason', 'mahara', 
                                          display_name($USER, $owner), $community->name, $cmr->reason);
                }
                require_once('activity.php');
                activity_occurred('maharamessage', 
                    array('users'   => array($community->owner), 
                          'subject' => get_string('communityrequestsubject'),
                          'message' => $message,
                          'url'     => get_config('wwwroot') . 'contacts/communities/view.php?id=' . $id));
                $SESSION->add_ok_msg(get_string('communityrequestsent'));
            }
            else {
                $SESSION->add_error_msg(get_string('couldnotrequestcommunity'));
            }
            break;
    }
    // redirect, stuff will have changed
    redirect('/contacts/communities/view.php?id=' . $id);
    exit;
}

$invited   = get_record('community_member_invite', 'community', $id, 'member', $USER->get('id'));
$requested = get_record('community_member_request', 'community', $id, 'member', $USER->get('id'));

$userview = get_config('wwwroot') . 'user/view.php?id=';
$viewview = get_config('wwwroot') . 'view/view.php?view=';
$commview = get_config('wwwroot') . 'contacts/communities/view.php';

// strings that are used in the js
$releaseviewstr  = get_string('releaseview');
$tutorstr        = get_string('tutor');
$memberstr       = get_string('member');
$removestr       = get_string('remove');
$declinestr      = get_string('declinerequest');
$updatefailedstr = get_string('updatefailed');
$requeststr      = get_string('sendrequest');
$reasonstr       = get_string('reason');
$removefromwatchliststr = get_string('removefromwatchlist', 'activity');
$addtowatchliststr = get_string('addtowatchlist', 'activity');

// all the permissions stuff
$tutor          = (int)($membership && ($membership != COMMUNITY_MEMBERSHIP_MEMBER));
$controlled     = (int)($community->jointype == 'controlled');
$admin          = (int)($membership & COMMUNITY_MEMBERSHIP_ADMIN != 0);
$canremove      = (int)(($tutor && $controlled) || $admin);
$canpromote     = (int)$tutor;
$canleave       = ($ismember && $community->jointype != 'controlled');
$canrequestjoin = (!$ismember && empty($invited) && empty($requested) && $community->jointype == 'request');
$canjoin        = (!$ismember && $community->jointype == 'open');

$javascript = '';
if ($membership) {
    $javascript .= <<<EOF
viewlist = new TableRenderer(
    'community_viewlist',
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
if ($tutor) {
    $javascript .= <<<EOF
    'reason',
     function (r) {
         var options = new Array();
         var tutor = OPTION({'value': 'tutor'}, '{$tutorstr}');
         var member = OPTION({'value': 'member'}, '{$memberstr}');
         if (r.tutor == 1) {
             tutor.selected = true;
         }
         else if (r.request != 1) {
             member.selected = true;
         }
         options.push(tutor);
         options.push(member);
         if (r.request) {
             var nonmember = OPTION({'value': 'declinerequest'}, '{$declinestr}');
             nonmember.selected = true;
             options.push(nonmember);
         }
EOF;
    if (($controlled && $tutor) || $admin) {
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
memberlist.type = 'members';
memberlist.id = $id;
memberlist.pending = 0;
memberlist.statevars.push('type');
memberlist.statevars.push('pending');
memberlist.statevars.push('id');
memberlist.updateOnLoad();

addLoadEvent(function () { hideElement($('pendingreasonheader')); });

function switchPending() {
    var pending = $('pendingselect').options[$('pendingselect').selectedIndex].value;
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
    var pd = {'type': 'release', 'id': '{$community->id}', 'view': id};
    sendjsonrequest('view.json.php', pd, 'GET', function (data) {
        viewlist.doupdate();
    });
    return false;
}

function toggleWatchlist() {
    var pd = {'type': 'watchlist', 'id': '{$community->id}'};
    var remove = '{$removefromwatchliststr}';
    var add = '{$addtowatchliststr}';
    sendjsonrequest('view.json.php', pd, 'GET', function (data) {
        if (data.member) {
            $('watchlistcontrolbutton').innerHTML = remove;
        }
        else {
            $('watchlistcontrolbutton').innerHTML = add;
        }

    });
    return false;
}

function updateMembership() {
    var pd = {'type': 'membercontrol', 'id': '{$community->id}'};
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

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('member', $membership);
$smarty->assign('tutor', $tutor);
$smarty->assign('controlled', $controlled);
$smarty->assign('canjoin', $canjoin);
$smarty->assign('canrequestjoin', $canrequestjoin);
$smarty->assign('canleave', $canleave);
$smarty->assign('canacceptinvite', $invited);
$smarty->assign('community', $community);
$smarty->assign('onwatchlist', record_exists('usr_watchlist_community', 'usr', $USER->get('id'), 'community', $community->id));
$smarty->display('contacts/communities/view.tpl');


?>
