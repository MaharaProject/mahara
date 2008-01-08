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
$string['editview']               = 'Edit Details for View "%s"';
$string['editaccessforview']      = 'Edit View Access for View "%s"';
$string['editblocksforview']      = 'Edit Blocks for View "%s"';
$string['next']                   = 'Next';
$string['back']                   = 'Back';
$string['createview']             = 'Create View';
$string['title']                  = 'View Title';
$string['description']            = 'View Description';
$string['startdate']              = 'Access Start Date/Time';
$string['stopdate']               = 'Access End Date/Time';
$string['startdatemustbebeforestopdate'] = 'The start date must be before the stop date';
$string['ownerformat']            = 'Name display format';
$string['ownerformatdescription'] = 'How do you want people who look at your view to see your name?';

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
$string['changeviewlayout'] = 'Change view layout';
$string['backtoyourview'] = 'Back to your view';

$string['by'] = 'by';
$string['in'] = 'in';
$string['noblocks'] = 'Sorry, no blocks in this category :(';

$string['50,50'] = $string['33,33,33'] = $string['25,25,25,25'] = 'Equal widths';
$string['67,33'] = 'Larger left column';
$string['33,67'] = 'Larger right column';
$string['25,50,25'] = 'Larger centre column';
$string['15,70,15'] = 'Much larger centre column';
$string['20,30,30,20'] = 'Larger centre columns';
$string['noviewlayouts'] = 'There are no view layouts for a %s column view';

$string['blocktypecategory.feeds'] = 'External feeds';
$string['blocktypecategory.images'] = 'Images';
$string['blocktypecategory.general'] = 'General';
$string['blocktypecategory.multimedia'] = 'Multimedia';

$string['notitle'] = 'No title';
$string['clickformoreinformation'] = 'Click for more information and to place feedback';

$string['Browse'] = 'Browse';
$string['Search'] = 'Search';
$string['noartefactstochoosefrom'] = 'Sorry, no artefacts to choose from';

?>
