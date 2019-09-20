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

$string['administration'] = 'Administration';

// Installer
$string['installation'] = 'Installation';
$string['release'] = 'version %s (%s)';
$string['copyright'] = 'Copyright &copy; 2006 onwards, <a href="https://wiki.mahara.org/wiki/Contributors">Catalyst IT and others</a>';
$string['installmahara'] = 'Install Mahara';
$string['component'] = 'Component or plugin';
$string['continue'] = 'Continue';
$string['coredata'] = 'core data';
$string['coredatasuccess'] = 'Successfully installed core data';
$string['fromversion'] = 'From version';
$string['Information'] = 'Information';
$string['installingplugin'] = 'Installing %s';
$string['installsuccess'] = 'Successfully installed version ';
$string['toversion'] =  'To version';
$string['localdatasuccess'] = 'Successfully installed local customisations';
$string['notinstalled'] = 'Not installed';
$string['nothingtoupgrade'] = 'Nothing to upgrade';
$string['performinginstallation'] = 'Performing installation...';
$string['performingupgrades'] = 'Performing upgrades...';
$string['runupgrade'] = 'Run upgrade';
$string['gotoinstallpage'] = 'Install via extensions page';
$string['successfullyinstalled'] = 'Successfully installed Mahara.';
$string['thefollowingupgradesareready'] = 'The following upgrades are ready:';
$string['thefollowingpluginsareready'] = 'The following new plugins are available:';
$string['registerthismaharasite'] = 'Register this Mahara site';
$string['upgradeloading'] = 'Loading...';
$string['upgrades'] = 'Upgrades';
$string['newplugins'] = 'New plugins';
$string['upgradingplugin'] = 'Upgrading %s';
$string['upgradesuccess'] = 'Successfully upgraded';
$string['upgradesuccesstoversion'] = 'Successfully upgraded to version ';
$string['upgradefailure'] = 'Failed to upgrade.';
$string['noupgrades'] = 'Nothing to upgrade. You are fully up to date.';
$string['youcanupgrade'] = 'You can upgrade Mahara from %s (%s) to %s (%s).';
$string['upgradeinprogress'] = 'An upgrade began at %s and did not complete. <a href="?rerun=1">Run this upgrade anyway.</a>';
$string['Plugin'] = 'Plugin';
$string['jsrequiredforupgrade'] = 'You must enable JavaScript to perform an install or upgrade.';
$string['dbnotutf8warning'] = 'You are not using a UTF-8 database. Mahara stores all data as UTF-8 internally. You may still attempt this upgrade, but it is recommended that you convert your database to UTF-8.';
$string['dbcollationmismatch'] = 'A column of your database is using a collation that is not the same as the database default. Please ensure all columns use the same collation as the database.';
$string['maharainstalled'] = 'Mahara is already installed.';
$string['cliadminpassword'] = 'The password for the admin user';
$string['cliadminemail'] = 'The email address for the admin user';
$string['clisitename'] = 'The site name';
$string['cliupdatesitenamefailed'] = 'Updating site name failed.';
$string['cliinstallerdescription'] = 'Install Mahara and create required data directories';
$string['cliinstallingmahara'] = 'Installing Mahara';
$string['cliupgraderdescription'] = 'Upgrade the Mahara database and data to the version of Mahara installed';
$string['cliupgradingmahara'] = 'Upgrading Mahara';
$string['cliclearingcaches'] = 'Clearing Mahara caches.';
$string['cliclearcachesdescription'] = 'Clearing caches will delete cached data from the server. There is no danger in clearing caches, but your site may appear slower for a while until the server and clients calculate new information and cache it.';
$string['clearcachesheading'] = 'Clear caches';
$string['clearcachessubmit'] = 'Clear caches';
$string['clearingcachessucceed'] = 'All caches were cleared.';
$string['clearingcacheserror'] = 'Error while clearing caches. Please check logs to get more information about this error.';
$string['maharanotinstalled'] = 'Mahara is not currently installed. Please install Mahara before trying to upgrade.';

// Admin navigation menu
$string['adminhome']      = 'Admin home';
$string['configsite']  = 'Configure site';
$string['configusers'] = 'Manage users';
$string['groups'] = 'Groups';
$string['managegroups'] = 'Manage groups';
$string['Extensions']   = 'Extensions';
$string['configextensions']   = 'Administer extensions';
$string['manageinstitutions'] = 'Manage institutions';

// Admin homepage strings
$string['siteoptions']    = 'Site options';
$string['siteoptionsdesc'] = 'Configure basic site options such as the name, language, and theme';
$string['staticpages']     = 'Static pages';
$string['institutionstaticpages']     = 'Institution static pages';
$string['staticpageinstitutionbad'] = "You can't access and/or edit static pages for institution '%s'";
$string['usedefault'] = 'Use site default';
$string['usedefaultdescription3'] = 'Use the site\'s default text for the selected page type.';
$string['staticpagesdesc'] = 'Edit the content of the hompage and "About"';
$string['staticpagesinstdesc'] = 'Edit the content of an institution\'s static pages';
$string['institutionprivacypagedescription'] = 'Edit the privacy statement for your institution, which is displayed in addition to the site privacy statement. The version you edited last becomes the current privacy statement automatically. You see the current site privacy statement as reference.';
$string['institutiontermspagedescription'] = 'Edit the terms and conditions for your institution, which are displayed in addition to the site terms and conditions. The version you edited last becomes the current terms and conditions automatically. You see the current site terms and conditions as reference.';
$string['institutionprivacytermsdesc'] = 'Add or edit an institution\'s privacy statement or terms and conditions';
$string['noinstitutionprivacy'] = 'There is no privacy statement for this institution yet.';
$string['noinstitutionterms'] = 'There are no terms and conditions for this institution yet.';
$string['menus'] = 'Menus';
$string['menusdescription'] = 'Manage the links and files within the "Links and resources" and footer menus';
$string['sharesitefilesdesc'] = 'Share site pages and collections';
$string['shareinstitutionfilesdesc'] = 'Share institution pages and collections';
$string['sitefiles']          = 'Site files';
$string['sitefonts'] = 'Fonts';
$string['sitefontsdesc'] = 'Upload and manage fonts that everyone can use in skins';
$string['sitelicenses'] = 'Licenses';
$string['sitelicensesadd'] = 'Add license';
$string['sitelicensesdesc']     = 'Configure the list of licenses that people can use on their content';
$string['sitelicensesdisablednote']     = '<b>Note</b>: License metadata is currently disabled. You will need to enable it in the "General settings" section of "<a href="%sadmin/site/options.php">Configure site</a>" before users will be able to specify licenses.';
$string['sitelicensesedit'] = 'Edit license';
$string['sitefilesdescription'] = 'Upload and administer files that can be put in the "Links and resources" menu and in site pages';
$string['siteskins'] = 'Site skins';
$string['siteskinsdesc'] = 'Create and administer page skins that can be used by everyone';
$string['siteviews']          = 'Site pages';
$string['siteviewscollections'] = 'Site pages and collections';
$string['siteviewsdesc'] = 'Create and administer pages, page templates, and collections for the entire site';
$string['networking']          = 'Networking';
$string['networkingdesc'] = 'Configure networking to another site';
$string['cookieconsent'] = 'Cookie Consent';
$string['cookieconsentdesc'] = 'Configure the "Cookie Consent" user privacy system';
$string['thememissing'] = 'The theme "%s" is missing. The theme has been set to the default theme.';
$string['parentthememissing'] = 'Theme "%s" has its parent theme "%s" missing and needs to be fixed. The theme has been set to the default theme.';
$string['staffusers'] = 'Staff users';
$string['staffusersdesc'] = 'Assign site staff permissions to users';
$string['adminusers'] = 'Admin users';
$string['adminusersdescription'] = 'Assign site administrator access rights';
$string['settings']   = 'Settings';
$string['institution']   = 'Institution';
$string['institutions']   = 'institutions';
$string['Institutions']   = 'Institutions';
$string['institutiondetails']   = 'Institution details';
$string['institutionauth']   = 'Institution authorities';
$string['institutionsettingsdesc'] = 'Set up and manage institutions';
$string['lastinstitution'] = 'Previous institution';
$string['adminnotifications'] = 'Admin notifications';
$string['adminnotificationsdescription'] = 'Overview of how administrators receive system notifications';
$string['uploadcsv'] = 'Add users by CSV';
$string['uploadcsvdesc'] = 'Upload a CSV file containing new users or update existing ones';
$string['uploadgroupcsv'] = 'Add groups by CSV';
$string['uploadgroupmemberscsv'] = 'Update group members by CSV';
$string['usersearch'] = 'User search';
$string['usersearchdescription'] = 'Search all users and perform administrative actions on them';
$string['usersearchinstructions'] = 'You can search for users by clicking on the initials of their first and last names or by entering a name in the search box. You can also enter an email address in the search box if you would like to search email addresses.';
$string['emailaddresshidden'] = 'Email address hidden';
$string['inactive'] = 'User not active';
$string['inactivefor'] = 'User "%s" is currently not active';

$string['administergroups'] = 'Administer groups';
$string['administergroupsdesc'] = 'Manage groups, their administrators, and space available';
$string['groupcategoriesdescription'] = 'Add and edit group categories';
$string['uploadgroupcsvdesc'] = 'Upload a CSV file containing new groups or update existing ones';
$string['uploadgroupmemberscsvdescription'] = 'Upload a CSV file containing members for groups';

$string['institutionmembersdescription'] = 'Associate users with institutions';
$string['institutionstaffdesc'] = 'Assign institution staff permissions to users';
$string['institutionadminsdescription'] = 'Assign institution administrator access rights';
$string['institutionviews']          = 'Institution pages';
$string['institutionviewsdesc'] = 'Create and administer pages, page templates, and collections for an institution';
$string['institutionfiles']          = 'Institution files';
$string['institutionfilesdescription'] = 'Upload and manage files for use in institution pages';
$string['pluginsfields'] = 'Plugins settings';

$string['pluginadmin'] = 'Plugin administration';
$string['pluginadmindescription'] = 'Install and configure plugins';
$string['missingplugindisabled1'] = 'The installed plugin "%s" could not be found and has been disabled';
$string['installedpluginsmissing'] = 'The following plugins are installed but can no longer be found';
$string['ensurepluginsexist'] = 'Please make sure all your installed plugins are available under %s and readable by the webserver.';

$string['htmlfilters'] = 'HTML filters';
$string['htmlfiltersdescription'] = 'Enable new filters for HTML Purifier';
$string['newfiltersdescription'] = 'If you have downloaded a new set of HTML filters, you can install them by unzipping the file into the folder %s and then clicking the button below.';
$string['filtersinstalled'] = 'Filters installed.';
$string['nofiltersinstalled'] = 'No HTML filters installed.';

$string['allowediframesites'] = 'Allowed iframe sources';
$string['iframesitesdescriptionshort'] = 'Whitelist the embedding of external content available via iframes';
$string['allowediframesitesdescription'] = 'Users are allowed to embed content from the following external sites on their pages, inside HTML &lt;iframe&gt; elements. Typically this is used to display content hosted elsewhere. The list of allowed sites can be modified on this page.';
$string['allowediframesitesdescriptiondetail'] = 'The icon and display name will be visible to users when they configure an external media block. All sites with the same display name are grouped together in the configuration form, but iframe source text matching any of the sites will be allowed.';
$string['iframeurldescription'] = "Text to match at the beginning of the iframe source URL (without the http://). Only letters, digits and the characters '.', '/', '_', and '-' are allowed.";
$string['iframedisplaynamedescription'] = 'The name of the site to be displayed to users.';
$string['iframeinvalidsite'] = "This field should contain a valid host and an optional path. It can contain only letters, digits, '.', '/', '_', and '-'.";
$string['iframeiconhost'] = 'Icon host';
$string['urlalreadyexists'] = 'This URL already exists. You cannot add it twice.';
$string['iframeiconhostdescription'] = 'If you wish, you may specify a different host for the favicon image. All sites with the same name will use this icon.';

$string['cleanurls'] = 'Clean URLs';
$string['cleanurlsdescription'] = "
<p>You can configure your site to use human-readable URLs for user profiles, group homepages and portfolio pages. For example,
<ul><li>http://mahara.example.com/user/bob</li>
<li>http://mahara.example.com/group/bobs-group</li>
<li>http://mahara.example.com/user/bob/bobs-portfolio-page</li>
</ul>
Before enabling this option, your server administrator must configure your web server so that incoming requests have their URLs rewritten.</p>
<p>See <a href=\"https://wiki.mahara.org/wiki/System_Administrator's_Guide/Clean_URL_Configuration\">Clean URL Configuration</a> on the Mahara wiki for instructions on how to do this.</p>
";
$string['cleanurlsdisabled'] = 'Clean URLs are disabled.';
$string['cleanurlsettings'] = 'Clean URL settings';
$string['regenerateurls'] = 'Regenerate URLs';
$string['regenerateurlsdescription'] = 'This will remove all clean URLs from the site and automatically regenerate them using usernames, group names and page titles.';
$string['regenerateurlsconfirm'] = 'Are you sure you want to do this? It will replace all existing URLs chosen by users.';
$string['generateduserurls'] = array(
    'Generated 1 profile URL',
    'Generated %s profile URLs',
);
$string['generatedgroupurls'] = array(
    'Generated 1 group homepage URL',
    'Generated %s group homepage URLs',
);
$string['generatedviewurls'] = array(
    'Generated 1 portfolio page URL',
    'Generated %s portfolio page URLs',
);
$string['cleanurlsdescriptionshort'] = 'Configure site to use human-readable URLs';

