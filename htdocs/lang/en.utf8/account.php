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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['changepassworddesc'] = 'New password';
$string['changepasswordotherinterface'] = 'You may <a href="%s">change your password</a> through a different interface';
$string['oldpasswordincorrect'] = 'This is not your current password';

$string['changeusernameheading'] = 'Change username';
$string['changeusername'] = 'New username';
$string['changeusernamedesc'] = 'The username you use to log into %s.  Usernames are 3-30 characters long, and may contain letters, numbers, and most common symbols excluding spaces.';

$string['usernameexists'] = 'This username is taken, please choose another.';

$string['accountoptionsdesc'] = 'General account options';
$string['friendsnobody'] = 'Nobody may add me as a friend';
$string['friendsauth'] = 'New friends require my authorisation';
$string['friendsauto'] = 'New friends are automatically authorised';
$string['friendsdescr'] = 'Friends control';
$string['updatedfriendcontrolsetting'] = 'Updated friends control';

$string['wysiwygdescr'] = 'HTML editor';
$string['on'] = 'On';
$string['off'] = 'Off';
$string['disabled'] = 'Disabled';
$string['enabled'] = 'Enabled';

$string['messagesdescr'] = 'Messages from other users';
$string['messagesnobody'] = 'Do not allow anyone to send me messages';
$string['messagesfriends'] = 'Allow people on my Friends list to send me messages';
$string['messagesallow'] = 'Allow anyone to send me messages';

$string['language'] = 'Language';

$string['showviewcolumns'] = 'Show controls to add and remove columns when editing a page';

$string['tagssideblockmaxtags'] = 'Maximum tags in cloud';
$string['tagssideblockmaxtagsdescription'] = 'Maximum number of tags to display in your Tag Cloud';

$string['enablemultipleblogs'] = 'Enable multiple journals';
$string['enablemultipleblogsdescription']  = 'By default, you have one journal. If you would like to keep more than one journal, check this option.';
$string['disablemultipleblogserror'] = 'You cannot disable multiple journals unless you only have one journal';

$string['hiderealname'] = 'Hide real name';
$string['hiderealnamedescription'] = 'Check this box if you have set a display name and you do not want other users to be able to find you by your real name in user searches.';

$string['showhomeinfo'] = 'Show information about Mahara on the home page';

$string['mobileuploadtoken'] = 'Mobile upload token';
$string['mobileuploadtokendescription'] = 'Enter a token here and on your phone to enable uploads (note: it will change automatically after each upload. <br/>If you have any problems - simply reset it here and on your phone.';

$string['prefssaved']  = 'Preferences saved';
$string['prefsnotsaved'] = 'Failed to save your Preferences!';

$string['maildisabled'] = 'E-mail disabled';
$string['maildisabledbounce'] =<<< EOF
Sending of e-mail to your e-mail address has been disabled as too many messages have been returned to the server.
Please check that your e-mail account is working as expected before you re-enable e-mail on the account preferences at %s.
EOF;
$string['maildisableddescription'] = 'Sending of email to your account has been disabled. You may <a href="%s">re-enable your email</a> from the account preferences page.';

$string['deleteaccount']  = 'Delete Account';
$string['deleteaccountdescription']  = 'If you delete your account, your profile information and your pages will no longer be visible to other users.  The content of any forum posts you have written will still be visible, but the author\'s name will no longer be displayed.';
$string['accountdeleted']  = 'Your account has been deleted.';
