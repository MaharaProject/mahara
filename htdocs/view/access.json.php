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

// NOTE: this JSON script is used by the 'viewacl' element. It could probably
// be moved elsewhere without harm if necessary (e.g. if the 'viewacl' element
// was used in more places
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('searchlib.php');

$type   = param_variable('type');
$query  = param_variable('query', '');
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

switch ($type) {
    case 'user':
        $data = search_user($query, $limit, $offset, array('exclude' => $USER->get('id')));
        break;
    case 'group':
        $data = search_group($query, $limit, $offset, '');
        $roles = get_records_array('grouptype_roles');
        $data['roles'] = array();
        foreach ($roles as $r) {
            $data['roles'][$r->grouptype][] = array('name' => $r->role, 'display' => get_string($r->role, 'grouptype.'.$r->grouptype));
        }
        break;
}

$data['error'] = false;
$data['message'] = '';
json_reply(false, $data);
