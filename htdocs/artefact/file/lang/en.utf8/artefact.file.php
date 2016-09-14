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

$string['pluginname'] = 'Files';

$string['sitefilesloaded'] = 'Site files loaded';
$string['addafile'] = 'Add a file';
$string['archive'] = 'Archive';
$string['bytes'] = 'bytes';
$string['cannotviewfolder'] = 'You do not have permission to view the content of this folder.';
$string['cannoteditfolder'] = 'You do not have permission to add content to this folder.';
$string['cannoteditfoldersubmitted'] = 'You cannot add content to a folder in a submitted page.';
$string['cannotremovefromsubmittedfolder'] = 'You cannot remove content from a folder in a submitted page.';
$string['cannotextractfilesubmitted'] = 'You cannot extract a file in a submitted page.';
$string['cannotextractfileinfoldersubmitted'] = 'You cannot extract a file in a folder in a submitted page.';
$string['changessaved'] = 'Changes saved';
$string['clickanddragtomovefile'] = 'Move %s by clicking and dragging or by pressing the space bar';
$string['moveto'] = 'Move to %s';
$string['editfolderspecific'] = 'Edit folder "%s"';
$string['deletefolderspecific'] = 'Delete folder "%s"';
$string['editfilespecific'] = 'Edit file "%s"';
$string['selectspecific'] = 'Select "%s"';
$string['foldercontents'] = 'Folder contents';
$string['copyrightnotice'] = 'Copyright notice';
$string['create'] = 'Create';
$string['Created'] = 'Created';
$string['createfolder'] = 'Create folder';
$string['foldername'] = 'Folder name';
$string['confirmdeletefile'] = 'Are you sure you want to delete this file?';
$string['confirmdeletefolder'] = 'Are you sure you want to delete this folder?';
$string['confirmdeletefolderandcontents'] = 'Are you sure you want to delete this folder and its content?';
$string['customagreement'] = 'Custom agreement';
$string['Date'] = 'Date';
$string['resizeonupload'] = 'Resize images on upload';
$string['resizeonuploaddescription'] = 'Automatically resize large images on upload';
$string['resizeonuploaduseroption1'] = 'User option';
$string['resizeonuploaduseroptiondescription3'] = 'Show users the option to enable or disable automatic resizing of large images when uploading.';
$string['resizeonuploadenable1'] = 'Resize large images automatically';
$string['resizeonuploadenablefilebrowser1'] = 'Automatic resizing of images larger than %sx%s px (recommended)';
$string['resizeonuploadmaxwidth'] = 'Maximum width';
$string['resizeonuploadmaxheight'] = 'Maximum height';
$string['resizeonuploadenabledescription3'] = 'Resize large images on upload if they exceed the maximum width and height settings.';
$string['defaultagreement'] = 'Default agreement';
$string['defaultquota'] = 'Default quota';
$string['defaultquotadescription'] = 'You can set the amount of disk space that new users will have as their quota here.';
$string['defaultuserquota'] = 'Default user quota';
$string['updateuserquotas'] = 'Update user quotas';
$string['updateuserquotasdesc2'] = 'Apply the default quota you choose above to all existing users.';
$string['institutionoverride1'] = 'Institutional override';
$string['institutionoverridedescription2'] = 'Allow institution administrators to set user file quotas and have default quotas for each institution.';
$string['maxquotaenabled'] = 'Enforce a sitewide maximum quota';
$string['maxquota'] = 'Maximum quota';
$string['maxquotatoolow'] = 'The maximum quota cannot be lower than the default quota.';
$string['maxquotaexceeded'] = 'You specified a quota above the maximum available setting for this site (%s). Try specifying a lower value or contact the site administrators to have them increase the maximum quota.';
$string['maxquotaexceededform'] = 'Please specify a file quota of less than %s.';
$string['maxquotadescription'] = 'You can set the maximum quota that an administrator can give to a user. Existing user quotas will not be affected.';
$string['defaultgroupquota'] = 'Default group quota';
$string['defaultgroupquotadescription'] = 'You can set the amount of disk space that new groups can use in their files area.';
$string['updategroupquotas'] = 'Update group quotas';
$string['updategroupquotasdesc2'] = 'Apply the default quota you choose above to all existing groups.';
$string['deletingfailed'] =  'Deleting failed: the file or folder does not exist any more';
$string['deletefile?'] = 'Are you sure you want to delete this file?';
$string['deletefolder?'] = 'Are you sure you want to delete this folder?';
$string['Description'] = 'Description';
$string['destination'] = 'Destination';
$string['Details'] = 'Details';
$string['View'] = 'View';
$string['Download'] = 'Download';
$string['downloadfile'] = 'Download %s';
$string['downloadoriginalversion'] = 'Download the original version';
$string['dragdrophere'] = 'Drop files here to upload';
$string['editfile'] = 'Edit file';
$string['editfolder'] = 'Edit folder';
$string['editingfailed'] = 'Editing failed: file or folder does not exist any more';
$string['emptyfolder'] = 'Empty folder';
$string['file'] = 'File'; // Capitalised to be consistent with names of all the other artefact types
$string['File'] = 'File';
$string['fileadded'] = 'File selected';
$string['filealreadyindestination'] = 'The file you moved is already in that folder';
$string['fileappearsinviews'] = 'This file appears in one or more of your pages.';
$string['fileattachedtoportfolioitems'] = array(
    0 => 'This file is attached to %s other item in your portfolio.',
    1 => 'This file is attached to %s other items in your portfolio.',
);
$string['fileappearsinskins'] = 'This file is used as a background image in one or more of your skins.';
$string['profileiconattachedtoportfolioitems'] = 'This profile picture is attached to other items in your portfolio.';
$string['profileiconappearsinviews'] = 'This profile picture appears in one or more of your pages.';
$string['profileiconappearsinskins'] = 'This profile picture is used as a background image in one or more of your skins.';
$string['fileremoved'] = 'File removed';
$string['files'] = 'files';
$string['Files'] = 'Files';
$string['fileexists'] = 'File exists';
$string['fileexistsoverwritecancel'] =  'A file with that name already exists. You can try a different name or overwrite the existing file.';
$string['fileisfolder'] = '"{{filename}}" is a folder. To upload a folder, please create a zip archive, upload that, then use the decompress option below.';
$string['filelistloaded'] = 'File list loaded';
$string['filemoved'] = 'File moved successfully';
$string['filenamefieldisrequired'] = 'The file field is required.';
$string['filenamefieldisrequired1'] = 'The file / folder name is required.';
$string['filespagedescription1'] = 'Here are your images, documents and other files for inclusion in pages. Drag and drop a file or folder icon to move the file or folder between folders.';
$string['groupfilespagedescription1'] = 'Here are the group images, documents and other files for inclusion in pages.';
$string['institutionfilespagedescription1'] = 'Here are the institution images, documents and other files for inclusion in pages.';
$string['fileuploadinstructions1'] = 'You can select multiple files to upload them at once.';
$string['filethingdeleted'] = '%s deleted';
$string['filewithnameexists'] = 'A file or folder with the name "%s" already exists.';
$string['folder'] = 'Folder';
$string['Folder'] = 'Folder';
$string['folderappearsinviews'] = 'This folder appears in one or more of your pages.';
$string['Folders'] = 'Folders';
$string['foldernotempty'] = 'This folder is not empty.';
$string['foldercontainsprofileicons'] = array(
        0 => 'The folder contains %s profile picture.',
        1 => 'The folder contains %s profile pictures.',
);
$string['foldercreated'] = 'Folder created';
$string['foldernamerequired'] = 'Please provide a name for the new folder.';
$string['gotofolder'] = 'Go to %s';
$string['groupfiles'] = 'Group files';
$string['home'] = 'Home';
$string['htmlremovedmessage'] = 'You are viewing <strong>%s</strong> by <a href="%s">%s</a>. The file displayed below has been filtered to remove malicious content and is only a rough representation of the original.';
$string['htmlremovedmessagenoowner'] = 'You are viewing <strong>%s</strong>. The file displayed below has been filtered to remove malicious content and is only a rough representation of the original.';
$string['image'] = 'Image';
$string['Images'] = 'Images';
$string['imagesdir'] = 'images';
$string['imagesdirdesc'] = 'Image files';
$string['lastmodified'] = 'Last modified';
$string['myfiles'] = 'My files';
$string['Name'] = 'Name';
$string['namefieldisrequired'] = 'The name field is required';
$string['maxuploadsize'] = 'Maximum upload size';
$string['nofolderformove'] = 'No folder available to move to';
$string['movefaileddestinationinartefact'] = 'You cannot put a folder inside itself.';
$string['movefaileddestinationnotfolder'] = 'You can only move files into folders.';
$string['movefailednotfileartefact'] = 'Only file, folder and image artefacts can be moved.';
$string['movefailednotowner'] = 'You do not have the permission to move the file into this folder.';
$string['movefailed'] = 'Move failed.';
$string['movingfailed'] = 'Moving failed: file or folder does not exist any more.';
$string['nametoolong'] = 'That name is too long. Please choose a shorter one.';
$string['nofilesfound'] = 'No files found';
$string['notpublishable'] = 'You do not have the permission to publish this file.';
$string['overwrite'] = 'Overwrite';
$string['Owner'] = 'Owner';
$string['parentfolder'] = 'Parent folder';
$string['phpzipneeded'] = 'The PHP zip extension is needed to be able to use this functionality';
$string['Preview'] = 'Preview';
$string['requireagreement'] = 'Require agreement';
$string['removingfailed'] = 'Removing failed: file or folder does not exist any more.';
$string['savechanges'] = 'Save changes';
$string['selectafile'] = 'Select a file';
$string['selectingfailed'] = 'Selecting failed: file or folder does not exist any more.';
$string['Size'] = 'Size';
$string['License'] = 'License';
$string['spaceused'] = 'Space used';
$string['timeouterror'] = 'File upload failed: try uploading the file again.';
$string['title'] = 'Name';
$string['titlefieldisrequired'] = 'The name field is required.';
$string['Type'] = 'Type';
$string['typefile'] = 'File';
$string['typefolder'] = 'Folder';
$string['upload'] = 'Upload';
$string['uploadagreement'] = 'Upload agreement';
$string['uploadagreementdescription'] = 'Enable this option if you would like to force users to agree to the text below before they can upload a file to the site.';
$string['uploadexceedsquota'] = 'Uploading this file would exceed your disk quota. Try deleting some files you have uploaded.';
$string['uploadexceedsquotagroup'] = 'Uploading this file would exceed this group\'s disk quota. Try deleting some files you have uploaded.';
$string['uploadfile'] =  'Upload file';
$string['uploadfileexistsoverwritecancel'] =  'A file with that name already exists. You can rename the file you are about to upload or overwrite the existing file.';
$string['uploadingfiletofolder'] =  'Uploading %s to %s';
$string['uploadoffilecomplete'] = 'Upload of %s complete';
$string['uploadoffilefailed'] =  'Upload of %s failed';
$string['uploadoffiletofoldercomplete'] = 'Upload of %s to %s complete';
$string['uploadoffiletofolderfailed'] = 'Upload of %s to %s failed';
$string['usecustomagreement'] = 'Use custom agreement';
$string['youmustagreetothecopyrightnotice'] = 'You must agree to the copyright notice.';
$string['fileuploadedtofolderas'] = '%s uploaded to %s as "%s"';
$string['fileuploadedas'] = '%s uploaded as "%s"';
$string['insufficientmemoryforresize'] = ' (Not enough memory available to resize the image. Consider resizing manually before uploading)';

