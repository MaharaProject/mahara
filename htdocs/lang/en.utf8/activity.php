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
 * @subpackage notification-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['typemaharamessage'] = 'System message';
$string['typeusermessage'] = 'Message from other users';
$string['typewatchlist'] = 'Watchlist';
$string['typeviewaccess'] = 'New page access';
$string['typecontactus'] = 'Contact us';
$string['typeobjectionable'] = 'Objectionable content';
$string['typevirusrepeat'] = 'Repeat virus upload';
$string['typevirusrelease'] = 'Virus flag release';
$string['typeadminmessages'] = 'Administration messages';
$string['typeinstitutionmessage'] = 'Institution message';
$string['typegroupmessage'] = 'Group message';

$string['type'] = 'Activity type';
$string['attime'] = 'at';
$string['prefsdescr'] = 'If you select either of the email options, notifications will still arrive in your Inbox, but they will be automatically marked as read.';

$string['subject'] = 'Subject';
$string['date'] = 'Date';
$string['read'] = 'Read';
$string['unread'] = 'Unread';

$string['markasread'] = 'Mark as read';
$string['selectall'] = 'Select all';
$string['recurseall'] = 'Recurse all';
$string['alltypes'] = 'All types';

$string['markedasread'] = 'Marked your notifications as read';
$string['failedtomarkasread'] = 'Failed to mark your notifications as read';

$string['deletednotifications'] = 'Deleted %s notifications';
$string['failedtodeletenotifications'] = 'Failed to delete your notifications';

$string['stopmonitoring'] = 'Stop monitoring';
$string['artefacts'] = 'Artefacts';
$string['groups'] = 'Groups';
$string['monitored'] = 'Monitored';

$string['stopmonitoringsuccess'] = 'Stopped monitoring successfully';
$string['stopmonitoringfailed'] = 'Failed to stop monitoring';

$string['newwatchlistmessage'] = 'New activity on your watchlist';
$string['newwatchlistmessageview'] = '%s has changed their page "%s"';

$string['newviewsubject'] = 'New page created';
$string['newviewmessage'] = '%s has created a new page "%s"';

$string['newcontactusfrom'] = 'New contact us from';
$string['newcontactus'] = 'New contact us';

$string['newviewaccessmessage'] = 'You have been added to the access list for the page "%s" by %s';
$string['newviewaccessmessagenoowner'] = 'You have been added to the access list for the page "%s"';
$string['newviewaccesssubject'] = 'New page access';

$string['viewmodified'] = 'has changed their page';
$string['ongroup'] = 'on Group';
$string['ownedby'] = 'owned by';

$string['objectionablecontentview'] = 'Objectionable content on Page "%s" reported by %s';
$string['objectionablecontentviewartefact'] = 'Objectionable content on Page "%s" in "%s" reported by %s';

$string['objectionablecontentviewhtml'] = '<div style="padding: 0.5em 0; border-bottom: 1px solid #999;">Objectionable content on "%s" reported by %s<strong></strong><br>%s</div>

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

$string['objectionablecontentviewartefacthtml'] = '<div style="padding: 0.5em 0; border-bottom: 1px solid #999;">Objectionable content on "%s" in "%s" reported by %s<strong></strong><br>%s</div>

<div style="margin: 1em 0;">%s</div>

<div style="font-size: smaller; border-top: 1px solid #999;">
<p>Complaint relates to: <a href="%s">%s</a></p>
<p>Reported by: <a href="%s">%s</a></p>
</div>';
$string['objectionablecontentviewartefacttext'] = 'Objectionable content on "%s" in "%s" reported by %s
%s
------------------------------------------------------------------------

%s

------------------------------------------------------------------------
To see the page, follow this link:
%s
To see the reporter\'s profile, follow this link:
%s';

$string['newgroupmembersubj'] = '%s is now a group member!';
$string['removedgroupmembersubj'] = '%s is no longer a group member';

$string['addtowatchlist'] = 'Add to watchlist';
$string['removefromwatchlist'] = 'Remove from watchlist';

$string['missingparam'] = 'Required parameter %s was empty for activity type %s';

$string['institutionrequestsubject'] = '%s has requested membership of %s.';
$string['institutionrequestmessage'] = 'You can add users to institutions on the Institution Members page:';

$string['institutioninvitesubject'] = 'You have been invited to join the institution %s.';
$string['institutioninvitemessage'] = 'You can confirm your membership of this institution on your Institution Settings Page:';

$string['deleteallnotifications'] = 'Delete all notifications';
$string['reallydeleteallnotifications'] = 'Are you sure you want to delete all your notifications of this activity type?';

$string['viewsubmittedsubject'] = 'Page submitted to %s';
$string['viewsubmittedmessage'] = '%s has submitted their page "%s" to %s';

$string['adminnotificationerror'] = 'User notification error was probably caused by your server configuration.';
