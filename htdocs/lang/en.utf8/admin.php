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

$string['administration'] = 'Administration';

// Installer
$string['installation'] = 'Installation';
$string['release'] = 'version %s (%s)';
$string['copyright'] = 'Copyright &copy; 2006 onwards, <a href="http://wiki.mahara.org/Contributors">Catalyst IT Ltd and others</a>';
$string['installmahara'] = 'Install Mahara';
$string['component'] = 'Component or plugin';
$string['continue'] = 'Continue';
$string['coredata'] = 'core data';
$string['coredatasuccess'] = 'Successfully installed core data';
$string['fromversion'] = 'From version';
$string['information'] = 'Information';
$string['installsuccess'] = 'Successfully installed version ';
$string['toversion'] =  'To version';
$string['localdatasuccess'] = 'Successfully installed local customisations';
$string['notinstalled'] = 'Not installed';
$string['nothingtoupgrade'] = 'Nothing to upgrade';
$string['performinginstallation'] = 'Performing installation...';
$string['performingupgrades'] = 'Performing upgrades...';
$string['runupgrade'] = 'Run upgrade';
$string['successfullyinstalled'] = 'Successfully installed Mahara!';
$string['thefollowingupgradesareready'] = 'The following upgrades are ready:';
$string['registerthismaharasite'] = 'Register this Mahara site';
$string['upgradeloading'] = 'Loading...';
$string['upgrades'] = 'Upgrades';
$string['upgradesuccess'] = 'Successfully upgraded';
$string['upgradesuccesstoversion'] = 'Successfully upgraded to version ';
$string['upgradefailure'] = 'Failed to upgrade!';
$string['noupgrades'] = 'Nothing to upgrade! You are fully up to date!';
$string['youcanupgrade'] = 'You can upgrade Mahara from %s (%s) to %s (%s)!';
$string['Plugin'] = 'Plugin';
$string['jsrequiredforupgrade'] = 'You must enable javascript to perform an install or upgrade.';
$string['dbnotutf8warning'] = 'You are not using a UTF-8 database. Mahara stores all data as UTF-8 internally. You may still attempt this upgrade but it is recommended that you convert your database to UTF-8.';
$string['dbcollationmismatch'] = 'A column of your database is using a collation that is not the same as the database default.  Please ensure all columns use the same collation as the database.';

// Admin navigation menu
$string['adminhome']      = 'Admin home';
$string['configsite']  = 'Configure Site';
$string['configusers'] = 'Manage Users';
$string['groups'] = 'Groups';
$string['managegroups'] = 'Manage Groups';
$string['Extensions']   = 'Extensions';
$string['configextensions']   = 'Administer Extensions';
$string['manageinstitutions'] = 'Manage Institutions';

// Admin homepage strings
$string['siteoptions']    = 'Site options';
$string['siteoptionsdescription'] = 'Configure basic site options such as the name, language and theme';
$string['editsitepages']     = 'Edit site pages';
$string['editsitepagesdescription'] = 'Edit the content of various pages around the site';
$string['menus'] = 'Menus';
$string['menusdescription'] = 'Manage the links and files within the Links and Resources and Footer Menus';
$string['sitefiles']          = 'Site Files';
$string['sitefilesdescription'] = 'Upload and administer files that can be put in the Links and Resources Menu and in Site Pages';
$string['siteviews']          = 'Site Pages';
$string['siteviewsdescription'] = 'Create and administer pages and page Templates for the entire site';
$string['networking']          = 'Networking';
$string['networkingdescription'] = 'Configure networking for Mahara';

$string['staffusers'] = 'Staff Users';
$string['staffusersdescription'] = 'Assign users Staff permissions';
$string['adminusers'] = 'Admin Users';
$string['adminusersdescription'] = 'Assign Site Administrator access rights';
$string['institution']   = 'Institution';
$string['institutions']   = 'Institutions';
$string['institutiondetails']   = 'Institution Details';
$string['institutionauth']   = 'Institution Authorities';
$string['institutionsdescription'] = 'Install and manage installed institutions';
$string['adminnotifications'] = 'Admin Notifications';
$string['adminnotificationsdescription'] = 'Overview of how administrators receive system notifications';
$string['uploadcsv'] = 'Add Users by CSV';
$string['uploadcsvdescription'] = 'Upload a CSV file containing new users';
$string['usersearch'] = 'User Search';
$string['usersearchdescription'] = 'Search all users and perform administrative actions on them';
$string['usersearchinstructions'] = 'You can search for users by clicking on the initials of their first and last names, or by entering a name in the search box.  You can also enter an email address in the search box if you would like to search email addresses.';

$string['administergroups'] = 'Administer Groups';
$string['administergroupsdescription'] = 'Appoint group administrators and delete groups';
$string['groupcategoriesdescription'] = 'Add and edit group categories';

$string['institutionmembersdescription'] = 'Associate users with institutions';
$string['institutionstaffdescription'] = 'Assign users Staff permissions';
$string['institutionadminsdescription'] = 'Assign Institution Administrator access rights';
$string['institutionviews']          = 'Institution Pages';
$string['institutionviewsdescription'] = 'Create and administer Pages and Page Templates for an Institution';
$string['institutionfiles']          = 'Institution Files';
$string['institutionfilesdescription'] = 'Upload and manage files for use in Institution Pages';

$string['pluginadmin'] = 'Plugin Administration';
$string['pluginadmindescription'] = 'Install and configure plugins';
$string['missingplugin'] = 'An installed plugin (%s) could not be found';
$string['installedpluginsmissing'] = 'The following plugins are installed but can no longer be found';
$string['ensurepluginsexist'] = 'Please make sure all your installed plugins are available under %s, and readable by the webserver.';

$string['htmlfilters'] = 'HTML Filters';
$string['htmlfiltersdescription'] = 'Enable new filters for HTML Purifier';
$string['newfiltersdescription'] = 'If you have downloaded a new set of HTML filters, you can install them by unzipping the file into the folder %s and then clicking the button below';
$string['filtersinstalled'] = 'Filters installed.';
$string['nofiltersinstalled'] = 'No html filters installed.';

// sanity check warnings
$string['warnings'] = 'Warning';

