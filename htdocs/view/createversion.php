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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
$viewid = param_integer('view');

$view = new View($viewid, null);

if (!$view || !$USER->can_edit_view($view) || $view->is_submitted()) {
    throw new AccessDeniedException(get_string('cantversionview', 'view'));
}
$groupid = $view->get('group');
if ($groupid && !group_within_edit_window($groupid)) {
    throw new AccessDeniedException(get_string('cantversionview', 'view'));
}

$version = new stdClass();
$version->numrows = $view->get('numrows');
$version->layout = $view->get('layout');
$version->description = $view->get('description');
$version->tags = $view->get('tags');
$version->columnsperrow = $view->get('columnsperrow');
$version->blocks = array();
$blocks = get_records_array('block_instance', 'view', $view->get('id'));

if ($blocks) {
    foreach ($blocks as $k => $b) {
        if (safe_require('blocktype', $b->blocktype, 'lib.php', 'require_once', true) !== false) {
            $oldblock = new BlockInstance($b->id, $b);

            $bi = new stdClass();
            $bi->blocktype = $oldblock->get('blocktype');
            $bi->title = $oldblock->get('title');
            $bi->configdata = $oldblock->get('configdata');
            $bi->row = $oldblock->get('row');
            $bi->column = $oldblock->get('column');
            $bi->order = $oldblock->get('order');
            $version->blocks[$k] = $bi;
        }
    }
}

$fordb = new stdClass();
$fordb->view = $view->get('id');
$fordb->ctime = db_format_timestamp(time());
$fordb->blockdata = json_encode($version);
$fordb->owner = $view->get('owner');
$id = insert_record('view_versioning', $fordb, 'id', true);
$SESSION->add_ok_msg(get_string('savedtotimeline', 'view'));
redirect('/view/view.php?id=' . $view->get('id'));
