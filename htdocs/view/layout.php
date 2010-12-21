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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// TODO fix title of this page
// TODO check security of this page
define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');
define('TITLE', get_string('changemyviewlayout', 'view'));

$id = param_integer('id');
$new = param_boolean('new');
$view = new View($id);
$numcolumns = $view->get('numcolumns');
$currentlayout = $view->get('layout');
$view->set_edit_nav();
$view->set_user_theme();

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

// if not set, use equal width layout for that number of columns
if (!$currentlayout) {
    $currentlayout = $view->get_layout()->id;
}

$layouts = get_records_assoc('view_layout', '', '', 'columns,id');
$options = array();
foreach ($layouts as $layout) {
    $options[$layout->id] = get_string($layout->widths, 'view');
}
$layoutform = new Pieform(array(
    'name' => 'viewlayout',
    'elements' => array(
        'layout'  => array(
            'type' => 'radio',
            'options' => $options,
            'defaultvalue' => $currentlayout,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
));

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('layouts', $layouts);
$smarty->assign('currentlayout', $currentlayout);
$smarty->assign('form', $layoutform);
$smarty->assign('form_start_tag', $layoutform->get_form_tag());
$smarty->assign('options', $options);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $view->get_url());
$smarty->assign('new', $new);
if (get_config('viewmicroheaders')) {
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false));
}
$smarty->display('view/layout.tpl');

function viewlayout_validate(Pieform $form, $values) {
    global $layouts;
    if (!isset($layouts[$values['layout']])) {
        $form->set_error('invalidlayout');
    }
}

function viewlayout_submit(Pieform $form, $values) {
    global $view, $SESSION, $new, $layouts;

    $oldcolumns = $view->get('numcolumns');
    $newcolumns = $layouts[$values['layout']]->columns;

    db_begin();

    if ($oldcolumns > $newcolumns) {
        for ($i = $oldcolumns; $i > $newcolumns; $i--) {
            $view->removecolumn(array('column' => $i));
        }
    }
    else if ($oldcolumns < $newcolumns) {
        for ($i = $oldcolumns; $i < $newcolumns; $i++) {
            $view->addcolumn(array('before' => $i + 1, 'returndata' => false));
        }
    }

    $dbcolumns = get_field('view', 'numcolumns', 'id', $view->get('id'));

    if ($dbcolumns != $newcolumns) {
        db_rollback();
        $SESSION->add_error_msg(get_string('changecolumnlayoutfailed', 'view'));
        redirect(get_config('wwwroot') . 'view/layout.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
    }

    db_commit();

    $view->set('layout', $values['layout']);
    $view->commit();
    $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
    redirect('/view/blocks.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
}