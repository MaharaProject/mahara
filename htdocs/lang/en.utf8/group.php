<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

// my groups
$string['groupname'] = 'Group name';
$string['groupshortname'] = 'Short name';
$string['creategroup'] = 'Create group';
$string['copygroup'] = 'Copy group "%s"';
$string['groupmemberrequests'] = 'Pending membership requests';
$string['membershiprequests'] = 'Membership requests';
$string['sendinvitation'] = 'Send invite';
$string['invitetogroupsubject'] = 'You were invited to join a group';
$string['invitetogroupmessage'] = '%s has invited you to join a group, \'%s\'. Click on the link below for more information.';
$string['inviteuserfailed'] = 'Failed to invite the user';
$string['userinvited'] = 'Invite sent';
$string['addedtogroupsubject'] = 'You were added to a group';
$string['addedtogroupmessage'] = '%s has added you to a group, \'%s\'. Click on the link below to see the group.';
$string['adduserfailed'] = 'Failed to add the user';
$string['useradded'] = 'User added';
$string['editgroup'] = 'Edit group';
$string['savegroup'] = 'Save group';
$string['groupsaved'] = 'Group saved successfully';
$string['invalidgroup'] = 'The group does not exist';
$string['canteditdontown'] = 'You cannot edit this group because you do not own it.';
$string['groupdescription'] = 'Group description';
$string['groupurl'] = 'Group homepage URL';
$string['groupurldescription'] = "The URL of your group's homepage. This field must be 3-30 characters long.";
$string['groupurltaken'] = 'That URL is already taken by another group.';

$string['Membership'] = 'Membership';
$string['Roles'] = 'Roles';
$string['Open'] = 'Open';
$string['opendescription'] = 'Users can join the group without approval from group administrators.';
$string['requestdescription'] = 'Users can send membership requests to group administrators.';
$string['Controlled'] = 'Controlled';
$string['controlleddescription'] = 'Group administrators can add users to the group without their consent, and members cannot leave the group.';
$string['membershiptype'] = 'Group membership type';
$string['membershiptype.controlled'] = 'Controlled membership';
$string['membershiptype.approve']    = 'Approved membership';
$string['membershiptype.open']       = 'Open membership';
$string['membershiptype.abbrev.controlled'] = 'Controlled';
$string['membershiptype.abbrev.approve']    = 'Normal';
$string['membershiptype.abbrev.open']       = 'Open';
$string['membershipopencontrolled']  = 'Membership cannot be both open and controlled.';
$string['membershipopenrequest']     = "Open membership groups do not accept membership requests.";
$string['requestmembership']         = 'Request membership';
$string['pendingmembers']            = 'Pending members';
$string['reason']                    = 'Reason';
$string['approve']                   = 'Approve';
$string['reject']                    = 'Reject';
$string['groupalreadyexists'] = 'A group by this name already exists.';
$string['groupshortnamealreadyexists'] = 'A group by this short name already exists.';
$string['invalidshortname'] = 'Invalid group short name.';
$string['shortnameformat'] = 'Group short names can be from 2 to 255 characters in length and contain only alphanumeric characters, ".", "-", and "_".';
$string['Created'] = 'Created';
$string['editable'] = 'Editable';
$string['editability'] = 'Editability';
$string['windowstart'] = 'Start date';
$string['windowstartdesc'] = 'Group cannot be edited by members before this date';
$string['windowend'] = 'End date';
$string['windowenddesc'] = 'Group cannot be edited by members after this date';
$string['editwindowbetween'] = 'Between %s and %s';
$string['editwindowfrom'] = 'From %s';
$string['editwindowuntil'] = 'Until %s';
$string['groupadmins'] = 'Group administrators';
$string['editroles1'] = 'Create and edit';
$string['editrolesdescription1'] = 'Roles with permission to create and edit group pages, journals and files.';
$string['allexceptmember'] = 'Everyone except ordinary members';
$string['Admin'] = 'Administrator';
$string['publiclyviewablegroup'] = 'Publicly viewable group';
$string['publiclyviewablegroupdescription1'] = 'Allow anyone online to view this group including the forums.';
$string['Type'] = 'Type';
$string['publiclyvisible'] = 'Publicly visible';
$string['Public'] = 'Public';
$string['usersautoadded'] = 'Auto-add users';
$string['usersautoaddeddescription1'] = 'Automatically add any user who joins the site to this group.';
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
$string['viewnotifydescription2'] = 'Select which group members should receive a notification when a new group page is created and when a group member shares one of their pages with the group. The group member sharing the page will not receive this notification. For very large groups it would be best to limit this to non ordinary members as it can produce a lot of notifications.';
$string['commentnotify'] = 'Comment notifications';
$string['commentnotifydescription1'] = 'Select which group members should receive a notification when comments are placed on a group page and artefacts.';
$string['allowsendnow'] = 'Send forum posts immediately';
$string['allowsendnowdescription1'] = 'Any group member can choose to send forum posts immediately. If this option is set to "Off", only group administrators, tutors and moderators can do so.';
$string['hiddengroup'] = 'Hide group';
$string['hiddengroupdescription1'] = 'Hide this group on the "Find groups" page.';
$string['hidemembers'] = 'Hide membership';
$string['hidemembersdescription'] = 'Hide the group\'s membership listing from non-members.';
$string['hidemembersfrommembers'] = 'Hide membership from members';
$string['hidemembersfrommembersdescription1'] = 'Hide the members of this group. Only group administrators can see the list of members. Administrators are still shown on the group homepage.';
$string['friendinvitations'] = 'Friend invitations';
$string['invitefriendsdescription1'] = 'Allow members to invite friends to join this group. Regardless of this setting, group administrators can always send invitations to anyone.';
$string['invitefriends'] = 'Invite friends';
$string['Recommendations'] = 'Recommendations';
$string['suggestfriendsdescription1'] = 'Allow members to send a recommendation for joining this group to their friends from a button on the group homepage.';
$string['suggesttofriends'] = 'Recommend to friends';
$string['userstosendrecommendationsto'] = 'Users who will be sent a recommendation';
$string['suggestgroupnotificationsubject'] = '%s suggested you join a group';
$string['suggestgroupnotificationmessage'] = '%s suggested that you join the group "%s" on %s';
$string['nrecommendationssent'] = array(
    0 => '1 recommendation sent',
    1 => '%d recommendations sent',
);
$string['suggestinvitefriends'] = 'You cannot enable both friend invitations and recommendations.';
$string['suggestfriendsrequesterror'] = 'You can only enable friend recommendations on open or request groups.';
$string['editwindowendbeforestart'] = 'The end date must be after the start date.';

