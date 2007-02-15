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
 * @subpackage search-solr
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$type = param_variable('type', 'all');
safe_require('search', 'solr');

switch ($type) {
    case 'user':
        PluginSearchSolr::rebuild_users();
        PluginSearchSolr::commit();
        PluginSearchSolr::optimize();
        break;
    case 'artefact':
        PluginSearchSolr::rebuild_artefacts();
        PluginSearchSolr::commit();
        PluginSearchSolr::optimize();
        break;
    default:
        PluginSearchSolr::rebuild_all();
        break;
}


json_headers();
$data['error'] = false;
$data['message'] = false;
echo json_encode($data);

?>