// Group management
$string['groupcategories'] = 'Group Categories';
$string['allowgroupcategories'] = 'Allow Group Categories';
$string['enablegroupcategories'] = 'Enable Group Categories';
$string['addcategories'] = 'Add Categories';
$string['allowgroupcategoriesdescription'] = 'If checked, admins will be able to create categories for users to assign their groups';
$string['groupoptionsset'] = 'Group options have been updated.';
$string['groupcategorydeleted'] = 'Category deleted';
$string['confirmdeletecategory'] = 'Do you really want to delete this category?';
$string['groupcategoriespagedescription'] = 'The categories listed here can be assigned to groups during group creation, and used to filter groups during searches.';
$string['groupadminsforgroup'] = "Group administrators for '%s'";
$string['potentialadmins'] = 'Potential administrators';
$string['currentadmins'] = 'Current administrators';
$string['groupadminsupdated'] = 'Group admins have been updated';

// Register your Mahara
$string['Field'] = 'Field';
$string['Value'] = 'Value';
$string['datathatwillbesent'] = 'Data that will be sent';
$string['sendweeklyupdates'] = 'Send weekly updates?';
$string['sendweeklyupdatesdescription'] = 'If checked, your site will send weekly updates to mahara.org with some statistics about your site';
$string['Register'] = 'Register';
$string['registrationfailedtrylater'] = 'Registation failed with error code %s. Please try again later.';
$string['registrationsuccessfulthanksforregistering'] = 'Registation successful - thanks for registering!';
$string['registeryourmaharasite'] = 'Register your Mahara Site';
$string['registeryourmaharasitesummary'] = '
<p>You can choose to register your Mahara Site with <a href="http://mahara.org/">mahara.org</a>, and help us to build up a picture of the Mahara installation base around the world.  Registering will remove this notice.</p>
<p>You can register your site, and preview the information that will be sent on the <strong><a href="%sadmin/registersite.php">Site Registration page.</a></strong></p>';
$string['registeryourmaharasitedetail'] = '
<p>You can choose to register your Mahara Site with <a href="http://mahara.org/">mahara.org</a>. Registration is free, and helps us build up a picture of the Mahara installation base around the world.</p>
<p>You can see the information that will be sent to mahara.org - nothing that can personally identify any of your users will be sent.</p>
<p>If you tick &quot;send weekly updates&quot;, Mahara will automatically send an update to mahara.org once a week with your updated information.</p>
<p>Registering will remove this notice. You will be able to change whether you send weekly updates on the <a href="%sadmin/site/options.php">site options</a> page.</p>';
$string['siteregistered'] = 'Your site has been registered. You can turn weekly updates on and off on the <a href="%sadmin/site/options.php">site options</a> page.</p>';

// Close site
$string['Close'] = 'Close';
$string['closesite'] = 'Close Site';
$string['closesitedetail'] = 'You may close the site to everyone except administrators.  This will be useful when preparing for a database upgrade.  Only administrators will be able to log in until you either reopen the site, or an upgrade is successfully completed.';
$string['Open'] = 'Open';
$string['reopensite'] = 'Reopen Site';
$string['reopensitedetail'] = 'Your site is closed.  Site administrators may stay logged in until an upgrade is detected.';

// Statistics
$string['siteinformation'] = 'Site Information';
$string['viewfullsitestatistics'] = 'View Full Site Statistics';
$string['sitestatistics'] = 'Site Statistics';
$string['siteinstalled'] = 'Site Installed';
$string['databasesize'] = 'Database Size';
$string['diskusage'] = 'Disk Usage';
$string['maharaversion'] = 'Mahara version';
$string['activeusers'] = 'Active users';
$string['loggedinsince'] = '%s today, %s since %s, %s all time';
$string['groupmemberaverage'] = 'On average, each user is in %s groups';
$string['viewsperuser'] = 'Users who make pages have about %s pages each';
$string['Cron'] = 'Cron';
$string['runningnormally'] = 'Running normally';
$string['cronnotrunning'] = 'Cron is not running.<br>See the <a href="http://wiki.mahara.org/System_Administrator\'s_Guide/Installing_Mahara">installation guide</a> for instructions on how to set it up.';
$string['Loggedin'] = 'Logged in';
$string['youraverageuser'] = 'Your Average User...';
$string['statsmaxfriends'] = 'Has %s friends (most is <a href="%s">%s</a> with %d)';
$string['statsnofriends'] = 'Has 0 friends :(';
$string['statsmaxviews'] = 'Has made %s pages (most is <a href="%s">%s</a> with %d)';
$string['statsnoviews'] = 'Has made 0 pages :(';
$string['statsmaxgroups'] = 'Is in %s groups (most is <a href="%s">%s</a> with %d)';
$string['statsnogroups'] = 'Is in 0 groups :(';
$string['statsmaxquotaused'] = 'Has used about %s of disk quota (most is <a href="%s">%s</a> with %s)';
$string['groupcountsbytype'] = 'Number of groups by Group Type';
$string['groupcountsbyjointype'] = 'Number of groups by Access Type';
$string['blockcountsbytype'] = 'Most frequently used blocks in Portfolio Pages';
$string['uptodate'] = 'up to date';
$string['latestversionis'] = 'latest version is <a href="%s">%s</a>';
$string['viewsbytype'] = 'Pages by Type';
$string['userstatstabletitle'] = 'Daily User Statistics';
$string['groupstatstabletitle'] = 'Biggest Groups';
$string['viewstatstabletitle'] = 'Most Popular Pages';

