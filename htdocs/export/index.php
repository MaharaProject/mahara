<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/export');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('collection.php');
define('TITLE', get_string('exportyourportfolio', 'export'));

$SESSION->set('exportdata', '');
$SESSION->set('exportfile', '');

$exportoptions = array();
$exportplugins = plugins_installed('export');

if (!$exportplugins) {
    die_info(get_string('noexportpluginsenabled', 'export'));
}
if (!is_executable(get_config('pathtozip'))) {
    log_info("Either you do not have the 'zip' command installed, or the config setting 'pathtozip' is not pointing at your zip command."
        . " Until you fix this, you will not be able to use the export system.");
    die_info(get_string('zipnotinstalled', 'export'));
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
        'separator' => '</div><div>',
    ),
    'what' => array(
        'type' => 'radio',
        'options' => array(
            'all' => get_string('allmydata', 'export'),
            'views' => get_string('justsomeviews', 'export'),
        ),
        'separator' => '</div><div>',
        'defaultvalue' => 'all',
    ),
);

if ($viewids = get_column('view', 'id', 'owner', $USER->get('id'), 'type', 'portfolio')) {
    foreach ($viewids as $viewid) {
        $view = new View($viewid);
        $elements['view_' . $viewid] = array(
            'type' => 'checkbox',
            'title' => $view->get('title'),
            'description' => $view->get('description'),
            'viewlink' => get_config('wwwroot') . 'view/view.php?id=' . $viewid,
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
    'value' => get_string('generateexport', 'export'),
);

$form = pieform(array(
    'name' => 'export',
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
    global $SESSION;
    $views = array();
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
                $views = array_merge($views, get_column('collection_view', 'view', 'collection', $collection));
            }
        }
        $values['what'] = 'views';
    }

    $exportdata = array(
        'format' => $values['format'],
        'what'   => $values['what'],
        'views'  => $views,
    );
    $SESSION->set('exportdata', $exportdata);

    $smarty = smarty();
    $smarty->assign('heading', '');
    $smarty->display('export/export.tpl');
    exit;
}

$smarty = smarty(
    $jsfiles,
    array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">'),
    array(),
    array('stylesheets' => array('style/views.css'))
);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('pagedescription', get_string('exportpagedescription', 'export'));
$smarty->assign('form', $form);
$smarty->display('form.tpl');
