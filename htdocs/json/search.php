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

require(dirname(dirname(__FILE__)) . '/init.php');
require('searchlib.php');

safe_require('search', 'internal');

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter','Missing parameter \'query\'');
}

$type = param_variable('type', 'user');

$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);

switch($type) {
    case 'community':
        $data = search_community($query, $limit, $offset, true);
        foreach ($data['data'] as &$result) {
            $result->type = 'community';
        }
        log_debug($data);
        break;
    default:
        $data = search_user($query, $limit, $offset);
        if ($data['data']) {
            foreach ($data['data'] as &$result) {
                $result->name = display_name($result);
                $result->type = 'user';
                
                /* if (!$USER->get('admin')) {
                    unset($result->firstname);
                    unset($result->lastname);
                    unset($result->preferredname);
                    unset($result->email);
                    unset($result->institution);
                    unset($result->username);
                } */
            }
        }

        safe_require('artefact', 'internal');
        $data['userfields'] = array_keys(ArtefactTypeProfile::get_public_fields());

        break;
}

log_debug($data);

json_headers();
$data['error'] = false;
$data['message'] = '';
echo json_encode($data);

?>