// Site options
$string['adminsonly'] = 'Administrators only';
$string['adminsandstaffonly'] = 'Administrators and Staff only';
$string['advanced'] = 'Advanced';
$string['allowpublicviews'] = 'Allow public pages';
$string['allowpublicviewsdescription'] = 'If set to yes, users will be able to create portfolio pages that are accessable to the public rather than only to logged in users';
$string['allowpublicprofiles'] = 'Allow public profiles';
$string['allowpublicprofilesdescription'] = 'If set to yes, users will be able to set their profile pages to be accessable to the public rather than only to logged in users';
$string['anonymouscomments'] = 'Anonymous Comments';
$string['anonymouscommentsdescription'] = 'If checked, logged-out users can leave comments on public pages or pages they can access by secret URL.';
$string['antispam'] = 'Anti-spam';
$string['antispamdescription'] = 'The type of anti-spam measures used on publicly visible forms';
$string['defaultaccountinactiveexpire'] = 'Default account inactivity time';
$string['defaultaccountinactiveexpiredescription'] = 'How long a user account will remain active without the user logging in';
$string['defaultaccountinactivewarn'] = 'Warning time for inactivity/expiry';
$string['defaultaccountinactivewarndescription'] = 'The time before user accounts are to expire or become inactive at which a warning message will be sent to them';
$string['defaultaccountlifetime'] = 'Default account lifetime';
$string['defaultaccountlifetimedescription'] = 'If set, user accounts will expire after this period of time from when they have been created';
$string['embeddedcontent'] = 'Embedded content';
$string['embeddedcontentdescription'] = 'If you would like users to be able to embed videos or other outside content into their portfolios, you can choose which sites to trust below.';
$string['Everyone'] = 'Everyone';
$string['homepageinfo'] = 'Show home page information';
$string['homepageinfodescription'] = 'If enabled, information about Mahara and how it is used will be displayed on the Mahara home page. Logged in users will have the option to disable it.';
$string['institutionautosuspend'] = 'Auto-suspend expired institutions';
$string['institutionautosuspenddescription'] = 'If checked, expired institutions will be automatically suspended';
$string['institutionexpirynotification'] = 'Warning time for institution expiry';
$string['institutionexpirynotificationdescription'] = 'A notification message will be sent to site and institutional admins the long before a site expires';
$string['language'] = 'Language';
$string['none'] = 'None';
$string['country'] = 'Country';
$string['pathtoclam'] = 'Path to clam';
$string['pathtoclamdescription'] = 'The filesystem path to clamscan or clamdscan';
$string['registerterms'] = 'Registration agreement';
$string['registertermsdescription'] = "Force users to agree to the Terms and Conditions before registration.  You should edit your site's Terms and Conditions page before enabling this option.";
$string['allowmobileuploads'] = 'Allow Mobile Uploads';
$string['allowmobileuploadsdescription'] = 'If enabled users will have the option of setting an authentication token - content uploaded with this token will be saved as artefacts.';
$string['remoteavatars'] = 'Display remote avatars';
$string['remoteavatarsdescription'] = 'If checked, the <a href="http://www.gravatar.com">Gravatar</a> service will be used for users\' default profile pictures.';
$string['searchplugin'] = 'Search plugin';
$string['searchplugindescription'] = 'Search plugin to use';
$string['searchusernames'] = 'Search Usernames';
$string['searchusernamesdescription'] = 'Allow usernames to be searched on as part of "Search Users".';
$string['sessionlifetime'] = 'Session lifetime';
$string['sessionlifetimedescription'] = 'Time in minutes after which an inactive logged in user will be automatically logged out';
$string['setsiteoptionsfailed'] = 'Failed setting the %s option';
$string['showonlineuserssideblock'] = 'Show online users';
$string['showonlineuserssideblockdescription'] = 'If checked, users will see a side block on the right hand side of the site with a list of the online users.';
$string['showselfsearchsideblock'] = 'Enable Portfolio Search';
$string['showselfsearchsideblockdescription'] = 'Display the "Search My Portfolio" side block in the Portfolio section of the site';
$string['showtagssideblock'] = 'Enable Tag Cloud';
$string['showtagssideblockdescription'] = 'If enabled, users will see a side block in the Portfolio section of the site with a list of their most frequently used tags';
$string['simple'] = 'Simple';
$string['sitedefault'] = 'Site Default';
$string['sitelanguagedescription'] = 'The default language for the site';
$string['sitecountrydescription'] = 'The default country for the site';
$string['sitename'] = 'Site name';
$string['sitenamedescription'] = 'The site name appears in certain places around the site and in e-mails sent from the site';
$string['siteoptionspagedescription'] = 'Here you can set some global options that will apply by default throughout the entire site. <BR> Note: Disabled options are overridden by your config.php file.';
$string['siteoptionsset'] = 'Site options have been updated.';
$string['sitethemedescription'] = 'The default theme for the site - if your theme is not listed, check the error log.';
$string['smallviewheaders'] = 'Small page headers';
$string['smallviewheadersdescription'] = 'If enabled, a small header and site navigation block will be displayed when viewing or editing portfolio pages created by users.';
$string['spamhaus'] = 'Enable Spamhaus URL blacklist';
$string['spamhausdescription'] = 'If enabled, URLs will be checked against the Spamhaus DNSBL';
$string['surbl'] = 'Enable SURBL URL blacklist';
$string['surbldescription'] = 'If enabled, URLs will be checked against the SURBL DNSBL';
$string['disableexternalresources'] = 'Disable external resources in user HTML';
$string['disableexternalresourcesdescription'] = 'Disables the embedding of external resources, preventing users from embedding things like images from other hosts';
$string['tagssideblockmaxtags'] = 'Maximum Tags in Cloud';
$string['tagssideblockmaxtagsdescription'] = 'The default number of tags to display in user tag clouds';
$string['trustedsites'] = 'Trusted sites';
$string['updatesiteoptions'] = 'Update site options';
$string['usersallowedmultipleinstitutions'] = 'Users allowed multiple institutions';
$string['usersallowedmultipleinstitutionsdescription'] = 'If checked, users can be members of several institutions at the same time';
$string['userscanchooseviewthemes'] = 'Users can choose Page themes';
$string['userscanchooseviewthemesdescription'] = 'If enabled, users will be allowed to select a theme when editing or creating a portfolio page.  The page will be displayed to other users using the selected theme.';
$string['userscanhiderealnames'] = 'Users can hide real names';
$string['userscanhiderealnamesdescription'] = 'If checked, users who have set a display name may choose to be searchable only by their display name, and will not be found in searches for their real name.  (In the site administration section of the site, users are always searchable by their real names).';
$string['usersseenewthemeonlogin'] = 'Other users will see the new theme the next time they log in.';
$string['viruschecking'] = 'Virus checking';
$string['viruscheckingdescription'] = 'If checked, virus checking will be enabled for all uploaded files using ClamAV';
$string['whocancreategroups'] = 'Who can create Groups';
$string['whocancreategroupsdescription'] = 'Which users will be able to create new groups';
$string['whocancreatepublicgroups'] = 'Who can create Public Groups';
$string['whocancreatepublicgroupsdescription'] = 'Which users will be able to make groups that are viewable by the general public';
$string['wysiwyg'] = 'HTML editor';
$string['wysiwygdescription'] = 'Defines whether or not the HTML editor is enabled globally or whether users are allowed to enable/disable it themselves.';
$string['wysiwyguserdefined'] = 'User defined';

