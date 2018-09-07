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

$string['pluginname'] = 'Profile';

$string['profile'] = 'Profile';

$string['mandatoryfields'] = 'Mandatory fields';
$string['mandatoryfieldsdescription'] = 'Profile fields that must be filled in';
$string['searchablefields'] = 'Searchable fields';
$string['searchablefieldsdescription'] = 'Profile fields that can be searched on by others';
$string['adminusersearchfields'] = 'Administration user search';
$string['adminusersearchfieldsdescription'] = 'Profile fields that appear as columns on the Administration user search list';

$string['aboutdescription'] = 'Enter your real first and last name here. If you want to show a different name to people in the system, put that name in as your display name.';
$string['infoisprivate'] = 'This information is private until you include it in a page that is shared with others.';
$string['viewmyprofile'] = 'View my profile';
$string['aboutprofilelinkdescription'] = '<p>Please go to your <a href="%s">Profile</a> page to arrange the information you wish to display to other users.</p>';

// profile categories
$string['aboutme'] = 'About me';
$string['contact'] = 'Contact information';
$string['social'] = 'Social media';
$string['messaging'] = 'Social media';

// profile fields
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['fullname'] = 'Full name';
$string['institution'] = 'Institution';
$string['studentid'] = 'Student ID';
$string['preferredname'] = 'Display name';
$string['introduction'] = 'Introduction';
$string['email'] = 'Email address';
$string['maildisabled'] = 'Email disabled';
$string['officialwebsite'] = 'Official website address';
$string['personalwebsite'] = 'Personal website address';
$string['blogaddress'] = 'Blog address';
$string['address'] = 'Postal address';
$string['town'] = 'Town';
$string['city'] = 'City/region';
$string['country'] = 'Country';
$string['homenumber'] = 'Home phone';
$string['businessnumber'] = 'Business phone';
$string['mobilenumber'] = 'Mobile phone';
$string['faxnumber'] = 'Fax number';
$string['aim.input'] = 'AIM screen name';
$string['aim'] = 'AIM';
$string['icq.input'] = 'ICQ number';
$string['icq'] = 'ICQ';
$string['jabber.input'] = 'Jabber username';
$string['jabber'] = 'Jabber';
$string['skype.input'] = 'Skype username';
$string['skype'] = 'Skype';
$string['yahoo.input'] = 'Yahoo Messenger';
$string['yahoo'] = 'Yahoo Messenger';
$string['facebook.input'] = 'Facebook URL';
$string['facebook'] = 'Facebook';
$string['twitter.input'] = 'Twitter username';
$string['twitter'] = 'Twitter';
$string['instagram.input'] = 'Instagram username';
$string['instagram'] = 'Instagram';
$string['tumblr.input'] = 'Tumblr URL';
$string['tumblr'] = 'Tumblr';
$string['pinterest.input'] = 'Pinterest username';
$string['pinterest'] = 'Pinterest';
$string['occupation'] = 'Occupation';
$string['industry'] = 'Industry';

// Field names for view user and search user display
$string['name'] = 'Name';
$string['principalemailaddress'] = 'Primary email';
$string['emailaddress'] = 'Alternative email';

$string['saveprofile'] = 'Save profile';
$string['profilesaved'] = 'Profile saved successfully';
$string['profilefailedsaved'] = 'Profile saving failed';


$string['emailvalidation_subject'] = 'Email validation';
$string['emailvalidation_body1'] = <<<EOF
Hello %s,

You have added the email address %s to your user account in %s. Please visit the link below to activate this address.

%s

If this email belongs to you, but you have not requested adding it to your %s account, follow the link below to decline the email activation.

%s
EOF;
$string['newemailalert_subject'] = 'New email address added to your %s account';
$string['newemailalert_body_text1'] = <<<EOF
Hello %s,

You have added the following email address(es) to your account in %s:

%s

If you have not requested this change in your %s account, please contact the site administrator.

%scontact.php

EOF;
$string['newemailalert_body_html1'] = <<<EOF
<p>Hello %s,</p>

<p>You have added the following email address(es) to your account in %s:</p>

<p>%s</p>

<p>If you have not requested this change in your %s account, please <a href="%scontact.php">contact the site administrator</a>.</p>

EOF;

$string['validationemailwillbesent'] = 'A validation email will be sent when you save your profile.';
$string['validationemailsent'] = 'A validation email has been sent.';
$string['emailactivation'] = 'Email activation';
$string['emailactivationsucceeded'] = 'Email activation successful';
$string['emailalreadyactivated'] = 'Email already activated';
$string['emailactivationfailed'] = 'Email activation failed';
$string['emailactivationdeclined'] = 'Email activation declined successfully';
$string['verificationlinkexpired'] = 'Verification link expired';
$string['invalidemailaddress'] = 'Invalid email address';
$string['unvalidatedemailalreadytaken'] = 'The email address you are trying to validate is already taken.';
$string['addbutton'] = 'Add';
$string['cancelbutton'] = 'Cancel';

