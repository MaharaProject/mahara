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

$data = new StdClass;
$USER = new User();
$USER->find_by_mobileuploadtoken(param_variable('token'));
$data->owner = $USER->get('id'); // id of owner

$folder = param_variable('foldername');
$artefact = ArtefactTypeFolder::get_folder_by_name($folder, null, $data->owner);  // id of folder you're putting the file into
if ( ! $artefact ) {
    	header($protocol." 500 Upload folder '$folder' does not exit");
	exit;	
}

$data->parent = $artefact->id;
if ( $data->parent == 0 ) $data->parent = null;

$originalname = $_FILES['userfile']['name'];
$originalname = $originalname ? basename($originalname) : get_string('file', 'artefact.file');

$data->title = ArtefactTypeFileBase::get_new_file_title($originalname, $data->parent, $data->owner);

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

echo 'foo';