// Site content
$string['about']               = 'About';
$string['discardpageedits']    = 'Discard your changes to this page?';
$string['editsitepagespagedescription'] = 'Here you can edit the content of some pages around the site, such as the homepage (for logged in and out users separately), and the pages linked to in the footer.';
$string['home']                = 'Home (Dashboard)';
$string['loadsitepagefailed']  = 'Failed to load site page';
$string['loggedouthome']       = 'Logged out home';
$string['pagename']            = 'Page name';
$string['pagesaved']           = 'Page saved';
$string['pagetext']            = 'Page text';
$string['privacy']             = 'Privacy Statement';
$string['savechanges']         = 'Save changes';
$string['savefailed']          = 'Save failed';
$string['sitepageloaded']      = 'Site page loaded';
$string['termsandconditions']  = 'Terms and Conditions';
$string['uploadcopyright']     = 'Upload Copyright Statement';

// Links and resources menu editor
$string['sitefile']            = 'Site file';
$string['adminpublicdirname']  = 'public';  // Name of the directory in which to store public admin files
$string['adminpublicdirdescription'] = 'Files accessible by logged out users';
$string['badmenuitemtype']     = 'Unknown item type';
$string['confirmdeletemenuitem'] = 'Do you really want to delete this item?';
$string['deletingmenuitem']    = 'Deleting item';
$string['deletefailed']        = 'Failed deleting item';
$string['externallink']        = 'External link';
$string['editlinksandresources'] = 'Edit links and resources';
$string['linkedto']            = 'Linked to';
$string['linksandresourcesmenu'] = 'Links and Resources Menu';
$string['linksandresourcesmenupagedescription'] = 'The Links and Resources Menu appears to all users on most pages. You can add links to other websites and to files uploaded to the %sAdmin Files%s section.';
$string['loadingmenuitems']    = 'Loading items';
$string['loadmenuitemsfailed'] = 'Failed to load items';
$string['loggedinmenu']        = 'Logged in links and resources';
$string['loggedoutmenu']       = 'Public links and resources';
$string['menuitemdeleted']     = 'Item deleted';
$string['menuitemsaved']       = 'Item saved';
$string['menuitemsloaded']     = 'Items loaded';
$string['name']                = 'Name';
$string['nositefiles']         = 'No site files available';
$string['public']              = 'public';
$string['savingmenuitem']      = 'Saving item';
$string['type']                = 'Type';
$string['footermenu']          = 'Footer Menu';
$string['footermenudescription'] = 'Enable or disable the links in the footer.';
$string['footerupdated']       = 'Footer updated';

// Admin Files
$string['adminfilespagedescription'] = 'Here you can upload files that can be included in the %sLinks and Resources Menu%s. Files in the home directory will be able to be added to the logged in menu, while files in the public directory will be able to be added to the public menu.';

// Networking options
$string['networkingextensionsmissing'] = 'Sorry, you cannot configure Mahara networking because your PHP installation is missing one or more required extensions:';
$string['publickey'] = 'Public key';
$string['publickeydescription2'] = 'This public key is automatically generated, and rotated every %s days';
$string['publickeyexpires'] = 'Public key expires';
$string['enablenetworkingdescription'] = 'Allow your Mahara server to communicate with servers running Moodle and other applications';
$string['enablenetworking'] = 'Enable networking';
$string['networkingenabled'] = 'Networking has been enabled. ';
$string['networkingdisabled'] = 'Networking has been disabled. ';
$string['networkingpagedescription'] = 'Mahara\'s networking features allow it to communicate with Mahara or Moodle sites running on the same or another machine. If networking is enabled, you can use it to configure single-sign-on for users who log in at either Moodle or Mahara.';
$string['networkingunchanged'] = 'Network settings were not changed';
$string['promiscuousmode'] = 'Auto-register all hosts';
$string['promiscuousmodedisabled'] = 'Auto-register has been disabled. ';
$string['promiscuousmodeenabled'] = 'Auto-register has been enabled. ';
$string['promiscuousmodedescription'] = 'Create an institution record for any host that connects to you, and allow its users to log on to Mahara';
$string['wwwroot'] = 'WWW Root';
$string['wwwrootdescription'] = 'This is the URL at which your users access this Mahara installation, and the URL the SSL keys are generated for';
$string['proxysettings'] = 'Proxy settings';
$string['proxyaddress'] = 'Proxy address';
$string['proxyaddressdescription'] = 'If your site uses a proxy server to access the internet, specify the proxies in <em>hostname:portnumber</em> notation';
$string['proxyaddressset'] = 'Proxy address set';
$string['proxyauthmodel'] = 'Proxy authenticated model';
$string['proxyauthmodeldescription'] = 'Select your proxy\'s authentication model, if appropriate';
$string['proxyauthmodelbasic'] = 'Basic (NCSA)';
$string['proxyauthmodelset'] = 'Proxy authentication model has been set';
$string['proxyauthcredentials'] = 'Proxy credentials';
$string['proxyauthcredentialsdescription'] = 'Enter the credentials required for your proxy to authenticate your web server in <em>username:password</em> format';
$string['proxyauthcredntialsset'] = 'Proxy authentication credentials set';
$string['emailsettings'] = 'Email settings';
$string['emailsmtphosts'] = 'SMTP Host';
$string['emailsmtphostsdescription'] = 'SMTP server to be used for mail sending, e.g. <em>smtp1.example.com</em>';
$string['emailsmtpport'] = 'SMTP Port';
$string['emailsmtpportdescription'] = 'Specify port number if SMTP server uses port different from 25';
$string['emailsmtpuser'] = 'User';
$string['emailsmtpuserdescription'] = 'If SMTP server requires authentication, enter user credentials in the corresponding fields';
$string['emailsmtppass'] = 'Pass';
$string['emailsmtpsecure'] = 'SMTP Encryption';
$string['emailsmtpsecuredescription'] = 'If the SMTP server supports encryption, enable it here.';
$string['emailsmtpsecuressl'] = 'SSL';
$string['emailsmtpsecuretls'] = 'TLS';
$string['emailnoreplyaddress'] = 'System Mail Address';
$string['emailnoreplyaddressdescription'] = 'Emails come out as from this address';




