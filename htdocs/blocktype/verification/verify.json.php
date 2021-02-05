<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-verification
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
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
        // Lock the collection
        if ($view->get_collection()) {
            $collectionid = $view->get_collection()->get('id');
            execute_sql("UPDATE {collection} SET lock = 1 WHERE id = ?", array($collectionid));
        }
    }
    if (!empty($config['notification'])) {
        // send notification to page owner
        $owner = $view->get('owner');
        require_once('activity.php');
        activity_occurred('maharamessage', array(
            'users'   => array($owner),
            'subject' => '',
            'message' => '',
            'strings' => (object) array(
                'subject' => (object) array(
                    'key'     => 'verifymessagesubject',
                    'section' => 'blocktype.verification',
                    'args'    => array(display_name($USER)),
                ),
                'message' => (object) array(
                    'key'     => 'verifymessage',
                    'section' => 'blocktype.verification',
                    'args'    => array(display_name($USER), format_date($config['verifieddate']), html2text($config['text'])),
                ),
            ),
            'url'     => $view->get_url(true),
            'urltext' => $view->get('title'),
        ));
    }
}
else {
    unset($config['verifieddate']);
    unset($config['verifierid']);
    $verifiedon = '';
}

$block->set('configdata', $config);
$block->commit();

$data = array('verified' => $config['verified'],
              'verifiedon' => $verifiedon);

json_reply(false, array('data' => $data));
