<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('IFRAME', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');

$parentfolder     = param_variable('parentfolder', null);    // id of parent artefact
$parentfoldername = param_variable('parentfoldername', '');  // path to parent folder
$title            = param_variable('title');
$description      = param_variable('description', null);
$tags             = param_variable('tags', null);
$uploadnumber     = param_integer('uploadnumber'); // id of target iframe
$collideaction    = param_variable('collideaction', 'fail');
$institution      = param_alpha('institution', null);
$group            = param_integer('group', null);

$result = new StdClass;
$result->uploadnumber = $uploadnumber;

$data = new StdClass;
if ($parentfolder) {
    $data->parent = (int) $parentfolder;
}
$data->title = $title;
$data->description = $description;
$data->tags = $tags;
$data->owner = null;

require_once(get_config('docroot') . 'artefact/lib.php');
if ($parentfolder && !$USER->can_edit_artefact(artefact_instance_from_id($parentfolder))) {
    $result->error = 'local';
    $result->message = get_string('cannoteditfolder', 'artefact.file');
}
else {
    if ($institution) {
        $data->institution = $institution;
    } else if ($group) {
        require_once(get_config('libroot') . 'group.php');
        if (!$parentfolder) {
            $role = group_user_access($group);
            if (!$role) {
                $result->error = 'local';
                $result->message = get_string('usernotingroup', 'mahara');
            }
            // Use default grouptype artefact permissions to check if the
            // user can upload a file to the group's root directory
            $permissions = group_get_default_artefact_permissions($group);
            if (!$permissions[$role]->edit) {
                $result->error = 'local';
                $result->message = get_string('cannoteditfolder', 'artefact.file');
            }
        }
        $data->group = $group;
        $data->rolepermissions = (array) json_decode(param_variable('permissions'));
    } else {
        $data->owner = $USER->get('id');
    }
}
$data->container = 0;
$data->locked = 0;

if (!isset($result->error)) {
    if ($oldid = ArtefactTypeFileBase::file_exists($title, $data->owner, $parentfolder, $institution, $group)) {
        if ($collideaction == 'replace') {
            $obj = artefact_instance_from_id($oldid);
            $obj->delete();
        }
        else {
            // Hopefully this will happen rarely as filename clashes are
            // detected in the javascript.
            $result->error = 'fileexists';
            $result->message = get_string('fileexistsonserver', 'artefact.file', $title);
        }
    }
}
if (!isset($result->error)) {
    try {
        ArtefactTypeFile::save_uploaded_file('userfile', $data);
    }
    catch (QuotaExceededException $e) {
        prepare_upload_failed_message($result, $e, $parentfoldername, $title);
    }
    catch (UploadException $e) {
        prepare_upload_failed_message($result, $e, $parentfoldername, $title);
    }
}

if (!isset($result->error)) {
    // Upload succeeded
    $result->error = false;
    if ($parentfoldername) {
        $result->message = get_string('uploadoffiletofoldercomplete', 'artefact.file', 
                                      $title, $parentfoldername);
    }
    else {
        $result->message = get_string('uploadoffilecomplete', 'artefact.file', $title);
    }
}

$result->quota = $USER->get('quota');
$result->quotaused = $USER->get('quotaused');

$r = json_encode($result);
$frame = <<< EOF
<html><head><script>
<!--
function senduploadresult() {
  var x = {$r};
  parent.uploader.getresult(x);
}
// -->
</script></head>
<body onload="senduploadresult()"></body>
</html>
EOF;

header('Content-type: text/html');
echo $frame;

/**
 * Helper function used above to minimise code duplication
 */
function prepare_upload_failed_message(&$result, $exception, $parentfoldername, $title) {
    $result->error = 'local';
    if ($parentfoldername) {
        $result->message = get_string('uploadoffiletofolderfailed', 'artefact.file', 
                                      $title, $parentfoldername);
    }
    else {
        $result->message = get_string('uploadoffilefailed', 'artefact.file',  $title);
    }
    $result->message .= ': ' . $exception->getMessage();
}

?>
