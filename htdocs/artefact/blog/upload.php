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
 * @subpackage artefact-blog
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

// Upload a temporary file to attach to a blog post.
// The uploaded file will not be saved as an artefact until the blog post is saved.

$result = new StdClass;
$result->title          = param_variable('title');
$result->description    = param_variable('description', null);
$result->tags           = param_variable('tags', null);
$result->uploadnumber   = param_integer('uploadnumber'); // id of target iframe
$createid               = param_variable('createid');

// Ignore possible file name clashes; they should be dealt with in the
// javascript on the edit blog post page.

safe_require('artefact', 'blog');
$attach = ArtefactTypeBlogPost::save_attachment_temporary('userfile', session_id() . $createid,
                                                          $result->uploadnumber);

if (!$attach->error) {
    $result->error = false;
    $result->artefacttype = $attach->type;
    $result->oldextension = $attach->oldextension;
    $result->message = get_string('uploadoffilecomplete', 'artefact.file', $result->title);
}
else {
    $result->error = 'local';
    $result->message = get_string('uploadoffilefailed', 'artefact.file', $result->title)
        . ': ' . $attach->error;
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
