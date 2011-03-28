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
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['addpostsuccess'] = 'Added post successfully';
$string['addtitle'] = 'Add forum';
$string['addtopic'] = 'Add topic';
$string['addtopicsuccess'] = 'Added topic successfully';
$string['autosubscribeusers'] = 'Automatically subscribe users?';
$string['autosubscribeusersdescription'] = 'Choose whether group users will automatically be subscribed to this forum';
$string['Body'] = 'Body';
$string['cantaddposttoforum'] = 'You are not allowed to post in this forum';
$string['cantaddposttotopic'] = 'You are not allowed to post in this topic';
$string['cantaddtopic'] = 'You are not allowed to add topics to this forum';
$string['cantdeletepost'] = 'You are not allowed to delete posts in this forum';
$string['cantdeletethispost'] = 'You are not allowed to delete this post';
$string['cantdeletetopic'] = 'You are not allowed to delete topics in this forum';
$string['canteditpost'] = 'You are not allowed to edit this post';
$string['cantedittopic'] = 'You are not allowed to edit this topic';
$string['cantfindforum'] = 'Coudn\'t find forum with id %s';
$string['cantfindpost'] = 'Couldn\'t find post with id %s';
$string['cantfindtopic'] = 'Couldn\'t find topic with id %s';
$string['cantviewforums'] = 'You are not allowed to view forums in this group';
$string['cantviewtopic'] = 'You are not allowed to view topics in this forum';
$string['chooseanaction'] = 'Choose an action';
$string['clicksetsubject'] = 'Click to set a subject';
$string['Closed'] = 'Closed';
$string['Close'] = 'Close';
$string['closeddescription'] = 'Closed topics can only be replied to by moderators and the group administrators';
$string['Count'] = 'Count';
$string['createtopicusersdescription'] = 'If set to "All group members", anyone can create new topics and reply to existing topics.  If set to "Moderators and group admins", only moderators and group administrators can start new topics, but once topics exist, all users can post replies to them.';
$string['currentmoderators'] = 'Current Moderators';
$string['defaultforumtitle'] = 'General Discussion';
$string['defaultforumdescription'] = '%s general discussion forum';
$string['deleteforum'] = 'Delete forum';
$string['deletepost'] = 'Delete post';
$string['deletepostsuccess'] = 'Post deleted successfully';
$string['deletepostsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['deletetopic'] = 'Delete topic';
$string['deletetopicvariable'] = 'Delete topic \'%s\'';
$string['deletetopicsuccess'] = 'Topic deleted successfully';
$string['deletetopicsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['editpost'] = 'Edit post';
$string['editpostsuccess'] = 'Post edited successfully';
$string['editstothispost'] = 'Edits to this post:';
$string['edittitle'] = 'Edit forum';
$string['edittopic'] = 'Edit topic';
$string['edittopicsuccess'] = 'Topic edited successfully';
$string['forumname'] = 'Forum Name';
$string['forumposthtmltemplate'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>%s by %s</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">Reply to this post online</a></p>
<p><a href=\"%s\">Unsubscribe from this %s</a></p>
</div>";
$string['forumposttemplate'] = "%s by %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see and reply to the post online, follow this link:
%s

To unsubscribe from this %s, visit:
%s";
$string['forumsuccessfulsubscribe'] = 'Forum subscribed successfully';
$string['forumsuccessfulunsubscribe'] = 'Forum unsubscribed successfully';
$string['gotoforums'] = 'Go to forums';
$string['groupadmins'] = 'Group administrators';
$string['groupadminlist'] = 'Group admins:';
$string['Key'] = 'Key';
$string['lastpost'] = 'Last post';
$string['latestforumposts'] = 'Latest Forum Posts';
$string['Moderators'] = 'Moderators';
$string['moderatorsandgroupadminsonly'] = 'Moderators and group admins only';
$string['moderatorslist'] = 'Moderators:';
$string['moderatorsdescription'] = 'Moderators can edit and delete topics and posts. They can also open, close, set and unset topics as sticky';
$string['name'] = 'Forum';
$string['nameplural'] = 'Forums';
$string['newforum'] = 'New forum';
$string['newforumpostnotificationsubject'] = '%s: %s: %s';
$string['newpost'] = 'New post: ';
$string['newtopic'] = 'New topic';
$string['noforumpostsyet'] = 'There are no posts in this group yet';
$string['noforums'] = 'There are no forums in this group';
$string['notopics'] = 'There are no topics in this forum';
$string['Open'] = 'Open';
$string['Order'] = 'Order';
$string['orderdescription'] = 'Choose where you want the forum to be ordered compared to the other forums';
$string['Post'] = 'Post';
$string['postaftertimeout'] = 'You have submitted your change after timeout of %s minutes. Your change has not been applied.';
$string['postbyuserwasdeleted'] = 'A post by %s was deleted';
$string['postdelay'] = 'Post Delay';
$string['postdelaydescription'] = 'The minimum time (in minutes) that must pass before a new post can be mailed out to forum subscribers.  The author of a post may make edits during this time.';
$string['postedin'] = '%s posted in %s';
$string['Poster'] = 'Poster';
$string['postreply'] = 'Post reply';
$string['Posts'] = 'Posts';
$string['allposts'] = 'All posts';
$string['postsvariable'] = 'Posts: %s';
$string['potentialmoderators'] = 'Potential Moderators';
$string['re'] ='Re: %s';
$string['regulartopics'] = 'Regular topics';
$string['Reply'] = 'Reply';
$string['replyforumpostnotificationsubject'] = 'Re: %s: %s: %s';
$string['replyto'] = 'Reply to: ';
$string['Sticky'] = 'Sticky';
$string['stickydescription'] = 'Sticky topics are at the top of every page';
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
$string['topicisclosed'] = 'This topic is closed. Only moderators and the group admins can post new replies';
$string['topicopenedsuccess'] = 'Topics opened successfully';
$string['topicstickysuccess'] = 'Topics set as sticky successfully';
$string['topicsubscribesuccess'] = 'Topics subscribed successfully';
$string['topicsuccessfulunsubscribe'] = 'Topic unsubscribed successfully';
$string['topicunstickysuccess'] = 'Topic unset as sticky successfully';
$string['topicunsubscribesuccess'] = 'Topics unsubscribed successfully';
$string['topicupdatefailed'] = 'Topics update failed';
$string['typenewpost'] = 'New forum post';
$string['Unsticky'] = 'Unsticky';
$string['Unsubscribe'] = 'Unsubscribe';
$string['unsubscribefromforum'] = 'Unsubscribe from forum';
$string['unsubscribefromtopic'] = 'Unsubscribe from topic';
$string['updateselectedtopics'] = 'Update selected topics';
$string['whocancreatetopics'] = 'Who can create topics';
$string['youcannotunsubscribeotherusers'] = 'You cannot unsubscribe other users';
$string['youarenotsubscribedtothisforum'] = 'You are not subscribed to this forum';
$string['youarenotsubscribedtothistopic'] = 'You are not subscribed to this topic';

$string['today'] = 'Today';
$string['yesterday'] = 'Yesterday';
$string['strftimerecentrelative'] = '%%v, %%k:%%M';
$string['strftimerecentfullrelative'] = '%%v, %%l:%%M %%p';

$string['indentmode'] = 'Forum Indent Mode';
$string['indentfullindent'] = 'Fully expand';
$string['indentmaxindent'] = 'Expand to max';
$string['indentflatindent'] = 'No indents';
$string['indentmodedescription'] = 'Specify how topics in this forum should be indented.';
$string['maxindent'] = 'Maximum Indent Level';
$string['maxindentdescription'] = 'Set the maximum indention level for a topic. This only applies if Indent mode has been set to Expand to max';

$string['closetopics'] = 'Close new topics';
$string['closetopicsdescription'] = 'If checked, all new topics in this forum will be closed by default.  Only moderators and group administrators can reply to closed topics.';

$string['activetopicsdescription'] = 'Recently updated topics in your groups.';

$string['timeleftnotice'] = 'You have %s minutes left to finish editing';