$string['editgroupmembership'] = 'Edit group membership';
$string['editmembershipforuser'] = 'Edit membership for %s';
$string['changedgroupmembership'] = 'Group membership updated successfully.';
$string['changedgroupmembershipsubject'] = 'Your group memberships have been changed.';
$string['addedtongroupsmessage'] = array(
        0 => "%2\$s has added you to the group:\n\n%3\$s\n\n",
        1 => "%2\$s has added you to the groups:\n\n%3\$s\n\n",
);
$string['removedfromngroupsmessage'] = array(
        0 => "%2\$s has removed you from the group:\n\n%3\$s\n\n",
        1 => "%2\$s has removed you from the groups:\n\n%3\$s\n\n",
);
$string['cantremovememberfromgroup'] = "You cannot remove users from %s.";
$string['current'] = "Current";
$string['requests'] = "Requests";
$string['invites'] = "Invites";

// Used to refer to all the members of a group - NOT a "member" group role!
$string['member'] = 'member';
$string['members'] = 'members';
$string['Members'] = 'Members';
$string['nmembers'] = array(
    '1 member',
    '%s members',
);

$string['memberrequests'] = 'Membership requests';
$string['declinerequest'] = 'Decline request';
$string['submittedviews'] = 'Submitted pages';
$string['releaseview'] = 'Release page';
$string['releasecollection'] = 'Release collection';
$string['invite'] = 'Invite';
$string['remove'] = 'Remove';
$string['updatemembership'] = 'Update membership';
$string['memberchangefailed'] = 'Failed to update some membership information';
$string['memberchangesuccess'] = 'Membership status changed successfully';
$string['viewreleasedsubject'] = 'Your page "%s" has been released from %s by %s';
$string['viewreleasedmessage'] = 'Your page "%s" has been released from %s by %s.';
$string['collectionreleasedsubject'] = 'Your collection "%s" has been released from %s by %s';
$string['collectionreleasedmessage'] = 'Your collection "%s" has been released from %s by %s.';
$string['viewreleasedsuccess'] = 'Page was released successfully';
$string['viewreleasedpending'] = 'Page will be released after archiving';
$string['collectionreleasedsuccess'] = 'Collection was released successfully';
$string['collectionreleasedpending'] = 'Collection will be released after archiving';
$string['leavegroup'] = 'Leave this group';
$string['joingroup'] = 'Join this group';
$string['requestjoingroup'] = 'Request to join this group';
$string['grouphaveinvite'] = 'You have been invited to join this group.';
$string['grouphaveinvitewithrole'] = 'You have been invited to join this group with the role';
$string['groupnotinvited'] = 'You have not been invited to join this group.';
$string['groupinviteaccepted'] = 'Invite accepted successfully. You are now a group member.';
$string['groupinvitedeclined'] = 'Invite declined successfully.';
$string['acceptinvitegroup'] = 'Accept';
$string['declineinvitegroup'] = 'Decline';
$string['leftgroup'] = 'You have now left this group.';
$string['leftgroupfailed'] = 'Leaving group failed';
$string['couldnotleavegroup'] = 'You cannot leave this group.';
$string['joinedgroup'] = 'You are now a group member.';
$string['couldnotjoingroup'] = 'You cannot join this group.';
$string['membershipcontrolled'] = 'Membership of this group is controlled.';
$string['membershipbyinvitationonly'] = 'Membership to this group is by invitation only.';
$string['grouprequestsent'] = 'Group membership request sent';
$string['couldnotrequestgroup'] = 'Could not send group membership request';
$string['cannotrequestjoingroup'] ='You cannot request to join this group.';
$string['grouprequestsubject'] = 'New group membership request';
$string['grouprequestmessage'] = '%s would like to join your group %s.';
$string['grouprequestmessagereason'] = "%s would like to join your group %s. Their reason for wanting to join is:\n\n%s";
$string['cantdeletegroup'] = 'You cannot delete this group.';
$string['groupconfirmdelete'] = "This will delete all pages, files and forums contained within the group. Are you sure you wish to fully delete this group and all its content?";
$string['deletegroup'] = 'Group deleted successfully';
$string['deletegroup1'] = 'Delete group';
$string['allmygroups'] = 'All my groups';
$string['groupsimin']  = 'Groups I\'m in';
$string['groupsiown']  = 'Groups I own';
$string['groupsiminvitedto'] = 'Groups I\'m invited to';
$string['groupsiwanttojoin'] = 'Groups I want to join';
$string['groupsicanjoin'] = 'Groups I can join';
$string['requestedtojoin'] = 'You have requested to join this group';
$string['groupnotfound'] = 'Group with id %s not found';
$string['groupnotfoundname'] = 'Group %s not found';
$string['groupconfirmleave'] = 'Are you sure you want to leave this group?';
$string['cantleavegroup'] = 'You cannot leave this group.';
$string['usercantleavegroup'] = 'This user cannot leave this group.';
$string['usercannotchangetothisrole'] = 'The user cannot change to this role.';
$string['leavespecifiedgroup'] = 'Leave group \'%s\'';
$string['memberslist'] = 'Members: ';
$string['nogroups'] = 'No groups';
$string['deletespecifiedgroup'] = 'Delete group \'%s\'';
$string['requestjoinspecifiedgroup'] = 'Request to join group \'%s\'';
$string['youaregroupmember'] = 'You are a member of this group.';
$string['youaregroupadmin'] = 'You are an administrator in this group.';
$string['youowngroup'] = 'You own this group.';
$string['groupsnotin'] = 'Groups I\'m not in';
$string['allgroups'] = 'All groups';
$string['allgroupmembers'] = 'All group members';
$string['trysearchingforgroups'] = 'Try %ssearching for groups%s to join.';
$string['nogroupsfound'] = 'No groups found.';
$string['group'] = 'group';
$string['Group'] = 'Group';
$string['groups'] = 'groups';
$string['notamember'] = 'You are not a member of this group.';
$string['notmembermayjoin'] = 'You must join the group \'%s\' to see this page.';
$string['declinerequestsuccess'] = 'Group membership request has been declined successfully.';
$string['notpublic'] = 'This group is not public.';
$string['moregroups'] = 'More groups';
$string['deletegroupnotificationsubject'] = 'The group "%s" was deleted';
$string['deletegroupnotificationmessage'] = 'You were a member of the group %s on %s. This group has now been deleted.';

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
$string['addedtofriendslistmessage'] = '%s added you as a friend. This means that %s is on your friends list now, too. '
    . ' Click on the link below to see their profile page.';

