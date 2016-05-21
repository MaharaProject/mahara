<?php
/**
 *
 * @package    mahara
 * @subpackage notification-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['typemaharamessage'] = 'System message';
$string['typeusermessage'] = 'Message from other users';
$string['typefeedback'] = 'Comment';
$string['typewatchlist'] = 'Watchlist';
$string['typeviewaccess'] = 'New page access';
$string['typecontactus'] = 'Contact us';
$string['typeobjectionable'] = 'Objectionable content';
$string['typevirusrepeat'] = 'Repeat virus upload';
$string['typevirusrelease'] = 'Virus flag release';
$string['typeadminmessages'] = 'Administration messages';
$string['typeinstitutionmessage'] = 'Institution message';
$string['typegroupmessage'] = 'Group message';
$string['typenewpost'] = 'Forum post';

$string['type'] = 'Activity type';
$string['attime'] = 'at';
$string['prefsdescr'] = 'If you select either of the email options, notifications will still arrive in your inbox, but they will be automatically marked as read.';

$string['messagetype'] = 'Message type';
$string['subject'] = 'Subject';
$string['date'] = 'Date';
$string['read'] = 'Read';
$string['unread'] = 'Unread';

$string['markasread'] = 'Mark as read';
$string['selectall'] = 'Select all';
$string['selectallread'] = 'All unread notifications';
$string['selectalldelete'] = 'All notifications for deletion';
$string['recurseall'] = 'Recurse all';
$string['alltypes'] = 'All types';
$string['nodelete'] = 'No notifications to delete';
$string['youroutboxisempty'] = 'Your outbox is empty.';
$string['yourinboxisempty'] = 'Your inbox is empty.';

$string['markedasread'] = 'Marked your notifications as read';
$string['failedtomarkasread'] = 'Failed to mark your notifications as read';

$string['deletednotifications1'] = array(
0 => 'Deleted %s notification',
1 => 'Deleted %s notifications'
);
$string['failedtodeletenotifications'] = 'Failed to delete your notifications';

$string['stopmonitoring'] = 'Stop monitoring';
$string['artefacts'] = 'Artefacts';
$string['groups'] = 'Groups';
$string['monitored'] = 'Monitored';

$string['stopmonitoringsuccess'] = 'Stopped monitoring successfully';
$string['stopmonitoringfailed'] = 'Failed to stop monitoring';

$string['newwatchlistmessage'] = 'New activity on your watchlist';
$string['newwatchlistmessageview1'] = 'The page "%s" belonging to %s has been changed';
$string['blockinstancenotification'] = 'The block "%s" has been added or changed';
$string['nonamegiven'] = 'no name given';

$string['newviewsubject'] = 'New page created';
$string['newviewmessage'] = '%s has created a new page "%s"';

$string['newcontactusfrom'] = 'New contact us from';
$string['newcontactus'] = 'New contact us';

$string['newcollectionaccessmessage'] = 'You have been added to the access list for the collection "%s" by %s';
$string['newcollectionaccessmessageviews'] = 'You have been added to the access list for the pages "%s" in collection "%3$s" by %2$s';
$string['newviewaccessmessage'] = 'You have been added to the access list for the page "%s" by %s';
$string['newviewaccessmessageviews'] = 'You have been added to the access list for the pages "%s" by %s';
$string['newcollectionaccessmessagenoowner'] = 'You have been added to the access list for the collection "%s"';
$string['newcollectionaccessmessagenoownerviews'] = 'You have been added to the access list for the pages "%s" in collection "%s"';
$string['newviewaccessmessagenoowner'] = 'You have been added to the access list for the page "%s"';
$string['newviewaccessmessagenoownerviews'] = 'You have been added to the access list for the pages "%s"';
$string['newcollectionaccesssubject'] = 'New access to collection "%s"';
$string['newviewaccesssubject1'] = 'New access to page "%s"';
$string['newviewaccesssubjectviews'] = 'New access to pages "%s"';

$string['viewmodified'] = 'has changed their page';
$string['ongroup'] = 'on group';
$string['ownedby'] = 'owned by';

$string['objectionablecontentview'] = 'Objectionable content on page "%s" reported by %s';
$string['objectionablecontentviewartefact'] = 'Objectionable content on page "%s" in "%s" reported by %s';

$string['objectionablecontentviewhtml'] = '<div style="padding: 0.5em 0; border-bottom: 1px solid #999;">Objectionable content on "%s" reported by %s<br>%s</div>

<div style="margin: 1em 0;">%s</div>

<div style="font-size: smaller; border-top: 1px solid #999;">
<p>Complaint relates to: <a href="%s">%s</a></p>
<p>Reported by: <a href="%s">%s</a></p>
</div>';
$string['objectionablecontentviewtext'] = 'Objectionable content on "%s" reported by %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see the page, follow this link:
%s
To see the reporter\'s profile, follow this link:
%s';

$string['objectionablecontentviewartefacthtml'] = '<div style="padding: 0.5em 0; border-bottom: 1px solid #999;">Objectionable content on "%s" in "%s" reported by %s<br>%s</div>

<div style="margin: 1em 0;">%s</div>

<div style="font-size: smaller; border-top: 1px solid #999;">
<p>Complaint relates to: <a href="%s">%s</a></p>
<p>Reported by: <a href="%s">%s</a></p>
</div>';
$string['objectionablecontentviewartefacttext'] = 'Objectionable content on %s in "%s" reported by %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see the page, follow this link:
%s
To see the reporter\'s profile, follow this link:
%s';

$string['newgroupmembersubj'] = '%s is now a group member.';
$string['removedgroupmembersubj'] = '%s is no longer a group member.';

$string['addtowatchlist'] = 'Add to watchlist';
$string['removefromwatchlist'] = 'Remove from watchlist';

$string['missingparam'] = 'Required parameter %s was empty for activity type %s';

$string['institutionrequestsubject'] = '%s has requested membership of %s.';
$string['institutionrequestmessage'] = 'You can add users to institutions on the "Institution members" page:';

$string['institutioninvitesubject'] = 'You have been invited to join the institution %s.';
$string['institutioninvitemessage'] = 'You can confirm your membership of this institution on your "Institution settings" page:';

$string['deleteallnotifications'] = 'Delete all notifications';
$string['reallydeleteallnotifications'] = 'Are you sure you want to delete all your notifications of this activity type?';

$string['viewsubmittedsubject1'] = 'Submission to %s';
$string['viewsubmittedmessage1'] = '%s has submitted "%s" to %s';

$string['adminnotificationerror'] = 'User notification error was probably caused by your server configuration.';