// sanity check warnings
$string['warnings'] = 'Warning';

// Group management
$string['groupcategories'] = 'Group categories';
$string['allowgroupcategories'] = 'Allow group categories';
$string['enablegroupcategories'] = 'Enable group categories';
$string['addcategories'] = 'Add categories';
$string['allowgroupcategoriesdescription1'] = 'Allow site administrators to create categories for users to assign their groups';
$string['groupoptionsset'] = 'Group options have been updated.';
$string['groupcategorydeleted'] = 'Category deleted';
$string['confirmdeletecategory'] = 'Do you really want to delete this category?';
$string['groupcategoriespagedescription'] = 'The categories listed here can be assigned to groups during group creation and used to filter groups during searches.';
$string['groupquotas'] = "Group quota for '%s'";
$string['groupfilequotadescription'] = 'Total storage available in the group\'s files area.';
$string['groupadminsforgroup'] = "Group administrators for '%s'";
$string['potentialadmins'] = 'Potential administrators';
$string['currentadmins'] = 'Current administrators';
$string['makeusersintoadmins'] = 'Turn selected users into administrators';
$string['makeadminsintousers'] = 'Turn selected administrators back into users';
$string['groupadminsupdated'] = 'Group administrators have been updated';
$string['groupquotaupdated'] = 'Group quota has been updated';
$string['addnewgroupcategory'] = 'Enter new group category';
$string['archivedsubmissions'] = 'Archived submissions';
$string['archivedsubmissionsdesc'] = 'Administer archived submissions';
$string['submittedto'] = 'Submitted to';
$string['ID'] = 'ID';
$string['filenameleap'] = 'Leap2A file';
$string['archivedon'] = 'Archived on';
$string['filemissing'] = '%s (file missing)';
$string['filemissingdesc'] = 'File %s%s is missing from server';

// Register your Mahara
$string['Field'] = 'Field';
$string['Value'] = 'Value';
$string['dataincluded'] = 'Data included';
$string['datathathavebeensent'] = 'Data that has been sent';
$string['sendweeklyupdates'] = 'Send weekly updates';
$string['sendweeklyupdatesdescription'] = 'Allow your site to send weekly updates to <a href="https://mahara.org/">mahara.org</a> with some statistics about your site.';
$string['newregistrationpolicyinfo'] = '<p>We have updated our registration data policy to require registered sites to provide weekly updates of their data. Please follow the link below to confirm or remove your registration.</p>';
$string['registerwithmahara'] = 'Register with mahara.org';
$string['registerwithmaharadescription'] = 'Once your site is registered with the Mahara project, you cannot unregister it.';
$string['Register'] = 'Register';
$string['Registration'] = 'Registration';
$string['registrationcancelled'] = 'You can choose to register at any time by going to the <a href="%sadmin/registersite.php">site registration page</a>.';
$string['registrationfailedtrylater'] = 'Registration failed with error code %s. Please try again later.';
$string['registrationsuccessfulthanksforregistering'] = 'Registration successful - thanks for registering.';
$string['registermaharasite'] = 'Site registration';
$string['registeryoursitesummary'] = '
<p>Registering your Mahara site with <a href="https://mahara.org/">mahara.org</a> helps us to get a picture of the Mahara installations around the world.</p>';
$string['registeryourmaharasitedetail'] = '
<p>You can register your Mahara site with the Mahara project. Registration is free and helps us get a picture of the Mahara installations around the world.</p>
<p>If you decide to send weekly updates, you can see the information that will be sent to the Mahara project. Nothing that can personally identify any of your users will be sent.</p>';
$string['sendingweeklyupdates1'] = 'You are sending weekly updates.';
$string['notsendingweeklyupdates'] = 'You are not sending weekly updates.';
$string['siteisregisteredsince'] = 'Your site has been registered with <a href="https://mahara.org/">mahara.org</a> since %s.';
$string['siteisregistered'] = 'Your site is registered with <a href="https://mahara.org/">mahara.org</a>.';
$string['siteregistrationpolicy'] = '<p>To ensure our statistics show accurately how Mahara is being used, registration now includes sending us weekly updates. Please review and confirm your registration by clicking &quot;Save&quot; below.</p>';
$string['startsendingdata'] = 'Your site is sending weekly updates to <a href="https://mahara.org/">mahara.org</a>';
$string['stoppedsendingdata'] = 'The weekly updates have been stopped';

// Close site
$string['Close'] = 'Close';
$string['closesite'] = 'Close site';
$string['closesitedetail'] = 'You may close the site to everyone except administrators. This will be useful when preparing for a database upgrade. Only administrators will be able to log in until you either reopen the site, or an upgrade is successfully completed.';
$string['Open'] = 'Open';
$string['reopensite'] = 'Reopen site';
$string['reopensitedetail'] = 'Your site is closed. Site administrators may stay logged in until an upgrade is detected.';

// Statistics / Reports
$string['siteinformation'] = 'Site information';
$string['viewfullsitestatistics'] = 'View full site statistics';
$string['institutioncreated_all'] = 'Site installed';
$string['databasesize'] = 'Database size';
$string['diskusage'] = 'Disk usage';
$string['maharaversion'] = 'Mahara version';
$string['activeusers'] = 'Active users';
$string['loggedinsince'] = '%s today, %s since %s, %s all time';
$string['groupmemberaverage'] = 'On average, each user is in %s groups';
$string['viewsperuser'] = 'Users who make pages have about %s pages each';
$string['Cron'] = 'Cron';
$string['runningnormally'] = 'Running normally';
$string['cronnotrunning2'] = '<strong class="error text-danger">Cron is not running.</strong><br>See the <a href="https://wiki.mahara.org/wiki/System_Administrator\'s_Guide/Installing_Mahara">installation guide</a> for instructions on how to set it up. If you have already set up cron, one or more of its activities have failed to run recently.';
$string['cronnotrunningsiteclosed1'] = '<strong class="error text-danger">Cron is not running.</strong><br>The site is currently closed. Please re-open the site for the cron to run.';
$string['Loggedin'] = 'Logged in';
$string['youraverageuser'] = 'Your average user...';
$string['statsmaxfriends1'] = array(
    0 => 'Has %2$s friends<br>(<a href="%3$s">%4$s has the most, with %1$d friend</a>)',
    1 => 'Has %2$s friends<br>(<a href="%3$s">%4$s has the most, with %1$d friends</a>)',
);
$string['statsnofriends'] = 'Has 0 friends';
$string['statsmaxviews1'] = array(
    0 => 'Has made %2$s pages<br>(<a href="%3$s">%4$s has the most, with %1$d page</a>)',
    1 => 'Has made %2$s pages<br>(<a href="%3$s">%4$s has the most, with %1$d pages</a>)',
);
$string['statsnoviews'] = 'Has made 0 pages';
$string['statsmaxgroups1'] = array(
    0 => 'Is in %2$s groups<br>(<a href="%3$s">%4$s is in the most, with membership to %1$d group</a>)',
    1 => 'Is in %2$s groups<br>(<a href="%3$s">%4$s is in the most, with membership to %1$d groups</a>)',
);
$string['statsnogroups'] = 'Is in 0 groups';
$string['statsnoquota'] = 'Is using no storage';
$string['statsmaxquotaused1'] = 'Has used about %s of disk quota<br>(<a href="%s">%s has used the most, with %s of disk quota</a>)';
$string['groupcountsbytype'] = 'Number of groups by group type';
$string['groupcountsbyjointype'] = 'Number of groups by access type';
$string['blockcountsbytype'] = 'Most frequently used blocks in portfolio pages';
$string['uptodate'] = 'Your local codebase is up to date with Mahara core.';
$string['latestversionis'] = 'Most recent major release: <a href="%s">%s</a>';
$string['latestbranchversionis'] = 'Latest minor version of this release: <a href="%s">%s</a>';
$string['versionnotinsupport'] = '%s is out of support.';
$string['versionnotinsupportdev'] = 'Development version not in support';
$string['viewsbytype'] = 'Pages by type';
$string['institutionloginstabletitle'] = 'Active institutions';
$string['institutionloginstablesubtitle'] = 'For %s - %s';
$string['visitedtimesrank'] = 'visited %s times, ranked number %s';
$string['pageownedby']  = 'Owned by';
$string['contentstats'] = 'modified %s times for the current week and %s times in total';
$string['exportstatsascsv'] = 'Export statistics in CSV format';
$string['downloadstatsascsv'] = 'statistics in CSV format';
$string['nostats'] = 'No statistics available';
$string['site'] = 'Site';
$string['exportgroupscsv'] = 'Export groups in CSV format';
$string['exportgroupmembershipscsv'] = 'Export group membership in CSV format';
$string['exportgroupmembershipscsvspecific'] = 'Export group membership in CSV format for "%s"';

// Institution statistics / reports
$string['nostatistics'] = 'There are currently no statistiscs to display for this institution';
$string['institutionstatisticsfor'] = 'Institution statistics for \'%s\'';
$string['institutioncreated'] = 'Institution created';
$string['People'] = 'People';
$string['Groups'] = 'Groups';
$string['Content'] = 'Content';
$string['configurereport'] = 'Configure report';
$string['Columns'] = 'Columns';

