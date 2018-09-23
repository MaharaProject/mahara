<?php
/**
 *
 * @package    mahara
 * @subpackage module-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['sendmessageto']        = 'Send message';
$string['composemessage']       = 'Compose';
$string['composemessagedesc']   = 'Create a new message';
$string['titlesubject']         = 'Subject';
$string['titlerecipient']       = 'Recipients';
$string['replysubjectprefix']   = 'Re:';
$string['labelrecipients']      = 'To: ';
$string['labelsubject']         = 'Subject:';
$string['deletednotifications1'] = 'Deleted %s notifications. Note that internal notifications can\'t be deleted from the "Sent" area.';
$string['notification']         = 'Notifications';
$string['cantsendemptysubject'] = 'Your subject is empty. Please enter a subject.';
$string['cantsendemptytext']    = 'Your message is empty. Please enter a message.';
$string['cantsendnorecipients'] = 'Please select at least one recipient.';
$string['removeduserfromlist']  = 'A user that can\'t receive messages from you has been removed from the list of recipients.';
$string['deleteduser']           = 'deleted user(s)';
$string['fromuser']             = 'From';
$string['touser']               = 'To';

// Notification Inbox URL Text
$string['reply']                = 'Reply';
$string['replyall']             = 'Reply all';
$string['linkindicator']        = '»';

$string['labeloutbox1']          = 'Sent';
$string['outboxdesc']         = 'Messages sent to other users';
$string['labelinbox']           = 'Inbox';
$string['inboxdesc1']          = 'Messages received from the system and other users';

$string['nothingtorender']      = '';
$string['replybuttonplaceholder'] = '...';

$string['selectallread'] = 'All unread notifications';
$string['selectalldelete'] = 'All notifications for deletion';
$string['clickformore'] = '(Press \'Enter\' to display more information)';

$string['labelsearch']          = 'Search';
$string['labelall']             = 'All';
$string['labelmessage']         = 'Message';

$string['multirecipientnotificationnotenabled'] = 'The module "multirecipientnotifications" needs to be installed and be active. If you are upgrading Mahara from a version older than 16.10.0, please upgrade to that point first and make sure the module is installed and active by visiting "Administration menu" → "Extensions" → "Plugin administration".';
