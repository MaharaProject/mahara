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

$string['createview']             = 'Create page';
$string['edittitle']              = 'Edit title';
$string['edittitleanddescription'] = 'Edit title and description';
$string['editcontent']            = 'Edit content';
$string['editcontentandlayout']   = 'Edit content and layout';
$string['editlayout']             = 'Edit layout';
$string['editaccess']             = 'Edit access';
$string['editaccessfor']          = 'Edit access (ID %s)';
$string['next']                   = 'Next';
$string['back']                   = 'Back';
$string['title']                  = 'Page title';
$string['undo']                   = 'Undo';
$string['viewurl']                = 'Page URL';
$string['viewurldescription']     = 'A readable URL for your page. This field must be between 3 and 100 characters long.';
$string['userviewurltaken']       = 'This URL is already taken. Please choose another one.';
$string['description']            = 'Page description';
$string['startdate']              = 'Access start date/time';
$string['stopdate']               = 'Access end date/time';
$string['overrideconflict'] = 'One or more access permissions are in conflict with the overriding dates. These access permissions will not be valid outside the overriding dates.';
$string['stopdatecannotbeinpast1'] = '"To" date cannot be in the past';
$string['startdatemustbebeforestopdate'] = 'Start date must be before stop date';
$string['newstopdatecannotbeinpast'] = 'The end date for \'%s\' access cannot be in the past.';
$string['newstartdatemustbebeforestopdate'] = 'The start date for \'%s\' access must be before the end date.';
$string['unrecogniseddateformat'] = 'Unrecognised date format';
$string['allowcommentsonview1']    = 'Allow users to leave comments.';
$string['ownerformat']            = 'Name display format';
$string['ownerformatdescription'] = 'How do you want people who look at your page to see your name?';
$string['anonymise']              = 'Anonymise';
$string['anonymisedescription']   = 'Hide your name as the author of the page from other users. Administrators will still be able to see your name if they so desire.';
$string['Locked']                 = 'Locked';
$string['lockedgroupviewdesc']    = 'If you lock this page, only group administrators will be able to edit it.';
$string['profileviewtitle']       = 'Profile page';
$string['dashboardviewtitle']     = 'Dashboard page';
$string['grouphomepageviewtitle'] = 'Group homepage';
$string['viewname']               = 'Page name';
$string['templatedashboard']      = 'Dashboard template';
$string['templategrouphomepage']  = 'Group homepage template';
$string['templateprofile']        = 'Profile template';
$string['templateportfolio']      = 'Page template';
$string['templateportfoliotitle']       = 'Untitled';
$string['templateportfoliodescription'] = 'Set up the default layout for the pages that your users create. You can also add blocks. Please note that any content you add to the default page will appear on every page that your users create.';

// my views
$string['artefacts'] = 'Artefacts';
$string['groupviews'] = 'Group pages';
$string['institutionviews'] = 'Institution pages';
$string['reallyaddaccesstoemptyview'] = 'Your page contains no blocks. Do you really want to give these users access to the page?';
$string['viewdeleted'] = 'Page deleted';
$string['viewsubmitted'] = 'Page submitted';
$string['deletethisview'] = 'Delete this page';
$string['submitthisviewto1'] = 'Submit this page for assessment to';
$string['submitthiscollectionto1'] = 'Submit this collection for assessment to';
$string['forassessment1'] = 'Submit for assessment';
$string['accessfromdate3'] = 'Nobody can see this page before %s.';
$string['accessuntildate3'] = 'Nobody can see this page after %s.';
$string['accessbetweendates3'] = 'Nobody can see this page before %s or after %s.';
$string['artefactsinthisview'] = 'Artefacts in this page';
$string['whocanseethisview'] = 'Who can see this page';
$string['view'] = 'page';
$string['vieworcollection'] = 'page or collection';
$string['views'] = 'pages';
$string['viewsandcollections'] = 'pages and collections';
$string['View'] = 'Page';
$string['Views'] = 'Pages';
$string['viewsubmittedtogroup'] = 'This page has been submitted to <a href="%s">%s</a>.';
$string['viewsubmittedtogroupon'] = 'This page was submitted to <a href="%s">%s</a> on %s.';
$string['collectionsubmittedtogroup'] = 'This collection has been submitted to <a href="%s">%s</a>.';
$string['collectionsubmittedtogroupon'] = 'This collection was submitted to <a href="%s">%s</a> on %s.';
$string['submittedpendingrelease'] = 'Pending release after archiving.';
$string['nobodycanseethisview2'] = 'Only you can see this page.';
$string['noviews'] = 'No pages.';
$string['nviews'] = array(
    '1 page',
    '%s pages',
);
$string['youhavenoviews'] = 'You have no pages.';
$string['youhaventcreatedanyviewsyet'] = "You have not created any pages yet.";
$string['youhaveoneview'] = 'You have 1 page.';
$string['youhaveviews']   = 'You have %s pages.';
$string['viewsownedbygroup'] = 'Pages owned by this group';
$string['nogroupviewsyet'] = 'There are no pages in this group yet';
$string['viewssharedtogroup'] = 'Pages shared with this group';
$string['nosharedviewsyet'] = 'There are no pages shared with this group yet';
$string['viewssharedtogroupbyothers'] = 'Pages shared with this group by others';
$string['submissionstogroup'] = 'Submissions to this group';
$string['nosubmittedviewscollectionsyet'] = 'There are no pages or collections submitted to this group yet';
$string['submittogroup'] = 'Submit a page or collection to this group';
$string['yoursubmissions'] = 'You have submitted';
$string['youhavesubmitted'] = 'You have submitted <a href="%s">%s</a> to this group';
$string['youhavesubmittedon'] = 'You submitted <a href="%s">%s</a> to this group on %s';
$string['listedinpages'] = 'Listed in pages';

