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

$string['pluginname'] = 'Files';

$string['sitefilesloaded'] = 'Site files loaded';
$string['addafile'] = 'Add a file';
$string['archive'] = 'Archive';
$string['bytes'] = 'bytes';
$string['cannoteditfolder'] = 'You do not have permission to add content to this folder';
$string['cannoteditfoldersubmitted'] = 'You cannot add content to a folder in a submitted page.';
$string['cannotremovefromsubmittedfolder'] = 'You cannot remove content from a folder in a submitted page.';
$string['changessaved'] = 'Changes saved';
$string['clickanddragtomovefile'] = 'Click and drag to move %s';
$string['contents'] = 'Contents';
$string['copyrightnotice'] = 'Copyright notice';
$string['create'] = 'Create';
$string['Created'] = 'Created';
$string['createfolder'] = 'Create folder';
$string['confirmdeletefile'] = 'Are you sure you want to delete this file?';
$string['confirmdeletefolder'] = 'Are you sure you want to delete this folder?';
$string['confirmdeletefolderandcontents'] = 'Are you sure you want to delete this folder and all its contents?';
$string['customagreement'] = 'Custom Agreement';
$string['Date'] = 'Date';
$string['defaultagreement'] = 'Default Agreement';
$string['defaultquota'] = 'Default Quota';
$string['defaultquotadescription'] = 'You can set the amount of disk space that new users will have as their quota here. Existing user quotas will not be changed.';
$string['maxquotaenabled'] = 'Enforce a site-wide maximum quota';
$string['maxquota'] = 'Maximum Quota';
$string['maxquotatoolow'] = 'The maximum quota cannot be lower than the default quota.';
$string['maxquotaexceeded'] = 'You specified a quota above the maximum available setting for this site (%s). Try specifying a lower value or contact the site administrator to have them increase the maximum quota.';
$string['maxquotaexceededform'] = 'Please specify a file quota of less than %s.';
$string['maxquotadescription'] = 'You can set the maximum quota that an administrator can give to a user. Existing user quotas will not be affected.';
$string['deletingfailed'] =  'Deleting failed: file or folder does not exist any more';
$string['deletefile?'] = 'Are you sure you want to delete this file?';
$string['deletefolder?'] = 'Are you sure you want to delete this folder?';
$string['Description'] = 'Description';
$string['destination'] = 'Destination';
$string['Details'] = 'Details';
$string['Download'] = 'Download';
$string['downloadfile'] = 'Download %s';
$string['downloadoriginalversion'] = 'Download the original version';
$string['editfile'] = 'Edit file';
$string['editfolder'] = 'Edit folder';
$string['editingfailed'] = 'Editing failed: file or folder does not exist any more';
$string['emptyfolder'] = 'Empty folder';
$string['file'] = 'File'; // Capitalised to be consistent with names of all the other artefact types
$string['File'] = 'File';
$string['fileadded'] = 'File selected';
$string['filealreadyindestination'] = 'The file you moved is already in that folder';
$string['fileappearsinviews'] = 'This file appears in one or more of your pages.';
$string['fileattached'] = 'This file is attached to %s other item(s) in your portfolio.';
$string['fileremoved'] = 'File removed';
$string['files'] = 'files';
$string['Files'] = 'Files';
$string['fileexists'] = 'File exists';
$string['fileexistsoverwritecancel'] =  'A file with that name already exists.  You can try a different name, or overwrite the existing file.';
$string['filelistloaded'] = 'File list loaded';
$string['filemoved'] = 'File moved successfully';
$string['filenamefieldisrequired'] = 'The file field is required';
$string['fileinstructions'] = 'Upload your images, documents, or other files for inclusion in pages. Drag and drop the icons to move files between folders.';
$string['filethingdeleted'] = '%s deleted';
$string['filewithnameexists'] = 'A file or folder with the name "%s" already exists.';
$string['folder'] = 'Folder';
$string['Folder'] = 'Folder';
$string['folderappearsinviews'] = 'This folder appears in one or more of your pages.';
$string['Folders'] = 'Folders';
$string['foldernotempty'] = 'This folder is not empty.';
$string['foldercreated'] = 'Folder created';
$string['foldernamerequired'] = 'Please provide a name for the new folder.';
$string['gotofolder'] = 'Go to %s';
$string['groupfiles'] = 'Group Files';
$string['home'] = 'Home';
$string['htmlremovedmessage'] = 'You are viewing <strong>%s</strong> by <a href="%s">%s</a>. The file displayed below has been filtered to remove malicious content, and is only a rough representation of the original.';
$string['htmlremovedmessagenoowner'] = 'You are viewing <strong>%s</strong>. The file displayed below has been filtered to remove malicious content, and is only a rough representation of the original.';
$string['image'] = 'Image';
$string['Images'] = 'Images';
$string['lastmodified'] = 'Last Modified';
$string['myfiles'] = 'My Files';
$string['Name'] = 'Name';
$string['namefieldisrequired'] = 'The name field is required';
$string['maxuploadsize'] = 'Max upload size';
$string['movefaileddestinationinartefact'] = 'You cannot put a folder inside itself.';
$string['movefaileddestinationnotfolder'] = 'You can only move files into folders.';
$string['movefailednotfileartefact'] = 'Only file, folder and image artefacts can be moved.';
$string['movefailednotowner'] = 'You do not have permission to move the file into this folder';
$string['movefailed'] = 'Move failed.';
$string['movingfailed'] = 'Moving failed: file or folder does not exist any more';
$string['nametoolong'] = 'That name is too long.  Please choose a shorter one.';
$string['nofilesfound'] = 'No files found';
$string['notpublishable'] = 'You do not have permission to publish this file';
$string['overwrite'] = 'Overwrite';
$string['Owner'] = 'Owner';
$string['parentfolder'] = 'Parent folder';
$string['Preview'] = 'Preview';
$string['requireagreement'] = 'Require Agreement';
$string['removingfailed'] = 'Removing failed: file or folder does not exist any more';
$string['savechanges'] = 'Save changes';
$string['selectafile'] = 'Select a file';
$string['selectingfailed'] = 'Selecting failed: file or folder does not exist any more';
$string['Size'] = 'Size';
$string['spaceused'] = 'Space used';
$string['timeouterror'] = 'File upload failed: try uploading the file again';
$string['title'] = 'Name';
$string['titlefieldisrequired'] = 'The name field is required';
$string['Type'] = 'Type';
$string['upload'] = 'Upload';
$string['uploadagreement'] = 'Upload Agreement';
$string['uploadagreementdescription'] = 'Enable this option if you would like to force users to agree to the text below before they can upload a file to the site.';
$string['uploadexceedsquota'] = 'Uploading this file would exceed your disk quota. Try deleting some files you have uploaded.';
$string['uploadfile'] =  'Upload file';
$string['uploadfileexistsoverwritecancel'] =  'A file with that name already exists.  You can rename the file you are about to upload, or overwrite the existing file.';
$string['uploadingfiletofolder'] =  'Uploading %s to %s';
$string['uploadoffilecomplete'] = 'Upload of %s complete';
$string['uploadoffilefailed'] =  'Upload of %s failed';
$string['uploadoffiletofoldercomplete'] = 'Upload of %s to %s complete';
$string['uploadoffiletofolderfailed'] = 'Upload of %s to %s failed';
$string['usecustomagreement'] = 'Use Custom Agreement';
$string['youmustagreetothecopyrightnotice'] = 'You must agree to the copyright notice';
$string['fileuploadedtofolderas'] = '%s uploaded to %s as "%s"';
$string['fileuploadedas'] = '%s uploaded as "%s"';


