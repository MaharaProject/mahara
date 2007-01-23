<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

//safe_require('artefact', 'file');

$viewid   = param_integer('view');
$message  = param_variable('message', '');
$public   = (int) param_boolean('public', false);
$filename = param_variable('filename');

// Upload the file into the view owner's my files area.  This file
// will add to the view owner's quota, but saving the file will not
// fail if the quota is exceeded.

// Community name, view title, feedback number?
$prefix = get_config('dbprefix');
$viewdata = get_record_sql('
     SELECT
         v.title, v.owner, c.name
     FROM ' . $prefix . 'view v
     INNER JOIN ' . $prefix . 'community c ON v.submittedto = c.id
     WHERE v.id = ' . $viewid, '');

$page = get_config('wwwroot') . 'view/view.php?view=' . $viewid;

require_once('uploadmanager.php');
$um = new upload_manager('attachment');
if ($error = $um->preprocess_file()) {
    // Should do something more sensible here, like display the error
    // next to the feedback submit button.
    log_info($error, false, false);
    redirect($page);
}
$size = $um->file['size'];

safe_require('artefact', 'file');
$folderid = ArtefactTypeFolder::get_folder_id(get_string('feedbackattachdirname'),
                                              get_string('feedbackattachdirdesc'),
                                              null, $viewdata->owner);

// Create a new file object
$data = (object) array('owner' => $viewdata->owner,
                       'parent' => $folderid,
                       'size' => $size,
                       'title' => $filename,
                       'description' => get_string('feedbackonviewbytutorofcommunity', 'mahara', 
                                                   $viewdata->title, display_name($USER), $viewdata->name));
$f = ArtefactTypeFile::new_file($um->file['tmp_name'], $data);
$f->commit();
$fileid = $f->get('id');

if ($error = $um->save_file(ArtefactTypeFile::get_file_directory($fileid) , $fileid)) {
    $f->delete();
    log_info($error, false, false);
    redirect($page);
}
else {
    //$USER->quota_add($size);
    // Just add to the usr table directly.
    $ownerquotaused = get_field('usr', 'quotaused', 'id', $viewdata->owner);
    set_field('usr', 'quotaused', $ownerquotaused + $size, 'id', $viewdata->owner);
}

global $USER;
$tutor = $USER->get('id');
$data = (object) array('view' => $viewid,
                       'author' => $tutor,
                       'message' => $message,
                       'attachment' => $fileid,
                       'ctime' => db_format_timestamp(time()),
                       'public' => $public);

if (!insert_record('view_feedback', $data)) {
    $f->delete();
}

redirect(get_config('wwwroot') . 'view/view.php?view=' . $viewid);

?>
