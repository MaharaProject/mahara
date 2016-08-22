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
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'framework');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'institution.php');
safe_require('module', 'framework');
safe_require('artefact', 'comment');

pieform_setup_headdata();

// This page should only be viewable if:
// 1). The collection has_framework() return true
// 2). The institution the collection owner belongs to has 'Smart Evidence' turned on.
// 3). The collection is able to be viewed by the user.

$collectionid = param_integer('id');
$collection = new Collection($collectionid);
$owner = $collection->get('owner');
$views = $collection->get('views');
if (empty($views)) {
    $errorstr = get_string('accessdeniednoviews', 'module.framework');
    throw new AccessDeniedException($errorstr);
}

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!$collection->has_framework()) {
    // We can't show the matrix page so redirect them to the first page of the collection instead
    redirect($view->get_url());
}

if (!can_view_view($view->get('id'))) {
    $errorstr = get_string('accessdenied', 'error');
    throw new AccessDeniedException($errorstr);
}
$frameworkid = $collection->get('framework');
$framework = new Framework($frameworkid);
$standards = $framework->standards();

define('TITLE', $collection->get('name'));

$javascript = array('js/collection-navigation.js', 'tinymce');

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}

$headers = array();
$headers[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';

// Set up skin, if the page has one
$viewskin = $view->get('skin');
$issiteview = $view->get('institution') == 'mahara';
if ($viewskin && get_config('skins') && can_use_skins($owner, false, $issiteview) && (!isset($THEME->skins) || $THEME->skins !== false)) {
    $skin = array('skinid' => $viewskin, 'viewid' => $view->get('id'));
}
else {
    $skin = false;
}

$headers[] = '<meta name="robots" content="noindex">';  // Tell search engines not to index this page

$smarty = smarty(
    $javascript,
    $headers,
    array('View' => 'view',
          'Collection' => 'collection'),
    array(
        'sidebars' => false,
        'skin' => $skin
    )
);

// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        $viewnav = $views['views'];
        array_unshift($viewnav, $collection->collection_nav_framework_option());
        $smarty->assign('collection', $viewnav);
    }
}

$evidence = $framework->get_evidence($collection->get('id'));
if (!$evidence) {
    $evidence = array();
}
$evidencematrix = $completed = array();
foreach ($evidence as $e) {
    $evidencematrix[$e->framework][$e->element][$e->view] = Framework::get_state_array($e->state);
    if (!isset($completed[$e->element])) {
        $completed[$e->element] = 0;
    }
    if ((int) $e->state === Framework::EVIDENCE_COMPLETED) {
        $completed[$e->element] ++;
    }
}

