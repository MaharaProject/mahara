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
 * @subpackage blocktype-myfriends
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('blocktype', 'myfriends');
require_once('user.php');

$offset = param_integer('offset');
$limit  = param_integer('limit', MAXFRIENDDISPLAY);
$bi = new BlockInstance(param_integer('block'));
if (!can_view_view($bi->get('view'))) {
    json_reply(true, get_string('accessdenied', 'error'));
}
$userid = $bi->get_view()->get('owner');

$friends = get_friends($userid, $limit, $offset);
PluginBlocktypeMyfriends::build_myfriends_html($friends, $userid, $bi);
unset($friends['data']);

json_reply(false, array('data' => $friends));