// Upload CSV and CSV errors
$string['csvfile'] = 'CSV File';
$string['emailusersaboutnewaccount'] = 'E-mail users about their account?';
$string['emailusersaboutnewaccountdescription'] = 'Whether an e-mail should be sent to users informing them of their new account details';
$string['forceuserstochangepassword'] = 'Force password change?';
$string['forceuserstochangepassworddescription'] = 'Whether users should be forced to change their password when they log in for the first time';
$string['uploadcsvinstitution'] = 'The institution and authentication method for the new users';
$string['configureauthplugin'] = 'You must configure an authentication plugin before you can add users';
$string['csvfiledescription'] = 'The file containing users to add';
$string['csverroremptyfile'] = 'The csv file is empty.';
$string['invalidfilename'] = 'The file "%s" does not exist';
$string['uploadcsverrorinvalidfieldname'] = 'The field name "%s" is invalid, or you have more fields than your header row specifies';
$string['uploadcsverrorrequiredfieldnotspecified'] = 'A required field "%s" has not been specified in the format line';
$string['uploadcsverrornorecords'] = 'The file appears to contain no records (although the header is fine)';
$string['uploadcsverrorunspecifiedproblem'] = 'The records in your CSV file could not be inserted for some reason. If your file is in the correct format then this is a bug and you should <a href="https://eduforge.org/tracker/?func=add&group_id=176&atid=739">create a bug report</a>, attaching the CSV file (remember to blank out passwords!) and, if possible, the error log file';
$string['uploadcsverrorinvalidemail'] = 'Error on line %s of your file: The e-mail address for this user is not in correct form';
$string['uploadcsverrorincorrectnumberoffields'] = 'Error on line %s of your file: This line does not have the correct number of fields';
$string['uploadcsverrorinvalidpassword'] = 'Error on line %s of your file: Passwords must be at least six characters long and contain at least one digit and two letters';
$string['uploadcsverrorinvalidusername'] = 'Error on line %s of your file: The username for this user is not in correct form';
$string['uploadcsverrormandatoryfieldnotspecified'] = 'Line %s of the file does not have the required "%s" field';
$string['uploadcsverroruseralreadyexists'] = 'Line %s of the file specifies the username "%s" that already exists';
$string['uploadcsverroremailaddresstaken'] = 'Line %s of the file specifies the e-mail address "%s" that is already taken by another user';
$string['uploadcsvpagedescription2'] = '<p>You may use this facility to upload new users via a <acronym title="Comma Separated Values">CSV</acronym> file.</p>
   
<p>The first row of your CSV file should specify the format of your CSV data. For example, it should look like this:</p>

<pre>username,password,email,firstname,lastname,studentid</pre>

<p>This row must include the <tt>username</tt>, <tt>password</tt>, <tt>email</tt>, <tt>firstname</tt> and <tt>lastname</tt> fields. It must also includes fields that you have made mandatory for all users to fill out, and any fields locked for the institution you are uploading the users for. You can <a href="%s">configure the mandatory fields</a> for all institutions, or <a href="%s">configure the locked fields for each institution</a>.</p>

<p>Your CSV file may include any other profile fields as you require. The full list of fields is:</p>

%s';
$string['uploadcsvpagedescription2institutionaladmin'] = '<p>You may use this facility to upload new users via a <acronym title="Comma Separated Values">CSV</acronym> file.</p>

<p>The first row of your CSV file should specify the format of your CSV data. For example, it should look like this:</p>

<pre>username,password,email,firstname,lastname,studentid</pre>

<p>This row must include the <tt>username</tt>, <tt>password</tt>, <tt>email</tt>, <tt>firstname</tt> and <tt>lastname</tt> fields. It must also include any fields that the site administrator has made mandatory, and any fields that are locked for your institution. You can <a href="%s">configure the locked fields</a> for the institution(s) you manage.</p>

<p>Your CSV file may include any other profile fields as you require. The full list of fields is:</p>

%s';
$string['uploadcsvsomeuserscouldnotbeemailed'] = 'Some users could not be e-mailed. Their e-mail addresses may be invalid, or the server Mahara is running on might not be configured to send e-mail properly. The server error log has more details. For now, you may want to contact these people manually:';
$string['uploadcsvusersaddedsuccessfully'] = 'The users in the file have been added successfully';
$string['uploadcsvfailedusersexceedmaxallowed'] = 'No users have been added because there are too many users in your file.  The number of users in the institution would have exceeded the maximum number allowed.';

// Bulk Leap2A import
$string['bulkleap2aimport'] = 'Import users from Leap2A files';
$string['bulkleap2aimportdescription'] = '<p>You can import users in bulk from a collection of Leap2A files on your server.  You must specify a zip file on the server filesystem which contains all the Leap2A zip files, and a single CSV file called usernames.csv, mapping usernames to filenames.</p>
<p>usernames.csv will look something like this:</p>
<pre>
&nbsp;&nbsp;bob,mahara-export-leap-user8-1265165366.zip<br>
&nbsp;&nbsp;nigel,mahara-export-leap-user1-1266458159.zip
</pre>
<p>where mahara-export-leap-user8-1265165366.zip and mahara-export-leap-user1-1266458159.zip are files in a subdirectory called users.</p>
<p>This zip file should normally be generated using the bulk export built into Mahara.</p>
<p>If you are importing a lot of users, please be patient.  The import process can take a long time.</p>';
$string['importfile'] = 'Bulk export file';
$string['importfilemissinglisting'] = 'The bulk export file is missing a file named usernames.csv. Did you use the Mahara bulk exporter to export these users?';
$string['importfilenotafile'] = 'Error during form submission: file was not recognised';
$string['importfilenotreadable'] = 'Error during form submission: file was not readable';
$string['bulkleap2aimportfiledescription'] = 'The zip file on your server containing all exported users (in Leap2A format) along with a CSV listing of usernames';
$string['unzipnotinstalled'] = 'Your system does not have the unzip command, or $cfg->pathtounzip is misconfigured. Please install unzip to enable importing a zipped export file or fix the $cfg->pathtounzip setting.';
$string['importednuserssuccessfully'] = 'Imported %d of %d users successfully';
$string['Import'] = 'Import';
$string['bulkimportdirdoesntexist'] = 'The directory %s does not exist';
$string['unabletoreadbulkimportdir'] = 'The directory %s is unreadable';
$string['unabletoreadcsvfile'] = 'Unable to read csv file %s';
$string['importfilenotreadable'] = 'Unable to read Leap2A file %s';
$string['importfileisnotazipfile'] = 'Import file %s was not detected as a zip file';
$string['unzipfailed'] = 'Failed to unzip the Leap2A file %s. See the error log for more information.';
$string['importfailedfornusers'] = 'Import failed for %d of %d users';
$string['invalidlistingfile'] = 'Invalid username listing. Did you use the Mahara bulk exporter to export these users?';

