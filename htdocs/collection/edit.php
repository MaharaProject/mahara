<?php
/**
 * Manage the editing of a Collection.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
if ($new) {
    // We're creating a new collection.
    $owner = null;
    $groupid = param_integer('group', 0);
    $institutionname = param_alphanum('institution', false);
    if (empty($groupid) && empty($institutionname)) {
        $owner = $USER->get('id');
    }
    $collection = new Collection(null, array('owner' => $owner, 'group' => $groupid, 'institution' => $institutionname));
    define('SUBSECTIONHEADING', get_string('edittitleanddesc', 'collection'));
}
else {
    // We're editing an existing or copied collection.
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
$group = null;
if (!empty($groupid)) {
    require_once('group.php');
    define('MENUITEM', 'engage/index');
    define('MENUITEM_SUBPAGE', 'views');
    define('GROUP', $groupid);
    $group = group_current_group();
    define('TITLE', $group->name . ' - ' . get_string('editcollection', 'collection'));
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
    define('TITLE', get_string('editcollection', 'collection'));
    $urlparams['institution'] = $institutionname;
}
else {
    define('MENUITEM', 'create/views');
    define('TITLE', get_string('editcollection', 'collection'));
    $baseurl = get_config('wwwroot') . 'view/index.php';
}

$outcomesgroup = $group && is_outcomes_group($group->id);
if (!$USER->can_edit_collection($collection) || (!empty($groupid) && $outcomesgroup && group_user_access($groupid) !== 'admin')) {
    throw new AccessDeniedException(get_string('canteditcollection', 'collection'));
}

if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}

$elements = $collection->get_collectionform_elements();

if ($copy) {
    $type = 'submit';
    $submitstr = get_string('continue') . ': ' . get_string('editviews', 'collection');
    $confirm = null;
    $class = 'btn-primary';
    $subclass = null;
}
else {
    $type = 'submitcancel';
    if ($collection->get('group') && is_outcomes_group($collection->get('group'))) {
        $submitstr = array('button' => get_string('continue'), 'cancel' => get_string('cancel'));
        $confirm = array('cancel' => get_string('confirmcancelcreatingcollection', 'collection'));
    }
    else if ($new) {
        $submitstr = array(
            'button' => get_string('continue') . ': ' . get_string('editviews', 'collection'),
            'cancel' => get_string('cancel')
        );
        $confirm = array('cancel' => get_string('confirmcancelcreatingcollection', 'collection'));
    }
    else {
        $submitstr = array(get_string('continue'), get_string('cancel'));
        $confirm = null;
    }
    $class = 'btn-primary';
    $subclass = array('btn-primary');
}
$elements['submitform'] = array(
    'type'      => $type,
    'class'     => $class,
    'subclass'  => $subclass,
    'value'     => $submitstr,
    'confirm'   => $confirm,
    'goto'      => $baseurl,
);
$form = pieform(array(
    'name' => 'edit',
    'method'     => 'post',
    'jsform'     => true,
    'jssuccesscallback' => 'edit_callback',
    'jserrorcallback'   => 'edit_callback',
    'plugintype' => 'core',
    'pluginname' => 'collection',
    'validatecallback' => 'collectionedit_validate',
    'successcallback' => 'collectionedit_submit',
    'elements' => $elements,
));

$autocopyjs = '';
$onlyactivetemplatewarning = '';
$updatingautocopytemplatewarning = null;
if (isset($institutionname)) {
    // Check if there's another collection set up as the institution auto copy
    // template.
    $oldtemplate = get_active_collection_template($institutionname);
    if ($oldtemplate && $oldtemplate->get('id') != $collection->get('id')) {
        $updatingautocopytemplatewarning = get_string(
            'updatingautocopytemplatewarning',
            'collection',
            institution_display_name($institutionname),
            $oldtemplate->get('name')
        );
    }
    $onlyactivetemplatewarning = get_string('onlyactivetemplatewarning', 'collection');

    $autocopyjs = <<<EOF
jQuery(function($) {
    $('#edit_autocopytemplate').on('click', function() {
        // show modal
        var setpagemodal = $("#set-confirm-form");
        var unsetpagemodal = $("#unset-confirm-form");
        var value = $('#edit input[name=autocopytemplate]:checked').val();
        if (value == 'on') {
            setpagemodal.modal('show');
            setpagemodal.on('shown.bs.modal', function() {
                setpagemodal.find('.btn').focus();
                keytabbinginadialog(setpagemodal, setpagemodal.find('.btn'), setpagemodal.find('.cancel'));
            });
        }
        else {
            unsetpagemodal.modal('show');
            unsetpagemodal.on('shown.bs.modal', function() {
                unsetpagemodal.find('.btn').focus();
                keytabbinginadialog(unsetpagemodal, unsetpagemodal.find('.btn'), unsetpagemodal.find('.cancel'));
            });
        }
    });
    $('#set-cancel-button').on('click', function() {
        $("#set-confirm-form").modal('hide');
        $('#edit input[name=autocopytemplate]').prop('checked', false);
        $('#edit_autocopytemplate').focus();
    });
    $('#set-yes-button').on('click', function() {
        $("#set-confirm-form").modal('hide');
        $('#edit_autocopytemplate').focus();
    });
    $('#unset-cancel-button').on('click', function() {
        $("#unset-confirm-form").modal('hide');
        $('#edit input[name=autocopytemplate]').prop('checked', true);
        $('#edit_autocopytemplate').focus();
    });
    $('#unset-yes-button').on('click', function() {
        $("#unset-confirm-form").modal('hide');
        $('#edit_autocopytemplate').focus();
    });
});
EOF;
}

$inlinejs = <<<EOF
function edit_callback(form, data) {
    edit_coverimage.callback(form, data);
};
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-folder-open');

$smarty->assign('headingclass', 'page-header');
$smarty->assign('INLINEJAVASCRIPT', $inlinejs . $autocopyjs);
$smarty->assign('form', $form);
if (isset($institutionname)) {
    $smarty->assign('institutionname', $institutionname);
    $smarty->assign('updatingautocopytemplatewarning', $updatingautocopytemplatewarning);
    $smarty->assign('onlyactivetemplatewarning', $onlyactivetemplatewarning);
}
$smarty->display('collection/edit.tpl');

/**
 * The validation callback for the Collection Edit form.
 *
 * @param Pieform $form The Pieform being validated.
 * @param mixed $values The values submitted by the Pieform.
 */
