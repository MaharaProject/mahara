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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)).'/init.php');
require('searchlib.php');

safe_require('search', 'internal', 'lib.php', 'require_once');

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter', get_string('missingparameter') . ' \'query\'');
}

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$data = search_user($query,$limit,$offset);
if (!empty($data['results'])) {
    foreach ($data['results'] as &$result) {
        $result['displayname'] = display_name($result);
        unset($result['username']);
        unset($result['preferredname']);
        unset($result['firstname']);
        unset($result['lastname']);
        unset($result['email']);
    }
}
$data['data'] = $data['results'];
unset($data['results']);

json_headers();
print json_encode($data);

?>
