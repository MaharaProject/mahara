<?php
/**
 *
 * @package    mahara
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/export');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('collection.php');
define('TITLE', get_string('exportyourportfolio', 'export'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'export');
define('SECTION_PAGE', 'index');


$SESSION->set('exportdata', '');
$SESSION->set('exportfile', '');

$exportoptions = array();
$exportplugins = plugins_installed('export');

if (!$exportplugins) {
    die_info(get_string('noexportpluginsenabled', 'export'));
}

foreach ($exportplugins as $plugin) {
    safe_require('export', $plugin->name);
    $exportoptions[$plugin->name] = array(
        'text' => call_static_method(generate_class_name('export', $plugin->name), 'get_title'),
        'description' => call_static_method(generate_class_name('export', $plugin->name), 'get_description'),
    );
}

$elements = array(
    'format' => array(
        'type' => 'radio',
        'options' => $exportoptions,
        'defaultvalue' => 'html',
    ),
    'what' => array(
        'type' => 'radio',
        'options' => array(
            'all' => get_string('allmydata', 'export'),
            'views' => get_string('justsomeviews', 'export'),
        ),
        'defaultvalue' => 'all',
    ),
    'includefeedback' => array(
        'type' => 'switchbox',
        'class' => 'last',
        'title' => get_string('includecomments', 'export'),
        'description' => get_string('includecommentsdescription', 'export'),
        'defaultvalue' => 1,
    ),
);

if ($viewids = get_column_sql('SELECT id FROM {view} WHERE owner = ? AND type = ? ORDER BY title', array($USER->get('id'), 'portfolio'))) {
    foreach ($viewids as $viewid) {
        $view = new View($viewid);
        $elements['view_' . $viewid] = array(
            'type' => 'checkbox',
            'class' => 'checkbox',
            'title' => $view->get('title'),
            'description' => $view->get('description'),
            'viewlink' => $view->get_url(true, true),
        );
    }
    $jsfiles = array('js/preview.js', 'js/export.js');

    $collections = get_records_sql_array('
        SELECT c.id, c.name, c.description
        FROM {collection} c JOIN {collection_view} cv ON c.id = cv.collection
        WHERE c.owner = ?
        GROUP BY c.id, c.name, c.description
        HAVING COUNT(cv.view) > 0',
        array($USER->get('id'))
    );
    if ($collections) {
        $elements['what']['options']['collections'] = get_string('justsomecollections', 'export');
        foreach ($collections as $collection) {
            $elements['collection_' . $collection->id] = array(
                'type' => 'checkbox',
                'class' => 'checkbox',
                'title' => $collection->name,
                'description' => $collection->description,
            );
        }
    }
}
else {
    $elements['what']['disabled'] = true;
    $jsfiles = array();
}

$elements['submit'] = array(
    'type' => 'submit',
    'class' => 'btn-primary',
    'value' => get_string('generateexport', 'export'),
);

$form = pieform(array(
    'name' => 'export',
    'class' => 'portfolio-export',
    'checkdirtychange' => false,
    'template' => 'export.php',
    'templatedir' => pieform_template_dir('export.php'),
    'autofocus' => false,
    'elements' => $elements
));


function export_validate(Pieform $form, $values) {
    global $SESSION;
    if ($values['what'] == 'views') {
        $viewchosen = false;
        foreach ($values as $key => $value) {
            if (substr($key, 0, 5) == 'view_' && $value) {
                $viewchosen = true;
            }
        }
        if (!$viewchosen) {
            $form->set_error('what', '');
            $SESSION->add_error_msg(get_string('youmustselectatleastoneviewtoexport', 'export'));
        }
    }
    else if ($values['what'] == 'collections') {
        $viewchosen = false;
        foreach ($values as $key => $value) {
            if (substr($key, 0, 11) == 'collection_' && $value) {
                $viewchosen = true;
            }
        }
        if (!$viewchosen) {
            $form->set_error('what', '');
            $SESSION->add_error_msg(get_string('youmustselectatleastonecollectiontoexport', 'export'));
        }
    }
}

function export_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    $views = array();
    $collections = array();
    if ($values['what'] == 'views') {
        foreach ($values as $key => $value) {
            if (substr($key, 0, 5) == 'view_' && $value) {
                $views[] = intval(substr($key, 5));
            }
        }
    }
    else if ($values['what'] == 'collections') {
        foreach ($values as $key => $value) {
            if (substr($key, 0, 11) == 'collection_' && $value) {
                $collection = intval(substr($key, 11));
                $collections[] = $collection;
                $views = array_merge($views, get_column('collection_view', 'view', 'collection', $collection));
            }
        }
    }

    if ($values['format'] == 'leap' && get_config('exporttoqueue') == 1) {
        // insert into the export_queue;
        require_once(get_config('docroot') . 'export/lib.php');
        $objectarray = array();
        if ($values['what'] == 'collections') {
            foreach ($collections as $collectionid) {
                $collection = new Collection($collectionid);
                $objectarray[] = $collection;
            }
        }
        else if ($values['what'] == 'views') {
            foreach ($views as $viewid) {
                $view = new View($viewid);
                $objectarray[] = $view;
            }
        }
        export_add_to_queue($objectarray, null, $USER, $values['what']);
        $SESSION->add_ok_msg(get_string('addedleap2atoexportqueue' . $values['what'], 'export'));
        redirect('/export/index.php');
    }
    else {
        $exportdata = array(
            'format'          => $values['format'],
            'what'            => $values['what'],
            'views'           => $views,
            'includefeedback' => $values['includefeedback'],
        );
        $SESSION->set('exportdata', $exportdata);

        $smarty = smarty();
        $smarty->assign('heading', '');
        $smarty->display('export/export.tpl');
        exit;
    }
}

$smarty = smarty(
    $jsfiles
);
$smarty->assign('pagedescription', get_string('exportportfoliodescription', 'export'));
$smarty->assign('form', $form);
$smarty->display('form.tpl');
