<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-openbadgedisplayer
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('blocktype', 'openbadgedisplayer');

$host = param_variable('host', null);
$email = param_variable('email', null);
if (!isset($host) || !isset($email)) {
    json_reply('local', get_string('parameterexception', 'error'));
}

// Make sure the email belongs to the current user
$emails = get_column('artefact_internal_profile_email', 'email', 'owner', $USER->id, 'verified', 1);
if (!isset($emails) || !in_array($email, $emails)) {
    json_reply('local', get_string('accessdeniedbadge', 'error'));
}

$uid = PluginBlocktypeOpenbadgedisplayer::get_backpack_id($host, $email);
$hosttitle = get_string('title_' . $host, 'blocktype.openbadgedisplayer');
$badgegroupnames = isset($uid) ? PluginBlocktypeOpenbadgedisplayer::get_badgegroupnames($host, $uid) : null;
$nobadgegroup = get_string('nobadgegroupsin1', 'blocktype.openbadgedisplayer', $hosttitle, $email);
$nobackpack = get_string('nobackpackidin1', 'blocktype.openbadgedisplayer', $email, $hosttitle);
if ($host == 'badgr' && is_null($uid)) {
    $nobackpack = get_string('nobadgruid1', 'blocktype.openbadgedisplayer');
}

json_reply(false, array(
    'host' => $host,
    'hosttitle' => $hosttitle,
    'uid' => $uid,
    'badgegroups' => $badgegroupnames,
    'nobackpackmsg' => $nobackpack,
    'nobadgegroupsmsg' => $nobadgegroup,
));
