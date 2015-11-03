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
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
safe_require('artefact', 'blog');
require_once('lib.php');

$json = array('time' => time());

if (!get_config('allowmobileuploads')) {
    mobile_api_json_reply( array('fail' => get_string('mobileuploadnotenabled', 'auth') ) );
}

$token = trim(param_variable('token', ''));

if ($token == '') {
    mobile_api_json_reply( array('fail' => get_string('mobileuploadtokennotset', 'auth') ) );
}

$username = trim(param_variable('username', ''));

if ($username == '') {
    mobile_api_json_reply( array('fail' => get_string('mobileuploadusernamenotset', 'auth') ) );
}

$USER = new User();

try {
    $USER->find_by_mobileuploadtoken($token, $username);
}
catch (AuthUnknownUserException $e) {
    mobile_api_json_reply( array('fail' => get_string('mobileuploadtokennotfound', 'auth') ) );
}

// Add in bits of sync data - let's start with notifications
$lastsync = param_integer('lastsync', 0);

$notification_types_sql = '';
$notification_types = explode(",", trim(param_variable('notifications', '')));
if (count($notification_types) > 0) {
    $notification_types_sql = ' a.name IN (' . join(',', array_map('db_quote',$notification_types)) . ')';
}

$activity_arr = get_records_sql_array("
            SELECT n.id, n.subject, n.message
            FROM {notification_internal_activity} n
            INNER JOIN {activity_type} a ON n.type=a.id
            WHERE $notification_types_sql
                AND n.read=0
                AND " . db_format_tsfield('ctime', '') . " >= ?
                AND n.usr= ? ",
             array($lastsync, $USER->id)
);

if (count($activity_arr) > 0) {
  $json['activity'] = $activity_arr;
}

// OK - let's add tags

$tags_arr = array();

$tagsort = param_alpha('ts', null) != 'freq' ? 'alpha' : 'freq';

foreach (get_my_tags(null, false, $tagsort) as $tag) {
    $tags_arr[] = array("id" => $tag->tag, "tag" => $tag->tag);
}

if (count($tags_arr) > 0) {
  $json['tags'] = $tags_arr;
}

// OK - let's add journals (and journal posts)
$blogs_arr = array();

$blogs = (object) array(
    'offset' => param_integer('offset', 0),
    'limit'  => param_integer('limit', 10),
);

$blogposts_arr = array();

$blogposts = array();

list($blogs->count, $blogs->data) = ArtefactTypeBlog::get_blog_list($blogs->limit, $blogs->offset);

foreach ($blogs->data as $blog) {
    if (!$blog->locked) {
        $blogs_arr[] = array("id" => $blog->id, "blog" => $blog->title);

        $blogposts = ArtefactTypeBlogpost::get_posts($blog->id, $blogs->limit, $blogs->offset, null);

        foreach ($blogposts['data'] as $blogpost) {
            if (!$blogpost->locked) {
                $blogposts_arr[] = array("id" => $blogpost->id, "blogpost" => $blogpost->title);
            }
        }
    }
}

if (count($blogs_arr) > 0) {
  $json['blogs'] = $blogs_arr;
}

if (count($blogposts_arr) > 0) {
  $json['blogposts'] = $blogposts_arr;
}

// OK - let's add folders

$folders_arr = array();
$folders = ArtefactTypeFile::get_my_files_data(0, $USER->id, null, null, array("artefacttype" => array("folder")));

foreach ($folders as $folder) {
    if ( ! $folder->locked ) {
        $folders_arr[] = array("id" => $folder->id, "folder" => $folder->title);
    }
}
if (count($folders_arr) > 0) {
    $json['folders'] = $folders_arr;
}

// Here we need to create a new hash - update our own store of it and return it to the handset
mobile_api_json_reply(array('success' => $USER->refresh_mobileuploadtoken($token), 'sync' => $json));
