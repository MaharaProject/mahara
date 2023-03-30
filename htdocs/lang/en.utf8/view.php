<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['basics']                 = 'Basics';
$string['createview']             = 'Create page';
$string['edittitle']              = 'Edit title';
$string['edittitleanddescription'] = 'Edit title and description';
$string['editcontent1']            = 'Edit';
$string['editcontentandlayout']   = 'Edit content and layout';
$string['editlayout']             = 'Edit layout';
$string['editaccess']             = 'Edit access';
$string['editaccessfor']          = 'Edit access (ID %s)';
$string['layout']                 = 'Layout';
$string['manageaccess']           = 'Manage access';
$string['manageaccessfor']        = 'Manage access for "%s"';
$string['managekeys']             = 'Manage secret URLs';
$string['managekeysfor']          = 'Manage secret URLs for "%s"';
$string['accessrulesfor']         = 'Access rules for "%s"';
$string['next']                   = 'Next';
$string['back']                   = 'Back';
$string['title']                  = 'Page title';
$string['undo']                   = 'Undo';
$string['viewurl']                = 'Page URL';
$string['viewurldescription']     = 'A readable URL for your page. This field must be between 3 and 100 characters long.';
$string['userviewurltaken']       = 'This URL is already taken. Please choose another one.';
$string['description']            = 'Page description';
$string['settings']                = 'Settings';
$string['startdate']              = 'Access start date/time';
$string['stopdate']               = 'Access end date/time';
$string['skin']                   = 'Skin';
$string['overrideconflict'] = 'One or more access permissions are in conflict with the overriding dates. These access permissions will not be valid outside the overriding dates.';
$string['pagepartofcollection']   = 'Your page is part of the collection \'%s\'. The permissions you set on this page will be applied to the entire collection.';
$string['stopdatecannotbeinpast1'] = '"To" date cannot be in the past';
$string['startdatemustbebeforestopdate'] = 'Start date must be before stop date';
$string['newstopdatecannotbeinpast'] = 'The end date for \'%s\' access cannot be in the past.';
$string['newstartdatemustbebeforestopdate'] = 'The start date for \'%s\' access must be before the end date.';
$string['unrecogniseddateformat'] = 'Unrecognised date format';
$string['allowcommentsonview1']    = 'Allow people to leave comments.';
$string['ownerformat']            = 'Name display format';
$string['ownerformatdescription'] = 'How do you want people who look at your page to see your name?';
$string['createtags']             = 'Create via tags';
$string['createtagsdesc1']        = 'Search for or enter tags to pull content into your page automatically. If you enter more than one tag, only content that is tagged with all these tags will appear on the page. You can then re-arrange and delete blocks.';
$string['anonymise']              = 'Anonymise';
$string['anonymisedescription']   = 'Hide your name as the author of the page from others. Administrators and staff will still be able to see your name if they so desire.';
$string['Locked']                 = 'Locked';
$string['lockedgroupviewdesc']    = 'If you lock this page, only group administrators will be able to edit it.';
$string['profileviewtitle']       = 'Profile page';
$string['dashboardviewtitle']     = 'Dashboard page';
$string['grouphomepageviewtitle'] = 'Group homepage';
$string['viewname']               = 'Page name';
$string['templatedashboard']      = 'Dashboard template';
$string['templategrouphomepage']  = 'Group homepage template';
$string['templateprofile']        = 'Profile template';
$string['templateprogress']       = 'Portfolio completion template';
$string['templateportfolio']      = 'Page template';
$string['templateportfoliotitle']       = 'Untitled';
$string['templateportfoliodescription1'] = 'Set up the default layout for the pages that are created. You can also add blocks. Please note that any content you add to the default page will appear on every page that is created after you made the change.';
$string['templateactivity']      = 'Activity page template';

