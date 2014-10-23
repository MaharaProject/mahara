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

// TODO fix title of this page
// TODO check security of this page
define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'layoutpreviewimage.php');
define('TITLE', get_string('changemyviewlayout', 'view'));

$id = param_integer('id');
$new = param_boolean('new');
$view = new View($id);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

$view->set_edit_nav();
$view->set_user_theme();
$numrows = $view->get('numrows');
$numcolumns = $view->get('numcolumns');
$layoutcolumns = View::$layoutcolumns; // static, all possible column width combinations
$layoutrows = $view->get_layoutrows();
$maxlayoutrows = View::$maxlayoutrows; // static, max possible rows for custom layouts
$basicoptionids = array_keys(
        get_records_select_assoc(
                'view_layout',
                'layoutmenuorder > 0 AND iscustom = 0',
                array(),
                'layoutmenuorder',
                'id, id'
        )
);
$currentlayout = $view->get('layout');
// if not set, use equal width layout for that number of columns
if (!$currentlayout) {
    // if columns have been dynamically added or removed from a multi-row layout,
    // there may be no valid layout id, in which case none of the layout options will be selected
    $currentlayout = $view->get_layout()->id;
}
if (!in_array($currentlayout, $basicoptionids)) {
    $basicoptionids[] = $currentlayout;
}

$layoutoptions = array();
$basiclayoutoptions = array();
$maxrows = 3;
foreach ($layoutrows as $key => $layout) {
    $maxrows = (count($layout) > $maxrows)? count($layout) : $maxrows;
    $layoutoptions[$key]['rows'] = count($layout);
    $layoutoptions[$key]['text'] = '';

    for ($r=0; $r<count($layout); $r++) {
        // store multi-row column widths for each option - used as img titles in layout.tpl
        if ($r==0) {
            $layoutoptions[$key]['columns'] = get_string($layoutcolumns[$layout[$r+1]]->widths, 'view');
        }
        else {
            $layoutoptions[$key]['columns'] .= ' / ' . get_string($layoutcolumns[$layout[$r+1]]->widths, 'view');
        }
    }
}

foreach ($basicoptionids as $id) {
    if (array_key_exists($id, $layoutoptions)) {
        $basiclayoutoptions[$id] = $layoutoptions[$id];
    }
}

$clnumcolumnsoptions = array();
for ($i=1; $i<6; $i++) {
    $clnumcolumnsoptions[$i] = $i;
}

$columnlayoutoptions = array();
$columnlayouts = get_records_assoc('view_layout_columns');
foreach ($columnlayouts as $layout => $percents) {
    $percentswidths = str_replace(',', ' - ', $percents->widths);
    $columnlayoutoptions[$layout] = $percentswidths;
}

// provide a simple default to build custom layouts with
$defaultcustomlayout = $view->default_columnsperrow();
$defaultlayout = get_record('view_layout_columns', 'columns', $defaultcustomlayout[1]->columns, 'widths', $defaultcustomlayout[1]->widths);
$clnumcolumnsdefault = $defaultlayout->columns;
$clwidths = $defaultlayout->widths;

$inlinejavascript = <<<JAVASCRIPT

function get_max_custom_rows() {
    return {$maxlayoutrows};
}

addLoadEvent(function () {
    formchangemanager.add("viewlayout");
});

JAVASCRIPT;

$elements = array(
    'viewid' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
    ),
);
$elements['customlayoutnumrows'] = array(
     'type'  => 'hidden',
     'value' => 1,
);
$elements['layoutselect'] = array(
        'type'  => 'hidden',
        'value' => $currentlayout,
        'sesskey' =>  $USER->get('sesskey'),
);
$elements['layoutfallback'] = array(
        'type'  => 'hidden',
        'value' => $defaultlayout->id,
);
$elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('save'),
);

$templatedata = array(
        'id' => $id,
        'basiclayoutoptions' => $basiclayoutoptions,
        'layoutoptions' => $layoutoptions,
        'currentlayout' => $currentlayout,
        'clnumcolumnsoptions' => $clnumcolumnsoptions,
        'clnumcolumnsdefault' => $clnumcolumnsdefault,
        'columnlayoutoptions' => $columnlayoutoptions,
        'customlayout' => $defaultlayout->id,
        'clwidths' => $clwidths,
        'maxrows' => $maxrows
        );