// Site options
$string['siteadminsonly'] = 'Site administrators only';
$string['adminsonly'] = 'Administrators only';
$string['adminsandstaffonly'] = 'Administrators and staff only';
$string['advanced'] = 'Advanced';
$string['allowpublicviews'] = 'Allow public pages';
$string['allowpublicviewsdescription1'] = 'Users can create portfolio pages that are accessible to the public rather than only to logged-in users.';
$string['allowinstitutionpublicviews'] = 'Allow institution public pages';
$string['allowinstitutionpublicviewsdescription2'] = 'Allow users belonging to this institution to create portfolio pages that are accessible to the public rather than only to registered users.';
$string['allowinstitutionsmartevidence'] = 'Allow SmartEvidence';
$string['allowinstitutionsmartevidencedescription'] = 'Turn collections into SmartEvidence collections, which are linked to a competency framework.';
$string['institutionsmartevidencenotallowed'] = 'You need to activate the "smartevidence" module on the "Plugin administration" page before you can set the SmartEvidence options';
$string['allowpublicprofiles'] = 'Allow public profiles';
$string['allowpublicprofilesdescription1'] = 'Users can set their profile pages to be accessible to the public rather than only to registered users.';
$string['allowanonymouspages'] = 'Allow anonymous pages';
$string['allowanonymouspagesdescription1'] = 'Users can choose to hide their name as the author of a page from other users. Administrators will still be able to see the author\'s name if they so desire.';
$string['anonymouscomments'] = 'Anonymous comments';
$string['anonymouscommentsdescription1'] = 'Anyone can leave comments on public pages or pages they can access by secret URL.';
$string['loggedinprofileviewaccess1'] = 'Profile access for all registered users';
$string['loggedinprofileviewaccessdescription1'] = 'A user\'s profile page will be viewable by all registered users.';
$string['antispam'] = 'Anti-spam';
$string['antispamdescription'] = 'The type of anti-spam measures used on publicly visible forms';
$string['dropdownmenu'] = 'Drop-down navigation';
$string['dropdownmenudescription2'] = 'The main Mahara navigation will use a drop-down format for the sub-navigation.';
$string['dropdownmenudescriptioninstitution2'] = 'The main navigation will use a drop-down menu.';
$string['commentsortorder'] = 'Comment sort order';
$string['commentsortorderdescription'] = 'Set the sort order for artefact comments when viewed on a page.';
$string['commentthreaded'] = 'Threaded comments';
$string['commentthreadeddescription'] = 'Allows threaded replies to individual comments on a page.';
$string['defaultaccountinactiveexpire'] = 'Default account inactivity time';
$string['defaultaccountinactiveexpiredescription'] = 'How long a user account will remain active without the user logging in';
$string['defaultaccountinactivewarn'] = 'Warning time for inactivity / expiry';
$string['defaultaccountinactivewarndescription'] = 'The time before user accounts are to expire or become inactive at which a warning message will be sent to them.';
$string['defaultregistrationexpirylifetime'] = 'Default registration expiry lifetime';
$string['defaultregistrationexpirylifetimedescription'] = 'How long registration applications that require an administrator action will stay active.';
$string['defaultaccountlifetime'] = 'Default account lifetime';
$string['defaultaccountlifetimedescription'] = 'If set, user accounts will expire after this period of time from either today or the day they are created in the future.';
$string['defaultaccountlifetimeupdate'] = 'Override user account lifetime';
$string['defaultaccountlifetimeupdatedescription'] = 'If default account lifetime is set, then choose how to deal with user expiry times.';
$string['defaultaccountlifetimeupdatenone'] = 'Only for newly created users';
$string['defaultaccountlifetimeupdatesome'] = 'For new users and users without an account lifetime already set (excluding site administrators)';
$string['defaultaccountlifetimeupdateall'] = 'For all user accounts (excluding site administrators)';
$string['passwordpolicy'] = 'Password policy';
$string['passwordpolicydesc'] = 'Password policy for people whose account is set to use the built-in internal authentication method. Select the number of minimum characters and the complexity of the password.';
$string['passwordpolicylength'] = 'Minimum password length';
$string['passwordpolicytype'] = 'Password complexity';
$string['deprecatedmobileapp'] = "Sorry, this mobile application has been superseded. You need to use Mahara Mobile instead. Download for Android devices https://play.google.com/store/apps/details?id=org.mahara.mobile or for Apple devices https://itunes.apple.com/us/app/mahara-mobile/id1172638950";
$string['embeddedcontent'] = 'Embedded content';
$string['embeddedcontentdescription'] = 'If you would like users to be able to embed videos or other outside content into their portfolios, you can choose which sites to trust below.';
$string['Everyone'] = 'Everyone';
$string['generatesitemap1'] = 'Sitemap';
$string['generatesitemapdescription'] = 'Generate sitemap files from publicly accessible pages, groups and forum topics';
$string['homepageinfo1'] = 'Show homepage / dashboard information';
$string['homepageinfodescription3'] = 'Show information about Mahara and how it is used on the Mahara homepage. Registered users will have the option to disable it for their dashboard.';
$string['homepageredirect'] = 'Custom landing page';
$string['homepageredirectdescription'] = 'If you want the first page a user sees after logging in to be something other than the dashboard page. Note: If a link was clicked that requires the user to log in, that redirect will take priority.';
$string['homepageredirecturl'] = 'Landing page';
$string['homepageredirecturldescription'] = 'For this to work, the page needs to be accessible to all registered users.';
$string['islandingpage'] = 'This page is used as custom landing page for the site.';
$string['landingpagegone'] = 'The page "%s" was the custom landing page for the site before you made the permission change. It has been removed as the custom landing page from the site settings.';
$string['landingpagegonesubject'] = 'Custom landing page removed';
$string['landingpagegonemessage'] = 'Please log in and choose a new custom landing page in the site settings.';
$string['landingpagegonemessagedeleted'] = 'The page "%s" was removed as the custom landing page for the site because the page was deleted. Please log in and choose a new custom landing page in the site settings.';
$string['institutionautosuspend'] = 'Auto-suspend expired institutions';
$string['institutionautosuspenddescription1'] = 'Automatically suspend expired institutions.';
$string['institutionexpirynotification'] = 'Warning time for institution expiry';
$string['institutionexpirynotificationdescriptioninfo'] = 'A notification message will be sent to site and institution administrators that long before an institution expires.';
$string['language'] = 'Language';
$string['none'] = 'None';
$string['nousernames'] = 'Never display usernames';
$string['nousernamesdescription1'] = 'Ordinary users will not be able to see the username of any other user, nor will they be able to search for users by their username. These restrictions do not apply to staff and administrators. Additionally, Clean URLs (if activated) for profile pages will be generated using display names (if provided) or real names, rather than usernames.';
$string['onlineuserssideblockmaxusers'] = 'Online users limit';
$string['onlineuserssideblockmaxusersdescription'] = 'The maximum number of users to display in the online users sidebar.';
$string['country'] = 'Country';
$string['pathtoclam'] = 'Path to ClamAV';
$string['pathtoclamdescription'] = 'The file system path to clamscan or clamdscan';
$string['pathtoclamnotset'] = '(not set)';
$string['registerterms'] = 'Registration agreement';
$string['registertermsdescription1'] = "Force users to agree to the privacy statements plus terms and conditions before registration. You should edit your site's and institution's legal pages before enabling this option.";
$string['licensemetadata'] = 'License metadata';
$string['licensemetadatadescription'] = "Request and store license metadata for user-generated content.";
$string['licenseallowcustom1'] = 'Custom licenses';
$string['licenseallowcustomdescription'] = "For license metadata, allow users to enter any URL as the license. If not checked, users will be limited to the licenses configured by the site administrator.";
$string['recaptchakeysmissing1'] = 'reCAPTCHA is turned on, but it will not function until you also provide a site key and secret key.';
$string['recaptchanotpassed'] = 'The reCAPTCHA wasn\'t entered correctly. Please try it again.';
$string['recaptchaonregisterform1'] = 'reCAPTCHA on user registration / contact us forms';
$string['recaptchaonregisterformdesc2'] = 'Users self-registering a new account or using the contact us form will have to prove themselves human by passing a <a href="http://recaptcha.org/">reCAPTCHA</a> test.';
$string['recaptchaprivatekey1'] = 'reCAPTCHA secret key';
$string['recaptchaprivatekeydesc1'] = 'The secret key for your site\'s reCAPTCHA account.';
$string['recaptchapublickey1'] = 'reCAPTCHA site key';
$string['recaptchapublickeydesc1'] = 'The site key for your site\'s reCAPTCHA account.';
$string['remoteavatars'] = 'Display remote avatars';
$string['remoteavatarsdescription1'] = 'Use the <a href="http://www.gravatar.com">Gravatar</a> service for users\' default profile pictures.';
$string['searchplugin'] = 'Search plugin';
$string['searchplugindescription'] = 'Search plugin to use';
$string['searchconfigerror1'] = 'The configuration settings for the search plugin "%s" are incorrect. Please check the configuration settings under "Extensions" → "Plugin type: search". You may need to hit the search\'s \'Reset\' button when done.';
$string['searchuserspublic'] = 'Show users in public search';
$string['searchuserspublicdescription'] = 'Allow users\' names to appear in public search results. This needs to have \'publicsearchallowed\' set to true and be using a search plugin that allows public search, e.g. Elasticsearch. Changing this setting will require search re-indexing.';
$string['sessionlifetime'] = 'Session lifetime';
$string['sessionlifetimedescription'] = 'Time in minutes after which an inactive logged-in user will be automatically logged out.';
$string['setsiteoptionsfailed'] = 'Failed setting the %s option';
$string['showonlineuserssideblock'] = 'Show online users';
$string['showonlineuserssideblockdescriptionmessage1'] = 'Users can see a sidebar with a list of the online users.';
$string['showselfsearchsideblock1'] = 'Portfolio search';
$string['showselfsearchsideblockdescription1'] = 'Display the "Search my portfolio" sidebar in a few places on the site.';
$string['showtagssideblock1'] = 'Tag cloud';
$string['showtagssideblockdescription2'] = 'Users can see a sidebar in a few places on the site with a list of their most frequently used tags.';
$string['simple'] = 'Simple';
$string['sitedefault'] = 'Site default';
$string['sitelanguagedescription'] = 'The default language for the site.';
$string['sitecountrydescription'] = 'The default country for the site.';
$string['sitename'] = 'Site name';
$string['sitenamedescription'] = 'The site name appears in certain places around the site and in emails sent from the site.';
$string['siteoptionspagedescription'] = 'Here you can set some global options that will apply by default throughout the entire site. <BR> Note: Disabled options are overridden by your config.php file.';
$string['siteoptionsset'] = 'Site options have been updated.';
$string['sitethemedescription'] = 'The default theme for the site. If your theme is not listed, check the error log.';
$string['skins'] = 'Page skins';
$string['skinsinstitutiondescription2'] = 'Members of this institution can use "skins" on their pages.';
$string['smallviewheaders'] = 'Small page headers';
$string['smallviewheadersdescription1'] = 'Display a small header and site navigation when viewing or editing portfolio pages.';
$string['spamhaus1'] = 'Spamhaus URL blacklist';
$string['spamhausdescription1'] = 'Check URLs against the Spamhaus DNSBL.';
$string['staffaccessreports'] = 'Access reports for staff';
$string['staffaccessreportsdescription1'] = 'Allow site and institution staff to access the "User details", "Portfolio access", and "Masquerading sessions" reports in their institution(s). These reports are normally restricted to administrators.';
$string['institutionstaffallreports'] = 'All reports for institution staff';
$string['institutionstaffallreportsdescription'] = 'Allow institution staff to access all reports for their institution(s). The reports are normally restricted to administrators and site staff.';
$string['surbl1'] = 'SURBL URL blacklist';
$string['surbldescription1'] = 'Check URLs against the SURBL DNSBL.';
$string['disableexternalresources'] = 'Disable external resources in user HTML';
$string['disableexternalresourcesdescription1'] = 'Disable the embedding of external resources, preventing users from embedding things like images from other hosts.';
$string['tagssideblockmaxtags'] = 'Maximum tags in cloud';
$string['tagssideblockmaxtagsdescription'] = 'The default number of tags to display in user tag clouds';
$string['trustedsites'] = 'Trusted sites';
$string['updatesiteoptions'] = 'Update site options';
$string['usersallowedmultipleinstitutions'] = 'Users allowed multiple institutions';
$string['usersallowedmultipleinstitutionsdescription1'] = 'Allow users to be members of several institutions at the same time.';
$string['institutionstrictprivacy'] = 'Strict privacy';
$string['institutionstrictprivacydescription'] = 'Turn on this option when your site needs to adhere to the <a href="https://www.eugdpr.org/">GDPR</a>.';
$string['requireregistrationconfirm'] = 'Confirm registration';
$string['requireregistrationconfirmdescription1'] = 'Force all institutions to approve new self-registered accounts.';
$string['userscanchooseviewthemes'] = 'Users can choose page themes';
$string['userscanchooseviewthemesdescription1'] = 'Allow users to select a theme when editing or creating a portfolio page. The page will be displayed to other users using the selected theme.';
$string['userscanhiderealnames'] = 'Users can hide real names';
$string['userscanhiderealnamesdescription1'] = 'Allow users, who have set a display name, to not be found in searches with their real name. Other users would only be able to search for the display name. In the site administration section of the site, users are always searchable by their real names.';
$string['usersseenewthemeonlogin'] = 'Other users will see the new theme the next time they log in.';
$string['viruschecking'] = 'Virus checking';
$string['viruscheckingdescription1'] = 'Check all uploaded files for viruses using ClamAV.';
$string['whocancreategroups'] = 'Create groups';
$string['whocancreategroupsdescription'] = 'Decide which users will be able to create new groups.';
$string['whocancreatepublicgroups'] = 'Create public groups';
$string['whocancreatepublicgroupsdescription'] = 'Decide which users will be able to make groups that are viewable by the general public.';
$string['wysiwyg'] = 'HTML editor';
$string['wysiwygdescription'] = 'Defines whether or not the HTML editor is enabled globally or whether users are allowed to enable / disable it themselves.';
$string['wysiwyguserdefined'] = 'User-defined';
$string['eventloglevel'] = 'Log events';
$string['eventlogleveldescription'] = 'Which events should be logged?';
$string['eventlogexpiry'] = 'Event log expiry';
$string['eventlogexpirydescription'] = 'How long to keep the event log.';
$string['eventloglevelnone'] = 'None';
$string['eventloglevelmasq'] = 'Masquerading';
$string['eventloglevelall'] = 'All';
$string['eventlogenhancedsearch'] = 'Event log reporting';
$string['eventlogenhancedsearchdescription1'] = 'This is required if you want to use the advanced analytics features in order to report over more activity on your site. You also need to enable Elasticsearch in the "Search Settings".';
$string['sitefilesaccess'] = 'Access to site files';
$string['sitefilesaccessdescription1'] = 'Allow registered users to access site files in subfolders. By default, only files in the top level directory are accessible to them.';
$string['watchlistdelaydescription'] = 'The delay in minutes between sending emails regarding watchlist changes.';
$string['watchlistdelaytitle'] = 'Watchlist notification delay';
$string['defaultmultipleblogs'] = 'Multiple journals';
$string['defaultmultipleblogsdescription1'] = 'Allow users to have multiple journals by default. Users can override this in their account settings page.';
$string['defaultreviewsselfdeletion'] = 'Review accounts before self-deletion';
$string['defaultreviewsselfdeletiondescription'] = 'Force all institutions to review account deletion requests.';
$string['reviewsselfdeletion'] = 'Review accounts before self-deletion';
$string['reviewsselfdeletiondescription'] = 'An institution administrator must approve requests from people who wish to delete their accounts.';
$string['mathjax'] = 'Enable MathJax';
$string['mathjaxdescription'] = 'MathJax renders LaTeX markup into properly formatted math and science equations on portfolio pages.';
$string['mathjaxconfig'] = 'MathJax configuration';
$string['timezone'] = 'Time zone';
$string['sitetimezonedescription'] = 'The default time zone for the site. If none is selected, one will be chosen based on the selected country. This can be innaccurate if the country has multiple time zones.';
$string['notimezoneselected'] = 'No time zone selected';
// Site content
$string['about']               = 'About';
$string['discardpageedits']    = 'Discard your changes to this page?';
$string['staticpagespagedescription'] = 'Here you can edit the content of some of Mahara\'s built-in pages, such as the dashboard for logged-in users and the homepage for logged-out users as well as the pages linked to in the footer.';
$string['home']                = 'Home (Dashboard)';
$string['loadsitecontentfailed']  = 'Failed to load site page content';
$string['loggedouthome']       = 'Logged-out home';
$string['pagename']            = 'Page name';
$string['pagesaved']           = 'Page saved';
$string['pagetext']            = 'Page text';
$string['privacy']             = 'Privacy statement';
$string['legal']               = 'Legal';
$string['savechanges']         = 'Save changes';
$string['savefailed']          = 'Save failed';
$string['sitepageloaded']      = 'Site page loaded';
$string['termsandconditions']  = 'Terms and conditions';
$string['uploadcopyright']     = 'Upload copyright statement';
$string['privacypagedescription'] = 'Edit the privacy statement for the entire site. The version you edited last becomes the current privacy statement automatically.';
$string['termspagedescription'] = 'Edit the terms and conditions for the entire site. The version you edited last becomes the current terms and conditions automatically.';

// Isolated institutions
$string['owngroupsonly'] = 'See own groups only';
$string['owngroupsonlydescription'] = 'Allow users to see only groups that they own or that they are members of. They can also only search for other users who are in these groups. This setting requires that isolated institutions are turned on in the config.php file.';

