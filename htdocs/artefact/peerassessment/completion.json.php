<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-peerassessment
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
$signoffstatus = param_integer('signoffstatus', null);

$bi = null;
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
$owneraction = $view->get_progress_action('owner');
$manageraction = $view->get_progress_action('manager');


$signable = (bool)$owneraction->get_action();
$verifiable = (bool)$manageraction->get_action();

$data = new stdClass();

if ($signoffstatus) {
    $record = get_record_sql("SELECT * from {view_signoff_verify} where view = ?", array($viewid));
    if ($view->get("owner") && $record->signofftime) {
        $signedoffby = (int)$view->get('owner');
        $signedoffbymsg = get_string('signedoffbyondate', 'blocktype.peerassessment/signoff', display_name($signedoffby, null, true), format_date(strtotime($record->signofftime), 'strftimedate'));
        $msg = $signedoffbymsg;
        if (($verifiedby = $record->verifier) && $showverify) {
            $verifiedbymsg = get_string('verifiedbyondate', 'blocktype.peerassessment/signoff', display_name($verifiedby, null, true), format_date(strtotime($record->verifiedtime), 'strftimedate'));
            $msg = '<p>' . $signedoffbymsg . '</p>' . '<p>' . $verifiedbymsg . '</p>';
        }
        else if ($showverify && !ArtefactTypePeerassessment::is_verified($view)) {
            $msg = get_string('readyforverification', 'blocktype.peerassessment/signoff');
        }
        json_reply(false, array('data' => $msg));
    }
    else {
        //throw an error
        json_reply('local', get_string('wrongsignoffviewrequest', 'blocktype.peerassessment/signoff'));
    }
}

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
