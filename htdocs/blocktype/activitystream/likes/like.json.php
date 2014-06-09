<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-activitystream
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('blocktype', 'activitystream');

$activityid = param_integer('activityid');
$action = param_alpha('action');

$activity = get_record('activity', 'id', $activityid);

if ($action == 'like') {
    Likes::add($activity, $USER->get('id'));
    $newaction = 'unlike';
}
else {
    Likes::remove($activity, $USER->get('id'));
    $newaction = 'like';
}

$totallikes = Likes::total_likes($activity);
$newactiontext = Likes::action_label($newaction, $activity);

// Send a reply with the new action and likes total.
json_reply(false, array('newaction' => $newaction, 'newactiontext' => $newactiontext, 'totallikes' => $totallikes));