// my views
$string['artefacts'] = 'Artefacts';
$string['groupviews1'] = 'Group portfolios';
$string['institutionviews'] = 'Institution pages';
$string['institutionviewscollections1'] = 'Institution portfolios';
$string['reallyaddaccesstoemptyview'] = 'Your page contains no blocks. Do you really want to give these people access to the page?';
$string['viewdeleted'] = 'Page deleted';
$string['deletethisview'] = 'Delete this page';
$string['submitthisviewto1'] = 'Submit this page for assessment to';
$string['submitthiscollectionto1'] = 'Submit this collection for assessment to';
$string['forassessment1'] = 'Submit for assessment';
$string['accessfromdate3'] = 'Nobody can see this page before %s.';
$string['accessuntildate3'] = 'Nobody can see this page after %s.';
$string['accessbetweendates3'] = 'Nobody can see this page before %s or after %s.';
$string['artefactsinthisview'] = 'Artefacts in this page';
$string['whocanseethisview'] = 'Who can see this page';
$string['pending'] = 'Portfolio under review';
$string['panelmenu'] = 'Menu';
$string['view'] = 'page';
$string['views'] = 'pages';
$string['nviews1'] = array(
    '%s page',
    '%s pages'
);
$string['View'] = 'Page';
$string['Views'] = 'Pages';
$string['portfolio'] = 'portfolio';
$string['portfolios'] = 'portfolios';
$string['nportfolios'] = array(
    '%s portfolio',
    '%s portfolios'
);
$string['Viewscollections1'] = 'Portfolios';
$string['viewsubmittedtogroup1'] = 'This portfolio has been submitted to <a href="%s">%s</a>.';
$string['viewsubmittedtogroupon1'] = 'This portfolio was submitted to <a href="%s">%s</a> on %s.';
$string['viewsubmittedtogroupgrade'] = 'This portfolio was submitted to the assignment <a href="%s">"%s"</a> in "%s" on %s.';
$string['viewsubmittedtohoston'] = 'This portfolio was submitted on %s.';
$string['viewsubmittedtohost'] = 'This portfolio has been submitted for assessment.';
$string['collectionsubmittedtogroup'] = 'This collection has been submitted to <a href="%s">%s</a>.';
$string['collectionsubmittedtogroupon'] = 'This collection was submitted to <a href="%s">%s</a> on %s.';
$string['collectionsubmittedtogroupgrade'] = 'This collection was submitted to the assignment <a href="%s">"%s"</a> in "%s" on %s.';
$string['collectionsubmittedtohost'] = 'This collection has been submitted for assessment.';
$string['collectionsubmittedtohoston'] = 'This collection was submitted on %s.';
$string['submittedpendingrelease'] = 'Pending release after archiving.';
$string['submittedpendingreleasefailed'] = 'Release failed to archive.<br>Go to <a href="%s">Export queue</a> to re-queue.';
$string['submittedstatus'] = 'Submission status';
$string['nobodycanseethisview2'] = 'Only you can see this page.';
$string['noviews2'] = 'No portfolios';
$string['youhavenoviews2'] = 'You don\'t have any portfolios.';
$string['youhaventcreatedanyviewsyet'] = "You have not created any pages yet.";
$string['youhavenviews'] = array(
    'You have 1 page.',
    'You have %d pages.',
);
$string['viewsownedbygroup'] = 'Pages owned by this group';
$string['ownedbygroup'] = 'Owned by this group';
$string['nogroupviewsyet'] = 'There are no pages in this group yet';
$string['viewscollectionssharedtogroup1'] = 'Portfolios shared with this group';
$string['viewssharedtogroup'] = 'Pages shared with this group';
$string['sharedtogroup'] = 'Shared with this group';
$string['nosharedviewsyet'] = 'There are no pages shared with this group yet';
$string['viewssharedtogroupbyothers'] = 'Pages shared with this group by others';
$string['sharedviews'] = 'Shared pages';
$string['submissionstogroup'] = 'Submissions to this group';
$string['viewsourceportfolio'] = 'Original portfolio \'%s\'';
$string['originalsubmissiondeleted'] = 'Original portfolio deleted';
$string['showsubmissions'] = 'Show submitted portfolios';
$string['nosubmittedviewscollectionsyet1'] = 'There are no portfolios submitted to this group yet';
$string['nosubmissionsfrom'] = 'Members without a submission to the group';
$string['submittogroup1'] = 'Submit a portfolio to this group';
$string['yoursubmissions'] = 'You have submitted';
$string['youhavesubmitted'] = 'You have submitted <a href="%s">%s</a> to this group';
$string['youhavesubmittedon'] = 'You submitted <a href="%s">%s</a> to this group on %s';
$string['listedinpages'] = 'Listed in pages';

// access levels
$string['public'] = 'Public';
$string['registeredusers'] = 'Registered people';
$string['friends'] = 'Friends';
$string['users'] = 'People';
$string['friend'] = 'Friend';
$string['group'] = 'Group';
$string['user'] = 'Person';
$string['everyoneingroup'] = 'Everyone in group';
$string['nospecialrole'] = 'No special role';
$string['peer'] = 'Peer';
$string['manager'] = 'Manager';
$string['peermanager'] = 'Peer and manager';
$string['verifier'] = 'Reviewer';

// Secret url
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
$string['publicaccessnotallowedforprobation'] = "Sorry, newly registered people aren't allowed to create secret URLs.";
$string['copyingsecreturl'] = 'Copied secret URL to clipboard';

// view user
$string['inviteusertojoingroup'] = 'Invite this person to join a group';
$string['addusertogroup'] = 'Add this person to a group';

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
$string['profilenotshared'] = 'Full access to this profile is restricted.';

