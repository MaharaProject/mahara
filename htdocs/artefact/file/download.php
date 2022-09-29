<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once('file.php');
require_once('embeddedimage.php');

$fileid = param_integer('file');
$groupid = param_integer('group', 0);
$viewid = param_integer('view', null);
$postid = param_integer('post', null);
$isembedded = param_integer('embedded', 0);
$size   = get_imagesize_parameters();

$options = array();
if (empty($isembedded)) {
    $options['forcedownload'] = true;
}

// Bail early if we can't find the file.
$file = artefact_instance_from_id($fileid);
if (!($file instanceof ArtefactTypeFile)) {
    throw new NotFoundException();
}

// Sanity check the file's group.
if ($groupid && $file->get('group') && $file->get('group') != $groupid) {
    throw new NotFoundException();
}

// Prepare the file for download.
$path = $file->get_path($size);
$title = $file->download_title();
$filetype = $file->get('filetype');
if ($contenttype = $file->override_content_type()) {
    $options['overridecontenttype'] = $contenttype;
}
$options['owner'] = $file->get('owner');

// If the file owner is the current user we don't need any more sanity checks.
if ($USER->is_logged_in()) {
    if ($file->get('owner') == $USER->get('id')) {
        AccessControl::log('TRUE: User is file owner. (' . __FILE__ . ')');
        serve_file($path, $title, $filetype, $options);
    }
}

$resourcetype = '';
$resourceid = null;
$resourcetypes = array_keys(EmbeddedImage::get_resourcetable_mapping());
// Let see if we can get a resource type from the URL
foreach ($resourcetypes as $param) {
    if ($param == 'institution') {
        $thisid = param_alphanum($param, null);
    }
    else {
        $thisid = param_integer($param, null);
    }
    if ($thisid) {
        $resourcetype = $param;
        $resourceid = $thisid;
        break;
    }
}

// if ($viewid && $file) {
    // $ancestors = $file->get_item_ancestors();
    // $artefactok = false;

    // Check if the artefact is embedded in the page description
    // if ($resourcetype == 'description' && $resourceid && $file instanceof ArtefactTypeImage) {
    //     $artefactok = EmbeddedImage::can_see_embedded_image($file, $resourcetype, $resourceid);
    // }

    // Check if the artefact is embedded in the page instructions
    // if ($resourcetype == 'instructions' && $resourceid && $file instanceof ArtefactTypeImage) {
    //     $artefactok = EmbeddedImage::can_see_embedded_image($file, $resourcetype, $resourceid);
    // }

    // if (!$artefactok && artefact_in_view($file, $viewid)) {
    //     $artefactok = true;
    // }
    // Check to see if the artefact has a parent that is allowed to be in this view.
    // For example, subdirectory of a folder artefact on a view.
    // if (!empty($ancestors) && !$artefactok) {
    //     foreach ($ancestors as $ancestor) {
    //         $pathitem = artefact_instance_from_id($ancestor);
    //         if (artefact_in_view($pathitem, $viewid)) {
    //             $artefactok = true;
    //             break;
    //         }
    //     }
    // }

    // If the view is a group view check that the $USER can view it
    // $author = $file->get('author');
    // $group = $file->get('group');
    // if (!empty($author) && !empty($group)) {
    //     if ($USER->can_view_artefact($file)) {
    //         $artefactok = true;
    //     }
    // }

    // If the artefact exists in a previous version of the view we can display it
    // if (!$artefactok && artefact_in_view_version($file, $viewid)) {
    //     $artefactok = true;
    // }

    // The user may be trying to download a file that's not in the page, but which has
    // been attached to a public comment on the page
    // 🚨 Doris double check this - covered in access control
    // if ($commentid = param_integer('comment', null)) {
    //     if (!record_exists('artefact_attachment', 'artefact', $commentid, 'attachment', $fileid)) {
    //         throw new AccessDeniedException();
    //     }
    //     safe_require('artefact', 'comment');
    //     $comment = new ArtefactTypeComment($commentid);
    //     if (!$comment->viewable_in($viewid)) {
    //         throw new AccessDeniedException();
    //     }
    // }
    // else if ($artefactok == false && $isembedded && $file instanceof ArtefactTypeImage) {
    //     // Check if the image is embedded in some text somewhere.
    //     if (!check_is_embedded_image_visible($file, null, array('comment'))) {
    //         throw new AccessDeniedException();
    //     }
    // }
    // else if ($artefactok == false) {
    //     throw new AccessDeniedException();
    // }

    // if (!can_view_view($viewid)) {
    //     throw new AccessDeniedException();
    // }

    // if (!($file instanceof ArtefactTypeFile)) {
    //     throw new NotFoundException();
    // }

    // We pass the initial checks. Let's send it through access control as well.
    $access_control = AccessControl::user($USER)
        ->set_file($file)
        ->set_resource($resourcetype, $resourceid)
        ->is_visible();
    if (!$access_control) {
        throw new AccessDeniedException();
    }
