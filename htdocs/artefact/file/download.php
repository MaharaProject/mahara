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
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once('file.php');

$fileid = param_integer('file');
$viewid = param_integer('view', null);
$postid = param_integer('post', null);
$size   = get_imagesize_parameters();
$forcedl = param_boolean('download');

$options = array();
if ($forcedl) {
    $options['forcedownload'] = true;
}
else {
    $options['downloadurl'] = get_config('wwwroot')
        . substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], 'artefact/file/download.php'))
        . '&download=1';
}

if ($viewid && $fileid) {

    // The user may be trying to download a file that's not in the view, but which has
    // been attached to public feedback on the view
    if ($commentid = param_integer('comment', null)) {
        if (!record_exists('artefact_attachment', 'artefact', $commentid, 'attachment', $fileid)) {
            throw new AccessDeniedException('');
        }
        safe_require('artefact', 'comment');
        $comment = new ArtefactTypeComment($commentid);
        if (!$comment->viewable_in($viewid)) {
            throw new AccessDeniedException('');
        }
    }
    else if (!artefact_in_view($fileid, $viewid)) {
        throw new AccessDeniedException('');
    }

    if (!can_view_view($viewid)) {
        throw new AccessDeniedException('');
    }

    $file = artefact_instance_from_id($fileid);
    if (!($file instanceof ArtefactTypeFile)) {
        throw new NotFoundException();
    }
}
else {
    // We just have a file ID
    $file = artefact_instance_from_id($fileid);
    if (!($file instanceof ArtefactTypeFile)) {
        throw new NotFoundException();
    }

    // If the file is in the public directory, it's fine to serve
    $fileispublic = (bool)get_field('artefact_parent_cache', 'artefact', 'artefact', $fileid, 'parent', ArtefactTypeFolder::admin_public_folder_id());
    $fileispublic &= $file->get('institution') == 'mahara';

    if (!$fileispublic) {
        // If the file is in the logged in menu and the user is logged in then
        // they can view it
        $fileinloggedinmenu = $file->get('institution') == 'mahara';
        $fileinloggedinmenu &= $file->get('parent') == null;
        $fileinloggedinmenu &= record_exists('site_menu', 'file', $fileid, 'public', 0);
        $fileinloggedinmenu &= $USER->is_logged_in();

        if (!$fileinloggedinmenu) {
            // Alternatively, if you own the file or you are an admin, it should always work

            if (!$USER->can_view_artefact($file)) {

                // Check for images sitting in visible forum posts
                $visibleinpost = false;
                if ($postid && $file instanceof ArtefactTypeImage) {
                    safe_require('interaction', 'forum');
                    $visibleinpost = PluginInteractionForum::can_see_attached_file($file, $postid);
                }

                if (!$visibleinpost) {
                    throw new AccessDeniedException(get_string('accessdenied', 'error'));
                }
            }
        }
    }
}

$path  = $file->get_path($size);
$title = $file->download_title();
if ($contenttype = $file->override_content_type()) {
    $options['overridecontenttype'] = $contenttype;
}
$options['owner'] = $file->get('owner');
serve_file($path, $title, $file->get('filetype'), $options);