$string['profileicon'] = 'Profile picture';
$string['Updatedon'] = 'Updated on';
$string['Createdon'] = 'Created on';

// general views stuff
$string['Added'] = 'Added';
$string['share'] = 'Share';
$string['sharedbyme'] = 'Shared by me';
$string['whosharewith'] = 'Who do you want to share with?';
$string['accesslist'] = 'Access list';
$string['defaultaccesslistmessage1'] = 'Nobody besides you can view your selected portfolios. Add people to give them access.';
$string['sharewithmygroups'] = 'Share with my groups';
$string['sharewithmyinstitutions'] = 'Share with my institutions';
$string['sharewithusers'] = 'Share with people';
$string['shareview1'] = 'Share';
$string['sharedwithothers'] = 'Share with others';
$string['moreoptions'] = 'Advanced options';
$string['moreinstitutions'] = 'More institutions';
$string['allviews'] = 'All pages';
$string['peopleinmyinstitution'] = "People in \"%s\"";
$string['peopleinmyinstitutions'] = 'People in my institutions';
$string['otherpeople'] = 'Other people';
$string['addaccess'] = 'Add access for "%s"';
$string['addaccessinstitution'] = 'Add access for institution "%s"';
$string['addaccessgroup'] = 'Add access for group "%s"';
$string['sharewithmaximum'] = array(
    'You can share the portfolio with up to 1 person or group of people.',
    'You can share the portfolio with up to %d people or groups of people.'
);
$string['shareallwithmaximum'] = array(
    'You can share the selected portfolios with up to 1 person or group of people.',
    'You can share the selected portfolios with up to %d people or groups of people.'
);
$string['submitconfirm1'] = 'If you submit \'%s\' to \'%s\', a copy will be made and submitted for assessment. You can continue editing your original portfolio. However, you will not be able to edit the submitted copy until it is released. Are you sure you want to submit your portfolio now?';
$string['portfoliosubmitted'] = 'Portfolio submitted. You can <a href="%s">view your submission</a>.';
$string['viewnotsubmitted'] = 'Portfolio not currently submitted';
$string['collectionviewsalreadysubmitted'] = "Some pages in this collection have already been submitted: \"%s\"\nYou cannot submit the collection until they have been released, or removed from the collection.";
$string['viewalreadysubmitted'] = 'This page has already been submitted to another assignment or group.';
$string['collectionalreadysubmitted'] = 'This collection has already been submitted to another assignment or group.';
$string['collectionsubmissionexceptiontitle'] = 'Could not submit collection';
$string['collectionsubmissionexceptionmessage'] = 'This collection cannot be submitted for the following reason:';
$string['cantsubmitcopyfailed'] = 'A copy of this portfolio could not be created. No submission has been made.';
$string['cantsubmitemptycollection'] = 'This collection does not contain any pages.';
$string['cantsubmitneedgrouporsubmittedhost'] = 'Submissions need to be made to a group or to a submitted host.';
$string['submittedtimetitle'] = '; submitted at %s';
$string['viewsubmissionexceptiontitle'] = 'Could not submit page';
$string['viewsubmissionexceptionmessage'] = 'This page cannot be submitted for the following reason:';
$string['submitviewtogroup'] = 'Submit \'%s\' to \'%s\' for assessment';
$string['cantsubmitviewtogroup'] = 'You cannot submit this page to this group for assessment.';
$string['cantsubmitcollectiontogroup'] = 'You cannot submit this collection.';
$string['cantsubmittogroup'] = 'You cannot submit to this group.';

$string['cantdeleteview'] = 'You cannot delete this page.';
$string['cantdeleteviewsubmission'] = 'You cannot delete this page while it is a submission.';
$string['deletespecifiedview'] = 'Delete page "%s"';
$string['deleteviewconfirm1'] = 'Do you really want to delete this page? It cannot be undone.';
$string['deleteviewconfirmbackup1'] = 'Please consider creating a backup of this page by <a href="%sexport/index.php?view=%s">exporting</a> it.';
$string['deleteviewconfirmnote3'] = '<strong>Note:</strong> All your files and journal entries that you linked in this page will still be available.<br/>However, any comments placed on this page will be deleted.';
$string['deleteviewconfirmnote2'] = 'This page is a part of the collection <a href="%s">"%s"</a>.';

