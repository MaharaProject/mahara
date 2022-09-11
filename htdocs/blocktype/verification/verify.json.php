<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-verification
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('blocktype', 'verification');

$blockid = param_integer('blockid');
$block = new BlockInstance($blockid);

// Is the block correct type
if ($block->get('blocktype') != 'verification') {
    json_reply('local', get_string('wrongblocktype', 'view'));
}

$view = $block->get_view();
$viewid = $view->get('id');
// Is the block on a page we can see
if (!can_view_view($viewid)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}

$config = $block->get('configdata');
$config['verified'] = isset($config['verified']) ? 1 - $config['verified'] : 1;
if ($config['verified']) {
    $config['verifieddate'] = time();
    $config['verifierid'] = $USER->get('id');
    if (!empty($config['displayverifiername'])) {
        $verifiedon = get_string('verifiedonby', 'blocktype.verification', profile_url($USER->get('id')), display_name($USER->get('id')), format_date($config['verifieddate']));
    }
    else {
        $verifiedon = get_string('verifiedon', 'blocktype.verification', format_date($config['verifieddate']));
    }
    if (!empty($config['lockportfolio'])) {
        $view->get_collection()->lock_collection();
    }
    if (!empty($config['notification'])) {
        // Send notification to page owner
        $owner = $view->get('owner');
        require_once('activity.php');
        //Check if verifier name needs to be anonymized.
        if (empty($config['displayverifiername'])) {
            $verifiersubjectstring = 'verifymessagesubjectnoname';
            $verifiersubjectargs = array();
            $verifiermessagestring = 'verifymessagenoname';
            $verifiermessageargs = array(format_date($config['verifieddate']), html2text($config['text']));
        }
        else {
            $verifiersubjectstring = 'verifymessagesubject';
            $verifiersubjectargs = array(display_name($USER));
            $verifiermessagestring = 'verifymessage';
            $verifiermessageargs = array(display_name($USER), format_date($config['verifieddate']), html2text($config['text']));
        }

        $message = array(
            'users'   => array($owner),
            'subject' => '',
            'message' => '',
            'strings' => (object) array(
                'subject' => (object) array(
                    'key'     => $verifiersubjectstring,
                    'section' => 'blocktype.verification',
                    'args'    => $verifiersubjectargs,
                ),
                'message' => (object) array(
                    'key'     => $verifiermessagestring,
                    'section' => 'blocktype.verification',
                    'args'    => $verifiermessageargs,
                ),
            ),
            'url'     => get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $view->get_collection()->get('id'),
            'urltext' => $view->get_collection()->get('name'),
        );
        activity_occurred('maharamessage', $message);
    }
    handle_event('verifiedprogress', array(
        'id' => $blockid,
        'eventfor' => 'block',
        'block'  => $config,
        'parenttype' => 'collection',
        'parentid' => $view->get_collection()->get('id'),
    ));
}
else {
    unset($config['verifieddate']);
    unset($config['verifierid']);
    if ($undo = get_record_sql("SELECT * FROM {blocktype_verification_undo} WHERE block = ? LIMIT 1", array($block->get('id')))) {
        $goto = get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $view->get_collection()->get('id');
        $users = array($view->get('owner'), $undo->reporter);
        $message = (object) array(
            'users' => $users,
            'subject' => get_string('undonesubject', 'collection'),
                                    'message' => get_string('undonemessage', 'collection', display_name($USER), $block->get('title'), $view->get_collection()->get('name')),
                                    'url'     => get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $view->get_collection()->get('id'),
                                    'urltext' => $view->get_collection()->get('name'),
             );
        activity_occurred('maharamessage', $message);
        delete_records('blocktype_verification_undo', 'block', $block->get('id'));
    }

    $verifiedon = '';
    // check if is the last verified locking statement block
    if (PluginBlocktypeVerification::is_last_locking_block($block)) {
        $view->get_collection()->unlock_collection();
    }
}

$block->set('configdata', $config);
$block->commit();

$data = array('verified' => $config['verified'],
              'verifiedon' => $verifiedon);

json_reply(false, array('data' => $data));
