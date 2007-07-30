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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// NOTE: this JSON script is used by the 'viewacl' element. It could probably
// be moved elsewhere without harm if necessary (e.g. if the 'viewacl' element
// was used in more places
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require('searchlib.php');
safe_require('search', 'internal');

json_headers();

$type   = param_variable('type');
$query  = param_variable('query', '');
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

switch ($type) {
    case 'user':
        $data = get_user_results($query, $limit, $offset);
        break;
    case 'community':
        $data = get_community_results($query, $limit, $offset);
        break;
}

json_headers();
$data['error'] = false;
$data['message'] = '';
echo json_encode($data);


function get_user_results($query, $limit, $offset) {
    $data = search_user($query, $limit, $offset);
    return $data;
}

function get_community_results($query, $limit, $offset) {
    $data = search_community($query, $limit, $offset);
    if ($data['data']) {
        foreach ($data['data'] as &$result) {
        }
    }

    return $data;
}

?>
