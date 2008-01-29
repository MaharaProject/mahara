<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
$string['ownerformatdescription'] = 'How do you want people who look at your view to see your name?';

// my views
$string['artefacts'] = 'Artefacts';
$string['myviews'] = 'My Views';
$string['reallyaddaccesstoemptyview'] = 'Your view contains no artefacts.  Do you really want to give these users access to the view?';
$string['viewdeleted'] = 'View deleted';
$string['viewsubmitted'] = 'View submitted';
$string['editviewnameanddescription'] = 'Edit view details';
$string['editviewaccess'] = 'Edit view access';
$string['deletethisview'] = 'Delete this view';
$string['submitviewforassessment'] = 'Submit view for assessment';
$string['accessfromdate'] = 'Nobody can see this view before %s';
$string['accessuntildate'] = 'Nobody can see this view after %s';
$string['accessbetweendates'] = 'Nobody can see this view before %s or after %s';
$string['artefactsinthisview'] = 'Artefacts in this view';
$string['whocanseethisview'] = 'Who can see this view';
$string['view'] = 'view';
$string['views'] = 'views';
$string['View'] = 'View';
$string['Views'] = 'Views';
$string['viewsubmittedtogroup'] = 'This view has been submitted to <a href="' . get_config('wwwroot') . 'group/view.php?id=%s">%s</a>';
$string['nobodycanseethisview'] = 'Nobody can see this view';
$string['noviews'] = 'You have no views.';

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
$string['addedtowatchlist'] = 'This view has been added to your watchlist';
$string['removedfromwatchlist'] = 'This view has been removed from your watchlist';
$string['addfeedbackfailed'] = 'Add feedback failed';
$string['addtowatchlist'] = 'Add view to watchlist';
$string['removefromwatchlist'] = 'Remove view from watchlist';
$string['alreadyinwatchlist'] = 'This view is already in your watchlist';
$string['attachedfileaddedtofolder'] = "The attached file %s has been added to your '%s' folder.";
$string['attachfile'] = "Attach file";
$string['complaint'] = 'Complaint';
$string['date'] = 'Date';
$string['feedback'] = 'Feedback';
$string['feedbackattachdirname'] = 'assessmentfiles';
$string['feedbackattachdirdesc'] = 'Files attached to view assessments';
$string['feedbackattachmessage'] = 'The attached file has been added to your %s folder';
$string['feedbackmadeprivate'] = 'Feedback changed to private';
$string['feedbackonthisartefactwillbeprivate'] = 'Feedback on this artefact will only be visible to the owner.';
$string['feedbackonviewbytutorofgroup'] = 'Feedback on %s by %s of %s';
$string['feedbacksubmitted'] = 'Feedback submitted';
$string['makepublic'] = 'Make public';
$string['nopublicfeedback'] = 'No public feedback';
$string['notifysiteadministrator'] = 'Notify site administrator';
$string['placefeedback'] = 'Place feedback';
$string['print'] = 'Print';
$string['private'] = 'Private';
$string['makeprivate'] = 'Change to Private';
$string['reportobjectionablematerial'] = 'Report objectionable material';
$string['reportsent'] = 'Your report has been sent';
$string['updatewatchlistfailed'] = 'Update of watchlist failed';
$string['View'] = 'View';
$string['watchlistupdated'] = 'Your watchlist has been updated';
$string['editmyview'] = 'Edit my view';

$string['friend'] = 'Friend';
$string['profileicon'] = 'Profile Icon';

// general views stuff
$string['allviews'] = 'All views';

$string['submitviewconfirm'] = 'If you submit \'%s\' to \'%s\' for assessment, you will not be able to edit the view or any of its associated artefacts until your tutor has finished marking the view.  Are you sure you want to submit this view now?';
$string['viewsubmitted'] = 'View submitted';
$string['submitviewtogroup'] = 'Submit \'%s\' to \'%s\' for assessment';
$string['cantsubmitviewtogroup'] = 'You cannot submit this view to this group for assessment';

$string['cantdeleteview'] = 'You cannot delete this view';
$string['deletespecifiedview'] = 'Delete View "%s"';
$string['deleteviewconfirm'] = 'Do you really want to delete this view? It cannot be undone.';

$string['editaccesspagedescription'] = '<p>You can control who can see your view, and when they can do so. By default, only you can see your views.</p>
    <p>You can grant access to your view to your friends, all logged in users, or only to the certain users and groups you choose.</p>
    <p>All dates are optional. If you wish, you can use them to restrict the time in which people can see your view.</p>';
$string['emptylabel'] = 'Click here to enter text for this label';
$string['empty_block'] = 'Select an artefact from the tree on the left to place here';

$string['viewinformationsaved'] = 'View information saved successfully';

$string['canteditdontown'] = 'You can\'t edit this view because you don\'t own it';
$string['canteditdontownfeedback'] = 'You can\'t edit this feedback because you don\'t own it';
$string['feedbackchangedtoprivate'] = 'Feedback changed to private';

$string['chooseformat'] = 'Select how you would like to display this artefact in this block ...';

$string['format.listself']       = 'List item (you can put multiple artefacts in this block like this)';
$string['format.listchildren']   = 'List children of this Artefact';
$string['format.renderfull']     = 'Display entire artefact';
$string['format.rendermetadata'] = 'Display metadata for this Artefact';

$string['addtutors'] = 'Add Tutors';
$string['viewcreatedsuccessfully'] = 'View created successfully';
$string['viewaccesseditedsuccessfully'] = 'View access saved successfully';
$string['viewsavedsuccessfully'] = 'View saved successfully';

$string['invalidcolumn'] = 'Column %s out of range';

$string['confirmcancelcreatingview'] = 'This view has not been completed. Do you really want to cancel?';

// view control stuff

$string['displaymyview'] = 'Display my view';
$string['editthisview'] = 'Edit this view';

$string['success.addblocktype'] = 'Added block successfully';
$string['err.addblocktype'] = 'Could not add the block to your view';
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

$string['addnewblockhere'] = 'Add new block here';
$string['add'] = 'Add';
$string['addcolumn'] = 'Add Column';
$string['remove'] = 'Remove';
$string['removecolumn'] = 'Remove this column';
$string['moveblockleft'] = 'Move this block left';
$string['moveblockdown'] = 'Move this block down';
$string['moveblockup'] = 'Move this block up';
$string['moveblockright'] = 'Move this block right';
$string['configureblock'] = 'Configure this block';
$string['removeblock'] = 'Remove this block';

$string['viewlayout'] = 'View layout';
$string['viewlayoutdescription'] = 'You can change the widths of the columns in your view.';
$string['changeviewlayout'] = 'Change my view layout';
$string['backtoyourview'] = 'Back to my view';
$string['viewlayoutchanged'] = 'View layout changed';
$string['selectnumberofcolumns'] = 'You can change the number of columns in your view';
$string['changeviewcolumns'] = 'Change my view columns';

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
$string['noviewlayouts'] = 'There are no view layouts for a %s column view';

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