// Links and resources menu editor
$string['sitefile']            = 'Site file';
$string['adminpublicdirname']  = 'public';  // Name of the directory in which to store public admin files
$string['adminpublicdirdescription'] = 'Files accessible by logged-out users';
$string['badmenuitemtype']     = 'Unknown item type';
$string['badurl']              = 'Bad link provided';
$string['oneormorelinksarebad']              = 'One or more of the links here are bad.';
$string['confirmdeletemenuitem'] = 'Do you really want to delete this item?';
$string['deletingmenuitem']    = 'Deleting item';
$string['deletefailed']        = 'Failed deleting item';
$string['externallink']        = 'External link';
$string['editlinksandresources'] = 'Edit links and resources';
$string['linkedto']            = 'Linked to';
$string['linksandresourcesmenu'] = 'Links and resources menu';
$string['linksandresourcesmenupagedescription'] = 'The "Links and resources" menu appears to all users on most pages. You can add links to other websites and to files uploaded to the %sadmin files%s section.';
$string['loadingmenuitems']    = 'Loading items';
$string['loadmenuitemsfailed'] = 'Failed to load items';
$string['loggedinmenu']        = 'Logged-in links and resources';
$string['loggedoutmenu']       = 'Public links and resources';
$string['menuitemdeleted']     = 'Item deleted';
$string['menuitemsaved']       = 'Item saved';
$string['menuitemsloaded']     = 'Items loaded';
$string['name']                = 'Name';
$string['nositefiles']         = 'No site files available';
$string['public']              = 'public';
$string['savingmenuitem']      = 'Saving item';
$string['type']                = 'Type';
$string['footermenu']          = 'Footer menu';
$string['footermenudescription'] = 'Enable or disable the links in the footer.';
$string['footerupdated']       = 'Footer updated';
$string['footercustomlink'] = 'You can override the default page, <em>%s</em>, by entering a URL here or leave it empty to use the default value.';

// Admin Files
$string['adminfilespagedescription2'] = 'Here are files that you can include in the %s"Links and resources"%s menu. You can add files from the home directory to the logged-in menu and files from the "public" folder to the public menu.';

// License settings
$string['extralicensesdescription'] = 'Note: The following licenses are used by some content but have not been configured.';
$string['licensenamelabel'] = 'URL';
$string['licensedisplaynamelabel'] = 'Display name';
$string['licenseshortnamelabel'] = 'Acronym';
$string['licenseiconlabel'] = 'Icon';
$string['addsitelicense'] = 'Add a license';
$string['licensedeleted'] = 'License deleted.';
$string['licensesave'] = 'Save';
$string['licensesaved'] = 'License saved.';
$string['licenseurldup'] = 'The URLs must be unique; %s is repeated here.';
$string['licenseurlnone'] = 'Please specify a URL for %s.';

// Networking options
$string['networkingextensionsmissing'] = 'Sorry, you cannot configure Mahara networking because your PHP installation is missing one or more required extensions:';
$string['publickey'] = 'Public key';
$string['publickeydescription2'] = 'This public key is automatically generated and rotated every %s days.';
$string['publickeyexpires'] = 'Public key expires';
$string['enablenetworkingdescription'] = 'Allow your Mahara server to communicate with servers running Moodle and other applications.';
$string['enablenetworking'] = 'Enable networking';
$string['networkingenabled'] = 'Networking has been enabled. ';
$string['networkingdisabled'] = 'Networking has been disabled. ';
$string['networkingpagedescription'] = 'Mahara\'s networking features allow it to communicate with Mahara or Moodle sites running on the same or another machine. If networking is enabled, you can use it to configure single sign-on for users who log in at either Moodle or Mahara.';
$string['networkingunchanged'] = 'Network settings were not changed';
$string['promiscuousmode'] = 'Auto-register all hosts';
$string['promiscuousmodedisabled'] = 'Auto-register has been disabled. ';
$string['promiscuousmodeenabled'] = 'Auto-register has been enabled. ';
$string['promiscuousmodedescription'] = 'Create an institution record for any host that connects to you and allow its users to log on to Mahara.';
$string['wwwroot'] = 'WWW root';
$string['wwwrootdescription'] = 'This is the URL at which your users access this Mahara installation and the URL for which the SSL keys are generated.';
$string['deletekey'] = 'Delete this key';
$string['keydeleted'] = 'Public key has been deleted and regenerated.';
$string['proxysettings'] = 'Proxy settings';
$string['proxyaddress'] = 'Proxy address';
$string['proxyaddressdescription'] = 'If your site uses a proxy server to access the Internet, specify the proxies in <em>hostname:portnumber</em> notation.';
$string['proxyaddressset'] = 'Proxy address set';
$string['proxyauthmodel'] = 'Proxy authentication model';
$string['proxyauthmodeldescription'] = 'Select your proxy\'s authentication model, if appropriate';
$string['proxyauthmodelbasic'] = 'Basic (NCSA)';
$string['proxyauthmodelset'] = 'Proxy authentication model has been set.';
$string['proxyauthcredentials'] = 'Proxy credentials';
$string['proxyauthcredentialsdescription'] = 'Enter the credentials required for your proxy to authenticate your web server in <em>username:password</em> format.';
$string['proxyauthcredntialsset'] = 'Proxy authentication credentials set.';
$string['emailsettings'] = 'Email settings';
$string['emailsmtphosts'] = 'SMTP host';
$string['emailsmtphostsdescription'] = 'SMTP server to be used for mail sending, e.g. <em>smtp1.example.com</em>';
$string['emailsmtpport'] = 'SMTP port';
$string['emailsmtpportdescription'] = 'Specify port number if SMTP server uses port different from 25';
$string['emailsmtpuser'] = 'User';
$string['emailsmtpuserdescription'] = 'If SMTP server requires authentication, enter user credentials in the corresponding fields.';
$string['emailsmtppass'] = 'Password';
$string['emailsmtpsecure'] = 'SMTP encryption';
$string['emailsmtpsecuredescription'] = 'If the SMTP server supports encryption, enable it here.';
$string['emailsmtpsecuressl'] = 'SSL';
$string['emailsmtpsecuretls'] = 'TLS';
$string['emailnoreplyaddress'] = 'System mail address';
$string['emailnoreplyaddressdescription'] = 'Emails come out as from this address';
$string['internalnotificationexpire'] = 'Internal notification expiry';
$string['internalnotificationexpiredescription'] = 'The number of days to keep internal notifications';
$string['notificationsettings'] = 'Notification settings';
$string['notificationsettingsdescription'] = 'Here you can set the default options for new users to get notifications. Users can override these settings on their own "Settings → Notifications" page.<br>
         If you select either of the email options, notifications will still arrive in the user\'s inbox, but they will be marked as read automatically.';

// Upload CSV and CSV errors
$string['csvfile'] = 'CSV file';
$string['emailusersaboutnewaccount'] = 'Email users about their account';
$string['emailusersaboutnewaccountdescription'] = 'If checked, an email will be sent to users informing them of their new account details.';
$string['forceuserstochangepassword'] = 'Force password change';
$string['forceuserstochangepassworddescription'] = 'If checked, users will be forced to change their password when they log in for the first time.';
$string['uploadcsvinstitution'] = 'The institution and authentication method for the new users';
$string['configureauthplugin'] = 'You must configure an authentication plugin before you can add users.';
$string['csvfiledescription'] = 'The file containing users to add.';
$string['csvmaxusersdescription'] = 'This file should not contain more than %s.';
$string['groupcsvfiledescription'] = 'The file containing groups to add';
$string['groupmemberscsvfiledescription'] = 'The file containing group members to update';
$string['csverroremptyfile'] = 'The CSV file is empty.';
$string['invalidfilename'] = 'The file "%s" does not exist.';
$string['uploadcsverrorinvalidfieldname'] = 'The field name "%s" is invalid, or you have more fields than your header row specifies.';
$string['uploadcsverrorrequiredfieldnotspecified'] = 'A required field "%s" has not been specified in the format line.';
$string['uploadcsverrornorecords'] = 'The file appears to contain no records (although the header is fine).';
$string['uploadcsverrorunspecifiedproblem1'] = 'The records in your CSV file could not be inserted for some reason. If your file is in the correct format, then this is a bug and you should <a href="https://bugs.launchpad.net/mahara/+filebug">create a bug report</a>, attaching the CSV file (remember to blank out passwords!) and, if possible, the error log file.';
$string['uploadcsverrorwrongnumberoffields'] = 'Error on line %s of your file: Incorrect number of fields.';
$string['uploadcsverrorinvalidemail'] = 'Error on line %s of your file: The email address for this user is not in the correct format.';
$string['uploadcsverrorincorrectnumberoffields'] = 'Error on line %s of your file: This line does not have the correct number of fields.';
$string['uploadcsverrorinvalidpassword1'] = 'Error on line %s of your file: %s';
$string['uploadcsverrorinvalidusername'] = 'Error on line %s of your file: The username for this user is not in the correct format.';
$string['uploadcsverrormandatoryfieldnotspecified'] = 'Line %s of the file does not have the required "%s" field.';
$string['uploadcsverroruseralreadyexists'] = 'Line %s of the file specifies the username "%s" that already exists.';
$string['uploadcsverroremailaddresstaken'] = 'Line %s of the file specifies the email address "%s" that is already taken by another user.';
$string['uploadcsverrorduplicateremoteuser'] = 'Line %s of the file specifies a remote username "%s" that is already taken by another user.';
$string['uploadcsverrorremoteusertaken'] = 'Line %s of the file specifies a remote username "%s" that is already taken by the user "%s".';
$string['uploadcsverrorusernotininstitution'] = 'Error on line %s: The user "%s" is not a member of the institution %s.';
$string['uploadcsverroruserinaninstitution'] = 'Error on line %s: The user "%s" is a member of the following institutions: %s. You cannot update this user\'s authentication method to "No Institution".';
$string['uploadcsverrorinvalidexpirydate'] = 'Error on line %s: The expiry "%s" is invalid. Please use a valid date format.';
$string['uploadcsverrorexpirydateinpast'] = 'Error on line %s: The expiry "%s" cannot be in the past.';
$string['uploadcsvpagedescription6'] = '<p>Here you can upload new users via a <acronym title="Comma Separated Values">CSV</acronym> file.</p>

<p>The first row of your CSV file should specify the format of your CSV data. For example, it should look like this:</p>

<pre>username,password,email,firstname,lastname,studentid</pre>

<p>This row must include the following fields when you create <i>new</i> users:</p>
<ul class="fieldslist">
<li>username</li>
<li>firstname</li>
<li>lastname</li>
<li>email</li>
<li>password</li>
</ul>

<p>You can leave out the "password" field when you update existing users.</p>

<p>Your CSV file may include any other profile fields that you want to pre-fill. The optional fields are:</p>

%s';
$string['uploadcsverrortoomanyusers'] = 'You have too many lines in your CSV file. Your file should not contain more than %s.';
$string['uploadcsverrordatamustbenumeric'] = 'Line %s: The value for category must be numeric';
$string['uploadgroupcsverrordisplaynamealreadyexists'] = 'Error on line %s of your file: The displayname "%s" already exists.';
$string['uploadgroupcsverrorinvalidshortname'] = 'Error on line %s of your file: The shortname "%s" is invalid.';
$string['uploadgroupcsverrorshortnamemissing'] = 'Error on line %s of your file: The group with the shortname "%s" does not exist.';
$string['uploadgroupcsverrorinvalidgrouptype'] = 'Error on line %s of your file: The grouptype "%s" is invalid.';
$string['uploadgroupcsverrorinvalideditroles'] = 'Error on line %s of your file: The value for editroles "%s" is invalid.';
$string['uploadgroupcsverrorshortnamealreadytaken1'] = 'Error on line %s of your file: The shortname "%s" is already taken. A valid alternative is "%s"';
$string['uploadgroupcsverrorusernamesnotlastfield'] = 'The "usernames" field must be the last field in the header.';
$string['uploadgroupcsverroropencontrolled'] = 'Line %s: Groups cannot have both open and controlled membership.';
$string['uploadgroupcsverroropenrequest'] = 'Line %s: Groups with open membership cannot allow membership requests.';
$string['uploadgroupcsverrorviewnotifyrequest'] = 'Line %s: The value for viewnotify must be numerical in the range of %s-%s.';
$string['uploadgroupcsverrordoesnotallowgroupcategory1'] = 'Line %s: This site does not allow for group categories. If you want to use them, a site administrator can turn them on in the administration area → Groups → Group categories.';
$string['uploadgroupcsverrorcategorydoesnotexist'] = 'Line %s: The category "%s" does not exist.';
$string['uploadgroupcsvpagedescription2'] = '<p>You may use this facility to upload new groups via a <acronym title="Comma Separated Values">CSV</acronym> file.</p>

<p>The first row of your CSV file should specify the format of your CSV data. For example, it should look like this:</p>

<pre>shortname,displayname,roles</pre>

<p>This row must include the <tt>shortname</tt>, <tt>displayname</tt>, and <tt>roles</tt> fields</p>

<p>The roles field can have any of the following: %s</p>

%s

<p>Your CSV file may include any other fields as you require. The full list of fields is:</p>