$string['editaccesspagedescription7'] = 'You are the only one who can see your portfolios by default. On this page you decide who can access them besides you.';
$string['editaccessdescription'] = 'You may set multiple items to have identical settings by choosing them from the checkboxes. Once you are done, scroll down and click "Save" to continue.';
$string['editaccessgrouppagedescription1'] = 'By default, only those group members who can add and edit portfolios can see group portfolios. ' . $string['editaccessdescription'];
$string['editaccessinstitutionpagedescription'] = 'By default, only the administrators of your institution can see your institution collections and pages. ' . $string['editaccessdescription'];
$string['editaccesssitepagedescription'] = 'By default, only site administrators can see site collections and pages. ' . $string['editaccessdescription'];
$string['editsecreturlsintable'] = '<strong>Secret URLs</strong> cannot be set on this page as they must be generated individually. To set secret URLs, please return to the <a href="%s">list of collections and pages</a>.';
$string['editaccessinvalidviewset1'] = 'Attempt to edit access on an invalid set of portfolios.';

$string['overridingstartstopdate'] = 'Overriding start/stop dates';
$string['overridingstartstopdatesdescription'] = 'If you want, you can set an overriding start and/or stop date. Other people will not be able to see your page before the start date and after the end date regardless of any other access you have granted.';

$string['emptylabel'] = 'Click here to enter text for this label.';
$string['empty_block'] = 'Select an artefact from the tree on the left to place here.';

$string['viewinformationsaved'] = 'Page information saved successfully';

$string['canteditdontown'] = 'You cannot edit this page because you do not own it.';
$string['canteditsubmitted'] = 'You cannot edit this page because it has been submitted for assessment to "%s". You will have to wait until a tutor releases the page.';
$string['canteditsubmission'] = 'You cannot edit this page while it is a submission.';
$string['notsubmitted'] = 'Not submitted';
$string['Submitted'] = 'Submitted';
$string['archiving'] = 'Archiving';
$string['released'] = 'Released';
$string['submittedforassessment'] = 'Submitted for assessment';
$string['blocknotinview'] = 'The block with ID "%d" is not in the page.';

$string['viewcreatedsuccessfully'] = 'Page created successfully';
$string['viewaccesseditedsuccessfully'] = 'Page access saved successfully';
$string['accesssavedsuccessfully'] = 'Access settings saved successfully';
$string['viewsavedsuccessfully'] = 'Page saved successfully';
$string['savedtotimeline'] = 'Saved to timeline';
$string['updatedaccessfornumviews1'] = array(
    'Access rules were updated for 1 page.',
    'Access rules were updated for %d pages.',
);

$string['cantversionviewinvalid'] = 'The supplied page identifier is invalid.';
$string['cantversionvieweditpermissions'] = 'You do not have permission to edit this page.';
$string['cantversionviewsubmitted'] = 'You cannot edit this page because it has been submitted for assessment. You will have to wait until it is released.';
$string['cantversionviewgroupeditwindow'] = 'You cannot edit this page. It is outside of the group editability time frame.';
$string['cantversionoldlayout'] = 'You cannot save a timeline version of a page with an old layout. You need to convert it to the new layout. To do this, edit the page and the layout will be converted.';
$string['cantaddannotationinoldlayout'] = 'You cannot add an annotation to this page. Please convert the page layout by editing the page first.';

$string['invalidcolumn'] = 'Column %s out of range';

$string['confirmcancelcreatingview'] = 'This page has not been completed. Do you really want to cancel?';

$string['groupviewurltaken'] = 'A page with this URL already exists.';

// view control stuff

$string['editblockspagedescription'] = '<p>Drag and drop content blocks from the buttons below to create your page.</p>';
$string['displayview'] = 'Display page';
$string['editthisview'] = 'Edit';
$string['expandcontract'] = 'Expand / Contract the list of block types';
$string['returntoviews1'] = 'Return to portfolios';
$string['returntoinstitutionportfolios1'] = 'Return to institution portfolios';
$string['returntositeportfolios1'] = 'Return to site portfolios';

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

$string['confirmcloseblockinstance'] = 'Are you sure you want to continue without saving your changes?';
$string['confirmdeleteblockinstance'] = 'Are you sure you wish to delete this block?';
$string['blockinstanceconfiguredsuccessfully'] = 'Block configured successfully';
$string['blockconfigurationrenderingerror'] = 'Configuration failed because the block could not be rendered.';

$string['blocksinstructionajaxlive2'] = 'This is a preview of your page. Changes are saved automatically.<br>Drag the \'Plus\' button onto the page to create a new block. Choose what type of block it will be. Drag blocks around the page to change their position.';
$string['blockchangedsuccess'] = "Changed the placeholder block to a '%s' block successfully.";
$string['blockchangederror'] = "Changing the block to a '%s' block failed.";
$string['blockchangedbacksuccess'] = "Changed the block back to a placeholder block.";
$string['blockchangedbackerror'] = "Changing the block back to a placeholder block failed.";

