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
 * @subpackage lang
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

// my groups
$string['groupname'] = 'Group Name';
$string['creategroup'] = 'Create Group';
$string['groupmemberrequests'] = 'Pending membership requests';
$string['membershiprequests'] = 'Membership requests';
$string['sendinvitation'] = 'Send invite';
$string['invitetogroupsubject'] = 'You were invited to join a group';
$string['invitetogroupmessage'] = '%s has invited you to join a group, \'%s\'.  Click on the link below for more information.';
$string['inviteuserfailed'] = 'Failed to invite the user';
$string['userinvited'] = 'Invite sent';
$string['addedtogroupsubject'] = 'You were added to a group';
$string['addedtogroupmessage'] = '%s has added you to a group, \'%s\'.  Click on the link below to see the group';
$string['adduserfailed'] = 'Failed to add the user';
$string['useradded'] = 'User added';
$string['editgroup'] = 'Edit Group';
$string['savegroup'] = 'Save Group';
$string['groupsaved'] = 'Group Saved Successfully';
$string['invalidgroup'] = 'The group doesn\'t exist';
$string['canteditdontown'] = 'You can\'t edit this group because you don\'t own it';
$string['groupdescription'] = 'Group Description';
$string['membershiptype'] = 'Group Membership Type';
$string['membershiptype.controlled'] = 'Controlled Membership';
$string['membershiptype.invite']     = 'Invite Only';
$string['membershiptype.request']    = 'Request Membership';
$string['membershiptype.open']       = 'Open Membership';
$string['membershiptype.abbrev.controlled'] = 'Controlled';
$string['membershiptype.abbrev.invite']     = 'Invite';
$string['membershiptype.abbrev.request']    = 'Request';
$string['membershiptype.abbrev.open']       = 'Open';
$string['pendingmembers']            = 'Pending Members';
$string['reason']                    = 'Reason';
$string['approve']                   = 'Approve';
$string['reject']                    = 'Reject';
$string['groupalreadyexists'] = 'A Group by this name already exists';
$string['Created'] = 'Created';
$string['groupadmins'] = 'Group admins';
$string['Admin'] = 'Admin';
$string['grouptype'] = 'Group type';
$string['publiclyviewablegroup'] = 'Publicly Viewable Group?';
$string['publiclyviewablegroupdescription'] = 'Allow people who are not logged-in to view this group, including the forums?';
$string['Type'] = 'Type';
$string['publiclyvisible'] = 'Publicly visible';
$string['Public'] = 'Public';
$string['usersautoadded'] = 'Users auto-added?';
$string['usersautoaddeddescription'] = 'Automatically put all new users into this group?';
$string['groupcategory'] = 'Group category';
$string['allcategories'] = 'All categories';
$string['groupoptionsset'] = 'Group options have been updated.';
$string['nocategoryselected'] = 'No category selected';
$string['categoryunassigned'] = 'Category unassigned';
$string['hasrequestedmembership'] = 'has requested membership of this group';
$string['hasbeeninvitedtojoin'] = 'has been invited to join this group';
$string['groupinvitesfrom'] = 'Invited to join:';
$string['requestedmembershipin'] = 'Requested membership in:';
$string['viewnotify'] = 'Shared page notifications';
$string['viewnotifydescription'] = 'If checked, a notification will be sent to every group member whenever a member shares one of their pages with the group.  Enabling this setting in very large groups can produce a lot of notifications.';

$string['editgroupmembership'] = 'Edit group membership';
$string['editmembershipforuser'] = 'Edit membership for %s';
$string['changedgroupmembership'] = 'Group membership updated sucessfully.';
$string['changedgroupmembershipsubject'] = 'Your group memberships have been changed';
$string['addedtogroupsmessage'] = "%s has added you to the group(s):\n\n%s\n\n";
$string['removedfromgroupsmessage'] = "%s has removed you from the group(s):\n\n%s\n\n";
$string['cantremoveuserisadmin'] = "Tutor cannot remove admins and other tutorsmembers.";
$string['cantremovemember'] = "Tutor cannot remove members.";
$string['current'] = "Current";
$string['requests'] = "Requests";
$string['invites'] = "Invites";

// Used to refer to all the members of a group - NOT a "member" group role!
$string['member'] = 'member';
$string['members'] = 'members';
$string['Members'] = 'Members';