%s';
$string['uploadgroupcsveditrolesdescription'] = '<p>The editroles field can have any of the following: %s</p>
%s';
$string['uploadgroupmemberscsverrorduplicateusername'] = 'Error on line %s of your file: The shortname "%s" and username "%s" have already been specified in this file.';
$string['uploadgroupmemberscsverrorinvalidrole'] = 'Error on line %s of your file: The role "%s" is invalid for the specified group.';
$string['uploadgroupmemberscsverrornoadminlisted'] = 'Error on line %s of your file: The group shortname "%s" did not have any users with the "admin" role specified.';
$string['uploadgroupmemberscsverrornosuchshortname'] = 'Error on line %s of your file: The group shortname "%s" does not exist or is not part of the institution "%s".';
$string['uploadgroupmemberscsverrornosuchusername'] = 'Error on line %s of your file: The username "%s" does not exist.';
$string['uploadgroupmemberscsverrorusernotininstitution'] = 'Error on line %s of your file: The username "%s" is not part of the institution "%s".';
$string['uploadgroupmemberscsvpagedescription3'] = '<p>You may use this facility to update group members in groups controlled by this institution. You can upload new members via a <acronym title="Comma Separated Values">CSV</acronym> file.</p>

<p>The first row of your CSV file should specify the format of your CSV data. For example, it should look like this:</p>

<pre>shortname,username,role</pre>

<p>This row must include all the fields mentioned above, but can be in any order.</p>

<p>The shortname field must be the same as the shortname you used to create the group <a href="%s" title="%s">here</a>.</p>

<p>The role field can have any of the following, depending on the type of group: <tt>admin</tt>, <tt>member</tt>, or <tt>tutor</tt>.</p>

<div class="warning"> Every CSV file upload removes all existing group members, including group administrators, completely. Ensure that you have at least one administrator for each group in your CSV file.</div>';
$string['uploadcsvsomeuserscouldnotbeemailed'] = 'Some users could not be emailed. Their email addresses may be invalid, or the server Mahara is running on might not be configured to send email properly. The server error log has more details. For now, you may want to contact these people manually:';
$string['uploadcsvfailedusersexceedmaxallowed'] = 'No users have been added because there are too many users in your file. The number of users in the institution would have exceeded the maximum number allowed.';
$string['updateusers'] = 'Update users';
$string['updateusersdescription'] = 'If your CSV file contains the usernames of users who are already members of the institution you have specified, their details will be overwritten with data from the file. Use with care.';
$string['updategroups'] = 'Update groups';
$string['updategroupsdescription2'] = 'The information in the CSV file will overwrite any details of groups whose group shortname is in the CSV file. Use with care.';
$string['csvfileprocessedsuccessfully'] = 'Your CSV file was processed successfully.';
$string['nousersadded'] = 'No users were added.';
$string['nogroupsadded'] = 'No groups were added.';
$string['numbernewusersadded'] = 'New users added: %s.';
$string['numbernewgroupsadded'] = 'New groups added: %s.';
$string['numberusersupdated'] = 'Users updated: %d.';
$string['numbergroupsupdated'] = 'Groups updated: %d.';
$string['showupdatedetails'] = 'Show update details';

// Bulk Leap2A import
$string['bulkleap2aimport'] = 'Import users from Leap2A files';
$string['bulkleap2aimportdescription'] = '<p>You can import users in bulk from a collection of Leap2A files on your server. You must specify a ZIP file on the server file system, which contains all the Leap2A ZIP files and a single CSV file called usernames.csv mapping usernames to filenames.</p>
<p>usernames.csv will look something like this:</p>
<pre>
&nbsp;&nbsp;bob,mahara-export-leap-user8-1265165366.zip<br>
&nbsp;&nbsp;nigel,mahara-export-leap-user1-1266458159.zip
</pre>
<p>where mahara-export-leap-user8-1265165366.zip and mahara-export-leap-user1-1266458159.zip are files in a subdirectory called users.</p>
<p>This ZIP file should normally be generated using the bulk export built into Mahara.</p>
<p>If you are importing a lot of users, please be patient. The import process can take a long time.</p>';
$string['importfile'] = 'Bulk export file';
$string['importfilemissinglisting'] = 'The bulk export file is missing a file named usernames.csv. Did you use the Mahara bulk exporter to export these users?';
$string['importfilenotafile'] = 'Error during form submission: file was not recognised.';
$string['importfilenotreadable'] = 'Error during form submission: file was not readable.';
$string['bulkleap2aimportfiledescription'] = 'The ZIP file on your server containing all exported users (in Leap2A format) along with a CSV listing of usernames';
$string['importednuserssuccessfully'] = 'Imported %d of %d users successfully.';
$string['Import'] = 'Import';
$string['bulkimportdirdoesntexist'] = 'The directory %s does not exist.';
$string['unabletoreadbulkimportdir'] = 'The directory %s is unreadable.';
$string['unabletoreadcsvfile'] = 'Unable to read CSV file %s.';
$string['importfilenotreadable'] = 'Unable to read Leap2A file %s.';
$string['importfileisnotazipfile'] = 'Import file %s was not detected as a ZIP file.';
$string['unzipfailed'] = 'Failed to unzip the Leap2A file %s. See the error log for more information.';
$string['importfailedfornusers'] = 'Import failed for %d of %d users.';
$string['invalidlistingfile'] = 'Invalid username listing. Did you use the Mahara bulk exporter to export these users?';
$string['importing'] = 'Importing';

// Admin Users
$string['adminuserspagedescription1'] = '<p>Here you can choose which users are administrators for the site. The current administrators are listed in the "Current administrators" field, and potential administrators are in the "Potential administrators" field.</p><p>The system must have at least one administrator.</p>';
$string['institutionadminuserspagedescription1'] = 'Here you can choose which users are administrators for the institution. The current administrators are listed in the "Current administrators" field, and potential administrators are shown in the "Institution members" field.';
$string['potentialadmins'] = 'Potential administrators';
$string['currentadmins'] = 'Current administrators';
$string['adminusersupdated'] = 'Administrators updated';

// Staff Users
$string['staffuserspagedescription1'] = 'Here you can choose which users are staff for the site. The current staff are in the "Current staff" field, and potential staff are in the "Potential staff" field.';
$string['institutionstaffuserspagedescription1'] = 'Here you can choose which users are staff for your institution. The current staff are in the "Institution staff" field, and potential staff are in the "Institution members" field.';
$string['potentialstaff'] = 'Potential staff';
$string['currentstaff'] = 'Current staff';
$string['makeusersintostaff'] = 'Turn selected users into staff';
$string['makestaffintousers'] = 'Turn selected staff back into users';
$string['staffusersupdated'] = 'Staff users updated';

// Admin Notifications

// Suspended Users
$string['deleteusers'] = 'Delete users';
$string['deleteuser'] = 'Delete user';
$string['confirmdeleteusers'] = 'Are you sure you want to delete the selected users?';
$string['exportingnotsupportedyet'] = 'Exporting user profiles is not supported yet.';
$string['exportuserprofiles'] = 'Export user profiles';
$string['nousersselected'] = 'No users selected';
$string['suspenduser'] = 'Suspend user';
$string['suspendeduserstitle'] = 'Suspended and expired users';
$string['suspendedusers'] = 'Suspended users';
$string['suspensionreason'] = 'Suspension reason';
$string['errorwhilesuspending'] = 'An error occurred while trying to suspend';
$string['suspendedusersdesc'] = 'View suspended and expired users and reactivate or delete them';
$string['unsuspendusers'] = 'Unsuspend users';
$string['usersdeletedsuccessfully'] = 'Users deleted successfully';
$string['usersunsuspendedsuccessfully'] = 'Users unsuspended successfully';
$string['suspendingadmin'] = 'Suspending administrator';
$string['usersuspended'] = 'User suspended';
$string['userunsuspended'] = 'User unsuspended';
$string['expiredusers'] = 'Expired users';
$string['expired'] = 'Expired';
$string['expires'] = 'Expires';
$string['unexpireusers'] = 'Reactivate expired users';
$string['usersreactivated'] = 'Users reactivated';

// User account settings
$string['accountsettings'] = 'Account settings';
$string['siteaccountsettings'] = 'Site account settings';
$string['changeusername'] = 'Change username';
$string['changeusernamedescription'] = 'Change this user\'s username. Usernames are 3-236 characters long and may contain letters, numbers and most common symbols excluding spaces.';
$string['resetpassword'] = 'Reset password';
$string['resetpassworddescription'] = 'If you enter text here, it will replace the user\'s current password.';
$string['forcepasswordchange'] = 'Force password change on next login';
$string['forcepasswordchangedescription'] = 'The user will be directed to a "Change password" page the next time they login.';
$string['primaryemail'] = 'Primary email';
$string['sitestaff'] = 'Site staff';
$string['siteadmins'] = 'Site administrators';
$string['siteadmin'] = 'Site administrator';
$string['accountexpiry'] = 'Account expires';
$string['accountexpirydescription'] = 'Date on which the user\'s login is automatically disabled.';
$string['suspended'] = 'Suspended';
$string['suspendedreason'] = 'Reason for suspension';
$string['suspendedreasondescription'] = 'The text that will be displayed to the user on their next login attempt.';
$string['unsuspenduser'] = 'Unsuspend user';
$string['thisuserissuspended'] = 'This user has been suspended.';
$string['suspendedinfo'] = 'This user was suspended by %s on %s.';
$string['expiredinfo'] = 'This user\'s account expired on %s.';
$string['unexpiredesc'] = 'To re-activate this account, uncheck the "Account expires" field below and save your changes.';
$string['deleteuser'] = 'Delete user';
$string['userdeletedsuccessfully'] = 'User deleted successfully';
$string['confirmdeleteuser'] = 'Are you sure you want to delete this user?';
$string['filequota1'] = 'File quota';
$string['quotaused'] = 'Quota used';
$string['filequotadescription'] = 'Total storage available in the user\'s files area.';
$string['probationbulkconfirm'] = 'Are you sure you want to change these users\' spam probation status?';
$string['probationbulksetspamprobation'] = 'Set spam probation';
$string['probationbulkset'] = 'Set';
$string['probationtitle'] = 'Spammer probation status';
$string['probationzeropoints'] = 'Not on probation';
$string['probationxpoints'] = array(
    0 => '%d point',
    1 => '%d points',
);
$string['probationreportcolumn'] = 'Probation';
$string['addusertoinstitution'] = 'Add user to institution';
$string['removeuserfrominstitution'] = 'Remove user from this institution';
$string['confirmremoveuserfrominstitution'] = 'Are you sure you want to remove the user from this institution?';
$string['usereditdescription1'] = 'Here you can manage this user account, including changing details, suspending or deleting it or changing its membership in institutions.';
$string['usereditwarning'] = 'NOTE: Saving the account changes will cause the user to be logged out (if currently logged in).';
$string['suspenduserdescription'] = 'A suspended user is unable to log in until the account is unsuspended.';
$string['deleteusernote'] = 'Please note that this operation <strong>cannot be undone</strong>.';
$string['youcannotadministerthisuser'] = 'You cannot administer this user.';
$string['userinstitutionjoined'] = 'User added to institution "%s".';
$string['userinstitutionremoved'] = 'User removed from institution "%s".';
$string['userinstitutionupdated'] = 'User settings for institution "%s" updated.';
$string['usernamechangenotallowed'] = 'The chosen authentication method does not allow changes to the username.';
$string['usersitesettingschanged'] = 'Site account settings updated.';
$string['passwordchangenotallowed'] = 'The chosen authentication method does not allow changes to the password.';
$string['thisuserdeleted'] = 'This user has been deleted.';
$string['disableemail'] = 'Disable email';

// Export queue
$string['exportqueue'] = 'Export queue';
$string['exportqueuedesc'] = 'View pending content exports';
$string['exportcontentname'] = 'Export content';
$string['selectuserexport'] = 'Select content "%s" to export';
$string['selectuserexportdelete'] = 'Select content "%s" to delete from export queue';
$string['exportpending'] = 'pending as of %s';
$string['exportfailed'] = 'failed on %s';
$string['exportqueuedeleted'] = array(
    'Deleted %s row successfully',
    'Deleted %s rows successfully',
);
$string['exportqueuearchived'] = array(
    'Updated %s row successfully',
    'Updated %s rows successfully',
);
$string['exportdataascsv'] = 'Export all data in CSV format';
$string['nocsvresults'] = 'No results found for CSV file';
$string['exportdownloademailsubject'] = 'Your Leap2A export for "%s" is ready for download';
$string['exportdownloademailmessage'] = '%s, your Leap2A export for "%s" is ready for download. This file will only be available for up to 24 hours after it was generated. Please follow the link below to download the file:';
$string['exportdownloadurl'] = 'Download exported file';

// Add User
$string['adduser'] = 'Add user';
$string['adduserdescription'] = 'Create a new user';
$string['basicinformationforthisuser'] = 'Basic information for this user.';
$string['clickthebuttontocreatetheuser'] = 'Click the button to create the user.';
$string['createnewuserfromscratch'] = 'Create new user from scratch';
$string['createuser'] = 'Create user';
$string['failedtoobtainuploadedleapfile'] = 'Failed to obtain the uploaded Leap2A file.';
$string['failedtounzipleap2afile'] = 'Failed to unzip the Leap2A file. Check the error log for more information.';
$string['fileisnotaziporxmlfile'] = 'This file has not been detected to be a ZIP file or XML file.';
$string['howdoyouwanttocreatethisuser'] = 'How do you want to create this user?';
$string['leap2aimportfailed'] = '<p><strong>Sorry, importing the Leap2A file failed.</strong></p><p>This could be because you did not select a valid Leap2A file to upload or because the version of your Leap2A file is not supported by this Mahara version. Alternatively, there may be a bug in Mahara causing your file to fail, even though it is valid.</p><p>Please <a href="add.php">go back and try again</a>, and if the problem persists, you may want to post to the <a href="https://mahara.org/forums/">Mahara Forums</a> to ask for help. Be prepared to be asked for a copy of your file.</p>';
$string['newuseremailnotsent'] = 'Failed to send welcome email to new user.';
$string['newusercreated'] = 'New user account created successfully';
$string['noleap2axmlfiledetected'] = 'No leap2a.xml file detected - please check your export file again.';
$string['Or'] = 'Or';
$string['userwillreceiveemailandhastochangepassword'] = 'They will receive an email informing them of their new account details. On first login, they will be forced to change their password.';
$string['uploadleap2afile'] = 'Upload Leap2A file';