// quotanotification
$string['quotanotifylimitoutofbounds'] = 'The notification limit is given in percent and has to be a number between 0 and 100.';
$string['usernotificationsubject']  = 'Your file storage is almost full';
$string['usernotificationmessage']  = 'You are using %s%% of your %s quota. You should get in touch with your site administrator about having your limit increased.';
$string['adm_notificationsubject']  = 'A user has nearly reached their file quota limit';
$string['adm_notificationmessage']  = 'User %s has arrived at %s%% percent of their %s quota. ';
$string['adm_group_notificationsubject']  = 'A group that you administer has nearly reached their file quota limit';
$string['adm_group_notificationmessage']  = 'Group "%s" has arrived at %s%% percent of their %s quota. ';
$string['textlinktouser'] = 'Edit profile of %s';
$string['quotanotifylimittitle1'] = 'Quota notification threshold';
$string['quotanotifylimitdescr1'] = 'When a user\'s file upload quota is filled to this percentage, they (and the site administrators) will be sent a notification to let them know they are nearing their upload limit. They can then either delete files to free up space or contact their administrator to have their quota increased.';
$string['quotanotifyadmin1'] = 'Site administrator notification';
$string['quotanotifyadmindescr3'] = 'Notify site administrators when users reach the notification threshold.';
$string['useroverquotathreshold'] = 'User %s has arrived at %s%% percent of their %s quota. ';

