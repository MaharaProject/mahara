<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2011 Catalyst IT Ltd and others; see:
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
 * @subpackage blocktype-openbadgedisplayer
 * @author     Discendum Oy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Discedum Oy http://discendum.com
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
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

$string['nobackpack'] = 'No Backpack found.<br> Please add your <a href="%s">Backpack</a> email address to your <a href="%s">profile</a>.';

$string['nobadgegroups'] = 'No public badge collections / badges found.';
$string['nobadgesselectone'] = 'No badges selected';
$string['nobackpackidin1'] = 'Your email %s is not found in the service %s.';

$string['nobadgegroupsin1'] = 'No public badge collections / badges found in the service %s for email %s.';

$string['confighelp'] = 'Select the badge collections to show in this block.<br/>Visit the following services to manage your collections and badges:<br/>%s';

$string['obppublicbadges'] = 'All public badges in Open Badge Passport';
$string['title_backpack'] = 'Mozilla Backpack';

$string['title_passport'] = 'Open Badge Passport';

$string['title_badgr'] = 'Badgr Backpack';

$string['fetchingbadges'] = 'Fetching entries. This may take a while.';

$string['missingbadgesources'] = 'Missing sources setting. Please add it to your config.php file, e.g.<br><br>$cfg->openbadgedisplayer_source = \'{"backpack":"https://backpack.openbadges.org/"}\'';

$string['selectall'] = 'Select all';
$string['selectnone'] = 'Select none';

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
$string['nobadgruid1'] = 'Before you can use Badgr, you need to set a token. Please go to "User menu → Settings → Apps → Badgr" to set it.';