$string['addblock'] = 'Add block: %s';
$string['blockcell'] = 'Cell';
$string['blockorder'] = 'Position';
$string['rownr'] = 'Row %s';
$string['nrrows'] = array(
    '%s row',
    '%s rows',
);
$string['addnewblockdrag'] = 'Drag to add a new block';
$string['addnewblockaccessible'] = 'Click to add a new block';
$string['addnewblock'] = 'Add new block';
$string['addnewblockhere'] = 'Add new block here';
$string['add'] = 'Add';
$string['addcolumn'] = 'Add column';
$string['remove'] = 'Remove';
$string['removecolumn'] = 'Remove this column';
$string['moveblock2'] = 'Move block';
$string['moveblock'] = 'Move %s block';
$string['movethisblock'] = 'Move this block';
$string['Configure1'] = 'Edit';
$string['configureblock3'] = 'Edit block';
$string['configureblock1'] = 'Configure %s block (ID %s)';
$string['configurethisblock1'] = 'Configure this block (ID %s)';
$string['closeconfiguration'] = 'Close configuration';
$string['removeblock2'] = 'Remove block';
$string['removeblock1'] = 'Remove %s block (ID %s)';
$string['removethisblock1'] = 'Remove this block (ID %s)';
$string['blocktitle'] = 'Block title';
$string['celltitle'] = 'Cell';

$string['basicoptions'] = 'Basic options';
$string['advancedoptions'] = 'Advanced options';
$string['Row'] = 'Row';

$string['layoutpreviewimage'] = 'Layout preview image';
$string['Help'] = 'Help';
$string['blockhelp'] = 'Block help';

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

$string['blocktypecategory.external'] = 'External';
$string['blocktypecategory.fileimagevideo'] = 'Media';
$string['blocktypecategory.general'] = 'General';
$string['blocktypecategory.internal'] = 'Personal info';
$string['blocktypecategorydesc.external'] = 'Click for external options';
$string['blocktypecategorydesc.fileimagevideo'] = 'Click for media options';
$string['blocktypecategorydesc.general'] = 'Click for general options';
$string['blocktypecategorydesc.internal'] = 'Click for personal info options';
$string['blocktypecategorydesc.blog'] = 'Click for journal options';

$string['draft'] = 'Draft';
$string['drafttextblockdescription'] = 'Save the block as draft if you don\'t want the text to be visible to anybody. Once the text is published, it can\'t be set to draft again.';

$string['notitle'] = 'No title';
$string['clickformoreinformation1'] = 'Click for more information and to add a comment.';
$string['detailslinkalt'] = 'Details';

$string['Browse'] = 'Browse';
$string['Search'] = 'Search';
$string['noartefactstochoosefrom'] = 'Sorry, no artefacts to choose from';

$string['access'] = 'Access';
$string['noaccesstoview'] = 'You do not have permission to access this page.';
$string['wrongblocktype'] = 'The ID supplied is not for a valid blocktype.';
$string['changeviewtheme'] = 'The theme you have chosen for this page is no longer available to you. Please select a different theme.';
$string['nothemeselected1'] = 'Use institution theme';
$string['usesitetheme'] = 'Use site theme';
$string['quickedit'] = 'Quick edit';

// Templates
$string['Template'] = 'Template';
$string['allowcopying'] = 'Allow copying';
$string['retainviewrights2'] = 'Retain view access on copied portfolios';
$string['templatedescriptionplural3'] = 'If people have access to your selected portfolios, they can make their own copies.';
$string['retainviewrightsdescription3'] = 'Add access for you to view copies of the selected portfolios that are copied by others. They can revoke this access later on if they wish. Portfolios that are created from a copy of this portfolio will not have this same access.';
$string['retainviewrightsgroupdescription3'] = 'Add access for members of this group to view copies of the selected portfolios that are copied by others. They can revoke this access later on if they wish. Portfolios that are created from a copy of this portfolio will not have this same access.';
$string['choosetemplatepageandcollectiondescription'] = '<p>Here you can search through the pages that you are allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p>';
$string['choosetemplategrouppageandcollectiondescription'] = '<p>Here you can search through the pages that this group is allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p><p><strong>Note:</strong> Groups cannot currently make copies of journals, journal entries, plans and résumé information.</p>';
$string['choosetemplateinstitutionpageandcollectiondescription'] = '<p>Here you can search through the pages that this institution is allowed to copy as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p><p><strong>Note:</strong> Institutions cannot currently make copies of journals, journal entries, plans and résumé information.</p>';
$string['choosetemplatesitepageandcollectiondescription1'] = '<p>Here you can search through the pages that can be copied on the site level as a starting point for making a new page. You can see a preview of each page by clicking on its name. Once you have found the page you wish to copy, click the corresponding "Copy page" button to make a copy and begin customising it. You may also choose to copy the entire collection that the page belongs to by clicking the corresponding "Copy collection" button.</p><p><strong>Note:</strong> Currently, it is not possible to have copies of journals, journal entries, plans and résumé information in site-level pages.</p>';