// File types
$string['ai'] = 'Postscript document';
$string['aiff'] = 'AIFF audio file';
$string['application'] = 'Unknown application';
$string['au'] = 'AU audio file';
$string['audio'] = 'audio file';
$string['avi'] = 'AVI video file';
$string['bmp'] = 'Bitmap image';
$string['doc'] = 'MS Word document';
$string['dss'] = 'Digital speech standard sound file';
$string['gif'] = 'GIF image';
$string['html'] = 'HTML file';
$string['jpg'] = 'JPEG image';
$string['jpeg'] = 'JPEG image';
$string['js'] = 'Javascript file';
$string['latex'] = 'LaTeX document';
$string['m3u'] = 'M3U audio file';
$string['mp3'] = 'MP3 audio file';
$string['mp4'] = 'MP4 media file';
$string['mpeg'] = 'MPEG movie';
$string['odb'] = 'OpenOffice / LibreOffice Base database file';
$string['odc'] = 'OpenOffice / LibreOffice Calc file';
$string['odf'] = 'OpenOffice / LibreOffice formula file';
$string['odg'] = 'OpenOffice / LibreOffice graphics file';
$string['odi'] = 'OpenOffice / LibreOffice image file';
$string['odm'] = 'OpenOffice / LibreOffice master document file';
$string['odp'] = 'OpenOffice / LibreOffice Impress file';
$string['ods'] = 'OpenOffice / LibreOffice Spreadsheet file';
$string['odt'] = 'OpenOffice / LibreOffice Writer document';
$string['oth'] = 'OpenOffice / LibreOffice web document';
$string['ott'] = 'OpenOffice / LibreOffice template document';
$string['oga'] = 'OGA audio file';
$string['ogv'] = 'OGV video file';
$string['ogg'] = 'OGG Vorbis audio file';
$string['pdf'] = 'PDF document';
$string['png'] = 'PNG image';
$string['ppt'] = 'MS PowerPoint document';
$string['quicktime'] = 'QuickTime movie';
$string['ra'] = 'Real audio file';
$string['rtf'] = 'RTF document';
$string['sgi_movie'] = 'SGI movie file';
$string['sh'] = 'Shell script';
$string['tar'] = 'TAR archive';
$string['gz'] = 'Gzip compressed file';
$string['bz2'] = 'Bzip2 compressed file';
$string['txt'] = 'Plain text file';
$string['video'] = 'Video file';
$string['wav'] = 'WAV audio file';
$string['wmv'] = 'WMV video file';
$string['xml'] = 'XML file';
$string['zip'] = 'ZIP archive';
$string['swf'] = 'SWF flash movie';
$string['flv'] = 'FLV flash movie';
$string['mov'] = 'MOV QuickTime movie';
$string['mpg'] = 'MPG movie';
$string['ram'] = 'RAM RealPlayer movie';
$string['rpm'] = 'RPM RealPlayer movie';
$string['rm'] = 'RM RealPlayer movie';
$string['webm'] = 'WEBM video file';
$string['3gp'] = '3GPP media file';