$string['usercreationmethod'] = '1 - User creation method';
$string['basicdetails'] = '2 - Basic details';
$string['create'] = '3 - Create';

// Login as
$string['loginas'] = 'Log in as';
$string['loginasthisuser'] = 'Log in as this user';
$string['loginasuser'] = 'Log in as %s';
$string['becomeadminagain'] = 'Become %s again';
// Login-as exceptions
$string['loginasdenied'] = 'Attempt to log in as another user without permission';
$string['loginastwice'] = 'Attempt to log in as another user when already logged in as another user';
$string['loginasrestorenodata'] = 'No user data to restore';
$string['loginasoverridepasswordchange'] = 'As you are masquerading as another user, you may choose to %slog in anyway%s ignoring the password change screen.';
$string['loginasoverridelegalaccept'] = 'As you are masquerading as another user, you may choose to %slog in anyway%s without needing to accept the legal statements.';

// Institutions
$string['Add'] = 'Add';
$string['all'] = 'All';
$string['admininstitutions'] = 'Administer institutions';
$string['adminauthorities'] = 'Administer authorities';
$string['addinstitution'] = 'Add institution';
$string['authplugin'] = 'Authentication plugin';
$string['deleteinstitution'] = 'Delete institution';
$string['deleteinstitutionconfirm'] = 'Are you really sure you wish to delete this institution?';
$string['institutionstillhas'] = 'This institution still has %s';
$string['institutionauthinuseby'] = "This institution's authentication is still in use by %s";
$string['institutiononly'] = 'Institution only';
$string['institutionaddedsuccessfully2'] = 'Institution added successfully';
$string['institutiondeletedsuccessfully'] = 'Institution deleted successfully';
$string['noauthpluginforinstitution'] = 'Your site administrator has not configured an authentication plugin for this institution.';
$string['adminnoauthpluginforinstitution'] = 'Please configure an authentication plugin for this institution.';
$string['institutionname'] = 'Institution name';
$string['institutionshortname'] = 'Institution short name';
$string['institutionnamealreadytaken'] = 'This institution name is already taken.';
$string['institutiondisplayname'] = 'Institution display name';
$string['institutionexpiry'] = 'Institution expiry date';
$string['institutionshortnamedescription'] = 'The short name is generated automatically and cannot be changed.';
$string['institutionexpirydescription'] = 'The date at which this institutions membership of %s will be suspended.';
$string['institutionlanguage'] = 'Language';
$string['institutionlanguagedescription'] = 'The default language for users in this institution.';
$string['defaultlangforinstitution'] = '%s default';
$string['institutionupdatedsuccessfully'] = 'Institution updated successfully.';
$string['registrationallowed'] = 'Registration allowed';
$string['registrationalloweddescription5'] = 'People can register for this institution using the registration form. If registration is off, non-members cannot request membership of the institution and members cannot leave the institution or delete their user accounts themselves.';
$string['registrationconfirm'] = 'Confirm registration';
$string['registrationconfirmdescription3'] = 'Registration must be approved by an institution administrator. If you cannot change this option, the site administrator requires all institutions to have this option turned on.';
$string['defaultmembershipperiod'] = 'Default membership period';
$string['defaultmembershipperioddescription'] = 'How long new members remain associated with the institution.';
$string['showonlineusers'] = 'Show online users';
$string['showonlineusersdescription'] = 'The online users to show to members of this institution. If users are allowed to be in multiple institutions and these have different settings, the most permissive institution settings will be used.';
$string['licensemandatory'] = 'Require license information';
$string['licensemandatorydescription1'] = 'Users will be required to select a license when creating artefacts. Otherwise, they will be able to leave the license field blank.';
$string['licensedefault'] = 'Default license';
$string['licensedefaultdescription'] = 'The default license for content created or uploaded by institution members. Users can override this on their account settings page and on the individual items.';
$string['licensedefaultmandatory'] = 'If users are required to choose a license, you need to choose a default license here. If you do not want to choose a default license, do not make it required for users.';

$string['Logo'] = 'Logo';
$string['Logomobile'] = 'Mobile logo';
$string['logodescription1'] = 'You can upload an image here that will be displayed to your institution\'s members as logo for your theme. The logo will be resized to fit the space available.';
$string['logoxsdescription'] = 'You can upload a second logo that is to be used on small devices when there is not much space. The logo should be square for best results.';
$string['deletelogo'] = 'Delete logo';
$string['deletelogoxsmobile'] = 'Delete mobile logo';
$string['deletelogodescription2'] = 'Revert to the standard header logo for your institution\'s theme.';
$string['deletelogoxsdescription3'] = 'Revert to the standard header mobile logo for your institution\'s theme.';
$string['customtheme'] = 'Custom theme configuration';
$string['customtheme.background'] = 'Header background';
$string['customtheme.backgroundfg'] = 'Text on header background';
$string['customtheme.link'] = 'Links';
$string['customtheme.headings'] = 'Headings';
$string['customtheme.navbg'] = 'Navigation background';
$string['customtheme.navfg'] = 'Navigation text';
$string['customtheme.subbg'] = 'Sub navigation background';
$string['customtheme.subfg'] = 'Sub navigation text';
$string['customtheme.sidebarbg'] = 'Sidebar background';
$string['customtheme.sidebarfg'] = 'Sidebar content background';
$string['customtheme.sidebarlink'] = 'Sidebar link';
$string['customtheme.rowbg'] = 'Row background';
$string['customstylesforinstitution'] = 'Custom styles for %s';
$string['resetcolours'] = 'Reset colours';
$string['resetcoloursdesc2'] = 'Restore the default colours.';

$string['authenticatedby'] = 'Authentication method';
$string['authenticatedbydescription'] = 'How this user authenticates to Mahara';
$string['remoteusername'] = 'Username for external authentication';
$string['remoteusernamedescription1'] = 'If this user signs in to %s from a remote site using an external authentication plugin, this is the username which identifies the user on the remote site.';
$string['institutionsettings'] = 'Institution settings';
$string['institutionsettingsdescription'] = 'Here you can change settings regarding this user\'s membership for institutions you are an administrator of.';
$string['changeinstitution'] = 'Change institution';
$string['institutionstaff'] = 'Institution staff';
$string['institutionadmin'] = 'Institution administrator';
$string['institutionadmins'] = 'Institution administrators';
$string['institutionadministrator'] = 'Institution administrator';
$string['institutionadmindescription1'] = 'Allow the user to administer all users in this institution.';
$string['settingsfor'] = 'Settings for:';
$string['institutionmembers'] = 'Institution members';
$string['notadminforinstitution'] = 'You are not an administrator for that institution.';
$string['institutionmemberspagedescription'] = 'On this page, you can see users who have requested membership of your institution and add them as members. You can also remove users from your institution and invite users to join.';
$string['suspendordeletethisuser'] = 'Suspend or delete this user';

$string['institutionusersinstructionsrequesters1'] = 'The list of users in the "%1$s" field shows all users who have asked to join your institution. You can use the search box to reduce the number of users displayed. If you would like to add users to the institution or decline their membership requests, first move some users to the "%2$s" field by selecting one or more users and then clicking on the right arrow. The "Add members" button will add all the users in the "%2$s" field to the institution. The "Decline requests" button will remove the membership requests of the users in the "%2$s" field.';
$string['institutionusersinstructionsnonmembers1'] = 'The list of users in the "%1$s" field shows all users who are not yet members of your institution. You can use the search box to reduce the number of users displayed. To invite users to join the institution, first move some users to the "%2$s" field by selecting one or more users and then clicking on the right arrow button to move those users to the "%2$s" field. The "Invite users" button will send invitations to all the users in the "%2$s" field. These users will not be associated with the institution until they accept the invitation.';
$string['institutionusersinstructionslastinstitution1'] = 'The list of users in the "%1$s" field shows all users who are not yet members of your institution and who have left the selected institution. You can use the search box to reduce the number of users displayed. To invite users to join the institution, first move some users to the "%2$s" field by selecting one or more users and then clicking on the right arrow to move those users to the "%2$s" list. The "Invite users" button will send invitations to all the users in the "%2$s". These users will not be associated with the institution until they accept the invitation.';
$string['institutionusersinstructionsmembers1'] = 'The list of users in the "%1$s" field shows all members of the institution. You can use the search box to reduce the number of users displayed. To remove users from the institution, first move some users to the "%2$s" field by selecting one or more users and then clicking on the right arrow. The users you selected will move to the "%2$s" field. The "Remove users" button will remove all the users in the "%2$s" field from the institution.';
$string['institutionusersinstructionsinvited1'] = 'The list of users in the "%1$s" field shows all users who have been sent an invitation to join the institution and who have not yet accepted or declined. You can use the search box to reduce the number of users displayed. To revoke invitations to the institution, first move some users to the "%2$s" field by selecting one or more users and then clicking on the right arrow. The users you selected will move to the "%2$s" field. The "Revoke invitations" button will remove all invitations to the users in the "%2$s" field. The other users will retain their invitations and will still be able to join at any time.';

$string['editmembers'] = 'Edit members';
$string['editstaff'] = 'Edit staff';
$string['editadmins'] = 'Edit administrators';
$string['membershipexpiry'] = 'Membership expires';
$string['membershipexpirydescription'] = 'Date on which the user will be removed automatically from the institution.';
$string['studentid'] = 'ID number';
$string['institutionstudentiddescription'] = 'An optional identifier specific to the institution. This field is not editable by the user.';

$string['userstodisplay'] = 'Users to display:';
$string['institutionusersrequesters'] = 'People who have requested institution membership';
$string['institutionusersnonmembers'] = 'People who have not requested institution membership yet';
$string['institutionuserslastinstitution'] = 'People who have left a given institution';
$string['institutionusersmembers'] = 'People who are already institution members';
$string['institutionusersinvited'] = 'People who have been invited';

$string['addnewmembers'] = 'Add new members';
$string['usersrequested'] = 'Users who have requested membership';
$string['userstobeadded'] = 'Users to be added as members';
$string['userstoaddorreject'] = 'Users to be added / rejected';
$string['addmembers'] = 'Add members';
$string['inviteuserstojoin'] = 'Invite users to join the institution';
$string['userswhohaveleft'] = 'Users who have left institution %s';
$string['Non-members'] = 'Non-members';
$string['userstobeinvited'] = 'Users to be invited';
$string['inviteusers'] = 'Invite users';
$string['removeusersfrominstitution'] = 'Remove users from the institution';
$string['currentmembers'] = 'Current members';
$string['userstoberemoved'] = 'Users to be removed';
$string['removeusers'] = 'Remove users';
$string['declinerequests'] = 'Decline requests';
$string['nousersupdated'] = 'No users were updated';
$string['errorupdatinginstitutionusers'] = 'An error occurred when updating institution users';
$string['revokeinvitations'] = 'Revoke invitations';
$string['invitedusers'] = 'Invited users';
$string['userstobeuninvited'] = 'Users to be uninvited';
$string['moveuserstoadd'] = 'Turn selected member requests into members';
$string['moveusersfromadd'] = 'Turn selected members into member requests';
$string['moveuserstoinvite'] = 'Turn selected non-members into invited';
$string['moveusersfrominvite'] = 'Turn selected invited into non-members';
$string['moveuserstoinviteinstitution'] = 'Turn selected old %s users into invited';
$string['moveusersfrominviteinstitution'] = 'Turn selected invited users into old %s users';
$string['movememberstoremove'] = 'Turn selected members into removed members';
$string['movemembersfromremove'] = 'Turn selected removed members into members';
$string['moveuserstouninvited'] = 'Turn selected invited users into uninvited';
$string['moveusersfromuninvited'] = 'Turn selected uninvited users into invited';

$string['institutionusersupdated_addUserAsMember'] = 'Users added';
$string['institutionusersupdated_declineRequestFromUser'] = 'Requests declined';
$string['institutionusersupdated_removeMembers'] = 'Users removed';
$string['institutionusersupdated_inviteUser'] = 'Invitations sent';
$string['institutionusersupdated_uninvite_users'] = 'Invitations removed';

$string['maxuseraccounts'] = 'Maximum user accounts allowed';
$string['maxuseraccountsdescription'] = 'The maximum number of user accounts that can be associated with the institution. If there is no limit, this field should be left blank.';
$string['institutionmaxusersexceeded'] = 'This institution is full. You will have to increase the number of allowed users in this institution before this user can be added.';
$string['institutionuserserrortoomanyusers'] = 'The users were not added. The number of members cannot exceed the maximum allowed for the institution. You can add fewer users, remove some users from the institution or ask the site administrator to increase the maximum number of users.';
$string['institutionuserserrortoomanyinvites'] = 'Your invitations were not sent. The number of existing members plus the number of outstanding invitations cannot exceed the institution\'s maximum number of users. You can invite fewer users, remove some users from the institution or ask the site administrator to increase the maximum number of users.';

