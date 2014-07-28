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

$id = param_integer('id');

// view addition/displayorder values
$view = param_integer('view',0);
$direction = param_variable('direction','');

$collection = new Collection($id);
if (!$USER->can_edit_collection($collection)) {
    throw new AccessDeniedException(get_string('canteditcollection', 'collection'));
}
$sesskey = $USER->get('sesskey');
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
$inlinejs = <<<EOF
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
            stop: function(e, ui) {
                var labelfor = ui.item.attr('for');
                if (typeof labelfor !== 'undefined' && labelfor !== false) {
                    var viewid = ui.item.attr('for').replace(/[^\d.]/g,''); // remove all but the digits
                    ui.item.replaceWith('<tr id="row_' + viewid + '"><td colspan="3">' + ui.item.text() + '</td></tr>');
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
                    \$j('#collectionpages .message').replaceWith('<table id="collectionviews"><tbody><tr id="row_' + viewid + '"><td colspan="3">' + ui.draggable.text() + '</td></tr></tbody></table>');
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

function addviews_submit(Pieform $form, $values) {
    global $SESSION, $collection;
    $count = $collection->add_views($values);
    if ($count > 1) {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection', 'collection'));
    }
    else {
        $SESSION->add_ok_msg(get_string('viewaddedtocollection', 'collection'));
    }
    redirect('/collection/views.php?id='.$collection->get('id'));

}

function removeview_submit(Pieform $form, $values) {
    global $SESSION, $collection;
    $collection->remove_view((int)$values['view']);
    $SESSION->add_ok_msg(get_string('viewremovedsuccessfully','collection'));
    redirect('/collection/views.php?id='.$collection->get('id'));
}