// Admin Users
$string['adminuserspagedescription'] = '<p>Here you can choose which users are administrators for the site. The current administrators are listed on the right, and potential administrators are on the left.</p><p>The system must have at least one administrator.</p>';
$string['institutionadminuserspagedescription'] = 'Here you can choose which users are administrators for the institution. The current administrators are listed on the right, and potential administrators are on the left.';
$string['potentialadmins'] = 'Potential Admins';
$string['currentadmins'] = 'Current Admins';
$string['adminusersupdated'] = 'Admin users updated';

// Staff Users
$string['staffuserspagedescription'] = 'Here you can choose which users are staff for the site. The current staff are on the right, and potential staff are on the left.';
$string['institutionstaffuserspagedescription'] = 'Here you can choose which users are staff for your institution. The current staff are on the right, and potential staff are on the left.';
$string['potentialstaff'] = 'Potential Staff';
$string['currentstaff'] = 'Current Staff';
$string['staffusersupdated'] = 'Staff users updated';

// Admin Notifications

// Suspended Users
$string['deleteusers'] = 'Delete Users';
$string['deleteuser'] = 'Delete User';
$string['confirmdeleteusers'] = 'Are you sure you want to delete the selected users?';
$string['exportingnotsupportedyet'] = 'Exporting user profiles is not supported yet';
$string['exportuserprofiles'] = 'Export User Profiles';
$string['nousersselected'] = 'No users selected';
$string['suspenduser'] = 'Suspend User';
$string['suspendedusers'] = 'Suspended Users';
$string['suspensionreason'] = 'Suspension reason';
$string['errorwhilesuspending'] = 'An error occured while trying to suspend';
$string['suspendedusersdescription'] = 'Suspend or reactivate users from using the site';
$string['unsuspendusers'] = 'Unsuspend Users';
$string['usersdeletedsuccessfully'] = 'Users deleted successfully';
$string['usersunsuspendedsuccessfully'] = 'Users unsuspended successfully';
$string['suspendingadmin'] = 'Suspending Admin';
$string['usersuspended'] = 'User suspended';
$string['userunsuspended'] = 'User unsuspended';

// User account settings
$string['accountsettings'] = 'Account settings';
$string['siteaccountsettings'] = 'Site account settings';
$string['changeusername'] = 'Change username';
$string['changeusernamedescription'] = 'Change this user\'s username. Usernames are 3-236 characters long, and may contain letters, numbers, and most common symbols excluding spaces.';
$string['resetpassword'] = 'Reset password';
$string['resetpassworddescription'] = 'If you enter text here, it will replace the user\'s current password.';
$string['forcepasswordchange'] = 'Force password change on next login';
$string['forcepasswordchangedescription'] = 'The user will be directed to a change password page the next time they login.';
$string['sitestaff'] = 'Site Staff';
$string['siteadmins'] = 'Site Admins';
$string['siteadmin'] = 'Site Administrator';
$string['accountexpiry'] = 'Account expires';
$string['accountexpirydescription'] = 'Date on which the user\'s login is automatically disabled.';
$string['suspended'] = 'Suspended';
$string['suspendedreason'] = 'Reason for suspension';
$string['suspendedreasondescription'] = 'The text that will be displayed to the user on their next login attempt.';
$string['unsuspenduser'] = 'Unsuspend User';
$string['thisuserissuspended'] = 'This user has been suspended';
$string['suspendedby'] = 'This user has been suspended by %s';
$string['deleteuser'] = 'Delete User';
$string['userdeletedsuccessfully'] = 'User deleted successfully';
$string['confirmdeleteuser'] = 'Are you sure you want to delete this user?';
$string['filequota'] = 'File quota (MB)';
$string['filequotadescription'] = 'Total storage available in the user\'s files area.';
$string['addusertoinstitution'] = 'Add User to Institution';
$string['removeuserfrominstitution'] = 'Remove user from this institution';
$string['confirmremoveuserfrominstitution'] = 'Are you sure you want to remove the user from this institution?';
$string['usereditdescription'] = 'Here you can view and set details for this user account. Below, you can also <a href="#suspend">suspend or delete this account</a>, or change settings for this user in the <a href="#institutions">institutions they are in</a>.';
$string['usereditwarning'] = 'NOTE: Saving the account changes will cause the user to be logged out (if currently logged in)';
$string['suspenddeleteuser'] = 'Suspend/Delete User';
$string['suspenddeleteuserdescription'] = 'Here you may suspend or entirely delete a user account. Suspended users are unable to log in until their account is unsuspended. Please note that while a suspension can be undone, deletion <strong>cannot</strong> be undone.';
$string['deleteusernote'] = 'Please note that this operation <strong>cannot be undone</strong>.';
$string['youcannotadministerthisuser'] = 'You cannot administer this user';
$string['usernamechangenotallowed'] = 'The chosen authentication method does not allow changes to the username.';
$string['passwordchangenotallowed'] = 'The chosen authentication method does not allow changes to the password.';

