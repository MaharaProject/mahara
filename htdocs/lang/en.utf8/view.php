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

$string['createview']             = 'Create Page';
$string['edittitle']              = 'Edit Title';
$string['edittitleanddescription'] = 'Edit Title and Description';
$string['editcontent']            = 'Edit Content';
$string['editcontentandlayout']   = 'Edit Content and Layout';
$string['editlayout']             = 'Edit Layout';
$string['editaccess']             = 'Edit Access';
$string['next']                   = 'Next';
$string['back']                   = 'Back';
$string['title']                  = 'Page Title';
$string['description']            = 'Page Description';
$string['startdate']              = 'Access Start Date/Time';
$string['stopdate']               = 'Access End Date/Time';
$string['accessdates']            = 'Access Date/Time';
$string['newstopdatecannotbeinpast'] = 'The end date for \'%s\' access cannot be in the past';
$string['newstartdatemustbebeforestopdate'] = 'The start date for \'%s\' access must be before the end date';
$string['unrecogniseddateformat'] = 'Unrecognised date format';
$string['allowcommentsonview']    = 'If checked, users will be allowed to leave comments.';
$string['ownerformat']            = 'Name display format';
$string['ownerformatdescription'] = 'How do you want people who look at your page to see your name?';
$string['profileviewtitle']       = 'Profile page';
$string['dashboardviewtitle']     = 'Dashboard page';
$string['grouphomepageviewtitle'] = 'Group Homepage';

// my views
$string['artefacts'] = 'Artefacts';
$string['groupviews'] = 'Group pages';
$string['institutionviews'] = 'Institution pages';
$string['reallyaddaccesstoemptyview'] = 'Your page contains no blocks.  Do you really want to give these users access to the page?';
$string['viewdeleted'] = 'Page deleted';
$string['viewsubmitted'] = 'Page submitted';
$string['deletethisview'] = 'Delete this page';
$string['submitthisviewto'] = 'Submit this page to';
$string['forassessment'] = 'for assessment';
$string['accessfromdate2'] = 'Nobody else can see this page before %s';
$string['accessuntildate2'] = 'Nobody else can see this page after %s';
$string['accessbetweendates2'] = 'Nobody else can see this page before %s or after %s';
$string['artefactsinthisview'] = 'Artefacts in this page';
$string['whocanseethisview'] = 'Who can see this page';
$string['view'] = 'page';
$string['views'] = 'pages';
$string['View'] = 'Page';
$string['Views'] = 'Pages';
$string['viewsubmittedtogroup'] = 'This page has been submitted to <a href="%s">%s</a>';
$string['viewsubmittedtogroupon'] = 'This page was submitted to <a href="%s">%s</a> on %s';
$string['nobodycanseethisview2'] = 'Only you can see this page';
$string['noviews'] = 'No pages.';
$string['youhavenoviews'] = 'You have no pages.';
$string['youhaventcreatedanyviewsyet'] = "You haven't created any pages yet.";
$string['youhaveoneview'] = 'You have 1 page.';
$string['youhaveviews']   = 'You have %s pages.';
$string['viewsownedbygroup'] = 'Pages owned by this group';
$string['viewssharedtogroup'] = 'Pages shared to this group';
$string['viewssharedtogroupbyothers'] = 'Pages shared to this group by others';
$string['viewssubmittedtogroup'] = 'Pages submitted to this group';
$string['submitaviewtogroup'] = 'Submit a page to this group';
$string['youhavesubmitted'] = 'You have submitted <a href="%s">%s</a> to this group';
$string['youhavesubmittedon'] = 'You submitted <a href="%s">%s</a> to this group on %s';

// access levels
$string['public'] = 'Public';
$string['loggedin'] = 'Logged In Users';
$string['friends'] = 'Friends';
$string['groups'] = 'Groups';
$string['users'] = 'Users';
$string['tutors'] = 'tutors';
$string['everyoneingroup'] = 'Everyone in Group';

// secret url
$string['token'] = 'Secret URL';
$string['editsecreturlaccess'] = 'Edit secret URL access';
$string['newsecreturl'] = 'New Secret URL';
$string['reallydeletesecreturl'] = 'Are you sure you want to delete this url?';
$string['secreturldeleted'] = 'Your secret URL was deleted.';
$string['secreturlupdated'] = 'Secret URL updated';
$string['generatesecreturl'] = 'Generate a new secret URL for %s';
$string['secreturls'] = 'Secret URLs';

