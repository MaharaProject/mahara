<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-openbadgesdisplayer-badgr-token
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * This page lets users manage a badgr token with their account
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'settings/badgr');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'openbadgedisplayer');
define('APPS', 1);

require('./../../init.php');
safe_require('blocktype', 'openbadgedisplayer');
define('TITLE', get_string('connectedapps'));
define('SUBSECTIONHEADING', get_string('badgrtokentitle', 'blocktype.openbadgedisplayer'));

// Users shouldn't be able to access this page if openbadgedisplayer blocktype is not active.
if (!is_plugin_active('openbadgedisplayer','blocktype')) {
    throw new AccessDeniedException(get_string('featuredisabled', 'blocktype.openbadgedisplayer'));
}

$sources = PluginBlocktypeOpenbadgedisplayer::get_backpack_source();
if (empty($sources['badgr'])) {
    throw new AccessDeniedException(get_string('badgrsourcemissing1', 'blocktype.openbadgedisplayer'));
}
$token = get_field('usr_account_preference', 'value', 'field', 'badgr_token', 'usr', $USER->get('id'));

$elements = array();
if ($token) {
    $elements['tokenhtml'] = array(
        'type' => 'html',
        'value' => get_string('badgrtoken', 'blocktype.openbadgedisplayer', $token),
    );
    $elements['token'] = array(
        'type' => 'hidden',
        'value' => $token,
    );
    // delete button
    $elements['submit'] = array(
        'type'  => 'button',
        'usebuttontag' => true,
        'class' => 'btn-secondary btn-sm',
        'value' => '<span class="icon icon-trash icon-lg text-danger left" role="presentation" aria-hidden="true"></span>' . get_string('delete'),
        'elementtitle' => get_string('deletespecific', 'mahara', $token),
    );
}
else {
    $elements['badgrusername'] = array(
        'title' => get_string('badgrusername', 'blocktype.openbadgedisplayer'),
        'type'  => 'text',
        'defaultvalue' => $USER->get('email'),
        'rules' => array('required' => true),
    );
    $elements['badgrpassword'] = array(
        'title' => get_string('badgrpassword', 'blocktype.openbadgedisplayer'),
        'type'  => 'password',
        'rules' => array('required' => true),
    );
    $elements['submit'] = array(
        'type' => 'submit',
        'class' => 'btn-primary',
        'value' => get_string('save'),
    );
}

$form = array(
    'renderer' => 'div',
    'id' => 'maintable',
    'name' => 'maincontainer',
    'dieaftersubmit' => false,
    'successcallback' => 'badgr_token_submit',
    'elements' => $elements,
);

/**
 * handle the callback for actions on the user token panel
 *  - generate new token
 *  - delete token
 *
 * @param Pieform $form
 * @param array $values
 */
function badgr_token_submit(Pieform $form, $values) {
    global $USER, $SESSION, $sources;
    if (!empty($values['token'])) {
        // We are in delete mode
        delete_records('usr_account_preference', 'usr', $USER->get('id'), 'field', 'badgr_token', 'value', $values['token']);
        $SESSION->add_ok_msg(get_string('badgrtokendeleted', 'blocktype.openbadgedisplayer'));
    }
    else {
        // We are in add mode
        $res = mahara_http_request(
            array(
                CURLOPT_URL        => $sources['badgr'] . 'api-auth/token',
                CURLOPT_POST       => 1,
                CURLOPT_POSTFIELDS => 'username=' . $values['badgrusername'] . '&password=' . $values['badgrpassword'],
            )
        );
        $json = json_decode($res->data);
        if (isset($json->token)) {
            set_account_preference($USER->get('id'), 'badgr_token', $json->token);
            $SESSION->add_ok_msg(get_string('badgrtokenadded', 'blocktype.openbadgedisplayer'));
        }
        else {
            $SESSION->add_error_msg(get_string('badgrtokennotfound', 'blocktype.openbadgedisplayer'));
        }
    }
    redirect('/blocktype/openbadgedisplayer/badgrtoken.php');
}

// render the page
$form = pieform($form);

$smarty = smarty();
setpageicon($smarty, 'icon-globe');

$smarty->assign('form', $form);
$smarty->display('form.tpl');