// Add User
$string['adduser'] = 'Add User';
$string['adduserdescription'] = 'Create a new user';
$string['basicinformationforthisuser'] = 'Basic information for this user.';
$string['clickthebuttontocreatetheuser'] = 'Click the button to create the user.';
$string['createnewuserfromscratch'] = 'Create new user from scratch';
$string['createuser'] = 'Create User';
$string['failedtoobtainuploadedleapfile'] = 'Failed to obtain the uploaded Leap2A file';
$string['failedtounzipleap2afile'] = 'Failed to unzip the Leap2A file. Check the error log for more information';
$string['fileisnotaziporxmlfile'] = 'This file has not been detected to be a zipfile or XML file';
$string['howdoyouwanttocreatethisuser'] = 'How do you want to create this user?';
$string['leap2aimportfailed'] = '<p><strong>Sorry - Importing the Leap2A file failed.</strong></p><p>This could be because you did not select a valid Leap2A file to upload or because the version of your LEAP2A file is not supported by this Mahara version. Alternatively, there may be a bug in Mahara causing your file to fail, even though it is valid.</p><p>Please <a href="add.php">go back and try again</a>, and if the problem persists, you may want to post to the <a href="http://mahara.org/forums/">Mahara Forums</a> to ask for help. Be prepared to be asked for a copy of your file!</p>';
$string['newuseremailnotsent'] = 'Failed to send welcome email to new user.';
$string['newusercreated'] = 'New user account created successfully';
$string['noleap2axmlfiledetected'] = 'No leap2a.xml file detected - please check your export file again';
$string['Or...'] = 'Or...';
$string['userwillreceiveemailandhastochangepassword'] = 'They will receive an e-mail informing them of their new account details. On first log in, they will be forced to change their password.';
$string['uploadleap2afile'] = 'Upload Leap2A File';

$string['usercreationmethod'] = '1 - User Creation Method';
$string['basicdetails'] = '2 - Basic Details';
$string['create'] = '3 - Create';

// Login as
$string['loginas'] = 'Login as';
$string['loginasuser'] = 'Login as %s';
$string['becomeadminagain'] = 'Become %s again';
// Login-as exceptions
$string['loginasdenied'] = 'Attempt to login as another user without permission';
$string['loginastwice'] = 'Attempt to login as another user when already logged in as another user';
$string['loginasrestorenodata'] = 'No user data to restore';
$string['loginasoverridepasswordchange'] = 'As you are masquerading as another user, you may choose to %slog in anyway%s, ignoring the password change screen.';

// Institutions
$string['Add'] = 'Add';
$string['admininstitutions'] = 'Administer Institutions';
$string['adminauthorities'] = 'Administer Authorities';
$string['addinstitution'] = 'Add Institution';
$string['authplugin'] = 'Authentication plugin';
$string['deleteinstitution'] = 'Delete Institution';
$string['deleteinstitutionconfirm'] = 'Are you really sure you wish to delete this institution?';
$string['institutionaddedsuccessfully2'] = 'Institution added successfully';
$string['institutiondeletedsuccessfully'] = 'Institution deleted successfully';
$string['noauthpluginforinstitution'] = 'Your site administrator has not configured an authentication plugin for this institution.';
$string['adminnoauthpluginforinstitution'] = 'Please configure an authentication plugin for this institution.';
$string['institutionname'] = 'Institution name';
$string['institutionnamealreadytaken'] = 'This institution name is already taken';
$string['institutiondisplayname'] = 'Institution display name';
$string['institutionexpiry'] = 'Institution expiry date';
$string['institutionexpirydescription'] = 'The date at which this institutions membership of %s will be suspended.';
$string['institutionupdatedsuccessfully'] = 'Institution updated successfully.';
$string['registrationallowed'] = 'Registration allowed?';
$string['registrationalloweddescription2'] = 'Whether users can register for your site for this institution using the registration form.  If registration is off, non-members cannot request membership of the institution, and members cannot leave the institution or delete their user accounts voluntarily.';
$string['defaultmembershipperiod'] = 'Default membership period';
$string['defaultmembershipperioddescription'] = 'How long new members remain associated with the institution';

$string['authenticatedby'] = 'Authentication Method';
$string['authenticatedbydescription'] = 'How this user authenticates to Mahara';
$string['remoteusername'] = 'Username for external authentication';
$string['remoteusernamedescription'] = 'If this user signs in to %s from a remote site using the XMLRPC authentication plugin, this is the username which identifies the user on the remote site';
$string['institutionsettings'] = 'Institution Settings';
$string['institutionsettingsdescription'] = 'Here you can change settings regarding this user\'s membership with institutions in the system.';
$string['changeinstitution'] = 'Change Institution';
$string['institutionstaff'] = 'Institution Staff';
$string['institutionadmins'] = 'Institution Administrators';
$string['institutionadmin'] = 'Institution Admin';
$string['institutionadministrator'] = 'Institution Administrator';
$string['institutionadmindescription'] = 'If checked, the user can administer all users in this institution.';
$string['settingsfor'] = 'Settings for:';
$string['institutionadministration'] = 'Institution Administration';
$string['institutionmembers'] = 'Institution Members';
$string['notadminforinstitution'] = 'You are not an administrator for that institution';
$string['institutionmemberspagedescription'] = 'On this page you can see users who have requested membership of your institution and add them as members.  You can also remove users from your institution, and invite users to join.';

$string['institutionusersinstructionsrequesters'] = 'The list of users on the left shows all users who have asked to join your institution.  You can use the search box to reduce the number of users displayed.  If you would like to add users to the institution, or decline their membership requests, first move some users to the right hand side by selecting one or more users and then clicking on the right arrow.  The "Add members" button will add all the users on the right to the institution.  The "Decline requests" button will remove the membership requests of the users on the right.';
$string['institutionusersinstructionsnonmembers'] = 'The list of users on the left shows all users who are not yet members of your institution.  You can use the search box to reduce the number of users displayed.  To invite users to join the institution, first move some users to the right hand side by selecting one or more users and then clicking on the right arrow to move those users to the list on the right.  The "Invite Users" button will send invitations to all the users on the right.  These users will not be associated with the institution until they accept the invitation.';
$string['institutionusersinstructionsmembers'] = 'The list of users on the left shows all members of the institution.  You can use the search box to reduce the number of users displayed.  To remove users from the institution, first move some users to the right hand side by selecting one or more users on the left and then clicking on the right arrow.  The users you selected will move to the right hand side.  The "Remove Users" button will remove all the users on the right from the institution.  The users on the left will remain in the institution.';

$string['editmembers'] = 'Edit Members';
$string['editstaff'] = 'Edit Staff';
$string['editadmins'] = 'Edit Admins';
$string['membershipexpiry'] = 'Membership expires';
$string['membershipexpirydescription'] = 'Date on which the user will be automatically removed from the institution.';
$string['studentid'] = 'ID Number';
$string['institutionstudentiddescription'] = 'An optional identifier specific to the institution.  This field is not editable by the user.';

$string['userstodisplay'] = 'Users to display:';
$string['institutionusersrequesters'] = 'People who have requested institution membership';
$string['institutionusersnonmembers'] = 'People who have not requested membership yet';
$string['institutionusersmembers'] = 'People who are already institution members';

