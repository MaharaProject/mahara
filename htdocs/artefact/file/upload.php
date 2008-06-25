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

$data = new StdClass;
if ($parentfolder) {
    $data->parent = (int) $parentfolder;
}
$data->title = $title;
$data->description = $description;
$data->tags = $tags;
if ($institution) {
    $data->institution = $institution;
} else if ($group) {
    require_once(get_config('docroot') . 'artefact/lib.php');
    require_once(get_config('docroot') . 'lib/group.php');
    if ($parentfolder && !$USER->can_edit_artefact(artefact_instance_from_id($parentfolder))) {
        json_reply('local', get_string('cannoteditfolder', 'artefact.file'));
    } else if (!$parentfolder && !user_can_access_group($group)) {
        json_reply('local', get_string('usernotingroup', 'mahara'));
    }
    $data->group = $group;
    $data->rolepermissions = json_decode(param_variable('permissions'));
} else {
    $data->owner = $USER->get('id');
}
$data->container = 0;
$data->locked = 0;

$result = new StdClass;
$result->uploadnumber = $uploadnumber;

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
if (!isset($result->error)) {
    $errmsg = ArtefactTypeFile::save_uploaded_file('userfile', $data);
    if (!$errmsg) {
        $result->error = false;
        if ($parentfoldername) {
            $result->message = get_string('uploadoffiletofoldercomplete', 'artefact.file', 
                                          $title, $parentfoldername);
        }
        else {
            $result->message = get_string('uploadoffilecomplete', 'artefact.file', $title);
        }
    }
    else {
        $result->error = 'local';
        if ($parentfoldername) {
            $result->message = get_string('uploadoffiletofolderfailed', 'artefact.file', 
                                          $title, $parentfoldername);
        }
        else {
            $result->message = get_string('uploadoffilefailed', 'artefact.file',  $title);
        }
        $result->message .= ': ' . $errmsg;
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

?>
