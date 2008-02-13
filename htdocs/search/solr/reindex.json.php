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

try {
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
        case 'view':
            PluginSearchSolr::rebuild_views();
            PluginSearchSolr::commit();
            PluginSearchSolr::optimize();
            break;
        default:
            PluginSearchSolr::rebuild_all();
            break;
    }
}
catch (RemoteServerException $e) {
    json_reply('local', 'Unable to perform re-index. Please check the ErrorLog for more information');
}


json_headers();
$data['error'] = false;
$data['message'] = false;
echo json_encode($data);

?>
