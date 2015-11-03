<?php
/**
 *
 * @package    mahara
 * @subpackage api
 * @author     Alan McNatty <alan@catalyst.net.nz>, Catalyst IT Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('JSON', 1);
define('NOSESSKEY', 1);

$json = array();

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
safe_require('artefact', 'blog');
require_once('lib.php');

if (!get_config('allowmobileuploads')) {
    mobile_api_json_reply(array('fail' => get_string('mobileuploadnotenabled', 'auth')));
}

$token = trim(param_variable('token', ''));

if ($token == '') {
    mobile_api_json_reply(array('fail' => get_string('mobileuploadtokennotset', 'auth')));
}

$username = trim(param_variable('username', ''));

if ($username == '') {
    mobile_api_json_reply(array('fail' => get_string('mobileuploadusernamenotset', 'auth')));
}

// Starting the creation of our artefact (file) object
$data = new StdClass;
$USER = new User();

try {
    $USER->find_by_mobileuploadtoken($token, $username);
}
catch (AuthUnknownUserException $e) {
    mobile_api_json_reply(array('fail' => get_string('mobileuploadtokennotfound', 'auth')));
}

$data->owner = $USER->get('id'); // id of owner

$folder = trim(param_variable('foldername', ''));

if ($folder) {
    $artefact = ArtefactTypeFolder::get_folder_by_name($folder, null, $data->owner);
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

// Check for Journal ID to add a post to
$blog = param_integer('blog', 0);
$blogpost = param_integer('blogpost', 0);
$draft = param_alpha('draft', '');
$allowcomments = param_alpha('allowcomments', '');

// Check for artefact attributes title, description (or entry), tags, etc
$title = param_variable('title', '');
$description = param_variable('description', '');
$tags = explode(",", param_variable('tags', ''));

// -- Now check for files to upload --
$artefact_id = '';  // our resulting artefact id on creation

if ($_FILES) {
    $file_title = $title;
    if ($blog || !$title) {  // set the filename to be the title of the artefact
        $file_title = basename($_FILES['userfile']['name']);
    }

    try {
        $data->title = ArtefactTypeFileBase::get_new_file_title($file_title, $data->parent, $data->owner);
        if (!$blog) { // only set a description if it's an artefact upload
          $data->description = $description;
        }
        $data->tags = $tags;
        $artefact_id = ArtefactTypeFile::save_uploaded_file('userfile', $data);
    }
    catch (QuotaExceededException $e) {
        mobile_api_json_reply(array('fail' => get_string('uploadexceedsquota', 'artefact.file')));
    }
    catch (UploadException $e) {
        mobile_api_json_reply(array('fail' => get_string('uploadoffilefailed', 'artefact.file',  $file_title)));
    }
}

// -- Next create a blog entry --
$postobj = '';  // our resulting blog post object on creation

if ($blog) {
    if (!get_record('artefact', 'id', $blog, 'owner', $USER->get('id'))) {
        // Blog security is also checked closer to when blogs are added, this
        // check ensures that malicious users do not even see the screen for
        // adding a post to a blog that is not theirs
        mobile_api_json_reply(array('fail' => get_string('youarenottheownerofthisblog', 'artefact.blog')));
    }
    $postobj = new ArtefactTypeBlogPost(null, null);
    $postobj->set('title', $title);
    $postobj->set('description', $description);
    $postobj->set('tags', $tags);
    $postobj->set('published', !$draft);
    $postobj->set('allowcomments', (int) $allowcomments);
    $postobj->set('parent', $blog);
    $postobj->set('owner', $USER->id);
    $postobj->commit();
    $blogpost = $postobj->get('id');
}
else if ($blogpost) {
    $postobj = new ArtefactTypeBlogPost($blogpost);
    $postobj->check_permission();
    if ($postobj->get('locked')) {
        mobile_api_json_reply(array('fail' => get_string('submittedforassessment', 'view')));
    }
}
if ($blogpost) {
    $json['id'] = $blogpost;
}

// Check to see if we're creating a journal entry

// -- Finally attach the file to the blog post once uploaded and --

if ($artefact_id && $postobj) {
    // If we created or matched a blog post and created an artefact
    // attach the artefact to the blog.
    $postobj->attach($artefact_id);
}

// Here we need to create a new hash - update our own store of it and return it to the handset
mobile_api_json_reply(array('success' => $USER->refresh_mobileuploadtoken($token), 'sync' => $json));
