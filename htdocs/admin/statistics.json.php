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
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'registration.php');

$limit  = param_integer('limit', 10);
$offset = param_integer('offset');

$type = param_alpha('type', 'users');
$subpages = array('users', 'groups', 'views');
if (!in_array($type, $subpages)) {
    $type = 'users';
}

switch ($type) {
case 'groups':
    $data = group_stats_table($limit, $offset);
    break;
case 'views':
    $data = view_stats_table($limit, $offset);
    break;
case 'users':
default:
    $data = user_stats_table($limit, $offset);
}

json_reply(false, (object) array('message' => false, 'data' => $data));