$string['filescopiedfromviewtemplate'] = 'Files copied from %s';
$string['viewfilesdirname'] = 'viewfiles';
$string['viewfilesdirdesc'] = 'Files from copied pages';
$string['thisviewmaybecopied'] = 'Copying is allowed';
$string['thisviewmaynotbecopied'] = 'Copying is not allowed';
$string['copythisportfolio'] = 'Copy this portfolio';
$string['copyview'] = 'Copy page';
$string['createemptyview'] = 'Create empty page';
$string['copyaview'] = 'Copy a page';
$string['copyvieworcollection1'] = 'Copy a portfolio';
$string['confirmaddtitle1'] = 'Create a portfolio';
$string['confirmadddesc'] = 'Please choose which you would like to create:';
$string['confirmcopytitle'] = 'Confirm copying';
$string['confirmcopydesc'] = 'Please choose which you would like to copy:';
$string['Untitled'] = 'Untitled';
$string['copyforexistingmembersprogress'] = 'Copying portfolios for existing group members';
$string['existinggroupmembercopy'] = 'Copy for existing group members';
$string['existinggroupmembercopydesc2'] = 'Copy the selected portfolios to the personal portfolio area of all existing group members. The slide switch resets after saving. Group members will only get a copy once.';
$string['copyfornewusers'] = 'Copy into new accounts';
$string['copyfornewusersdescription3'] = 'Whenever a new account is created, automatically make a personal copy of the selected portfolios in the new account. If you want these people to be able to copy the selected portfolios later on as well, please allow copying in general.';
$string['copyfornewmembers'] = 'Copy for new institution members';
$string['copyfornewmembersdescription3'] = 'Automatically make a personal copy of the selected portfolios for all new members of %s. If you want these people to be able to copy the selected portfolios later on as well, please allow copying in general.';
$string['copyfornewgroups'] = 'Copy for new groups';
$string['copyfornewgroupsdescription2'] = 'Make a copy of the selected portfolios in all new groups with these roles:';
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
$string['viewscopiedfornewusersmustbecopyable'] = 'You must allow copying before you can set a page to be copied for new accounts.';
$string['viewswithretainviewrightsmustbecopyable'] = 'You must allow copying before you can set a page to retain view access.';
$string['viewscopiedfornewgroupsmustbecopyable'] = 'You must allow copying before you can set a page to be copied for new groups.';
$string['copynewusergroupneedsloggedinaccess'] = 'Pages copied for new accounts or groups must give access to registered people.';
$string['viewcopywouldexceedquota'] = 'Copying this page would exceed your file quota.';
$string['viewcreatewouldexceedquota'] = 'Creating this page would exceed your file quota.';

$string['blockcopypermission'] = 'Block copy permission';
$string['blockcopypermissiondesc'] = 'If you allow other people to copy this page, you may choose how this block will be copied.';

// Sort by
$string['defaultsort'] = 'Alphabetical';
$string['latestcreated'] = 'Date created';
$string['latestmodified'] = 'Last modified';
$string['latestviewed'] = 'Last viewed';
$string['mostvisited'] = 'Most visited';
$string['mostcomments1'] = 'Most comments';

// View types
$string['dashboard'] = 'Dashboard';
$string['Profile'] = 'Profile';
$string['profile'] = 'profile'; // for stats
$string['Portfolio'] = 'Portfolio';
$string['Portfolios'] = 'Portfolios';
$string['Pages'] = 'Pages';
$string['Collection'] = 'Collection';
$string['Grouphomepage'] = 'Group homepage';
$string['grouphomepage'] = 'group homepage'; // for stats
$string['Progress'] = 'Portfolio completion'; // for stats
$string['progress'] = 'Portfolio completion'; // for stats
$string['grouphomepagedescription'] = 'The group homepage contains the content that appears on the "About" tab for this group';
$string['pageaccessrules'] = 'Page access rules';

// Shared views
$string['sharedwithme'] = 'Shared with me';
$string['sharedwithellipsis'] = 'Shared with...';
$string['sharedwithdescription'] = 'Select which portfolios from other people you want to see in this block.';
$string['titleanddescription'] = 'Title, description, tags';
$string['titleanddescriptionnotags'] = 'Title, description';
$string['titleanddescriptionandtagsandowner'] = 'Title, description, tags, owner';
$string['tagsonly'] = 'Tags only'; // for elasticsearch
$string['tagsonly1'] = 'Tags';
$string['matchalltags'] = 'Match all tags';
$string['matchalltagsdesc'] = 'Separate tags with commas, e.g. cats,tabby';
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
$string['review'] = 'Review';

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
$string['choosethemedesc'] = 'Choose a theme for the page.';