$string['requestedfriendlistsubject'] = 'New friend request';
$string['requestedfriendlistinboxmessage'] = '%s has requested that you add them as a friend.  '
    .' You can either do this by clicking the following link or by going to your friends list page.';

$string['requestedfriendlistmessageexplanation'] = '%s has requested that you add them as a friend.'
    . ' You can either do this by clicking the following link or by going to your friends list page'
    . ' Their reason was:
    ';

$string['removefromfriendslist'] = 'Remove from friends';
$string['removefromfriends'] = 'Remove %s from friends';
$string['removedfromfriendslistsubject'] = 'Removed from friends list';
$string['removedfromfriendslistmessage'] = '%s has removed you from their friends list.';
$string['removedfromfriendslistmessagereason'] = '%s has removed you from their friends list. Their reason was: ';
$string['cantremovefriend'] = 'You cannot remove this user from your friends list.';

$string['friendshipalreadyrequested'] = 'You have requested to be added to %s\'s friends list.';
$string['friendshipalreadyrequestedowner'] = '%s has requested to be added to your friends list.';
$string['rejectfriendshipreason'] = 'Reason for rejecting request';
$string['alreadyfriends'] = 'You are already friends with %s.';

$string['friendrequestacceptedsubject'] = 'Friend request accepted';
$string['friendrequestacceptedmessage'] = '%s has accepted your friend request and they have been added to your friends list.';
$string['friendrequestrejectedsubject'] = 'Friend request rejected';
$string['friendrequestrejectedmessage'] = '%s has rejected your friend request.';
$string['friendrequestrejectedmessagereason'] = '%s has rejected your friend request. Their reason was: ';
$string['acceptfriendshiprequestfailed'] = 'Failed to accept friendship request.';
$string['addtofriendsfailed'] = 'Failed to add %s to your friends list.';