$string['addnewmembers'] = 'Add new members';
$string['usersrequested'] = 'Users who have requested membership';
$string['userstobeadded'] = 'Users to be added as members';
$string['userstoaddorreject'] = 'Users to be added/rejected';
$string['addmembers'] = 'Add members';
$string['inviteuserstojoin'] = 'Invite users to join the institution';
$string['Non-members'] = 'Non-members';
$string['userstobeinvited'] = 'Users to be invited';
$string['inviteusers'] = 'Invite Users';
$string['removeusersfrominstitution'] = 'Remove users from the institution';
$string['currentmembers'] = 'Current Members';
$string['userstoberemoved'] = 'Users to be removed';
$string['removeusers'] = 'Remove Users';
$string['declinerequests'] = 'Decline requests';
$string['nousersupdated'] = 'No users were updated';

$string['institutionusersupdated_addUserAsMember'] = 'Users added';
$string['institutionusersupdated_declineRequestFromUser'] = 'Requests declined';
$string['institutionusersupdated_removeMembers'] = 'Users removed';
$string['institutionusersupdated_inviteUser'] = 'Invitations sent';

$string['maxuseraccounts'] = 'Maximum User Accounts Allowed';
$string['maxuseraccountsdescription'] = 'The maximum number of user accounts that can be associated with the institution.  If there is no limit, this field should be left blank.';
$string['institutionmaxusersexceeded'] = 'This institution is full, you will have to increase the number of allowed users in this institution before this user can be added.';
$string['institutionuserserrortoomanyusers'] = 'The users were not added.  The number of members cannot exceed the maximum allowed for the institution.  You can add fewer users, remove some users from the institution, or ask the site administrator to increase the maximum number of users.';
$string['institutionuserserrortoomanyinvites'] = 'Your invitations were not sent.  The number of existing members plus the number of outstanding invitations cannot exceed the institution\'s maximum number of users.  You can invite fewer users, remove some users from the institution, or ask the site administrator to increase the maximum number of users.';

$string['Members'] = 'Members';
$string['Maximum'] = 'Maximum';
$string['Staff'] = 'Staff';
$string['Admins'] = 'Admins';

$string['noinstitutions'] = 'No Institutions';
$string['noinstitutionsdescription'] = 'If you would like to associate users with an institution, you should create the institution first.';

$string['Lockedfields'] = 'Locked fields';
$string['disabledlockedfieldhelp'] = 'Note: Disabled check boxes are for profile fields which are locked in the institution settings for "%s".  These profile fields are locked at the site level and cannot be unlocked here.';

// Suspend Institutions
$string['errorwhileunsuspending'] = 'An error occured while trying to unsuspend';
$string['institutionsuspended'] = 'Institution suspended';
$string['institutionunsuspended'] = 'Institution unsuspended';
$string['suspendedinstitution'] = 'SUSPENDED';
$string['suspendinstitution'] = 'Suspend Institution';
$string['suspendinstitutiondescription'] = 'Here you may suspend an institution. Users of suspended institutions will be unable to log in until the institution is unsuspended.';
$string['suspendedinstitutionmessage'] = 'This institution has been suspended';
$string['unsuspendinstitution'] = 'Unsuspend Institution';
$string['unsuspendinstitutiondescription'] = 'Here you may unsuspend an institution. Users of suspended institutions will be unable to log in until the institution is unsuspended.<br /><strong>Beware:</strong> Unsuspending an institution without resetting or turning off its expiry date may result in a daily re-suspension.';
$string['unsuspendinstitutiondescription_top'] = '<em>Beware:</em> Unsuspending an institution without resetting or turning off its expiry date may result in a daily re-suspension.';
$string['unsuspendinstitutiondescription_top_instadmin'] = 'Users of suspended institutions are unable to log in. Contact site administrator to unsuspend the institution.';

// Bulk Leap2A User export
$string['bulkexport'] = 'Export users';
$string['bulkexportempty'] = 'Nothing suitable to export. Please double-check the list of usernames.';
$string['bulkexportinstitution'] = 'The institution from which all users should be exported';
$string['bulkexporttitle'] = 'Export users to Leap2A files';
$string['bulkexportdescription'] = 'Choose an institution to export <b>OR</b> specify a list of usernames:';
$string['bulkexportusernames'] = 'Usernames to export';
$string['bulkexportusernamesdescription'] = 'A list of the users (one username per line) to be exported along with their data';
$string['couldnotexportusers'] = 'The following user(s) could not be exported: %s';
$string['exportingusername'] = 'Exporting \'%s\'...';

// Admin User Search
$string['Search'] = 'Search';
$string['Institution'] = 'Institution';
$string['confirm'] = 'confirm';
$string['invitedby'] = 'Invited by';
$string['requestto'] = 'Request to';
$string['useradded'] = 'User added';
$string['invitationsent'] = 'Invitation sent';

// general stuff
$string['notificationssaved'] = 'Notification settings saved';
$string['onlyshowingfirst'] = 'Only showing first';
$string['resultsof'] = 'results of';

$string['installed'] = 'Installed';
$string['errors'] = 'Errors';
$string['install'] = 'Install';
$string['reinstall'] = 'Reinstall';

// spam trap names
$string['None'] = 'None';
$string['Simple'] = 'Simple';
$string['Advanced'] = 'Advanced';

//admin option fieldset legends
$string['sitesettingslegend'] = 'Site Settings';
$string['usersettingslegend'] = 'User Settings';
$string['groupsettingslegend'] = 'Group Settings';
$string['searchsettingslegend'] = 'Search Settings';
$string['institutionsettingslegend'] = 'Institution Settings';
$string['accountsettingslegend'] = 'Account Settings';
$string['securitysettingslegend'] = 'Security Settings';
$string['generalsettingslegend'] = 'General Settings';

$string['groupname'] = 'Group Name';
$string['groupmembers'] = 'Members';
$string['groupadmins'] = 'Admins';
$string['grouptype'] = 'Group Type';
$string['groupvisible'] = 'Visibility';
$string['groupmanage'] = 'Manage';
$string['groupdelete'] = 'Delete';
$string['managegroupdescription'] = 'Use the form below to promote and demote admins for this group.  If you remove a group administrator they will remain a group member.';
