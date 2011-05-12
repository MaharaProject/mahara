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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/preferences');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('deleteaccount', 'account'));
require_once('pieforms/pieform.php');

if (!$USER->can_delete_self()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$deleteform = pieform(array(
    'name' => 'account_delete',
    'plugintype' => 'core',
    'pluginname' => 'account',
    'elements'   => array(
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('delete'),
        ),
    ),
));

function account_delete_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    $userid = $USER->get('id');
    $USER->logout();
    delete_user($userid);
    $SESSION->add_ok_msg(get_string('accountdeleted', 'account'));
    redirect('/');
}

$smarty = smarty();
$smarty->assign('form', $deleteform);
$smarty->display('account/delete.tpl');