function collectionedit_validate(Pieform $form, $values) {
    if (!empty($values['id'])) {
        $collection = new Collection($values['id']);
        if ($collection->has_framework() && $collection->get('framework') != $values['framework']) {
            // Make sure that if the user is changing the framework that there
            // are not any annotations paired to the old framework.
            $views = get_records_sql_array("SELECT v.id, v.title FROM {view} v
                                            JOIN {collection_view} cv ON cv.view = v.id
                                            JOIN {framework_evidence} fe ON fe.view = cv.view
                                            WHERE cv.collection = ?
                                            GROUP BY v.id, v.title", array($values['id']));
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

/**
 * The submit callback for the Collection Edit form.
 *
 * @param Pieform $form The form being processed.
 * @param mixed $values The values that were submitted.
 */
function collectionedit_submit(Pieform $form, $values) {
    global $SESSION, $new, $copy, $urlparams, $institutionname, $collection, $USER;
    $values['navigation'] = (int) $values['navigation'];
    if (isset($values['progresscompletion'])) {
        $values['progresscompletion'] = (int) $values['progresscompletion'];
    }
    if (isset($values['lock'])) {
        $values['lock'] = (int) $values['lock'];
    }
    if (isset($values['template'])) {
        $values['template'] = (int) $values['template'];
    }
    if (isset($values['autocopytemplate'])) {
        // Need to deal with this after we have a collection id.
        $autocopytemplate = (int)$values['autocopytemplate'];
        unset($values['autocopytemplate']);
        $values['template'] = $autocopytemplate ? 1 : $values['template'];
    }
    if (empty($values['framework'])) {
        $values['framework'] = null;
    }
    $values['coverimage'] = (isset($values['coverimage']) ? $values['coverimage'] : null);
    $groupid = $collection->get('group');
    $values['outcomeportfolio'] = (int)($groupid && is_outcomes_group($groupid) && $values['outcomeportfolio']);
    $collection = Collection::save($values);

    if (isset($values['progresscompletion'])) {
        if ($values['progresscompletion']) {
            // Switch is on.
            $collection->add_progresscompletion_view();
        }
        else {
            // Switch is off.
            // Delete the progress page as it can't exist in a collection that
            // is not progress completion.
            if ($progressview = get_field_sql("SELECT v.id
                                               FROM {collection_view} cv
                                               JOIN {view} v ON v.id = cv.view
                                               WHERE cv.collection = ?
                                               AND v.type = ?", array($collection->get('id'), 'progress'))) {
                require_once(get_config('libroot') . 'view.php');
                $view = new View($progressview);
                $view->delete();
            }
        }
    }
    if (!empty($values['template'])) {
        if (isset($autocopytemplate)) {
            $collection->set_views_as_template($autocopytemplate);
        }
        else {
            $collection->set_views_as_template(null);
        }
    }
    if (isset($autocopytemplate)) {
        if ($autocopytemplate) {
            $collection->set_active_collection_template($institutionname);
        }
        else {
            // Only unset autocopy template if currently set to true.
            if (get_field('collection', 'autocopytemplate', 'id', $collection->get('id'))) {
                $collection->unset_active_collection_template($collection->get('id'), $institutionname, !empty($values['template']));
            }
        }
    }
    $result = array(
        'error'   => false,
        'message' => get_string('collectionsaved', 'collection'),
        'goto'    => $collection->post_edit_redirect_url($new, $copy, $urlparams),
    );

    if ($form->submitted_by_js()) {
        // Redirect back to the note page from within the iframe.
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

/**
 * Callback to return the user to the base URL.
 *
 * @todo Is this actually used?
 */
function edit_cancel_submit() {
    global $baseurl;
    redirect($baseurl);
}