// access levels
$string['public'] = 'Public';
$string['registeredusers'] = 'Registered users';
$string['friends'] = 'Friends';
$string['groups'] = 'Groups';
$string['users'] = 'Users';
$string['everyoneingroup'] = 'Everyone in group';

// secret url
$string['token'] = 'Secret URL';
$string['editsecreturlaccess'] = 'Edit secret URL access';
$string['editsecreturlaccessfor'] = 'Edit secret URL access (ID %s)';
$string['newsecreturl'] = 'New secret URL';
$string['reallydeletesecreturl'] = 'Are you sure you want to delete this URL?';
$string['secreturldeleted'] = 'Your secret URL was deleted.';
$string['secreturlupdated'] = 'Secret URL updated';
$string['generatesecreturl'] = 'Generate a new secret URL for "%s".';
$string['secreturls'] = 'Secret URLs';
$string['existingURLS'] = 'Existing URLs';
$string['publicaccessnotallowed'] = "Your institution or site administrator has disabled public pages and secret URLs. Any secret URLs you see listed here are currently inactive.";
$string['publicaccessnotallowedforprobation'] = "Sorry, newly registered users aren't allowed to create secret URLs.";
// view user
$string['inviteusertojoingroup'] = 'Invite this user to join a group';
$string['addusertogroup'] = 'Add this user to a group';

// view view
$string['addedtowatchlist'] = 'This page has been added to your watchlist.';
$string['attachment'] = 'Attachment';
$string['removedfromwatchlist'] = 'This page has been removed from your watchlist.';
$string['addtowatchlist'] = 'Add page to watchlist';
$string['removefromwatchlist'] = 'Remove page from watchlist';
$string['addtowatchlistartefact'] = 'Add page "%s" to watchlist';
$string['removefromwatchlistartefact'] = 'Remove page "%s" from watchlist';
$string['alreadyinwatchlist'] = 'This page is already on your watchlist.';
$string['attachedfileaddedtofolder'] = "The attached file %s has been added to your '%s' folder.";
$string['date'] = 'Date';
$string['print'] = 'Print';
$string['viewobjectionableunmark'] = 'This page, or something within it, has been reported as containing objectionable content. If this is no longer the case, you can click the button to remove this notice and notify the other administrators.';
$string['viewunobjectionablesubject'] = 'Page %s was marked as not objectionable by %s.';
$string['viewunobjectionablebody'] = '%s has looked at %s by %s and marked it as no longer containing objectionable material.';
$string['updatewatchlistfailed'] = 'Update of watchlist failed';
$string['watchlistupdated'] = 'Your watchlist has been updated.';
$string['viewvisitcount'] = array(
    0 => '%d page visit from %s to %s',
    1 => '%d page visits from %s to %s',
);
$string['profilenotshared'] = 'Full access to this user profile is restricted.';

