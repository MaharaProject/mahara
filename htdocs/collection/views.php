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
define('SECTION_PAGE', 'views');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');
require_once('view.php');

$id = param_integer('id');

// view addition/displayorder values
$view = param_integer('view',0);
$direction = param_variable('direction','');

$collection = new Collection($id);
if (!$USER->can_edit_collection($collection)) {
    throw new AccessDeniedException(get_string('canteditcollection', 'collection'));
}
$sesskey = $USER->get('sesskey');
$inlinejs = '';
if ($accesschanged = $SESSION->get('pageaccesschanged')) {
    $alertstr = get_string('viewsaddedaccesschanged', 'collection');
    foreach ($accesschanged as $viewid) {
        $changedview = new View($viewid);
        $alertstr .= " " . json_encode($changedview->get('title')) . ",";
    }
    $alertstr = substr($alertstr, 0, -1) . '.';
    $alertstr = get_string('viewsaddedtocollection1', 'collection', $SESSION->get('pagesadded')) . ' ' . $alertstr;
    $hassecreturl = $SESSION->get('hassecreturl');
    $alertstr .= ($hassecreturl) ? ' ' . get_string('viewaddedsecreturl', 'collection') : '';
    $inlinejs = <<<EOF
jQuery(function($) {
    var message = $('<div id="changestatusline" class="alert alert-dismissible alert-warning" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>$alertstr</p></div>');
    $('#messages').append(message);
});
EOF;
    $SESSION->set('pageaccesschanged', false);
    $SESSION->set('pagesadded', false);
    $SESSION->set('hassecreturl', false);
}
$owner = $collection->get('owner');
$groupid = $collection->get('group');
$institutionname = $collection->get('institution');
$urlparams = array();
if (!empty($groupid)) {
    define('MENUITEM', 'groups/collections');
    define('GROUP', $groupid);
    $group = group_current_group();
    define('TITLE', $group->name . ' - ' . get_string('editviews', 'collection'));
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
    define('TITLE', get_string('editviews', 'collection'));
    $urlparams['institution'] = $institutionname;
}
else {
    define('MENUITEM', 'myportfolio/collection');
    define('TITLE', get_string('editviews', 'collection'));
}
define('SUBSECTIONHEADING', $collection->get('name'));
$baseurl = get_config('wwwroot') . 'collection/index.php';
if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}
if ($collection->is_submitted()) {
    $submitinfo = $collection->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'collection', $submitinfo->name));
}

if ($view AND !empty($direction)) {
    $collection->set_viewdisplayorder($view,$direction);
    redirect('/collection/views.php?id='.$id);
}

$views = $collection->views();

if ($views) {
    foreach ($views['views'] as &$v) {
        $v->remove = pieform(array(
            'renderer' => 'div',
            'class' => 'form-as-button pull-right',
            'name' => 'removeview_' . $v->view,
            'successcallback' => 'removeview_submit',
            'elements' => array(
                'view' => array(
                    'type' => 'hidden',
                    'value' => $v->view,
                ),
                'submit' => array(
                    'type' => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-link btn-sm',
                    'confirm' => get_string('viewconfirmremove', 'collection'),
                    'value' => '<span class="icon icon-times icon-lg text-danger" role="presentation" aria-hidden="true"><span class="sr-only">' . get_string('remove') . '</span></span>',
                ),
            ),
        ));
    }
}

