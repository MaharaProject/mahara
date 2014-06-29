<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require('init.php');
require_once('file.php');

$id = param_integer('id');
$data = get_record_sql('SELECT e.*, a.* FROM {export_archive} e
                       LEFT JOIN {archived_submissions} a ON a.archiveid = e.id
                       WHERE e.id = ?', array($id));

if (!$USER->is_logged_in()) {
    throw new AccessDeniedException();
}
if (empty($data) || empty($data->filename) || !$USER->can_view_archive($data)) {
    throw new AccessDeniedException();
}

$path = $data->filepath . $data->filename;
$name = $data->filename;
$mimetype = 'application/zip';

if (!file_exists($path)) {
    throw new NotFoundException(get_string('filenotfoundmaybeexpired'));
}

serve_file($path, $name, $mimetype);
