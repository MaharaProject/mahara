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
 * @subpackage artefact-file
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
global $USER;

$parentfolder   = param_variable('parentfolder', null); // id of parent artefact
$title          = param_variable('title');
$description    = param_variable('description', null);
$uploadnumber   = param_integer('uploadnumber'); // id of target iframe
$collideaction  = param_variable('collideaction', 'fail');

$data = new StdClass;
if ($parentfolder) {
    $data->parent = (int) $parentfolder;
}
$data->title = $title;
$data->description = $description;
$data->owner = $USER->get('id');
$data->container = 0;
$data->locked = 0;

$result = new StdClass;
$result->uploadnumber = $uploadnumber;

if ($oldid = ArtefactTypeFileBase::exists_in_db($data->title, $data->owner, $parentfolder)) {
    if ($collideaction == 'replace') {
        require_once('artefact.php');
        $obj = artefact_instance_from_id($oldid);
        $obj->delete();
    }
    else {
        $result->error = 'fileexists';
    }
}
if (!isset($result->error)) {
    $f = new ArtefactTypeFile(0, $data);
    if ($f->save_uploaded_file('userfile')) {
        $result->error = false;
        $result->message = get_string('uploadoffilecomplete', 'artefact.file', 
                                      $f->get('title'));
    }
    else {
        $result->error = 'uploadfailed';
        $result->message = get_string('uploadoffilefailed', 'artefact.file', 
                                      $f->get('title'));
    }
}

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