// Profile icons
$string['cantcreatetempprofileiconfile'] = 'Could not write temporary profile picture image in %s';
$string['profileiconsize'] = 'Profile picture size';
$string['profileicons'] = 'Profile pictures';
$string['Default'] = 'Default';
$string['defaultprofileicon'] = 'This is currently set as your default profile picture.';
$string['deleteselectedicons'] = 'Delete';
$string['editprofileicon'] = 'Edit profile picture';
$string['profileicon'] = 'Profile picture';
$string['noimagesfound'] = 'No images found';
$string['profileiconaddedtoimagesfolder'] = "Your profile picture has been uploaded to your '%s' folder.";
$string['profileiconsetdefaultnotvalid'] = 'Could not set the default profile picture, the choice was not valid.';
$string['profileiconsdefaultsetsuccessfully'] = 'Default profile picture set successfully';
$string['nprofilepictures'] = array(
    'Profile picture',
    'Profile pictures',
);
$string['profileiconsnoneselected'] = 'No profile pictures were selected to be deleted.';
$string['onlyfiveprofileicons'] = 'You may upload only five profile pictures.';
$string['or'] = 'or';
$string['profileiconuploadexceedsquota'] = 'Uploading this profile picture would exceed your disk quota. Try deleting some files you have uploaded.';
$string['profileiconimagetoobig'] = 'The picture you uploaded was too big (%sx%s pixels). It must not be larger than %sx%s pixels.';
$string['uploadingfile'] = 'uploading file...';
$string['uploadprofileicon'] = 'Upload profile picture';
$string['uploadedprofileicon'] = 'Uploaded profile picture';
$string['profileiconsiconsizenotice'] = 'You may upload up to <strong>five</strong> profile pictures here and choose one to be displayed as your default picture at any one time. Your pictures must be between 16x16 and %sx%s pixels in size.';
$string['setdefault'] = 'Set default';
$string['setdefaultfor'] = 'Set default for "%s"';
$string['markfordeletionspecific'] = 'Mark "%s" for deletion';
$string['Title'] = 'Title';
$string['imagetitle'] = 'Image title';
$string['standardavatartitle'] = 'Standard or external avatar';
$string['standardavatarnote'] = 'Standard or external profile picture';
$string['wrongfiletypeforblock'] = 'The file you uploaded was not the correct type for this block.';