// view user
$string['inviteusertojoingroup'] = 'Invite this user to join a group';
$string['addusertogroup'] = 'Add this user to a group';

// view view
$string['addedtowatchlist'] = 'This page has been added to your watchlist';
$string['attachment'] = 'Attachment';
$string['removedfromwatchlist'] = 'This page has been removed from your watchlist';
$string['addtowatchlist'] = 'Add page to watchlist';
$string['removefromwatchlist'] = 'Remove page from watchlist';
$string['alreadyinwatchlist'] = 'This page is already in your watchlist';
$string['attachedfileaddedtofolder'] = "The attached file %s has been added to your '%s' folder.";
$string['complaint'] = 'Complaint';
$string['date'] = 'Date';
$string['notifysiteadministrator'] = 'Notify site administrator';
$string['notifysiteadministratorconfirm'] = 'Are you sure you wish to report this page as containing objectional material?';
$string['print'] = 'Print';
$string['reportobjectionablematerial'] = 'Report objectionable material';
$string['reportsent'] = 'Your report has been sent';
$string['viewobjectionableunmark'] = 'This page, or something within it, has been reported as containing objectionable content.  If this is no longer the case, you can click the button to remove this notice and notify the other administrators.';
$string['notobjectionable'] = 'Not objectionable';
$string['viewunobjectionablesubject'] = 'Page %s was marked as not objectionable by %s';
$string['viewunobjectionablebody'] = '%s has looked at %s by %s and marked it as no longer containing objectionable material.';
$string['updatewatchlistfailed'] = 'Update of watchlist failed';
$string['watchlistupdated'] = 'Your watchlist has been updated';
$string['viewvisitcount'] = '%d page visit(s) from %s to %s';

$string['friend'] = 'Friend';
$string['profileicon'] = 'Profile Picture';

// general views stuff
$string['Added'] = 'Added';
$string['share'] = 'Share';
$string['sharewith'] = 'Share with';
$string['accesslist'] = 'Access list';
$string['sharewithmygroups'] = 'Share with My Groups';
$string['shareview'] = 'Share page';
$string['otherusersandgroups'] = 'Share with other users and groups';
$string['moreoptions'] = 'Advanced Options';
$string['allviews'] = 'All pages';

$string['submitviewconfirm'] = 'If you submit \'%s\' to \'%s\' for assessment, you will not be able to edit the page until your tutor has finished marking it.  Are you sure you want to submit this page now?';
$string['viewsubmitted'] = 'Page submitted';
$string['submitviewtogroup'] = 'Submit \'%s\' to \'%s\' for assessment';
$string['cantsubmitviewtogroup'] = 'You cannot submit this page to this group for assessment';

$string['cantdeleteview'] = 'You cannot delete this page';
$string['deletespecifiedview'] = 'Delete page "%s"';
$string['deleteviewconfirm'] = 'Do you really want to delete this page? It cannot be undone.<br/>Please consider creating a backup of this page by <a href="%sexport/" target="_blank">exporting</a> it.';
$string['deleteviewconfirmnote'] = '<strong>NOTE:</strong> All your files and journal entries that you linked in this page will still be available.<br/>However, any feedback placed on this page as well as text entered directly into text boxes will be deleted.';

$string['editaccesspagedescription3'] = 'By default, only you can see your Pages. You can share pages with others by adding access rules.  Once you are done, scroll down and click Save to continue.';
$string['editaccessinvalidviewset'] = 'Attempt to edit access on an invalid set of pages and collections';

$string['overridingstartstopdate'] = 'Overriding Start/Stop Dates';
$string['overridingstartstopdatesdescription'] = 'If you want, you can set an overriding start and/or stop date. Other people will not be able to see your page before the start date and after the end date, regardless of any other access you have granted.';

$string['emptylabel'] = 'Click here to enter text for this label';
$string['empty_block'] = 'Select an artefact from the tree on the left to place here';

$string['viewinformationsaved'] = 'Page information saved successfully';

$string['canteditdontown'] = 'You can\'t edit this page because you don\'t own it';
$string['canteditsubmitted'] = 'You can\'t edit this page because it has been submitted for assessment to "%s". You will have to wait until a tutor releases the page.';
$string['Submitted'] = 'Submitted';
$string['submittedforassessment'] = 'Submitted for assessment';

$string['addtutors'] = 'Add Tutors';
$string['viewcreatedsuccessfully'] = 'Page created successfully';
$string['viewaccesseditedsuccessfully'] = 'Page access saved successfully';
$string['viewsavedsuccessfully'] = 'Page saved successfully';
$string['updatedaccessfornumviews'] = 'Access rules were updated for %d page(s)';