$string['memberrequests'] = 'Membership requests';
$string['declinerequest'] = 'Decline request';
$string['submittedviews'] = 'Submitted pages';
$string['releaseview'] = 'Release page';
$string['invite'] = 'Invite';
$string['remove'] = 'Remove';
$string['updatemembership'] = 'Update membership';
$string['memberchangefailed'] = 'Failed to update some membership information';
$string['memberchangesuccess'] = 'Membership status changed successfully';
$string['viewreleasedsubject'] = 'Your page "%s" has been released from %s by %s';
$string['viewreleasedmessage'] = 'Your page "%s" has been released from %s by %s';
$string['viewreleasedsuccess'] = 'Page was released successfully';
$string['groupmembershipchangesubject'] = 'Group membership: %s';
$string['groupmembershipchangedmessagetutor'] = 'You have been promoted to a tutor in this group';
$string['groupmembershipchangedmessagemember'] = 'You have been demoted from a tutor in this group';
$string['groupmembershipchangedmessageremove'] = 'You have been removed from this group';
$string['groupmembershipchangedmessagedeclinerequest'] = 'Your request to join this group has been declined';
$string['groupmembershipchangedmessageaddedtutor'] = 'You have been added as a tutor in this group';
$string['groupmembershipchangedmessageaddedmember'] = 'You have been added as a member in this group';
$string['leavegroup'] = 'Leave this group';
$string['joingroup'] = 'Join this group';
$string['requestjoingroup'] = 'Request to join this group';
$string['grouphaveinvite'] = 'You have been invited to join this group';
$string['grouphaveinvitewithrole'] = 'You have been invited to join this group with the role';
$string['groupnotinvited'] = 'You have not been invited to join this group';
$string['groupinviteaccepted'] = 'Invite accepted successfully! You are now a group member';
$string['groupinvitedeclined'] = 'Invite declined successfully!';
$string['acceptinvitegroup'] = 'Accept';
$string['declineinvitegroup'] = 'Decline';
$string['leftgroup'] = 'You have now left this group';
$string['leftgroupfailed'] = 'Leaving group failed';
$string['couldnotleavegroup'] = 'You cannot leave this group';
$string['joinedgroup'] = 'You are now a group member';
$string['couldnotjoingroup'] = 'You cannot join this group';
$string['grouprequestsent'] = 'Group membership request sent';
$string['couldnotrequestgroup'] = 'Could not send group membership request';
$string['cannotrequestjoingroup'] ='You cannot request to join this group';
$string['groupjointypeopen'] = 'Membership to this group is open. Feel free to join!';
$string['groupjointypecontrolled'] = 'Membership to this group  is controlled. You cannot join this group.';
$string['groupjointypeinvite'] = 'Membership to this group is by invitation only.';
$string['groupjointyperequest'] = 'Membership to this group is by request only.';
$string['grouprequestsubject'] = 'New group membership request';
$string['grouprequestmessage'] = '%s would like to join your group %s';
$string['grouprequestmessagereason'] = "%s would like to join your group %s. Their reason for wanting to join is:\n\n%s";
$string['cantdeletegroup'] = 'You cannot delete this group';
$string['groupconfirmdelete'] = "This will delete all pages, files, and forums contained within the group.  Are you sure you wish to fully delete this group and all its content?";
$string['deletegroup'] = 'Group Deleted Successfully';
$string['deletegroup1'] = 'Delete Group';
$string['allmygroups'] = 'All My Groups';
$string['groupsimin']  = 'Groups I\'m In';
$string['groupsiown']  = 'Groups I Own';
$string['groupsiminvitedto'] = 'Groups I\'m Invited To';
$string['groupsiwanttojoin'] = 'Groups I Want To Join';
$string['requestedtojoin'] = 'You have requested to join this group';
$string['groupnotfound'] = 'Group with id %s not found';
$string['groupconfirmleave'] = 'Are you sure you want to leave this group?';
$string['cantleavegroup'] = 'You can\'t leave this group';
$string['usercantleavegroup'] = 'This user cannot leave this group';
$string['usercannotchangetothisrole'] = 'The user cannot change to this role';
$string['leavespecifiedgroup'] = 'Leave group \'%s\'';
$string['memberslist'] = 'Members: ';
$string['nogroups'] = 'No groups';
$string['deletespecifiedgroup'] = 'Delete group \'%s\'';
$string['requestjoinspecifiedgroup'] = 'Request to join group \'%s\'';
$string['youaregroupmember'] = 'You are a member of this group';
$string['youaregrouptutor'] = 'You are a tutor in this group';
$string['youaregroupadmin'] = 'You are an admin in this group';
$string['youowngroup'] = 'You own this group';
$string['groupsnotin'] = 'Groups I\'m not in';
$string['allgroups'] = 'All groups';
$string['allgroupmembers'] = 'All group members';
$string['trysearchingforgroups'] = 'Try %ssearching for groups%s to join!';
$string['nogroupsfound'] = 'No groups found.';
$string['group'] = 'group';
$string['Group'] = 'Group';
$string['groups'] = 'groups';
$string['notamember'] = 'You are not a member of this group';
$string['notmembermayjoin'] = 'You must join the group \'%s\' to see this page.';
$string['declinerequestsuccess'] = 'Group membership request has been declined sucessfully.';
$string['notpublic'] = 'This group is not public.';
$string['moregroups'] = 'More groups';