$string['allfriends']     = 'All friends';
$string['currentfriends'] = 'Current friends';
$string['pendingfriends'] = 'Pending friends';
$string['backtofriendslist'] = 'Back to friends list';
$string['findnewfriends'] = 'Find new friends';
$string['Collections']    = 'Collections';
$string['Views']          = 'Pages';
$string['Files']          = 'Files';
$string['noviewstosee']   = 'None that you can see';
$string['whymakemeyourfriend'] = 'This is why you should make me your friend:';
$string['approverequest'] = 'Approve request';
$string['denyrequest']    = 'Deny request';
$string['pending']        = 'pending';
$string['pendingsince']   = 'pending since %s';
$string['trysearchingforfriends'] = 'Try %ssearching for new friends%s to grow your network.';
$string['nobodyawaitsfriendapproval'] = 'Nobody is awaiting your approval to become your friend.';
$string['sendfriendrequest'] = 'Send friend request';
$string['addtomyfriends'] = 'Add to my friends';
$string['friendshiprequested'] = 'Friendship requested';
$string['existingfriend'] = 'existing friend';
$string['nosearchresultsfound'] = 'No search results found';
$string['friend'] = 'friend';
$string['friends'] = 'friends';
$string['user'] = 'user';
$string['users'] = 'users';
$string['Friends'] = 'Friends';
$string['Everyone'] = 'Everyone';
$string['myinstitutions'] = 'My institutions';

$string['friendlistfailure'] = 'Failed to modify your friends list';
$string['userdoesntwantfriends'] = 'This user does not want any new friends.';
$string['cannotrequestfriendshipwithself'] = 'You cannot request friendship with yourself.';
$string['cantrequestfriendship'] = 'You cannot request friendship with this user.';

// Messaging between users
$string['messagebody'] = 'Send message'; // wtf
$string['sendmessage'] = 'Send message';
$string['messagesent'] = 'Message sent';
$string['messagenotsent'] = 'Failed to send message';
$string['newusermessage'] = 'New message from %s';
$string['newusermessageemailbody'] = '%s has sent you a message. To view this message, visit

