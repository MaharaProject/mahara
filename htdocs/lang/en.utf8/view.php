<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['createview']             = 'Create View';
$string['createviewstepone']      = 'Create View Step One: Details';
$string['createviewsteptwo']      = 'Create View Step Two: Layout';
$string['createviewstepthree']    = 'Create View Step Three: Access';
$string['editviewdetails']        = 'Edit Details for View "%s"';
$string['editblocksforview']      = 'Edit View "%s"';
$string['editaccessforview']      = 'Edit Access for View "%s"';
$string['next']                   = 'Next';
$string['back']                   = 'Back';
$string['title']                  = 'View Title';
$string['description']            = 'View Description';
$string['startdate']              = 'Access Start Date/Time';
$string['stopdate']               = 'Access End Date/Time';
$string['startdatemustbebeforestopdate'] = 'The start date must be before the stop date';
$string['ownerformat']            = 'Name display format';
$string['ownerformatdescription'] = 'How do you want people who look at your View to see your name?';

// my views
$string['artefacts'] = 'Artefacts';
$string['myviews'] = 'My Views';
$string['reallyaddaccesstoemptyview'] = 'Your View contains no artefacts.  Do you really want to give these users access to the View?';
$string['viewdeleted'] = 'View deleted';
$string['viewsubmitted'] = 'View submitted';
$string['editviewnameanddescription'] = 'Edit View details';
$string['editviewaccess'] = 'Edit View access';
$string['deletethisview'] = 'Delete this View';
$string['submitthisviewto'] = 'Submit this View to';
$string['forassessment'] = 'for assessment';
$string['accessfromdate'] = 'Nobody can see this View before %s';
$string['accessuntildate'] = 'Nobody can see this View after %s';
$string['accessbetweendates'] = 'Nobody can see this View before %s or after %s';
$string['artefactsinthisview'] = 'Artefacts in this View';
$string['whocanseethisview'] = 'Who can see this View';
$string['view'] = 'view';
$string['views'] = 'views';
$string['View'] = 'View';
$string['Views'] = 'Views';
$string['viewsubmittedtogroup'] = 'This View has been submitted to <a href="' . get_config('wwwroot') . 'group/view.php?id=%s">%s</a>';
$string['nobodycanseethisview'] = 'Nobody can see this View';
$string['noviews'] = 'You have no Views.';

// access levels
$string['public'] = 'Public';
$string['loggedin'] = 'Logged In Users';
$string['friends'] = 'Friends';
$string['groups'] = 'Groups';
$string['users'] = 'Users';
$string['friendslower'] = 'friends';
$string['grouplower'] = 'group';
$string['tutors'] = 'tutors';
$string['loggedinlower'] = 'logged in users';
$string['publiclower'] = 'public';

// view user
$string['inviteusertojoingroup'] = 'Invite this user to join a group';
$string['addusertogroup'] = 'Add this user to a group';

// view view
$string['addedtowatchlist'] = 'This View has been added to your watchlist';
$string['attachment'] = 'Attachment';
$string['removedfromwatchlist'] = 'This View has been removed from your watchlist';
$string['addfeedbackfailed'] = 'Add feedback failed';
$string['addtowatchlist'] = 'Add View to watchlist';
$string['removefromwatchlist'] = 'Remove View from watchlist';
$string['alreadyinwatchlist'] = 'This View is already in your watchlist';
$string['attachedfileaddedtofolder'] = "The attached file %s has been added to your '%s' folder.";
$string['attachfile'] = "Attach file";
$string['complaint'] = 'Complaint';
$string['date'] = 'Date';
$string['feedback'] = 'Feedback';
$string['feedbackattachdirname'] = 'assessmentfiles';
$string['feedbackattachdirdesc'] = 'Files attached to View assessments';
$string['feedbackattachmessage'] = 'The attached file has been added to your %s folder';
$string['feedbackonthisartefactwillbeprivate'] = 'Feedback on this artefact will only be visible to the owner.';
$string['feedbackonviewbytutorofgroup'] = 'Feedback on %s by %s of %s';
$string['feedbacksubmitted'] = 'Feedback submitted';
$string['makepublic'] = 'Make public';
$string['nopublicfeedback'] = 'No public feedback';
$string['notifysiteadministrator'] = 'Notify site administrator';
$string['placefeedback'] = 'Place feedback';
$string['print'] = 'Print';
$string['thisfeedbackispublic'] = 'This feedback is public';
$string['thisfeedbackisprivate'] = 'This feedback is private';
$string['makeprivate'] = 'Change to Private';
$string['reportobjectionablematerial'] = 'Report objectionable material';
$string['reportsent'] = 'Your report has been sent';
$string['updatewatchlistfailed'] = 'Update of watchlist failed';
$string['watchlistupdated'] = 'Your watchlist has been updated';
$string['editmyview'] = 'Edit my View';
$string['backtocreatemyview'] = 'Back to create my View';

$string['friend'] = 'Friend';
$string['profileicon'] = 'Profile Icon';

// general views stuff
$string['allviews'] = 'All Views';

