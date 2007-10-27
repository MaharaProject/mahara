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
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once('artefact.php');
require_once('file.php');

$fileid = param_integer('file');
$viewid = param_integer('view', null);
$size   = get_imagesize_parameters();

if ($viewid && $fileid) {
    if (!artefact_in_view($fileid, $viewid)) {
        throw new UserException('Artefact ' . $fileid . ' is not in view ' . $viewid);
    }

    if (!can_view_view($viewid)) {
        throw new AccessDeniedException();
    }

    $file = artefact_instance_from_id($fileid);
    if (!($file instanceof ArtefactTypeFile)) {
        throw new NotFoundException();
    }
    $path = $file->get_path(array('size' => $size));
    $title = $file->download_title();
    serve_file($path, $title);
}

// We just have a file ID
$file = artefact_instance_from_id($fileid);
if (!($file instanceof ArtefactTypeFile)) {
    throw new NotFoundException();
}

// If the file is in the public directory, it's fine to serve
$fileispublic = $file->get('parent') == ArtefactTypeFolder::admin_public_folder_id();
$fileispublic &= $file->get('adminfiles');
$fileispublic &= record_exists('site_menu', 'file', $fileid, 'public', 1);

if (!$fileispublic) {
    // If the file is in the logged in menu and the user is logged in then
    // they can view it
    $fileinloggedinmenu = $file->get('adminfiles');
    $fileinloggedinmenu &= $file->get('parent') == null;
    $fileinloggedinmenu &= record_exists('site_menu', 'file', $fileid, 'public', 0);
    $fileinloggedinmenu &= $USER->is_logged_in();

    if (!$fileinloggedinmenu) {
        // Alternatively, if you own the file or you are an admin, it should always work
        $fileisavailable = $USER->get('admin') || $file->get('owner') == $USER->get('id');

        if (!$fileisavailable) {
            throw new AccessDeniedException();
        }
    }
}

$path  = $file->get_path(array('size' => $size));
$title = $file->download_title();
serve_file($path, $title);

?>
