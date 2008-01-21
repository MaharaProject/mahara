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

$view = get_record('view', 'id', $viewid, 'owner', $USER->get('id'));

if (!$view) {
    throw new AccessDeniedException(get_string('cantdeleteview', 'view'));
}

define('TITLE', get_string('deletespecifiedview', 'view', $view->title));

$form = pieform(array(
    'name' => 'deleteview',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'title' => get_string('deleteviewconfirm', 'view'),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'view/temp.php'
        )
    ),
));

$smarty = smarty();
$smarty->assign('heading', TITLE);
$smarty->assign('form', $form);
$smarty->display('view/delete.tpl');

function deleteview_submit(Pieform $form, $values) {
	global $SESSION, $viewid;
    $view = new View($viewid, null);
    $view->delete();
    handle_event('deleteview', $viewid);
    $SESSION->add_ok_msg(get_string('viewdeleted', 'view'));
    redirect('/view/temp.php');
}
?>
