<?php
/**
 * Manage the deletion of a Collection.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);

define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PAGE', 'delete');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');

$id = param_integer('id');
$collection = new Collection($id);
if (!$USER->can_edit_collection($collection)) {
    throw new AccessDeniedException(get_string('cantdeletecollection', 'collection'));
}
if ($collection->is_submission()) {
    throw new AccessDeniedException(get_string('cantdeletecollectionsubmission', 'collection'));
}

if ($collection->get('group')) {
    $group = get_group_by_id($collection->get('group'));
    if (group_deny_access($group, 'member')) {
        throw new AccessDeniedException();
    }
}

$groupid = $collection->get('group');
$institutionname = $collection->get('institution');
$urlparams = array();
if (!empty($groupid)) {
    define('MENUITEM', 'engage/index');
    define('MENUITEM_SUBPAGE', 'views');
    define('GROUP', $groupid);
    $group = group_current_group();
    define('TITLE', $group->name . ' - ' . get_string('deletecollection', 'collection'));
    $baseurl = get_config('wwwroot') . 'view/groupviews.php';
    $urlparams['group'] = $groupid;
}
else if (!empty($institutionname)) {
    if ($institutionname == 'mahara') {
        define('ADMIN', 1);
        define('MENUITEM', 'configsite/views');
        $baseurl = get_config('wwwroot') . 'admin/site/views.php';
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        define('MENUITEM', 'manageinstitutions/institutionviews');
        $baseurl = get_config('wwwroot') . 'view/institutionviews.php';
    }
    define('TITLE', get_string('deletecollection', 'collection'));
    $urlparams['institution'] = $institutionname;
}
else {
    define('MENUITEM', 'create/views');
    define('TITLE', get_string('deletecollection', 'collection'));
    $baseurl = get_config('wwwroot') . 'view/index.php';
}
define('SUBSECTIONHEADING', $collection->get('name'));

if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}

if ($collection->is_submitted()) {
    $submitinfo = $collection->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'collection', $submitinfo->name));
}

$form = pieform(array(
    'name' => 'deletecollection',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'subclass' => array('btn-secondary'),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => $baseurl,
        ),
    ),
));

$smarty = smarty();
setpageicon($smarty, 'icon-folder-open');
$smarty->assign('subheading', get_string('deletespecifiedcollection', 'collection', $collection->get('name')));
$smarty->assign('message', get_string('collectionconfirmdelete1', 'collection', get_config('wwwroot'), $id));
$smarty->assign('form', $form);
$smarty->display('collection/delete.tpl');

/**
 * Callback to process the delete collection form.
 *
 * @param Pieform $form The Pieform being processed.
 * @param array $values The values submitted by the form.
 */
function deletecollection_submit(Pieform $form, $values) {
    global $SESSION, $collection, $baseurl;
    $collection->delete(true);
    $SESSION->add_ok_msg(get_string('collectiondeleted', 'collection'));
    redirect($baseurl);
}
