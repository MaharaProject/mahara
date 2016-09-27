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

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');

$new = param_boolean('new', 0);
$copy = param_boolean('copy', 0);

$subtitle = false;
if ($new) {    // if creating a new collection
    $owner = null;
    $groupid = param_integer('group', 0);
    $institutionname = param_alphanum('institution', false);
    if (empty($groupid) && empty($institutionname)) {
        $owner = $USER->get('id');
    }
    $collection = new Collection(null, array('owner' => $owner, 'group' => $groupid, 'institution' => $institutionname));
    define('SUBSECTIONHEADING', get_string('edittitleanddesc', 'collection'));
}
else {    // if editing an existing or copied collection
    $id = param_integer('id');
    $collection = new Collection($id);
    $owner = $collection->get('owner');
    $groupid = $collection->get('group');
    $institutionname = $collection->get('institution');
    define('SUBSECTIONHEADING', $collection->get('name'));
}

if ($collection->is_submitted()) {
    $submitinfo = $collection->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'collection', $submitinfo->name));
}

$urlparams = array();
if (!empty($groupid)) {
    require_once('group.php');
    define('MENUITEM', 'groups/collections');
    define('GROUP', $groupid);
    $group = group_current_group();
    define('TITLE', $group->name . ' - ' . get_string('editcollection', 'collection'));
    $urlparams['group'] = $groupid;
}
else if (!empty($institutionname)) {
    if ($institutionname == 'mahara') {
        define('ADMIN', 1);
        define('MENUITEM', 'configsite/collections');
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        define('MENUITEM', 'manageinstitutions/institutioncollections');
    }
    define('TITLE', get_string('editcollection', 'collection'));
    $urlparams['institution'] = $institutionname;
}
else {
    define('MENUITEM', 'myportfolio/collection');
    define('TITLE', get_string('editcollection', 'collection'));
}

if (!$USER->can_edit_collection($collection)) {
    throw new AccessDeniedException(get_string('canteditcollection', 'collection'));
}

$baseurl = get_config('wwwroot') . 'collection/index.php';
if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}

$elements = $collection->get_collectionform_elements();

if ($copy) {
    $type = 'submit';
    $submitstr = get_string('next') . ': ' . get_string('editviews', 'collection');
    $confirm = null;
}
else {
    $type = 'submitcancel';
    if ($new) {
        $submitstr = array('button' => get_string('next') . ': ' . get_string('editviews', 'collection'), 'cancel' => get_string('cancel'));
        $confirm = array('cancel' => get_string('confirmcancelcreatingcollection','collection'));
    }
    else {
        $submitstr = array(get_string('save'), get_string('cancel'));
        $confirm = null;
    }
}
$elements['submit'] = array(
    'type'      => $type,
    'class' => 'btn-primary',
    'value'     => $submitstr,
    'confirm'   => $confirm,
);

$form = pieform(array(
    'name' => 'edit',
    'plugintype' => 'core',
    'pluginname' => 'collection',
    'validatecallback' => 'collectionedit_validate',
    'successcallback' => 'collectionedit_submit',
    'elements' => $elements,
));

$smarty = smarty();
setpageicon($smarty, 'icon-folder-open');

$smarty->assign('headingclass', 'page-header');

$smarty->assign('form', $form);
$smarty->display('collection/edit.tpl');

function collectionedit_validate(Pieform $form, $values) {
    if (!empty($values['id'])) {
        $collection = new Collection($values['id']);
        if ($collection->has_framework() && $collection->get('framework') != $values['framework']) {
            // Make sure that if the user is changing the framework that there isn't annotations paired to the old framework
            $views = get_records_sql_array("SELECT v.id, v.title FROM {view} v
                                            JOIN {collection_view} cv ON cv.view = v.id
                                            JOIN {framework_evidence} fe ON fe.view = cv.view
                                            WHERE cv.collection = ?", array($values['id']));
            if (!empty($views)) {
                $errorstr = get_string('changeframeworkproblems', 'module.framework');
                foreach ($views as $view) {
                    $errorstr .= " '" . $view->title . "'";
                }
                $form->set_error('framework', $errorstr);
            }
        }
    }
}

function collectionedit_submit(Pieform $form, $values) {
    global $SESSION, $new, $copy, $urlparams;
    $values['navigation'] = (int) $values['navigation'];
    if (empty($values['framework'])) {
        $values['framework'] = null;
    }
    $collection = Collection::save($values);
    if (!$new) {
        $SESSION->add_ok_msg(get_string('collectionsaved', 'collection'));
    }
    $collection->post_edit_redirect($new, $copy, $urlparams);
}

function edit_cancel_submit() {
    global $baseurl;
    redirect($baseurl);
}
