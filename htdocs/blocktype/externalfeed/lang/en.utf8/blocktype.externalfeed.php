<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-externalfeeds
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'External feed';
$string['description'] = 'Embed an external RSS or ATOM feed';

$string['authuser'] = 'HTTP username';
$string['authuserdesc'] = 'Username (HTTP basic authentication) needed to access this feed (if required)';
$string['authpassword'] = 'HTTP password';
$string['authpassworddesc'] = 'Password (HTTP basic authentication) needed to access this feed (if required)';
$string['feedlocation'] = 'Feed location';
$string['feedlocationdesc'] = 'URL of a valid RSS or ATOM feed';
$string['insecuresslmode'] = 'Insecure SSL mode';
$string['insecuresslmodedesc'] = 'Disable SSL certificate verification. This is not recommended but might be necessary if the feed is served using an invalid or untrusted certificate.';
$string['itemstoshow'] = 'Items to show';
$string['itemstoshowdescription'] = 'Between 1 and 20';
$string['showfeeditemsinfull'] = 'Show feed items in full';
$string['showfeeditemsinfulldesc'] = 'Whether to show a summary of the feed items or show the full text for each one.';
$string['invalidurl'] = 'That URL is invalid. You can only view feeds for http and https URLs.';
$string['invalidfeed1'] = 'No valid feed detected at that URL.';
$string['lastupdatedon'] = 'Last updated on %s';
$string['publishedon'] = 'Published on %s';
$string['defaulttitledescription'] = 'If you leave this blank, the title of the feed will be used.';
$string['reenterpassword'] = 'Because you have changed the URL of the feed, please re-enter (or delete) the password.';