$string['invalidcolumn'] = 'Column %s out of range';

$string['confirmcancelcreatingview'] = 'This page has not been completed. Do you really want to cancel?';

// view control stuff

$string['editblockspagedescription'] = '<p>Drag and drop content blocks from the tabs below to create your page.</p>';
$string['displayview'] = 'Display page';
$string['editthisview'] = 'Edit this page';

$string['success.addblocktype'] = 'Added block successfully';
$string['err.addblocktype'] = 'Could not add the block to your page';
$string['success.moveblockinstance'] = 'Moved block successfully';
$string['err.moveblockinstance'] = 'Could not move the block to the specified position';
$string['success.removeblockinstance'] = 'Deleted block successfully';
$string['err.removeblockinstance'] = 'Could not delete block';
$string['success.addcolumn'] = 'Added column successfully';
$string['err.addcolumn'] = 'Failed to add new column';
$string['success.removecolumn'] = 'Deleted column successfully';
$string['err.removecolumn'] = 'Failed to delete column';
$string['success.changetheme'] = 'Theme updated successfully';
$string['err.changetheme'] = 'Could not update theme';

$string['confirmdeleteblockinstance'] = 'Are you sure you wish to delete this block?';
$string['blockinstanceconfiguredsuccessfully'] = 'Block configured successfully';
$string['blockconfigurationrenderingerror'] = 'Configuration failed because the block could not be rendered.';

$string['blocksintructionnoajax'] = 'Select a block and choose where to add it to your page. You can position a block using the arrow buttons in its titlebar';
$string['blocksinstructionajax'] = 'This area shows a preview of what your page will look like.<br>Drag blocks below this line to add them to your page layout. You can drag blocks around your page layout to position them.';

$string['addnewblockhere'] = 'Add new block here';
$string['add'] = 'Add';
$string['addcolumn'] = 'Add Column';
$string['remove'] = 'Remove';
$string['removecolumn'] = 'Remove this column';
$string['moveblockleft'] = "Move %s block left";
$string['movethisblockleft'] = "Move this block left";
$string['moveblockdown'] = "Move %s block down";
$string['movethisblockdown'] = "Move this block down";
$string['moveblockup'] = "Move %s block up";
$string['movethisblockup'] = "Move this block up";
$string['moveblockright'] = "Move %s block right";
$string['movethisblockright'] = "Move this block right";
$string['Configure'] = 'Configure';
$string['configureblock'] = 'Configure %s block';
$string['configurethisblock'] = 'Configure this block';
$string['removeblock'] = 'Remove %s block';
$string['removethisblock'] = 'Remove this block';
$string['blocktitle'] = 'Block Title';

$string['changemyviewlayout'] = 'Change My page Layout';
$string['viewcolumnspagedescription'] = 'First, select the number of columns in your page. In the next step, you will be able to change the widths of the columns.';
$string['viewlayoutpagedescription'] = 'Select how you would like the columns in your page to be layed out.';
$string['changeviewlayout'] = 'Change my page\'s column layout';
$string['viewlayoutchanged'] = 'Page layout changed';
$string['numberofcolumns'] = 'Number of columns';
$string['changecolumnlayoutfailed'] = 'Could not change the column layout. Someone else may have been editing the layout at the same time. Please try again later.';


$string['by'] = 'by';
$string['viewtitleby'] = '%s by <a href="%s">%s</a>';
$string['in'] = 'in';
$string['noblocks'] = 'Sorry, no blocks in this category :(';
$string['timeofsubmission'] = 'Time of submission';

$string['column'] = 'column';
$string['columns'] = 'columns';
$string['100'] = $string['50,50'] = $string['33,33,33'] = $string['25,25,25,25'] = $string['20,20,20,20,20'] = 'Equal widths';
$string['67,33'] = 'Larger left column';
$string['33,67'] = 'Larger right column';
$string['25,50,25'] = 'Larger centre column';
$string['15,70,15'] = 'Much larger centre column';
$string['20,30,30,20'] = 'Larger centre columns';
$string['noviewlayouts'] = 'There are no layouts for a %s column page';
$string['cantaddcolumn'] = 'You cannot add any more columns to this page';
$string['cantremovecolumn'] = 'You cannot remove the last column from this page';

$string['blocktypecategory.external'] = 'External content';
$string['blocktypecategory.fileimagevideo'] = 'Files, images and video';
$string['blocktypecategory.general'] = 'General';