$string['submitviewconfirm'] = 'If you submit \'%s\' to \'%s\' for assessment, you will not be able to edit the View until your tutor has finished marking the View.  Are you sure you want to submit this View now?';
$string['viewsubmitted'] = 'View submitted';
$string['submitviewtogroup'] = 'Submit \'%s\' to \'%s\' for assessment';
$string['cantsubmitviewtogroup'] = 'You cannot submit this View to this group for assessment';

$string['cantdeleteview'] = 'You cannot delete this View';
$string['deletespecifiedview'] = 'Delete View "%s"';
$string['deleteviewconfirm'] = 'Do you really want to delete this View? It cannot be undone.';

$string['editaccesspagedescription'] = '<p>By default, only you can see your View. Here you can choose who else you would like to be able to see your View.</p>';

$string['overridingstartstopdate'] = 'Overriding Start/Stop Dates';
$string['overridingstartstopdatesdescription'] = 'If you want, you can set an overriding start and/or stop date. Other people will not be able to see your View before the start date and after the end date, regardless of any other access you have granted.';

$string['emptylabel'] = 'Click here to enter text for this label';
$string['empty_block'] = 'Select an artefact from the tree on the left to place here';

$string['viewinformationsaved'] = 'View information saved successfully';

$string['canteditdontown'] = 'You can\'t edit this View because you don\'t own it';
$string['canteditdontownfeedback'] = 'You can\'t edit this feedback because you don\'t own it';
$string['feedbackchangedtoprivate'] = 'Feedback changed to private';

$string['addtutors'] = 'Add Tutors';
$string['viewcreatedsuccessfully'] = 'View created successfully';
$string['viewaccesseditedsuccessfully'] = 'View access saved successfully';
$string['viewsavedsuccessfully'] = 'View saved successfully';

$string['invalidcolumn'] = 'Column %s out of range';

$string['confirmcancelcreatingview'] = 'This View has not been completed. Do you really want to cancel?';

// view control stuff

$string['displaymyview'] = 'Display my View';
$string['editthisview'] = 'Edit this View';

$string['success.addblocktype'] = 'Added block successfully';
$string['err.addblocktype'] = 'Could not add the block to your View';
$string['success.moveblockinstance'] = 'Moved block successfully';
$string['err.moveblockinstance'] = 'Could not move the block to the specified position';
$string['success.removeblockinstance'] = 'Deleted block successfully';
$string['err.removeblockinstance'] = 'Could not delete block';
$string['success.addcolumn'] = 'Added column successfully';
$string['err.addcolumn'] = 'Failed to add new column';
$string['success.removecolumn'] = 'Deleted column successfully';
$string['err.removecolumn'] = 'Failed to delete column';

$string['confirmdeleteblockinstance'] = 'Are you sure you wish to delete this block?';
$string['blockinstanceconfiguredsuccessfully'] = 'Block configured successfully';

$string['blocksintructionnoajax'] = 'Select a block and choose where to add it to your View. You can position a block using the arrow buttons in its titlebar';
$string['blocksinstructionajax'] = 'Drag blocks here to add them to your View. You can drag blocks around your View to position them.';

$string['addnewblockhere'] = 'Add new block here';
$string['add'] = 'Add';
$string['addcolumn'] = 'Add Column';
$string['removecolumn'] = 'Remove this column';
$string['moveblockleft'] = 'Move this block left';
$string['moveblockdown'] = 'Move this block down';
$string['moveblockup'] = 'Move this block up';
$string['moveblockright'] = 'Move this block right';
$string['configureblock'] = 'Configure this block';
$string['removeblock'] = 'Remove this block';

$string['changemyviewlayout'] = 'Change My View Layout';
$string['viewcolumnspagedescription'] = 'First, select the number of columns in your View. In the next step, you will be able to change the widths of the columns.';
$string['viewlayoutpagedescription'] = 'Select how you would like the columns in your View to be layed out.';
$string['changeviewlayout'] = 'Change my View layout';
$string['backtoyourview'] = 'Back to my View';
$string['viewlayoutchanged'] = 'View layout changed';
$string['numberofcolumns'] = 'Number of columns';


$string['by'] = 'by';
$string['in'] = 'in';
$string['noblocks'] = 'Sorry, no blocks in this category :(';
$string['Preview'] = 'Preview';

$string['50,50'] = $string['33,33,33'] = $string['25,25,25,25'] = 'Equal widths';
$string['67,33'] = 'Larger left column';
$string['33,67'] = 'Larger right column';
$string['25,50,25'] = 'Larger centre column';
$string['15,70,15'] = 'Much larger centre column';
$string['20,30,30,20'] = 'Larger centre columns';
$string['noviewlayouts'] = 'There are no View layouts for a %s column View';

$string['blocktypecategory.feeds'] = 'External feeds';
$string['blocktypecategory.fileimagevideo'] = 'Files, images and video';
$string['blocktypecategory.general'] = 'General';

$string['notitle'] = 'No title';
$string['clickformoreinformation'] = 'Click for more information and to place feedback';

$string['Browse'] = 'Browse';
$string['Search'] = 'Search';
$string['noartefactstochoosefrom'] = 'Sorry, no artefacts to choose from';

$string['access'] = 'Access';

?>
