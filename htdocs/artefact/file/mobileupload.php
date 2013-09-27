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
    $username = trim(param_variable('username'));
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

if (get_config('licensemetadata')) {
    // Set licensing information
    try {
        $license = license_coalesce(null,
            param_variable('license'), param_variable('license_other', null));
        $licensor = param_variable('licensor');
        $licensorurl = param_variable('licensorurl');
    }
    catch (ParameterException $e) { }
}

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
JSONResponse ( "success", $USER->refresh_mobileuploadtoken($token) );

function JSONResponse ( $key, $value ) {
  header('Content-Type: application/json');
  echo json_encode(array($key => $value));
  exit;
}