$string['friend'] = 'Friend';
$string['profileicon'] = 'Profile picture';

// general views stuff
$string['Added'] = 'Added';
$string['share'] = 'Share';
$string['sharedbyme'] = 'Shared by me';
$string['sharewith'] = 'Share with';
$string['whosharewith'] = 'Who do you want to share with?';
$string['accesslist'] = 'Access list';
$string['defaultaccesslistmessage'] = 'Nobody besides you can view your selected pages / collections. Add people to give them access.';
$string['sharewithmygroups'] = 'Share with my groups';
$string['sharewithmyinstitutions'] = 'Share with my institutions';
$string['sharewithusers'] = 'Share with users';
$string['shareview'] = 'Share page';
$string['otherusersandgroups'] = 'Share with other users and groups';
$string['moreoptions'] = 'Advanced options';
$string['moreinstitutions'] = 'More institutions';
$string['allviews'] = 'All pages';

$string['addaccess'] = 'Add access for "%s"';
$string['addaccessinstitution'] = 'Add access for institution "%s"';
$string['addaccessgroup'] = 'Add access for group "%s"';

$string['submitconfirm'] = 'If you submit \'%s\' to %s for assessment, you will not be able to edit its contents until your tutor has finished marking it. Are you sure you want to submit now?';
$string['viewsubmitted'] = 'Page submitted';
$string['collectionsubmitted'] = 'Collection submitted';
$string['collectionviewsalreadysubmitted'] = "Some pages in this collection have already been submitted: \"%s\"\nYou cannot submit the collection until they have been released, or removed from the collection.";
$string['viewalreadysubmitted'] = 'This page has already been submitted to another assignment or group.';
$string['collectionalreadysubmitted'] = 'This collection has already been submitted to another assignment or group.';
$string['collectionsubmissionexceptiontitle'] = 'Could not submit collection';
$string['collectionsubmissionexceptionmessage'] = 'This collection cannot be submitted for the following reason:';
$string['cantsubmitemptycollection'] = 'This collection does not contain any pages.';
$string['viewsubmissionexceptiontitle'] = 'Could not submit page';
$string['viewsubmissionexceptionmessage'] = 'This page cannot be submitted for the following reason:';
$string['submitviewtogroup'] = 'Submit \'%s\' to \'%s\' for assessment';
$string['cantsubmitviewtogroup'] = 'You cannot submit this page to this group for assessment.';
$string['cantsubmitcollectiontogroup'] = 'You cannot submit this collection.';
$string['cantsubmittogroup'] = 'You cannot submit to this group.';

$string['cantdeleteview'] = 'You cannot delete this page.';
$string['deletespecifiedview'] = 'Delete page "%s"';
$string['deleteviewconfirm1'] = 'Do you really want to delete this page? It cannot be undone.';
$string['deleteviewconfirmbackup'] = 'Please consider creating a backup of this page by <a href="%sexport/">exporting</a> it.';
$string['deleteviewconfirmnote3'] = '<strong>Note:</strong> All your files and journal entries that you linked in this page will still be available.<br/>However, any comments placed on this page will be deleted.';
$string['deleteviewconfirmnote2'] = 'This page is a part of the collection <a href="%s">"%s"</a>.';

$string['editaccesspagedescription6'] = 'You are the only one who can see your pages and collections by default. On this page you decide who can access them besides you.';
$string['editaccessdescription'] = 'You may set multiple items to have identical settings by choosing them from the checkboxes. Once you are done, scroll down and click "Save" to continue.';
$string['editaccessgrouppagedescription'] = 'By default, only those group members who can add and edit pages and collections can see group collections and pages. ' . $string['editaccessdescription'];
$string['editaccessinstitutionpagedescription'] = 'By default, only the administrators of your institution can see your institution collections and pages. ' . $string['editaccessdescription'];
$string['editaccesssitepagedescription'] = 'By default, only site administrators can see site collections and pages. ' . $string['editaccessdescription'];
$string['editsecreturlsintable'] = '<b>Secret URLs</b> cannot be set on this page as they must be generated individually. To set secret URLs, please return to the <a href="%s">list of collections and pages</a>.';
$string['editaccessinvalidviewset'] = 'Attempt to edit access on an invalid set of pages and collections.';

