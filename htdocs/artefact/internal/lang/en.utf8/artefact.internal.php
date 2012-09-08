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

$string['pluginname'] = 'Profile';

$string['profile'] = 'Profile';

$string['mandatoryfields'] = 'Mandatory fields';
$string['mandatoryfieldsdescription'] = 'Profile fields that must be filled in';
$string['searchablefields'] = 'Searchable fields';
$string['searchablefieldsdescription'] = 'Profile fields that can be searched on by others';

$string['aboutdescription'] = 'Enter your real first and last name here. If you want to show a different name to people in the system, put that name in as your display name.';
$string['infoisprivate'] = 'This information is private until you include it in a page that is shared with others.';
$string['viewmyprofile'] = 'View my profile';

// profile categories
$string['aboutme'] = 'About me';
$string['contact'] = 'Contact information';
$string['messaging'] = 'Messaging';

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
$string['icqnumber'] = 'ICQ number';
$string['msnnumber'] = 'MSN chat';
$string['aimscreenname'] = 'AIM screen name';
$string['yahoochat'] = 'Yahoo chat';
$string['skypeusername'] = 'Skype username';
$string['jabberusername'] = 'Jabber username';
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
$string['viewprofilepage'] = 'View profile page';
$string['viewallprofileinformation'] = 'View all profile information';

$string['Note'] = 'Note';
$string['Notes'] = 'Notes';
$string['mynotes'] = 'My notes';
$string['notesfor'] = "Notes for %s";
$string['containedin'] = "Contained in:";
$string['notesdescription'] = 'These are the html notes you have created inside text box blocks on your pages.';
$string['editnote'] = 'Edit note';
$string['confirmdeletenote'] = 'This note is used in %d blocks and %d pages. If you delete it, all the blocks which currently contain the text will appear empty.';
$string['notedeleted'] = 'Note deleted';
$string['noteupdated'] = 'Note updated';