// Bulk add, invite
$string['addmembers'] = 'Add members';
$string['invitationssent'] = '%d invitations sent';
$string['newmembersadded'] = 'Added %d new members';
$string['potentialmembers'] = 'Potential members';
$string['sendinvitations'] = 'Send invitations';
$string['userstobeadded'] = 'Users to be added';
$string['userstobeinvited'] = 'Users to be invited';

// friendslist
$string['reasonoptional'] = 'Reason (optional)';
$string['request'] = 'Request';

$string['friendformaddsuccess'] = 'Added %s to your friends list';
$string['friendformremovesuccess'] = 'Removed %s from your friends list';
$string['friendformrequestsuccess'] = 'Sent a friendship request to %s';
$string['friendformacceptsuccess'] = 'Accepted friend request';
$string['friendformrejectsuccess'] = 'Rejected friend request';

$string['addtofriendslist'] = 'Add to friends';
$string['requestfriendship'] = 'Request friendship';

$string['addedtofriendslistsubject'] = '%s has added you as a friend';
$string['addedtofriendslistmessage'] = '%s added you as a friend! This means that %s is also on your friend list now too. '
    . ' Click on the link below to see their profile page';

$string['requestedfriendlistsubject'] = 'New friend request';
$string['requestedfriendlistmessage'] = '%s has requested that you add them as a friend.  '
    .' You can either do this from the link below, or from your friends list page';

$string['requestedfriendlistmessagereason'] = '%s has requested that you add them as a friend.'
    . ' You can either do this from the link below, or from your friends list page.'
    . ' Their reason was:
    ';

$string['removefromfriendslist'] = 'Remove from friends';
$string['removefromfriends'] = 'Remove %s from friends';
$string['confirmremovefriend'] = 'Are you sure you want to remove this user from your friends list?';
$string['removedfromfriendslistsubject'] = 'Removed from friends list';
$string['removedfromfriendslistmessage'] = '%s has removed you from their friends list.';
$string['removedfromfriendslistmessagereason'] = '%s has removed you from their friends list.  Their reason was: ';
$string['cantremovefriend'] = 'You cannot remove this user from your friends list';

$string['friendshipalreadyrequested'] = 'You have requested to be added to %s\'s friends list';
$string['friendshipalreadyrequestedowner'] = '%s has requested to be added to your friends list';
$string['rejectfriendshipreason'] = 'Reason for rejecting request';
$string['alreadyfriends'] = 'You are already friends with %s';

$string['friendrequestacceptedsubject'] = 'Friend request accepted';
$string['friendrequestacceptedmessage'] = '%s has accepted your friend request and they have been added to your friends list'; 
$string['friendrequestrejectedsubject'] = 'Friend request rejected';
$string['friendrequestrejectedmessage'] = '%s has rejected your friend request.';
$string['friendrequestrejectedmessagereason'] = '%s has rejected your friend request.  Their reason was: ';

