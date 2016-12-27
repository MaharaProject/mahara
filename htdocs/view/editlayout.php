<?php

/*
 * Created by De Chiara Antonella
 * Eticeo Santé (http://eticeo.fr)
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editlayout');

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'layoutpreviewimage.php');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('libroot') . 'view.php');

$id = param_integer('id');
$new = param_boolean('new');
$view = new View($id);

$state = get_string('changemyviewlayout', 'view');

if ($new) {
    define('TITLE', get_string('notitle', 'view'));
}
else {
    define('TITLE', $view->get('title'));
}
define('SUBSECTIONHEADING', TITLE);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}
// Make sure we can edit view title for this type.
// If not, then we probably meant to edit blocks
if (!$view->can_edit_title()) {
    redirect('/view/layout.php?id=' . $view->get('id'));
}
// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}

$group = $view->get('group');
$institution = $view->get('institution');
$view->set_edit_nav();
$view->set_user_theme();

// Edit
if ($group && !group_within_edit_window($group)) {
    throw new AccessDeniedException();
}

$new = param_boolean('new', 0);

$state = get_string('edittitleanddescription', 'view');

$formatstring = '%s (%s)';
$ownerformatoptions = array(
    FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
    FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
    FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
);

$displayname = display_name($USER);
if ($displayname !== '') {
    $ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('preferredname'), $displayname);
}
$studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
if ($studentid !== '') {
    $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
}

// Clean urls are only available for portfolio views owned by groups or users who already
// have their own clean profiles or group homepages.
if ($urlallowed = get_config('cleanurls') && $view->get('type') == 'portfolio' && !$institution) {
    if ($group) {
        $groupdata = get_record('group', 'id', $group);
        if ($urlallowed = !is_null($groupdata->urlid) && strlen($groupdata->urlid)) {
            $cleanurlbase = group_homepage_url($groupdata) . '/';
        }
    }
    else {
        $userurlid = $USER->get('urlid');
        if ($urlallowed = !is_null($userurlid) && strlen($userurlid)) {
            $cleanurlbase = profile_url($USER) . '/';
        }
    }
}

// Layout
$numrows = $view->get('numrows');
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

    $structure = array();
    $rowtext = array();
    for ($r = 1; $r <= count($layout); $r++) {
        $widths = $layoutcolumns[$layout[$r]]->widths;
        $structure['layout']['row' . $r] = $widths;
        $rowtext[] = str_replace(',', '-', $widths);
    }
    $structure['text'] = implode(' / ', $rowtext);
    $l = new LayoutPreviewImage($structure);
    $layoutoptions[$key]['layout'] = $l->create_preview();
    $layoutoptions[$key]['columns'] = $structure['text'];
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
$defaultcustomlayout = View::default_columnsperrow();
$defaultlayout = get_record('view_layout_columns', 'columns', $defaultcustomlayout[1]->columns, 'widths', $defaultcustomlayout[1]->widths);
$clnumcolumnsdefault = $defaultlayout->columns;
$clwidths = $defaultlayout->widths;

// Ready custom layout preview.
$defaultlayoutpreviewdata['layout']['row1'] = $defaultcustomlayout[1]->widths;
$defaultlayoutpreviewdata['text'] = get_string($defaultcustomlayout[1]->widths, 'view');
$defaultlayoutpreview = new LayoutPreviewImage($defaultlayoutpreviewdata);

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
// Edit
$elements['id'] = array(
    'type'  => 'hidden',
    'value' => $view->get('id'),
);
$elements['new'] = array(
    'type' => 'hidden',
    'value' => $new,
);
$elements['title'] = array(
    'type'         => 'text',
    'title'        => get_string('title','view'),
    'defaultvalue' => $view->get('title'),
    'rules'        => array( 'required' => true ),
);
$elements['urlid'] = array(
    'type'         => 'text',
    'title'        => get_string('viewurl', 'view'),
    'prehtml'      => '<span class="description">' . (isset($cleanurlbase) ? $cleanurlbase : '') . '</span> ',
    'description'  => get_string('viewurldescription', 'view') . ' ' . get_string('cleanurlallowedcharacters'),
    'defaultvalue' => $new ? null : $view->get('urlid'),
    'rules'        => array('maxlength' => 100, 'regex' => get_config('cleanurlvalidate')),
    'ignore'       => !$urlallowed || $new,
);
$elements['description'] = array(
    'type'         => 'wysiwyg',
    'title'        => get_string('description','view'),
    'rows'         => 10,
    'cols'         => 70,
    'defaultvalue' => $view->get('description'),
    'rules'        => array('maxlength' => 65536),
);
$elements['tags'] = array(
    'type'         => 'tags',
    'title'        => get_string('tags'),
    'description'  => get_string('tagsdescprofile'),
    'defaultvalue' => $view->get('tags'),
    'help'         => true,
);
if ($group) {
    $grouproles = $USER->get('grouproles');
    if ($grouproles[$group] == 'admin') {
        $elements['locked'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('Locked', 'view'),
            'description'  => get_string('lockedgroupviewdesc', 'view'),
            'defaultvalue' => $view->get('locked'),
            'disabled'     => $view->get('type') == 'grouphomepage', // This page unreachable for grouphomepage anyway
        );
    }
}
if (!($group || $institution)) {
    $default = $view->get('ownerformat');
    if (!$default) {
        $default = FORMAT_NAME_DISPLAYNAME;
    }
    $elements['ownerformat'] = array(
        'type'         => 'select',
        'title'        => get_string('ownerformat','view'),
        'description'  => get_string('ownerformatdescription','view'),
        'options'      => $ownerformatoptions,
        'defaultvalue' => $default,
        'rules'        => array('required' => true),
    );
}

if (get_config('allowanonymouspages')) {
    $elements['anonymise'] = array(
        'type'         => 'switchbox',
        'title'        => get_string('anonymise','view'),
        'description'  => get_string('anonymisedescription','view'),
        'defaultvalue' => $view->get('anonymise'),
    );
}
$elements['submit'] = array(
        'type' => 'submit',
        'class' => 'btn-primary',
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
    'customlayoutid' => $defaultlayout->id,
    'customlayout' => $defaultlayoutpreview->create_preview(),
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

$javascript = array('jquery','js/jquery/jquery-ui/js/jquery-ui.min.js', 'js/customlayout.js','js/jquery/modernizr.custom.js');
$stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';

$smarty = smarty($javascript, $stylesheets, array('view' => array('Row', 'removethisrow', 'rownr', 'nrrows')), array('sidebars' => false));

$smarty->assign('INLINEJAVASCRIPT', $inlinejavascript);
$smarty->assign('form', $layoutform);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtype', $view->get('type'));
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $view->get_url());
$smarty->assign('new', $new);
$smarty->assign('issiteview', $view->get('institution') == 'mahara');
$smarty->assign('issitetemplate', ($view->get('template') == View::SITE_TEMPLATE ? true : false));
$smarty->assign('PAGEHEADING', $state);
$smarty->display('view/editlayout.tpl');

function viewlayout_validate(Pieform $form, $values) {
    global $layoutrows, $view;

    if (!isset($layoutrows[$values['layoutselect']]) ) {
        $form->set_error(null, get_string('invalidlayoutselection', 'error'));
    }

    if (isset($values['urlid']) && $values['urlid'] != $view->get('urlid')) {
        if (strlen($values['urlid']) < 3) {
            $form->set_error('urlid', get_string('rule.minlength.minlength', 'pieforms', 3));
        }
        else if ($group = $view->get('group') and record_exists('view', 'group', $group, 'urlid', $values['urlid'])) {
            $form->set_error('urlid', get_string('groupviewurltaken', 'view'));
        }
        else if ($owner = $view->get('owner') and record_exists('view', 'owner', $owner, 'urlid', $values['urlid'])) {
            $form->set_error('urlid', get_string('userviewurltaken', 'view'));
        }
    }
}

function viewlayout_submit(Pieform $form, $values) {
    global $view, $SESSION, $new, $layoutrows, $layoutcolumns, $urlallowed;

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
            redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
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

    $view->set('title', $values['title']);
    if (trim($values['description']) !== '') {
        // Add or update embedded images in the view description
        require_once('embeddedimage.php');
        $view->set('description', EmbeddedImage::prepare_embedded_images($values['description'], 'description', $view->get('id')));
    }
    else {
        // deleting description
        $view->set('description', '');
    }
    $view->set('tags', $values['tags']);
    if (isset($values['locked'])) {
        $view->set('locked', (int)$values['locked']);
    }
    if (isset($values['ownerformat']) && $view->get('owner')) {
        $view->set('ownerformat', $values['ownerformat']);
    }
    if (isset($values['anonymise'])) {
        $view->set('anonymise', (int)$values['anonymise']);
    }
    if (isset($values['urlid'])) {
        $view->set('urlid', strlen($values['urlid']) == 0 ? null : $values['urlid']);
    }
    else if ($new && $urlallowed) {
        // Generate one automatically based on the title
        $desired = generate_urlid($values['title'], get_config('cleanurlviewdefault'), 3, 100);
        $ownerinfo = (object) array('owner' => $view->get('owner'), 'group' => $view->get('group'));
        $view->set('urlid', View::new_urlid($desired, $ownerinfo));
    }

    $view->set('layout', $newlayout);
    $view->commit();
    $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
    $SESSION->add_ok_msg(get_string('viewsavedsuccessfully', 'view'));
    redirect('/view/blocks.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
}