// Unzip
$string['Contents'] = 'Contents';
$string['Continue'] = 'Continue';
$string['Decompress'] = 'Decompress';
$string['decompressspecific'] = 'Decompress "%s"';
$string['nfolders'] = array(
    '%s folder',
    '%s folders',
);
$string['nfiles'] = array(
    '%s file',
    '%s files',
);
$string['createdtwothings'] = 'Created %s and %s.';
$string['filesextractedfromarchive'] = 'Files extracted from archive';
$string['filesextractedfromziparchive'] = 'Files extracted from Zip archive';
$string['fileswillbeextractedintofolder'] = 'Files will be extracted into %s';
$string['insufficientquotaforunzip'] = 'Your remaining file quota is too small to unzip this file. You can either delete files to free up space or contact your administrator to have your quota increased.';
$string['invalidarchive1'] = 'Invalid archive file.';
$string['invalidarchivehandle'] = 'Invalid archive file handle.';
$string['cannotopenarchive'] = 'Can not open the archive file %s.';
$string['cannotreadarchivecontent'] = 'Can not read the archive content.';
$string['cannotextractarchive'] = 'Unable to extract archive into %s.';
$string['cannotcopytemparchive'] = 'Unable to copy the archive file from %s to %s.';
$string['cannotdeletetemparchive'] = 'Unable to delete the temporary archive file %s.';
$string['pleasewaitwhileyourfilesarebeingunzipped'] = 'Please wait while your files are being unzipped.';
$string['spacerequired'] = 'Space required';
$string['unzipprogress'] = '%s files/folders created.';

// Group file permissions
$string['filepermission.view'] = 'View';
$string['filepermission.edit'] = 'Edit';
$string['filepermission.republish'] = 'Publish';

// Download Folder as zip
$string['zipdownloadheading'] = 'Folder downloads';
$string['downloadfolderzip'] = 'Download folders as a zip file';
$string['downloadfolderzipblock'] = 'Show download link';
$string['downloadfolderzipdescription3'] = 'Allow users to download a folder displayed via the "Folder" block as a zip file.';
$string['downloadfolderzipdescriptionblock'] = 'If checked, users can download the folder as a zip file.';
$string['downloadfolderziplink'] = 'Download folder content as a zip file';
$string['folderdownloadnofolderfound'] = 'Unable to find the folder with ID %d';
$string['zipfilenameprefix'] = 'folder';
$string['keepzipfor'] = 'Length of time to keep zip files';
$string['keepzipfordescription'] = 'Zip files created during the downloading of folders should be kept for this amount of time (in seconds).';

$string['progress_archive'] = array(
    'Add 1 archive file',
    'Add %s archive files',
);
$string['progress_audio'] = array(
    'Add 1 audio file',
    'Add %s audio files',
);
$string['progress_file'] = array(
    'Add 1 file',
    'Add %s files',
);
$string['progress_folder'] = array(
    'Add 1 folder',
    'Add %s folders',
);
$string['progress_image'] = array(
    'Add 1 image',
    'Add %s images',
);
$string['progress_profileicon'] = array(
    'Add 1 profile picture',
    'Add %s profile pictures',
);
$string['progress_video'] = array(
    'Add 1 video',
    'Add %s videos',
);
$string['anytypeoffile'] = 'File (any type)';