$string['lockblocks1'] = "Prevent removing of blocks";
$string['lockblocksdescription2'] = "You can prevent that blocks are removed when you edit the page. You can still change the location and size of the blocks. You can change this setting at any time to remove blocks if needed.";
$string['lockblocksdescriptioninstitution1'] = "You can prevent that blocks are removed when people copy the page into their personal or group portfolio area. This setting does not affect your editing of this site or institution page.";
$string['instructions'] = 'Instructions';
$string['advanced']     = 'Advanced';

// Versioning strings
$string['timeline'] = 'Timeline';
$string['timelinespecific'] = 'Timeline for %s';
$string['savetimeline'] = 'Save to timeline';
$string['savetimelinespecific'] = 'Save timeline for %s';
$string['noversionsexist'] = 'There are no saved versions to display for the page "%s"';
$string['previousversion'] = 'Previous version';
$string['nextversion'] = 'Next version';
$string['versionnumber'] = 'Version %s';
$string['gotonextversion'] = 'Go to the next version';
$string['gotopreviousversion'] = 'Go to the previous version';
$string['loadingtimelinecontent'] = 'Loading timeline for "%s". If the page has many versions, this may take a while.';

// layout strings
$string['bottom'] = 'Bottom';
$string['top'] = 'Top';
$string['blockssizeupdated'] = 'Block sizes were updated successfully';
$string['dimensionsnotset'] = 'Block dimensions not set';
$string['dontaskagain'] = 'Accept and remember';
$string['pleaseconfirmtranslate'] = 'Convert page layout';
$string['confirmconversionmessage'] = 'As part of Mahara 19.10 we introduced a new way to create a page layout. To be able to edit this page, you will need to convert the old to the new layout.
If you want to convert only this page, click \'Accept\'. To convert all pages and not see this message again, click \'Accept and remember\'. This option can be changed in your <a href="%s">Preferences</a>. To go back to the page without editing it, click \'Cancel\'.
';
$string['accessibleview'] = 'Accessible layout';
$string['accessibleviewdescription'] = 'To create a one-column page layout and edit it with the keyboard instead of drag-and-drop.';
$string['itemgrabbed'] = 'Item grabbed: %s';
$string['itemdropped'] = 'Item dropped: %s';
$string['itemreorder'] = 'List has been reordered. Item %s is now in position %s of %s';
$string['reordercancelled'] = 'The reordering was cancelled';
$string['accessibilitymodedescription1'] = 'This page has the accessible layout enabled. Click the \'Add new block\' button to add a block to the page.<br>
In this mode, the page blocks have full page width and are displayed one after the other. To change a block\'s position, navigate to it, grab it with the \'Enter\' key, and move it up and down the list of blocks with the arrow keys on your keyboard.';
$string['blocktypeis'] = ' %s blocktype';


// Cover image
$string['coverimage'] = 'Cover image';
$string['coverimagefolder'] = 'Cover images';
$string['coverimagedescription'] = 'The recommended dimensions are 180px wide by 130px high.';

// templates and copies
$string['locktemplate'] = 'Template';
$string['locktemplatedescription'] = 'When this is set to "Yes", people copying the page into their personal account will not be able to change any page or artefact instructions.';
$string['linktooriginaltemplate'] = 'Original template';
$string['linktooriginaltemplatedescription'] = 'This page is based on a template. This is the link to it.';
$string['linktooriginaltemplatedescriptiondeleted'] = 'This page is based on a template.';
$string['deletedview'] = 'Page deleted';
$string['copylocked'] = 'Copy locked';
$string['copylockeddescription'] = 'This is a template copy. Change this setting to lock/unlock the instruction fields on this copy.';

$string['linktosubmissionoriginaltitle'] = 'Original portfolio';
$string['linktosubmissionoriginallink'] = '<a href="%s">%s</a>';
$string['linktosubmissionoriginaldescription'] = 'This portfolio is a copy made for submission purposes. The link takes you to the original portfolio.';
$string['linktosubmissionoriginaldeleted'] = 'Deleted';
$string['linktosubmissionoriginaldeleteddescription'] = 'This portfolio is a copy made for submission purposes. The original portfolio has been deleted.';
$string['linkedtosourceportfoliotitle'] = 'Submission';
$string['linkedtosourceportfoliodescription'] = 'Switching this setting to \'No\' removes the association to the original portfolio. You can then use this copy that you made for submission purposes as you would any regular portfolio.';
$string['linkedtosourceportfoliodescriptioninacollection'] = 'This page is part of a collection that was submitted. Change the submission status in the collection settings for the entire collection.';

