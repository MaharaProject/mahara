<?php
/**
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['addpostsuccess'] = 'Added post successfully';
$string['addtitle'] = 'Add forum';
$string['addtopic'] = 'Add topic';
$string['addtopicsuccess'] = 'Added topic successfully';
$string['autosubscribeusers'] = 'Automatically subscribe users';
$string['autosubscribeusersdescription'] = 'Choose whether group users will automatically be subscribed to this forum';
$string['Body'] = 'Message';
$string['cantaddposttoforum'] = 'You are not allowed to post in this forum';
$string['cantaddposttotopic'] = 'You are not allowed to post in this topic';
$string['cantaddtopic'] = 'You are not allowed to add topics to this forum';
$string['cantdeletepost'] = 'You are not allowed to delete posts in this forum';
$string['cantdeletethispost'] = 'You are not allowed to delete this post';
$string['cantdeletetopic'] = 'You are not allowed to delete topics in this forum';
$string['canteditpost'] = 'You are not allowed to edit this post';
$string['cantedittopic'] = 'You are not allowed to edit this topic';
$string['cantfindforum'] = 'Could not find forum with id %s';
$string['cantfindpost'] = 'Could not find post with id %s';
$string['cantfindtopic'] = 'Could not find topic with id %s';
$string['cantmakenonobjectionable'] = 'You are not allowed to mark this post as not objectionable.';
$string['cantviewforums'] = 'You are not allowed to view forums in this group';
$string['cantviewtopic'] = 'You are not allowed to view topics in this forum';
$string['chooseanaction'] = 'Choose an action';
$string['clicksetsubject'] = 'Click to set a subject';
$string['Closed'] = 'Closed';
$string['Close'] = 'Close';
$string['closeddescription'] = 'Closed topics can only be replied to by moderators and the group administrators';
$string['complaint'] = 'Complaint';
$string['Count'] = 'Count';
$string['createtopicusersdescription'] = 'If set to "All group members", anyone can create new topics and reply to existing topics. If set to "Moderators and group administrators", only moderators and group administrators can start new topics, but once topics exist, all users can post replies to them.';
$string['currentmoderators'] = 'Current moderators';
$string['defaultforumtitle'] = 'General discussion';
$string['defaultforumdescription'] = '%s general discussion forum';
$string['deleteforum'] = 'Delete forum';
$string['deletepost'] = 'Delete post';
$string['deletepostsuccess'] = 'Post deleted successfully';
$string['deletepostsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['deletetopic'] = 'Delete topic';
$string['deletetopicspecific'] = 'Delete topic "%s"';
$string['deletetopicsuccess'] = 'Topic deleted successfully';
$string['deletetopicsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['editpost'] = 'Edit post';

$string['editpostsuccess'] = 'Post edited successfully';
$string['editstothispost'] = 'Edits to this post:';
$string['edittitle'] = 'Edit forum';
$string['edittopic'] = 'Edit topic';
$string['edittopicspecific'] = 'Edit topic "%s"';
$string['edittopicsuccess'] = 'Topic edited successfully';
$string['forumname'] = 'Forum name';
$string['forumposthtmltemplate'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>Forum: %s (%s)</strong></div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">Reply to this post online</a></p>
<p><a href=\"%s\">Unsubscribe from this %s</a></p>
</div>";
$string['forumposttemplate'] = "Forum: %s (%s)
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see and reply to the post online, follow this link:
%s

To unsubscribe from this %s, visit:
%s";
$string['forumsettings'] = 'Forum settings';
$string['forumsuccessfulsubscribe'] = 'Forum subscribed successfully';
$string['forumsuccessfulunsubscribe'] = 'Forum unsubscribed successfully';
$string['gotoforums'] = 'Go to forums';
$string['groupadmins'] = 'Group administrators';
$string['groupadminlist'] = 'Group administrators:';
$string['Key'] = 'Key';
$string['lastpost'] = 'Last post';
$string['latestforumposts'] = 'Latest forum posts';
$string['Moderators'] = 'Moderators';
$string['moderatorsandgroupadminsonly'] = 'Moderators and group administrators only';
$string['moderatorslist'] = 'Moderators:';
$string['moderatorsdescription'] = 'Moderators can edit and delete topics and posts. They can also open, close, set and unset topics as sticky.';
$string['name'] = 'Forum';
$string['nameplural'] = 'Forums';
$string['newforum'] = 'New forum';
$string['newforumpostnotificationsubjectline'] = '%s';
$string['newpost'] = 'New post: ';
$string['newtopic'] = 'New topic';
$string['noforumpostsyet'] = 'There are no posts in this group yet';
$string['noforums'] = 'There are no forums in this group';
$string['notopics'] = 'There are no topics in this forum';
$string['notifyadministrator'] = 'Notify administrator';
$string['objectionablepostdeletedsubject'] = 'Objectionable post in forum topic "%s" was deleted by %s.';
$string['objectionablepostdeletedbody'] = '%s has looked at post by %s previously reported as objectionable and deleted it.

The objectionable post content was:
%s';
$string['objectionabletopicdeletedsubject'] = 'Objectionable forum topic "%s" was deleted by %s.';
$string['objectionabletopicdeletedbody'] = '%s has looked at topic by %s previously reported as objectionable and deleted it.

The objectionable topic content was:
%s';
$string['Open'] = 'Open';
$string['Order'] = 'Order';
$string['orderdescription'] = 'Choose at which position this forum shall appear in the list of forums';
$string['Post'] = 'Post';
$string['postaftertimeout'] = 'You have submitted your change after timeout of %s minutes. Your change has not been applied.';
$string['postbyuserwasdeleted'] = 'A post by %s was deleted';
$string['postsbyuserweredeleted'] = '%s posts by %s were deleted';
$string['postdelay'] = 'Post delay';
$string['postdelaydescription'] = 'The minimum time (in minutes) that must pass before a new post can be mailed out to forum subscribers. The author of a post may make edits during this time.';
$string['postedin'] = '%s posted in %s';
$string['Poster'] = 'Poster';
$string['postobjectionable'] = 'This post has been reported by you as containing objectionable content.';
$string['postnotobjectionable'] = 'This post has been reported as containing objectionable content. If this is not the case, you can click the button to remove this notice and notify the other administrators.';
$string['postnotobjectionablebody'] = '%s has looked at post by %s and marked it as no longer containing objectionable material.';
$string['postnotobjectionablesubject'] = 'Post in forum topic "%s" was marked as not objectionable by %s.';
$string['postnotobjectionablesuccess'] = 'Post was marked as not objectionable.';
$string['postnotobjectionablesubmit'] = 'Not objectionable';
$string['postreply'] = 'Post reply';
$string['Posts'] = 'Posts';
$string['allposts'] = 'All posts';
$string['postsvariable'] = 'Posts: %s';
$string['potentialmoderators'] = 'Potential moderators';
$string['re'] ='Re: %s';
$string['regulartopics'] = 'Regular topics';
$string['Reply'] = 'Reply';
$string['replyforumpostnotificationsubjectline'] = 'Re: %s';
$string['Re:'] = 'Re: ';
$string['replyto'] = 'Reply to: ';
$string['reporteddetails'] = 'Reported details';
$string['reportedpostdetails'] = '<b>Reported by %s on %s:</b><p>%s</p>';
$string['reportobjectionablematerial'] = 'Report';
$string['reportpost'] = 'Report post';
$string['reportpostsuccess'] = 'Post reported successfully';
$string['sendnow'] = 'Send message now';
$string['sendnowdescription'] = 'Send message immediately instead of waiting at least %s minutes for it to be sent.';
$string['Sticky'] = 'Sticky';
$string['stickydescription'] = 'Sticky topics appear at the top of every page';
$string['stickytopics'] = 'Sticky topics';
$string['Subscribe'] = 'Subscribe';
$string['Subscribed'] = 'Subscribed';
$string['subscribetoforum'] = 'Subscribe to forum';
$string['subscribetotopic'] = 'Subscribe to topic';
$string['Subject'] = 'Subject';
$string['Topic'] = 'Topic';
$string['Topics'] = 'Topics';
$string['topiclower'] = 'topic';
$string['topicslower'] = 'topics';
$string['topicclosedsuccess'] = 'Topics closed successfully';
$string['topicisclosed'] = 'This topic is closed. Only moderators and the group administrators can post new replies.';
$string['topicopenedsuccess'] = 'Topics opened successfully';
$string['topicstickysuccess'] = 'Topics set as sticky successfully';
$string['topicsubscribesuccess'] = 'Topics subscribed successfully';
$string['topicsuccessfulunsubscribe'] = 'Topic unsubscribed successfully';
$string['topicunstickysuccess'] = 'Topic unset as sticky successfully';
$string['topicunsubscribesuccess'] = 'Topics unsubscribed successfully';
$string['topicupdatefailed'] = 'Topics update failed';
$string['typenewpost'] = 'New forum post';
$string['typereportpost'] = 'Objectionable content in forum';
$string['Unsticky'] = 'Unsticky';
$string['Unsubscribe'] = 'Unsubscribe';
$string['unsubscribefromforum'] = 'Unsubscribe from forum';
$string['unsubscribefromtopic'] = 'Unsubscribe from topic';
$string['updateselectedtopics'] = 'Update selected topics';
$string['whocancreatetopics'] = 'Who can create topics';
$string['youcannotunsubscribeotherusers'] = 'You cannot unsubscribe other users';
$string['youarenotsubscribedtothisforum'] = 'You are not subscribed to this forum';
$string['youarenotsubscribedtothistopic'] = 'You are not subscribed to this topic';
$string['Moveto'] = 'Move to';
$string['topicmovedsuccess'] = array(
        0 => 'Topic has been moved successfully.',
        1 => '%d topics have been moved successfully.',
);
$string['today'] = 'Today';
$string['yesterday'] = 'Yesterday';
$string['strftimerecentrelative'] = '%%v, %%k:%%M';
$string['strftimerecentfullrelative'] = '%%v, %%l:%%M %%p';

$string['indentmode'] = 'Forum indent mode';
$string['indentfullindent'] = 'Fully expand';
$string['indentmaxindent'] = 'Expand to maximum';
$string['indentflatindent'] = 'No indents';
$string['indentmodedescription'] = 'Specify how topics in this forum should be indented.';
$string['maxindent'] = 'Maximum indent level';
$string['maxindentdescription'] = 'Set the maximum indentation level for a topic. This only applies if the indent mode has been set to "Expand to maximum".';

$string['closetopics'] = 'Close new topics';
$string['closetopicsdescription1'] = 'Close all new topics by default. Only moderators and group administrators can reply to closed topics.';

$string['activetopicsdescription'] = 'Recently updated topics in your groups.';

$string['timeleftnotice'] = 'You have %s minutes left to finish editing.';

$string['objectionablecontentpost'] = 'Objectionable content on forum topic "%s" reported by %s';
$string['objectionablecontentposthtml'] = '<div style="padding: 0.5em 0; border-bottom: 1px solid #999;">Objectionable content on forum topic "%s" reported by %s
<br>%s</div>

<div style="margin: 1em 0;">%s</div>

<div style="padding: 0.5em 0; border-bottom: 1px solid #999;">The objectionable post content is:
<br>%s</div>

<div style="margin: 1em 0;">%s</div>

<div style="font-size: smaller; border-top: 1px solid #999;">
<p>Complaint relates to: <a href="%s">%s</a></p>
<p>Reported by: <a href="%s">%s</a></p>
</div>';
$string['objectionablecontentposttext'] = 'Objectionable content on forum topic "%s" reported by %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------

The objectionable post content is:
%s
------------------------------------------------------------------------

%s

-----------------------------------------------------------------------
To see the post, follow this link:
%s
To see the reporter\'s profile, follow this link:
%s';
