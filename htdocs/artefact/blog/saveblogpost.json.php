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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
global $USER;

json_headers();

$title      = param_variable('title');
$draft      = param_boolean('draft');
$createid   = param_integer('createid');
$blog       = param_integer('blog');
$blogpost   = param_integer('blogpost');
$uploads    = json_decode(param_variable('uploads'));
$artefacts  = json_decode(param_variable('artefacts'));
$body       = param_variable('body');

log_debug($title    );
log_debug($draft    );
log_debug($createid );
log_debug($blog     );
log_debug($blogpost );
log_debug($uploads  );
log_debug($artefacts);
log_debug($body     );

$userid = $USER->get('id');

safe_require('artefact', 'blog');

$postobj = new ArtefactTypeBlogPost($blogpost, null);
$postobj->set('title', $title);
$postobj->set('description', $body);
$postobj->set('published', !$draft);
if (!$blogpost) {
    $postobj->set('parent', $blog);
    $postobj->set('owner', $userid);
}
else if ($postobj->get('owner') != $userid) {
    json_reply('local', get_string('notowner'));
}
$postobj->commit();
$blogpost = $postobj->get('id');



// Delete old attachments in the db that no longer appear in the list
// of artefacts

if (!$old = get_column('artefact_blog_blogpost_file', 'file', 'blogpost', $blogpost)) {
    $old = array();
}

foreach ($old as $o) {
    if (!in_array($o, $artefacts)) {
        log_debug('old not in new ' . $o);
        delete_records('artefact_blog_blogpost_file', 'blogpost', $blogpost, 'file', $o);
    }
}



// Add new artefacts as attachments

foreach ($artefacts as $a) {
    if (!in_array($a, $old)) {
        $data = new StdClass;
        $data->blogpost = $blogpost;
        $data->file = $a;
        insert_record('artefact_blog_blogpost_file', $data);
    }
}



// Add the newly uploaded files to myfiles and then to the blog post.

if (!empty($uploads)) {

    // Create the blogfiles folder if it doesn't exist yet.
    $blogfilesid = ArtefactTypeBlogPost::blogfiles_folder_id();
    if (!$blogfilesid) {
        json_reply('local', get_string('erroraccessingblogfilesfolder', 'artefact.blog'));
    }

    // Turn all the uploaded files into artefacts.
    foreach ($uploads as $upload) {
        if (!$postobj->save_attachment(session_id() . $createid, $upload->id,
                                       $upload->title, $upload->description)) {
            json_reply('local', get_string('errorsavingattachments', 'artefact.blog'));
        }
    }
}



json_reply(false, 'foo');

?>