$string['canteditcollectionlocked'] = 'Unable to edit the portfolio page because the collection is locked.';
$string['canteditprogress'] = 'Unable to edit the portfolio completion page because the collection is locked or the page was copied from a template.';

$string['accessdeniedaccesss'] = 'You are not allowed to change the access permissions any more.';

$string['signoff'] = 'Sign-off';
$string['signoffhelp'] = "Indicate the pages you have completed";
$string['signoffhelppage'] = "Mark this page as 'Signed off' when you have finished adding all your evidence.";
$string['signoffdesc'] = 'The portfolio owner can sign off a page when all requirements have been met to indicate that it is ready for assessment.';

$string['verify'] = 'Verify';
$string['verifydesc1'] = 'Decide whether a manager needs to verify this page as part of the portfolio assessment process.';
$string['signedoff'] = 'Signed off';
$string['verified'] = 'Verified';

$string['signoffpagetitle'] = 'Sign-off page';
$string['signoffpagedesc'] = 'Select "Yes" to sign off this page and indicate that you have met all requirements. Select "No" to abort.';
$string['signoffpageundodesc'] = 'If you select "Yes", you will remove the signed-off status. That will also remove the verification if that had been part of the assessment work flow. Select "No" to abort.';
$string['signoffpageconfirm'] = 'Confirm this action?';

$string['verifypagetitle'] = 'Verify page';
$string['verifypagedesc'] = 'Select "Yes" to verify that the portfolio owner has met all requirements for this page. Select "No" to return to the page without verifying it.';

$string['signoffdetails'] = 'Sign-off details';
$string['updatesignoff'] = 'Update page sign-off';
$string['updateverify'] = 'Update page verification';
$string['viewsignoffdetails'] = 'View sign-off details information';
$string['readyforverification'] = 'This page is ready for verification.';
$string['signedoffbyondate'] = '%s signed off this page on %s.';
$string['verifiedbyondate'] = '%s verified this page on %s.';
$string['cannoteditaftersignoff'] = 'You cannot update "%s" after page is signed off';

$string['removedverifynotificationsubject'] = 'Verification for %s removed';
$string['removedverifynotification'] = 'The owner of the page %s has removed their sign-off. Therefore, your verification has also been removed. Please go to the page to see if it is ready to be marked as verified again.';

$string['signoffviewupdated'] = 'Sign-off status updated';
$string['verifyviewupdated'] = 'Verification status updated';
$string['wrongsignoffviewrequest'] = 'You do not have permission to perform the requested action';

// Activity page
$string['startdate_rule'] = 'Start date must be before completion date';
$string['add_activity_button'] = 'Add activity page';
$string['activity_info_fieldset'] = 'Activity information';
$string['activity_info_title'] = 'Activity description';
$string['activity_info_desc'] = 'Describe the activity that the learner should achieve. It is a longer version of the page title.';

// Subject
$string['activity_info_subject'] = 'Subject';
$string['activity_info_subject_desc'] = 'Select the subject that fits most closely for this activity.';
$string['activity_info_supervisor'] = 'Responsible staff';
$string['activity_info_activity_info_supervisor_desc'] = 'Select the group tutor or group administrator who is in charge of this activity.';

$string['activity_info_start_date'] = 'Start date';
$string['activity_info_end_date'] = 'End date';
$string['activity_info_start_date_desc'] = 'Select the date when the learner will start work on this activity.';
$string['activity_info_end_date_desc'] = 'Select the date when the learner is expected to finish this activity.';
$string['activity_info_achievement_levels'] = 'Levels of achievement';
$string['activity_info_achievement_levels_desc'] = 'Define the levels of achievement for this particular activity.';

// Activity page achievement levels
$string['activity_info_achievement_level'] = 'Level %s';
$string['activity_info_achievement_level_0'] = 'Not demonstrated';

// Activity page form at top of page
$string['activity_info_staff'] = 'Responsible staff';
$string['timeframe'] = 'Time frame';
$string['subject'] =  'Subject'; // Can be pulled from outcomes
$string['strategy_support']  = 'Strategies and support';

$string['strategy_support_desc'] = '
    Outline your strategies and support recommendations to help the learner.
';

$string['resources_support'] = 'Resources';
$string['resources_support_desc'] = 'Describe the resources you used and how they supported the learner.';
$string['learner_support'] = 'Learner support';
$string['learner_support_desc'] = 'Describe how you supported the learner to complete this activity.';
$string['supportupdatedfor'] = 'Updated "%s"';
