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

// Check we have access.
$access_control = AccessControl::user($USER)
    ->set_file($file)
    ->set_resource($resourcetype, $resourceid)
    ->is_visible();
if (!$access_control) {
    throw new AccessDeniedException();
}

serve_file($path, $title, $file->get('filetype'), $options);
