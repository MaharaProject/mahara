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
define('NOCHECKPASSWORDCHANGE', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

if (param_integer('login_submitted', 0)) {
    redirect(get_config('wwwroot'));
}

if (param_integer('restore', 0)) {
    $id = $USER->restore_identity();
    redirect(get_config('wwwroot') . 'admin/users/edit.php?id=' . $id);
}

$id = param_integer('id');
$USER->change_identity_to($id);  // Permissions checking is done in here
redirect(get_config('wwwroot'));
