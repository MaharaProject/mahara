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

$protocol = strtoupper($_SERVER['SERVER_PROTOCOL']);
if ($protocol != 'HTTP/1.1') {
    $protocol = 'HTTP/1.0';
}

if (!get_config('allowmobileuploads')) {
    header($protocol.' 500 Mobile uploads disabled');
    exit;
}

$token = '';
try {
    $token = param_variable('token');
    $token = trim($token);
}
catch (ParameterException $e) { }

if ($token == '') {
    header($protocol.' 500 Auth token cannot be blank');
    exit;
}

$username = '';
try {
    $username = param_variable('username');
}
catch (ParameterException $e) { }

if ($username == '') {
    header($protocol.' 500 Username cannot be blank');
    exit;
}

$data = new StdClass;
$USER = new User();

try {
    $USER->find_by_mobileuploadtoken($token, $username);
}
catch (AuthUnknownUserException $e) {
    header($protocol.' 500 Invalid user token');
    exit;
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
    header($protocol.' 500 Quota exceeded');
    exit;
}
catch (UploadException $e) {
    header($protocol.' 500 Failed to save file');
    exit;
}

// Here we need to create a new hash - update our own store of it and return it too the handset
echo $USER->refresh_mobileuploadtoken();
