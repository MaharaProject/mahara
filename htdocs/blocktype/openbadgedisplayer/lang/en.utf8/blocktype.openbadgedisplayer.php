<?php
/**
* @package    mahara
 * @subpackage blocktype-openbadgedisplayer
 * @author     Discendum Oy
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  (C) 2012 Discedum Oy http://discendum.com
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Open Badges';
$string['description'] = 'Display your Open Badges';

$string['issuerdetails'] = 'Issuer details';

$string['badgedetails'] = 'Badge details';

$string['issuancedetails'] = 'Issuance details';

$string['name'] = 'Name';

$string['url'] = 'URL';

$string['organization'] = 'Organisation';

$string['evidence'] = 'Evidence';
$string['issuedon'] = 'Issued on';
$string['expires'] = 'Expires';

$string['desc'] = 'Description';

$string['criteria'] = 'Criteria';

$string['nbadges'] = array('1 badge', '%s badges');

$string['nobackpack'] = 'No backpack found.<br> Please add your <a href="%s">backpack</a> email address to your <a href="%s">profile</a>.';

$string['nobadgegroups'] = 'No public badge collections / badges found.';
$string['nobadgesselectone'] = 'No badges selected';
$string['nobackpackidin1'] = 'Your email %s is not found in the service %s.';

$string['nobadgegroupsin1'] = 'No public badge collections or badges found in the service %s for email %s.';

$string['confighelp'] = 'Select the badge collections to show in this block.<br/>Visit the following services to manage your collections and badges:<br/>%s';

$string['obppublicbadges'] = 'All public badges in Open Badge Passport';
$string['title_backpack'] = 'Mozilla Backpack';

$string['title_passport'] = 'Open Badge Passport';

$string['title_badgr'] = 'Badgr Backpack';

$string['fetchingbadges'] = 'Fetching entries. This may take a while.';

$string['missingbadgesources'] = 'Missing sources setting. Please add it to your config.php file, e.g.<br><br>$cfg->openbadgedisplayer_source = \'{"backpack":"https://backpack.provider.org"}\'';

$string['selectall'] = 'Select all';
$string['selectnone'] = 'Select none';

$string['deprecatedhost'] = '<p class="alert alert-warning">Badges from the following services are not displayed because they have been discontinued: %s</p>';

// Badgr token page
$string['featuredisabled'] = 'The openbadgedisplayer blocktype is not active';
$string['badgrsourcemissing1'] = 'Badgr is not in the sources configuration in your config.php file.';
$string['badgrusername'] = "Badgr username";
$string['badgrpassword'] = "Badgr password";
$string['badgrtokentitle'] = "Badgr";
$string['badgrtoken'] = "Badgr token: %s";
$string['badgrtokenadded'] = "Badgr token added to account";
$string['badgrtokendeleted'] = "Badgr token deleted";
$string['badgrtokennotfound'] = "Badgr token not found with supplied credentials";
$string['nobadgruid2'] = 'Before you can use Badgr, you need to set a token. Please go to "Account menu → Settings → Apps → Badgr" to set it.';
$string['resetobsoletebadgrtokensubject'] = 'You need to reset your Badgr token.';
$string['resetobsoletebadgrtokenmessage1'] = 'Hi %s,

Your current Badgr token used in %s is obsolete.

This token is used to display your Badgr badges in portfolio pages.

Please go to "Account menu → Settings → Connected apps → Badgr" to set a new one.';
