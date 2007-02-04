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
 * @subpackage artefact-file
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

//log_debug('myfiles.json.php');

$limit = param_integer('limit', null);
$offset = param_integer('offset', 0);
$folder = param_integer('folder', null);
$adminfiles = param_boolean('adminfiles', false);
$userid = $USER->get('id');

safe_require('artefact', 'file');
$filedata = ArtefactTypeFileBase::get_my_files_data($folder, $userid, $adminfiles);

$result = array(
    'count'       => count($filedata),
    'limit'       => $limit,
    'offset'      => $offset,
    'data'        => $filedata,
    'error'       => false,
    'message'     => false,
);

//log_debug($result);

json_headers();
print json_encode($result);

?>
