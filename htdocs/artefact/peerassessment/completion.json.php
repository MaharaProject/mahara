<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-peerassessment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'peerassessment');
safe_require('blocktype', 'signoff');

$blockid = param_integer('block', null);
$viewid = param_integer('view', null);
$signoff = param_integer('signoff', null);
$verify = param_integer('verify', null);

if (empty($viewid) && !empty($blockid)) {
    // try to find view from blockid
    $bi = new BlockInstance($blockid);
    $viewid = $bi->get_view()->get('id');
}

// Does the view have a signoff block
if (!$block = get_field('block_instance', 'id', 'view', $viewid, 'blocktype', 'signoff')) {
    json_reply('local', get_string('wrongblocktype', 'view'));
}
else {
    $bi = new BlockInstance($block);
}

$view = new View($viewid);
$configdata = $bi->get('configdata');
$showsignoff = !empty($configdata['signoff']) ? true : false;
$showverify = !empty($configdata['verify']) ? true : false;
$signable = ArtefactTypePeerassessment::is_signable($view);
$verifiable = ArtefactTypePeerassessment::is_verifiable($view);

$data = new stdClass();
if ($showsignoff && $signable && $signoff !== null) {
    $currentstate = ArtefactTypePeerassessment::is_signed_off($view);
    $newstate = 1 - (int)$currentstate;
    execute_sql("UPDATE {view_signoff_verify} SET signoff = ?, signofftime = ? WHERE view = ?", array($newstate, db_format_timestamp(time()), $viewid));
    if ($currentstate == 1) {
        // We are removing sign-off so we should also remove verify as well
        // We send a notification to the manager that did the verification to alert them it has been removed
        $sendto = get_field('view_signoff_verify', 'verifier', 'view', $viewid);
        $url = $view->get_url(false);
        if (!empty($sendto)) {
            // Notify author
            $title = $view->get('title');
            $title = hsc($title);
            $data = (object) array(
                'subject'   => false,
                'message'   => false,
                'strings'   => (object) array(
                    'subject' => (object) array(
                        'key'     => 'removedverifynotificationsubject',
                        'section' => 'blocktype.peerassessment/signoff',
                        'args'    => array($title),
                    ),
                    'message' => (object) array(
                        'key'     => 'removedverifynotification',
                        'section' => 'blocktype.peerassessment/signoff',
                        'args'    => array($title),
                    ),
                    'urltext' => (object) array(
                        'key'     => 'view',
                    ),
                ),
                'users'     => array($sendto),
                'url'       => $url,
            );
            activity_occurred('maharamessage', $data);
        }

        execute_sql("UPDATE {view_signoff_verify} SET verified = ?, verifier = NULL, verifiedtime = NULL WHERE view = ?", array(0, $viewid));

        $data->verify_change = 1;
    }
    $data->signoff_newstate = $newstate;
}
else if ($showverify && $verifiable && !empty($verify)) {
    $currentstate = ArtefactTypePeerassessment::is_verified($view);
    $newstate = 1;
    if ((int)$currentstate != $newstate) {
        execute_sql("UPDATE {view_signoff_verify} SET verified = ?, verifier = ?, verifiedtime = ? WHERE view = ?", array($newstate, $USER->get('id'), db_format_timestamp(time()), $viewid));
    }
    $data->verify_newstate = $newstate;
}
else {
    // No rights to do what is requested
    json_reply('local', get_string('wrongsignoffviewrequest', 'blocktype.peerassessment/signoff'));
}
$message = $verify ? get_string('verifyviewupdated', 'blocktype.peerassessment/signoff') : get_string('signoffviewupdated', 'blocktype.peerassessment/signoff');
json_reply(false, array('message' => $message,
                        'data' => $data));
