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
$category = param_alpha('c', '');
$view = new View($id);
$numcolumns = $view->get('numcolumns');
$currentlayout = $view->get('layout');
$back = !$USER->get_account_preference('addremovecolumns');
$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

// if not set, use equal width layout for that number of columns
if (!$currentlayout) {
    $currentlayout = $view->get_layout()->id;
}

if ($numcolumns > 1 && $numcolumns < 5) {
    $layouts = get_records_array('view_layout', 'columns', $numcolumns);
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
                'type' => 'submitcancel',
                'value' => array(get_string('submit'), get_string('cancel')),
                'goto' => get_config('wwwroot') . 'view/columns.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new
            ),
        ),
    ));
}
else {
    $SESSION->add_error_msg(get_string('noviewlayouts', 'view', $numcolumns));
    redirect('/view/blocks.php?id=' . $id . '&c=' . $category . '&new=' . $new);
}

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('currentlayout', $currentlayout);
$smarty->assign('form', $layoutform);
$smarty->assign('form_start_tag', $layoutform->get_form_tag());
$smarty->assign('options', $options);
$smarty->assign('back', $back);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('view/layout.tpl');

function viewlayout_submit(Pieform $form, $values) {
    global $view, $SESSION, $category, $new;
    $view->set('layout', $values['layout']);
    $view->commit();
    $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
    redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new);
}

?>