$elements = array();
$viewsform = null;
if ($available = Collection::available_views($owner, $groupid, $institutionname)) {
    $checkboxes = array();
    foreach ($available as $a) {
        $checkboxes['view_'.$a->id] = array(
            'class'     => 'btn drag-default-button',
            'isformgroup' => false,
            'type'      => 'checkbox',
            'title'     => $a->title,
        );
    };

    $elements['draggable-group'] = array(
        'class' => 'btn-group btn-group-vertical fullwidth ',
        'type' => 'fieldset',
        'renderelementsonly' => true,
        'elements' => $checkboxes
    );

    $elements['submit'] = array(
        'class' => 'btn-primary pull-right add-pages',
        'type' => 'button',
        'usebuttontag' => true,
        'value' => '<span class="icon icon-arrow-right left" role="presentation" aria-hidden="true"></span>' . get_string('addviews','collection'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id,
    );

    $viewsform = pieform(array(
        'name' => 'addviews',
        'class' => 'btn-draggable fullwidth',
        'renderer' => 'div',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'method'   => 'post',
        'elements' => $elements,
    ));
}
$noviewsavailable = get_string('noviewsavailable', 'collection');
$smarty = smarty(array('jquery','js/jquery/jquery-ui/js/jquery-ui.min.js','js/jquery/jquery-ui/js/jquery-ui.touch-punch.min.js', 'manage-collection-pages'));
setpageicon($smarty, 'icon-folder-open');

$smarty->assign('id', $id);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('baseurl', $baseurl);
$smarty->assign('displayurl', get_config('wwwroot') . 'collection/views.php?id=' . $id);
$smarty->assign('removeurl', get_config('wwwroot') . 'collection/deleteview.php?id=' . $id);
$smarty->assign('views', $views);
$smarty->assign('viewsform', $viewsform);
$smarty->display('collection/views.tpl');

function addviews_validate(Pieform $form, $values) {

    // Check if a view was selected. Each view was marked with a
    // key of view_<id> in order to identify the correct items
    // from the form values
    $chosen = array();
    foreach ($values as $key => $value) {
        if (substr($key, 0, 5) === 'view_' AND $value == true) {
            $chosen[] = substr($key, 5);
        }
    }
    if (empty($chosen)) {
        $form->set_error(null, get_string('needtoselectaview', 'collection'));
        return;
    }
}

function addviews_submit(Pieform $form, $values) {
    global $SESSION, $collection;

    // Check if the existing view permissions are different from the views being added
    $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'));
    $firstviewaccess = array();
    if (count($viewids)) {
        $firstview = new View($viewids[0]);
        $firstviewaccess = $firstview->get_access();
    }

    $chosen = array();
    foreach ($values as $key => $value) {
        if (substr($key, 0, 5) === 'view_' AND $value == true) {
            $chosen[] = substr($key, 5);
        }
    }
    // New view permissions
    $collectiondifferent = false;
    $different = false;
    $differentarray = array();
    foreach ($chosen as $viewid) {
        $view = new View($viewid);
        $viewaccess = $view->get_access();

        if (!empty($firstviewaccess) && empty($viewaccess)) {
            // adding the collection access rules to the added pages
            $different = true;
            $differentarray[] = $viewid;
        }
        else if (!empty($firstviewaccess)) {
            $merged = combine_arrays($firstviewaccess, $viewaccess);
            if ($merged != $firstviewaccess) {
                // adding the new access rules to both collection and added pages
                $different = true;
                $collectiondifferent = true;
                $differentarray[] = $viewid;
            }
            else if ($merged != $viewaccess) {
                // adding collection access rules to the added pages
                $different = true;
                $differentarray[] = $viewid;
            }
        }
        else if (empty($firstviewaccess) && !empty($viewaccess)) {
            // adding the page's access rules to the collection pages
            $different = true;
            $collectiondifferent = true;
        }
    }
    $count = $collection->add_views($values);
    // Check if the collection has a secret url token for any of the existing views
    $hassecreturl = false;
    $views_all = array_merge($differentarray, $viewids);
    if (!empty($views_all)) {
        if (record_exists_select("view_access", "view IN (" . join(',', array_merge($differentarray, $viewids)) . ") AND (token IS NOT NULL AND token !='')")) {
            $hassecreturl = true;
        }
    }

    if ($collectiondifferent) {
        $differentarray = array_merge($differentarray, $viewids);
    }
    if ($different) {
        $SESSION->set('pageaccesschanged', $differentarray);
        $SESSION->set('pagesadded', $count);
        $SESSION->set('hassecreturl', $hassecreturl);
    }
    else {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection1', 'collection', $count));
        if ($hassecreturl) {
            $SESSION->add_error_msg(get_string('viewaddedsecreturl', 'collection'));
        }
    }
    redirect('/collection/views.php?id=' . $collection->get('id'));
}

function removeview_submit(Pieform $form, $values) {
    global $SESSION, $collection;
    $collection->remove_view((int)$values['view']);
    $SESSION->add_ok_msg(get_string('viewremovedsuccessfully','collection'));
    redirect('/collection/views.php?id=' . $collection->get('id'));
}
