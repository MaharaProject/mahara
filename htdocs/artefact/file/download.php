<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    $file = artefact_instance_from_id($fileid);
    $ancestors = $file->get_item_ancestors();
    $artefactok = false;

    if (artefact_in_view($file, $viewid)) {
        $artefactok = true;
    }
    // Check to see if the artefact has a parent that is allowed to be in this view.
    // For example, subdirectory of a folder artefact on a view.
    if (!empty($ancestors) && !$artefactok) {
        foreach ($ancestors as $ancestor) {
            $pathitem = artefact_instance_from_id($ancestor);
            if (artefact_in_view($pathitem, $viewid)) {
                $artefactok = true;
                break;
            }
        }
    }

    // If the view is a group view check that the $USER can view it
    $author = $file->get('author');
    $group = $file->get('group');
    if (!empty($author) && !empty($group)) {
        if ($USER->can_view_artefact($file)) {
            $artefactok = true;
        }
    }

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
    else if ($artefactok == false) {
        throw new AccessDeniedException('');
    }

    if (!can_view_view($viewid)) {
        throw new AccessDeniedException('');
    }

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
    $fileispublic = $file->get('institution') == 'mahara';
    $fileispublic = $fileispublic && (bool)get_field('artefact', 'id', 'id', $fileid, 'parent', ArtefactTypeFolder::admin_public_folder_id());

    if (!$fileispublic) {
        // If the file is in the logged in menu and the user is logged in then
        // they can view it
        $fileinloggedinmenu = $file->get('institution') == 'mahara';
        // check if users are allowed to access files in subfolders
        if (!get_config('sitefilesaccess')) {
            $fileinloggedinmenu = $fileinloggedinmenu && $file->get('parent') == null;
        }
        $fileinloggedinmenu = $fileinloggedinmenu && $USER->is_logged_in();
        $fileinloggedinmenu = $fileinloggedinmenu && record_exists('site_menu', 'file', $fileid, 'public', 0);

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
