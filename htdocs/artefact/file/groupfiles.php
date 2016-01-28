<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/files');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'groupfiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'group.php');
safe_require('artefact', 'file');

define('GROUP', param_integer('group'));
define('SUBSECTIONHEADING', get_string('Files', 'artefact.file'));
$group = group_current_group();

if (!$role = group_user_access($group->id) || !group_within_edit_window($group)) {
    throw new AccessDeniedException();
}
define('TITLE', $group->name . ' - ' . get_string('groupfiles', 'artefact.file'));

require_once(get_config('docroot') . 'interaction/lib.php');

$pagebase = get_config('wwwroot') . 'artefact/file/groupfiles.php?group=' . $group->id;
$form = pieform(ArtefactTypeFileBase::files_form($pagebase, $group->id));
$js = ArtefactTypeFileBase::files_js();

$smarty = smarty(
    array(),
    array(),
    array(),
    array(
        'sideblocks' => array(
            array(
                'name'   => 'groupquota',
                'weight' => -10,
                'data'   => array(),
            ),
        ),
    )
);
$smarty->assign('heading', $group->name);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('group', $group->name);
$smarty->display('artefact:file:files.tpl');