$string['Shortname'] = 'Short name';
$string['Members'] = 'Members';
$string['Maximum'] = 'Maximum';
$string['Staff'] = 'Staff';
$string['Admins'] = 'Administrators';

$string['noinstitutions'] = 'No institutions';
$string['noinstitutionsdescription'] = 'If you would like to associate users with an institution, you should create the institution first.';
$string['noinstitutionreports'] = 'Unable to view institution reports';
$string['noinstitutionreportsdescription'] = 'Staff report access needs to be turned on for the site to allow staff to view their institution reports. A site administrator can turn this setting on in "User settings" under <a href="%sadmin/site/options.php">Configure site</a>.';
$string['noinstitutionstaticpages'] = 'You do not have permission to edit any institution static pages or no institutions have been created yet.';
$string['noinstitutionstaticpagesadmin'] = 'If you are a site administrator, please edit the default static pages in <a href="%s">Configure site</a>.';

$string['Lockedfields'] = 'Locked fields';
$string['disabledlockedfieldhelp1'] = 'Note: If you cannot change one of the options, the profile fields are locked in the institution settings for "%s". These profile fields are locked at the site level and cannot be unlocked here.';

$string['defaultinstitutionquotadescription'] = 'You can set the amount of disk space new users in this institution will have as their quota.';
$string['updateinstitutionuserquotasdesc2'] = 'Apply the default quota you choose above to all existing members.';
$string['institutiontagsdesc'] = 'If institution tags are enabled, set up tags that members can use';

// pending institution registrations
$string['approve'] = 'Approve';
$string['deny'] = 'Deny';
$string['approveregistrationfor2'] = 'Approve registration for %s %s <%s>';
$string['approveregistrationmessage'] = 'This will approve the registration and add the user to the institution \'%s\'. Are you sure you want to approve this registration?';
$string['denyregistrationfor'] = 'Deny registration for \'%s %s\'';
$string['denyregistrationmessage'] = 'This will deny the registration for the user. Are you sure you want to deny this registration?';
$string['nopendingregistrations'] = 'No pending registrations were found for this institution.';
$string['pendingregistration'] = 'Pending registration';
$string['pendingregistrations'] = 'Pending registrations';
$string['pendingregistrationspagedescription2'] = '<p>On this page you can see users who have self-registered and requested membership in your institution. You can approve or deny their registration request.<p>
<p>On approving their registration, they are notified with further instructions about activating their account. When they follow the activation link supplied, they are added as a member of the institution. The activation link expires after 24 hours and their details are removed. If they still want to register, they will need to begin the process again.</p>
<p>On denying their registration, they will be notified of that automatically.</p>';
$string['pendingregistrationdesc'] = 'Approve or deny membership in your institution to self-registering users';
$string['pendingdeletiondesc'] = 'Approve or deny the deletion of users who wish to remove their accounts';
$string['pendingusercompletion'] = 'Approval sent, waiting on user completion';
$string['nosuchinstitution'] = 'No such institution.';
$string['registrationapprovedsuccessfully'] = 'Registration approved successfully.';
$string['registrationapprovedmessage'] = 'Registration message:';
$string['registrationapproveddesc'] = 'Additional information for newly registered users.';
$string['registrationdeniedreason'] = 'Denial reason';
$string['registrationdeniedreasondesc'] = 'Information as to why the application was denied that might help the user.';
$string['registrationdeniedsuccessful'] = 'Registration denied successfully.';
$string['registrationdeniedunsuccessful'] = 'The attempted registration denial failed.';
$string['registrationreason'] = 'Registration reason';
$string['makeuserinstitutionstaff'] = 'Automatically assign institution staff permissions to the owner of this email the first time they log in';

// Suspend Institutions
$string['errorwhileunsuspending'] = 'An error occurred while trying to unsuspend';
$string['institutionsuspended'] = 'Institution suspended';
$string['institutionunsuspended'] = 'Institution unsuspended';
$string['institutionlogoutusers'] = array(
    0 => 'Logged out 1 user',
    1 => 'Logged out %s users',
);
$string['suspendedinstitution'] = 'SUSPENDED';
$string['suspendinstitution'] = 'Suspend institution';
$string['suspendinstitutiondescription'] = 'Here you may suspend an institution. Users using an authentication method of a suspended institution will be unable to log in until the institution is unsuspended.';
$string['suspendedinstitutionmessage'] = 'This institution has been suspended.';
$string['unsuspendinstitution'] = 'Unsuspend institution';
$string['unsuspendinstitutiondescription'] = 'Here you may unsuspend an institution. Users of suspended institutions will be unable to log in until the institution is unsuspended.<br /><strong>Beware:</strong> Unsuspending an institution without resetting or turning off its expiry date may result in a daily re-suspension.';
$string['unsuspendinstitutiondescription_warning'] = '<em>Beware:</em> Unsuspending an institution without resetting or turning off its expiry date may result in a daily re-suspension.';
$string['unsuspendinstitutiondescription_instadmin'] = 'Users of suspended institutions are unable to log in. Contact the site administrator to unsuspend the institution.';

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
$string['withselectedusers'] = 'With selected users';
$string['withselectedusersedit'] = 'Edit selected users';
$string['withselectedusersreports'] = 'Get reports for selected users';
$string['getreports'] = 'Get reports';
$string['selectuser'] = 'Select user "%s"';

// Bulk actions & user reports
$string['bulkactions'] = 'Bulk actions';
$string['editselectedusersdescription1'] = 'Suspend, delete or change the authentication method of the users you have selected on the search page.';
$string['uneditableusers'] = array(
    0 => 'One of the users you selected is not editable by you and has been removed from the list.',
    1 => 'You selected %s users that are not editable by you. They have been removed from the list.',
);
$string['exportusersascsv'] = 'Export users in CSV format';
$string['downloadusersascsv'] = 'users in CSV format';
$string['downloaddataascsv'] = '%s statistics in CSV format';
$string['Download'] = 'Download';
$string['suspendusers'] = 'Suspend users';
$string['Suspend'] = 'Suspend';
$string['bulksuspenduserssuccess'] = 'Suspended %d user(s)';
$string['changeauthmethod'] = 'Change authentication method';
$string['someusersnotinauthinstanceinstitution'] = 'Some of the users you have selected are not in the institution associated with this authentication method.';
$string['bulkchangeauthmethodsuccess'] = 'Reset authentication method for %d user(s)';
$string['bulkchangeauthmethodresetpassword'] = 'You have chosen an authentication method that requires a password. %d user(s) do not have a password and will not be able to log in until their passwords are reset.';
$string['bulkdeleteuserssuccess'] = 'Deleted %d user(s)';
$string['bulkprobationpointssuccess'] = array(
    0 => 'Set probation points to %2$d for %1$d user',
    1 => 'Set probation points to %2$d for %1$d users'
);
$string['selectedusers'] = 'Selected users';
$string['selectednusers'] = array(
    0 => '1 user selected',
    1 => '%s users selected',
);
$string['remoteuser'] = 'Remote username';
$string['quotapercent'] = 'Quota used';
$string['userreports'] = 'User reports';
$string['userreportsdescription'] = 'View or download information about the users you selected on the search page.';
$string['unabletodeleteself1'] = 'You are not allowed to delete yourself.';
$string['unabletodeletealladmins1'] = 'You are not allowed to delete all site administrators.';

// general stuff
$string['notificationssaved'] = 'Notification settings saved';
$string['onlyshowingfirst'] = 'Only showing first';
$string['resultsof'] = 'results of';
$string['deprecated'] = '- deprecated';

$string['installed'] = 'Installed';
$string['errors'] = 'Errors';
$string['install'] = 'Install';
$string['reinstall'] = 'Reinstall';

// spam trap names
$string['None'] = 'None';
$string['Simple'] = 'Simple';
$string['Advanced'] = 'Advanced';

//admin option fieldset legends
$string['sitesettingslegend'] = 'Site settings';
$string['usersettingslegend'] = 'User settings';
$string['groupsettingslegend'] = 'Group settings';
$string['searchsettingslegend'] = 'Search settings';
$string['institutionsettingslegend'] = 'Institution settings';
$string['accountsettingslegend'] = 'Account settings';
$string['securitysettingslegend'] = 'Security settings';
$string['generalsettingslegend'] = 'General settings';
$string['loggingsettingslegend'] = 'Logging settings';

$string['groupname'] = 'Group name';
$string['groupshortname'] = 'Short name';
$string['groupmembers'] = 'Members';
$string['groupadmins'] = 'Administrators';
$string['grouptype'] = 'Group type';
$string['groupvisible'] = 'Visibility';
$string['groupmanage'] = 'Manage';
$string['groupmanagespecific'] = 'Manage "%s"';
$string['groupdelete'] = 'Delete';
$string['managegroupquotadescription1'] = 'Use this form to change the group file quota for this group.';
$string['managegroupdescription1'] = 'Use this form to promote and demote administrators for this group. If you remove a group administrator they will remain a group member.';

$string['userscandisabledevicedetection'] = 'Users can disable device detection';
$string['userscandisabledevicedetectiondescription1'] = 'Users will be allowed to disable mobile device detection when they are browsing this site.';

// Admin user search logged in filter
$string['loggedinfilter'] = 'Filter by login date:';
$string['anyuser'] = 'Any user';
$string['usershaveloggedin'] = 'Users have logged in';
$string['usershaveneverloggedin'] = 'Users have never logged in';
$string['usershaveloggedinsince'] = 'Users have logged in since';
$string['usershavenotloggedinsince'] = 'Users have not logged in since';

// Admin user search duplicate email filter
$string['duplicateemailfilter1'] = 'Duplicate email addresses';

// Admin user search objectionable content filter
$string['objectionable'] = 'Objectionable content';
$string['objectionablefilter'] = 'People with objectionable content';

$string['noemailfound'] = 'No email address found';

$string['lastlogin'] = 'Last login';

// Masquerading reasons and notification
$string['masqueradingreasonrequired'] = 'Require reason for masquerading';
$string['masqueradingreasonrequireddescription3'] = 'Require administrators to enter a reason for masquerading as other users. If the setting "Notify users of masquerading" is enabled, the reason will be included in the notification to the user about the masquerading. The logging of masquerading sessions needs to be turned on in the "Logging settings" for this to work.';
$string['masqueradingnotified'] = 'Notify users of masquerading';
$string['masqueradingnotifielddescription'] = 'Notify users when an administrator masqueraded as them. The notification will include who, when and - if enabled under "Require reason for masquerading" - why. The logging of masquerading sessions needs to be turned on in the "Logging settings" for this to work.';

$string['masquerade'] = 'Continue';
$string['masqueradereason'] = 'Reason';
$string['masqueradereasondescription'] = 'Please enter a reason for logging in as this user. Note: The user will not be notified of this reason, but it will be logged.';
$string['masqueradenotificationdone'] = 'The user has been notified of this masquerading session.';
$string['masqueradenotifiedreasondescription'] = 'Please enter a reason for logging in as this user. Note: The user will receive a message containing your name, the date and time as well as the reason for your masquerading.';
$string['masqueradetime'] = 'Start of masquerading';
$string['masquerader'] = 'Masquerading administrator';
$string['masqueradee'] = 'User';
$string['nomasquerades'] = 'No administrator has masqueraded yet as another user since the logging of masquerading sessions has been turned on.';
$string['loginaslog'] = 'Masquerading sessions';
$string['masqueradingnotloggedwarning'] = '<b>Note</b>: Logging of masquerading sessions is currently disabled. In order to see data in this table, the site administrator needs to turn it on in "Logging settings" under "<a href="%sadmin/site/options.php">Configure site</a>".';
$string['masqueradenotificationsubject'] = 'An administrator logged in as you';
$string['masqueradenotificationnoreason'] = 'The administrator %s logged into your account on %s.';
$string['masqueradenotificationreason'] = 'The administrator %s logged into your account on %s. The reason was: %s';

// Progress bar / Profile completion
$string['progressbar'] = 'Profile completion';
$string['showprogressbar'] = 'Show profile completion';
$string['progressbarsaved'] = 'Progress bar saved successfully.';
$string['showprogressbardescription1'] = 'Display a progress bar with tips about what to complete in the user profile as a sidebar to users. They have the option to disable it.';
$string['progressbardisablednote'] = '<b>Note</b>: Profile completion is currently disabled. You will need to enable it in the "User settings" section of "<a href="%sadmin/site/options.php">Configure site</a>" before users will be able to track their progress towards completing their profile.';
$string['profilecompletiondesc'] = 'If profile completion is enabled, select content that counts towards a completed profile';
$string['profilecompletenessdesc1'] = 'The profile completion allows your users to have a visual indicator in the sidebar showing them how complete their profile already is. You can choose the artefacts that will count towards the profile completion. All other artefacts can be used, but do not factor into the completion count.';
$string['profilecompletenesspreview'] = 'You can preview what the profile completion looks like in the "Profile completion preview" side block.';

$string['exporttoqueue'] = 'Export to queue';
$string['exporttoqueuedescription2'] = 'Let the export queue handle the exporting of user portfolios via Leap2A for better server load management.';

// Progress meter (ie uploading / downloading data)
$string['validating'] = 'Validating data...';
$string['checkingupdates'] = 'Checking updated data...';
$string['committingchanges'] = 'Saving changes...';

