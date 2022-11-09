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

$viewid = param_integer('view', null);
$signoff = param_integer('signoff', null);
$verify = param_integer('verify', null);
$signoffstatus = param_integer('signoffstatus', null);

// Does the view display a sign-off switch
$view_signoff = get_record('view_signoff_verify', 'view', $viewid);
if (!$view_signoff) {
    json_reply('local', 'Sign-off is not set for this page.');
}

$showsignoff = (bool)$view_signoff;
$view = new View($viewid);
$showverify = $view_signoff->show_verify;
$owneraction = $view->get_progress_action('owner');
$manageraction = $view->get_progress_action('manager');


$signable = (bool)$owneraction->get_action();
$verifiable = (bool)$manageraction->get_action();

$data = new stdClass();

if ($signoffstatus) {
    $record = get_record_sql("SELECT * from {view_signoff_verify} where view = ?", array($viewid));
    if ($view->get("owner") && $record->signofftime) {
        $signedoffby = (int)$view->get('owner');
        $signedoffbymsg = get_string('signedoffbyondate', 'view', display_name($signedoffby, null, true), format_date(strtotime($record->signofftime), 'strftimedate'));
        $msg = $signedoffbymsg;
        if (($verifiedby = $record->verifier) && $showverify) {
            $verifiedbymsg = get_string('verifiedbyondate', 'view', display_name($verifiedby, null, true), format_date(strtotime($record->verifiedtime), 'strftimedate'));
            $msg = '<p>' . $signedoffbymsg . '</p>' . '<p>' . $verifiedbymsg . '</p>';
        }
        else if ($showverify && !ArtefactTypePeerassessment::is_verified($view)) {
            $msg = get_string('readyforverification', 'view');
        }
        json_reply(false, array('data' => $msg));
    }
    else {
        //throw an error
        json_reply('local', get_string('wrongsignoffviewrequest', 'view'));
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
                        'section' => 'view',
                        'args'    => array($title),
                    ),
                    'message' => (object) array(
                        'key'     => 'removedverifynotification',
                        'section' => 'view',
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
    json_reply('local', get_string('wrongsignoffviewrequest', 'view'));
}
$message = $verify ? get_string('verifyviewupdated', 'view') : get_string('signoffviewupdated', 'view');
json_reply(false, array('message' => $message,
                        'data' => $data));
