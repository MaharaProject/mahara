<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$action = param_variable('action');

if ($action == 'suspend') {
    $id = param_integer('id');
    $reason = param_variable('reason');

    try {
        suspend_user($id, $reason);
    }
    catch (MaharaException $e) {
        json_reply('local', get_string('suspendfailed', 'admin') . ': ' . $e->getMessage());
    }

    json_reply(false, get_string('usersuspended', 'admin'));
}

if ($action == 'search') {
    require('searchlib.php');

    $params = new StdClass;
    $params->query       = trim(param_variable('query', ''));
    $params->institution = param_alphanum('institution', null);
    $params->f           = param_alpha('f', null);
    $params->l           = param_alpha('l', null);

    $offset  = param_integer('offset', 0);
    $limit   = param_integer('limit', 10);
    $sortby  = param_alpha('sortby', 'firstname');
    $sortdir = param_alpha('sortdir', 'asc');

    json_headers();
    $data['data'] = build_admin_user_search_results($params, $offset, $limit, $sortby, $sortdir);
    $data['error'] = false;
    $data['message'] = null;
    echo json_encode($data);
    exit;
}

?>