$inlinejs = <<<EOF
jQuery(function($) {
    // Variable to adjust for the hiding/showing of columns
    var minstart = 1; // The index of the last column before first page column, indexes start at zero so 1 = two columns
    var curstart = 2; // The index of first page currently being displayed
    var range = 4; // The number of pages to display
    var curend = curstart + range; // The index of last page currently being displayed
    var maxend = $( "#tablematrix tr th" ).length; // The number of columns in the table

    function carousel_matrix() {
        $('#tablematrix td:not(.special), #tablematrix th').each(function() {
            var index = $(this).index();
            if ((index > minstart && index < curstart) || index > curend) {
                $(this).hide();
            }
            else {
                $(this).show();
            }
        });

        if (curstart <= (minstart + 1)) {
            $('#prev').hide();
        }
        else {
            $('#prev').show();
        }
        if (curend >= (maxend - 1)) {
            $('#next').hide();
        }
        else {
            $('#next').show();
        }
    }

    $('#prev, #next').on('click', function(e) {
        e.preventDefault();
        var action = $(this).attr('id');
        if (action == 'next') {
            curend = Math.min(curend + 1, maxend - 1);
            curstart = curend - range;
            carousel_matrix();
        }
        if (action == 'prev') {
            curstart = Math.max(curstart - 1, minstart + 1);
            curend = curstart + range;
            carousel_matrix();
        }
    });

    var cellx = celly = 0;
    $('#tablematrix td.mid span:not(.disabled)').on('click', function(e) {
        e.preventDefault();
        cellx = $(this).closest('td').index();
        celly = $(this).closest('tr').index();
        var params = {};
        params.framework = $frameworkid;
        params.view = $(this).data("view");
        params.option = $(this).data("option");
        sendjsonrequest('matrixpoint.json.php', params, 'POST', function(data) {

            dock.show($('#configureblock'), true, false);
            var newpagemodal = $('#configureblock');
            newpagemodal.find('.blockinstance-header').html(data.data.form.title);
            newpagemodal.find('.blockinstance-content').html(data.data.form.content);

            deletebutton = newpagemodal.find('.deletebutton');
            // Lock focus to the newly opened dialog
            deletebutton.focus();
            deletebutton.on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (data.data.form.isnew) {
                    // need to delete empty annotation on cancel
                    params.action = 'delete';
                    params.blockconfig = $('#instconf_blockconfig').val();
                    editmatrix_update(params);
                }
                tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                feedbacktextarea = $("#addfeedbackmatrix textarea");
                if (feedbacktextarea.length) {
                    tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                }
                dock.hide();
            });
            cancelbutton = newpagemodal.find('.submitcancel.cancel');
            cancelbutton.on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (data.data.form.isnew) {
                    params.action = 'delete';
                    params.blockconfig = $('#instconf_blockconfig').val();
                    editmatrix_update(params);
                }
                tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                feedbacktextarea = $("#addfeedbackmatrix textarea");
                if (feedbacktextarea.length) {
                    tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                }
                dock.hide();
            });
            tinyMCE.idCounter=0;
            if ($("#instconf_text").length) {
                tinyMCE.execCommand('mceAddEditor', false, "instconf_text");
            }
            if ($("#addfeedbackmatrix").length) {
                textareaid = $("#addfeedbackmatrix textarea").attr('id');
                tinyMCE.execCommand('mceAddEditor', false, textareaid);
            }
            // Only allow the point selected to be active in the 'Standard' dropdown
            $("#instconf_smartevidence option:not(:selected)").prop('disabled', true);
            // block title will be overwritten with framework choice so make it disabled
            $("#instconf_title").attr('disabled', true);
            // Set up evidence choices and show/hide related descriptions
            $("#instconf_smartevidence").select2();

            function show_se_desc(id) {
                $("#instconf_smartevidencedesc_container div:not(.description)").addClass('hidden');
                $("#option_" + id).removeClass('hidden');
            }

            show_se_desc($("#instconf_smartevidence").val());
            $("#instconf_smartevidence").on('change', function() {
                show_se_desc($(this).val());
            });
            // When we are saving the annotation block config form
            $('#instconf').on('submit', function(se) {
                se.preventDefault();
                var sdata = $("#instconf :input").serializeArray();
                var values = {};
                var tags = new Array();
                sdata.forEach(function(item, index) {
                    if (item.name == 'tags[]') {
                        tags.push(item.value);
                    }
                    else {
                        values[item.name] = item.value;
                    }
                });
                values['tags'] = tags.join();
                values['framework'] = params.framework;
                values['view'] = params.view;
                values['option'] = params.option;
                values['action'] = 'update';
                tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                editmatrix_update(values);
                dock.hide();
            });
            // When we are saving the annotation feedback form - changing the evidence status
            $('#annotationfeedback').on('submit', function(se) {
                se.preventDefault();
                var sdata = $("#annotationfeedback :input").serializeArray();
                var values = {};
                sdata.forEach(function(item, index) {
                    values[item.name] = item.value;
                });
                values['framework'] = params.framework;
                values['view'] = params.view;
                values['option'] = params.option;
                values['action'] = 'evidence';
                editmatrix_update(values);
                tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                feedbacktextarea = $("#addfeedbackmatrix textarea");
                if (feedbacktextarea.length) {
                    tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                }
                dock.hide();
            });
            // When we are saving the annotation feedback form - adding new feedback
            $('#addfeedbackmatrix').on('submit', function(se) {
                se.preventDefault();
                var sdata = $("#addfeedbackmatrix :input").serializeArray();
                var values = {};
                sdata.forEach(function(item, index) {
                    values[item.name] = item.value;
                });

                textareaid = $("#addfeedbackmatrix textarea").attr('id');
                if (values['message'].length == 0) {
                    // add error message
                    $("#" + textareaid).parent().append('<div class="errmsg"><span>' +
                        get_string_ajax('annotationfeedbackempty', 'artefact.annotation') +
                        '</span></div>');
                }
                else {
                    values['framework'] = params.framework;
                    values['view'] = params.view;
                    values['option'] = params.option;
                    values['action'] = 'feedback';
                    editmatrix_update(values);
                }
            });
        });
    });

    function editmatrix_update(data) {
        params = data;
        sendjsonrequest('matrixpoint.json.php', params, 'POST', function(results) {
            if (results.data.class) {
                $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span')
                  .attr('class', results.data.class)
                  .attr('title', results.data.title)
                  .data('option', results.data.option)
                  .data('view', results.data.view).empty();
                var completed = parseInt($('#tablematrix tr:eq(' + celly + ') td.completedcount').text(), 10);
                $('#tablematrix tr:eq(' + celly + ') td.completedcount').text(completed + results.data.completed);
            }
            if (results.data.tablerows) {
                if ($("#matrixfeedbacklist").has(".annotationfeedbacktable").length == 0) {
                    $("#matrixfeedbacklist").html('<ul class="annotationfeedbacktable list-group list-group-lite list-unstyled"></div>');
                }
                $("#matrixfeedbacklist .annotationfeedbacktable").html(results.data.tablerows);
                textareaid = $("#addfeedbackmatrix textarea").attr('id');
                tinyMCE.get(textareaid).setContent('');
            }
        });
    }
    // Setup
    carousel_matrix();

    // show / hide tooltips for standard elements
    $('td.code div, tr.standard div').hover(
        function() {
            $(this).find('span').removeClass('hidden');
        },
        function() {
            $(this).find('span').addClass('hidden');
        }
    );
});
EOF;

$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('maintitle', $collection->get('name'));
$smarty->assign('owner', $owner);
$smarty->assign('PAGEHEADING', null);
$smarty->assign('name', $framework->get('name'));
$smarty->assign('description', $framework->get('description'));
$smarty->assign('standards', $standards['standards']);
$smarty->assign('evidence', $evidencematrix);
$smarty->assign('completed', $completed);
$smarty->assign('canaddannotation', Framework::allow_annotation($view->get('id')));
$smarty->assign('standardscount', $standards['count']);
$smarty->assign('framework', $collection->get('framework'));
$smarty->assign('views', $views['views']);
$smarty->assign('viewcount', $views['count']);
if ($view->is_anonymous()) {
    $smarty->assign('PAGEAUTHOR', get_string('anonymoususer'));
    $smarty->assign('author', get_string('anonymoususer'));
    if ($view->is_staff_or_admin_for_page()) {
        $smarty->assign('realauthor', $view->display_author());
    }
    $smarty->assign('anonymous', TRUE);
}
else {
    $smarty->assign('PAGEAUTHOR', $view->formatted_owner());
    $smarty->assign('author', $view->display_author());
    $smarty->assign('anonymous', FALSE);
}

$smarty->display('module:framework:matrix.tpl');