$string['overridingstartstopdate'] = 'Overriding start/stop dates';
$string['overridingstartstopdatesdescription'] = 'If you want, you can set an overriding start and/or stop date. Other people will not be able to see your page before the start date and after the end date regardless of any other access you have granted.';

$string['emptylabel'] = 'Click here to enter text for this label.';
$string['empty_block'] = 'Select an artefact from the tree on the left to place here.';

$string['viewinformationsaved'] = 'Page information saved successfully';

$string['canteditdontown'] = 'You cannot edit this page because you do not own it.';
$string['canteditsubmitted'] = 'You cannot edit this page because it has been submitted for assessment to "%s". You will have to wait until a tutor releases the page.';
$string['Submitted'] = 'Submitted';
$string['submittedforassessment'] = 'Submitted for assessment';
$string['blocknotinview'] = 'The block with ID "%d" is not in the page.';

$string['viewcreatedsuccessfully'] = 'Page created successfully';
$string['viewaccesseditedsuccessfully'] = 'Page access saved successfully';
$string['viewsavedsuccessfully'] = 'Page saved successfully';
$string['updatedaccessfornumviews1'] = array(
    'Access rules were updated for 1 page.',
    'Access rules were updated for %d pages.',
);

$string['invalidcolumn'] = 'Column %s out of range';

$string['confirmcancelcreatingview'] = 'This page has not been completed. Do you really want to cancel?';

$string['groupviewurltaken'] = 'A page with this URL already exists.';

// view control stuff

$string['editblockspagedescription'] = '<p>Drag and drop content blocks from the tabs below to create your page.</p>';
$string['displayview'] = 'Display page';
$string['editthisview'] = 'Edit this page';
$string['expandcontract'] = 'Expand /   Contract list of block types';

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

$string['blocksintructionnoajax'] = 'Select a block and choose where to add it to your page. You can position a block using the arrow buttons in its titlebar.';
$string['blocksinstructionajaxlive'] = 'This area shows a preview of what your page looks like. Changes are saved automatically.<br>Drag blocks onto the page to add them. You can then also drag them around the page to change their position.';

$string['addblock'] = 'Add block: %s';
$string['blockcell'] = 'Cell';
$string['cellposition'] = 'Row %s Column %s';
$string['blockorder'] = 'Position';
$string['blockordertopcell'] = 'Top of cell';
$string['blockorderafter'] = 'After "%s"';
$string['rownr'] = 'Row %s';
$string['nrrows'] = array(
    '%s row',
    '%s rows',
);

$string['addnewblockhere'] = 'Add new block here';
$string['add'] = 'Add';
$string['addcolumn'] = 'Add column';
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
$string['moveblock'] = 'Move %s block';
$string['movethisblock'] = 'Move this block';
$string['Configure'] = 'Configure';
$string['configureblock1'] = 'Configure %s block (ID %s)';
$string['configurethisblock1'] = 'Configure this block (ID %s)';
$string['closeconfiguration'] = 'Close configuration';
$string['removeblock1'] = 'Remove %s block (ID %s)';
$string['removethisblock1'] = 'Remove this block (ID %s)';
$string['blocktitle'] = 'Block title';

$string['changemyviewlayout'] = 'Change my page layout';
$string['createcustomlayout'] = 'Create custom layout';
$string['createnewlayout'] = 'Create new layout';
$string['basicoptions'] = 'Basic options';
$string['advancedoptions'] = 'Advanced options';
$string['viewcolumnspagedescription'] = 'First, select the number of columns in your page. In the next step, you will be able to change the widths of the columns.';
$string['viewlayoutpagedescription'] = 'Select how you would like your page to be laid out.';
$string['changeviewlayout'] = 'Change my page\'s column layout';
$string['viewlayoutchanged'] = 'Page layout changed';
$string['numberofcolumns'] = 'Number of columns';
$string['changecolumnlayoutfailed'] = 'Could not change the column layout. Someone else may have been editing the layout at the same time. Please try again later.';
$string['changerowlayoutfailed'] = 'Could not change the row layout. Someone else may have been editing the layout at the same time. Please try again later.';
$string['Row'] = 'Row';
$string['addarow'] = 'Add a row';
$string['removethisrow'] = 'Remove this row';
$string['columnlayout'] = 'Column layout';
$string['layoutpreview'] = 'Layout preview';
$string['layoutpreviewimage'] = 'Layout preview image';
$string['Help'] = 'Help';

