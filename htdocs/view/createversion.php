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

if (!$view ) {
    throw new AccessDeniedException(get_string('cantversionviewinvalid', 'view'));
}
if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException(get_string('cantversionvieweditpermissions', 'view'));
}
if ($view->is_submitted()) {
    throw new AccessDeniedException(get_string('cantversionviewsubmitted', 'view'));
}

$groupid = $view->get('group');
if ($groupid && !group_within_edit_window($groupid)) {
    throw new AccessDeniedException(get_string('cantversionviewgroupeditwindow', 'view'));
}

$version = new stdClass();
$version->numrows = $view->get('numrows');
$version->layout = $view->get('layout');
$version->description = $view->get('description');
$version->title = $view->get('title');
$version->tags = $view->get('tags');
$version->columnsperrow = $view->get('columnsperrow');
$version->blocks = array();
$blocks = get_records_array('block_instance', 'view', $view->get('id'));

if ($blocks) {
    foreach ($blocks as $k => $b) {
        if (safe_require('blocktype', $b->blocktype, 'lib.php', 'require_once', true) !== false) {
            $oldblock = new BlockInstance($b->id, $b);

            $bi = new stdClass();
            $bi->originalblockid = $oldblock->get('id');
            $bi->blocktype = $oldblock->get('blocktype');
            $bi->title = $oldblock->get('title');
            $bi->configdata = $oldblock->get('configdata');
            $bi->row = $oldblock->get('row');
            $bi->column = $oldblock->get('column');
            $bi->order = $oldblock->get('order');

            $classname = generate_class_name('blocktype', $oldblock->get('blocktype'));
            if (is_callable($classname . '::'. 'get_current_artefacts')) {
                // The block is for one artefact so lets see if it displays more than one artefact
                if ($artefacts = call_static_method($classname, 'get_current_artefacts', $oldblock)) {
                    // We need to ignore the parent artefactid
                    foreach ($artefacts as $key => $artefact) {
                        if (isset($bi->configdata['artefactid']) && $bi->configdata['artefactid'] == $artefact) {
                            unset($artefacts[$key]);
                        }
                    }
                    if (!empty($artefacts)) {
                        $bi->configdata['existing_artefacts'] = $artefacts;
                    }
                }
            }
            if ($oldblock->get('blocktype') == 'annotation' || $oldblock->get('blocktype') ==  'textbox') {
                $configdata = $oldblock->get('configdata');
                if (!empty($configdata['artefactid'])) {
                    safe_require('artefact', 'file');
                    $artefactid = $configdata['artefactid'];
                    $artefact = $oldblock->get_artefact_instance($artefactid);
                    $bi->configdata['text'] = $artefact->get('description');
                }
            }
            if ($oldblock->get('blocktype') == 'taggedposts') {
                $tagrecords = get_records_array('blocktype_taggedposts_tags', 'block_instance', $oldblock->get('id'), 'tagtype desc, tag', 'tag, tagtype');
                $bi->configdata['tagrecords'] = $tagrecords;
            }
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
