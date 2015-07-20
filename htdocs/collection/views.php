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
require_once('pieforms/pieform.php');
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
    $inlinejs = <<<EOF
jQuery(function($) {
    var message = $('<div id="changestatusline" class="warning"><div>$alertstr</div></div>');
    $('#messages').append(message);
});
EOF;
    $SESSION->set('pageaccesschanged', false);
    $SESSION->set('pagesadded', false);
}
$owner = $collection->get('owner');
$groupid = $collection->get('group');
$institutionname = $collection->get('institution');
$urlparams = array();
if (!empty($groupid)) {
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
define('SUBTITLE', $collection->get('name'). ': ' . get_string('editviews', 'collection'));
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
                    'value' => '<span class="icon icon-times text-danger"><span class="sr-only">' . get_string('remove') . '</span></span>',
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
            'class'     => 'btn drag-default-button ',
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
        'class' => 'btn-primary pull-right mtl',
        'type' => 'button',
        'usebuttontag' => true,
        'value' => '<span class="icon icon-arrow-right prs"></span>' . get_string('addviews','collection'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id,
    );

    $viewsform = pieform(array(
        'name' => 'addviews',
        'class' => 'ptm btn-draggable fullwidth',
        'renderer' => 'div',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'method'   => 'post',
        'elements' => $elements,
    ));
}
$noviewsavailable = get_string('noviewsavailable', 'collection');
$inlinejs .= <<<EOF
jQuery(function($) {
    var fixhelper = function(e, div) {
        var originals = div.children();
        var helper = div.clone();
        helper.children().each(function(index) {
            $(this).width(originals.eq(index).width());
        });
        return helper;
    };
    var updaterows = function(viewid) {
        var sortorder = $('#collectionviews').sortable('serialize');
        $.post(config['wwwroot'] + "collection/views.json.php", { sesskey: '$sesskey', id: $id, direction: sortorder })
        .done(function(data) {
            // update the page with the new table
            if (data.returnCode == '0') {
                $('#collectionviews').replaceWith(data.message.html);

                if (viewid) {
                    $('#addviews_view_' + viewid + '_container').remove();
                    // check if we have just removed the last option leaving
                    // only the add pages button
                    if ($("#addviews .checkbox").children().length <= 1) {
                        $("#addviews").remove();
                        $("#pagestoadd .panel-body").append('$noviewsavailable');
                    }
                }
                if (data.message.message) {

                    var warningClass = data.message.messagestatus === 'ok' ? 'success' : 'warning';

                    var warnmessage = $('<div id="changestatusline" class="alert alert-dismissible alert-' + warningClass + '" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>' + data.message.message + '</p></div>');

                    $('#messages').empty().append(warnmessage);
                }
                wiresortables();
                wireaddrow();
            }
        });
    };

    var wiresortables = function() {
        $('#collectionviews').sortable({
            items: '> div',
            cursor: 'move',
            helper: fixhelper,
            placeholder: "highlight",
            over: function(e, ui) {
               if (ui.placeholder[0].tagName == 'DIV') {
                   var container = $('#collectionviews');
                   var div = $('#collectionviews div.highlight');
                   div.css('width', container.width());
                   div.css('height', '30px');
                   $(ui.helper).addClass('btn-warning');
               }
            },
            stop: function(e, ui) {
                // Get label within the div using children
                var labelfor = ui.item.children().attr('for');
                if (typeof labelfor !== 'undefined' && labelfor !== false) {
                    // remove all but the digits
                    var viewid = ui.item.children().attr('for').replace(/[^\d.]/g,'');
                    ui.item.replaceWith('<div id="row_' + viewid + '" class="dropped-in-row">' + ui.item.html() + '</div>');
                    updaterows(viewid);
               }
               else {
                    updaterows(false);
               }
           }
        })
        .disableSelection()
        .hover(function() {
            $(this).css('cursor', 'move');
        });
    };

    var wireaddrow = function() {
        $('#addviews div').draggable({
            connectToSortable: '#collectionviews',
            cursor: 'move',
            revert: 'invalid',
            helper: 'clone',
        }).hover(function() {
            $(this).css('cursor', 'move');
        });
    };

    var wireaddnewrow = function() {
        $('#addviews > div').draggable({
            cursor: 'move',
            revert: 'invalid',
            helper: 'clone',
            start: function(e, ui) {
                $('#collectionpages .message').addClass('highlight');
            },
            stop: function(e, ui) {
                $('#collectionpages .message').removeClass('highlight');
            },
        }).hover(function() {
            $(this).css('cursor', 'move');
        });
    };

    var wiredrop = function() {
        $('#collectionpages .dropzone-previews').droppable({
            accept: "div",
            drop: function (e, ui) {
                var labelfor = ui.draggable.children().attr('for');
                if (typeof labelfor !== 'undefined' && labelfor !== false) {
                    // remove all but the digits
                    var viewid = ui.draggable.children().attr('for').replace(/[^\d.]/g,'');
                    $('#collectionpages .dropzone-previews').replaceWith('<div id="collectionviews"><div id="row_' + viewid + '">' + ui.draggable.html() + '</div></div>');
                    wiresortables();
                    updaterows(viewid);
                }
            },
        });
    };

    var wireselectall = function() {
        $("#selectall").click(function(e) {
            e.preventDefault();
            $("#addviews :checkbox").prop("checked", true);
        });
    };

    var wireselectnone = function() {
        $("#selectnone").click(function(e) {
            e.preventDefault();
            $("#addviews :checkbox").prop("checked", false);
        });
    };

    // init
    if ($('#collectionviews > li').length > 0) {
        wireaddrow();
        wiresortables();
    }
    else {
        wireaddnewrow();
        wiredrop();
    }
    wireselectall();
    wireselectnone();
});
EOF;

$smarty = smarty(array('jquery','js/jquery/jquery-ui/js/jquery-ui-1.10.2.min.js','js/jquery/jquery-ui/js/jquery-ui.touch-punch.min.js'));
setpageicon($smarty, 'icon-folder-open');

if (!empty($groupid)) {

    $smarty->assign('PAGESUBHEADING', SUBTITLE);
    $smarty->assign('PAGEHELPNAME', '0');
    $smarty->assign('SUBPAGEHELPNAME', '1');
}
else {
    $smarty->assign('PAGEHEADING', SUBTITLE);
}

$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('baseurl', $baseurl);
$smarty->assign('displayurl', get_config('wwwroot') . 'collection/views.php?id=' . $id);
$smarty->assign('removeurl', get_config('wwwroot') . 'collection/deleteview.php?id=' . $id);
$smarty->assign_by_ref('views', $views);
$smarty->assign_by_ref('viewsform', $viewsform);
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
    if ($collectiondifferent) {
        $differentarray = array_merge($differentarray, $viewids);
    }
    if ($different) {
        $SESSION->set('pageaccesschanged', $differentarray);
        $SESSION->set('pagesadded', $count);
    }
    else {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection1', 'collection', $count));
    }
    redirect('/collection/views.php?id='.$collection->get('id'));
}

function removeview_submit(Pieform $form, $values) {
    global $SESSION, $collection;
    $collection->remove_view((int)$values['view']);
    $SESSION->add_ok_msg(get_string('viewremovedsuccessfully','collection'));
    redirect('/collection/views.php?id='.$collection->get('id'));
}