// File types
$string['ai'] = 'Postscript Document';
$string['aiff'] = 'AIFF Audio File';
$string['application'] = 'Unknown Application';
$string['au'] = 'AU Audio File';
$string['avi'] = 'AVI Video File';
$string['bmp'] = 'Bitmap Image';
$string['doc'] = 'MS Word Document';
$string['dss'] = 'Digital Speech Standard Sound File';
$string['gif'] = 'GIF Image';
$string['html'] = 'HTML File';
$string['jpg'] = 'JPEG Image';
$string['jpeg'] = 'JPEG Image';
$string['js'] = 'Javascript File';
$string['latex'] = 'LaTeX Document';
$string['m3u'] = 'M3U Audio File';
$string['mp3'] = 'MP3 Audio File';
$string['mp4_audio'] = 'MP4 Audio File';
$string['mp4_video'] = 'MP4 Video File';
$string['mpeg'] = 'MPEG Movie';
$string['odb'] = 'Openoffice Database';
$string['odc'] = 'Openoffice Calc File';
$string['odf'] = 'Openoffice Formula File';
$string['odg'] = 'Openoffice Graphics File';
$string['odi'] = 'Openoffice Image';
$string['odm'] = 'Openoffice Master Document File';
$string['odp'] = 'Openoffice Presentation';
$string['ods'] = 'Openoffice Spreadsheet';
$string['odt'] = 'Openoffice Document';
$string['oth'] = 'Openoffice Web Document';
$string['ott'] = 'Openoffice Template Document';
$string['pdf'] = 'PDF Document';
$string['png'] = 'PNG Image';
$string['ppt'] = 'MS Powerpoint Document';
$string['quicktime'] = 'Quicktime Movie';
$string['ra'] = 'Real Audio File';
$string['rtf'] = 'RTF Document';
$string['sgi_movie'] = 'SGI Movie File';
$string['sh'] = 'Shell Script';
$string['tar'] = 'TAR Archive';
$string['gz'] = 'Gzip Compressed File';
$string['bz2'] = 'Bzip2 Compressed File';
$string['txt'] = 'Plain Text File';
$string['wav'] = 'WAV Audio File';
$string['wmv'] = 'WMV Video File';
$string['xml'] = 'XML File';
$string['zip'] = 'ZIP Archive';
$string['swf'] = 'SWF Flash movie';
$string['flv'] = 'FLV Flash movie';
$string['mov'] = 'MOV Quicktime movie';
$string['mpg'] = 'MPG Movie';
$string['ram'] = 'RAM Real Player Movie';
$string['rpm'] = 'RPM Real Player Movie';
$string['rm'] = 'RM Real Player Movie';