$string['allfriends']     = 'All Friends';
$string['currentfriends'] = 'Current Friends';
$string['pendingfriends'] = 'Pending friends';
$string['backtofriendslist'] = 'Back to Friends List';
$string['findnewfriends'] = 'Find New Friends';
$string['Views']          = 'Pages';
$string['Files']          = 'Files';
$string['seeallviews']    = 'See all %s pages...';
$string['noviewstosee']   = 'None that you can see :(';
$string['whymakemeyourfriend'] = 'This is why you should make me your friend:';
$string['approverequest'] = 'Approve Request!';
$string['denyrequest']    = 'Deny Request';
$string['pending']        = 'pending';
$string['trysearchingforfriends'] = 'Try %ssearching for new friends%s to grow your network!';
$string['nobodyawaitsfriendapproval'] = 'Nobody is awaiting your approval to become your friend';
$string['sendfriendrequest'] = 'Send Friend Request!';
$string['addtomyfriends'] = 'Add to My Friends!';
$string['friendshiprequested'] = 'Friendship requested!';
$string['existingfriend'] = 'existing friend';
$string['nosearchresultsfound'] = 'No search results found :(';
$string['friend'] = 'friend';
$string['friends'] = 'friends';
$string['user'] = 'user';
$string['users'] = 'users';
$string['Friends'] = 'Friends';

$string['friendlistfailure'] = 'Failed to modify your friends list';
$string['userdoesntwantfriends'] = 'This user doesn\'t want any new friends';
$string['cannotrequestfriendshipwithself'] = 'You cannot request a friendship with yourself';
$string['cantrequestfriendship'] = 'You cannot request friendship with this user';

// Messaging between users
$string['messagebody'] = 'Send message'; // wtf
$string['sendmessage'] = 'Send message';
$string['messagesent'] = 'Message sent!';
$string['messagenotsent'] = 'Failed to send message';
$string['newusermessage'] = 'New message from %s';
$string['newusermessageemailbody'] = '%s has sent you a message.  To view this message, visit

%s';
$string['sendmessageto'] = 'Send message to %s';
$string['viewmessage'] = 'View Message';
$string['Reply'] = 'Reply';

$string['denyfriendrequest'] = 'Deny Friend Request';
$string['sendfriendshiprequest'] = 'Send %s a friendship request';
$string['cantdenyrequest'] = 'That is not a valid friendship request';
$string['cantmessageuser'] = 'You cannot send this user a message';
$string['cantviewmessage'] = 'You cannot view this message';
$string['requestedfriendship'] = 'requested friendship';
$string['notinanygroups'] = 'Not in any groups';
$string['addusertogroup'] = 'Add to ';
$string['inviteusertojoingroup'] = 'Invite to ';
$string['invitemembertogroup'] = 'Invite %s to join \'%s\'';
$string['cannotinvitetogroup'] = 'You can\'t invite this user to this group';
$string['useralreadyinvitedtogroup'] = 'This user has already been invited to, or is already a member of, this group.';
$string['removefriend'] = 'Remove friend';
$string['denyfriendrequestlower'] = 'Deny friend request';

// Group interactions (activities)
$string['groupinteractions'] = 'Group Activities';
$string['nointeractions'] = 'There are no activities in this group';
$string['notallowedtoeditinteractions'] = 'You are not allowed to add or edit activities in this group';
$string['notallowedtodeleteinteractions'] = 'You are not allowed to delete activities in this group';
$string['interactionsaved'] = '%s saved successfully';
$string['deleteinteraction'] = 'Delete %s \'%s\'';
$string['deleteinteractionsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['interactiondeleted'] = '%s deleted successfully';
$string['addnewinteraction'] = 'Add new %s';
$string['title'] = 'Title';
$string['Role'] = 'Role';
$string['changerole'] = 'Change role';
$string['changeroleofuseringroup'] = 'Change role of %s in %s';
$string['currentrole'] = 'Current role';
$string['changerolefromto'] = 'Change role from %s to';
$string['rolechanged'] = 'Role changed';
$string['removefromgroup'] = 'Remove from group';
$string['userremoved'] = 'User removed';
$string['About'] = 'About';
$string['aboutgroup'] = 'About %s';

$string['Joined'] = 'Joined';

$string['membersdescription:invite'] = 'This is an invite-only group. You can invite users through their profile pages or <a href="%s">send multiple invitations at once</a>.';
$string['membersdescription:controlled'] = 'This is a controlled membership group. You can add users through their profile pages or <a href="%s">add many users at once</a>.';

// View submission
$string['submit'] = 'Submit';
$string['allowssubmissions'] = 'Allows submissions';
