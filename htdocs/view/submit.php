<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Clare Lenihan <clare@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
$viewid = param_integer('id');
$groupid = param_integer('group');

$view = get_record('view', 'id', $viewid, 'owner', $USER->get('id'));
$group = get_record_sql(
    'SELECT g.id, g.name
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {group_member} t ON t.group = g.id 
       WHERE u.member = ?
       AND t.tutor = 1
       AND t.member != u.member
       AND g.id = ?
       GROUP BY g.id, g.name',
    array($USER->get('id'), $groupid)
);

if (!$view || !$group || $view->submittedto) {
    log_debug($view);
    log_debug($group);
    throw new AccessDeniedException(get_string('cantsubmitviewtogroup', 'view'));
}

define('TITLE', get_string('submitviewtogroup', 'view', $view->title, $group->name));

$form = pieform(array(
    'name' => 'submitview',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'title' => get_string('submitviewconfirm', 'view', $view->title, $group->name),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'view/'
        )
    ),
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->display('view/submit.tpl');

function submitview_submit(Pieform $form, $values) {
	global $SESSION, $viewid, $groupid;
    update_record('view', array('submittedto' => $groupid), array('id' => $viewid));
    $SESSION->add_ok_msg(get_string('viewsubmitted', 'view'));
    redirect('/view/');
}
?>
