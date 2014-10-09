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
\$j(function() {
    var message = \$j('<div id="changestatusline" class="warning"><div>$alertstr</div></div>');
    \$j('#messages').append(message);
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
            'name' => 'removeview_' . $v->view,
            'successcallback' => 'removeview_submit',
            'elements' => array(
                'view' => array(
                    'type' => 'hidden',
                    'value' => $v->view,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'confirm' => get_string('viewconfirmremove', 'collection'),
                    'value' => get_string('remove'),
                ),
            ),
        ));
    }
}

$elements = array();
$viewsform = null;
if ($available = Collection::available_views($owner, $groupid, $institutionname)) {
    foreach ($available as $a) {
        $elements['view_'.$a->id] = array(
            'type'      => 'checkbox',
            'title'     => $a->title,
        );
    }
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('addviews','collection'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id,
    );

    $viewsform = pieform(array(
        'name' => 'addviews',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'method'   => 'post',
        'elements' => $elements,
    ));
}
$noviewsavailable = get_string('noviewsavailable', 'collection');
$inlinejs .= <<<EOF
\$j(function() {
    var fixhelper = function(e, tr) {
        var originals = tr.children();
        var helper = tr.clone();
        helper.children().each(function(index) {
            \$j(this).width(originals.eq(index).width());
        });
        return helper;
    };
    var updaterows = function(viewid) {
        var sortorder = \$j('#collectionviews tbody').sortable('serialize');
        \$j.post(config['wwwroot'] + "collection/views.json.php", { sesskey: '$sesskey', id: $id, direction: sortorder })
        .done(function(data) {
            // update the page with the new table
            if (data.returnCode == '0') {
                \$j('#collectionviews').replaceWith(data.message.html);
                if (viewid) {
                    \$j('#addviews_view_' + viewid + '_container').remove();
                    // check if we have just removed the last option leaving
                    // only the add pages button
                    if (\$j("#addviews tbody").children().length <= 1) {
                        \$j("#addviews").remove();
                        \$j("#pagestoadd").append('$noviewsavailable');
                    }
                }
                if (data.message.message) {
                    var warnmessage = \$j('<div id="changestatusline" class="' + data.message.messagestatus + '"><div>' + data.message.message + '</div></div>');
                    \$j('#messages').empty().append(warnmessage);
                }
                wiresortables();
                wireaddrow();
            }
        });
    };

    var wiresortables = function() {
        \$j('#collectionviews tbody').sortable({
            items: 'tr',
            cursor: 'move',
            opacity: 0.6,
            helper: fixhelper,
            placeholder: "highlight",
            over: function(e, ui) {
               if (ui.placeholder[0].tagName == 'LABEL') {
                   var tr = \$j('#collectionviews tbody tr:first');
                   var label = \$j('#collectionviews tbody label.highlight');
                   label.css('width', tr.width());
                   label.css('height', tr.height());
                   label.css('display', 'table-row');
               }
            },
            stop: function(e, ui) {
                var labelfor = ui.item.attr('for');
                if (typeof labelfor !== 'undefined' && labelfor !== false) {
                    var viewid = ui.item.attr('for').replace(/[^\d.]/g,''); // remove all but the digits
                    ui.item.replaceWith('<tr id="row_' + viewid + '" class="dropped-in-row"><td colspan="3">' + ui.item.html() + '</td></tr>');
                    updaterows(viewid);
                }
                else {
                    updaterows(false);
                }
            },
        })
        .disableSelection()
        .hover(function() {
            \$j(this).css('cursor', 'move');
        });
    };

    var wireaddrow = function() {
        \$j('#addviews label').draggable({
            connectToSortable: '#collectionviews tbody',
            cursor: 'move',
            revert: 'invalid',
            helper: 'clone',
        }).hover(function() {
            \$j(this).css('cursor', 'move');
        });
    };

    var wireaddnewrow = function() {
        \$j('#addviews label').draggable({
            cursor: 'move',
            revert: 'invalid',
            helper: 'clone',
            start: function(e, ui) {
                \$j('#collectionpages .message').addClass('highlight');
            },
            stop: function(e, ui) {
                \$j('#collectionpages .message').removeClass('highlight');
            },
        }).hover(function() {
            \$j(this).css('cursor', 'move');
        });
    };

    var wiredrop = function() {
        \$j('#collectionpages .message').droppable({
            accept: "label",
            drop: function (e, ui) {
                var labelfor = ui.draggable.attr('for');
                if (typeof labelfor !== 'undefined' && labelfor !== false) {
                    var viewid = ui.draggable.attr('for').replace(/[^\d.]/g,''); // remove all but the digits
                    \$j('#collectionpages .message').replaceWith('<table id="collectionviews"><tbody><tr id="row_' + viewid + '"><td colspan="3">' + ui.draggable.html() + '</td></tr></tbody></table>');
                    wiresortables();
                    updaterows(viewid);
                }
            },
        });
    };

    var wireselectall = function() {
        \$j("#selectall").click(function(e) {
            e.preventDefault();
            \$j("#addviews :checkbox").prop("checked", true);
        });
    };

    var wireselectnone = function() {
        \$j("#selectnone").click(function(e) {
            e.preventDefault();
            \$j("#addviews :checkbox").prop("checked", false);
        });
    };

    // init
    if (\$j('#collectionviews tbody').length > 0) {
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
