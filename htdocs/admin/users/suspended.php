<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/suspendedusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('suspendedusers', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'suspendedusers');
require_once('pieforms/pieform.php');

$smarty = smarty(array('tablerenderer'));

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
            return TD(null, r.institutions ? map(partial(DIV, null), r.institutionids) : r.studentid);
        },
        'cusrname',
        'reason',
        function (rowdata) { return TD(null, INPUT({'type': 'checkbox', 'name': 'usr_' + rowdata.id})); }
    ]
);
suspendedlist.updateOnLoad();

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
        )
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
