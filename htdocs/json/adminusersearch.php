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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

//
// NOTE:
// This script is used by the userlist element to retrieve data on all users, 
// for the 'potential staff' and 'potential admins' boxes. The general plan is 
// that after 0.9, the script admin/users/search.json.php is used instead of 
// this one, as per richardm's institutionaladmin branch. At which point, this 
// script will probably be removed.
//
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

if (!$USER->get('admin')) {
    throw new AccessDeniedException();
}

require('searchlib.php');
safe_require('search', 'internal');

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter','Missing parameter \'query\'');
}

$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);
$allfields = param_boolean('allfields');

if ($query) {
    $query = array(
        array(
            'type' => 'contains',
            'field' => 'firstname',
            'string' => $query,
        ),
        array(
            'type' => 'contains',
            'field' => 'lastname',
            'string' => $query,
        ),
        array(
            'type' => 'contains',
            'field' => 'username',
            'string' => $query,
        ),
    );
}

$data = call_static_method(generate_class_name('search', 'internal'), 'admin_search_user', 
            $query, null, $offset, $limit, 'username', 'asc');

if ($data['data']) {
    foreach($data['data'] as &$row) {
        $row['name'] = display_name($row);
    }
}

json_headers();
$data['error'] = false;
$data['message'] = false;
echo json_encode($data);

?>