%s';
$string['sendmessageto'] = 'Send message to %s';
$string['viewmessage'] = 'View message';
$string['Reply'] = 'Reply';

$string['denyfriendrequest'] = 'Deny friend request';
$string['sendfriendshiprequest'] = 'Send %s a friendship request';
$string['cantdenyrequest'] = 'That is not a valid friendship request.';
$string['cantmessageuser'] = 'You cannot send this user a message.';
$string['cantmessageuserdeleted'] = 'You cannot send this user a message because the account has been deleted.';
$string['cantviewmessage'] = 'You cannot view this message.';
$string['requestedfriendship'] = 'requested friendship';
$string['notinanygroups'] = 'Not in any groups';
$string['addusertogroup'] = 'Add to ';
$string['inviteusertojoingroup'] = 'Invite to ';
$string['invitemembertogroup'] = 'Invite %s to join \'%s\'';
$string['cannotinvitetogroup'] = 'You cannot invite this user to this group.';
$string['useralreadyinvitedtogroup'] = 'This user has already been invited to, or is already a member of, this group.';
$string['removefriend'] = 'Remove friend';
$string['denyfriendrequestlower'] = 'Deny friend request';

// Group interactions (activities)
$string['groupinteractions'] = 'Group activities';
$string['nointeractions'] = 'There are no activities in this group.';
$string['notallowedtoeditinteractions'] = 'You are not allowed to add or edit activities in this group.';
$string['notallowedtodeleteinteractions'] = 'You are not allowed to delete activities in this group.';
$string['interactionsaved'] = '%s saved successfully';
$string['deleteinteraction'] = 'Delete %s \'%s\'';
$string['deleteinteractionsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['interactiondeleted'] = '%s deleted successfully';
$string['addnewinteraction'] = 'Add new %s';
$string['title'] = 'Title';
$string['Role'] = 'Role';
$string['changerole'] = 'Change role';
$string['changeroleofuseringroup'] = 'Change role of %s in %s';
$string['changerolepermissions'] = 'Change %s role for %s';
$string['currentrole'] = 'Current role';
$string['changerolefromto'] = 'Change role from %s to';
$string['rolechanged'] = 'Role changed';
$string['removefromgroup'] = 'Remove from group';
$string['userremoved'] = 'User removed';
$string['About'] = 'About';
$string['aboutgroup'] = 'About %s';

$string['Joined'] = 'Joined';

$string['invitemembersdescription'] = 'You can invite users to join this group through their profile pages or <a href="%s">send multiple invitations at once</a>.';
$string['membersdescription:controlled'] = 'This is a controlled membership group. You can add users directly through their profile pages or <a href="%s">add many users at once</a>.';

// View submission
$string['submit'] = 'Submit';
$string['allowssubmissions'] = 'Allows submissions';
$string['allowsubmissions'] = 'Allow submissions';
$string['allowssubmissionsdescription1'] = "Members can submit pages to the group that are then locked. These pages cannot be edited until they are released by a group tutor or administrator.";
$string['allowssubmissionsdescription'] = 'Members can submit pages to the group.';
$string['allowsarchives'] = 'Allow archiving of submissions';
$string['allowsarchivesdescription'] = 'Pages / collections are archived as zipped Leap2A files during the submission release process.';

// Group reports
$string['report'] = 'Report';
$string['grouphasntcreatedanyviewsyet'] = "This group has not created any pages yet.";
$string['noviewssharedwithgroupyet'] = "There are no pages shared with this group yet.";
$string['groupsharedviewsscrolled'] = "You have scrolled past the end of the shared pages list.";
$string['groupcreatedviewsscrolled'] = "You have scrolled past the end of the group's pages list.";
$string['nnonmembers'] = array(
    '1 non-member',
    '%s non-members',
);
$string['membercommenters'] = "Members involved";
$string['extcommenters'] = "Non-members involved";
$string['groupparticipationreports'] = "Participation report";
$string['groupparticipationreportsdesc1'] = "Group administrators can access a report displaying all group and shared pages and who has commented on them.";

$string['returntogrouppages'] = "Return to group pages";
