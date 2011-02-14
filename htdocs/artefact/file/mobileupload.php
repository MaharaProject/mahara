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

if (!get_config('allowmobileuploads')) {
    JSONResponse('fail', 'Mobile uploads disabled');
}

$token = '';
try {
    $token = param_variable('token');
    $token = trim($token);
}
catch (ParameterException $e) { }

if ($token == '') {
    JSONResponse('fail', 'Auth token cannot be blank');
}

$username = '';
try {
    $username = param_variable('username');
}
catch (ParameterException $e) { }

if ($username == '') {
    JSONResponse('fail', 'Username cannot be blank');
}

$data = new StdClass;
$USER = new User();

try {
    $USER->find_by_mobileuploadtoken($token, $username);
}
catch (AuthUnknownUserException $e) {
    JSONResponse('fail', 'Invalid user token');
}

$data->owner = $USER->get('id'); // id of owner

$folder = '';
try {
    $folder = param_variable('foldername');
    $folder = trim($folder);

    if ($folder) {
        // TODO: create if doesn't exist - note assumes it is a base folder (hence null parent)
        $artefact = ArtefactTypeFolder::get_folder_by_name($folder, null, $data->owner);  // id of folder you're putting the file into
        if ($artefact) {
            $data->parent = $artefact->id;
            if ($data->parent == 0) {
                $data->parent = null;
            }
        }
        else {
            $fd = (object) array(
                'owner' => $data->owner,
                'title' => $folder,
                'parent' => null,
            );
            $f = new ArtefactTypeFolder(0, $fd);
            $f->commit();
            $data->parent = $f->get('id');
        }
    }
    else {
        $data->parent = null;
    }
}
catch (ParameterException $e) {
    $data->parent = null;
}

// Set title
$title = '';
try {
    $title = param_variable('title');
}
catch (ParameterException $e) {
    // Default ot the name given with the post
    $title = $_FILES['userfile']['name'];
}
$title = $title ? basename($title) : get_string('file', 'artefact.file');
$data->title = ArtefactTypeFileBase::get_new_file_title($title, $data->parent, $data->owner);

// Set description
try {
    $data->description = param_variable('description');
}
catch (ParameterException $e) { }

// Set tags
try {
    $data->tags = explode(" ", param_variable('tags'));
}
catch (ParameterException $e) { }

try {
    $newid = ArtefactTypeFile::save_uploaded_file('userfile', $data);
}
catch (QuotaExceededException $e) {
    JSONResponse('fail', 'Quota exceeded');
}
catch (UploadException $e) {
    JSONResponse('fail', 'Failed to save file');
}

// Here we need to create a new hash - update our own store of it and return it too the handset
JSONResponse ( "success", $USER->refresh_mobileuploadtoken() );

function JSONResponse ( $key, $value ) {
  header('Content-Type: application/json');
  echo json_encode(array($key => $value));
  exit;
}