$string['by'] = 'by';
$string['viewtitleby'] = '%s by <a href="%s">%s</a>';
$string['viewauthor'] = 'by <a href="%s">%s</a>';
$string['in'] = 'in';
$string['noblocks'] = 'Sorry, no blocks in this category.';
$string['timeofsubmission'] = 'Time of submission';

$string['column'] = 'column';
$string['row'] = 'row';
$string['columns'] = 'columns';
$string['rows'] = 'rows';
$string['100'] = '100';
$string['50,50'] = '50-50';
$string['33,33,33'] = '33-33-33';
$string['25,25,25,25'] = '25-25-25-25';
$string['20,20,20,20,20'] = '20-20-20-20-20';
$string['67,33'] = '67-33';
$string['33,67'] = '33-67';
$string['25,25,50'] = '25-25-50';
$string['50,25,25'] = '50-25-25';
$string['25,50,25'] = '25-50-25';
$string['15,70,15'] = '15-70-15';
$string['20,30,30,20'] = '20-30-30-20';
$string['noviewlayouts'] = 'There are no layouts for a %s column page.';
$string['cantaddcolumn'] = 'You cannot add any more columns to this page.';
$string['cantremovecolumn'] = 'You cannot remove the last column from this page.';

$string['blocktypecategory.external'] = 'External';
$string['blocktypecategory.fileimagevideo'] = 'Media';
$string['blocktypecategory.general'] = 'General';
$string['blocktypecategory.internal'] = 'Personal info';
$string['blocktypecategorydesc.external'] = 'Click for external options';
$string['blocktypecategorydesc.fileimagevideo'] = 'Click for media options';
$string['blocktypecategorydesc.general'] = 'Click for general options';
$string['blocktypecategorydesc.internal'] = 'Click for personal info options';
$string['blocktypecategorydesc.blog'] = 'Click for journal options';

$string['notitle'] = 'No title';
$string['clickformoreinformation1'] = 'Click for more information and to add a comment.';
$string['detailslinkalt'] = 'Details';

$string['Browse'] = 'Browse';
$string['Search'] = 'Search';
$string['noartefactstochoosefrom'] = 'Sorry, no artefacts to choose from';

$string['access'] = 'Access';
$string['noaccesstoview'] = 'You do not have permission to access this page.';

$string['changeviewtheme'] = 'The theme you have chosen for this page is no longer available to you. Please select a different theme.';
$string['nothemeselected1'] = 'Use institution theme';