$string['notitle'] = 'No title';
$string['clickformoreinformation'] = 'Click for more information and to place feedback';

$string['Browse'] = 'Browse';
$string['Search'] = 'Search';
$string['noartefactstochoosefrom'] = 'Sorry, no artefacts to choose from';

$string['access'] = 'Access';
$string['noaccesstoview'] = 'You do not have permission to access this page';

$string['changeviewtheme'] = 'The theme you have chosen for this page is no longer available to you.  Please select a different theme.';

// Templates
$string['Template'] = 'Template';
$string['allowcopying'] = 'Allow copying';
$string['templatedescription'] = 'Check this box if you would like the people who can see your page to be able to make their own copies of it, along with any files and folders it contains.';
$string['templatedescriptionplural'] = 'Check this box if you would like the people who can see your pages to be able to make their own copies of them, along with any files and folders they contain.';
$string['choosetemplatepagedescription'] = '<p>Here you can search through the pages that you are allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy Page" button to make a copy and begin customising it.</p>';
$string['choosetemplategrouppagedescription'] = '<p>Here you can search through the pages that this group is allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy Page" button to make a copy and begin customising it.</p><p><strong>Note:</strong> Groups cannot currently make copies of Journals or Journal Entriess.</p>';
$string['choosetemplateinstitutionpagedescription'] = '<p>Here you can search through the pages that this institution is allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy Page" button to make a copy and begin customising it.</p><p><strong>Note:</strong> Institutions cannot currently make copies of Journals or Journal Entries.</p>';
$string['copiedblocksandartefactsfromtemplate'] = 'Copied %d blocks and %d artefacts from %s';
$string['filescopiedfromviewtemplate'] = 'Files copied from %s';
$string['viewfilesdirname'] = 'viewfiles';
$string['viewfilesdirdesc'] = 'Files from copied pages';
$string['thisviewmaybecopied'] = 'Copying is allowed';
$string['copythisview'] = 'Copy this page';
$string['copyview'] = 'Copy page';
$string['createemptyview'] = 'Create empty page';
$string['copyaview'] = 'Copy a page';
$string['Untitled'] = 'Untitled';
$string['copyfornewusers'] = 'Copy for new users';
$string['copyfornewusersdescription'] = 'Whenever a new user is created, automatically make a personal copy of this page in the user\'s portfolio.';
$string['copyfornewmembers'] = 'Copy for new institution members';
$string['copyfornewmembersdescription'] = 'Automatically make a personal copy of this page for all new members of %s.';
$string['copyfornewgroups'] = 'Copy for new groups';
$string['copyfornewgroupsdescription'] = 'Make a copy of this page in all new groups with these Group Types:';
$string['searchviews'] = 'Search pages';
$string['searchowners'] = 'Search owners';
$string['owner'] = 'owner';
$string['Owner'] = 'Owner';
$string['owners'] = 'owners';
$string['show'] = 'Show';
$string['searchviewsbyowner'] = 'Search for pages by owner:';
$string['selectaviewtocopy'] = 'Select the page you wish to copy:';
$string['listviews'] = 'List pages';
$string['nocopyableviewsfound'] = 'No pages that you can copy';
$string['noownersfound'] = 'No owners found';
$string['Preview'] = 'Preview';
$string['viewscopiedfornewusersmustbecopyable'] = 'You must allow copying before you can set a page to be copied for new users.';
$string['viewscopiedfornewgroupsmustbecopyable'] = 'You must allow copying before you can set a page to be copied for new groups.';
$string['copynewusergroupneedsloggedinaccess'] = 'Pages copied for new users or groups must give access to logged-in users.';
$string['viewcopywouldexceedquota'] = 'Copying this page would exceed your file quota.';

$string['blockcopypermission'] = 'Block copy permission';
$string['blockcopypermissiondesc'] = 'If you allow other users to copy this page, you may choose how this block will be copied';

// View types
$string['dashboard'] = 'Dashboard';
$string['profile'] = 'Profile';
$string['portfolio'] = 'Portfolio';
$string['grouphomepage'] = 'Group Homepage';

$string['grouphomepagedescription'] = 'The Group Homepage is the content that appears on the About tab for this group';

// Shared views
$string['sharedviews'] = 'Shared Pages';
$string['titleanddescription'] = 'Title, description, tags';
$string['tagsonly'] = 'Tags only';
$string['sharedviewsdescription'] = 'This page lists the most recently modified or commented on pages that have been shared with you.  They may have been shared with you directly, shared with friends of the owner, or shared with one of your groups.';
