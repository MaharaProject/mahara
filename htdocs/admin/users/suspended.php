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
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/suspendedusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('suspendeduserstitle', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'suspendedusers');
require_once('pieforms/pieform.php');

$type = param_alpha('type', 'suspended') == 'expired' ? 'expired' : 'suspended';
$enc_type = json_encode($type);

$typeform = pieform(
    array(
        'name' => 'usertype',
        'elements' => array(
            'type' => array(
                'type' => 'select',
                'title' => get_string('show') . ':',
                'options' => array(
                    'suspended' => get_string('suspendedusers', 'admin'),
                    'expired'   => get_string('expiredusers', 'admin'),
                ),
                'defaultvalue' => $type,
            ),
            'typesubmit' => array(
                'type' => 'submit',
                'class' => 'js-hidden',
                'value' => get_string('change'),
            ),
        ),
    )
);

$smarty = smarty(array('tablerenderer'));
$smarty->assign('typeform', $typeform);

$smarty->assign('INLINEJAVASCRIPT', <<<EOF
var suspendedlist = new TableRenderer(
    'suspendedlist',
    'suspended.json.php',
    [
        'name',
        function (r) {
            return TD(null, r.institutions ? map(partial(DIV, null), r.institutions) : null);
        },
        function (r) {
            return TD(null, r.institutions ? map(partial(DIV, {'class':'dont-collapse'}), r.institutionids) : r.studentid);
        },
        'cusrname',
        'reason',
        'expiry',
        function (rowdata) { return TD(null, INPUT({'type': 'checkbox', 'name': 'usr_' + rowdata.id})); }
    ]
);
suspendedlist.type = {$enc_type};
suspendedlist.statevars.push('type');
suspendedlist.updateOnLoad();

addLoadEvent(function() {
    connect('usertype_type', 'onchange', function() {
        if (suspendedlist.type != $('usertype_type').value) {
            suspendedlist.offset = 0;
            suspendedlist.type = $('usertype_type').value;
            suspendedlist.doupdate();
        }
    });
});

EOF
);

$form = new Pieform(array(
    'name'      => 'buttons',
    'renderer'  => 'oneline',
    'autofocus' => false,
    'elements' => array(
        'unsuspend' => array(
            'type' => 'submit',
            'name' => 'unsuspend',
            'value' => get_string('unsuspendusers', 'admin')
        ),
        'delete' => array(
            'type'    => 'submit',
            'confirm' => get_string('confirmdeleteusers', 'admin'),
            'name'    => 'delete',
            'value'   => get_string('deleteusers', 'admin')
        ),
        'unexpire' => array(
            'type' => 'submit',
            'name' => 'unexpire',
            'value' => get_string('unexpireusers', 'admin')
        ),
    )
));
$smarty->assign('buttonformopen', $form->get_form_tag());
$smarty->assign('buttonform', $form->build(false));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/suspended.tpl');

function buttons_submit_unsuspend(Pieform $form, $values) {
    global $SESSION;

    $ids = get_user_ids_from_post();
    foreach ($ids as $userid) {
        unsuspend_user($userid);
    }

    $SESSION->add_ok_msg(get_string('usersunsuspendedsuccessfully', 'admin'));
    redirect('/admin/users/suspended.php');
}

function buttons_submit_unexpire(Pieform $form, $values) {
    global $SESSION;

    $ids = get_user_ids_from_post();
    foreach ($ids as $userid) {
        unexpire_user($userid);
    }

    $SESSION->add_ok_msg(get_string('usersreactivated', 'admin'));
    redirect('/admin/users/suspended.php?type=expired');
}

function buttons_submit_delete(Pieform $form, $values) {
    global $SESSION;

    $ids = get_user_ids_from_post();
    foreach ($ids as $userid) {
        delete_user($userid);
    }

    $SESSION->add_ok_msg(get_string('usersdeletedsuccessfully', 'admin'));
    redirect('/admin/users/suspended.php');
}

function get_user_ids_from_post() {
    $ids = array();
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'usr_') {
            $ids[] = intval(substr($key, 4));
        }
    }

    if (!$ids) {
        global $SESSION;
        $SESSION->add_info_msg(get_string('nousersselected', 'admin'));
        redirect('/admin/users/suspended.php');
    }

    return $ids;
}