// }
// else {
//     // If the file is in the public directory, it's fine to serve
//     $fileispublic = $file->get('institution') == 'mahara';
//     $fileispublic = $fileispublic && (bool)get_field('artefact', 'id', 'id', $fileid, 'parent', ArtefactTypeFolder::admin_public_folder_id());

//     if (!$fileispublic) {
//         // If the file is in the logged in menu and the user is logged in then
//         // they can view it
//         $fileinloggedinmenu = $file->get('institution') == 'mahara';
//         // check if users are allowed to access files in subfolders
//         if (!get_config('sitefilesaccess')) {
//             $fileinloggedinmenu = $fileinloggedinmenu && $file->get('parent') == null;
//         }
//         $fileinloggedinmenu = $fileinloggedinmenu && $USER->is_logged_in();
//         $fileinloggedinmenu = $fileinloggedinmenu && record_exists('site_menu', 'file', $fileid, 'public', 0);

//         if (!$fileinloggedinmenu) {
//             // Alternatively, if you own the file or you are an admin, it should always work.
//             if (!$USER->can_view_artefact($file)) {

//                 $imagevisible = false;

//                 // Check for resume elements in pages
//                 $resumelements = array('coverletter','interest','personalgoal','academicgoal','careergoal','personalskill','academicskill','workskill','introduction');
//                 foreach ($resumelements as $element) {
//                     $resourceid = param_integer($element, null);
//                     if ($resourceid && $file instanceof ArtefactTypeImage) {
//                         $imagevisible = EmbeddedImage::can_see_embedded_image($file, $element, $resourceid);
//                     }
//                     if ($imagevisible) {
//                         break;
//                     }
//                 }

//                 // Check for artefacts sitting in visible forum posts
//                 if (!$imagevisible && $postid && $file instanceof ArtefactType) {
//                     safe_require('interaction', 'forum');
//                     $imagevisible = PluginInteractionForum::can_see_attached_file($file, $postid);
//                 }

//                 // if (!$imagevisible && $groupid) {
//                 //     // Check if group description is viewable
//                 //     require_once('view.php');
//                 //     $view = group_get_homepage_view($groupid);
//                 //     if (!can_view_view($view->get('id'))) {
//                 //         throw new AccessDeniedException();
//                 //     }
//                 //     $imagevisible = EmbeddedImage::can_see_embedded_image($file, 'group', $groupid);
//                 // }

//                 if (!$imagevisible && $isembedded && $file instanceof ArtefactTypeImage) {
//                     // Use AccessControl in this check.
//                     $imagevisible = check_is_embedded_image_visible($file);
//                 }
//                 else {
//                     if (!$isembedded) {
//                         AccessControl::log('File is not embedded');
//                     }
//                     if (!$file instanceof ArtefactTypeImage) {
//                         AccessControl::log('File is not an image');
//                     }
//                 }

//                 if (!$imagevisible) {
//                     throw new AccessDeniedException();
//                 }
//             }
//         }
//     }
// }

serve_file($path, $title, $file->get('filetype'), $options);

/**
 * Check if the image is embedded in an artefact of type:
 *     comment, annotation, annotationfeedback, blog, textbox, editnote, text, wallpost.
 * Please check first that the fileid is of type ArtefactTypeImage and that the download
 * is called with the embedded flag set.
 *
 * @param ArtefactTypeFile $file the file object to check.
 * @param array $includeresourcetypes an array of extra artefact types to include in the check.
 * @param array $excluderesourcetypes an array of artefact types to exclude from the check.
 * @return boolean TRUE the image is visible; FALSE the image is not visible.
 */
function check_is_embedded_image_visible($file, $includeresourcetypes = null, $excluderesourcetypes = null) {
    $isvisible = false;
    // Check for resource types a file may be embedded in.
    $resourcetypes = AccessControl::get_resource_types();
    if (!empty($includeresourcetypes)) {
        if (!is_array($includeresourcetypes)) {
            $includeresourcetypes = array($includeresourcetypes);
        }
        $resourcetypes = array_merge($resourcetypes, $includeresourcetypes);
    }
    if (!empty($excluderesourcetypes)) {
        if (!is_array($excluderesourcetypes)) {
            $excluderesourcetypes = array($excluderesourcetypes);
        }
        $resourcetypes = array_diff($resourcetypes, $excluderesourcetypes);
    }
    foreach ($resourcetypes as $resourcetype) {
        $resourceid = param_integer($resourcetype, null);
        if ($resourceid) {
            $isvisible = EmbeddedImage::can_see_embedded_image($file, $resourcetype, $resourceid);
        }
        if ($isvisible) {
            break;
        }
    }
    return $isvisible;
}
