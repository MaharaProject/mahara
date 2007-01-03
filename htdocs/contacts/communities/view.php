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
require_once('community.php');

$id = param_integer('id');

if (!$community = get_record('community', 'id', $id)) {
    throw new CommunityNotFoundException("Couldn't find community with id $id");
}
$community->ownername = display_name(get_record('usr', 'id', $community->owner));

$membership = user_can_access_community($id);

$invited = get_record('community_member_invite', 'community', $id, 'member', $USER->get('id'));
$requested = get_record('community_member_request', 'community', $id, 'member', $USER->get('id'));


$userview = get_config('wwwroot') . 'user/view.php?id=';
$viewview = get_config('wwwroot') . 'view/view.php?id=';

$releaseviewstr = get_string('releaseview');
$tutorstr = get_string('tutor');
$memberstr = get_string('member');
$removestr = get_string('remove');
$updatefailedstr = get_string('updatefailed');

$tutor = ($membership < COMMUNITY_MEMBERSHIP_MEMBER);
$controlled = ($community->jointype == 'controlled');
$admin = ($membership == COMMUNITY_MEMBERSHIP_ADMIN);
$canremove = (int)(($tutor && $controlled) || $admin);
$canpromote = (int)$tutor;

$javascript = <<<EOF
viewlist = new TableRenderer(
    'viewlist',
    'view.json.php',
    [
     function (r) {
         return TD(null, A({'href': '{$viewview}' + r.id}, r.title));
     },
     function (r) {
         return TD(null, A({'href': '{$userview}' + r.owner}, r.ownername));
     },
     function (r,d) {
         if (r.submittedto && {$tutor}) {
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

     function (r) {
         var options = new Array();
         var tutor = OPTION({'value': 'tutor'}, '{$tutorstr}');
         var member = OPTION({'value': 'member'}, '{$memberstr}');
         if (r.tutor == 1) {
             tutor.selected = true;
         }
         else {
             member.selected = true;
         }
         options.push(tutor);
         options.push(member);
EOF;
    if (($controlled && $tutor) || $admin) {
        $javascript .= <<<EOF
        var remove = OPTION({'value': 'nonmember'}, '{$removestr}');
        options.push(remove);
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

function memberControl(id, type) {
    return false;
}

function releaseView(id) {
    var pd = {'type': 'release', 'id': '{$community->id}', 'view': id};
    var d = loadJSONDoc('view.json.php', pd);
    d.addCallbacks(function (data) {
        $('messagediv').innerHTML = data.message;
        viewlist.doupdate();
    },
    function () {
        $('messagediv').innerHTML = '{$updatefailedstr}';
    });
    return false;
}

function updateMembership() {
    var pd = {'type': 'membercontrol', 'id': '{$community->id}'};
    var e = getElementsByTagAndClassName(null, 'member');
    for (s in e) {
        pd[e[s].name] = e[s].options[e[s].selectedIndex].value;
    }
    var d = loadJSONDoc('view.json.php', pd);
    d.addCallbacks(function (data) {
        $('messagediv').innerHTML = data.message;
        memberlist.doupdate();
    },
    function () {
        $('messagediv').innerHTML = '{$updatefailedstr}';
    });
}
EOF;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('member', $membership);
$smarty->assign('tutor', $tutor);
$smarty->assign('canjoin', empty($membership) && $community->joinmode == 'open');
$smarty->assign('canrequestjoin', empty($membership) && empty($invited) && empty($requested) && $community->joinmode == 'request');
$smarty->assign('canleave', $membership == COMMUNITY_MEMBERSHIP_MEMBER && $community->jointype != 'controlled');
$smarty->assign('canacceptinvite', $invited);
$smarty->assign('community', $community);
$smarty->display('contacts/communities/view.tpl');


?>