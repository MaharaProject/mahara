<?php
/**
 *
 * @package    mahara
 * @subpackage notification-email
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['name'] = 'Email';
$string['emailsubject'] = '%s';
$string['emailheader'] = 'You have been sent a notification from %s. Message follows:';
$string['emailfooter'] = 'This is an auto-generated notification from %s. To update your notification preferences, visit %s';
$string['referurl'] = 'See %s';
$string['unsubscribe'] = 'To unsubscribe go to %s';
$string['unsubscribetitle'] = 'Unsubscribe';
$string['unsubscribesuccess'] = 'You have successfully unsubscribed.';
$string['unsubscribefailed'] = 'You have failed to unsubscribe. You have either already unsubscribed or you need to sort this manually. Please log in and visit the related section of Mahara.';

// Watchlist specific strings
$string['unsubscribe_watchlist'] = 'To remove this from your watchlist go to %s';
$string['unsubscribe_watchlist_heading'] = 'Remove your watchlist notification for "%s"';
