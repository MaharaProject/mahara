<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('MENUITEM', 'managegroups/groups');

require_once('pieforms/pieform.php');

$groupid = param_integer('id');
$group = get_record_sql("SELECT g.name FROM {group} g WHERE g.id = ? AND g.deleted = 0", array($groupid));

define('TITLE', get_string('deletespecifiedgroup', 'group', $group->name));

$form = pieform(array(
    'name' => 'admindeletegroup',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'admin/groups/groups.php',
        )
    ),
));

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('message', get_string('groupconfirmdelete', 'group'));
$smarty->assign('form', $form);
$smarty->display('group/delete.tpl');

function admindeletegroup_submit(Pieform $form, $values) {
    global $SESSION, $groupid;
    require_once(get_config('libroot') . 'group.php');
    group_delete($groupid);
    $SESSION->add_ok_msg(get_string('deletegroup', 'group'));
    redirect(get_config('wwwroot').'admin/groups/groups.php');
}
