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
 * @subpackage blocktype-externalfeeds
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'External Feed';
$string['description'] = 'Embed an external RSS or ATOM feed';
$string['feedlocation'] = 'Feed location';
$string['feedlocationdesc'] = 'URL of a valid RSS or ATOM feed';
$string['itemstoshow'] = 'Items to show';
$string['itemstoshowdescription'] = 'Between 1 and 20';
$string['showfeeditemsinfull'] = 'Show feed items in full?';
$string['showfeeditemsinfulldesc'] = 'Whether to show a summary of the feed items, or show the full text for each one too';
$string['invalidurl'] = 'That URL is invalid. You can only view feeds for http and https URLs.';
$string['invalidfeed'] = 'The feed appears to be invalid. The error reported was: %s';
$string['lastupdatedon'] = 'Last updated on %s';
$string['defaulttitledescription'] = 'If you leave this blank, the title of the feed will be used';