$string['emailingfailed'] = 'Profile saved, but emails were not sent to: %s';

$string['loseyourchanges'] = 'Lose your changes?';

$string['Title'] = 'Title';

$string['Created'] = 'Created';
$string['Description'] = 'Description';
$string['Download'] = 'Download';
$string['lastmodified'] = 'Last modified';
$string['Owner'] = 'Owner';
$string['Preview'] = 'Preview';
$string['Size'] = 'Size';
$string['Type'] = 'Type';

$string['profileinformation'] = 'Profile information';
$string['profilepage'] = 'Profile page';
$string['profileimagefor'] = 'Profile picture for %s';
$string['viewprofilepage'] = 'View profile page';
$string['viewallprofileinformation'] = 'View all profile information';

$string['Note'] = 'Note';
$string['noteTitle'] = 'Note title';
$string['blockTitle'] = 'Block title';
$string['Notes'] = 'Notes';
$string['mynotes'] = 'My notes';
$string['notesfor'] = "Notes for %s";
$string['containedin'] = "Contained in";
$string['currenttitle'] = "Titled";
$string['notesdescription1'] = 'These are the HTML notes you have created inside note blocks on your pages.';
$string['editnote'] = 'Edit note';
$string['confirmdeletenote'] = 'This note is used in %d blocks and %d pages. If you delete it, all the blocks which currently contain the text will appear empty.';
$string['notedeleted'] = 'Note deleted';
$string['noteupdated'] = 'Note updated';
$string['html'] = 'Note';
$string['duplicatedprofilefieldvalue'] = 'Duplicated value';
$string['existingprofilefieldvalues'] = 'Existing values';

$string['progressbaritem_messaging'] = 'Messaging';
$string['progressbaritem_joingroup'] = 'Join a group';
$string['progressbaritem_makefriend'] = 'Make a friend';
$string['progress_firstname'] = 'Add your first name';
$string['progress_lastname'] = 'Add your last name';
$string['progress_studentid'] = 'Add your student ID';
$string['progress_preferredname'] = 'Add a display name';
$string['progress_introduction'] = 'Add an introduction about yourself';
$string['progress_email'] = 'Add an email address';
$string['progress_officialwebsite'] = 'Add an official website';
$string['progress_personalwebsite'] = 'Add your personal website';
$string['progress_blogaddress'] = 'Add your blog address';
$string['progress_address'] = 'Add your postal address';
$string['progress_town'] = 'Add a town';
$string['progress_city'] = 'Add a city/region';
$string['progress_country'] = 'Add a country';
$string['progress_homenumber'] = 'Add your home phone';
$string['progress_businessnumber'] = 'Add your business phone';
$string['progress_mobilenumber'] = 'Add your mobile phone';
$string['progress_faxnumber'] = 'Add your fax number';
$string['progress_messaging'] = 'Add messaging information';
$string['progress_occupation'] = 'Add your occupation';
$string['progress_industry'] = 'Add your industry';
$string['progress_joingroup'] = array(
    'Join a group',
    'Join %s groups',
);
$string['progress_makefriend'] = array(
    'Make a friend',
    'Make %s friends',
);

// Social profiles
$string['socialprofile'] = 'Social media';
$string['socialprofiles'] = 'Social media accounts';
$string['service'] = 'Social network';
$string['servicedesc'] = 'Enter the name of the social media network, e.g. Facebook, LinkedIn, Twitter, etc.';
$string['profileurl'] = 'Your URL or username';
$string['profileurldesc'] = 'The URL of your profile page or your username.';
$string['profileurlexists'] = 'This social media account cannot be added because its username or URL is a duplicate of one you\'ve already entered.';
$string['profiletype'] = 'Social network';

$string['deleteprofile'] = 'Delete social media account';
$string['deletethisprofile'] = 'Delete social media account: \'%s\'';
$string['deleteprofileconfirm'] = 'Are you sure you wish to delete this social media account?';
$string['editthisprofile'] = 'Edit social media account: \'%s\'';
$string['newsocialprofile'] = 'New social media account';
$string['notvalidprofileurl'] = 'This is not a valid social profile URL. Please enter a valid URL or select a messaging service from the list above.';
$string['profiledeletedsuccessfully'] = 'Social media account deleted successfully';
$string['profilesavedsuccessfully'] = 'Social media account saved successfully';
$string['socialprofilerequired'] = 'At least one social media account is required.';
$string['duplicateurl'] = 'This social media account username or URL is a duplicate of one you\'ve already entered.';