// Password reset script
$string['cli_pwreset_authupdated'] = 'Auth method updated to "internal".';
$string['cli_pwreset_failure'] = 'ERROR: Unable to successfully reset the password for "%s".';
$string['cli_pwreset_forcepasswordchange'] = 'Force password change on next login (Default "true" if you use the "--password" option; "false" otherwise).';
$string['cli_pwreset_info'] = 'This command-line PHP script allows you to reset a user\'s password. This will only work for users whose auth method allows password resets (e.g. "internal").' ;
$string['cli_pwreset_makeinternal'] = 'Change the user\'s auth method to "internal" (Default "false").';
$string['cli_pwreset_nointernalauth'] = 'ERROR: Couldn\'t find default "internal" auth method.';
$string['cli_pwreset_nosuchuser'] = 'ERROR: There is no user with username "%s" in the database.';
$string['cli_pwreset_notsupported'] = 'ERROR: User "%s" has an auth method that doesn\'t support password resets. Use the "-i=true" option if you want to change them to "internal" auth.';
$string['cli_pwreset_password'] = 'The new password. If this parameter is not supplied, the script will prompt you for a password.';
$string['cli_pwreset_prompt1'] = 'Enter new password';
$string['cli_pwreset_prompt2'] = 'Retype new password';
$string['cli_pwreset_success'] = 'Successfully reset password for user "%s".';
$string['cli_pwreset_success_forcepasswordchange'] = 'The user will be forced to reset their password at their next login.';
$string['cli_pwreset_typo'] = 'Sorry, passwords do not match.';

// Maintenance mode script
$string['cli_close_site_info'] = 'This command-line PHP script allows you to close your site to non-admin users, and open it again. (This is the same as pressing the "Close site" button on the Administration homepage.)';
$string['cli_close_site_siteclosed'] = 'Site closed for maintenance.';
$string['cli_close_site_siteopen'] = 'Site open.';

// Languange update script
$string['cli_backupdir_notwritable'] = 'The language backup directory "%s" is not writable.';
$string['cli_langpack'] = 'List by language code, e.g. "de,pt_BR,fr".';
$string['cli_langpack_backup'] = 'Optional: Back up the language directory before updating.';
$string['cli_langpack_backup_done'] = 'Backup of language "%s" done';
$string['cli_langpack_backup_failed'] = '*** WARNING *** Unable to make backup of "%s"';
$string['cli_langpack_en'] = 'You do not need to update the English langpack as the strings are defined in Mahara itself. You can set special lang strings in local/lang/.';
$string['cli_langpack_extract_done'] = 'Copied new "%s" langpack into place';
$string['cli_langpack_extract_failed'] = '*** WARNING *** Unable to extract files from "%s". Reason: %s';
$string['cli_langpack_info'] = 'To update your language packs via the command line.
You can:
1) Install or update langpacks by listing the languages by their code, e.g. de = German, fr = French:
   sudo -u www-data php admin/cli/sync_langpacks.php -l=de,fr

This will make a backup of the existing language (if it already exists) into the dataroot langpacks_backup directory.

2) If you don\'t want to make backups, then add the -b=false flag:
   sudo -u www-data php admin/cli/sync_langpacks.php -l=de,fr -b=false

3) If you need to rollback the backups, you can use the -r=true flag:
   sudo -u www-data php admin/cli/sync_langpacks.php -l=de,fr -r=true

It will ignore trying to update the \'en\' language as that is set within Mahara itself.';
$string['cli_langpack_missing'] = 'No langpacks specified';
$string['cli_langpack_revert'] = 'Revert to last backed up version of the language.';
$string['cli_langdir_notwritable'] = 'The language directory "%s" is not writable.';
$string['cli_language_make_backup'] = 'Make a backup? %s';
$string['cli_language_status'] = 'The language "%s" is already installed: %s';
$string['cli_langpack_url'] = 'Language pack URL "%s"';
$string['cli_langpack_url_failed'] = '*** WARNING *** Language pack for "%s" does not exist or cannot be downloaded. Return code: %s';
$string['cli_langpack_upload'] = 'Saving language pack "%s" to temp directory';
$string['cli_restore_done'] = 'Restored backup of "%s" done';
$string['cli_restore_warning'] = '*** WARNING *** Unable to restore backup of "%s"';
$string['cli_tmpdir_notwritable'] = 'The temporary upload directory "%s" is not writable.';
$string['cli_lang_branch'] = 'Mahara series version to fetch langpacks for series "%s"';

// Fast index for elasticsearch
$string['cli_fast_index'] = 'Elasticsearch fast indexer allows quicker indexing of sites by avoiding waiting for the next cron run. Instead, it begins the next run immediately after first finishes.';
$string['cli_unabletoupdatecron'] = 'Unable to update the search cron database record';
$string['cli_problemindexing'] = 'A problem occurred while indexing';
$string['cli_done'] = 'Indexing finished';

// Clean up old users
$string['cli_param_dryrun'] = 'Dry run to indicate what will happen. Set to "true" by default. Need to set -d=false to actually update database.';
$string['cli_deleteinactiveusers_beforedate'] = 'Delete users where their last login date is earlier than this date. Date can be any datetime string, e.g. "2018-02-25" or "-6 months".';
$string['cli_deleteinactiveusers_limit'] = 'Limit of users to work with for each delete run.';
$string['cli_deleteinactiveusers_cleanusers'] = 'Try to delete users from the "usr" table as well.';
$string['cli_deleteinactiveusers_info'] = 'This command-line PHP script allows you to delete old users. This will only work for users who either have not logged in or have never made a forum post.';
$string['cli_deleteinactiveusers_neverloggedin'] = 'Delete users that have never logged in (were set up by an admin).';
$string['cli_param_baddate'] = 'The supplied date "%s" is not valid.';
$string['cli_deleteinactiveusers_usercount'] = 'There are "%s" users to delete.';
$string['cli_deleteinactiveusers_userunabletoclean'] = 'Unable to delete user "%s" (ID %s) fully.';
$string['cli_deleteinactiveusers_userunabletodelete'] = 'Unable to delete user "%s" (ID %s).';
$string['cli_deleteinactiveusers_nouserstodelete'] = 'There are no users to delete.';
$string['cli_deleteinactiveusers_onlydryrun1'] = 'This is only a dry run with the following settings:
Institution: %s
Group: %s
Before date: %s
Delete users from "usr" table: %s
Never logged in: %s';

// Clean up old groups
$string['cli_deleteinactivegroups_info'] = 'This command-line PHP script allows you to delete old groups. This will only work for groups that do not have any members. This will also include groups with only one owner / administrator if the "onlyadmins" flag is set.';
$string['cli_deleteinactivegroups_beforedate'] = 'Delete groups where their last modification date is earlier than this date. Date can be any datetime string, e.g. "2018-02-25" or "-6 months".';
$string['cli_deleteinactivegroups_limit'] = 'Limit of groups to work with for each delete run.';
$string['cli_deleteinactivegroups_cleangroups'] = 'Try to delete groups from the "group" table as well.';
$string['cli_deleteinactivegroups_onlyadmins'] = 'Also include groups that only have owner / administrator as members.';
$string['cli_deleteinactivegroups_onlydryrun'] = 'This is only a dry run with the following settings:
Institution: %s
Before date: %s
Delete groups from "group" table: %s
Also delete groups that only contain owner/admin: %s
%s';
$string['cli_deleteinactivegroups_danger'] = '
*** The settings chosen will delete all groups ***
If this is what you desire, then set the --beforedate to a date in the future.';
$string['cli_deleteinactivegroups_groupcount'] = 'There are "%s" groups to delete.';
$string['cli_deleteinactivegroups_nogroupstodelete'] = 'There are no groups to delete.';
$string['cli_deleteinactivegroups_emptygroups'] = 'Only delete groups that do not have any members.';
$string['cli_deleteinactivegroups_groupunabletoclean'] = 'Unable to fully delete group "%s" (ID %s).';

$string['withselectedcontentexport'] = 'Re-queue items into the export queue';
$string['withselectedcontentdelete'] = 'Delete selected items from the export queue';
$string['allothers'] = 'All others';

// Behat variables script
$string['behatvariables'] = 'Behat steps';
$string['behatvariablesdesc'] = 'Tool to find available Behat steps quickly';
$string['nobehatfeaturefiles'] = 'No Behat feature files found';
$string['behatmatchingrows'] = '%s in ';
$string['behatnocore'] = 'Unable to read core Behat steps from behat.yml file';
$string['behatstepnotused'] = 'Currently, this Behat step is not used.';

// Privacy Statement section
$string['author'] = 'Author';
$string['content'] = 'Content';
$string['creationdate'] = 'Creation date';
$string['version'] = 'Version';
$string['viewversion'] = 'View version %s';
$string['editversion'] = 'Edit version %s';
$string['privacylastversion'] = 'Privacy statements must have unique version names of up to 15 characters. The latest version for this privacy statement is "%s".';
$string['termsandconditionslastversion'] = 'Terms and conditions must have unique version names of up to 15 characters. The latest version for these terms and conditions is "%s".';
$string['versionalreadyexist'] = 'A %s version with the name "%s" already exists.';
$string['siteprivacy'] = 'Site privacy statement';
$string['siteprivacyconsentdate'] = 'Site privacy consent reviewed';
$string['sitetermsandconditions'] = 'Site terms and conditions';
$string['sitetermsconsentdate'] = 'Site terms and conditions consent reviewed';
$string['addoneversionlink']   = '<a href="%s">Add one</a> if you need it.';
$string['privacyversionfor'] = 'Privacy statement for version "%s" is as follows:';
$string['termsversionfor'] = 'Terms and conditions for version "%s" are as follows:';
$string['institutionprivacystatement'] = 'Institution privacy statement';
$string['institutiontermsandconditions'] = 'Institution terms and conditions';
$string['institutionprivacyconsentdate'] = 'Institution privacy consent reviewed';
$string['institutiontermsconsentdate'] = 'Institution terms and conditions consent reviewed';
$string['userprivacypagedescription1'] = 'Displayed are the current privacy statements and terms and conditions.';
$string['lastupdated'] = 'Last updated on';
$string['newprivacy'] = 'Before entering your account, please read the information displayed below.';
$string['privacyagreement'] = 'I consent to the %s';
$string['privacyagreementdescription'] = 'By choosing "Yes", you give your consent to the clauses of the privacy statement above.';
$string['privacyagreedto'] = 'You consented to this %s in %s.';
$string['agreementsaved'] = 'Agreement saved';
$string['refuseprivacy'] = 'Refuse privacy statement or terms and conditions';
$string['confirmprivacyrefusal'] = 'Are you really sure you wish to continue?';
$string['privacyrefusaldetails'] = 'If you do not consent to the privacy statement(s) or terms and conditions, your account will be suspended.';
$string['privacyrefusal'] = 'Refused to consent to the privacy statement.';
$string['registerrefusal'] = 'Your account will not be created when you do not consent to the %s.';
$string['registerprivacy'] = 'Please read the privacy statement. If you do not consent to it, you cannot create an account on the site.';
$string['registertermsandconditions'] = 'Please read the terms and conditions. If you do not consent to them, you cannot create an account on the site.';
$string['enterreason'] = 'Please enter the reason of refusal here...';
$string['hasrefused'] = 'has refused the privacy statement';
$string['privacylowcase'] = 'privacy statement';
$string['termsandconditionslowcase'] = 'terms and conditions';
$string['hasrefused'] = 'has refused the %s';
$string['privacyandtotheterms'] = 'privacy statement and to the terms and conditions';
$string['privacyandtheterms'] = 'privacy statement and the terms and conditions';
$string['consentdate'] = 'Date of consent / rejection';
$string['legalconsent'] = 'Legal consent';
$string['userprivacyagreements'] = 'Displayed below are all the privacy statements the user has agreed to.';
$string['usertermsagreements'] = 'Displayed below are all the terms and conditions the user has agreed to.';
$string['privacytermsdesc'] = 'Edit the privacy statement or terms and conditions for your site';

// pending user account deletions
$string['approveuserdeletionfor'] = 'Approve user account deletion for %s %s <%s>';
$string['approveuserdeletionmessage'] = 'If you continue, you will delete the user account "%s". All personal content will be deleted permanently. Are you sure you want to approve this request?';
$string['deletionapprovedsuccessfully'] = 'User account deletion approved successfully.';
$string['deletionapprovedfailed'] = 'User account deletion failed.';
$string['deletiondeniedreason'] = 'Denial reason';
$string['deletiondeniedreasondesc'] = 'Information as to why the deletion request was denied that might help the user.';
$string['deletionreason'] = 'Deletion reason';
$string['denyuserdeletionfor'] = 'Deny user account deletion request for \'%s %s\'';
$string['denyuserdeletionmessage'] = 'If you continue, you will not approve the deletion of the account. Please provide a reason for the user.';
$string['nopendingdeletions'] = 'No pending account deletion requests were found for this institution.';
$string['pendingdeletion'] = 'Pending deletion';
$string['pendingdeletions'] = 'Pending deletions';
$string['pendingdeletionspagedescription'] = '<p>On this page you can see users who requested their accounts to be deleted from the site and approve their deletion.<p>
<p>On approving the deletion of an account, all personal content from that account will be deleted permanently. The person who made the request will be notified via email once their account has been deleted.</p>';
$string['userdeletiondeniedsuccessful'] = 'Request denied successfully.';
$string['userdeletiondeniedunsuccessful'] = 'The attempted user account deletion denial failed.';
$string['consented'] = 'Consented';
$string['groupid'] = 'Group ID';
