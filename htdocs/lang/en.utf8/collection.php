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

$string['pluginname'] = 'Collections';

$string['about'] = 'About';
$string['access'] = 'Access';
$string['accesscantbeused'] = 'Access override not saved. The chosen pages access (Secret URL) cannot be used for multiple pages.';
$string['accessoverride'] = 'Access override';
$string['accesssaved'] = 'Collection access saved successfully.';
$string['accessignored'] = 'Some secret URL access types were ignored.';
$string['add'] = 'Add';
$string['addviews'] = 'Add pages';
$string['addviewstocollection'] = 'Add pages to collection';
$string['back'] = 'Back';
$string['cantlistgroupcollections'] = 'You are not allowed to list group collections.';
$string['cantlistinstitutioncollections'] = 'You are not allowed to list institution collections.';
$string['canteditgroupcollections'] = 'You are not allowed to edit group collections.';
$string['canteditinstitutioncollections'] = 'You are not allowed to edit institution collections.';
$string['canteditcollection'] = 'You are not allowed to edit this collection.';
$string['cantcreatecollection'] = 'You are not allowed to create this collection.';
$string['cantdeletecollection'] = 'You cannot delete this collection.';
$string['canteditdontown'] = 'You cannot edit this collection because you do not own it.';
$string['canteditsubmitted'] = 'You cannot edit this collection because it has been submitted for assessment to %s. You will have to wait until it is released.';
$string['collection'] = 'collection';
$string['Collection'] = 'Collection';
$string['collections'] = 'collections';
$string['Collections'] = 'Collections';
$string['groupcollections'] = 'Group collections';
$string['institutioncollections'] = 'Institution collections';
$string['sitecollections'] = 'Site collections';
$string['collectionaccess'] = 'Collection access';
$string['collectionaccessrules'] = 'Collection access rules';
$string['collectionaccesseditedsuccessfully'] = 'Collection access saved successfully';
$string['collectioneditaccess'] = 'You are editing access for %d pages in this collection.';
$string['collectionconfirmdelete'] = 'Pages in this collection will not be deleted. Are you sure you wish to delete this collection?';
$string['collectioncreatedsuccessfully'] = 'Collection created successfully.';
$string['collectioncreatedsuccessfullyshare'] = 'Your collection has been created successfully. Share your collection with others using the access links below.';
$string['collectiondeleted'] = 'Collection deleted successfully.';
$string['collectiondescription'] = 'A collection is a set of pages that are linked to one another and have the same access permissions. You can create as many collections as you like, but a page cannot appear in more than one collection.';
$string['collectiontitle'] = 'Collection title';
$string['confirmcancelcreatingcollection'] = 'This collection has not been completed. Do you really want to cancel?';
$string['collectionsaved'] = 'Collection saved successfully.';
$string['copyacollection'] = 'Copy a collection';
$string['created'] = 'Created';
$string['deletecollection'] = 'Delete collection';
$string['deletespecifiedcollection'] = 'Delete collection \'%s\'';
$string['deletingcollection'] = 'Deleting collection';
$string['deleteview'] = 'Remove page from collection';
$string['description'] = 'Collection description';
$string['collectiondragupdate1'] = 'Drag page names from the \'Add pages to collection\' box or tick the check boxes and click the \'Add pages\' button to move pages to \'Pages already in collection\'.<br>
You can drag page names or use the arrow buttons to re-order pages in the \'Pages already in collection\' area.';
$string['viewsincollection'] = 'Pages already in collection';
$string['editcollection'] = 'Edit collection';
$string['editingcollection'] = 'Editing collection';
$string['edittitleanddesc'] = 'Edit title and description';
$string['editviews'] = 'Edit collection pages';
$string['editviewaccess'] = 'Edit page access';
$string['editaccess'] = 'Edit collection access';
$string['emptycollectionnoeditaccess'] = 'You cannot edit access to empty collections. Add some pages first.';
$string['emptycollection'] = 'Empty collection';
$string['manage'] = 'Manage';
$string['manageviews'] = 'Manage pages';
$string['manageviewsspecific'] = 'Manage pages in "%s"';
$string['name'] = 'Collection name';
$string['needtoselectaview'] = 'You need to select a page to add to the collection.';
$string['newcollection'] = 'New collection';
$string['nocollections'] = 'No collections yet.';
$string['nocollectionsaddone'] = 'No collections yet. %sAdd one%s.';
$string['nooverride'] = 'No override';
$string['noviewsavailable'] = 'No pages are available to add.';
$string['noviewsaddsome'] = 'No pages in collection. %sAdd some%s.';
$string['noviews'] = 'No pages.';
$string['overrideaccess'] = 'Override access';
$string['potentialviews'] = 'Potential pages';
$string['saveapply'] = 'Apply and save';
$string['savecollection'] = 'Save collection';
$string['smartevidence'] = 'SmartEvidence';
$string['smartevidencedesc'] = 'Administer SmartEvidence frameworks';
$string['update'] = 'Update';
$string['usecollectionname'] = 'Use collection name?';
$string['usecollectionnamedesc'] = 'If you wish to use the collection name instead of the block title, leave this checked.';
$string['numviewsincollection'] = array(
    '%s page in collection',
    '%s pages in collection',
);
$string['viewsaddedtocollection1'] = array(
    '%s page added to collection.',
    '%s pages added to collection.',
);
$string['viewsaddedtocollection1different'] = array(
    '%s page added to collection. The shared access has changed for all pages in the collection.',
    '%s pages added to collection. The shared access has changed for all pages in the collection.',
);
$string['viewsaddedaccesschanged'] = 'Access permissions have changed for the following pages:';
$string['viewaddedsecreturl'] = 'Available publicly via secret URL';
$string['viewcollection'] = 'View collection details';
$string['viewcount'] = 'Pages';
$string['viewremovedsuccessfully'] = 'Page removed successfully.';
$string['viewnavigation'] = 'Page navigation bar';
$string['viewnavigationdesc'] = 'Add a horizontal navigation bar to every page in this collection by default.';
$string['viewstobeadded'] = 'Pages to be added';
$string['viewconfirmremove'] = 'Are you sure you wish to remove this page from the collection?';
$string['collectioncopywouldexceedquota'] = 'Copying this collection would exceed your file quota.';
$string['copiedpagesblocksandartefactsfromtemplate'] = 'Copied %d pages, %d blocks and %d artefacts from %s';
$string['copiedblogpoststonewjournal'] = 'Copied journal posts have been put into a new separate journal.';
$string['by'] = 'by';
$string['copycollection'] = 'Copy collection';
$string['youhavencollections'] = array(
    'You have 1 collection.',
    'You have %d collections.',
);
$string['youhavenocollections'] = 'You have no collections.';
$string['collectionssharedtogroup'] = 'Collections shared with this group';
$string['nosharedcollectionsyet'] = 'There are no collections shared with this group yet';
$string['nextpage'] = 'Next page';
$string['prevpage'] = 'Previous page';
$string['viewingpage'] = 'You are on page ';
$string['navtopage'] = 'Navigate to page:';
