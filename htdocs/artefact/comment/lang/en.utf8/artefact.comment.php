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

$string['pluginname'] = 'Comment';
$string['Comment'] = 'Comment';
$string['Comments'] = 'Comments';
$string['comment'] = 'comment';
$string['comments'] = 'comments';
$string['addcomment'] = 'Add comment';
$string['Allow'] = 'Allow';
$string['allowcomments'] = 'Allow comments';
$string['approvalrequired'] = 'Comments are moderated. If you choose to make this comment public, it will not be visible to others until it is approved by the owner.';
$string['attachfile'] = "Attach file";
$string['Attachments'] = "Attachments";
$string['cantedithasreplies'] = 'You can only edit the most recent comment';
$string['canteditnotauthor'] = 'You are not the author of this comment';
$string['cantedittooold'] = 'You can only edit comments that are less than %d minutes old';
$string['commentmadepublic'] = "Comment made public";
$string['commentdeletedauthornotification'] = "Your comment on %s was deleted:\n%s";
$string['commentdeletednotificationsubject'] = 'Comment on %s deleted';
$string['commentnotinview'] = 'Comment %d not in page %d';
$string['commentremoved'] = 'Comment removed';
$string['commentremovedbyauthor'] = 'Comment removed by the author';
$string['commentremovedbyowner'] = 'Comment removed by the owner';
$string['commentremovedbyadmin'] = 'Comment removed by an administrator';
$string['commentupdated'] = 'Comment updated';
$string['editcomment'] = 'Edit comment';
$string['editcommentdescription'] = 'You can update your comments if they are less than %d minutes old and have had no newer replies added. After this time you may still be able to delete your comments and add new ones.';
$string['entriesimportedfromleapexport'] = 'Entries imported from a Leap2A export that were not able to be imported elsewhere';
$string['feedback'] = 'Feedback';
$string['feedbackattachdirname'] = 'commentfiles';
$string['feedbackattachdirdesc'] = 'Files attached to comments on your portfolio';
$string['feedbackattachmessage'] = 'The attached file(s) have been added to your %s folder';
$string['commentonviewbyuser'] = 'Comment on %s by %s';
$string['commentsubmitted'] = 'Comment submitted';
$string['commentsubmittedmoderatedanon'] = 'Comment submitted, awaiting moderation';
$string['commentsubmittedprivateanon'] = 'Private comment submitted';
$string['forcepubliccomment'] = 'Public';
$string['forceprivatecomment'] = 'Private: This reply will only be visible to you and the author of the preceeding comment.';
$string['lastcomment'] = 'Last comment';
$string['makecommentpublic'] = 'Make comment public';
$string['makepublicnotallowed'] = 'You are not allowed to make this comment public';
$string['makepublicrequestsubject'] = 'Request to change private comment to public';
$string['makepublicrequestbyauthormessage'] = '%s has requested that you make their comment public.';
$string['makepublicrequestbyownermessage'] = '%s has requested that you make your comment public.';
$string['makepublicrequestsent'] = 'A message has been sent to %s to request that the comment be made public.';
$string['groupadmins'] = 'Group administrators';
$string['messageempty'] = 'Your message is empty. Please enter a message or attach a file.';
$string['Moderate'] = 'Moderate';
$string['moderatecomments'] = 'Moderate comments';
$string['moderatecommentsdescription2'] = 'Comments on pages and artefacts remain private until you approve them. Comments by people not logged into their accounts always go into a moderation queue and need to be approved.';
$string['newcommentnotificationsubject'] = 'New comment on %s';
$string['progress_feedback'] = array(
    'Comment on another user\'s page',
    'Comment on %s other users\' pages',
);
$string['rating'] = 'Rating';
$string['reallydeletethiscomment'] = 'Are you sure you want to delete this comment?';
$string['reply'] = 'Reply';
$string['replyto'] = 'Reply to:';
$string['replytonoaccess'] = 'You are not allowed to post a reply to this comment.';
$string['replytonoprivatereplyallowed'] = 'You are not allowed to post a private reply to this comment.';
$string['replytonopublicreplyallowed'] = 'You are not allowed to post a public reply to this comment.';
$string['thiscommentisprivate'] = 'This comment is private';
$string['typefeedback'] = 'Comment';
$string['viewcomment'] = 'View comment';
$string['youhaverequestedpublic'] = 'You have requested that this comment be made public.';

$string['feedbacknotificationhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>%s commented on %s</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">Reply to this comment online</a></p>
</div>";
$string['feedbacknotificationtext'] = "%s commented on %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see and reply to the comment online, follow this link:
%s";
$string['feedbackdeletedhtml'] = "<div style=\"padding: 0.5em 0; border-bottom: 1px solid #999;\"><strong>A comment on %s was removed</strong><br>%s</div>

<div style=\"margin: 1em 0;\">%s</div>

<div style=\"font-size: smaller; border-top: 1px solid #999;\">
<p><a href=\"%s\">%s</a></p>
</div>";
$string['feedbackdeletedtext'] = "A comment on %s was removed
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see %s online, follow this link:
%s";

$string['artefactdefaultpermissions'] = 'Default comment permission';
$string['artefactdefaultpermissionsdescription'] = 'The selected artefact types will have comments enabled on creation. Users can override these settings for individual artefacts.';

// Extension config form
$string['commentratings'] = 'Enable comment ratings';
$string['ratingicons'] = 'Icon to use to display ratings';
$string['ratinglength'] = 'Number of rating choices';
$string['ratingcolour'] = 'Colour';
$string['ratingcolourdesc'] = 'The colour to display the rating choices in. A chosen rating will display the icon in the solid colour, and an unchosen one will display the colour in the icon outline.';
$string['star'] = 'Star';
$string['heart'] = 'Heart';
$string['thumbsup'] = 'Thumbs up';
$string['ok'] = 'Tick';
$string['ratingexample'] = 'Generated example';
$string['removerating'] = 'Clear ratings';
$string['ratingoption'] = "Set rating %s out of %s";