// Profile icons
$string['cantcreatetempprofileiconfile'] = 'Could not write temporary profile picture image in %s';
$string['profileiconsize'] = 'Profile Picture Size';
$string['profileicons'] = 'Profile Pictures';
$string['Default'] = 'Default';
$string['deleteselectedicons'] = 'Delete selected Profile Pictures';
$string['profileicon'] = 'Profile Pictures';
$string['noimagesfound'] = 'No images found';
$string['uploadedprofileiconsuccessfully'] = 'Uploaded new profile picture successfully';
$string['profileiconsetdefaultnotvalid'] = 'Could not set the default profile picture, the choice was not valid';
$string['profileiconsdefaultsetsuccessfully'] = 'Default profile picture set successfully';
$string['profileiconsdeletedsuccessfully'] = 'Profile picture(s) deleted successfully';
$string['profileiconsnoneselected'] = 'No profile pictures were selected to be deleted';
$string['onlyfiveprofileicons'] = 'You may upload only five profile pictures';
$string['or'] = 'or';
$string['profileiconuploadexceedsquota'] = 'Uploading this profile picture would exceed your disk quota. Try deleting some files you have uploaded';
$string['profileiconimagetoobig'] = 'The picture you uploaded was too big (%sx%s pixels). It must not be larger than %sx%s pixels';
$string['uploadingfile'] = 'uploading file...';
$string['uploadprofileicon'] = 'Upload Profile Picture';
$string['profileiconsiconsizenotice'] = 'You may upload up to <strong>five</strong> profile pictures here, and choose one to be displayed as your default icon at any one time. Your icons must be between 16x16 and %sx%s pixels in size.';
$string['setdefault'] = 'Set Default';
$string['Title'] = 'Title';
$string['imagetitle'] = 'Image Title';
$string['usenodefault'] = 'Use no default';
$string['usingnodefaultprofileicon'] = 'Now using no default profile picture';
$string['wrongfiletypeforblock'] = 'The file you uploaded was not the correct type for this block.';

// Unzip
$string['Contents'] = 'Contents';
$string['Continue'] = 'Continue';
$string['extractfilessuccess'] = 'Created %s folders and %s files.';
$string['filesextractedfromarchive'] = 'Files extracted from archive';
$string['filesextractedfromziparchive'] = 'Files extracted from Zip archive';
$string['fileswillbeextractedintofolder'] = 'Files will be extracted into %s';
$string['insufficientquotaforunzip'] = 'Your remaining file quota is too small to unzip this file.';
$string['invalidarchive'] = 'Error reading archive file.';
$string['pleasewaitwhileyourfilesarebeingunzipped'] = 'Please wait while your files are being unzipped.';
$string['spacerequired'] = 'Space Required';
$string['Unzip'] = 'Unzip';
$string['unzipprogress'] = '%s files/folders created.';

// Group file permissions
$string['filepermission.view'] = 'View';
$string['filepermission.edit'] = 'Edit';
$string['filepermission.republish'] = 'Publish';
