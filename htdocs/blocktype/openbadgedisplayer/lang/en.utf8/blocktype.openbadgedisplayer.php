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

$string['issuerdetails'] = 'Issuer Details';

$string['badgedetails'] = 'Badge Details';

$string['issuancedetails'] = 'Issuance Details';

$string['name'] = 'Name';

$string['url'] = 'URL';

$string['organization'] = 'Organization';

$string['evidence'] = 'Evidence';
$string['issuedon'] = 'Issued On';
$string['expires'] = 'Expires';

$string['desc'] = 'Description';

$string['criteria'] = 'Criteria';

$string['nbadges'] = array('1 badge', '%s badges');

$string['nobackpack'] = 'No Backpack found.<br> Please add your <a href="%s">Backpack</a> email address to your <a href="%s">profile</a>.';

$string['nobadgegroups'] = 'No public badge collections/badges found.';

$string['nobackpackidin'] = 'Your email is not found in the service %s.';

$string['nobadgegroupsin'] = 'No public badge collections/badges found in the service: %s.';

$string['confighelp'] = 'Select the badge collections to show in this block.<br/>Visit the following services to manage your collections and badges:<br/>%s';

$string['obppublicbadges'] = 'All public badges in Open Badge Passport';
$string['title_backpack'] = 'Mozilla Backpack';

$string['title_passport'] = 'Open Badge Passport';

$string['fetchingbadges'] = 'Fetching entries. This may take a while.';

$string['missingbadgesources'] = 'Missing sources setting. Please add to your config.php file, eg:<br><br>$cfg->openbadgedisplayer_source = \'{"backpack":"https://backpack.openbadges.org/"}\'';