$layoutform = array(
        'name' => 'viewlayout',
        'template' => 'viewlayout.php',
        'templatedir' => pieform_template_dir('viewlayout.php'),
        'autofocus' => false,
        'templatedata' => $templatedata,
        'elements' => $elements
);

$layoutform = pieform($layoutform);

$javascript = array('jquery','js/jquery/jquery-ui/js/jquery-ui-1.10.2.min.js', 'js/customlayout.js','js/jquery/modernizr.custom.js');
$stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/ui-lightness/jquery-ui-1.10.2.min.css') . '">';

$smarty = smarty($javascript, $stylesheets, array('view' => array('Row', 'removethisrow', 'rownr', 'nrrows', 'generatingpreview')), array('sidebars' => false));

$smarty->assign('INLINEJAVASCRIPT', $inlinejavascript);
$smarty->assign('form', $layoutform);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtype', $view->get('type'));
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $view->get_url());
$smarty->assign('new', $new);
if (get_config('viewmicroheaders')) {
    $smarty->assign('maharalogofilename', 'images/site-logo-small.png');
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false));
}
$smarty->assign('issiteview', $view->get('institution') == 'mahara');
if ($view->get('owner') == "0") {
    $smarty->assign('issitetemplate', true);
}
$smarty->display('view/layout.tpl');

function viewlayout_validate(Pieform $form, $values) {
    global $layoutrows;
    if (!isset($layoutrows[$values['layoutselect']]) ) {
        $form->set_error(null, get_string('invalidlayoutselection', 'error'));
    }
}

function viewlayout_submit(Pieform $form, $values) {
    global $view, $SESSION, $new, $layoutrows, $layoutcolumns;

    $oldrows = $view->get('numrows');
    $oldlayout = $view->get_layout();
    $newlayout = $values['layoutselect'];
    if (!isset($layoutrows[$newlayout])) {
        throw new ParamOutOfRangeException(get_string('invalidlayoutselection', 'error', $action));
    }
    else {
        $newrows = count($layoutrows[$newlayout]);
    }

    db_begin();

    // for each existing row which will still exist after the update, check whether to add or remove columns
    for ($i = 0; $i < min(array($oldrows, $newrows)); $i++) {
        // compare oldlayout column structure with newlayout
        $oldcolumns = $oldlayout->rows[$i+1]['columns'];
        $newcolumnindex = $layoutrows[$newlayout][$i+1];
        $newcolumns = $layoutcolumns[$newcolumnindex]->columns;

        // Specify row when adding or removing columns
        if ($oldcolumns > $newcolumns) {
            for ($j = $oldcolumns; $j > $newcolumns; $j--) {
                $view->removecolumn(array('row' => $i+1, 'column' => $j));
            }
        }
        else if ($oldcolumns < $newcolumns) {
            for ($j = $oldcolumns; $j < $newcolumns; $j++) {
                $view->addcolumn(array('row' => $i+1, 'before' => $j+1, 'returndata' => false));
            }
        }

        $dbcolumns = get_field('view_rows_columns', 'columns', 'view', $view->get('id'), 'row', $i+1);

        if ($dbcolumns != $newcolumns) {
            db_rollback();
            $SESSION->add_error_msg(get_string('changecolumnlayoutfailed', 'view'));
            redirect(get_config('wwwroot') . 'view/layout.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
        }
    }
    // add or remove rows and move content accordingly if required
    if ($oldrows > $newrows) {
        for ($i = $oldrows; $i > $newrows; $i--) {
            $view->removerow(array('row' => $i, 'layout' => $oldlayout));
        }
    }
    else if ($oldrows < $newrows) {
        for ($i = $oldrows; $i < $newrows; $i++) {
            $view->addrow(array('before' => $i + 1, 'newlayout' => $newlayout, 'returndata' => false));
        }
    }

    if ($view->get('numrows') != $newrows) {
        db_rollback();
        $SESSION->add_error_msg(get_string('changerowlayoutfailed', 'view'));
        redirect(get_config('wwwroot') . 'view/layout.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
    }

    db_commit();

    $view->set('layout', $newlayout);
    $view->commit();
    $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
    redirect('/view/blocks.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
}