// Templates
$string['Template'] = 'Template';
$string['allowcopying'] = 'Allow copying';
$string['retainviewrights1'] = 'Retain view access on copied pages or collections';
$string['templatedescriptionplural2'] = 'If people have access to your selected pages / collections, they can make their own copies.';
$string['retainviewrightsdescription2'] = 'Add access for you to view copies of the selected pages / collections that are copied by other users. Those users can revoke this access later on if they wish. Pages that are copied from a copy of this page or collection will not have this same access.';
$string['retainviewrightsgroupdescription2'] = 'Add access for members of this group to view copies of the selected pages / collections that are copied by other users. Those users can revoke this access later on if they wish. Pages that are copied from a copy of this page or collection will not have this same access.';
$string['choosetemplatepageandcollectiondescription'] = '<p>Here you can search through the pages that you are allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p>';
$string['choosetemplategrouppageandcollectiondescription'] = '<p>Here you can search through the pages that this group is allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p><p><strong>Note:</strong> Groups cannot currently make copies of journals, journal entries, plans and résumé information.</p>';
$string['choosetemplateinstitutionpageandcollectiondescription'] = '<p>Here you can search through the pages that this institution is allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p><p><strong>Note:</strong> Institutions cannot currently make copies of journals, journal entries, plans and résumé information.</p>';
$string['choosetemplatesitepageandcollectiondescription1'] = '<p>Here you can search through the pages that can be copied on the site level as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p><p><strong>Note:</strong> Currently, it is not possible to have copies of journals, journal entries, plans and résumé information in site-level pages.</p>';
$string['copiedblocksandartefactsfromtemplate'] = 'Copied %d blocks and %d artefacts from %s';
$string['filescopiedfromviewtemplate'] = 'Files copied from %s';
$string['viewfilesdirname'] = 'viewfiles';
$string['viewfilesdirdesc'] = 'Files from copied pages';
$string['thisviewmaybecopied'] = 'Copying is allowed';
$string['thisviewmaynotbecopied'] = 'Copying is not allowed';
$string['copythisview'] = 'Copy this page';
$string['copyview'] = 'Copy page';
$string['createemptyview'] = 'Create empty page';
$string['copyaview'] = 'Copy a page';
$string['copyvieworcollection'] = 'Copy a page or collection';
$string['confirmcopytitle'] = 'Confirm copying';
$string['confirmcopydesc'] = 'Please choose which you would like to copy:';
$string['Untitled'] = 'Untitled';
$string['copyfornewusers'] = 'Copy for new users';
$string['copyfornewusersdescription2'] = 'Whenever a new user is created, automatically make a personal copy of the selected pages / collections in the user\'s account. If you want these users to be able to copy the selected pages / collections later on as well, please allow copying in general.';
$string['copyfornewmembers'] = 'Copy for new institution members';
$string['copyfornewmembersdescription2'] = 'Automatically make a personal copy of the selected pages / collections for all new members of %s. If you want these users to be able to copy the selected pages / collections later on as well, please allow copying in general.';
$string['copyfornewgroups'] = 'Copy for new groups';
$string['copyfornewgroupsdescription1'] = 'Make a copy of the selected pages / collections in all new groups with these roles:';
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
$string['viewswithretainviewrightsmustbecopyable'] = 'You must allow copying before you can set a page to retain view access.';
$string['viewscopiedfornewgroupsmustbecopyable'] = 'You must allow copying before you can set a page to be copied for new groups.';
$string['copynewusergroupneedsloggedinaccess'] = 'Pages copied for new users or groups must give access to logged-in users.';
$string['viewcopywouldexceedquota'] = 'Copying this page would exceed your file quota.';
$string['viewcreatewouldexceedquota'] = 'Creating this page would exceed your file quota.';

$string['blockcopypermission'] = 'Block copy permission';
$string['blockcopypermissiondesc'] = 'If you allow other users to copy this page, you may choose how this block will be copied';

// Sort by
$string['defaultsort'] = '';
$string['latestcreated'] = 'Date created';
$string['latestmodified'] = 'Last modified';
$string['latestviewed'] = 'Last viewed';
$string['mostvisited'] = 'Most visited';
$string['mostcomments1'] = 'Most comments';

// View types
$string['dashboard'] = 'Dashboard';
$string['profile'] = 'Profile';
$string['portfolio'] = 'Portfolio';
$string['grouphomepage'] = 'Group homepage';

$string['grouphomepagedescription'] = 'The group homepage contains the content that appears on the "About" tab for this group';

// Shared views
$string['sharedwithme'] = 'Shared with me';
$string['titleanddescription'] = 'Title, description, tags';
$string['titleanddescriptionandtagsandowner'] = 'Title, description, tags, owner';
$string['tagsonly'] = 'Tags only';
$string['sharedviewsdescription'] = 'This page lists the most recently modified or commented on pages that have been shared with you. They may have been shared with you directly, shared with friends of the owner, or shared with one of your groups.';
$string['sharedwith'] = 'Shared with';
$string['sharewith'] = 'Share with';
$string['general'] = 'General';
$string['searchfor'] = 'Search for...';
$string['institutions'] = 'Institutions';
$string['groups'] = 'Groups';
$string['search'] = 'Search';
$string['Me'] = 'Me';
$string['entersearchquery'] = 'Enter search query';
$string['allow'] = 'Allow';
$string['comments'] = 'Comments';
$string['moderate'] = 'Moderate';

// Group reports
$string['sharedby'] = 'Shared by';

// Retractable blocks
$string['retractable'] = 'Retractable';
$string['retractabledescription'] = 'Select to allow this block to be retracted when the header is clicked.';
$string['retractedonload'] = 'Automatically retract';
$string['retractedonloaddescription'] = 'Select to automatically retract this block.';

// Artefact chooser panel
$string['textbox1'] = 'Note';
$string['image'] = 'Image';
$string['addcontent'] = 'Add Content';
$string['theme'] = 'Theme';
