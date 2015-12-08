<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('NOCHECKPASSWORDCHANGE', 1);
define('NOCHECKREQUIREDFIELDS', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('activity.php');

if (param_integer('login_submitted', 0)) {
    redirect(get_config('wwwroot'));
}

if (param_integer('restore', 0)) {
    $id = $USER->restore_identity();
    redirect(get_config('wwwroot') . 'admin/users/edit.php?id=' . $id);
}

/**
 * Notify user (if configured), do the masquerading and emit event. Called when
 * no (further) interaction with the admin is needed before the loginas.
 *
 * @param string $why The masquerading reason (if given) or null.
 */
function do_masquerade($why = null) {
    global $USER, $SESSION;
    $id = param_integer('id');
    $who = display_name($USER, $id);
    $when = format_date(time());
    if (get_config('masqueradingnotified')) {
        $msg = (object) array(
            'subject'   => get_string('masqueradenotificationsubject', 'admin'),
            'message'   => $why === null ?
                get_string('masqueradenotificationnoreason', 'admin',
                    $who, $when
                ) :
                get_string('masqueradenotificationreason', 'admin',
                    $who, $when, $why
                ),
            'users'     => array($id),
            'url'       => profile_url($USER, false),
            'urltext'   => $who,
        );
        activity_occurred('maharamessage', $msg);
        $SESSION->add_info_msg(get_string('masqueradenotificationdone', 'admin'));
    }
    $USER->change_identity_to($id);  // Permissions checking is done in here
    handle_event('loginas', array(
        'who' => $who,
        'when' => $when,
        'reason' => $why,
    ));
    $SESSION->set('nocheckrequiredfields', null);
    $SESSION->set('remoteavatar', null);
    redirect(get_config('wwwroot'));
}

if (!get_config('masqueradingreasonrequired')) {
    do_masquerade();
}

$form = array(
    'name'       => 'masqueradereason',
    'plugintype' => 'core',
    'pluginname' => 'core',
    'elements'   => array(
        'reason' => array(
            'type'         => 'textarea',
            'title'        => get_string('masqueradereason', 'admin'),
            'description'  => (get_config('masqueradingnotified') ?  get_string('masqueradenotifiedreasondescription', 'admin') : get_string('masqueradereasondescription', 'admin')),
            'defaultvalue' => '',
            'rows'         => 3,
            'cols'         => 30,
            'rules'        => array(
                'required'     => true,
            ),
            'help'         => true,
        ),
        'id' => array(
            'type'         => 'hidden',
            'value'        => param_integer('id'),
        ),
        'submit' => array(
            'type'         => 'submit',
            'value'        => get_string('masquerade', 'admin'),
            'class'        => 'btn-primary'
        ),
    ),
);
$form = pieform($form);

function masqueradereason_submit(Pieform $form, $values) {
    do_masquerade($values['reason']);
}

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->display('admin/users/changeuser.tpl');
