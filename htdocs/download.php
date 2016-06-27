<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require('init.php');
require_once('file.php');

$type = param_alpha('type', null);

if ($type == 'sitemap') {
    if (!get_config('generatesitemap')) {
        throw new NotFoundException(get_string('filenotfound'));
    }
    if ($name = param_alphanumext('name', null)) {
        if (!preg_match('/^sitemap_[a-z0-9_]+\.xml(\.gz)?$/', $name, $m)) {
            throw new NotFoundException(get_string('filenotfound'));
        }
        $mimetype = empty($m[1]) ? 'text/xml' : 'application/gzip';
    }
    else {
        $name = 'sitemap_index.xml';
        $mimetype = 'text/xml';
    }
    $path = get_config('dataroot') . 'sitemaps/' . $name;
}
else if ($type == 'groupmembership') {
    $group_id = param_integer('groupid');

    $data = group_get_membership_file_data($group_id);

    if (!$USER->is_logged_in() || empty($data)) {
        throw new NotFoundException(get_string('filenotfound'));
    }

    $path = get_config('dataroot') . 'export/' . $USER->get('id') . '/' . $data['file'];
    $name = $data['name'];
    $mimetype = $data['mimetype'];
}
else {
    $data = $SESSION->get('downloadfile');

    if (!$USER->is_logged_in() || empty($data) || empty($data['file'])) {
        throw new NotFoundException(get_string('filenotfound'));
    }

    $path = get_config('dataroot') . 'export/' . $USER->get('id') . '/' . $data['file'];
    $name = $data['name'];
    $mimetype = $data['mimetype'];
}

if (!file_exists($path)) {
    throw new NotFoundException(get_string('filenotfound'));
}

serve_file($path, $name, $mimetype);
